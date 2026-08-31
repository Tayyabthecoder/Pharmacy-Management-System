<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">

    <!-- ════════════ Hero Welcome Section ════════════ -->
    <div class="dash-welcome dash-animate">
        <div class="dash-welcome-text">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
            <p>Here's your personal sales performance for today.</p>
        </div>
        <div class="dash-welcome-meta">
            <div class="dash-date-badge">
                <i class="far fa-calendar-alt"></i>
                <?php echo date('l, F d, Y'); ?>
            </div>
        </div>
    </div>

    <!-- ════════════ Expiry & Stock Awareness Alerts ════════════ -->
    <?php if ($nearExpiryCount > 0): ?>
        <div class="dash-alert-banner pulse-warning dash-animate dash-delay-1">
            <div class="dash-alert-content">
                <div class="dash-alert-icon" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="dash-alert-text">
                    <strong>Information:</strong> There are <strong><?php echo $nearExpiryCount; ?></strong> medicine(s) expiring within 6 months. Please prioritize these items if applicable.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($lowStockCount > 0): ?>
        <div class="dash-alert-banner dash-animate dash-delay-1" style="border-left: 4px solid #ef4444; background: var(--bg-card);">
            <div class="dash-alert-content">
                <div class="dash-alert-icon" style="background: rgba(239, 68, 68, 0.2); color: #ef4444;">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="dash-alert-text">
                    <strong>Information:</strong> <strong><?php echo $lowStockCount; ?></strong> medicine(s) are running low on stock. Check stock levels before committing large sales.
                </div>
            </div>
            <a href="<?php echo url('/salesman/inventory'); ?>" class="dash-alert-action">
                <i class="fas fa-box"></i> Check Stock
            </a>
        </div>
    <?php endif; ?>

    <!-- ════════════ Quick Action Shortcuts ════════════ -->
    <div class="quick-actions-container dash-animate dash-delay-2">
        <a href="<?php echo url('/salesman/create_invoice'); ?>" class="quick-action-btn qa-invoice">
            <i class="fas fa-file-invoice-dollar"></i> New Invoice
        </a>
        <a href="<?php echo url('/salesman/inventory'); ?>" class="quick-action-btn qa-medicine">
            <i class="fas fa-box"></i> Check Stock
        </a>
        <a href="<?php echo url('/salesman/invoices'); ?>" class="quick-action-btn qa-reports">
            <i class="fas fa-file-alt"></i> My Invoices
        </a>
    </div>

    <!-- ════════════ KPI Stats Section ════════════ -->
    <div class="dash-stats-section">

        <!-- ── Row 1: Today's Performance ── -->
        <div class="dash-section-header dash-animate dash-delay-3">
            <div class="dash-section-icon" style="background: rgba(59,130,246,0.12); color: #3b82f6;">
                <i class="fas fa-bolt"></i>
            </div>
            <h2>Today's Performance</h2>
            <span class="dash-section-badge"><?php echo date('M d'); ?></span>
        </div>
        <div class="dash-stats-row cols-4 dash-animate dash-delay-3">
            <!-- Today's Revenue -->
            <div class="dash-stat-card stat-blue">
                <div class="dash-stat-icon dash-icon-blue">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($todaySales); ?></div>
                    <div class="dash-stat-label">Today's Revenue</div>
                </div>
            </div>

            <!-- Today's Sales Count -->
            <div class="dash-stat-card stat-green">
                <div class="dash-stat-icon dash-icon-green">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($todayInvoices); ?></div>
                    <div class="dash-stat-label">Today's Sales</div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="dash-stat-card stat-purple">
                <div class="dash-stat-icon dash-icon-purple">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($totalSales); ?></div>
                    <div class="dash-stat-label">All-Time Revenue</div>
                </div>
            </div>

            <!-- Total Invoices -->
            <div class="dash-stat-card stat-cyan">
                <div class="dash-stat-icon dash-icon-cyan">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($totalInvoices); ?></div>
                    <div class="dash-stat-label">Total Invoices</div>
                </div>
            </div>
        </div>

        <!-- ── Row 2: Monthly Progress ── -->
        <div class="dash-section-header dash-animate dash-delay-4" style="margin-top: 30px;">
            <div class="dash-section-icon" style="background: rgba(16,185,129,0.12); color: #10b981;">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h2>This Month</h2>
            <span class="dash-section-badge"><?php echo date('F Y'); ?></span>
        </div>
        <div class="dash-stats-row cols-4 dash-animate dash-delay-4">
             <div class="dash-stat-card stat-orange">
                <div class="dash-stat-icon dash-icon-orange">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($thisMonthSales); ?></div>
                    <div class="dash-stat-label">This Month's Revenue</div>
                </div>
            </div>

            <!-- This Month's Sales Count -->
            <div class="dash-stat-card stat-blue">
                <div class="dash-stat-icon dash-icon-blue">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($thisMonthInvoices); ?></div>
                    <div class="dash-stat-label">This Month's Invoices</div>
                </div>
            </div>

            <!-- Total Items Sold This Month -->
            <div class="dash-stat-card stat-green">
                <div class="dash-stat-icon dash-icon-green">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($thisMonthItems); ?></div>
                    <div class="dash-stat-label">Items Sold This Month</div>
                </div>
            </div>

            <!-- Average Order Value This Month -->
            <div class="dash-stat-card stat-pink">
                <div class="dash-stat-icon dash-icon-pink">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($thisMonthAvgValue); ?></div>
                    <div class="dash-stat-label">Average Order Value</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════ Recent Invoices Section ════════════ -->
    <div style="margin-top: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 1.3rem; color: var(--text-color);"><i class="fas fa-layer-group" style="color:var(--primary); margin-right:8px;"></i> Recent Activity</h3>
            <div>
                <a href="<?php echo url('/salesman/invoices'); ?>" class="btn btn-sm" style="background: var(--bg-hover); color: var(--text-color); border: none; border-radius: 8px; padding: 8px 16px; font-weight: 500; margin-right: 10px; transition: all 0.2s;">
                    View All
                </a>
                <a href="<?php echo url('/salesman/create_invoice'); ?>" class="btn btn-sm btn-primary" style="border-radius: 8px; padding: 8px 16px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                    <i class="fas fa-plus"></i> New Invoice
                </a>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Patient Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentInvoices)): ?>
                            <?php foreach ($recentInvoices as $inv): ?>
                                <tr>
                                    <td>#<?php echo str_pad($inv['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($inv['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                    <td><?php echo format_price($inv['total_amount']); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($inv['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo url('/invoice/print?id=' . $inv['id']); ?>" target="_blank" class="btn btn-sm btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if (!empty($settings['salesman_can_edit_invoice'])): ?>
                                        <a href="<?php echo url('/salesman/edit_invoice?id=' . $inv['id']); ?>" class="btn btn-sm btn-primary" style="margin-left: 5px">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['salesman_can_delete_invoice'])): ?>
                                        <button class="btn btn-sm btn-confirm-delete" style="margin-left: 5px; background-color: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer;" onclick="confirmVoid(<?php echo $inv['id']; ?>)">
                                            <i class="fas fa-trash"></i> Void
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-file-invoice"></i>
                                    No invoices found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>

<script>
function confirmVoid(id) {
    if (confirm("Are you sure you want to void invoice #" + String(id).padStart(5, '0') + "? This will restore inventory stock levels.")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo url("/salesman/delete_invoice"); ?>';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'csrf_token';
        tokenInput.value = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
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
