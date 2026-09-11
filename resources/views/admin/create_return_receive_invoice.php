<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
?>

<style>
    /* Hide spin buttons for number inputs */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield; /* Firefox */
    }

    /* Workstation Grid Layout Fix for Viewport Fit */
    .pos-container {
        max-width: 100%;
        overflow-x: hidden;
    }

    .pos-workstation-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .pos-workstation-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Return Live Badge */
    .pos-terminal-badge.return-active {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #ef4444;
    }

    .pos-live-dot.return-dot {
        background-color: #ef4444;
        box-shadow: 0 0 8px #ef4444;
    }

    /* 2-Column Context Card Styling */
    .pos-context-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: var(--card-shadow);
    }

    .pos-context-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--surface-border);
    }

    .pos-context-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-color);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .recv-context-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }

    .recv-context-col {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    @media (max-width: 768px) {
        .recv-context-grid-2col {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    /* Input Icon Alignment inside Context Card */
    .pos-context-card .pos-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .pos-context-card .pos-input-icon {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        font-size: 0.95rem;
        pointer-events: none;
        z-index: 10;
    }

    .pos-context-card .pos-input-field {
        width: 100%;
        height: 42px;
        padding: 8px 14px 8px 38px !important;
        background: var(--surface-color);
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        color: var(--text-color);
        font-size: 0.95rem;
        font-weight: 500;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }

    .pos-context-card .pos-input-field:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: var(--focus-ring);
        background: var(--card-bg);
    }

    /* Fetch Button inside Reference Input */
    .btn-fetch-ref {
        padding: 0 16px;
        height: 42px;
        background: rgba(var(--primary-rgb, 99, 102, 241), 0.12);
        border: 1px solid rgba(var(--primary-rgb, 99, 102, 241), 0.3);
        color: var(--primary-color);
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-fetch-ref:hover {
        background: var(--primary-color);
        color: #ffffff;
    }

    /* TomSelect Alignment inside Context Card */
    .pos-context-card .ts-wrapper {
        width: 100%;
    }

    .pos-context-card .ts-control {
        background: var(--surface-color) !important;
        border: 1px solid var(--surface-border) !important;
        border-radius: 8px !important;
        color: var(--text-color) !important;
        font-size: 0.95rem !important;
        padding: 8px 14px 8px 38px !important;
        min-height: 42px !important;
        height: 42px !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
        position: relative !important;
        top: -8px !important;
        left: -39px !important;
        width: 110.3% !important;
    }

    .pos-context-card .ts-wrapper.focus .ts-control {
        border-color: var(--primary-color) !important;
        box-shadow: var(--focus-ring) !important;
        background: var(--card-bg) !important;
    }

    .pos-context-card .ts-control input {
        color: var(--text-color) !important;
        font-size: 0.95rem !important;
    }

    .pos-context-card .ts-control .item {
        color: var(--text-color) !important;
        font-weight: 500 !important;
    }

    /* Fixed Table Layout & Min Width for Browser Stability */
    .recv-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        background: var(--card-bg);
    }

    .recv-table {
        width: 100%;
        min-width: 820px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .recv-table th {
        background: var(--surface-color);
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 8px;
        border-bottom: 1px solid var(--surface-border);
        text-align: left;
        white-space: nowrap;
    }

    .recv-table td {
        padding: 6px 8px;
        border-bottom: 1px solid var(--surface-border);
        vertical-align: middle;
        background: var(--card-bg);
    }

    .recv-table tr:last-child td {
        border-bottom: none;
    }

    .recv-table tr:hover td {
        background: var(--hover-bg);
    }

    /* Specific Cell Minimum Width Controls */
    .col-medicine { width: 26%; min-width: 180px; }
    .col-batch { width: 12%; min-width: 95px; }
    .col-expiry { width: 13%; min-width: 115px; }
    .col-currcost { width: 9%; min-width: 75px; }
    .col-recvcost { width: 10%; min-width: 85px; }
    .col-disc { width: 8%; min-width: 65px; }
    .col-qty { width: 7%; min-width: 60px; }
    .col-subtotal { width: 10%; min-width: 80px; text-align: right; }
    .col-action { width: 5%; min-width: 35px; text-align: center; }

    /* Input Styling Adjustments for Compact Fit */
    .pos-cell-input {
        padding: 6px 8px;
        font-size: 0.85rem;
    }

    /* TomSelect Dropdown Alignment */
    .recv-table .ts-control {
        background: var(--surface-color) !important;
        border: 1px solid var(--surface-border) !important;
        border-radius: 6px !important;
        color: var(--text-color) !important;
        font-size: 0.85rem !important;
        padding: 4px 8px !important;
        min-height: 34px !important;
        box-shadow: none !important;
    }

    .ts-dropdown {
        background: var(--dropdown-bg, #1e293b) !important;
        border: 1px solid var(--dropdown-border, #334155) !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3) !important;
        z-index: 9999 !important;
    }
    .ts-dropdown .option {
        color: var(--text-color) !important;
        padding: 8px 12px !important;
    }
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background: var(--primary-color) !important;
        color: #ffffff !important;
    }

    /* Summary Card Styling Fix */
    .pos-summary-card {
        padding: 18px;
        gap: 14px;
        width: 100%;
        box-sizing: border-box;
    }

    .btn-pos-primary, .btn-pos-secondary {
        white-space: normal;
        font-size: 0.9rem;
    }

    .btn-pos-danger {
        background: #ef4444 !important;
        color: white !important;
        box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.39) !important;
    }
    .btn-pos-danger:hover {
        background: #dc2626 !important;
    }
</style>

<div class="dashboard-container">
    <div class="pos-container">
        
        <!-- POS Header Bar -->
        <div class="pos-header-bar">
            <div class="pos-header-title">
                <h1>Return Stock to Supplier</h1>
                <div class="pos-terminal-badge return-active">
                    <span class="pos-live-dot return-dot"></span>
                    Stock Return Active
                </div>
            </div>

            <div class="pos-shortcuts-legend">
                <div class="shortcut-pill" onclick="openHotkeyModal()" style="cursor: pointer; background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.35); color: #3b82f6;" title="View all keyboard shortcuts">
                    <span class="shortcut-key">F1</span> Shortcuts
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">F2</span> Add Item
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">F9</span> Process Return
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">Esc</span> Clear Form
                </div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="receiveInvoiceForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <!-- Delivery & Supplier Context Card (2-Column Layout) -->
            <div class="pos-context-card" style="margin-bottom: 20px;">
                <div class="pos-context-header">
                    <div class="pos-context-title">
                        <i class="fas fa-undo" style="color: #ef4444;"></i> Supplier Return Context
                    </div>
                    <div class="pos-meta-chip" style="padding: 4px 12px; font-size: 0.8rem;">
                        <span><i class="far fa-clock"></i> <strong id="livePosClock"><?php echo date('H:i'); ?></strong></span>
                        <span style="margin-left: 10px;"><i class="far fa-calendar-alt"></i> <strong><?php echo date('d M Y'); ?></strong></span>
                    </div>
                </div>

                <div class="recv-context-grid-2col">
                    <!-- Left Column: Supplier Name & Supplier Reference Invoice # -->
                    <div class="recv-context-col">
                        <div class="pos-field-group">
                            <label for="supplier_id" class="pos-field-label">
                                Supplier Name <span class="text-danger">*</span>
                            </label>
                            <div class="pos-input-wrapper">
                                <i class="fas fa-truck pos-input-icon"></i>
                                <select name="supplier_id" id="supplier_id" class="pos-input-field" required>
                                    <option value="">Search & Select Supplier...</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <?php if ($sup['status'] == 'active'): ?>
                                            <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="pos-field-group">
                            <label for="reference_number" class="pos-field-label">
                                Supplier's Ref. Invoice # <span class="text-danger">*</span>
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <div class="pos-input-wrapper" style="flex: 1;">
                                    <i class="fas fa-receipt pos-input-icon"></i>
                                    <input type="text" name="reference_number" id="reference_number" class="pos-input-field" required placeholder="Enter original bill or receipt #" onkeydown="if(event.key==='Enter'){event.preventDefault(); fetchReferenceInvoice();}">
                                </div>
                                <button type="button" class="btn-fetch-ref" onclick="fetchReferenceInvoice()">
                                    <i class="fas fa-sync-alt"></i> Fetch
                                </button>
                            </div>
                            <small class="text-muted" style="margin-top: 2px;">Enter original invoice number to auto-fill return items</small>
                        </div>
                    </div>

                    <!-- Right Column: Return Invoice Number & Received Date -->
                    <div class="recv-context-col">
                        <div class="pos-field-group">
                            <label for="invoice_number" class="pos-field-label">
                                Return Invoice Number
                            </label>
                            <div class="pos-input-wrapper">
                                <i class="fas fa-barcode pos-input-icon"></i>
                                <input type="text" id="invoice_number" class="pos-input-field" value="<?php echo htmlspecialchars($nextInvoiceNumber); ?>" readonly tabIndex="-1" style="background-color: var(--surface-hover); font-weight: 700; letter-spacing: 0.5px; color: #ef4444;">
                            </div>
                        </div>

                        <div class="pos-field-group">
                            <label for="received_date" class="pos-field-label">
                                Return Date <span class="text-danger">*</span>
                            </label>
                            <div class="pos-input-wrapper">
                                <i class="far fa-calendar-check pos-input-icon"></i>
                                <input type="date" name="received_date" id="received_date" class="pos-input-field" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Workstation Layout (Items Table + Invoice Summary Panel) -->
            <div class="pos-workstation-grid">
                
                <!-- Items Table Card -->
                <div class="pos-items-card">
                    <div class="pos-items-header">
                        <div class="pos-items-title">
                            <i class="fas fa-boxes" style="color: #ef4444;"></i> Returned Medicine Items 
                            <span class="pos-count-badge" id="posItemCount" style="background-color: #ef4444;">0</span>
                        </div>
                        <button type="button" class="btn-pos-outline" style="padding: 4px 10px; font-size: 0.8rem;" onclick="confirmClearForm()">
                            <i class="fas fa-trash-alt"></i> Clear Items
                        </button>
                    </div>

                    <div class="recv-table-wrapper">
                        <table class="recv-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th class="col-medicine">Medicine <span class="text-danger">*</span></th>
                                    <th class="col-batch">Batch No.</th>
                                    <th class="col-expiry">Expiry Date</th>
                                    <th class="col-currcost">Curr. Cost</th>
                                    <th class="col-recvcost">Recv. Cost <span class="text-danger">*</span></th>
                                    <th class="col-disc">Disc.(%)</th>
                                    <th class="col-qty">Qty <span class="text-danger">*</span></th>
                                    <th class="col-subtotal">Subtotal</th>
                                    <th class="col-action"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItems">
                                <!-- Dynamic items rows -->
                            </tbody>
                        </table>

                        <!-- Empty state placeholder -->
                        <div class="pos-empty-state" id="posEmptyState" style="display: none;">
                            <div class="pos-empty-icon"><i class="fas fa-undo-alt" style="color: #ef4444;"></i></div>
                            <p style="margin:0; font-weight: 600;">No returned items added yet</p>
                            <p style="margin:4px 0 0 0; font-size: 0.85rem;">Click "Add Item Row" or press F2 to begin</p>
                        </div>
                    </div>
                    
                    <button type="button" class="pos-btn-add-row" onclick="addRow()">
                        <i class="fas fa-plus-circle"></i> Add Item Row <span style="font-size:0.75rem; opacity:0.8;">(F2)</span>
                    </button>
                </div>

                <!-- Right Side Summary Card -->
                <?php 
                $showTax = false;
                $showAdminOverride = false;
                $closeUrl = URL_ROOT . '/admin/receive_invoices';
                $saveBtnText = 'Process Return';
                $saveBtnClass = 'btn-pos-danger';
                require BASE_PATH . '/resources/views/admin/partials/invoice_summary.php'; 
                ?>

            </div>
        </form>
    </div>
</div>

<script>
    const products = <?php echo $productsJson; ?>;
    let supplierTomSelect = null;

    // Live clock updates
    function updateClock() {
        const clockEl = document.getElementById('livePosClock');
        if (clockEl) {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }
    setInterval(updateClock, 1000);

    function updateItemCountBadge() {
        const rows = document.querySelectorAll('#invoiceItems tr');
        const countBadge = document.getElementById('posItemCount');
        const emptyState = document.getElementById('posEmptyState');
        
        if (countBadge) countBadge.textContent = rows.length;
        if (emptyState) {
            emptyState.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    // Helper to generate dynamic rows
    function addRow() {
        const tbody = document.getElementById('invoiceItems');
        const rowId = 'row_' + Date.now();
        
        let options = '<option value="">Select Medicine</option>';
        products.forEach(p => {
            const cost = parseFloat(p.trad_price) > 0 ? p.trad_price : p.cost_price;
            let text = p.name;
            if (p.generic_name) text += ` (${p.generic_name})`;
            if (p.strength) text += ` - ${p.strength}`;
            options += `<option value="${p.id}" data-cost="${cost}">${text}</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td class="col-medicine">
                <select name="product_id[]" class="pos-cell-input product-select" required onchange="updateRow('${rowId}')">
                    ${options}
                </select>
            </td>
            <td class="col-batch"><input type="text" name="batch_number[]" class="pos-cell-input batch-input" placeholder="e.g. BT-2024"></td>
            <td class="col-expiry"><input type="date" name="expiry_date[]" class="pos-cell-input expiry-input" style="min-width: 110px;"></td>
            <td class="col-currcost"><input type="number" class="pos-cell-input current-cost" readonly tabIndex="-1" value="0.00" step="0.01"></td>
            <td class="col-recvcost"><input type="number" name="cost_price[]" class="pos-cell-input cost-input" required min="0" step="0.01" oninput="calculateRow('${rowId}')"></td>
            <td class="col-disc"><input type="number" name="item_discount[]" class="pos-cell-input item-discount-input" min="0" max="100" step="0.01" value="0.00" oninput="calculateRow('${rowId}')"></td>
            <td class="col-qty"><input type="number" name="quantity[]" class="pos-cell-input qty-input" required min="1" oninput="calculateRow('${rowId}')"></td>
            <td class="col-subtotal"><input type="number" class="pos-cell-input subtotal-input subtotal-val" readonly tabIndex="-1" value="0.00" step="0.01" style="text-align: right;"></td>
            <td class="col-action" style="text-align: center;">
                <button type="button" class="pos-btn-delete" title="Remove line item" onclick="removeRow(this)" style="margin: 0 auto;">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        
        if (typeof TomSelect !== 'undefined') {
            const tomInst = new TomSelect(tr.querySelector('.product-select'), {
                create: false,
                dropdownParent: 'body',
                sortField: {field: "text", direction: "asc"}
            });
            setTimeout(() => tomInst.focus(), 100);
        }
        updateItemCountBadge();
    }

    function updateRow(rowId) {
        const row = document.getElementById(rowId);
        const select = row.querySelector('.product-select');
        const currentCostInput = row.querySelector('.current-cost');
        const costInput = row.querySelector('.cost-input');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
            currentCostInput.value = cost.toFixed(2);
            if (!costInput.value || costInput.value === 'NaN' || costInput.value === '0.00' || costInput.value === '0') {
                costInput.value = cost.toFixed(2);
            }
            const qtyInput = row.querySelector('.qty-input');
            if (qtyInput && !qtyInput.value) {
                qtyInput.value = 1;
            }
            calculateRow(rowId);
        } else {
            currentCostInput.value = '0.00';
            costInput.value = '';
            row.querySelector('.subtotal-input').value = '0.00';
            calculateTotal();
        }
    }

    function calculateRow(rowId) {
        const row = document.getElementById(rowId);
        const costInput = row.querySelector('.cost-input');
        const qtyInput = row.querySelector('.qty-input');
        const discountInput = row.querySelector('.item-discount-input');
        const subtotalInput = row.querySelector('.subtotal-input');
        
        const cost = parseFloat(costInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 0;
        const discountPct = parseFloat(discountInput.value) || 0;
        
        let subtotal = cost * qty;
        if (discountPct > 0) {
            subtotal = subtotal - (subtotal * (discountPct / 100));
        }
        subtotalInput.value = subtotal.toFixed(2);
        
        calculateTotal();
    }

    function removeRow(button) {
        const row = button.closest('tr');
        row.remove();
        calculateTotal();
        updateItemCountBadge();
    }

    function confirmClearForm() {
        if (confirm('Are you sure you want to clear all items from this return invoice?')) {
            document.getElementById('invoiceItems').innerHTML = '';
            calculateTotal();
            updateItemCountBadge();
        }
    }

    function calculateTotal() {
        let netAmount = 0;
        let itemDiscountTotal = 0;
        let subtotal = 0;
        
        document.querySelectorAll('#invoiceItems tr').forEach(row => {
            const costInput = row.querySelector('.cost-input');
            const discountInput = row.querySelector('.item-discount-input');
            const qtyInput = row.querySelector('.qty-input');

            if (!costInput || !qtyInput) return;

            const price = parseFloat(costInput.value) || 0;
            const discountPct = parseFloat(discountInput ? discountInput.value : 0) || 0;
            const qty = parseFloat(qtyInput.value) || 0;
            
            const rowGross = price * qty;
            const rowDiscount = rowGross * (discountPct / 100);
            const rowNet = rowGross - rowDiscount;
            
            netAmount += rowGross;
            itemDiscountTotal += rowDiscount;
            subtotal += rowNet;
        });
        
        const taxRate = parseFloat(document.getElementById('taxRate')?.value) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        
        const grandTotal = subtotal + taxAmount;
        
        const netEl = document.getElementById('netAmount');
        const discEl = document.getElementById('totalDiscount');
        const subEl = document.getElementById('subTotal');
        const taxEl = document.getElementById('taxAmount');
        const grandEl = document.getElementById('grandTotal');

        if (netEl) netEl.textContent = netAmount.toFixed(2);
        if (discEl) discEl.textContent = itemDiscountTotal.toFixed(2);
        if (subEl) subEl.textContent = subtotal.toFixed(2);
        if (taxEl) taxEl.textContent = taxAmount.toFixed(2);
        if (grandEl) grandEl.textContent = Math.round(grandTotal);
    }

    // Auto-fill original invoice details logic
    function fetchReferenceInvoice() {
        const num = document.getElementById('reference_number').value.trim();
        if (!num) {
            alert("Please enter a reference invoice number to fetch.");
            return;
        }
        
        fetch('<?php echo URL_ROOT; ?>/api/receive_invoice/details?invoice_number=' + encodeURIComponent(num) + '&id=' + encodeURIComponent(num))
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || "Original receive invoice not found.");
                    return;
                }
                
                if (data.invoice && data.invoice.supplier_id) {
                    const supEl = document.getElementById('supplier_id');
                    supEl.value = data.invoice.supplier_id;
                    if (supplierTomSelect) {
                        supplierTomSelect.setValue(data.invoice.supplier_id);
                    }
                }
                
                document.getElementById('invoiceItems').innerHTML = '';
                
                if (!data.items || data.items.length === 0) {
                    addRow();
                } else {
                    data.items.forEach(item => {
                        const tbody = document.getElementById('invoiceItems');
                        const rowId = 'row_' + Date.now() + Math.random().toString(36).substr(2, 9);
                        
                        let options = '<option value="">Select Medicine</option>';
                        products.forEach(p => {
                            const isSelected = (p.id == item.product_id) ? 'selected' : '';
                            const cost = parseFloat(p.trad_price) > 0 ? p.trad_price : p.cost_price;
                            let text = p.name;
                            if (p.generic_name) text += ` (${p.generic_name})`;
                            if (p.strength) text += ` - ${p.strength}`;
                            options += `<option value="${p.id}" data-cost="${cost}" ${isSelected}>${text}</option>`;
                        });
                        
                        const qty = Math.abs(parseFloat(item.quantity) || 0);
                        const costPrice = Math.abs(parseFloat(item.cost_price) || 0);

                        const tr = document.createElement('tr');
                        tr.id = rowId;
                        tr.innerHTML = `
                            <td class="col-medicine">
                                <select name="product_id[]" class="pos-cell-input product-select" required onchange="updateRow('${rowId}')">
                                    ${options}
                                </select>
                            </td>
                            <td class="col-batch"><input type="text" name="batch_number[]" class="pos-cell-input batch-input" placeholder="e.g. BT-2024" value="${item.batch_number || ''}"></td>
                            <td class="col-expiry"><input type="date" name="expiry_date[]" class="pos-cell-input expiry-input" style="min-width: 110px;" value="${item.expiry_date || ''}"></td>
                            <td class="col-currcost"><input type="number" class="pos-cell-input current-cost" readonly tabIndex="-1" value="${costPrice.toFixed(2)}" step="0.01"></td>
                            <td class="col-recvcost"><input type="number" name="cost_price[]" class="pos-cell-input cost-input" required min="0" step="0.01" value="${costPrice.toFixed(2)}" oninput="calculateRow('${rowId}')"></td>
                            <td class="col-disc"><input type="number" name="item_discount[]" class="pos-cell-input item-discount-input" min="0" max="100" step="0.01" value="${parseFloat(item.discount || 0).toFixed(2)}" oninput="calculateRow('${rowId}')"></td>
                            <td class="col-qty"><input type="number" name="quantity[]" class="pos-cell-input qty-input" required min="1" value="${qty}" oninput="calculateRow('${rowId}')"></td>
                            <td class="col-subtotal"><input type="number" class="pos-cell-input subtotal-input subtotal-val" readonly tabIndex="-1" value="0.00" step="0.01" style="text-align: right;"></td>
                            <td class="col-action" style="text-align: center;">
                                <button type="button" class="pos-btn-delete" title="Remove line item" onclick="removeRow(this)" style="margin: 0 auto;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                        calculateRow(rowId);
                        
                        if (typeof TomSelect !== 'undefined') {
                            new TomSelect(tr.querySelector('.product-select'), {
                                create: false,
                                dropdownParent: 'body',
                                sortField: {field: "text", direction: "asc"}
                            });
                        }
                    });
                }
                calculateTotal();
                updateItemCountBadge();
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while fetching original invoice details.");
            });
    }

    // Keyboard Shortcuts Engine
    document.addEventListener('keydown', function(e) {
        // F2 -> Add medicine row
        if (e.key === 'F2') {
            e.preventDefault();
            addRow();
        }
        // F9 -> Submit Form (Process Return)
        else if (e.key === 'F9') {
            e.preventDefault();
            const form = document.getElementById('receiveInvoiceForm');
            if (form) {
                if (form.reportValidity()) {
                    form.submit();
                }
            }
        }
        // Esc -> Confirm clear form
        else if (e.key === 'Escape') {
            confirmClearForm();
        }
    });

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Supplier TomSelect if available
        const supplierSelect = document.getElementById('supplier_id');
        if (supplierSelect && typeof TomSelect !== 'undefined') {
            supplierTomSelect = new TomSelect(supplierSelect, {
                create: false,
                dropdownParent: 'body',
                placeholder: 'Search & Select Supplier...',
                sortField: {field: "text", direction: "asc"}
            });
        }

        addRow();
        
        // Prevent form submission with empty rows or zero total
        document.getElementById('receiveInvoiceForm').addEventListener('submit', function(e) {
            const rowCount = document.querySelectorAll('#invoiceItems tr').length;
            if (rowCount === 0) {
                alert("Please add at least one medicine item to return.");
                e.preventDefault();
                return;
            }
            
            const total = parseFloat(document.getElementById('grandTotal').textContent) || 0;
            if (total <= 0) {
                const selectCount = Array.from(document.querySelectorAll('.product-select')).filter(s => s.value !== '').length;
                if (selectCount === 0) {
                    alert("Please select medicine in the rows.");
                    e.preventDefault();
                }
            }
        });
    });
</script>

<?php require_once BASE_PATH . '/resources/views/admin/partials/hotkey_overlay.php'; ?>
<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>
