<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
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
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
        }
        .btn-primary:hover {
            background: #1e293b;
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
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .invoice-header-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .brand-section h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.025em;
        }
        .brand-section p {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 2px;
        }
        .header-meta {
            text-align: right;
        }
        .header-meta h2 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #38bdf8;
            margin-bottom: 6px;
        }
        .header-meta p {
            font-size: 0.95rem;
            color: #94a3b8;
        }
        .invoice-body {
            padding: 40px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .details-block h4 {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .details-block p {
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.6;
        }
        .details-block strong {
            color: #0f172a;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #334155;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .summary-table {
            width: 350px;
            margin: 0;
        }
        .summary-table td {
            padding: 10px 12px;
            border: none;
            font-size: 0.9rem;
        }
        .summary-table tr:not(:last-child) td {
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-table .grand-total-row td {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            background: #f8fafc;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }
        .footer-note {
            background: #f8fafc;
            border-left: 4px solid #0f172a;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 35px;
        }
        .footer-note h5 {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 6px;
        }
        .footer-note p {
            font-size: 0.85rem;
            color: #64748b;
        }
        .invoice-footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
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
            .invoice-box {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                border-radius: 0;
            }
            .invoice-header-bar {
                background: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .header-meta h2 {
                color: #0284c7 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            th {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-table .grand-total-row td {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            thead {
                display: table-header-group;
            }
            tr {
                page-break-inside: avoid;
            }
            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>
<?php 
$buttonText = 'Print Invoice';
require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
?>

<div class="invoice-box">
    <div class="invoice-header-bar">
        <div class="brand-section">
            <h1><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h1>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo htmlspecialchars(str_replace("\n", ", ", $settings['company_address'])); ?></p>
            <?php endif; ?>
            <p>
                <?php if (!empty($settings['company_phone'])): ?>Phone: <?php echo htmlspecialchars($settings['company_phone']); ?> | <?php endif; ?>
                <?php if (!empty($settings['company_email'])): ?>Email: <?php echo htmlspecialchars($settings['company_email']); ?><?php endif; ?>
            </p>
        </div>
        <div class="header-meta" style="text-align: right;">
            <h2>COMMERCIAL INVOICE</h2>
            <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-') . str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>

    <div class="invoice-body">
        <div class="details-grid">
            <div class="details-block">
                <h4>Customer Details</h4>
                <p>
                    <strong><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in Customer'); ?></strong><br>
                    <?php if (!empty($invoice['customer_address'])): ?>
                        Address: <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?><br>
                    <?php endif; ?>
                    <?php if (!empty($invoice['customer_phone'])): ?>
                        Phone: <?php echo htmlspecialchars($invoice['customer_phone']); ?><br>
                    <?php endif; ?>
                    <?php if (!empty($invoice['customer_email'])): ?>
                        Email: <?php echo htmlspecialchars($invoice['customer_email']); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="details-block">
                <h4>Sales Representative</h4>
                <p>
                    <strong>Name:</strong> <?php echo htmlspecialchars($invoice['salesman_name'] ?? 'N/A'); ?><br>
                    <?php if (!empty($invoice['salesman_email'])): ?>
                        <strong>Email:</strong> <?php echo htmlspecialchars($invoice['salesman_email']); ?><br>
                    <?php endif; ?>
                    <strong>Role:</strong> Sales Representative
                </p>
            </div>

            <div class="details-block">
                <h4>Statement Meta</h4>
                <p>
                    <strong>Date Issued:</strong> <?php echo date('M d, Y', strtotime($invoice['created_at'] ?? 'now')); ?><br>
                    <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime(($invoice['created_at'] ?? 'now') . ' + ' . ($settings['invoice_due_days'] ?? 30) . ' days')); ?><br>
                    <strong>Payment Method:</strong> <span class="badge"><?php echo strtoupper(htmlspecialchars($settings['default_payment_method'] ?? 'CASH')); ?></span>
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th>Product / Service</th>
                    <th class="text-center" style="width: 15%">Quantity</th>
                    <th class="text-right" style="width: 20%">Unit Price</th>
                    <?php if (!empty($settings['invoice_show_tax'])): ?>
                        <th class="text-right" style="width: 15%">Tax</th>
                    <?php endif; ?>
                    <th class="text-right" style="width: 20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): $i = 1; ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></strong></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-right"><?php echo format_currency($item['price'], $settings); ?></td>
                            <?php if (!empty($settings['invoice_show_tax'])): ?>
                                <td class="text-right"><?php echo ($settings['tax_rate'] ?? 0) . '%'; ?></td>
                            <?php endif; ?>
                            <td class="text-right"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo !empty($settings['invoice_show_tax']) ? 6 : 5; ?>" class="text-center">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="summary-wrapper">
            <table class="summary-table">
                <?php 
                $subtotal = 0;
                if (!empty($items)) {
                    foreach ($items as $item) {
                        $subtotal += $item['subtotal'];
                    }
                }
                $taxAmount = $invoice['tax_amount'] ?? 0;
                if ($taxAmount <= 0 && !empty($settings['invoice_show_tax']) && isset($settings['tax_rate'])) {
                    $taxAmount = $subtotal * ((float)$settings['tax_rate'] / 100);
                }
                $invoiceTotal = $invoice['total_amount'] ?? $subtotal;
                $prevBalance = $invoice['previous_balance'] ?? 0;
                $netOutstanding = $invoiceTotal + $prevBalance;
                ?>
                <tr>
                    <td class="text-right">Subtotal:</td>
                    <td class="text-right"><?php echo format_currency($subtotal, $settings); ?></td>
                </tr>
                <?php if (!empty($settings['invoice_show_tax']) || $taxAmount > 0): ?>
                    <tr>
                        <td class="text-right">Tax (<?php echo ($settings['tax_rate'] ?? 0) . '%'; ?>):</td>
                        <td class="text-right"><?php echo format_currency($taxAmount, $settings); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="grand-total-row">
                    <td class="text-right">Grand Total:</td>
                    <td class="text-right"><?php echo format_currency($invoiceTotal, $settings); ?></td>
                </tr>
                <tr>
                    <td class="text-right" style="color: #64748b;">Previous Balance:</td>
                    <td class="text-right" style="color: #64748b;"><?php echo format_currency($prevBalance, $settings); ?></td>
                </tr>
                <tr style="border-top: 2px solid #0f172a; font-weight: 700; background: #f8fafc; font-size: 1.05rem;">
                    <td class="text-right" style="color: #0f172a;">Total Outstanding Balance:</td>
                    <td class="text-right" style="color: #0f172a;"><?php echo format_currency($netOutstanding, $settings); ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($settings['invoice_notes'])): ?>
            <div class="footer-note">
                <h5>Invoice Terms / Custom Disclaimers</h5>
                <p><?php echo htmlspecialchars($settings['invoice_notes']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($settings['invoice_footer'])): ?>
            <div class="invoice-footer-text">
                <p><?php echo htmlspecialchars($settings['invoice_footer']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
