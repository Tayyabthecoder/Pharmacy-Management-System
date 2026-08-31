<?php

namespace App\Controllers;

use Exception;
use PDO;
use PDOException;
use App\Models\User;
use App\Models\Setting;

class UserController {
    protected $userModel;

    public function __construct() {
        (new \App\Middleware\RoleMiddleware(['admin']))->handle();
        $this->userModel = new User();
    }

    public function index() {
        $pageTitle = "User Management";

        // Handle Form Submission
        $msg = '';
        $msgType = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (isset($_POST['action'])) {
                $name = trim($_POST['name'] ?? '');
                $username = strtolower(trim($_POST['username'] ?? ''));
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? '';
                $status = $_POST['status'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if ($_POST['action'] == 'add') {
                    if (!empty($name) && !empty($username) && !empty($email) && !empty($password)) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $msg = "Please enter a valid email address.";
                            $msgType = "danger";
                        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                            $msg = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
                            $msgType = "danger";
                        } else {
                            // Password Strength Validation
                            if (strlen($password) < 8 || !preg_match("/[a-z]/i", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[^a-zA-Z0-9]/", $password)) {
                                $msg = "Password must be at least 8 characters long, include a letter, a number, and a special character.";
                                $msgType = "danger";
                            } else {
                                // Check if email or username exists
                                if ($this->userModel->findByUsername($username)) {
                                    $msg = "Username already exists!";
                                    $msgType = "danger";
                                } elseif ($this->userModel->findByEmail($email)) {
                                    $msg = "Email already exists!";
                                    $msgType = "danger";
                                } else {
                                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                                    $data = [
                                        'name' => $name,
                                        'username' => $username,
                                        'email' => $email,
                                        'password' => $hashedPassword,
                                        'role' => $role,
                                        'status' => $status
                                    ];
                                    if ($this->userModel->create($data)) {
                                        $msg = "User added successfully!";
                                        $msgType = "success";
                                    } else {
                                        $msg = "Failed to add user.";
                                        $msgType = "danger";
                                    }
                                }
                            }
                        }
                    } else {
                        $msg = "Please fill in all required fields (Name, Username, Email, Password).";
                        $msgType = "danger";
                    }
                } elseif ($_POST['action'] == 'edit') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id > 0 && !empty($username) && !empty($email)) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $msg = "Please enter a valid email address.";
                            $msgType = "danger";
                        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                            $msg = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
                            $msgType = "danger";
                        } else {
                            // Check if duplicate username or email exists for other users
                            $existingUser = $this->userModel->findByUsername($username);
                            $existingEmailUser = $this->userModel->findByEmail($email);
                            
                            if ($existingUser && (int)$existingUser['id'] !== $id) {
                                $msg = "Username already exists!";
                                $msgType = "danger";
                            } elseif ($existingEmailUser && (int)$existingEmailUser['id'] !== $id) {
                                $msg = "Email already exists!";
                                $msgType = "danger";
                            } else {
                                $data = ['name' => $name, 'username' => $username, 'email' => $email, 'role' => $role, 'status' => $status];
                                $passwordValid = true;

                                // Update password if provided
                                if (!empty($password)) {
                                    if (strlen($password) < 8 || !preg_match("/[a-z]/i", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[^a-zA-Z0-9]/", $password)) {
                                        $msg = "Password must be at least 8 characters long, include a letter, a number, and a special character.";
                                        $msgType = "danger";
                                        $passwordValid = false;
                                    } else {
                                        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                                    }
                                }
                                
                                if ($passwordValid) {
                                    if ($this->userModel->update($id, $data)) {
                                        $msg = "User updated successfully!";
                                        $msgType = "success";
                                    } else {
                                        $msg = "Failed to update user.";
                                        $msgType = "danger";
                                    }
                                }
                            }
                        }
                    } else {
                        $msg = "Please fill in all required fields (Name, Username, Email).";
                        $msgType = "danger";
                    }
                } elseif ($_POST['action'] == 'edit_photo') {
                    // Profile photo updates are handled by ProfileController
                } elseif ($_POST['action'] == 'delete') {
                    $id = (int)($_POST['delete_id'] ?? 0);
                    if ($id > 0) {
                        // Prevent deleting self
                        if ($id === (int)$_SESSION['user_id']) {
                            $msg = "You cannot delete yourself!";
                            $msgType = "danger";
                        } else {
                            if ($this->userModel->deleteUser($id)) {
                                $msg = "User deleted successfully!";
                                $msgType = "success";
                            } else {
                                $msg = "Failed to delete user.";
                                $msgType = "danger";
                            }
                        }
                    }
                }
            }
        }

        // Fetch Users using Model with Pagination
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $settingModel = new Setting();
        $perPage = (int)$settingModel->get('records_per_page', 10);

        $pagination = $this->userModel->paginate('users', $page, $perPage, '1=1', [], 'created_at DESC');
        $users = $pagination['data'];

        require_once BASE_PATH . '/resources/views/admin/users.php';
    }
}

