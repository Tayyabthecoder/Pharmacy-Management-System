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
            background: #ffffff;
            color: #1e293b;
            padding: 20px 10px;
            font-size: 12px;
            line-height: 1.4;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 10px;
        }
        
        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            color: #ffffff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-section h1 {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.025em;
        }
        .brand-section p {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-meta h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
        }
        .invoice-meta p {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        .body-content {
            padding: 20px;
        }
        
        .metadata-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .meta-group h3 {
            font-size: 0.8rem;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #00b4db;
            display: inline-block;
            padding-bottom: 2px;
        }
        .meta-item {
            display: flex;
            margin-bottom: 4px;
        }
        .meta-label {
            width: 80px;
            font-weight: 600;
            color: #64748b;
        }
        .meta-value {
            color: #1e293b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #475569;
        }
        th {
            background: #f1f5f9;
            color: #1e293b;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 8px 10px;
            border: 1px solid #475569;
            text-transform: uppercase;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 0.85rem;
            color: #1e293b;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background: #f8fafc;
            font-weight: 700;
        }
        .total-row td {
            font-size: 0.9rem;
            color: #0f172a;
            border-top: 1.5px solid #475569;
        }
        
        .bottom-layout {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 20px;
            align-items: flex-end;
            margin-top: 20px;
        }
        .note-box {
            border-left: 3px solid #00b4db;
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 0 6px 6px 0;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .note-box h4 {
            font-size: 0.75rem;
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .note-box p {
            font-size: 0.75rem;
            color: #475569;
            line-height: 1.4;
            text-align: justify;
        }
        
        .sig-box {
            text-align: right;
        }
        .sig-line {
            width: 160px;
            border-top: 1.2px solid #475569;
            margin-left: auto;
            margin-top: 5px;
            padding-top: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .invoice-container {
                border: 1px solid #000000 !important;
            }
            .invoice-header {
                background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%) !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .total-row {
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
                size: A5;
                margin: 5mm;
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
        </div>
        <div class="invoice-meta" style="text-align: right;">
            <h2>INVOICE</h2>
            <p><strong>No:</strong> <?php echo str_pad($invoice['id'] ?? 10010, 5, '0', STR_PAD_LEFT); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>

    <div class="body-content">
        <div class="metadata-row">
            <div class="meta-group">
                <h3>Customer</h3>
                <div class="meta-item">
                    <div class="meta-label">Name:</div>
                    <div class="meta-value"><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">City:</div>
                    <div class="meta-value">
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
            </div>
            
            <div class="meta-group">
                <h3>Details</h3>
                <div class="meta-item">
                    <div class="meta-label">Sales Man:</div>
                    <div class="meta-value"><?php echo htmlspecialchars($invoice['salesman_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>PRODUCT NAME</th>
                    <th class="text-center" style="width: 15%">QTY</th>
                    <th class="text-right" style="width: 20%">RATE</th>
                    <th class="text-right" style="width: 20%">AMOUNT</th>
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

        <div class="bottom-layout">
            <div class="note-box">
                <h4>Warranty / Note:</h4>
                <p>
                    <?php 
                    if (!empty($settings['invoice_notes'])) {
                        echo nl2br(htmlspecialchars($settings['invoice_notes']));
                    } else {
                        echo "Warranty given under section 23 of Drugs Act 1976.";
                    }
                    ?>
                </p>
            </div>
            
            <div class="sig-box">
                <div class="sig-line">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
