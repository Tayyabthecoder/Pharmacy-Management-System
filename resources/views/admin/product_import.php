<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<div class="dashboard-container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title"><i class="fas fa-file-import" style="color: var(--primary-color, #2563eb); margin-right: 8px;"></i> Import Medicines</h1>
            <p class="text-muted">Bulk upload medicine catalog and opening stocks from CSV or Excel (.xlsx) files</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo url('/admin/products/template'); ?>" class="btn" style="background: #10b981; color: white;">
                <i class="fas fa-download"></i> Download CSV Template
            </a>
            <a href="<?php echo url('/admin/products'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Medicines
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?php echo $_SESSION['msgType'] ?? 'info'; ?>" style="margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i>
            <?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg'], $_SESSION['msgType']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['import_summary'])): ?>
        <?php 
            $summary = $_SESSION['import_summary'];
            unset($_SESSION['import_summary']);
        ?>
        <div class="card" style="margin-bottom: 24px; padding: 20px; border-left: 4px solid #2563eb;">
            <h3 style="margin-bottom: 12px; font-size: 16px; font-weight: 700;">Import Summary</h3>
            <div style="display: flex; gap: 24px; margin-bottom: 12px;">
                <div style="color: #16a34a; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> <?php echo $summary['success_count']; ?> Medicines Added
                </div>
                <div style="color: #dc2626; font-weight: 600;">
                    <i class="fas fa-times-circle"></i> <?php echo $summary['skip_count']; ?> Skipped / Failed
                </div>
            </div>

            <?php if (!empty($summary['errors'])): ?>
                <details style="margin-top: 10px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <summary style="font-weight: 600; cursor: pointer; color: #64748b;">View Details / Errors (<?php echo count($summary['errors']); ?> issues)</summary>
                    <ul style="margin-top: 10px; padding-left: 20px; font-size: 13px; color: #dc2626;">
                        <?php foreach ($summary['errors'] as $err): ?>
                            <li style="margin-bottom: 4px;"><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        <!-- Upload Card -->
        <div class="card" style="padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #1e293b;">
                <i class="fas fa-cloud-upload-alt" style="color: #2563eb; margin-right: 8px;"></i> Upload Spreadsheet
            </h2>

            <form method="POST" action="<?php echo url('/admin/products/import'); ?>" enctype="multipart/form-data" id="importForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 40px 20px; text-align: center; background: #f8fafc; cursor: pointer; margin-bottom: 20px;" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-file-excel" style="font-size: 48px; color: #10b981; margin-bottom: 12px;"></i>
                    <h4 style="font-size: 15px; font-weight: 600; color: #334155; margin-bottom: 4px;">Choose CSV or Excel (.xlsx) file</h4>
                    <p style="font-size: 13px; color: #64748b;" id="fileNameDisplay">or drag and drop file here</p>
                    <input type="file" name="file" id="fileInput" accept=".csv, .xlsx, .xls, text/csv" style="display: none;" onchange="handleFileSelected(this)">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="<?php echo url('/admin/products/template'); ?>" style="font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 500;">
                        <i class="fas fa-file-csv"></i> Download sample template (.csv)
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled style="padding: 10px 24px;">
                        <i class="fas fa-upload"></i> Start Import
                    </button>
                </div>
            </form>
        </div>

        <!-- Instructions & Column Mapping Card -->
        <div class="card" style="padding: 24px;">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #1e293b;">
                <i class="fas fa-info-circle" style="color: #2563eb; margin-right: 8px;"></i> Column Mapping Guide
            </h2>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 16px;">
                The importer auto-detects column names. Unrecognized categories, generics, and companies are automatically created in your database if not already present.
            </p>

            <table class="table" style="font-size: 12px; width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 8px 10px;">Column Name</th>
                        <th style="padding: 8px 10px;">Required?</th>
                        <th style="padding: 8px 10px;">Example / Format</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Name</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#fee2e2; color:#b91c1c;">Required</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">Panadol Extra</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Price</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#fee2e2; color:#b91c1c;">Required</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">35.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Strength</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">500mg</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Generic</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">Paracetamol</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Category</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">Analgesics</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Company / Manufacturer</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">GSK</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Cost Price / Trade Price</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">28.00 / 30.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Quantity (Opening Stock)</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">100 (Creates initial FEFO batch)</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Expiry Date</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">YYYY-MM-DD (e.g. 2027-12-31)</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 10px; font-weight: 600;">Barcode</td>
                        <td style="padding: 8px 10px;"><span class="badge" style="background:#f1f5f9; color:#64748b;">Optional</span></td>
                        <td style="padding: 8px 10px; color:#64748b;">EAN-13 / Code128 (auto-generated if empty)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleFileSelected(input) {
    const display = document.getElementById('fileNameDisplay');
    const submitBtn = document.getElementById('submitBtn');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        display.innerHTML = '<strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
        display.style.color = '#10b981';
        submitBtn.disabled = false;
    } else {
        display.innerHTML = 'or drag and drop file here';
        display.style.color = '#64748b';
        submitBtn.disabled = true;
    }
}
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
