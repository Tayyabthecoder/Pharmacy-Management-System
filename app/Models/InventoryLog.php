<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;
use App\Support\QueryHelper;

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
        $todayCond = QueryHelper::isToday('created_at');
        $sql = "SELECT type, COUNT(*) as count FROM {$this->table} WHERE {$todayCond} GROUP BY type";
        return $this->db->query($sql)->fetchAll();
    }

    public function getFilteredLogs(int $page, int $perPage, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $whereClauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $whereClauses[] = "(p.name LIKE :search OR l.remarks LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['type'])) {
            $whereClauses[] = "l.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['user_id'])) {
            $whereClauses[] = "l.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($filters['start_date'])) {
            $whereClauses[] = "date(l.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $whereClauses[] = "date(l.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        }

        $sqlCount = "SELECT COUNT(*) FROM {$this->table} l LEFT JOIN products p ON l.product_id = p.id {$whereSql}";
        $stmtCount = $this->db->prepare($sqlCount);
        foreach ($params as $key => $val) {
            $stmtCount->bindValue($key, $val);
        }
        $stmtCount->execute();
        $totalRecords = (int)$stmtCount->fetchColumn();

        $totalPages = (int)ceil($totalRecords / $perPage);
        $totalPages = max(1, $totalPages);

        $sqlData = "SELECT l.*, p.name as product_name, u.name as user_name, c.name as category_name 
                    FROM {$this->table} l 
                    LEFT JOIN products p ON l.product_id = p.id 
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON l.user_id = u.id 
                    {$whereSql} 
                    ORDER BY l.created_at DESC, l.id DESC 
                    LIMIT :limit OFFSET :offset";

        $stmtData = $this->db->prepare($sqlData);
        foreach ($params as $key => $val) {
            $stmtData->bindValue($key, $val);
        }
        $stmtData->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtData->execute();

        return [
            'data'          => $stmtData->fetchAll(PDO::FETCH_ASSOC),
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'current_page'  => $page,
            'per_page'      => $perPage
        ];
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (product_id, user_id, qty_change, type, remarks) 
                VALUES (:pid, :uid, :change, :type, :remarks)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'pid'     => $data['product_id'],
            'uid'     => $data['user_id'],
            'change'  => $data['qty_change'],
            'type'    => $data['type'],
            'remarks' => $data['remarks']
        ]);
    }
}
