<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class ProductBatch extends BaseModel {
    protected $table = 'product_batches';

    /**
     * Get all active batches with stock > 0 for a product ordered by expiry date (FEFO).
     * Batches with earliest expiry come first. Batches without expiry date come last.
     */
    public function getAvailableBatchesFEFO(int $productId): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_id = :pid AND quantity > 0 
                ORDER BY 
                    CASE WHEN expiry_date IS NULL OR expiry_date = '' THEN 1 ELSE 0 END ASC,
                    expiry_date ASC, 
                    id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all batches (including depleted) for a product.
     */
    public function getByProduct(int $productId): array {
        $sql = "SELECT b.*, ri.invoice_number as receive_invoice_number, s.name as supplier_name 
                FROM {$this->table} b 
                LEFT JOIN receive_invoices ri ON b.receive_invoice_id = ri.id 
                LEFT JOIN suppliers s ON ri.supplier_id = s.id 
                WHERE b.product_id = :pid 
                ORDER BY b.created_at DESC, b.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pid' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atomically decrement stock from a specific batch.
     */
    public function decrementBatch(int $batchId, int $qty): bool {
        if ($qty <= 0) return true;
        $sql = "UPDATE {$this->table} SET quantity = quantity - :qty WHERE id = :id AND quantity >= :qty";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['qty' => $qty, 'id' => $batchId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Increment stock for a batch (e.g. returns).
     */
    public function incrementBatch(int $batchId, int $qty): bool {
        if ($qty <= 0) return true;
        $sql = "UPDATE {$this->table} SET quantity = quantity + :qty WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['qty' => $qty, 'id' => $batchId]);
    }

    /**
     * Create a new product batch record.
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (product_id, batch_number, expiry_date, quantity, cost_price, receive_invoice_id) 
                VALUES (:pid, :batch, :expiry, :qty, :cost, :receive_id)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            'pid'        => (int)$data['product_id'],
            'batch'      => !empty($data['batch_number']) ? trim($data['batch_number']) : null,
            'expiry'     => !empty($data['expiry_date']) ? trim($data['expiry_date']) : null,
            'qty'        => (int)$data['quantity'],
            'cost'       => (float)($data['cost_price'] ?? 0.00),
            'receive_id' => !empty($data['receive_invoice_id']) ? (int)$data['receive_invoice_id'] : null
        ]);
        return $success ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Perform true FEFO deduction for requested quantity across available batches.
     * Returns an array of deductions: [['batch_id' => X, 'batch_number' => '...', 'quantity' => Y, 'cost_price' => Z], ...]
     * Throws Exception if available batches do not have sufficient stock.
     */
    public function deductStockFEFO(int $productId, int $requestedQty): array {
        if ($requestedQty <= 0) return [];

        $batches = $this->getAvailableBatchesFEFO($productId);
        $deductions = [];
        $remaining = $requestedQty;

        foreach ($batches as $batch) {
            $batchQty = (int)$batch['quantity'];
            if ($batchQty <= 0) continue;

            $take = min($batchQty, $remaining);
            if (!$this->decrementBatch((int)$batch['id'], $take)) {
                throw new Exception("Concurrent batch stock deduction conflict on batch ID {$batch['id']}.");
            }

            $deductions[] = [
                'batch_id'     => (int)$batch['id'],
                'batch_number' => $batch['batch_number'] ?? 'DEFAULT',
                'quantity'     => $take,
                'cost_price'   => (float)($batch['cost_price'] ?? 0.00),
                'expiry_date'  => $batch['expiry_date'] ?? null
            ];

            $remaining -= $take;
            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            // Fallback: Check if product table has remaining quantity and create an auto-reconcile batch
            $stmt = $this->db->prepare("SELECT quantity, batch_number, expiry_date, cost_price FROM products WHERE id = :pid");
            $stmt->execute(['pid' => $productId]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($prod && (int)$prod['quantity'] >= $requestedQty) {
                // Create an auto-reconciled batch for remaining
                $newBatchId = $this->create([
                    'product_id' => $productId,
                    'batch_number' => $prod['batch_number'] ?? 'AUTO-MIGRATED',
                    'expiry_date' => $prod['expiry_date'] ?? null,
                    'quantity' => 0, // already deducting
                    'cost_price' => (float)($prod['cost_price'] ?? 0.00)
                ]);
                $deductions[] = [
                    'batch_id'     => $newBatchId,
                    'batch_number' => $prod['batch_number'] ?? 'AUTO-MIGRATED',
                    'quantity'     => $remaining,
                    'cost_price'   => (float)($prod['cost_price'] ?? 0.00),
                    'expiry_date'  => $prod['expiry_date'] ?? null
                ];
                $remaining = 0;
            } else {
                throw new Exception("Insufficient stock in active batches to fulfill requested quantity of $requestedQty (missing $remaining units).");
            }
        }

        return $deductions;
    }

    /**
     * Restore or increment stock for returns.
     */
    public function returnStockToBatch(int $productId, int $qty, ?string $batchNumber = null, ?string $expiryDate = null, float $costPrice = 0.00): bool {
        if ($qty <= 0) return true;

        // Try to find the latest active batch for this product
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE product_id = :pid ORDER BY id DESC LIMIT 1");
        $stmt->execute(['pid' => $productId]);
        $batchId = $stmt->fetchColumn();

        if ($batchId) {
            return $this->incrementBatch((int)$batchId, $qty);
        } else {
            // Create a return batch
            $newId = $this->create([
                'product_id' => $productId,
                'batch_number' => $batchNumber ?? 'RETURNED',
                'expiry_date' => $expiryDate,
                'quantity' => $qty,
                'cost_price' => $costPrice
            ]);
            return $newId > 0;
        }
    }
}

