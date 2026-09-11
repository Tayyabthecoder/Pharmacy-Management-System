<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;
use App\Support\QueryHelper;

class Invoice extends BaseModel {
    protected $table = 'invoices';

    public function generateInvoiceNumber(string $prefix = 'INV-'): string {
        $stmt = $this->db->query("SELECT MAX(id) FROM {$this->table}");
        $nextId = ((int)$stmt->fetchColumn()) + 1;
        return $prefix . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);
    }

    public function getAllWithSalesman(?int $page = null, ?int $perPage = null, string $search = '', string $dateFilter = '') {
        $searchSql = " WHERE 1=1 ";
        $params = [];
        if (!empty($search)) {
            $searchSql .= " AND (i.id LIKE :search1 OR i.invoice_number LIKE :search2 OR i.customer_name LIKE :search3 OR u.name LIKE :search4) ";
            $params[':search1'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
            $params[':search4'] = "%{$search}%";
        }
        if (!empty($dateFilter)) {
            if ($dateFilter === 'today') {
                $searchSql .= " AND " . QueryHelper::isToday('i.created_at') . " ";
            } elseif ($dateFilter === 'week') {
                $searchSql .= " AND " . QueryHelper::datePastDays('i.created_at', 7) . " ";
            } elseif ($dateFilter === 'month') {
                $searchSql .= " AND " . QueryHelper::isCurrentMonth('i.created_at') . " ";
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
        $invoiceNumber = !empty($data['invoice_number']) ? $data['invoice_number'] : $this->generateInvoiceNumber(!empty($data['is_return']) ? 'RET-' : 'INV-');
        $sql = "INSERT INTO {$this->table} (invoice_number, user_id, customer_name, total_amount, doctor_name, doctor_license, is_return, original_invoice_id) 
                VALUES (:invoice_number, :user_id, :customer_name, :total_amount, :doctor_name, :doctor_license, :is_return, :original_invoice_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'invoice_number' => $invoiceNumber,
            'user_id' => $data['user_id'],
            'customer_name' => $data['customer_name'],
            'total_amount' => $data['total_amount'],
            'doctor_name' => $data['doctor_name'] ?? null,
            'doctor_license' => $data['doctor_license'] ?? null,
            'is_return' => $data['is_return'] ?? 0,
            'original_invoice_id' => $data['original_invoice_id'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function updateInvoice($id, $data) {
        $sql = "UPDATE {$this->table} SET customer_name = :cname, total_amount = :total, doctor_name = :dname, doctor_license = :dlicense, is_return = :is_return, original_invoice_id = :orig_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'cname' => $data['customer_name'],
            'total' => $data['total_amount'],
            'dname' => $data['doctor_name'] ?? null,
            'dlicense' => $data['doctor_license'] ?? null,
            'is_return' => $data['is_return'] ?? 0,
            'orig_id' => $data['original_invoice_id'] ?? null,
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

    public function getByInvoiceNumber(string $invoiceNumber, ?int $userId = null) {
        $sql = "SELECT i.*, u.name as salesman_name, u.email as salesman_email 
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                WHERE i.invoice_number = :invoice_number";
        $params = ['invoice_number' => $invoiceNumber];
        if ($userId !== null) {
            $sql .= " AND i.user_id = :uid";
            $params['uid'] = $userId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getByIdOrNumber(string $query, ?int $userId = null) {
        $trimmed = trim($query);
        if (is_numeric($trimmed)) {
            $res = $this->getInvoiceWithDetails((int)$trimmed, $userId);
            if ($res) return $res;
        }
        return $this->getByInvoiceNumber($trimmed, $userId);
    }

    public function getItemsByInvoiceId(int $invoiceId) {
        $itemModel = new InvoiceItem();
        return $itemModel->getByInvoiceWithProducts($invoiceId);
    }

    public function getMonthlySales(int $months = 6) {
        $monthFmt = QueryHelper::dateFormat('created_at', '%Y-%m');
        $dateCond = QueryHelper::datePastMonths('created_at', $months);
        $sql = "SELECT {$monthFmt} as month, SUM(total_amount) as revenue, COUNT(id) as count 
                FROM {$this->table} 
                WHERE {$dateCond}
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
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
        $todayCond = QueryHelper::isToday('created_at');
        $stmt = $this->db->query("SELECT SUM(total_amount) FROM {$this->table} WHERE {$todayCond}");
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getTodayCount() {
        $todayCond = QueryHelper::isToday('created_at');
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE {$todayCond}");
        return $stmt->fetchColumn() ?: 0;
    }

    public function getTodayRevenueByUser(int $userId) {
        $todayCond = QueryHelper::isToday('created_at');
        $stmt = $this->db->prepare("SELECT SUM(total_amount) FROM {$this->table} WHERE user_id = :uid AND {$todayCond}");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getTodayCountByUser(int $userId) {
        $todayCond = QueryHelper::isToday('created_at');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid AND {$todayCond}");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyRevenueByUser(int $userId) {
        $curMonthCond = QueryHelper::isCurrentMonth('created_at');
        $stmt = $this->db->prepare("SELECT SUM(total_amount) FROM {$this->table} WHERE user_id = :uid AND {$curMonthCond}");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0.00;
    }

    public function getMonthlyCountByUser(int $userId) {
        $curMonthCond = QueryHelper::isCurrentMonth('created_at');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :uid AND {$curMonthCond}");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlyQuantityByUser(int $userId) {
        $curMonthCond = QueryHelper::isCurrentMonth('i.created_at');
        $sql = "SELECT SUM(ii.quantity) 
                FROM invoice_items ii 
                JOIN invoices i ON ii.invoice_id = i.id 
                WHERE i.user_id = :uid 
                AND {$curMonthCond}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function getMonthlySalesByUser(int $userId, int $months = 6) {
        $monthFmt = QueryHelper::dateFormat('created_at', '%Y-%m');
        $dateCond = QueryHelper::datePastMonths('created_at', $months);
        $sql = "SELECT {$monthFmt} as month, SUM(total_amount) as revenue, COUNT(id) as count 
                FROM {$this->table} 
                WHERE user_id = :uid AND {$dateCond}
                GROUP BY month 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get top-performing salesmen ranked by total revenue and sales count.
     *
     * @param int $days Number of past days to query (0 for all-time)
     * @param int $limit Max records to return
     * @return array
     */
    public function getSalesmanLeaderboard(int $days = 7, int $limit = 5): array {
        $where = "WHERE (i.is_return = 0 OR i.is_return IS NULL)";

        if ($days > 0) {
            $where .= " AND " . QueryHelper::datePastDays('i.created_at', $days);
        }

        $sql = "SELECT 
                    u.id as user_id,
                    u.name as salesman_name,
                    u.email as salesman_email,
                    u.role as user_role,
                    COUNT(i.id) as invoice_count,
                    COALESCE(SUM(i.total_amount), 0) as total_revenue,
                    COALESCE(AVG(i.total_amount), 0) as avg_order_value
                FROM {$this->table} i
                JOIN users u ON i.user_id = u.id
                {$where}
                GROUP BY u.id, u.name, u.email, u.role
                ORDER BY total_revenue DESC, invoice_count DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top-selling products by quantity and revenue within the specified timeframe.
     *
     * @param int $days Number of past days to query (0 for all-time)
     * @param int $limit Max records to return
     * @return array
     */
    public function getTopSellingProductsStats(int $days = 7, int $limit = 5): array {
        $where = "WHERE (i.is_return = 0 OR i.is_return IS NULL)";

        if ($days > 0) {
            $where .= " AND " . QueryHelper::datePastDays('i.created_at', $days);
        }

        $sql = "SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    p.barcode as product_barcode,
                    p.strength as product_strength,
                    p.quantity as current_stock,
                    c.name as category_name,
                    COALESCE(SUM(ii.quantity), 0) as units_sold,
                    COALESCE(SUM(ii.subtotal), 0) as total_revenue
                FROM invoice_items ii
                JOIN {$this->table} i ON ii.invoice_id = i.id
                JOIN products p ON ii.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                {$where}
                GROUP BY p.id, p.name, p.barcode, p.strength, p.quantity, c.name
                ORDER BY units_sold DESC, total_revenue DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
