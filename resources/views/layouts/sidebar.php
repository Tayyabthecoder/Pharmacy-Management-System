<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? 'salesman';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <?php if (!empty($globalSettings['company_logo'])): ?>
                <img src="<?php echo url($globalSettings['company_logo']); ?>" alt="Logo" style="max-height: 36px; max-width: 140px; object-fit: contain; vertical-align: middle; margin-right: 6px;">
            <?php else: ?>
                <i class="fas fa-prescription-bottle-medical"></i> 
            <?php endif; ?>
            <span><?php echo htmlspecialchars($companyName); ?></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <?php if ($role === 'admin'): ?>
                <li class="<?php echo strpos($uri, '/admin/dashboard') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/dashboard"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/products') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/products"><i class="fas fa-pills"></i> <span>Medicines</span></a>
                </li>
<?php 
$username = strtolower($_SESSION['username'] ?? '');
$isOwner = ($role === 'owner' || $username === 'owner' || ($_SESSION['user_id'] ?? 0) == 999999);
?>
                <?php if ($isOwner): ?>
                <li class="<?php echo strpos($uri, '/admin/categories') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/categories"><i class="fas fa-tags"></i> <span>Categories & Generics</span></a>
                </li>
                <?php endif; ?>
                <li class="<?php echo strpos($uri, '/admin/suppliers') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/suppliers"><i class="fas fa-handshake"></i> <span>Suppliers & Companies</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/expiry_report') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/expiry_report"><i class="fas fa-calendar-times"></i> <span>Expiry Report</span></a>
                </li>


                <li class="<?php echo (strpos($uri, '/admin/invoices') !== false || strpos($uri, '/admin/create_invoice') !== false || strpos($uri, '/admin/edit_invoice') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/invoices"><i class="fas fa-file-invoice-dollar"></i> <span>Invoices</span></a>
                </li>
                <li class="<?php echo (strpos($uri, '/admin/receive_invoices') !== false || strpos($uri, '/admin/create_receive_invoice') !== false || strpos($uri, '/admin/edit_receive_invoice') !== false || strpos($uri, '/admin/view_receive_invoice') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/receive_invoices"><i class="fas fa-truck-loading"></i> <span>Receive Invoices</span></a>
                </li>
                 <li class="<?php echo strpos($uri, '/admin/users') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/users"><i class="fas fa-users-cog"></i> <span>Users</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/reports') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/reports"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/notifications') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/notifications"><i class="fas fa-bell"></i> <span>Notifications</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/settings') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/settings"><i class="fas fa-cog"></i> <span>Settings</span></a>
                </li>
            <?php else: ?>
                <li class="<?php echo strpos($uri, '/salesman/dashboard') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/salesman/dashboard"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/salesman/inventory') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/salesman/inventory"><i class="fas fa-box"></i> <span>Check Stock</span></a>
                </li>

                <li class="<?php echo strpos($uri, '/salesman/invoices') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/salesman/invoices"><i class="fas fa-file-invoice-dollar"></i> <span>My Invoices</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/admin/reports') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/admin/reports"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
                </li>
                <li class="<?php echo strpos($uri, '/salesman/notifications') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo URL_ROOT; ?>/salesman/notifications"><i class="fas fa-bell"></i> <span>Notifications</span></a>
                </li>
            <?php endif; ?>
            
            <li class="logout-link">
                <form method="POST" action="<?php echo URL_ROOT; ?>/logout" style="margin: 0; padding: 0" id="sidebarLogoutForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <a href="javascript:void(0);" onclick="document.getElementById('sidebarLogoutForm').submit();">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </a>
                </form>
            </li>
        </ul>
    </nav>
</aside>

