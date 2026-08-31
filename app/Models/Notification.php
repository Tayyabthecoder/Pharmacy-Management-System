<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class Notification extends BaseModel {
    protected $table = 'notifications';

    public function getUnreadByUser($userId, $limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUser($userId, $page = 1, $perPage = 15) {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id");
        $countStmt->execute(['user_id' => $userId]);
        $total = (int) $countStmt->fetchColumn();
        
        // Get paginated data
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    public function countUnreadByUser($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAsRead($id, $userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function send($userId, $title, $message, $type = 'info', $link = null, $deduplicateTitle = false) {
        if ($deduplicateTitle) {
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE user_id = :user_id AND title = :title AND is_read = 0 LIMIT 1");
            $stmt->execute(['user_id' => $userId, 'title' => $title]);
            if ($stmt->fetch()) {
                return false; // Already sent and unread
            }
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, title, message, type, link) VALUES (:user_id, :title, :message, :type, :link)");
        return $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link
        ]);
    }

    // Helper to notify all admins
    public function notifyAdmins($title, $message, $type = 'info', $link = null, $deduplicateTitle = false) {
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        $admins = $stmt->fetchAll();
        foreach ($admins as $admin) {
            $this->send($admin['id'], $title, $message, $type, $link, $deduplicateTitle);
        }
        return true;
    }
}
