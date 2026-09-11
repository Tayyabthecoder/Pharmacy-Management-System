<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Support\Cache;

class CacheTest extends TestCase {

    protected function tearDown(): void {
        Cache::flush();
        parent::tearDown();
    }

    public function testSetAndGetCache(): void {
        $key = 'test_key_' . uniqid();
        $value = ['message' => 'Hello Cache', 'number' => 42];

        $saved = Cache::set($key, $value, 60);
        $this->assertTrue($saved);

        $retrieved = Cache::get($key);
        $this->assertEquals($value, $retrieved);
        $this->assertTrue(Cache::has($key));
    }

    public function testRememberCallsClosureOnlyOnMiss(): void {
        $key = 'remember_key_' . uniqid();
        $counter = 0;

        $first = Cache::remember($key, 60, function() use (&$counter) {
            $counter++;
            return 'calculated_val';
        });

        $this->assertEquals('calculated_val', $first);
        $this->assertEquals(1, $counter);

        // Second call should hit cache without incrementing counter
        $second = Cache::remember($key, 60, function() use (&$counter) {
            $counter++;
            return 'recalculated_val';
        });

        $this->assertEquals('calculated_val', $second);
        $this->assertEquals(1, $counter, "Closure must not be executed on cache hit");
    }

    public function testForgetRemovesKey(): void {
        $key = 'forget_key_' . uniqid();
        Cache::set($key, 'temp_value', 60);
        $this->assertTrue(Cache::has($key));

        Cache::forget($key);
        $this->assertNull(Cache::get($key));
        $this->assertFalse(Cache::has($key));
    }
}
