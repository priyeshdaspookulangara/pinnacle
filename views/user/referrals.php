<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header & Quick Share -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="users-round" class="w-8 h-8 text-brand-400"></i> My Referrals
        </h1>
        <p class="text-xs text-gray-400 mt-1">Manage and track all direct team members sponsored by your referral link</p>
    </div>

    <!-- Referral Link Widget -->
    <div class="glass-card rounded-2xl p-2.5 border border-gray-800 flex items-center gap-2 bg-gray-900/80">
        <input type="text" readonly value="<?= APP_URL ?>/index.php?page=register&ref=<?= htmlspecialchars($user['username']) ?>" id="referralLinkInput" class="bg-black/50 text-xs font-mono text-gray-300 px-3 py-1.5 rounded-xl border border-gray-800 focus:outline-none w-64 md:w-80 truncate">
        <button onclick="copyReferralLink()" class="px-3 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-extrabold text-xs transition flex items-center gap-1.5 shadow-lg shadow-brand-500/20 whitespace-nowrap">
            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Link
        </button>
    </div>
</div>

<!-- Key Referral Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="glass-card rounded-3xl p-6 border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center border border-brand-500/20">
            <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400">Total Referrals</div>
            <div class="text-2xl font-black text-white mt-0.5"><?= count($referrals) ?></div>
        </div>
    </div>

    <?php
        $activePackageCount = array_sum(array_column($referrals, 'active_packages'));
        $totalSponsorEarned = array_sum(array_column($referrals, 'total_generated_comm'));
    ?>

    <div class="glass-card rounded-3xl p-6 border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
            <i data-lucide="box" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400">Active Package Subscriptions</div>
            <div class="text-2xl font-black text-white mt-0.5"><?= $activePackageCount ?></div>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
            <i data-lucide="coins" class="w-6 h-6"></i>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400">Direct Bonus Earned</div>
            <div class="text-2xl font-black text-white mt-0.5"><?= formatCurrency($totalSponsorEarned ?: 0) ?></div>
        </div>
    </div>
</div>

<!-- Social Share Options -->
<div class="glass-card rounded-3xl p-6 mb-8 border border-gray-800 bg-gradient-to-r from-gray-900 via-gray-900/80 to-brand-950/30">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="share-2" class="w-4 h-4 text-brand-400"></i> Invite New Members & Grow Your Team
            </h3>
            <p class="text-xs text-gray-400 mt-1">Share your link directly across popular platforms to get direct referral commissions.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="https://api.whatsapp.com/send?text=Join%20Pinnacle%20Hunt%20using%20my%20referral%20link:%20<?= urlencode(APP_URL . '/index.php?page=register&ref=' . $user['username']) ?>" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 font-bold text-xs transition flex items-center gap-2">
                <i data-lucide="message-square" class="w-4 h-4"></i> WhatsApp
            </a>
            <a href="https://t.me/share/url?url=<?= urlencode(APP_URL . '/index.php?page=register&ref=' . $user['username']) ?>&text=Join%20Pinnacle%20Hunt!" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600/20 hover:bg-sky-600/30 text-sky-400 border border-sky-500/30 font-bold text-xs transition flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i> Telegram
            </a>
        </div>
    </div>
</div>

<!-- Direct Referrals Detailed Table -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i data-lucide="users" class="w-5 h-5 text-brand-400"></i> Direct Referral Directory
        </h3>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-800 text-gray-300 border border-gray-700">
            <?= count($referrals) ?> Member(s)
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">Member Details</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Active Packages</th>
                    <th class="px-4 py-3">Direct Bonus Generated</th>
                    <th class="px-4 py-3">Registration Date</th>
                    <th class="px-4 py-3 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($referrals)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-500 font-sans">
                            <i data-lucide="user-plus" class="w-10 h-10 mx-auto text-gray-600 mb-2"></i>
                            <p class="font-semibold text-gray-400">No direct referrals recorded yet.</p>
                            <p class="text-xs text-gray-500 mt-1">Copy and share your referral link above to start earning sponsor rewards!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($referrals as $ref): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3.5 font-sans">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gray-800 text-brand-400 font-bold flex items-center justify-center text-xs border border-gray-700">
                                        <?= strtoupper(substr($ref['username'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">@<?= htmlspecialchars($ref['username']) ?></div>
                                        <div class="text-[11px] text-gray-400"><?= htmlspecialchars($ref['full_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-gray-300 font-sans">
                                <div><?= htmlspecialchars($ref['email']) ?></div>
                                <div class="text-[11px] text-gray-500"><?= htmlspecialchars($ref['phone'] ?: 'No Phone') ?></div>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($ref['active_packages'] > 0): ?>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-brand-500/10 text-brand-400 border border-brand-500/20">
                                        <?= $ref['active_packages'] ?> Active Tier(s)
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-800 text-gray-500 border border-gray-700">
                                        No Active Tier
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-brand-400 font-bold font-mono">
                                <?= formatCurrency($ref['total_generated_comm'] ?: 0) ?>
                            </td>
                            <td class="px-4 py-3.5 text-gray-400 font-sans">
                                <?= date('M d, Y', strtotime($ref['created_at'])) ?>
                            </td>
                            <td class="px-4 py-3.5 text-right font-sans">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Active Member
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyReferralLink() {
    const linkInput = document.getElementById('referralLinkInput');
    linkInput.select();
    navigator.clipboard.writeText(linkInput.value);
    alert('Referral link copied to clipboard!');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
