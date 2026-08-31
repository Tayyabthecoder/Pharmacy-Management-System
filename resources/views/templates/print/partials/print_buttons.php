<?php
$buttonText = $buttonText ?? 'Print Document';
?>
<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary"><?php echo htmlspecialchars($buttonText); ?></button>
    <a href="<?php echo url(($_SESSION['role'] ?? '') === 'admin' ? '/admin/invoices' : '/salesman/invoices'); ?>" class="btn btn-secondary">Back</a>
</div>
