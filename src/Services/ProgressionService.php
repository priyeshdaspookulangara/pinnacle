<?php
// src/Services/ProgressionService.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Package.php';

class ProgressionService {
    private PDO $db;
    private User $userModel;
    private Package $packageModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->userModel = new User();
        $this->packageModel = new Package();
    }

    /**
     * Evaluate and execute automated package progression if conditions are met.
     */
    public function checkAndProcessProgression(int $userId): bool {
        $user = $this->userModel->findById($userId);
        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        // Get user's highest active package
        $stmtActive = $this->db->prepare("
            SELECT up.package_id, p.sort_order, p.price, p.name
            FROM user_packages up
            JOIN packages p ON up.package_id = p.id
            WHERE up.user_id = ? AND up.status = 'active'
            ORDER BY p.sort_order DESC
            LIMIT 1
        ");
        $stmtActive->execute([$userId]);
        $currentPkg = $stmtActive->fetch();

        if (!$currentPkg) {
            return false;
        }

        // Get next package tier
        $nextPkg = $this->packageModel->getNextPackage((int)$currentPkg['package_id']);
        if (!$nextPkg) {
            // Already at the highest tier
            return false;
        }

        $nextPrice = (float)$nextPkg['price'];
        $reserveBalance = (float)$user['reserve_balance'];

        // Check trigger condition: Sufficient Reserve Balance
        if ($reserveBalance >= $nextPrice) {
            return $this->executeUpgrade($user, $currentPkg, $nextPkg);
        }

        return false;
    }

    private function executeUpgrade(array $user, array $currentPkg, array $nextPkg): bool {
        $userId = (int)$user['id'];
        $nextPrice = (float)$nextPkg['price'];

        $this->db->beginTransaction();
        try {
            // Deduct upgrade cost from reserve balance
            $stmtDeduct = $this->db->prepare("
                UPDATE users
                SET reserve_balance = reserve_balance - ?
                WHERE id = ? AND reserve_balance >= ?
            ");
            $executed = $stmtDeduct->execute([$nextPrice, $userId, $nextPrice]);

            if (!$executed || $stmtDeduct->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // Update user's current package status to upgraded
            $stmtUpdatePrev = $this->db->prepare("
                UPDATE user_packages
                SET status = 'upgraded'
                WHERE user_id = ? AND package_id = ?
            ");
            $stmtUpdatePrev->execute([$userId, $currentPkg['package_id']]);

            // Fetch fresh user data for ledger balance tracking
            $updatedUser = $this->userModel->findById($userId);

            // Record Ledger Entry
            $stmtLedger = $this->db->prepare("
                INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                VALUES (?, 'upgrade_payment', ?, ?, ?, ?)
            ");
            $desc = sprintf("Auto-Progression Upgrade from %s (Tier #%d) to %s (Tier #%d)",
                $currentPkg['name'], $currentPkg['package_id'], $nextPkg['name'], $nextPkg['id']);
            $stmtLedger->execute([$userId, $nextPrice, $updatedUser['wallet_balance'], $updatedUser['reserve_balance'], $desc]);

            $this->db->commit();

            // Perform BFS matrix placement & commission distribution for the new package
            // Avoid infinite circular dependency by creating MatrixService here
            if (!class_exists('MatrixService')) {
                require_once __DIR__ . '/MatrixService.php';
            }
            $matrixService = new MatrixService();
            $matrixService->purchaseAndPlace($userId, (int)$nextPkg['id']);

            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Upgrade failed for User #{$userId}: " . $e->getMessage());
            return false;
        }
    }
}
