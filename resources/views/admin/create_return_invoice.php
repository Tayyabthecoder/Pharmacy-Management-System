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
                <h1>Customer Return POS <?php echo $role === 'admin' ? '<span style="font-size:0.8rem; opacity:0.7;">(Admin)</span>' : ''; ?></h1>
                <div class="pos-terminal-badge" style="background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.25); color: #ef4444;">
                    <span class="pos-live-dot" style="background-color: #ef4444; box-shadow: 0 0 8px #ef4444;"></span>
                    Return Terminal Active
                </div>
            </div>

            <div class="pos-shortcuts-legend">
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 0;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="invoiceForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <!-- Patient & Reference Search Context Card -->
            <div class="pos-context-card" style="margin-bottom: 20px;">
                <div class="pos-context-grid" style="grid-template-columns: 1fr 1.5fr 250px;">
                    <div class="pos-field-group">
                        <label for="reference_invoice_id" class="pos-field-label">
                            <i class="fas fa-receipt" style="color: var(--primary-color);"></i> Original Invoice ID
                        </label>
                        <div class="pos-input-wrapper">
                            <i class="fas fa-search pos-input-icon"></i>
                            <input type="number" id="reference_invoice_id" class="pos-input-field" placeholder="Enter Invoice ID" value="" onkeydown="if(event.key==='Enter'){event.preventDefault(); fetchReferenceInvoice();}">
                            <button type="button" class="pos-quick-btn" style="position: absolute; right: 6px;" onclick="fetchReferenceInvoice()">
                                Fetch
                            </button>
                        </div>
                    </div>

                    <div class="pos-field-group">
                        <label for="customer_name" class="pos-field-label">
                            <i class="fas fa-user-injured" style="color: var(--primary-color);"></i> Patient / Customer Name
                        </label>
                        <div class="pos-input-wrapper">
                            <i class="fas fa-user pos-input-icon"></i>
                            <input type="text" name="customer_name" id="customer_name" class="pos-input-field" placeholder="Patient Name" value="">
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
                            <i class="fas fa-undo-alt" style="color: #ef4444;"></i> Return Medicine Items 
                            <span class="pos-count-badge" id="posItemCount" style="background: #ef4444;">0</span>
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
                            <div class="pos-empty-icon"><i class="fas fa-box-open"></i></div>
                            <p style="margin:0; font-weight: 600;">No return items added yet</p>
                            <p style="margin:4px 0 0 0; font-size: 0.85rem;">Enter Original Invoice ID above or press F2 to add items</p>
                        </div>
                    </div>
                    
                    <button type="button" class="pos-btn-add-row" onclick="addRow()">
                        <i class="fas fa-plus-circle"></i> Add Return Item <span style="font-size:0.75rem; opacity:0.8;">(F2)</span>
                    </button>
                </div>

                <!-- Right Side Summary Card -->
                <?php 
                $showTax = true;
                $showAdminOverride = true;
                $closeUrl = URL_ROOT . '/' . ($rolePath ?? 'admin') . '/invoices';
                $saveBtnText = 'Process Return';
                $saveBtnClass = 'btn-danger';
                $saveBtnIcon = 'fa-undo';
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
            const isExpired = p.expiry_date ? (new Date(p.expiry_date) <= new Date()) : false;
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
        if (grandEl) grandEl.textContent = Math.round(grandTotal);

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
        if (confirm('Are you sure you want to clear all items from this return invoice?')) {
            document.getElementById('invoiceItems').innerHTML = '';
            calculateTotal();
            updateItemCountBadge();
            const refInput = document.getElementById('reference_invoice_id');
            const custInput = document.getElementById('customer_name');
            if (refInput) refInput.value = '';
            if (custInput) custInput.value = '';
        }
    }

    // Auto-fill logic from reference invoice ID
    function fetchReferenceInvoice() {
        const id = document.getElementById('reference_invoice_id').value.trim();
        if (!id) return;
        
        fetch('<?php echo URL_ROOT; ?>/api/invoice/details?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || "Invoice not found.");
                    return;
                }
                
                document.getElementById('customer_name').value = data.invoice.customer_name || '';
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
                            const isExpired = p.expiry_date ? (new Date(p.expiry_date) <= new Date()) : false;
                            const expText = isExpired ? ' [EXPIRED]' : '';
                            options += `<option value="${p.id}" data-price="${p.price}" data-batch="${p.batch_number || '-'}" data-discount="${p.discount_amount || '0'}" data-stock="${p.quantity}" ${isSelected}>${p.name}${expText}</option>`;
                        });
                        
                        const qty = parseFloat(item.quantity) || 0;
                        const price = parseFloat(item.price) || 0;
                        const subtotal = parseFloat(item.subtotal) || 0;
                        const gross = qty * price;
                        let discountPct = 0;
                        if (gross > 0) {
                            discountPct = ((gross - subtotal) / gross) * 100;
                        }

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
                            <td><input type="number" class="pos-cell-input retail-price-input" step="0.01" readonly tabIndex="-1" value="${price.toFixed(2)}"></td>
                            <td><input type="number" class="pos-cell-input discount-input" step="1" min="0" max="100" value="${Math.round(discountPct)}" oninput="calculateRow('${rowId}')" ${isSalesman && !salesmanCanDiscount ? 'readonly' : ''}></td>
                            <td><input type="number" name="quantity[]" class="pos-cell-input qty-input" min="1" value="${qty}" required oninput="calculateRow('${rowId}')" onfocus="this.select()"></td>
                            <td>
                                <input type="hidden" name="price[]" class="effective-price-input">
                                <input type="number" class="pos-cell-input subtotal-input subtotal-val" readonly tabIndex="-1" value="${subtotal.toFixed(2)}">
                            </td>
                            <td>
                                <button type="button" class="pos-btn-delete" title="Remove line item" onclick="removeRow(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                        
                        const select = tr.querySelector('.product-select');
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.value) {
                            const stockQty = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
                            tr.querySelector('.batch-input').value = selectedOption.getAttribute('data-batch') || '-';
                            tr.querySelector('.stock-input').value = stockQty;
                            const stockTag = document.getElementById(rowId + '_stock_tag');
                            if (stockTag) {
                                stockTag.textContent = stockQty;
                                stockTag.className = 'pos-stock-tag ' + (stockQty <= 10 ? 'pos-stock-low' : 'pos-stock-healthy');
                            }
                        }
                        
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
                updateItemCountBadge();
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while fetching the invoice details.");
            });
    }

    // Keyboard POS Shortcuts Engine
    document.addEventListener('keydown', function(e) {
        // F2 -> Add medicine row
        if (e.key === 'F2') {
            e.preventDefault();
            addRow();
        }
        // F9 -> Submit Form (Process Return & Print)
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
        // Esc -> Confirm clear form
        else if (e.key === 'Escape') {
            confirmClearForm();
        }
    });

    // Add initial row on load
    window.onload = function() {
        addRow();
        const refInput = document.getElementById('reference_invoice_id');
        if (refInput) refInput.focus();
    };
</script>

<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>

