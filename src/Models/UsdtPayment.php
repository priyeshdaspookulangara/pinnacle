<?php
// src/Models/UsdtPayment.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/../Services/MatrixService.php';

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
            'trc20_qr' => $this->settings->get('usdt_trc20_qr') ?: 'public/assets/images/usdt_bep20_qr_clean.png',
            'bep20_qr' => $this->settings->get('usdt_bep20_qr') ?: 'public/assets/images/usdt_bep20_qr_clean.png',
            'inr_rate' => (float)($this->settings->get('usdt_inr_rate') ?: 90.00),
        ];
    }

    /**
     * Flexible createPayment accepting:
     * - (userId, packageId, amountInr, network, txid)
     * - OR (userId, packageId, amountInr, amountUsdt, network, walletAddress, txid)
     */
    public function createPayment(int $userId, int $packageId, float $amountInr, ...$params): int {
        $config = $this->getSettings();
        $rate = $config['inr_rate'] > 0 ? $config['inr_rate'] : 90.0;

        $amountUsdt = null;
        $network = 'TRC20';
        $walletAddress = null;
        $txid = null;

        if (count($params) >= 1) {
            if (is_numeric($params[0])) {
                $amountUsdt = (float)$params[0];
                $network = isset($params[1]) ? (string)$params[1] : 'TRC20';
                $walletAddress = isset($params[2]) ? (string)$params[2] : null;
                $txid = isset($params[3]) ? (string)$params[3] : null;
            } else {
                $network = (string)$params[0];
                $txid = isset($params[1]) ? (string)$params[1] : null;
            }
        }

        if ($amountUsdt === null || $amountUsdt <= 0) {
            $amountUsdt = round($amountInr / $rate, 2);
        }
        if (empty($walletAddress)) {
            $walletAddress = ($network === 'BEP20') ? $config['bep20_address'] : $config['trc20_address'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO usdt_payments (user_id, package_id, amount_inr, amount_usdt, network, wallet_address, txid, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $packageId, $amountInr, $amountUsdt, $network, $walletAddress, $txid]);
        return (int)$this->db->lastInsertId();
    }

    public function approvePayment(int $paymentId, ?int $adminId = null, ?string $notes = null): bool {
        $payment = $this->findById($paymentId);
        if (!$payment || $payment['status'] !== 'pending') {
            return false;
        }

        // Activate user
        $stmtAct = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmtAct->execute([$payment['user_id']]);

        // Mark payment approved
        $this->updateStatus($paymentId, 'approved', $notes ?: 'Approved via USDT verification');

        // Place user in BFS Matrix
        $matrixService = new MatrixService();
        $matrixService->purchaseAndPlace((int)$payment['user_id'], (int)$payment['package_id']);

        return true;
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
