<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="git-fork" class="w-8 h-8 text-brand-400"></i> Global Company Matrix Tree
        </h1>
        <p class="text-xs text-gray-400 mt-1">System-wide 7-level BFS 3-matrix forced spillover node topology and downline inspector</p>
    </div>

    <!-- Package Selector Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
        <?php foreach ($packages as $pkg): ?>
            <a href="index.php?page=admin_matrix_tree&package_id=<?= $pkg['id'] ?>" class="px-3.5 py-2 rounded-2xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap <?= $pkg['id'] == $selectedPackageId ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'bg-gray-800 text-gray-300 hover:bg-gray-700 border border-gray-700' ?>">
                Tier #<?= $pkg['sort_order'] ?> (<?= formatCurrency($pkg['price']) ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Helper function to recursively render matrix tree node
function renderAdminTreeNode(array $node, int $depth = 1) {
    if (empty($node)) return;
    ?>
    <div class="flex flex-col items-center">
        <!-- Node Card -->
        <div class="glass-card rounded-2xl p-3 border border-gray-700/80 hover:border-brand-500/80 transition shadow-xl min-w-[140px] text-center relative group">
            <div class="w-9 h-9 mx-auto rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-black font-extrabold flex items-center justify-center text-sm shadow-md mb-1.5">
                <?= strtoupper(substr($node['username'], 0, 2)) ?>
            </div>
            <div class="font-bold text-xs text-white truncate max-w-[120px]" title="<?= htmlspecialchars($node['full_name']) ?>">
                @<?= htmlspecialchars($node['username']) ?>
            </div>
            <div class="text-[10px] text-brand-400 font-mono mt-0.5">
                Depth <?= $node['depth'] ?> | Pos <?= $node['position_index'] ?>
            </div>
            <div class="text-[10px] text-gray-400 mt-1 flex items-center justify-center gap-1">
                <i data-lucide="users" class="w-3 h-3 text-emerald-400"></i> Children: <strong class="text-white"><?= $node['children_count'] ?>/3</strong>
            </div>
        </div>

        <!-- Children Connectors & Subnodes -->
        <?php if (!empty($node['children'])): ?>
            <div class="w-0.5 h-6 bg-gradient-to-b from-brand-500 to-gray-700"></div>
            <div class="flex gap-4 sm:gap-6 relative before:absolute before:top-0 before:left-1/4 before:right-1/4 before:h-0.5 before:bg-gray-700">
                <?php foreach ($node['children'] as $child): ?>
                    <div class="flex flex-col items-center relative before:absolute before:-top-6 before:w-0.5 before:h-6 before:bg-gray-700">
                        <?php renderAdminTreeNode($child, $depth + 1); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>

<!-- Visual Tree Container -->
<div class="glass-card rounded-3xl p-6 sm:p-10 mb-8 border border-gray-800 overflow-x-auto">
    <div class="text-center mb-6">
        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-500/10 text-brand-400 border border-brand-500/20">
            Forced BFS 3-Matrix Topology for Package Tier #<?= $selectedPackageId ?>
        </span>
    </div>

    <div class="min-w-[700px] flex justify-center py-4">
        <?php if (!empty($tree)): ?>
            <?php renderAdminTreeNode($tree); ?>
        <?php else: ?>
            <div class="text-center py-12 text-gray-400">
                <i data-lucide="git-branch" class="w-12 h-12 mx-auto text-gray-600 mb-3"></i>
                <p class="text-sm font-semibold">No active nodes in this matrix tier yet.</p>
                <p class="text-xs text-gray-500 mt-1">Once members purchase Package Tier #<?= $selectedPackageId ?>, their nodes will populate here via BFS spillover.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
