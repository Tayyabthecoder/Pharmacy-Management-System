<?php
/**
 * Render a dynamic QR code image for print templates.
 * @param string $data The content to encode
 * @param int $size Width/height in pixels
 */
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;

if (!function_exists('render_qr_code')) {
    function render_qr_code($data, $size = 100) {
        try {
            if (class_exists(QRCode::class)) {
                $options = new QROptions([
                    'outputInterface' => QRMarkupSVG::class,
                    'outputBase64'    => true,
                    'addQuietzone'    => true,
                ]);
                $src = (new QRCode($options))->render($data);
                echo '<img src="' . $src . '" alt="QR Code" width="' . $size . '" height="' . $size . '" style="display: block; margin: 0 auto; border: 1px solid #e2e8f0; padding: 2px; background: #fff;" />';
                return;
            }
        } catch (\Throwable $e) {
            // Fallback if rendering fails
        }

        // Offline SVG fallback placeholder
        echo '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 100 100" style="display:block;margin:0 auto;border:1px solid #e2e8f0;background:#fff;" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#f8fafc"/><text x="50" y="55" font-family="sans-serif" font-size="11" fill="#64748b" text-anchor="middle">QR Code</text></svg>';
    }
}
?>
