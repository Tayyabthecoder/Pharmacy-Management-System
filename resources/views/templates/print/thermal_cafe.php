<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bistro Order #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #fdfbf7;
            color: #292524;
            width: 80mm;
            padding: 4mm;
            font-size: 11px;
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
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #78350f;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #451a03;
        }
        .btn-secondary {
            background: #f5f5f4;
            color: #57534e;
            margin-left: 5px;
            border: 1px solid #e7e5e4;
        }
        
        .receipt-card {
            width: 80mm;
            background: #ffffff;
            border: 1px solid #e7e5e4;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(120, 53, 15, 0.03);
            padding: 6mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 4mm;
        }
        .brand-logo {
            font-size: 18px;
            font-weight: 800;
            color: #78350f;
            margin-bottom: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 9.5px;
            color: #78716c;
        }
        
        .order-number-badge {
            background: #fef3c7;
            border: 1.5px dashed #d97706;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            margin: 3mm 0;
        }
        .order-number-badge span {
            font-size: 9px;
            font-weight: 600;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
        }
        .order-number-badge strong {
            font-size: 18px;
            font-weight: 800;
            color: #78350f;
        }
        
        .divider {
            border-top: 1px dashed #e7e5e4;
            margin: 3mm 0;
        }
        
        .bistro-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            font-size: 9.5px;
            color: #57534e;
            margin-bottom: 2mm;
        }
        .bistro-meta div strong {
            color: #292524;
        }
        .text-right {
            text-align: right;
        }
        
        .item-list {
            margin: 3mm 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2mm 0;
            font-size: 10px;
            border-bottom: 1px solid #f5f5f4;
        }
        .item-qty-name {
            display: flex;
            gap: 8px;
        }
        .item-qty {
            font-weight: 700;
            color: #d97706;
            min-width: 15px;
        }
        .item-name {
            font-weight: 600;
            color: #292524;
        }
        .item-subtotal {
            font-weight: 600;
            color: #292524;
        }
        
        .totals-section {
            font-size: 10px;
            color: #57534e;
            margin-top: 3mm;
        }
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 1mm 0;
        }
        .grand-total {
            font-size: 13px;
            font-weight: 800;
            color: #78350f;
            border-top: 1.5px dashed #78350f;
            border-bottom: 1.5px dashed #78350f;
            padding: 2.5mm 0;
            margin-top: 1mm;
            }
        
        .footer {
            text-align: center;
            font-size: 9px;
            color: #78716c;
            margin-top: 5mm;
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
            .order-number-badge {
                border-color: #000000 !important;
                background: transparent !important;
            }
            .order-number-badge strong, .order-number-badge span {
                color: #000000 !important;
            }
            .grand-total {
                border-color: #000000 !important;
                color: #000000 !important;
            }
            .item-qty {
                color: #000000 !important;
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
        <button onclick="window.print()" class="btn btn-primary">Print Order</button>
        <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div class="brand-logo"><?php echo htmlspecialchars($settings['company_name'] ?? 'BISTRO CAFE'); ?></div>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo htmlspecialchars($settings['company_address']); ?></p>
            <?php endif; ?>
        </div>

        <div class="order-number-badge">
            <span>ORDER NUMBER</span>
            <strong>#<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></strong>
        </div>

        <div class="bistro-meta">
            <div>DATE: <strong><?php echo date('d.m.Y H:i', strtotime($invoice['created_at'] ?? 'now')); ?></strong></div>
            <div class="text-right">SERVER: <strong><?php echo htmlspecialchars($invoice['salesman_name'] ?? 'Self'); ?></strong></div>
            <div style="grid-column: span 2;">CUSTOMER: <strong><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></strong></div>
        </div>

        <div class="divider"></div>

        <div class="item-list">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-qty-name">
                            <span class="item-qty"><?php echo (int)($item['quantity'] ?? 0); ?></span>
                            <span class="item-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></span>
                        </div>
                        <span class="item-subtotal"><?php echo format_currency($item['subtotal'], $settings); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="item-row text-center" style="justify-content: center;">
                    No items found
                </div>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

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
                    <span>GST (<?php echo ($settings['tax_rate'] ?? 0) . '%'; ?>)</span>
                    <span><?php echo format_currency($taxAmount, $settings); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="total-item grand-total">
                <span>TOTAL AMOUNT</span>
                <span><?php echo format_currency($invoiceTotal, $settings); ?></span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for dining with us!</p>
            <?php if (!empty($settings['invoice_notes'])): ?>
                <p style="margin-top: 2mm;"><?php echo htmlspecialchars($settings['invoice_notes']); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
