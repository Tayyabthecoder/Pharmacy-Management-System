<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<style>
    @media print {
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .sidebar, .topbar, .no-print, .action-row, header, footer {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .dashboard-container {
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
        }
        .table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        .table th, .table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Action Row (Hidden on print) -->
    <div class="page-header action-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px">
        <div>
            <h1 class="page-title">Receive Invoice Details</h1>
            <p class="text-muted">Detailed overview of stock shipment #<?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
        </div>
        <div style="display: flex; gap: 10px">
            <a href="<?php echo url('/admin/receive_invoices'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Printable Area -->
    <div class="card" style="padding: 30px; margin-bottom: 25px">
        <!-- Brand Header for Print -->
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--surface-border, #ddd); padding-bottom: 20px; margin-bottom: 20px">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; color: var(--primary-color)"><?php echo htmlspecialchars($settings['company_name'] ?? 'Pharmacy Management System'); ?></h2>
                <p class="text-muted" style="margin: 5px 0 0 0; font-size: 0.85rem">Supplier Shipment Intake Ledger</p>
            </div>
            <div style="text-align: right">
                <h2 style="margin: 0; font-size: 1.3rem">RECEIVE INVOICE</h2>
                <h3 style="margin: 5px 0 0 0; font-weight: bold; color: var(--text-color)">#<?php echo htmlspecialchars($invoice['invoice_number']); ?></h3>
            </div>
        </div>

        <!-- Supplier & Shipment details grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px">
            <div>
                <h4 style="margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid var(--surface-border); padding-bottom: 5px; text-transform: uppercase; font-size: 0.85rem" class="text-muted">Supplier Information</h4>
                <strong style="font-size: 1.1rem; display: block; margin-bottom: 5px"><?php echo htmlspecialchars($invoice['supplier_name'] ?? 'Unknown Supplier'); ?></strong>
                <?php if (!empty($invoice['supplier_address'])): ?>
                    <p style="margin: 0 0 10px 0; font-size: 0.9rem"><?php echo nl2br(htmlspecialchars($invoice['supplier_address'])); ?></p>
                <?php endif; ?>
                <div style="font-size: 0.9rem">
                    <?php if (!empty($invoice['supplier_phone'])): ?>
                        <div><i class="fas fa-phone text-muted" style="width: 20px"></i> <?php echo htmlspecialchars($invoice['supplier_phone']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['supplier_email'])): ?>
                        <div><i class="fas fa-envelope text-muted" style="width: 20px"></i> <?php echo htmlspecialchars($invoice['supplier_email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="text-align: right">
                <h4 style="margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid var(--surface-border); padding-bottom: 5px; text-transform: uppercase; font-size: 0.85rem" class="text-muted">Shipment Metadata</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem">
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">Date Received:</td>
                        <td style="text-align: right; padding: 4px 0"><strong><?php echo date('F d, Y', strtotime($invoice['received_date'])); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">Intake Officer:</td>
                        <td style="text-align: right; padding: 4px 0"><?php echo htmlspecialchars($invoice['user_name'] ?? 'System Admin'); ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">Transaction Status:</td>
                        <td style="text-align: right; padding: 4px 0">
                            <?php if ($invoice['status'] == 'received'): ?>
                                <span class="badge badge-success" style="background-color: #10b981; color: #fff; padding: 2px 6px; border-radius: var(--radius-sm)">Received</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="background-color: #ef4444; color: #fff; padding: 2px 6px; border-radius: var(--radius-sm)">Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">System Timestamp:</td>
                        <td style="text-align: right; padding: 4px 0; font-size: 0.8rem" class="text-muted"><?php echo date('M d, Y H:i:s', strtotime($invoice['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table" style="margin-bottom: 30px">
            <thead>
                <tr style="border-bottom: 2px solid var(--surface-border)">
                    <th style="text-align: left; padding: 10px">#</th>
                    <th style="text-align: left; padding: 10px">Medicine / Item Details</th>
                    <th style="text-align: left; padding: 10px">Batch No.</th>
                    <th style="text-align: left; padding: 10px">Expiry Date</th>
                    <th style="text-align: center; padding: 10px">Quantity Received</th>
                    <th style="text-align: right; padding: 10px">Unit Cost Price</th>
                    <th style="text-align: right; padding: 10px">Disc(%)</th>
                    <th style="text-align: right; padding: 10px">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid var(--surface-border)">
                        <td style="padding: 10px"><?php echo $i++; ?></td>
                        <td style="padding: 10px">
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <?php if (!empty($item['product_generic_name']) || !empty($item['product_strength'])): ?>
                                <br><span class="text-muted" style="font-size: 0.8rem"><?php echo htmlspecialchars($item['product_generic_name'] ?? ''); ?> <?php echo htmlspecialchars($item['product_strength'] ?? ''); ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px">
                            <?php echo !empty($item['batch_number']) ? '<code>' . htmlspecialchars($item['batch_number']) . '</code>' : '<span class="text-muted">—</span>'; ?>
                        </td>
                        <td style="padding: 10px">
                            <?php echo !empty($item['expiry_date']) ? date('M d, Y', strtotime($item['expiry_date'])) : '<span class="text-muted">—</span>'; ?>
                        </td>
                        <td style="text-align: center; padding: 10px"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right; padding: 10px"><?php echo format_price($item['cost_price']); ?></td>
                        <td style="text-align: right; padding: 10px"><?php echo (isset($item['discount']) && $item['discount'] > 0) ? htmlspecialchars(number_format($item['discount'], 2)) . '%' : '-'; ?></td>
                        <td style="text-align: right; padding: 10px"><strong><?php echo format_price($item['subtotal']); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals & Notes -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px">
            <div>
                <?php if (!empty($invoice['reference_number'])): ?>
                    <h4 style="margin-top: 0; margin-bottom: 10px; text-transform: uppercase; font-size: 0.85rem" class="text-muted">Reference Invoice Number</h4>
                    <p style="margin: 0; font-size: 1.1rem; padding: 10px; background-color: var(--bg-color, #f9fafb); border: 1px solid var(--surface-border); border-radius: var(--radius-sm); font-weight: bold;">
                        #<?php echo htmlspecialchars($invoice['reference_number']); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div>
                <table style="width: 100%; border-collapse: collapse; font-size: 1.05rem">
                    <tr>
                        <td style="text-align: left; padding: 6px 0" class="text-muted">Subtotal:</td>
                        <td style="text-align: right; padding: 6px 0"><?php echo format_price($invoice['total_amount']); ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 6px 0" class="text-muted">Discount Applied:</td>
                        <td style="text-align: right; padding: 6px 0; color: var(--danger-color, #ef4444)">-<?php echo format_price($invoice['discount']); ?></td>
                    </tr>
                    <tr style="border-top: 2px solid var(--surface-border); font-size: 1.25rem; font-weight: bold">
                        <td style="text-align: left; padding: 10px 0">Grand Total:</td>
                        <td style="text-align: right; padding: 10px 0; color: var(--primary-color)"><?php echo format_price($invoice['net_amount']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
