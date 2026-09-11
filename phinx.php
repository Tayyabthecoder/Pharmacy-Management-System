<?php

// Load .env variables
require_once __DIR__ . '/app/Support/EnvLoader.php';
\App\Support\EnvLoader::load(__DIR__ . '/.env');

$dbDriver = env('DB_DRIVER', 'sqlite');

$environments = [
    'default_migration_table' => 'phinxlog',
    'default_environment' => 'development',
];

if ($dbDriver === 'mysql') {
    $environments['development'] = [
        'adapter' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'name' => env('DB_NAME', 'PMS_db'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'port' => env('DB_PORT', '3306'),
        'charset' => 'utf8mb4',
    ];
} else {
    $environments['development'] = [
        'adapter' => 'sqlite',
        'name' => __DIR__ . '/database/database',
        'suffix' => '.sqlite',
    ];
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => $environments,
    'version_order' => 'creation'
];
