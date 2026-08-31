<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="dash-section-header">
        <div class="dash-section-icon" style="background: rgba(59,130,246,0.12); color: #3b82f6;">
            <i class="fas fa-bell"></i>
        </div>
        <h2>Notifications</h2>
    </div>

    <div class="dash-panel-card">
        <div class="dash-panel-body">
            <?php if (!empty($notifications)): ?>
                <ul class="notifications-list" style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($notifications as $notif): ?>
                        <li style="padding: 15px; border-bottom: 1px solid var(--surface-border); display: flex; align-items: start; gap: 15px; background: <?php echo $notif['is_read'] ? 'transparent' : 'rgba(59,130,246,0.05)'; ?>">
                            <div style="padding: 10px; border-radius: 50%; background: rgba(59,130,246,0.1); color: #3b82f6;">
                                <i class="fas <?php echo htmlspecialchars($notif['icon'] ?? 'fa-info-circle'); ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <h4 style="margin: 0; font-size: 1rem; color: var(--text-color);"><?php echo htmlspecialchars($notif['title']); ?></h4>
                                    <small style="color: var(--text-muted);"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></small>
                                </div>
                                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <?php if (!empty($notif['link'])): ?>
                                    <?php 
                                        $link = $notif['link'];
                                        $finalUrl = (strpos($link, 'http') === 0 || strpos($link, URL_ROOT) === 0) ? $link : url($link); 
                                    ?>
                                    <a href="<?php echo htmlspecialchars($finalUrl); ?>" class="btn-primary" style="display: inline-block; margin-top: 10px; padding: 5px 12px; font-size: 0.8rem; border-radius: 4px; background: #3b82f6; color: white; text-decoration: none;">View Details</a>
                                <?php endif; ?>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: #3b82f6; margin-top: 15px;"></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" style="padding: 8px 12px; border-radius: 4px; background: <?php echo $i === $currentPage ? '#3b82f6' : 'var(--surface-color)'; ?>; color: <?php echo $i === $currentPage ? 'white' : 'var(--text-color)'; ?>; border: 1px solid var(--surface-border); text-decoration: none;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>No notifications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
