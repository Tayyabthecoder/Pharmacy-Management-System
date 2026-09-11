<?php
/**
 * POS & Workstation Keyboard Shortcuts Overlay Modal
 */
?>
<style>
.hotkey-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(6px);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.hotkey-modal-card {
    background: var(--card-bg, #ffffff);
    border-radius: 18px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid var(--surface-border, #e2e8f0);
    overflow: hidden;
    animation: hotkeyPopIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes hotkeyPopIn {
    from { opacity: 0; transform: scale(0.95) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.hotkey-modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--surface-border, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--surface-color, #f8fafc);
}

.hotkey-modal-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    display: flex;
    align-items: center;
    gap: 10px;
}

.hotkey-close-btn {
    background: none;
    border: none;
    font-size: 1.4rem;
    color: var(--text-muted, #94a3b8);
    cursor: pointer;
    line-height: 1;
    padding: 4px;
    transition: color 0.15s ease;
}

.hotkey-close-btn:hover {
    color: var(--text-color, #0f172a);
}

.hotkey-modal-body {
    padding: 20px 24px;
    max-height: 75vh;
    overflow-y: auto;
}

.hotkey-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

@media (max-width: 520px) {
    .hotkey-grid {
        grid-template-columns: 1fr;
    }
}

.hotkey-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 10px;
    background: var(--surface-color, #f8fafc);
    border: 1px solid var(--surface-border, #e2e8f0);
    transition: background-color 0.15s ease;
}

.hotkey-item:hover {
    background: var(--surface-hover, #f1f5f9);
}

.hotkey-label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-color, #334155);
}

.hotkey-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 26px;
    padding: 0 8px;
    border-radius: 6px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary-color, #3b82f6);
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.25);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.hotkey-badge.danger {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.25);
}

.hotkey-badge.success {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.25);
}

.hotkey-footer-tip {
    margin-top: 18px;
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(59, 130, 246, 0.06);
    border: 1px solid rgba(59, 130, 246, 0.15);
    font-size: 0.82rem;
    color: var(--text-muted, #64748b);
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<!-- Hotkey Modal Container -->
<div id="hotkeyOverlayModal" class="hotkey-modal-overlay">
    <div class="hotkey-modal-card">
        <div class="hotkey-modal-header">
            <h3 class="hotkey-modal-title">
                <i class="fas fa-keyboard" style="color: var(--primary-color, #3b82f6);"></i> Keyboard Shortcuts Guide
            </h3>
            <button type="button" class="hotkey-close-btn" onclick="closeHotkeyModal()" aria-label="Close">&times;</button>
        </div>
        <div class="hotkey-modal-body">
            <div class="hotkey-grid">
                <div class="hotkey-item">
                    <span class="hotkey-label">Toggle Shortcuts Guide</span>
                    <span class="hotkey-badge">F1</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Add Medicine Item</span>
                    <span class="hotkey-badge">F2</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Barcode Quick Scan</span>
                    <span class="hotkey-badge">F3</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Complete & Print Sale</span>
                    <span class="hotkey-badge success">F9</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Clear Form / Close</span>
                    <span class="hotkey-badge danger">Esc</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Patient / Customer Field</span>
                    <span class="hotkey-badge">Alt + P</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Go to Sales Returns</span>
                    <span class="hotkey-badge">Alt + R</span>
                </div>
                <div class="hotkey-item">
                    <span class="hotkey-label">Go to New Sale POS</span>
                    <span class="hotkey-badge">Alt + S</span>
                </div>
            </div>

            <div class="hotkey-footer-tip">
                <i class="fas fa-lightbulb" style="color: #f59e0b;"></i>
                <span>Tip: Barcode scanners transmit an Enter key on scan, auto-adding items instantly!</span>
            </div>
        </div>
    </div>
</div>

<script>
function openHotkeyModal() {
    const modal = document.getElementById('hotkeyOverlayModal');
    if (modal) modal.style.display = 'flex';
}

function closeHotkeyModal() {
    const modal = document.getElementById('hotkeyOverlayModal');
    if (modal) modal.style.display = 'none';
}

// Global hotkey listener for F1 / ? / Alt+R / Alt+S / Alt+P
document.addEventListener('keydown', function(e) {
    // F1 or ? (Shift+/) -> Toggle hotkey guide
    if (e.key === 'F1' || (e.key === '?' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName))) {
        e.preventDefault();
        const modal = document.getElementById('hotkeyOverlayModal');
        if (modal) {
            modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        }
    }
    // Alt + R -> Jump to Return Invoice
    else if (e.altKey && (e.key === 'r' || e.key === 'R')) {
        e.preventDefault();
        window.location.href = "<?php echo url('/' . (($_SESSION['role'] ?? 'admin') === 'admin' ? 'admin' : 'salesman') . '/create_return_invoice'); ?>";
    }
    // Alt + S -> Jump to Sale POS
    else if (e.altKey && (e.key === 's' || e.key === 'S')) {
        e.preventDefault();
        window.location.href = "<?php echo url('/' . (($_SESSION['role'] ?? 'admin') === 'admin' ? 'admin' : 'salesman') . '/create_invoice'); ?>";
    }
    // Alt + P -> Focus customer/patient name
    else if (e.altKey && (e.key === 'p' || e.key === 'P')) {
        e.preventDefault();
        const custInput = document.getElementById('customer_name');
        if (custInput) {
            custInput.focus();
            custInput.select();
        }
    }
    // Close modal on Escape if hotkey modal is open
    else if (e.key === 'Escape') {
        const modal = document.getElementById('hotkeyOverlayModal');
        if (modal && modal.style.display === 'flex') {
            e.stopPropagation();
            closeHotkeyModal();
        }
    }
});

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('hotkeyOverlayModal');
    if (e.target === modal) {
        closeHotkeyModal();
    }
});
</script>
