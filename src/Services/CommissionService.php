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
     * Process multi-tier 6-way proportional package allocation and matrix level commissions.
     * Rayon Global Impact Allocation Ratios:
     * - Company Share: 11/35 (31.42857%)
     * - Loop Entry:    11/35 (31.42857%)
     * - Referral:      5/35  (14.28571%)
     * - Gift Pool:     8/105 (7.61905%)
     * - Legal Pool:    8/105 (7.61905%)
     * - Marketing:     8/105 (7.61905%)
     * Total = 100.00%
     */
    public function processCommissions(int $buyerUserId, int $packageId, float $packagePrice, ?int $newNodeId): array {
        $settings = $this->settingsModel->getAll();
        $rate = (float)($settings['usdt_inr_rate'] ?? 93.76);
        if ($rate <= 0) {
            $rate = 93.76;
        }

        $packageUsdt = $packagePrice / $rate;
        $allocation = calculatePackageAllocation($packageUsdt, $rate);

        $buyer = $this->userModel->findById($buyerUserId);
        $buyerWallet = $buyer ? (float)$buyer['wallet_balance'] : 0.00;
        $buyerReserve = $buyer ? (float)$buyer['reserve_balance'] : 0.00;

        // 1. Credit the 5 System Pools in database (and audit to ledger)
        $this->updateSystemPool('company_share', $allocation['company_usdt'], $allocation['company_inr']);
        $this->updateSystemPool('loop_entry', $allocation['loop_usdt'], $allocation['loop_inr']);
        $this->updateSystemPool('referral_income', $allocation['referral_usdt'], $allocation['referral_inr']);
        $this->updateSystemPool('gift_pool', $allocation['gift_usdt'], $allocation['gift_inr']);
        $this->updateSystemPool('legal_pool', $allocation['legal_usdt'], $allocation['legal_inr']);
        $this->updateSystemPool('marketing_pool', $allocation['marketing_usdt'], $allocation['marketing_inr']);

        // Record pool fee deduction audit entries in ledger for transparency
        $poolAudits = [
            'Company Share (11/35)' => $allocation['company_inr'],
            'Loop Entry Matrix Pool (11/35)' => $allocation['loop_inr'],
            'Gift Pool (8/105)' => $allocation['gift_inr'],
            'Legal Pool (8/105)' => $allocation['legal_inr'],
            'Marketing Pool (8/105)' => $allocation['marketing_inr'],
        ];

        foreach ($poolAudits as $poolDesc => $poolInr) {
            if ($poolInr > 0 && $buyer) {
                $stmtLedger = $this->db->prepare("
                    INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                    VALUES (?, 'debit', ?, ?, ?, ?)
                ");
                $desc = sprintf("Package Tier #%d Allocation: %s (%s / ~%.4f USDT)",
                    $packageId, $poolDesc, formatCurrency($poolInr), $poolInr / $rate);
                $stmtLedger->execute([$buyerUserId, $poolInr, $buyerWallet, $buyerReserve, $desc]);
            }
        }

        $payouts = [];

        // 2. Direct Sponsor Referral Commission: Exactly 5/35 (14.28571%) of Package Inflow
        if ($buyer && !empty($buyer['sponsor_id'])) {
            $sponsorId = (int)$buyer['sponsor_id'];
            $referralInr = $allocation['referral_inr'];
            $referralUsdt = $allocation['referral_usdt'];

            if ($referralInr > 0) {
                // 100% of referral commission credited directly to wallet (no deduction)
                $this->userModel->updateBalances($sponsorId, $referralInr, 0.00);
                $updatedSponsor = $this->userModel->findById($sponsorId);

                // Record commission
                $stmt = $this->db->prepare("
                    INSERT INTO commissions (user_id, source_user_id, package_id, type, level, amount, reserved_amount, net_amount)
                    VALUES (?, ?, ?, 'direct_sponsor', NULL, ?, 0.00, ?)
                ");
                $stmt->execute([$sponsorId, $buyerUserId, $packageId, $referralInr, $referralInr]);

                // Record Ledger
                $stmtLedger = $this->db->prepare("
                    INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                    VALUES (?, 'credit', ?, ?, ?, ?)
                ");
                $desc = sprintf("Direct Referral Commission (5/35) Tier #%d from %s (%s / ~%.2f USDT - 100%% credited)",
                    $packageId, $buyer['username'], formatCurrency($referralInr), $referralUsdt);
                $stmtLedger->execute([$sponsorId, $referralInr, $updatedSponsor['wallet_balance'], $updatedSponsor['reserve_balance'], $desc]);

                $payouts[] = [
                    'user_id' => $sponsorId,
                    'type' => 'direct_sponsor',
                    'amount' => $referralInr,
                    'amount_usdt' => $referralUsdt,
                    'net' => $referralInr,
                    'reserved' => 0.00
                ];
            }
        }

        // 3. Matrix Level Commissions from Loop Entry Pool (up to 8 levels up)
        if ($newNodeId) {
            $currentParentId = $this->getParentMatrixNodeId($newNodeId);
            $level = 1;
            $reservePercent = (float)($settings['reserve_percentage'] ?? 25.00);
            $loopInrBase = $allocation['loop_inr']; // Powered by loop entry pool (11/35)

            // Normalized level percentages over the 8 levels for the matrix loop
            $matrixLevelWeights = [
                1 => 0.30, // 30% of loop pool
                2 => 0.25, // 25% of loop pool
                3 => 0.18, // 18% of loop pool
                4 => 0.12, // 12% of loop pool
                5 => 0.06, // 6% of loop pool
                6 => 0.04, // 4% of loop pool
                7 => 0.03, // 3% of loop pool
                8 => 0.02, // 2% of loop pool
            ];

            while ($currentParentId && $level <= 8) {
                $parentNode = $this->getMatrixNode($currentParentId);
                if (!$parentNode) {
                    break;
                }

                $ancestorUserId = (int)$parentNode['user_id'];
                $weight = $matrixLevelWeights[$level] ?? 0.0;

                if ($weight > 0 && $loopInrBase > 0) {
                    $grossAmount = $loopInrBase * $weight;
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
                    $desc = sprintf("Matrix Loop Level %d Commission (Tier #%d) from %s (Net: %s, Reserve Hold: %s)",
                        $level, $packageId, $buyer ? $buyer['username'] : "User#$buyerUserId", formatCurrency($netAmount), formatCurrency($reserveAmount));
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

                $currentParentId = $parentNode['parent_id'];
                $level++;
            }
        }

        return $payouts;
    }

    /**
     * Helper to increment system pool ledger balances
     */
    private function updateSystemPool(string $poolName, float $amountUsdt, float $amountInr): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_pools (pool_name, balance_usdt, balance_inr, total_inflow_usdt, total_inflow_inr)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    balance_usdt = balance_usdt + VALUES(balance_usdt),
                    balance_inr = balance_inr + VALUES(balance_inr),
                    total_inflow_usdt = total_inflow_usdt + VALUES(total_inflow_usdt),
                    total_inflow_inr = total_inflow_inr + VALUES(total_inflow_inr)
            ");
            $stmt->execute([$poolName, $amountUsdt, $amountInr, $amountUsdt, $amountInr]);
        } catch (Exception $e) {
            // Pool table logging should not interrupt main matrix flow if running without migration
        }
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
