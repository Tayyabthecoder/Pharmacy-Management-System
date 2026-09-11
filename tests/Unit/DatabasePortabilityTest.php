<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Support\QueryHelper;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Report;

class DatabasePortabilityTest extends TestCase {
    protected function tearDown(): void {
        QueryHelper::setDriver(null);
    }

    public function testSqliteDateQueriesProduceValidResults() {
        QueryHelper::setDriver('sqlite');

        $invoiceModel = new Invoice();
        $todayRev = $invoiceModel->getTodayRevenue();
        $this->assertIsNumeric($todayRev);

        $productModel = new Product();
        $stats = $productModel->getSummaryStats();
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('expiring', $stats);

        $reportModel = new Report();
        $trend = $reportModel->getSalesTrend([]);
        $this->assertIsArray($trend);
    }

    public function testQueryHelperDialectSwitching() {
        QueryHelper::setDriver('mysql');
        $this->assertTrue(QueryHelper::isMySql());
        $this->assertEquals("CURDATE()", QueryHelper::dateNow());
        $this->assertEquals("DATE(i.created_at) = CURDATE()", QueryHelper::isToday('i.created_at'));

        QueryHelper::setDriver('sqlite');
        $this->assertTrue(QueryHelper::isSqlite());
        $this->assertEquals("date('now')", QueryHelper::dateNow());
        $this->assertEquals("date(i.created_at) = date('now')", QueryHelper::isToday('i.created_at'));
    }
}
