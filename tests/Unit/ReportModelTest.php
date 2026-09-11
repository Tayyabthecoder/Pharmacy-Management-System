<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Report;

class ReportModelTest extends TestCase {
    protected Report $reportModel;

    protected function setUp(): void {
        parent::setUp();
        $this->reportModel = new Report();
    }

    public function testGetAnalyticsSummaryStructure(): void {
        $summary = $this->reportModel->getAnalyticsSummary([
            'start_date'  => '',
            'end_date'    => '',
            'salesman_id' => 'all',
            'category_id' => 'all'
        ]);

        $this->assertArrayHasKey('totalRevenue', $summary);
        $this->assertArrayHasKey('totalProfit', $summary);
        $this->assertArrayHasKey('profitMargin', $summary);
        $this->assertArrayHasKey('totalInvoices', $summary);
        $this->assertArrayHasKey('totalCustomers', $summary);
        $this->assertArrayHasKey('averageOrderValue', $summary);
        $this->assertArrayHasKey('totalInventoryValue', $summary);
        $this->assertArrayHasKey('outOfStockCount', $summary);
    }

    public function testGetMedicineReport(): void {
        $result = $this->reportModel->getMedicineReport('detailed_info');
        $this->assertEquals('Detailed Medicine Information', $result['title']);
        $this->assertIsArray($result['data']);
    }

    public function testGetStockReport(): void {
        $result = $this->reportModel->getStockReport('overall_overview');
        $this->assertEquals('Overall Stock Overview', $result['title']);
        $this->assertIsArray($result['data']);
    }
}
