<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Support\QueryHelper;

class QueryHelperTest extends TestCase {
    protected function tearDown(): void {
        QueryHelper::setDriver(null);
    }

    public function testSqliteDialectGeneratesCorrectSql() {
        QueryHelper::setDriver('sqlite');

        $this->assertTrue(QueryHelper::isSqlite());
        $this->assertFalse(QueryHelper::isMySql());

        $this->assertEquals("date('now')", QueryHelper::dateNow());
        $this->assertEquals("strftime('%Y-%m-%d %H:%M:%S', 'now')", QueryHelper::dateTimeNow());
        $this->assertEquals("date(created_at, '+7 days')", QueryHelper::dateAdd('created_at', 7, 'DAY'));
        $this->assertEquals("date('now', '-30 days')", QueryHelper::dateSub("'now'", 30, 'DAY'));
        $this->assertEquals("strftime('%Y-%m', created_at)", QueryHelper::dateFormat('created_at', '%Y-%m'));
        $this->assertEquals("a || b || c", QueryHelper::concat('a', 'b', 'c'));
        $this->assertEquals("GROUP_CONCAT(name, ',')", QueryHelper::groupConcat('name', ','));
        $this->assertEquals("date(created_at) = date('now')", QueryHelper::isToday('created_at'));
        $this->assertEquals("strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')", QueryHelper::isCurrentMonth('created_at'));
        $this->assertEquals("created_at >= date('now', '-7 days')", QueryHelper::datePastDays('created_at', 7));
        $this->assertEquals("expiry_date <= date('now', '+180 days')", QueryHelper::dateFutureDays('expiry_date', 180));
    }

    public function testMySqlDialectGeneratesCorrectSql() {
        QueryHelper::setDriver('mysql');

        $this->assertTrue(QueryHelper::isMySql());
        $this->assertFalse(QueryHelper::isSqlite());

        $this->assertEquals("CURDATE()", QueryHelper::dateNow());
        $this->assertEquals("NOW()", QueryHelper::dateTimeNow());
        $this->assertEquals("DATE_ADD(created_at, INTERVAL 7 DAY)", QueryHelper::dateAdd('created_at', 7, 'DAY'));
        $this->assertEquals("DATE_SUB('now', INTERVAL 30 DAY)", QueryHelper::dateSub("'now'", 30, 'DAY'));
        $this->assertEquals("DATE_FORMAT(created_at, '%Y-%m')", QueryHelper::dateFormat('created_at', '%Y-%m'));
        $this->assertEquals("CONCAT(a, b, c)", QueryHelper::concat('a', 'b', 'c'));
        $this->assertEquals("GROUP_CONCAT(name SEPARATOR ',')", QueryHelper::groupConcat('name', ','));
        $this->assertEquals("DATE(created_at) = CURDATE()", QueryHelper::isToday('created_at'));
        $this->assertEquals("DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')", QueryHelper::isCurrentMonth('created_at'));
        $this->assertEquals("created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", QueryHelper::datePastDays('created_at', 7));
        $this->assertEquals("expiry_date <= DATE_ADD(CURDATE(), INTERVAL 180 DAY)", QueryHelper::dateFutureDays('expiry_date', 180));
    }
}
