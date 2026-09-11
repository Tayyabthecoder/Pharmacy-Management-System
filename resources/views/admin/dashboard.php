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
            <p>Here's your real-time operational snapshot for today.</p>
        </div>
        <div class="dash-welcome-meta">
            <div class="dash-date-badge">
                <i class="far fa-calendar-alt"></i>
                <?php echo date('l, F d, Y'); ?>
            </div>
        </div>
    </div>

    <!-- ════════════ Critical Expiry Alert ════════════ -->
    <?php if ($expiredCount > 0): ?>
        <div class="dash-alert-banner pulse-warning dash-animate dash-delay-1">
            <div class="dash-alert-content">
                <div class="dash-alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="dash-alert-text">
                    <strong>Critical Alert:</strong> You have <strong><?php echo $expiredCount; ?></strong> expired medicine(s) in active inventory. Immediate removal from shelf is required.
                </div>
            </div>
            <a href="<?php echo url('/admin/expiry_report'); ?>" class="dash-alert-action">
                <i class="fas fa-arrow-right"></i> Review Expiry Report
            </a>
        </div>
    <?php endif; ?>

    <!-- ════════════ Quick Action Shortcuts ════════════ -->
    <div class="quick-actions-container dash-animate dash-delay-2">
        <a href="<?php echo url('/admin/create_invoice'); ?>" class="quick-action-btn qa-invoice">
            <i class="fas fa-file-invoice-dollar"></i> New Invoice
        </a>
        <a href="<?php echo url('/admin/products'); ?>" class="quick-action-btn qa-medicine">
            <i class="fas fa-pills"></i> Manage Medicines
        </a>
        <a href="<?php echo url('/admin/reports'); ?>" class="quick-action-btn qa-reports">
            <i class="fas fa-chart-line"></i> View Reports
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
                    <div class="dash-stat-value"><?php echo format_price($todayRevenue); ?></div>
                    <div class="dash-stat-label">Today's Revenue</div>
                </div>
            </div>

            <!-- Today's Sales Count -->
            <div class="dash-stat-card stat-green">
                <div class="dash-stat-icon dash-icon-green">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($todayInvoiceCount); ?></div>
                    <div class="dash-stat-label">Today's Sales</div>
                </div>
            </div>

            <!-- Lifetime Revenue -->
            <div class="dash-stat-card stat-purple">
                <div class="dash-stat-icon dash-icon-purple">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($totalSales); ?></div>
                    <div class="dash-stat-label">Lifetime Revenue</div>
                </div>
            </div>

            <!-- Asset Valuation -->
            <div class="dash-stat-card stat-cyan">
                <div class="dash-stat-icon dash-icon-cyan">
                    <i class="fas fa-vault"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo format_price($totalInventoryValue); ?></div>
                    <div class="dash-stat-label">Asset Valuation</div>
                </div>
            </div>
        </div>

        <!-- ── Row 2: Inventory Status ── -->
        <div class="dash-section-header dash-animate dash-delay-5">
            <div class="dash-section-icon" style="background: rgba(16,185,129,0.12); color: #10b981;">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <h2>Inventory Status</h2>
        </div>
        <div class="dash-stats-row cols-5 dash-animate dash-delay-5">
            <!-- Total Medicines -->
            <div class="dash-stat-card stat-blue clickable" onclick="window.location.href='<?php echo url('/admin/products'); ?>'">
                <div class="dash-stat-icon dash-icon-blue">
                    <i class="fas fa-prescription-bottle-medical"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($totalProducts); ?></div>
                    <div class="dash-stat-label">Total Medicines</div>
                </div>
                <span class="dash-stat-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>

            <!-- Categories -->
            <div class="dash-stat-card stat-orange clickable" onclick="window.location.href='<?php echo url('/admin/categories'); ?>'">
                <div class="dash-stat-icon dash-icon-orange">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?php echo number_format($totalCategories); ?></div>
                    <div class="dash-stat-label">Categories</div>
                </div>
                <span class="dash-stat-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>

            <!-- Low Stock -->
            <div class="dash-stat-card stat-pink clickable" onclick="window.location.href='<?php echo url('/admin/reports?preset=all'); ?>'">
                <div class="dash-stat-icon dash-icon-pink">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value" style="color: <?php echo $lowStock > 0 ? '#ec4899' : 'inherit'; ?>"><?php echo number_format($lowStock); ?></div>
                    <div class="dash-stat-label">Low Stock Items</div>
                </div>
                <span class="dash-stat-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>

            <!-- Expired Medicines -->
            <div class="dash-stat-card stat-red clickable" onclick="window.location.href='<?php echo url('/admin/expiry_report'); ?>'">
                <div class="dash-stat-icon dash-icon-red-ghost">
                    <i class="fas fa-skull-crossbones"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value" style="color: <?php echo $expiredCount > 0 ? '#ef4444' : 'inherit'; ?>"><?php echo number_format($expiredCount); ?></div>
                    <div class="dash-stat-label">Expired Medicines</div>
                </div>
                <span class="dash-stat-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>

            <!-- Near Expiry -->
            <div class="dash-stat-card stat-orange clickable" onclick="window.location.href='<?php echo url('/admin/expiry_report'); ?>'">
                <div class="dash-stat-icon dash-icon-amber-ghost">
                    <i class="fas fa-calendar-xmark"></i>
                </div>
                <div class="dash-stat-info">
                    <div class="dash-stat-value" style="color: <?php echo $nearExpiryCount > 0 ? '#f59e0b' : 'inherit'; ?>"><?php echo number_format($nearExpiryCount); ?></div>
                    <div class="dash-stat-label">Near Expiry (6m)</div>
                </div>
                <span class="dash-stat-arrow"><i class="fas fa-chevron-right"></i></span>
            </div>
        </div>
    </div>

    <!-- ════════════ Performance & Sales Analytics ════════════ -->
    <style>
        .leaderboard-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 16px 20px 20px;
        }
        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            background: var(--surface-color, rgba(255, 255, 255, 0.03));
            border: 1px solid var(--surface-border, rgba(255, 255, 255, 0.06));
            border-radius: var(--radius-md, 10px);
            transition: all 0.2s ease;
        }
        .leaderboard-item:hover {
            background: var(--hover-bg, rgba(255, 255, 255, 0.06));
            border-color: rgba(245, 158, 11, 0.3);
            transform: translateY(-2px);
        }
        .rank-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.82rem;
            flex-shrink: 0;
        }
        .rank-1 {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #ffffff;
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.45);
        }
        .rank-2 {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
            color: #ffffff;
            box-shadow: 0 0 8px rgba(148, 163, 184, 0.3);
        }
        .rank-3 {
            background: linear-gradient(135deg, #f97316 0%, #c2410c 100%);
            color: #ffffff;
            box-shadow: 0 0 8px rgba(249, 115, 22, 0.3);
        }
        .rank-other {
            background: var(--card-border, rgba(255, 255, 255, 0.08));
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        .leaderboard-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #a78bfa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .leaderboard-details {
            flex: 1;
            min-width: 0;
        }
        .leaderboard-name-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }
        .leaderboard-name {
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .leaderboard-revenue {
            font-weight: 800;
            font-size: 0.95rem;
            color: #10b981;
            white-space: nowrap;
        }
        .leaderboard-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.76rem;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .leaderboard-progress-bg {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 999px;
            overflow: hidden;
        }
        .leaderboard-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #10b981);
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .product-rank-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: var(--surface-color, rgba(255, 255, 255, 0.03));
            border: 1px solid var(--surface-border, rgba(255, 255, 255, 0.06));
            border-radius: var(--radius-md, 10px);
            transition: all 0.2s ease;
        }
        .product-rank-item:hover {
            background: var(--hover-bg, rgba(255, 255, 255, 0.06));
            border-color: rgba(6, 182, 212, 0.3);
            transform: translateY(-2px);
        }
        .product-rank-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .product-rank-info {
            min-width: 0;
        }
        .product-rank-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .product-rank-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 3px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .product-rank-right {
            text-align: right;
            flex-shrink: 0;
        }
        .product-rank-volume {
            font-weight: 800;
            font-size: 0.92rem;
            color: #06b6d4;
        }
        .product-rank-rev {
            font-size: 0.76rem;
            color: var(--text-muted);
            margin-top: 2px;
        }
    </style>

    <div class="dash-section-header dash-animate dash-delay-6">
        <div class="dash-section-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fas fa-trophy"></i>
        </div>
        <h2>Sales & Team Performance</h2>
        <span class="dash-section-badge">Ranked Analytics</span>
    </div>

    <div class="dashboard-grid-2col dash-animate dash-delay-6" style="margin-bottom: 24px;">
        <!-- ── Salesman Performance Leaderboard Panel ── -->
        <div class="dash-panel-card" style="border-left: 4px solid #f59e0b;">
            <div class="dash-panel-header">
                <div class="dash-panel-title-group">
                    <div class="dash-panel-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;"><i class="fas fa-medal"></i></div>
                    <h3 class="dash-panel-title">Sales Team Leaderboard</h3>
                </div>
                <span style="font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: var(--radius-full); background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25);">Top Performers</span>
            </div>

            <div class="leaderboard-list">
                <?php if (!empty($salesmanLeaderboard)): ?>
                    <?php 
                        $maxRev = max(array_column($salesmanLeaderboard, 'total_revenue')) ?: 1;
                        $rank = 1;
                        foreach ($salesmanLeaderboard as $sm): 
                            $percent = min(100, round(($sm['total_revenue'] / $maxRev) * 100));
                            $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
                            $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                            $initials = strtoupper(substr($sm['salesman_name'] ?? 'U', 0, 2));
                    ?>
                        <div class="leaderboard-item">
                            <div class="rank-badge <?php echo $rankClass; ?>">
                                <?php echo $medal; ?>
                            </div>
                            <div class="leaderboard-avatar">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div class="leaderboard-details">
                                <div class="leaderboard-name-row">
                                    <span class="leaderboard-name" title="<?php echo htmlspecialchars($sm['salesman_name']); ?>">
                                        <?php echo htmlspecialchars($sm['salesman_name']); ?>
                                        <span style="font-size: 0.7rem; font-weight: 600; padding: 1px 6px; border-radius: 4px; background: rgba(255,255,255,0.06); color: var(--text-muted); text-transform: capitalize; margin-left: 4px;"><?php echo htmlspecialchars($sm['user_role'] ?? 'Salesman'); ?></span>
                                    </span>
                                    <span class="leaderboard-revenue"><?php echo format_price($sm['total_revenue']); ?></span>
                                </div>
                                <div class="leaderboard-meta-row">
                                    <span><i class="fas fa-receipt" style="margin-right: 4px;"></i><?php echo number_format($sm['invoice_count']); ?> Invoices</span>
                                    <span>Avg: <?php echo format_price($sm['avg_order_value']); ?></span>
                                </div>
                                <div class="leaderboard-progress-bg" title="<?php echo $percent; ?>% of top revenue">
                                    <div class="leaderboard-progress-bar" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $rank++;
                        endforeach; 
                    ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                        <i class="fas fa-trophy" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.3; display: block;"></i>
                        No sales transactions recorded yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Top-Selling Medicines Panel ── -->
        <div class="dash-panel-card" style="border-left: 4px solid #10b981;">
            <div class="dash-panel-header">
                <div class="dash-panel-title-group">
                    <div class="dash-panel-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;"><i class="fas fa-fire-alt"></i></div>
                    <h3 class="dash-panel-title">Top-Selling Medicines</h3>
                </div>
                <a href="<?php echo url('/admin/reports'); ?>" class="dash-panel-action">
                    <i class="fas fa-chart-pie"></i> Reports
                </a>
            </div>

            <div class="leaderboard-list">
                <?php if (!empty($topProductsStats)): ?>
                    <?php 
                        $pRank = 1;
                        foreach ($topProductsStats as $prod): 
                            $pRankClass = $pRank === 1 ? 'rank-1' : ($pRank === 2 ? 'rank-2' : ($pRank === 3 ? 'rank-3' : 'rank-other'));
                    ?>
                        <div class="product-rank-item">
                            <div class="product-rank-left">
                                <div class="rank-badge <?php echo $pRankClass; ?>">
                                    #<?php echo $pRank; ?>
                                </div>
                                <div class="product-rank-info">
                                    <div class="product-rank-name" title="<?php echo htmlspecialchars($prod['product_name']); ?>">
                                        <?php echo htmlspecialchars($prod['product_name']); ?>
                                        <?php if (!empty($prod['product_strength'])): ?>
                                            <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">(<?php echo htmlspecialchars($prod['product_strength']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-rank-meta">
                                        <?php if (!empty($prod['category_name'])): ?>
                                            <span style="padding: 1px 6px; border-radius: 4px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                                        <?php endif; ?>
                                        <span><i class="fas fa-boxes" style="margin-right: 3px;"></i>Stock: <strong style="color: <?php echo ($prod['current_stock'] <= 10) ? '#ef4444' : 'inherit'; ?>;"><?php echo number_format($prod['current_stock']); ?></strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="product-rank-right">
                                <div class="product-rank-volume"><?php echo number_format($prod['units_sold']); ?> sold</div>
                                <div class="product-rank-rev"><?php echo format_price($prod['total_revenue']); ?></div>
                            </div>
                        </div>
                    <?php 
                        $pRank++;
                        endforeach; 
                    ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                        <i class="fas fa-pills" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.3; display: block;"></i>
                        No product sales recorded yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ════════════ Bottom Two-Panel Layout ════════════ -->
    <div class="dash-section-header dash-animate dash-delay-7">
        <div class="dash-section-icon" style="background: rgba(139,92,246,0.12); color: #8b5cf6;">
            <i class="fas fa-stream"></i>
        </div>
        <h2>Operations Feed</h2>
    </div>

    <div class="dashboard-grid-2col dash-animate dash-delay-7">
        <!-- ── Recent Transactions Panel ── -->
        <div class="dash-panel-card" style="border-left: 4px solid #8b5cf6;">
            <div class="dash-panel-header">
                <div class="dash-panel-title-group">
                    <div class="dash-panel-icon icon-purple"><i class="fas fa-receipt"></i></div>
                    <h3 class="dash-panel-title">Recent Transactions</h3>
                </div>
                <a href="<?php echo url('/admin/invoices'); ?>" class="dash-panel-action">
                    <i class="fas fa-external-link-alt"></i> View All
                </a>
            </div>
            
            <!-- Summary Bar for Transactions -->
            <?php 
                $todayAvgOrder = $todayInvoiceCount > 0 ? $todayRevenue / $todayInvoiceCount : 0.00;
            ?>
            <div class="dash-panel-summary-bar">
                <div class="dash-panel-summary-item"><i class="fas fa-file-invoice"></i> Invoices: <strong><?php echo $todayInvoiceCount; ?></strong></div>
                <div style="width: 1px; background: var(--surface-border, rgba(255,255,255,0.06)); height: 14px; align-self: center;"></div>
                <div class="dash-panel-summary-item"><i class="fas fa-dollar-sign"></i> Total: <strong><?php echo format_price($todayRevenue); ?></strong></div>
                <div style="width: 1px; background: var(--surface-border, rgba(255,255,255,0.06)); height: 14px; align-self: center;"></div>
                <div class="dash-panel-summary-item"><i class="fas fa-calculator"></i> AOV: <strong><?php echo format_price($todayAvgOrder); ?></strong></div>
            </div>

            <div class="dash-panel-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Patient</th>
                                <th>Salesman</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentSales) > 0): ?>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr class="clickable-row" onclick="window.open('<?php echo url('/invoice/print?id=' . $sale['id']); ?>', '_blank')" title="Click to view/print invoice">
                                        <td><code style="font-size: 0.82rem; padding: 2px 8px; border-radius: 4px; background: var(--hover-bg, rgba(255,255,255,0.06)); font-weight: 700;">#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($sale['customer_name'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($sale['salesman'] ?? ''); ?></td>
                                        <td style="font-weight: 700;"><?php echo format_price($sale['total_amount']); ?></td>
                                        <td style="font-size: 0.82rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($sale['created_at'])); ?></td>
                                        <td><span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); padding: 2px 10px; border-radius: var(--radius-full); font-size: 0.72rem; font-weight: 700;"><i class="fas fa-check-circle" style="font-size: 0.6rem;"></i> Completed</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                        <i class="fas fa-inbox" style="display: block; font-size: 1.5rem; margin-bottom: 8px; opacity: 0.4;"></i>
                                        No recent transactions found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Activity Timeline Panel ── -->
        <div class="dash-panel-card" style="border-left: 4px solid #06b6d4;">
            <div class="dash-panel-header">
                <div class="dash-panel-title-group">
                    <div class="dash-panel-icon icon-cyan"><i class="fas fa-history"></i></div>
                    <h3 class="dash-panel-title">Operational Activity</h3>
                </div>
                <span style="font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: var(--radius-full); background: rgba(6,182,212,0.1); color: #06b6d4; border: 1px solid rgba(6,182,212,0.2);">Last 10</span>
            </div>

            <!-- Summary Bar for Timeline -->
            <?php
                $summaryCounts = ['restock' => 0, 'sale' => 0, 'adjustment' => 0, 'return' => 0];
                if (!empty($activitySummary)) {
                    foreach ($activitySummary as $sum) {
                        $summaryCounts[$sum['type']] = (int)$sum['count'];
                    }
                }
            ?>
            <div class="dash-panel-summary-bar">
                <div class="dash-panel-summary-item"><i class="fas fa-plus-circle text-primary"></i> Restocks: <strong><?php echo $summaryCounts['restock']; ?></strong></div>
                <div style="width: 1px; background: var(--surface-border, rgba(255,255,255,0.06)); height: 14px; align-self: center;"></div>
                <div class="dash-panel-summary-item"><i class="fas fa-shopping-cart text-success"></i> Sales: <strong><?php echo $summaryCounts['sale']; ?></strong></div>
                <div style="width: 1px; background: var(--surface-border, rgba(255,255,255,0.06)); height: 14px; align-self: center;"></div>
                <div class="dash-panel-summary-item"><i class="fas fa-sliders-h text-warning"></i> Adj: <strong><?php echo $summaryCounts['adjustment']; ?></strong></div>
                <div style="width: 1px; background: var(--surface-border, rgba(255,255,255,0.06)); height: 14px; align-self: center;"></div>
                <div class="dash-panel-summary-item"><i class="fas fa-undo text-danger"></i> Returns: <strong><?php echo $summaryCounts['return']; ?></strong></div>
            </div>

            <div class="dash-timeline-body">
                <div class="activity-timeline">
                    <?php if (count($recentActivity) > 0): ?>
                        <?php foreach ($recentActivity as $log): 
                            $iconClass = 'fa-sliders-h';
                            $typeClass = 'type-adjustment';
                            if ($log['type'] === 'sale') {
                                $iconClass = 'fa-shopping-cart';
                                $typeClass = 'type-sale';
                            } elseif ($log['type'] === 'restock') {
                                $iconClass = 'fa-plus-circle';
                                $typeClass = 'type-restock';
                            } elseif ($log['type'] === 'return') {
                                $iconClass = 'fa-undo-alt';
                                $typeClass = 'type-return';
                            }
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-icon <?php echo $typeClass; ?>">
                                    <i class="fas <?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="timeline-header">
                                    <span class="timeline-title">
                                        <strong><?php echo htmlspecialchars($log['product_name'] ?? 'Unknown Product'); ?></strong>
                                        <?php if (!empty($log['category_name'])): ?>
                                            <span class="timeline-category-badge"><?php echo htmlspecialchars($log['category_name']); ?></span>
                                        <?php endif; ?>
                                        <span class="timeline-type-badge <?php echo $typeClass; ?>"><?php echo $log['type']; ?></span>
                                        <?php if ($log['qty_change'] > 0): ?>
                                            <span class="timeline-qty-badge qty-pos">+<?php echo $log['qty_change']; ?></span>
                                        <?php else: ?>
                                            <span class="timeline-qty-badge qty-neg"><?php echo $log['qty_change']; ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="timeline-time" data-timestamp="<?php echo $log['created_at']; ?>" title="<?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>"><?php echo date('H:i, M d', strtotime($log['created_at'])); ?></span>
                                </div>
                                <div class="timeline-desc">
                                    <?php echo htmlspecialchars($log['remarks'] ?? ''); ?>
                                    <?php if (!empty($log['user_name'])): ?>
                                        &middot; <em>by <?php echo htmlspecialchars($log['user_name']); ?></em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="timeline-empty">
                            <i class="fas fa-history"></i>
                            No operational events recorded yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════ Danger Zone (Owner Only) ════════════ -->
    <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'Owner'): ?>
        <style>
            .dz-premium-wrapper {
                margin-top: 50px;
                margin-bottom: 40px;
                position: relative;
                border-radius: 16px;
                background: #0f1115; /* Deep dark background */
                box-shadow: 0 20px 40px -10px rgba(220, 38, 38, 0.25), 
                            inset 0 1px 0 rgba(255, 255, 255, 0.1);
                overflow: hidden;
                font-family: 'Inter', system-ui, sans-serif;
            }
            
            /* Warning Tape Top Border */
            .dz-warning-tape {
                height: 12px;
                width: 100%;
                background: repeating-linear-gradient(
                    -45deg,
                    #facc15,
                    #facc15 15px,
                    #000000 15px,
                    #000000 30px
                );
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .dz-content {
                padding: 40px;
                position: relative;
                z-index: 2;
            }

            /* Red glowing ambient background */
            .dz-ambient-glow {
                position: absolute;
                top: -50%;
                left: 50%;
                transform: translateX(-50%);
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(220, 38, 38, 0.15) 0%, rgba(15, 17, 21, 0) 70%);
                pointer-events: none;
                z-index: 1;
            }

            .dz-header {
                display: flex;
                align-items: flex-start;
                gap: 20px;
                margin-bottom: 35px;
            }

            .dz-icon-box {
                width: 56px;
                height: 56px;
                border-radius: 14px;
                background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
                border: 1px solid rgba(239, 68, 68, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 0 20px rgba(220, 38, 38, 0.4);
                color: #fca5a5;
                font-size: 1.75rem;
                animation: dz-breathe 3s ease-in-out infinite;
            }

            @keyframes dz-breathe {
                0%, 100% { box-shadow: 0 0 15px rgba(220, 38, 38, 0.3); }
                50% { box-shadow: 0 0 30px rgba(220, 38, 38, 0.6); transform: scale(1.02); }
            }

            .dz-title {
                color: #fef2f2;
                font-size: 1.75rem;
                font-weight: 800;
                margin: 0 0 6px 0;
                letter-spacing: -0.02em;
            }

            .dz-subtitle {
                color: #fca5a5;
                font-size: 1rem;
                margin: 0;
                opacity: 0.8;
                font-weight: 400;
            }

            .dz-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 24px;
            }

            .dz-card {
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                padding: 24px;
                backdrop-filter: blur(10px);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            .dz-card:hover {
                background: rgba(239, 68, 68, 0.05);
                border-color: rgba(239, 68, 68, 0.3);
                transform: translateY(-4px);
                box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.5);
            }

            .dz-card-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 12px;
            }

            .dz-card-header i {
                color: #ef4444;
                font-size: 1.25rem;
            }

            .dz-card-title {
                color: #fef2f2;
                margin: 0;
                font-size: 1.15rem;
                font-weight: 600;
            }

            .dz-card-desc {
                color: #9ca3af;
                font-size: 0.9rem;
                line-height: 1.6;
                margin: 0 0 24px 0;
            }

            .dz-card-desc strong {
                color: #fca5a5;
            }

            .dz-btn {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 12px;
                border-radius: 8px;
                font-weight: 600;
                font-size: 0.95rem;
                cursor: pointer;
                transition: all 0.2s;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .dz-btn-outline {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #ef4444;
            }

            .dz-btn-outline:hover {
                background: rgba(239, 68, 68, 0.2);
                border-color: #ef4444;
                color: #fca5a5;
            }

            .dz-btn-solid {
                background: #dc2626;
                border: 1px solid #b91c1c;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
            }

            .dz-btn-solid:hover {
                background: #ef4444;
                box-shadow: 0 6px 16px rgba(220, 38, 38, 0.6);
            }
        </style>

        <div class="dz-premium-wrapper dash-animate dash-delay-8">
            <div class="dz-warning-tape"></div>
            <div class="dz-ambient-glow"></div>
            
            <div class="dz-content">
                <div class="dz-header">
                    <div class="dz-icon-box">
                        <i class="fas fa-radiation"></i>
                    </div>
                    <div>
                        <h2 class="dz-title">Super Admin Danger Zone</h2>
                        <p class="dz-subtitle">Highly destructive operations. These actions bypass standard safety protocols.</p>
                    </div>
                </div>

                <div class="dz-grid">
                    <!-- Purge Invoices -->
                    <div class="dz-card">
                        <div>
                            <div class="dz-card-header">
                                <i class="fas fa-receipt"></i>
                                <h3 class="dz-card-title">Purge All Invoices</h3>
                            </div>
                            <p class="dz-card-desc">
                                Eradicates all sales and purchase records from the database. Leaves inventory logs and user accounts intact. <strong>Irreversible operation.</strong>
                            </p>
                        </div>
                        <form action="<?php echo url('/admin/system/wipe_invoices'); ?>" method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete ALL invoices? This cannot be undone.');" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <button type="submit" class="dz-btn dz-btn-outline">
                                <i class="fas fa-trash-alt"></i> Execute Purge
                            </button>
                        </form>
                    </div>

                    <!-- Factory Reset -->
                    <div class="dz-card">
                        <div>
                            <div class="dz-card-header">
                                <i class="fas fa-bomb"></i>
                                <h3 class="dz-card-title">Total System Reset</h3>
                            </div>
                            <p class="dz-card-desc">
                                Catastrophically wipes the entire database: products, invoices, logs, and user accounts (excluding this Owner session). <strong>Data is lost permanently.</strong>
                            </p>
                        </div>
                        <form action="<?php echo url('/admin/system/wipe'); ?>" method="POST" onsubmit="return confirm('CATASTROPHIC WARNING: Are you absolutely sure you want to delete ALL data? This will destroy the database contents permanently.');" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <button type="submit" class="dz-btn dz-btn-solid">
                                <i class="fas fa-skull"></i> Execute Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'just now';
    }
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return diffInMinutes === 1 ? '1 min ago' : `${diffInMinutes} mins ago`;
    }
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return diffInHours === 1 ? '1 hour ago' : `${diffInHours} hours ago`;
    }
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) {
        return diffInDays === 1 ? 'yesterday' : `${diffInDays} days ago`;
    }
    
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-timestamp]').forEach(el => {
        const timestamp = el.getAttribute('data-timestamp');
        if (timestamp) {
            el.textContent = timeAgo(timestamp);
        }
    });
});
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
