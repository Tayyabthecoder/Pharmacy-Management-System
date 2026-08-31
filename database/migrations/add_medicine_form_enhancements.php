<?php
$pdo = new PDO('mysql:host=localhost;dbname=PMS_db', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

try {
    // 1. Create generics table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS generics (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Fetch distinct existing generic names
    $stmt = $pdo->query("SELECT DISTINCT generic_name FROM products WHERE generic_name IS NOT NULL AND generic_name != ''");
    $existingGenerics = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Map names to their new IDs
    $genericIdMap = [];
    $insertStmt = $pdo->prepare("INSERT INTO generics (name) VALUES (:name)");
    foreach ($existingGenerics as $genericName) {
        $check = $pdo->prepare("SELECT id FROM generics WHERE name = :name LIMIT 1");
        $check->execute(['name' => $genericName]);
        if ($row = $check->fetch()) {
            $genericIdMap[$genericName] = $row['id'];
        } else {
            $insertStmt->execute(['name' => $genericName]);
            $genericIdMap[$genericName] = $pdo->lastInsertId();
        }
    }

    // 3. Add new columns to products (Ignore if already exist)
    try { $pdo->exec("ALTER TABLE products ADD COLUMN generic_id INT(11) NULL AFTER category_id"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE products ADD COLUMN manufacturer VARCHAR(150) NULL AFTER supplier_id"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE products ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER cost_price"); } catch(Exception $e){}

    $pdo->exec("ALTER TABLE products MODIFY quantity INT(11) NULL DEFAULT 0");

    // 4. Update existing products to point to new generic_id
    $updateStmt = $pdo->prepare("UPDATE products SET generic_id = :generic_id WHERE generic_name = :generic_name");
    foreach ($genericIdMap as $name => $id) {
        $updateStmt->execute(['generic_id' => $id, 'generic_name' => $name]);
    }

    // 5. Drop old generic_name and is_prescription_required columns
    try { $pdo->exec("ALTER TABLE products DROP COLUMN generic_name"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE products DROP COLUMN is_prescription_required"); } catch(Exception $e){}

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

