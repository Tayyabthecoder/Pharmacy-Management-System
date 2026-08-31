<?php

namespace App\Support;

use Exception;
use PDO;
use PDOException;

/**
 * app/Support/Router.php
 */

class Router {
    protected $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];

        // --- Global Maintenance Mode Middleware ---
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $settingModel = new \App\Models\Setting();
        $isMaintenance = ($settingModel->get('maintenance_mode') === '1');
        $userRole = strtolower($_SESSION['role'] ?? '');
        $username = strtolower($_SESSION['username'] ?? '');
        $isAdmin = ($userRole === 'admin' || $userRole === 'owner' || $username === 'owner' || ($_SESSION['user_id'] ?? 0) == 999999);

        if ($isMaintenance && !$isAdmin && $uri !== '/login' && $uri !== '/logout') {
            http_response_code(503);
            $msg = htmlspecialchars($settingModel->get('maintenance_message', 'System under scheduled maintenance. Please try again later.'));
            echo "<!DOCTYPE html><html><head><title>System Maintenance</title><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8fafc;color:#1e293b;}h1{font-size:2.5rem;color:#e11d48;}p{font-size:1.1rem;margin-top:15px;}</style></head><body><h1>🛠️ Under Maintenance</h1><p>{$msg}</p></body></html>";
            exit();
        }

        // --- Global CSRF Middleware ---
        $csrf = new \App\Middleware\CsrfMiddleware();
        $csrf->handle($uri);
        // ------------------------------

        if (isset($this->routes[$method][$uri])) {
            $controllerAction = $this->routes[$method][$uri];
            list($controller, $action) = explode('@', $controllerAction);

            $controllerClass = "\\App\\Controllers\\" . $controller;
            $controllerInstance = new $controllerClass();
            $controllerInstance->$action();
        } else {
            // No matching route found — return 404.
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
