<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ===================================================================
           RESET & BASIC STYLES
           =================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
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

        /* ===================================================================
           THERMAL RECEIPT FRAME (80mm width standard)
           =================================================================== */
        .thermal-receipt {
            width: 302px; /* Standard 80mm roll width */
            background: #ffffff;
            color: #000000;
            padding: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border-radius: 2px;
            position: relative;
        }

        /* -------------------------------------------------------------------
           STYLE 1: MODERNIZED CLASSIC
           ------------------------------------------------------------------- */
        .tpl-modern-classic { font-family: 'Outfit', sans-serif; }
        .tpl-modern-classic .brand-title { font-size: 1.35rem; font-weight: 800; text-align: center; line-height: 1.15; letter-spacing: -0.3px; text-transform: uppercase; }
        .tpl-modern-classic .brand-sub { font-size: 0.78rem; color: #4b5563; text-align: center; margin-top: 3px; }
        .tpl-modern-classic .header-divider { border-bottom: 2px solid #000; margin: 10px 0; }
        .tpl-modern-classic .meta-grid { display: grid; grid-template-columns: 1fr 1.1fr; gap: 6px; font-size: 0.8rem; margin-bottom: 12px; }
        .tpl-modern-classic .meta-grid div span { font-weight: 700; }
        .tpl-modern-classic .section-banner { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; text-align: center; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; padding: 5px; margin-bottom: 12px; }
        .tpl-modern-classic table { width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-bottom: 12px; }
        .tpl-modern-classic th { border-bottom: 1.5px solid #000; padding-bottom: 4px; text-align: left; font-weight: 700; }
        .tpl-modern-classic td { padding: 6px 0; border-bottom: 1px dashed #e5e7eb; }
        .tpl-modern-classic .num-col { text-align: center; width: 22px; }
        .tpl-modern-classic .qty-col { text-align: center; width: 35px; }
        .tpl-modern-classic .price-col { text-align: right; width: 45px; }
        .tpl-modern-classic .disc-col { text-align: center; width: 40px; }
        .tpl-modern-classic .total-col { text-align: right; width: 55px; font-weight: 600; }
        
        .tpl-modern-classic .totals-block { border-top: 1.5px solid #000; padding-top: 8px; margin-bottom: 15px; }
        .tpl-modern-classic .totals-row { display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px; }
        .tpl-modern-classic .totals-row.grand { font-size: 1rem; font-weight: 800; border-top: 1px solid #000; margin-top: 6px; padding-top: 6px; }
        .tpl-modern-classic .terms-block { border: 1px solid #000; padding: 10px; border-radius: 2px; }
        .tpl-modern-classic .terms-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; margin-bottom: 6px; }
        .tpl-modern-classic .terms-list { list-style: none; font-size: 0.68rem; line-height: 1.35; }
        .tpl-modern-classic .terms-list li { margin-bottom: 4px; padding-left: 10px; text-indent: -10px; }

        .qr-wrapper {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        /* ===================================================================
           PRINT STYLES
           ================================================================== */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000 !important;
            }
            body, table, td, th, div, span, p, h1, h2, h3, li {
                color: #000000;
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
            .tpl-modern-classic .section-banner {
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
    $buttonText = 'Print Receipt';
    require BASE_PATH . '/resources/views/templates/print/partials/print_buttons.php'; 
    ?>

    <div class="thermal-receipt tpl-modern-classic">
        <!-- Brand Header -->
        <?php if (!empty($settings['company_logo'])): ?>
            <div style="text-align: center; margin-bottom: 6px;">
                <img src="<?php echo url($settings['company_logo']); ?>" alt="Pharmacy Logo" style="max-height: 40px; max-width: 140px; object-fit: contain;">
            </div>
        <?php endif; ?>
        <?php 
        $compName = (!empty($settings['company_name']) && !in_array(trim($settings['company_name']), ['Pharmacy Name', 'Inventory System', 'My Inventory System', ''])) 
            ? $settings['company_name'] 
            : 'AlShafa Child Care Pharmacy';
        $compAddr = !empty($settings['company_address']) ? $settings['company_address'] : '';
        $compPhone = !empty($settings['company_phone']) ? $settings['company_phone'] : '';
        $contactSub = implode(' · ', array_filter([$compAddr, $compPhone]));
        ?>
        <div class="brand-title"><?php echo htmlspecialchars($compName); ?></div>
        <?php if (!empty($contactSub)): ?>
            <div class="brand-sub"><?php echo htmlspecialchars($contactSub); ?></div>
        <?php endif; ?>
        
        <div class="header-divider"></div>
        
        <!-- Metadata -->
        <div class="meta-grid">
            <div><span>Patient:</span> <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in'); ?></div>
            <div style="text-align: right;"><span>invoice no:</span> nv-<?php echo str_pad($invoice['id'] ?? 0, 4, '0', STR_PAD_LEFT); ?></div>
            <div><span>Date:</span> <?php echo date('d/m/Y', strtotime($invoice['created_at'] ?? 'now')); ?></div>
            <div style="text-align: right;"><span>Salesman:</span> <?php echo htmlspecialchars($invoice['salesman_name'] ?? 'Staff'); ?></div>
            
            <?php if (!empty($invoice['doctor_name'])): ?>
                <div style="grid-column: span 2;"><span>Doctor:</span> <?php echo htmlspecialchars($invoice['doctor_name']); ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Section Banner -->
        <div class="section-banner">Sale Invoice</div>
        
        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th class="num-col">Sr.</th>
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
                            <td><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></td>
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
                <span>Net Amount</span>
                <span><?php echo format_currency($subtotal, $settings); ?></span>
            </div>
            <div class="totals-row">
                <span>Total Discount</span>
                <span><?php echo format_currency($discountAmount, $settings); ?></span>
            </div>
            <div class="totals-row">
                <span>Tax</span>
                <span><?php echo format_currency($taxAmount, $settings); ?></span>
            </div>
            <div class="totals-row grand">
                <span>Grand Total</span>
                <span><?php echo format_currency($grandTotal, $settings); ?></span>
            </div>
            
            <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
                <div class="totals-row" style="margin-top: 4px; color: #4b5563;">
                    <span>Previous Balance Due</span>
                    <span><?php echo format_currency($invoice['previous_balance'], $settings); ?></span>
                </div>
                <div class="totals-row" style="font-weight: 800; border-top: 1px solid #000; margin-top: 4px; padding-top: 4px;">
                    <span>Total Outstanding</span>
                    <span><?php echo format_currency($grandTotal + $invoice['previous_balance'], $settings); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="terms-block">
            <div class="terms-title">Terms &amp; Conditions</div>
            <ul class="terms-list">
                <li>1. Medicines can only be returned within 15 days of purchase.</li>
                <li>2. Returns are not accepted without a valid invoice.</li>
                <li>3. Please consult a doctor before taking any medication.</li>
                <li>4. Opened medicine packs and loose strips cannot be returned.</li>
            </ul>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/resources/views/templates/print/partials/autoprint.php'; ?>
</body>
</html>
