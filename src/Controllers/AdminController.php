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
                'direct_sponsor_commission_percent' => (float)($_POST['direct_sponsor_commission_percent'] ?? 20.00),
                'matrix_level_1_percent' => (float)($_POST['matrix_level_1_percent'] ?? 12.50),
                'matrix_level_2_percent' => (float)($_POST['matrix_level_2_percent'] ?? 10.00),
                'matrix_level_3_percent' => (float)($_POST['matrix_level_3_percent'] ?? 7.50),
                'matrix_level_4_percent' => (float)($_POST['matrix_level_4_percent'] ?? 5.00),
                'matrix_level_5_percent' => (float)($_POST['matrix_level_5_percent'] ?? 2.50),
                'matrix_level_6_percent' => (float)($_POST['matrix_level_6_percent'] ?? 1.25),
                'matrix_level_7_percent' => (float)($_POST['matrix_level_7_percent'] ?? 1.25),
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

    public function matrixTree() {
        require_once __DIR__ . '/../Services/MatrixService.php';
        $matrixService = new MatrixService();

        $packages = $this->packageModel->getAll();
        $selectedPackageId = (int)($_GET['package_id'] ?? ($packages[0]['id'] ?? 1));

        // Find system root node or top node for selected package
        $stmt = $this->db->prepare("
            SELECT m.id
            FROM matrix_nodes m
            WHERE m.package_id = ?
            ORDER BY m.depth ASC, m.id ASC
            LIMIT 1
        ");
        $stmt->execute([$selectedPackageId]);
        $rootNodeId = $stmt->fetchColumn();

        $tree = [];
        if ($rootNodeId) {
            $tree = $matrixService->getMatrixTree($selectedPackageId);
        }

        require_once __DIR__ . '/../../views/admin/matrix_tree.php';
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

    public function sqlConsole() {
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $packages = $this->packageModel->getAllActive();
        require_once __DIR__ . '/../../views/admin/sql_console.php';
    }

    public function executeSql() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=admin_sql_console");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token.";
            header("Location: index.php?page=admin_sql_console");
            exit();
        }

        $rawSql = trim($_POST['sql_query'] ?? '');
        $packageId = (int)($_POST['package_id'] ?? 1);

        if (empty($rawSql)) {
            $_SESSION['error'] = "SQL statement cannot be empty.";
            header("Location: index.php?page=admin_sql_console");
            exit();
        }

        require_once __DIR__ . '/../Services/MatrixService.php';
        $matrixService = new MatrixService();

        try {
            // Snapshot existing user IDs prior to execution
            $beforeUsers = $this->db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);

            // Execute the SQL queries (supports multiple statements separated by semicolon)
            $this->db->exec($rawSql);

            // Identify newly inserted user IDs in insertion order
            $afterUsers = $this->db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
            $newMemberIds = array_values(array_diff($afterUsers, $beforeUsers));

            $placedCount = 0;
            $placementDetails = [];

            foreach ($newMemberIds as $userId) {
                $user = $this->userModel->findById((int)$userId);
                if (!$user) {
                    continue;
                }

                // If password was stored in plain text, ensure it is securely hashed with bcrypt
                if (!empty($user['password'])) {
                    $algo = password_get_info($user['password'])['algo'] ?? 0;
                    if ($algo === 0) {
                        $hashed = password_hash($user['password'], PASSWORD_BCRYPT);
                        $stmtHash = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmtHash->execute([$hashed, $userId]);
                    }
                }

                // Auto-place member into BFS Matrix with spillover for the specified/default package
                try {
                    $placeRes = $matrixService->purchaseAndPlace((int)$userId, $packageId);
                    $placedCount++;
                    $placementDetails[] = [
                        'user_id' => $userId,
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'node_id' => $placeRes['node_id'],
                        'depth' => $placeRes['depth'],
                        'parent_id' => $placeRes['parent_id'],
                        'payouts_count' => count($placeRes['payouts'] ?? [])
                    ];
                } catch (Exception $pe) {
                    $placementDetails[] = [
                        'user_id' => $userId,
                        'username' => $user['username'],
                        'full_name' => $user['full_name'],
                        'error' => $pe->getMessage()
                    ];
                }
            }

            $currentMemberCount = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

            $_SESSION['sql_results'] = [
                'inserted_count' => count($newMemberIds),
                'placed_count' => $placedCount,
                'current_member_count' => $currentMemberCount,
                'package_id' => $packageId,
                'placements' => $placementDetails,
                'executed_sql' => $rawSql
            ];

            $_SESSION['success'] = sprintf(
                "SQL executed successfully! %d member(s) inserted and auto-placed in BFS matrix spillover. Total active members is now %d.",
                count($newMemberIds),
                $currentMemberCount
            );

        } catch (Exception $e) {
            $_SESSION['error'] = "SQL Execution Failed: " . $e->getMessage();
            $_SESSION['last_sql_query'] = $rawSql;
        }

        header("Location: index.php?page=admin_sql_console");
        exit();
    }
}
