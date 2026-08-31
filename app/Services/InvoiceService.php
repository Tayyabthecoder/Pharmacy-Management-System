<?php

namespace App\Services;

use Exception;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;
use App\Models\Notification;
use App\Models\Setting;

class InvoiceService {
    protected $productModel;
    protected $invoiceModel;
    protected $itemModel;
    protected $logModel;
    protected $notificationModel;
    protected $settingModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->logModel = new InventoryLog();
        $this->notificationModel = new Notification();
        $this->settingModel = new Setting();
    }

    public function createInvoice($userId, $customerName, $productIds, $quantities, $prices, $doctorName = '', $doctorLicense = '', $taxRate = 0, $discountRate = 0, $adminEmail = '', $adminPassword = '') {
        if (empty($customerName)) {
            $customerName = "Walk-in Patient";
        }

        $totalAmount = 0;
        $requiresRx = false;
        $validatedProducts = [];
        $subtotalAmount = 0;

        try {
            $this->invoiceModel->beginTransaction();

            foreach ($productIds as $index => $pid) {
                $qty = (int)$quantities[$index];
                $price = (float)$prices[$index];
                
                if ($qty > 0) {
                    $product = $this->productModel->find('products', $pid);
                    if (!$product) {
                        throw new Exception("Product ID $pid not found.");
                    }

                    if ($product['quantity'] < $qty) {
                        throw new Exception("Insufficient stock for medicine: " . htmlspecialchars($product['name']) . " (Available: " . $product['quantity'] . ", Requested: $qty)");
                    }

                    if ($product['expiry_date'] !== null && strtotime($product['expiry_date']) <= strtotime(date('Y-m-d'))) {
                        throw new Exception("Cannot sell expired medicine: " . htmlspecialchars($product['name']) . " (Expired on: " . $product['expiry_date'] . ")");
                    }

                    if (!empty($product['is_prescription_required'])) {
                        $requiresRx = true;
                    }

                    $subtotalAmount += ($qty * $price);
                    $validatedProducts[] = [
                        'product' => $product,
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $qty * $price
                    ];
                }
            }

            if (empty($validatedProducts)) {
                throw new Exception("Cannot create invoice: No items selected.");
            }

            if ($requiresRx && (empty($doctorName) || empty($doctorLicense))) {
                throw new Exception("Prescription required: Doctor Name and License Number are mandatory.");
            }

            $totalAmount = $subtotalAmount;
            if ($discountRate > 0) {
                $totalAmount = $totalAmount * (1 - ($discountRate / 100));
            }
            if ($taxRate > 0) {
                $totalAmount = $totalAmount * (1 + ($taxRate / 100));
            }
            $totalAmount = round($totalAmount);

            // Settings controls check
            $settings = $this->settingModel->getAll();
            $userRole = strtolower($_SESSION['role'] ?? '');

            // Backend discount cap validation for salesman role
            if ($userRole === 'salesman' && $discountRate > 0) {
                if (empty($settings['salesman_can_give_discount']) || $settings['salesman_can_give_discount'] === '0') {
                    throw new Exception("Salesmen are not permitted to issue discounts.");
                }
                $maxDiscount = (float)($settings['max_salesman_discount'] ?? 0.0);
                if ($discountRate > $maxDiscount) {
                    throw new Exception("Discount rate ({$discountRate}%) exceeds maximum allowed limit ({$maxDiscount}%).");
                }
            }

            // Mandatory Admin Approval check for invoices exceeding threshold
            if (!empty($settings['require_admin_approval']) && $totalAmount > (float)($settings['approval_threshold_amount'] ?? 5000.00)) {
                if (empty($adminEmail) || empty($adminPassword)) {
                    $thresholdFormatted = number_format((float)($settings['approval_threshold_amount'] ?? 5000.00), 2);
                    throw new Exception("Invoice amount exceeds threshold limit ({$thresholdFormatted}). Administrator override credentials are required.");
                }
                global $pdo;
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND (role = 'admin' OR role = 'owner' OR username = 'owner') LIMIT 1");
                $stmt->execute(['email' => $adminEmail]);
                $adminUser = $stmt->fetch();
                
                if (!$adminUser || !password_verify($adminPassword, $adminUser['password'])) {
                    throw new Exception("Invoice amount exceeds salesman limit and requires valid administrator override credentials.");
                }
            }

            $invoiceId = $this->invoiceModel->create([
                'user_id' => $userId,
                'customer_name' => $customerName,
                'total_amount' => $totalAmount,
                'doctor_name' => $requiresRx ? $doctorName : null,
                'doctor_license' => $requiresRx ? $doctorLicense : null
            ]);

            foreach ($validatedProducts as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];
                $subtotal = $item['subtotal'];
                $product = $item['product'];
                $costPrice = (float)($product['cost_price'] ?? 0.00);

                $this->itemModel->create([
                    'invoice_id' => $invoiceId,
                    'product_id' => $pid,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'cost_price' => $costPrice
                ]);

                if (!$this->productModel->updateStockAtomic($pid, $qty)) {
                    throw new Exception("Stock deduction failed for medicine: " . htmlspecialchars($product['name']) . ". Insufficient available stock or concurrent change.");
                }

                $this->logModel->create([
                    'product_id' => $pid,
                    'user_id' => $userId,
                    'qty_change' => -$qty,
                    'type' => 'sale',
                    'remarks' => "Invoice #$invoiceId"
                ]);

                $product = $item['product'];
                $newStock = $product['quantity'] - $qty;
                if ($newStock <= $product['min_stock_level']) {
                    if ($this->settingModel->get('notify_low_stock')) {
                        $this->notificationModel->notifyAdmins(
                            "Low Stock Alert: " . $product['name'],
                            "Stock for {$product['name']} has dropped to {$newStock} (Minimum: {$product['min_stock_level']}).",
                            'warning',
                            URL_ROOT . '/admin/products',
                            true
                        );
                    }
                }
            }

            if ($this->settingModel->get('notify_new_invoice')) {
                $currency = $this->settingModel->get('currency', '$');
                $this->notificationModel->notifyAdmins(
                    "New Invoice Created",
                    "Invoice #{$invoiceId} was created for {$customerName} totaling {$currency}{$totalAmount}.",
                    'success',
                    URL_ROOT . '/admin/invoices',
                    false
                );
            }

            $this->invoiceModel->commit();
            return $invoiceId;

        } catch (Exception $e) {
            if ($this->invoiceModel->inTransaction()) {
                $this->invoiceModel->rollBack();
            }
            throw $e;
        }
    }

    public function createReturnInvoice($userId, $customerName, $productIds, $quantities, $prices, $doctorName = '', $doctorLicense = '', $taxRate = 0, $discountRate = 0, $adminEmail = '', $adminPassword = '') {
        if (empty($customerName)) {
            $customerName = "Walk-in Patient";
        }

        $totalAmount = 0;
        $requiresRx = false;
        $validatedProducts = [];
        $subtotalAmount = 0;

        try {
            $this->invoiceModel->beginTransaction();

            foreach ($productIds as $index => $pid) {
                $qty = (int)$quantities[$index];
                $price = (float)$prices[$index];
                
                if ($qty > 0) {
                    $product = $this->productModel->find('products', $pid);
                    if (!$product) {
                        throw new Exception("Product ID $pid not found.");
                    }

                    // For returns, we don't check if we have enough stock, we are receiving it back.
                    $subtotalAmount += ($qty * $price);
                    $validatedProducts[] = [
                        'product' => $product,
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'price' => $price,
                        'subtotal' => $qty * $price
                    ];
                }
            }

            if (empty($validatedProducts)) {
                throw new Exception("Cannot process return: No items selected.");
            }

            $totalAmount = $subtotalAmount;
            if ($discountRate > 0) {
                $totalAmount = $totalAmount * (1 - ($discountRate / 100));
            }
            if ($taxRate > 0) {
                $totalAmount = $totalAmount * (1 + ($taxRate / 100));
            }
            $totalAmount = round($totalAmount);

            // Make it negative for returns
            $negativeTotalAmount = -$totalAmount;

            $invoiceId = $this->invoiceModel->create([
                'user_id' => $userId,
                'customer_name' => $customerName,
                'total_amount' => $negativeTotalAmount,
                'doctor_name' => $doctorName,
                'doctor_license' => $doctorLicense,
                'is_return' => 1
            ]);

            foreach ($validatedProducts as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];
                $subtotal = $item['subtotal'];
                
                // Store negative quantities and subtotals with cost_price
                $product = $item['product'];
                $costPrice = (float)($product['cost_price'] ?? 0.00);
                $this->itemModel->create([
                    'invoice_id' => $invoiceId,
                    'product_id' => $pid,
                    'quantity' => -$qty,
                    'price' => $price,
                    'subtotal' => -$subtotal,
                    'cost_price' => $costPrice
                ]);

                // Update stock: Add stock back. $this->productModel->updateStock does SET quantity = quantity - :quantity
                // So passing negative $qty will ADD it to stock.
                $this->productModel->updateStock($pid, -$qty);

                $this->logModel->create([
                    'product_id' => $pid,
                    'user_id' => $userId,
                    'qty_change' => $qty, // We received $qty back into stock
                    'type' => 'return_in',
                    'remarks' => "Return Invoice #$invoiceId"
                ]);
            }

            if ($this->settingModel->get('notify_new_invoice')) {
                $currency = $this->settingModel->get('currency', '$');
                $this->notificationModel->notifyAdmins(
                    "Return Invoice Processed",
                    "Return Invoice #{$invoiceId} was processed for {$customerName} totaling {$currency}{$totalAmount}.",
                    'info',
                    URL_ROOT . '/admin/invoices',
                    false
                );
            }

            $this->invoiceModel->commit();
            return $invoiceId;

        } catch (Exception $e) {
            if ($this->invoiceModel->inTransaction()) {
                $this->invoiceModel->rollBack();
            }
            throw $e;
        }
    }


}
