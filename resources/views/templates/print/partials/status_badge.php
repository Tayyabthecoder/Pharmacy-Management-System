<?php
/**
 * Render a styled payment status badge.
 * @param array $invoice
 */
if (!function_exists('render_status_badge')) {
    function render_status_badge($invoice) {
        $isPaid = !isset($invoice['previous_balance']) || $invoice['previous_balance'] <= 0;
        $statusText = $isPaid ? 'PAID' : 'UNPAID';
        $bgColor = $isPaid ? '#d1fae5' : '#fee2e2';
        $textColor = $isPaid ? '#065f46' : '#991b1b';
        $borderColor = $isPaid ? '#a7f3d0' : '#fecaca';
        
        echo '<div class="status-badge-container" style="display: inline-block; padding: 4px 12px; font-size: 11px; font-weight: bold; border-radius: 9999px; background: ' . $bgColor . '; color: ' . $textColor . '; border: 1px solid ' . $borderColor . '; text-transform: uppercase; letter-spacing: 0.5px; font-family: sans-serif;">' . $statusText . '</div>';
    }
}
?>
