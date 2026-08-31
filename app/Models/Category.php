<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class Category extends BaseModel {
    protected $table = 'categories';

    public function getAll() {
        return $this->all($this->table);
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description) VALUES (:name, :description)");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = :name, description = :description WHERE id = :id");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'id' => $id
        ]);
    }

    public function getAllWithProductCount() {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByNameExcludingId($name, $excludeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE LOWER(name) = LOWER(:name) AND id != :excludeId LIMIT 1");
        $stmt->execute(['name' => $name, 'excludeId' => $excludeId]);
        return $stmt->fetch();
    }

    public function deleteCategory($id) {
        return $this->delete($this->table, $id);
    }
}
