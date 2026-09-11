<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\InvoiceService;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Exception;

class InvoiceServiceTest extends TestCase {
    protected InvoiceService $service;
    protected Product $productModel;
    protected ProductBatch $batchModel;
    protected User $userModel;
    protected int $userId;
    protected int $rxProductId = 0;
    protected int $normalProductId = 0;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new InvoiceService();
        $this->productModel = new Product();
        $this->batchModel = new ProductBatch();
        $this->userModel = new User();

        $users = $this->userModel->getAll();
        $this->userId = !empty($users) ? (int)$users[0]['id'] : 1;

        // Normal Product
        $this->normalProductId = (int)$this->productModel->create([
            'name'                    => 'OTC Panadol Test ' . uniqid(),
            'price'                   => 20.00,
            'cost_price'              => 10.00,
            'quantity'                => 100,
            'min_stock_level'         => 5,
            'is_prescription_required'=> 0
        ]);
        $this->batchModel->create([
            'product_id'   => $this->normalProductId,
            'batch_number' => 'BATCH-OTC-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+1 year')),
            'quantity'     => 100,
            'cost_price'   => 10.00
        ]);

        // Rx Required Product
        $this->rxProductId = (int)$this->productModel->create([
            'name'                    => 'Rx Amoxicillin Test ' . uniqid(),
            'price'                   => 50.00,
            'cost_price'              => 25.00,
            'quantity'                => 50,
            'min_stock_level'         => 5,
            'is_prescription_required'=> 1
        ]);
        $this->batchModel->create([
            'product_id'   => $this->rxProductId,
            'batch_number' => 'BATCH-RX-' . uniqid(),
            'expiry_date'  => date('Y-m-d', strtotime('+1 year')),
            'quantity'     => 50,
            'cost_price'   => 25.00
        ]);
    }

    protected function tearDown(): void {
        if ($this->normalProductId > 0) {
            $this->productModel->deleteProduct($this->normalProductId);
        }
        if ($this->rxProductId > 0) {
            $this->productModel->deleteProduct($this->rxProductId);
        }
        parent::tearDown();
    }

    public function testThrowsExceptionWhenNoItemsProvided(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No items selected');

        $this->service->createInvoice(
            $this->userId,
            'John Doe',
            [],
            [],
            []
        );
    }

    public function testRxValidationBlocksSaleWithoutDoctorDetails(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Prescription required');

        $this->service->createInvoice(
            $this->userId,
            'Jane Doe',
            [$this->rxProductId],
            [1],
            [50.00],
            '', // Missing doctorName
            ''  // Missing doctorLicense
        );
    }

    public function testInsufficientStockThrowsException(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->service->createInvoice(
            $this->userId,
            'Jane Doe',
            [$this->normalProductId],
            [9999], // Exceeds available stock
            [20.00]
        );
    }

    public function testSuccessfulSaleGeneratesInvoiceAndDeductsStock(): void {
        $initialStock = (int)$this->productModel->find('products', $this->normalProductId)['quantity'];

        $invoiceId = $this->service->createInvoice(
            $this->userId,
            'Valid Customer',
            [$this->normalProductId],
            [5],
            [20.00]
        );

        $this->assertNotEmpty($invoiceId);
        $this->assertGreaterThan(0, (int)$invoiceId);

        $updatedProd = $this->productModel->find('products', $this->normalProductId);
        $this->assertEquals($initialStock - 5, (int)$updatedProd['quantity']);
    }
}
