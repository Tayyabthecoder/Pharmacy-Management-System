<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class InventoryLog extends BaseModel {
    protected $table = 'inventory_logs';

    public function getRecent($limit = 20) {
        $sql = "SELECT l.*, p.name as product_name, u.name as user_name, c.name as category_name 
                FROM {$this->table} l 
                LEFT JOIN products p ON l.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTodaySummary() {
        $sql = "SELECT type, COUNT(*) as count FROM {$this->table} WHERE date(created_at) = date('now') GROUP BY type";
        return $this->db->query($sql)->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (product_id, user_id, qty_change, type, remarks) 
                VALUES (:pid, :uid, :change, :type, :remarks)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'pid' => $data['product_id'],
            'uid' => $data['user_id'],
            'change' => $data['qty_change'],
            'type' => $data['type'],
            'remarks' => $data['remarks']
        ]);
    }
}
