<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class InvoiceItem extends BaseModel {
    protected $table = 'invoice_items';

    public function getByInvoice($invoiceId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE invoice_id = :id");
        $stmt->execute(['id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (invoice_id, product_id, quantity, price, subtotal, cost_price) 
                VALUES (:inv_id, :pid, :qty, :price, :subtotal, :cost_price)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'inv_id' => $data['invoice_id'],
            'pid' => $data['product_id'],
            'qty' => $data['quantity'],
            'price' => $data['price'],
            'subtotal' => $data['subtotal'],
            'cost_price' => $data['cost_price'] ?? 0.00
        ]);
    }

    public function deleteByInvoice($invoiceId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE invoice_id = :id");
        return $stmt->execute(['id' => $invoiceId]);
    }

    public function getByInvoiceWithProducts(int $invoiceId) {
        $sql = "SELECT ii.*, p.name as product_name 
                FROM {$this->table} ii 
                LEFT JOIN products p ON ii.product_id = p.id 
                WHERE ii.invoice_id = :aid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['aid' => $invoiceId]);
        return $stmt->fetchAll();
    }
}
