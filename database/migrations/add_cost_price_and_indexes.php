<?php

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config/db.php';

global $pdo;

try {
    echo "Starting migration: add_cost_price_and_indexes...\n";

    // 1. Add cost_price to invoice_items if missing
    try {
        $pdo->exec("ALTER TABLE invoice_items ADD COLUMN cost_price DECIMAL(10,2) DEFAULT 0.00;");
        echo "Added 'cost_price' column to 'invoice_items'.\n";
    } catch (Exception $e) {
        echo "Note: 'cost_price' column might already exist: " . $e->getMessage() . "\n";
    }

    // 2. Backfill cost_price for existing invoice_items from products table
    try {
        $sqlBackfill = "UPDATE invoice_items 
                        SET cost_price = (
                            SELECT cost_price FROM products WHERE products.id = invoice_items.product_id
                        )
                        WHERE cost_price = 0.00 OR cost_price IS NULL;";
        $affected = $pdo->exec($sqlBackfill);
        echo "Backfilled cost_price for {$affected} existing invoice items.\n";
    } catch (Exception $e) {
        echo "Backfill note: " . $e->getMessage() . "\n";
    }

    // 3. Create database indexes for reporting & lookup performance
    $indexes = [
        'idx_invoices_created_at' => "CREATE INDEX IF NOT EXISTS idx_invoices_created_at ON invoices(created_at);",
        'idx_invoices_user_id'    => "CREATE INDEX IF NOT EXISTS idx_invoices_user_id ON invoices(user_id);",
        'idx_products_category'   => "CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);",
        'idx_products_generic'    => "CREATE INDEX IF NOT EXISTS idx_products_generic ON products(generic_id);",
        'idx_products_expiry'     => "CREATE INDEX IF NOT EXISTS idx_products_expiry ON products(expiry_date);"
    ];

    foreach ($indexes as $indexName => $sql) {
        try {
            $pdo->exec($sql);
            echo "Index '{$indexName}' created successfully.\n";
        } catch (Exception $e) {
            echo "Index '{$indexName}' note: " . $e->getMessage() . "\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
