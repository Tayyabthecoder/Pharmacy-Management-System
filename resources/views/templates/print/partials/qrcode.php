<?php
/**
 * Render a dynamic QR code image for print templates.
 * @param string $data The content to encode
 * @param int $size Width/height in pixels
 */
if (!function_exists('render_qr_code')) {
    function render_qr_code($data, $size = 100) {
        $encodedData = urlencode($data);
        $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}&ecc=M";
        echo '<img src="' . $apiUrl . '" alt="QR Code" width="' . $size . '" height="' . $size . '" style="display: block; margin: 0 auto; border: 1px solid #e2e8f0; padding: 2px; background: #fff;" />';
    }
}
?>
