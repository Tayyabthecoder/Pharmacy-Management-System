<?php
define('BASE_PATH', realpath(__DIR__ . '/../../'));
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';

try {
    // Check if description column exists in products table
    $stmt = $pdo->query("PRAGMA table_info(products)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasDescription = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'description') {
            $hasDescription = true;
            break;
        }
    }

    if (!$hasDescription) {
        echo "The description column is already removed from products table.\n";
        exit(0);
    }

    // Try SQLite 3.35+ DROP COLUMN syntax
    try {
        $pdo->exec("ALTER TABLE products DROP COLUMN description");
        echo "Successfully dropped description column from products table.\n";
    } catch (Exception $e) {
        // Fallback for older SQLite versions: recreate table without description
        $pdo->beginTransaction();
        $pdo->exec("PRAGMA foreign_keys = OFF");

        $pdo->exec("CREATE TABLE products_new (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          category_id INTEGER DEFAULT NULL,
          generic_id INTEGER DEFAULT NULL,
          name varchar(150) NOT NULL,
          strength varchar(50) DEFAULT NULL,
          batch_number varchar(100) DEFAULT NULL,
          expiry_date date DEFAULT NULL,
          company_id INTEGER DEFAULT NULL,
          manufacturer varchar(150) DEFAULT NULL,
          price decimal(10,2) NOT NULL,
          trad_price decimal(10,2) DEFAULT 0.00,
          cost_price decimal(10,2) DEFAULT 0.00,
          quantity INTEGER DEFAULT 0,
          min_stock_level INTEGER DEFAULT 10,
          image varchar(255) DEFAULT NULL,
          created_at timestamp NOT NULL DEFAULT current_timestamp,
          FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
          FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL,
          FOREIGN KEY (generic_id) REFERENCES generics (id) ON DELETE SET NULL
        )");

        $pdo->exec("INSERT INTO products_new (id, category_id, generic_id, name, strength, batch_number, expiry_date, company_id, manufacturer, price, trad_price, cost_price, quantity, min_stock_level, image, created_at)
                    SELECT id, category_id, generic_id, name, strength, batch_number, expiry_date, company_id, manufacturer, price, trad_price, cost_price, quantity, min_stock_level, image, created_at FROM products");

        $pdo->exec("DROP TABLE products");
        $pdo->exec("ALTER TABLE products_new RENAME TO products");

        $pdo->exec("PRAGMA foreign_keys = ON");
        $pdo->commit();

        echo "Successfully recreated products table without description column.\n";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
