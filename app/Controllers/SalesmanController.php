<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\User;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;
use App\Models\Notification;
use App\Support\ProfileTrait;

require_once BASE_PATH . '/app/Support/ProfileTrait.php';

class SalesmanController {
    use ProfileTrait;

    protected $invoiceModel;
    protected $itemModel;
    protected $productModel;
    protected $userModel;
    protected $settingModel;
    protected $logModel;
    protected $notificationModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['salesman']))->handle();
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->settingModel = new Setting();
        $this->logModel = new InventoryLog();
        $this->notificationModel = new Notification();
    }

    // -------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------
    public function dashboard() {
        $pageTitle = "Salesman Dashboard";
        $userId = (int)$_SESSION['user_id'];

        // Total Sales using Model
        $totalSales = $this->invoiceModel->getTotalRevenue($userId);

        // Total Invoices using Model
        $totalInvoices = $this->invoiceModel->count('invoices', 'user_id = :uid', ['uid' => $userId]);

        // Recent Invoices using Model
        $recentInvoices = $this->invoiceModel->getByUser($userId, 5);

        // New specific data for dashboard
        $todaySales = $this->invoiceModel->getTodayRevenueByUser($userId);
        $todayInvoices = $this->invoiceModel->getTodayCountByUser($userId);
        $thisMonthSales = $this->invoiceModel->getMonthlyRevenueByUser($userId);
        $thisMonthInvoices = $this->invoiceModel->getMonthlyCountByUser($userId);
        $thisMonthItems = $this->invoiceModel->getMonthlyQuantityByUser($userId);
        $thisMonthAvgValue = $thisMonthInvoices > 0 ? ($thisMonthSales / $thisMonthInvoices) : 0;
        
        $monthlySalesData = $this->invoiceModel->getMonthlySalesByUser($userId, 6);
        $topProducts = $this->productModel->getTopSellingProducts(5);
        $nearExpiryCount = count($this->productModel->getNearExpiryProducts(180));
        $lowStockCount = count($this->productModel->getLowStockProducts(10));

        require_once BASE_PATH . '/resources/views/salesman/dashboard.php';
    }

    // -------------------------------------------------------
    // Invoice List
    // -------------------------------------------------------
    public function invoices() {
        $pageTitle = "My Invoices";
        $userId = (int)$_SESSION['user_id'];

        $settings = $this->settingModel->getAll();

        // Fetch Invoices with Pagination
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = (int)($settings['records_per_page'] ?? 10);

        $pagination = $this->invoiceModel->getByUser($userId, $page, $perPage);
        $invoices = $pagination['data'];

        require_once BASE_PATH . '/resources/views/admin/invoices.php';
    }

    // -------------------------------------------------------
    // Create Invoice
    // -------------------------------------------------------
    public function createInvoice() {
        $pageTitle = "Create Invoice";

        $settings = $this->settingModel->getAll();

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
                    (float)($_POST['tax_rate'] ?? 0),
                    0,
                    trim($_POST['admin_email'] ?? ''),
                    trim($_POST['admin_password'] ?? '')
                );

                $action = $_POST['action'] ?? 'save';
                $_SESSION['success'] = "Invoice created successfully.";
                if ($action === 'print') {
                    $printUrl = url('/invoice/print?id=' . $invoiceId . '&print=1');
                    echo "<script>window.location.href = '$printUrl';</script>";
                } else {
                    $listUrl = url('/salesman/invoices');
                    echo "<script>window.location.href = '$listUrl';</script>";
                }
                exit();

            } catch (Exception $e) {
                $error = "Transaction failed: " . $e->getMessage();
            }
        }

        // Fetch products for dropdown
        $products = $this->productModel->all('products', 'name ASC');
        
        // Pass products as JSON for JavaScript handling
        $productsJson = json_encode($products);
        
        require_once BASE_PATH . '/resources/views/admin/create_invoice.php';
    }

    // -------------------------------------------------------
    // Create Return Invoice
    // -------------------------------------------------------
    public function createReturnInvoice() {
        $pageTitle = "Return Invoice";

        $settings = $this->settingModel->getAll();

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
                    (float)($_POST['tax_rate'] ?? 0),
                    0,
                    trim($_POST['admin_email'] ?? ''),
                    trim($_POST['admin_password'] ?? '')
                );

                $action = $_POST['action'] ?? 'save';
                $_SESSION['success'] = "Return Invoice processed successfully.";
                if ($action === 'print') {
                    $printUrl = url('/invoice/print?id=' . $invoiceId . '&print=1');
                    echo "<script>window.location.href = '$printUrl';</script>";
                } else {
                    $listUrl = url('/salesman/invoices');
                    echo "<script>window.location.href = '$listUrl';</script>";
                }
                exit();

            } catch (Exception $e) {
                $error = "Transaction failed: " . $e->getMessage();
            }
        }

        // Fetch products for dropdown
        $products = $this->productModel->all('products', 'name ASC');
        $productsJson = json_encode($products);
        
        require_once BASE_PATH . '/resources/views/admin/create_return_invoice.php';
    }

    // -------------------------------------------------------
    // Inventory (read-only view for salesman)
    // -------------------------------------------------------
    public function inventory() {
        $pageTitle = "Inventory Check";
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $search = trim($_GET['search'] ?? '');
        
        $settings = $this->settingModel->getAll();
        $perPage = (int)($settings['records_per_page'] ?? 10);
        $minStock = (int)($settings['min_stock'] ?? 10);
        
        $paginationData = $this->productModel->getFilteredProducts($search, '', $minStock, $page, $perPage);
        $products = $paginationData['data'] ?? [];
        
        $pagination = [
            'current' => $page,
            'total' => $paginationData['total_pages'] ?? 1,
            'has_next' => $page < ($paginationData['total_pages'] ?? 1),
            'has_prev' => $page > 1
        ];

        require_once BASE_PATH . '/resources/views/salesman/inventory.php';
    }



    // -------------------------------------------------------
    // Delete/Void Invoice (Conditional)
    // -------------------------------------------------------
    public function deleteInvoice() {
        $settings = $this->settingModel->getAll();
        if (empty($settings['salesman_can_delete_invoice'])) {
            $_SESSION['error'] = "You do not have permission to delete/void invoices.";
            redirect('/salesman/invoices');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoiceId = (int)($_POST['id'] ?? 0);
            $userId = (int)$_SESSION['user_id'];

            if ($invoiceId > 0) {
                try {
                    $this->invoiceModel->beginTransaction();

                    $invoice = $this->invoiceModel->find('invoices', $invoiceId);
                    if (!$invoice || (int)$invoice['user_id'] !== $userId) {
                        throw new Exception("Invoice not found or unauthorized.");
                    }

                    $items = $this->itemModel->getByInvoice($invoiceId);

                    // Revert stock
                    foreach ($items as $item) {
                        $this->productModel->updateStock($item['product_id'], -$item['quantity']);
                        $this->logModel->create([
                            'product_id' => $item['product_id'],
                            'user_id' => $userId,
                            'qty_change' => $item['quantity'],
                            'type' => 'return',
                            'remarks' => "Void Invoice #$invoiceId"
                        ]);
                    }



                    // Delete invoice and items
                    $this->itemModel->deleteByInvoice($invoiceId);
                    $this->invoiceModel->delete('invoices', $invoiceId);

                    $this->invoiceModel->commit();
                    $_SESSION['success'] = "Invoice #$invoiceId voided successfully.";
                } catch (Exception $e) {
                    if ($this->invoiceModel->inTransaction()) {
                        $this->invoiceModel->rollBack();
                    }
                    $_SESSION['error'] = "Failed to void invoice: " . $e->getMessage();
                }
            }
        }
        redirect('/salesman/invoices');
    }
}
