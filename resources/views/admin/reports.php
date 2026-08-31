<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════
   REPORT PAGE — PREMIUM UI STYLES
   ═══════════════════════════════════════════════════════════ */

/* ── Entrance Animations ─────────────────────────────── */
@keyframes rpt-fadeSlideUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes rpt-scaleIn {
    from { opacity: 0; transform: scale(0.92); }
    to   { opacity: 1; transform: scale(1); }
}
@keyframes rpt-shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
@keyframes rpt-pulse {
    0%, 100% { opacity: 1; }
    50%      { opacity: 0.5; }
}
@keyframes rpt-countUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.rpt-animate {
    animation: rpt-fadeSlideUp 0.5s ease-out both;
}
.rpt-animate-delay-1 { animation-delay: 0.06s; }
.rpt-animate-delay-2 { animation-delay: 0.12s; }
.rpt-animate-delay-3 { animation-delay: 0.18s; }
.rpt-animate-delay-4 { animation-delay: 0.24s; }
.rpt-animate-delay-5 { animation-delay: 0.30s; }
.rpt-animate-delay-6 { animation-delay: 0.36s; }

/* ── Page Header ─────────────────────────────────────── */
.rpt-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.rpt-page-header h1 {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
    background: linear-gradient(135deg, var(--text-color) 0%, var(--primary-color, #6366f1) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.rpt-page-header .rpt-subtitle {
    font-size: 0.88rem;
    color: var(--text-muted, #94a3b8);
    font-weight: 400;
}
.rpt-page-header .rpt-header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}
.btn-print-report {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: var(--radius-md, 8px);
    border: none;
    background: linear-gradient(135deg, var(--primary-color, #6366f1), #8b5cf6);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(var(--primary-rgb, 99, 102, 241), 0.3);
}
.btn-print-report:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(var(--primary-rgb, 99, 102, 241), 0.4);
}

/* ── Filter Toolbar ──────────────────────────────────── */
.rpt-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: flex-end;
    padding: 22px 26px;
    border-radius: var(--radius-lg, 12px);
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}
.rpt-filter-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color, #6366f1), #8b5cf6, #ec4899, #f59e0b, #10b981);
    border-radius: var(--radius-lg, 12px) var(--radius-lg, 12px) 0 0;
}
.rpt-filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.rpt-filter-bar .filter-group label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    opacity: 0.6;
}
.rpt-filter-bar .filter-group select,
.rpt-filter-bar .filter-group input[type="date"] {
    padding: 9px 14px;
    border-radius: var(--radius-md, 8px);
    border: 1px solid var(--surface-border, rgba(255,255,255,0.12));
    background: var(--hover-bg, rgba(255,255,255,0.06));
    color: inherit;
    font-size: 0.84rem;
    font-family: inherit;
    min-width: 148px;
    transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
}
.rpt-filter-bar .filter-group select:focus,
.rpt-filter-bar .filter-group input[type="date"]:focus {
    outline: none;
    border-color: var(--primary-color, #6366f1);
    box-shadow: var(--focus-ring, 0 0 0 3px rgba(99, 102, 241, 0.2));
    background: rgba(var(--primary-rgb, 99, 102, 241), 0.04);
}
.rpt-filter-bar .filter-actions {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}
.rpt-btn-apply {
    padding: 9px 22px;
    border-radius: var(--radius-md, 8px);
    border: none;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s;
    font-family: inherit;
    background: linear-gradient(135deg, var(--primary-color, #6366f1), #8b5cf6);
    color: #fff;
    box-shadow: 0 2px 10px rgba(var(--primary-rgb, 99, 102, 241), 0.25);
}
.rpt-btn-apply:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 18px rgba(var(--primary-rgb, 99, 102, 241), 0.35);
}
.rpt-btn-reset {
    padding: 9px 18px;
    border-radius: var(--radius-md, 8px);
    border: 1px solid var(--surface-border, rgba(255,255,255,0.12));
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    font-family: inherit;
    background: transparent;
    color: inherit;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.rpt-btn-reset:hover {
    background: var(--hover-bg, rgba(255,255,255,0.08));
    border-color: var(--primary-color, #6366f1);
    color: var(--primary-color, #6366f1);
}
.date-custom-fields { display: none; }
.date-custom-fields.visible { display: flex; gap: 14px; }

/* ── Active Filter Tags ──────────────────────────────── */
.rpt-filter-tags {
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.rpt-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: var(--radius-full, 9999px);
    font-size: 0.74rem;
    font-weight: 600;
    background: rgba(var(--primary-rgb, 99, 102, 241), 0.1);
    color: var(--primary-color, #6366f1);
    border: 1px solid rgba(var(--primary-rgb, 99, 102, 241), 0.2);
    transition: all 0.2s;
}
.rpt-filter-tag:hover {
    background: rgba(var(--primary-rgb, 99, 102, 241), 0.18);
}
.rpt-filter-tag i {
    font-size: 0.68rem;
    opacity: 0.7;
}

/* ── KPI Cards Grid ──────────────────────────────────── */
.rpt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
@media (max-width: 1200px) {
    .rpt-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
    .rpt-kpi-grid { grid-template-columns: 1fr; }
}

.rpt-kpi-card {
    position: relative;
    border-radius: var(--radius-lg, 12px);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--card-border, rgba(255,255,255,0.08));
    border-left: 4px solid var(--card-border);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: all 0.3s ease;
    backdrop-filter: var(--surface-blur, none);
}
.rpt-kpi-card::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    opacity: 0.04;
    transform: translate(30px, -30px);
    pointer-events: none;
}
.rpt-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-hover-shadow, 0 12px 30px rgba(0,0,0,0.15));
}
.rpt-kpi-card.kpi-revenue   { border-left-color: #3b82f6; }
.rpt-kpi-card.kpi-revenue::after   { background: #3b82f6; }
.rpt-kpi-card.kpi-revenue:hover    { border-left-color: #3b82f6; }
.rpt-kpi-card.kpi-profit   { border-left-color: #14b8a6; }
.rpt-kpi-card.kpi-profit::after   { background: #14b8a6; }
.rpt-kpi-card.kpi-profit:hover    { border-left-color: #14b8a6; }
.rpt-kpi-card.kpi-margin   { border-left-color: #84cc16; }
.rpt-kpi-card.kpi-margin::after   { background: #84cc16; }
.rpt-kpi-card.kpi-margin:hover    { border-left-color: #84cc16; }
.rpt-kpi-card.kpi-assets    { border-left-color: #10b981; }
.rpt-kpi-card.kpi-assets::after    { background: #10b981; }
.rpt-kpi-card.kpi-assets:hover     { border-left-color: #10b981; }
.rpt-kpi-card.kpi-customers { border-left-color: #f59e0b; }
.rpt-kpi-card.kpi-customers::after { background: #f59e0b; }
.rpt-kpi-card.kpi-customers:hover  { border-left-color: #f59e0b; }
.rpt-kpi-card.kpi-invoices  { border-left-color: #ec4899; }
.rpt-kpi-card.kpi-invoices::after  { background: #ec4899; }
.rpt-kpi-card.kpi-invoices:hover   { border-left-color: #ec4899; }
.rpt-kpi-card.kpi-aov       { border-left-color: #8b5cf6; }
.rpt-kpi-card.kpi-aov::after       { background: #8b5cf6; }
.rpt-kpi-card.kpi-aov:hover        { border-left-color: #8b5cf6; }
.rpt-kpi-card.kpi-oos       { border-left-color: #ef4444; }
.rpt-kpi-card.kpi-oos::after       { background: #ef4444; }
.rpt-kpi-card.kpi-oos:hover        { border-left-color: #ef4444; }

.rpt-kpi-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-lg, 12px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    color: #fff;
    position: relative;
    z-index: 1;
}
.rpt-kpi-icon.icon-revenue   { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.35); }
.rpt-kpi-icon.icon-profit    { background: linear-gradient(135deg, #14b8a6, #0f766e); box-shadow: 0 4px 14px rgba(20,184,166,0.35); }
.rpt-kpi-icon.icon-margin    { background: linear-gradient(135deg, #84cc16, #4d7c0f); box-shadow: 0 4px 14px rgba(132,204,22,0.35); }
.rpt-kpi-icon.icon-assets    { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
.rpt-kpi-icon.icon-customers { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 14px rgba(245,158,11,0.35); }
.rpt-kpi-icon.icon-invoices  { background: linear-gradient(135deg, #ec4899, #db2777); box-shadow: 0 4px 14px rgba(236,72,153,0.35); }
.rpt-kpi-icon.icon-aov       { background: linear-gradient(135deg, #8b5cf6, #7c3aed); box-shadow: 0 4px 14px rgba(139,92,246,0.35); }
.rpt-kpi-icon.icon-oos       { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 14px rgba(239,68,68,0.35); }

.rpt-kpi-info {
    flex: 1;
    min-width: 0;
    position: relative;
    z-index: 1;
}
.rpt-kpi-value {
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.5px;
    color: var(--text-color, #f8fafc);
    animation: rpt-countUp 0.6s ease-out both;
}
.rpt-kpi-label {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted, #94a3b8) !important;
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── Charts Grid ─────────────────────────────────────── */
.rpt-charts-grid {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 22px;
    margin-bottom: 22px;
}
.rpt-charts-grid-even {
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: 22px;
    margin-bottom: 28px;
}
@media (max-width: 1100px) {
    .rpt-charts-grid,
    .rpt-charts-grid-even { grid-template-columns: 1fr; }
}

.rpt-chart-card {
    border-radius: var(--radius-lg, 12px);
    padding: 24px;
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--card-border, rgba(255,255,255,0.08));
    border-left: 4px solid var(--card-border);
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    backdrop-filter: var(--surface-blur, none);
    display: flex;
    flex-direction: column;
}
.rpt-chart-card:hover {
    box-shadow: var(--card-hover-shadow, 0 8px 25px rgba(0,0,0,0.12));
    border-left-color: var(--primary-color, #6366f1);
}
.rpt-chart-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--surface-border, rgba(255,255,255,0.08));
}
.rpt-chart-header-icon {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-md, 8px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.rpt-chart-header-icon.icon-trend    { background: rgba(59,130,246,0.12); color: #3b82f6; }
.rpt-chart-header-icon.icon-category { background: rgba(16,185,129,0.12); color: #10b981; }
.rpt-chart-header-icon.icon-leader   { background: rgba(245,158,11,0.12); color: #f59e0b; }
.rpt-chart-header-icon.icon-products { background: rgba(139,92,246,0.12); color: #8b5cf6; }

.rpt-chart-title {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.2px;
}
.rpt-chart-body {
    flex: 1;
    position: relative;
    min-height: 280px;
}
.rpt-chart-body-centered {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    min-height: 280px;
}

/* ── Table Section ───────────────────────────────────── */
.rpt-tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
}
@media (max-width: 1000px) {
    .rpt-tables-grid { grid-template-columns: 1fr; }
}

.rpt-table-card {
    border-radius: var(--radius-lg, 12px);
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--card-border, rgba(255,255,255,0.08));
    border-left: 4px solid var(--card-border);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: all 0.3s ease;
    backdrop-filter: var(--surface-blur, none);
}
.rpt-table-card:hover {
    box-shadow: var(--card-hover-shadow, 0 8px 25px rgba(0,0,0,0.12));
    border-left-color: var(--primary-color, #6366f1);
}
.rpt-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--surface-border, rgba(255,255,255,0.06));
}
.rpt-table-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rpt-table-icon {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-md, 8px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.rpt-table-icon.icon-top    { background: rgba(245,158,11,0.12); color: #f59e0b; }
.rpt-table-icon.icon-alert  { background: rgba(239,68,68,0.12); color: #ef4444; }

.rpt-table-title {
    font-size: 1rem;
    font-weight: 700;
}
.rpt-btn-csv {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-radius: var(--radius-md, 8px);
    border: 1px solid var(--surface-border, rgba(255,255,255,0.12));
    background: transparent;
    color: var(--text-muted, #94a3b8);
    cursor: pointer;
    transition: all 0.25s;
    font-family: inherit;
    text-transform: uppercase;
}
.rpt-btn-csv:hover {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.4);
    color: #10b981;
    transform: translateY(-1px);
}
.rpt-table-body {
    padding: 0;
}
.rpt-table-body .table {
    margin-bottom: 0;
}

/* Rank badges for top products */
.rpt-rank-badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 800;
    flex-shrink: 0;
}
.rpt-rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 2px 8px rgba(245,158,11,0.35); }
.rpt-rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
.rpt-rank-3 { background: linear-gradient(135deg, #a78bfa, #8b5cf6); color: #fff; }
.rpt-rank-4, .rpt-rank-5 { background: var(--hover-bg, rgba(255,255,255,0.06)); color: var(--text-muted, #94a3b8); border: 1px solid var(--surface-border, rgba(255,255,255,0.08)); }

.rpt-product-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rpt-product-img {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md, 8px);
    object-fit: cover;
    border: 1px solid var(--surface-border, rgba(255,255,255,0.08));
}
.rpt-product-placeholder {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md, 8px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--hover-bg, rgba(255,255,255,0.04));
    color: var(--text-muted, #94a3b8);
    font-size: 0.85rem;
}

/* Stock severity bar */
.rpt-stock-bar {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rpt-stock-indicator {
    height: 6px;
    border-radius: 3px;
    flex: 1;
    max-width: 50px;
    background: var(--hover-bg, rgba(255,255,255,0.06));
    overflow: hidden;
}
.rpt-stock-indicator-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease-out;
}
.rpt-stock-critical .rpt-stock-indicator-fill { background: #ef4444; }
.rpt-stock-warning  .rpt-stock-indicator-fill { background: #f59e0b; }
.rpt-stock-ok       .rpt-stock-indicator-fill { background: #10b981; }

/* ── Empty State ─────────────────────────────────────── */
.rpt-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted, #94a3b8);
    font-size: 0.9rem;
}
.rpt-empty-state i {
    display: block;
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.4;
}

/* ── Print Stylesheet ────────────────────────────────── */
@media print {
    .sidebar, .topbar, .rpt-filter-bar, .rpt-btn-csv,
    .btn-print-report, .rpt-filter-tags, .no-print { display: none !important; }
    .layout-wrapper { margin-left: 0 !important; padding: 0 !important; }
    .dashboard-container { padding: 10px !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    body { background: #fff !important; color: #111 !important; -webkit-text-fill-color: #111 !important; }
    .rpt-page-header h1 { -webkit-text-fill-color: #111 !important; background: none !important; }
    .rpt-kpi-card, .rpt-chart-card, .rpt-table-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        border-left: 4px solid #ddd !important;
        page-break-inside: avoid;
        backdrop-filter: none !important;
    }
    .rpt-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .rpt-charts-grid, .rpt-charts-grid-even { grid-template-columns: 1fr 1fr; }
    canvas { max-height: 220px !important; }
    .rpt-kpi-icon { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .rpt-rank-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="dashboard-container">

    <!-- ════════════ Page Header ════════════ -->
    <div class="rpt-page-header rpt-animate">
        <div>
            <h1>Executive Analytics & Reports</h1>
            <p class="rpt-subtitle">Real-time performance metrics and inventory insights</p>
        </div>
        <div class="rpt-header-actions no-print">
            <button onclick="window.print()" class="btn-print-report">
                <i class="fas fa-print"></i> Export / Print
            </button>
        </div>
    </div>


    <!-- ════════════ Filter Toolbar ════════════ -->
    <form method="GET" action="<?php echo URL_ROOT; ?>/admin/reports" class="card rpt-filter-bar rpt-animate rpt-animate-delay-1 no-print" id="reportFilterForm">
        <div class="filter-group">
            <label><i class="fas fa-calendar-alt"></i> Date Range</label>
            <select name="preset" id="filterPreset" onchange="toggleCustomDates()">
                <option value="today" <?php echo ($filter_preset === 'today') ? 'selected' : ''; ?>>Today</option>
                <option value="yesterday" <?php echo ($filter_preset === 'yesterday') ? 'selected' : ''; ?>>Yesterday</option>
                <option value="7days" <?php echo ($filter_preset === '7days') ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="30days" <?php echo ($filter_preset === '30days') ? 'selected' : ''; ?>>Last 30 Days</option>
                <!-- Note: default is 'all' when no preset param is in the URL -->
                <option value="this_month" <?php echo ($filter_preset === 'this_month') ? 'selected' : ''; ?>>This Month</option>
                <option value="6months" <?php echo ($filter_preset === '6months') ? 'selected' : ''; ?>>Last 6 Months</option>
                <option value="all" <?php echo ($filter_preset === 'all') ? 'selected' : ''; ?>>All Time</option>
                <option value="custom" <?php echo ($filter_preset === 'custom') ? 'selected' : ''; ?>>Custom Range</option>
            </select>
        </div>

        <div class="date-custom-fields <?php echo ($filter_preset === 'custom') ? 'visible' : ''; ?>" id="customDateFields">
            <div class="filter-group">
                <label>From</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="filter-group">
                <label>To</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-user-tie"></i> Salesman</label>
            <select name="salesman_id">
                <option value="all">All Salesmen</option>
                <?php foreach ($salesmen as $sm): ?>
                    <option value="<?php echo $sm['id']; ?>" <?php echo ($salesman_id == $sm['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sm['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-tag"></i> Category</label>
            <select name="category_id">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="rpt-btn-apply"><i class="fas fa-search"></i> Apply</button>
            <a href="<?php echo URL_ROOT; ?>/admin/reports" class="rpt-btn-reset"><i class="fas fa-undo"></i> Reset</a>
        </div>
    </form>

    <!-- Active filter tags -->
    <?php if ($filter_preset !== 'all' || $salesman_id !== 'all' || $category_id !== 'all'): ?>
    <div class="rpt-filter-tags rpt-animate rpt-animate-delay-2 no-print">
        <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-right: 4px"><i class="fas fa-sliders-h"></i> Active:</span>
        <?php
            $presetLabels = [
                'today' => 'Today', 'yesterday' => 'Yesterday', '7days' => 'Last 7 Days',
                '30days' => 'Last 30 Days', 'this_month' => 'This Month', '6months' => 'Last 6 Months',
                'all' => 'All Time', 'custom' => 'Custom Range'
            ];
        ?>
        <span class="rpt-filter-tag"><i class="fas fa-calendar-day"></i> <?php echo $presetLabels[$filter_preset] ?? $filter_preset; ?></span>
        <?php if ($start_date !== '' && $end_date !== ''): ?>
            <span class="rpt-filter-tag"><i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($start_date); ?> → <?php echo htmlspecialchars($end_date); ?></span>
        <?php endif; ?>
        <?php if ($salesman_id !== 'all'): ?>
            <?php foreach ($salesmen as $sm): if ($sm['id'] == $salesman_id): ?>
                <span class="rpt-filter-tag"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($sm['name']); ?></span>
            <?php endif; endforeach; ?>
        <?php endif; ?>
        <?php if ($category_id !== 'all'): ?>
            <?php foreach ($categories as $cat): if ($cat['id'] == $category_id): ?>
                <span class="rpt-filter-tag"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat['name']); ?></span>
            <?php endif; endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>


    <!-- ════════════ KPI Summary Grid (6 cards) ════════════ -->
    <div class="rpt-kpi-grid">
        <div class="rpt-kpi-card kpi-revenue rpt-animate rpt-animate-delay-1">
            <div class="rpt-kpi-icon icon-revenue"><i class="fas fa-chart-line"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo format_price($totalRevenue); ?></div>
                <div class="rpt-kpi-label">Total Revenue</div>
            </div>
        </div>

        <div class="rpt-kpi-card kpi-profit rpt-animate rpt-animate-delay-1">
            <div class="rpt-kpi-icon icon-profit"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo format_price($totalProfit); ?></div>
                <div class="rpt-kpi-label">Total Profit</div>
            </div>
        </div>

        <div class="rpt-kpi-card kpi-assets rpt-animate rpt-animate-delay-2">
            <div class="rpt-kpi-icon icon-assets"><i class="fas fa-vault"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo format_price($totalInventoryValue); ?></div>
                <div class="rpt-kpi-label">Asset Valuation</div>
            </div>
        </div>

        <div class="rpt-kpi-card kpi-invoices rpt-animate rpt-animate-delay-4">
            <div class="rpt-kpi-icon icon-invoices"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo number_format($totalInvoices); ?></div>
                <div class="rpt-kpi-label">Total Invoices</div>
            </div>
        </div>

        <div class="rpt-kpi-card kpi-aov rpt-animate rpt-animate-delay-5">
            <div class="rpt-kpi-icon icon-aov"><i class="fas fa-receipt"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo format_price($averageOrderValue); ?></div>
                <div class="rpt-kpi-label">Avg Order Value</div>
            </div>
        </div>

        <div class="rpt-kpi-card kpi-oos rpt-animate rpt-animate-delay-6">
            <div class="rpt-kpi-icon icon-oos"><i class="fas fa-box-open"></i></div>
            <div class="rpt-kpi-info">
                <div class="rpt-kpi-value"><?php echo number_format($outOfStockCount); ?></div>
                <div class="rpt-kpi-label">Out of Stock</div>
            </div>
        </div>
    </div>


    <!-- ════════════ Charts Row 1: Revenue Trend + Category Breakdown ════════════ -->
    <div class="rpt-charts-grid rpt-animate rpt-animate-delay-3">
        <!-- Revenue Trend (Line) -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-trend"><i class="fas fa-chart-area"></i></div>
                <h3 class="rpt-chart-title">Revenue Trend</h3>
            </div>
            <div class="rpt-chart-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Sales by Category (Doughnut) -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-category"><i class="fas fa-chart-pie"></i></div>
                <h3 class="rpt-chart-title">Sales by Category</h3>
            </div>
            <div class="rpt-chart-body-centered">
                <canvas id="salesByCategoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ════════════ Charts Row 2: Salesman Leaderboard + Top Products ════════════ -->
    <div class="rpt-charts-grid-even rpt-animate rpt-animate-delay-4">
        <!-- Top Product Share (Doughnut) -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-products"><i class="fas fa-trophy"></i></div>
                <h3 class="rpt-chart-title">Top Product Share</h3>
            </div>
            <div class="rpt-chart-body-centered">
                <canvas id="topProductsPie"></canvas>
            </div>
        </div>

        <!-- Salesman Leaderboard (Horizontal Bar) -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-leader"><i class="fas fa-ranking-star"></i></div>
                <h3 class="rpt-chart-title">Salesman Leaderboard</h3>
            </div>
            <div class="rpt-chart-body">
                <canvas id="salesmanLeaderboardChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ════════════ Charts Row 3: Pharmacy Dimensions ════════════ -->
    <div class="rpt-charts-grid rpt-animate rpt-animate-delay-5">
        <!-- Sales by Generic -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-category" style="background: rgba(168,85,247,0.12); color: #a855f7;"><i class="fas fa-capsules"></i></div>
                <h3 class="rpt-chart-title">Sales by Generic</h3>
            </div>
            <div class="rpt-chart-body">
                <canvas id="salesByGenericChart"></canvas>
            </div>
        </div>

        <!-- Sales by Company -->
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon icon-category" style="background: rgba(236,72,153,0.12); color: #ec4899;"><i class="fas fa-industry"></i></div>
                <h3 class="rpt-chart-title">Sales by Manufacturer</h3>
            </div>
            <div class="rpt-chart-body">
                <canvas id="salesByCompanyChart"></canvas>
            </div>
        </div>
    </div>


        <!-- ════════════ Advanced Reports Modal/Section ════════════ -->
    <div class="rpt-charts-grid rpt-animate rpt-animate-delay-5" style="grid-template-columns: 1fr; margin-bottom: 28px;">
        <div class="rpt-chart-card">
            <div class="rpt-chart-header">
                <div class="rpt-chart-header-icon" style="background: rgba(99,102,241,0.12); color: #6366f1;"><i class="fas fa-file-invoice"></i></div>
                <h3 class="rpt-chart-title">Generate Advanced Exportable Reports</h3>
            </div>
            <div class="rpt-chart-body" style="min-height: auto; padding-top: 10px;">
                <form method="POST" action="<?php echo URL_ROOT; ?>/admin/reports/generate" target="_blank" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <div class="filter-group" style="flex: 1; min-width: 200px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Report Category</label>
                        <select name="report_category" id="report_category" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                            <option value="">Select Category...</option>
                            <option value="medicine">Medicine Reports</option>
                            <option value="receive">Receive/Purchase Reports</option>
                            <option value="sale">Sales Reports</option>
                            <option value="stock">Stock Reports</option>
                        </select>
                    </div>

                    <div class="filter-group" style="flex: 1; min-width: 200px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Report Type</label>
                        <select name="report_type" id="report_type" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                            <option value="">First Select Category</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" style="flex: 1; min-width: 140px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Export Format</label>
                        <select name="export_format" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                            <option value="html">HTML (View)</option>
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel Spreadsheet</option>
                            <option value="word">Word Document</option>
                            <option value="csv">CSV File</option>
                        </select>
                    </div>

                    <div class="filter-group" style="flex: 1; min-width: 130px; display: none;" id="adv_date_start">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Start Date</label>
                        <input type="date" name="start_date" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                    </div>

                    <div class="filter-group" style="flex: 1; min-width: 130px; display: none;" id="adv_date_end">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">End Date</label>
                        <input type="date" name="end_date" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                    </div>

                    <div class="filter-group" style="flex: 1; min-width: 140px;" id="adv_salesman_grp">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Salesman Filter</label>
                        <select name="salesman_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                            <option value="all">All Salesmen</option>
                            <?php foreach ($salesmen as $sm): ?>
                                <option value="<?php echo $sm['id']; ?>"><?php echo htmlspecialchars($sm['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group" style="flex: 1; min-width: 140px;" id="adv_category_grp">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; display: block;">Category Filter</label>
                        <select name="category_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--surface-border); background: var(--surface-color); color: inherit;">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 8px; border: none; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; font-weight: 700; cursor: pointer; height: 42px;">
                            <i class="fas fa-magic"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ════════════ Tables Section ════════════ -->
    <div class="rpt-tables-grid rpt-animate rpt-animate-delay-5">
        <!-- Top 5 Products Table -->
        <div class="rpt-table-card">
            <div class="rpt-table-header">
                <div class="rpt-table-title-group">
                    <div class="rpt-table-icon icon-top"><i class="fas fa-star"></i></div>
                    <h3 class="rpt-table-title">Top Selling Products</h3>
                </div>
                <button class="rpt-btn-csv no-print" onclick="exportCSV('topProducts')"><i class="fas fa-download"></i> CSV</button>
            </div>
            <div class="rpt-table-body">
                <div class="table-container">
                    <table class="table" id="topProductsTable">
                        <thead>
                            <tr>
                                <th style="width: 40px">#</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="text-align: center">Sold</th>
                                <th style="text-align: right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topProducts)): ?>
                                <?php foreach ($topProducts as $idx => $tp): ?>
                                    <tr>
                                        <td><span class="rpt-rank-badge rpt-rank-<?php echo $idx + 1; ?>"><?php echo $idx + 1; ?></span></td>
                                        <td>
                                            <div class="rpt-product-cell">
                                                <?php if (!empty($tp['image'])): ?>
                                                    <img src="<?php echo url($tp['image']); ?>" alt="" class="rpt-product-img">
                                                <?php else: ?>
                                                    <div class="rpt-product-placeholder"><i class="fas fa-box"></i></div>
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($tp['name']); ?></strong>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-warning"><?php echo htmlspecialchars($tp['category_name'] ?? 'General'); ?></span></td>
                                        <td style="text-align: center"><span class="badge badge-success" style="font-size: 0.82rem"><?php echo number_format($tp['total_qty']); ?></span></td>
                                        <td style="text-align: right; font-weight: 700"><?php echo format_price($tp['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5"><div class="rpt-empty-state"><i class="fas fa-chart-bar"></i> No sales data available for this period.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Risk Table -->
        <div class="rpt-table-card">
            <div class="rpt-table-header">
                <div class="rpt-table-title-group">
                    <div class="rpt-table-icon icon-alert"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3 class="rpt-table-title">Inventory Alerts</h3>
                </div>
                <button class="rpt-btn-csv no-print" onclick="exportCSV('lowStock')"><i class="fas fa-download"></i> CSV</button>
            </div>
            <div class="rpt-table-body">
                <div class="table-container">
                    <table class="table" id="lowStockTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th style="text-align: center">Stock Level</th>
                                <th style="text-align: right">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lowStockProducts)): ?>
                                <?php
                                    $minStock = $minStock ?? 10;
                                    foreach ($lowStockProducts as $lsp):
                                        $stockPct = $minStock > 0 ? min(100, ($lsp['quantity'] / $minStock) * 100) : 0;
                                        $severityClass = $lsp['quantity'] == 0 ? 'rpt-stock-critical' : ($stockPct < 50 ? 'rpt-stock-warning' : 'rpt-stock-ok');
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($lsp['name']); ?></strong></td>
                                        <td><span class="badge badge-warning"><?php echo htmlspecialchars($lsp['category_name'] ?? 'General'); ?></span></td>
                                        <td style="text-align: center">
                                            <div class="rpt-stock-bar <?php echo $severityClass; ?>">
                                                <span class="badge badge-danger" style="font-size: 0.82rem; font-weight: 700; padding: 3px 10px; min-width: 42px">
                                                    <?php echo $lsp['quantity']; ?>
                                                </span>
                                                <div class="rpt-stock-indicator">
                                                    <div class="rpt-stock-indicator-fill" style="width: <?php echo $stockPct; ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: right; font-weight: 700"><?php echo format_price($lsp['price']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4"><div class="rpt-empty-state"><i class="fas fa-check-circle" style="color: #10b981"></i> All inventory levels are optimal.</div></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Hidden data for CSV export -->
<script id="csvTopProductsData" type="application/json"><?php echo json_encode($allFilteredTopProducts ?? []); ?></script>
<script id="csvLowStockData" type="application/json"><?php echo json_encode($allFilteredLowStockProducts ?? []); ?></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ── Theme-adaptive color detection ──────────────
    const bodyStyles = getComputedStyle(document.body);
    const isDark = document.body.classList.contains('theme-dark') ||
                   document.body.classList.contains('theme-glass') ||
                   (bodyStyles.backgroundColor.match(/^rgb\((\d+)/) || [])[1] < 100;

    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tickColor = isDark ? '#94a3b8' : '#64748b';
    const legendColor = isDark ? '#e2e8f0' : '#334155';
    const borderBg = isDark ? 'rgba(0,0,0,0.3)' : '#ffffff';
    const tooltipBg = isDark ? 'rgba(15,23,42,0.92)' : 'rgba(255,255,255,0.95)';
    const tooltipText = isDark ? '#e2e8f0' : '#1e293b';
    const tooltipBorder = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

    const chartPalette = [
        '#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899',
        '#06b6d4','#f43f5e','#84cc16','#14b8a6','#a855f7'
    ];

    // Shared tooltip config
    const tooltipConfig = {
        backgroundColor: tooltipBg,
        titleColor: tooltipText,
        bodyColor: tooltipText,
        borderColor: tooltipBorder,
        borderWidth: 1,
        padding: 12,
        cornerRadius: 8,
        titleFont: { weight: '700', size: 13 },
        bodyFont: { size: 12 },
        displayColors: true,
        boxPadding: 4
    };

    // ── 1. Revenue Trend Line Chart ─────────────────
    const trendData = <?php echo json_encode($salesTrend ?? []); ?>;
    const trendLabels = trendData.map(item => {
        const parts = item.time_label.split('-');
        if (parts.length === 3) {
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            return d.toLocaleDateString('default', { month: 'short', day: 'numeric' });
        }
        const d = new Date(parts[0], parts[1] - 1);
        return d.toLocaleString('default', { month: 'short', year: 'numeric' });
    });
    const trendRevenues = trendData.map(item => parseFloat(item.revenue));

    const trendProfits = trendData.map(item => parseFloat(item.profit || 0));

    const revenueGradient = document.getElementById('revenueChart').getContext('2d');
    const grad = revenueGradient.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
    grad.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

    const profitGradient = revenueGradient.createLinearGradient(0, 0, 0, 300);
    profitGradient.addColorStop(0, 'rgba(20, 184, 166, 0.25)');
    profitGradient.addColorStop(1, 'rgba(20, 184, 166, 0.02)');

    new Chart(revenueGradient, {
        type: 'line',
        data: {
            labels: trendLabels.length > 0 ? trendLabels : ['No Data'],
            datasets: [
                {
                    label: 'Revenue (<?= currency_symbol() ?>)',
                    data: trendRevenues.length > 0 ? trendRevenues : [0],
                    borderColor: '#3b82f6',
                    backgroundColor: grad,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: borderBg,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 3
                },
                {
                    label: 'Profit (<?= currency_symbol() ?>)',
                    data: trendProfits.length > 0 ? trendProfits : [0],
                    borderColor: '#14b8a6',
                    backgroundColor: profitGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#14b8a6',
                    pointBorderColor: borderBg,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBorderWidth: 3
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: tooltipConfig
            },
            scales: {
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '<?= currency_symbol() ?>' + v.toLocaleString() },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 11 }, maxRotation: 45 },
                    border: { display: false }
                }
            }
        }
    });

    // ── 2. Sales by Category Doughnut ────────────────
    const catData = <?php echo json_encode($salesByCategory ?? []); ?>;
    const catNames = catData.map(c => c.category_name);
    const catRevs = catData.map(c => parseFloat(c.total_revenue));

    new Chart(document.getElementById('salesByCategoryChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: catNames.length > 0 ? catNames : ['No Data'],
            datasets: [{
                data: catRevs.length > 0 ? catRevs : [1],
                backgroundColor: chartPalette.slice(0, Math.max(catRevs.length, 1)),
                borderWidth: 3,
                borderColor: borderBg,
                hoverBorderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: legendColor, boxWidth: 12, padding: 14, font: { size: 12, weight: '500' }, usePointStyle: true, pointStyle: 'circle' }
                },
                tooltip: { ...tooltipConfig, callbacks: { label: ctx => ' ' + ctx.label + ': <?= currency_symbol() ?>' + ctx.parsed.toLocaleString() } }
            },
            cutout: '68%'
        }
    });

    // ── 3. Salesman Leaderboard Horizontal Bar ──────
    const lbData = <?php echo json_encode($salesmanLeaderboard ?? []); ?>;
    const lbNames = lbData.map(s => s.salesman_name);
    const lbRevs = lbData.map(s => parseFloat(s.total_revenue));

    new Chart(document.getElementById('salesmanLeaderboardChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: lbNames.length > 0 ? lbNames : ['No Data'],
            datasets: [{
                label: 'Revenue (<?= currency_symbol() ?>)',
                data: lbRevs.length > 0 ? lbRevs : [0],
                backgroundColor: chartPalette.slice(0, Math.max(lbRevs.length, 1)),
                borderWidth: 0,
                borderRadius: 8,
                barPercentage: 0.55,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipConfig, callbacks: { label: ctx => ' Revenue: <?= currency_symbol() ?>' + ctx.parsed.x.toLocaleString() } }
            },
            scales: {
                x: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '<?= currency_symbol() ?>' + v.toLocaleString() },
                    border: { display: false }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 12, weight: '600' } },
                    border: { display: false }
                }
            }
        }
    });

    // ── 4. Top Products Doughnut ─────────────────────
    const topProducts = <?php echo json_encode($topProducts ?? []); ?>;
    const tpNames = topProducts.map(tp => tp.name);
    const tpQtys = topProducts.map(tp => parseInt(tp.total_qty));

    new Chart(document.getElementById('topProductsPie').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: tpNames.length > 0 ? tpNames : ['No Data'],
            datasets: [{
                data: tpQtys.length > 0 ? tpQtys : [1],
                backgroundColor: chartPalette.slice(0, Math.max(tpQtys.length, 1)),
                borderWidth: 3,
                borderColor: borderBg,
                hoverBorderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: legendColor, boxWidth: 12, padding: 14, font: { size: 12, weight: '500' }, usePointStyle: true, pointStyle: 'circle' }
                },
                tooltip: { ...tooltipConfig, callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' units' } }
            },
            cutout: '68%'
        }
    });
    // ── 5. Sales by Generic Bar ─────────────────────
    const genData = <?php echo json_encode($salesByGeneric ?? []); ?>;
    const genNames = genData.map(g => g.generic_name || 'Unknown');
    const genRevs = genData.map(g => parseFloat(g.total_revenue));

    new Chart(document.getElementById('salesByGenericChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: genNames.length > 0 ? genNames : ['No Data'],
            datasets: [{
                label: 'Revenue (<?= currency_symbol() ?>)',
                data: genRevs.length > 0 ? genRevs : [0],
                backgroundColor: chartPalette.slice(0, Math.max(genRevs.length, 1)),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipConfig, callbacks: { label: ctx => ' Revenue: <?= currency_symbol() ?>' + ctx.parsed.y.toLocaleString() } }
            },
            scales: {
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '<?= currency_symbol() ?>' + v.toLocaleString() },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 10 } },
                    border: { display: false }
                }
            }
        }
    });

    // ── 6. Sales by Company Bar ─────────────────────
    const compData = <?php echo json_encode($salesByCompany ?? []); ?>;
    const compNames = compData.map(c => c.company_name || 'Unknown');
    const compRevs = compData.map(c => parseFloat(c.total_revenue));

    new Chart(document.getElementById('salesByCompanyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: compNames.length > 0 ? compNames : ['No Data'],
            datasets: [{
                label: 'Revenue (<?= currency_symbol() ?>)',
                data: compRevs.length > 0 ? compRevs : [0],
                backgroundColor: chartPalette.slice(0, Math.max(compRevs.length, 1)).reverse(),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipConfig, callbacks: { label: ctx => ' Revenue: <?= currency_symbol() ?>' + ctx.parsed.y.toLocaleString() } }
            },
            scales: {
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '<?= currency_symbol() ?>' + v.toLocaleString() },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 10 }, maxRotation: 45 },
                    border: { display: false }
                }
            }
        }
    });

});


// ── Custom Date Toggle ──────────────────────────────
function toggleCustomDates() {
    const preset = document.getElementById('filterPreset').value;
    const fields = document.getElementById('customDateFields');
    fields.classList.toggle('visible', preset === 'custom');
}

// ── CSV Export (Full Filtered Dataset) ───────────────
function exportCSV(type) {
    let rows = [];
    let filename = 'report.csv';

    if (type === 'topProducts') {
        const data = JSON.parse(document.getElementById('csvTopProductsData').textContent);
        rows.push(['Product', 'Category', 'Units Sold', 'Revenue']);
        data.forEach(r => {
            rows.push([
                '"' + (r.name || '').replace(/"/g, '""') + '"',
                '"' + (r.category_name || 'General').replace(/"/g, '""') + '"',
                r.total_qty,
                r.total_revenue
            ]);
        });
        filename = 'top_selling_products.csv';
    } else if (type === 'lowStock') {
        const data = JSON.parse(document.getElementById('csvLowStockData').textContent);
        rows.push(['Product', 'Category', 'Current Stock', 'Unit Price']);
        data.forEach(r => {
            rows.push([
                '"' + (r.name || '').replace(/"/g, '""') + '"',
                '"' + (r.category_name || 'General').replace(/"/g, '""') + '"',
                r.quantity,
                r.price
            ]);
        });
        filename = 'low_stock_alerts.csv';
    }

    const csvContent = rows.map(r => r.join(',')).join('\n');
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const reportOptions = {
        'medicine': [
            {val: 'detailed_info', text: 'Detailed Medicine Information'},
            {val: 'company_wise', text: 'Company-wise Medicine Report'},
            {val: 'category_wise', text: 'Category-wise Medicine Report'},
            {val: 'rate_list', text: 'Up-to-date Medicine Rate List'}
        ],
        'receive': [
            {val: 'complete_register', text: 'Complete Receive Register'},
            {val: 'received_return', text: 'Detailed Received Return Register'}
        ],
        'sale': [
            {val: 'individual_medicine', text: 'Individual Medicine Sale Report'},
            {val: 'invoice_overview', text: 'Invoice-based Sale Overview'},
            {val: 'company_wise_sale', text: 'Company-wise Medicine Sale Report'}
        ],
        'stock': [
            {val: 'overall_overview', text: 'Overall Stock Overview'},
            {val: 'company_wise_stock', text: 'Company-wise Stock Valuation'}
        ]
    };

    const categorySelect = document.getElementById('report_category');
    const typeSelect = document.getElementById('report_type');
    const startDateContainer = document.getElementById('adv_date_start');
    const endDateContainer = document.getElementById('adv_date_end');

    categorySelect.addEventListener('change', function() {
        const cat = this.value;
        typeSelect.innerHTML = '';
        if(cat && reportOptions[cat]) {
            reportOptions[cat].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt.val;
                o.textContent = opt.text;
                typeSelect.appendChild(o);
            });
            // Show date filters for receive, sale, and analytics
            if(cat === 'receive' || cat === 'sale' || cat === 'analytics') {
                startDateContainer.style.display = 'block';
                endDateContainer.style.display = 'block';
            } else {
                startDateContainer.style.display = 'none';
                endDateContainer.style.display = 'none';
            }
        } else {
            const o = document.createElement('option');
            o.value = "";
            o.textContent = "First Select Category";
            typeSelect.appendChild(o);
            startDateContainer.style.display = 'none';
            endDateContainer.style.display = 'none';
        }
    });
});
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
