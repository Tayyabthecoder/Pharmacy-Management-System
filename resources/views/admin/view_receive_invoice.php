<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';

$paymentStatus = $invoice['payment_status'] ?? ($paymentStatus ?? 'paid');
$amountPaid = isset($amountPaid) ? (float)$amountPaid : (float)($invoice['amount_paid'] ?? 0.00);
$netAmount = isset($netAmount) ? (float)$netAmount : (float)($invoice['net_amount'] ?? 0.00);
$remainingDue = isset($remainingDue) ? (float)$remainingDue : max(0.00, $netAmount - $amountPaid);
$items = $items ?? [];
$payments = $payments ?? [];
$companyName = $globalSettings['company_name'] ?? ($settings['company_name'] ?? 'Pharmacy Management System');
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
            <p class="text-muted">Detailed overview of stock shipment #<?php echo htmlspecialchars($invoice['invoice_number'] ?? ''); ?></p>
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
                <h2 style="margin: 0; font-size: 1.5rem; color: var(--primary-color)"><?php echo htmlspecialchars($companyName); ?></h2>
                <p class="text-muted" style="margin: 5px 0 0 0; font-size: 0.85rem">Supplier Shipment Intake Ledger</p>
            </div>
            <div style="text-align: right">
                <h2 style="margin: 0; font-size: 1.3rem">RECEIVE INVOICE</h2>
                <h3 style="margin: 5px 0 0 0; font-weight: bold; color: var(--text-color)">#<?php echo htmlspecialchars($invoice['invoice_number'] ?? ''); ?></h3>
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
                        <td style="text-align: right; padding: 4px 0"><strong><?php echo !empty($invoice['received_date']) ? date('F d, Y', strtotime($invoice['received_date'])) : '—'; ?></strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">Intake Officer:</td>
                        <td style="text-align: right; padding: 4px 0"><?php echo htmlspecialchars($invoice['user_name'] ?? 'System Admin'); ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">Transaction Status:</td>
                        <td style="text-align: right; padding: 4px 0">
                            <?php if (($invoice['status'] ?? 'received') == 'received'): ?>
                                <span class="badge badge-success" style="background-color: #10b981; color: #fff; padding: 2px 6px; border-radius: var(--radius-sm)">Received</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="background-color: #ef4444; color: #fff; padding: 2px 6px; border-radius: var(--radius-sm)">Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 4px 0" class="text-muted">System Timestamp:</td>
                        <td style="text-align: right; padding: 4px 0; font-size: 0.8rem" class="text-muted"><?php echo !empty($invoice['created_at']) ? date('M d, Y H:i:s', strtotime($invoice['created_at'])) : '—'; ?></td>
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
                            <strong><?php echo htmlspecialchars($item['product_name'] ?? 'Medicine'); ?></strong>
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
                        <td style="text-align: center; padding: 10px"><?php echo htmlspecialchars((string)($item['quantity'] ?? 0)); ?></td>
                        <td style="text-align: right; padding: 10px"><?php echo format_price($item['cost_price'] ?? 0); ?></td>
                        <td style="text-align: right; padding: 10px"><?php echo (isset($item['discount']) && (float)$item['discount'] > 0) ? htmlspecialchars(number_format((float)$item['discount'], 2)) . '%' : '-'; ?></td>
                        <td style="text-align: right; padding: 10px"><strong><?php echo format_price($item['subtotal'] ?? 0); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals & Notes -->
        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; margin-bottom: 30px;">
            <div>
                <?php if (!empty($invoice['reference_number'])): ?>
                    <h4 style="margin-top: 0; margin-bottom: 8px; text-transform: uppercase; font-size: 0.85rem" class="text-muted">Supplier Reference Bill Number</h4>
                    <p style="margin: 0 0 16px 0; font-size: 1rem; padding: 10px 14px; background-color: var(--bg-color, #f9fafb); border: 1px solid var(--surface-border); border-radius: var(--radius-sm); font-weight: bold;">
                        #<?php echo htmlspecialchars($invoice['reference_number']); ?>
                    </p>
                <?php endif; ?>

                <!-- Payment Status Box -->
                <div style="background: var(--bg-color, #f8fafc); border: 1px solid var(--surface-border, #e2e8f0); border-radius: 12px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;" class="text-muted">Payment & Credit Status</span>
                        <?php if ($paymentStatus === 'paid'): ?>
                            <span class="badge" style="background-color: #10b981; color: white; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                <i class="fas fa-check-circle"></i> Paid in Full
                            </span>
                        <?php elseif ($paymentStatus === 'partial'): ?>
                            <span class="badge" style="background-color: #f59e0b; color: white; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                <i class="fas fa-adjust"></i> Partially Paid
                            </span>
                        <?php else: ?>
                            <span class="badge" style="background-color: #ef4444; color: white; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                <i class="fas fa-clock"></i> Unpaid / Credit
                            </span>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
                        <div>
                            <span class="text-muted">Amount Paid:</span>
                            <div style="font-weight: 700; color: #10b981; font-size: 1.1rem;"><?php echo format_price($amountPaid); ?></div>
                        </div>
                        <div>
                            <span class="text-muted">Balance Due:</span>
                            <div style="font-weight: 700; color: <?php echo $remainingDue > 0 ? '#ef4444' : '#64748b'; ?>; font-size: 1.1rem;"><?php echo format_price($remainingDue); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($invoice['payment_due_date'])): ?>
                        <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-muted);">
                            <i class="far fa-calendar-alt"></i> Due Date: <strong><?php echo date('M d, Y', strtotime($invoice['payment_due_date'])); ?></strong>
                            <?php if ($remainingDue > 0 && strtotime($invoice['payment_due_date']) < strtotime(date('Y-m-d'))): ?>
                                <span style="color: #ef4444; font-weight: bold; margin-left: 6px;">(OVERDUE)</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($remainingDue > 0 && $invoice['status'] === 'received'): ?>
                        <div class="no-print" style="margin-top: 14px;">
                            <button type="button" onclick="openPayModal()" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 0.9rem; padding: 8px 14px;">
                                <i class="fas fa-hand-holding-usd"></i> Record Payment (<?php echo format_price($remainingDue); ?> Due)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
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

        <!-- Supplier Payments History Section -->
        <div style="border-top: 2px dashed var(--surface-border, #e2e8f0); padding-top: 24px; margin-top: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-color);">
                    <i class="fas fa-history" style="color: var(--primary-color);"></i> Payment Transactions History
                </h4>
                <span class="text-muted" style="font-size: 0.85rem;"><?php echo count($payments); ?> recorded payment(s)</span>
            </div>

            <?php if (!empty($payments)): ?>
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--surface-border); background: var(--surface-color, #f8fafc);">
                            <th style="padding: 10px; text-align: left;">Payment Date</th>
                            <th style="padding: 10px; text-align: left;">Method</th>
                            <th style="padding: 10px; text-align: right;">Amount Paid</th>
                            <th style="padding: 10px; text-align: left;">Recorded By</th>
                            <th style="padding: 10px; text-align: left;">Notes / Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr style="border-bottom: 1px solid var(--surface-border);">
                                <td style="padding: 10px; font-weight: 600;"><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                <td style="padding: 10px; text-transform: capitalize;">
                                    <span class="badge" style="background: var(--surface-border, #e2e8f0); color: var(--text-color); padding: 2px 8px; border-radius: 4px;">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $p['payment_method'] ?? 'cash')); ?>
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: right; font-weight: 700; color: #10b981;"><?php echo format_price($p['amount']); ?></td>
                                <td style="padding: 10px; color: var(--text-muted);"><?php echo htmlspecialchars($p['created_by_name'] ?? 'Admin'); ?></td>
                                <td style="padding: 10px; color: var(--text-muted);"><?php echo !empty($p['notes']) ? htmlspecialchars($p['notes']) : '<span class="text-muted">—</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 20px; background: var(--surface-color, #f8fafc); border-radius: 8px; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                    <i class="far fa-credit-card" style="font-size: 1.5rem; margin-bottom: 6px; display: block; opacity: 0.5;"></i>
                    No payments have been recorded for this receive invoice yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Pay Modal (Hidden on print) -->
<div id="payModal" class="no-print" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg, #ffffff); border-radius: 16px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid var(--surface-border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--surface-border); padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-color); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-hand-holding-usd" style="color: var(--primary-color);"></i> Record Supplier Payment
            </h3>
            <button type="button" onclick="closePayModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="<?php echo url('/admin/receive_invoices/pay'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="receive_invoice_id" value="<?php echo $invoice['id']; ?>">
            <input type="hidden" name="redirect_to" value="/admin/view_receive_invoice?id=<?php echo $invoice['id']; ?>">

            <div style="background: var(--surface-color, #f8fafc); border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span class="text-muted">Supplier:</span>
                    <strong><?php echo htmlspecialchars($invoice['supplier_name'] ?? 'Supplier'); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Remaining Balance Due:</span>
                    <strong style="color: #ef4444; font-size: 1.05rem;"><?php echo format_price($remainingDue); ?></strong>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Payment Amount (<?php echo currency_symbol(); ?>) <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="<?php echo $remainingDue; ?>" value="<?php echo $remainingDue; ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); font-size: 1rem; font-weight: 700;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Payment Date <span class="text-danger">*</span></label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border);">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Payment Method</label>
                <select name="payment_method" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border);">
                    <option value="cash" selected>Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Notes / Transaction Ref (Optional)</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Paid via cheque #10294" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closePayModal()" class="btn btn-secondary" style="padding: 10px 18px; border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-check"></i> Submit Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPayModal() {
        const modal = document.getElementById('payModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closePayModal() {
        const modal = document.getElementById('payModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
