<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Services\InvoiceService;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;
use App\Models\User;

class PosCheckoutFlowTest extends TestCase {
    protected InvoiceService $invoiceService;
    protected Product $productModel;
    protected ProductBatch $batchModel;
    protected Invoice $invoiceModel;
    protected InvoiceItem $itemModel;
    protected InventoryLog $logModel;
    protected User $userModel;
    protected int $userId;
    protected int $productId;
    protected int $batch1Id;
    protected int $batch2Id;

    protected function setUp(): void {
        parent::setUp();
        $this->invoiceService = new InvoiceService();
        $this->productModel = new Product();
        $this->batchModel = new ProductBatch();
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->logModel = new InventoryLog();
        $this->userModel = new User();

        $users = $this->userModel->getAll();
        $this->userId = !empty($users) ? (int)$users[0]['id'] : 1;

        // 1. Create a Product
        $this->productId = (int)$this->productModel->create([
            'name'                    => 'POS Feature Test Medicine ' . uniqid(),
            'price'                   => 100.00,
            'cost_price'              => 60.00,
            'quantity'                => 40,
            'min_stock_level'         => 5,
            'is_prescription_required'=> 0,
            'barcode'                 => '890' . rand(10000000, 99999999)
        ]);

        // 2. Create 2 Batches (Batch 1 expires earlier than Batch 2)
        $this->batch1Id = (int)$this->batchModel->create([
            'product_id'   => $this->productId,
            'batch_number' => 'BATCH-POS-1',
            'expiry_date'  => date('Y-m-d', strtotime('+30 days')),
            'quantity'     => 15,
            'cost_price'   => 60.00
        ]);

        $this->batch2Id = (int)$this->batchModel->create([
            'product_id'   => $this->productId,
            'batch_number' => 'BATCH-POS-2',
            'expiry_date'  => date('Y-m-d', strtotime('+90 days')),
            'quantity'     => 25,
            'cost_price'   => 60.00
        ]);
    }

    protected function tearDown(): void {
        if ($this->productId > 0) {
            $this->productModel->deleteProduct($this->productId);
        }
        parent::tearDown();
    }

    public function testCompletePosCheckoutLifecycle(): void {
        // Step 1: Execute POS Sale for 20 units (should consume 15 from Batch 1, and 5 from Batch 2)
        $invoiceId = $this->invoiceService->createInvoice(
            $this->userId,
            'Sarah Connor',
            [$this->productId],
            [20],
            [100.00]
        );

        // Step 2: Verify Invoice Record
        $this->assertNotEmpty($invoiceId);
        $this->assertGreaterThan(0, (int)$invoiceId);

        $invoice = $this->invoiceModel->getInvoiceWithDetails($invoiceId);
        $this->assertNotEmpty($invoice);
        $this->assertEquals('Sarah Connor', $invoice['customer_name']);
        $this->assertEquals(2000.00, (float)$invoice['total_amount']);
        $this->assertStringStartsWith('INV-', $invoice['invoice_number']);

        // Step 3: Verify Invoice Items
        $items = $this->itemModel->getByInvoice($invoiceId);
        $this->assertCount(1, $items);
        $this->assertEquals($this->productId, $items[0]['product_id']);
        $this->assertEquals(20, (int)$items[0]['quantity']);

        // Step 4: Verify Multi-Batch FEFO Decrement
        $b1 = $this->batchModel->find('product_batches', $this->batch1Id);
        $b2 = $this->batchModel->find('product_batches', $this->batch2Id);

        $this->assertEquals(0, (int)$b1['quantity'], "Batch 1 (early expiry) must be fully depleted");
        $this->assertEquals(20, (int)$b2['quantity'], "Batch 2 must have 20 remaining (25 - 5)");

        // Step 5: Verify Product Stock Aggregation
        $prod = $this->productModel->find('products', $this->productId);
        $this->assertEquals(20, (int)$prod['quantity'], "Product overall stock must be 20");
    }
}
