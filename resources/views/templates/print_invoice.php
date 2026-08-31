<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

</head>
<body>

<div style="text-align: center; margin-bottom: 20px" class="no-print">
    <button onclick="window.print()" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem">Print Invoice</button>
    <a href="<?php echo url($_SESSION['role'] === 'admin' ? '/admin/sales' : '/salesman/invoices'); ?>" style="margin-left: 10px; text-decoration: none">Back</a>
</div>

<div class="invoice-box">
    <div class="invoice-header">
        <div class="company-info">
            <h2><?php echo htmlspecialchars($settings['company_name'] ?? 'Inventory System'); ?></h2>
            <p>123 Business St<br>City, Country</p>
        </div>
        <div class="invoice-details">
            <h3>INVOICE #<?php echo str_pad($invoice['id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></h3>
            <p>Date: <?php echo date('M d, Y', strtotime($invoice['created_at'] ?? 'now')); ?><br>
               To: <?php echo htmlspecialchars($invoice['customer_name'] ?? ''); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center">Quantity</th>
                <th style="text-align: right">Price</th>
                <th style="text-align: right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></td>
                        <td style="text-align: center"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right"><?php echo format_price($item['price']); ?></td>
                        <td style="text-align: right"><?php echo format_price($item['subtotal']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3" style="text-align: right">Grand Total:</td>
                <td style="text-align: right"><?php echo format_price($invoice['total_amount'] ?? 0); ?></td>
            </tr>
            <?php if (isset($invoice['previous_balance']) && $invoice['previous_balance'] > 0): ?>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: 600">Previous Balance Due:</td>
                <td style="text-align: right; font-weight: 600"><?php echo format_price($invoice['previous_balance']); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Salesman: <?php echo htmlspecialchars($invoice['salesman_name'] ?? ''); ?></p>
        <p>Thank you for your business!</p>
    </div>
</div>

</body>
</html>
