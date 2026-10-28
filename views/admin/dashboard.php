<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header Banner -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="shield" class="w-8 h-8 text-brand-400"></i> Admin Control Panel
        </h1>
        <p class="text-xs text-gray-400 mt-1">Platform management, spillover oversight, rule configuration, and financial reports</p>
    </div>

    <!-- One-Click CSV Export Tools -->
    <div class="flex items-center gap-2 flex-wrap">
        <a href="/index.php?action=export_csv&type=ledger" class="px-3.5 py-2 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-bold transition flex items-center gap-1.5 shadow-md">
            <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i> Export Ledger CSV
        </a>
        <a href="/index.php?action=export_csv&type=commissions" class="px-3.5 py-2 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-bold transition flex items-center gap-1.5 shadow-md">
            <i data-lucide="download" class="w-4 h-4 text-blue-400"></i> Export Commissions CSV
        </a>
        <a href="/index.php?action=export_csv&type=withdrawals" class="px-3.5 py-2 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-bold transition flex items-center gap-1.5 shadow-md">
            <i data-lucide="download" class="w-4 h-4 text-amber-400"></i> Export Withdrawals CSV
        </a>
    </div>
</div>

<!-- Admin Key Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Total Active Members -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Registered Users</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-white tracking-tight"><?= $totalUsers ?></div>
        <p class="text-xs text-gray-400 mt-2">Active network members</p>
    </div>

    <!-- Total Withdrawable Wallet Balances -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">User Wallet Balances</span>
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-emerald-400 tracking-tight"><?= formatCurrency($totalSystemWallet) ?></div>
        <p class="text-xs text-gray-400 mt-2">Liability pool ready for payout</p>
    </div>

    <!-- Reserve Funds Pool -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reserve Hold Pool</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-amber-400 tracking-tight"><?= formatCurrency($totalReserveHold) ?></div>
        <p class="text-xs text-gray-400 mt-2">Allocated for package auto-upgrades</p>
    </div>

    <!-- Total Gross Commissions Paid -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gross Commissions Generated</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-white tracking-tight"><?= formatCurrency($totalCommissionsPaid) ?></div>
        <p class="text-xs text-gray-400 mt-2">Direct & Matrix payouts total</p>
    </div>
</div>

<!-- Pending Withdrawal Approval Table -->
<div class="glass-card rounded-3xl p-6 mb-8 border border-gray-800">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i data-lucide="clock" class="w-5 h-5 text-amber-400"></i> Pending Withdrawal Requests (<?= count($pendingWithdrawals) ?>)
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Payout Details</th>
                    <th class="px-4 py-3">Requested Date</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($pendingWithdrawals)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500 font-sans">No pending withdrawal requests.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingWithdrawals as $pw): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3 text-gray-400">#<?= $pw['id'] ?></td>
                            <td class="px-4 py-3 font-sans">
                                <div class="font-bold text-white">@<?= htmlspecialchars($pw['username']) ?></div>
                                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($pw['full_name']) ?></div>
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-400 text-sm"><?= formatCurrency($pw['amount']) ?></td>
                            <td class="px-4 py-3 text-gray-300 font-sans text-[11px] max-w-xs truncate" title="<?= htmlspecialchars($pw['payout_details']) ?>"><?= htmlspecialchars($pw['payout_details']) ?></td>
                            <td class="px-4 py-3 text-gray-500 font-sans text-[11px]"><?= $pw['created_at'] ?></td>
                            <td class="px-4 py-3 font-sans">
                                <div class="flex items-center gap-2">
                                    <form action="/index.php?action=process_withdrawal" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="withdrawal_id" value="<?= $pw['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" onclick="return confirm('Approve and pay this withdrawal?')" class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                                        </button>
                                    </form>

                                    <form action="/index.php?action=process_withdrawal" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="withdrawal_id" value="<?= $pw['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" onclick="return confirm('Reject this withdrawal and refund wallet balance?')" class="px-3 py-1.5 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 text-xs font-bold transition flex items-center gap-1">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recently Registered Members -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i data-lucide="user-plus" class="w-5 h-5 text-brand-400"></i> New Member Signups
        </h3>
        <a href="/index.php?page=admin_members" class="text-xs font-semibold text-brand-400 hover:underline">View All Members &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Username</th>
                    <th class="px-4 py-3">Full Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Joined Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php foreach ($recentUsers as $ru): ?>
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="px-4 py-3 text-gray-400">#<?= $ru['id'] ?></td>
                        <td class="px-4 py-3 font-bold text-brand-400 font-sans">@<?= htmlspecialchars($ru['username']) ?></td>
                        <td class="px-4 py-3 text-white font-sans"><?= htmlspecialchars($ru['full_name']) ?></td>
                        <td class="px-4 py-3 text-gray-300 font-sans"><?= htmlspecialchars($ru['email']) ?></td>
                        <td class="px-4 py-3 font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $ru['role'] === 'admin' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-gray-800 text-gray-300' ?>"><?= strtoupper($ru['role']) ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 font-sans text-[11px]"><?= $ru['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
