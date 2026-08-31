<?php

namespace App\Middleware;

use Exception;
use PDO;
use PDOException;

/**
 * RateLimiter
 *
 * Database-backed brute-force protection for the login endpoint.
 *
 * Tracks failed login attempts per IP address (and optionally per email).
 * After MAX_ATTEMPTS failures within the decay window, the IP is locked out
 * for DECAY_MINUTES minutes. All expired records are automatically ignored.
 *
 * Config (via .env):
 *   RATE_LIMIT_MAX_ATTEMPTS  — default 5
 *   RATE_LIMIT_DECAY_MINUTES — default 15
 *   RATE_LIMIT_BY_IP         — default true
 */
class RateLimiter {

    protected PDO $pdo;
    protected int    $maxAttempts;
    protected int    $decayMinutes;
    protected bool   $byIp;
    protected string $ipAddress;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        
        $settingModel = new \App\Models\Setting();
        $dbMaxAttempts = $settingModel->get('max_login_attempts');
        $dbDecayMinutes = $settingModel->get('lockout_duration');

        $this->maxAttempts  = $dbMaxAttempts !== null ? (int)$dbMaxAttempts : (int) env('RATE_LIMIT_MAX_ATTEMPTS', 5);
        $this->decayMinutes = $dbDecayMinutes !== null ? (int)$dbDecayMinutes : (int) env('RATE_LIMIT_DECAY_MINUTES', 15);
        $this->byIp         = (bool) env('RATE_LIMIT_BY_IP', true);
        $this->ipAddress    = $this->resolveIp();
    }

    // -----------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------

    /**
     * Record a failed login attempt for the current IP / email.
     */
    public function recordFailure(string $email = ''): void {
        $expiresAt = gmdate('Y-m-d H:i:s', time() + ($this->decayMinutes * 60));

        $stmt = $this->pdo->prepare(
            "INSERT INTO login_attempts (ip_address, email, attempted_at, expires_at)
             VALUES (:ip, :email, strftime('%Y-%m-%d %H:%M:%S', 'now'), :expires)"
        );
        $stmt->execute([
            'ip'      => $this->ipAddress,
            'email'   => strtolower(trim($email)),
            'expires' => $expiresAt,
        ]);

        // Housekeeping: remove expired records older than 24 h to keep table lean
        $this->pruneExpired();
    }

    /**
     * Returns true if the current IP is locked out.
     */
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

        // Check IP lockout remaining time
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

        // Check Email lockout remaining time
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
     * Clear all attempts for the current IP and email (call on successful login).
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
        // Query by IP
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE ip_address = :ip
               AND expires_at > datetime('now')"
        );
        $stmt->execute(['ip' => $this->ipAddress]);
        $ipAttempts = (int) $stmt->fetchColumn();

        // If email is provided, query by email and return the maximum
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
    // Private helpers
    // -----------------------------------------------------------------

    /**
     * Resolve the real client IP, accounting for trusted proxies.
     */
    protected function resolveIp(): string {
        $trusted = env('TRUSTED_PROXIES', false);
        if ($trusted) {
            $headers = [
                'HTTP_CF_CONNECTING_IP',    // Cloudflare
                'HTTP_X_FORWARDED_FOR',     // Standard proxy
                'HTTP_X_REAL_IP',
            ];

            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    // X-Forwarded-For can be a comma-separated list; take first
                    $ip = trim(explode(',', $_SERVER[$header])[0]);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Delete records that expired more than 24 hours ago.
     */
    protected function pruneExpired(): void {
        $this->pdo->exec(
            "DELETE FROM login_attempts
             WHERE expires_at < datetime('now', '-24 hours')"
        );
    }
}
