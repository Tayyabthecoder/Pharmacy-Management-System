<?php
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';

try {
    $pdo->exec("ALTER TABLE invoices ADD COLUMN is_return TINYINT(1) DEFAULT 0;");
    echo "Added is_return to invoices.\n";
} catch (Exception $e) {
    echo "Error on invoices: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE receive_invoices ADD COLUMN is_return TINYINT(1) DEFAULT 0;");
    echo "Added is_return to receive_invoices.\n";
} catch (Exception $e) {
    echo "Error on receive_invoices: " . $e->getMessage() . "\n";
}
