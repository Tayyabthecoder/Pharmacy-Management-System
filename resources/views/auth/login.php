<?php
// Fetch system settings dynamically for global theme and branding
$globalSettings = [];
try {
    global $pdo;
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT meta_key, meta_value FROM system_settings");
        while ($row = $stmt->fetch()) {
            $globalSettings[$row['meta_key']] = $row['meta_value'];
        }
    }
} catch (Exception $e) {
    // Fail silently
}
$themeColor = $globalSettings['theme_color'] ?? '#4f46e5';
$themeStyle = $globalSettings['theme_style'] ?? 'glass';
$sidebarStyle = $globalSettings['sidebar_style'] ?? 'expanded';
$companyName = $globalSettings['company_name'] ?? 'InventorySys';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($companyName); ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css">
    
    <!-- Theme Stylesheet -->
    <?php
    $themeStyleFile = 'glass.css';
    if ($themeStyle === 'dark') {
        $themeStyleFile = 'neon-dark.css';
    } elseif ($themeStyle === 'classic') {
        $themeStyleFile = 'flat.css';
    }
    ?>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/themes/<?php echo $themeStyleFile; ?>">
</head>
<body class="theme-<?php echo htmlspecialchars($themeStyle); ?> sidebar-<?php echo htmlspecialchars($sidebarStyle); ?>">

<div class="auth-container">
    <div class="auth-card">
        <!-- Left Pane: Branding -->
        <div class="auth-left" style="background: linear-gradient(135deg, rgba(8, 10, 20, 0.85) 0%, rgba(17, 14, 45, 0.9) 100%), url('<?php echo URL_ROOT; ?>/assets/img/pharmacy_login_hero.png') no-repeat center center; background-size: cover;">
            <div class="auth-logo">
                <i class="fas fa-prescription-bottle-medical"></i> <?php echo htmlspecialchars($companyName); ?>
            </div>
            
            <div class="auth-left-content">
                <span class="auth-tagline">Secure Portal</span>
                <h2 class="auth-title">Pharmacy System</h2>
                <p class="auth-desc">Precision medicine dispensing, smart stock controls, and billing management in one unified system.</p>
                
                <div class="auth-features">
                    <div class="auth-feature-item">
                        <i class="fas fa-capsules"></i>
                        <span>Smart Inventory Control</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Real-time Billing & Invoicing</span>
                    </div>
                    <div class="auth-feature-item">
                        <i class="fas fa-shield-halved"></i>
                        <span>Secure Patient Records</span>
                    </div>
                </div>
            </div>
            
            <div class="auth-footer">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($companyName); ?>. All Rights Reserved.
            </div>
        </div>

        <!-- Right Pane: Form details -->
        <div class="auth-right">
            <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; color: var(--text-color);">Welcome Back</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 25px;">Please sign in to access your dashboard.</p>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo URL_ROOT; ?>/login" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" id="username" class="form-control has-icon" placeholder="Enter your username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control has-icon" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password-btn" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    Secure Sign In <i class="fas fa-shield-halved btn-icon-pulse"></i>
                </button>
            </form>
                
            <p style="margin-top: 25px; font-size: 0.85rem; color: var(--text-muted); text-align: center;">
                Don't have an account? <span style="color: var(--primary-color); cursor: pointer;">Contact Administrator</span>
            </p>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
        this.classList.toggle('fa-eye');
    });
</script>

</body>
</html>
