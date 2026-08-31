<?php
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_image_fetched'])) {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT user_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['user_image'] = $stmt->fetchColumn();
        $_SESSION['user_image_fetched'] = true;
    }
}
?>
<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <div class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </div>
            <div class="topbar-clock" style="display: flex; align-items: center; background: var(--bg-color, #f4f7f6); padding: 0.5rem 1rem; border-radius: 20px; color: var(--text-color, #333); font-size: 0.95rem; font-weight: 500; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
                <i class="far fa-calendar-alt" style="color: var(--primary-color, #3498db); margin-right: 8px;"></i>
                <span id="liveClock"></span>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    function updateClock() {
                        const now = new Date();
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                        const clockEl = document.getElementById('liveClock');
                        if (clockEl) clockEl.textContent = now.toLocaleDateString('en-US', options);
                    }
                    setInterval(updateClock, 1000);
                    updateClock();
                });
            </script>
        </div>
        
        <div class="topbar-actions">
            <div class="action-item notification-icon" id="notificationIcon">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge" style="display: none;">0</span>
            </div>

            <aside class="notification-sidebar">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Notifications</h2>
            <div>
                <button id="markAllReadBtn" style="background: none; border: none; color: var(--primary-color); cursor: pointer; font-size: 0.8rem; margin-right: 10px;">Mark all read</button>
                <i class="fa-solid fa-xmark"></i>
            </div>
        </div>
        <ul class="notification-list" id="notificationList">
            <!-- Dynamic items will be populated here -->
        </ul>
    </aside>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifBadge = document.getElementById('notifBadge');
        const notificationList = document.getElementById('notificationList');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const csrfToken = "<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>";
        
        function fetchNotifications() {
            fetch('<?php echo URL_ROOT; ?>/api/notifications')
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        if(data.count > 0) {
                            notifBadge.textContent = data.count;
                            notifBadge.style.display = 'flex';
                        } else {
                            notifBadge.style.display = 'none';
                        }
                        
                        notificationList.innerHTML = '';
                        if(data.data.length === 0) {
                            notificationList.innerHTML = '<li style="text-align:center; color: #888; padding: 20px;">No new notifications.</li>';
                        } else {
                            const escapeHTML = str => (str || '').replace(/[&<>'"]/g, tag => ({
                                '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
                            }[tag] || tag));

                            data.data.forEach(notif => {
                                let icon = 'fa-info-circle';
                                let color = '#3498db';
                                if (notif.type === 'warning') { icon = 'fa-exclamation-triangle'; color = '#f39c12'; }
                                else if (notif.type === 'danger') { icon = 'fa-times-circle'; color = '#e74c3c'; }
                                else if (notif.type === 'success') { icon = 'fa-check-circle'; color = '#2ecc71'; }
                                
                                const li = document.createElement('li');
                                li.style.cursor = 'pointer';
                                li.innerHTML = `
                                    <div class="notification-content" style="border-left: 3px solid ${color}; padding-left: 10px;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                            <i class="fas ${icon}" style="color: ${color}"></i>
                                            <strong style="font-size: 0.9rem;">${escapeHTML(notif.title)}</strong>
                                        </div>
                                        <p>${escapeHTML(notif.message)}</p>
                                        <span class="timestamp">${new Date(notif.created_at).toLocaleString()}</span>
                                    </div>
                                `;
                                li.onclick = () => {
                                    markAsRead(notif.id, notif.link);
                                };
                                notificationList.appendChild(li);
                            });
                        }
                    }
                });
        }

        function markAsRead(id, link) {
            fetch('<?php echo URL_ROOT; ?>/api/notifications/read', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: 'id=' + id + '&csrf_token=' + encodeURIComponent(csrfToken)
            }).then(res => res.json()).then(data => {
                if(data.success) {
                    if(link) window.location.href = link;
                    else fetchNotifications();
                }
            });
        }

        if(markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                fetch('<?php echo URL_ROOT; ?>/api/notifications/read-all', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken 
                    },
                    body: 'csrf_token=' + encodeURIComponent(csrfToken)
                }).then(res => res.json()).then(data => {
                    if(data.success) fetchNotifications();
                });
            });
        }

        fetchNotifications();
        // Poll every 60 seconds
        setInterval(fetchNotifications, 60000);
    });
    </script>
            
            <div class="user-profile" id="userProfileToggle">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <span class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'Salesman'); ?></span>
                </div>
                <div class="user-avatar" style="position: relative; display: flex; align-items: center">
                    <?php 
                    $user_img = $_SESSION['user_image'] ?? null;
                    if ($user_img && file_exists(BASE_PATH . '/public/uploads/profiles/' . $user_img)): 
                    ?>
                        <img src="<?php echo URL_ROOT; ?>/uploads/profiles/<?php echo htmlspecialchars($user_img); ?>" alt="Profile" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <!-- <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 8px"></i> -->
                </div>
                
                <!-- Dropdown Menu -->
                <div class="profile-dropdown-menu" id="profileDropdownMenu">
                    <div class="dropdown-header">
                        <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></strong>
                        <span style="display: block; font-size: 0.8rem"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></span>
                    </div>
                    <a href="<?php echo url($_SESSION['role'] === 'admin' ? '/admin/profile' : '/salesman/profile'); ?>" class="dropdown-item">
                        <i class="fas fa-user-circle"></i> My Profile
                    </a>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="<?php echo URL_ROOT; ?>/admin/settings" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <?php endif; ?>
                    <!-- Logout: POST + CSRF to prevent clickjacking/CSRF logout attacks -->
                    <form method="POST" action="<?php echo URL_ROOT; ?>/logout" style="margin: 0; padding: 0">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <button type="submit" class="dropdown-item text-danger" style="border: none; width: 100%; text-align: left; cursor: pointer; padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.5rem">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>

            </div>










            
        </div>
    </header>

