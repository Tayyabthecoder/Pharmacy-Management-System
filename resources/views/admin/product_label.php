<?php
$barcodeValue = !empty($product['barcode']) ? $product['barcode'] : sprintf("890%08d", $product['id']);
$currency = $settings['currency'] ?? 'PKR';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label - <?php echo htmlspecialchars($product['name']); ?></title>
    <!-- JsBarcode for rendering crisp vector SVG barcodes -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-secondary {
            background-color: #64748b;
            color: #ffffff;
        }
        
        /* Barcode Sticker Container (Optimized for 50x30mm thermal label) */
        .label-sticker {
            width: 200px;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 8px 10px;
            text-align: center;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .pharmacy-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .strength-text {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .price-tag {
            font-size: 13px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .barcode-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2px 0;
        }
        .barcode-svg {
            max-width: 100%;
            height: 40px;
        }
        .meta-footer {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #475569;
            font-weight: 600;
            border-top: 1px dotted #e2e8f0;
            padding-top: 3px;
            margin-top: 2px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .controls {
                display: none !important;
            }
            .label-sticker {
                border: none !important;
                box-shadow: none !important;
                page-break-inside: avoid;
                margin: 0 !important;
                padding: 4px !important;
            }
            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Label
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✕ Close
        </button>
    </div>

    <div class="label-sticker">
        <div class="pharmacy-title"><?php echo htmlspecialchars($pharmacyName); ?></div>
        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
        <?php if (!empty($product['strength'])): ?>
            <div class="strength-text"><?php echo htmlspecialchars($product['strength']); ?></div>
        <?php endif; ?>
        
        <div class="price-tag">
            MRP: <?php echo htmlspecialchars($currency); ?> <?php echo number_format($product['price'], 2); ?>
        </div>

        <div class="barcode-container">
            <svg id="barcode" class="barcode-svg"></svg>
        </div>

        <div class="meta-footer">
            <span>B: <?php echo htmlspecialchars($product['batch_number'] ?? 'N/A'); ?></span>
            <span>EXP: <?php echo !empty($product['expiry_date']) ? date('m/y', strtotime($product['expiry_date'])) : 'N/A'; ?></span>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            try {
                JsBarcode("#barcode", "<?php echo addslashes($barcodeValue); ?>", {
                    format: "CODE128",
                    width: 1.5,
                    height: 36,
                    displayValue: true,
                    fontSize: 10,
                    margin: 0,
                    textMargin: 1
                });
            } catch (e) {
                console.error("Barcode generation failed", e);
            }
        });
    </script>
</body>
</html>
