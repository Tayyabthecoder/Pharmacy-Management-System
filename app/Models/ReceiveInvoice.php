<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class ReceiveInvoice extends BaseModel {
    protected $table = 'receive_invoices';

    public function getNextInvoiceNumber() {
        $stmt = $this->db->query("SELECT invoice_number FROM {$this->table} WHERE is_return = 0 AND invoice_number LIKE 'RI-%' ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();
        if (!$last) {
            return 'RI-00001';
        }
        preg_match('/\d+$/', $last, $matches);
        $num = isset($matches[0]) ? (int)$matches[0] : 0;
        return 'RI-' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (invoice_number, supplier_id, user_id, total_amount, discount, net_amount, reference_number, status, received_date, is_return) 
                VALUES (:invoice_number, :supplier_id, :user_id, :total_amount, :discount, :net_amount, :reference_number, :status, :received_date, :is_return)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_number' => $data['invoice_number'],
            'supplier_id' => $data['supplier_id'],
            'user_id' => $data['user_id'],
            'total_amount' => $data['total_amount'],
            'discount' => $data['discount'] ?? 0.00,
            'net_amount' => $data['net_amount'],
            'reference_number' => $data['reference_number'],
            'status' => $data['status'] ?? 'received',
            'received_date' => $data['received_date'],
            'is_return' => $data['is_return'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function updateInvoice($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET supplier_id = :supplier_id, 
                    total_amount = :total_amount, 
                    discount = :discount, 
                    net_amount = :net_amount, 
                    reference_number = :reference_number, 
                    status = :status, 
                    received_date = :received_date,
                    is_return = :is_return 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'supplier_id' => $data['supplier_id'],
            'total_amount' => $data['total_amount'],
            'discount' => $data['discount'] ?? 0.00,
            'net_amount' => $data['net_amount'],
            'reference_number' => $data['reference_number'],
            'status' => $data['status'] ?? 'received',
            'received_date' => $data['received_date'],
            'is_return' => $data['is_return'] ?? 0,
            'id' => $id
        ]);
    }

    public function getAllPaginated(int $page, int $perPage, array $filters = []) {
        $offset = ($page - 1) * $perPage;
        
        $whereClauses = [];
        $params = [];
        
        if (!empty($filters['supplier_id'])) {
            $whereClauses[] = "ri.supplier_id = :supplier_id";
            $params['supplier_id'] = $filters['supplier_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereClauses[] = "ri.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "ri.received_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "ri.received_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }
        
        $sqlCount = "SELECT COUNT(*) FROM {$this->table} ri {$whereSql}";
        $stmtCount = $this->db->prepare($sqlCount);
        // Bind parameters for count query
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $totalRecords = (int)$stmtCount->fetchColumn();
        
        $totalPages = (int)ceil($totalRecords / $perPage);
        $totalPages = max(1, $totalPages);
        
        $sqlData = "SELECT ri.*, s.name as supplier_name, u.name as user_name 
                    FROM {$this->table} ri 
                    LEFT JOIN suppliers s ON ri.supplier_id = s.id 
                    LEFT JOIN users u ON ri.user_id = u.id 
                    {$whereSql} 
                    ORDER BY ri.received_date DESC, ri.id DESC 
                    LIMIT :limit OFFSET :offset";
        
        $stmtData = $this->db->prepare($sqlData);
        // Bind parameters for data query
        foreach ($params as $key => $val) {
            $stmtData->bindValue($key, $val);
        }
        $stmtData->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtData->execute();
        
        return [
            'data' => $stmtData->fetchAll(),
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }

    public function getWithDetails(int $id) {
        $sql = "SELECT ri.*, s.name as supplier_name, s.phone as supplier_phone, s.email as supplier_email, s.address as supplier_address, u.name as user_name 
                FROM {$this->table} ri 
                LEFT JOIN suppliers s ON ri.supplier_id = s.id 
                LEFT JOIN users u ON ri.user_id = u.id 
                WHERE ri.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
