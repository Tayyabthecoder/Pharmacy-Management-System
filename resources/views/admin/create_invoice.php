<?php
require_once BASE_PATH . '/resources/views/layouts/header.php';
require_once BASE_PATH . '/resources/views/layouts/sidebar.php';
require_once BASE_PATH . '/resources/views/layouts/topbar.php';
$role = $_SESSION['role'] ?? 'salesman';
$rolePath = $role === 'admin' ? 'admin' : 'salesman';
?>

<div class="dashboard-container">
    <div class="pos-container">
        
        <!-- POS Header Bar -->
        <div class="pos-header-bar">
            <div class="pos-header-title">
                <h1>Pharmacy Sales POS <?php echo $role === 'admin' ? '<span style="font-size:0.8rem; opacity:0.7;">(Admin)</span>' : ''; ?></h1>
                <div class="pos-terminal-badge">
                    <span class="pos-live-dot"></span>
                    Terminal Active
                </div>
            </div>

            <div class="pos-shortcuts-legend">
                <div class="shortcut-pill">
                    <span class="shortcut-key">F2</span> Add Item
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">F9</span> Complete & Print
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">Esc</span> Clear Form
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 0;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="invoiceForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <!-- Patient & Sale Context Card -->
            <div class="pos-context-card" style="margin-bottom: 20px;">
                <div class="pos-context-grid">
                    <div class="pos-field-group">
                        <label for="customer_name" class="pos-field-label">
                            <i class="fas fa-user-injured" style="color: var(--primary-color);"></i> Patient / Customer Name <span class="text-danger">*</span>
                        </label>
                        <div class="pos-input-wrapper">
                            <i class="fas fa-user pos-input-icon"></i>
                            <input type="text" name="customer_name" id="customer_name" class="pos-input-field" placeholder="Enter Patient Name" value="" required>
                        </div>
                    </div>

                    <div class="pos-field-group">
                        <div class="pos-meta-chip">
                            <span><i class="far fa-calendar-alt"></i> Date: <strong><?php echo date('d M Y'); ?></strong></span>
                            <span><i class="far fa-clock"></i> <strong id="livePosClock"><?php echo date('H:i'); ?></strong></span>
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
                            <i class="fas fa-pills" style="color: var(--primary-color);"></i> Order Medicine Items 
                            <span class="pos-count-badge" id="posItemCount">0</span>
                        </div>
                        <button type="button" class="btn-pos-outline" style="padding: 4px 10px; font-size: 0.8rem;" onclick="confirmClearForm()">
                            <i class="fas fa-trash-alt"></i> Clear Items
                        </button>
                    </div>

                    <div class="pos-table-wrapper">
                        <table class="pos-table" id="invoiceTable">
                            <thead>
                                <tr>
                                    <th width="36%">Medicine</th>
                                    <th width="11%">Batch</th>
                                    <th width="8%">Stock</th>
                                    <th width="11%">Unit Price</th>
                                    <th width="9%">Disc.(%)</th>
                                    <th width="10%">Qty</th>
                                    <th width="11%" style="text-align: right;">Subtotal</th>
                                    <th width="4%"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItems">
                                <!-- Items rows dynamically added here -->
                            </tbody>
                        </table>

                        <!-- Empty state placeholder -->
                        <div class="pos-empty-state" id="posEmptyState" style="display: none;">
                            <div class="pos-empty-icon"><i class="fas fa-shopping-basket"></i></div>
                            <p style="margin:0; font-weight: 600;">No medicine items added yet</p>
                            <p style="margin:4px 0 0 0; font-size: 0.85rem;">Click "Add Medicine Item" or press F2 to begin</p>
                        </div>
                    </div>
                    
                    <button type="button" class="pos-btn-add-row" onclick="addRow()">
                        <i class="fas fa-plus-circle"></i> Add Medicine Item <span style="font-size:0.75rem; opacity:0.8;">(F2)</span>
                    </button>
                </div>

                <!-- Right Side Summary Card -->
                <?php 
                $showTax = true;
                $showAdminOverride = true;
                $closeUrl = URL_ROOT . '/' . ($rolePath ?? 'admin') . '/invoices';
                $saveBtnText = 'Complete Sale';
                require BASE_PATH . '/resources/views/admin/partials/invoice_summary.php'; 
                ?>

            </div>
        </form>
    </div>
</div>

<script>
    const products = <?php echo $productsJson; ?>;
    const isSalesman = <?php echo $role === 'salesman' ? 'true' : 'false'; ?>;
    const salesmanCanDiscount = <?php echo !empty($settings['salesman_can_give_discount']) ? 'true' : 'false'; ?>;
    const maxSalesmanDiscount = parseFloat("<?php echo $settings['max_salesman_discount'] ?? 100; ?>");
    const threshold = parseFloat("<?php echo $settings['approval_threshold_amount'] ?? 5000; ?>");
    const requireApproval = <?php echo (!empty($settings['require_admin_approval']) && $role !== 'admin') ? 'true' : 'false'; ?>;

    // Live Clock Updates
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('livePosClock');
        if (clockEl) {
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

    function addRow() {
        const tbody = document.getElementById('invoiceItems');
        const rowId = 'row_' + Date.now();
        
        let options = '<option value="">Select Medicine</option>';
        products.forEach(p => {
            const isExpired = p.expiry_date ? (new Date(p.expiry_date + 'T23:59:59') <= new Date()) : false;
            const expText = isExpired ? ' [EXPIRED]' : '';
            options += `<option value="${p.id}" data-price="${p.price}" data-batch="${p.batch_number || '-'}" data-discount="${p.discount_amount || '0'}" data-stock="${p.quantity}" ${isExpired ? 'disabled style="color: #ef4444"' : ''}>${p.name}${expText}</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td>
                <select name="product_id[]" class="pos-cell-input product-select" required onchange="updateRow('${rowId}')">
                    ${options}
                </select>
            </td>
            <td><input type="text" class="pos-cell-input batch-input" readonly tabIndex="-1" placeholder="-"></td>
            <td>
                <span class="pos-stock-tag" id="${rowId}_stock_tag">-</span>
                <input type="hidden" class="stock-input">
            </td>
            <td><input type="number" class="pos-cell-input retail-price-input" step="0.01" readonly tabIndex="-1"></td>
            <td><input type="number" class="pos-cell-input discount-input" step="1" min="0" max="100" value="0" oninput="calculateRow('${rowId}')" ${isSalesman && !salesmanCanDiscount ? 'readonly' : ''}></td>
            <td><input type="number" name="quantity[]" class="pos-cell-input qty-input" min="1" required oninput="calculateRow('${rowId}')" onfocus="this.select()"></td>
            <td>
                <input type="hidden" name="price[]" class="effective-price-input">
                <input type="number" class="pos-cell-input subtotal-input subtotal-val" readonly tabIndex="-1">
            </td>
            <td>
                <button type="button" class="pos-btn-delete" title="Remove line item" onclick="removeRow(this)">
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
        const batchInput = row.querySelector('.batch-input');
        const priceInput = row.querySelector('.retail-price-input');
        const discountInput = row.querySelector('.discount-input');
        const stockHidden = row.querySelector('.stock-input');
        const stockTag = document.getElementById(rowId + '_stock_tag');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const stockQty = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
            batchInput.value = selectedOption.getAttribute('data-batch') || '-';
            priceInput.value = selectedOption.getAttribute('data-price');
            discountInput.value = '0';
            stockHidden.value = stockQty;
            
            if (stockTag) {
                stockTag.textContent = stockQty;
                stockTag.className = 'pos-stock-tag ' + (stockQty <= 10 ? 'pos-stock-low' : 'pos-stock-healthy');
            }

            const qtyInput = row.querySelector('.qty-input');
            if (qtyInput && !qtyInput.value) {
                qtyInput.value = 1;
            }
            calculateRow(rowId);
        } else {
            batchInput.value = '';
            priceInput.value = '';
            discountInput.value = '0';
            stockHidden.value = '';
            if (stockTag) {
                stockTag.textContent = '-';
                stockTag.className = 'pos-stock-tag';
            }
            row.querySelector('.subtotal-input').value = '';
            row.querySelector('.effective-price-input').value = '';
            calculateTotal();
        }
    }

    function calculateRow(rowId) {
        const row = document.getElementById(rowId);
        const price = parseFloat(row.querySelector('.retail-price-input').value) || 0;
        let discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const stock = parseFloat(row.querySelector('.stock-input').value) || 0;
        
        if (qty > stock) {
            alert('Quantity exceeds available shelf stock! (Available: ' + stock + ')');
            row.querySelector('.qty-input').value = stock;
            return calculateRow(rowId);
        }
        
        if (isSalesman && salesmanCanDiscount && discount > maxSalesmanDiscount) {
            alert('Maximum allowed discount is ' + maxSalesmanDiscount + '%');
            discount = maxSalesmanDiscount;
            row.querySelector('.discount-input').value = discount;
        }

        const effectivePrice = Math.max(0, price * (1 - (discount / 100)));
        const subtotal = effectivePrice * qty;
        
        row.querySelector('.effective-price-input').value = effectivePrice.toFixed(2);
        row.querySelector('.subtotal-input').value = subtotal.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        let netAmount = 0;
        let itemDiscountTotal = 0;
        let subtotal = 0;
        
        document.querySelectorAll('#invoiceItems tr').forEach(row => {
            const price = parseFloat(row.querySelector('.retail-price-input').value) || 0;
            const discountPct = parseFloat(row.querySelector('.discount-input').value) || 0;
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            
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
        if (grandEl) grandEl.textContent = grandTotal.toFixed(2);

        // Handle Admin approval threshold
        const approvalSection = document.getElementById('adminApprovalSection');
        if (approvalSection) {
            if (requireApproval && grandTotal > threshold) {
                approvalSection.style.display = 'block';
                approvalSection.querySelectorAll('input').forEach(input => input.setAttribute('required', 'required'));
            } else {
                approvalSection.style.display = 'none';
                approvalSection.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
            }
        }
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotal();
        updateItemCountBadge();
    }

    function confirmClearForm() {
        if (confirm('Are you sure you want to clear all items from this invoice?')) {
            document.getElementById('invoiceItems').innerHTML = '';
            calculateTotal();
            updateItemCountBadge();
            const custInput = document.getElementById('customer_name');
            if (custInput) custInput.value = '';
        }
    }

    // Keyboard POS Shortcuts Engine
    document.addEventListener('keydown', function(e) {
        // F2 -> Add medicine row
        if (e.key === 'F2') {
            e.preventDefault();
            addRow();
        }
        // F9 -> Submit Form (Complete & Print Sale)
        else if (e.key === 'F9') {
            e.preventDefault();
            const form = document.getElementById('invoiceForm');
            if (form) {
                const hiddenAction = document.createElement('input');
                hiddenAction.type = 'hidden';
                hiddenAction.name = 'action';
                hiddenAction.value = 'print';
                form.appendChild(hiddenAction);
                if (form.reportValidity()) {
                    form.submit();
                }
            }
        }
        // Esc -> Confirm clear form (if no active dropdown/modal)
        else if (e.key === 'Escape') {
            const openDropdown = document.querySelector('.ts-dropdown:not([style*="display: none"])');
            const openModal = document.querySelector('.modal[style*="display: block"]');
            if (!openDropdown && !openModal) {
                confirmClearForm();
            }
        }
    });

    // Add initial row on load
    window.onload = function() {
        addRow();
        const custInput = document.getElementById('customer_name');
        if (custInput) custInput.focus();
    };
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>

