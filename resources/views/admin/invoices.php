<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
$role = $_SESSION['role'] ?? 'salesman';
$rolePath = $role === 'admin' ? 'admin' : 'salesman';
?>

<div class="dashboard-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center">
        <div>
            <h1 class="page-title">Invoices</h1>
            <p class="text-muted">Overview and management of all transaction invoices</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo url('/' . $rolePath . '/create_return_invoice'); ?>" class="btn" style="background-color: #ef4444; color: white; border: none;">
                <i class="fas fa-undo"></i> Return Invoice
            </a>
            <a href="<?php echo url('/' . $rolePath . '/create_invoice'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Invoice
            </a>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="card" style="margin-bottom: 20px; padding: 15px; background-color: var(--surface-color, #ffffff); border-radius: 8px; box-shadow: var(--shadow-sm, 0 1px 2px rgba(0,0,0,0.05));">
        <form method="GET" action="<?php echo url('/admin/invoices'); ?>" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" name="search" class="form-control" placeholder="Search by Invoice ID, Patient Name or Salesman..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding-left: 35px; width: 100%; box-sizing: border-box;">
            </div>

            <div class="btn btn-primary" style="display: flex; align-items: center; gap: 8px; height: 38px; padding: 0 16px; margin: 0; cursor: default;">
                <i class="fas fa-filter"></i>
                <select name="date_filter" style="width: auto; border: none; background: transparent; padding: 0; font-weight: 500; color: inherit; cursor: pointer; height: 100%; box-shadow: none; outline: none;">
                    <option value="" style="color: #333;">All Time</option>
                    <option value="today" style="color: #333;" <?php echo ($_GET['date_filter'] ?? '') === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" style="color: #333;" <?php echo ($_GET['date_filter'] ?? '') === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" style="color: #333;" <?php echo ($_GET['date_filter'] ?? '') === 'month' ? 'selected' : ''; ?>>This Month</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px;">
                Search
            </button>

            <?php if (!empty($_GET['search']) || !empty($_GET['date_filter'])): ?>
                <a href="<?php echo url('/admin/invoices'); ?>" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px; text-decoration: none; height: 38px; padding: 0 16px; box-sizing: border-box;">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
            
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Patient Name</th>
                        <?php if ($role === 'admin'): ?><th>Salesman</th><?php endif; ?>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($inv['invoice_number'] ?? ('#' . str_pad($inv['id'], 5, '0', STR_PAD_LEFT))); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($inv['customer_name']); ?>
                                </td>
                                <?php if ($role === 'admin'): ?><td><?php echo htmlspecialchars($inv['salesman'] ?? ''); ?></td><?php endif; ?>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span style="color: #ef4444;"><?php echo format_price($inv['total_amount']); ?></span>
                                    <?php else: ?>
                                        <?php echo format_price($inv['total_amount']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($inv['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($inv['is_return'])): ?>
                                        <span class="badge" style="background-color: #ef4444; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Return</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #10b981; color: white; font-size: 0.85rem; padding: 4px 8px; border-radius: 4px;">Sale</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo url('/invoice/print?id=' . $inv['id']); ?>" target="_blank" class="btn btn-sm btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    
                                    <?php if ($role === 'salesman' && !empty($settings['salesman_can_delete_invoice'])): ?>
                                    <button class="btn btn-sm btn-confirm-delete" style="margin-left: 5px; background-color: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer;" onclick="confirmVoid(<?php echo $inv['id']; ?>)">
                                        <i class="fas fa-trash"></i> Void
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $role === 'admin' ? '6' : '5'; ?>" class="empty-state">
                                <i class="fas fa-receipt"></i>
                                No invoices found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo renderPagination($pagination, url('/' . $rolePath . '/invoices')); ?>
    </div>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>

<?php if ($role === 'salesman' && !empty($settings['salesman_can_delete_invoice'])): ?>
<script>
function confirmVoid(id) {
    if (confirm("Are you sure you want to void invoice #" + String(id).padStart(5, '0') + "? This will restore inventory stock levels.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo url("/salesman/delete_invoice"); ?>';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?php echo $_SESSION["csrf_token"]; ?>';
        form.appendChild(tokenInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php endif; ?>
