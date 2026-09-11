<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo invoice_display_number($invoice); ?></title>
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
            color: #334155;
            padding: 40px 20px;
            line-height: 1.5;
        }
        .no-print {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 24px;
            margin-bottom: 30px;
        }
        .company-info h2 {
            font-size: 1.5rem;
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .company-info p {
            font-size: 0.9rem;
            color: #64748b;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h3 {
            font-size: 1.4rem;
            color: #4f46e5;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }
        .invoice-details p {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.6;
        }
        .invoice-details strong {
            color: #334155;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
            color: #334155;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row td {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.05rem;
            border-bottom: 2px solid #cbd5e1;
            background: #f8fafc;
        }
        .balance-row td {
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }
        .footer p {
            margin-bottom: 6px;
        }
        .footer strong {
            color: #475569;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: #000000 !important;
            }
            .total-row td {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: #000000 !important;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
<?php 
$buttonText = 'Print Invoice';
require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
?>

<div class="invoice-box">
    <div class="invoice-header">
        <div class="company-info">
            <?php if (!empty($settings['company_logo'])): ?>
                <img src="<?php echo url($settings['company_logo']); ?>" alt="Pharmacy Logo" style="max-height: 50px; max-width: 180px; object-fit: contain; margin-bottom: 8px;">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo nl2br(htmlspecialchars($settings['company_address'])); ?></p>
            <?php endif; ?>
        </div>
        <div class="invoice-details" style="text-align: right;">
            <h3>INVOICE</h3>
            <p><strong>Invoice #:</strong> <?php echo invoice_display_number($invoice); ?></p>
            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($invoice['created_at'] ?? 'now')); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($invoice['customer_name'] ?? ''); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center" style="width: 15%">Quantity</th>
                <th class="text-right" style="width: 20%">Price</th>
                <th class="text-right" style="width: 25%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></td>
                        <td class="text-center"><?php echo (int)($item['quantity'] ?? 0); ?></td>
                        <td class="text-right"><?php echo format_currency($item['price'], $settings); ?></td>
                        <td class="text-right"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No items found</td>
                </tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td colspan="3" class="text-right">Grand Total:</td>
                <td class="text-right"><?php echo format_currency($invoice['total_amount'] ?? 0, $settings); ?></td>
            </tr>
            
            <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr class="balance-row">
                <td colspan="3" class="text-right">Previous Balance Due:</td>
                <td class="text-right"><?php echo format_currency($invoice['previous_balance'], $settings); ?></td>
            </tr>
            <tr class="balance-row" style="font-weight: 600; color: #0f172a;">
                <td colspan="3" class="text-right">Total Outstanding Balance:</td>
                <td class="text-right"><?php echo format_currency(($invoice['total_amount'] ?? 0) + $invoice['previous_balance'], $settings); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <?php if (!empty($invoice['salesman_name'])): ?>
            <p>Salesperson: <strong><?php echo htmlspecialchars($invoice['salesman_name']); ?></strong></p>
        <?php endif; ?>
        <p><?php echo htmlspecialchars($settings['invoice_notes'] ?? 'Thank you for your business!'); ?></p>
    </div>
</div>

</body>
</html>
