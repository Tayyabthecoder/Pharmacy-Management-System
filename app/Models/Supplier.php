<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class Supplier extends BaseModel {
    protected $table = 'suppliers';

    public function getAll() {
        return $this->all($this->table, 'name ASC');
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, contact_name, email, phone, address, status) 
                VALUES (:name, :contact_name, :email, :phone, :address, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public function update($id, $data) {
        $allowedFields = ['name', 'contact_name', 'email', 'phone', 'address', 'status'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteSupplier($id) {
        return $this->delete($this->table, $id);
    }
}
