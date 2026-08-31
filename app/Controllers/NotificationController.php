<?php

namespace App\Controllers;

use Exception;
use App\Models\Notification;

class NotificationController {
    protected $notificationModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin', 'salesman']))->handle();
        $this->notificationModel = new Notification();
    }

    public function index() {
        $pageTitle = "All Notifications";
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 15;
        $result = $this->notificationModel->getByUser($_SESSION['user_id'], $page, $perPage);
        $notifications = $result['data'] ?? [];
        $totalPages = $result['total_pages'] ?? 1;
        $currentPage = $result['current_page'] ?? 1;

        $role = strtolower($_SESSION['role'] ?? '');
        $username = strtolower($_SESSION['username'] ?? '');
        $isAdmin = ($role === 'admin' || $role === 'owner' || $username === 'owner' || ($_SESSION['user_id'] ?? 0) == 999999);
        $viewDir = $isAdmin ? 'admin' : 'salesman';
        require_once BASE_PATH . "/resources/views/{$viewDir}/notifications.php";
    }

    public function indexAdmin() {
        $this->index();
    }

    public function indexSalesman() {
        $this->index();
    }


    public function fetch() {
        header('Content-Type: application/json');
        try {
            $notifications = $this->notificationModel->getUnreadByUser($_SESSION['user_id']);
            $count = $this->notificationModel->countUnreadByUser($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'count' => $count,
                'data' => $notifications
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function read() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $this->notificationModel->markAsRead($id, $_SESSION['user_id']);
                echo json_encode(['success' => true]);
                return;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    public function readAll() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->notificationModel->markAllAsRead($_SESSION['user_id']);
            echo json_encode(['success' => true]);
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
}
