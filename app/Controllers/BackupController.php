<?php

namespace App\Controllers;

use Exception;
use PDO;

class BackupController {
    protected PDO $db;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * Get backup storage directory path (environment & OS aware)
     */
    public static function getBackupDir(): string {
        // Desktop application / production environment check
        if (strpos(BASE_PATH, 'resources' . DIRECTORY_SEPARATOR . 'app') !== false || (defined('APP_ENV') && APP_ENV === 'production')) {
            $appData = getenv('APPDATA') ?: sys_get_temp_dir();
            $dir = $appData . '/PMS/backups';
        } else {
            $dir = BASE_PATH . '/storage/backups';
        }

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        // Fallback: If directory cannot be created or is not writable, fallback to APPDATA or system temp directory
        if (!is_dir($dir) || !is_writable($dir)) {
            $appData = getenv('APPDATA') ?: sys_get_temp_dir();
            $dir = $appData . '/PMS/backups';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        return $dir;
    }

    /**
     * List all existing backup files
     */
    public static function getBackupFiles(): array {
        $dir = self::getBackupDir();
        $files = glob($dir . '/*.*');
        $backups = [];

        if ($files) {
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['sql', 'sqlite', 'db', 'gz'])) {
                    $backups[] = [
                        'filename'   => basename($file),
                        'filepath'   => $file,
                        'size'       => filesize($file),
                        'created_at' => filemtime($file),
                        'extension'  => $ext,
                    ];
                }
            }
            // Sort by date descending
            usort($backups, function($a, $b) {
                return $b['created_at'] - $a['created_at'];
            });
        }

        return $backups;
    }

    /**
     * Get active database file path
     */
    public static function getActiveDbFile(): string {
        $appData = getenv('APPDATA') ?: sys_get_temp_dir();
        $writableDbFile = $appData . '/PMS/database.sqlite';
        if (file_exists($writableDbFile)) {
            return $writableDbFile;
        }
        return BASE_PATH . '/database/database.sqlite';
    }

    /**
     * Create a manual database backup (SQL Dump or SQLite file copy)
     */
    public function create() {
        // Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_msg'] = "Invalid CSRF security token.";
            $_SESSION['flash_type'] = "danger";
            redirect('/admin/settings');
        }

        try {
            $type = $_POST['backup_format'] ?? 'sql';
            $timestamp = date('Y-m-d_H-i-s');
            $dir = self::getBackupDir();

            if ($type === 'sqlite') {
                $filename = "pms_manual_backup_{$timestamp}.sqlite";
                $targetPath = $dir . '/' . $filename;

                $backupDone = false;
                // Attempt 1: Native SQLite VACUUM INTO (safe for active WAL mode databases & Windows file locks)
                try {
                    $normalizedTargetPath = str_replace('\\', '/', $targetPath);
                    $quotedTarget = $this->db->quote($normalizedTargetPath);
                    $this->db->exec("VACUUM INTO {$quotedTarget}");
                    if (file_exists($targetPath) && filesize($targetPath) > 0) {
                        $backupDone = true;
                    }
                } catch (\Throwable $e) {
                    $backupDone = false;
                }

                // Attempt 2: Fallback to direct file copy
                if (!$backupDone) {
                    $dbFile = self::getActiveDbFile();
                    if (!file_exists($dbFile)) {
                        throw new Exception("Original database file not found.");
                    }
                    if (file_exists($targetPath)) {
                        @unlink($targetPath);
                    }
                    if (!@copy($dbFile, $targetPath)) {
                        $lastErr = error_get_last()['message'] ?? 'Permission denied or destination directory not writable.';
                        throw new Exception("Failed to copy database file: " . $lastErr);
                    }
                }
            } else {
                // SQL Dump Generation
                $filename = "pms_manual_backup_{$timestamp}.sql";
                $targetPath = $dir . '/' . $filename;

                $sqlDump = $this->generateSqlDump();
                if (file_put_contents($targetPath, $sqlDump) === false) {
                    $lastErr = error_get_last()['message'] ?? 'Permission denied or destination directory not writable.';
                    throw new Exception("Failed to write SQL backup file: " . $lastErr);
                }
            }

            // Direct download option if requested via button
            if (!empty($_POST['download_immediately'])) {
                $this->outputFileForDownload($targetPath, $filename);
                exit();
            }

            $_SESSION['flash_msg'] = "Manual backup created successfully: " . $filename;
            $_SESSION['flash_type'] = "success";

        } catch (\Throwable $e) {
            $_SESSION['flash_msg'] = "Backup creation failed: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        redirect('/admin/settings');
    }

    /**
     * Download an existing backup file
     */
    public function download() {
        $filename = basename($_GET['file'] ?? '');
        $filepath = self::getBackupDir() . '/' . $filename;

        if (!empty($filename) && file_exists($filepath)) {
            $this->outputFileForDownload($filepath, $filename);
            exit();
        }

        $_SESSION['flash_msg'] = "Requested backup file does not exist.";
        $_SESSION['flash_type'] = "danger";
        redirect('/admin/settings');
    }

    /**
     * Delete a backup file
     */
    public function delete() {
        // Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_msg'] = "Invalid CSRF security token.";
            $_SESSION['flash_type'] = "danger";
            redirect('/admin/settings');
        }

        $filename = basename($_POST['filename'] ?? '');
        $filepath = self::getBackupDir() . '/' . $filename;

        if (!empty($filename) && file_exists($filepath)) {
            @unlink($filepath);
            $_SESSION['flash_msg'] = "Backup file '{$filename}' deleted successfully.";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "Failed to delete backup file.";
            $_SESSION['flash_type'] = "danger";
        }

        redirect('/admin/settings');
    }

    /**
     * Restore database from backup file
     */
    public function restore() {
        // Verify CSRF
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_msg'] = "Invalid CSRF security token.";
            $_SESSION['flash_type'] = "danger";
            redirect('/admin/settings');
        }

        try {
            $filepath = null;
            $ext = '';

            if (!empty($_FILES['backup_file']['tmp_name'])) {
                $filepath = $_FILES['backup_file']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION));
            } elseif (!empty($_POST['filename'])) {
                $filename = basename($_POST['filename']);
                $filepath = self::getBackupDir() . '/' . $filename;
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            }

            if (!$filepath || !file_exists($filepath)) {
                throw new Exception("No valid backup file selected.");
            }

            if ($ext === 'sql') {
                $sql = file_get_contents($filepath);
                if (empty($sql)) {
                    throw new Exception("Selected SQL backup file is empty.");
                }

                $this->db->exec("PRAGMA foreign_keys = OFF;");
                $this->db->exec($sql);
                $this->db->exec("PRAGMA foreign_keys = ON;");

                $_SESSION['flash_msg'] = "Database successfully restored from SQL backup!";
                $_SESSION['flash_type'] = "success";
            } elseif (in_array($ext, ['sqlite', 'db'])) {
                $dbFile = self::getActiveDbFile();

                // Close active PDO connection and force garbage collection to release file locks on Windows
                unset($this->db);
                global $pdo;
                $pdo = null;
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }

                // Remove active WAL files before restoring SQLite database
                @unlink($dbFile . '-wal');
                @unlink($dbFile . '-shm');

                if (!@copy($filepath, $dbFile)) {
                    $lastErr = error_get_last()['message'] ?? 'Permission denied or active database is locked.';
                    throw new Exception("Failed to overwrite active database file: " . $lastErr);
                }

                $_SESSION['flash_msg'] = "Database successfully restored from SQLite file!";
                $_SESSION['flash_type'] = "success";
            } else {
                throw new Exception("Unsupported file format: .$ext");
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_msg'] = "Database restore failed: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        redirect('/admin/settings');
    }

    /**
     * Generate full SQL schema and insert statements
     */
    private function generateSqlDump(): string {
        $out = "-- ========================================================\n";
        $out .= "-- Pharmacy Management System (PMS) Database Backup\n";
        $out .= "-- Generated Date: " . date('Y-m-d H:i:s') . "\n";
        $out .= "-- ========================================================\n\n";
        $out .= "PRAGMA foreign_keys = OFF;\n\n";

        // Fetch all tables
        $tablesStmt = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $out .= "-- --------------------------------------------------------\n";
            $out .= "-- Table structure for `$table`\n";
            $out .= "-- --------------------------------------------------------\n";
            $out .= "DROP TABLE IF EXISTS `$table`;\n";

            // Table CREATE statement
            $createStmt = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name=" . $this->db->quote($table));
            $createSql = $createStmt->fetchColumn();
            if ($createSql) {
                $out .= $createSql . ";\n\n";
            }

            // Table data INSERTS
            $rowsStmt = $this->db->query("SELECT * FROM `$table`");
            $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rows) {
                $out .= "-- Data dumping for `$table`\n";
                foreach ($rows as $row) {
                    $cols = array_map(fn($c) => "`$c`", array_keys($row));
                    $vals = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return $this->db->quote($val);
                    }, array_values($row));

                    $out .= "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                }
                $out .= "\n";
            }
        }

        $out .= "PRAGMA foreign_keys = ON;\n";
        return $out;
    }

    /**
     * Output file headers for downloading
     */
    private function outputFileForDownload(string $filepath, string $filename) {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
}
