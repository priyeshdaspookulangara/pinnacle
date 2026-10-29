<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="git-fork" class="w-8 h-8 text-brand-400"></i> BFS 3-Matrix Genealogy Tree
        </h1>
        <p class="text-xs text-gray-400 mt-1">Interactive corporate downline matrix visualization spanning 6 levels</p>
    </div>

    <!-- Package Selector Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
        <?php foreach ($packages as $pkg): ?>
            <a href="index.php?page=genealogy&package_id=<?= $pkg['id'] ?>" class="px-3.5 py-2 rounded-2xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap <?= $pkg['id'] == $selectedPackageId ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'bg-gray-800 text-gray-300 hover:bg-gray-700 border border-gray-700' ?>">
                Tier #<?= $pkg['sort_order'] ?> (<?= formatCurrency($pkg['price']) ?>)
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Helper function to recursively render matrix tree node
function renderTreeNode(array $node, int $depth = 1) {
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
                        <?php renderTreeNode($child, $depth + 1); ?>
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
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20">
            Forced BFS 3-Matrix Structure for Package Tier #<?= $selectedPackageId ?>
        </span>
    </div>

    <div class="min-w-[700px] flex justify-center py-4">
        <?php if (!empty($tree)): ?>
            <?php renderTreeNode($tree); ?>
        <?php else: ?>
            <div class="text-center py-12 text-gray-400">
                <i data-lucide="git-branch" class="w-12 h-12 mx-auto text-gray-600 mb-3"></i>
                <p class="text-sm font-semibold">No active nodes in this matrix tier yet.</p>
                <p class="text-xs text-gray-500 mt-1">Activate Tier #<?= $selectedPackageId ?> from your dashboard to enter this matrix structure!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Direct Referral Downline Table -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i data-lucide="users" class="w-5 h-5 text-brand-400"></i> Direct Sponsor Referrals (<?= count($directReferrals) ?>)
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">Member</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Downline Size</th>
                    <th class="px-4 py-3">Account Status</th>
                    <th class="px-4 py-3">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($directReferrals)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500 font-sans">No direct referrals yet. Share your referral link to build your team!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($directReferrals as $ref): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3 font-sans">
                                <div class="font-bold text-white">@<?= htmlspecialchars($ref['username']) ?></div>
                                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($ref['full_name']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-gray-300 font-sans"><?= htmlspecialchars($ref['email']) ?></td>
                            <td class="px-4 py-3 text-brand-400 font-bold"><?= $ref['ref_count'] ?> Members</td>
                            <td class="px-4 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-sans"><?= $ref['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
