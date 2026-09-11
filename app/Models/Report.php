<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;
use App\Support\QueryHelper;

class Report extends BaseModel {
    protected $table = 'invoices';

    /**
     * Helper to build date WHERE condition.
     */
    public function getDateWhere(string $field, string $start, string $end): string {
        if ($start && $end) return "WHERE DATE($field) BETWEEN :start AND :end";
        if ($start) return "WHERE DATE($field) >= :start";
        if ($end) return "WHERE DATE($field) <= :end";
        return "";
    }

    /**
     * Helper to build date parameters array.
     */
    public function getDateParams(string $start, string $end): array {
        $p = [];
        if ($start) $p['start'] = $start;
        if ($end) $p['end'] = $end;
        return $p;
    }

    /**
     * Build invoice WHERE conditions and bound parameters from filters.
     */
    public function buildInvoiceFilters(array $filters): array {
        $where = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $where[] = "i.created_at >= :start_date";
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $where[] = "i.created_at <= :end_date";
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['salesman_id']) && $filters['salesman_id'] !== 'all') {
            $where[] = "i.user_id = :salesman_id";
            $params['salesman_id'] = (int)$filters['salesman_id'];
        }

        $catWhere = $where;
        $catParams = $params;
        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $catWhere[] = "p.category_id = :category_id";
            $catParams['category_id'] = (int)$filters['category_id'];
        }

        return [
            'invoiceWhere' => $where,
            'invoiceParams' => $params,
            'catWhere' => $catWhere,
            'catParams' => $catParams,
        ];
    }

    /**
     * Calculate core Analytics Dashboard KPIs (Revenue, Profit, Invoices, Customers, Margin, Valuation, Out of Stock)
     */
    public function getAnalyticsSummary(array $filters): array {
        $f = $this->buildInvoiceFilters($filters);
        $categoryId = $filters['category_id'] ?? 'all';

        $totalRevenue = 0.0;
        $totalProfit = 0.0;
        $totalInvoices = 0;
        $totalCustomers = 0;

        if ($categoryId !== 'all') {
            $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : "";

            $revQuery = "SELECT SUM(ii.subtotal) as revenue, 
                                SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit,
                                COUNT(DISTINCT i.id) as invoice_count,
                                COUNT(DISTINCT i.customer_name) as customer_count
                         FROM invoices i 
                         JOIN invoice_items ii ON i.id = ii.invoice_id
                         JOIN products p ON ii.product_id = p.id
                         $whereStr";
            $stmt = $this->db->prepare($revQuery);
            $stmt->execute($f['catParams']);
            $revResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalRevenue = (float)($revResult['revenue'] ?? 0.0);
            $totalProfit = (float)($revResult['profit'] ?? 0.0);
            $totalInvoices = (int)($revResult['invoice_count'] ?? 0);
            $totalCustomers = (int)($revResult['customer_count'] ?? 0);
        } else {
            $whereStr = count($f['invoiceWhere']) > 0 ? "WHERE " . implode(" AND ", $f['invoiceWhere']) : "";

            $revQuery = "SELECT SUM(ii.subtotal) as revenue, 
                                SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit,
                                COUNT(DISTINCT i.id) as invoice_count,
                                COUNT(DISTINCT i.customer_name) as customer_count
                         FROM invoices i 
                         LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                         LEFT JOIN products p ON ii.product_id = p.id
                         $whereStr";
            $stmt = $this->db->prepare($revQuery);
            $stmt->execute($f['invoiceParams']);
            $revResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalRevenue = (float)($revResult['revenue'] ?? 0.0);
            $totalProfit = (float)($revResult['profit'] ?? 0.0);
            $totalInvoices = (int)($revResult['invoice_count'] ?? 0);
            $totalCustomers = (int)($revResult['customer_count'] ?? 0);
        }

        $averageOrderValue = $totalInvoices > 0 ? ($totalRevenue / $totalInvoices) : 0.0;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0.0;

        // Inventory Valuation & Out of Stock
        if ($categoryId !== 'all') {
            $stmtVal = $this->db->prepare("SELECT SUM(price * quantity) FROM products WHERE category_id = :category_id");
            $stmtVal->execute(['category_id' => (int)$categoryId]);
            $totalInventoryValue = (float)($stmtVal->fetchColumn() ?: 0.0);

            $stmtOut = $this->db->prepare("SELECT COUNT(*) FROM products WHERE quantity = 0 AND category_id = :category_id");
            $stmtOut->execute(['category_id' => (int)$categoryId]);
            $outOfStockCount = (int)($stmtOut->fetchColumn() ?: 0);
        } else {
            $totalInventoryValue = (float)($this->db->query("SELECT SUM(price * quantity) FROM products")->fetchColumn() ?: 0.0);
            $outOfStockCount = (int)($this->db->query("SELECT COUNT(*) FROM products WHERE quantity = 0")->fetchColumn() ?: 0);
        }

        return [
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalProfit,
            'profitMargin' => $profitMargin,
            'totalInventoryValue' => $totalInventoryValue,
            'totalCustomers' => $totalCustomers,
            'totalInvoices' => $totalInvoices,
            'averageOrderValue' => $averageOrderValue,
            'outOfStockCount' => $outOfStockCount,
        ];
    }

    /**
     * Get Sales Trend over time.
     */
    public function getSalesTrend(array $filters, bool $isDaily = false): array {
        $f = $this->buildInvoiceFilters($filters);
        $categoryId = $filters['category_id'] ?? 'all';
        $timeFormat = $isDaily ? '%Y-%m-%d' : '%Y-%m';
        $groupBy = QueryHelper::dateFormat('i.created_at', $timeFormat);

        if ($categoryId !== 'all') {
            $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : "";
            $trendQuery = "SELECT $groupBy as time_label, SUM(ii.subtotal) as revenue, 
                                  SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit, 
                                  COUNT(DISTINCT i.id) as count
                           FROM invoices i
                           JOIN invoice_items ii ON i.id = ii.invoice_id
                           JOIN products p ON ii.product_id = p.id
                           $whereStr
                           GROUP BY time_label
                           ORDER BY time_label ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute($f['catParams']);
        } else {
            $whereStr = count($f['invoiceWhere']) > 0 ? "WHERE " . implode(" AND ", $f['invoiceWhere']) : "";
            $trendQuery = "SELECT $groupBy as time_label, SUM(ii.subtotal) as revenue, 
                                  SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit, 
                                  COUNT(DISTINCT i.id) as count
                           FROM invoices i
                           LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                           LEFT JOIN products p ON ii.product_id = p.id
                           $whereStr
                           GROUP BY time_label
                           ORDER BY time_label ASC";
            $stmt = $this->db->prepare($trendQuery);
            $stmt->execute($f['invoiceParams']);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top selling products based on filtered sales.
     */
    public function getTopSellingProducts(array $filters, ?int $limit = null): array {
        $f = $this->buildInvoiceFilters($filters);
        $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : "";

        $topProdQuery = "SELECT p.id, p.name, p.image, c.name as category_name, 
                                SUM(ii.quantity) as total_qty, SUM(ii.subtotal) as total_revenue
                         FROM invoice_items ii
                         JOIN products p ON ii.product_id = p.id
                         LEFT JOIN categories c ON p.category_id = c.id
                         JOIN invoices i ON ii.invoice_id = i.id
                         $whereStr
                         GROUP BY p.id, p.name, p.image, c.name
                         ORDER BY total_qty DESC";
        if ($limit !== null) {
            $topProdQuery .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->db->prepare($topProdQuery);
        $stmt->execute($f['catParams']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Low stock products with optional category filter.
     */
    public function getLowStockProducts($categoryId = 'all', ?int $limit = null): array {
        $sql = "SELECT p.id, p.name, p.strength, p.quantity, p.price, p.min_stock_level, 
                       c.name as category_name, g.name as generic_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN generics g ON p.generic_id = g.id
                WHERE p.quantity <= COALESCE(p.min_stock_level, 10)";
        $params = [];
        if ($categoryId !== 'all' && !empty($categoryId)) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = (int)$categoryId;
        }
        $sql .= " ORDER BY p.quantity ASC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sales grouped by category for breakdown charts.
     */
    public function getSalesByCategory(array $filters): array {
        $f = $this->buildInvoiceFilters($filters);
        $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : "";

        $catChartQuery = "SELECT c.name as category_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                          FROM invoice_items ii
                          JOIN products p ON ii.product_id = p.id
                          JOIN categories c ON p.category_id = c.id
                          JOIN invoices i ON ii.invoice_id = i.id
                          $whereStr
                          GROUP BY c.id, c.name
                          ORDER BY total_revenue DESC";
        $stmt = $this->db->prepare($catChartQuery);
        $stmt->execute($f['catParams']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Salesman performance list based on report filters.
     */
    public function getSalesmanPerformance(array $filters): array {
        $f = $this->buildInvoiceFilters($filters);
        $categoryId = $filters['category_id'] ?? 'all';

        if ($categoryId !== 'all') {
            $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : "";
            $sql = "SELECT u.name as salesman_name, SUM(ii.subtotal) as total_revenue, COUNT(DISTINCT i.id) as invoice_count
                    FROM invoices i
                    JOIN users u ON i.user_id = u.id
                    JOIN invoice_items ii ON i.id = ii.invoice_id
                    JOIN products p ON ii.product_id = p.id
                    $whereStr
                    GROUP BY u.id, u.name
                    ORDER BY total_revenue DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($f['catParams']);
        } else {
            $whereStr = count($f['invoiceWhere']) > 0 ? "WHERE " . implode(" AND ", $f['invoiceWhere']) : "";
            $sql = "SELECT u.name as salesman_name, SUM(i.total_amount) as total_revenue, COUNT(i.id) as invoice_count
                    FROM invoices i
                    JOIN users u ON i.user_id = u.id
                    $whereStr
                    GROUP BY u.id, u.name
                    ORDER BY total_revenue DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($f['invoiceParams']);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sales grouped by Generic formula.
     */
    public function getSalesByGeneric(array $filters, int $limit = 10): array {
        $f = $this->buildInvoiceFilters($filters);
        $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : (count($f['invoiceWhere']) > 0 ? "WHERE " . implode(" AND ", $f['invoiceWhere']) : "");

        $genericQuery = "SELECT g.name as generic_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                         FROM invoice_items ii
                         JOIN products p ON ii.product_id = p.id
                         LEFT JOIN generics g ON p.generic_id = g.id
                         JOIN invoices i ON ii.invoice_id = i.id
                         $whereStr
                         GROUP BY g.id, g.name
                         ORDER BY total_revenue DESC LIMIT :limit";
        $stmt = $this->db->prepare($genericQuery);
        $params = ($filters['category_id'] ?? 'all') !== 'all' ? $f['catParams'] : $f['invoiceParams'];
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sales grouped by Manufacturer / Company.
     */
    public function getSalesByCompany(array $filters, int $limit = 10): array {
        $f = $this->buildInvoiceFilters($filters);
        $whereStr = count($f['catWhere']) > 0 ? "WHERE " . implode(" AND ", $f['catWhere']) : (count($f['invoiceWhere']) > 0 ? "WHERE " . implode(" AND ", $f['invoiceWhere']) : "");

        $companyQuery = "SELECT comp.name as company_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                         FROM invoice_items ii
                         JOIN products p ON ii.product_id = p.id
                         LEFT JOIN companies comp ON p.company_id = comp.id
                         JOIN invoices i ON ii.invoice_id = i.id
                         $whereStr
                         GROUP BY comp.id, comp.name
                         ORDER BY total_revenue DESC LIMIT :limit";
        $stmt = $this->db->prepare($companyQuery);
        $params = ($filters['category_id'] ?? 'all') !== 'all' ? $f['catParams'] : $f['invoiceParams'];
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Detailed Medicine Report Generators.
     */
    public function getMedicineReport(string $type, $categoryId = 'all', $companyId = 'all'): array {
        $curr = currency_symbol();
        $where = [];
        if ($categoryId !== 'all' && !empty($categoryId)) $where[] = "p.category_id = " . (int)$categoryId;
        if ($companyId !== 'all' && !empty($companyId)) $where[] = "p.company_id = " . (int)$companyId;
        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        if ($type === 'detailed_info') {
            $stmt = $this->db->query("SELECT p.name as 'Medicine', g.name as 'Generic', comp.name as 'Manufacturer', c.name as 'Category', p.batch_number as 'Batch', p.expiry_date as 'Expiry', p.min_stock_level as 'Min Stock' FROM products p LEFT JOIN generics g ON p.generic_id = g.id LEFT JOIN companies comp ON p.company_id = comp.id LEFT JOIN categories c ON p.category_id = c.id $whereSql ORDER BY p.name ASC");
            return ['title' => 'Detailed Medicine Information', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } elseif ($type === 'company_wise') {
            $stmt = $this->db->query("SELECT comp.name as group_name, p.name as medicine, g.name as generic, c.name as category, p.batch_number as batch, p.expiry_date as expiry, p.quantity as stock, p.cost_price as cost, p.price as price FROM products p JOIN companies comp ON p.company_id = comp.id LEFT JOIN generics g ON p.generic_id = g.id LEFT JOIN categories c ON p.category_id = c.id $whereSql ORDER BY comp.name ASC, p.name ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $groups = [];
            foreach ($rows as $row) {
                $gName = $row['group_name'] ?: 'Unassigned Company';
                if (!isset($groups[$gName])) {
                    $groups[$gName] = [
                        'group_title' => 'Company: ' . $gName,
                        'group_name'  => $gName,
                        'items'       => []
                    ];
                }
                $groups[$gName]['items'][] = [
                    'Medicine'      => $row['medicine'],
                    'Generic'       => $row['generic'] ?: 'N/A',
                    'Category'      => $row['category'] ?: 'N/A',
                    'Batch'         => $row['batch'] ?: 'N/A',
                    'Expiry'        => $row['expiry'] ?: 'N/A',
                    'Stock Qty'     => (int)$row['stock'],
                    'Cost Price'    => (float)$row['cost'],
                    'Retail Price'  => (float)$row['price'],
                    'Total Cost Value' => round((float)$row['stock'] * (float)$row['cost'], 2),
                    'Total Retail Value' => round((float)$row['stock'] * (float)$row['price'], 2)
                ];
            }
            return [
                'title'    => 'Company-wise Medicine Report',
                'data'     => $groups,
                'is_list'  => true,
                'group_by' => 'Company'
            ];
        } elseif ($type === 'category_wise') {
            $stmt = $this->db->query("SELECT c.name as group_name, p.name as medicine, g.name as generic, comp.name as company, p.batch_number as batch, p.expiry_date as expiry, p.quantity as stock, p.cost_price as cost, p.price as price FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN generics g ON p.generic_id = g.id LEFT JOIN companies comp ON p.company_id = comp.id $whereSql ORDER BY c.name ASC, p.name ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $groups = [];
            foreach ($rows as $row) {
                $gName = $row['group_name'] ?: 'General Category';
                if (!isset($groups[$gName])) {
                    $groups[$gName] = [
                        'group_title' => 'Category: ' . $gName,
                        'group_name'  => $gName,
                        'items'       => []
                    ];
                }
                $groups[$gName]['items'][] = [
                    'Medicine'      => $row['medicine'],
                    'Generic'       => $row['generic'] ?: 'N/A',
                    'Company'       => $row['company'] ?: 'N/A',
                    'Batch'         => $row['batch'] ?: 'N/A',
                    'Expiry'        => $row['expiry'] ?: 'N/A',
                    'Stock Qty'     => (int)$row['stock'],
                    'Cost Price'    => (float)$row['cost'],
                    'Retail Price'  => (float)$row['price'],
                    'Total Cost Value' => round((float)$row['stock'] * (float)$row['cost'], 2),
                    'Total Retail Value' => round((float)$row['stock'] * (float)$row['price'], 2)
                ];
            }
            return [
                'title'    => 'Category-wise Medicine Report',
                'data'     => $groups,
                'is_list'  => true,
                'group_by' => 'Category'
            ];
        } elseif ($type === 'rate_list') {
            $stmt = $this->db->query("SELECT p.name as 'Medicine', p.strength as 'Strength', p.cost_price as 'Cost Price ({$curr})', p.trad_price as 'Trade Price ({$curr})', p.price as 'Retail Price ({$curr})', (p.price - p.cost_price) as 'Profit per Unit ({$curr})' FROM products p $whereSql ORDER BY p.name ASC");
            return ['title' => 'Up-to-date Medicine Rate List', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }
        return ['title' => 'Medicine Report', 'data' => []];
    }

    /**
     * Receive / Intake Reports.
     */
    public function getReceiveReport(string $type, string $start, string $end, $companyId = 'all'): array {
        $curr = currency_symbol();
        $where = $this->getDateWhere('ri.created_at', $start, $end);
        
        if ($type === 'complete_register') {
            $sql = "SELECT ri.invoice_number as 'Invoice #', DATE(ri.created_at) as 'Date', s.name as 'Supplier', ri.total_amount as 'Total Amount ({$curr})', ri.status as 'Status' 
                    FROM receive_invoices ri 
                    LEFT JOIN suppliers s ON ri.supplier_id = s.id 
                    $where ORDER BY ri.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->getDateParams($start, $end));
            return ['title' => 'Complete Receive Register', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } elseif ($type === 'received_return') {
            $where2 = $this->getDateWhere('il.created_at', $start, $end);
            $where2 = $where2 ? $where2 . " AND il.type = 'return'" : "WHERE il.type = 'return'";
            $sql = "SELECT DATE(il.created_at) as 'Date', p.name as 'Product', u.name as 'User', il.qty_change as 'Qty Change', il.remarks as 'Remarks' 
                    FROM inventory_logs il 
                    LEFT JOIN products p ON il.product_id = p.id 
                    LEFT JOIN users u ON il.user_id = u.id 
                    $where2 ORDER BY il.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->getDateParams($start, $end));
            return ['title' => 'Detailed Received Return Register', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }
        return ['title' => 'Receive Report', 'data' => []];
    }

    /**
     * Sales Reports.
     */
    public function getSaleReport(string $type, string $start, string $end, $salesmanId = 'all', $categoryId = 'all', $companyId = 'all'): array {
        $curr = currency_symbol();
        $conds = [];
        $params = [];
        if ($start) { $conds[] = "DATE(i.created_at) >= :start"; $params['start'] = $start; }
        if ($end) { $conds[] = "DATE(i.created_at) <= :end"; $params['end'] = $end; }
        if ($salesmanId !== 'all' && !empty($salesmanId)) { $conds[] = "i.user_id = :salesman_id"; $params['salesman_id'] = $salesmanId; }

        if ($type === 'individual_medicine') {
            if ($categoryId !== 'all' && !empty($categoryId)) { $conds[] = "p.category_id = :category_id"; $params['category_id'] = $categoryId; }
            if ($companyId !== 'all' && !empty($companyId)) { $conds[] = "p.company_id = :company_id"; $params['company_id'] = $companyId; }
            $whereSql = !empty($conds) ? "WHERE " . implode(" AND ", $conds) : "";

            $sql = "SELECT p.name as 'Medicine', g.name as 'Generic', SUM(ii.quantity) as 'Total Qty Sold', SUM(ii.subtotal) as 'Total Revenue ({$curr})', SUM(ii.subtotal - (ii.quantity * COALESCE(ii.cost_price, p.cost_price))) as 'Gross Profit ({$curr})'
                    FROM invoice_items ii 
                    JOIN invoices i ON ii.invoice_id = i.id 
                    JOIN products p ON ii.product_id = p.id 
                    LEFT JOIN generics g ON p.generic_id = g.id 
                    $whereSql GROUP BY p.id ORDER BY `Total Revenue ({$curr})` DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['title' => 'Individual Medicine Sale Report', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } elseif ($type === 'invoice_overview') {
            $whereSql = !empty($conds) ? "WHERE " . implode(" AND ", $conds) : "";
            $sql = "SELECT i.id as 'Invoice ID', DATE(i.created_at) as 'Invoice Date', i.customer_name as 'Customer Name', u.name as 'Salesman', i.total_amount as 'Total Amount ({$curr})' FROM invoices i LEFT JOIN users u ON i.user_id = u.id $whereSql ORDER BY DATE(i.created_at) ASC, i.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['title' => 'Invoice-based Sale Overview', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } elseif ($type === 'company_wise_sale') {
            if ($companyId !== 'all' && !empty($companyId)) { $conds[] = "p.company_id = :company_id"; $params['company_id'] = $companyId; }
            $whereSql = !empty($conds) ? "WHERE " . implode(" AND ", $conds) : "";
            $sql = "SELECT comp.name as 'Company/Manufacturer', COUNT(DISTINCT ii.invoice_id) as 'Total Invoices', SUM(ii.quantity) as 'Total Qty Sold', SUM(ii.subtotal) as 'Total Revenue ({$curr})' FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id JOIN products p ON ii.product_id = p.id JOIN companies comp ON p.company_id = comp.id $whereSql GROUP BY comp.id ORDER BY `Total Revenue ({$curr})` DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['title' => 'Company-wise Medicine Sale Report', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }
        return ['title' => 'Sale Report', 'data' => []];
    }

    /**
     * Stock Valuation Reports.
     */
    public function getStockReport(string $type, $categoryId = 'all', $companyId = 'all'): array {
        $curr = currency_symbol();
        $conds = [];
        if ($categoryId !== 'all' && !empty($categoryId)) $conds[] = "p.category_id = " . (int)$categoryId;
        if ($companyId !== 'all' && !empty($companyId)) $conds[] = "p.company_id = " . (int)$companyId;

        if ($type === 'overall_overview') {
            $conds[] = "p.quantity > 0";
            $whereSql = "WHERE " . implode(" AND ", $conds);
            $stmt = $this->db->query("SELECT p.name as 'Medicine', c.name as 'Category', comp.name as 'Company', p.quantity as 'Current Stock', p.cost_price as 'Cost/Unit ({$curr})', (p.quantity * p.cost_price) as 'Total Value ({$curr})' FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN companies comp ON p.company_id = comp.id $whereSql ORDER BY `Total Value ({$curr})` DESC");
            return ['title' => 'Overall Stock Overview', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } elseif ($type === 'company_wise_stock' || $type === 'company_wise') {
            $whereSql = !empty($conds) ? "WHERE " . implode(" AND ", $conds) : "";
            $stmt = $this->db->query("SELECT comp.name as 'Company/Manufacturer', COUNT(p.id) as 'Total Products', SUM(p.quantity) as 'Total Stock Qty', SUM(p.quantity * p.cost_price) as 'Total Stock Value ({$curr})' FROM products p JOIN companies comp ON p.company_id = comp.id $whereSql GROUP BY comp.id ORDER BY `Total Stock Value ({$curr})` DESC");
            return ['title' => 'Company-wise Stock Valuation', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        }
        return ['title' => 'Stock Report', 'data' => []];
    }

    /**
     * Margin & Markup Analysis.
     */
    public function getMarginAnalysisReport(string $start, string $end, $categoryId = 'all', $companyId = 'all'): array {
        $curr = currency_symbol();
        $where = [];
        $params = [];
        if ($start) { $where[] = "DATE(p.created_at) >= :start"; $params['start'] = $start; }
        if ($end) { $where[] = "DATE(p.created_at) <= :end"; $params['end'] = $end; }
        if ($categoryId !== 'all' && !empty($categoryId)) { $where[] = "p.category_id = :cat_id"; $params['cat_id'] = $categoryId; }
        if ($companyId !== 'all' && !empty($companyId)) { $where[] = "p.company_id = :comp_id"; $params['comp_id'] = $companyId; }
        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT p.name as 'Medicine', g.name as 'Generic', c.name as 'Category', comp.name as 'Company',
                       p.cost_price as 'Cost Price ({$curr})', p.price as 'Selling Price ({$curr})',
                       (p.price - p.cost_price) as 'Profit/Unit ({$curr})',
                       ROUND(CASE WHEN p.price > 0 THEN ((p.price - p.cost_price) / p.price) * 100 ELSE 0 END, 2) as 'Margin (%)',
                       p.quantity as 'Current Stock'
                FROM products p
                LEFT JOIN generics g ON p.generic_id = g.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                $whereSql
                ORDER BY `Margin (%)` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['title' => 'Profit Margin & Markup Analysis', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * Dead Stock / Locked Capital Analysis.
     */
    public function getDeadStockReport(int $days = 90, $categoryId = 'all', $companyId = 'all'): array {
        $curr = currency_symbol();
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        $where = ["p.quantity > 0", "p.id NOT IN (SELECT DISTINCT product_id FROM invoice_items ii JOIN invoices i ON ii.invoice_id = i.id WHERE DATE(i.created_at) >= :cutoff)"];
        $params = ['cutoff' => $cutoffDate];

        if ($categoryId !== 'all' && !empty($categoryId)) { $where[] = "p.category_id = :cat_id"; $params['cat_id'] = $categoryId; }
        if ($companyId !== 'all' && !empty($companyId)) { $where[] = "p.company_id = :comp_id"; $params['comp_id'] = $companyId; }
        $whereSql = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT p.name as 'Medicine', c.name as 'Category', comp.name as 'Company',
                       p.quantity as 'Unsold Stock Qty', p.cost_price as 'Unit Cost ({$curr})',
                       (p.quantity * p.cost_price) as 'Locked Capital ({$curr})',
                       p.expiry_date as 'Expiry Date'
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN companies comp ON p.company_id = comp.id
                $whereSql
                ORDER BY `Locked Capital ({$curr})` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['title' => "Dead Stock & Capital Lockup Report (Last {$days} Days)", 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * Customer Ledger Statement.
     */
    public function getCustomerLedgerReport(string $start, string $end): array {
        $curr = currency_symbol();
        $where = $this->getDateWhere('created_at', $start, $end);
        $sql = "SELECT customer_name as 'Customer Name', COUNT(id) as 'Total Invoices',
                       SUM(total_amount) as 'Total Spent ({$curr})',
                       MAX(created_at) as 'Last Transaction Date'
                FROM invoices
                $where
                GROUP BY customer_name
                ORDER BY `Total Spent ({$curr})` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->getDateParams($start, $end));
        return ['title' => 'Customer Credit & Ledger Statement', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }
}
