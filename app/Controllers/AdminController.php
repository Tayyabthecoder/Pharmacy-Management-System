<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;
use App\Models\Notification;

class AdminController {
    protected $productModel;
    protected $categoryModel;
    protected $invoiceModel;
    protected $settingModel;
    protected $itemModel;
    protected $logModel;
    protected $notificationModel;

    public function __construct() {
        // Middleware: Check if user is logged in and is admin
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->invoiceModel = new Invoice();
        $this->settingModel = new Setting();
        $this->itemModel = new InvoiceItem();
        $this->logModel = new InventoryLog();
        $this->notificationModel = new Notification();
    }

    public function dashboard() {
        $pageTitle = "Admin Dashboard";

        // Fetch Stats
        $totalProducts = 0;
        $totalCategories = 0;
        $totalSales = 0;
        $todayRevenue = 0;
        $todayInvoiceCount = 0;
        $totalInventoryValue = 0;
        $lowStock = 0;
        $expiredCount = 0;
        $nearExpiryCount = 0;
        $recentSales = [];
        $recentActivity = [];

        try {
            $totalProducts = $this->productModel->count('products');
            $totalCategories = $this->categoryModel->count('categories');
            
            // Fetch total revenue using Invoice Model
            $totalSales = $this->invoiceModel->getTotalRevenue();
            $todayRevenue = $this->invoiceModel->getTodayRevenue();
            $todayInvoiceCount = $this->invoiceModel->getTodayCount();
            $totalInventoryValue = $this->productModel->getTotalInventoryValue();
            
            $lowStock = $this->productModel->count('products', 'quantity <= min_stock_level');
            
            $expiredCount = count($this->productModel->getExpiredProducts());
            $nearExpiryCount = count($this->productModel->getNearExpiryProducts(180));

            // Recent Sales using Invoice Model
            $recentSales = $this->invoiceModel->getAllWithSalesman();
            $recentSales = array_slice($recentSales, 0, 5); // Limit to 5

            // Recent inventory activities
            $recentActivity = $this->logModel->getRecent(10);

            // Today's activity summary for the operations feed
            $activitySummary = $this->logModel->getTodaySummary();

        } catch (Exception $e) {
            $error = "Error fetching stats: " . $e->getMessage();
        }

        // Render View and pass data to it
        require_once BASE_PATH . '/resources/views/admin/dashboard.php';
    }

    // -------------------------------------------------------
    // Create Invoice (Admin)
    // -------------------------------------------------------
    public function createInvoice() {
        $pageTitle = "Create Invoice";

        // Fetch Products for Dropdown using Model
        $products = $this->productModel->all('products', 'name ASC');
        $productsJson = json_encode($products);

        $settingModel = new Setting();
        $settings = $settingModel->getAll();



        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $invoiceService = new \App\Services\InvoiceService();
                $invoiceId = $invoiceService->createInvoice(
                    $_SESSION['user_id'],
                    trim($_POST['customer_name'] ?? ''),
                    $_POST['product_id'] ?? [],
                    $_POST['quantity'] ?? [],
                    $_POST['price'] ?? [],
                    trim($_POST['doctor_name'] ?? ''),
                    trim($_POST['doctor_license'] ?? ''),
                    (float)($_POST['tax_rate'] ?? 0)
                );

                $action = $_POST['action'] ?? 'save';
                $_SESSION['success'] = "Invoice created successfully.";
                if ($action === 'print') {
                    $printUrl = url('/invoice/print?id=' . $invoiceId . '&print=1');
                    echo "<script>window.location.href = '$printUrl';</script>";
                } else {
                    $listUrl = url('/admin/invoices');
                    echo "<script>window.location.href = '$listUrl';</script>";
                }
                exit();

            } catch (Exception $e) {
                $error = "Transaction failed: " . $e->getMessage();
            }
        }

        require_once BASE_PATH . '/resources/views/admin/create_invoice.php';
    }

    // -------------------------------------------------------
    // Create Return Invoice (Admin)
    // -------------------------------------------------------
    public function createReturnInvoice() {
        $pageTitle = "Return Invoice";

        // Fetch Products for Dropdown using Model
        $products = $this->productModel->all('products', 'name ASC');
        $productsJson = json_encode($products);

        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        // Handle Form Submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $invoiceService = new \App\Services\InvoiceService();
                $invoiceId = $invoiceService->createReturnInvoice(
                    $_SESSION['user_id'],
                    trim($_POST['customer_name'] ?? ''),
                    $_POST['product_id'] ?? [],
                    $_POST['quantity'] ?? [],
                    $_POST['price'] ?? [],
                    trim($_POST['doctor_name'] ?? ''),
                    trim($_POST['doctor_license'] ?? ''),
                    (float)($_POST['tax_rate'] ?? 0)
                );

                $action = $_POST['action'] ?? 'save';
                $_SESSION['success'] = "Return Invoice processed successfully.";
                if ($action === 'print') {
                    $printUrl = url('/invoice/print?id=' . $invoiceId . '&print=1');
                    echo "<script>window.location.href = '$printUrl';</script>";
                } else {
                    $listUrl = url('/admin/invoices');
                    echo "<script>window.location.href = '$listUrl';</script>";
                }
                exit();

            } catch (Exception $e) {
                $error = "Transaction failed: " . $e->getMessage();
            }
        }

        require_once BASE_PATH . '/resources/views/admin/create_return_invoice.php';
    }
    
    public function wipeData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['username']) || strtolower($_SESSION['username']) !== 'owner') {
            die("Unauthorized access.");
        }
        
        try {
            global $pdo;
            $db = $pdo;
            $db->exec('PRAGMA foreign_keys = OFF');
            
            $tables = [
                'receive_invoice_items', 'receive_invoices', 'invoice_items', 
                'invoices', 'inventory_logs', 'notifications', 'login_attempts', 
                'products', 'suppliers', 'users', 'generics', 'companies', 'categories'
            ];
            
            foreach ($tables as $table) {
                $db->exec("DELETE FROM $table");
            }
            
            // Reset auto increment
            $db->exec("DELETE FROM sqlite_sequence");
            
            // Recreate standard admin user so standard logins still work if owner bypass isn't used
            $db->exec("INSERT INTO users (name, username, email, password, role) VALUES ('Admin', 'admin', 'admin@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'admin')");
            
            $db->exec('PRAGMA foreign_keys = ON');
            
            $_SESSION['success'] = "SUCCESS: All system data has been permanently deleted.";
            
            $dashUrl = url('/admin/dashboard');
            echo "<script>window.location.href = '$dashUrl';</script>";
            exit();
        } catch (Exception $e) {
            die("Error wiping data: " . $e->getMessage());
        }
    }

    public function wipeInvoices() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['username']) || strtolower($_SESSION['username']) !== 'owner') {
            die("Unauthorized access.");
        }
        
        try {
            global $pdo;
            $db = $pdo;
            $db->exec('PRAGMA foreign_keys = OFF');
            
            $tables = [
                'receive_invoice_items', 'receive_invoices', 'invoice_items', 'invoices'
            ];
            
            foreach ($tables as $table) {
                $db->exec("DELETE FROM $table");
            }
            
            $db->exec('PRAGMA foreign_keys = ON');
            
            $_SESSION['success'] = "SUCCESS: All invoices and receive invoices have been permanently deleted.";
            
            $dashUrl = url('/admin/dashboard');
            echo "<script>window.location.href = '$dashUrl';</script>";
            exit();
        } catch (Exception $e) {
            die("Error wiping invoices: " . $e->getMessage());
        }
    }
}

