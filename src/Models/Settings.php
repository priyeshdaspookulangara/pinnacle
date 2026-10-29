<?php
// src/Models/Settings.php

require_once __DIR__ . '/../../config/database.php';

class Settings {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value, description FROM settings");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function get(string $key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public function updateMany(array $settings): bool {
        $this->db->beginTransaction();
        try {
            $stmtCheck = $this->db->prepare("SELECT setting_key FROM settings WHERE setting_key = ?");
            $stmtUp = $this->db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmtIns = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");

            foreach ($settings as $key => $value) {
                $stmtCheck->execute([$key]);
                if ($stmtCheck->fetchColumn() !== false) {
                    $stmtUp->execute([(string)$value, $key]);
                } else {
                    $stmtIns->execute([$key, (string)$value]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
