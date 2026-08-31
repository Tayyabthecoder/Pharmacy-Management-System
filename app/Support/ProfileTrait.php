<?php

namespace App\Support;

use Exception;
use PDO;
use PDOException;

trait ProfileTrait {
    public function profile() {
        $pageTitle = "My Profile";
        $user_id = (int)$_SESSION['user_id'];
        
        $msg = '';
        $msgType = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action']) && $_POST['action'] == 'update') {
                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $bio = trim($_POST['bio'] ?? '');
                $address = trim($_POST['address'] ?? '');
                
                if (!empty($name)) {
                    $data = [
                        'name' => $name,
                        'phone' => $phone,
                        'bio' => $bio,
                        'address' => $address
                    ];
                    if ($this->userModel->update($user_id, $data)) {
                        $_SESSION['name'] = $name;
                        $msg = "Profile updated successfully!";
                        $msgType = "success";
                    } else {
                        $msg = "Failed to update profile.";
                        $msgType = "danger";
                    }
                } else {
                    $msg = "Name is required.";
                    $msgType = "danger";
                }
            } elseif (isset($_POST['action']) && $_POST['action'] == 'change_password') {
                $current = $_POST['current_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';
                
                $user = $this->userModel->find('users', $user_id);
                
                if (password_verify($current, $user['password'])) {
                    if ($new === $confirm) {
                        if (strlen($new) < 8 || !preg_match("/[a-z]/i", $new) || !preg_match("/[0-9]/", $new) || !preg_match("/[^a-zA-Z0-9]/", $new)) {
                            $msg = "Password must be at least 8 characters long, include a letter, a number, and a special character.";
                            $msgType = "danger";
                        } else {
                            $hashed = password_hash($new, PASSWORD_DEFAULT);
                            if ($this->userModel->update($user_id, ['password' => $hashed])) {
                                $msg = "Password updated successfully!";
                                $msgType = "success";
                            } else {
                                $msg = "Failed to update password.";
                                $msgType = "danger";
                            }
                        }
                    } else {
                        $msg = "New passwords do not match.";
                        $msgType = "danger";
                    }
                } else {
                    $msg = "Incorrect current password.";
                    $msgType = "danger";
                }
            } elseif (isset($_POST['action']) && $_POST['action'] == 'edit_photo') {
                // Photo upload
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES["profile_image"]["tmp_name"]);
                    finfo_close($finfo);
                    
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $imageInfo = getimagesize($_FILES["profile_image"]["tmp_name"]);
                    
                    if (in_array($ext, $allowed) && in_array($mime, $allowedMimeTypes) && $imageInfo !== false) {
                        $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                        $upload_dir = BASE_PATH . '/public/uploads/profiles/';
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                            $user = $this->userModel->find('users', $user_id);
                            $old_img = $user['user_image'] ?? null;
                            
                            if ($old_img && file_exists($upload_dir . $old_img)) {
                                unlink($upload_dir . $old_img);
                            }
                            
                            if ($this->userModel->update($user_id, ['user_image' => $new_filename])) {
                                $msg = "Profile photo updated successfully!";
                                $msgType = "success";
                            } else {
                                $msg = "Failed to update profile photo in database.";
                                $msgType = "danger";
                            }
                        } else {
                            $msg = "Failed to move uploaded file.";
                            $msgType = "danger";
                        }
                    } else {
                        $msg = "Invalid image file type.";
                        $msgType = "danger";
                    }
                } else {
                    $msg = "No file selected or upload error.";
                    $msgType = "danger";
                }
            }
        }
        
        $user = $this->userModel->find('users', $user_id);
        
        require_once BASE_PATH . '/resources/views/admin/profile.php';
    }
}
