<?php
// src/Models/UsdtPayment.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Settings.php';

class UsdtPayment {
    private PDO $db;
    private Settings $settings;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->settings = new Settings();
    }

    public function getSettings(): array {
        return [
            'trc20_address' => $this->settings->get('usdt_trc20_address') ?: 'TYDzpSLtq8X2yB33x9v2QG2H1234567890',
            'bep20_address' => $this->settings->get('usdt_bep20_address') ?: '0x572790c26FC062C247D0e1797D646F20a093AE14',
            'inr_rate' => (float)($this->settings->get('usdt_inr_rate') ?: 93.76),
        ];
    }

    public function createPayment(int $userId, int $packageId, float $amountInr, string $network, string $txid): int {
        $config = $this->getSettings();
        $rate = $config['inr_rate'] > 0 ? $config['inr_rate'] : 93.76;
        $amountUsdt = round($amountInr / $rate, 2);
        $walletAddress = ($network === 'BEP20') ? $config['bep20_address'] : $config['trc20_address'];

        $stmt = $this->db->prepare("
            INSERT INTO usdt_payments (user_id, package_id, amount_inr, amount_usdt, network, wallet_address, txid, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $packageId, $amountInr, $amountUsdt, $network, $walletAddress, $txid]);
        return (int)$this->db->lastInsertId();
    }

    public function getUserPayments(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT p.*, pkg.name as package_name, pkg.price as package_price
            FROM usdt_payments p
            JOIN packages pkg ON p.package_id = pkg.id
            WHERE p.user_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllPending(): array {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.full_name, u.email, pkg.name as package_name
            FROM usdt_payments p
            JOIN users u ON p.user_id = u.id
            JOIN packages pkg ON p.package_id = pkg.id
            WHERE p.status = 'pending'
            ORDER BY p.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAll(): array {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.full_name, u.email, pkg.name as package_name
            FROM usdt_payments p
            JOIN users u ON p.user_id = u.id
            JOIN packages pkg ON p.package_id = pkg.id
            ORDER BY p.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM usdt_payments WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): bool {
        $stmt = $this->db->prepare("
            UPDATE usdt_payments
            SET status = ?, admin_notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([$status, $notes, $id]);
    }
}
