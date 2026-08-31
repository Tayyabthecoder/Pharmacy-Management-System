<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\InventoryLog;
use App\Models\ReceiveInvoice;
use App\Models\ReceiveInvoiceItem;
use App\Models\Notification;

class ReceiveInvoiceController {
    protected $receiveInvoiceModel;
    protected $receiveInvoiceItemModel;
    protected $productModel;
    protected $logModel;
    protected $supplierModel;
    protected $settingModel;
    protected $notificationModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->receiveInvoiceModel = new ReceiveInvoice();
        $this->receiveInvoiceItemModel = new ReceiveInvoiceItem();
        $this->productModel = new Product();
        $this->logModel = new InventoryLog();
        $this->supplierModel = new Supplier();
        $this->settingModel = new Setting();
        $this->notificationModel = new Notification();
    }

    public function index() {
        $pageTitle = "Receive Invoices";
        
        $msg = '';
        $msgType = '';


        // Filters
        $filters = [
            'supplier_id' => $_GET['supplier_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = (int)$this->settingModel->get('records_per_page', 10);

        $pagination = $this->receiveInvoiceModel->getAllPaginated($page, $perPage, $filters);
        $invoices = $pagination['data'];
        
        $suppliers = $this->supplierModel->getAll();

        require_once BASE_PATH . '/resources/views/admin/receive_invoices.php';
    }

    public function create() {
        $pageTitle = "Create Receive Invoice";
        $error = '';
        
        $nextInvoiceNumber = $this->receiveInvoiceModel->getNextInvoiceNumber();
        $suppliers = $this->supplierModel->getAll();
        
        // Fetch all active products for autocomplete
        $products = $this->productModel->all('products', 'name ASC');
        $productsJson = json_encode($products);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $this->receiveInvoiceModel->beginTransaction();

                $supplierId = (int)($_POST['supplier_id'] ?? 0);
                $receivedDate = $_POST['received_date'] ?? date('Y-m-d');
                $referenceNumber = trim($_POST['reference_number'] ?? '');
                $discount = (float)($_POST['discount'] ?? 0.00);

                $productIds   = $_POST['product_id']    ?? [];
                $quantities   = $_POST['quantity']       ?? [];
                $costPrices   = $_POST['cost_price']     ?? [];
                $itemDiscounts = $_POST['item_discount'] ?? [];
                $batchNumbers = $_POST['batch_number']   ?? [];
                $expiryDates  = $_POST['expiry_date']    ?? [];

                if ($supplierId <= 0) {
                    throw new Exception("Supplier selection is required.");
                }
                
                if (empty($referenceNumber)) {
                    throw new Exception("Reference Invoice number is mandatory.");
                }
                
                if (empty($productIds)) {
                    throw new Exception("At least one medicine item is required.");
                }

                $discount = 0.00;
                $totalAmount = 0.00;
                $itemsToCreate = [];

                foreach ($productIds as $index => $pid) {
                    $pid   = (int)$pid;
                    $qty   = (int)$quantities[$index];
                    $cost  = (float)$costPrices[$index];
                    $itemDiscount = (float)($itemDiscounts[$index] ?? 0);
                    $batch = trim($batchNumbers[$index] ?? '');
                    $expiry = trim($expiryDates[$index] ?? '');

                    if ($qty <= 0) {
                        throw new Exception("Quantity must be greater than zero.");
                    }

                    if ($cost < 0) {
                        throw new Exception("Cost price cannot be negative.");
                    }

                    $product = $this->productModel->find('products', $pid);
                    if (!$product) {
                        throw new Exception("Medicine not found.");
                    }

                    $subtotal = $qty * $cost;
                    $totalAmount += $subtotal;
                    
                    $itemDiscountAmt = $subtotal * ($itemDiscount / 100);
                    $discount += $itemDiscountAmt;

                    $itemsToCreate[] = [
                        'product_id'   => $pid,
                        'quantity'     => $qty,
                        'cost_price'   => $cost,
                        'discount'     => $itemDiscount,
                        'subtotal'     => $subtotal - $itemDiscountAmt,
                        'batch_number' => $batch,
                        'expiry_date'  => $expiry,
                    ];
                }

                $netAmount = $totalAmount - $discount;
                if ($netAmount < 0) {
                    throw new Exception("Discount cannot exceed total amount.");
                }

                // 1. Create invoice header
                $invoiceData = [
                    'invoice_number' => $nextInvoiceNumber,
                    'supplier_id' => $supplierId,
                    'user_id' => $_SESSION['user_id'],
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'net_amount' => $netAmount,
                    'reference_number' => $referenceNumber,
                    'status' => 'received',
                    'received_date' => $receivedDate
                ];
                
                $invoiceId = $this->receiveInvoiceModel->create($invoiceData);

                if (!$invoiceId) {
                    throw new Exception("Failed to save invoice.");
                }

                // 2. Create invoice items, update product stocks and log inventory changes
                foreach ($itemsToCreate as $item) {
                    $item['receive_invoice_id'] = $invoiceId;
                    
                    // Create item record (includes batch_number & expiry_date)
                    $this->receiveInvoiceItemModel->create($item);

                    // Update stock: add quantities (passing negative decreases the negative, i.e., adds stock)
                    $this->productModel->updateStock($item['product_id'], -$item['quantity']);

                    // Write back cost price, batch number, and expiry date to the product master record
                    $productUpdateData = ['cost_price' => $item['cost_price']];
                    if (!empty($item['batch_number'])) {
                        $productUpdateData['batch_number'] = $item['batch_number'];
                    }
                    if (!empty($item['expiry_date'])) {
                        $productUpdateData['expiry_date'] = $item['expiry_date'];
                    }
                    $this->productModel->update($item['product_id'], $productUpdateData);

                    // Log inventory transaction
                    $this->logModel->create([
                        'product_id' => $item['product_id'],
                        'user_id'    => $_SESSION['user_id'],
                        'qty_change' => $item['quantity'],
                        'type'       => 'restock',
                        'remarks'    => "Receive Invoice #{$nextInvoiceNumber}"
                    ]);
                }

                // Trigger restock notification
                if ($this->settingModel->get('notify_restock')) {
                    $currency = $this->settingModel->get('currency', '$');
                    $this->notificationModel->notifyAdmins(
                        "Stock Received",
                        "Receive Invoice #{$nextInvoiceNumber} recorded. Total amount: {$currency}{$netAmount}.",
                        'info',
                        URL_ROOT . '/admin/receive_invoices',
                        false
                    );
                }

                $this->receiveInvoiceModel->commit();
                $_SESSION['success_msg'] = "Receive Invoice created and inventory updated successfully!";
                redirect('/admin/receive_invoices');
            } catch (Exception $e) {
                if ($this->receiveInvoiceModel->inTransaction()) {
                    $this->receiveInvoiceModel->rollBack();
                }
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/resources/views/admin/create_receive_invoice.php';
    }

    public function createReturn() {
        $pageTitle = "Return Stock to Supplier";
        $error = '';
        
        $nextInvoiceNumber = 'RET-' . $this->receiveInvoiceModel->getNextInvoiceNumber(); // e.g. RET-RI-00002
        $suppliers = $this->supplierModel->getAll();
        
        // Fetch all active products for autocomplete
        $products = $this->productModel->all('products', 'name ASC');
        $productsJson = json_encode($products);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $this->receiveInvoiceModel->beginTransaction();

                $supplierId = (int)($_POST['supplier_id'] ?? 0);
                $receivedDate = $_POST['received_date'] ?? date('Y-m-d');
                $referenceNumber = trim($_POST['reference_number'] ?? '');
                $discount = (float)($_POST['discount'] ?? 0.00);

                $productIds   = $_POST['product_id']    ?? [];
                $quantities   = $_POST['quantity']       ?? [];
                $costPrices   = $_POST['cost_price']     ?? [];
                $itemDiscounts = $_POST['item_discount'] ?? [];
                $batchNumbers = $_POST['batch_number']   ?? [];
                $expiryDates  = $_POST['expiry_date']    ?? [];

                if ($supplierId <= 0) {
                    throw new Exception("Supplier selection is required.");
                }
                
                if (empty($referenceNumber)) {
                    throw new Exception("Reference Invoice number is mandatory.");
                }
                
                if (empty($productIds)) {
                    throw new Exception("At least one medicine item is required.");
                }

                $discount = 0.00;
                $totalAmount = 0.00;
                $itemsToCreate = [];

                foreach ($productIds as $index => $pid) {
                    $pid   = (int)$pid;
                    $qty   = (int)$quantities[$index];
                    $cost  = (float)$costPrices[$index];
                    $itemDiscount = (float)($itemDiscounts[$index] ?? 0);
                    $batch = trim($batchNumbers[$index] ?? '');
                    $expiry = trim($expiryDates[$index] ?? '');

                    if ($qty <= 0) {
                        throw new Exception("Quantity must be greater than zero.");
                    }

                    if ($cost < 0) {
                        throw new Exception("Cost price cannot be negative.");
                    }

                    $product = $this->productModel->find('products', $pid);
                    if (!$product) {
                        throw new Exception("Medicine not found.");
                    }

                    $subtotal = $qty * $cost;
                    $totalAmount += $subtotal;
                    
                    $itemDiscountAmt = $subtotal * ($itemDiscount / 100);
                    $discount += $itemDiscountAmt;

                    $itemsToCreate[] = [
                        'product_id'   => $pid,
                        'quantity'     => $qty,
                        'cost_price'   => $cost,
                        'discount'     => $itemDiscount,
                        'subtotal'     => $subtotal - $itemDiscountAmt,
                        'batch_number' => $batch,
                        'expiry_date'  => $expiry,
                    ];
                }

                $netAmount = $totalAmount - $discount;
                if ($netAmount < 0) {
                    throw new Exception("Discount cannot exceed total amount.");
                }

                // 1. Create invoice header (Negative amounts for returns)
                $invoiceData = [
                    'invoice_number' => $nextInvoiceNumber,
                    'supplier_id' => $supplierId,
                    'user_id' => $_SESSION['user_id'],
                    'total_amount' => -$totalAmount,
                    'discount' => -$discount,
                    'net_amount' => -$netAmount,
                    'reference_number' => $referenceNumber,
                    'status' => 'received',
                    'received_date' => $receivedDate,
                    'is_return' => 1
                ];
                
                $invoiceId = $this->receiveInvoiceModel->create($invoiceData);

                if (!$invoiceId) {
                    throw new Exception("Failed to save return invoice.");
                }

                // 2. Create invoice items, update product stocks and log inventory changes
                foreach ($itemsToCreate as $item) {
                    $item['receive_invoice_id'] = $invoiceId;
                    
                    // Make quantity and subtotal negative for DB
                    $item['quantity'] = -$item['quantity'];
                    $item['subtotal'] = -$item['subtotal'];

                    // Create item record (includes batch_number & expiry_date)
                    $this->receiveInvoiceItemModel->create($item);

                    // Update stock: we deduct stock for a return. 
                    // $item['quantity'] is already negative now.
                    // $this->productModel->updateStock does: SET quantity = quantity - :quantity
                    // To deduct stock, we pass positive.
                    // Since $item['quantity'] is negative, we pass -$item['quantity'] which makes it positive.
                    $this->productModel->updateStock($item['product_id'], -$item['quantity']);

                    // Log inventory transaction
                    $this->logModel->create([
                        'product_id' => $item['product_id'],
                        'user_id'    => $_SESSION['user_id'],
                        'qty_change' => $item['quantity'], // Negative
                        'type'       => 'return_out',
                        'remarks'    => "Return Stock #{$nextInvoiceNumber}"
                    ]);
                }

                if ($this->settingModel->get('notify_restock')) {
                    $currency = $this->settingModel->get('currency', '$');
                    $this->notificationModel->notifyAdmins(
                        "Stock Returned",
                        "Stock Return #{$nextInvoiceNumber} recorded. Total amount: {$currency}{$netAmount}.",
                        'info',
                        URL_ROOT . '/admin/receive_invoices',
                        false
                    );
                }

                $this->receiveInvoiceModel->commit();
                $_SESSION['success_msg'] = "Return Invoice created and inventory updated successfully!";
                redirect('/admin/receive_invoices');
            } catch (Exception $e) {
                if ($this->receiveInvoiceModel->inTransaction()) {
                    $this->receiveInvoiceModel->rollBack();
                }
                $error = $e->getMessage();
            }
        }

        require_once BASE_PATH . '/resources/views/admin/create_return_receive_invoice.php';
    }



    public function view() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid Invoice ID";
            redirect('/admin/receive_invoices');
        }

        $invoice = $this->receiveInvoiceModel->getWithDetails($id);
        if (!$invoice) {
            $_SESSION['error'] = "Receive Invoice not found.";
            redirect('/admin/receive_invoices');
        }

        $items = $this->receiveInvoiceItemModel->getByInvoice($id);

        require_once BASE_PATH . '/resources/views/admin/view_receive_invoice.php';
    }

    public function detailsApi() {
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);
        $invoiceNumber = trim($_GET['invoice_number'] ?? '');
        
        if ($id <= 0 && empty($invoiceNumber)) {
            echo json_encode(['success' => false, 'message' => 'Invalid Request']);
            return;
        }
        
        // Find by ID or Invoice Number
        $invoice = null;
        if ($id > 0) {
            $invoice = $this->receiveInvoiceModel->getWithDetails($id);
        } else {
            // Find by invoice number
            $stmt = $this->receiveInvoiceModel->getDb()->prepare("SELECT id FROM receive_invoices WHERE invoice_number = :num LIMIT 1");
            $stmt->execute(['num' => $invoiceNumber]);
            $foundId = $stmt->fetchColumn();
            if ($foundId) {
                $invoice = $this->receiveInvoiceModel->getWithDetails($foundId);
                $id = $foundId;
            }
        }
        
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Receive Invoice not found']);
            return;
        }
        
        $items = $this->receiveInvoiceItemModel->getByInvoice($id);
        
        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'items' => $items
        ]);
    }
}
