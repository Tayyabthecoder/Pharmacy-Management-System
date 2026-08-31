<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;

class SaleController {
    protected $invoiceModel;
    protected $itemModel;
    protected $productModel;
    protected $logModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->productModel = new Product();
        $this->logModel = new InventoryLog();
    }

    public function index() {
        $pageTitle = "Invoices Management";

        // Fetch Invoices with Pagination
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $settingModel = new Setting();
        $perPage = (int)$settingModel->get('records_per_page', 10);

        $search = trim($_GET['search'] ?? '');
        $dateFilter = trim($_GET['date_filter'] ?? '');
        $pagination = $this->invoiceModel->getAllWithSalesman($page, $perPage, $search, $dateFilter);
        $invoices = $pagination['data'];

        require_once BASE_PATH . '/resources/views/admin/invoices.php';
    }


    public function detailsApi() {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Invoice ID']);
            return;
        }
        
        $invoice = $this->invoiceModel->getInvoiceWithDetails($id);
        
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
            return;
        }
        
        $items = $this->itemModel->getByInvoiceWithProducts($id);
        
        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'items' => $items
        ]);
    }
}
