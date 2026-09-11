<?php

namespace App\Middleware;

use Exception;
use PDO;
use PDOException;
use App\Support\QueryHelper;

/**
 * RateLimiter
 *
 * Granular, per-endpoint rate limiting & brute-force protection.
 * Supports login attempt tracking and endpoint-specific throughput limits.
 */
class RateLimiter {

    protected PDO $pdo;
    protected int    $maxAttempts;
    protected int    $decayMinutes;
    protected bool   $byIp;
    protected string $ipAddress;

    /**
     * Endpoint-specific rate limiting configurations.
     * Format: 'uri_prefix' => ['max' => int, 'window' => seconds]
     */
    public const ENDPOINT_LIMITS = [
        '/login'                     => ['max' =>  10, 'window' => 300], // Brute-force tight window
        '/api/products/autocomplete' => ['max' => 150, 'window' =>  60], // Fast typing search
        '/api/products/scan'         => ['max' => 150, 'window' =>  60], // Barcode scanner rapid input
        '/api/products/batches'      => ['max' => 120, 'window' =>  60], // Batches lookup
        'default'                    => ['max' =>  60, 'window' =>  60], // General fallback
    ];

    public function __construct(?PDO $pdo = null) {
        if ($pdo === null) {
            global $pdo;
        }
        $this->pdo = $pdo ?? ($GLOBALS['pdo'] ?? (new \App\Models\Product())->getDb());
        
        $settingModel = new \App\Models\Setting();
        $dbMaxAttempts = $settingModel->get('max_login_attempts');
        $dbDecayMinutes = $settingModel->get('lockout_duration');

        $this->maxAttempts  = $dbMaxAttempts !== null ? (int)$dbMaxAttempts : (int) env('RATE_LIMIT_MAX_ATTEMPTS', 5);
        $this->decayMinutes = $dbDecayMinutes !== null ? (int)$dbDecayMinutes : (int) env('RATE_LIMIT_DECAY_MINUTES', 15);
        $this->byIp         = (bool) env('RATE_LIMIT_BY_IP', true);
        $this->ipAddress    = $this->resolveIp();
    }

    // -----------------------------------------------------------------
    // Per-Endpoint Rate Limiting Engine
    // -----------------------------------------------------------------

    /**
     * Resolve configuration for a given URI.
     */
    public static function getLimitConfig(string $uri): array {
        $cleanUri = parse_url($uri, PHP_URL_PATH) ?: $uri;
        
        foreach (self::ENDPOINT_LIMITS as $prefix => $cfg) {
            if ($prefix === 'default') continue;
            if (str_contains($cleanUri, $prefix)) {
                return $cfg;
            }
        }

        return self::ENDPOINT_LIMITS['default'];
    }

    /**
     * Evaluate rate limit for a specific request endpoint.
     *
     * @param string|null $uri
     * @param string|null $ip
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'retry_after' => int]
     */
    public function checkEndpoint(?string $uri = null, ?string $ip = null): array {
        $targetUri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $targetIp  = $ip ?? $this->ipAddress;
        $config    = self::getLimitConfig($targetUri);

        $maxRequests   = $config['max'];
        $windowSeconds = $config['window'];
        $cleanPath     = parse_url($targetUri, PHP_URL_PATH) ?: $targetUri;
        $endpointKey   = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cleanPath), 0, 50);

        // Ensure rate_limits table exists
        $this->ensureRateLimitTable();

        $windowStart = date('Y-m-d H:i:s', time() - $windowSeconds);

        // Query current hits in sliding window
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM endpoint_hits 
             WHERE ip_address = :ip AND endpoint = :endpoint AND hit_at >= :window_start"
        );
        $stmt->execute([
            'ip'           => $targetIp,
            'endpoint'     => $endpointKey,
            'window_start' => $windowStart
        ]);
        $currentHits = (int)$stmt->fetchColumn();

        $allowed = $currentHits < $maxRequests;
        $remaining = max(0, $maxRequests - $currentHits - 1);

        if ($allowed) {
            // Record this hit
            $nowSql = QueryHelper::dateTimeNow();
            $insert = $this->pdo->prepare(
                "INSERT INTO endpoint_hits (ip_address, endpoint, hit_at) 
                 VALUES (:ip, :endpoint, {$nowSql})"
            );
            $insert->execute([
                'ip'       => $targetIp,
                'endpoint' => $endpointKey
            ]);
            $retryAfter = 0;
        } else {
            // Determine retry after seconds from earliest hit
            $earliestStmt = $this->pdo->prepare(
                "SELECT hit_at FROM endpoint_hits 
                 WHERE ip_address = :ip AND endpoint = :endpoint AND hit_at >= :window_start 
                 ORDER BY hit_at ASC LIMIT 1"
            );
            $earliestStmt->execute([
                'ip'           => $targetIp,
                'endpoint'     => $endpointKey,
                'window_start' => $windowStart
            ]);
            $earliestHit = $earliestStmt->fetchColumn();
            $retryAfter = $earliestHit ? max(1, $windowSeconds - (time() - strtotime($earliestHit))) : $windowSeconds;
        }

        // Periodically prune older endpoint hits
        if (mt_rand(1, 100) <= 5) {
            $this->pruneEndpointHits();
        }

        return [
            'allowed'     => $allowed,
            'limit'       => $maxRequests,
            'remaining'   => $remaining,
            'retry_after' => $retryAfter,
            'window'      => $windowSeconds
        ];
    }

    /**
     * Middleware entrypoint: checks limit, attaches headers, returns false & 429 if exceeded.
     */
    public function handle(?string $uri = null): bool {
        $result = $this->checkEndpoint($uri);

        if (!headers_sent()) {
            header('X-RateLimit-Limit: ' . $result['limit']);
            header('X-RateLimit-Remaining: ' . $result['remaining']);
            if (!$result['allowed']) {
                header('Retry-After: ' . $result['retry_after']);
            }
        }

        if (!$result['allowed']) {
            http_response_code(429);
            $targetUri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '');
            if (str_contains($targetUri, '/api/')) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error'       => 'Too Many Requests',
                    'message'     => 'Rate limit exceeded. Please wait before retrying.',
                    'retry_after' => $result['retry_after']
                ]);
            } else {
                echo "<h1>429 - Too Many Requests</h1><p>Rate limit exceeded. Please try again in {$result['retry_after']} seconds.</p>";
            }
            return false;
        }

        return true;
    }

    // -----------------------------------------------------------------
    // Login-Specific Brute Force API (Preserved for backward compatibility)
    // -----------------------------------------------------------------

    /**
     * Record a failed login attempt for the current IP / email.
     */
    public function recordFailure(string $email = ''): void {
        $expiresAt = gmdate('Y-m-d H:i:s', time() + ($this->decayMinutes * 60));
        $nowSql = QueryHelper::dateTimeNow();

        $stmt = $this->pdo->prepare(
            "INSERT INTO login_attempts (ip_address, email, attempted_at, expires_at)
             VALUES (:ip, :email, {$nowSql}, :expires)"
        );
        $stmt->execute([
            'ip'      => $this->ipAddress,
            'email'   => strtolower(trim($email)),
            'expires' => $expiresAt,
        ]);

        $this->pruneExpired();
    }

    /**
     * Returns true if the current IP or email is locked out.
     */
    public function isLocked(string $email = ''): bool {
        return $this->recentAttempts($email) >= $this->maxAttempts;
    }

    /**
     * Seconds remaining until lockout expires, or 0 if not locked.
     */
    public function secondsUntilUnlock(string $email = ''): int {
        $ipRemaining = 0;
        $emailRemaining = 0;

        $stmt = $this->pdo->prepare(
            "SELECT expires_at FROM login_attempts
             WHERE ip_address = :ip
               AND expires_at > datetime('now')
             ORDER BY expires_at ASC"
        );
        $stmt->execute(['ip' => $this->ipAddress]);
        $times = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $count = count($times);
        if ($count >= $this->maxAttempts) {
            $earliest = $times[$count - $this->maxAttempts];
            if ($earliest) {
                $ipRemaining = max(0, strtotime($earliest) - time());
            }
        }

        if (!empty($email)) {
            $stmt = $this->pdo->prepare(
                "SELECT expires_at FROM login_attempts
                 WHERE email = :email
                   AND expires_at > datetime('now')
                 ORDER BY expires_at ASC"
            );
            $stmt->execute(['email' => strtolower(trim($email))]);
            $times = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $count = count($times);
            if ($count >= $this->maxAttempts) {
                $earliest = $times[$count - $this->maxAttempts];
                if ($earliest) {
                    $emailRemaining = max(0, strtotime($earliest) - time());
                }
            }
        }

        return max($ipRemaining, $emailRemaining);
    }

    /**
     * Human-readable "X minutes Y seconds" remaining string.
     */
    public function retryAfterMessage(string $email = ''): string {
        $secs = $this->secondsUntilUnlock($email);
        if ($secs <= 0) return '';

        $mins = (int) ceil($secs / 60);
        return $mins === 1
            ? "Please try again in 1 minute."
            : "Please try again in {$mins} minutes.";
    }

    /**
     * Clear all attempts for the current IP and email.
     */
    public function clearAttempts(string $email = ''): void {
        if (!empty($email)) {
            $stmt = $this->pdo->prepare(
                "DELETE FROM login_attempts WHERE ip_address = :ip OR email = :email"
            );
            $stmt->execute([
                'ip' => $this->ipAddress,
                'email' => strtolower(trim($email))
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                "DELETE FROM login_attempts WHERE ip_address = :ip"
            );
            $stmt->execute(['ip' => $this->ipAddress]);
        }
    }

    /**
     * How many non-expired attempts for this IP/email in the current window.
     */
    public function recentAttempts(string $email = ''): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = :ip
               AND expires_at > datetime('now')"
        );
        $stmt->execute(['ip' => $this->ipAddress]);
        $ipAttempts = (int) $stmt->fetchColumn();

        if (!empty($email)) {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE email = :email
                   AND expires_at > datetime('now')"
            );
            $stmt->execute(['email' => strtolower(trim($email))]);
            $emailAttempts = (int) $stmt->fetchColumn();

            return max($ipAttempts, $emailAttempts);
        }

        return $ipAttempts;
    }

    /**
     * How many attempts remain before lockout.
     */
    public function attemptsRemaining(string $email = ''): int {
        return max(0, $this->maxAttempts - $this->recentAttempts($email));
    }

    // -----------------------------------------------------------------
    // Internal Utilities
    // -----------------------------------------------------------------

    /**
     * Create endpoint_hits table if not already created.
     */
    protected function ensureRateLimitTable(): void {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS endpoint_hits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                endpoint VARCHAR(100) NOT NULL,
                hit_at DATETIME NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_endpoint_hits_lookup ON endpoint_hits (ip_address, endpoint, hit_at);"
        );
    }

    /**
     * Resolve the real client IP.
     */
    protected function resolveIp(): string {
        $trusted = env('TRUSTED_PROXIES', false);
        if ($trusted) {
            $headers = [
                'HTTP_CF_CONNECTING_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_REAL_IP',
            ];

            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ip = trim(explode(',', $_SERVER[$header])[0]);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Delete expired login attempt records.
     */
    protected function pruneExpired(): void {
        $this->pdo->exec(
            "DELETE FROM login_attempts
             WHERE expires_at < datetime('now', '-24 hours')"
        );
    }

    /**
     * Delete endpoint hits older than 1 hour.
     */
    protected function pruneEndpointHits(): void {
        $this->pdo->exec(
            "DELETE FROM endpoint_hits
             WHERE hit_at < datetime('now', '-1 hour')"
        );
    }
}
