<?php

namespace App\Controllers;

use App\Models\Generic;

class GenericController {
    protected $genericModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        (new \App\Middleware\RoleMiddleware(['admin', 'owner']))->handle();
        $this->genericModel = new Generic();
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
            echo json_encode(['success' => false, 'message' => 'Generic name is required.']);
            exit;
        }

        try {
            // Check if generic already exists
            $existing = $this->genericModel->findByName($name);
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Generic name already exists.',
                    'data' => [
                        'id' => $existing['id'],
                        'name' => $existing['name'],
                        'description' => $existing['description'] ?? ''
                    ]
                ]);
                exit;
            }

            $data = ['name' => $name, 'description' => $description];
            if ($this->genericModel->create($data)) {
                $newId = $this->genericModel->lastInsertId();
                echo json_encode([
                    'success' => true,
                    'message' => 'Generic name added successfully!',
                    'data' => [
                        'id' => $newId,
                        'name' => $name,
                        'description' => $description
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save generic name.']);
            }
        } catch (\Exception $e) {
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
            echo json_encode(['success' => false, 'message' => 'Invalid generic ID.']);
            exit;
        }

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Generic name cannot be empty.']);
            exit;
        }

        try {
            // Check duplicate name excluding this ID
            $existing = $this->genericModel->findByNameExcludingId($name, $id);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Another generic composition with this name already exists.']);
                exit;
            }

            $data = ['name' => $name, 'description' => $description];
            if ($this->genericModel->update($id, $data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Generic composition updated successfully!',
                    'data' => [
                        'id' => $id,
                        'name' => $name,
                        'description' => $description
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update generic composition.']);
            }
        } catch (\Exception $e) {
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
            echo json_encode(['success' => false, 'message' => 'Invalid generic ID.']);
            exit;
        }

        try {
            if ($this->genericModel->deleteGeneric($id)) {
                echo json_encode(['success' => true, 'message' => 'Generic composition deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete generic composition.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
