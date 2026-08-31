<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\User;
use App\Support\ProfileTrait;

require_once BASE_PATH . '/app/Support/ProfileTrait.php';

class ProfileController {
    use ProfileTrait;

    protected $userModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin', 'salesman']))->handle();
        $this->userModel = new User();
    }
}

