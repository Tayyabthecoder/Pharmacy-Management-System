<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
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
            width: 80mm;
            padding: 2mm;
            font-size: 10px;
            line-height: 1.25;
        }
        .no-print {
            text-align: center;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
            font-family: sans-serif;
            width: 100%;
        }
        .btn {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #666;
            background: #f0f0f0;
            color: #333;
        }
        .btn-primary {
            background: #000;
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
            margin-bottom: 2mm;
        }
        .header h2 {
            font-size: 12px;
            font-weight: bold;
        }
        .line-divider {
            border-top: 1px solid #000000;
            margin: 1.5mm 0;
        }
        
        .meta-table, .item-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 9px;
            padding: 0.3mm 0;
        }
        
        .item-table th {
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border-bottom: 1px solid #000000;
            padding: 0.5mm 0;
        }
        .item-table td {
            padding: 1mm 0;
            font-size: 9.5px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        
        .totals-table td {
            padding: 0.5mm 0;
            font-size: 9.5px;
        }
        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 11px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 1.5mm 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 4mm;
            font-size: 8px;
        }
        
        @media print {
            body {
                width: 80mm;
                padding: 0;
                color: #000000 !important;
            }
            * {
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">Print Eco</button>
    <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
</div>

<div class="receipt">
    <div class="header">
        <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
        <?php if (!empty($settings['company_phone'])): ?>
            <p>Ph: <?php echo htmlspecialchars($settings['company_phone']); ?></p>
        <?php endif; ?>
    </div>

    <div class="line-divider"></div>

    <table class="meta-table">
        <tr>
            <td>INV NO: <?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
            <td class="text-right">DATE: <?php echo date('Y-m-d H:i', strtotime($invoice['created_at'] ?? 'now')); ?></td>
        </tr>
        <tr>
            <td>CUST: <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></td>
            <td class="text-right">STAFF: <?php echo htmlspecialchars($invoice['salesman_name'] ?? 'Sys'); ?></td>
        </tr>
    </table>

    <div class="line-divider"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="text-center" style="width: 15%">QTY</th>
                <th class="text-right" style="width: 30%">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></div>
                            <div style="font-size: 8px;"><?php echo (int)($item['quantity'] ?? 0); ?>x <?php echo format_currency($item['price'], $settings); ?></div>
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

    <div class="line-divider"></div>

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
            <td>SUBTOTAL:</td>
            <td class="text-right"><?php echo format_currency($subtotal, $settings); ?></td>
        </tr>
        <?php if (!empty($settings['invoice_show_tax']) && $taxAmount > 0): ?>
            <tr>
                <td>TAX (<?php echo ($settings['tax_rate'] ?? 0) . '%'; ?>):</td>
                <td class="text-right"><?php echo format_currency($taxAmount, $settings); ?></td>
            </tr>
        <?php endif; ?>
        <tr class="grand-total">
            <td><strong>TOTAL DUE:</strong></td>
            <td class="text-right"><strong><?php echo format_currency($invoice['total_amount'] ?? $subtotal, $settings); ?></strong></td>
        </tr>
        <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr style="font-size: 8.5px;">
                <td>PREV BAL:</td>
                <td class="text-right"><?php echo format_currency($invoice['previous_balance'], $settings); ?></td>
            </tr>
            <tr style="font-weight: bold; font-size: 9.5px; border-top: 1px dotted #000;">
                <td>NET DUE:</td>
                <td class="text-right"><?php echo format_currency(($invoice['total_amount'] ?? $subtotal) + $invoice['previous_balance'], $settings); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="line-divider"></div>

    <div class="footer">
        <p>ECO-RECEIPT - THANK YOU</p>
        <?php if (!empty($settings['invoice_notes'])): ?>
            <p><?php echo htmlspecialchars($settings['invoice_notes']); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
