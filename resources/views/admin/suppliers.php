<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';

// Compute metrics for top summary bar
$totSup = $totalSuppliersCount ?? count($suppliers);
$actSup = $activeSuppliersCount ?? count(array_filter($suppliers, function($s) { return ($s['status'] ?? 'active') === 'active'; }));

$totComp = $totalCompaniesCount ?? count($companies);
$actComp = $activeCompaniesCount ?? count(array_filter($companies, function($c) { return ($c['status'] ?? 'active') === 'active'; }));

$totPartners = $totSup + $totComp;
$actPartners = $actSup + $actComp;
$actRate = $totPartners > 0 ? round(($actPartners / $totPartners) * 100) : 0;
$inactPartners = $totPartners - $actPartners;

// Helper to compute initials for partner avatar badges
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
    return $initials ?: 'PA';
}
?>

<style>
/* Modern Scoped Theme System & Premium Layout for Supplier & Company Management */
:root {
    --sup-primary: #3b82f6;
    --sup-primary-hover: #2563eb;
    --sup-accent-purple: #8b5cf6;
    --sup-accent-success: #10b981;
    --sup-accent-warning: #f59e0b;
    --sup-accent-danger: #ef4444;
}

/* Tab Display Logic */
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}

/* Hero Header Bar */
.sup-hero-banner {
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

.sup-hero-title-group h1 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.sup-hero-title-group p {
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
}

.btn-primary-glow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

/* KPI Summary Cards Grid */
.sup-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.sup-kpi-card {
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

.sup-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-hover-shadow, 0 12px 24px -4px rgba(0, 0, 0, 0.08));
}

.sup-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.sup-kpi-card.suppliers::before { background: linear-gradient(180deg, #3b82f6, #60a5fa); }
.sup-kpi-card.companies::before { background: linear-gradient(180deg, #8b5cf6, #a78bfa); }
.sup-kpi-card.active-rate::before { background: linear-gradient(180deg, #10b981, #34d399); }
.sup-kpi-card.attention::before { background: linear-gradient(180deg, #f59e0b, #fbbf24); }

.sup-kpi-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.sup-kpi-icon.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.sup-kpi-icon.purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
.sup-kpi-icon.green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.sup-kpi-icon.amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }

.sup-kpi-info h4 {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sup-kpi-value {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--text-color, #1e293b);
    line-height: 1;
    margin-bottom: 4px;
}

.sup-kpi-subtext {
    font-size: 0.8rem;
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Control Toolbar & Filters */
.sup-control-panel {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    padding: 18px 22px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03));
    margin-bottom: 24px;
}

.sup-control-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.sup-tabs-wrapper {
    display: flex;
    background: var(--surface-border, rgba(226, 232, 240, 0.6));
    padding: 4px;
    border-radius: 12px;
    gap: 4px;
}

.sup-tab-btn {
    padding: 9px 22px;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    background: transparent;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.sup-tab-btn:hover {
    color: var(--primary-color, #3b82f6);
}

.sup-tab-btn.active {
    background: var(--card-bg, #ffffff);
    color: var(--primary-color, #3b82f6);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.sup-tab-badge {
    background: var(--surface-border, rgba(148, 163, 184, 0.2));
    color: var(--text-color, #334155);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
}

.sup-tab-btn.active .sup-tab-badge {
    background: rgba(59, 130, 246, 0.15);
    color: #3b82f6;
}

.sup-toolbar-right {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}

.sup-search-box {
    position: relative;
    min-width: 260px;
}

.sup-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted, #94a3b8);
    font-size: 0.9rem;
}

.sup-search-box input {
    width: 100%;
    padding: 9px 14px 9px 38px;
    font-size: 0.9rem;
    border-radius: 10px;
    border: 1px solid var(--surface-border, #cbd5e1);
    background: var(--input-bg, var(--card-bg, #ffffff));
    color: var(--text-color, #1e293b);
    transition: all 0.2s ease;
}

.sup-search-box input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    outline: none;
}

.sup-filter-select {
    padding: 9px 14px;
    font-size: 0.9rem;
    border-radius: 10px;
    border: 1px solid var(--surface-border, #cbd5e1);
    background: var(--input-bg, var(--card-bg, #ffffff));
    color: var(--text-color, #1e293b);
    cursor: pointer;
}

/* View Mode Switcher (Grid vs Table) */
.view-mode-toggle {
    display: flex;
    background: var(--surface-border, rgba(226, 232, 240, 0.6));
    padding: 3px;
    border-radius: 10px;
    gap: 2px;
}

.view-mode-btn {
    width: 36px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.view-mode-btn.active {
    background: var(--card-bg, #ffffff);
    color: #3b82f6;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

/* Grid Cards View Layout */
.partner-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.partner-card {
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
}

.partner-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-hover-shadow, 0 12px 24px -4px rgba(0, 0, 0, 0.08));
}

.partner-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.partner-avatar-wrapper {
    display: flex;
    align-items: center;
    gap: 14px;
}

.partner-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.05rem;
    color: #ffffff;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}

.partner-avatar.supplier-avatar {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.partner-avatar.company-avatar {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.partner-info h3 {
    margin: 0 0 3px 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    line-height: 1.2;
}

.partner-id-badge {
    font-size: 0.75rem;
    color: var(--text-muted, #64748b);
    font-weight: 600;
}

.partner-card-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 18px;

    background: var(--surface-border, rgba(241, 245, 249, 0.5));
    padding: 12px 14px;
    border-radius: 12px;
}

.partner-detail-line {
    font-size: 0.88rem;
    color: var(--text-color, #334155);
    display: flex;
    align-items: center;
    gap: 10px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.partner-detail-line i {
    color: var(--text-muted, #94a3b8);
    width: 16px;
    text-align: center;
    font-size: 0.85rem;
}

.partner-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid var(--card-border, rgba(226, 232, 240, 0.8));
}

/* Status Pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}

.status-pill.active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.status-pill.inactive {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-pill.active .status-dot {
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
}

.status-pill.inactive .status-dot {
    background: #ef4444;
}

/* Table View Layout */
.sup-table-card {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 16px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.8)));
    box-shadow: var(--card-shadow, 0 4px 14px rgba(0, 0, 0, 0.03));
    overflow: hidden;
}

.sup-table-responsive {
    overflow-x: auto;
}

.sup-custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.sup-custom-table th {
    background: var(--surface-border, rgba(241, 245, 249, 0.6));
    color: var(--text-muted, #475569);
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--card-border, #e2e8f0);
}

.sup-custom-table td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--card-border, rgba(226, 232, 240, 0.6));
    font-size: 0.9rem;
    color: var(--text-color, #334155);
    vertical-align: middle;
}

.sup-custom-table tbody tr {
    transition: background-color 0.15s ease;
}

.sup-custom-table tbody tr:hover {
    background-color: var(--surface-border, rgba(241, 245, 249, 0.4));
}

.action-btns-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-icon-action {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: var(--surface-border, rgba(241, 245, 249, 0.8));
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.88rem;
}

.btn-icon-action.view:hover {
    background: rgba(59, 130, 246, 0.15);
    color: #3b82f6;
}

.btn-icon-action.edit:hover {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}

.btn-icon-action.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #dc2626;
}

/* Empty State */
.sup-empty-state {
    padding: 50px 20px;
    text-align: center;
    color: var(--text-muted, #64748b);
    grid-column: 1 / -1;
}

.sup-empty-state i {
    font-size: 2.8rem;
    margin-bottom: 14px;
    opacity: 0.4;
    display: block;
}

/* Glassmorphism Modals */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(8px);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-glass-card {
    background: var(--card-bg, var(--surface-color, #ffffff));
    border-radius: 18px;
    border: 1px solid var(--card-border, var(--surface-border, rgba(226, 232, 240, 0.9)));
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 540px;
    overflow: hidden;
    animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalPopIn {
    from { opacity: 0; transform: scale(0.94) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-glass-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--card-border, rgba(226, 232, 240, 0.8));
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-glass-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--text-muted, #94a3b8);
    cursor: pointer;
    transition: color 0.15s ease;
}

.modal-close-btn:hover {
    color: var(--text-color, #0f172a);
}

.modal-glass-body {
    padding: 24px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group-custom {
    margin-bottom: 16px;
}

.form-group-custom label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted, #475569);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.form-control-custom {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid var(--surface-border, #cbd5e1);
    background: var(--input-bg, var(--card-bg, #ffffff));
    color: var(--text-color, #1e293b);
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-control-custom:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    outline: none;
}

.modal-glass-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--card-border, rgba(226, 232, 240, 0.8));
    background: var(--surface-border, rgba(241, 245, 249, 0.4));
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-secondary-custom {
    background: var(--surface-border, #e2e8f0);
    color: var(--text-color, #475569);
    border: none;
    padding: 9px 18px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
}

/* Quick View Detail Drawer Styling */
.partner-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px dashed var(--card-border, #cbd5e1);
    margin-bottom: 20px;
}

.partner-profile-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    font-weight: 700;
    color: #ffffff;
}

.partner-profile-meta h3 {
    margin: 0 0 4px 0;
    font-size: 1.2rem;
    color: var(--text-color, #0f172a);
}

.detail-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.detail-item {
    background: var(--surface-border, rgba(241, 245, 249, 0.6));
    padding: 12px 14px;
    border-radius: 10px;
}

.detail-item-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    font-weight: 600;
    margin-bottom: 4px;
}

.detail-item-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--text-color, #1e293b);
}
</style>

<div class="dashboard-container">
    <!-- Streamlined Hero Header -->
    <div class="sup-hero-banner">
        <div class="sup-hero-title-group">
            <h1><i class="fas fa-handshake" style="color: #3b82f6;"></i> Suppliers & Companies</h1>
            <p>Centralized partner management for pharmaceutical distributors and manufacturing companies</p>
        </div>
        <button class="btn-primary-glow" id="openAddModal">
            <i class="fas fa-plus-circle"></i> <span id="addBtnText">Add Supplier</span>
        </button>
    </div>

    <!-- Alert Banner -->
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo htmlspecialchars($msgType); ?>" style="border-radius: 14px; margin-bottom: 24px;">
            <i class="fas fa-<?php echo $msgType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Grid -->
    <div class="sup-kpi-grid">
        <div class="sup-kpi-card suppliers">
            <div class="sup-kpi-icon blue">
                <i class="fas fa-truck-field"></i>
            </div>
            <div class="sup-kpi-info">
                <h4>Total Suppliers</h4>
                <div class="sup-kpi-value"><?php echo $totSup; ?></div>
                <div class="sup-kpi-subtext"><i class="fas fa-check-circle" style="color:#10b981;"></i> <?php echo $actSup; ?> Active</div>
            </div>
        </div>

        <div class="sup-kpi-card companies">
            <div class="sup-kpi-icon purple">
                <i class="fas fa-building"></i>
            </div>
            <div class="sup-kpi-info">
                <h4>Total Companies</h4>
                <div class="sup-kpi-value"><?php echo $totComp; ?></div>
                <div class="sup-kpi-subtext"><i class="fas fa-check-circle" style="color:#10b981;"></i> <?php echo $actComp; ?> Active</div>
            </div>
        </div>

        <div class="sup-kpi-card active-rate">
            <div class="sup-kpi-icon green">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="sup-kpi-info">
                <h4>Active Partner Rate</h4>
                <div class="sup-kpi-value"><?php echo $actRate; ?>%</div>
                <div class="sup-kpi-subtext"><?php echo $actPartners; ?> of <?php echo $totPartners; ?> Partners Operational</div>
            </div>
        </div>

        <div class="sup-kpi-card attention">
            <div class="sup-kpi-icon amber">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="sup-kpi-info">
                <h4>Inactive Accounts</h4>
                <div class="sup-kpi-value"><?php echo $inactPartners; ?></div>
                <div class="sup-kpi-subtext">Partners Needing Review</div>
            </div>
        </div>
    </div>

    <!-- Integrated Toolbar & Controls -->
    <div class="sup-control-panel">
        <div class="sup-control-header">
            <div class="sup-tabs-wrapper">
                <button type="button" class="sup-tab-btn <?php echo $activeTab === 'suppliers' ? 'active' : ''; ?>" data-tab="suppliers" onclick="switchTab('suppliers')">
                    <i class="fas fa-truck"></i> Suppliers
                    <span class="sup-tab-badge"><?php echo count($suppliers); ?></span>
                </button>
                <button type="button" class="sup-tab-btn <?php echo $activeTab === 'companies' ? 'active' : ''; ?>" data-tab="companies" onclick="switchTab('companies')">
                    <i class="fas fa-building"></i> Companies
                    <span class="sup-tab-badge"><?php echo count($companies); ?></span>
                </button>
            </div>

            <div class="sup-toolbar-right">
                <div class="sup-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tableSearchInput" placeholder="Search partner, contact, phone..." onkeyup="filterView()">
                </div>

                <select id="statusFilterSelect" class="sup-filter-select" onchange="filterView()">
                    <option value="all">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>

                <!-- Dual View Mode Toggle Switch -->
                <div class="view-mode-toggle">
                    <button type="button" class="view-mode-btn active" id="viewGridBtn" onclick="setViewMode('grid')" title="Grid Cards View">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="view-mode-btn" id="viewTableBtn" onclick="setViewMode('table')" title="List Table View">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SUPPLIERS SECTION -->
    <div id="suppliers" class="tab-content <?php echo $activeTab === 'suppliers' ? 'active' : ''; ?>">
        <!-- Grid View Container -->
        <div class="partner-cards-grid view-grid-container" id="suppliersGrid">
            <?php if (count($suppliers) > 0): ?>
                <?php foreach ($suppliers as $sup): ?>
                    <div class="partner-card"
                         data-name="<?php echo strtolower(htmlspecialchars($sup['name'])); ?>"
                         data-contact="<?php echo strtolower(htmlspecialchars($sup['contact_name'] ?? '')); ?>"
                         data-email="<?php echo strtolower(htmlspecialchars($sup['email'] ?? '')); ?>"
                         data-phone="<?php echo strtolower(htmlspecialchars($sup['phone'] ?? '')); ?>"
                         data-address="<?php echo strtolower(htmlspecialchars($sup['address'] ?? '')); ?>"
                         data-status="<?php echo strtolower($sup['status']); ?>">
                        <div>
                            <div class="partner-card-header">
                                <div class="partner-avatar-wrapper">
                                    <div class="partner-avatar supplier-avatar">
                                        <?php echo getInitials($sup['name']); ?>
                                    </div>
                                    <div class="partner-info">
                                        <h3><?php echo htmlspecialchars($sup['name']); ?></h3>
                                        <span class="partner-id-badge">#SUP-<?php echo sprintf('%03d', $sup['id']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="partner-card-body">
                                <div class="partner-detail-line">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo !empty($sup['contact_name']) ? htmlspecialchars($sup['contact_name']) : '<span class="text-muted">No contact person</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo !empty($sup['email']) ? htmlspecialchars($sup['email']) : '<span class="text-muted">No email address</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo !empty($sup['phone']) ? htmlspecialchars($sup['phone']) : '<span class="text-muted">No phone number</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo !empty($sup['address']) ? htmlspecialchars(mb_strimwidth($sup['address'], 0, 32, '...')) : '<span class="text-muted">No address provided</span>'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="partner-card-footer">
                            <span class="status-pill <?php echo $sup['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <span class="status-dot"></span>
                                <?php echo ucfirst($sup['status']); ?>
                            </span>

                            <div class="action-btns-wrapper">
                                <button class="btn-icon-action view view-btn"
                                        title="View Details"
                                        data-type="supplier"
                                        data-id="<?php echo $sup['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($sup['name']); ?>"
                                        data-contact="<?php echo htmlspecialchars($sup['contact_name'] ?? '-'); ?>"
                                        data-email="<?php echo htmlspecialchars($sup['email'] ?? '-'); ?>"
                                        data-phone="<?php echo htmlspecialchars($sup['phone'] ?? '-'); ?>"
                                        data-address="<?php echo htmlspecialchars($sup['address'] ?? '-'); ?>"
                                        data-status="<?php echo ucfirst($sup['status']); ?>"
                                        data-initials="<?php echo getInitials($sup['name']); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon-action edit edit-btn"
                                        title="Edit Supplier"
                                        data-type="supplier"
                                        data-id="<?php echo $sup['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($sup['name']); ?>"
                                        data-contact="<?php echo htmlspecialchars($sup['contact_name'] ?? ''); ?>"
                                        data-email="<?php echo htmlspecialchars($sup['email'] ?? ''); ?>"
                                        data-phone="<?php echo htmlspecialchars($sup['phone'] ?? ''); ?>"
                                        data-address="<?php echo htmlspecialchars($sup['address'] ?? ''); ?>"
                                        data-status="<?php echo $sup['status']; ?>">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-icon-action delete delete-btn"
                                        title="Delete Supplier"
                                        data-type="supplier"
                                        data-id="<?php echo $sup['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($sup['name']); ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sup-empty-state">
                    <i class="fas fa-truck-loading"></i>
                    <p style="margin: 0; font-weight: 600;">No suppliers found</p>
                    <small>Click "Add Supplier" above to register a new distributor.</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Table View Container -->
        <div class="sup-table-card view-table-container" id="suppliersTableCard" style="display: none;">
            <div class="sup-table-responsive">
                <table class="sup-custom-table" id="suppliersTable">
                    <thead>
                        <tr>
                            <th>Partner ID</th>
                            <th>Supplier Name</th>
                            <th>Contact Person</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($suppliers) > 0): ?>
                            <?php foreach ($suppliers as $sup): ?>
                                <tr data-name="<?php echo strtolower(htmlspecialchars($sup['name'])); ?>"
                                    data-contact="<?php echo strtolower(htmlspecialchars($sup['contact_name'] ?? '')); ?>"
                                    data-email="<?php echo strtolower(htmlspecialchars($sup['email'] ?? '')); ?>"
                                    data-phone="<?php echo strtolower(htmlspecialchars($sup['phone'] ?? '')); ?>"
                                    data-address="<?php echo strtolower(htmlspecialchars($sup['address'] ?? '')); ?>"
                                    data-status="<?php echo strtolower($sup['status']); ?>">
                                    <td><span class="partner-id-badge">#SUP-<?php echo sprintf('%03d', $sup['id']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($sup['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sup['contact_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($sup['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($sup['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($sup['address'] ?? '-', 0, 30, '...')); ?></td>
                                    <td>
                                        <span class="status-pill <?php echo $sup['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                            <span class="status-dot"></span> <?php echo ucfirst($sup['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns-wrapper" style="justify-content: flex-end;">
                                            <button class="btn-icon-action view view-btn"
                                                    title="View Details"
                                                    data-type="supplier"
                                                    data-id="<?php echo $sup['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($sup['name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($sup['contact_name'] ?? '-'); ?>"
                                                    data-email="<?php echo htmlspecialchars($sup['email'] ?? '-'); ?>"
                                                    data-phone="<?php echo htmlspecialchars($sup['phone'] ?? '-'); ?>"
                                                    data-address="<?php echo htmlspecialchars($sup['address'] ?? '-'); ?>"
                                                    data-status="<?php echo ucfirst($sup['status']); ?>"
                                                    data-initials="<?php echo getInitials($sup['name']); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon-action edit edit-btn"
                                                    title="Edit Supplier"
                                                    data-type="supplier"
                                                    data-id="<?php echo $sup['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($sup['name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($sup['contact_name'] ?? ''); ?>"
                                                    data-email="<?php echo htmlspecialchars($sup['email'] ?? ''); ?>"
                                                    data-phone="<?php echo htmlspecialchars($sup['phone'] ?? ''); ?>"
                                                    data-address="<?php echo htmlspecialchars($sup['address'] ?? ''); ?>"
                                                    data-status="<?php echo $sup['status']; ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn-icon-action delete delete-btn"
                                                    title="Delete Supplier"
                                                    data-type="supplier"
                                                    data-id="<?php echo $sup['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($sup['name']); ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="sup-empty-state"><i class="fas fa-truck-loading"></i> No suppliers found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($pagination)): ?>
            <div style="margin-top: 20px;">
                <?php echo renderPagination($pagination, url('/admin/suppliers')); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- COMPANIES SECTION -->
    <div id="companies" class="tab-content <?php echo $activeTab === 'companies' ? 'active' : ''; ?>">
        <!-- Grid View Container -->
        <div class="partner-cards-grid view-grid-container" id="companiesGrid">
            <?php if (count($companies) > 0): ?>
                <?php foreach ($companies as $comp): ?>
                    <div class="partner-card"
                         data-name="<?php echo strtolower(htmlspecialchars($comp['name'])); ?>"
                         data-contact="<?php echo strtolower(htmlspecialchars($comp['contact_name'] ?? '')); ?>"
                         data-email="<?php echo strtolower(htmlspecialchars($comp['email'] ?? '')); ?>"
                         data-phone="<?php echo strtolower(htmlspecialchars($comp['phone'] ?? '')); ?>"
                         data-address="<?php echo strtolower(htmlspecialchars($comp['address'] ?? '')); ?>"
                         data-status="<?php echo strtolower($comp['status']); ?>">
                        <div>
                            <div class="partner-card-header">
                                <div class="partner-avatar-wrapper">
                                    <div class="partner-avatar company-avatar">
                                        <?php echo getInitials($comp['name']); ?>
                                    </div>
                                    <div class="partner-info">
                                        <h3><?php echo htmlspecialchars($comp['name']); ?></h3>
                                        <span class="partner-id-badge">#CMP-<?php echo sprintf('%03d', $comp['id']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="partner-card-body">
                                <div class="partner-detail-line">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo !empty($comp['contact_name']) ? htmlspecialchars($comp['contact_name']) : '<span class="text-muted">No contact person</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo !empty($comp['email']) ? htmlspecialchars($comp['email']) : '<span class="text-muted">No email address</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo !empty($comp['phone']) ? htmlspecialchars($comp['phone']) : '<span class="text-muted">No phone number</span>'; ?></span>
                                </div>
                                <div class="partner-detail-line">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo !empty($comp['address']) ? htmlspecialchars(mb_strimwidth($comp['address'], 0, 32, '...')) : '<span class="text-muted">No address provided</span>'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="partner-card-footer">
                            <span class="status-pill <?php echo $comp['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                <span class="status-dot"></span>
                                <?php echo ucfirst($comp['status']); ?>
                            </span>

                            <div class="action-btns-wrapper">
                                <button class="btn-icon-action view view-btn"
                                        title="View Details"
                                        data-type="company"
                                        data-id="<?php echo $comp['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($comp['name']); ?>"
                                        data-contact="<?php echo htmlspecialchars($comp['contact_name'] ?? '-'); ?>"
                                        data-email="<?php echo htmlspecialchars($comp['email'] ?? '-'); ?>"
                                        data-phone="<?php echo htmlspecialchars($comp['phone'] ?? '-'); ?>"
                                        data-address="<?php echo htmlspecialchars($comp['address'] ?? '-'); ?>"
                                        data-status="<?php echo ucfirst($comp['status']); ?>"
                                        data-initials="<?php echo getInitials($comp['name']); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon-action edit edit-btn"
                                        title="Edit Company"
                                        data-type="company"
                                        data-id="<?php echo $comp['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($comp['name']); ?>"
                                        data-contact="<?php echo htmlspecialchars($comp['contact_name'] ?? ''); ?>"
                                        data-email="<?php echo htmlspecialchars($comp['email'] ?? ''); ?>"
                                        data-phone="<?php echo htmlspecialchars($comp['phone'] ?? ''); ?>"
                                        data-address="<?php echo htmlspecialchars($comp['address'] ?? ''); ?>"
                                        data-status="<?php echo $comp['status']; ?>">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-icon-action delete delete-btn"
                                        title="Delete Company"
                                        data-type="company"
                                        data-id="<?php echo $comp['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($comp['name']); ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sup-empty-state">
                    <i class="fas fa-building"></i>
                    <p style="margin: 0; font-weight: 600;">No companies found</p>
                    <small>Click "Add Company" above to register a new manufacturing partner.</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Table View Container -->
        <div class="sup-table-card view-table-container" id="companiesTableCard" style="display: none;">
            <div class="sup-table-responsive">
                <table class="sup-custom-table" id="companiesTable">
                    <thead>
                        <tr>
                            <th>Company ID</th>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($companies) > 0): ?>
                            <?php foreach ($companies as $comp): ?>
                                <tr data-name="<?php echo strtolower(htmlspecialchars($comp['name'])); ?>"
                                    data-contact="<?php echo strtolower(htmlspecialchars($comp['contact_name'] ?? '')); ?>"
                                    data-email="<?php echo strtolower(htmlspecialchars($comp['email'] ?? '')); ?>"
                                    data-phone="<?php echo strtolower(htmlspecialchars($comp['phone'] ?? '')); ?>"
                                    data-address="<?php echo strtolower(htmlspecialchars($comp['address'] ?? '')); ?>"
                                    data-status="<?php echo strtolower($comp['status']); ?>">
                                    <td><span class="partner-id-badge">#CMP-<?php echo sprintf('%03d', $comp['id']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($comp['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($comp['contact_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($comp['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($comp['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($comp['address'] ?? '-', 0, 30, '...')); ?></td>
                                    <td>
                                        <span class="status-pill <?php echo $comp['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                            <span class="status-dot"></span> <?php echo ucfirst($comp['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns-wrapper" style="justify-content: flex-end;">
                                            <button class="btn-icon-action view view-btn"
                                                    title="View Details"
                                                    data-type="company"
                                                    data-id="<?php echo $comp['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($comp['name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($comp['contact_name'] ?? '-'); ?>"
                                                    data-email="<?php echo htmlspecialchars($comp['email'] ?? '-'); ?>"
                                                    data-phone="<?php echo htmlspecialchars($comp['phone'] ?? '-'); ?>"
                                                    data-address="<?php echo htmlspecialchars($comp['address'] ?? '-'); ?>"
                                                    data-status="<?php echo ucfirst($comp['status']); ?>"
                                                    data-initials="<?php echo getInitials($comp['name']); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn-icon-action edit edit-btn"
                                                    title="Edit Company"
                                                    data-type="company"
                                                    data-id="<?php echo $comp['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($comp['name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($comp['contact_name'] ?? ''); ?>"
                                                    data-email="<?php echo htmlspecialchars($comp['email'] ?? ''); ?>"
                                                    data-phone="<?php echo htmlspecialchars($comp['phone'] ?? ''); ?>"
                                                    data-address="<?php echo htmlspecialchars($comp['address'] ?? ''); ?>"
                                                    data-status="<?php echo $comp['status']; ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn-icon-action delete delete-btn"
                                                    title="Delete Company"
                                                    data-type="company"
                                                    data-id="<?php echo $comp['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($comp['name']); ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="sup-empty-state"><i class="fas fa-building"></i> No companies found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($compPagination)): ?>
            <div style="margin-top: 20px;">
                <?php 
                    $compLinks = renderPagination($compPagination, url('/admin/suppliers'));
                    $compLinks = str_replace('page=', 'cpage=', $compLinks);
                    echo $compLinks; 
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Glassmorphism Add/Edit Modal -->
<div id="supplierModal" class="modal-overlay">
    <div class="modal-glass-card">
        <div class="modal-glass-header">
            <h2 class="modal-glass-title" id="modalTitle">
                <i class="fas fa-plus-circle" style="color: #3b82f6;"></i> Add Supplier
            </h2>
            <button type="button" class="modal-close-btn close-modal-btn">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-glass-body">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="type" id="formType" value="supplier">
                <input type="hidden" name="id" id="supId">
                
                <div class="form-group-custom">
                    <label for="supName" id="nameLabel">Supplier Name *</label>
                    <input type="text" name="name" id="supName" class="form-control-custom" placeholder="e.g. Acme Pharmaceuticals Ltd" required>
                </div>
                
                <div class="form-group-custom">
                    <label for="supContact">Contact Person</label>
                    <input type="text" name="contact_name" id="supContact" class="form-control-custom" placeholder="e.g. John Doe (Account Manager)">
                </div>

                <div class="form-grid-2">
                    <div class="form-group-custom">
                        <label for="supEmail">Email Address</label>
                        <input type="email" name="email" id="supEmail" class="form-control-custom" placeholder="sales@partner.com">
                    </div>
                    <div class="form-group-custom">
                        <label for="supPhone">Phone Number</label>
                        <input type="text" name="phone" id="supPhone" class="form-control-custom" placeholder="+1 (555) 000-0000">
                    </div>
                </div>

                <div class="form-group-custom">
                    <label for="supAddress">Physical Address</label>
                    <textarea name="address" id="supAddress" class="form-control-custom" rows="2" placeholder="Street address, city, state..."></textarea>
                </div>

                <div class="form-group-custom" style="margin-bottom: 0;">
                    <label for="supStatus">Status</label>
                    <select name="status" id="supStatus" class="form-control-custom">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="modal-glass-footer">
                <button type="button" class="btn-secondary-custom close-modal-btn">Cancel</button>
                <button type="submit" class="btn-primary-glow" id="modalBtn">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick View Profile Modal -->
<div id="viewDetailsModal" class="modal-overlay">
    <div class="modal-glass-card" style="max-width: 480px;">
        <div class="modal-glass-header">
            <h2 class="modal-glass-title" id="viewModalTitle">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Partner Profile
            </h2>
            <button type="button" class="modal-close-btn close-view-modal">&times;</button>
        </div>
        <div class="modal-glass-body">
            <div class="partner-profile-header">
                <div class="partner-profile-avatar" id="viewAvatar" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                    PA
                </div>
                <div class="partner-profile-meta">
                    <h3 id="viewName">Partner Name</h3>
                    <span class="status-pill active" id="viewStatusBadge">
                        <span class="status-dot"></span> <span id="viewStatusText">Active</span>
                    </span>
                </div>
            </div>

            <div class="detail-info-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Entity ID</div>
                    <div class="detail-item-value" id="viewId">-</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Entity Type</div>
                    <div class="detail-item-value" id="viewType">-</div>
                </div>
                <div class="detail-item" style="grid-column: span 2;">
                    <div class="detail-item-label">Contact Person</div>
                    <div class="detail-item-value" id="viewContact">-</div>
                </div>
                <div class="detail-item" style="grid-column: span 2;">
                    <div class="detail-item-label">Email Address</div>
                    <div class="detail-item-value" id="viewEmail">-</div>
                </div>
                <div class="detail-item" style="grid-column: span 2;">
                    <div class="detail-item-label">Phone Number</div>
                    <div class="detail-item-value" id="viewPhone">-</div>
                </div>
                <div class="detail-item" style="grid-column: span 2;">
                    <div class="detail-item-label">Address</div>
                    <div class="detail-item-value" id="viewAddress">-</div>
                </div>
            </div>
        </div>
        <div class="modal-glass-footer">
            <button type="button" class="btn-secondary-custom close-view-modal">Close</button>
        </div>
    </div>
</div>

<!-- Glassmorphism Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-glass-card" style="max-width: 420px;">
        <div class="modal-glass-header">
            <h2 class="modal-glass-title" style="color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
            </h2>
            <button type="button" class="modal-close-btn close-delete-modal">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-glass-body" style="text-align: center; padding: 30px 24px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 16px auto;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p id="deleteMsg" style="font-size: 0.95rem; color: var(--text-color, #1e293b); margin: 0; line-height: 1.5;">
                    Are you sure you want to delete this partner record? This action cannot be undone.
                </p>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" id="deleteType" value="supplier">
                <input type="hidden" name="delete_id" id="deleteId">
            </div>

            <div class="modal-glass-footer" style="justify-content: center;">
                <button type="button" class="btn-secondary-custom close-delete-modal">Cancel</button>
                <button type="submit" class="btn-primary-glow" style="background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
                    Yes, Delete Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentTab = '<?php echo $activeTab; ?>';
    let currentViewMode = localStorage.getItem('sup_view_mode') || 'grid';

    // Apply default view mode and active tab on DOM content load
    document.addEventListener('DOMContentLoaded', () => {
        switchTab(currentTab);
        setViewMode(currentViewMode);
    });

    function switchTab(tabId) {
        currentTab = tabId;
        
        // Hide all tab content containers
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.remove('active');
        });

        // Deactivate all tab buttons
        document.querySelectorAll('.sup-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activate selected tab content
        const activeTabEl = document.getElementById(tabId);
        if (activeTabEl) {
            activeTabEl.classList.add('active');
        }
        
        // Activate selected tab button
        const btn = document.querySelector(`.sup-tab-btn[data-tab="${tabId}"]`);
        if (btn) {
            btn.classList.add('active');
        }
        
        // Dynamic Add button text
        const addBtnText = document.getElementById('addBtnText');
        if (addBtnText) {
            addBtnText.innerText = tabId === 'companies' ? 'Add Company' : 'Add Supplier';
        }
        
        filterView();
    }

    function setViewMode(mode) {
        currentViewMode = mode;
        localStorage.setItem('sup_view_mode', mode);

        const gridContainers = document.querySelectorAll('.view-grid-container');
        const tableContainers = document.querySelectorAll('.view-table-container');

        const gridBtn = document.getElementById('viewGridBtn');
        const tableBtn = document.getElementById('viewTableBtn');

        if (mode === 'grid') {
            gridContainers.forEach(el => el.style.display = 'grid');
            tableContainers.forEach(el => el.style.display = 'none');
            if (gridBtn) gridBtn.classList.add('active');
            if (tableBtn) tableBtn.classList.remove('active');
        } else {
            gridContainers.forEach(el => el.style.display = 'none');
            tableContainers.forEach(el => el.style.display = 'block');
            if (gridBtn) gridBtn.classList.remove('active');
            if (tableBtn) tableBtn.classList.add('active');
        }

        filterView();
    }

    // Client-side search and status filter across active Tab (Grid & Table)
    function filterView() {
        const queryInput = document.getElementById('tableSearchInput');
        const statusSelect = document.getElementById('statusFilterSelect');
        if (!queryInput || !statusSelect) return;

        const query = queryInput.value.toLowerCase().trim();
        const statusFilter = statusSelect.value.toLowerCase();
        
        const isComp = currentTab === 'companies';
        const gridContainerId = isComp ? 'companiesGrid' : 'suppliersGrid';
        const tableId = isComp ? 'companiesTable' : 'suppliersTable';

        // Filter Grid Cards
        const gridEl = document.getElementById(gridContainerId);
        if (gridEl) {
            const cards = gridEl.querySelectorAll('.partner-card');
            cards.forEach(card => {
                const name = card.dataset.name || '';
                const contact = card.dataset.contact || '';
                const email = card.dataset.email || '';
                const phone = card.dataset.phone || '';
                const address = card.dataset.address || '';
                const status = card.dataset.status || '';

                const matchesSearch = !query || name.includes(query) || contact.includes(query) || email.includes(query) || phone.includes(query) || address.includes(query);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                card.style.display = (matchesSearch && matchesStatus) ? 'flex' : 'none';
            });
        }

        // Filter Table Rows
        const tableEl = document.getElementById(tableId);
        if (tableEl) {
            const rows = tableEl.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip empty row
                
                const name = row.dataset.name || '';
                const contact = row.dataset.contact || '';
                const email = row.dataset.email || '';
                const phone = row.dataset.phone || '';
                const address = row.dataset.address || '';
                const status = row.dataset.status || '';

                const matchesSearch = !query || name.includes(query) || contact.includes(query) || email.includes(query) || phone.includes(query) || address.includes(query);
                const matchesStatus = statusFilter === 'all' || status === statusFilter;

                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }
    }

    // Modal controls
    const supplierModal = document.getElementById('supplierModal');
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const deleteModal = document.getElementById('deleteModal');
    const openAddBtn = document.getElementById('openAddModal');

    if (openAddBtn) {
        openAddBtn.addEventListener('click', () => {
            const isCompany = currentTab === 'companies';
            const entity = isCompany ? 'Company' : 'Supplier';
            
            document.getElementById('modalTitle').innerHTML = `<i class="fas fa-plus-circle" style="color: #3b82f6;"></i> Add ${entity}`;
            document.getElementById('formAction').value = "add";
            document.getElementById('formType').value = isCompany ? "company" : "supplier";
            document.getElementById('nameLabel').innerText = `${entity} Name *`;
            
            document.getElementById('supId').value = "";
            document.getElementById('supName').value = "";
            document.getElementById('supContact').value = "";
            document.getElementById('supEmail').value = "";
            document.getElementById('supPhone').value = "";
            document.getElementById('supAddress').value = "";
            document.getElementById('supStatus').value = "active";
            
            document.getElementById('modalBtn').innerText = `Save ${entity}`;
            supplierModal.style.display = 'flex';
        });
    }

    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => supplierModal.style.display = 'none');
    });

    document.querySelectorAll('.close-view-modal').forEach(btn => {
        btn.addEventListener('click', () => viewDetailsModal.style.display = 'none');
    });

    document.querySelectorAll('.close-delete-modal').forEach(btn => {
        btn.addEventListener('click', () => deleteModal.style.display = 'none');
    });

    window.addEventListener('click', (e) => {
        if (e.target === supplierModal) supplierModal.style.display = 'none';
        if (e.target === viewDetailsModal) viewDetailsModal.style.display = 'none';
        if (e.target === deleteModal) deleteModal.style.display = 'none';
    });

    // Quick View Profile Modal
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            const entityLabel = type === 'company' ? 'Company' : 'Supplier';
            const prefix = type === 'company' ? '#CMP-' : '#SUP-';
            
            document.getElementById('viewModalTitle').innerHTML = `<i class="fas fa-building" style="color: #3b82f6;"></i> ${entityLabel} Profile`;
            document.getElementById('viewId').innerText = prefix + String(btn.dataset.id).padStart(3, '0');
            document.getElementById('viewType').innerText = entityLabel;
            document.getElementById('viewName').innerText = btn.dataset.name;
            document.getElementById('viewContact').innerText = btn.dataset.contact;
            document.getElementById('viewEmail').innerText = btn.dataset.email;
            document.getElementById('viewPhone').innerText = btn.dataset.phone;
            document.getElementById('viewAddress').innerText = btn.dataset.address;
            document.getElementById('viewAvatar').innerText = btn.dataset.initials;
            document.getElementById('viewAvatar').className = `partner-profile-avatar ${type}-avatar`;
            
            const isAct = btn.dataset.status.toLowerCase() === 'active';
            const statusBadge = document.getElementById('viewStatusBadge');
            statusBadge.className = `status-pill ${isAct ? 'active' : 'inactive'}`;
            document.getElementById('viewStatusText').innerText = isAct ? 'Active' : 'Inactive';

            viewDetailsModal.style.display = 'flex';
        });
    });

    // Edit Modal prefill
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            const entity = type === 'company' ? 'Company' : 'Supplier';
            
            document.getElementById('modalTitle').innerHTML = `<i class="fas fa-edit" style="color: #3b82f6;"></i> Edit ${entity}`;
            document.getElementById('formAction').value = "edit";
            document.getElementById('formType').value = type;
            document.getElementById('nameLabel').innerText = `${entity} Name *`;
            
            document.getElementById('supId').value = btn.dataset.id;
            document.getElementById('supName').value = btn.dataset.name;
            document.getElementById('supContact').value = btn.dataset.contact;
            document.getElementById('supEmail').value = btn.dataset.email;
            document.getElementById('supPhone').value = btn.dataset.phone;
            document.getElementById('supAddress').value = btn.dataset.address;
            document.getElementById('supStatus').value = btn.dataset.status;
            
            document.getElementById('modalBtn').innerText = `Update ${entity}`;
            supplierModal.style.display = 'flex';
        });
    });

    // Delete Modal confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            const entityName = btn.dataset.name;
            
            document.getElementById('deleteType').value = type;
            document.getElementById('deleteId').value = btn.dataset.id;
            document.getElementById('deleteMsg').innerHTML = `Are you sure you want to delete <strong>"${entityName}"</strong>? This action cannot be undone.`;
            
            deleteModal.style.display = 'flex';
        });
    });
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
