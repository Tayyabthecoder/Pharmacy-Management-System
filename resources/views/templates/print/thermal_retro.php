<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RETRO RECEIPT #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #ffffff;
            color: #000000;
            width: 80mm;
            padding: 4mm;
            font-size: 11px;
            line-height: 1.3;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1.5px solid #000;
            font-family: sans-serif;
            width: 100%;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 0;
            cursor: pointer;
            text-decoration: none;
            border: 2px solid #000;
            background: #fff;
            color: #000;
            text-transform: uppercase;
        }
        .btn-primary {
            background: #000;
            color: #fff;
        }
        .btn-secondary {
            margin-left: 5px;
        }
        
        .receipt {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 4mm;
            border: 2px solid #000;
            padding: 10px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }
        .header p {
            font-size: 9px;
        }
        
        .line-stars {
            text-align: center;
            margin: 2mm 0;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .line-hash {
            text-align: center;
            margin: 2mm 0;
            font-weight: bold;
            letter-spacing: 1px;
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
            border-bottom: 2px solid #000;
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
            font-size: 10px;
        }
        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 2.5mm 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 6mm;
            font-size: 10px;
            border-top: 2px solid #000;
            padding-top: 4mm;
        }
        
        @media print {
            body {
                width: 80mm;
                padding: 2mm;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                color: #000000 !important;
            }
            * {
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">PRINT RECEIPT</button>
    <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">BACK</a>
</div>

<div class="receipt">
    <div class="header">
        <h2>## <?php echo htmlspecialchars(strtoupper($settings['company_name'] ?? 'RETRO POS')); ?> ##</h2>
        <?php if (!empty($settings['company_address'])): ?>
            <p><?php echo htmlspecialchars(strtoupper($settings['company_address'])); ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['company_phone'])): ?>
            <p>PH: <?php echo htmlspecialchars($settings['company_phone']); ?></p>
        <?php endif; ?>
    </div>

    <div class="line-hash">########################################</div>

    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td>RECEIPT NO:</td>
                <td class="text-right">#<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td>DATE TIME:</td>
                <td class="text-right"><?php echo strtoupper(date('Y-M-d H:i:s', strtotime($invoice['created_at'] ?? 'now'))); ?></td>
            </tr>
            <tr>
                <td>CUSTOMER:</td>
                <td class="text-right"><?php echo htmlspecialchars(strtoupper($invoice['customer_name'] ?? 'Walk-in')); ?></td>
            </tr>
            <?php if (!empty($invoice['salesman_name'])): ?>
                <tr>
                    <td>CASHIER:</td>
                    <td class="text-right"><?php echo htmlspecialchars(strtoupper($invoice['salesman_name'])); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="line-stars">****************************************</div>

    <table class="item-table">
        <thead>
            <tr>
                <th>ITEM DESCRIPTION</th>
                <th class="text-center" style="width: 15%">QTY</th>
                <th class="text-right" style="width: 30%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div><?php echo htmlspecialchars(strtoupper($item['product_name'] ?? '')); ?></div>
                            <div style="font-size: 8px;">@ <?php echo format_currency($item['price'], $settings); ?></div>
                        </td>
                        <td class="text-center" style="vertical-align: middle;"><?php echo $item['quantity']; ?></td>
                        <td class="text-right" style="vertical-align: middle;"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="line-stars">****************************************</div>

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
            <td>TOTAL DUE:</td>
            <td class="text-right"><?php echo format_currency($invoice['total_amount'] ?? $subtotal, $settings); ?></td>
        </tr>
        <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr style="font-size: 9px;">
                <td>PREV BAL:</td>
                <td class="text-right"><?php echo format_currency($invoice['previous_balance'], $settings); ?></td>
            </tr>
            <tr style="font-weight: bold; font-size: 11px; border-top: 1px dotted #000;">
                <td>BALANCE DUE:</td>
                <td class="text-right"><?php echo format_currency(($invoice['total_amount'] ?? $subtotal) + $invoice['previous_balance'], $settings); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="footer">
        <p>*** THANK YOU FOR SHOPPING ***</p>
        <?php if (!empty($settings['invoice_notes'])): ?>
            <p style="margin-top: 2mm;"><?php echo htmlspecialchars(strtoupper($settings['invoice_notes'])); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
