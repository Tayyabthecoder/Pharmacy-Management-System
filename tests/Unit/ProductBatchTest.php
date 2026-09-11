<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Product;
use App\Models\ProductBatch;

class ProductBatchTest extends TestCase {
    protected Product $productModel;
    protected ProductBatch $batchModel;
    protected int $testProductId;

    protected function setUp(): void {
        parent::setUp();
        $this->productModel = new Product();
        $this->batchModel = new ProductBatch();

        // Create a temporary test product
        $this->testProductId = (int)$this->productModel->create([
            'name'                    => 'Unit Test Product Batch ' . uniqid(),
            'price'                   => 25.00,
            'cost_price'              => 15.00,
            'quantity'                => 50,
            'min_stock_level'         => 5,
            'is_prescription_required'=> 0,
        ]);
    }

    protected function tearDown(): void {
        if ($this->testProductId > 0) {
            $this->productModel->deleteProduct($this->testProductId);
        }
        parent::tearDown();
    }

    public function testBatchesAreOrderedByEarliestExpiryFirst(): void {
        // Create 2 batches: one expiring in 30 days, one expiring in 90 days
        $batchEarlyId = $this->batchModel->create([
            'product_id'   => $this->testProductId,
            'batch_number' => 'BATCH-EARLY-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+30 days')),
            'quantity'     => 15,
            'cost_price'   => 15.00
        ]);

        $batchLateId = $this->batchModel->create([
            'product_id'   => $this->testProductId,
            'batch_number' => 'BATCH-LATE-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+90 days')),
            'quantity'     => 35,
            'cost_price'   => 15.00
        ]);

        $available = $this->batchModel->getAvailableBatchesFEFO($this->testProductId);
        $this->assertNotEmpty($available);
        $this->assertEquals($batchEarlyId, $available[0]['id'], "Earliest expiring batch must be first in FEFO list");
    }

    public function testAtomicDecrementBatch(): void {
        $batchId = $this->batchModel->create([
            'product_id'   => $this->testProductId,
            'batch_number' => 'BATCH-DECR-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+60 days')),
            'quantity'     => 20,
            'cost_price'   => 15.00
        ]);

        $success = $this->batchModel->decrementBatch($batchId, 5);
        $this->assertTrue($success);

        $batch = $this->batchModel->find('product_batches', $batchId);
        $this->assertEquals(15, (int)$batch['quantity']);

        // Decrementing more than available must fail
        $overDecrement = $this->batchModel->decrementBatch($batchId, 50);
        $this->assertFalse($overDecrement);
    }

    public function testDeductStockFEFOAcrossBatches(): void {
        $b1 = $this->batchModel->create([
            'product_id'   => $this->testProductId,
            'batch_number' => 'FEFO-B1-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+20 days')),
            'quantity'     => 10,
            'cost_price'   => 12.00
        ]);

        $b2 = $this->batchModel->create([
            'product_id'   => $this->testProductId,
            'batch_number' => 'FEFO-B2-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+40 days')),
            'quantity'     => 20,
            'cost_price'   => 12.00
        ]);

        // Deduct 15 units: 10 from b1, 5 from b2
        $result = $this->batchModel->deductStockFEFO($this->testProductId, 15);
        $this->assertCount(2, $result);

        $batch1 = $this->batchModel->find('product_batches', $b1);
        $batch2 = $this->batchModel->find('product_batches', $b2);

        $this->assertEquals(0, (int)$batch1['quantity']);
        $this->assertEquals(15, (int)$batch2['quantity']);
    }
}
