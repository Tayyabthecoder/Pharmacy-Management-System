<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
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
            background: #111827;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(17, 24, 39, 0.2);
        }
        .btn-primary:hover {
            background: #1f2937;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        .statement-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .statement-header {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: #ffffff;
            padding: 32px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-details h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.025em;
        }
        .company-details p {
            font-size: 0.85rem;
            color: #9ca3af;
        }
        .statement-title {
            text-align: right;
        }
        .statement-title h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 6px;
        }
        .statement-title p {
            font-size: 0.85rem;
            color: #e5e7eb;
        }
        .body-content {
            padding: 40px;
        }
        .customer-card {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .customer-card h4 {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .customer-card p {
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.5;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .stat-card-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .stat-card-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #111827;
        }
        .stat-card-highlight {
            border-top: 3px solid #10b981;
        }
        .stat-card-accent {
            border-top: 3px solid #3b82f6;
        }
        .stat-card-neutral {
            border-top: 3px solid #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f9fafb;
            color: #374151;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.9rem;
            color: #374151;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .amount-due-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 8px;
            padding: 20px;
            text-align: right;
            margin-bottom: 30px;
        }
        .amount-due-box span {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #15803d;
            display: block;
            margin-bottom: 4px;
        }
        .amount-due-box strong {
            font-size: 1.6rem;
            font-weight: 800;
        }
        .footer-note {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px dashed #e5e7eb;
            color: #6b7280;
            font-size: 0.85rem;
            text-align: center;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding: 0 20px;
        }
        .sig-line {
            width: 200px;
            border-top: 1px solid #9ca3af;
            text-align: center;
            padding-top: 6px;
            font-size: 0.8rem;
            color: #6b7280;
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
            .statement-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                border-radius: 0;
            }
            .statement-header {
                background: #111827 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .statement-title h3 {
                color: #38bdf8 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            th {
                background: #f9fafb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .amount-due-box {
                background: #f0fdf4 !important;
                border-color: #bbf7d0 !important;
                color: #166534 !important;
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
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
<?php 
$buttonText = 'Print Statement';
require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
?>

<div class="statement-container">
    <div class="statement-header">
        <div class="company-details">
            <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo htmlspecialchars(str_replace("\n", ", ", $settings['company_address'])); ?></p>
            <?php endif; ?>
        </div>
        <div class="statement-title" style="text-align: right;">
            <h3>ACCOUNT STATEMENT</h3>
            <p><strong>Invoice Reference:</strong> #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Issued:</strong> <?php echo date('M d, Y', strtotime($invoice['created_at'] ?? 'now')); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>

    <div class="body-content">
        <div class="customer-card">
            <h4>Bill To</h4>
            <p>
                <strong><?php echo htmlspecialchars($invoice['customer_name'] ?? ''); ?></strong><br>
                <?php if (!empty($invoice['customer_address'])): ?>
                    Address: <?php echo nl2br(htmlspecialchars($invoice['customer_address'])); ?><br>
                <?php endif; ?>
                <?php if (!empty($invoice['customer_phone'])): ?>
                    Phone: <?php echo htmlspecialchars($invoice['customer_phone']); ?>
                <?php endif; ?>
            </p>
        </div>

        <?php
        $subtotal = 0;
        if (!empty($items)) {
            foreach ($items as $item) {
                $subtotal += $item['subtotal'];
            }
        }
        $taxAmount = $invoice['tax_amount'] ?? 0;
        $invoiceTotal = $invoice['total_amount'] ?? $subtotal;
        $prevBalance = $invoice['previous_balance'] ?? 0;
        $netOutstanding = $invoiceTotal + $prevBalance;
        ?>

        <div class="stats-grid">
            <div class="stat-card stat-card-neutral">
                <div class="stat-card-title">Previous Balance</div>
                <div class="stat-card-value"><?php echo format_currency($prevBalance, $settings); ?></div>
            </div>
            <div class="stat-card stat-card-accent">
                <div class="stat-card-title">Current Charges</div>
                <div class="stat-card-value"><?php echo format_currency($invoiceTotal, $settings); ?></div>
            </div>
            <div class="stat-card stat-card-highlight">
                <div class="stat-card-title">Total Outstanding</div>
                <div class="stat-card-value"><?php echo format_currency($netOutstanding, $settings); ?></div>
            </div>
        </div>

        <h4>Itemized Transactions</h4>
        <table>
            <thead>
                <tr>
                    <th>Item / Description</th>
                    <th class="text-center" style="width: 15%">Quantity</th>
                    <th class="text-right" style="width: 25%">Unit Price</th>
                    <th class="text-right" style="width: 25%">Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></strong></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-right"><?php echo format_currency($item['price'], $settings); ?></td>
                            <td class="text-right"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="amount-due-box">
            <span>Total Outstanding Amount Due</span>
            <strong><?php echo format_currency($netOutstanding, $settings); ?></strong>
        </div>

        <div class="signature-section">
            <div class="sig-line">
                Prepared By
            </div>
            <div class="sig-line">
                Customer Acceptance Signature
            </div>
        </div>

        <div class="footer-note">
            <p><?php echo htmlspecialchars($settings['invoice_notes'] ?? 'Thank you for your business!'); ?></p>
            <?php if (!empty($invoice['salesman_name'])): ?>
                <p style="margin-top: 6px; font-size: 0.8rem;">Representative: <strong><?php echo htmlspecialchars($invoice['salesman_name']); ?></strong></p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
