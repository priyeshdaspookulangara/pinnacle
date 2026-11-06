<?php
// src/Controllers/UserController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/../Models/UsdtPayment.php';
require_once __DIR__ . '/../Services/MatrixService.php';

class UserController {
    private User $userModel;
    private Package $packageModel;
    private UsdtPayment $usdtModel;
    private MatrixService $matrixService;
    private PDO $db;

    public function __construct() {
        requireAuth();
        $this->userModel = new User();
        $this->packageModel = new Package();
        $this->usdtModel = new UsdtPayment();
        $this->matrixService = new MatrixService();
        $this->db = Database::getConnection();
    }

    public function usdtActivationPage() {
        $user = currentUser();
        $packages = $this->packageModel->getAllActive();
        $usdtConfig = $this->usdtModel->getSettings();
        $userPayments = $this->usdtModel->getUserPayments($user['id']);

        require_once __DIR__ . '/../../views/user/usdt_activate.php';
    }

    public function updateProfileUsdt() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        $user = currentUser();
        $trc20 = trim($_POST['usdt_trc20_address'] ?? '');
        $bep20 = trim($_POST['usdt_bep20_address'] ?? '');
        $qrImagePath = null;

        if (isset($_FILES['usdt_qr_file']) && $_FILES['usdt_qr_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['usdt_qr_file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mime, $allowedTypes)) {
                $_SESSION['error'] = "Invalid image type. Please upload a PNG, JPG, or WEBP QR code.";
                header("Location: index.php?page=usdt_activate");
                exit();
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = "QR Code image file size must be under 5MB.";
                header("Location: index.php?page=usdt_activate");
                exit();
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user['id'] . '_qr_' . time() . '.' . strtolower($ext);
            $destination = __DIR__ . '/../../public/uploads/qr/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $qrImagePath = 'public/uploads/qr/' . $filename;
            }
        }

        $this->userModel->updateUsdtDetails($user['id'], $trc20, $bep20, $qrImagePath);
        $_SESSION['success'] = "Your personal USDT wallet addresses and QR code have been updated successfully!";
        header("Location: index.php?page=usdt_activate");
        exit();
    }

    public function submitUsdtPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        $user = currentUser();
        $packageId = (int)($_POST['package_id'] ?? 0);
        $network = trim($_POST['network'] ?? 'TRC20');
        $txid = trim($_POST['txid'] ?? '');

        if ($packageId <= 0 || empty($txid)) {
            $_SESSION['error'] = "Package selection and Transaction Hash (TXID) are required.";
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        $package = $this->packageModel->findById($packageId);
        if (!$package) {
            $_SESSION['error'] = "Selected package does not exist.";
            header("Location: index.php?page=usdt_activate");
            exit();
        }

        try {
            $this->usdtModel->createPayment($user['id'], $packageId, (float)$package['price'], $network, $txid);
            $_SESSION['success'] = "USDT payment proof submitted successfully! Admin will verify and activate your package shortly.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to submit USDT payment proof: " . $e->getMessage();
        }

        header("Location: index.php?page=usdt_activate");
        exit();
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
            $_SESSION['success'] = "Package successfully activated! You have been placed in the BFS 5-Matrix.";
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

        // Get BFS 5-matrix tree starting from current user or top
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

    public function referrals() {
        $user = currentUser();

        // Get direct referrals sponsored by current user
        $stmtRef = $this->db->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM user_packages up WHERE up.user_id = u.id AND up.status = 'active') as active_packages,
                   (SELECT SUM(c.amount) FROM commissions c WHERE c.user_id = ? AND c.source_user_id = u.id) as total_generated_comm
            FROM users u
            WHERE u.sponsor_id = ?
            ORDER BY u.id DESC
        ");
        $stmtRef->execute([$user['id'], $user['id']]);
        $referrals = $stmtRef->fetchAll();

        require_once __DIR__ . '/../../views/user/referrals.php';
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
