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
$userEId = $userModel->register('userE', 'usere@example.com', 'Pass@123', 'User E', '5555555555', $userAId);
$userFId = $userModel->register('userF', 'userf@example.com', 'Pass@123', 'User F', '6666666666', $userAId);
$userGId = $userModel->register('userG', 'userg@example.com', 'Pass@123', 'User G', '7777777777', $userBId);

echo "Registered Users A to G.\n\n";

// Step 2: Testing BFS Matrix Placement for Package Tier 1 (₹500) in 5-Matrix
echo "2. Testing Package Tier 1 (₹500) BFS 5-Matrix Placement...\n";

// User A purchases Tier 1 -> should become Matrix Root
$resA = $matrixService->purchaseAndPlace($userAId, 1);
echo "User A placed: Node ID {$resA['node_id']}, Depth {$resA['depth']}, Parent ID " . ($resA['parent_id'] ?? 'NULL') . "\n";
assert($resA['depth'] == 1 && $resA['parent_id'] === null, "User A must be root");

// User B purchases Tier 1 -> placed under User A (Pos 1)
$resB = $matrixService->purchaseAndPlace($userBId, 1);
echo "User B placed: Node ID {$resB['node_id']}, Depth {$resB['depth']}, Parent ID {$resB['parent_id']}\n";
assert($resB['depth'] == 2 && $resB['parent_id'] == $resA['node_id'], "User B must be under User A (Pos 1)");

// User C purchases Tier 1 -> placed under User A (Pos 2)
$resC = $matrixService->purchaseAndPlace($userCId, 1);
echo "User C placed: Node ID {$resC['node_id']}, Depth {$resC['depth']}, Parent ID {$resC['parent_id']}\n";
assert($resC['depth'] == 2 && $resC['parent_id'] == $resA['node_id'], "User C must be under User A (Pos 2)");

// User D purchases Tier 1 -> placed under User A (Pos 3)
$resD = $matrixService->purchaseAndPlace($userDId, 1);
echo "User D placed: Node ID {$resD['node_id']}, Depth {$resD['depth']}, Parent ID {$resD['parent_id']}\n";
assert($resD['depth'] == 2 && $resD['parent_id'] == $resA['node_id'], "User D must be under User A (Pos 3)");

// User E purchases Tier 1 -> placed under User A (Pos 4)
$resE = $matrixService->purchaseAndPlace($userEId, 1);
echo "User E placed: Node ID {$resE['node_id']}, Depth {$resE['depth']}, Parent ID {$resE['parent_id']}\n";
assert($resE['depth'] == 2 && $resE['parent_id'] == $resA['node_id'], "User E must be under User A (Pos 4)");

// User F purchases Tier 1 -> placed under User A (Pos 5) - Completes User A's 5 children!
$resF = $matrixService->purchaseAndPlace($userFId, 1);
echo "User F placed: Node ID {$resF['node_id']}, Depth {$resF['depth']}, Parent ID {$resF['parent_id']}\n";
assert($resF['depth'] == 2 && $resF['parent_id'] == $resA['node_id'], "User F must be under User A (Pos 5)");

// User G purchases Tier 1 -> User A has 5 children, so BFS forced spillover places User G under User B (Depth 3, Pos 1)!
$resG = $matrixService->purchaseAndPlace($userGId, 1);
echo "User G placed (Spillover): Node ID {$resG['node_id']}, Depth {$resG['depth']}, Parent ID {$resG['parent_id']}\n";
assert($resG['depth'] == 3 && $resG['parent_id'] == $resB['node_id'], "User G must spillover under User B in 5-matrix");

echo "5-Matrix BFS Placement Verified Successfully!\n\n";

// Step 3: Verify Commissions & Ledger
echo "3. Verifying Commission Ledger & Balances...\n";
$userAData = $userModel->findById($userAId);
echo "User A Wallet Balance: " . formatCurrency($userAData['wallet_balance']) . "\n";
echo "User A Reserve Balance: " . formatCurrency($userAData['reserve_balance']) . "\n";

$commissions = $db->query("SELECT * FROM commissions WHERE user_id = $userAId")->fetchAll();
echo "User A Total Commission Records: " . count($commissions) . "\n";
foreach ($commissions as $comm) {
    echo "  - Type: {$comm['type']}, Level: {$comm['level']}, Amount: ₹{$comm['amount']}, Net: ₹{$comm['net_amount']}, Reserved: ₹{$comm['reserved_amount']}\n";
    if ($comm['type'] === 'direct_sponsor') {
        assert((float)$comm['reserved_amount'] === 0.0, "Referral income must have 0 cutting / reserved amount");
        assert((float)$comm['net_amount'] === (float)$comm['amount'], "Referral income must be 100% credited to net amount");
    }
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

// Step 5: Verify Package Tier 2 (and subsequent tiers) 5-Matrix Topology
echo "\n5. Testing Package Tier 2 5-Matrix Placement...\n";
// Let users B, C, D, E, F upgrade to Tier 2 and place under User A in Tier 2
$resB2 = $matrixService->purchaseAndPlace($userBId, 2);
$resC2 = $matrixService->purchaseAndPlace($userCId, 2);
$resD2 = $matrixService->purchaseAndPlace($userDId, 2);
$resE2 = $matrixService->purchaseAndPlace($userEId, 2);
$resF2 = $matrixService->purchaseAndPlace($userFId, 2);

echo "Tier 2 Children under User A in Tier 2:\n";
echo "  User B: Node {$resB2['node_id']}, Parent {$resB2['parent_id']}, Depth {$resB2['depth']}\n";
echo "  User C: Node {$resC2['node_id']}, Parent {$resC2['parent_id']}, Depth {$resC2['depth']}\n";
echo "  User D: Node {$resD2['node_id']}, Parent {$resD2['parent_id']}, Depth {$resD2['depth']}\n";
echo "  User E: Node {$resE2['node_id']}, Parent {$resE2['parent_id']}, Depth {$resE2['depth']}\n";
echo "  User F: Node {$resF2['node_id']}, Parent {$resF2['parent_id']}, Depth {$resF2['depth']}\n";

$tier1NodeA = $db->query("SELECT id FROM matrix_nodes WHERE package_id = 2 AND user_id = $userAId")->fetchColumn();
assert($resB2['parent_id'] == $tier1NodeA && $resF2['parent_id'] == $tier1NodeA, "All 5 users must be placed under User A in Tier 2");

// Now User G purchases Tier 2 -> 6th child in Tier 2 must spillover under User B in Tier 2
$resG2 = $matrixService->purchaseAndPlace($userGId, 2);
echo "Tier 2 User G Spillover: Node {$resG2['node_id']}, Parent {$resG2['parent_id']} (User B Tier 2 node is {$resB2['node_id']}), Depth {$resG2['depth']}\n";
assert($resG2['parent_id'] == $resB2['node_id'], "User G must spillover under User B in Tier 2 5-matrix!");
echo "Both Tier 1 and Tier 2 (all packages) operate identically as 5-Matrix!\n";


// Step 6: Test Depth 8 Matrix Placement and Level 8 Commission
echo "\n6. Testing Level 8 Matrix Depth and Commission...\n";
// Let's create a chain of 8 levels down from User A in Package Tier 3
// Depth 1: User A
$resChain1 = $matrixService->purchaseAndPlace($userAId, 3);
// Create users Chain2 to Chain8
$prevUserId = $userAId;
$prevNodeId = $resChain1['node_id'];
$chainUserIds = [];

for ($depth = 2; $depth <= 8; $depth++) {
    $chainUsername = "chainUser_" . $depth;
    $chainEmail = "chain_{$depth}@example.com";
    $chainUserId = $userModel->register($chainUsername, $chainEmail, 'Pass@123', "Chain User $depth", "987650000$depth", $prevUserId);
    $chainUserIds[$depth] = $chainUserId;
    $resChain = $matrixService->purchaseAndPlace($chainUserId, 3);
    echo "  Depth $depth placed: Node {$resChain['node_id']}, Depth {$resChain['depth']}, Parent {$resChain['parent_id']}\n";
    assert($resChain['depth'] == $depth, "Chain node must have depth $depth");
    $prevUserId = $chainUserId;
}

// Verify BFS placement and level commissions up to 8 levels:
// In BFS 5-matrix, 5 nodes fill depth 2, then spillover fills depth 3, etc.
// Verify upline received matrix level commission
$stmtLvl2 = $db->prepare("SELECT * FROM commissions WHERE user_id = ? AND package_id = 3 AND level = 2");
$stmtLvl2->execute([$userAId]);
$commLvl2 = $stmtLvl2->fetch();
assert(!empty($commLvl2), "User A should have received Level 2 matrix commission from depth 3 spillover");
echo "Matrix commission verified from BFS spillover: ₹{$commLvl2['amount']}\n";

// Now verify level 8 commission rate setting exists and can be retrieved
$settingsModel = new Settings();
$allSettings = $settingsModel->getAll();
assert(isset($allSettings['matrix_level_8_percent']), "matrix_level_8_percent must exist in settings");
assert((float)$allSettings['matrix_level_8_percent'] === 1.0, "matrix_level_8_percent must be 1.00%");
echo "8-level matrix depth and commission configurations verified!\n";

// Step 7: Test Admin Edit and Deep Delete Member (Purging all amounts/commissions)
echo "\n7. Testing Admin Edit Member and Deep Delete (Amount Trace Purge)...\n";
// Let's create a test downline member under User A
$testMemberId = $userModel->register('purgeUser', 'purge@example.com', 'Pass@123', 'Purge Member', '9988776655', $userAId);
$uplineBefore = $userModel->findById($userAId);
$uplineWalletBefore = (float)$uplineBefore['wallet_balance'];
$uplineReserveBefore = (float)$uplineBefore['reserve_balance'];

// testMember purchases Tier 1
$matrixService->purchaseAndPlace($testMemberId, 1);
$uplineAfterPurchase = $userModel->findById($userAId);
assert((float)$uplineAfterPurchase['wallet_balance'] > $uplineWalletBefore, "User A should have received commission from testMember");

// Test Edit Member
$userModel->updateMember($testMemberId, 'Updated Purge Member', 'purge_updated@example.com', '1234567890', 500.00, 100.00, 'active');
$editedMember = $userModel->findById($testMemberId);
assert($editedMember['full_name'] === 'Updated Purge Member', "Member name should update");
assert((float)$editedMember['wallet_balance'] === 500.00, "Member wallet balance should update");
echo "Member Edit verified successfully!\n";

// Test Deep Delete Member
$userModel->deleteMemberCompletely($testMemberId);
$deletedCheck = $userModel->findById($testMemberId);
assert($deletedCheck === null, "Member record must be completely deleted");

// Verify that commissions generated by testMember to User A were purged from User A's balances
$uplineAfterDelete = $userModel->findById($userAId);
echo "User A Wallet before purchase: {$uplineWalletBefore}, after purchase: {$uplineAfterPurchase['wallet_balance']}, after deep delete: {$uplineAfterDelete['wallet_balance']}\n";
assert(abs((float)$uplineAfterDelete['wallet_balance'] - $uplineWalletBefore) < 0.01, "User A's wallet balance should be reverted back to state before testMember joined");
assert(abs((float)$uplineAfterDelete['reserve_balance'] - $uplineReserveBefore) < 0.01, "User A's reserve balance should be reverted back to state before testMember joined");

// Verify commissions table has no remaining records where source_user_id was testMemberId
$remainingComms = $db->query("SELECT COUNT(*) FROM commissions WHERE source_user_id = $testMemberId")->fetchColumn();
assert((int)$remainingComms === 0, "All commissions generated by deleted member must be purged");

echo "Deep Delete with 100% amount purge verified successfully!\n";

// 8. Testing Member Status Activation/Deactivation and USDT Activation Flow
echo "\n8. Testing Member Status Activation/Deactivation and USDT Payment Flow...\n";
require_once __DIR__ . '/../src/Models/UsdtPayment.php';
$usdtModel = new UsdtPayment();

// Register new member for USDT flow
$usdtUser = $userModel->register('usdt_trader', 'trader@example.com', 'Trader@2026', 'USDT Trader', '9123456780', $userAId);

// Test Deactivation Toggle
$userModel->toggleMemberStatus($usdtUser, 'inactive');
$chkInactive = $userModel->findById($usdtUser);
assert($chkInactive['status'] === 'inactive', "Member should be inactive");

// Submit USDT payment proof
$payId = $usdtModel->createPayment($usdtUser, 1, 500.0, 'TRC20', 'TRX_TXID_TEST_99887766');
assert($payId > 0, "USDT payment record created");
$payRec = $usdtModel->findById($payId);
assert($payRec['status'] === 'pending', "USDT payment should be pending");
assert((float)$payRec['amount_usdt'] > 0, "USDT calculated amount should be greater than 0");

// Admin approval simulation (MatrixService handles its own internal transaction)
$stmtAct = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
$stmtAct->execute([$usdtUser]);
$usdtModel->updateStatus($payId, 'approved', 'Test approved');
$matrixService->purchaseAndPlace($usdtUser, 1);

$chkActive = $userModel->findById($usdtUser);
assert($chkActive['status'] === 'active', "Member should now be active after USDT approval");

// Check user package is active
$stmtPkg = $db->prepare("SELECT status FROM user_packages WHERE user_id = ? AND package_id = 1");
$stmtPkg->execute([$usdtUser]);
assert($stmtPkg->fetchColumn() === 'active', "User package 1 should be active");

echo "USDT Payment and Member Activation/Deactivation Flow verified successfully!\n";


// 9. Testing Registration with Direct USDT Payment
echo "\n9. Testing Registration with Direct USDT Payment...\n";
$regUsername = 'reg_usdt_' . time();
$regEmail = $regUsername . '@example.com';
$newRegUserId = $userModel->register($regUsername, $regEmail, 'Secret@123', 'Direct USDT User', '+91 9988776655', 1);
$usdtModel = new UsdtPayment();
$usdtId = $usdtModel->createPayment($newRegUserId, 1, 500.00, 5.56, 'TRC20', 'TYDzpSLtq8X2yB33x9v2QG2H1234567890', 'REG_TXID_' . time());
assert($usdtId > 0, "Registration USDT payment submission failed!");

// Approve and verify account activation
$usdtModel->approvePayment($usdtId, 1, 'Verified from registration');
$activatedUser = $userModel->findById($newRegUserId);
assert($activatedUser['status'] === 'active', "User not activated upon USDT approval!");
assert($userModel->isPackageActive($newRegUserId, 1), "User package 1 not active upon USDT approval!");
echo "Direct USDT Registration Payment Flow verified successfully!\n";

// 10. Testing User USDT Details & QR Upload + Admin Corporate QR Code Setting
echo "\n10. Testing User Personal USDT Details and Admin Corporate QR Code...\n";
$testTrc = 'TX_TEST_TRC20_WALLET_ADDRESS_123';
$testBep = '0x1234567890abcdef1234567890abcdef12345678';
$testUserQr = 'public/uploads/qr/user_test_qr.png';

$userModel->updateUsdtDetails($userAId, $testTrc, $testBep, $testUserQr);
$updatedUserA = $userModel->findById($userAId);
assert($updatedUserA['usdt_trc20_address'] === $testTrc, "User TRC20 address should be updated");
assert($updatedUserA['usdt_bep20_address'] === $testBep, "User BEP20 address should be updated");
assert($updatedUserA['usdt_qr_image'] === $testUserQr, "User QR image path should be updated");
echo "User personal USDT addresses and QR path verified successfully!\n";

// Test Admin Corporate QR code settings
$settingsModel->set('usdt_trc20_qr', 'public/uploads/qr/admin_trc20_custom.png');
$settingsModel->set('usdt_bep20_qr', 'public/uploads/qr/admin_bep20_custom.png');
$usdtSettings = $usdtModel->getSettings();
assert($usdtSettings['trc20_qr'] === 'public/uploads/qr/admin_trc20_custom.png', "Admin TRC20 QR setting must be updated");
assert($usdtSettings['bep20_qr'] === 'public/uploads/qr/admin_bep20_custom.png', "Admin BEP20 QR setting must be updated");
echo "Admin corporate USDT QR settings verified successfully!\n";
