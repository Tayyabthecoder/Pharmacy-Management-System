<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Report') ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════════
           CSS VARIABLES
        ═══════════════════════════════════════════════ */
        :root {
            --brand:        #2563eb;
            --brand-dark:   #1e3a8a;
            --brand-light:  #dbeafe;
            --accent:       #7c3aed;
            --success:      #059669;
            --success-bg:   #d1fae5;
            --warning:      #d97706;
            --warning-bg:   #fef3c7;
            --danger:       #dc2626;
            --danger-bg:    #fee2e2;
            --neutral:      #6b7280;
            --neutral-bg:   #f3f4f6;
            --surface:      #ffffff;
            --surface-alt:  #f8fafc;
            --border:       #e5e7eb;
            --text-primary: #111827;
            --text-muted:   #6b7280;
            --radius:       10px;
            --shadow-sm:    0 1px 3px rgba(0,0,0,.08);
            --shadow-md:    0 4px 16px rgba(0,0,0,.10);
            --shadow-lg:    0 10px 40px rgba(0,0,0,.12);
        }

        /* ═══════════════════════════════════════════════
           RESET & BASE
        ═══════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ═══════════════════════════════════════════════
           ANIMATIONS
        ═══════════════════════════════════════════════ */
        @keyframes fadeUp   { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes scaleIn  { from { opacity:0; transform:scale(.94); } to { opacity:1; transform:scale(1); } }
        @keyframes shimmer  { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        @keyframes countUp  { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        /* ═══════════════════════════════════════════════
           ACTION BAR (no-print)
        ═══════════════════════════════════════════════ */
        .action-bar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .action-bar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .85rem;
            font-weight: 600;
            color: var(--brand-dark);
        }
        .action-bar-brand i { color: var(--brand); font-size: 1.1rem; }
        .action-bar-right { display: flex; gap: 10px; align-items: center; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            font-size: .83rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s ease;
            line-height: 1;
        }
        .btn-close {
            background: var(--surface);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-close:hover { background: var(--neutral-bg); color: var(--text-primary); }
        .btn-print {
            background: linear-gradient(135deg, var(--brand), #3b82f6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,.3);
        }
        .btn-print:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.4); }

        /* ═══════════════════════════════════════════════
           PAGE WRAPPER
        ═══════════════════════════════════════════════ */
        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px 60px;
            animation: fadeUp .5s ease both;
        }

        /* ═══════════════════════════════════════════════
           HERO HEADER
        ═══════════════════════════════════════════════ */
        .hero {
            background: linear-gradient(135deg, var(--brand-dark) 0%, #1e40af 40%, #312e81 100%);
            border-radius: 16px;
            padding: 36px 40px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            color: #fff;
            box-shadow: var(--shadow-lg);
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 40%;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }
        .hero-left {}
        .hero-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: .75rem;
            font-weight: 500;
            color: rgba(255,255,255,.9);
            margin-bottom: 12px;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .hero-breadcrumb i { font-size: .7rem; opacity: .7; }
        .hero-title {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -.5px;
            margin-bottom: 10px;
        }
        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .8rem;
            color: rgba(255,255,255,.8);
        }
        .hero-meta-item i { font-size: .75rem; opacity: .7; }
        .hero-right {
            text-align: right;
        }
        .hero-company-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .hero-company-sub {
            font-size: .78rem;
            color: rgba(255,255,255,.65);
            margin-top: 4px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: .75rem;
            font-weight: 600;
            color: #fff;
        }
        .hero-badge .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ═══════════════════════════════════════════════
           KPI CARDS
        ═══════════════════════════════════════════════ */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 20px 22px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
            overflow: hidden;
            animation: scaleIn .4s ease both;
            transition: transform .2s, box-shadow .2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .kpi-card.blue::before   { background: linear-gradient(90deg,#2563eb,#3b82f6); }
        .kpi-card.green::before  { background: linear-gradient(90deg,#059669,#34d399); }
        .kpi-card.purple::before { background: linear-gradient(90deg,#7c3aed,#a78bfa); }
        .kpi-card.amber::before  { background: linear-gradient(90deg,#d97706,#fbbf24); }
        .kpi-card.rose::before   { background: linear-gradient(90deg,#e11d48,#fb7185); }
        .kpi-card.teal::before   { background: linear-gradient(90deg,#0891b2,#22d3ee); }

        .kpi-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            margin-bottom: 2px;
        }
        .kpi-card.blue   .kpi-icon { background:#dbeafe; color:#2563eb; }
        .kpi-card.green  .kpi-icon { background:#d1fae5; color:#059669; }
        .kpi-card.purple .kpi-icon { background:#ede9fe; color:#7c3aed; }
        .kpi-card.amber  .kpi-icon { background:#fef3c7; color:#d97706; }
        .kpi-card.rose   .kpi-icon { background:#ffe4e6; color:#e11d48; }
        .kpi-card.teal   .kpi-icon { background:#cffafe; color:#0891b2; }

        .kpi-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
        }
        .kpi-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            animation: countUp .5s ease both;
        }
        .kpi-sub {
            font-size: .72rem;
            color: var(--text-muted);
        }

        /* ═══════════════════════════════════════════════
           TABLE PANEL
        ═══════════════════════════════════════════════ */
        .table-panel {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: fadeUp .5s .12s ease both;
        }
        .table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--surface-alt);
        }
        .table-toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-title {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .row-count-badge {
            background: var(--brand-light);
            color: var(--brand);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .73rem;
            font-weight: 600;
        }
        .search-box {
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: .8rem;
            pointer-events: none;
        }
        .search-box input {
            padding: 8px 12px 8px 32px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: .82rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            background: var(--surface);
            width: 230px;
            transition: border .2s, box-shadow .2s;
            outline: none;
        }
        .search-box input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        .search-box input::placeholder { color: #adb5bd; }

        /* Table scroll wrapper */
        .table-scroll {
            overflow-x: auto;
            max-height: 560px;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .84rem;
        }
        thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        thead tr {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        }
        th {
            padding: 13px 16px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: rgba(255,255,255,.9);
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            transition: background .2s;
            border-right: 1px solid rgba(255,255,255,.08);
        }
        th:last-child { border-right: none; }
        th:hover { background: rgba(255,255,255,.08); }
        th .th-inner {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        th .sort-icon { opacity: .4; font-size: .7rem; transition: opacity .2s, transform .2s; }
        th.sort-asc  .sort-icon,
        th.sort-desc .sort-icon { opacity: 1; color: #93c5fd; }
        th.sort-desc .sort-icon { transform: rotate(180deg); }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover td { background: #eff6ff !important; }
        tbody tr.filtered-out { display: none; }

        td {
            padding: 12px 16px;
            color: var(--text-primary);
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        tbody tr:nth-child(odd)  td { background: var(--surface); }

        td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 500; }
        td.txt { text-align: left; }

        /* Status/badge detection */
        .val-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .73rem;
            font-weight: 600;
        }
        .val-badge.success { background: var(--success-bg); color: var(--success); }
        .val-badge.warning { background: var(--warning-bg); color: var(--warning); }
        .val-badge.danger  { background: var(--danger-bg);  color: var(--danger); }
        .val-badge.neutral { background: var(--neutral-bg); color: var(--neutral); }
        .val-badge .badge-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }

        /* No-results row */
        .no-results-row td {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
            font-size: .9rem;
        }
        .no-results-row i { display:block; font-size:2.5rem; margin-bottom:10px; color:#d1d5db; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 70px 20px;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 3.5rem; color: #d1d5db; margin-bottom: 16px; display: block; }
        .empty-state p { font-size: 1rem; font-weight: 500; }
        .empty-state small { font-size: .82rem; color: #9ca3af; }

        /* Table footer */
        .table-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            background: var(--surface-alt);
            font-size: .78rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 8px;
        }
        .table-footer strong { color: var(--text-primary); }

        /* ═══════════════════════════════════════════════
           PAGE FOOTER
        ═══════════════════════════════════════════════ */
        .page-footer {
            text-align: center;
            margin-top: 32px;
            font-size: .75rem;
            color: var(--text-muted);
        }
        .page-footer a { color: var(--brand); text-decoration: none; }

        /* ═══════════════════════════════════════════════
           PRINT STYLES
        ═══════════════════════════════════════════════ */
        @media print {
            @page { margin: 1.2cm; size: landscape; }

            body { background: white; }
            .action-bar { display: none !important; }
            .table-toolbar .search-box { display: none; }
            .page-wrapper { padding: 0; max-width: none; }

            .hero {
                background: #1e3a8a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border-radius: 6px;
                padding: 24px 28px;
                margin-bottom: 18px;
                box-shadow: none;
            }
            .kpi-card {
                border: 1px solid #e5e7eb;
                box-shadow: none;
                page-break-inside: avoid;
            }
            .kpi-card::before { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table-panel { border-radius: 4px; box-shadow: none; }
            .table-scroll { max-height: none; overflow: visible; }
            thead tr { background: #1e3a8a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { color: white !important; }
            tbody tr.filtered-out { display: none; }
            .table-footer, .page-footer { font-size: 9px; }
        }
    </style>
</head>
<body>

<?php
/* ─── Helpers ──────────────────────────────────────────── */
if (!function_exists('arr_isNumeric')) {
    function arr_isNumeric(string $header): bool {
        $h = strtolower($header);
        return str_contains($h, '(')
            || str_contains($h, 'qty')
            || str_contains($h, 'total')
            || str_contains($h, 'price')
            || str_contains($h, 'stock')
            || str_contains($h, 'amount')
            || str_contains($h, 'profit')
            || str_contains($h, 'value')
            || str_contains($h, 'revenue')
            || str_contains($h, 'cost')
            || (str_contains($h, 'id') && strlen($h) <= 4);
    }
}

if (!function_exists('arr_isDate')) {
    function arr_isDate(string $header): bool {
        $h = strtolower($header);
        return str_contains($h, 'date') || str_contains($h, 'expiry') || str_contains($h, 'created') || str_contains($h, 'time');
    }
}

if (!function_exists('arr_badgeClass')) {
    function arr_badgeClass(string $val): string {
        $v = strtolower(trim($val));
        if (in_array($v, ['active','completed','paid','received','available','yes'])) return 'success';
        if (in_array($v, ['pending','partial','low','warning'])) return 'warning';
        if (in_array($v, ['inactive','return','returned','expired','no','overdue','cancelled'])) return 'danger';
        return '';
    }
}

if (!function_exists('arr_isBadge')) {
    function arr_isBadge(string $header): bool {
        $h = strtolower($header);
        return str_contains($h, 'status') || $h === 'type' || $h === 'remarks';
    }
}

if (!function_exists('arr_formatNum')) {
    function arr_formatNum($val): string {
        if (!is_numeric($val)) return htmlspecialchars((string)$val);
        $n = (float)$val;
        if (floor($n) === $n) return number_format($n, 0);
        return number_format($n, 2);
    }
}

/* ─── Compute KPI cards from data ─────────────────────── */
$kpiCards    = [];
$colorCycle  = ['blue','green','purple','amber','rose','teal'];
$iconMap     = [
    'row'     => ['fa-table-list',   'blue'],
    'revenue' => ['fa-circle-dollar-to-slot','green'],
    'profit'  => ['fa-trending-up',  'green'],
    'qty'     => ['fa-boxes-stacked','purple'],
    'amount'  => ['fa-receipt',      'amber'],
    'stock'   => ['fa-warehouse',    'teal'],
    'value'   => ['fa-coins',        'amber'],
    'invoice' => ['fa-file-invoice', 'purple'],
];

if (!empty($data)) {
    if (!empty($isList) && $isList) {
        $totalRows = 0;
        $totalStockQty = 0;
        $totalCostVal = 0;
        foreach ($data as $grp) {
            foreach ($grp['items'] as $item) {
                $totalRows++;
                $totalStockQty += ($item['Stock Qty'] ?? 0);
                $totalCostVal += ($item['Total Cost Value'] ?? 0);
            }
        }
        $kpiCards[] = [
            'label'  => 'Total Medicines',
            'value'  => number_format($totalRows),
            'sub'    => count($data) . ' groups in report',
            'icon'   => 'fa-table-list',
            'color'  => 'blue',
        ];
        $kpiCards[] = [
            'label'  => 'Total Stock Qty',
            'value'  => number_format($totalStockQty) . ' units',
            'sub'    => 'across all groups',
            'icon'   => 'fa-boxes-stacked',
            'color'  => 'purple',
        ];
        $kpiCards[] = [
            'label'  => 'Total Cost Valuation',
            'value'  => currency_symbol() . ' ' . number_format($totalCostVal, 2),
            'sub'    => 'total inventory value',
            'icon'   => 'fa-coins',
            'color'  => 'teal',
        ];
    } else {
        $headers   = array_keys($data[0]);
        $totalRows = count($data);

        // Always: total rows
        $kpiCards[] = [
            'label'  => 'Total Records',
            'value'  => number_format($totalRows),
            'sub'    => 'rows in this report',
            'icon'   => 'fa-table-list',
            'color'  => 'blue',
        ];

        // Sum numeric columns (max 5 more cards)
        $numColsAdded = 0;
        foreach ($headers as $i => $header) {
            if (!arr_isNumeric($header)) continue;
            if ($numColsAdded >= 4) break;

            $sum = 0;
            foreach ($data as $row) {
                $v = $row[$header] ?? 0;
                if (is_numeric($v)) $sum += (float)$v;
            }
            if ($sum == 0) continue;

            $h    = strtolower($header);
            $color = $colorCycle[($numColsAdded + 1) % count($colorCycle)];
            $icon  = 'fa-chart-bar';
            foreach ($iconMap as $kw => [$ico, $col]) {
                if (str_contains($h, $kw)) { $icon = $ico; $color = $col; break; }
            }

            // Strip trailing (currency) from label for card display
            $label = preg_replace('/\s*\([^)]+\)$/', '', $header);

            $kpiCards[] = [
                'label' => $label,
                'value' => number_format($sum, 2),
                'sub'   => 'total across all rows',
                'icon'  => $icon,
                'color' => $color,
            ];
            $numColsAdded++;
        }
    }
}

/* ─── Page meta ────────────────────────────────────────── */
$generatedAt = date('F j, Y  ·  g:i a');
$category    = ucfirst($_POST['report_category'] ?? 'Report');
$reportType  = $_POST['report_type'] ?? '';
$startDate   = $_POST['start_date'] ?? '';
$endDate     = $_POST['end_date']   ?? '';
$totalRows   = !empty($data) ? count($data) : 0;
?>

<!-- ════════════════ ACTION BAR ════════════════ -->
<div class="action-bar no-print">
    <div class="action-bar-brand">
        <i class="fas fa-file-chart-column"></i>
        Advanced Report Preview
    </div>
    <div class="action-bar-right">
        <button class="btn btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<div class="page-wrapper">

    <!-- ═══════════════ HERO HEADER ═══════════════ -->
    <div class="hero">
        <div class="hero-inner">
            <div class="hero-left">
                <div class="hero-breadcrumb">
                    <i class="fas fa-layer-group"></i>
                    <?= htmlspecialchars($category) ?> Reports
                </div>
                <h1 class="hero-title"><?= htmlspecialchars($title ?? 'Report') ?></h1>
                <div class="hero-meta">
                    <span class="hero-meta-item">
                        <i class="far fa-clock"></i>
                        Generated: <?= $generatedAt ?>
                    </span>
                    <?php if ($startDate): ?>
                    <span class="hero-meta-item">
                        <i class="far fa-calendar-range"></i>
                        Period: <?= htmlspecialchars($startDate) ?> — <?= htmlspecialchars($endDate ?: 'Present') ?>
                    </span>
                    <?php endif; ?>
                    <span class="hero-meta-item">
                        <i class="fas fa-table-rows"></i>
                        <?= number_format($totalRows) ?> records found
                    </span>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-company-name">
                    <i class="fas fa-briefcase-medical" style="margin-right:6px;opacity:.7"></i>
                    PHARMACY SYSTEM
                </div>
                <div class="hero-company-sub">Advanced Reporting Module</div>
                <div class="hero-badge">
                    <span class="dot"></span>
                    Computer-Generated Report
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════ KPI CARDS ═══════════════ -->
    <?php if (!empty($kpiCards)): ?>
    <div class="kpi-grid">
        <?php foreach ($kpiCards as $i => $card): ?>
        <div class="kpi-card <?= $card['color'] ?>" style="animation-delay: <?= $i * 0.07 ?>s">
            <div class="kpi-icon"><i class="fas <?= $card['icon'] ?>"></i></div>
            <div class="kpi-label"><?= htmlspecialchars($card['label']) ?></div>
            <div class="kpi-value"><?= $card['value'] ?></div>
            <div class="kpi-sub"><?= htmlspecialchars($card['sub']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ═══════════════ DATA TABLE ═══════════════ -->
    <div class="table-panel">

        <?php if (!empty($isList) && $isList): ?>
            <!-- List Format Report -->
            <div style="display: flex; flex-direction: column; gap: 24px; margin-top: 20px;">
                <?php foreach ($data as $group): ?>
                    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                        <div style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #ffffff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 10px; color: #ffffff;">
                                <i class="fas fa-layer-group"></i>
                                <?= htmlspecialchars($group['group_title']) ?>
                            </h3>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                <?= count($group['items']) ?> Medicines
                            </span>
                        </div>
                        <div style="padding: 16px 20px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid var(--border); text-transform: uppercase; font-size: 0.75rem; color: var(--text-muted);">
                                        <th style="text-align: left; padding: 10px 12px;">Medicine & Generic</th>
                                        <th style="text-align: left; padding: 10px 12px;"><?= (($groupBy ?? '') === 'Company') ? 'Category' : 'Company' ?></th>
                                        <th style="text-align: center; padding: 10px 12px;">Batch / Expiry</th>
                                        <th style="text-align: right; padding: 10px 12px;">Stock Qty</th>
                                        <th style="text-align: right; padding: 10px 12px;">Cost Price</th>
                                        <th style="text-align: right; padding: 10px 12px;">Retail Price</th>
                                        <th style="text-align: right; padding: 10px 12px;">Total Cost Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grpStock = 0; $grpValue = 0;
                                    foreach ($group['items'] as $item):
                                        $grpStock += $item['Stock Qty'];
                                        $grpValue += $item['Total Cost Value'];
                                    ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 12px;">
                                            <div style="font-weight: 700; color: var(--brand-dark); font-size: 0.92rem;"><?= htmlspecialchars($item['Medicine']) ?></div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;"><?= htmlspecialchars($item['Generic']) ?></div>
                                        </td>
                                        <td style="padding: 12px; font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars((($groupBy ?? '') === 'Company') ? $item['Category'] : $item['Company']) ?></td>
                                        <td style="text-align: center; padding: 12px; font-size: 0.82rem;">
                                            <span style="font-weight: 600;"><?= htmlspecialchars($item['Batch']) ?></span>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($item['Expiry']) ?></div>
                                        </td>
                                        <td style="text-align: right; padding: 12px; font-weight: 700; font-size: 0.9rem;"><?= number_format($item['Stock Qty']) ?></td>
                                        <td style="text-align: right; padding: 12px; font-size: 0.85rem; color: var(--text-secondary);">Rs. <?= number_format($item['Cost Price'], 2) ?></td>
                                        <td style="text-align: right; padding: 12px; font-size: 0.85rem; color: var(--text-secondary);">Rs. <?= number_format($item['Retail Price'], 2) ?></td>
                                        <td style="text-align: right; padding: 12px; font-weight: 700; color: var(--brand); font-size: 0.9rem;">Rs. <?= number_format($item['Total Cost Value'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: var(--surface-alt); font-weight: 700; border-top: 2px solid var(--brand);">
                                        <td colspan="3" style="padding: 12px; color: var(--brand-dark);">Group Subtotal for <?= htmlspecialchars($group['group_name']) ?></td>
                                        <td style="text-align: right; padding: 12px; color: var(--brand);"><?= number_format($grpStock) ?> units</td>
                                        <td colspan="2"></td>
                                        <td style="text-align: right; padding: 12px; color: var(--brand);">Rs. <?= number_format($grpValue, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (empty($data)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No data found for the selected criteria.</p>
                <small>Try adjusting your filters or date range.</small>
            </div>
        <?php else: ?>

        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="table-toolbar-left">
                <span class="table-title"><i class="fas fa-table" style="margin-right:6px;color:var(--brand)"></i> Report Data</span>
                <span class="row-count-badge" id="rowCountBadge">
                    <?= number_format($totalRows) ?> rows
                </span>
            </div>
            <div class="search-box no-print">
                <i class="fas fa-search"></i>
                <input type="text" id="tableSearch" placeholder="Search all columns…" autocomplete="off">
            </div>
        </div>

        <!-- Scrollable Table -->
        <div class="table-scroll">
            <table id="reportTable">
                <thead>
                    <tr>
                        <?php foreach (array_keys($data[0]) as $colIdx => $header): ?>
                        <?php $isNum = arr_isNumeric($header); $isDate = arr_isDate($header); ?>
                        <th
                            data-col="<?= $colIdx ?>"
                            data-numeric="<?= $isNum ? '1' : '0' ?>"
                            style="text-align: <?= $isNum ? 'right' : 'left' ?>"
                        >
                            <span class="th-inner">
                                <?php if ($isDate): ?><i class="far fa-calendar-alt" style="opacity:.6;font-size:.68rem"></i><?php elseif ($isNum): ?><i class="fas fa-hashtag" style="opacity:.5;font-size:.65rem"></i><?php endif; ?>
                                <?= htmlspecialchars($header) ?>
                                <i class="fas fa-chevron-up sort-icon"></i>
                            </span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody id="reportTbody">
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $header => $val): ?>
                        <?php
                            $isNum   = arr_isNumeric($header);
                            $isBadge = arr_isBadge($header);
                            $tdClass = $isNum ? 'num' : 'txt';
                            $strVal  = (string)($val ?? '');
                            $badgeCls = $isBadge ? arr_badgeClass($strVal) : '';
                        ?>
                        <td class="<?= $tdClass ?>" data-raw="<?= htmlspecialchars(strtolower($strVal)) ?>">
                            <?php if ($isBadge && $badgeCls): ?>
                                <span class="val-badge <?= $badgeCls ?>">
                                    <span class="badge-dot"></span>
                                    <?= htmlspecialchars($strVal) ?>
                                </span>
                            <?php elseif ($isNum): ?>
                                <?= arr_formatNum($val) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($strVal) ?>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                    <!-- No-results placeholder -->
                    <tr class="no-results-row" id="noResultsRow" style="display:none">
                        <td colspan="<?= count(array_keys($data[0])) ?>">
                            <i class="fas fa-magnifying-glass"></i>
                            No matching records found.
                        </td>
                    </tr>
                </tbody>
                <?php if (!empty($summaryTotals)): ?>
                <tfoot>
                    <tr style="background: var(--surface-alt); font-weight: 700; border-top: 2px solid var(--brand); border-bottom: 2px solid var(--brand);">
                        <?php 
                        $colIdx = 0;
                        foreach (array_keys($data[0]) as $header):
                            $colIdx++;
                            $val = $summaryTotals[$header] ?? '';
                            $isNum = arr_isNumeric($header);
                            $display = ($val !== '' && is_numeric($val)) ? arr_formatNum($val) : htmlspecialchars((string)$val);
                            if ($colIdx === 1 && $display === '') {
                                $display = 'TOTALS';
                            }
                        ?>
                        <td class="<?= $isNum ? 'num' : 'txt' ?>" style="font-weight: 700; color: var(--brand-dark); font-size: .85rem; padding: 14px 16px;"><?= $display ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="table-footer">
            <span>Showing <strong id="visibleCount"><?= number_format($totalRows) ?></strong> of <strong><?= number_format($totalRows) ?></strong> records</span>
            <span>&copy; <?= date('Y') ?> Pharmacy Management System &mdash; Computer-generated report</span>
        </div>

        <?php endif; ?>
    </div>

    <!-- ═══════════════ PAGE FOOTER ═══════════════ -->
    <div class="page-footer no-print">
        <p>Generated by the Advanced Reporting Module &mdash; <?= date('Y') ?> Pharmacy Management System</p>
    </div>

</div><!-- /page-wrapper -->

<!-- ════════════════ JAVASCRIPT ════════════════ -->
<script>
(function () {
    'use strict';

    const table     = document.getElementById('reportTable');
    const tbody     = document.getElementById('reportTbody');
    const searchInp = document.getElementById('tableSearch');
    const countBadge = document.getElementById('rowCountBadge');
    const visCount  = document.getElementById('visibleCount');
    const noResults = document.getElementById('noResultsRow');

    if (!table) return;

    /* ── Live Search ─────────────────────────── */
    if (searchInp) {
        searchInp.addEventListener('input', () => {
            const q = searchInp.value.trim().toLowerCase();
            let shown = 0;
            const rows = tbody.querySelectorAll('tr:not(.no-results-row)');
            rows.forEach(tr => {
                const match = !q || tr.innerText.toLowerCase().includes(q);
                tr.classList.toggle('filtered-out', !match);
                if (match) shown++;
            });
            if (visCount) visCount.textContent = shown.toLocaleString();
            if (countBadge) countBadge.textContent = shown.toLocaleString() + ' rows';
            if (noResults) noResults.style.display = shown === 0 ? '' : 'none';
        });
    }

    /* ── Column Sorting ──────────────────────── */
    let sortState = { col: -1, asc: true };

    table.querySelectorAll('thead th').forEach(th => {
        th.addEventListener('click', () => {
            const col = parseInt(th.dataset.col, 10);
            const isNum = th.dataset.numeric === '1';

            if (sortState.col === col) {
                sortState.asc = !sortState.asc;
            } else {
                sortState.col = col;
                sortState.asc = true;
            }

            // Update header classes
            table.querySelectorAll('thead th').forEach(h => h.classList.remove('sort-asc','sort-desc'));
            th.classList.add(sortState.asc ? 'sort-asc' : 'sort-desc');

            // Sort rows
            const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
            rows.sort((a, b) => {
                const aCell = a.cells[col];
                const bCell = b.cells[col];
                if (!aCell || !bCell) return 0;
                const aVal = aCell.dataset.raw ?? aCell.innerText.trim().toLowerCase();
                const bVal = bCell.dataset.raw ?? bCell.innerText.trim().toLowerCase();

                let cmp;
                if (isNum) {
                    const aNum = parseFloat(aVal.replace(/,/g,'')) || 0;
                    const bNum = parseFloat(bVal.replace(/,/g,'')) || 0;
                    cmp = aNum - bNum;
                } else {
                    cmp = aVal.localeCompare(bVal, undefined, { sensitivity: 'base', numeric: true });
                }
                return sortState.asc ? cmp : -cmp;
            });

            rows.forEach(r => tbody.appendChild(r));
            if (noResults) tbody.appendChild(noResults);
        });
    });

})();
</script>

</body>
</html>
