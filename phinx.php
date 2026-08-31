<?php

// Load .env variables
require_once __DIR__ . '/app/Support/EnvLoader.php';
\App\Support\EnvLoader::load(__DIR__ . '/.env');

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'name' => env('DB_NAME', 'PMS_db'),
            'user' => env('DB_USER', 'root'),
            'pass' => env('DB_PASS', ''),
            'port' => '3306',
            'charset' => 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'
];
