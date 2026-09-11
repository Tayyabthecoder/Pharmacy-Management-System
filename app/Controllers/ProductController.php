<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Generic;
use App\Models\Company;
use App\Support\Cache;

class ProductController {
    protected $productModel;
    protected $categoryModel;

    public function __construct() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // If checking api endpoints, allow any logged in user (admin/owner/salesman)
        if (strpos($uri, '/api/products/') !== false) {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
        } else {
            // Middleware: Check if user is logged in and is admin
            (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        }

        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    protected function maskCostPriceIfNeeded(array $products): array {
        $userRole = strtolower($_SESSION['role'] ?? '');
        $settingModel = new Setting();
        $canSeeCost = ($settingModel->get('salesman_can_see_cost_price') === '1');
        
        if ($userRole === 'salesman' && !$canSeeCost) {
            foreach ($products as &$p) {
                if (is_array($p) && isset($p['cost_price'])) {
                    $p['cost_price'] = 0.00;
                }
            }
        }
        return $products;
    }

    public function autocomplete() {
        header('Content-Type: application/json');
        $query = trim($_GET['q'] ?? '');
        $results = [];
        if ($query !== '') {
            $results = $this->productModel->searchAutocomplete($query, 8);
            $results = $this->maskCostPriceIfNeeded($results);
        }
        echo json_encode(['success' => true, 'data' => $results]);
        exit();
    }

    public function searchGeneric() {
        $query = $_GET['q'] ?? '';
        $products = [];
        if ($query !== '') {
            $products = $this->productModel->searchByGeneric($query);
            $products = $this->maskCostPriceIfNeeded($products);
        }
        header('Content-Type: application/json');
        echo json_encode($products);
        exit();
    }

    public function checkDuplicate() {
        header('Content-Type: application/json');
        $name = trim($_GET['name'] ?? '');
        $excludeId = (int)($_GET['exclude_id'] ?? 0);
        
        $result = $this->productModel->findDuplicatesAndRelated($name, $excludeId);
        
        echo json_encode(array_merge(['success' => true], $result));
        exit();
    }

    public function index() {
        $pageTitle = "Medicine Management";
        $msg = $_SESSION['msg'] ?? '';
        $msgType = $_SESSION['msgType'] ?? '';
        unset($_SESSION['msg'], $_SESSION['msgType']);

        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        $search = trim($_GET['search'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = (int)($settings['records_per_page'] ?? 10);

        $status = trim($_GET['status'] ?? '');
        $minStock = (int)($settings['min_stock'] ?? 10);

        $pagination = $this->productModel->getFilteredProducts($search, $status, $minStock, $page, $perPage);
        $products = $pagination['data'] ?? [];
        $summaryStats = $this->productModel->getSummaryStats();
        
        $categories = $this->categoryModel->all('categories', 'name ASC');
        
        $genericModel = new Generic();
        $generics = $genericModel->all('generics', 'name ASC');
        
        $companyModel = new Company();
        $companies = $companyModel->all('companies', 'name ASC');

        require_once BASE_PATH . '/resources/views/admin/products.php';
    }

    public function store() {
        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $genericId = !empty($_POST['generic_id']) ? (int)$_POST['generic_id'] : null;
            $strength = trim($_POST['strength'] ?? '');
            $batchNumber = !empty($_POST['batch_number']) ? trim($_POST['batch_number']) : null;
            $expiryDate = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
            $companyId = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;
            $manufacturer = trim($_POST['manufacturer'] ?? '');
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $price = isset($_POST['price']) ? round((float)$_POST['price'], 2) : 0.00;
            $tradPrice = isset($_POST['trad_price']) ? round((float)$_POST['trad_price'], 2) : 0.00;
            $costPrice = isset($_POST['cost_price']) ? round((float)$_POST['cost_price'], 2) : 0.00;
            $quantity = $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : 0;
            $minStockLevel = $_POST['min_stock_level'] !== '' ? (int)$_POST['min_stock_level'] : 10;
            $isRx = !empty($_POST['is_prescription_required']) ? 1 : 0;
            
            $barcode = !empty($_POST['barcode']) ? trim($_POST['barcode']) : null;
            
            $imagePath = '';
            $imageUploadError = false;
            if (!empty($settings['enable_product_images']) && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $maxSize = (int)($settings['max_product_image_size'] ?? 5242880);
                if ($_FILES['image']['size'] > $maxSize) {
                    $_SESSION['msg'] = "File exceeds maximum upload size.";
                    $_SESSION['msgType'] = "danger";
                    $imageUploadError = true;
                } else {
                    $targetDir = BASE_PATH . "/public/uploads/products/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                    $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                    $targetFile = $targetDir . $fileName;
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
                    finfo_close($finfo);
                    
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $imageInfo = getimagesize($_FILES["image"]["tmp_name"]); 
                    
                    if(in_array($mime, $allowedMimeTypes) && $imageInfo !== false) {
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                            $imagePath = 'uploads/products/' . $fileName;
                        }
                    } else {
                        $_SESSION['msg'] = "Invalid image format.";
                        $_SESSION['msgType'] = "danger";
                        $imageUploadError = true;
                    }
                }
            }

            if (!$imageUploadError && !empty($name) && !empty($price)) {
                $data = [
                    'name'                    => $name,
                    'generic_id'              => $genericId,
                    'strength'                => $strength,
                    'batch_number'            => $batchNumber,
                    'expiry_date'             => $expiryDate,
                    'company_id'              => $companyId,
                    'manufacturer'            => $manufacturer,
                    'category_id'             => $categoryId,
                    'price'                   => $price,
                    'trad_price'              => $tradPrice,
                    'cost_price'              => $costPrice,
                    'quantity'                => $quantity,
                    'min_stock_level'         => $minStockLevel,
                    'is_prescription_required'=> $isRx,
                    'barcode'                 => $barcode,
                    'image'                   => $imagePath
                ];
                $newProductId = $this->productModel->create($data);
                if ($newProductId) {
                    // Auto-generate barcode if omitted
                    if (empty($barcode)) {
                        $autoBarcode = sprintf("890%08d", $newProductId);
                        $this->productModel->update($newProductId, ['barcode' => $autoBarcode]);
                    }

                    // Create initial batch if quantity > 0
                    if ($quantity > 0) {
                        $batchModel = new \App\Models\ProductBatch();
                        $batchModel->create([
                            'product_id'   => $newProductId,
                            'batch_number' => $batchNumber ?? 'INIT-' . date('Ymd'),
                            'expiry_date'  => $expiryDate,
                            'quantity'     => $quantity,
                            'cost_price'   => $costPrice
                        ]);

                        $logModel = new \App\Models\InventoryLog();
                        $logModel->create([
                            'product_id' => $newProductId,
                            'user_id'    => $_SESSION['user_id'] ?? null,
                            'qty_change' => $quantity,
                            'type'       => 'restock',
                            'remarks'    => "Initial stock on medicine creation"
                        ]);
                    }

                    Cache::forget('dash_total_products');
                    Cache::forget('dash_total_inventory_val');
                    Cache::forget('dash_low_stock_count');
                    $_SESSION['msg'] = "Medicine added successfully!";
                    $_SESSION['msgType'] = "success";
                } else {
                    $_SESSION['msg'] = "Failed to add medicine.";
                    $_SESSION['msgType'] = "danger";
                }
            }
        }
        header('Location: ' . url('/admin/products'));
        exit();
    }

    public function update() {
        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $name = trim($_POST['name'] ?? '');
                $genericId = !empty($_POST['generic_id']) ? (int)$_POST['generic_id'] : null;
                $strength = trim($_POST['strength'] ?? '');
                $batchNumber = !empty($_POST['batch_number']) ? trim($_POST['batch_number']) : null;
                $expiryDate = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
                $companyId = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;
                $manufacturer = trim($_POST['manufacturer'] ?? '');
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $price = isset($_POST['price']) ? round((float)$_POST['price'], 2) : 0.00;
                $tradPrice = isset($_POST['trad_price']) ? round((float)$_POST['trad_price'], 2) : 0.00;
                $costPrice = isset($_POST['cost_price']) ? round((float)$_POST['cost_price'], 2) : 0.00;
                $quantity = $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : 0;
                $minStockLevel = $_POST['min_stock_level'] !== '' ? (int)$_POST['min_stock_level'] : 10;
                $isRx = !empty($_POST['is_prescription_required']) ? 1 : 0;
                $barcode = !empty($_POST['barcode']) ? trim($_POST['barcode']) : null;
                
                $imagePath = '';
                if (!empty($settings['enable_product_images']) && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $targetDir = BASE_PATH . "/public/uploads/products/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                    $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $fileName)) {
                        $imagePath = 'uploads/products/' . $fileName;
                    }
                }

                $data = [
                    'name'                    => $name,
                    'generic_id'              => $genericId,
                    'strength'                => $strength,
                    'batch_number'            => $batchNumber,
                    'expiry_date'             => $expiryDate,
                    'company_id'              => $companyId,
                    'manufacturer'            => $manufacturer,
                    'category_id'             => $categoryId,
                    'price'                   => $price,
                    'trad_price'              => $tradPrice,
                    'cost_price'              => $costPrice,
                    'quantity'                => $quantity,
                    'min_stock_level'         => $minStockLevel,
                    'is_prescription_required'=> $isRx,
                    'barcode'                 => $barcode
                ];
                if ($imagePath != '') {
                    $data['image'] = $imagePath;
                }
                
                if ($this->productModel->update($id, $data)) {
                    Cache::forget('dash_total_products');
                    Cache::forget('dash_total_inventory_val');
                    Cache::forget('dash_low_stock_count');
                    $_SESSION['msg'] = "Medicine updated successfully!";
                    $_SESSION['msgType'] = "success";
                } else {
                    $_SESSION['msg'] = "Failed to update medicine.";
                    $_SESSION['msgType'] = "danger";
                }
            }
        }
        header('Location: ' . url('/admin/products'));
        exit();
    }

    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = (int)($_POST['delete_id'] ?? 0);
            if ($id > 0) {
                if ($this->productModel->deleteProduct($id)) {
                    Cache::forget('dash_total_products');
                    Cache::forget('dash_total_inventory_val');
                    Cache::forget('dash_low_stock_count');
                    $_SESSION['msg'] = "Medicine deleted successfully!";
                    $_SESSION['msgType'] = "success";
                } else {
                    $_SESSION['msg'] = "Failed to delete medicine.";
                    $_SESSION['msgType'] = "danger";
                }
            }
        }
        header('Location: ' . url('/admin/products'));
        exit();
    }

    public function scanLookup() {
        header('Content-Type: application/json');
        $barcode = trim($_GET['barcode'] ?? '');
        if ($barcode === '') {
            echo json_encode(['success' => false, 'message' => 'Barcode required']);
            exit();
        }

        $product = $this->productModel->findByBarcode($barcode);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found for barcode: ' . htmlspecialchars($barcode)]);
            exit();
        }

        $masked = $this->maskCostPriceIfNeeded([$product]);
        echo json_encode(['success' => true, 'product' => $masked[0]]);
        exit();
    }

    public function batchesApi() {
        header('Content-Type: application/json');
        $productId = (int)($_GET['id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            exit();
        }

        $batchModel = new \App\Models\ProductBatch();
        $batches = $batchModel->getByProduct($productId);
        $batches = $this->maskCostPriceIfNeeded($batches);

        echo json_encode(['success' => true, 'batches' => $batches]);
        exit();
    }

    public function printLabel() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Invalid Product ID";
            redirect('/admin/products');
        }

        $product = $this->productModel->find('products', $id);
        if (!$product) {
            $_SESSION['error'] = "Product not found";
            redirect('/admin/products');
        }

        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        $pharmacyName = $settings['company_name'] ?? 'Pharmacy';

        require_once BASE_PATH . '/resources/views/admin/product_label.php';
    }

    public function exportTemplate() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=medicines_import_template.csv');
        
        $output = fopen('php://output', 'w');
        // CSV Headers
        fputcsv($output, [
            'Name',
            'Strength',
            'Generic',
            'Category',
            'Company',
            'Manufacturer',
            'Price',
            'Trade Price',
            'Cost Price',
            'Quantity',
            'Min Stock Level',
            'Prescription Required (0 or 1)',
            'Batch Number',
            'Expiry Date (YYYY-MM-DD)',
            'Barcode'
        ]);

        // Sample Rows
        fputcsv($output, [
            'Panadol Extra',
            '500mg/65mg',
            'Paracetamol + Caffeine',
            'Analgesics',
            'GSK',
            'GlaxoSmithKline',
            '35.00',
            '30.00',
            '28.00',
            '100',
            '20',
            '0',
            'BATCH-001',
            date('Y-m-d', strtotime('+1 year')),
            '890123450001'
        ]);
        fputcsv($output, [
            'Augmentin 625mg',
            '625mg',
            'Amoxicillin + Clavulanic Acid',
            'Antibiotics',
            'GSK',
            'GlaxoSmithKline',
            '240.00',
            '210.00',
            '195.00',
            '50',
            '10',
            '1',
            'BATCH-002',
            date('Y-m-d', strtotime('+18 months')),
            '890123450002'
        ]);

        fclose($output);
        exit();
    }

    public function importForm() {
        $pageTitle = "Import Medicines";
        $categoryModel = new Category();
        $genericModel = new \App\Models\Generic();
        $companyModel = new Company();

        $categories = $categoryModel->all('categories', 'name ASC');
        $generics = $genericModel->all('generics', 'name ASC');
        $companies = $companyModel->all('companies', 'name ASC');

        require_once BASE_PATH . '/resources/views/admin/product_import.php';
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/products/import');
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Please select a valid CSV or Excel spreadsheet file to upload.";
            redirect('/admin/products/import');
        }

        $filePath = $_FILES['file']['tmp_name'];
        $originalName = $_FILES['file']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $rows = [];

        try {
            if ($extension === 'csv' || $extension === 'txt') {
                if (($handle = fopen($filePath, 'r')) !== false) {
                    $header = null;
                    while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                        if (!$header) {
                            $header = array_map(function($h) {
                                return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
                            }, $data);
                        } else {
                            if (count($data) >= 2) {
                                $row = [];
                                foreach ($header as $idx => $colName) {
                                    $row[$colName] = $data[$idx] ?? '';
                                }
                                $rows[] = $row;
                            }
                        }
                    }
                    fclose($handle);
                }
            } else {
                // Try PhpSpreadsheet for xlsx / xls
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $excelData = $worksheet->toArray();
                if (!empty($excelData)) {
                    $rawHeader = array_shift($excelData);
                    $header = array_map(function($h) {
                        return strtolower(trim((string)$h));
                    }, $rawHeader);
                    foreach ($excelData as $data) {
                        if (empty(array_filter($data))) continue;
                        $row = [];
                        foreach ($header as $idx => $colName) {
                            $row[$colName] = $data[$idx] ?? '';
                        }
                        $rows[] = $row;
                    }
                }
            }

            if (empty($rows)) {
                throw new Exception("No valid rows found in uploaded file.");
            }

            $genericModel = new \App\Models\Generic();
            $companyModel = new Company();
            $batchModel = new \App\Models\ProductBatch();
            $logModel = new \App\Models\InventoryLog();

            // Cache existing lookup maps for efficiency
            $genericsMap = [];
            foreach ($genericModel->all('generics') as $g) {
                $genericsMap[strtolower(trim($g['name']))] = (int)$g['id'];
            }
            $categoriesMap = [];
            foreach ($this->categoryModel->all('categories') as $c) {
                $categoriesMap[strtolower(trim($c['name']))] = (int)$c['id'];
            }
            $companiesMap = [];
            foreach ($companyModel->all('companies') as $co) {
                $companiesMap[strtolower(trim($co['name']))] = (int)$co['id'];
            }

            $successCount = 0;
            $skipCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $lineNum = $index + 2;

                // Find fields by fuzzy header keys
                $name = '';
                foreach (['name', 'medicine name', 'product name', 'product'] as $k) {
                    if (!empty($row[$k])) { $name = trim($row[$k]); break; }
                }

                if (empty($name)) {
                    $skipCount++;
                    $errors[] = "Row #$lineNum: Skipped (Medicine Name is empty)";
                    continue;
                }

                $price = 0.00;
                foreach (['price', 'sale price', 'mrp', 'retail price'] as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') { $price = (float)$row[$k]; break; }
                }

                if ($price <= 0) {
                    $skipCount++;
                    $errors[] = "Row #$lineNum ($name): Skipped (Invalid or missing sale price)";
                    continue;
                }

                $strength = '';
                foreach (['strength', 'dose', 'dosage'] as $k) {
                    if (!empty($row[$k])) { $strength = trim($row[$k]); break; }
                }

                $genericName = '';
                foreach (['generic', 'generic name', 'formula'] as $k) {
                    if (!empty($row[$k])) { $genericName = trim($row[$k]); break; }
                }
                $genericId = null;
                if (!empty($genericName)) {
                    $gKey = strtolower($genericName);
                    if (isset($genericsMap[$gKey])) {
                        $genericId = $genericsMap[$gKey];
                    } else {
                        $genericId = $genericModel->create(['name' => $genericName]);
                        if ($genericId) $genericsMap[$gKey] = (int)$genericId;
                    }
                }

                $categoryName = '';
                foreach (['category', 'category name'] as $k) {
                    if (!empty($row[$k])) { $categoryName = trim($row[$k]); break; }
                }
                $categoryId = null;
                if (!empty($categoryName)) {
                    $cKey = strtolower($categoryName);
                    if (isset($categoriesMap[$cKey])) {
                        $categoryId = $categoriesMap[$cKey];
                    } else {
                        $categoryId = $this->categoryModel->create(['name' => $categoryName]);
                        if ($categoryId) $categoriesMap[$cKey] = (int)$categoryId;
                    }
                }

                $companyName = '';
                foreach (['company', 'company name', 'manufacturer'] as $k) {
                    if (!empty($row[$k])) { $companyName = trim($row[$k]); break; }
                }
                $companyId = null;
                if (!empty($companyName)) {
                    $coKey = strtolower($companyName);
                    if (isset($companiesMap[$coKey])) {
                        $companyId = $companiesMap[$coKey];
                    } else {
                        $companyId = $companyModel->create(['name' => $companyName]);
                        if ($companyId) $companiesMap[$coKey] = (int)$companyId;
                    }
                }

                $tradPrice = 0.00;
                foreach (['trade price', 'trad price', 'trad_price', 'tp'] as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') { $tradPrice = (float)$row[$k]; break; }
                }

                $costPrice = 0.00;
                foreach (['cost price', 'cost_price', 'purchase price', 'cost'] as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') { $costPrice = (float)$row[$k]; break; }
                }

                $qty = 0;
                foreach (['quantity', 'stock', 'qty', 'opening stock'] as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') { $qty = (int)$row[$k]; break; }
                }

                $minStock = 10;
                foreach (['min stock level', 'min_stock_level', 'min stock', 'reorder level'] as $k) {
                    if (isset($row[$k]) && $row[$k] !== '') { $minStock = (int)$row[$k]; break; }
                }

                $isRx = 0;
                foreach (['prescription required (0 or 1)', 'prescription required', 'is_prescription_required', 'rx', 'prescription'] as $k) {
                    if (isset($row[$k])) {
                        $val = strtolower(trim((string)$row[$k]));
                        if (in_array($val, ['1', 'yes', 'true', 'rx', 'y'])) {
                            $isRx = 1;
                        }
                        break;
                    }
                }

                $batchNum = null;
                foreach (['batch number', 'batch_number', 'batch'] as $k) {
                    if (!empty($row[$k])) { $batchNum = trim($row[$k]); break; }
                }

                $expiryDate = null;
                foreach (['expiry date (yyyy-mm-dd)', 'expiry date', 'expiry_date', 'expiry', 'exp date'] as $k) {
                    if (!empty($row[$k])) {
                        $rawExp = trim($row[$k]);
                        $ts = strtotime($rawExp);
                        if ($ts !== false) {
                            $expiryDate = date('Y-m-d', $ts);
                        }
                        break;
                    }
                }

                $barcode = null;
                foreach (['barcode', 'ean', 'upc', 'code'] as $k) {
                    if (!empty($row[$k])) { $barcode = trim($row[$k]); break; }
                }

                $productData = [
                    'name'                     => $name,
                    'generic_id'               => $genericId,
                    'strength'                 => $strength,
                    'batch_number'             => $batchNum,
                    'expiry_date'              => $expiryDate,
                    'company_id'               => $companyId,
                    'manufacturer'             => $companyName,
                    'category_id'              => $categoryId,
                    'price'                    => $price,
                    'trad_price'               => $tradPrice,
                    'cost_price'               => $costPrice,
                    'quantity'                 => $qty,
                    'min_stock_level'          => $minStock,
                    'is_prescription_required' => $isRx,
                    'barcode'                  => $barcode,
                    'image'                    => ''
                ];

                $newId = $this->productModel->create($productData);
                if ($newId) {
                    if (empty($barcode)) {
                        $autoBarcode = sprintf("890%08d", $newId);
                        $this->productModel->update($newId, ['barcode' => $autoBarcode]);
                    }

                    if ($qty > 0) {
                        $batchModel->create([
                            'product_id'   => $newId,
                            'batch_number' => $batchNum ?? 'INIT-' . date('Ymd'),
                            'expiry_date'  => $expiryDate,
                            'quantity'     => $qty,
                            'cost_price'   => $costPrice
                        ]);

                        $logModel->create([
                            'product_id' => $newId,
                            'user_id'    => $_SESSION['user_id'] ?? null,
                            'qty_change' => $qty,
                            'type'       => 'restock',
                            'remarks'    => "Initial stock on CSV import"
                        ]);
                    }

                    $successCount++;
                } else {
                    $skipCount++;
                    $errors[] = "Row #$lineNum ($name): Database insert failed.";
                }
            }

            if ($successCount > 0) {
                Cache::forget('dash_total_products');
                Cache::forget('dash_total_inventory_val');
                Cache::forget('dash_low_stock_count');
            }

            $_SESSION['import_summary'] = [
                'success_count' => $successCount,
                'skip_count'    => $skipCount,
                'errors'        => $errors
            ];
            $_SESSION['msg'] = "Successfully imported $successCount medicines ($skipCount skipped).";
            $_SESSION['msgType'] = $successCount > 0 ? 'success' : 'warning';

        } catch (Exception $e) {
            $_SESSION['error'] = "Error importing file: " . $e->getMessage();
        }

        redirect('/admin/products/import');
    }
}
