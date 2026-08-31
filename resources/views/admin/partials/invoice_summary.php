<?php
/**
 * Unified Invoice Summary Layout
 * 
 * Available variables:
 * - $showTax (bool) - Show tax input
 * - $showAdminOverride (bool) - Show admin override box (if settings require it)
 * - $closeUrl (string) - URL to redirect to when closing form
 * - $saveBtnText (string) - Text for the save button
 * - $role (string) - The user role
 * - $rolePath (string) - The user role path
 * - $settings (array) - Application settings
 */

$showTax = $showTax ?? true;
$showAdminOverride = $showAdminOverride ?? true;
$closeUrl = $closeUrl ?? URL_ROOT . '/' . ($rolePath ?? 'admin') . '/invoices';
$saveBtnText = $saveBtnText ?? 'Complete Sale';
?>

<div class="pos-summary-card">
    <h4 class="pos-summary-title">
        <i class="fas fa-file-invoice-dollar" style="color: var(--primary-color);"></i> Invoice Summary
    </h4>

    <div class="pos-summary-row">
        <span>Net Amount:</span>
        <span class="pos-summary-val"><?php echo currency_symbol(); ?><span id="netAmount">0.00</span></span>
    </div>

    <div class="pos-summary-row">
        <span>Total Discount:</span>
        <span class="pos-summary-val discount">-<?php echo currency_symbol(); ?><span id="totalDiscount">0.00</span></span>
    </div>

    <div class="pos-summary-row bold" style="border-top: 1px dashed var(--surface-border); padding-top: 10px;">
        <span>Sub Total:</span>
        <span class="pos-summary-val"><?php echo currency_symbol(); ?><span id="subTotal">0.00</span></span>
    </div>

    <?php if ($showTax): ?>
    <div class="pos-summary-row">
        <div class="pos-tax-group">
            <span>Tax (%):</span>
            <input type="number" name="tax_rate" id="taxRate" class="pos-tax-input" value="0" min="0" max="100" step="0.1" oninput="calculateTotal()">
        </div>
        <span class="pos-summary-val">+<?php echo currency_symbol(); ?><span id="taxAmount">0.00</span></span>
    </div>
    <?php else: ?>
    <!-- Hidden input for calculateTotal() JS function compatibility -->
    <input type="hidden" id="taxRate" value="0">
    <span id="taxAmount" style="display:none;">0.00</span>
    <?php endif; ?>

    <div class="pos-grand-total-box">
        <span class="pos-grand-total-label">Grand Total:</span>
        <span class="pos-grand-total-amount"><?php echo currency_symbol(); ?><span id="grandTotal">0.00</span></span>
    </div>

    <?php if ($showAdminOverride && !empty($settings['require_admin_approval']) && isset($role) && $role !== 'admin'): ?>
    <!-- Admin approval override box -->
    <div id="adminApprovalSection" class="pos-approval-box" style="display: none;">
        <div class="pos-approval-title">
            <i class="fas fa-lock"></i> Exceeds Limit (<?php echo format_price($settings['approval_threshold_amount'] ?? 5000); ?>). Admin approval required:
        </div>
        <div class="form-group" style="margin: 0">
            <input type="email" name="admin_email" placeholder="Admin Email" class="pos-cell-input" style="font-size: 0.85rem">
        </div>
        <div class="form-group" style="margin: 0">
            <input type="password" name="admin_password" placeholder="Admin Password" class="pos-cell-input" style="font-size: 0.85rem">
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="pos-actions-grid">
        <button type="submit" name="action" value="print" class="btn-pos-primary">
            <i class="fas fa-print"></i> <?php echo htmlspecialchars($saveBtnText); ?> (F9)
        </button>
        
        <button type="submit" name="action" value="save" class="btn-pos-secondary">
            <i class="fas fa-save"></i> Save Sale Only
        </button>
        
        <div class="pos-actions-subgrid">
            <button type="button" onclick="confirmClearForm()" class="btn-pos-outline">
                <i class="fas fa-eraser"></i> Clear (Esc)
            </button>
            <button type="button" onclick="window.location.href='<?php echo htmlspecialchars($closeUrl); ?>'" class="btn-pos-danger-outline">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

