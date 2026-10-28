<?php
// tests/test_backend.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Package.php';
require_once __DIR__ . '/../src/Services/MatrixService.php';

echo "=== STARTING PINNACLE HUNT BACKEND TEST SUITE ===\n\n";

$db = Database::getConnection();

// Clean existing data for testing except admin user
$db->exec("DELETE FROM ledger");
$db->exec("DELETE FROM commissions");
$db->exec("DELETE FROM matrix_nodes");
$db->exec("DELETE FROM user_packages");
$db->exec("DELETE FROM users WHERE id > 1");

$userModel = new User();
$packageModel = new Package();
$matrixService = new MatrixService();

// Step 1: Register Root User & Downline
echo "1. Registering Users...\n";
$userAId = $userModel->register('userA', 'usera@example.com', 'Pass@123', 'User A', '1111111111', 1); // Sponsored by Admin (ID 1)
$userBId = $userModel->register('userB', 'userb@example.com', 'Pass@123', 'User B', '2222222222', $userAId); // Sponsored by User A
$userCId = $userModel->register('userC', 'userc@example.com', 'Pass@123', 'User C', '3333333333', $userAId);
$userDId = $userModel->register('userD', 'userd@example.com', 'Pass@123', 'User D', '4444444444', $userAId);
$userEId = $userModel->register('userE', 'usere@example.com', 'Pass@123', 'User E', '5555555555', $userBId);

echo "Registered User A (#$userAId), User B (#$userBId), User C (#$userCId), User D (#$userDId), User E (#$userEId).\n\n";

// Step 2: Testing BFS Matrix Placement for Package Tier 1 (₹500)
echo "2. Testing Package Tier 1 (₹500) BFS 3-Matrix Placement...\n";

// User A purchases Tier 1 -> should become Matrix Root
$resA = $matrixService->purchaseAndPlace($userAId, 1);
echo "User A placed: Node ID {$resA['node_id']}, Depth {$resA['depth']}, Parent ID " . ($resA['parent_id'] ?? 'NULL') . "\n";
assert($resA['depth'] == 1 && $resA['parent_id'] === null, "User A must be root");

// User B purchases Tier 1 -> placed under User A (Pos 1)
$resB = $matrixService->purchaseAndPlace($userBId, 1);
echo "User B placed: Node ID {$resB['node_id']}, Depth {$resB['depth']}, Parent ID {$resB['parent_id']}\n";
assert($resB['depth'] == 2 && $resB['parent_id'] == $resA['node_id'], "User B must be under User A");

// User C purchases Tier 1 -> placed under User A (Pos 2)
$resC = $matrixService->purchaseAndPlace($userCId, 1);
echo "User C placed: Node ID {$resC['node_id']}, Depth {$resC['depth']}, Parent ID {$resC['parent_id']}\n";
assert($resC['depth'] == 2 && $resC['parent_id'] == $resA['node_id'], "User C must be under User A");

// User D purchases Tier 1 -> placed under User A (Pos 3) - Filling User A's 3-matrix!
$resD = $matrixService->purchaseAndPlace($userDId, 1);
echo "User D placed: Node ID {$resD['node_id']}, Depth {$resD['depth']}, Parent ID {$resD['parent_id']}\n";
assert($resD['depth'] == 2 && $resD['parent_id'] == $resA['node_id'], "User D must be under User A");

// User E purchases Tier 1 -> User A has 3 children, so BFS forced spillover places User E under User B (Depth 3)!
$resE = $matrixService->purchaseAndPlace($userEId, 1);
echo "User E placed (Spillover): Node ID {$resE['node_id']}, Depth {$resE['depth']}, Parent ID {$resE['parent_id']}\n";
assert($resE['depth'] == 3 && $resE['parent_id'] == $resB['node_id'], "User E must spillover under User B");

echo "BFS Placement Verified Successfully!\n\n";

// Step 3: Verify Commissions & Ledger
echo "3. Verifying Commission Ledger & Balances...\n";
$userAData = $userModel->findById($userAId);
echo "User A Wallet Balance: " . formatCurrency($userAData['wallet_balance']) . "\n";
echo "User A Reserve Balance: " . formatCurrency($userAData['reserve_balance']) . "\n";

$commissions = $db->query("SELECT * FROM commissions WHERE user_id = $userAId")->fetchAll();
echo "User A Total Commission Records: " . count($commissions) . "\n";
foreach ($commissions as $comm) {
    echo "  - Type: {$comm['type']}, Level: {$comm['level']}, Amount: ₹{$comm['amount']}, Net: ₹{$comm['net_amount']}, Reserved: ₹{$comm['reserved_amount']}\n";
}

// Step 4: Test Auto-Upgrade / Progression
echo "\n4. Testing Auto-Progression Engine...\n";
// Manually add reserve balance to User A to trigger auto-upgrade to Tier 2 (₹1,000)
$db->exec("UPDATE users SET reserve_balance = 1000.00 WHERE id = $userAId");
$progressionService = new ProgressionService();
$upgraded = $progressionService->checkAndProcessProgression($userAId);

echo "Auto-Progression Triggered for User A: " . ($upgraded ? 'YES' : 'NO') . "\n";
assert($upgraded === true, "Auto-progression should have succeeded");

$userAAfterUpgrade = $userModel->findById($userAId);
echo "User A New Reserve Balance after upgrade: " . formatCurrency($userAAfterUpgrade['reserve_balance']) . "\n";

$stmtTier2 = $db->prepare("SELECT * FROM user_packages WHERE user_id = ? AND package_id = 2");
$stmtTier2->execute([$userAId]);
$pkg2 = $stmtTier2->fetch();
assert(!empty($pkg2), "User A should now be enrolled in Package Tier 2!");
echo "User A Tier 2 Status: {$pkg2['status']}\n";

echo "\n=== ALL BACKEND TESTS PASSED SUCCESSFULLY! ===\n";
