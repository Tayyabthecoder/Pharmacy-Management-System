<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>
<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">System Settings</h1>
            <p class="text-muted">Configure company profiles, invoice rules, stock parameters, email configurations, and system security rules with search-indexed filters.</p>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msgType; ?>">
            <i class="fas fa-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="settings-layout">
            <!-- Sidebar Navigation Tabs -->
            <nav class="settings-nav">
                <!-- V2 Settings Search -->
                <div class="settings-search-wrapper">
                    <div class="settings-search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" id="settings-search" class="settings-search-control" placeholder="Search settings..." autocomplete="off">
                    </div>
                </div>

                <div class="settings-nav-list" id="settings-nav-groups-wrapper">
                    <!-- Group 1: Core Config -->
                    <div class="settings-nav-group" data-group="core">
                        <div class="settings-nav-group-title">
                            <i class="fas fa-sliders-h"></i> Core Config
                        </div>
                        <div class="settings-nav-item active" data-tab="general">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-general">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>General Info</span>
                                    <small>Profile & localizations</small>
                                </div>
                            </a>
                        </div>
                        <div class="settings-nav-item" data-tab="invoice">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-invoice">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Invoices</span>
                                    <small>Formats, paper & terms</small>
                                </div>
                            </a>
                        </div>
                        <div class="settings-nav-item" data-tab="appearance">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-appearance">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Appearance</span>
                                    <small>Theme accent & layouts</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Group 2: Operations -->
                    <div class="settings-nav-group" data-group="operations">
                        <div class="settings-nav-group-title">
                            <i class="fas fa-boxes"></i> Operations
                        </div>
                        <div class="settings-nav-item" data-tab="inventory">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-inventory">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Inventory & Products</span>
                                    <small>Stock & valuation rules</small>
                                </div>
                            </a>
                        </div>

                        <div class="settings-nav-item" data-tab="salesman">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-salesman">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Salesman Controls</span>
                                    <small>Access limits & values</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Group 3: Infrastructure -->
                    <div class="settings-nav-group" data-group="infra">
                        <div class="settings-nav-group-title">
                            <i class="fas fa-network-wired"></i> Infrastructure
                        </div>
                        <div class="settings-nav-item" data-tab="notification">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-notification">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Notifications</span>
                                    <small>Alert chimes & reports</small>
                                </div>
                            </a>
                        </div>
                        <div class="settings-nav-item" data-tab="email">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-email">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Email & SMTP</span>
                                    <small>Outgoing server configs</small>
                                </div>
                            </a>
                        </div>
                        <div class="settings-nav-item" data-tab="security">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-security">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Security</span>
                                    <small>Pass rules & locks</small>
                                </div>
                            </a>
                        </div>
                        <div class="settings-nav-item" data-tab="backup">
                            <a href="javascript:void(0)">
                                <div class="settings-nav-icon nav-icon-backup">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="settings-nav-label">
                                    <span>Backup & Maintenance</span>
                                    <small>Health & sql backups</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area with Panels -->
            <div class="settings-content" id="settings-panels-wrapper">

                <!-- 1. GENERAL SETTINGS PANEL -->
                <div class="settings-panel active" id="panel-general">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-general">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>General Info</h2>
                                <p>Control core company profiles, identification details, timezone, and language preferences.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-info-circle"></i> Company Profile
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_name">Company Name</label>
                                            <span class="tooltip-trigger-premium">
                                                <i class="fas fa-info-circle"></i>
                                                <span class="tooltip-content-premium">Printed on invoice templates and headers.</span>
                                            </span>
                                        </div>
                                        <input type="text" name="company_name" id="company_name" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'My Inventory System'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_email">Company Email</label>
                                            <span class="tooltip-trigger-premium">
                                                <i class="fas fa-info-circle"></i>
                                                <span class="tooltip-content-premium">Target inbox for correspondence and invoices.</span>
                                            </span>
                                        </div>
                                        <input type="email" name="company_email" id="company_email" class="form-control" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" placeholder="billing@mycompany.com">
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_phone">Company Phone</label>
                                        </div>
                                        <input type="text" name="company_phone" id="company_phone" class="form-control" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>" placeholder="+1 (555) 123-4567">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_address">Office Address</label>
                                        </div>
                                        <textarea name="company_address" id="company_address" class="form-control" rows="1" placeholder="123 Corporate Blvd, Suite 100, Tech City"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_website">Company Website</label>
                                        </div>
                                        <input type="url" name="company_website" id="company_website" class="form-control" value="<?php echo htmlspecialchars($settings['company_website'] ?? ''); ?>" placeholder="https://mycompany.com">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="company_registration">Registration / Tax ID</label>
                                        </div>
                                        <input type="text" name="company_registration" id="company_registration" class="form-control" value="<?php echo htmlspecialchars($settings['company_registration'] ?? ''); ?>" placeholder="REG-8829-X">
                                    </div>
                                </div>
                                <div class="settings-row" style="margin-top: 15px;">
                                    <div class="settings-field" style="grid-column: span 2;">
                                        <div class="setting-label-wrapper">
                                            <label for="company_logo">Pharmacy Logo</label>
                                            <span class="tooltip-trigger-premium">
                                                <i class="fas fa-info-circle"></i>
                                                <span class="tooltip-content-premium">Upload your custom pharmacy logo to proudly display on sale invoices, thermal receipts, and system headers.</span>
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.03); border: 1px dashed var(--surface-border, rgba(255,255,255,0.15)); border-radius: 10px; padding: 15px;">
                                            <?php if (!empty($settings['company_logo'])): ?>
                                                <div style="text-align: center; flex-shrink: 0;">
                                                    <img src="<?php echo url($settings['company_logo']); ?>" alt="Pharmacy Logo" style="max-height: 60px; max-width: 150px; object-fit: contain; border-radius: 6px; border: 1px solid var(--surface-border, #ddd); padding: 4px; background: #ffffff;">
                                                    <label style="display: block; margin-top: 6px; font-size: 0.75rem; color: #ef4444; cursor: pointer; font-weight: 600;">
                                                        <input type="checkbox" name="remove_company_logo" value="1"> Remove Logo
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                            <div style="flex: 1;">
                                                <input type="file" name="company_logo" id="company_logo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                                                <small class="text-muted" style="display: block; margin-top: 4px;">PNG, JPG, WEBP or SVG up to 2MB. Recommended size: 300x100px.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-coins"></i> Localisation & Default Thresholds
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="currency">Currency Symbol</label>
                                        </div>
                                        <input type="text" name="currency" id="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? '$'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="tax_rate">Default Tax Rate (%)</label>
                                        </div>
                                        <div class="input-group-premium has-suffix">
                                            <input type="number" step="0.01" name="tax_rate" id="tax_rate" class="form-control" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '5'); ?>">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="timezone">System Timezone</label>
                                        </div>
                                        <select name="timezone" id="timezone" class="form-control">
                                            <option value="UTC" <?php echo ($settings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : ''; ?>>UTC (Coordinated Universal Time)</option>
                                            <option value="Asia/Karachi" <?php echo ($settings['timezone'] ?? 'UTC') == 'Asia/Karachi' ? 'selected' : ''; ?>>Asia/Karachi (PKT)</option>
                                            <option value="America/New_York" <?php echo ($settings['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST/EDT)</option>
                                            <option value="Europe/London" <?php echo ($settings['timezone'] ?? 'UTC') == 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT/BST)</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="language">System Language</label>
                                        </div>
                                        <select name="language" id="language" class="form-control">
                                            <option value="en" <?php echo ($settings['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="ur" <?php echo ($settings['language'] ?? 'en') == 'ur' ? 'selected' : ''; ?>>Urdu</option>
                                            <option value="ar" <?php echo ($settings['language'] ?? 'en') == 'ar' ? 'selected' : ''; ?>>Arabic</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. INVOICE SETTINGS PANEL -->
                <div class="settings-panel" id="panel-invoice">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-invoice">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Invoice Management</h2>
                                <p>Manage invoice templates, prefixes, payment terms, formats, and default disclaimers.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-cog"></i> Format & Terms
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_prefix">Invoice Prefix Code</label>
                                        </div>
                                        <input type="text" name="invoice_prefix" id="invoice_prefix" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_due_days">Payment Due Term (Days)</label>
                                        </div>
                                        <input type="number" name="invoice_due_days" id="invoice_due_days" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_due_days'] ?? '30'); ?>">
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_number_format">Invoice Number Format</label>
                                        </div>
                                        <select name="invoice_number_format" id="invoice_number_format" class="form-control">
                                            <option value="sequential" <?php echo ($settings['invoice_number_format'] ?? 'sequential') == 'sequential' ? 'selected' : ''; ?>>Sequential (e.g. INV-00001)</option>
                                            <option value="year" <?php echo ($settings['invoice_number_format'] ?? 'sequential') == 'year' ? 'selected' : ''; ?>>Year Prefixed (e.g. INV-2026-00001)</option>
                                            <option value="date" <?php echo ($settings['invoice_number_format'] ?? 'sequential') == 'date' ? 'selected' : ''; ?>>Date Prefixed (e.g. INV-20260630-00001)</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_number_padding">Zero Padding Width</label>
                                        </div>
                                        <input type="number" min="2" max="10" name="invoice_number_padding" id="invoice_number_padding" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_number_padding'] ?? '5'); ?>">
                                    </div>
                                </div>
                                <!-- V2 Live Preview: Invoice Format -->
                                <div class="settings-row single">
                                    <div class="preview-widget-card">
                                        <div class="preview-widget-title"><i class="fas fa-eye"></i> Number Format Preview</div>
                                        <code id="invoice-format-preview" style="font-size: 1rem; font-weight: bold; color: var(--primary-color);">INV-00001</code>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-file-signature"></i> Policies & Defaults
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="default_payment_method">Default Payment Method</label>
                                        </div>
                                        <select name="default_payment_method" id="default_payment_method" class="form-control">
                                            <option value="cash" <?php echo ($settings['default_payment_method'] ?? 'cash') == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                            <option value="credit" <?php echo ($settings['default_payment_method'] ?? 'cash') == 'credit' ? 'selected' : ''; ?>>Credit</option>
                                            <option value="bank" <?php echo ($settings['default_payment_method'] ?? 'cash') == 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                                            <option value="cheque" <?php echo ($settings['default_payment_method'] ?? 'cash') == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_paper_size">Invoice Paper Size</label>
                                        </div>
                                        <select name="invoice_paper_size" id="invoice_paper_size" class="form-control">
                                            <option value="a4" <?php echo ($settings['invoice_paper_size'] ?? 'a4') == 'a4' ? 'selected' : ''; ?>>A4 (Standard)</option>
                                            <option value="letter" <?php echo ($settings['invoice_paper_size'] ?? 'a4') == 'letter' ? 'selected' : ''; ?>>Letter</option>
                                            <option value="a5" <?php echo ($settings['invoice_paper_size'] ?? 'a4') == 'a5' ? 'selected' : ''; ?>>A5 (Half-size)</option>
                                            <option value="thermal" <?php echo ($settings['invoice_paper_size'] ?? 'a4') == 'thermal' ? 'selected' : ''; ?>>Thermal Receipt (80mm)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="settings-row single">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="print_template">Print Template Style</label>
                                            <span class="tooltip-trigger-premium">
                                                <i class="fas fa-info-circle"></i>
                                                <span class="tooltip-content-premium">Choose the default layout used when printing invoices. Can also be overridden per-print via URL parameter.</span>
                                            </span>
                                        </div>
                                        <select name="print_template" id="print_template" class="form-control">
                                            <option value="simple" <?php echo ($settings['print_template'] ?? 'simple') == 'simple' ? 'selected' : ''; ?>>Simple / Minimal</option>
                                            <option value="professional" <?php echo ($settings['print_template'] ?? 'simple') == 'professional' ? 'selected' : ''; ?>>Professional / Corporate</option>
                                            <option value="thermal" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal' ? 'selected' : ''; ?>>Thermal Receipt (80mm)</option>
                                            <option value="alshafa_thermal" <?php echo in_array(($settings['print_template'] ?? 'simple'), ['alshafa_thermal', 'thermal_alshafa', 'alshafa']) ? 'selected' : ''; ?>>AlShafa Child Care Thermal (80mm)</option>
                                            <option value="detailed" <?php echo ($settings['print_template'] ?? 'simple') == 'detailed' ? 'selected' : ''; ?>>Detailed Commercial</option>
                                            <option value="statement" <?php echo ($settings['print_template'] ?? 'simple') == 'statement' ? 'selected' : ''; ?>>Statement Style</option>
                                            <option value="walker_care" <?php echo ($settings['print_template'] ?? 'simple') == 'walker_care' ? 'selected' : ''; ?>>Walker Care (Pharma Style)</option>
                                            <option value="walker_care_modern" <?php echo ($settings['print_template'] ?? 'simple') == 'walker_care_modern' ? 'selected' : ''; ?>>Walker Care Modern</option>
                                            <option value="walker_care_dark" <?php echo ($settings['print_template'] ?? 'simple') == 'walker_care_dark' ? 'selected' : ''; ?>>Walker Care Dark</option>
                                            <option value="walker_care_compact" <?php echo ($settings['print_template'] ?? 'simple') == 'walker_care_compact' ? 'selected' : ''; ?>>Walker Care Compact (A5)</option>
                                            <option value="thermal_58mm" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_58mm' ? 'selected' : ''; ?>>Thermal Compact (58mm)</option>
                                            <option value="thermal_detailed" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_detailed' ? 'selected' : ''; ?>>Thermal Detailed (80mm)</option>
                                            <option value="thermal_modern" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_modern' ? 'selected' : ''; ?>>Thermal Modern (80mm)</option>
                                            <option value="thermal_retro" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_retro' ? 'selected' : ''; ?>>Thermal Retro (Dot-Matrix)</option>
                                            <option value="thermal_ticket" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_ticket' ? 'selected' : ''; ?>>Thermal Ticket / Voucher</option>
                                            <option value="thermal_eco" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_eco' ? 'selected' : ''; ?>>Thermal Eco (Ink-Saver)</option>
                                            <option value="thermal_cafe" <?php echo ($settings['print_template'] ?? 'simple') == 'thermal_cafe' ? 'selected' : ''; ?>>Thermal Cafe / Bistro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="settings-row single">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="return_policy_days">Return Policy Timeframe (Days)</label>
                                        </div>
                                        <input type="number" min="0" name="return_policy_days" id="return_policy_days" class="form-control" value="<?php echo htmlspecialchars($settings['return_policy_days'] ?? '15'); ?>" style="max-width: 150px">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-sliders-h"></i> Layout Options & Footer
                                </div>
                                
                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Calculate and Display Tax Breakdown</span>
                                        <span class="toggle-description">Enable tax computation columns and percentage visualizers on generated PDF files.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="invoice_show_tax" value="0">
                                        <input type="checkbox" id="invoice_show_tax" name="invoice_show_tax" value="1" <?php echo !empty($settings['invoice_show_tax']) ? 'checked' : ''; ?>>
                                        <label for="invoice_show_tax" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Allow Recording Partial Payments</span>
                                        <span class="toggle-description">Enable partial payment entries on customer invoice transactions.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="enable_partial_payments" value="0">
                                        <input type="checkbox" id="enable_partial_payments" name="enable_partial_payments" value="1" <?php echo !empty($settings['enable_partial_payments']) ? 'checked' : ''; ?>>
                                        <label for="enable_partial_payments" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable Sales Returns Workflow</span>
                                        <span class="toggle-description">Permit return of products and issue of credit notes against invoices.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="enable_returns" value="0">
                                        <input type="checkbox" id="enable_returns" name="enable_returns" value="1" <?php echo !empty($settings['enable_returns']) ? 'checked' : ''; ?>>
                                        <label for="enable_returns" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="settings-row single" style="margin-top: 20px">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="invoice_notes">Default Footnotes / Standard Disclaimers</label>
                                        </div>
                                        <textarea name="invoice_notes" id="invoice_notes" class="form-control" rows="3" placeholder="Thank you for your valuable business!"><?php echo htmlspecialchars($settings['invoice_notes'] ?? 'Thank you for your business!'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- 2b. INVENTORY & PRODUCTS PANEL -->
                <div class="settings-panel" id="panel-inventory">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-inventory">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Inventory & Products</h2>
                                <p>Manage product constraints, stock logic, pricing alerts, and valuation methods.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-box-open"></i> Product Defaults
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="max_product_image_size">Max Image Upload Size</label>
                                        </div>
                                        <select name="max_product_image_size" id="max_product_image_size" class="form-control">
                                            <option value="1048576" <?php echo ($settings['max_product_image_size'] ?? '5242880') == '1048576' ? 'selected' : ''; ?>>1 MB</option>
                                            <option value="2097152" <?php echo ($settings['max_product_image_size'] ?? '5242880') == '2097152' ? 'selected' : ''; ?>>2 MB</option>
                                            <option value="5242880" <?php echo ($settings['max_product_image_size'] ?? '5242880') == '5242880' ? 'selected' : ''; ?>>5 MB (Default)</option>
                                            <option value="10485760" <?php echo ($settings['max_product_image_size'] ?? '5242880') == '10485760' ? 'selected' : ''; ?>>10 MB</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="default_restock_qty">Default Restock Quantity</label>
                                        </div>
                                        <input type="number" name="default_restock_qty" id="default_restock_qty" class="form-control" value="<?php echo htmlspecialchars($settings['default_restock_qty'] ?? '50'); ?>">
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable Product Images</span>
                                        <span class="toggle-description">Enable product image display grids and upload fields in Product Forms.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="enable_product_images" value="0">
                                        <input type="checkbox" id="enable_product_images" name="enable_product_images" value="1" <?php echo !empty($settings['enable_product_images']) ? 'checked' : ''; ?>>
                                        <label for="enable_product_images" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-warehouse"></i> Stock & Valuation Rules
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="inventory_valuation_method">Inventory Valuation Method</label>
                                        </div>
                                        <select name="inventory_valuation_method" id="inventory_valuation_method" class="form-control">
                                            <option value="fifo" <?php echo ($settings['inventory_valuation_method'] ?? 'fifo') == 'fifo' ? 'selected' : ''; ?>>FIFO (First-In, First-Out)</option>
                                            <option value="lifo" <?php echo ($settings['inventory_valuation_method'] ?? 'fifo') == 'lifo' ? 'selected' : ''; ?>>LIFO (Last-In, First-Out)</option>
                                            <option value="avco" <?php echo ($settings['inventory_valuation_method'] ?? 'fifo') == 'avco' ? 'selected' : ''; ?>>Weighted Average Cost (AVCO)</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="profit_margin_warning">Minimum Profit Margin Warning (%)</label>
                                        </div>
                                        <div class="input-group-premium has-suffix">
                                            <input type="number" step="0.1" name="profit_margin_warning" id="profit_margin_warning" class="form-control" value="<?php echo htmlspecialchars($settings['profit_margin_warning'] ?? '10'); ?>">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Allow Negative Stock Transactions</span>
                                        <span class="toggle-description">Permit salesman to draft invoices selling items beyond active warehouse quantities.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="allow_negative_stock" value="0">
                                        <input type="checkbox" id="allow_negative_stock" name="allow_negative_stock" value="1" <?php echo !empty($settings['allow_negative_stock']) ? 'checked' : ''; ?>>
                                        <label for="allow_negative_stock" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Track Product Cost Price</span>
                                        <span class="toggle-description">Maintain purchase/buying cost fields on product entries for profit computations.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="track_cost_price" value="0">
                                        <input type="checkbox" id="track_cost_price" name="track_cost_price" value="1" <?php echo !empty($settings['track_cost_price']) ? 'checked' : ''; ?>>
                                        <label for="track_cost_price" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable Barcode Scanning Support</span>
                                        <span class="toggle-description">Integrate barcode field search and automatic scanner listeners on invoice creation screens.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="enable_barcode" value="0">
                                        <input type="checkbox" id="enable_barcode" name="enable_barcode" value="1" <?php echo !empty($settings['enable_barcode']) ? 'checked' : ''; ?>>
                                        <label for="enable_barcode" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2c. SALESMAN CONTROLS PANEL -->
                <div class="settings-panel" id="panel-salesman">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-salesman">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Salesman Controls</h2>
                                <p>Define access permissions, limits, invoice modifications, and pricing limits for salesman roles.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-user-lock"></i> Salesman Feature Access
                                </div>
                                


                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Allow Salesman to Void/Delete Invoices</span>
                                        <span class="toggle-description">Permit salesman to void transactions (requires reason logs).</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="salesman_can_delete_invoice" value="0">
                                        <input type="checkbox" id="salesman_can_delete_invoice" name="salesman_can_delete_invoice" value="1" <?php echo !empty($settings['salesman_can_delete_invoice']) ? 'checked' : ''; ?>>
                                        <label for="salesman_can_delete_invoice" class="toggle-slider"></label>
                                    </div>
                                </div>



                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Allow Salesman to See Product Cost Price</span>
                                        <span class="toggle-description">Make product purchase costs visible in warehouse logs search tables for salesman.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="salesman_can_see_cost_price" value="0">
                                        <input type="checkbox" id="salesman_can_see_cost_price" name="salesman_can_see_cost_price" value="1" <?php echo !empty($settings['salesman_can_see_cost_price']) ? 'checked' : ''; ?>>
                                        <label for="salesman_can_see_cost_price" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-hand-holding-usd"></i> Pricing & Approval Limits
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="max_salesman_discount">Maximum Salesman Discount Rate (%)</label>
                                        </div>
                                        <div class="input-group-premium has-suffix">
                                            <input type="number" step="0.1" name="max_salesman_discount" id="max_salesman_discount" class="form-control" value="<?php echo htmlspecialchars($settings['max_salesman_discount'] ?? '5.0'); ?>">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="approval_threshold_amount">Invoice Approval Threshold Amount</label>
                                        </div>
                                        <input type="number" step="0.01" name="approval_threshold_amount" id="approval_threshold_amount" class="form-control" value="<?php echo htmlspecialchars($settings['approval_threshold_amount'] ?? '5000.00'); ?>">
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable Salesman Discounts Permissions</span>
                                        <span class="toggle-description">Permit salesman to apply manual line-item/cart percentage discounts.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="salesman_can_give_discount" value="0">
                                        <input type="checkbox" id="salesman_can_give_discount" name="salesman_can_give_discount" value="1" <?php echo !empty($settings['salesman_can_give_discount']) ? 'checked' : ''; ?>>
                                        <label for="salesman_can_give_discount" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Require Admin Approval Above Threshold</span>
                                        <span class="toggle-description">Hold invoices exceeding the threshold amount pending admin authentication logs.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="require_admin_approval" value="0">
                                        <input type="checkbox" id="require_admin_approval" name="require_admin_approval" value="1" <?php echo !empty($settings['require_admin_approval']) ? 'checked' : ''; ?>>
                                        <label for="require_admin_approval" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. NOTIFICATION SETTINGS PANEL -->
                <div class="settings-panel" id="panel-notification">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-notification">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>System Alerts & Notifications</h2>
                                <p>Enable alerts for low stock events, newly generated transactions, and payment updates.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-bell"></i> In-App Alerts Toggles
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Low Stock Inventory Warnings</span>
                                        <span class="toggle-description">Flag dashboard notification bars when products drop below their designated limit.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notify_low_stock" value="0">
                                        <input type="checkbox" id="notify_low_stock" name="notify_low_stock" value="1" <?php echo !empty($settings['notify_low_stock']) ? 'checked' : ''; ?>>
                                        <label for="notify_low_stock" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">New Sales Invoices Generated</span>
                                        <span class="toggle-description">Alert administrators immediately when salesman issues any invoice.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notify_new_invoice" value="0">
                                        <input type="checkbox" id="notify_new_invoice" name="notify_new_invoice" value="1" <?php echo !empty($settings['notify_new_invoice']) ? 'checked' : ''; ?>>
                                        <label for="notify_new_invoice" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Product Restock Alerts</span>
                                        <span class="toggle-description">Alert administrators when an inventory restock (receive invoice) is recorded.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notify_restock" value="0">
                                        <input type="checkbox" id="notify_restock" name="notify_restock" value="1" <?php echo !empty($settings['notify_restock']) ? 'checked' : ''; ?>>
                                        <label for="notify_restock" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Payment Received Alerts</span>
                                        <span class="toggle-description">Notify administrators when customer invoice payment deposits are recorded.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notify_payment_received" value="0">
                                        <input type="checkbox" id="notify_payment_received" name="notify_payment_received" value="1" <?php echo !empty($settings['notify_payment_received']) ? 'checked' : ''; ?>>
                                        <label for="notify_payment_received" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Product Expiry Approaching Alerts</span>
                                        <span class="toggle-description">Warn when warehouse items are within their critical shelf-life boundary.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notify_product_expiry" value="0">
                                        <input type="checkbox" id="notify_product_expiry" name="notify_product_expiry" value="1" <?php echo !empty($settings['notify_product_expiry']) ? 'checked' : ''; ?>>
                                        <label for="notify_product_expiry" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Notification Audio Sounds</span>
                                        <span class="toggle-description">Play audio chimes upon receiving new system/in-app alert updates.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="notification_sound" value="0">
                                        <input type="checkbox" id="notification_sound" name="notification_sound" value="1" <?php echo !empty($settings['notification_sound']) ? 'checked' : ''; ?>>
                                        <label for="notification_sound" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-envelope"></i> External Email Summary Alerts
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="low_stock_email">Stock Administrator Email</label>
                                        </div>
                                        <input type="email" name="low_stock_email" id="low_stock_email" class="form-control" value="<?php echo htmlspecialchars($settings['low_stock_email'] ?? ''); ?>" placeholder="warehouse-alert@mycompany.com">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="daily_report_time">Daily Summary Send Time</label>
                                        </div>
                                        <input type="time" name="daily_report_time" id="daily_report_time" class="form-control" value="<?php echo htmlspecialchars($settings['daily_report_time'] ?? '18:00'); ?>">
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Broadcast Daily Sales Summary Report</span>
                                        <span class="toggle-description">Generate and email an end-of-day sales metric summary sheet to the administrator address.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="daily_report_email" value="0">
                                        <input type="checkbox" id="daily_report_email" name="daily_report_email" value="1" <?php echo !empty($settings['daily_report_email']) ? 'checked' : ''; ?>>
                                        <label for="daily_report_email" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3a. EMAIL & SMTP PANEL -->
                <div class="settings-panel" id="panel-email">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-email">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Email & SMTP Config</h2>
                                <p>Setup system outgoing SMTP mail server variables for dispatching transaction and summary reports.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-mail-bulk"></i> Outgoing Mail Settings
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="email_from_name">Sender Display Name</label>
                                        </div>
                                        <input type="text" name="email_from_name" id="email_from_name" class="form-control" value="<?php echo htmlspecialchars($settings['email_from_name'] ?? 'IMS Billing System'); ?>" placeholder="My Inventory System">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="email_from_address">Sender Email Address</label>
                                        </div>
                                        <input type="email" name="email_from_address" id="email_from_address" class="form-control" value="<?php echo htmlspecialchars($settings['email_from_address'] ?? 'noreply@company.com'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-server"></i> SMTP Server Integration
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="smtp_host">SMTP Host Address</label>
                                        </div>
                                        <input type="text" name="smtp_host" id="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="smtp_port">SMTP Connection Port</label>
                                        </div>
                                        <input type="number" name="smtp_port" id="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="smtp_username">SMTP Authentication User</label>
                                        </div>
                                        <input type="text" name="smtp_username" id="smtp_username" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" placeholder="user@gmail.com">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="smtp_password">SMTP Authentication Pass</label>
                                        </div>
                                        <input type="password" name="smtp_password" id="smtp_password" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" placeholder="••••••••••••">
                                    </div>
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field" style="max-width: 250px">
                                        <div class="setting-label-wrapper">
                                            <label for="smtp_encryption">SMTP Connection Encryption</label>
                                        </div>
                                        <select name="smtp_encryption" id="smtp_encryption" class="form-control">
                                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS (Secure Port 587)</option>
                                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'ssl' ? 'selected' : ''; ?>>SSL (Implicit secure 465)</option>
                                            <option value="none" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'none' ? 'selected' : ''; ?>>None (Plain unencrypted)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable SMTP Outgoing Services</span>
                                        <span class="toggle-description">Enable sending automated emails via SMTP. When disabled, standard PHP mail() will be used.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="smtp_enabled" value="0">
                                        <input type="checkbox" id="smtp_enabled" name="smtp_enabled" value="1" <?php echo !empty($settings['smtp_enabled']) ? 'checked' : ''; ?>>
                                        <label for="smtp_enabled" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SECURITY SETTINGS PANEL -->
                <div class="settings-panel" id="panel-security">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-security">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Security Parameters</h2>
                                <p>Strengthen portal access security, password complexity, login lockout durations, and timeouts.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-user-lock"></i> Session & Authentication Rules
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="session_timeout">Idle Session Timeout (Minutes)</label>
                                        </div>
                                        <input type="number" name="session_timeout" id="session_timeout" class="form-control" value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '60'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="password_min_length">Minimum Password Length</label>
                                        </div>
                                        <input type="number" min="6" max="20" name="password_min_length" id="password_min_length" class="form-control" value="<?php echo htmlspecialchars($settings['password_min_length'] ?? '8'); ?>">
                                    </div>
                                </div>
                                <div class="settings-row single" style="margin-top: 15px">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="password_expiry_days">Password Expiration Cycle (Days)</label>
                                        </div>
                                        <input type="number" min="0" name="password_expiry_days" id="password_expiry_days" class="form-control" value="<?php echo htmlspecialchars($settings['password_expiry_days'] ?? '90'); ?>" style="max-width: 150px">
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-ban"></i> Lockout & Rate Limiting
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="max_login_attempts">Maximum Password Failures Allowed</label>
                                        </div>
                                        <input type="number" name="max_login_attempts" id="max_login_attempts" class="form-control" value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>">
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="lockout_duration">Lockout Period (Minutes)</label>
                                        </div>
                                        <input type="number" name="lockout_duration" id="lockout_duration" class="form-control" value="<?php echo htmlspecialchars($settings['lockout_duration'] ?? '15'); ?>">
                                    </div>
                                </div>
                                <div class="settings-row single" style="margin-top: 15px">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="ip_whitelist">Restricted Login IP Whitelist</label>
                                        </div>
                                        <textarea name="ip_whitelist" id="ip_whitelist" class="form-control" rows="2" placeholder="e.g. 192.168.1.50, 10.0.0.12"><?php echo htmlspecialchars($settings['ip_whitelist'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-user-shield"></i> Security Policies & Audits
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Require Uppercase Password Characters</span>
                                        <span class="toggle-description">Ensure user passwords contain at least one uppercase letter (A-Z).</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="require_uppercase" value="0">
                                        <input type="checkbox" id="require_uppercase" name="require_uppercase" value="1" <?php echo !empty($settings['require_uppercase']) ? 'checked' : ''; ?>>
                                        <label for="require_uppercase" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Require Special Characters</span>
                                        <span class="toggle-description">Force user passwords to contain at least one special character symbol (e.g. @, #, $, %).</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="require_special_char" value="0">
                                        <input type="checkbox" id="require_special_char" name="require_special_char" value="1" <?php echo !empty($settings['require_special_char']) ? 'checked' : ''; ?>>
                                        <label for="require_special_char" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enforce Two-Factor Authentication (2FA)</span>
                                        <span class="toggle-description">Require administrative accounts to verify via email codes upon login.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="two_factor_auth" value="0">
                                        <input type="checkbox" id="two_factor_auth" name="two_factor_auth" value="1" <?php echo !empty($settings['two_factor_auth']) ? 'checked' : ''; ?>>
                                        <label for="two_factor_auth" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Enable System Activity Audit Log</span>
                                        <span class="toggle-description">Keep strict history logs of database actions, invoice deletion, and inventory adjustments.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="enable_audit_log" value="0">
                                        <input type="checkbox" id="enable_audit_log" name="enable_audit_log" value="1" <?php echo !empty($settings['enable_audit_log']) ? 'checked' : ''; ?>>
                                        <label for="enable_audit_log" class="toggle-slider"></label>
                                    </div>
                                </div>

                                <div class="toggle-wrapper">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Terminate Session on Browser Close</span>
                                        <span class="toggle-description">Auto-logout user immediately when closing the system browser tab.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="auto_logout_on_close" value="0">
                                        <input type="checkbox" id="auto_logout_on_close" name="auto_logout_on_close" value="1" <?php echo !empty($settings['auto_logout_on_close']) ? 'checked' : ''; ?>>
                                        <label for="auto_logout_on_close" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5a. BACKUP & MAINTENANCE PANEL -->
                <div class="settings-panel" id="panel-backup">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header" style="border-bottom: 1px solid var(--surface-border, rgba(226, 232, 240, 0.8)); padding-bottom: 16px; margin-bottom: 20px;">
                            <div class="settings-panel-header-icon nav-icon-backup" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Backup & System Maintenance</h2>
                                <p>On-demand manual backups, database restoration, and system maintenance locks.</p>
                            </div>
                        </div>

                        <div class="settings-panel-body">
                            <?php 
                            $totalBackupCount = count($backupFiles ?? []);
                            $totalBackupBytes = array_sum(array_column($backupFiles ?? [], 'size'));
                            $totalBackupSizeFormatted = $totalBackupBytes >= 1048576 
                                ? number_format($totalBackupBytes / 1048576, 2) . ' MB' 
                                : number_format($totalBackupBytes / 1024, 1) . ' KB';
                            $lastBackupTime = !empty($backupFiles[0]['created_at']) 
                                ? date('M d, Y h:i A', $backupFiles[0]['created_at']) 
                                : 'No Backups Yet';
                            ?>

                            <!-- 0. TOP DASHBOARD METRIC SUMMARY CARDS -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                                <!-- Stat 1: Total Backups -->
                                <div style="background: var(--card-bg, var(--surface-color, #ffffff)); border: 1px solid var(--surface-border, rgba(226, 232, 240, 0.8)); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.35rem; font-weight: 700; color: var(--text-color, #1e293b); line-height: 1.2;"><?php echo $totalBackupCount; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 500;">Saved Backups</div>
                                    </div>
                                </div>

                                <!-- Stat 2: Total Storage Used -->
                                <div style="background: var(--card-bg, var(--surface-color, #ffffff)); border: 1px solid var(--surface-border, rgba(226, 232, 240, 0.8)); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fas fa-hard-drive"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.35rem; font-weight: 700; color: var(--text-color, #1e293b); line-height: 1.2;"><?php echo $totalBackupSizeFormatted; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 500;">Storage Used</div>
                                    </div>
                                </div>

                                <!-- Stat 3: Last Backup Time -->
                                <div style="background: var(--card-bg, var(--surface-color, #ffffff)); border: 1px solid var(--surface-border, rgba(226, 232, 240, 0.8)); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div style="overflow: hidden;">
                                        <div style="font-size: 0.92rem; font-weight: 700; color: var(--text-color, #1e293b); line-height: 1.3; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo $lastBackupTime; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 500;">Last Backup</div>
                                    </div>
                                </div>

                                <!-- Stat 4: System Protection Status -->
                                <div style="background: var(--card-bg, var(--surface-color, #ffffff)); border: 1px solid var(--surface-border, rgba(226, 232, 240, 0.8)); border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                        <i class="fas fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #10b981; font-size: 0.95rem;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span> Protected
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); font-weight: 500;">Database Status</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 1. MANUAL BACKUP GENERATOR HERO CARD -->
                            <div class="settings-section" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%); border: 1px solid var(--surface-border, rgba(59, 130, 246, 0.25)); padding: 24px; border-radius: 14px; margin-bottom: 24px; box-shadow: var(--shadow-sm, 0 2px 6px rgba(0,0,0,0.04)); position: relative; overflow: hidden;">
                                <div style="position: absolute; right: -20px; bottom: -20px; font-size: 120px; color: var(--primary-color, #3b82f6); opacity: 0.04; pointer-events: none; z-index: 0;">
                                    <i class="fas fa-cloud-arrow-down"></i>
                                </div>

                                <div style="position: relative; z-index: 1;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                        <div style="width: 38px; height: 38px; border-radius: 8px; background: var(--primary-color, #3b82f6); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                                            <i class="fas fa-download"></i>
                                        </div>
                                        <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--text-color, #1e293b);">Generate On-Demand Backup</h3>
                                    </div>
                                    
                                    <p style="color: var(--text-muted, #64748b); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5; max-width: 750px;">
                                        Instantly export a complete snapshot of your pharmacy database including all products, sales invoices, user logs, customers, suppliers, and system configuration rules.
                                    </p>
                                    
                                    <div style="display: flex; flex-direction: column; gap: 20px;">
                                        <div style="flex: 1; min-width: 240px;">
                                            <label for="manual_backup_format_select" style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.88rem; color: var(--text-color, #1e293b);">
                                                <i class="fas fa-file-code" style="margin-right: 4px; color: var(--primary-color, #3b82f6);"></i> Select Backup Format
                                            </label>
                                            <select id="manual_backup_format_select" class="form-control" style="height: 44px; width: 100%; font-weight: 500; border-radius: 8px; background: var(--card-bg, var(--surface-color, #ffffff)); color: var(--text-color, #1e293b); border: 1px solid var(--surface-border, #cbd5e1);">
                                                <option value="sql">SQL Dump Script (.sql) — Universal & Portable</option>
                                                <option value="sqlite">SQLite Database File (.sqlite) — Raw Snapshot</option>
                                            </select>
                                        </div>

                                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                            <button type="button" onclick="triggerManualBackup(document.getElementById('manual_backup_format_select').value, 1)" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 0 22px; height: 44px; border-radius: 8px; font-weight: 600; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 9px; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                                                <i class="fas fa-cloud-arrow-down" style="font-size: 1.05rem;"></i> Instant Download (.SQL)
                                            </button>

                                            <button type="button" onclick="triggerManualBackup(document.getElementById('manual_backup_format_select').value, 0)" class="btn" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #ffffff; border: none; padding: 0 22px; height: 44px; border-radius: 8px; font-weight: 600; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 9px; cursor: pointer; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: transform 0.15s ease, box-shadow 0.15s ease;">
                                                <i class="fas fa-vault" style="font-size: 1rem;"></i> Save to Vault Storage
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. BACKUP HISTORY & FILE MANAGEMENT TABLE -->
                            <div class="settings-section" style="margin-bottom: 28px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-color, #1e293b);">Backup Vault Storage</h3>
                                    </div>
                                    <span style="font-size: 0.82rem; font-weight: 600; padding: 5px 12px; border-radius: 20px; background: rgba(59, 130, 246, 0.12); color: var(--primary-color, #3b82f6);">
                                        <i class="fas fa-folder-open" style="margin-right: 4px;"></i> <?php echo count($backupFiles ?? []); ?> Saved File(s)
                                    </span>
                                </div>
                                
                                <div class="table-container" style="border: 1px solid var(--surface-border, rgba(226,232,240,0.8)); border-radius: 12px; overflow: hidden; background: var(--card-bg, var(--surface-color, #ffffff)); box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));">
                                    <table class="table" style="margin: 0; width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: var(--surface-color, rgba(0,0,0,0.03)); border-bottom: 1px solid var(--surface-border, rgba(226,232,240,0.8)); text-align: left;">
                                                <th style="padding: 14px 18px; font-weight: 600; color: var(--text-color, #1e293b);">Backup File</th>
                                                <th style="padding: 14px 18px; font-weight: 600; color: var(--text-color, #1e293b);">Type</th>
                                                <th style="padding: 14px 18px; font-weight: 600; color: var(--text-color, #1e293b);">Size</th>
                                                <th style="padding: 14px 18px; font-weight: 600; color: var(--text-color, #1e293b);">Date Created</th>
                                                <th style="padding: 14px 18px; font-weight: 600; color: var(--text-color, #1e293b); text-align: right;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($backupFiles) && count($backupFiles) > 0): ?>
                                                <?php foreach ($backupFiles as $backup): ?>
                                                    <tr style="border-bottom: 1px solid var(--surface-border, rgba(226,232,240,0.6)); transition: background 0.15s ease;">
                                                        <td style="padding: 14px 18px;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <i class="fas fa-file-code" style="color: <?php echo $backup['extension'] === 'sql' ? '#10b981' : '#8b5cf6'; ?>; font-size: 1.1rem;"></i>
                                                                <strong style="color: var(--text-color, #1e293b); font-size: 0.9rem; font-family: monospace;"><?php echo htmlspecialchars($backup['filename']); ?></strong>
                                                            </div>
                                                        </td>
                                                        <td style="padding: 14px 18px;">
                                                            <span style="background: <?php echo $backup['extension'] === 'sql' ? 'rgba(16, 185, 129, 0.12)' : 'rgba(139, 92, 246, 0.12)'; ?>; color: <?php echo $backup['extension'] === 'sql' ? '#10b981' : '#8b5cf6'; ?>; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                                                <?php echo htmlspecialchars($backup['extension']); ?> DUMP
                                                            </span>
                                                        </td>
                                                        <td style="padding: 14px 18px; color: var(--text-muted, #64748b); font-size: 0.88rem; font-weight: 500;">
                                                            <?php echo number_format($backup['size'] / 1024, 1); ?> KB
                                                        </td>
                                                        <td style="padding: 14px 18px; color: var(--text-color, #1e293b); font-size: 0.88rem;">
                                                            <?php echo date('M d, Y — h:i A', $backup['created_at']); ?>
                                                        </td>
                                                        <td style="padding: 14px 18px; text-align: right;">
                                                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                                                <!-- Download Button -->
                                                                <a href="<?php echo url('/admin/backup/download?file=' . urlencode($backup['filename'])); ?>" class="btn btn-sm" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 6px 13px; border-radius: 7px; text-decoration: none; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: background 0.15s ease;" title="Download File">
                                                                    <i class="fas fa-download"></i> Download
                                                                </a>

                                                                <!-- Restore Button -->
                                                                <button type="button" onclick="triggerRestoreBackup('<?php echo htmlspecialchars($backup['filename'], ENT_QUOTES); ?>')" class="btn btn-sm" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25); padding: 6px 13px; border-radius: 7px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: background 0.15s ease;" title="Restore Database">
                                                                    <i class="fas fa-undo"></i> Restore
                                                                </button>

                                                                <!-- Delete Button -->
                                                                <button type="button" onclick="triggerDeleteBackup('<?php echo htmlspecialchars($backup['filename'], ENT_QUOTES); ?>')" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); padding: 6px 13px; border-radius: 7px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: background 0.15s ease;" title="Delete File">
                                                                    <i class="fas fa-trash-alt"></i> Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" style="padding: 32px; text-align: center; color: var(--text-muted, #94a3b8); font-size: 0.9rem;">
                                                        <i class="fas fa-database" style="font-size: 2.2rem; margin-bottom: 10px; display: block; color: var(--text-muted, #cbd5e1);"></i>
                                                        No backup files stored in vault. Click <strong>"Instant Download"</strong> or <strong>"Save to Vault Storage"</strong> above to generate your first backup.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 3. RESTORE FROM EXTERNAL FILE CARD -->
                            <div class="settings-section" style="background: var(--card-bg, var(--surface-color, #ffffff)); border: 1px dashed rgba(245, 158, 11, 0.5); padding: 22px; border-radius: 12px; margin-bottom: 28px; box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.03));">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                                        <i class="fas fa-cloud-arrow-up"></i>
                                    </div>
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-color, #1e293b);">Restore Database from External File</h3>
                                </div>
                                
                                <p style="color: var(--text-muted, #64748b); font-size: 0.88rem; margin-bottom: 16px; line-height: 1.5;">
                                    Upload an external <code>.sql</code> or <code>.sqlite</code> backup file from your computer to restore all database tables and records.
                                </p>
                                
                                <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
                                    <input type="file" id="ui_backup_file_input" accept=".sql,.sqlite,.db" class="form-control" style="flex: 1; min-width: 260px; padding: 8px 14px; height: 44px; border-radius: 8px; background: var(--card-bg, var(--surface-color, #ffffff)); color: var(--text-color, #1e293b); border: 1px solid var(--surface-border, #cbd5e1);">
                                    <button type="button" onclick="triggerUploadRestore()" class="btn" style="background: #f59e0b; color: #ffffff; border: none; padding: 0 22px; height: 44px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);">
                                        <i class="fas fa-upload"></i> Upload & Restore Database
                                    </button>
                                </div>
                            </div>

                            <!-- 4. SYSTEM MAINTENANCE MODE -->
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-tools"></i> System Maintenance Mode
                                </div>
                                <div class="settings-row single">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="maintenance_message">Public Maintenance Notice Message</label>
                                        </div>
                                        <textarea name="maintenance_message" id="maintenance_message" class="form-control" rows="2" placeholder="The system is undergoing scheduled upgrade. Please try again later."><?php echo htmlspecialchars($settings['maintenance_message'] ?? 'The system is undergoing scheduled upgrade. Please try again later.'); ?></textarea>
                                    </div>
                                </div>

                                <div class="toggle-wrapper" style="margin-top: 15px">
                                    <div class="toggle-info">
                                        <span class="toggle-label">Activate Maintenance Mode</span>
                                        <span class="toggle-description">Lock out non-admin salesman roles immediately to perform safe backend migrations.</span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="maintenance_mode" value="0">
                                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php echo !empty($settings['maintenance_mode']) ? 'checked' : ''; ?>>
                                        <label for="maintenance_mode" class="toggle-slider"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. APPEARANCE SETTINGS PANEL -->
                <div class="settings-panel" id="panel-appearance">
                    <div class="settings-panel-card">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon nav-icon-appearance">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="settings-panel-header-text">
                                <h2>Portal Appearance</h2>
                                <p>Adjust system color schemes, data page layouts, responsive grids, and standard display formats.</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-brush"></i> UI Custom Theme Scheme
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label>Corporate Primary Theme Color</label>
                                        </div>
                                        <div class="color-picker-wrapper">
                                            <input type="color" name="theme_color" id="theme_color" class="color-picker-input" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#4f46e5'); ?>">
                                            <input type="text" id="theme_color_text" class="color-picker-value" value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#4f46e5'); ?>" maxlength="7" pattern="^#([A-Fa-f0-9]{6})$">
                                            <span class="settings-badge settings-badge-info">Active System Accent</span>
                                        </div>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="theme_style">Interface Layout Theme Style</label>
                                        </div>
                                        <select name="theme_style" id="theme_style" class="form-control">
                                            <option value="glass" <?php echo ($settings['theme_style'] ?? 'glass') == 'glass' ? 'selected' : ''; ?>>Modern Glassmorphism (Default)</option>
                                            <option value="dark" <?php echo ($settings['theme_style'] ?? 'glass') == 'dark' ? 'selected' : ''; ?>>Sleek Dark Mode (Neon Dark)</option>
                                            <option value="classic" <?php echo ($settings['theme_style'] ?? 'glass') == 'classic' ? 'selected' : ''; ?>>Classic Corporate (Minimalist Light)</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- V2 Live Preview Color accent box -->
                                <div class="settings-row single">
                                    <div class="preview-widget-card">
                                        <div class="preview-widget-title"><i class="fas fa-palette"></i> Live Color Scheme Accent Preview</div>
                                        <span id="theme-preview-box" class="preview-accent-box" style="background-color: <?php echo htmlspecialchars($settings['theme_color'] ?? '#4f46e5'); ?>;">Primary Button Accent Active</span>
                                    </div>
                                </div>
                            </div>

                            <div class="settings-section">
                                <div class="settings-section-title">
                                    <i class="fas fa-th-list"></i> Table Grid Layout Default Filters
                                </div>
                                <div class="settings-row">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="records_per_page">Default List Records count</label>
                                        </div>
                                        <select name="records_per_page" id="records_per_page" class="form-control">
                                            <option value="10" <?php echo ($settings['records_per_page'] ?? '25') == '10' ? 'selected' : ''; ?>>10 Rows</option>
                                            <option value="25" <?php echo ($settings['records_per_page'] ?? '25') == '25' ? 'selected' : ''; ?>>25 Rows</option>
                                            <option value="50" <?php echo ($settings['records_per_page'] ?? '25') == '50' ? 'selected' : ''; ?>>50 Rows</option>
                                            <option value="100" <?php echo ($settings['records_per_page'] ?? '25') == '100' ? 'selected' : ''; ?>>100 Rows</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="date_format">System Date Display Presets</label>
                                        </div>
                                        <select name="date_format" id="date_format" class="form-control">
                                            <option value="Y-m-d" <?php echo ($settings['date_format'] ?? 'M d, Y') == 'Y-m-d' ? 'selected' : ''; ?>>2026-05-29 (ISO - YYYY-MM-DD)</option>
                                            <option value="m/d/Y" <?php echo ($settings['date_format'] ?? 'M d, Y') == 'm/d/Y' ? 'selected' : ''; ?>>05/29/2026 (US - MM/DD/YYYY)</option>
                                            <option value="d/m/Y" <?php echo ($settings['date_format'] ?? 'M d, Y') == 'd/m/Y' ? 'selected' : ''; ?>>29/05/2026 (UK - DD/MM/YYYY)</option>
                                            <option value="M d, Y" <?php echo ($settings['date_format'] ?? 'M d, Y') == 'M d, Y' ? 'selected' : ''; ?>>May 29, 2026 (Readable Text)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="settings-row" style="margin-top: 20px">
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="sidebar_style">Navigation Sidebar Visual Layout</label>
                                        </div>
                                        <select name="sidebar_style" id="sidebar_style" class="form-control">
                                            <option value="expanded" <?php echo ($settings['sidebar_style'] ?? 'expanded') == 'expanded' ? 'selected' : ''; ?>>Fully Expanded (Default text + Icon)</option>
                                            <option value="collapsed" <?php echo ($settings['sidebar_style'] ?? 'expanded') == 'collapsed' ? 'selected' : ''; ?>>Minimalist Slim Layout (Icons only)</option>
                                        </select>
                                    </div>
                                    <div class="settings-field">
                                        <div class="setting-label-wrapper">
                                            <label for="system_resolution">System Screen Resolution / UI Scaling</label>
                                            <span class="tooltip-trigger-premium">
                                                <i class="fas fa-info-circle"></i>
                                                <span class="tooltip-content-premium">Tailor screen layout scaling for POS touch terminals, laptops, or large monitors.</span>
                                            </span>
                                        </div>
                                        <select name="system_resolution" id="system_resolution" class="form-control">
                                            <option value="1920x1080" <?php echo in_array(($settings['system_resolution'] ?? '1920x1080'), ['1920x1080', '100']) ? 'selected' : ''; ?>>1920 x 1080 px — Full HD (Standard Desktop Display)</option>
                                            <option value="1600x900"  <?php echo in_array(($settings['system_resolution'] ?? ''), ['1600x900', '90']) ? 'selected' : ''; ?>>1600 x 900 px — HD+ (Medium Display / Laptops)</option>
                                            <option value="1366x768"  <?php echo in_array(($settings['system_resolution'] ?? ''), ['1366x768', '80']) ? 'selected' : ''; ?>>1366 x 768 px — Standard Laptop Display</option>
                                            <option value="1280x720"  <?php echo in_array(($settings['system_resolution'] ?? ''), ['1280x720', '75']) ? 'selected' : ''; ?>>1280 x 720 px — 720p HD Display</option>
                                            <option value="1024x768"  <?php echo in_array(($settings['system_resolution'] ?? ''), ['1024x768', '70', '110']) ? 'selected' : ''; ?>>1024 x 768 px — POS Touch Terminal / Tablet</option>
                                            <option value="2560x1440" <?php echo in_array(($settings['system_resolution'] ?? ''), ['2560x1440', '115', '125']) ? 'selected' : ''; ?>>2560 x 1440 px — 2K QHD Display</option>
                                            <option value="3840x2160" <?php echo in_array(($settings['system_resolution'] ?? ''), ['3840x2160', '135']) ? 'selected' : ''; ?>>3840 x 2160 px — 4K UHD Large Monitor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shared Unified Submission Save Button Sticky Drawer -->
                <div class="settings-panel-card" style="margin-top: 24px">
                    <div class="settings-actions">
                        <div class="save-hint">
                            <i class="fas fa-info-circle"></i>
                            <span>Alter parameters as desired across any tabs before clicking Save.</span>
                        </div>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Save Global Settings
                        </button>
                    </div>
                </div>

    </form>
</div>

<!-- STANDALONE BACKUP FORMS (Outside main settings form to prevent nested form bugs) -->
<form id="standaloneBackupCreateForm" method="POST" action="<?php echo url('/admin/backup/create'); ?>" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="backup_format" id="standalone_backup_format" value="sql">
    <input type="hidden" name="download_immediately" id="standalone_download_immediately" value="0">
</form>

<form id="standaloneBackupDeleteForm" method="POST" action="<?php echo url('/admin/backup/delete'); ?>" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="filename" id="standalone_delete_filename" value="">
</form>

<form id="standaloneBackupRestoreForm" method="POST" action="<?php echo url('/admin/backup/restore'); ?>" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="filename" id="standalone_restore_filename" value="">
</form>

<form id="standaloneBackupUploadRestoreForm" method="POST" action="<?php echo url('/admin/backup/restore'); ?>" enctype="multipart/form-data" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="file" name="backup_file" id="standalone_upload_file_input">
</form>

<!-- Premium Javascript Tab Switching Controller and V2 Search / Live Previews -->
<script>
// Global Standalone Backup Helper Functions (attached to window for inline onclick handlers)
window.triggerManualBackup = function(format, downloadImmediately) {
    localStorage.setItem("inventory_active_tab_v2", "backup");
    document.getElementById('standalone_backup_format').value = format;
    document.getElementById('standalone_download_immediately').value = downloadImmediately;
    document.getElementById('standaloneBackupCreateForm').submit();
};

window.triggerDeleteBackup = function(filename) {
    if (!confirm("Are you sure you want to delete backup file '" + filename + "'?")) {
        return;
    }
    localStorage.setItem("inventory_active_tab_v2", "backup");
    document.getElementById('standalone_delete_filename').value = filename;
    document.getElementById('standaloneBackupDeleteForm').submit();
};

window.triggerRestoreBackup = function(filename) {
    if (!confirm("WARNING: Restoring database from '" + filename + "' will overwrite active system data. Are you sure you want to proceed?")) {
        return;
    }
    localStorage.setItem("inventory_active_tab_v2", "backup");
    document.getElementById('standalone_restore_filename').value = filename;
    document.getElementById('standaloneBackupRestoreForm').submit();
};

window.triggerUploadRestore = function() {
    const uiInput = document.getElementById('ui_backup_file_input');
    if (!uiInput.files || uiInput.files.length === 0) {
        alert('Please select a .sql or .sqlite backup file first.');
        return;
    }
    if (!confirm('CRITICAL WARNING: Restoring from an uploaded file will overwrite all current system data. Ensure you have a recent backup before continuing. Proceed?')) {
        return;
    }
    localStorage.setItem("inventory_active_tab_v2", "backup");
    const standaloneForm = document.getElementById('standaloneBackupUploadRestoreForm');
    const standaloneInput = document.getElementById('standalone_upload_file_input');
    
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(uiInput.files[0]);
    standaloneInput.files = dataTransfer.files;
    
    standaloneForm.submit();
};

document.addEventListener("DOMContentLoaded", function() {
    const navItems = document.querySelectorAll(".settings-nav-item");
    const panels = document.querySelectorAll(".settings-panel");
    const colorPicker = document.getElementById("theme_color");
    const colorPickerText = document.getElementById("theme_color_text");
    const themePreviewBox = document.getElementById("theme-preview-box");
    const searchInput = document.getElementById("settings-search");

    // Live preview values
    const invoicePrefixInput = document.getElementById("invoice_prefix");
    const invoicePaddingInput = document.getElementById("invoice_number_padding");
    const invoiceFormatSelect = document.getElementById("invoice_number_format");
    const invoiceFormatPreview = document.getElementById("invoice-format-preview");

    // Retrieve last active tab from localStorage if exists
    const lastActiveTab = localStorage.getItem("inventory_active_tab_v2") || "general";
    activateTab(lastActiveTab);

    // Bind tab clicks
    navItems.forEach(item => {
        item.addEventListener("click", function() {
            const targetTab = this.getAttribute("data-tab");
            activateTab(targetTab);
        });
    });

    function activateTab(tabId) {
        // Remove active class from all nav items
        navItems.forEach(item => {
            if (item.getAttribute("data-tab") === tabId) {
                item.classList.add("active");
            } else {
                item.classList.remove("active");
            }
        });

        // Hide all panels, show the targeted one
        panels.forEach(panel => {
            if (panel.id === `panel-${tabId}`) {
                panel.classList.add("active");
            } else {
                panel.classList.remove("active");
            }
        });

        // Persist state in localStorage
        localStorage.setItem("inventory_active_tab_v2", tabId);
    }

    // Keep color picker text field and picker tool values in sync
    if (colorPicker && colorPickerText) {
        colorPicker.addEventListener("input", function() {
            colorPickerText.value = this.value;
            if (themePreviewBox) {
                themePreviewBox.style.backgroundColor = this.value;
            }
        });

        colorPickerText.addEventListener("input", function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
                if (themePreviewBox) {
                    themePreviewBox.style.backgroundColor = this.value;
                }
            }
        });
    }

    // Invoice Format Live Preview Generator
    function updateInvoiceFormatPreview() {
        if (!invoiceFormatPreview) return;
        const prefix = invoicePrefixInput ? invoicePrefixInput.value : 'INV-';
        const paddingVal = invoicePaddingInput ? parseInt(invoicePaddingInput.value) || 5 : 5;
        const format = invoiceFormatSelect ? invoiceFormatSelect.value : 'sequential';
        
        let seqNum = "1".padStart(paddingVal, "0");
        let formatStr = prefix;

        if (format === 'year') {
            formatStr += new Date().getFullYear() + '-' + seqNum;
        } else if (format === 'date') {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            formatStr += `${yyyy}${mm}${dd}-${seqNum}`;
        } else {
            formatStr += seqNum;
        }

        invoiceFormatPreview.textContent = formatStr;
    }

    if (invoicePrefixInput) invoicePrefixInput.addEventListener("input", updateInvoiceFormatPreview);
    if (invoicePaddingInput) invoicePaddingInput.addEventListener("input", updateInvoiceFormatPreview);
    if (invoiceFormatSelect) invoiceFormatSelect.addEventListener("change", updateInvoiceFormatPreview);
    updateInvoiceFormatPreview();

    // V2 Live search function
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            const query = this.value.trim().toLowerCase();
            
            if (query === "") {
                // Remove all hidden/highlight classes
                document.querySelectorAll(".settings-field, .toggle-wrapper, .settings-section, .settings-nav-group, .settings-nav-item").forEach(el => {
                    el.classList.remove("search-hidden", "search-match");
                });
                return;
            }

            // Loop through each tab panel
            panels.forEach(panel => {
                let panelHasMatch = false;
                
                // Scan each section within the panel
                panel.querySelectorAll(".settings-section").forEach(section => {
                    let sectionHasMatch = false;
                    
                    // Scan text fields
                    section.querySelectorAll(".settings-field").forEach(field => {
                        const labelText = field.querySelector("label") ? field.querySelector("label").textContent.toLowerCase() : "";
                        const descriptionText = field.querySelector(".field-hint") ? field.querySelector(".field-hint").textContent.toLowerCase() : "";
                        
                        if (labelText.includes(query) || descriptionText.includes(query)) {
                            field.classList.remove("search-hidden");
                            field.classList.add("search-match");
                            sectionHasMatch = true;
                            panelHasMatch = true;
                        } else {
                            field.classList.add("search-hidden");
                            field.classList.remove("search-match");
                        }
                    });

                    // Scan toggles
                    section.querySelectorAll(".toggle-wrapper").forEach(toggle => {
                        const labelText = toggle.querySelector(".toggle-label") ? toggle.querySelector(".toggle-label").textContent.toLowerCase() : "";
                        const descriptionText = toggle.querySelector(".toggle-description") ? toggle.querySelector(".toggle-description").textContent.toLowerCase() : "";
                        
                        if (labelText.includes(query) || descriptionText.includes(query)) {
                            toggle.classList.remove("search-hidden");
                            toggle.classList.add("search-match");
                            sectionHasMatch = true;
                            panelHasMatch = true;
                        } else {
                            toggle.classList.add("search-hidden");
                            toggle.classList.remove("search-match");
                        }
                    });

                    if (sectionHasMatch) {
                        section.classList.remove("search-hidden");
                    } else {
                        section.classList.add("search-hidden");
                    }
                });

                // Hide/show tab navigation link based on match
                const tabId = panel.id.replace("panel-", "");
                const navLink = document.querySelector(`.settings-nav-item[data-tab="${tabId}"]`);
                
                if (navLink) {
                    if (panelHasMatch) {
                        navLink.classList.remove("search-hidden");
                    } else {
                        navLink.classList.add("search-hidden");
                    }
                }
            });

            // Hide nav groups if all items in the group are hidden
            document.querySelectorAll(".settings-nav-group").forEach(group => {
                const itemsCount = group.querySelectorAll(".settings-nav-item").length;
                const hiddenItemsCount = group.querySelectorAll(".settings-nav-item.search-hidden").length;
                
                if (itemsCount === hiddenItemsCount) {
                    group.classList.add("search-hidden");
                } else {
                    group.classList.remove("search-hidden");
                }
            });
        });
    }
});
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
