<?php require_once __DIR__ . '/../layout/header.php'; ?>

<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$referralUrl = "{$protocol}://{$host}/index.php?page=register&ref=" . urlencode($user['username']);
$shareText = urlencode("Join Pinnacle Hunt - high-performing 3-Matrix platform! Register using my link: " . $referralUrl);
?>

<!-- Header Banner -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            Welcome back, <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-400 to-emerald-300"><?= htmlspecialchars($user['full_name']) ?></span> 👋
        </h1>
        <p class="text-sm text-gray-400 mt-1">Breadth-First Search (BFS) 3-Matrix Forced Spillover Member Portal</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="index.php?page=genealogy" class="px-4 py-2.5 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 font-semibold text-xs transition flex items-center gap-2">
            <i data-lucide="git-fork" class="w-4 h-4 text-brand-400"></i> View Matrix Tree
        </a>
        <a href="index.php?page=ledger" class="px-4 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-extrabold text-xs transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
            <i data-lucide="arrow-up-right" class="w-4 h-4"></i> Withdraw Funds
        </a>
    </div>
</div>

<!-- Stats Overview Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Wallet Balance -->
    <div class="glass-card rounded-3xl p-6 relative overflow-hidden border border-gray-800 hover:border-brand-500/40 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Withdrawable Balance</span>
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-white tracking-tight"><?= formatCurrency($user['wallet_balance']) ?></div>
        <p class="text-xs text-emerald-400 mt-2 flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Ready for instant payout</p>
    </div>

    <!-- Reserve Balance Pool -->
    <div class="glass-card rounded-3xl p-6 relative overflow-hidden border border-gray-800 hover:border-brand-500/40 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reserve Auto-Upgrade Pool</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-amber-400 tracking-tight"><?= formatCurrency($user['reserve_balance']) ?></div>
        <p class="text-xs text-gray-400 mt-2">Held for automated tier progression</p>
    </div>

    <!-- Total Gross Earnings -->
    <div class="glass-card rounded-3xl p-6 relative overflow-hidden border border-gray-800 hover:border-brand-500/40 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Gross Earnings</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-white tracking-tight"><?= formatCurrency($totalEarnings) ?></div>
        <p class="text-xs text-gray-400 mt-2">Commissions & Matrix level payouts</p>
    </div>

    <!-- Direct Referrals -->
    <div class="glass-card rounded-3xl p-6 relative overflow-hidden border border-gray-800 hover:border-brand-500/40 transition">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Direct Referrals</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="text-3xl font-extrabold text-white tracking-tight"><?= $totalReferrals ?> <span class="text-sm font-normal text-gray-400">Members</span></div>
        <p class="text-xs text-purple-400 mt-2">Personal sponsor network</p>
    </div>
</div>

<!-- Social Referral Sharing Widget -->
<div class="glass-card rounded-3xl p-6 mb-8 border border-gray-800 relative overflow-hidden">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i data-lucide="share-2" class="w-5 h-5 text-brand-400"></i> Your Referral & Spillover Engine Link
            </h3>
            <p class="text-xs text-gray-400 mt-1">Share your referral link to earn 10% direct sponsor commissions and build downline volume.</p>
        </div>

        <div class="flex-1 max-w-2xl">
            <div class="flex items-center gap-2">
                <input type="text" readonly value="<?= htmlspecialchars($referralUrl) ?>" class="w-full px-4 py-2.5 bg-gray-900/90 border border-gray-700 rounded-2xl text-xs font-mono text-gray-200 focus:outline-none">
                <button id="copyRefBtn" onclick="copyToClipboard('<?= htmlspecialchars($referralUrl) ?>', 'copyRefBtn')" class="px-4 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-extrabold text-xs transition flex items-center gap-1.5 whitespace-nowrap shadow-md">
                    <i data-lucide="copy" class="w-4 h-4"></i> Copy Link
                </button>
            </div>

            <!-- Social Quick Share Icons -->
            <div class="flex items-center gap-3 mt-3">
                <span class="text-xs text-gray-400 font-semibold">Share on:</span>
                <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> WhatsApp
                </a>
                <a href="https://t.me/share/url?url=<?= urlencode($referralUrl) ?>&text=<?= urlencode("Join Pinnacle Hunt!") ?>" target="_blank" class="px-3 py-1.5 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/20 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Telegram
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>" target="_blank" class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="twitter" class="w-3.5 h-3.5"></i> Twitter/X
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Package Tier Selection & Activation Grid -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Investment & Package Tiers</h2>
            <p class="text-xs text-gray-400 mt-1">Activate packages to unlock BFS 3-Matrix positioning and 6-level commission streams.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($packages as $pkg): ?>
            <?php
                $isPurchased = in_array($pkg['id'], $userPackageIds);
                $statusText = '';
                if ($isPurchased) {
                    foreach ($userPackages as $up) {
                        if ($up['package_id'] == $pkg['id']) {
                            $statusText = ucfirst($up['status']);
                        }
                    }
                }
            ?>
            <div class="glass-card rounded-3xl p-6 border border-gray-800 flex flex-col justify-between relative transition hover:border-brand-500/50 <?= $isPurchased ? 'ring-1 ring-brand-500/30 bg-brand-500/5' : '' ?>">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-gray-800 text-gray-300 border border-gray-700">
                            Tier #<?= $pkg['sort_order'] ?>
                        </span>
                        <?php if ($isPurchased): ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> <?= $statusText ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-lg font-extrabold text-white mb-1"><?= htmlspecialchars($pkg['name']) ?></h3>
                    <div class="text-3xl font-black text-brand-400 mb-4"><?= formatCurrency($pkg['price']) ?></div>

                    <ul class="text-xs text-gray-300 space-y-2 mb-6 border-t border-gray-800 pt-4">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-brand-400"></i> Direct Bonus: <strong class="text-white"><?= $pkg['direct_commission_percent'] ?>%</strong></li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-brand-400"></i> Matrix Structure: <strong class="text-white">3-Matrix (6 Levels)</strong></li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-brand-400"></i> Automated Spillover: <strong class="text-white">BFS Algorithm</strong></li>
                    </ul>
                </div>

                <div>
                    <?php if ($isPurchased): ?>
                        <a href="index.php?page=genealogy&package_id=<?= $pkg['id'] ?>" class="w-full py-2.5 px-4 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-extrabold transition flex items-center justify-center gap-1.5 border border-gray-700">
                            <i data-lucide="git-fork" class="w-3.5 h-3.5 text-brand-400"></i> View Matrix Tree
                        </a>
                    <?php else: ?>
                        <form action="index.php?action=purchase_package" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                            <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold text-xs transition shadow-md shadow-brand-500/20 flex items-center justify-center gap-1.5">
                                <i data-lucide="zap" class="w-4 h-4"></i> Activate Package
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Commission Streams Table -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i data-lucide="activity" class="w-5 h-5 text-brand-400"></i> Recent Payout Streams
        </h3>
        <a href="index.php?page=ledger" class="text-xs font-semibold text-brand-400 hover:underline flex items-center gap-1">View Full Ledger &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Tier</th>
                    <th class="px-4 py-3">Source Member</th>
                    <th class="px-4 py-3">Gross Amount</th>
                    <th class="px-4 py-3">Reserve Hold (25%)</th>
                    <th class="px-4 py-3">Net Paid</th>
                    <th class="px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($recentCommissions)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 font-sans">No recent commissions earned yet. Activate packages or refer members to get started!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentCommissions as $comm): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3">
                                <?php if ($comm['type'] === 'direct_sponsor'): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-sans font-bold text-[10px]">Direct Sponsor</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 font-sans font-bold text-[10px]">Level <?= $comm['level'] ?> Matrix</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-sans font-semibold text-white"><?= htmlspecialchars($comm['package_name']) ?></td>
                            <td class="px-4 py-3 text-brand-400 font-sans">@<?= htmlspecialchars($comm['source_username']) ?></td>
                            <td class="px-4 py-3 text-white"><?= formatCurrency($comm['amount']) ?></td>
                            <td class="px-4 py-3 text-amber-400"><?= formatCurrency($comm['reserved_amount']) ?></td>
                            <td class="px-4 py-3 text-emerald-400 font-bold"><?= formatCurrency($comm['net_amount']) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-[11px] font-sans"><?= $comm['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
