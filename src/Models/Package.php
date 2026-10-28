<?php
// src/Models/Package.php

require_once __DIR__ . '/../../config/database.php';

class Package {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM packages ORDER BY sort_order ASC, price ASC");
        return $stmt->fetchAll();
    }

    public function getAllActive(): array {
        $stmt = $this->db->query("SELECT * FROM packages WHERE status = 'active' ORDER BY sort_order ASC, price ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $pkg = $stmt->fetch();
        return $pkg ?: null;
    }

    public function getNextPackage(int $currentPackageId): ?array {
        $current = $this->getById($currentPackageId);
        if (!$current) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM packages
            WHERE sort_order > ? AND status = 'active'
            ORDER BY sort_order ASC, price ASC
            LIMIT 1
        ");
        $stmt->execute([$current['sort_order']]);
        $next = $stmt->fetch();
        return $next ?: null;
    }

    public function update(int $id, string $name, float $price, float $directCommissionPercent): bool {
        $stmt = $this->db->prepare("
            UPDATE packages
            SET name = ?, price = ?, direct_commission_percent = ?
            WHERE id = ?
        ");
        return $stmt->execute([$name, $price, $directCommissionPercent, $id]);
    }
}
