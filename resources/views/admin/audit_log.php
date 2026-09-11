<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title"><i class="fas fa-history" style="color: var(--primary-color, #2563eb); margin-right: 8px;"></i> Inventory Audit Trail</h1>
            <p class="text-muted">Immutable log of all medicine stock deductions, additions, returns, and inventory adjustments</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php
        $todaySale = 0;
        $todayRestock = 0;
        $todayReturn = 0;
        foreach ($todaySummary as $row) {
            if ($row['type'] === 'sale') $todaySale += (int)$row['count'];
            elseif ($row['type'] === 'restock') $todayRestock += (int)$row['count'];
            elseif (strpos($row['type'], 'return') !== false) $todayReturn += (int)$row['count'];
        }
        ?>
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(37,99,235,0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div>
                <div style="font-size: 13px; color: #64748b; font-weight: 500;">Today's Sales Logs</div>
                <div style="font-size: 22px; font-weight: 700; color: #1e293b;"><?php echo number_format($todaySale); ?></div>
            </div>
        </div>
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fas fa-truck-loading"></i>
            </div>
            <div>
                <div style="font-size: 13px; color: #64748b; font-weight: 500;">Today's Restocks</div>
                <div style="font-size: 22px; font-weight: 700; color: #1e293b;"><?php echo number_format($todayRestock); ?></div>
            </div>
        </div>
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(239,68,68,0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fas fa-undo"></i>
            </div>
            <div>
                <div style="font-size: 13px; color: #64748b; font-weight: 500;">Today's Returns</div>
                <div style="font-size: 22px; font-weight: 700; color: #1e293b;"><?php echo number_format($todayReturn); ?></div>
            </div>
        </div>
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(139,92,246,0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <div style="font-size: 13px; color: #64748b; font-weight: 500;">Total Filtered Logs</div>
                <div style="font-size: 22px; font-weight: 700; color: #1e293b;"><?php echo number_format($pagination['total_records']); ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card" style="margin-bottom: 20px; padding: 18px; border-radius: 8px;">
        <form method="GET" action="<?php echo url('/admin/audit_log'); ?>" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 200px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">Search Medicine / Remark</label>
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>" style="padding-left: 35px; width: 100%;">
                </div>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">Action Type</label>
                <select name="type" class="form-control">
                    <option value="">All Actions</option>
                    <option value="sale" <?php echo $filters['type'] === 'sale' ? 'selected' : ''; ?>>Sale</option>
                    <option value="restock" <?php echo $filters['type'] === 'restock' ? 'selected' : ''; ?>>Restock</option>
                    <option value="return_in" <?php echo $filters['type'] === 'return_in' ? 'selected' : ''; ?>>Customer Return</option>
                    <option value="return_out" <?php echo $filters['type'] === 'return_out' ? 'selected' : ''; ?>>Supplier Return</option>
                    <option value="adjustment" <?php echo $filters['type'] === 'adjustment' ? 'selected' : ''; ?>>Adjustment</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">User / Staff</label>
                <select name="user_id" class="form-control">
                    <option value="">All Staff</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $filters['user_id'] == $u['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['role']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">From Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block;">To Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 42px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="<?php echo url('/admin/audit_log'); ?>" class="btn" style="background: #f1f5f9; color: #475569; height: 42px; display: flex; align-items: center;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Log Table -->
    <div class="card" style="padding: 0; overflow: hidden; border-radius: 8px;">
        <table class="table" style="width: 100%; margin: 0;">
            <thead>
                <tr>
                    <th style="padding: 14px 16px;">Date & Time</th>
                    <th>User</th>
                    <th>Medicine</th>
                    <th>Action</th>
                    <th style="text-align: right;">Qty Change</th>
                    <th style="padding-right: 16px;">Remarks / Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 36px; color: #94a3b8;">
                            <i class="fas fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            No audit log records found for the selected criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="padding: 12px 16px; font-size: 13px; color: #64748b; white-space: nowrap;">
                                <i class="far fa-clock" style="margin-right: 4px;"></i>
                                <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: #1e293b; font-size: 13px;">
                                    <?php echo htmlspecialchars($log['user_name'] ?? 'System / Owner'); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;">
                                    <?php echo htmlspecialchars($log['product_name'] ?? 'Product #' . $log['product_id']); ?>
                                </div>
                                <?php if (!empty($log['category_name'])): ?>
                                    <small class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($log['category_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                switch ($log['type']) {
                                    case 'sale':
                                        echo '<span class="badge" style="background-color: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;"><i class="fas fa-shopping-bag"></i> Sale</span>';
                                        break;
                                    case 'restock':
                                        echo '<span class="badge" style="background-color: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;"><i class="fas fa-truck-loading"></i> Restock</span>';
                                        break;
                                    case 'return_in':
                                        echo '<span class="badge" style="background-color: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;"><i class="fas fa-undo"></i> Return In</span>';
                                        break;
                                    case 'return_out':
                                        echo '<span class="badge" style="background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;"><i class="fas fa-reply"></i> Return Out</span>';
                                        break;
                                    case 'adjustment':
                                        echo '<span class="badge" style="background-color: #f3e8ff; color: #7e22ce; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;"><i class="fas fa-sliders-h"></i> Adjustment</span>';
                                        break;
                                    default:
                                        echo '<span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;">' . htmlspecialchars($log['type']) . '</span>';
                                }
                                ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                <?php if ($log['qty_change'] > 0): ?>
                                    <span style="color: #16a34a;">+<?php echo number_format($log['qty_change']); ?></span>
                                <?php elseif ($log['qty_change'] < 0): ?>
                                    <span style="color: #dc2626;"><?php echo number_format($log['qty_change']); ?></span>
                                <?php else: ?>
                                    <span style="color: #64748b;">0</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding-right: 16px; color: #475569; font-size: 13px;">
                                <?php echo htmlspecialchars($log['remarks'] ?? '-'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
            <div class="text-muted" style="font-size: 13px;">
                Showing page <strong><?php echo $pagination['current_page']; ?></strong> of <strong><?php echo $pagination['total_pages']; ?></strong> (<?php echo $pagination['total_records']; ?> entries)
            </div>
            <div style="display: flex; gap: 4px;">
                <?php
                $queryParams = $filters;
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                    <?php $queryParams['page'] = $pagination['current_page'] - 1; ?>
                    <a href="<?php echo url('/admin/audit_log?' . http_build_query($queryParams)); ?>" class="btn btn-sm" style="background: #f1f5f9; color: #334155;">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                    <?php $queryParams['page'] = $i; ?>
                    <a href="<?php echo url('/admin/audit_log?' . http_build_query($queryParams)); ?>" class="btn btn-sm <?php echo $i === $pagination['current_page'] ? 'btn-primary' : ''; ?>" style="<?php echo $i !== $pagination['current_page'] ? 'background: #f1f5f9; color: #334155;' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <?php $queryParams['page'] = $pagination['current_page'] + 1; ?>
                    <a href="<?php echo url('/admin/audit_log?' . http_build_query($queryParams)); ?>" class="btn btn-sm" style="background: #f1f5f9; color: #334155;">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
