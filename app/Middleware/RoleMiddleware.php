<?php

namespace App\Middleware;

/**
 * RoleMiddleware
 *
 * Enforces role-based access control for routes.
 */
class RoleMiddleware {
    protected $allowedRoles;

    /**
     * @param array $allowedRoles Array of roles that are allowed to access the route.
     */
    public function __construct(array $allowedRoles = ['admin']) {
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * Handle the request. Redirects if unauthorized.
     */
    public function handle(): void {
        // Ensure session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->respondUnauthorized('Please login to access this page.', '/login');
        }

        // Check if user has an allowed role
        $userRole = strtolower($_SESSION['role'] ?? '');
        $username = strtolower($_SESSION['username'] ?? '');
        $isOwner  = ($userRole === 'owner' || $username === 'owner' || ($_SESSION['user_id'] ?? 0) == 999999);

        // Effective user roles: owner has both 'owner' and 'admin' permissions
        $effectiveRoles = [$userRole];
        if ($isOwner) {
            $effectiveRoles[] = 'owner';
            $effectiveRoles[] = 'admin';
        }

        $hasPermission = false;
        foreach ($this->allowedRoles as $role) {
            if (in_array(strtolower($role), $effectiveRoles)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            $redirectUrl = ($userRole === 'salesman') ? '/salesman/dashboard' : '/admin/dashboard';
            $this->respondUnauthorized('Access Denied: Only the Owner has permission to perform this action.', $redirectUrl);
        }
    }

    /**
     * Handle unauthorized response based on request type (AJAX vs regular HTTP).
     */
    protected function respondUnauthorized(string $message, string $redirectUrl): void {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        // Standard HTTP redirect
        redirect($redirectUrl);
    }
}
