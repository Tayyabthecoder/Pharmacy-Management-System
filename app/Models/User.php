<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class User extends BaseModel {
    protected $table = 'users';

    public function getAll() {
        return $this->all($this->table);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function create($data) {
        if (isset($data['password']) && !password_get_info($data['password'])['algo']) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql = "INSERT INTO {$this->table} (name, username, email, password, role, status) VALUES (:name, :username, :email, :password, :role, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'salesman',
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public function update($id, $data) {
        if (array_key_exists('password', $data)) {
            if (empty($data['password'])) {
                unset($data['password']);
            } else if (!password_get_info($data['password'])['algo']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }

        $allowedFields = ['name', 'username', 'email', 'password', 'role', 'status', 'phone', 'bio', 'address', 'user_image', 'profile_image'];
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

    public function deleteUser($id) {
        return $this->delete($this->table, $id);
    }
}
