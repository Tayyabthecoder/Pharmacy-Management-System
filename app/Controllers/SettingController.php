<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Setting;

class SettingController {
    protected $settingModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->settingModel = new Setting();
    }

    public function index() {
        $pageTitle = "System Settings";

        // Handle Flash Messages
        $msg = '';
        $msgType = '';
        if (!empty($_SESSION['flash_msg'])) {
            $msg = $_SESSION['flash_msg'];
            $msgType = $_SESSION['flash_type'] ?? 'info';
            unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $allowedKeys = [
                // General Info
                'company_name', 'company_email', 'company_phone', 'company_address', 
                'currency', 'tax_rate', 'company_website', 'company_registration', 
                'timezone', 'language', 'company_logo',

                // Invoices
                'invoice_prefix', 'invoice_due_days', 'invoice_show_tax', 'enable_tax', 
                'invoice_notes', 'invoice_footer', 'invoice_number_format', 'invoice_number_padding', 
                'enable_partial_payments', 'enable_returns', 'return_policy_days', 
                'default_payment_method', 'invoice_paper_size', 'print_template',

                // Notifications
                'notify_low_stock', 'notify_new_invoice', 'notify_restock', 'low_stock_email', 'notify_payment_received', 
                'notify_product_expiry', 'daily_report_email', 'daily_report_time', 'notification_sound',

                // Security
                'session_timeout', 'min_password_length', 'password_min_length', 'max_login_attempts', 
                'lockout_duration', 'require_uppercase', 'require_special_char', 'password_expiry_days', 
                'two_factor_auth', 'ip_whitelist', 'enable_audit_log', 'auto_logout_on_close',

                // Appearance
                'theme_color', 'theme_style', 'records_per_page', 'date_format', 'sidebar_style', 'system_resolution',

                // Backup & Maintenance
                'maintenance_mode', 'maintenance_message',

                // Inventory & Products
                'enable_product_images', 'max_product_image_size', 'allow_negative_stock', 
                'default_restock_qty', 'enable_barcode', 'track_cost_price', 'profit_margin_warning', 
                'inventory_valuation_method',

                // Email & SMTP
                'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 
                'smtp_encryption', 'email_from_name', 'email_from_address',

                // Salesman Controls
                'salesman_can_edit_invoice', 'salesman_can_delete_invoice', 
                'salesman_can_see_cost_price', 'salesman_can_give_discount', 'max_salesman_discount', 
                'require_admin_approval', 'approval_threshold_amount'
            ];

            try {
                // List of boolean toggle checkboxes that emit no value when unchecked
                $toggleKeys = [
                    'invoice_show_tax', 'enable_tax', 'enable_partial_payments', 'enable_returns',
                    'notify_low_stock', 'notify_new_invoice', 'notify_restock', 'notify_payment_received',
                    'notify_product_expiry', 'daily_report_email', 'notification_sound',
                    'require_uppercase', 'require_special_char', 'two_factor_auth', 'enable_audit_log',
                    'auto_logout_on_close', 'maintenance_mode', 'enable_product_images', 'allow_negative_stock',
                    'enable_barcode', 'track_cost_price', 'smtp_enabled',
                    'salesman_can_edit_invoice', 'salesman_can_delete_invoice',
                    'salesman_can_see_cost_price', 'salesman_can_give_discount', 'require_admin_approval'
                ];

                foreach ($toggleKeys as $toggleKey) {
                    if (!isset($_POST[$toggleKey])) {
                        $this->settingModel->updateSetting($toggleKey, '0');
                    }
                }

                // Handle Pharmacy Logo Image Upload
                if (!empty($_POST['remove_company_logo'])) {
                    $this->settingModel->updateSetting('company_logo', '');
                } elseif (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = BASE_PATH . '/public/uploads/settings/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'])) {
                        $fileName = 'logo_' . time() . '.' . $ext;
                        $targetFilePath = $targetDir . $fileName;
                        if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $targetFilePath)) {
                            $this->settingModel->updateSetting('company_logo', 'uploads/settings/' . $fileName);
                        }
                    }
                }

                foreach ($_POST as $key => $value) {
                    if (in_array($key, $allowedKeys)) {
                        $value = trim($value);
                        $this->settingModel->updateSetting($key, $value);
                    }
                }
                
                $msg = "Settings updated successfully!";
                $msgType = "success";
            } catch (Exception $e) {
                $msg = "Error updating settings: " . $e->getMessage();
                $msgType = "danger";
            }
        }

        // Fetch Settings and Backup Files
        $settings = $this->settingModel->getAll();
        $backupFiles = \App\Controllers\BackupController::getBackupFiles();

        require_once BASE_PATH . '/resources/views/admin/settings.php';
    }
}

