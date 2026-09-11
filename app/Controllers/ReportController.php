<?php

namespace App\Controllers;

use Exception;
use PDO;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\Report;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

class ReportController {
    protected Product $productModel;
    protected Invoice $invoiceModel;
    protected Setting $settingModel;
    protected Report $reportModel;
    protected Category $categoryModel;
    protected User $userModel;

    public function __construct() {
        // Middleware: Check if user is logged in and is admin or salesman
        (new \App\Middleware\RoleMiddleware(['admin', 'salesman']))->handle();
        $this->productModel = new Product();
        $this->invoiceModel = new Invoice();
        $this->settingModel = new Setting();
        $this->reportModel = new Report();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    public function index() {
        $pageTitle = "System Analytics & Reports";

        // Fetch query parameters
        $filter_preset = $_GET['preset'] ?? 'all';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $salesman_id = $_GET['salesman_id'] ?? 'all';
        $category_id = $_GET['category_id'] ?? 'all';

        // Calculate start/end dates if a preset is selected
        if ($filter_preset !== 'custom') {
            $end_date = date('Y-m-d');
            if ($filter_preset === 'today') {
                $start_date = date('Y-m-d');
            } elseif ($filter_preset === 'yesterday') {
                $start_date = date('Y-m-d', strtotime('-1 day'));
                $end_date = date('Y-m-d', strtotime('-1 day'));
            } elseif ($filter_preset === '7days') {
                $start_date = date('Y-m-d', strtotime('-7 days'));
            } elseif ($filter_preset === '30days') {
                $start_date = date('Y-m-d', strtotime('-30 days'));
            } elseif ($filter_preset === 'this_month') {
                $start_date = date('Y-m-01');
            } elseif ($filter_preset === '6months') {
                $start_date = date('Y-m-d', strtotime('-6 months'));
            } elseif ($filter_preset === 'all') {
                $start_date = '';
                $end_date = '';
            }
        }

        // Fetch dropdown options
        $salesmen = [];
        $categories = [];
        try {
            $salesmen = $this->userModel->all('users', 'name ASC', null, "role = 'salesman' AND status = 'active'");
            $categories = $this->categoryModel->all('categories', 'name ASC');
        } catch (Exception $e) {
            error_log("[Report Dropdown Error] " . $e->getMessage());
        }

        // Initialize KPIs and visual data variables
        $totalRevenue = 0.0;
        $totalProfit = 0.0;
        $profitMargin = 0.0;
        $totalInventoryValue = 0.0;
        $totalCustomers = 0;
        $totalInvoices = 0;
        $averageOrderValue = 0.0;
        $outOfStockCount = 0;

        $salesTrend = [];
        $topProducts = [];
        $allFilteredTopProducts = [];
        $lowStockProducts = [];
        $allFilteredLowStockProducts = [];
        $salesByCategory = [];
        $salesmanLeaderboard = [];
        $salesByGeneric = [];
        $salesByCompany = [];

        try {
            $filters = [
                'preset'      => $filter_preset,
                'start_date'   => $start_date,
                'end_date'     => $end_date,
                'salesman_id'  => $salesman_id,
                'category_id'  => $category_id,
            ];

            // 1. Calculate Summary KPIs
            $summary = $this->reportModel->getAnalyticsSummary($filters);
            $totalRevenue = $summary['totalRevenue'];
            $totalProfit = $summary['totalProfit'];
            $profitMargin = $summary['profitMargin'];
            $totalInventoryValue = $summary['totalInventoryValue'];
            $totalCustomers = $summary['totalCustomers'];
            $totalInvoices = $summary['totalInvoices'];
            $averageOrderValue = $summary['averageOrderValue'];
            $outOfStockCount = $summary['outOfStockCount'];

            // 2. Sales Trend Grouping
            $isDaily = false;
            if ($start_date !== '' && $end_date !== '') {
                $diff = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                if ($diff <= 45) {
                    $isDaily = true;
                }
            }
            $salesTrend = $this->reportModel->getSalesTrend($filters, $isDaily);

            // 3. Top Selling Products
            $allFilteredTopProducts = $this->reportModel->getTopSellingProducts($filters);
            $topProducts = array_slice($allFilteredTopProducts, 0, 5);

            // 4. Low Stock Alerts
            $allFilteredLowStockProducts = $this->reportModel->getLowStockProducts($category_id);
            $lowStockProducts = array_slice($allFilteredLowStockProducts, 0, 5);

            // 5. Category Breakdown
            $salesByCategory = $this->reportModel->getSalesByCategory($filters);

            // 6. Salesman Leaderboard
            $salesmanLeaderboard = $this->reportModel->getSalesmanPerformance($filters);

            // 7. Generic & Company Breakdown
            $salesByGeneric = $this->reportModel->getSalesByGeneric($filters, 10);
            $salesByCompany = $this->reportModel->getSalesByCompany($filters, 10);

        } catch (Exception $e) {
            error_log("[Report Error] " . $e->getMessage());
        }

        require_once BASE_PATH . '/resources/views/admin/reports.php';
    }

    public function expiryReport() {
        $pageTitle = "Medicine Expiry Report";
        
        $expiredProducts = $this->productModel->getExpiredProducts();
        $nearExpiryProducts = $this->productModel->getNearExpiryProducts(180);
        
        require_once BASE_PATH . '/resources/views/admin/expiry_report.php';
    }

    public function generate() {
        $category = $_POST['report_category'] ?? '';
        $type = $_POST['report_type'] ?? '';
        $format = $_POST['export_format'] ?? 'html';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $salesmanId = $_POST['salesman_id'] ?? 'all';
        $categoryId = $_POST['category_id'] ?? 'all';
        $companyId = $_POST['company_id'] ?? 'all';
        $selectedColumns = $_POST['selected_columns'] ?? [];

        $data = [];
        $title = "Report";

        if ($category === 'medicine') {
            $result = $this->reportModel->getMedicineReport($type, $categoryId, $companyId);
        } elseif ($category === 'receive') {
            $result = $this->reportModel->getReceiveReport($type, $startDate, $endDate, $companyId);
        } elseif ($category === 'sale') {
            $result = $this->reportModel->getSaleReport($type, $startDate, $endDate, $salesmanId, $categoryId, $companyId);
        } elseif ($category === 'stock') {
            $result = $this->reportModel->getStockReport($type, $categoryId, $companyId);
        } elseif ($category === 'analytics') {
            if ($type === 'margin_analysis') {
                $result = $this->reportModel->getMarginAnalysisReport($startDate, $endDate, $categoryId, $companyId);
            } elseif ($type === 'dead_stock') {
                $days = (int)($_POST['dead_stock_days'] ?? 90);
                $result = $this->reportModel->getDeadStockReport($days, $categoryId, $companyId);
            } elseif ($type === 'customer_ledger') {
                $result = $this->reportModel->getCustomerLedgerReport($startDate, $endDate);
            } else {
                $result = ['title' => 'Analytics Report', 'data' => []];
            }
        } else {
            $result = ['title' => 'Unknown Report', 'data' => []];
        }

        $rawNavData = $result['data'] ?: [];
        $title = $result['title'];
        $isList = $result['is_list'] ?? false;
        $groupBy = $result['group_by'] ?? '';

        if ($isList) {
            $data = $rawNavData;
            $summaryTotals = [];
        } else {
            $data = $this->filterColumns($rawNavData, $selectedColumns);
            $summaryTotals = $this->calculateSummaryTotals($data);
        }

        if ($format === 'html') {
            $pageTitle = $title;
            require_once __DIR__ . '/../../resources/views/admin/advanced_reports_result.php';
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'excel') {
            $this->exportExcel($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'word') {
            $this->exportWord($data, $title, $summaryTotals, $isList, $groupBy);
        } elseif ($format === 'csv') {
            $this->exportCsv($data, $title, $summaryTotals, $isList, $groupBy);
        }
    }

    private function filterColumns(array $data, array $selectedColumns): array {
        if (empty($data) || empty($selectedColumns)) return $data;
        $filtered = [];
        foreach ($data as $row) {
            $newRow = [];
            foreach ($selectedColumns as $col) {
                if (array_key_exists($col, $row)) {
                    $newRow[$col] = $row[$col];
                }
            }
            if (!empty($newRow)) {
                $filtered[] = $newRow;
            }
        }
        return !empty($filtered) ? $filtered : $data;
    }

    private function calculateSummaryTotals(array $data): array {
        if (empty($data)) return [];
        $totals = [];
        $headers = array_keys($data[0]);

        foreach ($headers as $index => $header) {
            $h = strtolower($header);
            $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                      strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                      strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                      strpos($h,'cost') !== false || strpos($h,'spent') !== false || strpos($h,'capital') !== false) && $h !== 'id' && strpos($h,'margin') === false;

            $isMargin = strpos($h,'margin') !== false;

            if ($isMargin) {
                $sum = 0; $count = 0;
                foreach ($data as $row) {
                    if (isset($row[$header]) && is_numeric($row[$header])) {
                        $sum += (float)$row[$header];
                        $count++;
                    }
                }
                $totals[$header] = $count > 0 ? round($sum / $count, 2) : 0;
            } elseif ($isNum) {
                $sum = 0;
                foreach ($data as $row) {
                    if (isset($row[$header]) && is_numeric($row[$header])) {
                        $sum += (float)$row[$header];
                    }
                }
                $totals[$header] = round($sum, 2);
            } else {
                $totals[$header] = ($index === 0) ? 'TOTALS' : '';
            }
        }
        return $totals;
    }

    private function buildHtmlTable($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $date = date('F j, Y, g:i a');
        ob_start();
        require BASE_PATH . '/resources/views/admin/pdf_report.php';
        return ob_get_clean();
    }

    private function exportPDF($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $html = $this->buildHtmlTable($data, $title, $summaryTotals, $isList, $groupBy);
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(strtolower(str_replace(' ', '_', $title)) . '.pdf', ["Attachment" => true]);
        exit;
    }

    private function exportCsv($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $filename = strtolower(str_replace(' ', '_', $title)) . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        if (!empty($data)) {
            if ($isList) {
                foreach ($data as $group) {
                    fputcsv($output, ['[' . $group['group_title'] . ']']);
                    if (!empty($group['items'])) {
                        fputcsv($output, array_keys($group['items'][0]));
                        foreach ($group['items'] as $item) {
                            fputcsv($output, $item);
                        }
                    }
                    fputcsv($output, []);
                }
            } else {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
                if (!empty($summaryTotals)) {
                    $summaryRow = [];
                    foreach (array_keys($data[0]) as $h) {
                        $summaryRow[] = $summaryTotals[$h] ?? '';
                    }
                    fputcsv($output, $summaryRow);
                }
            }
        }
        fclose($output);
        exit;
    }

    private function exportExcel($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheetTitle = mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $title), 0, 31);
        $sheet->setTitle($sheetTitle ?: 'Report');

        if (!empty($data)) {
            if ($isList) {
                $rowNum = 1;
                $sheet->setCellValue('A' . $rowNum, $title);
                $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(16);
                $rowNum += 2;

                foreach ($data as $group) {
                    $sheet->setCellValue('A' . $rowNum, $group['group_title']);
                    $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1E3A8A');
                    $rowNum++;

                    if (!empty($group['items'])) {
                        $headers = array_keys($group['items'][0]);
                        $colIdx = 1;
                        foreach ($headers as $h) {
                            $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowNum;
                            $sheet->setCellValue($cellCoord, $h);
                            $sheet->getStyle($cellCoord)->getFont()->setBold(true);
                            $colIdx++;
                        }
                        $rowNum++;

                        foreach ($group['items'] as $item) {
                            $colIdx = 1;
                            foreach ($item as $val) {
                                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowNum;
                                $sheet->setCellValue($cellCoord, $val);
                                $colIdx++;
                            }
                            $rowNum++;
                        }
                    }
                    $rowNum++;
                }

                foreach (range(1, 10) as $col) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colString)->setAutoSize(true);
                }
            } else {
                $META_ROWS = 4;
                $headers   = array_keys($data[0]);
                $colCount  = count($headers);
                $lastCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->setCellValue('A2', 'Generated: ' . date('F j, Y, g:i a') . '   |   Records: ' . number_format(count($data)));
                $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle('A2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);

                $sheet->getRowDimension(3)->setRowHeight(8);
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $sheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DBEAFE');

                $sheet->getRowDimension(4)->setRowHeight(6);

                $headerRowNum = $META_ROWS + 1;
                $sheet->getRowDimension($headerRowNum)->setRowHeight(22);

                foreach ($headers as $colIdx => $header) {
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . $headerRowNum;
                    $sheet->setCellValue($cellCoordinate, $header);

                    $h = strtolower($header);
                    $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                              strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                              strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                              strpos($h,'cost') !== false || $h === 'id');

                    $sheet->getStyle($cellCoordinate)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'))->setSize(10);
                    $sheet->getStyle($cellCoordinate)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
                    $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    if ($isNum) {
                        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    }
                }

                $dataStartRow = $headerRowNum + 1;
                foreach ($data as $rIndex => $row) {
                    $rowNum = $dataStartRow + $rIndex;
                    $sheet->getRowDimension($rowNum)->setRowHeight(19);

                    $cIndex = 1;
                    foreach ($row as $header => $val) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                        $cellCoord = $colLetter . $rowNum;

                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                                  strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                                  strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                                  strpos($h,'cost') !== false || $h === 'id');

                        if ($isNum && is_numeric($val)) {
                            $sheet->setCellValueExplicit($cellCoord, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || $h === 'id' || strpos($h,'count') !== false;
                            if ($isInt) {
                                $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0');
                            } else {
                                $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('#,##0.00');
                            }
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        } else {
                            $sheet->setCellValue($cellCoord, $val);
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }

                        if ($rIndex % 2 === 1) {
                            $sheet->getStyle($cellCoord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                        }

                        $sheet->getStyle($cellCoord)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
                        $cIndex++;
                    }
                }

                $dataEndRow = $dataStartRow + count($data) - 1;

                if (!empty($summaryTotals)) {
                    $summaryRowNum = $dataEndRow + 1;
                    $sheet->getRowDimension($summaryRowNum)->setRowHeight(22);
                    $cIndex = 1;
                    foreach ($headers as $header) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                        $cellCoord = $colLetter . $summaryRowNum;
                        $val = $summaryTotals[$header] ?? '';
                        $h = strtolower($header);
                        $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                                  strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                                  strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                                  strpos($h,'cost') !== false);

                        if ($val !== '' && is_numeric($val)) {
                            $sheet->setCellValueExplicit($cellCoord, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || strpos($h,'count') !== false;
                            $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode($isInt ? '#,##0' : '#,##0.00');
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        } else {
                            $sheet->setCellValue($cellCoord, $val);
                            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }

                        $sheet->getStyle($cellCoord)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
                        $sheet->getStyle($cellCoord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
                        $sheet->getStyle($cellCoord)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)->getColor()->setRGB('1E3A8A');
                        $sheet->getStyle($cellCoord)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)->getColor()->setRGB('1E3A8A');
                        $cIndex++;
                    }
                }

                foreach (range(1, $colCount) as $col) {
                    $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colString)->setAutoSize(true);
                }
            }
        }

        $filename = strtolower(str_replace(' ', '_', $title)) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportWord($data, $title, $summaryTotals = [], $isList = false, $groupBy = '') {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'orientation'   => 'landscape',
            'marginTop'     => 720,
            'marginRight'   => 720,
            'marginBottom'  => 720,
            'marginLeft'    => 720,
            'pageSizeW'     => 15840,
            'pageSizeH'     => 12240,
        ]);

        $section->addText(
            htmlspecialchars($title),
            ['bold' => true, 'size' => 18, 'color' => '1E3A8A'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 0]
        );

        $section->addText(
            '',
            ['size' => 2],
            ['spaceAfter' => 0, 'spaceBefore' => 0, 'borderBottomSize' => 12, 'borderBottomColor' => '2563EB']
        );

        $section->addText(
            'Generated: ' . date('F j, Y, g:i a'),
            ['size' => 8, 'color' => '64748B', 'italic' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceBefore' => 80, 'spaceAfter' => 200]
        );

        if (!empty($data)) {
            if ($isList) {
                foreach ($data as $group) {
                    $section->addText(
                        htmlspecialchars($group['group_title']),
                        ['bold' => true, 'size' => 14, 'color' => '1E3A8A'],
                        ['spaceBefore' => 180, 'spaceAfter' => 60]
                    );

                    if (!empty($group['items'])) {
                        foreach ($group['items'] as $item) {
                            $line = "• " . $item['Medicine'] . " (" . $item['Generic'] . ")";
                            $line .= " | Stock: " . number_format($item['Stock Qty']);
                            $line .= " | Cost: Rs. " . number_format($item['Cost Price'], 2);
                            $line .= " | Price: Rs. " . number_format($item['Retail Price'], 2);
                            $line .= " | Total Value: Rs. " . number_format($item['Total Cost Value'], 2);
                            $section->addText(htmlspecialchars($line), ['size' => 9.5], ['spaceAfter' => 40, 'indent' => 240]);
                        }
                    }
                }
            } else {
                $headers   = array_keys($data[0]);
                $colCount  = count($headers);
                $pageWidth  = 14400;
                $numericCols = [];
                $isNumFlags  = [];

                foreach ($headers as $i => $header) {
                    $h = strtolower($header);
                    $isNum = (strpos($h,'(') !== false || strpos($h,'qty') !== false || strpos($h,'total') !== false ||
                              strpos($h,'price') !== false || strpos($h,'stock') !== false || strpos($h,'amount') !== false ||
                              strpos($h,'profit') !== false || strpos($h,'value') !== false || strpos($h,'revenue') !== false ||
                              strpos($h,'cost') !== false || $h === 'id');
                    $isNumFlags[$i] = $isNum;
                    $numericCols[$i] = $isNum ? 1 : 2;
                }

                $totalWeight = array_sum($numericCols);
                $colWidths   = [];
                foreach ($numericCols as $i => $w) {
                    $colWidths[$i] = (int)(($w / $totalWeight) * $pageWidth);
                }

                $tableStyleName = 'ReportTable';
                $phpWord->addTableStyle($tableStyleName,
                    ['borderSize' => 4, 'borderColor' => 'E2E8F0', 'cellMargin' => 80,
                     'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::LEFT],
                    ['bgColor' => '1E3A8A', 'borderBottomSize' => 8, 'borderBottomColor' => '1E40AF']
                );

                $table = $section->addTable($tableStyleName);

                $table->addRow(350);
                $hFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 8];
                foreach ($headers as $i => $header) {
                    $align = $isNumFlags[$i] ? \PhpOffice\PhpWord\SimpleType\Jc::RIGHT : \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
                    $table->addCell($colWidths[$i], ['bgColor' => '1E3A8A', 'valign' => 'center'])
                          ->addText(htmlspecialchars($header), $hFontStyle, ['alignment' => $align]);
                }

                $rowIdx = 0;
                foreach ($data as $row) {
                    $table->addRow(280);
                    $bgColor = ($rowIdx % 2 === 1) ? 'F8FAFC' : 'FFFFFF';
                    $i = 0;
                    foreach ($row as $header => $val) {
                        $isNum = $isNumFlags[$i];
                        $align = $isNum ? \PhpOffice\PhpWord\SimpleType\Jc::RIGHT : \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
                        $strVal = (string)($val ?? '');
                        if ($isNum && is_numeric($val)) {
                            $h = strtolower($header);
                            $isInt = strpos($h,'qty') !== false || strpos($h,'stock') !== false || $h === 'id' || strpos($h,'count') !== false;
                            $strVal = $isInt ? number_format((float)$val, 0) : number_format((float)$val, 2);
                        }
                        $table->addCell($colWidths[$i], ['bgColor' => $bgColor, 'valign' => 'center'])
                          ->addText(htmlspecialchars($strVal), ['size' => 8], ['alignment' => $align]);
                        $i++;
                    }
                    $rowIdx++;
                }
            }
        }

        $filename = strtolower(str_replace(' ', '_', $title)) . '.docx';
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }
}
