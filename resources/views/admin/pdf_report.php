<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    /* ── Base ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
        font-size: 11px;
        color: #1e293b;
        background: #fff;
        padding: 0;
    }

    /* ── Branded Header Band ── */
    .header-band {
        background-color: #1e3a8a;
        padding: 22px 28px 18px;
        margin-bottom: 0;
    }
    .header-top {
        display: table;
        width: 100%;
        margin-bottom: 12px;
    }
    .header-top-left {
        display: table-cell;
        vertical-align: middle;
    }
    .header-top-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .company-name {
        font-size: 20px;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 0.5px;
    }
    .company-sub {
        font-size: 9px;
        color: rgba(255,255,255,0.65);
        margin-top: 3px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .report-title {
        font-size: 16px;
        font-weight: bold;
        color: #bfdbfe;
        margin-top: 2px;
    }

    /* ── Accent Divider ── */
    .accent-bar {
        height: 4px;
        background-color: #3b82f6;
        margin-bottom: 0;
    }

    /* ── Meta Strip ── */
    .meta-strip {
        background-color: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 28px;
        display: table;
        width: 100%;
    }
    .meta-cell {
        display: table-cell;
        font-size: 9.5px;
        color: #475569;
        vertical-align: middle;
        width: 50%;
    }
    .meta-cell strong {
        color: #1e3a8a;
        font-weight: bold;
    }

    /* ── Body Padding ── */
    .body-content {
        padding: 18px 28px 24px;
    }

    /* ── Table ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
        font-size: 10px;
    }
    thead tr {
        background-color: #1e40af;
    }
    th {
        padding: 9px 11px;
        color: #ffffff;
        font-weight: bold;
        font-size: 8.5px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border: 1px solid #1e3a8a;
    }
    td {
        padding: 8px 11px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
        color: #1e293b;
    }
    tbody tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    tbody tr:last-child td {
        border-bottom: 2px solid #cbd5e1;
    }
    .num { text-align: right; font-weight: 500; }
    .txt { text-align: left; }

    /* ── No Data ── */
    .no-data {
        padding: 30px 20px;
        text-align: center;
        color: #94a3b8;
        font-size: 11px;
    }

    /* ── Footer ── */
    .footer {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        font-size: 8.5px;
        color: #94a3b8;
    }
    .footer strong { color: #64748b; }
</style>
</head>
<body>

    <!-- ════════ BRANDED HEADER BAND ════════ -->
    <?php
    $cLogo = \App\Models\Setting::getSetting('company_logo', '');
    $cName = \App\Models\Setting::getSetting('company_name', 'PHARMACY SYSTEM');
    ?>
    <div class="header-band">
        <div class="header-top">
            <div class="header-top-left">
                <div class="company-name">
                    <?php if (!empty($cLogo) && file_exists(BASE_PATH . '/public/' . $cLogo)): ?>
                        <img src="<?php echo BASE_PATH . '/public/' . $cLogo; ?>" style="max-height: 28px; max-width: 140px; vertical-align: middle; margin-right: 8px;">
                    <?php endif; ?>
                    <?php echo htmlspecialchars($cName); ?>
                </div>
                <div class="company-sub">Advanced Reporting Module &mdash; Confidential</div>
            </div>
            <div class="header-top-right">
                <div class="report-title"><?php echo htmlspecialchars($title); ?></div>
            </div>
        </div>
    </div>

    <div class="accent-bar"></div>

    <!-- ════════ META STRIP ════════ -->
    <div class="meta-strip">
        <div class="meta-cell">
            <strong>Generated:</strong> &nbsp;<?php echo htmlspecialchars($date); ?>
        </div>
        <div class="meta-cell" style="text-align:right">
            <strong>Records:</strong> &nbsp;<?php echo number_format(count($data)); ?> rows
        </div>
    </div>

    <!-- ════════ TABLE CONTENT ════════ -->
    <div class="body-content">

    <?php if (!empty($isList) && $isList): ?>
        <?php foreach ($data as $group): ?>
            <div style="margin-bottom: 22px; page-break-inside: avoid;">
                <div style="background-color: #1e3a8a; color: #ffffff; padding: 8px 14px; font-size: 13px; font-weight: bold; border-radius: 4px; margin-bottom: 8px;">
                    <?php echo htmlspecialchars($group['group_title']); ?>
                    <span style="font-size: 10px; font-weight: normal; float: right; opacity: 0.9;">
                        <?php echo count($group['items']); ?> Medicine(s)
                    </span>
                </div>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
                    <thead>
                        <tr style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                            <th style="text-align: left; padding: 6px 10px; font-size: 9px;">Medicine & Generic</th>
                            <th style="text-align: left; padding: 6px 10px; font-size: 9px;"><?php echo (($groupBy ?? '') === 'Company') ? 'Category' : 'Company'; ?></th>
                            <th style="text-align: center; padding: 6px 10px; font-size: 9px;">Batch / Expiry</th>
                            <th style="text-align: right; padding: 6px 10px; font-size: 9px;">Stock Qty</th>
                            <th style="text-align: right; padding: 6px 10px; font-size: 9px;">Cost Price</th>
                            <th style="text-align: right; padding: 6px 10px; font-size: 9px;">Retail Price</th>
                            <th style="text-align: right; padding: 6px 10px; font-size: 9px;">Total Cost Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grpStock = 0; $grpValue = 0;
                        foreach ($group['items'] as $item):
                            $grpStock += $item['Stock Qty'];
                            $grpValue += $item['Total Cost Value'];
                        ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 6px 10px;">
                                <strong><?php echo htmlspecialchars($item['Medicine']); ?></strong>
                                <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;"><?php echo htmlspecialchars($item['Generic']); ?></div>
                            </td>
                            <td style="padding: 6px 10px; font-size: 9.5px;"><?php echo htmlspecialchars((($groupBy ?? '') === 'Company') ? $item['Category'] : $item['Company']); ?></td>
                            <td style="text-align: center; padding: 6px 10px; font-size: 9px;"><?php echo htmlspecialchars($item['Batch']); ?> <span style="color:#64748b;">(<?php echo htmlspecialchars($item['Expiry']); ?>)</span></td>
                            <td style="text-align: right; padding: 6px 10px; font-weight: bold;"><?php echo number_format($item['Stock Qty']); ?></td>
                            <td style="text-align: right; padding: 6px 10px;">Rs. <?php echo number_format($item['Cost Price'], 2); ?></td>
                            <td style="text-align: right; padding: 6px 10px;">Rs. <?php echo number_format($item['Retail Price'], 2); ?></td>
                            <td style="text-align: right; padding: 6px 10px; font-weight: bold; color: #1e3a8a;">Rs. <?php echo number_format($item['Total Cost Value'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8fafc; font-weight: bold; border-top: 1.5px solid #1e3a8a;">
                            <td colspan="3" style="padding: 7px 10px; font-size: 9.5px; color: #1e3a8a;">Subtotal for <?php echo htmlspecialchars($group['group_name']); ?></td>
                            <td style="text-align: right; padding: 7px 10px; font-size: 9.5px; font-weight: bold; color: #1e3a8a;"><?php echo number_format($grpStock); ?> units</td>
                            <td colspan="2"></td>
                            <td style="text-align: right; padding: 7px 10px; font-size: 9.5px; font-weight: bold; color: #1e3a8a;">Rs. <?php echo number_format($grpValue, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endforeach; ?>
    <?php elseif (empty($data)): ?>
        <div class="no-data">No data available for the selected criteria.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <?php foreach (array_keys($data[0]) as $header): ?>
                    <?php
                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false || strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false || strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false || strpos($h,'cost') !== false || ($h === 'id'));
                        $alignClass = $isNum ? 'num' : 'txt';
                    ?>
                    <th class="<?php echo $alignClass; ?>"><?php echo htmlspecialchars($header); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $header => $val): ?>
                    <?php
                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false || strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false || strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false || strpos($h,'cost') !== false || ($h === 'id'));
                        $alignClass = $isNum ? 'num' : 'txt';
                        $strVal = (string)($val ?? '');
                        $display = ($isNum && is_numeric($val)) ? number_format((float)$val, strpos($h,'qty') !== false || $h === 'id' || strpos($h,'stock') !== false ? 0 : 2) : htmlspecialchars($strVal);
                    ?>
                    <td class="<?php echo $alignClass; ?>"><?php echo $display; ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if (!empty($summaryTotals)): ?>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #1e3a8a; border-bottom: 2px solid #1e3a8a;">
                    <?php 
                    $colIdx = 0;
                    foreach (array_keys($data[0]) as $header):
                        $colIdx++;
                        $val = $summaryTotals[$header] ?? '';
                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false || strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false || strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false || strpos($h,'cost') !== false);
                        $display = ($val !== '' && is_numeric($val)) ? number_format((float)$val, strpos($h,'qty') !== false || strpos($h,'stock') !== false ? 0 : 2) : htmlspecialchars((string)$val);
                        if ($colIdx === 1 && $display === '') {
                            $display = 'TOTALS';
                        }
                    ?>
                    <td class="<?php echo $isNum ? 'num' : 'txt'; ?>" style="font-weight: 700; color: #0f172a; padding: 10px 11px; font-size: 9.5px;"><?php echo $display; ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>

        <!-- ════════ FOOTER ════════ -->
        <div class="footer">
            <strong>&copy; <?php echo date('Y'); ?> Pharmacy Management System</strong> &mdash;
            This is a computer-generated report. Generated on <?php echo $date; ?>.
            Confidential &mdash; For internal use only.
        </div>

    </div><!-- /body-content -->

</body>
</html>
