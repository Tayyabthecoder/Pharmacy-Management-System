<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Middleware\RateLimiter;

class RateLimiterTest extends TestCase {
    protected RateLimiter $limiter;

    protected function setUp(): void {
        parent::setUp();
        $this->limiter = new RateLimiter();
    }

    public function testEndpointPrefixMatching(): void {
        $loginConfig = RateLimiter::getLimitConfig('/login');
        $this->assertEquals(10, $loginConfig['max']);
        $this->assertEquals(300, $loginConfig['window']);

        $autoConfig = RateLimiter::getLimitConfig('/api/products/autocomplete?q=para');
        $this->assertEquals(150, $autoConfig['max']);

        $defaultConfig = RateLimiter::getLimitConfig('/admin/dashboard');
        $this->assertEquals(60, $defaultConfig['max']);
    }

    public function testCheckEndpointAllowsWithinLimits(): void {
        $testIp = '192.168.100.' . rand(1, 250);
        $res = $this->limiter->checkEndpoint('/api/products/scan?barcode=123', $testIp);

        $this->assertTrue($res['allowed']);
        $this->assertEquals(150, $res['limit']);
        $this->assertLessThan(150, $res['remaining']);
    }
}
