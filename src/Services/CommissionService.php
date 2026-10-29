<?php
// src/Services/CommissionService.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Settings.php';

class CommissionService {
    private PDO $db;
    private User $userModel;
    private Settings $settingsModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->userModel = new User();
        $this->settingsModel = new Settings();
    }

    /**
     * Process direct sponsor commission and matrix level commissions.
     */
    public function processCommissions(int $buyerUserId, int $packageId, float $packagePrice, ?int $newNodeId): array {
        $settings = $this->settingsModel->getAll();
        $reservePercent = (float)($settings['reserve_percentage'] ?? 25.00);

        $payouts = [];

        // 1. Direct Sponsor Commission
        $buyer = $this->userModel->findById($buyerUserId);
        if ($buyer && !empty($buyer['sponsor_id'])) {
            $sponsorId = (int)$buyer['sponsor_id'];
            $directPercent = (float)($settings['direct_sponsor_commission_percent'] ?? 20.00);

            $grossAmount = $packagePrice * ($directPercent / 100.0);
            $reserveAmount = $grossAmount * ($reservePercent / 100.0);
            $netAmount = $grossAmount - $reserveAmount;

            if ($grossAmount > 0) {
                // Update sponsor balances
                $this->userModel->updateBalances($sponsorId, $netAmount, $reserveAmount);

                // Fetch updated balances
                $updatedSponsor = $this->userModel->findById($sponsorId);

                // Record commission
                $stmt = $this->db->prepare("
                    INSERT INTO commissions (user_id, source_user_id, package_id, type, level, amount, reserved_amount, net_amount)
                    VALUES (?, ?, ?, 'direct_sponsor', NULL, ?, ?, ?)
                ");
                $stmt->execute([$sponsorId, $buyerUserId, $packageId, $grossAmount, $reserveAmount, $netAmount]);

                // Record Ledger
                $stmtLedger = $this->db->prepare("
                    INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                    VALUES (?, 'credit', ?, ?, ?, ?)
                ");
                $desc = sprintf("Direct Sponsor Bonus (Tier #%d) from %s (Net: %s, Reserve Hold: %s)",
                    $packageId, $buyer['username'], formatCurrency($netAmount), formatCurrency($reserveAmount));
                $stmtLedger->execute([$sponsorId, $netAmount, $updatedSponsor['wallet_balance'], $updatedSponsor['reserve_balance'], $desc]);

                $payouts[] = [
                    'user_id' => $sponsorId,
                    'type' => 'direct_sponsor',
                    'amount' => $grossAmount,
                    'net' => $netAmount,
                    'reserved' => $reserveAmount
                ];
            }
        }

        // 2. Matrix Level Commissions (up to 7 levels up)
        if ($newNodeId) {
            $currentParentId = $this->getParentMatrixNodeId($newNodeId);
            $level = 1;

            while ($currentParentId && $level <= 7) {
                $parentNode = $this->getMatrixNode($currentParentId);
                if (!$parentNode) {
                    break;
                }

                $ancestorUserId = (int)$parentNode['user_id'];
                $levelPercentKey = "matrix_level_{$level}_percent";
                $levelPercent = (float)($settings[$levelPercentKey] ?? 0.0);

                if ($levelPercent > 0) {
                    $grossAmount = $packagePrice * ($levelPercent / 100.0);
                    $reserveAmount = $grossAmount * ($reservePercent / 100.0);
                    $netAmount = $grossAmount - $reserveAmount;

                    // Update ancestor balances
                    $this->userModel->updateBalances($ancestorUserId, $netAmount, $reserveAmount);
                    $updatedAncestor = $this->userModel->findById($ancestorUserId);

                    // Record commission
                    $stmt = $this->db->prepare("
                        INSERT INTO commissions (user_id, source_user_id, package_id, type, level, amount, reserved_amount, net_amount)
                        VALUES (?, ?, ?, 'matrix_level', ?, ?, ?, ?)
                    ");
                    $stmt->execute([$ancestorUserId, $buyerUserId, $packageId, $level, $grossAmount, $reserveAmount, $netAmount]);

                    // Record Ledger
                    $stmtLedger = $this->db->prepare("
                        INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                        VALUES (?, 'credit', ?, ?, ?, ?)
                    ");
                    $desc = sprintf("Matrix Level %d Commission (Tier #%d) from %s (Net: %s, Reserve Hold: %s)",
                        $level, $packageId, $buyer['username'], formatCurrency($netAmount), formatCurrency($reserveAmount));
                    $stmtLedger->execute([$ancestorUserId, $netAmount, $updatedAncestor['wallet_balance'], $updatedAncestor['reserve_balance'], $desc]);

                    $payouts[] = [
                        'user_id' => $ancestorUserId,
                        'level' => $level,
                        'type' => 'matrix_level',
                        'amount' => $grossAmount,
                        'net' => $netAmount,
                        'reserved' => $reserveAmount
                    ];
                }

                // Move to next parent up
                $currentParentId = $parentNode['parent_id'];
                $level++;
            }
        }

        return $payouts;
    }

    private function getParentMatrixNodeId(int $nodeId): ?int {
        $stmt = $this->db->prepare("SELECT parent_id FROM matrix_nodes WHERE id = ?");
        $stmt->execute([$nodeId]);
        $parentId = $stmt->fetchColumn();
        return $parentId !== false ? ($parentId ? (int)$parentId : null) : null;
    }

    private function getMatrixNode(int $nodeId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM matrix_nodes WHERE id = ?");
        $stmt->execute([$nodeId]);
        $node = $stmt->fetch();
        return $node ?: null;
    }
}
