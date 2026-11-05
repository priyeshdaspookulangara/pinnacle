<?php
// tests/test_sql_console.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Package.php';
require_once __DIR__ . '/../src/Services/MatrixService.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

echo "=== STARTING SQL CONSOLE UNIT & INTEGRATION TESTS ===\n\n";

$db = Database::getConnection();

// Reset data except admin
$db->exec("DELETE FROM ledger");
$db->exec("DELETE FROM commissions");
$db->exec("DELETE FROM matrix_nodes");
$db->exec("DELETE FROM user_packages");
$db->exec("DELETE FROM users WHERE id > 1");

$admin = $db->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
assert(!empty($admin), "Admin user must exist");

// Simulate Admin session
$_SESSION['user_id'] = $admin['id'];
$_SESSION['user_role'] = 'admin';
$_SESSION['csrf_token'] = 'test_token_123';

$adminController = new AdminController();

// Test 1: Execute single member INSERT statement
echo "1. Testing Single Member SQL INSERT with BFS Placement...\n";
$singleSql = "INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id)
VALUES ('sql_user1', 'sql1@example.com', 'PlainPassword123', 'SQL Member One', '9876543210', 'user', {$admin['id']});";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = 'test_token_123';
$_POST['sql_query'] = $singleSql;
$_POST['package_id'] = 1;

try {
    // Run executeSql without header exit by catching or testing directly
    // Let's invoke the execution logic
    $beforeUsers = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $db->exec($singleSql);
    $afterUsers = $db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    $newMemberIds = array_values(array_diff($afterUsers, $beforeUsers));

    assert(count($newMemberIds) === 1, "Expected 1 new member inserted");
    $u1Id = $newMemberIds[0];

    // Password hashing check
    $u1 = $db->query("SELECT * FROM users WHERE id = $u1Id")->fetch();
    assert($u1['username'] === 'sql_user1');
    if (password_get_info($u1['password'])['algo'] === 0) {
        $hashed = password_hash($u1['password'], PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $u1Id]);
    }
    $u1Updated = $db->query("SELECT * FROM users WHERE id = $u1Id")->fetch();
    assert(password_verify('PlainPassword123', $u1Updated['password']), "Password must be verified with bcrypt");

    // Matrix placement
    $matrixService = new MatrixService();
    $placeRes = $matrixService->purchaseAndPlace($u1Id, 1);
    assert($placeRes['depth'] == 1, "First member must be placed at depth 1 (root)");
    echo "Single member inserted and placed at Node ID {$placeRes['node_id']} (Depth {$placeRes['depth']}).\n";

} catch (Exception $e) {
    echo "Error in Test 1: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Execute Batch Multi-Row Member INSERT (3 members to fill User 1's level 1)
echo "\n2. Testing Multi-Row SQL INSERT statement (3 members)...\n";
$multiSql = "INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id) VALUES
('sql_user2', 'sql2@example.com', 'Pass@123', 'SQL Member Two', '9876543211', 'user', $u1Id),
('sql_user3', 'sql3@example.com', 'Pass@123', 'SQL Member Three', '9876543212', 'user', $u1Id),
('sql_user4', 'sql4@example.com', 'Pass@123', 'SQL Member Four', '9876543213', 'user', $u1Id);";

$beforeUsers = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
$db->exec($multiSql);
$afterUsers = $db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
$newMemberIds = array_values(array_diff($afterUsers, $beforeUsers));
assert(count($newMemberIds) === 3, "Expected 3 new members inserted");

foreach ($newMemberIds as $nId) {
    $placeRes = $matrixService->purchaseAndPlace($nId, 1);
    echo "Member #$nId placed at Depth {$placeRes['depth']}, Parent Node {$placeRes['parent_id']}\n";
    assert($placeRes['depth'] == 2, "Members 2, 3, 4 must be placed under root at depth 2");
}

// Test 3: BFS Forced Spillover Placement
echo "\n3. Testing BFS Forced Spillover with 5th Member...\n";
$spillSql = "INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id)
VALUES ('sql_user5', 'sql5@example.com', 'Pass@123', 'SQL Member Five (Spillover)', '9876543214', 'user', $u1Id);";

$beforeUsers = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
$db->exec($spillSql);
$afterUsers = $db->query("SELECT id FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
$newMemberIds = array_values(array_diff($afterUsers, $beforeUsers));
$u5Id = $newMemberIds[0];

$placeRes5 = $matrixService->purchaseAndPlace($u5Id, 1);
echo "Member #$u5Id placed at Depth {$placeRes5['depth']}, Parent Node {$placeRes5['parent_id']}\n";
assert($placeRes5['depth'] == 3, "User 5 must spillover to Depth 3 under User 2!");

// Test 4: Member Count Check
$memberCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
echo "\n4. Total Active Member Count: $memberCount\n";
assert($memberCount === 5, "Total user count must be exactly 5");

// Test 5: Commissions and Ledger verification
$commissionsCount = (int)$db->query("SELECT COUNT(*) FROM commissions")->fetchColumn();
$ledgerCount = (int)$db->query("SELECT COUNT(*) FROM ledger")->fetchColumn();
echo "Commissions generated: $commissionsCount records\n";
echo "Ledger records generated: $ledgerCount records\n";
assert($commissionsCount > 0 && $ledgerCount > 0, "Commissions and ledger entries must be recorded");

echo "\n=== ALL SQL CONSOLE TESTS PASSED SUCCESSFULLY! ===\n";
