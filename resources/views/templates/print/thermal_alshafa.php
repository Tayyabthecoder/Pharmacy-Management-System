<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlShafa Thermal Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            background: #f1f5f9;
            color: #000000;
            padding: 30px 15px;
            line-height: 1.35;
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
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: #0f172a;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #1e293b;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 5px;
        }

        /* 80mm Thermal Receipt Container */
        .thermal-receipt {
            width: 302px; /* Standard 80mm roll width */
            background: #ffffff;
            color: #000000;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            position: relative;
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            text-align: center;
            line-height: 1.15;
            letter-spacing: -0.3px;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 0.76rem;
            color: #334155;
            text-align: center;
            margin-top: 3px;
            font-weight: 500;
        }
        .header-divider {
            border-bottom: 2px solid #000000;
            margin: 10px 0;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 8px;
            font-size: 0.78rem;
            margin-bottom: 10px;
        }
        .meta-grid div span {
            font-weight: 700;
            color: #000000;
        }
        .section-banner {
            background: #000000 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            text-align: center;
            font-weight: 800;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px;
            margin-bottom: 10px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            margin-bottom: 10px;
        }
        table.items-table th {
            border-bottom: 1.5px solid #000000;
            padding-bottom: 4px;
            text-align: left;
            font-weight: 800;
            font-size: 0.72rem;
            text-transform: uppercase;
        }
        table.items-table td {
            padding: 5px 0;
            border-bottom: 1px dashed #cbd5e1;
            vertical-align: top;
        }
        .num-col { text-align: center; width: 18px; }
        .qty-col { text-align: center; width: 32px; }
        .price-col { text-align: right; width: 45px; }
        .disc-col { text-align: center; width: 35px; }
        .total-col { text-align: right; width: 55px; font-weight: 700; }

        .totals-block {
            border-top: 1.5px solid #000000;
            padding-top: 6px;
            margin-bottom: 12px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            margin-bottom: 3px;
        }
        .totals-row.grand {
            font-size: 0.98rem;
            font-weight: 800;
            border-top: 1.5px solid #000000;
            margin-top: 5px;
            padding-top: 5px;
        }

        .terms-block {
            border: 1px solid #000000;
            padding: 8px;
            border-radius: 2px;
            margin-top: 10px;
        }
        .terms-title {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-bottom: 4px;
        }
        .terms-list {
            list-style: none;
            font-size: 0.65rem;
            line-height: 1.35;
        }
        .terms-list li {
            margin-bottom: 3px;
            padding-left: 8px;
            text-indent: -8px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000 !important;
            }
            body, table, td, th, div, span, p, h1, h2, h3, li {
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .thermal-receipt {
                border: none;
                box-shadow: none;
                padding: 0;
                width: 302px;
                border-radius: 0;
            }
            .section-banner {
                background-color: #000000 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div style="display: flex; flex-direction: column; align-items: center;">
    <?php 
    $buttonText = 'Print AlShafa Receipt';
    require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
    ?>

    <div class="thermal-receipt">
        <!-- Brand Header -->
        <?php if (!empty($settings['company_logo'])): ?>
            <div style="text-align: center; margin-bottom: 6px;">
                <img src="<?php echo url($settings['company_logo']); ?>" alt="Pharmacy Logo" style="max-height: 40px; max-width: 140px; object-fit: contain;">
            </div>
        <?php endif; ?>
        <div class="brand-title"><?php echo htmlspecialchars($settings['company_name'] ?? 'AlShafa Child Care Pharmacy'); ?></div>
        <div class="brand-sub">
            <?php 
            $addr = !empty($settings['company_address']) ? htmlspecialchars($settings['company_address']) : 'Location';
            $phone = !empty($settings['company_phone']) ? htmlspecialchars($settings['company_phone']) : 'Phone number';
            echo $addr . ' · ' . $phone;
            ?>
        </div>
        
        <div class="header-divider"></div>
        
        <!-- Invoice Metadata -->
        <div class="meta-grid">
            <div><span>Patient:</span> <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></div>
            <div style="text-align: right;"><span>Inv No:</span> nv-<?php echo str_pad($invoice['id'] ?? 0, 4, '0', STR_PAD_LEFT); ?></div>
            <div><span>Date:</span> <?php echo date('d/m/Y', strtotime($invoice['created_at'] ?? 'now')); ?></div>
            <div style="text-align: right;"><span>Salesman:</span> <?php echo htmlspecialchars($invoice['salesman_name'] ?? 'Staff'); ?></div>
            
            <?php if (!empty($invoice['doctor_name'])): ?>
                <div style="grid-column: span 2;"><span>Doctor:</span> <?php echo htmlspecialchars($invoice['doctor_name']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Section Banner -->
        <div class="section-banner">AlShafa Sale Invoice</div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th>Medicine</th>
                    <th class="qty-col">Qty</th>
                    <th class="price-col">Price</th>
                    <th class="disc-col">Dis%</th>
                    <th class="total-col">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                $sr = 1;
                ?>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <?php $subtotal += $item['subtotal']; ?>
                        <tr>
                            <td class="num-col"><?php echo $sr++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></strong>
                                <?php if (!empty($item['generic_name'])): ?>
                                    <br><small style="color: #475569; font-style: italic;"><?php echo htmlspecialchars($item['generic_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="qty-col"><?php echo (int)$item['quantity']; ?></td>
                            <td class="price-col"><?php echo (float)$item['price']; ?></td>
                            <td class="disc-col">0</td>
                            <td class="total-col"><?php echo format_currency($item['subtotal'], $settings); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Totals Block -->
        <?php
        $grandTotal = $invoice['total_amount'] ?? $subtotal;
        $discountAmount = 0;
        $taxAmount = 0;
        if ($grandTotal < $subtotal) {
            $discountAmount = $subtotal - $grandTotal;
        } elseif ($grandTotal > $subtotal) {
            $taxAmount = $invoice['tax_amount'] ?? 0;
            if ($taxAmount <= 0) {
                $taxAmount = $grandTotal - $subtotal;
            }
        }
        ?>
        <div class="totals-block">
            <div class="totals-row">
                <span>Net Amount:</span>
                <span><?php echo format_currency($subtotal, $settings); ?></span>
            </div>
            <div class="totals-row">
                <span>Total Discount:</span>
                <span><?php echo format_currency($discountAmount, $settings); ?></span>
            </div>
            <div class="totals-row">
                <span>Tax:</span>
                <span><?php echo format_currency($taxAmount, $settings); ?></span>
            </div>
            <div class="totals-row grand">
                <span>Grand Total:</span>
                <span><?php echo format_currency($grandTotal, $settings); ?></span>
            </div>
            
            <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
                <div class="totals-row" style="margin-top: 4px; color: #334155;">
                    <span>Previous Balance Due:</span>
                    <span><?php echo format_currency($invoice['previous_balance'], $settings); ?></span>
                </div>
                <div class="totals-row" style="font-weight: 800; border-top: 1.5px solid #000; margin-top: 4px; padding-top: 4px;">
                    <span>Total Outstanding:</span>
                    <span><?php echo format_currency($grandTotal + $invoice['previous_balance'], $settings); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="terms-block">
            <div class="terms-title">Terms &amp; Conditions</div>
            <ul class="terms-list">
                <li>1. Medicines can only be returned within 15 days of purchase with original receipt.</li>
                <li>2. Returns are not accepted without a valid invoice.</li>
                <li>3. Please consult a doctor before taking any medication.</li>
                <li>4. Opened medicine packs, liquids, and loose strips cannot be returned.</li>
            </ul>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
