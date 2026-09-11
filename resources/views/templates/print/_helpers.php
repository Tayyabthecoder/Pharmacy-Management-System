<?php
if (!function_exists('format_currency')) {
    function format_currency($amount, $settings = null) {
        $currency = !empty($settings['currency']) ? $settings['currency'] : currency_symbol();
        $currency = htmlspecialchars($currency);
        $floatAmount = (float)($amount ?? 0);
        if (strlen($currency) === 3 && preg_match('/^[A-Z]{3}$/i', $currency)) {
            return number_format($floatAmount, 2) . ' ' . strtoupper($currency);
        }
        return $currency . number_format($floatAmount, 2);
    }
}

if (!function_exists('invoice_display_number')) {
    function invoice_display_number($invoice) {
        if (!empty($invoice['invoice_number'])) {
            return htmlspecialchars($invoice['invoice_number']);
        }
        $prefix = (!empty($invoice['is_return'])) ? 'RET-' : 'INV-';
        return $prefix . str_pad((string)($invoice['id'] ?? 0), 5, '0', STR_PAD_LEFT);
    }
}

require_once BASE_PATH . '/resources/views/templates/print/partials/qrcode.php';
require_once BASE_PATH . '/resources/views/templates/print/partials/status_badge.php';

