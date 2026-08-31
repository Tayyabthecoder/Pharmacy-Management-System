<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<style>
.view-mode-toggle {
    display: flex;
    background: var(--surface-border, rgba(226, 232, 240, 0.6));
    padding: 3px;
    border-radius: 10px;
    gap: 2px;
}

.view-mode-btn {
    width: 38px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.view-mode-btn.active {
    background: var(--card-bg, #ffffff);
    color: #3b82f6;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
</style>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 class="page-title"><i class="fas fa-boxes-stacked" style="color: #3b82f6;"></i> Inventory Stock Check</h1>
            <p class="text-muted">Search products and check stock availability</p>
        </div>
    </div>

    <!-- Search Bar & View Mode Toggle Toolbar -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px; border-radius: 16px; box-shadow: var(--card-shadow, 0 4px 14px rgba(0,0,0,0.03)); border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
            <form action="" method="GET" style="display: flex; gap: 10px; flex: 1; min-width: 260px; max-width: 500px;">
                <input type="text" name="search" class="form-control" placeholder="Search product name, composition or category..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-search"></i> Search</button>
            </form>

            <div class="view-mode-toggle" title="Switch View Mode">
                <button type="button" class="view-mode-btn active" id="viewGridBtn" onclick="setSalesmanViewMode('grid')" title="Grid View">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="view-mode-btn" id="viewTableBtn" onclick="setSalesmanViewMode('table')" title="Table View">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Layout 1: Grid Cards View -->
    <div id="inventoryGrid" class="row" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $prod): 
                $cardStyle = '';
                $minStockLevel = $prod['min_stock_level'] ?? ($minStock ?? 10);
                if ($prod['quantity'] == 0) {
                    $cardStyle = 'background-color: rgba(239, 68, 68, 0.05); border: 1px solid #ef4444;';
                } elseif ($prod['quantity'] < $minStockLevel) {
                    $cardStyle = 'background-color: rgba(245, 158, 11, 0.05); border: 1px solid #f59e0b;';
                } else {
                    $cardStyle = 'background-color: rgba(16, 185, 129, 0.02); border: 1px solid rgba(16, 185, 129, 0.3);';
                }
            ?>
                <div class="card" style="width: 260px; flex: 1 0 260px; display: flex; flex-direction: column; border-radius: 16px; padding: 18px; <?php echo $cardStyle; ?>">
                     <?php if (!empty($settings['enable_product_images'])): ?>
                         <?php if(!empty($prod['image'])): ?>
                            <img src="<?php echo url($prod['image']); ?>" alt="Img" style="width: 100%; height: 140px; object-fit: cover; border-radius: 12px; margin-bottom: 12px;">
                        <?php else: ?>
                            <div class="img-placeholder-lg" style="height: 100px; border-radius: 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; background: rgba(59, 130, 246, 0.08); color: #3b82f6;"><i class="fas fa-pills fa-2x"></i></div>
                        <?php endif; ?>
                     <?php endif; ?>
                    
                    <div style="padding: 5px 0; flex: 1">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 4px 0; color: var(--text-color, #1e293b);"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 8px; font-style: italic;"><?php echo htmlspecialchars($prod['generic_name'] ?? $prod['category_name'] ?? ''); ?></p>
                        <h4 style="margin: 8px 0; font-weight: 800; color: var(--primary-color, #3b82f6);"><?php echo format_price($prod['price']); ?></h4>
                        <?php if (!empty($settings['salesman_can_see_cost_price'])): ?>
                            <p class="text-muted" style="font-size: 0.82rem; margin-top: 2px">Cost: <?php echo format_price($prod['cost_price']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: auto; padding-top: 10px;">
                         <?php if ($prod['quantity'] == 0): ?>
                             <div class="stock-status stock-out" style="padding: 6px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; text-align: center; background: rgba(239, 68, 68, 0.15); color: #dc2626;">Out of Stock</div>
                         <?php elseif ($prod['quantity'] < $minStockLevel): ?>
                             <div class="stock-status stock-low" style="padding: 6px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; text-align: center; background: rgba(245, 158, 11, 0.15); color: #d97706;">Low Stock: <?php echo $prod['quantity']; ?></div>
                         <?php else: ?>
                             <div class="stock-status stock-ok" style="padding: 6px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; text-align: center; background: rgba(16, 185, 129, 0.15); color: #059669;">In Stock: <?php echo $prod['quantity']; ?></div>
                         <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="width: 100%; padding: 40px; text-align: center; border-radius: 16px;">
                <div class="empty-state">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; color: var(--text-muted, #94a3b8);"></i>
                    <p style="margin: 0; color: var(--text-muted, #64748b);">No products found matching your search.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Layout 2: Table View -->
    <div id="inventoryTableContainer" class="card" style="display: none; padding: 18px; border-radius: 16px; margin-bottom: 24px; box-shadow: var(--card-shadow, 0 4px 14px rgba(0,0,0,0.03)); border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));">
        <div class="table-container">
            <table class="table" style="width: 100%; margin: 0;">
                <thead>
                    <tr>
                        <?php if (!empty($settings['enable_product_images'])): ?>
                        <th>Image</th>
                        <?php endif; ?>
                        <th>Brand Name</th>
                        <th>Generic Composition</th>
                        <th>Type / Strength</th>
                        <th>Price</th>
                        <?php if (!empty($settings['salesman_can_see_cost_price'])): ?>
                        <th>Cost Price</th>
                        <?php endif; ?>
                        <th>Availability Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $prod): 
                            $minStockLevel = $prod['min_stock_level'] ?? ($minStock ?? 10);
                        ?>
                            <tr>
                                <?php if (!empty($settings['enable_product_images'])): ?>
                                <td>
                                    <?php if(!empty($prod['image'])): ?>
                                        <img src="<?php echo url($prod['image']); ?>" alt="Img" style="width: 42px; height: 42px; border-radius: 8px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="img-placeholder" style="width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(59, 130, 246, 0.08); color: #3b82f6;"><i class="fas fa-pills"></i></div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                <td><span class="text-muted" style="font-style: italic;"><?php echo htmlspecialchars($prod['generic_name'] ?? '-'); ?></span></td>
                                <td><?php echo htmlspecialchars($prod['category_name'] ?? '-'); ?> / <?php echo htmlspecialchars($prod['strength'] ?? '-'); ?></td>
                                <td><strong><?php echo format_price($prod['price']); ?></strong></td>
                                <?php if (!empty($settings['salesman_can_see_cost_price'])): ?>
                                <td><span class="text-muted"><?php echo format_price($prod['cost_price']); ?></span></td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($prod['quantity'] == 0): ?>
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; padding: 4px 10px; border-radius: 12px; font-weight: 600;">Out of Stock</span>
                                    <?php elseif ($prod['quantity'] < $minStockLevel): ?>
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 10px; border-radius: 12px; font-weight: 600;">Low Stock: <?php echo $prod['quantity']; ?></span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 10px; border-radius: 12px; font-weight: 600;">In Stock: <?php echo $prod['quantity']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state" style="padding: 30px; text-align: center;">No products found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo renderPagination($pagination, url('/salesman/inventory')); ?>
</div>

<script>
let currentSalesmanViewMode = localStorage.getItem('salesman_inv_view_mode') || 'grid';

function setSalesmanViewMode(mode) {
    currentSalesmanViewMode = mode;
    localStorage.setItem('salesman_inv_view_mode', mode);

    const gridEl = document.getElementById('inventoryGrid');
    const tableEl = document.getElementById('inventoryTableContainer');
    const gridBtn = document.getElementById('viewGridBtn');
    const tableBtn = document.getElementById('viewTableBtn');

    if (mode === 'grid') {
        if (gridEl) gridEl.style.display = 'flex';
        if (tableEl) tableEl.style.display = 'none';
        if (gridBtn) gridBtn.classList.add('active');
        if (tableBtn) tableBtn.classList.remove('active');
    } else {
        if (gridEl) gridEl.style.display = 'none';
        if (tableEl) tableEl.style.display = 'block';
        if (gridBtn) gridBtn.classList.remove('active');
        if (tableBtn) tableBtn.classList.add('active');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    setSalesmanViewMode(currentSalesmanViewMode);
});
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
