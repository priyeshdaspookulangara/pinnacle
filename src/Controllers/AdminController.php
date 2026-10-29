<?php
// src/Controllers/AdminController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/../Models/Settings.php';

class AdminController {
    private User $userModel;
    private Package $packageModel;
    private Settings $settingsModel;
    private PDO $db;

    public function __construct() {
        requireAuth('admin');
        $this->userModel = new User();
        $this->packageModel = new Package();
        $this->settingsModel = new Settings();
        $this->db = Database::getConnection();
    }

    public function dashboard() {
        // Stats
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $totalSystemWallet = (float)$this->db->query("SELECT SUM(wallet_balance) FROM users")->fetchColumn();
        $totalReserveHold = (float)$this->db->query("SELECT SUM(reserve_balance) FROM users")->fetchColumn();
        $totalCommissionsPaid = (float)$this->db->query("SELECT SUM(amount) FROM commissions")->fetchColumn();

        $recentUsers = $this->db->query("SELECT * FROM users ORDER BY id DESC LIMIT 5")->fetchAll();
        $pendingWithdrawals = $this->db->query("
            SELECT w.*, u.username, u.full_name
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            WHERE w.status = 'pending'
            ORDER BY w.id ASC
        ")->fetchAll();

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function settings() {
        $settings = $this->settingsModel->getAll();
        $packages = $this->packageModel->getAll();

        require_once __DIR__ . '/../../views/admin/settings.php';
    }

    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=admin_settings");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=admin_settings");
            exit();
        }

        try {
            $this->settingsModel->updateMany([
                'reserve_percentage' => (float)($_POST['reserve_percentage'] ?? 25.00),
                'auto_upgrade_milestone_level' => (int)($_POST['auto_upgrade_milestone_level'] ?? 3),
                'direct_sponsor_commission_percent' => (float)($_POST['direct_sponsor_commission_percent'] ?? 10.00),
                'matrix_level_1_percent' => (float)($_POST['matrix_level_1_percent'] ?? 5.00),
                'matrix_level_2_percent' => (float)($_POST['matrix_level_2_percent'] ?? 4.00),
                'matrix_level_3_percent' => (float)($_POST['matrix_level_3_percent'] ?? 3.00),
                'matrix_level_4_percent' => (float)($_POST['matrix_level_4_percent'] ?? 2.00),
                'matrix_level_5_percent' => (float)($_POST['matrix_level_5_percent'] ?? 1.00),
                'matrix_level_6_percent' => (float)($_POST['matrix_level_6_percent'] ?? 1.00),
            ]);

            // Update packages prices & commission rates if submitted
            if (isset($_POST['packages']) && is_array($_POST['packages'])) {
                foreach ($_POST['packages'] as $pkgId => $pkgData) {
                    $this->packageModel->update(
                        (int)$pkgId,
                        trim($pkgData['name']),
                        (float)$pkgData['price'],
                        (float)$pkgData['direct_commission_percent']
                    );
                }
            }

            $_SESSION['success'] = "Platform settings and rules updated successfully!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update settings: " . $e->getMessage();
        }

        header("Location: index.php?page=admin_settings");
        exit();
    }

    public function members() {
        $users = $this->userModel->getAllUsers();
        require_once __DIR__ . '/../../views/admin/members.php';
    }

    public function processWithdrawal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=admin_dashboard");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=admin_dashboard");
            exit();
        }

        $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($withdrawalId > 0 && in_array($action, ['approve', 'reject'])) {
            $status = $action === 'approve' ? 'approved' : 'rejected';

            // Get withdrawal
            $stmt = $this->db->prepare("SELECT * FROM withdrawals WHERE id = ?");
            $stmt->execute([$withdrawalId]);
            $w = $stmt->fetch();

            if ($w && $w['status'] === 'pending') {
                $this->db->beginTransaction();
                try {
                    $stmtUp = $this->db->prepare("UPDATE withdrawals SET status = ?, processed_at = NOW() WHERE id = ?");
                    $stmtUp->execute([$status, $withdrawalId]);

                    // If rejected, refund wallet balance
                    if ($status === 'rejected') {
                        $this->userModel->updateBalances((int)$w['user_id'], (float)$w['amount'], 0);
                        $updatedUser = $this->userModel->findById((int)$w['user_id']);

                        $stmtLedger = $this->db->prepare("
                            INSERT INTO ledger (user_id, type, amount, balance_after, reserve_after, description)
                            VALUES (?, 'credit', ?, ?, ?, ?)
                        ");
                        $desc = "Refund for Rejected Withdrawal #" . $withdrawalId;
                        $stmtLedger->execute([$w['user_id'], $w['amount'], $updatedUser['wallet_balance'], $updatedUser['reserve_balance'], $desc]);
                    }

                    $this->db->commit();
                    $_SESSION['success'] = "Withdrawal #" . $withdrawalId . " marked as " . $status . ".";
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
            }
        }

        header("Location: index.php?page=admin_dashboard");
        exit();
    }

    public function exportCsv() {
        $type = $_GET['type'] ?? 'ledger';
        $filename = "pinnacle_hunt_{$type}_export_" . date('Y-m-d_H-i') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if ($type === 'ledger') {
            fputcsv($output, ['ID', 'User ID', 'Username', 'Type', 'Amount (INR)', 'Wallet Balance After', 'Reserve Balance After', 'Description', 'Date']);
            $rows = $this->db->query("
                SELECT l.*, u.username
                FROM ledger l
                JOIN users u ON l.user_id = u.id
                ORDER BY l.id DESC
            ")->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['user_id'],
                    $row['username'],
                    $row['type'],
                    $row['amount'],
                    $row['balance_after'],
                    $row['reserve_after'],
                    $row['description'],
                    $row['created_at']
                ]);
            }
        } elseif ($type === 'commissions') {
            fputcsv($output, ['ID', 'Recipient User', 'Source User', 'Package Tier', 'Type', 'Matrix Level', 'Gross Amount', 'Reserved Hold', 'Net Paid', 'Date']);
            $rows = $this->db->query("
                SELECT c.*, u.username as recipient, s.username as source_user, p.name as package_name
                FROM commissions c
                JOIN users u ON c.user_id = u.id
                JOIN users s ON c.source_user_id = s.id
                JOIN packages p ON c.package_id = p.id
                ORDER BY c.id DESC
            ")->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['recipient'],
                    $row['source_user'],
                    $row['package_name'],
                    $row['type'],
                    $row['level'] ?: 'N/A',
                    $row['amount'],
                    $row['reserved_amount'],
                    $row['net_amount'],
                    $row['created_at']
                ]);
            }
        } elseif ($type === 'withdrawals') {
            fputcsv($output, ['ID', 'User ID', 'Username', 'Amount (INR)', 'Payout Details', 'Status', 'Processed Date', 'Requested Date']);
            $rows = $this->db->query("
                SELECT w.*, u.username
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                ORDER BY w.id DESC
            ")->fetchAll();

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['user_id'],
                    $row['username'],
                    $row['amount'],
                    $row['payout_details'],
                    $row['status'],
                    $row['processed_at'] ?: 'N/A',
                    $row['created_at']
                ]);
            }
        }

        fclose($output);
        exit();
    }
}
