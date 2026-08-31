<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class Invoice extends BaseModel {
    protected $table = 'invoices';

    public function getAllWithSalesman(?int $page = null, ?int $perPage = null, string $search = '', string $dateFilter = '') {
        $searchSql = " WHERE 1=1 ";
        $params = [];
        if (!empty($search)) {
            $searchSql .= " AND (i.id LIKE :search1 OR i.customer_name LIKE :search2 OR u.name LIKE :search3) ";
            $params[':search1'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
        }
        if (!empty($dateFilter)) {
            if ($dateFilter === 'today') {
                $searchSql .= " AND date(i.created_at) = date('now') ";
            } elseif ($dateFilter === 'week') {
                $searchSql .= " AND i.created_at >= date('now', '-7 days') ";
            } elseif ($dateFilter === 'month') {
                $searchSql .= " AND strftime('%m', i.created_at) = strftime('%m', 'now') AND strftime('%Y', i.created_at) = strftime('%Y', 'now') ";
            }
        }

        if ($page !== null && $perPage !== null) {
            $offset = ($page - 1) * $perPage;
            
            $countSql = "SELECT COUNT(*) FROM {$this->table} i LEFT JOIN users u ON i.user_id = u.id" . $searchSql;
            $stmtCount = $this->db->prepare($countSql);
            foreach ($params as $k => $v) {
                $stmtCount->bindValue($k, $v);
            }
            $stmtCount->execute();
            $totalRecords = (int)$stmtCount->fetchColumn();
            
            $totalPages = (int)ceil($totalRecords / $perPage);
            $totalPages = max(1, $totalPages);
            
            $sql = "SELECT i.*, u.name as salesman 
                    FROM {$this->table} i 
                    LEFT JOIN users u ON i.user_id = u.id 
                    $searchSql 
                    ORDER BY i.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'data' => $stmt->fetchAll(),
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'per_page' => $perPage
            ];
        }

        $sql = "SELECT i.*, u.name as salesman 
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                $searchSql 
                ORDER BY i.created_at DESC";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, customer_name, total_amount, doctor_name, doctor_license, is_return) 
                VALUES (:user_id, :customer_name, :total_amount, :doctor_name, :doctor_license, :is_return)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'customer_name' => $data['customer_name'],
            'total_amount' => $data['total_amount'],
            'doctor_name' => $data['doctor_name'] ?? null,
            'doctor_license' => $data['doctor_license'] ?? null,
            'is_return' => $data['is_return'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function updateInvoice($id, $data) {
        $sql = "UPDATE {$this->table} SET customer_name = :cname, total_amount = :total, doctor_name = :dname, doctor_license = :dlicense, is_return = :is_return WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'cname' => $data['customer_name'],
            'total' => $data['total_amount'],
            'dname' => $data['doctor_name'] ?? null,
            'dlicense' => $data['doctor_license'] ?? null,
            'is_return' => $data['is_return'] ?? 0,
            'id' => $id
        ]);
    }

    public function getTotalRevenue(?int $userId = null) {
        if ($userId !== null) {
            $stmt = $this->db->prepare("SELECT SUM(total_amount) FROM {$this->table} WHERE user_id = :uid");
            $stmt->execute(['uid' => $userId]);
            return $stmt->fetchColumn() ?: 0;
        }
        return $this->db->query("SELECT SUM(total_amount) FROM {$this->table}")->fetchColumn() ?: 0;
    }

    public function getInvoiceWithDetails(int $id, ?int $userId = null) {
        $sql = "SELECT i.*, u.name as salesman_name, u.email as salesman_email 
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                WHERE i.id = :id";
        $params = ['id' => $id];
        if ($userId !== null) {
            $sql .= " AND i.user_id = :uid";
            $params['uid'] = $userId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getMonthlySales(int $months = 6) {
        $sql = "SELECT strftime('%Y-%m', created_at) as month, SUM(total_amount) as revenue, COUNT(id) as count 
                FROM {$this->table} 
                WHERE created_at >= date('now', :months_str)
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':months_str', '-' . $months . ' months', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, ?int $page = null, ?int $perPage = null) {
        $sqlWhere = "user_id = :uid";
        $params = ['uid' => $userId];

        if ($page !== null && $perPage !== null) {
            return $this->paginate($this->table, $page, $perPage, $sqlWhere, $params, 'created_at DESC');
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$sqlWhere} ORDER BY created_at DESC";
        if ($page !== null) {
            $sql .= " LIMIT :limit";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        if ($page !== null) {
            $stmt->bindValue(':limit', $page, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTodayRevenue() {
        $stmt = $this->db->query("SELECT SUM(total_amount) FROM {$this->table} WHERE date(created_at) = date('now')");
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getTodayCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE date(created_at) = date('now')");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTodayRevenueByUser(int $userId) {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) FROM {$this->table} WHERE user_id = :uid AND date(created_at) = date('now')");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getTodayCountByUser(int $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid AND date(created_at) = date('now')");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyRevenueByUser(int $userId) {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) FROM {$this->table} WHERE user_id = :uid AND strftime('%m', created_at) = strftime('%m', 'now') AND strftime('%Y', created_at) = strftime('%Y', 'now')");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getMonthlyCountByUser(int $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid AND strftime('%m', created_at) = strftime('%m', 'now') AND strftime('%Y', created_at) = strftime('%Y', 'now')");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyQuantityByUser(int $userId) {
        $sql = "SELECT SUM(ii.quantity) 
                FROM invoice_items ii 
                JOIN invoices i ON ii.invoice_id = i.id 
                WHERE i.user_id = :uid 
                AND strftime('%m', i.created_at) = strftime('%m', 'now') 
                AND strftime('%Y', i.created_at) = strftime('%Y', 'now')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlySalesByUser(int $userId, int $months = 6) {
        $sql = "SELECT strftime('%Y-%m', created_at) as month, SUM(total_amount) as revenue, COUNT(id) as count 
                FROM {$this->table} 
                WHERE user_id = :uid AND created_at >= date('now', :months_str)
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':months_str', '-' . $months . ' months', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
