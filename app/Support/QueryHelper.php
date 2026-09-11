<?php

namespace App\Support;

use PDO;

/**
 * Class QueryHelper
 *
 * Provides cross-database query dialect helpers supporting both SQLite and MySQL.
 */
class QueryHelper {
    /**
     * Cache driver name in memory for performance.
     */
    private static ?string $driver = null;

    /**
     * Override or reset driver for testing purposes.
     */
    public static function setDriver(?string $driver): void {
        self::$driver = $driver ? strtolower(trim($driver)) : null;
    }

    /**
     * Detect the active database driver ('sqlite' or 'mysql').
     */
    public static function getDriver(?PDO $pdo = null): string {
        if (self::$driver !== null) {
            return self::$driver;
        }

        $envDriver = env('DB_DRIVER');
        if (!empty($envDriver)) {
            self::$driver = strtolower(trim($envDriver));
            return self::$driver;
        }

        $conn = $pdo ?? ($GLOBALS['pdo'] ?? null);
        if ($conn instanceof PDO) {
            try {
                self::$driver = strtolower($conn->getAttribute(PDO::ATTR_DRIVER_NAME));
                return self::$driver;
            } catch (\Exception $e) {
                // Fallback below
            }
        }

        self::$driver = 'sqlite';
        return self::$driver;
    }

    /**
     * Check if active driver is SQLite.
     */
    public static function isSqlite(?PDO $pdo = null): bool {
        return self::getDriver($pdo) === 'sqlite';
    }

    /**
     * Check if active driver is MySQL.
     */
    public static function isMySql(?PDO $pdo = null): bool {
        return self::getDriver($pdo) === 'mysql';
    }

    /**
     * SQL expression for the current date (YYYY-MM-DD).
     */
    public static function dateNow(): string {
        return self::isMySql() ? "CURDATE()" : "date('now')";
    }

    /**
     * SQL expression for current date and time (YYYY-MM-DD HH:MM:SS).
     */
    public static function dateTimeNow(): string {
        return self::isMySql() ? "NOW()" : "strftime('%Y-%m-%d %H:%M:%S', 'now')";
    }

    /**
     * SQL expression for date addition: $column + $amount $unit.
     *
     * @param string $columnOrExpr Column name or expression (e.g. 'created_at', "'now'")
     * @param int|string $amount Numeric count or expression
     * @param string $unit DAY, MONTH, YEAR, HOUR, MINUTE, SECOND
     */
    public static function dateAdd(string $columnOrExpr, int|string $amount, string $unit = 'DAY'): string {
        $cleanUnit = strtoupper(trim($unit));
        if (self::isMySql()) {
            return "DATE_ADD({$columnOrExpr}, INTERVAL {$amount} {$cleanUnit})";
        }
        $pluralUnit = strtolower($cleanUnit) . 's';
        if ($columnOrExpr === "'now'" || $columnOrExpr === "date('now')") {
            return "date('now', '+{$amount} {$pluralUnit}')";
        }
        return "date({$columnOrExpr}, '+{$amount} {$pluralUnit}')";
    }

    /**
     * SQL expression for date subtraction: $column - $amount $unit.
     */
    public static function dateSub(string $columnOrExpr, int|string $amount, string $unit = 'DAY'): string {
        $cleanUnit = strtoupper(trim($unit));
        if (self::isMySql()) {
            return "DATE_SUB({$columnOrExpr}, INTERVAL {$amount} {$cleanUnit})";
        }
        $pluralUnit = strtolower($cleanUnit) . 's';
        if ($columnOrExpr === "'now'" || $columnOrExpr === "date('now')") {
            return "date('now', '-{$amount} {$pluralUnit}')";
        }
        return "date({$columnOrExpr}, '-{$amount} {$pluralUnit}')";
    }

    /**
     * SQL expression for formatting a datetime column.
     * Common formats: '%Y-%m-%d', '%Y-%m', '%Y', '%m', '%H:%M:%S', '%Y-%m-%d %H:%M:%S'.
     */
    public static function dateFormat(string $columnOrExpr, string $format): string {
        if (self::isMySql()) {
            // Convert strftime-like specifiers to MySQL specifiers if needed
            $mysqlFormat = str_replace(
                ['%H:%M:%S', '%H:%M'],
                ['%H:%i:%s', '%H:%i'],
                $format
            );
            return "DATE_FORMAT({$columnOrExpr}, '{$mysqlFormat}')";
        }
        return "strftime('{$format}', {$columnOrExpr})";
    }

    /**
     * SQL expression for string concatenation.
     */
    public static function concat(string ...$parts): string {
        if (empty($parts)) return "''";
        if (self::isMySql()) {
            return "CONCAT(" . implode(', ', $parts) . ")";
        }
        return implode(' || ', $parts);
    }

    /**
     * SQL expression for group concatenation / string aggregation.
     */
    public static function groupConcat(string $columnOrExpr, string $separator = ','): string {
        if (self::isMySql()) {
            return "GROUP_CONCAT({$columnOrExpr} SEPARATOR '{$separator}')";
        }
        return "GROUP_CONCAT({$columnOrExpr}, '{$separator}')";
    }

    /**
     * WHERE condition checking if a datetime column falls on today's date.
     */
    public static function isToday(string $column): string {
        if (self::isMySql()) {
            return "DATE({$column}) = CURDATE()";
        }
        return "date({$column}) = date('now')";
    }

    /**
     * WHERE condition checking if a datetime column falls in the current calendar month.
     */
    public static function isCurrentMonth(string $column): string {
        if (self::isMySql()) {
            return "DATE_FORMAT({$column}, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
        }
        return "strftime('%Y-%m', {$column}) = strftime('%Y-%m', 'now')";
    }

    /**
     * WHERE condition checking if a datetime column is within the last $days days.
     */
    public static function datePastDays(string $column, int|string $days): string {
        if (self::isMySql()) {
            return "{$column} >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)";
        }
        return "{$column} >= date('now', '-{$days} days')";
    }

    /**
     * WHERE condition checking if a datetime column is within the last $months months.
     */
    public static function datePastMonths(string $column, int|string $months): string {
        if (self::isMySql()) {
            return "{$column} >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)";
        }
        return "{$column} >= date('now', '-{$months} months')";
    }

    /**
     * WHERE condition checking if a date column expires within the next $days days.
     */
    public static function dateFutureDays(string $column, int|string $days): string {
        if (self::isMySql()) {
            return "{$column} <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY)";
        }
        return "{$column} <= date('now', '+{$days} days')";
    }

    /**
     * WHERE condition checking if a date column expires within the next $months months.
     */
    public static function dateFutureMonths(string $column, int|string $months): string {
        if (self::isMySql()) {
            return "{$column} <= DATE_ADD(CURDATE(), INTERVAL {$months} MONTH)";
        }
        return "{$column} <= date('now', '+{$months} months')";
    }
}
