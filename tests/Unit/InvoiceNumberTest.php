<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Invoice;

class InvoiceNumberTest extends TestCase {
    protected Invoice $invoiceModel;

    protected function setUp(): void {
        parent::setUp();
        $this->invoiceModel = new Invoice();
    }

    public function testInvoiceNumberFormat(): void {
        $invNumber = $this->invoiceModel->generateInvoiceNumber('INV-');
        $this->assertStringStartsWith('INV-', $invNumber);
        $this->assertMatchesRegularExpression('/^INV-\d{5,}$/', $invNumber);
    }

    public function testReturnInvoiceNumberFormat(): void {
        $retNumber = $this->invoiceModel->generateInvoiceNumber('RET-');
        $this->assertStringStartsWith('RET-', $retNumber);
        $this->assertMatchesRegularExpression('/^RET-\d{5,}$/', $retNumber);
    }
}
