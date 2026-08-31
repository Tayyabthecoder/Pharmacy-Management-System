<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class InvoiceController {
    protected Invoice $invoiceModel;
    protected InvoiceItem $itemModel;
    protected Setting $settingModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin', 'salesman']))->handle();
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->settingModel = new Setting();
    }

    public function print() {
        $invoiceId = (int) ($_GET['id'] ?? 0);

        if (!$invoiceId) {
            die("<div style='padding: 40px; font-family: sans-serif'>Invalid Invoice ID</div>");
        }

        // Fetch Invoice with Salesman & Customer Details using Model
        $userId = ($_SESSION['role'] !== 'admin') ? (int)$_SESSION['user_id'] : null;
        $invoice = $this->invoiceModel->getInvoiceWithDetails($invoiceId, $userId);

        if (!$invoice) {
            die("<div style='padding: 40px; font-family: sans-serif'>Invoice not found or access denied.</div>");
        }

        // Fetch Items with Product Names using Model
        $items = $this->itemModel->getByInvoiceWithProducts($invoiceId);

        // Fetch System Settings (Company Info) using Model
        $settings = $this->settingModel->getAll();

        // Determine template: URL param > system setting > fallback to 'simple'
        $templateName = $_GET['template'] ?? $settings['print_template'] ?? 'simple';
        $allowedTemplates = [
            'professional', 'thermal', 'thermal_alshafa', 'alshafa_thermal', 'alshafa', 'simple', 'detailed', 'statement', 'walker_care',
            'walker_care_modern', 'walker_care_dark', 'walker_care_compact',
            'thermal_58mm', 'thermal_detailed', 'thermal_modern',
            'thermal_retro', 'thermal_ticket', 'thermal_eco', 'thermal_cafe'
        ];
        if (!in_array($templateName, $allowedTemplates)) {
            $templateName = 'simple';
        }

        require_once BASE_PATH . "/resources/views/templates/print/_helpers.php";
        require_once BASE_PATH . "/resources/views/templates/print/{$templateName}.php";
        if (isset($_GET['print'])) {
            echo "<script>window.addEventListener('load', () => window.print());</script>";
        }
    }
}
