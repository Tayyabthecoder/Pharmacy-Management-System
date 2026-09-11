<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;
use App\Support\QueryHelper;

class Product extends BaseModel {
    protected $table = 'products';

    public function getAllWithCategory(?int $page = null, ?int $perPage = null) {
        if ($page !== null && $perPage !== null) {
            $offset = ($page - 1) * $perPage;
            
            $totalRecords = (int)$this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
            $totalPages = (int)ceil($totalRecords / $perPage);
            $totalPages = max(1, $totalPages);
            
            $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                    FROM {$this->table} p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN generics g ON p.generic_id = g.id
                    LEFT JOIN companies comp ON p.company_id = comp.id
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
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

        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, generic_id, strength, batch_number, expiry_date, company_id, manufacturer, category_id, price, trad_price, cost_price, quantity, min_stock_level, is_prescription_required, barcode, image) 
                VALUES (:name, :generic_id, :strength, :batch_number, :expiry_date, :company_id, :manufacturer, :category_id, :price, :trad_price, :cost_price, :quantity, :min_stock_level, :is_prescription_required, :barcode, :image)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            'name'                     => $data['name'],
            'generic_id'               => !empty($data['generic_id']) ? (int)$data['generic_id'] : null,
            'strength'                 => $data['strength'] ?? null,
            'batch_number'             => $data['batch_number'] ?? null,
            'expiry_date'              => !empty($data['expiry_date']) ? $data['expiry_date'] : null,
            'company_id'               => !empty($data['company_id']) ? (int)$data['company_id'] : null,
            'manufacturer'             => $data['manufacturer'] ?? null,
            'category_id'              => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'price'                    => $data['price'],
            'trad_price'               => $data['trad_price'] ?? 0.00,
            'cost_price'               => $data['cost_price'] ?? 0.00,
            'quantity'                 => $data['quantity'] !== '' ? (int)$data['quantity'] : 0,
            'min_stock_level'          => $data['min_stock_level'] !== '' ? (int)$data['min_stock_level'] : 10,
            'is_prescription_required' => !empty($data['is_prescription_required']) ? 1 : 0,
            'barcode'                  => !empty($data['barcode']) ? trim($data['barcode']) : null,
            'image'                    => $data['image'] ?? ''
        ]);
        return $success ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $allowedFields = ['name', 'generic_id', 'strength', 'batch_number', 'expiry_date', 'company_id', 'manufacturer', 'category_id', 'price', 'trad_price', 'cost_price', 'quantity', 'min_stock_level', 'is_prescription_required', 'barcode', 'image'];
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

    public function findByBarcode(string $barcode) {
        $trimmed = trim($barcode);
        if ($trimmed === '') return null;
        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE p.barcode = :barcode OR p.id = :idInt LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'barcode' => $trimmed,
            'idInt'   => ctype_digit($trimmed) ? (int)$trimmed : -1
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteProduct($id) {
        return $this->delete($this->table, $id);
    }

    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = quantity - :quantity WHERE id = :id");
        return $stmt->execute(['quantity' => $quantity, 'id' => $id]);
    }

    /**
     * Atomically decrement stock only if available quantity is sufficient
     */
    public function updateStockAtomic(int $id, int $quantity): bool {
        if ($quantity <= 0) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity = quantity - :quantity WHERE id = :id AND quantity >= :quantity");
        $stmt->execute(['quantity' => $quantity, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getTotalInventoryValue() {
        return $this->db->query("SELECT SUM(price * quantity) FROM {$this->table}")->fetchColumn() ?: 0;
    }

    public function getTopSellingProducts(int $limit = 5) {
        $sql = "SELECT p.name, p.image, c.name as category_name, SUM(ii.quantity) as total_qty, SUM(ii.subtotal) as total_revenue
                FROM invoice_items ii
                JOIN {$this->table} p ON ii.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                GROUP BY ii.product_id, p.name, p.image, c.name
                ORDER BY total_qty DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLowStockProducts(int $minStock) {
        $sql = "SELECT p.name, p.quantity, p.price, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.quantity <= p.min_stock_level 
                ORDER BY p.quantity ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFilteredProducts(string $search = '', string $status = '', int $minStock = 10, ?int $page = null, ?int $perPage = null) {
        $sqlWhere = "1=1";
        $params = [];
        if ($search !== '') {
            $sqlWhere .= " AND p.name LIKE :search";
            $params['search'] = "%$search%";
        }

        if ($status === 'out_of_stock') {
            $sqlWhere .= " AND p.quantity = 0";
        } elseif ($status === 'low_stock') {
            $sqlWhere .= " AND p.quantity <= p.min_stock_level";
        } elseif ($status === 'in_stock') {
            $sqlWhere .= " AND p.quantity > p.min_stock_level";
        }

        if ($page !== null && $perPage !== null) {
            $offset = ($page - 1) * $perPage;
            
            $sqlCount = "SELECT COUNT(*) FROM {$this->table} p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN generics g ON p.generic_id = g.id WHERE {$sqlWhere}";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalRecords = (int)$stmtCount->fetchColumn();
            
            $totalPages = (int)ceil($totalRecords / $perPage);
            $totalPages = max(1, $totalPages);
            
            $sqlData = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                        FROM {$this->table} p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        LEFT JOIN generics g ON p.generic_id = g.id
                        LEFT JOIN companies comp ON p.company_id = comp.id
                        WHERE {$sqlWhere} 
                        ORDER BY p.name ASC 
                        LIMIT :limit OFFSET :offset";
            
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

        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE {$sqlWhere} 
                ORDER BY p.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSummaryStats(): array {
        $expiringCond = QueryHelper::dateFutureMonths('expiry_date', 6);
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) AS in_stock,
                    SUM(CASE WHEN quantity > 0 AND quantity < COALESCE(min_stock_level, 10) THEN 1 ELSE 0 END) AS low_stock,
                    SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date != '' AND {$expiringCond} THEN 1 ELSE 0 END) AS expiring
                FROM {$this->table}";
        $res = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

        return [
            'total'     => (int)($res['total'] ?? 0),
            'in_stock'  => (int)($res['in_stock'] ?? 0),
            'low_stock' => (int)($res['low_stock'] ?? 0),
            'expiring'  => (int)($res['expiring'] ?? 0),
        ];
    }

    // -----------------------------------------------------------------
    // Pharmacy Specific Expiry & Search Helpers
    // -----------------------------------------------------------------

    public function getExpiredProducts() {
        $today = QueryHelper::dateNow();
        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE p.expiry_date IS NOT NULL AND p.expiry_date <= {$today}
                ORDER BY p.expiry_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getNearExpiryProducts(int $days = 180) {
        $today = QueryHelper::dateNow();
        $futureDate = QueryHelper::dateFutureDays('p.expiry_date', $days);
        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE p.expiry_date IS NOT NULL 
                  AND p.expiry_date > {$today} 
                  AND {$futureDate}
                ORDER BY p.expiry_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchByGeneric(string $genericName) {
        $today = QueryHelper::dateNow();
        $sql = "SELECT p.*, c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE g.name LIKE :generic AND p.quantity > 0 AND (p.expiry_date IS NULL OR p.expiry_date > {$today})
                ORDER BY p.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['generic' => '%' . $genericName . '%']);
        return $stmt->fetchAll();
    }

    public function searchAutocomplete(string $query, int $limit = 8) {
        $sql = "SELECT p.id, p.name, p.strength, p.price, p.trad_price, p.cost_price, p.quantity, p.min_stock_level, p.is_prescription_required, p.barcode, p.image, 
                       c.name as category_name, g.name as generic_name, comp.name as company_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                WHERE (p.name LIKE :q OR g.name LIKE :q OR c.name LIKE :q OR comp.name LIKE :q OR p.barcode LIKE :q)
                ORDER BY 
                    CASE 
                        WHEN p.barcode = :exactQ THEN 0
                        WHEN LOWER(p.name) LIKE LOWER(:exactQ) THEN 1
                        WHEN LOWER(p.name) LIKE LOWER(:startQ) THEN 2
                        ELSE 3
                    END,
                    p.name ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':q', '%' . $query . '%');
        $stmt->bindValue(':exactQ', $query);
        $stmt->bindValue(':startQ', $query . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findDuplicatesAndRelated(string $name, int $excludeId = 0) {
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            return [
                'exists' => false,
                'exact_match' => false,
                'entered_name' => $trimmedName,
                'related' => []
            ];
        }

        // Check exact match (case-insensitive)
        $sqlExact = "SELECT p.id, p.name, p.strength, p.price, p.quantity, 
                            c.name as category_name, g.name as generic_name, comp.name as company_name 
                     FROM {$this->table} p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN generics g ON p.generic_id = g.id
                     LEFT JOIN companies comp ON p.company_id = comp.id
                     WHERE LOWER(TRIM(p.name)) = LOWER(TRIM(:exactName))";
        if ($excludeId > 0) {
            $sqlExact .= " AND p.id != :excludeId";
        }
        $stmtExact = $this->db->prepare($sqlExact);
        $stmtExact->bindValue(':exactName', $trimmedName);
        if ($excludeId > 0) {
            $stmtExact->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmtExact->execute();
        $exactMatches = $stmtExact->fetchAll(PDO::FETCH_ASSOC);
        $isExactMatch = !empty($exactMatches);

        // Check related/similar matches (by substring, first word, or generic)
        $words = preg_split('/\s+/', $trimmedName);
        $firstWord = !empty($words[0]) ? $words[0] : $trimmedName;

        $sqlRelated = "SELECT p.id, p.name, p.strength, p.price, p.quantity, 
                              c.name as category_name, g.name as generic_name, comp.name as company_name 
                       FROM {$this->table} p
                       LEFT JOIN categories c ON p.category_id = c.id
                       LEFT JOIN generics g ON p.generic_id = g.id
                       LEFT JOIN companies comp ON p.company_id = comp.id
                       WHERE (
                           LOWER(p.name) LIKE LOWER(:partial)
                           OR (LENGTH(:firstWord) >= 3 AND LOWER(p.name) LIKE LOWER(:firstWordPartial))
                           OR (g.name IS NOT NULL AND LOWER(g.name) LIKE LOWER(:partial))
                       )";
        if ($excludeId > 0) {
            $sqlRelated .= " AND p.id != :excludeId";
        }
        $sqlRelated .= " ORDER BY 
                            CASE 
                                WHEN LOWER(TRIM(p.name)) = LOWER(TRIM(:exactName)) THEN 1
                                WHEN LOWER(p.name) LIKE LOWER(:startPartial) THEN 2
                                ELSE 3 
                            END, p.name ASC 
                         LIMIT 15";

        $stmtRelated = $this->db->prepare($sqlRelated);
        $stmtRelated->bindValue(':partial', '%' . $trimmedName . '%');
        $stmtRelated->bindValue(':firstWord', $firstWord);
        $stmtRelated->bindValue(':firstWordPartial', '%' . $firstWord . '%');
        $stmtRelated->bindValue(':exactName', $trimmedName);
        $stmtRelated->bindValue(':startPartial', $trimmedName . '%');
        if ($excludeId > 0) {
            $stmtRelated->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmtRelated->execute();
        $relatedList = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);

        $exists = $isExactMatch || !empty($relatedList);

        return [
            'exists' => $exists,
            'exact_match' => $isExactMatch,
            'entered_name' => $trimmedName,
            'related' => $relatedList
        ];
    }
}
