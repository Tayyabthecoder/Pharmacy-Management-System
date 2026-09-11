<?php

namespace App\Support;

class Cache {
    protected static ?string $cacheDir = null;

    /**
     * Get or initialize the cache storage directory path.
     */
    protected static function getCacheDir(): string {
        if (self::$cacheDir === null) {
            $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            self::$cacheDir = rtrim($base, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0777, true);
            }
        }
        return self::$cacheDir;
    }

    /**
     * Get the absolute filepath for a given cache key.
     */
    protected static function getFilePath(string $key): string {
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return self::getCacheDir() . DIRECTORY_SEPARATOR . 'cache_' . md5($safeKey) . '.json';
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null): mixed {
        $file = self::getFilePath($key);
        if (!file_exists($file)) {
            return $default;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return $default;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires_at'])) {
            @unlink($file);
            return $default;
        }

        if (time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $data['payload'] ?? $default;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttlSeconds
     * @return bool
     */
    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): bool {
        $file = self::getFilePath($key);
        $data = [
            'key'        => $key,
            'expires_at' => time() + max(1, $ttlSeconds),
            'payload'    => $value,
        ];

        return @file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }

    /**
     * Check if a cache key exists and is not expired.
     */
    public static function has(string $key): bool {
        return self::get($key) !== null;
    }

    /**
     * Get an item from cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param int $ttlSeconds
     * @param callable $callback
     * @return mixed
     */
    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed {
        $val = self::get($key);
        if ($val !== null) {
            return $val;
        }

        $val = $callback();
        self::set($key, $val, $ttlSeconds);
        return $val;
    }

    /**
     * Remove an item from the cache.
     */
    public static function forget(string $key): bool {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Clear all cached files.
     */
    public static function flush(): bool {
        $dir = self::getCacheDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . 'cache_*.json');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        return true;
    }
}
