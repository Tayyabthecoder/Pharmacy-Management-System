<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
            background: #ffffff;
            color: #000000;
            width: 58mm;
            padding: 2mm;
            font-size: 9px;
            line-height: 1.3;
        }
        .no-print {
            text-align: center;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #ccc;
            font-family: sans-serif;
            width: 100%;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #666;
            background: #f0f0f0;
            color: #333;
        }
        .btn-primary {
            background: #333;
            color: #fff;
            border-color: #000;
        }
        .btn-secondary {
            margin-left: 5px;
        }
        
        .receipt {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 3mm;
        }
        .header h2 {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0.5mm;
        }
        .dotted-line {
            border-top: 1px dotted #000000;
            margin: 1.5mm 0;
        }
        
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            font-size: 8px;
            padding: 0.3mm 0;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5mm 0;
        }
        .item-table th {
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            border-bottom: 1px dotted #000000;
            padding: 0.8mm 0;
        }
        .item-table td {
            padding: 1mm 0;
            font-size: 8.5px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm;
        }
        .totals-table td {
            padding: 0.8mm 0;
            font-size: 9px;
        }
        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 10px;
            border-top: 1px dotted #000000;
            border-bottom: 1px double #000000;
            padding: 1.5mm 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 5mm;
            font-size: 8px;
        }
        
        @media print {
            body {
                width: 58mm;
                padding: 1mm;
                color: #000000 !important;
            }
            * {
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
</div>

<div class="receipt">
    <div class="header">
        <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
        <?php if (!empty($settings['company_phone'])): ?>
            <p>Ph: <?php echo htmlspecialchars($settings['company_phone']); ?></p>
        <?php endif; ?>
    </div>

    <div class="dotted-line"></div>

    <div class="meta-info">
        <table>
            <tr>
                <td>RCIPT #:</td>
                <td class="text-right"><?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td>DATE:</td>
                <td class="text-right"><?php echo date('Y-m-d H:i', strtotime($invoice['created_at'] ?? 'now')); ?></td>
            </tr>
            <tr>
                <td>CUST:</td>
                <td class="text-right"><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></td>
            </tr>
        </table>
    </div>

    <div class="dotted-line"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="text-center" style="width: 15%">QTY</th>
                <th class="text-right" style="width: 30%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></div>
                            <div style="font-size: 7.5px; color: #333;"><?php echo (int)($item['quantity'] ?? 0); ?>x <?php echo format_currency($item['price'], $settings); ?></div>
                        </td>
                        <td class="text-center" style="vertical-align: middle;"><?php echo (int)($item['quantity'] ?? 0); ?></td>
                        <td class="text-right" style="vertical-align: middle;"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No items found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="dotted-line"></div>

    <table class="totals-table">
        <?php 
        $subtotal = 0;
        if (!empty($items)) {
            foreach ($items as $item) {
                $subtotal += $item['subtotal'];
            }
        }
        $taxAmount = $invoice['tax_amount'] ?? 0;
        ?>
        <tr>
            <td>Subtotal:</td>
            <td class="text-right"><?php echo format_currency($subtotal, $settings); ?></td>
        </tr>
        <?php if (!empty($settings['invoice_show_tax']) && $taxAmount > 0): ?>
            <tr>
                <td>Tax (<?php echo ($settings['tax_rate'] ?? 0) . '%'; ?>):</td>
                <td class="text-right"><?php echo format_currency($taxAmount, $settings); ?></td>
            </tr>
        <?php endif; ?>
        <tr class="grand-total">
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong><?php echo format_currency($invoice['total_amount'] ?? $subtotal, $settings); ?></strong></td>
        </tr>
        <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr style="font-size: 8px;">
                <td>Prev Due:</td>
                <td class="text-right"><?php echo format_currency($invoice['previous_balance'], $settings); ?></td>
            </tr>
            <tr style="font-weight: bold; font-size: 8.5px;">
                <td>Net Due:</td>
                <td class="text-right"><?php echo format_currency(($invoice['total_amount'] ?? $subtotal) + $invoice['previous_balance'], $settings); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="dotted-line"></div>

    <div class="footer">
        <p>Thank you for shopping!</p>
        <p><?php echo date('Y-m-d H:i', strtotime($invoice['created_at'] ?? 'now')); ?></p>
    </div>
</div>
<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
