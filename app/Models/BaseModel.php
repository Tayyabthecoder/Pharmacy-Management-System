<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;

class BaseModel {
    protected $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo ?? ($GLOBALS['pdo'] ?? null);
    }

    public function getDb() {
        return $this->db;
    }

    /**
     * Sanitize table name to prevent SQL injection
     */
    protected function sanitizeTable(string $table): string {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name identifier.");
        }
        return $table;
    }

    /**
     * Sanitize order by clause to prevent SQL injection
     */
    protected function sanitizeOrderBy(string $orderBy): string {
        if (!preg_match('/^[a-zA-Z0-9_\.]+(\s+(ASC|DESC))?$/i', trim($orderBy))) {
            throw new Exception("Invalid order by clause.");
        }
        return $orderBy;
    }

    /**
     * Find a record by ID
     */
    public function find($table, $id) {
        $table = $this->sanitizeTable($table);
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all records from a table
     */
    public function all($table, $orderBy = 'created_at DESC') {
        $table = $this->sanitizeTable($table);
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $stmt = $this->db->query("SELECT * FROM {$table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    /**
     * Delete a record by ID
     */
    public function delete($table, $id) {
        $table = $this->sanitizeTable($table);
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count records in a table
     */
    public function count($table, $where = '', $params = []) {
        $table = $this->sanitizeTable($table);
        $sql = "SELECT COUNT(*) FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Paginate records from a table
     */
    public function paginate(string $table, int $page, int $perPage, string $where = '', array $params = [], string $orderBy = 'created_at DESC'): array {
        $table = $this->sanitizeTable($table);
        $orderBy = $this->sanitizeOrderBy($orderBy);
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        
        $sqlCount = "SELECT COUNT(*) FROM {$table}";
        if ($where) {
            $sqlCount .= " WHERE {$where}";
        }
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $totalRecords = (int)$stmtCount->fetchColumn();
        
        $totalPages = (int)ceil($totalRecords / $perPage);
        $totalPages = max(1, $totalPages);
        
        $sqlData = "SELECT * FROM {$table}";
        if ($where) {
            $sqlData .= " WHERE {$where}";
        }
        $sqlData .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
        
        $stmtData = $this->db->prepare($sqlData);
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

    // -----------------------------------------------------------------
    // Transaction Helpers
    // -----------------------------------------------------------------
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    public function commit(): bool {
        return $this->db->commit();
    }

    public function rollBack(): bool {
        return $this->db->rollBack();
    }

    public function inTransaction(): bool {
        return $this->db->inTransaction();
    }

    /**
     * Get the ID of the last inserted row.
     */
    public function lastInsertId(): string {
        return $this->db->lastInsertId();
    }
}
