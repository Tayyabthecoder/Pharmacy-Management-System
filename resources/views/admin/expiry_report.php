<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Medicine Expiry Report</h1>
            <p class="text-muted">Monitor expired and near-expiry pharmaceutical stock</p>
        </div>
    </div>

    <!-- EXPIRED SECTION -->
    <div class="card" style="border-top: 4px solid var(--danger-color, #ef4444); margin-bottom: 30px">
        <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px">
            <span style="color: var(--danger-color, #ef4444); font-size: 1.5rem"><i class="fas fa-exclamation-triangle"></i></span>
            <h2 style="margin: 0">Expired Medicines (Action Required)</h2>
            <span class="badge badge-danger" style="margin-left: 10px"><?php echo count($expiredProducts); ?> items</span>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Current Qty</th>
                        <th>Est. Loss</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($expiredProducts) > 0): ?>
                        <?php foreach ($expiredProducts as $prod): ?>
                            <tr style="background-color: rgba(239, 68, 68, 0.03)">
                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                <td><span class="text-muted" style="font-style: italic"><?php echo htmlspecialchars($prod['generic_name'] ?? '-'); ?></span></td>
                                <td><code><?php echo htmlspecialchars($prod['batch_number'] ?? '-'); ?></code></td>
                                <td><span class="text-danger font-weight-bold"><?php echo date('M d, Y', strtotime($prod['expiry_date'])); ?> (Expired)</span></td>
                                <td><?php echo htmlspecialchars($prod['supplier_name'] ?? '-'); ?></td>
                                <td><span class="text-danger font-weight-bold"><?php echo $prod['quantity']; ?></span></td>
                                <td><?php echo format_price((float)($prod['cost_price'] ?? 0) * (int)$prod['quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state" style="padding: 20px 0; text-align: center; color: var(--success-color, #10b981)">
                                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 8px"></i><br>
                                Excellent! No expired products in inventory.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- NEAR EXPIRY SECTION -->
    <div class="card" style="border-top: 4px solid var(--warning-color, #f59e0b)">
        <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px">
            <span style="color: var(--warning-color, #f59e0b); font-size: 1.5rem"><i class="fas fa-hourglass-half"></i></span>
            <h2 style="margin: 0">Near Expiry Medicines (Next 6 Months)</h2>
            <span class="badge badge-warning" style="margin-left: 10px"><?php echo count($nearExpiryProducts); ?> items</span>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Current Qty</th>
                        <th>Days Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($nearExpiryProducts) > 0): ?>
                        <?php foreach ($nearExpiryProducts as $prod): ?>
                            <?php 
                            $diff = strtotime($prod['expiry_date']) - strtotime(date('Y-m-d'));
                            $daysRemaining = round($diff / (60 * 60 * 24));
                            ?>
                            <tr style="background-color: rgba(245, 158, 11, 0.02)">
                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                <td><span class="text-muted" style="font-style: italic"><?php echo htmlspecialchars($prod['generic_name'] ?? '-'); ?></span></td>
                                <td><code><?php echo htmlspecialchars($prod['batch_number'] ?? '-'); ?></code></td>
                                <td><span class="text-warning font-weight-bold"><?php echo date('M d, Y', strtotime($prod['expiry_date'])); ?></span></td>
                                <td><?php echo htmlspecialchars($prod['supplier_name'] ?? '-'); ?></td>
                                <td><strong><?php echo $prod['quantity']; ?></strong></td>
                                <td><span class="badge badge-warning"><?php echo $daysRemaining; ?> days left</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state" style="padding: 20px 0; text-align: center">
                                <i class="fas fa-calendar-check" style="font-size: 2rem; margin-bottom: 8px"></i><br>
                                No products expiring within the next 6 months.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
