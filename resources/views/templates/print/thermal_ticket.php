<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Voucher #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
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
        }
        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 5px;
        }
        
        .ticket-box {
            width: 80mm;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            padding: 5mm;
            position: relative;
        }
        
        /* Ticket scalloped edges visual effect in browser */
        .ticket-box::before, .ticket-box::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            background: #f1f5f9;
            border-radius: 50%;
            top: 70%;
            border: 2px solid #0f172a;
        }
        .ticket-box::before { left: -9px; }
        .ticket-box::after { right: -9px; }
        
        .header {
            text-align: center;
            margin-bottom: 4mm;
        }
        .header h2 {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header p {
            font-size: 9px;
            color: #64748b;
        }
        
        .thick-divider {
            border-top: 2px solid #0f172a;
            margin: 3mm 0;
        }
        
        .meta-table, .item-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 9px;
            color: #475569;
            padding: 0.5mm 0;
        }
        
        .item-table th {
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            padding: 1mm 0;
        }
        .item-table td {
            padding: 1.5mm 0;
            font-size: 9.5px;
            color: #1e293b;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        
        .totals-table td {
            padding: 0.8mm 0;
            font-size: 10px;
        }
        .totals-table .grand-total td {
            font-weight: bold;
            font-size: 12px;
            color: #4f46e5;
            border-top: 1px dashed #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 2mm 0;
        }
        
        /* Dotted tear off stub line */
        .tear-off-stub {
            border-top: 2.5px dashed #0f172a;
            margin: 6mm 0 3mm 0;
            position: relative;
            text-align: center;
        }
        .tear-off-stub span {
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 8px;
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stub-box {
            text-align: center;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 12px;
            border-radius: 6px;
        }
        .stub-box h4 {
            font-size: 11px;
            color: #4f46e5;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .stub-box p {
            font-size: 8.5px;
            color: #64748b;
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
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .ticket-box {
                border: 2px solid #000000;
                box-shadow: none;
                padding: 0;
                width: 80mm;
                border-radius: 0;
            }
            .ticket-box::before, .ticket-box::after {
                display: none;
            }
            .totals-table .grand-total td {
                color: #000000 !important;
            }
            .stub-box {
                border: 1px solid #000000 !important;
                background: transparent !important;
            }
            .stub-box h4 {
                color: #000000 !important;
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

<div style="display: flex; flex-direction: column; align-items: center;">
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">PRINT VOUCHER</button>
        <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">BACK</a>
    </div>

    <div class="ticket-box">
        <div class="header">
            <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
            <p>VOUCHER & RECEIPT TICKET</p>
        </div>

        <div class="thick-divider"></div>

        <table class="meta-table">
            <tr>
                <td>TICKET ID: #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></td>
                <td class="text-right">DATE: <?php echo date('d.m.Y', strtotime($invoice['created_at'] ?? 'now')); ?></td>
            </tr>
            <tr>
                <td>CUST REF: <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></td>
                <td class="text-right">TYPE: INV-STUB</td>
            </tr>
        </table>

        <div class="thick-divider"></div>

        <table class="item-table">
            <thead>
                <tr>
                    <th>ITEM DESCRIPTION</th>
                    <th class="text-center" style="width: 15%">QTY</th>
                    <th class="text-right" style="width: 30%">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></strong></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-right"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="thick-divider"></div>

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
                    <td>TAX:</td>
                    <td class="text-right"><?php echo format_currency($taxAmount, $settings); ?></td>
                </tr>
            <?php endif; ?>
            <tr class="grand-total">
                <td>GRAND TOTAL:</td>
                <td class="text-right"><?php echo format_currency($invoice['total_amount'] ?? $subtotal, $settings); ?></td>
            </tr>
        </table>

        <!-- Dotted tear off line -->
        <div class="tear-off-stub">
            <span>✂ TEAR OFF STUB</span>
        </div>

        <div class="stub-box">
            <h4>EXCHANGE VOUCHER</h4>
            <p><strong>REFERENCE:</strong> TKT-<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></p>
            <p>Exchange policy valid for <?php echo htmlspecialchars($settings['return_policy_days'] ?? '15'); ?> days with this stub.</p>
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
