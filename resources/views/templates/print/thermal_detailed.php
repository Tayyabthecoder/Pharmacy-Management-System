<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
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
            padding: 4mm;
            font-size: 11px;
            line-height: 1.4;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #ccc;
            font-family: sans-serif;
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
            border: 1px solid #666;
            background: #f0f0f0;
            color: #333;
        }
        .btn-primary {
            background: #000000;
            color: #ffffff;
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
            margin-bottom: 5mm;
        }
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }
        .header p {
            font-size: 10px;
        }
        
        .divider-double {
            border-top: 2px double #000000;
            margin: 2mm 0;
        }
        .divider-dashed {
            border-top: 1px dashed #000000;
            margin: 2mm 0;
        }
        .stars-divider {
            text-align: center;
            margin: 2.5mm 0;
            font-size: 11px;
            letter-spacing: 3px;
        }
        
        .meta-table, .item-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 10px;
            padding: 0.5mm 0;
        }
        
        .item-table th {
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border-bottom: 1px solid #000000;
            padding: 1.5mm 0;
        }
        .item-table td {
            padding: 2mm 0;
            font-size: 10px;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        
        .totals-table td {
            padding: 1mm 0;
            font-size: 11px;
        }
        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px dashed #000000;
            border-bottom: 2px double #000000;
            padding: 3mm 0;
        }
        
        .policy-note {
            text-align: center;
            font-size: 9px;
            margin-top: 6mm;
            line-height: 1.3;
            padding: 0 2mm;
        }
        .footer {
            text-align: center;
            margin-top: 5mm;
            font-size: 10px;
        }
        
        @media print {
            body {
                width: 80mm;
                padding: 2mm;
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
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
</div>

<div class="receipt">
    <div class="header">
        <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
        <?php if (!empty($settings['company_address'])): ?>
            <p><?php echo htmlspecialchars($settings['company_address']); ?></p>
        <?php endif; ?>
        <p>
            <?php if (!empty($settings['company_phone'])): ?>Ph: <?php echo htmlspecialchars($settings['company_phone']); ?> | <?php endif; ?>
            <?php if (!empty($settings['company_email'])): ?>Email: <?php echo htmlspecialchars($settings['company_email']); ?><?php endif; ?>
        </p>
    </div>

    <div class="divider-double"></div>

    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td>RECEIPT: <?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
                <td class="text-right">CASHIER: <?php echo htmlspecialchars(strtoupper($invoice['salesman_name'] ?? 'SYSTEM')); ?></td>
            </tr>
            <tr>
                <td>DATE: <?php echo date('Y-m-d', strtotime($invoice['created_at'] ?? 'now')); ?></td>
                <td class="text-right">TIME: <?php echo date('H:i:s', strtotime($invoice['created_at'] ?? 'now')); ?></td>
            </tr>
            <tr>
                <td colspan="2">CUSTOMER: <?php echo htmlspecialchars(strtoupper($invoice['customer_name'] ?? 'Walk-in')); ?></td>
            </tr>
        </table>
    </div>

    <div class="divider-dashed"></div>

    <table class="item-table">
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th class="text-center" style="width: 12%">QTY</th>
                <th class="text-right" style="width: 25%">UNIT</th>
                <th class="text-right" style="width: 28%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars(strtoupper($item['product_name'] ?? '')); ?></strong></td>
                        <td class="text-center" style="vertical-align: middle;"><?php echo $item['quantity']; ?></td>
                        <td class="text-right" style="vertical-align: middle;"><?php echo format_currency($item['price'], $settings); ?></td>
                        <td class="text-right" style="vertical-align: middle;"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No items found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="divider-dashed"></div>

    <table class="totals-table">
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
            <td class="text-right"><strong><?php echo format_currency($invoiceTotal, $settings); ?></strong></td>
        </tr>
        
        <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr style="font-size: 10px; border-top: 1px dotted #000;">
                <td>PREV OUTSTANDING:</td>
                <td class="text-right"><?php echo format_currency($invoice['previous_balance'], $settings); ?></td>
            </tr>
            <tr style="font-size: 11px; font-weight: bold; border-bottom: 1px solid #000;">
                <td>NET ACCOUNT BALANCE:</td>
                <td class="text-right"><?php echo format_currency($invoiceTotal + $invoice['previous_balance'], $settings); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="stars-divider">* * * * * * * * * * * * *</div>

    <div class="policy-note">
        <p><strong>RETURN POLICY:</strong></p>
        <p>Items may be returned/exchanged within <?php echo htmlspecialchars($settings['return_policy_days'] ?? '15'); ?> days of purchase in original packaging with this receipt.</p>
        <?php if (!empty($settings['invoice_notes'])): ?>
            <p style="margin-top: 2mm;"><?php echo htmlspecialchars($settings['invoice_notes']); ?></p>
        <?php endif; ?>
    </div>

    <div class="divider-double"></div>

    <div class="footer">
        <p>THANK YOU FOR YOUR PATRONAGE!</p>
        <p>HAVE A WONDERFUL DAY</p>
    </div>
</div>
<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
