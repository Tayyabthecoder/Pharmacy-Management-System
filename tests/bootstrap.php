<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('URL_ROOT')) {
    define('URL_ROOT', 'http://localhost/PMS');
}

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config/db.php';

// Ensure $pdo is available in superglobal $GLOBALS
global $pdo;
$GLOBALS['pdo'] = $pdo;

// Register fallback PSR-4 autoloader for Tests namespace
spl_autoload_register(function ($class) {
    $prefix = 'Tests\\';
    $baseDir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
