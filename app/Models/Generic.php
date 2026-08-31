<?php

namespace App\Models;

use PDO;

class Generic extends BaseModel {
    protected $table = 'generics';

    public function getAll() {
        return $this->all($this->table, 'name ASC');
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
            SELECT g.*, COUNT(p.id) as product_count
            FROM {$this->table} g
            LEFT JOIN products p ON p.generic_id = g.id
            GROUP BY g.id
            ORDER BY g.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByNameExcludingId($name, $excludeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE LOWER(name) = LOWER(:name) AND id != :excludeId LIMIT 1");
        $stmt->execute(['name' => $name, 'excludeId' => $excludeId]);
        return $stmt->fetch();
    }

    public function deleteGeneric($id) {
        return $this->delete($this->table, $id);
    }
}
