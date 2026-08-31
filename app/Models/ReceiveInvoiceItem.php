<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class ReceiveInvoiceItem extends BaseModel {
    protected $table = 'receive_invoice_items';

    public function getByInvoice($receiveInvoiceId) {
        $sql = "SELECT rii.*, p.name as product_name, g.name as product_generic_name, p.strength as product_strength 
                FROM {$this->table} rii 
                LEFT JOIN products p ON rii.product_id = p.id 
                LEFT JOIN generics g ON p.generic_id = g.id
                WHERE rii.receive_invoice_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $receiveInvoiceId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (receive_invoice_id, product_id, quantity, cost_price, subtotal, batch_number, expiry_date) 
                VALUES (:receive_invoice_id, :product_id, :quantity, :cost_price, :subtotal, :batch_number, :expiry_date)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'receive_invoice_id' => $data['receive_invoice_id'],
            'product_id'         => $data['product_id'],
            'quantity'           => $data['quantity'],
            'cost_price'         => $data['cost_price'],
            'subtotal'           => $data['subtotal'],
            'batch_number'       => !empty($data['batch_number']) ? $data['batch_number'] : null,
            'expiry_date'        => !empty($data['expiry_date']) ? $data['expiry_date'] : null,
        ]);
    }

    public function deleteByInvoice($receiveInvoiceId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE receive_invoice_id = :id");
        return $stmt->execute(['id' => $receiveInvoiceId]);
    }
}
