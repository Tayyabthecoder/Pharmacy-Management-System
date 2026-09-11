<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';

// Compute metrics for top summary bar across all database records
$totMed = $summaryStats['total'] ?? ($pagination['total_records'] ?? count($products));
$inStockMed = $summaryStats['in_stock'] ?? 0;
$lowStockMed = $summaryStats['low_stock'] ?? 0;
$expMed = $summaryStats['expiring'] ?? 0;
?>

<style>
/* Modern Scoped Theme System & Premium Layout for Medicine Management */
:root {
    --med-primary: #3b82f6;
    --med-primary-hover: #2563eb;
    --med-accent-purple: #8b5cf6;
    --med-accent-success: #10b981;
    --med-accent-warning: #f59e0b;
    --med-accent-danger: #ef4444;
}

/* Hero Header Bar */
.med-hero-banner {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    box-shadow: var(--card-shadow, 0 4px 16px rgba(0, 0, 0, 0.03));
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.med-hero-title-group h1 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.med-hero-title-group p {
    margin: 0;
    color: var(--text-muted, #64748b);
    font-size: 0.92rem;
}

.btn-primary-glow {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 11px 24px;
    font-weight: 600;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    transition: all 0.25s ease;
    text-decoration: none;
}

.btn-primary-glow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
}

/* KPI Summary Cards Grid */
.med-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.med-kpi-card {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03));
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}

.med-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow, 0 12px 24px -4px rgba(0, 0, 0, 0.08));
}

.med-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.med-kpi-card.total::before { background: linear-gradient(180deg, #3b82f6, #60a5fa); }
.med-kpi-card.instock::before { background: linear-gradient(180deg, #10b981, #34d399); }
.med-kpi-card.lowstock::before { background: linear-gradient(180deg, #f59e0b, #fbbf24); }
.med-kpi-card.expiring::before { background: linear-gradient(180deg, #ef4444, #f87171); }

.med-kpi-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.med-kpi-icon.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.med-kpi-icon.green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.med-kpi-icon.amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.med-kpi-icon.red { background: rgba(239, 68, 68, 0.12); color: #ef4444; }

.med-kpi-info h4 {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.med-kpi-value {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--text-color, #1e293b);
    line-height: 1;
    margin-bottom: 4px;
}

.med-kpi-subtext {
    font-size: 0.8rem;
    color: var(--text-muted, #64748b);
}

/* Control Panel & Filters */
.med-control-panel {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    padding: 18px 22px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03));
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.med-search-box {
    position: relative;
    min-width: 260px;
    flex: 1;
    max-width: 400px;
}

.med-search-box i.fa-search {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted, #94a3b8);
    font-size: 0.95rem;
}

.med-search-box input {
    width: 100%;
    padding: 10px 38px 10px 38px;
    font-size: 0.9rem;
    border-radius: 10px;
    border: 1px solid var(--surface-border, #cbd5e1);
    background: var(--input-bg, var(--card-bg, #ffffff));
    color: var(--text-color, #1e293b);
    transition: all 0.2s ease;
}

.med-search-box input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    outline: none;
}

.med-filter-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}

.med-filter-select {
    padding: 9px 14px;
    font-size: 0.9rem;
    border-radius: 10px;
    border: 1px solid var(--surface-border, #cbd5e1);
    background: var(--input-bg, var(--card-bg, #ffffff));
    color: var(--text-color, #1e293b);
    cursor: pointer;
}

/* View Mode Switcher */
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

/* Grid View Cards Layout */
.medicine-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.medicine-card {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03));
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
}

.medicine-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-hover-shadow, 0 12px 24px -4px rgba(0, 0, 0, 0.08));
}

.medicine-card.card-expired {
    border-left: 4px solid #ef4444;
}

.medicine-card.card-warning {
    border-left: 4px solid #f59e0b;
}

.medicine-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 14px;
}

.med-thumb {
    width: 54px;
    height: 54px;
    border-radius: 12px;
    object-fit: cover;
    border: 1px solid var(--surface-border, #cbd5e1);
    flex-shrink: 0;
}

.med-avatar-icon {
    width: 54px;
    height: 54px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(139, 92, 246, 0.12));
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.medicine-card-title-group {
    flex: 1;
    min-width: 0;
}

.medicine-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    margin: 0 0 3px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.medicine-card-generic {
    font-size: 0.86rem;
    color: var(--text-muted, #64748b);
    font-style: italic;
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.med-badges-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.med-pill-tag {
    font-size: 0.73rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.med-pill-tag.cat {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
}

.med-pill-tag.stock-ok {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}

.med-pill-tag.stock-low {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}

.med-pill-tag.stock-out {
    background: rgba(239, 68, 68, 0.15);
    color: #dc2626;
}

.med-pill-tag.exp-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #dc2626;
}

.med-pill-tag.exp-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}

.medicine-card-body {
    border-top: 1px dashed var(--surface-border, rgba(226, 232, 240, 0.8));
    border-bottom: 1px dashed var(--surface-border, rgba(226, 232, 240, 0.8));
    padding: 10px 0;
    margin: 10px 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.85rem;
}

.med-info-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--text-color, #334155);
}

.med-info-line label {
    color: var(--text-muted, #64748b);
    font-weight: 500;
    margin: 0;
}

.medicine-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding-top: 4px;
}

.med-price-display {
    display: flex;
    flex-direction: column;
}

.med-retail-price {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--primary-color, #3b82f6);
}

.med-sub-price {
    font-size: 0.75rem;
    color: var(--text-muted, #64748b);
}
</style>

<div class="dashboard-container">
    <!-- Hero Header Banner -->
    <div class="med-hero-banner">
        <div class="med-hero-title-group">
            <h1><i class="fas fa-pills" style="color: #3b82f6;"></i> Medicine Management</h1>
            <p>Manage pharmacy medicines, generics, categories, and inventory stock levels</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <?php 
            $currentUserRole = strtolower($_SESSION['role'] ?? '');
            $currentUsername = strtolower($_SESSION['username'] ?? '');
            $isOwner = ($currentUserRole === 'owner' || $currentUsername === 'owner' || ($_SESSION['user_id'] ?? 0) == 999999);
            if ($isOwner): 
            ?>
            <a href="<?php echo url('/admin/categories'); ?>" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border-radius: 12px; padding: 11px 18px; font-weight: 600;">
                <i class="fas fa-tags"></i> Manage Categories & Generics
            </a>
            <?php endif; ?>
            <a href="<?php echo url('/admin/products/import'); ?>" class="btn" style="background: #10b981; color: white; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; border-radius: 12px; padding: 11px 18px; font-weight: 600;">
                <i class="fas fa-file-import"></i> Import CSV
            </a>
            <button class="btn-primary-glow" id="openAddModal">
                <i class="fas fa-plus-circle"></i> Add Medicine
            </button>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>" style="margin-bottom: 24px; border-radius: 12px;">
            <i class="fas fa-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- KPI Metrics Summary Bar -->
    <div class="med-kpi-grid">
        <div class="med-kpi-card total">
            <div class="med-kpi-icon blue">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="med-kpi-info">
                <h4>Total Medicines</h4>
                <div class="med-kpi-value"><?php echo $totMed; ?></div>
                <div class="med-kpi-subtext">Registered items</div>
            </div>
        </div>

        <div class="med-kpi-card instock">
            <div class="med-kpi-icon green">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="med-kpi-info">
                <h4>In Stock</h4>
                <div class="med-kpi-value"><?php echo $inStockMed; ?></div>
                <div class="med-kpi-subtext">Available for sale</div>
            </div>
        </div>

        <div class="med-kpi-card lowstock">
            <div class="med-kpi-icon amber">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="med-kpi-info">
                <h4>Low Stock Alert</h4>
                <div class="med-kpi-value"><?php echo $lowStockMed; ?></div>
                <div class="med-kpi-subtext">Reorder recommended</div>
            </div>
        </div>

        <div class="med-kpi-card expiring">
            <div class="med-kpi-icon red">
                <i class="fas fa-calendar-xmark"></i>
            </div>
            <div class="med-kpi-info">
                <h4>Expiring / Expired</h4>
                <div class="med-kpi-value"><?php echo $expMed; ?></div>
                <div class="med-kpi-subtext">Check batch expiry</div>
            </div>
        </div>
    </div>

    <!-- Control Panel & Filters Toolbar -->
    <div class="med-control-panel">
        <div class="med-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search brand name, generic, company, batch..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" autocomplete="off">
            <button type="button" id="clearSearchBtn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted, #94a3b8); font-size: 1.1rem; cursor: pointer; display: none; padding: 4px 8px;" title="Clear search">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>

        <div class="med-filter-group">
            <select id="statusFilterSelect" class="med-filter-select" onchange="runTableFilter()">
                <option value="all">All Stock Statuses</option>
                <option value="instock">In Stock</option>
                <option value="lowstock">Low Stock Alert</option>
                <option value="outstock">Out of Stock</option>
                <option value="expiring">Expiring Soon (6 Mos)</option>
                <option value="expired">Expired</option>
            </select>

            <?php if (!empty($categories)): ?>
            <select id="categoryFilterSelect" class="med-filter-select" onchange="runTableFilter()">
                <option value="all">All Medicine Types</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>

            <!-- View Mode Switcher Toggle (Grid vs Table) -->
            <div class="view-mode-toggle" title="Switch View Mode">
                <button type="button" class="view-mode-btn" id="viewGridBtn" onclick="setViewMode('grid')" title="Grid View">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="view-mode-btn" id="viewTableBtn" onclick="setViewMode('table')" title="Table View">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Dual Layout View 1: Grid View -->
    <div class="view-grid-container medicine-cards-grid" id="medicineGrid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $prod): ?>
                <?php 
                $isExpired = $prod['expiry_date'] !== null && strtotime($prod['expiry_date']) <= strtotime(date('Y-m-d')); 
                $isNearExpiry = $prod['expiry_date'] !== null && !$isExpired && strtotime($prod['expiry_date']) <= strtotime('+6 months');
                ?>
                <div class="medicine-card <?php echo $isExpired ? 'card-expired' : ($isNearExpiry ? 'card-warning' : ''); ?>"
                     data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                     data-generic="<?php echo htmlspecialchars($prod['generic_name'] ?? ''); ?>"
                     data-category="<?php echo htmlspecialchars($prod['category_name'] ?? ''); ?>"
                     data-catid="<?php echo $prod['category_id'] ?? ''; ?>"
                     data-company="<?php echo htmlspecialchars($prod['company_name'] ?? ''); ?>"
                     data-batch="<?php echo htmlspecialchars($prod['batch_number'] ?? ''); ?>"
                     data-strength="<?php echo htmlspecialchars($prod['strength'] ?? ''); ?>"
                     data-qty="<?php echo $prod['quantity'] ?? 0; ?>"
                     data-minstock="<?php echo $prod['min_stock_level'] ?? 10; ?>"
                     data-is-expired="<?php echo $isExpired ? '1' : '0'; ?>"
                     data-is-expiring="<?php echo $isNearExpiry ? '1' : '0'; ?>">

                    <div>
                        <div class="medicine-card-header">
                            <?php if (!empty($settings['enable_product_images']) && !empty($prod['image'])): ?>
                                <img src="<?php echo url($prod['image']); ?>" alt="Img" class="med-thumb">
                            <?php else: ?>
                                <div class="med-avatar-icon">
                                    <i class="fas fa-pills"></i>
                                </div>
                            <?php endif; ?>

                            <div class="medicine-card-title-group">
                                <h3 class="medicine-card-title" title="<?php echo htmlspecialchars($prod['name']); ?>">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </h3>
                                <div class="medicine-card-generic">
                                    <i class="fas fa-atom" style="font-size:0.8rem; margin-right:2px; opacity:0.7;"></i>
                                    <?php echo htmlspecialchars($prod['generic_name'] ?? 'No Generic'); ?>
                                </div>

                                <div class="med-badges-row">
                                    <?php if (!empty($prod['category_name'])): ?>
                                        <span class="med-pill-tag cat">
                                            <i class="fas fa-capsules"></i> <?php echo htmlspecialchars($prod['category_name']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($prod['quantity'] <= 0): ?>
                                        <span class="med-pill-tag stock-out"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                    <?php elseif ($prod['quantity'] < ($prod['min_stock_level'] ?? 10)): ?>
                                        <span class="med-pill-tag stock-low"><i class="fas fa-exclamation-triangle"></i> Low: <?php echo $prod['quantity']; ?></span>
                                    <?php else: ?>
                                        <span class="med-pill-tag stock-ok"><i class="fas fa-check-circle"></i> Stock: <?php echo $prod['quantity']; ?></span>
                                    <?php endif; ?>
                                    <?php if ($isExpired): ?>
                                        <span class="med-pill-tag exp-danger"><i class="fas fa-calendar-times"></i> Expired</span>
                                    <?php elseif ($isNearExpiry): ?>
                                        <span class="med-pill-tag exp-warning"><i class="fas fa-clock"></i> Expiring Soon</span>
                                    <?php endif; ?>

                                    <?php if (!empty($prod['is_prescription_required'])): ?>
                                        <span class="med-pill-tag" style="background: rgba(239, 68, 68, 0.15); color: #dc2626;"><i class="fas fa-prescription"></i> Rx Required</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="medicine-card-body">
                            <div class="med-info-line">
                                <label><i class="fas fa-layer-group" style="width: 16px;"></i> Type / Strength:</label>
                                <span><strong><?php echo htmlspecialchars($prod['category_name'] ?? '-'); ?></strong> / <?php echo htmlspecialchars($prod['strength'] ?? '-'); ?></span>
                            </div>
                            <div class="med-info-line">
                                <label><i class="fas fa-building" style="width: 16px;"></i> Company:</label>
                                <span><?php echo htmlspecialchars($prod['company_name'] ?? '-'); ?></span>
                            </div>
                            <div class="med-info-line">
                                <label><i class="fas fa-barcode" style="width: 16px;"></i> Batch No:</label>
                                <span><code><?php echo htmlspecialchars($prod['batch_number'] ?? '-'); ?></code></span>
                            </div>
                            <div class="med-info-line">
                                <label><i class="fas fa-calendar-alt" style="width: 16px;"></i> Expiry Date:</label>
                                <span>
                                    <?php if (!empty($prod['expiry_date'])): ?>
                                        <strong class="<?php echo $isExpired ? 'text-danger' : ($isNearExpiry ? 'text-warning' : ''); ?>">
                                             <?php echo date('M d, Y', strtotime($prod['expiry_date'])); ?>
                                        </strong>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="medicine-card-footer">
                        <div class="med-price-display">
                            <span class="med-retail-price"><?php echo format_price($prod['price']); ?></span>
                            <?php if (!empty($prod['trad_price']) && (float)$prod['trad_price'] > 0): ?>
                                <span class="med-sub-price">Trade: <?php echo format_price($prod['trad_price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="action-btns" style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <a href="<?php echo url('/admin/products/label?id=' . $prod['id']); ?>" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #334155; padding: 5px 8px;" title="Print Barcode Label">
                                <i class="fas fa-barcode"></i>
                            </a>
                            <button class="btn btn-sm view-batches-btn" data-id="<?php echo $prod['id']; ?>" data-name="<?php echo htmlspecialchars($prod['name']); ?>" style="background: #e0f2fe; color: #0369a1; padding: 5px 8px;" title="View FEFO Batches">
                                <i class="fas fa-layer-group"></i>
                            </button>
                            <button class="btn btn-sm btn-edit edit-btn" 
                                    data-id="<?php echo $prod['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                    data-generic="<?php echo $prod['generic_id'] ?? ''; ?>"
                                    data-strength="<?php echo htmlspecialchars($prod['strength'] ?? ''); ?>"
                                    data-batch="<?php echo htmlspecialchars($prod['batch_number'] ?? ''); ?>"
                                    data-expiry="<?php echo htmlspecialchars($prod['expiry_date'] ?? ''); ?>"
                                    data-company="<?php echo htmlspecialchars($prod['company_id'] ?? ''); ?>"
                                    data-cat="<?php echo $prod['category_id']; ?>"
                                    data-price="<?php echo $prod['price']; ?>"
                                    data-tradprice="<?php echo $prod['trad_price'] ?? '0.00'; ?>"
                                    data-cost="<?php echo $prod['cost_price'] ?? '0.00'; ?>"
                                    data-qty="<?php echo $prod['quantity'] ?? '0'; ?>"
                                    data-minstock="<?php echo $prod['min_stock_level'] ?? '10'; ?>"
                                    data-rx="<?php echo !empty($prod['is_prescription_required']) ? '1' : '0'; ?>"
                                    data-barcode="<?php echo htmlspecialchars($prod['barcode'] ?? ''); ?>"
                                    title="Edit Medicine">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-delete delete-btn" 
                                    data-id="<?php echo $prod['id']; ?>"
                                    title="Delete Medicine">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1; padding: 40px; text-align: center; background: var(--card-bg, #fff); border-radius: 16px; border: 1px solid var(--surface-border, #cbd5e1);">
                <i class="fas fa-pills" style="font-size: 2.5rem; color: var(--text-muted, #94a3b8); margin-bottom: 12px; display: block;"></i>
                <h3 style="font-size: 1.1rem; color: var(--text-color, #1e293b); margin: 0 0 6px 0;">No medicines found</h3>
                <p style="color: var(--text-muted, #64748b); font-size: 0.9rem; margin: 0;">Add new medicines to your inventory to populate this view.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dual Layout View 2: Table View -->
    <div class="view-table-container card" id="medicineTableContainer" style="padding: 18px; border-radius: 16px; box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03)); border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8))); margin-bottom: 24px;">
        <div class="table-container">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <?php if (!empty($settings['enable_product_images'])): ?>
                        <th>Image</th>
                        <?php endif; ?>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Type / Strength</th>
                        <th>Batch</th>
                        <th>Expiry Date</th>
                        <th>Company</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTbody">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $prod): ?>
                            <?php 
                            $isExpired = $prod['expiry_date'] !== null && strtotime($prod['expiry_date']) <= strtotime(date('Y-m-d')); 
                            $isNearExpiry = $prod['expiry_date'] !== null && !$isExpired && strtotime($prod['expiry_date']) <= strtotime('+6 months');
                            ?>
                            <tr class="<?php echo $isExpired ? 'row-expired' : ($isNearExpiry ? 'row-warning' : ''); ?>"
                                style="<?php echo $isExpired ? 'background-color: rgba(239, 68, 68, 0.08)' : ($isNearExpiry ? 'background-color: rgba(245, 158, 11, 0.08)' : ''); ?>"
                                data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                data-generic="<?php echo htmlspecialchars($prod['generic_name'] ?? ''); ?>"
                                data-category="<?php echo htmlspecialchars($prod['category_name'] ?? ''); ?>"
                                data-catid="<?php echo $prod['category_id'] ?? ''; ?>"
                                data-company="<?php echo htmlspecialchars($prod['company_name'] ?? ''); ?>"
                                data-batch="<?php echo htmlspecialchars($prod['batch_number'] ?? ''); ?>"
                                data-strength="<?php echo htmlspecialchars($prod['strength'] ?? ''); ?>"
                                data-qty="<?php echo $prod['quantity'] ?? 0; ?>"
                                data-minstock="<?php echo $prod['min_stock_level'] ?? 10; ?>"
                                data-is-expired="<?php echo $isExpired ? '1' : '0'; ?>"
                                data-is-expiring="<?php echo $isNearExpiry ? '1' : '0'; ?>">
                                <?php if (!empty($settings['enable_product_images'])): ?>
                                <td>
                                    <?php if($prod['image']): ?>
                                        <img src="<?php echo url($prod['image']); ?>" alt="Img" class="product-thumb">
                                    <?php else: ?>
                                        <div class="img-placeholder"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <strong class="prod-name-text"><?php echo htmlspecialchars($prod['name']); ?></strong>
                                    <?php if (!empty($prod['is_prescription_required'])): ?>
                                        <span class="badge" style="background-color: #ef4444; color: white; font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: 600;" title="Prescription Required"><i class="fas fa-prescription"></i> Rx</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-muted" style="font-style: italic"><?php echo htmlspecialchars($prod['generic_name'] ?? '-'); ?></span></td>
                                <td><?php echo htmlspecialchars($prod['category_name'] ?? '-'); ?> / <?php echo htmlspecialchars($prod['strength'] ?? '-'); ?></td>
                                <td><code><?php echo htmlspecialchars($prod['batch_number'] ?? '-'); ?></code></td>
                                <td>
                                    <?php if ($prod['expiry_date']): ?>
                                        <span class="<?php echo $isExpired ? 'text-danger font-weight-bold' : ($isNearExpiry ? 'text-warning' : ''); ?>">
                                            <?php echo date('M d, Y', strtotime($prod['expiry_date'])); ?>
                                            <?php if ($isExpired): ?> (Expired)<?php elseif ($isNearExpiry): ?> (Expiring)<?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($prod['company_name'] ?? '-'); ?></td>
                                <td><?php echo format_price($prod['price']); ?></td>
                                <td>
                                    <span class="<?php echo $prod['quantity'] < ($prod['min_stock_level'] ?? 10) ? 'text-danger font-weight-bold' : ''; ?>">
                                        <?php echo $prod['quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns" style="display: flex; gap: 4px; align-items: center;">
                                        <a href="<?php echo url('/admin/products/label?id=' . $prod['id']); ?>" target="_blank" class="btn btn-sm" style="background: #f1f5f9; color: #334155; padding: 5px 8px;" title="Print Barcode Label">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                        <button class="btn btn-sm view-batches-btn" data-id="<?php echo $prod['id']; ?>" data-name="<?php echo htmlspecialchars($prod['name']); ?>" style="background: #e0f2fe; color: #0369a1; padding: 5px 8px;" title="View FEFO Batches">
                                            <i class="fas fa-layer-group"></i>
                                        </button>
                                        <button class="btn btn-sm btn-edit edit-btn" 
                                                data-id="<?php echo $prod['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($prod['name']); ?>"
                                                data-generic="<?php echo $prod['generic_id'] ?? ''; ?>"
                                                data-strength="<?php echo htmlspecialchars($prod['strength'] ?? ''); ?>"
                                                data-batch="<?php echo htmlspecialchars($prod['batch_number'] ?? ''); ?>"
                                                data-expiry="<?php echo htmlspecialchars($prod['expiry_date'] ?? ''); ?>"
                                                data-company="<?php echo htmlspecialchars($prod['company_id'] ?? ''); ?>"
                                                data-cat="<?php echo $prod['category_id']; ?>"
                                                data-price="<?php echo $prod['price']; ?>"
                                                data-tradprice="<?php echo $prod['trad_price'] ?? '0.00'; ?>"
                                                data-cost="<?php echo $prod['cost_price'] ?? '0.00'; ?>"
                                                data-qty="<?php echo $prod['quantity'] ?? '0'; ?>"
                                                data-minstock="<?php echo $prod['min_stock_level'] ?? '10'; ?>"
                                                data-rx="<?php echo !empty($prod['is_prescription_required']) ? '1' : '0'; ?>"
                                                data-barcode="<?php echo htmlspecialchars($prod['barcode'] ?? ''); ?>"
                                                title="Edit Medicine">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-delete delete-btn" 
                                                data-id="<?php echo $prod['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo !empty($settings['enable_product_images']) ? 10 : 9; ?>" class="empty-state">
                                <i class="fas fa-pills"></i>
                                No medicines found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px; margin-bottom: 24px;">
        <?php 
        $queryStr = $_GET;
        unset($queryStr['page']);
        $baseUrl = url('/admin/products') . (!empty($queryStr) ? '?' . http_build_query($queryStr) : '');
        echo renderPagination($pagination, $baseUrl); 
        ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="productModal" class="modal">
    <div class="modal-content" style="max-width: 680px">
        <span class="close-modal">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Medicine</h2>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id" id="prodId">
            
            <!-- Wizard Progress Bar -->
            <div class="wizard-progress-container" id="wizardProgressContainer">
                <div class="wizard-progress">
                    <div class="wizard-progress-bar" id="wizardProgressBar"></div>
                    <div class="wizard-step-node active" data-step="1">
                        1 <span class="wizard-step-label">Drug Info</span>
                    </div>
                    <div class="wizard-step-node" data-step="2">
                        2 <span class="wizard-step-label">Inventory</span>
                    </div>
                    <div class="wizard-step-node" data-step="3">
                        3 <span class="wizard-step-label">Pricing</span>
                    </div>
                </div>
            </div>

            <!-- STEP 1: DRUG INFO -->
            <div class="wizard-step-content active" data-step="1">
                <div class="form-group-section-title">1. Medicine Classification</div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px">
                    <div class="form-group">
                        <label for="name">Brand/Medicine Name</label>
                        <input type="text" name="name" id="prodName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="generic_id" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Generic Name (Composition)</span>
                            <a href="javascript:void(0)" id="btnAddNewGeneric" style="font-size: 0.8rem; text-decoration: none; color: var(--primary-color, #3b82f6);"><i class="fas fa-plus"></i> Add New</a>
                        </label>
                        <select name="generic_id" id="prodGeneric" class="form-control">
                            <option value="">Select Generic</option>
                            <?php foreach ($generics as $gen): ?>
                                <option value="<?php echo $gen['id']; ?>"><?php echo htmlspecialchars($gen['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px">
                    <div class="form-group">
                        <label for="strength">Strength (e.g. 500mg, 10ml)</label>
                        <input type="text" name="strength" id="prodStrength" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="category" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Medicine Type</span>
                            <a href="javascript:void(0)" id="btnAddNewCategory" style="font-size: 0.8rem; text-decoration: none; color: var(--primary-color, #3b82f6);"><i class="fas fa-plus"></i> Add New</a>
                        </label>
                        <select name="category_id" id="prodCat" class="form-control">
                            <option value="">Select Type</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 10px; background: rgba(239, 68, 68, 0.05); padding: 12px 14px; border-radius: 10px; border: 1px dashed rgba(239, 68, 68, 0.3);">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; font-size: 0.9rem; color: var(--text-color, #1e293b);">
                        <input type="checkbox" name="is_prescription_required" id="prodRxRequired" value="1" style="width: 18px; height: 18px; accent-color: #ef4444; cursor: pointer;">
                        <span><i class="fas fa-prescription" style="color: #ef4444;"></i> <strong>Prescription Required (Rx)</strong> — POS checkout requires Doctor Name & License</span>
                    </label>
                </div>
            </div>

            <!-- STEP 2: INVENTORY -->
            <div class="wizard-step-content" data-step="2">
                <div class="form-group-section-title">2. Inventory & Logistics</div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px">
                    <div class="form-group">
                        <label for="company_id">Manufacturer / Company</label>
                        <select name="company_id" id="prodCompany" class="form-control">
                            <option value="">Select Company</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?php echo $comp['id']; ?>"><?php echo htmlspecialchars($comp['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity In Stock (Optional)</label>
                        <input type="number" name="quantity" id="prodQty" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="batch_number">Batch Number (Optional)</label>
                        <input type="text" name="batch_number" id="prodBatch" class="form-control" placeholder="e.g. BATCH-101">
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" id="prodExpiry" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="min_stock_level">Min Stock Alert Level</label>
                        <input type="number" name="min_stock_level" id="prodMinStock" class="form-control" value="10">
                    </div>
                    <div class="form-group">
                        <label for="barcode">Barcode (EAN-13 / Code128)</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" name="barcode" id="prodBarcode" class="form-control" placeholder="Scan or enter barcode">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('prodBarcode').value='890'+Math.floor(10000000+Math.random()*90000000)" style="white-space: nowrap; padding: 0 12px;" title="Auto-generate barcode">
                                <i class="fas fa-magic"></i> Auto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PRICING -->
            <div class="wizard-step-content" data-step="3">
                <div class="form-group-section-title">3. Pricing & Details                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px">
                    <div class="form-group">
                        <label for="price">Retail Price *</label>
                        <input type="number" step="0.01" name="price" id="prodPrice" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="trad_price">Trade Price</label>
                        <input type="number" step="0.01" name="trad_price" id="prodTradPrice" class="form-control">
                    </div>
                    <?php if (!empty($settings['track_cost_price'])): ?>
                    <div class="form-group">
                        <label for="cost_price">Cost Price (<?php echo currency_symbol(); ?>)</label>
                        <input type="number" step="0.01" name="cost_price" id="prodCostPrice" class="form-control" required>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="margin-warning" class="alert alert-warning" style="display: none; padding: 8px 12px; margin-top: 10px; font-size: 0.85rem">
                    <i class="fas fa-exclamation-triangle"></i> Warning: Profit margin is below the minimum required limit of <?php echo $settings['profit_margin_warning'] ?? 10; ?>%.
                </div>

                <?php if (!empty($settings['enable_product_images'])): ?>
                <div class="form-group">
                    <label for="image">Medicine Image</label>
                    <input type="file" name="image" id="prodImage" class="form-control" accept="image/*">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-top: 1px solid var(--surface-border, #e2e8f0); padding-top: 15px; margin-top: 15px">
                <div>
                    <button type="button" class="btn btn-cancel" id="wizardBackBtn" style="display: none">Back</button>
                </div>
                <div style="display: flex; gap: 10px">
                    <button type="button" class="btn btn-cancel" id="closeFormBtn" onclick="productModal.style.display='none'">Cancel</button>
                    <button type="button" class="btn btn-primary" id="wizardNextBtn">Next</button>
                    <button type="submit" class="btn btn-primary" id="modalBtn" style="display: none">Save Medicine</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px">
        <span class="close-modal">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title">Confirm Delete</h2>
        </div>
        <div class="delete-modal-icon" style="text-align: center; color: var(--danger-color, #ef4444); font-size: 2.5rem; margin: 15px 0">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="delete-modal-text" style="text-align: center">Are you sure you want to delete this medicine?<br><strong>This action cannot be undone.</strong></p>
        <form method="POST" action="<?php echo url('/admin/products/delete'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="delete_id" id="deleteId">
            <div class="modal-footer" style="justify-content: center">
                <button type="button" class="btn btn-cancel" onclick="deleteModal.style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-danger" style="background-color: var(--danger-color, #ef4444); color: white">Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- FEFO Batches Modal -->
<div id="batchesModal" class="modal">
    <div class="modal-content" style="max-width: 720px">
        <span class="close-modal" id="closeBatchesModal">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title" id="batchesModalTitle"><i class="fas fa-layer-group" style="color: #2563eb; margin-right: 8px;"></i> Batch Stock Details</h2>
        </div>
        <div style="padding: 15px 0;" id="batchesModalBody">
            <div style="text-align: center; padding: 20px; color: #64748b;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading batch data...
            </div>
        </div>
        <div class="modal-footer" style="justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="batchesModal.style.display='none'">Close</button>
        </div>
    </div>
</div>

<!-- Mini Category Modal -->
<div class="cat-mini-modal-overlay" id="catMiniOverlay"></div>
<div class="cat-mini-modal" id="catMiniModal">
    <div class="cat-mini-modal-header">
        <h3>Add New Type</h3>
        <span class="cat-mini-modal-close" id="btnCloseCatMini">&times;</span>
    </div>
    <div class="cat-mini-modal-body">
        <div class="form-group">
            <label for="catMiniName">Type Name</label>
            <input type="text" id="catMiniName" class="form-control" placeholder="e.g. Tablet, Syrup, Capsule" required>
        </div>
        <div class="form-group">
            <label for="catMiniDesc">Description (Optional)</label>
            <textarea id="catMiniDesc" class="form-control" rows="2" placeholder="Brief category description..."></textarea>
        </div>
        <div id="catMiniError" class="text-danger" style="display: none; font-size: 0.85rem; margin-bottom: 10px;"></div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn btn-cancel" id="btnCancelCatMini">Cancel</button>
            <button type="button" class="btn btn-primary" id="btnSaveCatMini">Save Type</button>
        </div>
    </div>
</div>

<!-- Mini Generic Modal -->
<div class="cat-mini-modal-overlay" id="genMiniOverlay"></div>
<div class="cat-mini-modal" id="genMiniModal">
    <div class="cat-mini-modal-header">
        <h3>Add New Generic Name</h3>
        <span class="cat-mini-modal-close" id="btnCloseGenMini">&times;</span>
    </div>
    <div class="cat-mini-modal-body">
        <div class="form-group">
            <label for="genMiniName">Generic Name</label>
            <input type="text" id="genMiniName" class="form-control" placeholder="e.g. Paracetamol" required>
        </div>
        <div class="form-group">
            <label for="genMiniDesc">Description (Optional)</label>
            <textarea id="genMiniDesc" class="form-control" rows="2" placeholder="Brief description..."></textarea>
        </div>
        <div id="genMiniError" class="text-danger" style="display: none; font-size: 0.85rem; margin-bottom: 10px;"></div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn btn-cancel" id="btnCancelGenMini">Cancel</button>
            <button type="button" class="btn btn-primary" id="btnSaveGenMini">Save Generic</button>
        </div>
    </div>
</div>

<!-- Duplicate Medicine Warning Pop-Up Modal -->
<div id="duplicateMedicineModal" class="modal" style="z-index: 1050; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
    <div class="modal-content" style="max-width: 650px; border-radius: 16px; box-shadow: var(--shadow-lg, 0 20px 25px -5px rgba(0, 0, 0, 0.3)); border: 1px solid var(--surface-border, #e2e8f0); padding: 24px;">
        <span class="close-modal" id="closeDupModal" style="float: right; font-size: 1.5rem; font-weight: bold; cursor: pointer; color: var(--text-muted, #94a3b8);">&times;</span>
        
        <div class="modal-header" style="display: flex; align-items: flex-start; gap: 14px; border-bottom: 1px solid var(--surface-border, #e2e8f0); padding-bottom: 16px; margin-bottom: 18px;">
            <div style="background: rgba(245, 158, 11, 0.15); color: #d97706; min-width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 4px 0; color: var(--text-color, #1e293b);">Medicine Name Already Saved</h3>
                <p style="font-size: 0.88rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.4;">The medicine name you entered (or similar related names) is already saved in your database.</p>
            </div>
        </div>
        
        <div class="modal-body" style="padding: 0 0 10px 0;">
            <div style="background: var(--hover-bg, #f8fafc); border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px;">
                <span style="font-size: 0.8rem; text-transform: uppercase; tracking: 0.5px; color: var(--text-muted, #64748b); font-weight: 600; display: block;">Entered Medicine Name:</span>
                <strong id="dupEnteredName" style="font-size: 1.2rem; color: var(--text-color, #0f172a); font-weight: 700;">-</strong>
            </div>

            <div>
                <h4 style="font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #64748b); margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-pills" style="color: var(--primary-color, #3b82f6);"></i> Related Saved Medicines in System:
                </h4>
                
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--surface-border, #cbd5e1); border-radius: 10px; background: var(--card-bg, #ffffff);">
                    <table class="table" style="margin: 0; font-size: 0.86rem; width: 100%; border-collapse: collapse;">
                        <thead style="position: sticky; top: 0; background: var(--surface-border, #f1f5f9); z-index: 2;">
                            <tr>
                                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Brand Name</th>
                                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Generic Composition</th>
                                <th style="padding: 10px 12px; text-align: left; font-weight: 600;">Type / Strength</th>
                                <th style="padding: 10px 12px; text-align: right; font-weight: 600;">Price</th>
                                <th style="padding: 10px 12px; text-align: center; font-weight: 600;">Stock Qty</th>
                            </tr>
                        </thead>
                        <tbody id="dupRelatedListTbody">
                            <!-- Populated dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-top: 1px solid var(--surface-border, #e2e8f0); padding-top: 16px; margin-top: 18px; gap: 12px;">
            <button type="button" class="btn btn-secondary" id="btnDupEditName" style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                <i class="fas fa-edit"></i> Edit Medicine Name
            </button>
            <button type="button" class="btn btn-primary" id="btnDupProceed" style="background-color: var(--primary-color, #3b82f6); color: white; display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none;">
                <i class="fas fa-arrow-right"></i> Proceed Anyway
            </button>
        </div>
    </div>
</div>

<script>
    // Modal Logic
    const productModal = document.getElementById('productModal');
    const deleteModal = document.getElementById('deleteModal');
    const openAddBtn = document.getElementById('openAddModal');
    const closeBtns = document.querySelectorAll('.close-modal');
    
    // Duplicate Medicine Checking Logic & Pop-Up Modal
    const duplicateMedicineModal = document.getElementById('duplicateMedicineModal');
    const dupEnteredName = document.getElementById('dupEnteredName');
    const dupRelatedListTbody = document.getElementById('dupRelatedListTbody');
    const btnDupProceed = document.getElementById('btnDupProceed');
    const btnDupEditName = document.getElementById('btnDupEditName');
    const closeDupModal = document.getElementById('closeDupModal');
    const prodNameInput = document.getElementById('prodName');

    let userBypassedDuplicate = false;
    let dupOnProceedCallback = null;

    function hideDuplicateModal() {
        if (duplicateMedicineModal) {
            duplicateMedicineModal.style.display = 'none';
        }
    }

    function checkAndShowDuplicateModal(onClearOrProceed) {
        if (!prodNameInput) {
            if (onClearOrProceed) onClearOrProceed();
            return;
        }

        const nameVal = prodNameInput.value.trim();
        const prodIdVal = document.getElementById('prodId') ? document.getElementById('prodId').value : '';

        if (!nameVal || userBypassedDuplicate) {
            if (onClearOrProceed) onClearOrProceed();
            return;
        }

        const checkUrl = '<?php echo url('/api/products/check_duplicate'); ?>?name=' + encodeURIComponent(nameVal) + '&exclude_id=' + encodeURIComponent(prodIdVal);

        fetch(checkUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && res.exists && res.related && res.related.length > 0) {
                dupOnProceedCallback = onClearOrProceed || null;
                if (dupEnteredName) {
                    dupEnteredName.textContent = res.entered_name || nameVal;
                }

                if (dupRelatedListTbody) {
                    dupRelatedListTbody.innerHTML = '';
                    res.related.forEach(item => {
                        const tr = document.createElement('tr');
                        const isExact = res.exact_match && item.name.toLowerCase().trim() === nameVal.toLowerCase().trim();
                        if (isExact) {
                            tr.style.backgroundColor = 'rgba(245, 158, 11, 0.1)';
                        }

                        tr.innerHTML = `
                            <td style="padding: 9px 12px;">
                                <strong>${escapeHtml(item.name)}</strong>
                                ${isExact ? '<span class="badge" style="background:#f59e0b; color:#fff; font-size:0.7rem; padding:2px 6px; border-radius:4px; margin-left:6px;">Exact Match</span>' : ''}
                            </td>
                            <td style="padding: 9px 12px; color: var(--text-muted, #64748b); font-style: italic;">
                                ${escapeHtml(item.generic_name || '-')}
                            </td>
                            <td style="padding: 9px 12px;">
                                ${escapeHtml((item.category_name || '-') + ' / ' + (item.strength || '-'))}
                            </td>
                            <td style="padding: 9px 12px; text-align: right; font-weight: 600;">
                                ${parseFloat(item.price || 0).toFixed(2)}
                            </td>
                            <td style="padding: 9px 12px; text-align: center;">
                                <span class="${parseInt(item.quantity || 0) < 10 ? 'text-danger font-weight-bold' : ''}">
                                    ${parseInt(item.quantity || 0)}
                                </span>
                            </td>
                        `;
                        dupRelatedListTbody.appendChild(tr);
                    });
                }

                if (duplicateMedicineModal) {
                    duplicateMedicineModal.style.display = 'block';
                }
            } else {
                if (onClearOrProceed) onClearOrProceed();
            }
        })
        .catch(err => {
            console.error('Duplicate check error:', err);
            if (onClearOrProceed) onClearOrProceed();
        });
    }

    if (btnDupProceed) {
        btnDupProceed.onclick = function() {
            userBypassedDuplicate = true;
            hideDuplicateModal();
            if (dupOnProceedCallback) {
                const cb = dupOnProceedCallback;
                dupOnProceedCallback = null;
                cb();
            }
        };
    }

    if (btnDupEditName) {
        btnDupEditName.onclick = function() {
            hideDuplicateModal();
            dupOnProceedCallback = null;
            if (typeof goToStep === 'function') {
                goToStep(1);
            }
            if (prodNameInput) {
                prodNameInput.focus();
                prodNameInput.select();
            }
        };
    }

    if (closeDupModal) {
        closeDupModal.onclick = function() {
            hideDuplicateModal();
            dupOnProceedCallback = null;
        };
    }

    if (prodNameInput) {
        prodNameInput.addEventListener('input', function() {
            userBypassedDuplicate = false;
        });
    }

    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            const prodIdVal = document.getElementById('prodId') ? document.getElementById('prodId').value : '';
            // Only trigger duplicate check pop-up when ADDING a NEW medicine (prodId is empty)
            if (!prodIdVal && !userBypassedDuplicate && prodNameInput && prodNameInput.value.trim() !== '') {
                e.preventDefault();
                checkAndShowDuplicateModal(function() {
                    userBypassedDuplicate = true;
                    productForm.submit();
                });
            }
        });
    }

    // Wizard State
    let currentStep = 1;
    
    function goToStep(step) {
        currentStep = step;
        const wizardBackBtn = document.getElementById('wizardBackBtn');
        const wizardNextBtn = document.getElementById('wizardNextBtn');
        const modalBtn = document.getElementById('modalBtn');
        const closeFormBtn = document.getElementById('closeFormBtn');
        const progressBar = document.getElementById('wizardProgressBar');

        // Toggle visibility of step contents
        document.querySelectorAll('.wizard-step-content').forEach(el => {
            el.style.display = 'none';
        });
        const activeContent = document.querySelector(`.wizard-step-content[data-step="${step}"]`);
        if (activeContent) activeContent.style.display = 'block';

        // Update indicators
        document.querySelectorAll('.wizard-step-node').forEach(node => {
            const nodeStep = parseInt(node.getAttribute('data-step'));
            node.classList.remove('active', 'completed');
            if (nodeStep === step) {
                node.classList.add('active');
            } else if (nodeStep < step) {
                node.classList.add('completed');
            }
        });

        // Update Progress Bar Line Width
        if (progressBar) {
            const percent = ((step - 1) / 2) * 100;
            progressBar.style.width = `${percent}%`;
        }

        // Handle buttons
        if (step === 1) {
            if (wizardBackBtn) wizardBackBtn.style.display = 'none';
            if (wizardNextBtn) wizardNextBtn.style.display = 'block';
            if (modalBtn) modalBtn.style.display = 'none';
            if (closeFormBtn) closeFormBtn.style.display = 'block';
        } else if (step === 2) {
            if (wizardBackBtn) wizardBackBtn.style.display = 'block';
            if (wizardNextBtn) wizardNextBtn.style.display = 'block';
            if (modalBtn) modalBtn.style.display = 'none';
            if (closeFormBtn) closeFormBtn.style.display = 'none';
        } else if (step === 3) {
            if (wizardBackBtn) wizardBackBtn.style.display = 'block';
            if (wizardNextBtn) wizardNextBtn.style.display = 'none';
            if (modalBtn) modalBtn.style.display = 'block';
            if (closeFormBtn) closeFormBtn.style.display = 'none';
        }
    }

    function validateStep(step) {
        const stepContent = document.querySelector(`.wizard-step-content[data-step="${step}"]`);
        if (!stepContent) return true;
        
        const inputs = stepContent.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.reportValidity();
                isValid = false;
            }
        });
        
        return isValid;
    }

    const wizardNextBtn = document.getElementById('wizardNextBtn');
    const wizardBackBtn = document.getElementById('wizardBackBtn');

    if (wizardNextBtn) {
        wizardNextBtn.onclick = function() {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        };
    }

    if (wizardBackBtn) {
        wizardBackBtn.onclick = function() {
            goToStep(currentStep - 1);
        };
    }

    // Add Mode
    openAddBtn.onclick = function() {
        userBypassedDuplicate = false;
        document.getElementById('modalTitle').textContent = "Add Medicine";
        document.getElementById('productForm').action = "<?php echo url('/admin/products/store'); ?>";
        document.getElementById('prodId').value = "";
        document.getElementById('prodName').value = "";
        document.getElementById('prodGeneric').value = "";
        document.getElementById('prodStrength').value = "";
        if(document.getElementById('prodBatch')) document.getElementById('prodBatch').value = "";
        if(document.getElementById('prodExpiry')) document.getElementById('prodExpiry').value = "";
        if(document.getElementById('prodCompany')) document.getElementById('prodCompany').value = "";
        document.getElementById('prodCat').value = "";
        document.getElementById('prodPrice').value = "";
        document.getElementById('prodTradPrice').value = "";
        if (document.getElementById('prodCostPrice')) {
            document.getElementById('prodCostPrice').value = "";
        }
        if (document.getElementById('margin-warning')) {
            document.getElementById('margin-warning').style.display = "none";
        }
        document.getElementById('prodQty').value = "";
        document.getElementById('prodMinStock').value = "10";
        if (document.getElementById('prodRxRequired')) {
            document.getElementById('prodRxRequired').checked = false;
        }
        if (document.getElementById('prodBarcode')) {
            document.getElementById('prodBarcode').value = "";
        }
        document.getElementById('modalBtn').textContent = "Save Medicine";
        
        if (window.tsGeneric) window.tsGeneric.clear();
        if (window.tsCategory) window.tsCategory.clear();
        if (window.tsCompany) window.tsCompany.clear();

        
        goToStep(1); // Reset wizard
        productModal.style.display = "block";
    }

    // Edit Mode
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('modalTitle').textContent = "Edit Medicine";
            document.getElementById('productForm').action = "<?php echo url('/admin/products/update'); ?>";
            document.getElementById('prodId').value = this.getAttribute('data-id');
            document.getElementById('prodName').value = this.getAttribute('data-name');
            document.getElementById('prodStrength').value = this.getAttribute('data-strength');
            
            if(document.getElementById('prodBatch')) document.getElementById('prodBatch').value = this.getAttribute('data-batch') || "";
            if(document.getElementById('prodExpiry')) document.getElementById('prodExpiry').value = this.getAttribute('data-expiry') || "";
            if(document.getElementById('prodBarcode')) document.getElementById('prodBarcode').value = this.getAttribute('data-barcode') || "";
            
            if (window.tsGeneric) window.tsGeneric.setValue(this.getAttribute('data-generic'));
            else document.getElementById('prodGeneric').value = this.getAttribute('data-generic');
            
            if (window.tsCompany && this.getAttribute('data-company')) window.tsCompany.setValue(this.getAttribute('data-company'));
            else if(document.getElementById('prodCompany')) document.getElementById('prodCompany').value = this.getAttribute('data-company');
            
            if (window.tsCategory) window.tsCategory.setValue(this.getAttribute('data-cat'));
            else document.getElementById('prodCat').value = this.getAttribute('data-cat');

            document.getElementById('prodPrice').value = this.getAttribute('data-price');
            document.getElementById('prodTradPrice').value = this.getAttribute('data-tradprice');
            if (document.getElementById('prodCostPrice')) {
                document.getElementById('prodCostPrice').value = this.getAttribute('data-cost');
            }
            document.getElementById('prodQty').value = this.getAttribute('data-qty');
            document.getElementById('prodMinStock').value = this.getAttribute('data-minstock');
            if (document.getElementById('prodRxRequired')) {
                document.getElementById('prodRxRequired').checked = (this.getAttribute('data-rx') === '1');
            }
            document.getElementById('modalBtn').textContent = "Update Medicine";
            
            if (typeof checkProfitMargin === 'function') {
                checkProfitMargin();
            }
            
            goToStep(1); // Reset wizard
            productModal.style.display = "block";
        }
    });

    // Batches Mode
    const batchesModal = document.getElementById('batchesModal');
    const closeBatchesModal = document.getElementById('closeBatchesModal');
    if (closeBatchesModal && batchesModal) {
        closeBatchesModal.onclick = function() { batchesModal.style.display = 'none'; };
    }

    document.querySelectorAll('.view-batches-btn').forEach(btn => {
        btn.onclick = function() {
            const pId = this.getAttribute('data-id');
            const pName = this.getAttribute('data-name');
            const modalTitle = document.getElementById('batchesModalTitle');
            const modalBody = document.getElementById('batchesModalBody');

            if (modalTitle) modalTitle.innerHTML = `<i class="fas fa-layer-group" style="color: #2563eb; margin-right: 8px;"></i> FEFO Batches: ${escapeHtml(pName)}`;
            if (modalBody) modalBody.innerHTML = `<div style="text-align: center; padding: 20px; color: #64748b;"><i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i> Loading batch data...</div>`;
            
            if (batchesModal) batchesModal.style.display = 'block';

            fetch(`<?php echo url('/api/products/batches?id='); ?>${pId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.batches && data.batches.length > 0) {
                        let html = `
                            <table class="table" style="width: 100%; font-size: 13px;">
                                <thead>
                                    <tr>
                                        <th>Batch Number</th>
                                        <th>Expiry Date</th>
                                        <th style="text-align: right;">Remaining Qty</th>
                                        <th style="text-align: right;">Cost Price</th>
                                        <th>Receive Ref / Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        const today = new Date().toISOString().split('T')[0];
                        data.batches.forEach(b => {
                            const isExp = b.expiry_date && b.expiry_date <= today;
                            const isZero = parseInt(b.quantity) <= 0;
                            html += `
                                <tr style="${isExp ? 'background: rgba(239, 68, 68, 0.06);' : (isZero ? 'opacity: 0.6;' : '')}">
                                    <td><strong><code>${escapeHtml(b.batch_number || 'DEFAULT')}</code></strong></td>
                                    <td>
                                        ${b.expiry_date ? `<span class="${isExp ? 'text-danger font-weight-bold' : ''}">${escapeHtml(b.expiry_date)} ${isExp ? '(Expired)' : ''}</span>` : '<span class="text-muted">No Expiry</span>'}
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">
                                        <span class="${parseInt(b.quantity) <= 0 ? 'text-muted' : 'text-success'}">${escapeHtml(b.quantity)}</span>
                                    </td>
                                    <td style="text-align: right;">${parseFloat(b.cost_price || 0).toFixed(2)}</td>
                                    <td>
                                        <small class="text-muted">
                                            ${b.receive_invoice_number ? `<i class="fas fa-truck-loading"></i> ${escapeHtml(b.receive_invoice_number)}` : 'Manual / Initial'}
                                            ${b.supplier_name ? ` (${escapeHtml(b.supplier_name)})` : ''}
                                        </small>
                                    </td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table>`;
                        modalBody.innerHTML = html;
                    } else {
                        modalBody.innerHTML = `<div style="text-align: center; padding: 30px; color: #64748b;"><i class="fas fa-box-open" style="font-size: 32px; margin-bottom: 8px; display: block;"></i> No active or historical batches recorded for this medicine.</div>`;
                    }
                })
                .catch(err => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading batch information.</div>`;
                });
        };
    });

    // Delete Mode
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('deleteId').value = this.getAttribute('data-id');
            deleteModal.style.display = "block";
        }
    });

    // Close Modals
    closeBtns.forEach(span => {
        span.onclick = function() {
            productModal.style.display = "none";
            deleteModal.style.display = "none";
            if (batchesModal) batchesModal.style.display = "none";
            if (typeof duplicateMedicineModal !== 'undefined' && duplicateMedicineModal) {
                duplicateMedicineModal.style.display = "none";
            }
        }
    });

    window.onclick = function(event) {
        if (event.target == productModal) productModal.style.display = "none";
        if (event.target == deleteModal) deleteModal.style.display = "none";
        if (batchesModal && event.target == batchesModal) batchesModal.style.display = "none";
        if (typeof duplicateMedicineModal !== 'undefined' && event.target == duplicateMedicineModal) {
            duplicateMedicineModal.style.display = "none";
        }
    }

    // Profit margin live check
    const marginWarning = document.getElementById('margin-warning');
    const priceInput = document.getElementById('prodPrice');
    const costInput = document.getElementById('prodCostPrice');
    const minMargin = parseFloat("<?php echo $settings['profit_margin_warning'] ?? 10; ?>");

    function checkProfitMargin() {
        if (!marginWarning || !priceInput || !costInput) return;
        const price = parseFloat(priceInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        if (price > 0 && cost > 0) {
            const margin = ((price - cost) / price) * 100;
            if (margin < minMargin) {
                marginWarning.style.display = 'block';
            } else {
                marginWarning.style.display = 'none';
            }
        } else {
            marginWarning.style.display = 'none';
        }
    }

    if (priceInput) priceInput.addEventListener('input', checkProfitMargin);
    if (costInput) costInput.addEventListener('input', checkProfitMargin);

    // Auto-round price inputs to 2 decimal places
    ['prodPrice', 'prodTradPrice', 'prodCostPrice'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                    if (id === 'prodPrice' || id === 'prodCostPrice') {
                        checkProfitMargin();
                    }
                }
            });
        }
    });

    // ==========================================
    // MINI MODAL CATEGORY ADDITION
    // ==========================================
    const btnAddNewCategory = document.getElementById('btnAddNewCategory');
    const catMiniModal = document.getElementById('catMiniModal');
    const catMiniOverlay = document.getElementById('catMiniOverlay');
    const btnCloseCatMini = document.getElementById('btnCloseCatMini');
    const btnCancelCatMini = document.getElementById('btnCancelCatMini');
    const btnSaveCatMini = document.getElementById('btnSaveCatMini');
    const catMiniName = document.getElementById('catMiniName');
    const catMiniDesc = document.getElementById('catMiniDesc');
    const prodCat = document.getElementById('prodCat');
    const catMiniError = document.getElementById('catMiniError');

    function openCatMini() {
        if (catMiniOverlay) catMiniOverlay.style.display = 'block';
        if (catMiniModal) {
            catMiniModal.classList.add('show');
            if (catMiniName) {
                setTimeout(() => catMiniName.focus(), 100);
            }
        }
    }

    function closeCatMini() {
        if (catMiniModal) catMiniModal.classList.remove('show');
        if (catMiniOverlay) {
            setTimeout(() => { catMiniOverlay.style.display = 'none'; }, 200);
        }
        if (catMiniName) catMiniName.value = '';
        if (catMiniDesc) catMiniDesc.value = '';
        if (catMiniError) catMiniError.style.display = 'none';
    }

    if (btnAddNewCategory) {
        btnAddNewCategory.onclick = function(e) {
            e.preventDefault();
            openCatMini();
        };
    }

    if (btnCloseCatMini) btnCloseCatMini.onclick = closeCatMini;
    if (btnCancelCatMini) btnCancelCatMini.onclick = closeCatMini;
    if (catMiniOverlay) catMiniOverlay.onclick = closeCatMini;

    if (btnSaveCatMini) {
        btnSaveCatMini.onclick = function() {
            const name = catMiniName.value.trim();
            const desc = catMiniDesc.value.trim();
            catMiniError.style.display = 'none';

            if (!name) {
                catMiniError.textContent = 'Type name is required.';
                catMiniError.style.display = 'block';
                return;
            }

            btnSaveCatMini.disabled = true;
            btnSaveCatMini.textContent = 'Saving...';

            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', desc);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('<?php echo url('/admin/categories/ajax-add'); ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                btnSaveCatMini.disabled = false;
                btnSaveCatMini.textContent = 'Save Type';

                if (res.success) {
                    let exists = false;
                    for (let i = 0; i < prodCat.options.length; i++) {
                        if (prodCat.options[i].value == res.data.id) {
                            exists = true;
                            break;
                        }
                    }

                    if (!exists) {
                        const opt = document.createElement('option');
                        opt.value = res.data.id;
                        opt.textContent = res.data.name;
                        prodCat.appendChild(opt);
                        if (window.tsCategory) {
                            window.tsCategory.addOption({value: res.data.id, text: res.data.name});
                        }
                    }

                    if (window.tsCategory) {
                        window.tsCategory.setValue(res.data.id);
                    } else {
                        prodCat.value = res.data.id;
                    }
                    closeCatMini();
                } else {
                    catMiniError.textContent = res.message || 'Failed to save type.';
                    catMiniError.style.display = 'block';
                }
            })
            .catch(err => {
                btnSaveCatMini.disabled = false;
                btnSaveCatMini.textContent = 'Save Type';
                catMiniError.textContent = 'An error occurred. Please try again.';
                catMiniError.style.display = 'block';
                console.error(err);
            });
        };
    }

    // ==========================================
    // MINI MODAL GENERIC ADDITION
    // ==========================================
    const btnAddNewGeneric = document.getElementById('btnAddNewGeneric');
    const genMiniModal = document.getElementById('genMiniModal');
    const genMiniOverlay = document.getElementById('genMiniOverlay');
    const btnCloseGenMini = document.getElementById('btnCloseGenMini');
    const btnCancelGenMini = document.getElementById('btnCancelGenMini');
    const btnSaveGenMini = document.getElementById('btnSaveGenMini');
    const genMiniName = document.getElementById('genMiniName');
    const genMiniDesc = document.getElementById('genMiniDesc');
    const prodGeneric = document.getElementById('prodGeneric');
    const genMiniError = document.getElementById('genMiniError');

    function openGenMini() {
        if (genMiniOverlay) genMiniOverlay.style.display = 'block';
        if (genMiniModal) {
            genMiniModal.classList.add('show');
            if (genMiniName) {
                setTimeout(() => genMiniName.focus(), 100);
            }
        }
    }

    function closeGenMini() {
        if (genMiniModal) genMiniModal.classList.remove('show');
        if (genMiniOverlay) {
            setTimeout(() => { genMiniOverlay.style.display = 'none'; }, 200);
        }
        if (genMiniName) genMiniName.value = '';
        if (genMiniDesc) genMiniDesc.value = '';
        if (genMiniError) genMiniError.style.display = 'none';
    }

    if (btnAddNewGeneric) {
        btnAddNewGeneric.onclick = function(e) {
            e.preventDefault();
            openGenMini();
        };
    }

    if (btnCloseGenMini) btnCloseGenMini.onclick = closeGenMini;
    if (btnCancelGenMini) btnCancelGenMini.onclick = closeGenMini;
    if (genMiniOverlay) genMiniOverlay.onclick = closeGenMini;

    if (btnSaveGenMini) {
        btnSaveGenMini.onclick = function() {
            const name = genMiniName.value.trim();
            const desc = genMiniDesc.value.trim();
            genMiniError.style.display = 'none';

            if (!name) {
                genMiniError.textContent = 'Generic name is required.';
                genMiniError.style.display = 'block';
                return;
            }

            btnSaveGenMini.disabled = true;
            btnSaveGenMini.textContent = 'Saving...';

            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', desc);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('<?php echo url('/admin/generics/ajax-add'); ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                btnSaveGenMini.disabled = false;
                btnSaveGenMini.textContent = 'Save Generic';

                if (res.success) {
                    let exists = false;
                    for (let i = 0; i < prodGeneric.options.length; i++) {
                        if (prodGeneric.options[i].value == res.data.id) {
                            exists = true;
                            break;
                        }
                    }

                    if (!exists) {
                        const opt = document.createElement('option');
                        opt.value = res.data.id;
                        opt.textContent = res.data.name;
                        prodGeneric.appendChild(opt);
                        if (window.tsGeneric) {
                            window.tsGeneric.addOption({value: res.data.id, text: res.data.name});
                        }
                    }

                    if (window.tsGeneric) {
                        window.tsGeneric.setValue(res.data.id);
                    } else {
                        prodGeneric.value = res.data.id;
                    }
                    closeGenMini();
                } else {
                    genMiniError.textContent = res.message || 'Failed to save generic.';
                    genMiniError.style.display = 'block';
                }
            })
            .catch(err => {
                btnSaveGenMini.disabled = false;
                btnSaveGenMini.textContent = 'Save Generic';
                genMiniError.textContent = 'An error occurred. Please try again.';
                genMiniError.style.display = 'block';
                console.error(err);
            });
        };
    }
    
    // -----------------------------------------------------------------
    // View Mode Switcher (Grid vs Table Dual Layout)
    // -----------------------------------------------------------------
    let currentViewMode = localStorage.getItem('med_view_mode') || 'grid';

    function setViewMode(mode) {
        currentViewMode = mode;
        localStorage.setItem('med_view_mode', mode);

        const gridContainer = document.getElementById('medicineGrid');
        const tableContainer = document.getElementById('medicineTableContainer');

        const gridBtn = document.getElementById('viewGridBtn');
        const tableBtn = document.getElementById('viewTableBtn');

        if (mode === 'grid') {
            if (gridContainer) gridContainer.style.display = 'grid';
            if (tableContainer) tableContainer.style.display = 'none';
            if (gridBtn) gridBtn.classList.add('active');
            if (tableBtn) tableBtn.classList.remove('active');
        } else {
            if (gridContainer) gridContainer.style.display = 'none';
            if (tableContainer) tableContainer.style.display = 'block';
            if (gridBtn) gridBtn.classList.remove('active');
            if (tableBtn) tableBtn.classList.add('active');
        }

        runTableFilter();
    }

    // -----------------------------------------------------------------
    // Instant Live Dual Filtering (Grid Cards & Table Rows)
    // -----------------------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function runTableFilter() {
        const searchInputEl = document.getElementById('searchInput');
        const statusSelectEl = document.getElementById('statusFilterSelect');
        const catSelectEl = document.getElementById('categoryFilterSelect');
        
        if (!searchInputEl) return;

        const filter = searchInputEl.value.toLowerCase().trim();
        const statusFilter = statusSelectEl ? statusSelectEl.value.toLowerCase() : 'all';
        const catFilter = catSelectEl ? catSelectEl.value.toLowerCase() : 'all';

        if (clearSearchBtn) {
            clearSearchBtn.style.display = filter !== '' ? 'block' : 'none';
        }

        // Helper filter matcher
        function checkMatches(itemData) {
            const name = (itemData.name || '').toLowerCase();
            const generic = (itemData.generic || '').toLowerCase();
            const company = (itemData.company || '').toLowerCase();
            const category = (itemData.category || '').toLowerCase();
            const catId = (itemData.catid || '').toLowerCase();
            const batch = (itemData.batch || '').toLowerCase();
            const strength = (itemData.strength || '').toLowerCase();
            const qty = parseInt(itemData.qty || 0);
            const minStock = parseInt(itemData.minstock || 10);
            const isExpired = itemData.isExpired === '1';
            const isExpiring = itemData.isExpiring === '1';

            const matchesSearch = !filter || name.includes(filter) || generic.includes(filter) || company.includes(filter) || category.includes(filter) || batch.includes(filter) || strength.includes(filter);
            
            let matchesStatus = true;
            if (statusFilter === 'instock') matchesStatus = (qty > 0);
            else if (statusFilter === 'lowstock') matchesStatus = (qty > 0 && qty < minStock);
            else if (statusFilter === 'outstock') matchesStatus = (qty <= 0);
            else if (statusFilter === 'expiring') matchesStatus = isExpiring;
            else if (statusFilter === 'expired') matchesStatus = isExpired;

            let matchesCat = true;
            if (catFilter !== 'all') {
                matchesCat = (catId === catFilter || category === catFilter);
            }

            return matchesSearch && matchesStatus && matchesCat;
        }

        // 1. Filter Grid Cards
        const gridContainer = document.getElementById('medicineGrid');
        let visibleGridCount = 0;
        if (gridContainer) {
            const cards = gridContainer.querySelectorAll('.medicine-card');
            cards.forEach(card => {
                const matches = checkMatches({
                    name: card.dataset.name,
                    generic: card.dataset.generic,
                    company: card.dataset.company,
                    category: card.dataset.category,
                    catid: card.dataset.catid,
                    batch: card.dataset.batch,
                    strength: card.dataset.strength,
                    qty: card.dataset.qty,
                    minstock: card.dataset.minstock,
                    isExpired: card.dataset.isExpired,
                    isExpiring: card.dataset.isExpiring
                });

                if (matches) {
                    card.style.display = 'flex';
                    visibleGridCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            let noResultsGrid = document.getElementById('noResultsGrid');
            if (visibleGridCount === 0 && cards.length > 0) {
                if (!noResultsGrid) {
                    noResultsGrid = document.createElement('div');
                    noResultsGrid.id = 'noResultsGrid';
                    noResultsGrid.className = 'empty-state';
                    noResultsGrid.style.cssText = 'grid-column: 1 / -1; padding: 40px; text-align: center; background: var(--card-bg, #fff); border-radius: 16px; border: 1px solid var(--surface-border, #cbd5e1);';
                    noResultsGrid.innerHTML = `
                        <i class="fas fa-search" style="font-size: 2.5rem; color: var(--text-muted, #94a3b8); margin-bottom: 12px; display: block;"></i>
                        <h3 style="font-size: 1.1rem; color: var(--text-color, #1e293b); margin: 0 0 6px 0;">No medicines found</h3>
                        <p style="color: var(--text-muted, #64748b); font-size: 0.9rem; margin: 0;">No medicines match your current search query or filter options.</p>
                    `;
                    gridContainer.appendChild(noResultsGrid);
                }
                noResultsGrid.style.display = 'block';
            } else if (noResultsGrid) {
                noResultsGrid.style.display = 'none';
            }
        }

        // 2. Filter Table Rows
        const productsTbody = document.getElementById('productsTbody');
        let visibleTableCount = 0;
        if (productsTbody) {
            const rows = productsTbody.querySelectorAll('tr:not(.no-results-row)');
            rows.forEach(row => {
                const matches = checkMatches({
                    name: row.dataset.name,
                    generic: row.dataset.generic,
                    company: row.dataset.company,
                    category: row.dataset.category,
                    catid: row.dataset.catid,
                    batch: row.dataset.batch,
                    strength: row.dataset.strength,
                    qty: row.dataset.qty,
                    minstock: row.dataset.minstock,
                    isExpired: row.dataset.isExpired,
                    isExpiring: row.dataset.isExpiring
                });

                if (matches) {
                    row.style.display = '';
                    visibleTableCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            let noResultsRow = document.getElementById('noResultsRow');
            if (visibleTableCount === 0 && rows.length > 0) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noResultsRow';
                    noResultsRow.className = 'no-results-row';
                    const colCount = productsTbody.closest('table').querySelectorAll('thead th').length;
                    noResultsRow.innerHTML = `
                        <td colspan="${colCount}" class="empty-state" style="padding: 30px; text-align: center;">
                            <i class="fas fa-search" style="font-size: 2rem; color: var(--text-muted, #94a3b8); margin-bottom: 10px; display: block;"></i>
                            No medicines found matching your criteria.
                        </td>
                    `;
                    productsTbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', runTableFilter);
    }

    if (clearSearchBtn && searchInput) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            runTableFilter();
            searchInput.focus();
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Initialize view mode from localStorage
        setViewMode(currentViewMode);

        if(document.getElementById('prodGeneric')) {
            window.tsGeneric = new TomSelect('#prodGeneric', { create: false, sortField: {field: "text", direction: "asc"} });
        }
        if(document.getElementById('prodCat')) {
            window.tsCategory = new TomSelect('#prodCat', { create: false, sortField: {field: "text", direction: "asc"} });
        }
        if(document.getElementById('prodCompany')) {
            window.tsCompany = new TomSelect('#prodCompany', { create: false, sortField: {field: "text", direction: "asc"} });
        }
    });
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
