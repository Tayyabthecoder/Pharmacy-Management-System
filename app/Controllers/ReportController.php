<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class ReportController {
    protected Product $productModel;
    protected Invoice $invoiceModel;
    protected Setting $settingModel;
    protected PDO $db;

    public function __construct() {
        // Middleware: Check if user is logged in and is admin or salesman
        (new \App\Middleware\RoleMiddleware(['admin', 'salesman']))->handle();
        $this->productModel = new Product();
        $this->invoiceModel = new Invoice();
        $this->settingModel = new Setting();
        
        global $pdo;
        $this->db = $pdo;
    }

    public function index() {
        $pageTitle = "System Analytics & Reports";
        $db = $this->db;

        // Fetch query parameters
        $filter_preset = $_GET['preset'] ?? 'all';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $salesman_id = $_GET['salesman_id'] ?? 'all';
        $category_id = $_GET['category_id'] ?? 'all';

        // Calculate start/end dates if a preset is selected
        if ($filter_preset !== 'custom') {
            $end_date = date('Y-m-d');
            if ($filter_preset === 'today') {
                $start_date = date('Y-m-d');
            } elseif ($filter_preset === 'yesterday') {
                $start_date = date('Y-m-d', strtotime('-1 day'));
                $end_date = date('Y-m-d', strtotime('-1 day'));
            } elseif ($filter_preset === '7days') {
                $start_date = date('Y-m-d', strtotime('-7 days'));
            } elseif ($filter_preset === '30days') {
                $start_date = date('Y-m-d', strtotime('-30 days'));
            } elseif ($filter_preset === 'this_month') {
                $start_date = date('Y-m-01');
            } elseif ($filter_preset === '6months') {
                $start_date = date('Y-m-d', strtotime('-6 months'));
            } elseif ($filter_preset === 'all') {
                $start_date = '';
                $end_date = '';
            }
        }

        // Fetch dropdown options
        $salesmen = [];
        $categories = [];
        try {
            $salesmen = $db->query("SELECT id, name FROM users WHERE role = 'salesman' AND status = 'active' ORDER BY name ASC")->fetchAll();
            $categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
        } catch (Exception $e) {
            error_log("[Report Dropdown Error] " . $e->getMessage());
        }

        // Initialize KPIs and visual data variables
        $totalRevenue = 0.0;
        $totalProfit = 0.0;
        $profitMargin = 0.0;
        $totalInventoryValue = 0.0;
        $totalCustomers = 0;
        $totalInvoices = 0;
        $averageOrderValue = 0.0;
        $outOfStockCount = 0;

        $salesTrend = [];
        $topProducts = [];
        $allFilteredTopProducts = [];
        $lowStockProducts = [];
        $allFilteredLowStockProducts = [];
        $salesByCategory = [];
        $salesmanLeaderboard = [];
        $salesByGeneric = [];
        $salesByCompany = [];

        try {
            // Build WHERE clauses for invoices
            $invoiceWhere = [];
            $invoiceParams = [];

            if ($start_date !== '') {
                $invoiceWhere[] = "i.created_at >= :start_date";
                $invoiceParams['start_date'] = $start_date . ' 00:00:00';
            }
            if ($end_date !== '') {
                $invoiceWhere[] = "i.created_at <= :end_date";
                $invoiceParams['end_date'] = $end_date . ' 23:59:59';
            }
            if ($salesman_id !== 'all') {
                $invoiceWhere[] = "i.user_id = :salesman_id";
                $invoiceParams['salesman_id'] = (int)$salesman_id;
            }

            // Category joined queries
            $catWhere = $invoiceWhere;
            $catParams = $invoiceParams;
            if ($category_id !== 'all') {
                $catWhere[] = "p.category_id = :category_id";
                $catParams['category_id'] = (int)$category_id;
            }

            // 1. Calculate Revenue, Profit & Invoices Count
            if ($category_id !== 'all') {
                $whereStr = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : "";
                
                // Revenue & Profit
                $revQuery = "SELECT SUM(ii.subtotal) as revenue, SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit
                             FROM invoices i 
                             JOIN invoice_items ii ON i.id = ii.invoice_id
                             JOIN products p ON ii.product_id = p.id
                             $whereStr";
                $stmt = $db->prepare($revQuery);
                $stmt->execute($catParams);
                $revResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalRevenue = (float)($revResult['revenue'] ?? 0.0);
                $totalProfit = (float)($revResult['profit'] ?? 0.0);

                // Invoices
                $invQuery = "SELECT COUNT(DISTINCT i.id) 
                             FROM invoices i 
                             JOIN invoice_items ii ON i.id = ii.invoice_id
                             JOIN products p ON ii.product_id = p.id
                             $whereStr";
                $stmt = $db->prepare($invQuery);
                $stmt->execute($catParams);
                $totalInvoices = (int)($stmt->fetchColumn() ?: 0);
            } else {
                $whereStr = count($invoiceWhere) > 0 ? "WHERE " . implode(" AND ", $invoiceWhere) : "";
                
                // Revenue & Profit
                // We join products to get cost_price fallback
                $revQuery = "SELECT SUM(ii.subtotal) as revenue, SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit
                             FROM invoices i 
                             LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                             LEFT JOIN products p ON ii.product_id = p.id
                             $whereStr";
                $stmt = $db->prepare($revQuery);
                $stmt->execute($invoiceParams);
                $revResult = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalRevenue = (float)($revResult['revenue'] ?? 0.0);
                $totalProfit = (float)($revResult['profit'] ?? 0.0);

                // Invoices
                $invQuery = "SELECT COUNT(*) FROM invoices i $whereStr";
                $stmt = $db->prepare($invQuery);
                $stmt->execute($invoiceParams);
                $totalInvoices = (int)($stmt->fetchColumn() ?: 0);
            }

            // 2. Active Customers (based on distinct patient names)
            if ($category_id !== 'all') {
                $custWhereStr = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : "";
                
                $custQuery = "SELECT COUNT(DISTINCT i.customer_name) 
                              FROM invoices i 
                              JOIN invoice_items ii ON i.id = ii.invoice_id
                              JOIN products p ON ii.product_id = p.id
                              $custWhereStr";
                $stmt = $db->prepare($custQuery);
                $stmt->execute($catParams);
                $totalCustomers = (int)($stmt->fetchColumn() ?: 0);
            } else {
                $custWhereStr = count($invoiceWhere) > 0 ? "WHERE " . implode(" AND ", $invoiceWhere) : "";
                
                $custQuery = "SELECT COUNT(DISTINCT i.customer_name) FROM invoices i $custWhereStr";
                $stmt = $db->prepare($custQuery);
                $stmt->execute($invoiceParams);
                $totalCustomers = (int)($stmt->fetchColumn() ?: 0);
            }

            // 3. Average Order Value & Profit Margin
            $averageOrderValue = $totalInvoices > 0 ? ($totalRevenue / $totalInvoices) : 0.0;
            $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0.0;

            // 4. Asset Valuation (Current inventory value)
            if ($category_id !== 'all') {
                $stmt = $db->prepare("SELECT SUM(price * quantity) FROM products WHERE category_id = :category_id");
                $stmt->execute(['category_id' => (int)$category_id]);
            } else {
                $stmt = $db->query("SELECT SUM(price * quantity) FROM products");
            }
            $totalInventoryValue = (float)($stmt->fetchColumn() ?: 0.0);

            // 5. Out of Stock count
            if ($category_id !== 'all') {
                $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE quantity = 0 AND category_id = :category_id");
                $stmt->execute(['category_id' => (int)$category_id]);
            } else {
                $stmt = $db->query("SELECT COUNT(*) FROM products WHERE quantity = 0");
            }
            $outOfStockCount = (int)($stmt->fetchColumn() ?: 0);

            // 6. Sales Trend Grouping (By day for short ranges, by month for long ranges)
            $isDaily = false;
            if ($start_date !== '' && $end_date !== '') {
                $diff = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                if ($diff <= 45) {
                    $isDaily = true;
                }
            }
            $timeFormat = $isDaily ? '%Y-%m-%d' : '%Y-%m';
            $groupBy = "strftime('{$timeFormat}', i.created_at)";

            if ($category_id !== 'all') {
                $whereStr = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : "";
                $trendQuery = "SELECT $groupBy as time_label, SUM(ii.subtotal) as revenue, SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit, COUNT(DISTINCT i.id) as count
                               FROM invoices i
                               JOIN invoice_items ii ON i.id = ii.invoice_id
                               JOIN products p ON ii.product_id = p.id
                               $whereStr
                               GROUP BY time_label
                               ORDER BY time_label ASC";
                $stmt = $db->prepare($trendQuery);
                $stmt->execute($catParams);
            } else {
                $whereStr = count($invoiceWhere) > 0 ? "WHERE " . implode(" AND ", $invoiceWhere) : "";
                $trendQuery = "SELECT $groupBy as time_label, SUM(ii.subtotal) as revenue, SUM(ii.subtotal - (COALESCE(ii.cost_price, p.cost_price) * ii.quantity)) as profit, COUNT(DISTINCT i.id) as count
                               FROM invoices i
                               LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                               LEFT JOIN products p ON ii.product_id = p.id
                               $whereStr
                               GROUP BY time_label
                               ORDER BY time_label ASC";
                $stmt = $db->prepare($trendQuery);
                $stmt->execute($invoiceParams);
            }
            $salesTrend = $stmt->fetchAll();

            // 7. Top Selling Products
            $whereStr = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : "";
            $topProdQuery = "SELECT p.id, p.name, p.image, c.name as category_name, SUM(ii.quantity) as total_qty, SUM(ii.subtotal) as total_revenue
                             FROM invoice_items ii
                             JOIN products p ON ii.product_id = p.id
                             LEFT JOIN categories c ON p.category_id = c.id
                             JOIN invoices i ON ii.invoice_id = i.id
                             $whereStr
                             GROUP BY p.id, p.name, p.image, c.name
                             ORDER BY total_qty DESC";
            $stmt = $db->prepare($topProdQuery);
            $stmt->execute($catParams);
            $allFilteredTopProducts = $stmt->fetchAll();
            $topProducts = array_slice($allFilteredTopProducts, 0, 5);

            // 8. Low Stock Alerts
            $sql = "SELECT p.id, p.name, p.strength, p.quantity, p.price, p.min_stock_level, c.name as category_name, g.name as generic_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN generics g ON p.generic_id = g.id";
            if ($category_id !== 'all') {
                $sql .= " WHERE p.quantity <= p.min_stock_level AND p.category_id = :category_id
                        ORDER BY p.quantity ASC";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':category_id', (int)$category_id, PDO::PARAM_INT);
            } else {
                $sql .= " WHERE p.quantity <= p.min_stock_level 
                        ORDER BY p.quantity ASC";
                $stmt = $db->prepare($sql);
            }
            $stmt->execute();
            $allFilteredLowStockProducts = $stmt->fetchAll();
            $lowStockProducts = array_slice($allFilteredLowStockProducts, 0, 5);

            // 9. Sales by Category (for Doughnut Chart)
            $catChartQuery = "SELECT c.name as category_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                              FROM invoice_items ii
                              JOIN products p ON ii.product_id = p.id
                              JOIN categories c ON p.category_id = c.id
                              JOIN invoices i ON ii.invoice_id = i.id
                              $whereStr
                              GROUP BY c.id, c.name
                              ORDER BY total_revenue DESC";
            $stmt = $db->prepare($catChartQuery);
            $stmt->execute($catParams);
            $salesByCategory = $stmt->fetchAll();

            // 10. Salesman Leaderboard (for Bar Chart)
            if ($category_id !== 'all') {
                $whereStrForLeaderboard = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : "";
                $leaderboardQuery = "SELECT u.name as salesman_name, SUM(ii.subtotal) as total_revenue, COUNT(DISTINCT i.id) as invoice_count
                                     FROM invoices i
                                     JOIN users u ON i.user_id = u.id
                                     JOIN invoice_items ii ON i.id = ii.invoice_id
                                     JOIN products p ON ii.product_id = p.id
                                     $whereStrForLeaderboard
                                     GROUP BY u.id, u.name
                                     ORDER BY total_revenue DESC";
                $stmt = $db->prepare($leaderboardQuery);
                $stmt->execute($catParams);
            } else {
                $whereStrForLeaderboard = count($invoiceWhere) > 0 ? "WHERE " . implode(" AND ", $invoiceWhere) : "";
                $leaderboardQuery = "SELECT u.name as salesman_name, SUM(i.total_amount) as total_revenue, COUNT(i.id) as invoice_count
                                     FROM invoices i
                                     JOIN users u ON i.user_id = u.id
                                     $whereStrForLeaderboard
                                     GROUP BY u.id, u.name
                                     ORDER BY total_revenue DESC";
                $stmt = $db->prepare($leaderboardQuery);
                $stmt->execute($invoiceParams);
            }
            $salesmanLeaderboard = $stmt->fetchAll();

            // 11. Sales by Generic Name
            $whereStrGeneric = count($catWhere) > 0 ? "WHERE " . implode(" AND ", $catWhere) : (count($invoiceWhere) > 0 ? "WHERE " . implode(" AND ", $invoiceWhere) : "");
            $genericQuery = "SELECT g.name as generic_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                             FROM invoice_items ii
                             JOIN products p ON ii.product_id = p.id
                             LEFT JOIN generics g ON p.generic_id = g.id
                             JOIN invoices i ON ii.invoice_id = i.id
                             $whereStrGeneric
                             GROUP BY g.id, g.name
                             ORDER BY total_revenue DESC LIMIT 10";
            $stmt = $db->prepare($genericQuery);
            $stmt->execute($category_id !== 'all' ? $catParams : $invoiceParams);
            $salesByGeneric = $stmt->fetchAll();

            // 12. Sales by Company/Manufacturer
            $companyQuery = "SELECT comp.name as company_name, SUM(ii.subtotal) as total_revenue, SUM(ii.quantity) as total_qty
                             FROM invoice_items ii
                             JOIN products p ON ii.product_id = p.id
                             LEFT JOIN companies comp ON p.company_id = comp.id
                             JOIN invoices i ON ii.invoice_id = i.id
                             $whereStrGeneric
                             GROUP BY comp.id, comp.name
                             ORDER BY total_revenue DESC LIMIT 10";
            $stmt = $db->prepare($companyQuery);
            $stmt->execute($category_id !== 'all' ? $catParams : $invoiceParams);
            $salesByCompany = $stmt->fetchAll();


        } catch (Exception $e) {
            error_log("[Report Error] " . $e->getMessage());
        }

        require_once BASE_PATH . '/resources/views/admin/reports.php';
    }

    public function expiryReport() {
        $pageTitle = "Medicine Expiry Report";
        
        $expiredProducts = $this->productModel->getExpiredProducts();
        $nearExpiryProducts = $this->productModel->getNearExpiryProducts(180);
        
        require_once BASE_PATH . '/resources/views/admin/expiry_report.php';
    }

    public function generate() {
        $category = $_POST['report_category'] ?? '';
        $type = $_POST['report_type'] ?? '';
        $format = $_POST['export_format'] ?? 'html';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $salesmanId = $_POST['salesman_id'] ?? 'all';
        $categoryId = $_POST['category_id'] ?? 'all';
        $companyId = $_POST['company_id'] ?? 'all';
        $selectedColumns = $_POST['selected_columns'] ?? [];

        $data = [];
        $title = "Report";

        if ($category === 'medicine') {
            $result = $this->getMedicineReport($type, $categoryId, $companyId);
        } elseif ($category === 'receive') {
            $result = $this->getReceiveReport($type, $startDate, $endDate, $companyId);
        } elseif ($category === 'sale') {
            $result = $this->getSaleReport($type, $startDate, $endDate, $salesmanId, $categoryId, $companyId);
        } elseif ($category === 'stock') {
            $result = $this->getStockReport($type, $categoryId, $companyId);
        } elseif ($category === 'analytics') {
            if ($type === 'margin_analysis') {
                $result = $this->getMarginAnalysisReport($startDate, $endDate, $categoryId, $companyId);
            } elseif ($type === 'dead_stock') {
                $days = (int)($_POST['dead_stock_days'] ?? 90);
                $result = $this->getDeadStockReport($days, $categoryId, $companyId);
            } elseif ($type === 'customer_ledger') {
                $result = $this->getCustomerLedgerReport($startDate, $endDate);
            } else {
                $result = ['title' => 'Analytics Report', 'data' => []];
            }
        } else {
            $result = ['title' => 'Unknown Report', 'data' => []];
        }

        $rawNavData = $result['data'] ?: [];
        $title = $result['title'];
        $isList = $result['is_list'] ?? false;
        $groupBy = $result['group_by'] ?? '';

        if ($isList) {
            $data = $rawNavData;
            $summaryTotals = [];
        } else {
            $data = $this->filterColumns($rawNavData, $selectedColumns);
            $summaryTotals = $this->calculateSummaryTotals($data);
        }

        if ($format === 'html') {
            $pageTitle = $title;
            require_once __DIR__ . '/../../resources/views/admin/advanced_reports_result.php';
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'excel') {
            $this->exportExcel($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'word') {
            $this->exportWord($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'csv') {
            $this->exportCsv($data, $title, $summaryTotals, $isList, $groupBy);
        }
    }

    private function filterColumns(array $data, array $selectedColumns): array {
        if (empty($data) || empty($selectedColumns)) return $data;
        $filtered = [];
        foreach ($data as $row) {
            $newRow = [];
            foreach ($selectedColumns as $col) {
                if (array_key_exists($col, $row)) {
                    $newRow[$col] = $row[$col];
                }
            }
            if (!empty($newRow)) {
                $filtered[] = $newRow;
            }
        }
        return !empty($filtered) ? $filtered : $data;
    }

    private function calculateSummaryTotals(array $data): array {
        if (empty($data)) return [];
        $totals = [];
        $headers = array_keys($data[0]);

        foreach ($headers as $index => $header) {
            $h = strtolower($header);
            $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                      strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                      strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                      strpos($h,'cost') !== false || strpos($h,'spent') !== false || strpos($h,'capital') !== false) && $h !== 'id' && strpos($h,'margin') === false;

            $isMargin = strpos($h,'margin') !== false;

            if ($isMargin) {
                $sum = 0; $count = 0;
                foreach ($data as $row) {
                    if (isset($row[$header]) && is_numeric($row[$header])) {
                        $sum += (float)$row[$header];
                        $count++;
                    }
                }
                $totals[$header] = $count > 0 ? round($sum / $count, 2) : 0;
            } elseif ($isNum) {
                $sum = 0;
                foreach ($data as $row) {
                    if (isset($row[$header]) && is_numeric($row[$header])) {
                        $sum += (float)$row[$header];
                    }
                }
                $totals[$header] = round($sum, 2);
            } else {
                $totals[$header] = ($index === 0) ? 'TOTALS' : '';
            }
        }
        return $totals;
    }

    private function getMedicineReport($type, $categoryId = 'all', $companyId = 'all') {
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

    private function getReceiveReport($type, $start, $end, $companyId = 'all') {
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

    private function getSaleReport($type, $start, $end, $salesmanId = 'all', $categoryId = 'all', $companyId = 'all') {
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

    private function getStockReport($type, $categoryId = 'all', $companyId = 'all') {
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

    private function getMarginAnalysisReport($start, $end, $categoryId = 'all', $companyId = 'all') {
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

    private function getDeadStockReport($days = 90, $categoryId = 'all', $companyId = 'all') {
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

    private function getCustomerLedgerReport($start, $end) {
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

    private function getDateWhere($field, $start, $end) {
        if ($start && $end) return "WHERE DATE($field) BETWEEN :start AND :end";
        if ($start) return "WHERE DATE($field) >= :start";
        if ($end) return "WHERE DATE($field) <= :end";
        return "";
    }

    private function getDateParams($start, $end) {
        $p = [];
        if ($start) $p['start'] = $start;
        if ($end) $p['end'] = $end;
        return $p;
    }

    private function buildHtmlTable($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $date = date('F j, Y, g:i a');
        ob_start();
        require BASE_PATH . '/resources/views/admin/pdf_report.php';
        return ob_get_clean();
    }

    private function exportPDF($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $html = $this->buildHtmlTable($data, $title, $summaryTotals, $isList, $groupBy);
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(strtolower(str_replace(' ', '_', $title)) . '.pdf', ["Attachment" => true]);
        exit;
    }

    private function exportCsv($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $filename = strtolower(str_replace(' ', '_', $title)) . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        if (!empty($data)) {
            if ($isList) {
                foreach ($data as $group) {
                    fputcsv($output, ['[' . $group['group_title'] . ']']);
                    if (!empty($group['items'])) {
                        fputcsv($output, array_keys($group['items'][0]));
                        foreach ($group['items'] as $item) {
                            fputcsv($output, $item);
                        }
                    }
                    fputcsv($output, []);
                }
            } else {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
                if (!empty($summaryTotals)) {
                    $summaryRow = [];
                    foreach (array_keys($data[0]) as $h) {
                        $summaryRow[] = $summaryTotals[$h] ?? '';
                    }
                    fputcsv($output, $summaryRow);
                }
            }
        }
        fclose($output);
        exit;
    }

    private function exportExcel($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheetTitle = mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $title), 0, 31);
        $sheet->setTitle($sheetTitle ?: 'Report');

        if (!empty($data)) {
            if ($isList) {
                $rowNum = 1;
                $sheet->setCellValue('A' . $rowNum, $title);
                $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(16);
                $rowNum += 2;

                foreach ($data as $group) {
                    $sheet->setCellValue('A' . $rowNum, $group['group_title']);
                    $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1E3A8A');
                    $rowNum++;

                    if (!empty($group['items'])) {
                        $headers = array_keys($group['items'][0]);
                        $colIdx = 1;
                        foreach ($headers as $h) {
                            $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowNum;
                            $sheet->setCellValue($cellCoord, $h);
                            $sheet->getStyle($cellCoord)->getFont()->setBold(true);
                            $colIdx++;
                        }
                        $rowNum++;

                        foreach ($group['items'] as $item) {
                            $colIdx = 1;
                            foreach ($item as $val) {
                                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowNum;
                                $sheet->setCellValue($cellCoord, $val);
                                $colIdx++;
                            }
                            $rowNum++;
                        }
                    }
                    $rowNum++;
                }

                foreach (range(1, 10) as $col) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colString)->setAutoSize(true);
                }
            } else {
                $META_ROWS = 4;
                $headers   = array_keys($data[0]);
                $colCount  = count($headers);
                $lastCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->setCellValue('A2', 'Generated: ' . date('F j, Y, g:i a') . '   |   Records: ' . number_format(count($data)));
                $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle('A2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);

                $sheet->getRowDimension(3)->setRowHeight(8);
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $sheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DBEAFE');

                $sheet->getRowDimension(4)->setRowHeight(6);

                $headerRowNum = $META_ROWS + 1;
                $sheet->getRowDimension($headerRowNum)->setRowHeight(22);

                foreach ($headers as $colIdx => $header) {
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . $headerRowNum;
                    $sheet->setCellValue($cellCoordinate, $header);

                    $h = strtolower($header);
                    $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                              strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                              strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                              strpos($h,'cost') !== false || $h === 'id');

                    $sheet->getStyle($cellCoordinate)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'))->setSize(10);
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
                    $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    if ($isNum) {
                        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    }
                }

                $dataStartRow = $headerRowNum + 1;
                foreach ($data as $rIndex => $row) {
                    $rowNum = $dataStartRow + $rIndex;
                    $sheet->getRowDimension($rowNum)->setRowHeight(19);

                    $cIndex = 1;
                    foreach ($row as $header => $val) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                        $cellCoord = $colLetter . $rowNum;

                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                                  strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                                  strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                                  strpos($h,'cost') !== false || $h === 'id');

                        if ($isNum && is_numeric($val)) {
                            $sheet->setCellValueExplicit($cellCoord, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || $h === 'id' || strpos($h,'count') !== false;
                            if ($isInt) {
                                $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                            } else {
                                $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0.00');
                            }
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        } else {
                            $sheet->setCellValue($cellCoord, $val);
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }

                        if ($rIndex % 2 === 1) {
                            $sheet->getStyle($cellCoord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                        }

                        $sheet->getStyle($cellCoord)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
                        $cIndex++;
                    }
                }

                $dataEndRow = $dataStartRow + count($data) - 1;

                if (!empty($summaryTotals)) {
                    $summaryRowNum = $dataEndRow + 1;
                    $sheet->getRowDimension($summaryRowNum)->setRowHeight(22);
                    $cIndex = 1;
                    foreach ($headers as $header) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                        $cellCoord = $colLetter . $summaryRowNum;
                        $val = $summaryTotals[$header] ?? '';
                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                                  strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                                  strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                                  strpos($h,'cost') !== false);

                        if ($val !== '' && is_numeric($val)) {
                            $sheet->setCellValueExplicit($cellCoord, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || strpos($h,'count') !== false;
                            $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode($isInt ? '#,##0' : '#,##0.00');
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        } else {
                            $sheet->setCellValue($cellCoord, $val);
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }

                        $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
                        $sheet->getStyle($cellCoord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
                        $sheet->getStyle($cellCoord)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)->getColor()->setRGB('1E3A8A');
                        $sheet->getStyle($cellCoord)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)->getColor()->setRGB('1E3A8A');
                        $cIndex++;
                    }
                }

                foreach (range(1, $colCount) as $col) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colString)->setAutoSize(true);
                }
            }
        }

        $filename = strtolower(str_replace(' ', '_', $title)) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportWord($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'orientation'   => 'landscape',
            'marginTop'     => 720,
            'marginRight'   => 720,
            'marginBottom'  => 720,
            'marginLeft'    => 720,
            'pageSizeW'     => 15840,
            'pageSizeH'     => 12240,
        ]);

        $section->addText(
            htmlspecialchars($title),
            ['bold' => true, 'size' => 18, 'color' => '1E3A8A'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 0]
        );

        $section->addText(
            '',
            ['size' => 2],
            ['spaceAfter' => 0, 'spaceBefore' => 0, 'borderBottomSize' => 12, 'borderBottomColor' => '2563EB']
        );

        $section->addText(
            'Generated: ' . date('F j, Y, g:i a'),
            ['size' => 8, 'color' => '64748B', 'italic' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 80, 'spaceAfter' => 200]
        );

        if (!empty($data)) {
            if ($isList) {
                foreach ($data as $group) {
                    $section->addText(
                        htmlspecialchars($group['group_title']),
                        ['bold' => true, 'size' => 14, 'color' => '1E3A8A'],
                        ['spaceBefore' => 180, 'spaceAfter' => 60]
                    );

                    if (!empty($group['items'])) {
                        foreach ($group['items'] as $item) {
                            $line = "• " . $item['Medicine'] . " (" . $item['Generic'] . ")";
                            $line .= " | Stock: " . number_format($item['Stock Qty']);
                            $line .= " | Cost: Rs. " . number_format($item['Cost Price'], 2);
                            $line .= " | Price: Rs. " . number_format($item['Retail Price'], 2);
                            $line .= " | Total Value: Rs. " . number_format($item['Total Cost Value'], 2);
                            $section->addText(htmlspecialchars($line), ['size' => 9.5], ['spaceAfter' => 40, 'indent' => 240]);
                        }
                    }
                }
            } else {
                $headers   = array_keys($data[0]);
                $colCount  = count($headers);
                $pageWidth  = 14400;
                $numericCols = [];
                $isNumFlags  = [];

                foreach ($headers as $i => $header) {
                    $h = strtolower($header);
                    $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                              strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                              strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                              strpos($h,'cost') !== false || $h === 'id');
                    $isNumFlags[$i] = $isNum;
                    $numericCols[$i] = $isNum ? 1 : 2;
                }

                $totalWeight = array_sum($numericCols);
                $colWidths   = [];
                foreach ($numericCols as $i => $w) {
                    $colWidths[$i] = (int)(($w / $totalWeight) * $pageWidth);
                }

                $tableStyleName = 'ReportTable';
                $phpWord->addTableStyle($tableStyleName,
                    ['borderSize' => 4, 'borderColor' => 'E2E8F0', 'cellMargin' => 80,
                     'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::LEFT],
                    ['bgColor' => '1E3A8A', 'borderBottomSize' => 8, 'borderBottomColor' => '1E40AF']
                );

                $table = $section->addTable($tableStyleName);

                $table->addRow(350);
                $hFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 8];
                foreach ($headers as $i => $header) {
                    $align = $isNumFlags[$i] ? \PhpOffice\PhpWord\SimpleType\Jc::RIGHT : \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
                    $table->addCell($colWidths[$i], ['bgColor' => '1E3A8A', 'valign' => 'center'])
                          ->addText(htmlspecialchars($header), $hFontStyle, ['alignment' => $align]);
                }

                $rowIdx = 0;
                foreach ($data as $row) {
                    $table->addRow(280);
                    $bgColor = ($rowIdx % 2 === 1) ? 'F8FAFC' : 'FFFFFF';
                    $i = 0;
                    foreach ($row as $header => $val) {
                        $isNum = $isNumFlags[$i];
                        $align = $isNum ? \PhpOffice\PhpWord\SimpleType\Jc::RIGHT : \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
                        $strVal = (string)($val ?? '');
                        if ($isNum && is_numeric($val)) {
                            $h = strtolower($header);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || $h === 'id' || strpos($h,'count') !== false;
                            $strVal = $isInt ? number_format((float)$val, 0) : number_format((float)$val, 2);
                        }
                        $table->addCell($colWidths[$i], ['bgColor' => $bgColor, 'valign' => 'center'])
                          ->addText(htmlspecialchars($strVal), ['size' => 8], ['alignment' => $align]);
                        $i++;
                    }
                    $rowIdx++;
                }
            }
        }

        $filename = strtolower(str_replace(' ', '_', $title)) . '.docx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }

}

