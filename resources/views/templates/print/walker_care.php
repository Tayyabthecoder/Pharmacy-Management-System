<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
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
            color: #1e293b;
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
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2);
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        /* Invoice Container */
        .invoice-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px 16px 12px 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        /* Gradient Header */
        .invoice-header-gradient {
            background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
            color: #ffffff;
            padding: 30px 40px;
            text-align: center;
            border-radius: 16px 16px 0 0;
        }
        .invoice-header-gradient h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .invoice-header-gradient p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 4px;
        }
        .invoice-header-gradient .sales-invoice-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 15px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #ffffff;
        }
        
        .invoice-body {
            padding: 40px;
        }
        
        /* Metadata Section */
        .metadata-section {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .meta-column h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 12px;
            padding-bottom: 4px;
            border-bottom: 2px solid #00b4db;
            display: inline-block;
        }
        
        .meta-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .meta-label {
            width: 110px;
            font-weight: 600;
            color: #475569;
        }
        .meta-value {
            color: #0f172a;
            flex: 1;
        }
        
        /* Product Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #334155;
        }
        th {
            background: #f1f5f9;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 14px;
            border: 1px solid #334155;
            text-align: left;
        }
        td {
            padding: 12px 14px;
            border: 1px solid #334155;
            font-size: 0.9rem;
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
            font-size: 0.95rem;
            color: #0f172a;
        }
        
        /* Bottom Layout */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
            margin-top: 40px;
            align-items: flex-end;
        }
        
        /* Warranty Box */
        .warranty-box {
            border-left: 5px solid #00b4db;
            background: #ffffff;
            padding: 15px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-top: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 0 8px 8px 0;
        }
        .warranty-box h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 8px;
        }
        .warranty-box p {
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.5;
            text-align: justify;
        }
        
        /* Signature Section */
        .signature-box {
            text-align: right;
            padding-right: 10px;
        }
        .signature-line {
            width: 220px;
            border-top: 1.5px solid #475569;
            margin-left: auto;
            margin-top: 10px;
            padding-top: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
        }
        
        /* SVG Signature Accent */
        .sig-svg {
            margin-bottom: -15px;
            margin-right: 35px;
            opacity: 0.85;
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
            .invoice-header-gradient {
                background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%) !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .invoice-header-gradient p {
                color: #ffffff !important;
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
            .metadata-section {
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
<body>
<?php 
$buttonText = 'Print Invoice';
require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
?>

<div class="invoice-container">
    <!-- Gradient Header Area -->
    <div class="invoice-header-gradient">
        <h1><?php echo htmlspecialchars($settings['company_name'] ?? 'WALKER CARE'); ?></h1>
        <?php if (!empty($settings['company_address'])): ?>
            <p><?php echo htmlspecialchars($settings['company_address']); ?></p>
        <?php endif; ?>
        <p>
            Mob: <?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?> 
            <?php if (!empty($settings['company_registration'])): ?>
                | Licence No: <?php echo htmlspecialchars($settings['company_registration']); ?>
            <?php endif; ?>
        </p>
        <div class="sales-invoice-title">SALES INVOICE</div>
        <div style="text-align: center; margin-top: 5px;"><?php render_status_badge($invoice); ?></div>
    </div>

    <div class="invoice-body">
        <!-- Metadata Section (Customer Info & Invoice Details) -->
        <div class="metadata-section">
            <div class="meta-column">
                <h3>Customer Info</h3>
                <div class="meta-row">
                    <div class="meta-label">Name:</div>
                    <div class="meta-value"><?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></div>
                </div>
                <div class="meta-row">
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
                <div class="meta-row">
                    <div class="meta-label">R/G No:</div>
                    <div class="meta-value"><?php echo htmlspecialchars($invoice['customer_id'] ?? 'N/A'); ?></div>
                </div>
            </div>
            
            <div class="meta-column">
                <h3>Invoice Details</h3>
                <div class="meta-row">
                    <div class="meta-label">Invoice No:</div>
                    <div class="meta-value"><?php echo str_pad($invoice['id'] ?? 10010, 5, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Sales Date:</div>
                    <div class="meta-value"><?php echo date('d/m/Y', strtotime($invoice['created_at'] ?? 'now')); ?></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Sales Type:</div>
                    <div class="meta-value">DEFAULT</div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Sales Man:</div>
                    <div class="meta-value"><?php echo htmlspecialchars($invoice['salesman_name'] ?? 'DEFAULT'); ?></div>
                </div>
            </div>
        </div>

        <!-- Product Table -->
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

        <!-- Bottom Warranty & Signature Layout -->
        <div class="bottom-section">
            <div class="warranty-box">
                <h4>Warranty / Note:</h4>
                <p>
                    <?php 
                    if (!empty($settings['invoice_notes'])) {
                        echo nl2br(htmlspecialchars($settings['invoice_notes']));
                    } else {
                        echo "I Mujahid Iqbal Janjua being a person resident in Pakistan carrying on business under the name of Ali Hassan Agency, Shahzad Plaza, Flat No. 01, Floor No. A, China Market Gordon College Road, Rawalpindi do here by give this warranty that the drugs described in this invoice as sold by us do not contravene in any wat the provision of section 23 Drugs Act 1976.\n\n" .
                             "NOTE: THIS WARRANTY DOES NOT APPLY TO AYURVEDIC, UNANI, HOMEOPATHIC, INFANTSUPPLEMENT, BIO-CHEMIC SYSTEM OF MEDICINES CONSUMER AND GENERAL ITEMS ETC, IF ANY, MENTIONED IN THIS INVOICE/CASH MEMO";
                    }
                    ?>
                </p>
            </div>
            
            <div class="signature-box">
                <!-- SVG Signature resembling reference image signature -->
                <svg class="sig-svg" width="140" height="60" viewBox="0 0 140 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 35C22.5 28 35 15 48 10C53.5 7.9 61.5 8 62 13.5C62.5 19 55 35.5 50.5 41C46 46.5 40 48 37 43C34 38 41 21 47 13.5C53 6 62.5 4 67.5 7.5C72.5 11 74 20.5 71.5 27.5C69 34.5 61.5 49 59 52C56.5 55 53 54.5 56.5 46.5C60 38.5 72.5 15.5 80.5 12C88.5 8.5 95 10 93 17.5C91 25 81.5 41.5 86.5 37.5C91.5 33.5 107.5 17 116 14C124.5 11 129 13.5 124.5 21C120 28.5 110.5 35 117.5 29C124.5 23 135 19 132.5 24C130 29 122 34 125 31" stroke="#0f172a" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
