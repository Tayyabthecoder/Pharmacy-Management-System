<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class SupplierPayment extends BaseModel {
    protected $table = 'supplier_payments';

    public function createPayment(array $data): int {
        $sql = "INSERT INTO {$this->table} (receive_invoice_id, supplier_id, amount, payment_date, payment_method, notes, created_by)
                VALUES (:receive_invoice_id, :supplier_id, :amount, :payment_date, :payment_method, :notes, :created_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'receive_invoice_id' => (int)$data['receive_invoice_id'],
            'supplier_id'        => (int)$data['supplier_id'],
            'amount'             => (float)$data['amount'],
            'payment_date'       => $data['payment_date'] ?? date('Y-m-d'),
            'payment_method'     => $data['payment_method'] ?? 'cash',
            'notes'              => !empty($data['notes']) ? trim($data['notes']) : null,
            'created_by'         => !empty($data['created_by']) ? (int)$data['created_by'] : null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByReceiveInvoice(int $receiveInvoiceId): array {
        $sql = "SELECT sp.*, u.name as created_by_name 
                FROM {$this->table} sp 
                LEFT JOIN users u ON sp.created_by = u.id 
                WHERE sp.receive_invoice_id = :id 
                ORDER BY sp.payment_date DESC, sp.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $receiveInvoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySupplier(int $supplierId): array {
        $sql = "SELECT sp.*, ri.invoice_number as receive_invoice_number, u.name as created_by_name 
                FROM {$this->table} sp 
                LEFT JOIN receive_invoices ri ON sp.receive_invoice_id = ri.id 
                LEFT JOIN users u ON sp.created_by = u.id 
                WHERE sp.supplier_id = :supplier_id 
                ORDER BY sp.payment_date DESC, sp.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['supplier_id' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPaidForInvoice(int $receiveInvoiceId): float {
        $sql = "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE receive_invoice_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $receiveInvoiceId]);
        return (float)$stmt->fetchColumn();
    }

    public function getSupplierCreditSummary(): array {
        $sql = "SELECT 
                    s.id as supplier_id,
                    s.name as supplier_name,
                    s.phone as supplier_phone,
                    COALESCE(SUM(CASE WHEN ri.is_return = 0 THEN ri.net_amount ELSE -ri.net_amount END), 0) as total_purchases,
                    COALESCE(SUM(CASE WHEN ri.is_return = 0 THEN ri.amount_paid ELSE -ri.amount_paid END), 0) as total_paid,
                    COALESCE(SUM(CASE WHEN ri.is_return = 0 THEN (ri.net_amount - ri.amount_paid) ELSE 0 END), 0) as total_due
                FROM suppliers s
                LEFT JOIN receive_invoices ri ON s.id = ri.supplier_id
                GROUP BY s.id, s.name, s.phone
                ORDER BY total_due DESC, s.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
