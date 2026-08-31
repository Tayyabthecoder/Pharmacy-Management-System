<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #fafafa;
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
            background: #0d9488;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);
        }
        .btn-primary:hover {
            background: #0f766e;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            padding: 40px;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .brand-section h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0d9488;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
        }
        .brand-section p {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
        }
        
        .invoice-title-block {
            text-align: right;
        }
        .invoice-title-block h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
        }
        .invoice-title-block p {
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .detail-card h3 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0d9488;
            margin-bottom: 12px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
            display: inline-block;
        }
        .detail-row {
            display: flex;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }
        .detail-label {
            width: 100px;
            font-weight: 500;
            color: #64748b;
        }
        .detail-value {
            color: #1e293b;
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 8px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 16px 8px;
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
        
        .total-row {
            font-weight: 700;
            font-size: 1.05rem;
        }
        .total-row td {
            border-bottom: 2px solid #0d9488;
            color: #0f172a;
        }
        
        .bottom-section {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin-top: 40px;
            align-items: flex-end;
        }
        
        .note-box {
            border-left: 3px solid #0d9488;
            background: #fafafa;
            padding: 15px 20px;
            border-radius: 0 4px 4px 0;
        }
        .note-box h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0d9488;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .note-box p {
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.5;
            text-align: justify;
        }
        
        .sig-box {
            text-align: right;
        }
        .sig-line {
            width: 200px;
            border-top: 1.2px solid #94a3b8;
            margin-left: auto;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            text-align: center;
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
            .invoice-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                border-radius: 0;
            }
            th {
                border-bottom: 2px solid #000000 !important;
            }
            .total-row td {
                border-bottom: 2px solid #000000 !important;
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

<div class="invoice-container">
    <div class="invoice-header">
        <div class="brand-section">
            <h1><?php echo htmlspecialchars($settings['company_name'] ?? 'Walker Care'); ?></h1>
            <?php if (!empty($settings['company_address'])): ?>
                <p><?php echo htmlspecialchars($settings['company_address']); ?></p>
            <?php endif; ?>
            <?php if (!empty($settings['company_phone'])): ?>
                <p>Phone: <?php echo htmlspecialchars($settings['company_phone']); ?></p>
            <?php endif; ?>
        </div>
        <div class="invoice-title-block" style="text-align: right;">
            <h2>INVOICE</h2>
            <p><strong>#</strong> <?php echo str_pad($invoice['id'] ?? 10010, 5, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($invoice['created_at'] ?? 'now')); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>

    <div class="details-grid">
        <div class="detail-card">
            <h3>Customer Info</h3>
            <div class="detail-row">
                <div class="detail-label">Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">City:</div>
                <div class="detail-value">
                    <?php 
                    if (!empty($invoice['customer_address'])) {
                        $parts = explode(',', $invoice['customer_address']);
                        echo htmlspecialchars(trim(end($parts)));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Customer ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($invoice['customer_id'] ?? 'N/A'); ?></div>
            </div>
        </div>
        
        <div class="detail-card">
            <h3>Billing Details</h3>
            <div class="detail-row">
                <div class="detail-label">Salesperson:</div>
                <div class="detail-value"><?php echo htmlspecialchars($invoice['salesman_name'] ?? 'N/A'); ?></div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%">Product Name</th>
                <th class="text-center" style="width: 12%">Qty</th>
                <th class="text-right" style="width: 14%">Rate</th>
                <th class="text-right" style="width: 14%">Amount</th>
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
                    <td colspan="4" class="text-center" style="padding: 10px;">No items found</td>
                </tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td colspan="3" class="text-right">Total:</td>
                <td class="text-right"><?php echo format_currency($invoice['total_amount'] ?? 0, $settings); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="bottom-section">
        <div class="note-box">
            <h4>Notes & Warranty</h4>
            <p>
                <?php 
                if (!empty($settings['invoice_notes'])) {
                    echo nl2br(htmlspecialchars($settings['invoice_notes']));
                } else {
                    echo "I Mujahid Iqbal Janjua being a person resident in Pakistan carrying on business under the name of Ali Hassan Agency, Shahzad Plaza, Flat No. 01, Floor No. A, China Market Rawalpindi do here by give this warranty under section 23 of Drugs Act 1976.";
                }
                ?>
            </p>
        </div>
        
        <div class="sig-box">
            <div class="sig-line">Authorized Signature</div>
        </div>
    </div>
</div>

</body>
</html>
