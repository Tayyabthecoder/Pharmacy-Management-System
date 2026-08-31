<?php
define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config/db.php';

global $pdo;
try {
    // 1. Add username column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER name");
        echo "Column 'username' added successfully.\n";
    } catch (Exception $e) {
        echo "Note: Column 'username' might already exist: " . $e->getMessage() . "\n";
    }

    // 2. Populate usernames for existing users
    $stmt = $pdo->query("SELECT id, name, email FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");

    foreach ($users as $user) {
        $username = '';
        if ($user['email'] === 'admin@admin.com') {
            $username = 'admin';
        } elseif ($user['email'] === 'Tayyab@Tayyab.com') {
            $username = 'tayyab';
        } else {
            // Clean up name: lowercase, strip spaces, keep alphanumeric
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $user['name']);
            $username = strtolower($cleanName);
            if (empty($username)) {
                // fallback to email prefix
                $username = strtolower(explode('@', $user['email'])[0]);
            }
        }
        
        // Ensure username is unique in database before setting
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
        $checkStmt->execute(['username' => $username, 'id' => $user['id']]);
        $exists = $checkStmt->fetchColumn();
        
        if ($exists > 0) {
            $username = $username . $user['id']; // append ID if duplicate
        }

        $updateStmt->execute(['username' => $username, 'id' => $user['id']]);
        echo "Updated user #{$user['id']} ({$user['name']}) with username: '{$username}'\n";
    }

    // 3. Set username column as NOT NULL and UNIQUE
    $pdo->exec("ALTER TABLE users MODIFY COLUMN username VARCHAR(50) NOT NULL UNIQUE");
    echo "Username column modified to NOT NULL and UNIQUE.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
