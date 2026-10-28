<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="users" class="w-8 h-8 text-brand-400"></i> Member & Matrix Oversight
        </h1>
        <p class="text-xs text-gray-400 mt-1">Directory of system users, sponsor linkages, wallet balances, and reserve holdings</p>
    </div>
</div>

<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Member Details</th>
                    <th class="px-4 py-3">Direct Sponsor</th>
                    <th class="px-4 py-3">Wallet Balance</th>
                    <th class="px-4 py-3">Reserve Hold</th>
                    <th class="px-4 py-3">Role & Status</th>
                    <th class="px-4 py-3">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 font-sans">No members registered yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3 text-gray-400">#<?= $u['id'] ?></td>
                            <td class="px-4 py-3 font-sans">
                                <div class="font-bold text-white">@<?= htmlspecialchars($u['username']) ?></div>
                                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>)</div>
                            </td>
                            <td class="px-4 py-3 text-brand-400 font-sans">
                                <?= !empty($u['sponsor_username']) ? '@' . htmlspecialchars($u['sponsor_username']) : '<span class="text-gray-500">None (Root)</span>' ?>
                            </td>
                            <td class="px-4 py-3 text-emerald-400 font-bold"><?= formatCurrency($u['wallet_balance']) ?></td>
                            <td class="px-4 py-3 text-amber-400 font-bold"><?= formatCurrency($u['reserve_balance']) ?></td>
                            <td class="px-4 py-3 font-sans">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $u['role'] === 'admin' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-gray-800 text-gray-300' ?>"><?= strtoupper($u['role']) ?></span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><?= ucfirst($u['status']) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-sans text-[11px]"><?= $u['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
