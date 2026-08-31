<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 40px 20px;
            line-height: 1.4;
            display: flex;
            justify-content: center;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #0f172a;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1e293b;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 5px;
        }
        
        .receipt-card {
            width: 80mm;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            padding: 6mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5mm;
        }
        .logo-placeholder {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.025em;
            margin-bottom: 1.5mm;
            text-transform: uppercase;
        }
        .header p {
            font-size: 9.5px;
            color: #64748b;
        }
        
        .thick-line {
            border-top: 2px solid #0f172a;
            margin: 3mm 0;
        }
        .thin-line {
            border-top: 1px solid #e2e8f0;
            margin: 2mm 0;
        }
        
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            font-size: 9px;
            color: #475569;
            margin-bottom: 3mm;
        }
        .meta-right {
            text-align: right;
        }
        
        .item-list {
            width: 100%;
            margin-bottom: 3mm;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.5mm 0;
            font-size: 10px;
        }
        .item-row:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }
        .item-name {
            font-weight: 600;
            color: #0f172a;
        }
        .item-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 0.5mm;
        }
        .item-total {
            font-weight: 500;
            color: #0f172a;
            white-space: nowrap;
        }
        
        .totals-section {
            font-size: 10px;
            color: #475569;
        }
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 1mm 0;
        }
        .grand-total {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            padding: 2mm 0;
        }
        
        .barcode-wrapper {
            text-align: center;
            margin-top: 6mm;
            margin-bottom: 2mm;
        }
        .barcode-svg {
            opacity: 0.85;
        }
        
        .footer {
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4mm;
        }
        
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000 !important;
            }
            * {
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                border: none;
                box-shadow: none;
                padding: 0;
                width: 80mm;
                border-radius: 0;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div style="display: flex; flex-direction: column; align-items: center;">
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
        <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div class="logo-placeholder"><?php echo htmlspecialchars($settings['company_name'] ?? 'Boutique POS'); ?></div>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo htmlspecialchars($settings['company_address']); ?></p>
            <?php endif; ?>
            <?php if (!empty($settings['company_phone'])): ?>
                <p>Phone: <?php echo htmlspecialchars($settings['company_phone']); ?></p>
            <?php endif; ?>
        </div>

        <div class="thick-line"></div>

        <div class="meta-grid">
            <div><strong>RECEIPT:</strong> #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></div>
            <div class="meta-right"><strong>DATE:</strong> <?php echo date('d.m.Y', strtotime($invoice['created_at'] ?? 'now')); ?></div>
            <div><strong>STAFF:</strong> <?php echo htmlspecialchars($invoice['salesman_name'] ?? 'System'); ?></div>
            <div class="meta-right"><strong>TIME:</strong> <?php echo date('H:i', strtotime($invoice['created_at'] ?? 'now')); ?></div>
            <div style="grid-column: span 2;"><strong>CUSTOMER:</strong> <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in Customer'); ?></div>
        </div>

        <div class="thick-line"></div>

        <div class="item-list">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div>
                            <div class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></div>
                            <div class="item-meta"><?php echo $item['quantity']; ?> x <?php echo format_currency($item['price'], $settings); ?></div>
                        </div>
                        <div class="item-total"><?php echo format_currency($item['subtotal'], $settings); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="item-row text-center" style="justify-content: center;">
                    No items found
                </div>
            <?php endif; ?>
        </div>

        <div class="thin-line"></div>

        <div class="totals-section">
            <?php 
            $subtotal = 0;
            if (!empty($items)) {
                foreach ($items as $item) {
                    $subtotal += $item['subtotal'];
                }
            }
            $taxAmount = $invoice['tax_amount'] ?? 0;
            $invoiceTotal = $invoice['total_amount'] ?? $subtotal;
            ?>
            <div class="total-item">
                <span>Subtotal</span>
                <span><?php echo format_currency($subtotal, $settings); ?></span>
            </div>
            <?php if (!empty($settings['invoice_show_tax']) && $taxAmount > 0): ?>
                <div class="total-item">
                    <span>Tax (<?php echo ($settings['tax_rate'] ?? 0) . '%'; ?>)</span>
                    <span><?php echo format_currency($taxAmount, $settings); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="thin-line"></div>
            
            <div class="total-item grand-total">
                <span>TOTAL DUE</span>
                <span><?php echo format_currency($invoiceTotal, $settings); ?></span>
            </div>
            
            <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
                <div class="total-item" style="color: #64748b; font-size: 9px;">
                    <span>Account Balance</span>
                    <span><?php echo format_currency($invoice['previous_balance'], $settings); ?></span>
                </div>
                <div class="total-item" style="font-weight: 600; font-size: 10.5px;">
                    <span>Net Outstanding</span>
                    <span><?php echo format_currency($invoiceTotal + $invoice['previous_balance'], $settings); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dynamic QR Code -->
        <div class="qr-wrapper" style="margin: 4mm 0; text-align: center;">
            <?php render_qr_code(url('/invoice/print?id=' . ($invoice['id'] ?? 0)), 80); ?>
        </div>

        <div class="footer">
            <p>Thank you for your visit!</p>
            <?php if (!empty($settings['invoice_notes'])): ?>
                <p style="margin-top: 2mm;"><?php echo htmlspecialchars($settings['invoice_notes']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
