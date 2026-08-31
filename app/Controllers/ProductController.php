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

class ProductController {
    protected $productModel;
    protected $categoryModel;

    public function __construct() {
        // Allow salesman to search generics, but restrict CRUD index to admin
        $action = $_GET['action'] ?? '';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // If checking api search, allow logged in users (admin/owner/salesman) as well
        if (strpos($uri, '/api/products/search_generic') !== false || strpos($uri, '/api/products/autocomplete') !== false || strpos($uri, '/api/products/check_duplicate') !== false) {
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
                    'image'                   => $imagePath
                ];
                if ($this->productModel->create($data)) {
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
                    'min_stock_level'         => $minStockLevel
                ];
                if ($imagePath != '') {
                    $data['image'] = $imagePath;
                }
                
                if ($this->productModel->update($id, $data)) {
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

}
