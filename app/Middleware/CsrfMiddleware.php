<?php

namespace App\Middleware;

use Exception;
use PDO;
use PDOException;

/**
 * CsrfMiddleware
 *
 * Globally intercepts all HTTP POST, PUT, PATCH, DELETE requests and validates
 * the CSRF token before the request reaches any Controller.
 *
 * Exempt routes (e.g. webhooks) can be added to the $except array.
 */
class CsrfMiddleware {

    /**
     * Routes that are exempt from CSRF validation.
     * Add API webhook endpoints here if needed.
     */
    protected array $except = [];

    /**
     * Run the middleware. Returns true if the request passes, or terminates with
     * an error response if it fails.
     *
     * @param string $uri  The current request URI
     * @return bool
     */
    public function handle(string $uri): bool {
        // Only validate on state-changing HTTP methods
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true; // GET requests always pass
        }

        // Check if this route is explicitly exempt
        foreach ($this->except as $exemptUri) {
            if ($uri === $exemptUri) {
                return true;
            }
        }

        // Validate the CSRF token
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!$this->validateToken($token)) {
            $this->abort();
        }

        return true;
    }

    /**
     * Validate the token using a timing-safe comparison.
     */
    protected function validateToken(string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Terminate the request with a 419 CSRF error response.
     */
    protected function abort(): void {
        http_response_code(419);
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>419 - Request Expired</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f1f5f9;
            --text-muted: #94a3b8;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-box {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -0.05em;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .error-msg {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: #fff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class='error-box'>
        <div class='error-code'>419</div>
        <div class='error-title'>Request Expired</div>
        <p class='error-msg'>
            Your form submission could not be verified.<br>
            This usually happens when the page has been open too long.<br>
            Please go back and try again.
        </p>
        <a href='javascript:history.back()' class='btn'>&#8592; Go Back</a>
    </div>
</body>
</html>";
        exit();
    }
}
