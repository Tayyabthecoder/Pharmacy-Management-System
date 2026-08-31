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
$companyLogo = $globalSettings['company_logo'] ?? '';
$rawResolution = $globalSettings['system_resolution'] ?? '1920x1080';
$resolutionMap = [
    '3840x2160' => 135, // 4K Ultra HD
    '2560x1440' => 115, // 2K QHD
    '1920x1080' => 100, // 1080p Full HD (Standard Desktop)
    '1600x900'  => 90,  // 900p HD+ (Laptops)
    '1366x768'  => 80,  // 768p Laptop / Compact
    '1280x720'  => 75,  // 720p HD Display
    '1024x768'  => 70,  // XGA / POS Touch Terminal
];

if (isset($resolutionMap[$rawResolution])) {
    $systemResolution = $resolutionMap[$rawResolution];
} elseif (is_numeric($rawResolution)) {
    $systemResolution = (int)$rawResolution;
} else {
    $systemResolution = 100;
}

if ($systemResolution < 60 || $systemResolution > 180) {
    $systemResolution = 100;
}
$zoomScale = $systemResolution / 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . htmlspecialchars($companyName) : htmlspecialchars($companyName); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Main Stylesheet & Local Vector Icon Engine -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/local-icons.css">

    <!-- TomSelect CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap4.min.css" rel="stylesheet">
    
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

    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($themeColor); ?>;
            --system-resolution: <?php echo $systemResolution; ?>%;
        }
        <?php if ($systemResolution !== 100): ?>
        html {
            zoom: <?php echo $zoomScale; ?>;
        }
        <?php endif; ?>
    </style>

</head>
<body class="theme-<?php echo htmlspecialchars($themeStyle); ?> sidebar-<?php echo htmlspecialchars($sidebarStyle); ?>">
    <div class="layout-wrapper">

