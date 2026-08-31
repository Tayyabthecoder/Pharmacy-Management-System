<?php
// Profile view
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
        </div>
        
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>">
            <i class="fas fa-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Left Column: Avatar & Quick Info -->
        <div class="profile-col-left">
            <div class="card text-center">
                <div class="profile-avatar-container">
                    <?php if ($user['user_image'] && file_exists('../uploads/profiles/' . $user['user_image'])): ?>
                        <img id="avatarPreview" class="profile-avatar" src="../uploads/profiles/<?php echo htmlspecialchars($user['user_image']); ?>" alt="Profile">
                    <?php else: ?>
                        <div id="avatarPreviewFallback" class="profile-avatar profile-avatar-fallback">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-primary btn-sm avatar-upload-btn" onclick="document.getElementById('profile_image').click()">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="profile-badge-wrapper">
                    <span class="badge badge-warning">Admin</span>
                </div>
                
                <form id="photoForm" method="POST" enctype="multipart/form-data" style="display: none">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update_photo">
                    <input type="file" name="profile_image" id="profile_image" accept="image/jpeg, image/png, image/gif" onchange="previewAndSubmitPhoto()">
                </form>
            </div>
        </div>
        
        <!-- Right Column: Forms -->
        <div class="profile-col-right">
            <!-- Edit Profile -->
            <div class="card profile-info-card">
                <div class="card-header">
                    <h3>Personal Information</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row profile-form-row">
                        <div class="form-group profile-form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group profile-form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+1 234 567 8900">
                    </div>
                    
                    <div class="form-group">
                        <label>Bio / Notes</label>
                        <textarea name="bio" class="form-control" placeholder="Tell us a little about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="profile-card-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h3>Change Password</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-row profile-form-row">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="profile-card-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAndSubmitPhoto() {
    const file = document.getElementById('profile_image').files[0];
    if (file) {
        // Automatically submit the form to upload the image
        document.getElementById('photoForm').submit();
    }
}
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>


