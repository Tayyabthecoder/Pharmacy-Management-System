<?php

namespace App\Controllers;

use Exception;
use App\Models\InventoryLog;
use App\Models\User;
use App\Models\Setting;
use App\Middleware\RoleMiddleware;

class AuditLogController {
    protected $logModel;
    protected $userModel;
    protected $settingModel;

    public function __construct() {
        (new RoleMiddleware(['admin']))->handle();
        $this->logModel = new InventoryLog();
        $this->userModel = new User();
        $this->settingModel = new Setting();
    }

    public function index() {
        $pageTitle = "Inventory & System Audit Logs";

        $filters = [
            'search'     => trim($_GET['search'] ?? ''),
            'type'       => trim($_GET['type'] ?? ''),
            'user_id'    => !empty($_GET['user_id']) ? (int)$_GET['user_id'] : '',
            'start_date' => trim($_GET['start_date'] ?? ''),
            'end_date'   => trim($_GET['end_date'] ?? '')
        ];

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = (int)$this->settingModel->get('records_per_page', 20);

        $pagination = $this->logModel->getFilteredLogs($page, $perPage, $filters);
        $logs = $pagination['data'];

        $users = $this->userModel->all('users', 'name ASC');
        $todaySummary = $this->logModel->getTodaySummary();

        require_once BASE_PATH . '/resources/views/admin/audit_log.php';
    }
}
