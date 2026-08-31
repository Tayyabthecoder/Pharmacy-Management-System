<?php

namespace App\Models;

use Exception;
use PDO;
use PDOException;
use App\Models\BaseModel;

class Setting extends BaseModel {
    protected $table = 'system_settings';
    protected static array $cache = [];

    public function getAll() {
        if (!empty(self::$cache)) {
            return self::$cache;
        }
        $settings = [];
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        while ($row = $stmt->fetch()) {
            $settings[$row['meta_key']] = $row['meta_value'];
        }
        self::$cache = $settings;
        return self::$cache;
    }

    public function updateSetting($key, $value) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (meta_key, meta_value) VALUES (:key, :value) ON CONFLICT(meta_key) DO UPDATE SET meta_value = :update_value");
        $result = $stmt->execute(['key' => $key, 'value' => $value, 'update_value' => $value]);
        self::$cache[$key] = (string)$value;
        return $result;
    }

    public function get($key, $default = null) {
        $all = $this->getAll();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function getSetting($key, $default = null) {
        $instance = new self();
        return $instance->get($key, $default);
    }

    public static function getAllSettings() {
        $instance = new self();
        return $instance->getAll();
    }
}
