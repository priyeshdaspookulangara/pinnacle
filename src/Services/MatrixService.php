<?php
// src/Services/MatrixService.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/CommissionService.php';
require_once __DIR__ . '/ProgressionService.php';

class MatrixService {
    private PDO $db;
    private Package $packageModel;
    private CommissionService $commissionService;
    private ProgressionService $progressionService;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->packageModel = new Package();
        $this->commissionService = new CommissionService();
        $this->progressionService = new ProgressionService();
    }

    /**
     * Enrolls or purchases a package for a user and places them in the 3-matrix via BFS.
     */
    public function purchaseAndPlace(int $userId, int $packageId): array {
        $package = $this->packageModel->getById($packageId);
        if (!$package) {
            throw new Exception("Package not found.");
        }

        // Check if user is already placed in this package matrix
        $stmt = $this->db->prepare("SELECT id FROM matrix_nodes WHERE user_id = ? AND package_id = ?");
        $stmt->execute([$userId, $packageId]);
        if ($stmt->fetch()) {
            throw new Exception("User is already placed in Package Tier #" . $packageId);
        }

        $this->db->beginTransaction();
        try {
            // 1. Record User Package
            $stmtPkg = $this->db->prepare("
                INSERT INTO user_packages (user_id, package_id, status)
                VALUES (?, ?, 'active')
                ON DUPLICATE KEY UPDATE status = 'active'
            ");
            $stmtPkg->execute([$userId, $packageId]);

            // 2. BFS Placement Algorithm for Company-Wide 3-Matrix
            $targetParent = $this->findBfsPlacementSpot($packageId);

            $parentId = null;
            $depth = 1;
            $positionIndex = 1;

            if ($targetParent) {
                $parentId = (int)$targetParent['id'];
                $depth = (int)$targetParent['depth'] + 1;
                $positionIndex = (int)$targetParent['children_count'] + 1;
            }

            // Insert Matrix Node
            $stmtNode = $this->db->prepare("
                INSERT INTO matrix_nodes (package_id, user_id, parent_id, depth, position_index, children_count)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmtNode->execute([$packageId, $userId, $parentId, $depth, $positionIndex]);
            $newNodeId = (int)$this->db->lastInsertId();

            // Update parent children_count
            if ($parentId) {
                $stmtUpdateParent = $this->db->prepare("UPDATE matrix_nodes SET children_count = children_count + 1 WHERE id = ?");
                $stmtUpdateParent->execute([$parentId]);
            }

            // 3. Process Commissions (Direct & 6-Level Matrix)
            $payouts = $this->commissionService->processCommissions($userId, $packageId, (float)$package['price'], $newNodeId);

            $this->db->commit();

            // 4. Trigger Auto-Progression Check for buyer and upline ancestors
            $this->progressionService->checkAndProcessProgression($userId);
            if ($parentId) {
                $ancestorUserIds = $this->getUplineUserIds($newNodeId);
                foreach ($ancestorUserIds as $ancestorId) {
                    $this->progressionService->checkAndProcessProgression($ancestorId);
                }
            }

            return [
                'success' => true,
                'node_id' => $newNodeId,
                'package' => $package,
                'depth' => $depth,
                'parent_id' => $parentId,
                'payouts' => $payouts
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Breadth-First Search (BFS) to find the first open node (children_count < 3 and depth < 6)
     */
    private function findBfsPlacementSpot(int $packageId): ?array {
        $stmt = $this->db->prepare("
            SELECT id, depth, children_count
            FROM matrix_nodes
            WHERE package_id = ? AND children_count < 3 AND depth < 6
            ORDER BY depth ASC, id ASC
            LIMIT 1
        ");
        $stmt->execute([$packageId]);
        $parent = $stmt->fetch();
        return $parent ?: null;
    }

    /**
     * Get matrix tree representation for visualization
     */
    public function getMatrixTree(int $packageId, ?int $rootUserId = null): array {
        if ($rootUserId) {
            $stmt = $this->db->prepare("SELECT * FROM matrix_nodes WHERE package_id = ? AND user_id = ?");
            $stmt->execute([$packageId, $rootUserId]);
            $rootNode = $stmt->fetch();
        } else {
            $stmt = $this->db->prepare("SELECT * FROM matrix_nodes WHERE package_id = ? AND parent_id IS NULL ORDER BY id ASC LIMIT 1");
            $stmt->execute([$packageId]);
            $rootNode = $stmt->fetch();
        }

        if (!$rootNode) {
            return [];
        }

        return $this->buildSubtree($rootNode, 6);
    }

    private function buildSubtree(array $node, int $maxDepthRemaining): array {
        // Attach user info
        $stmtUser = $this->db->prepare("SELECT username, full_name FROM users WHERE id = ?");
        $stmtUser->execute([$node['user_id']]);
        $userInfo = $stmtUser->fetch();
        $node['username'] = $userInfo['username'] ?? 'Unknown';
        $node['full_name'] = $userInfo['full_name'] ?? 'Unknown';

        $children = [];
        if ($maxDepthRemaining > 1) {
            $stmtChild = $this->db->prepare("SELECT * FROM matrix_nodes WHERE parent_id = ? ORDER BY position_index ASC");
            $stmtChild->execute([$node['id']]);
            $childRows = $stmtChild->fetchAll();

            foreach ($childRows as $child) {
                $children[] = $this->buildSubtree($child, $maxDepthRemaining - 1);
            }
        }

        $node['children'] = $children;
        return $node;
    }

    private function getUplineUserIds(int $nodeId): array {
        $ancestors = [];
        $currentId = $nodeId;
        for ($i = 0; $i < 6; $i++) {
            $stmt = $this->db->prepare("SELECT parent_id FROM matrix_nodes WHERE id = ?");
            $stmt->execute([$currentId]);
            $parentId = $stmt->fetchColumn();
            if (!$parentId) {
                break;
            }
            $stmtUser = $this->db->prepare("SELECT user_id, parent_id FROM matrix_nodes WHERE id = ?");
            $stmtUser->execute([$parentId]);
            $parent = $stmtUser->fetch();
            if ($parent) {
                $ancestors[] = (int)$parent['user_id'];
                $currentId = $parentId;
            } else {
                break;
            }
        }
        return array_unique($ancestors);
    }
}
