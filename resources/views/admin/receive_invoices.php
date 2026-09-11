<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';

// Flash alert handling
$successMsg = $_SESSION['success_msg'] ?? '';
$errorMsg = $_SESSION['error'] ?? '';
if (!empty($successMsg)) {
    $msg = $successMsg;
    $msgType = 'success';
    unset($_SESSION['success_msg']);
} elseif (!empty($errorMsg)) {
    $msg = $errorMsg;
    $msgType = 'danger';
    unset($_SESSION['error']);
}
?>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px">
        <div>
            <h1 class="page-title">Receive Invoices</h1>
            <p class="text-muted">Record and manage inventory stock shipments from suppliers</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo url('/admin/create_return_receive_invoice'); ?>" class="btn" style="background-color: #ef4444; color: white; border: none;">
                <i class="fas fa-undo"></i> Return Stock
            </a>
            <a href="<?php echo url('/admin/create_receive_invoice'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Receive Stock (New Invoice)
            </a>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msgType; ?>" style="margin-bottom: 20px">
            <i class="fas fa-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 20px">
        <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end">
            <div class="form-group" style="margin-bottom: 0; min-width: 200px">
                <label for="supplier_id" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block">Supplier</label>
                <select name="supplier_id" id="supplier_id" class="form-control">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['id']; ?>" <?php echo $filters['supplier_id'] == $sup['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sup['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0; min-width: 160px">
                <label for="payment_status" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="paid" <?php echo ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="partial" <?php echo ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : ''; ?>>Partial</option>
                    <option value="unpaid" <?php echo ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid (Credit)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0; min-width: 150px">
                <label for="start_date" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0; min-width: 150px">
                <label for="end_date" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; display: block">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>">
            </div>
            <div style="display: flex; gap: 10px; margin-bottom: 2px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="<?php echo url('/admin/receive_invoices'); ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Supplier</th>
                        <th>Received Date</th>
                        <th>Net Amount</th>
                        <th>Paid</th>
                        <th>Due / Balance</th>
                        <th>Payment Status</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <?php 
                            $net = (float)($inv['net_amount'] ?? 0.00);
                            $paid = (float)($inv['amount_paid'] ?? 0.00);
                            $due = max(0.00, $net - $paid);
                            $pStatus = $inv['payment_status'] ?? 'paid';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong>
                                    <?php if (!empty($inv['reference_number']) && $inv['reference_number'] !== 'N/A'): ?>
                                        <div style="font-size: 11px; color: #64748b;">Ref: <?php echo htmlspecialchars($inv['reference_number']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($inv['supplier_name'] ?? 'Walk-in/Unknown'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($inv['received_date'])); ?></td>
                                <td>
                                    <strong>
                                        <?php if (!empty($inv['is_return'])): ?>
                                            <span style="color: #ef4444;"><?php echo format_price($inv['net_amount']); ?></span>
                                        <?php else: ?>
                                            <?php echo format_price($inv['net_amount']); ?>
                                        <?php endif; ?>
                                    </strong>
                                </td>
                                <td>
                                    <span style="color: #10b981; font-weight: 600;">
                                        <?php echo format_price($paid); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($inv['is_return']) && $due > 0): ?>
                                        <span style="color: #ef4444; font-weight: 700;">
                                            <?php echo format_price($due); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #64748b;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span class="badge" style="background-color: #fee2e2; color: #b91c1c;">N/A</span>
                                    <?php elseif ($pStatus === 'paid'): ?>
                                        <span class="badge" style="background-color: #dcfce7; color: #15803d; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    <?php elseif ($pStatus === 'partial'): ?>
                                        <span class="badge" style="background-color: #fef3c7; color: #b45309; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                            <i class="fas fa-adjust"></i> Partial
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #fee2e2; color: #b91c1c; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                            <i class="fas fa-clock"></i> Unpaid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span class="badge" style="background-color: #ef4444; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Return</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #3b82f6; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Receive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns" style="display: flex; gap: 5px; align-items: center;">
                                        <a href="<?php echo url('/admin/view_receive_invoice?id=' . $inv['id']); ?>" class="btn btn-sm btn-view" title="View details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (empty($inv['is_return']) && $pStatus !== 'paid' && $due > 0): ?>
                                            <button type="button" class="btn btn-sm btn-primary open-pay-btn" 
                                                    data-id="<?php echo $inv['id']; ?>" 
                                                    data-num="<?php echo htmlspecialchars($inv['invoice_number']); ?>"
                                                    data-due="<?php echo $due; ?>"
                                                    style="padding: 4px 8px; font-size: 12px; background-color: #10b981; border: none;" 
                                                    title="Record Payment">
                                                <i class="fas fa-hand-holding-usd"></i> Pay
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state" style="text-align: center; padding: 40px;">
                                <i class="fas fa-truck-loading" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 10px; display: block"></i>
                                No receive invoices found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo renderPagination($pagination, url('/admin/receive_invoices')); ?>
    </div>
</div>

<!-- Pay Supplier Modal -->
<div id="payModal" class="modal" style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 460px; width: 90%; margin: 60px auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 700; margin: 0; color: #0f172a;">
                <i class="fas fa-money-bill-wave" style="color: #10b981; margin-right: 6px;"></i> Record Supplier Payment
            </h3>
            <span style="font-size: 24px; cursor: pointer; color: #94a3b8;" onclick="document.getElementById('payModal').style.display='none'">&times;</span>
        </div>
        
        <form method="POST" action="<?php echo url('/admin/receive_invoices/pay'); ?>" id="supplierPayForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" name="receive_invoice_id" id="modalInvoiceId">
            <input type="hidden" name="redirect_to" value="/admin/receive_invoices">

            <div style="margin-bottom: 14px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 13px; color: #64748b;">Receive Invoice: <strong id="modalInvoiceNum" style="color: #0f172a;"></strong></div>
                <div style="font-size: 14px; color: #dc2626; font-weight: 700; margin-top: 4px;">Remaining Due: <span id="modalDueDisplay"></span></div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Payment Amount *</label>
                <input type="number" step="0.01" name="amount" id="modalPayAmount" class="form-control" required style="width: 100%;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Payment Date</label>
                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Payment Method</label>
                <select name="payment_method" class="form-control" style="width: 100%;">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="online">Online / Card</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Notes / Transaction Ref</label>
                <input type="text" name="notes" class="form-control" placeholder="Optional reference notes" style="width: 100%;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('payModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" style="background: #10b981; color: white;">Save Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.open-pay-btn').forEach(btn => {
    btn.onclick = function() {
        const id = this.getAttribute('data-id');
        const num = this.getAttribute('data-num');
        const due = parseFloat(this.getAttribute('data-due') || 0);

        document.getElementById('modalInvoiceId').value = id;
        document.getElementById('modalInvoiceNum').textContent = num;
        document.getElementById('modalDueDisplay').textContent = '<?php echo currency_symbol(); ?> ' + due.toFixed(2);
        document.getElementById('modalPayAmount').value = due.toFixed(2);
        document.getElementById('modalPayAmount').max = due.toFixed(2);

        document.getElementById('payModal').style.display = 'flex';
    };
});
</script>



<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
