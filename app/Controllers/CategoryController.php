<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\Category;
use App\Models\Generic;

class CategoryController {
    protected $categoryModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        (new \App\Middleware\RoleMiddleware(['admin', 'owner']))->handle();
        $this->categoryModel = new Category();
    }

    public function index() {
        $pageTitle = "Categories & Generic Names Management";
        
        $categories = $this->categoryModel->getAllWithProductCount();
        
        $genericModel = new Generic();
        $generics = $genericModel->getAllWithProductCount();

        require_once BASE_PATH . '/resources/views/admin/categories_generics.php';
    }

    public function storeAjax() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required.']);
            exit;
        }

        try {
            // Check if category already exists
            $existing = $this->categoryModel->findByName($name);
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category already exists.',
                    'data' => [
                        'id' => $existing['id'],
                        'name' => $existing['name']
                    ]
                ]);
                exit;
            }

            $data = ['name' => $name, 'description' => $description];
            if ($this->categoryModel->create($data)) {
                $newId = $this->categoryModel->lastInsertId();
                echo json_encode([
                    'success' => true,
                    'message' => 'Category added successfully!',
                    'data' => [
                        'id' => $newId,
                        'name' => $name,
                        'description' => $description
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save category.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function updateAjax() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid category ID.']);
            exit;
        }

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name cannot be empty.']);
            exit;
        }

        try {
            // Check duplicate name excluding this ID
            $existing = $this->categoryModel->findByNameExcludingId($name, $id);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Another category with this name already exists.']);
                exit;
            }

            $data = ['name' => $name, 'description' => $description];
            if ($this->categoryModel->update($id, $data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category updated successfully!',
                    'data' => [
                        'id' => $id,
                        'name' => $name,
                        'description' => $description
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function deleteAjax() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid category ID.']);
            exit;
        }

        try {
            if ($this->categoryModel->deleteCategory($id)) {
                echo json_encode(['success' => true, 'message' => 'Category deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
