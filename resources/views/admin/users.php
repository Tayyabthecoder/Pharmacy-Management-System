<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center">
        <div>
            <h1 class="page-title">Users & Salesmen</h1>
            <p class="text-muted">Manage system access</p>
        </div>
        <button class="btn btn-primary" id="openAddModal">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msgType; ?>">
            <i class="fas fa-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><code style="background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: var(--radius-sm);"><?php echo htmlspecialchars($user['username']); ?></code></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if($user['role'] == 'admin'): ?>
                                        <span class="badge badge-warning">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Salesman</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user['status'] == 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn btn-sm btn-edit edit-btn" 
                                                data-id="<?php echo $user['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-role="<?php echo $user['role']; ?>"
                                                data-status="<?php echo $user['status']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-delete delete-btn" 
                                                    data-id="<?php echo $user['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-users"></i>
                                No users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo renderPagination($pagination, url('/admin/users')); ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add User</h2>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="userId">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="userName" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="userUsername" class="form-control" placeholder="e.g. johndoe" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="userEmail" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password <span id="pwdHelp" class="text-muted" style="font-size: 0.8rem; font-weight: normal">(Required for new users)</span></label>
                <input type="password" name="password" id="userPassword" class="form-control">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="userRole" class="form-control">
                        <option value="salesman">Salesman</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="userStatus" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="userModal.style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalBtn">Save User</button>
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
        <div class="delete-modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="delete-modal-text">Are you sure you want to delete this user?<br><strong>This action cannot be undone.</strong></p>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_id" id="deleteId">
            <div class="modal-footer" style="justify-content: center">
                <button type="button" class="btn btn-cancel" onclick="deleteModal.style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-confirm-delete">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Logic
    const userModal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const openAddBtn = document.getElementById('openAddModal');
    const closeBtns = document.querySelectorAll('.close-modal');
    
    // Add Mode
    openAddBtn.onclick = function() {
        document.getElementById('modalTitle').textContent = "Add User";
        document.getElementById('formAction').value = "add";
        document.getElementById('userId').value = "";
        document.getElementById('userName').value = "";
        document.getElementById('userUsername').value = "";
        document.getElementById('userEmail').value = "";
        document.getElementById('userPassword').value = "";
        document.getElementById('userPassword').required = true;
        document.getElementById('pwdHelp').textContent = "(Required)";
        document.getElementById('userRole').value = "salesman";
        document.getElementById('userStatus').value = "active";
        document.getElementById('modalBtn').textContent = "Save User";
        userModal.style.display = "block";
    }

    // Edit Mode
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('modalTitle').textContent = "Edit User";
            document.getElementById('formAction').value = "edit";
            document.getElementById('userId').value = this.getAttribute('data-id');
            document.getElementById('userName').value = this.getAttribute('data-name');
            document.getElementById('userUsername').value = this.getAttribute('data-username');
            document.getElementById('userEmail').value = this.getAttribute('data-email');
            document.getElementById('userPassword').value = "";
            document.getElementById('userPassword').required = false;
            document.getElementById('pwdHelp').textContent = "(Leave blank to keep current)";
            document.getElementById('userRole').value = this.getAttribute('data-role');
            document.getElementById('userStatus').value = this.getAttribute('data-status');
            document.getElementById('modalBtn').textContent = "Update User";
            userModal.style.display = "block";
        }
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
            userModal.style.display = "none";
            deleteModal.style.display = "none";
        }
    });

    window.onclick = function(event) {
        if (event.target == userModal) userModal.style.display = "none";
        if (event.target == deleteModal) deleteModal.style.display = "none";
    }
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>


