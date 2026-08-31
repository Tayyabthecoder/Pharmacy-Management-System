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
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Created By</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($inv['supplier_name'] ?? 'Walk-in/Unknown'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($inv['received_date'])); ?></td>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span style="color: #ef4444;"><?php echo format_price($inv['total_amount']); ?></span>
                                    <?php else: ?>
                                        <?php echo format_price($inv['total_amount']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_price($inv['discount']); ?></td>
                                <td>
                                    <strong>
                                        <?php if (!empty($inv['is_return'])): ?>
                                            <span style="color: #ef4444;"><?php echo format_price($inv['net_amount']); ?></span>
                                        <?php else: ?>
                                            <?php echo format_price($inv['net_amount']); ?>
                                        <?php endif; ?>
                                    </strong>
                                </td>
                                <td><?php echo htmlspecialchars($inv['user_name'] ?? 'Admin'); ?></td>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span class="badge" style="background-color: #ef4444; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Return</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #10b981; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Receive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns" style="display: flex; gap: 5px">
                                        <a href="<?php echo url('/admin/view_receive_invoice?id=' . $inv['id']); ?>" class="btn btn-sm btn-view" title="View details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
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



<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
