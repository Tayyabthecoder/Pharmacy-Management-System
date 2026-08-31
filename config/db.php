<?php

/**
 * config/db.php
 *
 * Bootstraps the environment loader, establishes the PDO database connection,
 * configures secure session parameters, and initialises the CSRF token.
 *
 * This file is included once by public/index.php (the Front Controller).
 */

// -----------------------------------------------------------------
// 1. Load Environment Variables
// -----------------------------------------------------------------
(new \App\Support\EnvLoader(
    BASE_PATH . '/.env',
    ['DB_HOST', 'DB_NAME', 'DB_USER']   // DB_PASS may be blank (root with no password)
))->load();

// -----------------------------------------------------------------
// 2. Application Constants (derived from .env)
// -----------------------------------------------------------------
define('APP_NAME',  env('APP_NAME',  'Pharmacy Management System'));
define('APP_ENV',   env('APP_ENV',   'local'));
define('APP_DEBUG', env('APP_DEBUG', true));
define('APP_URL',   env('APP_URL',   'http://localhost'));

define('DB_HOST',   env('DB_HOST',   'localhost'));
define('DB_PORT',   env('DB_PORT',   '3306'));
define('DB_NAME',   env('DB_NAME',   'PMS_db'));
define('DB_USER',   env('DB_USER',   'root'));
define('DB_PASS',   env('DB_PASS',   ''));

define('UPLOAD_MAX_SIZE', (int) env('UPLOAD_MAX_SIZE', 5242880));
define('UPLOAD_PATH',     env('UPLOAD_PATH', 'public/uploads'));
define('FORCE_HTTPS',     env('FORCE_HTTPS', false));

// -----------------------------------------------------------------
// 3. Error Reporting (environment-aware)
// -----------------------------------------------------------------
if (APP_DEBUG && APP_ENV !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// -----------------------------------------------------------------
// 4. HTTPS Redirect (production hardening)
// -----------------------------------------------------------------
if (FORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    $redirectUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    header("Location: $redirectUrl", true, 301);
    exit();
}

// -----------------------------------------------------------------
// 5. Database Connection (PDO)
// -----------------------------------------------------------------
try {
    $dbFile = BASE_PATH . '/database/database.sqlite';
    
    // In production (Desktop app), copy SQLite DB to a writable location 
    // because Program Files / installation directories are read-only.
    if (strpos(BASE_PATH, 'resources' . DIRECTORY_SEPARATOR . 'app') !== false || APP_ENV === 'production') {
        $appData = getenv('APPDATA') ?: sys_get_temp_dir();
        $writableDir = $appData . '/PMS';
        
        if (!is_dir($writableDir)) {
            @mkdir($writableDir, 0777, true);
        }
        
        $writableDbFile = $writableDir . '/database.sqlite';
        
        // Copy pristine DB on first run
        if (!file_exists($writableDbFile) && file_exists($dbFile)) {
            copy($dbFile, $writableDbFile);
        }
        
        // Switch to the writable DB if available
        if (file_exists($writableDbFile)) {
            $dbFile = $writableDbFile;
        }
    }

    $dsn = 'sqlite:' . $dbFile;
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // Enable Write-Ahead Logging (WAL), normal sync, and foreign keys for concurrency & data integrity
    $pdo->exec('PRAGMA journal_mode=WAL;');
    $pdo->exec('PRAGMA synchronous=NORMAL;');
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    error_log('[DB Error] ' . $e->getMessage());
    if (APP_DEBUG) {
        die('<pre style="padding: 20px">[Database Error] ' . htmlspecialchars($e->getMessage()) . '</pre>');
    } else {
        die('A database error occurred. Please contact the administrator.');
    }
}

// -----------------------------------------------------------------
// 6. Secure Session Configuration
// -----------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $sessionLifetime = (int) env('SESSION_LIFETIME', 86400);
    $sessionSecure   = (bool) env('SESSION_SECURE', false);
    $sessionSamesite = env('SESSION_SAMESITE', 'Strict');

    // Ensure session directory exists and is writable
    if (strpos(BASE_PATH, 'resources' . DIRECTORY_SEPARATOR . 'app') !== false || APP_ENV === 'production') {
        $appData = getenv('APPDATA') ?: sys_get_temp_dir();
        $sessionPath = $appData . '/PMS/sessions';
    } else {
        $sessionPath = BASE_PATH . '/database/sessions';
    }
    
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);

    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $sessionSecure || FORCE_HTTPS,
        'httponly' => true,
        'samesite' => $sessionSamesite,
    ]);
    session_start();
}

// -----------------------------------------------------------------
// 7. CSRF Token Generation
// -----------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verify a CSRF token using a timing-safe comparison.
 * Note: Global enforcement is handled by CsrfMiddleware.
 *       This helper remains available for manual checks if needed.
 */
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Retrieve the active currency symbol from the database settings.
 */
function currency_symbol(): string {
    static $symbol = null;
    if ($symbol === null) {
        global $pdo;
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("SELECT meta_value FROM system_settings WHERE meta_key = 'currency' LIMIT 1");
                $stmt->execute();
                $val = $stmt->fetchColumn();
                $symbol = ($val !== false && $val !== '') ? $val : '$';
            } catch (Exception $e) {
                $symbol = '$';
            }
        } else {
            $symbol = '$';
        }
    }
    return $symbol;
}

/**
 * Format a number/price with the system currency symbol.
 */
function format_price($amount): string {
    return currency_symbol() . number_format((float)$amount, 2);
}

/**
 * Generate a full URL by prepending the URL_ROOT.
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return URL_ROOT . '/' . $path;
}

/**
 * Redirect to a path within the application.
 */
function redirect($path) {
    header("Location: " . url($path));
    exit();
}

/**
 * Render pagination HTML components.
 */
function renderPagination(array $pagination, string $baseUrl): string {
    $totalPages = $pagination['total_pages'] ?? $pagination['total'] ?? 1;
    if ($totalPages <= 1) {
        return '';
    }

    $currentPage = $pagination['current_page'] ?? $pagination['current'] ?? 1;
    $totalRecords = $pagination['total_records'] ?? 0;
    $perPage = $pagination['per_page'] ?? 15;

    $parts = parse_url($baseUrl);
    $queryParams = [];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $queryParams);
    }
    unset($queryParams['page']);

    $buildUrl = function($pageNum) use ($parts, $queryParams) {
        $params = $queryParams;
        $params['page'] = $pageNum;
        $query = http_build_query($params);
        $path = $parts['path'] ?? '';
        return $path . '?' . $query;
    };

    $html = '<div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 10px 0; flex-wrap: wrap; gap: 10px">';
    $html .= '<div class="pagination-info" style="font-size: 0.9rem">';
    
    $startItem = $totalRecords > 0 ? min($totalRecords, ($currentPage - 1) * $perPage + 1) : (($currentPage - 1) * $perPage + 1);
    $endItem = $totalRecords > 0 ? min($totalRecords, $currentPage * $perPage) : ($currentPage * $perPage);
    $totalText = $totalRecords > 0 ? $totalRecords : 'many';
    
    $html .= 'Showing ' . $startItem . ' to ' . $endItem . ' of ' . $totalText . ' entries';
    $html .= '</div>';
    
    $html .= '<ul class="pagination-list" style="display: flex; gap: 6px; list-style: none; margin: 0; padding: 0">';
    
    if ($currentPage > 1) {
        $html .= '<li><a href="' . htmlspecialchars($buildUrl(1)) . '" class="pagination-link" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); text-decoration: none"><i class="fas fa-angle-double-left"></i></a></li>';
        $html .= '<li><a href="' . htmlspecialchars($buildUrl($currentPage - 1)) . '" class="pagination-link" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); text-decoration: none"><i class="fas fa-angle-left"></i></a></li>';
    } else {
        $html .= '<li><span class="pagination-link disabled" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md)"><i class="fas fa-angle-double-left"></i></span></li>';
        $html .= '<li><span class="pagination-link disabled" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md)"><i class="fas fa-angle-left"></i></span></li>';
    }

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    for ($i = $start; $i <= $end; $i++) {
        if ($i === $currentPage) {
            $html .= '<li><span class="pagination-link active" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); font-weight: 700">' . $i . '</span></li>';
        } else {
            $html .= '<li><a href="' . htmlspecialchars($buildUrl($i)) . '" class="pagination-link" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); text-decoration: none">' . $i . '</a></li>';
        }
    }

    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . htmlspecialchars($buildUrl($currentPage + 1)) . '" class="pagination-link" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); text-decoration: none"><i class="fas fa-angle-right"></i></a></li>';
        $html .= '<li><a href="' . htmlspecialchars($buildUrl($totalPages)) . '" class="pagination-link" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md); text-decoration: none"><i class="fas fa-angle-double-right"></i></a></li>';
    } else {
        $html .= '<li><span class="pagination-link disabled" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md)"><i class="fas fa-angle-right"></i></span></li>';
        $html .= '<li><span class="pagination-link disabled" style="padding: 8px 12px; border: 1px solid; border-radius: var(--radius-md)"><i class="fas fa-angle-double-right"></i></span></li>';
    }

    $html .= '</ul>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Render a local SVG vector icon
 */
function icon(string $name, string $extraClass = '', string $extraAttrs = ''): string {
    static $svgCache = [];
    $cleanName = trim($name);
    if (!str_starts_with($cleanName, 'fa-') && !str_starts_with($cleanName, 'bi-')) {
        $cleanName = 'fa-' . $cleanName;
    }
    $fileName = $cleanName . '.svg';
    $filePath = BASE_PATH . '/public/assets/icons/svg/' . $fileName;

    if (!isset($svgCache[$fileName])) {
        if (file_exists($filePath)) {
            $svgCache[$fileName] = file_get_contents($filePath);
        } else {
            $svgCache[$fileName] = '';
        }
    }

    $svg = $svgCache[$fileName];
    if ($svg) {
        $classList = trim("local-svg-icon {$cleanName} {$extraClass}");
        if (str_contains($svg, 'class=')) {
            $svg = preg_replace('/class=["\']([^"\']*)["\']/', 'class="' . htmlspecialchars($classList) . ' $1"', $svg, 1);
        } else {
            $svg = preg_replace('/<svg\s+/', '<svg class="' . htmlspecialchars($classList) . '" ', $svg, 1);
        }
        if ($extraAttrs) {
            $svg = preg_replace('/<svg\s+/', '<svg ' . $extraAttrs . ' ', $svg, 1);
        }
        return $svg;
    }

    return '<i class="' . htmlspecialchars($cleanName . ' ' . $extraClass) . '" ' . $extraAttrs . '></i>';
}
