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

require_once BASE_PATH . '/resources/views/templates/print/partials/qrcode.php';
require_once BASE_PATH . '/resources/views/templates/print/partials/status_badge.php';

