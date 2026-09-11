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
                <div class="shortcut-pill" id="posOfflineQueuePill" style="display: none; cursor: pointer; background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.4); color: #d97706;" onclick="syncOfflineInvoices(true)" title="Click to sync offline sales now">
                    <i class="fas fa-cloud-upload-alt"></i> <span id="posOfflineCountText">0 Offline Sales</span>
                </div>
                <div class="shortcut-pill" onclick="openHotkeyModal()" style="cursor: pointer; background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.35); color: #3b82f6;" title="View all keyboard shortcuts">
                    <span class="shortcut-key">F1</span> Shortcuts
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">F2</span> Add Item
                </div>
                <div class="shortcut-pill">
                    <span class="shortcut-key">F3</span> Barcode Scan
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
                    <!-- POS Barcode Quick Scan Bar -->
                    <div class="pos-barcode-bar" style="background: var(--surface-color, #f8fafc); border: 1px solid var(--surface-border, #e2e8f0); border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; gap: 12px;">
                        <div style="position: relative; flex: 1;">
                            <i class="fas fa-barcode" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--primary-color, #3b82f6); font-size: 1.1rem; pointer-events: none;"></i>
                            <input type="text" id="barcodeScanInput" placeholder="Scan Barcode or Type & Press Enter (F3)..." autocomplete="off" style="width: 100%; height: 40px; padding: 6px 12px 6px 38px; border-radius: 8px; border: 1px solid var(--surface-border, #cbd5e1); background: var(--card-bg, #fff); font-size: 0.95rem; font-weight: 600; color: var(--text-color, #0f172a);">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="handleBarcodeScanSubmit()" style="height: 40px; padding: 0 16px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <i class="fas fa-plus"></i> Scan & Add
                        </button>
                    </div>
                    <div id="scanFeedbackToast" style="display: none; padding: 8px 14px; border-radius: 8px; margin-bottom: 12px; font-size: 0.88rem; font-weight: 600; align-items: center; gap: 8px;"></div>

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
    const serverProducts = <?php echo $productsJson; ?>;
    if (Array.isArray(serverProducts) && serverProducts.length > 0) {
        try { localStorage.setItem('pms_pos_catalog', JSON.stringify(serverProducts)); } catch(e) {}
    }
    const products = (Array.isArray(serverProducts) && serverProducts.length > 0) 
        ? serverProducts 
        : (JSON.parse(localStorage.getItem('pms_pos_catalog') || '[]'));

    const isSalesman = <?php echo $role === 'salesman' ? 'true' : 'false'; ?>;
    const salesmanCanDiscount = <?php echo !empty($settings['salesman_can_give_discount']) ? 'true' : 'false'; ?>;
    const maxSalesmanDiscount = parseFloat("<?php echo $settings['max_salesman_discount'] ?? 100; ?>");
    const threshold = parseFloat("<?php echo $settings['approval_threshold_amount'] ?? 5000; ?>");
    const requireApproval = <?php echo (!empty($settings['require_admin_approval']) && $role !== 'admin') ? 'true' : 'false'; ?>;

    // Offline Queue Store Helpers
    function getOfflineInvoices() {
        try {
            return JSON.parse(localStorage.getItem('pms_offline_invoices') || '[]');
        } catch (e) {
            return [];
        }
    }

    function setOfflineInvoices(queue) {
        try {
            localStorage.setItem('pms_offline_invoices', JSON.stringify(queue));
        } catch(e) {}
        updateOfflineQueueBadge();
    }

    function updateOfflineQueueBadge() {
        const queue = getOfflineInvoices();
        const pill = document.getElementById('posOfflineQueuePill');
        const text = document.getElementById('posOfflineCountText');
        if (pill && text) {
            if (queue.length > 0) {
                text.textContent = queue.length + (queue.length === 1 ? ' Offline Sale (Sync)' : ' Offline Sales (Sync)');
                pill.style.display = 'inline-flex';
            } else {
                pill.style.display = 'none';
            }
        }
    }

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

    function addRow(selectedProductId = null) {
        const tbody = document.getElementById('invoiceItems');
        const rowId = 'row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        
        let options = '<option value="">Select Medicine</option>';
        products.forEach(p => {
            const isExpired = p.expiry_date ? (new Date(p.expiry_date + 'T23:59:59') <= new Date()) : false;
            const expText = isExpired ? ' [EXPIRED]' : '';
            const isSel = selectedProductId && parseInt(selectedProductId) === parseInt(p.id);
            options += `<option value="${p.id}" data-price="${p.price}" data-batch="${p.batch_number || '-'}" data-discount="${p.discount_amount || '0'}" data-stock="${p.quantity}" ${isExpired ? 'disabled style="color: #ef4444"' : ''} ${isSel ? 'selected' : ''}>${p.name}${expText}</option>`;
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
            if (selectedProductId) {
                tomInst.setValue(selectedProductId);
            } else {
                setTimeout(() => tomInst.focus(), 100);
            }
        } else if (selectedProductId) {
            updateRow(rowId);
        }

        updateItemCountBadge();
        return rowId;
    }

    function updateRow(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
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
        if (!row) return;
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

    // Audio feedback synth
    function playBeep(success = true) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(success ? 880 : 220, ctx.currentTime);
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (success ? 0.12 : 0.22));
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + (success ? 0.12 : 0.22));
        } catch(e) {}
    }

    function showScanToast(msg, type = 'success') {
        const toast = document.getElementById('scanFeedbackToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'flex';
        if (type === 'success') {
            toast.style.background = 'rgba(16, 185, 129, 0.12)';
            toast.style.color = '#10b981';
            toast.style.border = '1px solid rgba(16, 185, 129, 0.3)';
        } else if (type === 'warning') {
            toast.style.background = 'rgba(245, 158, 11, 0.12)';
            toast.style.color = '#d97706';
            toast.style.border = '1px solid rgba(245, 158, 11, 0.3)';
        } else {
            toast.style.background = 'rgba(239, 68, 68, 0.12)';
            toast.style.color = '#ef4444';
            toast.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        }
        clearTimeout(window._toastTimeout);
        window._toastTimeout = setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }

    // POS Barcode Scanner Handler
    async function handleBarcodeScan(barcodeStr = null) {
        const input = document.getElementById('barcodeScanInput');
        const code = (barcodeStr !== null ? barcodeStr : (input ? input.value : '')).trim();
        if (!code) return;

        // Search local products list first by barcode or ID or exact name
        let product = products.find(p => p.barcode && String(p.barcode).trim() === code);
        if (!product) {
            product = products.find(p => String(p.name).toLowerCase() === code.toLowerCase());
        }

        // If not in memory, query server scan API
        if (!product) {
            try {
                const res = await fetch(`<?php echo url('/api/products/scan'); ?>?barcode=${encodeURIComponent(code)}`);
                const data = await res.json();
                if (data.success && data.product) {
                    product = data.product;
                }
            } catch(e) {}
        }

        if (!product) {
            playBeep(false);
            showScanToast(`No medicine found matching barcode "${code}"`, 'danger');
            if (input) {
                input.select();
                input.style.borderColor = '#ef4444';
                setTimeout(() => { input.style.borderColor = ''; }, 1500);
            }
            return;
        }

        // Check expiry
        if (product.expiry_date && (new Date(product.expiry_date + 'T23:59:59') <= new Date())) {
            playBeep(false);
            showScanToast(`Cannot sell "${product.name}" — product expired on ${product.expiry_date}`, 'danger');
            if (input) input.select();
            return;
        }

        // Check stock
        const availableStock = parseFloat(product.quantity) || 0;
        if (availableStock <= 0) {
            playBeep(false);
            showScanToast(`"${product.name}" is out of shelf stock! (Stock: 0)`, 'warning');
            if (input) input.select();
            return;
        }

        // Check if product is already added in one of the rows
        let existingRow = null;
        document.querySelectorAll('#invoiceItems tr').forEach(row => {
            const sel = row.querySelector('.product-select');
            if (sel && parseInt(sel.value) === parseInt(product.id)) {
                existingRow = row;
            }
        });

        if (existingRow) {
            const qtyInput = existingRow.querySelector('.qty-input');
            const currentQty = parseInt(qtyInput.value) || 0;
            if (currentQty + 1 > availableStock) {
                playBeep(false);
                showScanToast(`Requested quantity (${currentQty + 1}) exceeds shelf stock (${availableStock}) for ${product.name}`, 'warning');
                if (input) input.select();
                return;
            }
            qtyInput.value = currentQty + 1;
            calculateRow(existingRow.id);
            playBeep(true);
            showScanToast(`Added +1 to ${product.name} (Total Qty: ${qtyInput.value})`, 'success');
        } else {
            // Remove first row if empty
            const rows = document.querySelectorAll('#invoiceItems tr');
            if (rows.length === 1) {
                const firstSel = rows[0].querySelector('.product-select');
                if (firstSel && !firstSel.value) {
                    rows[0].remove();
                }
            }
            addRow(product.id);
            playBeep(true);
            showScanToast(`Added ${product.name} to cart`, 'success');
        }

        if (input) {
            input.value = '';
            input.focus();
        }
    }

    function handleBarcodeScanSubmit() {
        handleBarcodeScan();
    }

    // Keyboard POS Shortcuts Engine
    document.addEventListener('keydown', function(e) {
        // F2 -> Add medicine row
        if (e.key === 'F2') {
            e.preventDefault();
            addRow();
        }
        // F3 -> Focus barcode scanner
        else if (e.key === 'F3') {
            e.preventDefault();
            const scanInput = document.getElementById('barcodeScanInput');
            if (scanInput) {
                scanInput.focus();
                scanInput.select();
            }
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

    // Offline Sale Handler & Slip Printer
    function submitOfflineInvoice(formDataObj) {
        const queue = getOfflineInvoices();
        const tempId = 'OFF-' + Date.now();
        formDataObj.temp_id = tempId;
        formDataObj.queued_at = new Date().toISOString();
        queue.push(formDataObj);
        setOfflineInvoices(queue);
        
        showScanToast(`Offline sale saved locally (${tempId})! Will sync once connection restores.`, 'warning');
        playBeep(true);

        if (formDataObj.action === 'print') {
            printOfflineSlip(formDataObj);
        }

        // Reset POS Form for next patient
        document.getElementById('invoiceItems').innerHTML = '';
        addRow();
        const custInput = document.getElementById('customer_name');
        if (custInput) custInput.value = '';
        calculateTotal();
    }

    function printOfflineSlip(invoiceData) {
        const printWin = window.open('', '_blank', 'width=420,height=600');
        if (!printWin) return;
        
        let itemsHtml = '';
        (invoiceData.items || []).forEach(item => {
            itemsHtml += `<tr><td style="padding:4px 0;">${item.name || 'Medicine'}</td><td style="text-align:center;">${item.quantity}</td><td style="text-align:right;">${item.subtotal}</td></tr>`;
        });

        printWin.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Offline Receipt - ${invoiceData.temp_id}</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 13px; padding: 15px; margin: 0; color: #111; }
                    .header { text-align: center; margin-bottom: 12px; border-bottom: 2px dashed #333; padding-bottom: 8px; }
                    .header h2 { margin: 0 0 4px 0; font-size: 16px; }
                    .pill { display: inline-block; background: #333; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 4px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th { border-bottom: 1px solid #333; padding-bottom: 4px; text-align: left; font-size: 11px; }
                    .total-box { border-top: 2px dashed #333; margin-top: 12px; padding-top: 8px; text-align: right; font-size: 14px; font-weight: bold; }
                    .footer-note { text-align: center; margin-top: 16px; font-size: 10px; color: #666; border-top: 1px dotted #ccc; padding-top: 6px; }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                <div class="header">
                    <h2>Pharmacy Sales Receipt</h2>
                    <span class="pill">OFFLINE SALE</span>
                    <div style="margin-top: 6px; font-size: 11px;">Receipt ID: <strong>${invoiceData.temp_id}</strong></div>
                    <div style="font-size: 11px;">Date: ${new Date().toLocaleString()}</div>
                    <div style="font-size: 11px;">Patient: ${invoiceData.customer_name || 'Walk-in'}</div>
                </div>
                <table>
                    <thead>
                        <tr><th>Medicine</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Subtotal</th></tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
                <div class="total-box">
                    Total Amount: ${invoiceData.grand_total || '0.00'}
                </div>
                <div class="footer-note">
                    Recorded locally in offline queue — Transaction pending server sync.
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    let isSyncing = false;
    async function syncOfflineInvoices(isManual = false) {
        if (isSyncing) return;
        const queue = getOfflineInvoices();
        if (queue.length === 0) {
            if (isManual) showScanToast('No offline sales pending in queue.', 'success');
            return;
        }

        if (!navigator.onLine) {
            if (isManual) showScanToast('Cannot sync: Browser is currently offline.', 'danger');
            return;
        }

        isSyncing = true;
        const pillText = document.getElementById('posOfflineCountText');
        if (pillText) pillText.textContent = `Syncing (${queue.length})...`;

        let syncedCount = 0;
        const remainingQueue = [];

        for (const invoice of queue) {
            try {
                const postBody = new URLSearchParams();
                postBody.append('csrf_token', '<?php echo htmlspecialchars($_SESSION["csrf_token"] ?? ""); ?>');
                postBody.append('customer_name', invoice.customer_name || '');
                postBody.append('doctor_name', invoice.doctor_name || '');
                postBody.append('doctor_license', invoice.doctor_license || '');
                postBody.append('tax_rate', invoice.tax_rate || '0');
                postBody.append('action', 'save');
                postBody.append('is_offline_sync', '1');

                (invoice.product_id || []).forEach(pid => postBody.append('product_id[]', pid));
                (invoice.quantity || []).forEach(qty => postBody.append('quantity[]', qty));
                (invoice.price || []).forEach(p => postBody.append('price[]', p));

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: postBody
                });

                if (response.ok) {
                    syncedCount++;
                } else {
                    remainingQueue.push(invoice);
                }
            } catch (e) {
                remainingQueue.push(invoice);
            }
        }

        setOfflineInvoices(remainingQueue);
        isSyncing = false;

        if (syncedCount > 0) {
            showScanToast(`Successfully synchronized ${syncedCount} offline sale(s) to server!`, 'success');
        }
    }

    // Auto-sync whenever internet connectivity restores
    window.addEventListener('online', function() {
        setTimeout(() => syncOfflineInvoices(false), 1500);
    });

    // Intercept form submission when offline
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('invoiceForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!navigator.onLine) {
                    e.preventDefault();
                    
                    const productIds = [];
                    const quantities = [];
                    const prices = [];
                    const items = [];

                    document.querySelectorAll('#invoiceItems tr').forEach(row => {
                        const sel = row.querySelector('.product-select');
                        const qtyInput = row.querySelector('.qty-input');
                        const effPrice = row.querySelector('.effective-price-input');
                        const subtotal = row.querySelector('.subtotal-input');
                        const opt = sel ? sel.options[sel.selectedIndex] : null;

                        if (sel && sel.value && qtyInput && qtyInput.value) {
                            productIds.push(sel.value);
                            quantities.push(qtyInput.value);
                            prices.push(effPrice ? effPrice.value : '0');
                            items.push({
                                name: opt ? opt.textContent.trim() : 'Medicine',
                                quantity: qtyInput.value,
                                subtotal: subtotal ? subtotal.value : '0'
                            });
                        }
                    });

                    if (productIds.length === 0) {
                        alert('Please add at least one medicine item before submitting.');
                        return;
                    }

                    const actionVal = (document.activeElement && document.activeElement.name === 'action') 
                        ? document.activeElement.value 
                        : 'print';

                    const invoicePayload = {
                        customer_name: document.getElementById('customer_name')?.value || 'Walk-in Customer',
                        doctor_name: document.getElementById('doctor_name')?.value || '',
                        doctor_license: document.getElementById('doctor_license')?.value || '',
                        tax_rate: document.getElementById('taxRate')?.value || '0',
                        grand_total: document.getElementById('grandTotal')?.textContent || '0.00',
                        action: actionVal,
                        product_id: productIds,
                        quantity: quantities,
                        price: prices,
                        items: items
                    };

                    submitOfflineInvoice(invoicePayload);
                }
            });
        }
        updateOfflineQueueBadge();
    });

    // Add initial row on load & attach barcode enter key listener
    window.onload = function() {
        addRow();
        const scanInput = document.getElementById('barcodeScanInput');
        if (scanInput) {
            scanInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleBarcodeScan();
                }
            });
        }
        const custInput = document.getElementById('customer_name');
        if (custInput) custInput.focus();
        updateOfflineQueueBadge();
    };
</script>

<?php require_once BASE_PATH . '/resources/views/admin/partials/hotkey_overlay.php'; ?>
<?php require_once BASE_PATH . '/resources/views/layouts/footer.php'; ?>

