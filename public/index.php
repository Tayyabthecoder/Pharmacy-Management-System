<?php
/**
 * public/index.php — Front Controller
 *
 * Single entry point for the entire application.
 * Bootstraps the environment, config, and router.
 */

// Define the application root (one level up from /public)
define('BASE_PATH', dirname(__DIR__));

// Emit HTTP security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header("Content-Security-Policy: default-src 'self'; "
    . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
    . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
    . "img-src 'self' data: https://api.qrserver.com; "
    . "connect-src 'self';");

// Register Composer Autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Bootstrap: loads .env → constants → PDO → session → CSRF token
require_once BASE_PATH . '/config/db.php';

// Resolve the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // e.g. /new_system/public

// The public folder is our entry point, but we want the URL to be clean
$urlRoot = str_replace('\\', '/', $scriptName);
$urlRoot = rtrim($urlRoot, '/');
define('URL_ROOT', $urlRoot);

// Clean URI: Remove the urlRoot (e.g. /new_system/public) or just the prefix (e.g. /new_system)
// if we are using the root .htaccess.
if ($urlRoot !== '' && strpos($uri, $urlRoot) === 0) {
    $uri = substr($uri, strlen($urlRoot));
} else {
    // Check if we are using the root .htaccess (urlRoot minus /public)
    $rootPrefix = str_replace('/public', '', $urlRoot);
    if ($rootPrefix !== '' && strpos($uri, $rootPrefix) === 0) {
        $uri = substr($uri, strlen($rootPrefix));
    }
}

// Default to /login when accessing the root
if ($uri === '' || $uri === '/') {
    $uri = '/login';
}

// Initialize the Router and load route definitions
$router = new \App\Support\Router();
require_once BASE_PATH . '/routes/web.php';

// Dispatch the request
$router->dispatch($uri);
