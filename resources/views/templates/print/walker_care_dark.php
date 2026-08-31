<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
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
            background: #06b6d4;
            color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.2);
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #0891b2;
        }
        .btn-secondary {
            background: #1e293b;
            color: #94a3b8;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #334155;
            color: #cbd5e1;
        }
        
        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
            border: 1px solid #334155;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #06b6d4;
        }
        .brand-section h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #06b6d4;
            letter-spacing: -0.025em;
            margin-bottom: 6px;
        }
        .brand-section p {
            font-size: 0.85rem;
            color: #94a3b8;
        }
        
        .invoice-meta h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            text-align: right;
        }
        .invoice-meta p {
            font-size: 0.9rem;
            color: #94a3b8;
            text-align: right;
        }
        
        .invoice-body {
            padding: 40px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .detail-card {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 20px;
        }
        .detail-card h3 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #06b6d4;
            margin-bottom: 12px;
            border-bottom: 1px solid #334155;
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
            font-weight: 600;
            color: #94a3b8;
        }
        .detail-value {
            color: #cbd5e1;
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #334155;
        }
        th {
            background: #0f172a;
            color: #06b6d4;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #06b6d4;
            border-right: 1px solid #334155;
            text-align: left;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #334155;
            border-right: 1px solid #334155;
            font-size: 0.9rem;
            color: #cbd5e1;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background: #0f172a;
            font-weight: 700;
        }
        .total-row td {
            color: #06b6d4;
            font-size: 1rem;
        }
        
        .bottom-section {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin-top: 40px;
            align-items: flex-end;
        }
        
        .note-box {
            border-left: 4px solid #06b6d4;
            background: #0f172a;
            padding: 15px 20px;
            border-radius: 0 8px 8px 0;
            border-top: 1px solid #334155;
            border-right: 1px solid #334155;
            border-bottom: 1px solid #334155;
        }
        .note-box h4 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #06b6d4;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .note-box p {
            font-size: 0.8rem;
            color: #94a3b8;
            line-height: 1.5;
            text-align: justify;
        }
        
        .sig-box {
            text-align: right;
        }
        .sig-line {
            width: 200px;
            border-top: 1.5px solid #06b6d4;
            margin-left: auto;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
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
                background: #ffffff;
                background: #ffffff;
            }
            .invoice-header {
                background: #111827 !important;
                color: #ffffff !important;
                border-bottom: 2px solid #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .brand-section h1 {
                color: #ffffff !important;
            }
            .details-grid {
                gap: 20px;
            }
            .detail-card {
                background: #ffffff !important;
                border: 1px solid #000000 !important;
            }
            .detail-card h3 {
                color: #000000 !important;
                border-bottom: 1px solid #000000 !important;
            }
            .detail-value {
                color: #000000 !important;
            }
            .detail-label {
                color: #333333 !important;
            }
            table {
                border: 1px solid #000000 !important;
            }
            th {
                background: #f3f4f6 !important;
                color: #000000 !important;
                border-bottom: 2px solid #000000 !important;
                border-right: 1px solid #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            td {
                color: #000000 !important;
                border-bottom: 1px solid #000000 !important;
                border-right: 1px solid #000000 !important;
            }
            .total-row {
                background: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .total-row td {
                color: #000000 !important;
            }
            .note-box {
                background: #ffffff !important;
                border: 1px solid #000000 !important;
                border-left: 4px solid #000000 !important;
            }
            .note-box h4 {
                color: #000000 !important;
            }
            .note-box p {
                color: #333333 !important;
            }
            .sig-line {
                border-top: 1.5px solid #000000 !important;
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
            <p><strong>Invoice No:</strong> <?php echo str_pad($invoice['id'] ?? 10010, 5, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($invoice['created_at'] ?? 'now')); ?></p>
            <div style="margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
        </div>
    </div>

    <div class="invoice-body">
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
                    <div class="detail-label">R/G No:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($invoice['customer_id'] ?? 'N/A'); ?></div>
                </div>
            </div>
            
            <div class="detail-card">
                <h3>Sales Info</h3>
                <div class="detail-row">
                    <div class="detail-label">Salesperson:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($invoice['salesman_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 55%">PRODUCT NAME</th>
                    <th class="text-center" style="width: 15%">QTY</th>
                    <th class="text-right" style="width: 15%">RATE</th>
                    <th class="text-right" style="width: 15%">AMOUNT</th>
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
                <h4>Warranty / Notes</h4>
                <p>
                    <?php 
                    if (!empty($settings['invoice_notes'])) {
                        echo nl2br(htmlspecialchars($settings['invoice_notes']));
                    } else {
                        echo "I Mujahid Iqbal Janjua being a person resident in Pakistan carrying on business under the name of Ali Hassan Agency do here by give this warranty under section 23 of Drugs Act 1976.";
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
