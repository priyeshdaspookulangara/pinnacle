<?php
// src/Controllers/UserController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/../Services/MatrixService.php';

class UserController {
    private User $userModel;
    private Package $packageModel;
    private MatrixService $matrixService;
    private PDO $db;

    public function __construct() {
        requireAuth();
        $this->userModel = new User();
        $this->packageModel = new Package();
        $this->matrixService = new MatrixService();
        $this->db = Database::getConnection();
    }

    public function dashboard() {
        $user = currentUser();
        $packages = $this->packageModel->getAllActive();

        // Get user active/purchased packages
        $stmtUserPkg = $this->db->prepare("
            SELECT up.*, p.name, p.price, p.sort_order
            FROM user_packages up
            JOIN packages p ON up.package_id = p.id
            WHERE up.user_id = ?
            ORDER BY p.sort_order ASC
        ");
        $stmtUserPkg->execute([$user['id']]);
        $userPackages = $stmtUserPkg->fetchAll();
        $userPackageIds = array_column($userPackages, 'package_id');

        // Total earnings
        $stmtEarn = $this->db->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ?");
        $stmtEarn->execute([$user['id']]);
        $totalEarnings = (float)($stmtEarn->fetchColumn() ?: 0);

        // Total referrals
        $stmtRef = $this->db->prepare("SELECT COUNT(*) FROM users WHERE sponsor_id = ?");
        $stmtRef->execute([$user['id']]);
        $totalReferrals = (int)$stmtRef->fetchColumn();

        // Recent commissions
        $stmtComm = $this->db->prepare("
            SELECT c.*, p.name as package_name, s.username as source_username
            FROM commissions c
            JOIN packages p ON c.package_id = p.id
            JOIN users s ON c.source_user_id = s.id
            WHERE c.user_id = ?
            ORDER BY c.id DESC LIMIT 5
        ");
        $stmtComm->execute([$user['id']]);
        $recentCommissions = $stmtComm->fetchAll();

        require_once __DIR__ . '/../../views/user/dashboard.php';
    }

    public function purchasePackage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=dashboard");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=dashboard");
            exit();
        }

        $packageId = (int)($_POST['package_id'] ?? 0);
        $user = currentUser();

        try {
            $result = $this->matrixService->purchaseAndPlace($user['id'], $packageId);
            $_SESSION['success'] = "Package successfully activated! You have been placed in the BFS 3-Matrix.";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?page=dashboard");
        exit();
    }

    public function genealogy() {
        $user = currentUser();
        $packages = $this->packageModel->getAllActive();

        $selectedPackageId = (int)($_GET['package_id'] ?? ($packages[0]['id'] ?? 1));

        // Get BFS 3-matrix tree starting from current user or top
        $tree = $this->matrixService->getMatrixTree($selectedPackageId, $user['id']);

        // Get direct downline referrals
        $stmtDownline = $this->db->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM users r WHERE r.sponsor_id = u.id) as ref_count
            FROM users u
            WHERE u.sponsor_id = ?
            ORDER BY u.id DESC
        ");
        $stmtDownline->execute([$user['id']]);
        $directReferrals = $stmtDownline->fetchAll();

        require_once __DIR__ . '/../../views/user/genealogy.php';
    }

    public function ledger() {
        $user = currentUser();

        // Get Ledger History
        $stmtLedger = $this->db->prepare("
            SELECT * FROM ledger
            WHERE user_id = ?
            ORDER BY id DESC
        ");
        $stmtLedger->execute([$user['id']]);
        $ledgerEntries = $stmtLedger->fetchAll();

        // Get Withdrawal Requests
        $stmtWithdraw = $this->db->prepare("
            SELECT * FROM withdrawals
            WHERE user_id = ?
            ORDER BY id DESC
        ");
        $stmtWithdraw->execute([$user['id']]);
        $withdrawals = $stmtWithdraw->fetchAll();

        require_once __DIR__ . '/../../views/user/ledger.php';
    }

    public function requestWithdrawal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=ledger");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=ledger");
            exit();
        }

        $user = currentUser();
        $amount = (float)($_POST['amount'] ?? 0);
        $payoutDetails = trim($_POST['payout_details'] ?? '');

        if ($amount <= 0) {
            $_SESSION['error'] = "Please enter a valid withdrawal amount.";
            header("Location: index.php?page=ledger");
            exit();
        }

        if ($amount > (float)$user['wallet_balance']) {
            $_SESSION['error'] = "Insufficient wallet balance.";
            header("Location: index.php?page=ledger");
            exit();
        }

        $this->db->beginTransaction();
        try {
            // Deduct balance
            $stmtDeduct = $this->db->prepare("
                UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ? AND wallet_balance >= ?
            ");
            $stmtDeduct->execute([$amount, $user['id'], $amount]);

            // Record withdrawal
            $stmtReq = $this->db->prepare("
                INSERT INTO withdrawals (user_id, amount, payout_details, status) VALUES (?, ?, ?, 'pending')
            ");
            $stmtReq->execute([$user['id'], $amount, $payoutDetails]);

            // Record ledger entry
            $updatedUser = $this->userModel->findById($user['id']);
            $stmtLedger = $this->db->prepare("
                INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                VALUES (?, 'debit', ?, ?, ?, ?)
            ");
            $desc = "Withdrawal Request Submitted (" . formatCurrency($amount) . " to " . htmlspecialchars($payoutDetails) . ")";
            $stmtLedger->execute([$user['id'], $amount, $updatedUser['wallet_balance'], $updatedUser['reserve_balance'], $desc]);

            $this->db->commit();
            $_SESSION['success'] = "Withdrawal request submitted successfully!";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Failed to submit withdrawal: " . $e->getMessage();
        }

        header("Location: index.php?page=ledger");
        exit();
    }
}
