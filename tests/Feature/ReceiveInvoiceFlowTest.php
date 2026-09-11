<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\ReceiveInvoice;
use App\Models\ReceiveInvoiceItem;
use App\Models\SupplierPayment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\User;

class ReceiveInvoiceFlowTest extends TestCase {
    protected ReceiveInvoice $receiveInvoiceModel;
    protected ReceiveInvoiceItem $receiveInvoiceItemModel;
    protected SupplierPayment $supplierPaymentModel;
    protected Product $productModel;
    protected ProductBatch $batchModel;
    protected Supplier $supplierModel;
    protected User $userModel;
    protected int $supplierId;
    protected int $productId;
    protected int $userId;

    protected function setUp(): void {
        parent::setUp();
        $this->receiveInvoiceModel = new ReceiveInvoice();
        $this->receiveInvoiceItemModel = new ReceiveInvoiceItem();
        $this->supplierPaymentModel = new SupplierPayment();
        $this->productModel = new Product();
        $this->batchModel = new ProductBatch();
        $this->supplierModel = new Supplier();
        $this->userModel = new User();

        $users = $this->userModel->getAll();
        $this->userId = !empty($users) ? (int)$users[0]['id'] : 1;

        $suppliers = $this->supplierModel->getAll();
        if (!empty($suppliers)) {
            $this->supplierId = (int)$suppliers[0]['id'];
        } else {
            $this->supplierId = (int)$this->supplierModel->create([
                'name' => 'Test Supplier Pharma ' . uniqid(),
                'contact_person' => 'Supplier Agent',
                'phone' => '123456789'
            ]);
        }

        $this->productId = (int)$this->productModel->create([
            'name' => 'Receive Test Medicine ' . uniqid(),
            'price' => 120.00,
            'cost_price' => 80.00,
            'quantity' => 10,
            'min_stock_level' => 5
        ]);
    }

    protected function tearDown(): void {
        if ($this->productId > 0) {
            $this->productModel->deleteProduct($this->productId);
        }
        parent::tearDown();
    }

    public function testCreateReceiveInvoiceWithNetAmount(): void {
        $nextInvoiceNumber = $this->receiveInvoiceModel->getNextInvoiceNumber();
        $qty = 20;
        $cost = 80.00;
        $subtotal = $qty * $cost; // 1600.00
        $discount = 100.00;
        $netAmount = $subtotal - $discount; // 1500.00

        $invoiceData = [
            'invoice_number'   => $nextInvoiceNumber,
            'supplier_id'      => $this->supplierId,
            'user_id'          => $this->userId,
            'total_amount'     => $subtotal,
            'discount'         => $discount,
            'net_amount'       => $netAmount,
            'reference_number' => 'REF-' . rand(1000, 9999),
            'status'           => 'received',
            'payment_status'   => 'partial',
            'payment_due_date' => date('Y-m-d', strtotime('+15 days')),
            'amount_paid'      => 500.00,
            'received_date'    => date('Y-m-d')
        ];

        $invoiceId = $this->receiveInvoiceModel->create($invoiceData);
        $this->assertNotEmpty($invoiceId);
        $this->assertGreaterThan(0, (int)$invoiceId);

        // Verify invoice was inserted properly with valid net_amount
        $invoice = $this->receiveInvoiceModel->getWithDetails((int)$invoiceId);
        $this->assertNotEmpty($invoice);
        $this->assertEquals(1600.00, (float)$invoice['total_amount']);
        $this->assertEquals(100.00, (float)$invoice['discount']);
        $this->assertEquals(1500.00, (float)$invoice['net_amount']);
        $this->assertEquals('partial', $invoice['payment_status']);
        $this->assertEquals(500.00, (float)$invoice['amount_paid']);

        // Create item and batch
        $this->receiveInvoiceItemModel->create([
            'receive_invoice_id' => $invoiceId,
            'product_id'         => $this->productId,
            'quantity'           => $qty,
            'cost_price'         => $cost,
            'discount'           => 0,
            'subtotal'           => $subtotal,
            'batch_number'       => 'BATCH-REC-01',
            'expiry_date'        => date('Y-m-d', strtotime('+180 days'))
        ]);

        $batchId = $this->batchModel->create([
            'product_id'         => $this->productId,
            'batch_number'       => 'BATCH-REC-01',
            'expiry_date'        => date('Y-m-d', strtotime('+180 days')),
            'quantity'           => $qty,
            'cost_price'         => $cost,
            'receive_invoice_id' => $invoiceId
        ]);
        $this->assertGreaterThan(0, $batchId);

        // Record supplier payment
        $paymentId = $this->supplierPaymentModel->createPayment([
            'receive_invoice_id' => $invoiceId,
            'supplier_id'        => $this->supplierId,
            'amount'             => 500.00,
            'payment_date'       => date('Y-m-d'),
            'payment_method'     => 'cash',
            'notes'              => 'Initial test partial payment',
            'created_by'         => $this->userId
        ]);
        $this->assertGreaterThan(0, $paymentId);
    }
}
