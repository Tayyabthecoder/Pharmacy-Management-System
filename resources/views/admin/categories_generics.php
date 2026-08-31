<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';

$totalCategories = count($categories);
$totalGenerics = count($generics);
$totalCategoryProducts = array_sum(array_column($categories, 'product_count'));
$totalGenericProducts = array_sum(array_column($generics, 'product_count'));
?>

<style>
/* High-Contrast & Theme-Adaptable Styling for Category & Generic Management */
:root {
    --cg-primary-accent: #3b82f6;
    --cg-purple-accent: #8b5cf6;
    --cg-success-accent: #10b981;
}

.cg-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.cg-kpi-card {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: var(--card-shadow, 0 4px 6px -1px rgba(0, 0, 0, 0.05));
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cg-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-hover-shadow, 0 10px 15px -3px rgba(0, 0, 0, 0.1));
}

.cg-kpi-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}

.cg-kpi-icon.primary {
    background: rgba(59, 130, 246, 0.15);
    color: #3b82f6;
}

.cg-kpi-icon.success {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.cg-kpi-icon.purple {
    background: rgba(139, 92, 246, 0.15);
    color: #8b5cf6;
}

.cg-kpi-info h4 {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cg-kpi-info .cg-kpi-value {
    font-size: 1.7rem;
    font-weight: 700;
    color: var(--text-color, #1e293b);
    margin: 0;
    line-height: 1;
}

.cg-tabs-container {
    display: flex;
    gap: 12px;
    border-bottom: 2px solid var(--surface-border, #e2e8f0);
    margin-bottom: 22px;
}

.cg-tab-btn {
    padding: 12px 24px;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
}

.cg-tab-btn:hover {
    color: var(--primary-color, #3b82f6);
}

.cg-tab-btn.active {
    color: var(--primary-color, #3b82f6);
    border-bottom-color: var(--primary-color, #3b82f6);
}

.cg-tab-badge {
    background: var(--surface-border, rgba(148, 163, 184, 0.2));
    color: var(--text-color, #334155);
    font-size: 0.78rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 12px;
    border: 1px solid var(--surface-border, transparent);
}

.cg-tab-btn.active .cg-tab-badge {
    background: rgba(59, 130, 246, 0.2);
    color: var(--primary-color, #2563eb);
    border-color: rgba(59, 130, 246, 0.3);
}

.cg-tab-content {
    display: none;
}

.cg-tab-content.active {
    display: block;
}

.cg-id-badge {
    display: inline-block;
    background: rgba(59, 130, 246, 0.1);
    color: var(--primary-color, #3b82f6);
    padding: 2px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.cat-name-text {
    color: var(--text-color, #1e293b);
    font-size: 0.96rem;
    font-weight: 600;
}

.gen-name-text {
    color: var(--primary-color, #8b5cf6);
    font-size: 0.96rem;
    font-weight: 600;
}

.text-muted-custom {
    color: var(--text-muted, #64748b) !important;
    font-size: 0.9rem;
}

.badge-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
}

.badge-count.has-items {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.badge-count.zero-items {
    background: rgba(148, 163, 184, 0.12);
    color: var(--text-muted, #64748b);
    border: 1px solid rgba(148, 163, 184, 0.2);
}

/* Modals & Form Contrast */
.modal-content {
    background: var(--card-bg, var(--surface-color, #ffffff)) !important;
    color: var(--text-color, #1e293b) !important;
    border: 1px solid var(--surface-border, #e2e8f0);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}

.modal-header h2, .modal-title {
    color: var(--text-color, #1e293b) !important;
}

.form-group label {
    color: var(--text-color, #1e293b) !important;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

.search-input-field {
    color: var(--text-color, #1e293b) !important;
    background: var(--card-bg, var(--surface-color, #ffffff)) !important;
    border: 1px solid var(--surface-border, #cbd5e1) !important;
}

.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 14px 20px;
    border-radius: 8px;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideInRight 0.3s ease forwards;
}

.toast-notification.success {
    background: #10b981;
}

.toast-notification.error {
    background: #ef4444;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
        <div>
            <h1 class="page-title"><i class="fas fa-tags" style="color: var(--primary-color, #3b82f6); margin-right: 8px;"></i> Categories & Generic Names</h1>
            <p class="text-muted-custom">Effortlessly edit, delete, and organize medicine categories and chemical compositions.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" onclick="openAddModal('category')">
                <i class="fas fa-plus"></i> Add Category
            </button>
            <button class="btn btn-secondary" onclick="openAddModal('generic')" style="background-color: var(--cg-purple-accent); color: #fff; border-color: var(--cg-purple-accent);">
                <i class="fas fa-flask"></i> Add Generic Name
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="cg-kpi-grid">
        <div class="cg-kpi-card">
            <div class="cg-kpi-icon primary">
                <i class="fas fa-folder-open"></i>
            </div>
            <div class="cg-kpi-info">
                <h4>Total Categories</h4>
                <div class="cg-kpi-value" id="kpiCategoryCount"><?php echo $totalCategories; ?></div>
            </div>
        </div>

        <div class="cg-kpi-card">
            <div class="cg-kpi-icon purple">
                <i class="fas fa-flask"></i>
            </div>
            <div class="cg-kpi-info">
                <h4>Generic Compositions</h4>
                <div class="cg-kpi-value" id="kpiGenericCount"><?php echo $totalGenerics; ?></div>
            </div>
        </div>

        <div class="cg-kpi-card">
            <div class="cg-kpi-icon success">
                <i class="fas fa-pills"></i>
            </div>
            <div class="cg-kpi-info">
                <h4>Linked Medicines</h4>
                <div class="cg-kpi-value"><?php echo ($totalCategoryProducts + $totalGenericProducts); ?></div>
            </div>
        </div>
    </div>

    <!-- Interactive Navigation Tabs -->
    <div class="cg-tabs-container">
        <button class="cg-tab-btn active" id="tabBtnCategories" onclick="switchTab('categories')">
            <i class="fas fa-list"></i> Medicine Categories 
            <span class="cg-tab-badge" id="badgeTabCategories"><?php echo $totalCategories; ?></span>
        </button>
        <button class="cg-tab-btn" id="tabBtnGenerics" onclick="switchTab('generics')">
            <i class="fas fa-flask"></i> Generic Compositions 
            <span class="cg-tab-badge" id="badgeTabGenerics"><?php echo $totalGenerics; ?></span>
        </button>
    </div>

    <!-- TAB 1: CATEGORIES -->
    <div class="cg-tab-content active" id="tabContentCategories">
        <div class="card" style="margin-bottom: 20px; padding: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1; min-width: 250px; position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted, #94a3b8);"></i>
                    <input type="text" id="searchCategoryInput" onkeyup="filterTable('categoriesTable', 'searchCategoryInput')" class="form-control search-input-field" placeholder="Search categories by name or description..." style="padding-left: 35px; width: 100%;">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th style="width: 170px; text-align: center;">Linked Medicines</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTbody">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr id="row-category-<?php echo $cat['id']; ?>">
                                    <td><span class="cg-id-badge">#<?php echo $cat['id']; ?></span></td>
                                    <td>
                                        <strong class="cat-name-text"><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted-custom cat-desc-text"><?php echo htmlspecialchars($cat['description'] ?? 'No description provided.'); ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-count <?php echo $cat['product_count'] > 0 ? 'has-items' : 'zero-items'; ?>">
                                            <i class="fas fa-pills"></i> <?php echo $cat['product_count']; ?> Medicine(s)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn btn-sm btn-edit" title="Edit Category"
                                                    onclick="openEditModal('category', <?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cat['description'] ?? '')); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-delete" title="Delete Category"
                                                    onclick="openDeleteModal('category', <?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>', <?php echo $cat['product_count']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyCategoriesRow">
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    No categories found. Click "Add Category" above to create one!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: GENERICS -->
    <div class="cg-tab-content" id="tabContentGenerics">
        <div class="card" style="margin-bottom: 20px; padding: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="flex: 1; min-width: 250px; position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted, #94a3b8);"></i>
                    <input type="text" id="searchGenericInput" onkeyup="filterTable('genericsTable', 'searchGenericInput')" class="form-control search-input-field" placeholder="Search generic compositions by name or details..." style="padding-left: 35px; width: 100%;">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table" id="genericsTable">
                    <thead>
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th>Generic Name (Composition)</th>
                            <th>Description / Remarks</th>
                            <th style="width: 170px; text-align: center;">Linked Medicines</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="genericsTbody">
                        <?php if (count($generics) > 0): ?>
                            <?php foreach ($generics as $gen): ?>
                                <tr id="row-generic-<?php echo $gen['id']; ?>">
                                    <td><span class="cg-id-badge">#<?php echo $gen['id']; ?></span></td>
                                    <td>
                                        <strong class="gen-name-text"><?php echo htmlspecialchars($gen['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted-custom gen-desc-text"><?php echo htmlspecialchars($gen['description'] ?? 'No details provided.'); ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-count <?php echo $gen['product_count'] > 0 ? 'has-items' : 'zero-items'; ?>">
                                            <i class="fas fa-pills"></i> <?php echo $gen['product_count']; ?> Medicine(s)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="btn btn-sm btn-edit" title="Edit Generic Name"
                                                    onclick="openEditModal('generic', <?php echo $gen['id']; ?>, '<?php echo htmlspecialchars(addslashes($gen['name'])); ?>', '<?php echo htmlspecialchars(addslashes($gen['description'] ?? '')); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-delete" title="Delete Generic Name"
                                                    onclick="openDeleteModal('generic', <?php echo $gen['id']; ?>, '<?php echo htmlspecialchars(addslashes($gen['name'])); ?>', <?php echo $gen['product_count']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyGenericsRow">
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-flask"></i>
                                    No generic compositions found. Click "Add Generic Name" above to create one!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: ADD CATEGORY / GENERIC -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 500px">
        <span class="close-modal" onclick="closeModal('addModal')">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title" id="addModalTitle"><i class="fas fa-plus-circle"></i> Add Item</h2>
        </div>
        <form id="addForm" onsubmit="handleAddSubmit(event)">
            <input type="hidden" id="addType" value="category">
            <div class="form-group">
                <label for="addName" id="addNameLabel">Name *</label>
                <input type="text" id="addName" class="form-control search-input-field" placeholder="Enter name" required>
            </div>
            <div class="form-group">
                <label for="addDesc">Description / Remarks (Optional)</label>
                <textarea id="addDesc" class="form-control search-input-field" rows="3" placeholder="Enter optional details..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveAdd"><i class="fas fa-check"></i> Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT CATEGORY / GENERIC -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 500px">
        <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
        <div class="modal-header">
            <h2 class="modal-title" id="editModalTitle"><i class="fas fa-edit"></i> Edit Item</h2>
        </div>
        <form id="editForm" onsubmit="handleEditSubmit(event)">
            <input type="hidden" id="editType" value="category">
            <input type="hidden" id="editId" value="0">
            <div class="form-group">
                <label for="editName" id="editNameLabel">Name *</label>
                <input type="text" id="editName" class="form-control search-input-field" placeholder="Enter name" required>
            </div>
            <div class="form-group">
                <label for="editDesc">Description / Remarks</label>
                <textarea id="editDesc" class="form-control search-input-field" rows="3" placeholder="Enter details..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveEdit"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: DELETE CONFIRMATION -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 480px">
        <span class="close-modal" onclick="closeModal('deleteModal')">&times;</span>
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <h2 class="modal-title" style="color: #ef4444 !important;"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h2>
        </div>
        <div style="padding: 15px 0;">
            <input type="hidden" id="deleteType" value="category">
            <input type="hidden" id="deleteId" value="0">
            
            <p style="font-size: 1rem; color: var(--text-color, #1e293b);">
                Are you sure you want to delete <strong id="deleteTargetName" style="color: var(--primary-color, #3b82f6);"></strong>?
            </p>

            <div id="deleteWarningBox" class="alert alert-warning" style="display: none; margin-top: 15px; font-size: 0.88rem;">
                <i class="fas fa-info-circle"></i>
                <span id="deleteWarningText">This item is assigned to 0 medicines. Deleting it will set their category/generic to NULL.</span>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="executeDelete()" id="btnConfirmDelete"><i class="fas fa-trash"></i> Yes, Delete</button>
        </div>
    </div>
</div>

<script>
const URL_ROOT = '<?php echo URL_ROOT; ?>';
const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s reverse forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function switchTab(tabName) {
    document.querySelectorAll('.cg-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.cg-tab-content').forEach(content => content.classList.remove('active'));

    if (tabName === 'categories') {
        document.getElementById('tabBtnCategories').classList.add('active');
        document.getElementById('tabContentCategories').classList.add('active');
    } else {
        document.getElementById('tabBtnGenerics').classList.add('active');
        document.getElementById('tabContentGenerics').classList.add('active');
    }
}

function filterTable(tableId, inputId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    const rows = document.querySelectorAll(`#${tableId} tbody tr`);
    
    rows.forEach(row => {
        if (row.classList.contains('empty-state-row')) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Window click to close modal
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

function openAddModal(type) {
    document.getElementById('addType').value = type;
    document.getElementById('addName').value = '';
    document.getElementById('addDesc').value = '';

    if (type === 'category') {
        document.getElementById('addModalTitle').innerHTML = '<i class="fas fa-folder-plus" style="color: var(--primary-color);"></i> Add New Category';
        document.getElementById('addNameLabel').innerText = 'Category Name *';
        document.getElementById('addName').placeholder = 'e.g. Antibiotics, Painkillers, Tablets';
    } else {
        document.getElementById('addModalTitle').innerHTML = '<i class="fas fa-flask" style="color: var(--cg-purple-accent);"></i> Add New Generic Name';
        document.getElementById('addNameLabel').innerText = 'Generic Composition Name *';
        document.getElementById('addName').placeholder = 'e.g. Paracetamol, Amoxicillin, Ibuprofen';
    }
    openModal('addModal');
}

function handleAddSubmit(e) {
    e.preventDefault();
    const type = document.getElementById('addType').value;
    const name = document.getElementById('addName').value.trim();
    const description = document.getElementById('addDesc').value.trim();

    if (!name) return;

    const endpoint = type === 'category' ? '/admin/categories/store' : '/admin/generics/store';
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('name', name);
    formData.append('description', description);

    fetch(URL_ROOT + endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: formData
    })
    .then(async res => {
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch (err) { data = null; }
        if (!res.ok) {
            throw new Error((data && data.message) ? data.message : 'Server error while saving.');
        }
        return data || { success: false, message: 'Invalid response from server.' };
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('addModal');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'Error occurred.', 'error');
        }
    })
    .catch(err => {
        showToast(err.message || 'Server error while saving.', 'error');
    });
}

function openEditModal(type, id, name, description) {
    document.getElementById('editType').value = type;
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = description;

    if (type === 'category') {
        document.getElementById('editModalTitle').innerHTML = '<i class="fas fa-edit" style="color: var(--primary-color);"></i> Edit Category';
        document.getElementById('editNameLabel').innerText = 'Category Name *';
    } else {
        document.getElementById('editModalTitle').innerHTML = '<i class="fas fa-edit" style="color: var(--cg-purple-accent);"></i> Edit Generic Composition';
        document.getElementById('editNameLabel').innerText = 'Generic Name (Composition) *';
    }
    openModal('editModal');
}

function handleEditSubmit(e) {
    e.preventDefault();
    const type = document.getElementById('editType').value;
    const id = document.getElementById('editId').value;
    const name = document.getElementById('editName').value.trim();
    const description = document.getElementById('editDesc').value.trim();

    if (!name || id <= 0) return;

    const endpoint = type === 'category' ? '/admin/categories/update' : '/admin/generics/update';
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('id', id);
    formData.append('name', name);
    formData.append('description', description);

    fetch(URL_ROOT + endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: formData
    })
    .then(async res => {
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch (err) { data = null; }
        if (!res.ok) {
            throw new Error((data && data.message) ? data.message : 'Server error while updating.');
        }
        return data || { success: false, message: 'Invalid response from server.' };
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('editModal');
            
            // Update row dynamically
            const rowPrefix = type === 'category' ? 'row-category-' : 'row-generic-';
            const row = document.getElementById(rowPrefix + id);
            if (row) {
                if (type === 'category') {
                    row.querySelector('.cat-name-text').innerText = name;
                    row.querySelector('.cat-desc-text').innerText = description || 'No description provided.';
                } else {
                    row.querySelector('.gen-name-text').innerText = name;
                    row.querySelector('.gen-desc-text').innerText = description || 'No details provided.';
                }
                const editBtn = row.querySelector('.btn-edit');
                if (editBtn) {
                    editBtn.setAttribute('onclick', `openEditModal('${type}', ${id}, ${JSON.stringify(name)}, ${JSON.stringify(description)})`);
                }
            } else {
                setTimeout(() => location.reload(), 600);
            }
        } else {
            showToast(data.message || 'Failed to update.', 'error');
        }
    })
    .catch(err => {
        showToast(err.message || 'Server error while updating.', 'error');
    });
}

function openDeleteModal(type, id, name, productCount) {
    document.getElementById('deleteType').value = type;
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteTargetName').innerText = name;

    const warningBox = document.getElementById('deleteWarningBox');
    const warningText = document.getElementById('deleteWarningText');

    if (productCount > 0) {
        warningText.innerText = `Warning: This ${type} is currently linked to ${productCount} medicine(s). Deleting it will set their ${type} to Unassigned (NULL).`;
        warningBox.style.display = 'block';
    } else {
        warningBox.style.display = 'none';
    }

    openModal('deleteModal');
}

function executeDelete() {
    const type = document.getElementById('deleteType').value;
    const id = document.getElementById('deleteId').value;

    if (id <= 0) return;

    const btn = document.getElementById('btnConfirmDelete');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    const endpoint = type === 'category' ? '/admin/categories/delete' : '/admin/generics/delete';
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('id', id);

    fetch(URL_ROOT + endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: formData
    })
    .then(async res => {
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch (err) { data = null; }
        if (!res.ok) {
            throw new Error((data && data.message) ? data.message : 'Server error while deleting.');
        }
        return data || { success: false, message: 'Invalid response from server.' };
    })
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Yes, Delete';
        
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('deleteModal');
            
            const rowPrefix = type === 'category' ? 'row-category-' : 'row-generic-';
            const row = document.getElementById(rowPrefix + id);
            if (row) {
                row.remove();
            } else {
                setTimeout(() => location.reload(), 600);
            }
        } else {
            showToast(data.message || 'Failed to delete.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Yes, Delete';
        showToast(err.message || 'Server error while deleting.', 'error');
    });
}
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
