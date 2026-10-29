<?php require_once __DIR__ . '/../layout/header.php'; ?>

<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$referralUrl = "{$protocol}://{$host}/index.php?page=register&ref=" . urlencode($user['username'] ?? 'admin');
?>

<!-- Header Title & Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded-full bg-brand-500/10 text-brand-400 border border-brand-500/20 text-[11px] font-mono font-bold uppercase tracking-wider">
                Official Compensation Presentation
            </span>
        </div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Business Plan & Brochure</h1>
        <p class="text-xs text-gray-400 mt-1">Full breakdown of package tiers, BFS 3-Matrix forced spillover structure, and earnings model.</p>
    </div>

    <div class="flex items-center gap-3">
        <button onclick="window.print()" class="px-4 py-2.5 rounded-2xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 font-bold text-xs transition flex items-center gap-2 shadow-md">
            <i data-lucide="printer" class="w-4 h-4 text-brand-400"></i> Print / Save Page
        </button>
        <a href="index.php?action=download_pdf" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold text-xs transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4"></i> Download PDF Brochure
        </a>
    </div>
</div>

<!-- Hero Executive Summary Card -->
<div class="glass-card rounded-3xl p-6 sm:p-8 mb-8 border border-gray-800 relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-3xl">
        <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2">
            <i data-lucide="sparkles" class="w-5 h-5 text-brand-400"></i> Platform Architecture Overview
        </h2>
        <p class="text-xs text-gray-300 leading-relaxed mb-6">
            Pinnacle Hunt is designed as a high-frequency, fair, and scalable financial distribution matrix. By combining an instant <strong class="text-brand-400">10% Direct Referral Bonus</strong>, an automated <strong class="text-brand-400">Breadth-First Search (BFS) 3-Matrix Spillover</strong> engine across 6 depth levels, and an automated <strong class="text-amber-400">25% Reserve Auto-Upgrade Pool</strong>, Pinnacle Hunt ensures continuous upward movement for all participating members.
        </p>

        <!-- Feature Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                    10%
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Direct Sponsor Bonus</div>
                    <div class="text-[10px] text-gray-400">Instant Cash Credited</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold">
                    3x6
                </div>
                <div>
                    <div class="text-xs font-bold text-white">BFS Forced Spillover</div>
                    <div class="text-[10px] text-gray-400">6 Levels Systematic Placement</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    25%
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Reserve Auto-Upgrade</div>
                    <div class="text-[10px] text-gray-400">Automated Tier Progression</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section 1: Investment & Package Tiers -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-white tracking-tight flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-500/20 text-brand-400 text-xs flex items-center justify-center font-mono">1</span>
                Investment Package Tiers
            </h2>
            <p class="text-xs text-gray-400 mt-1">Eight distinct matrix tiers ranging from starter entry to elite pinnacle levels.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($packages as $pkg): ?>
            <?php $directCommission = ($pkg['price'] * $pkg['direct_commission_percent']) / 100; ?>
            <div class="glass-card rounded-3xl p-6 border border-gray-800 flex flex-col justify-between transition hover:border-brand-500/50">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-gray-800 text-gray-300 border border-gray-700">
                            Tier #<?= $pkg['sort_order'] ?>
                        </span>
                        <span class="text-[10px] text-brand-400 font-mono font-bold">3-Matrix Tree</span>
                    </div>

                    <h3 class="text-lg font-extrabold text-white mb-1"><?= htmlspecialchars($pkg['name']) ?></h3>
                    <div class="text-3xl font-black text-brand-400 mb-4"><?= formatCurrency($pkg['price']) ?></div>

                    <ul class="text-xs text-gray-300 space-y-2 mb-4 border-t border-gray-800/80 pt-4 font-mono">
                        <li class="flex items-center justify-between">
                            <span class="text-gray-400 font-sans">Direct Referral (10%):</span>
                            <strong class="text-emerald-400"><?= formatCurrency($directCommission) ?></strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-400 font-sans">Matrix Capacity:</span>
                            <strong class="text-white font-sans">1,092 Members</strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-400 font-sans">Spillover System:</span>
                            <strong class="text-blue-400 font-sans">BFS Queue</strong>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Section 2: 6-Level Matrix Payout Breakdown -->
<div class="glass-card rounded-3xl p-6 sm:p-8 mb-10 border border-gray-800">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-white tracking-tight flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-500/20 text-brand-400 text-xs flex items-center justify-center font-mono">2</span>
                6-Level Matrix Level Commission Structure
            </h2>
            <p class="text-xs text-gray-400 mt-1">Payout percentages allocated across your 3-matrix downline tree.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/80 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3.5">Matrix Level</th>
                    <th class="px-4 py-3.5">Max Member Count</th>
                    <th class="px-4 py-3.5">Payout %</th>
                    <th class="px-4 py-3.5">Payout Example (Tier 1 - ₹500)</th>
                    <th class="px-4 py-3.5">Payout Example (Tier 8 - ₹1,00,000)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/80 font-mono">
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 1</td>
                    <td class="px-4 py-3.5 text-brand-400">3 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">5.0%</td>
                    <td class="px-4 py-3.5">₹25.00 / member (Max: ₹75.00)</td>
                    <td class="px-4 py-3.5">₹5,000.00 / member (Max: ₹15,000.00)</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 2</td>
                    <td class="px-4 py-3.5 text-brand-400">9 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">4.0%</td>
                    <td class="px-4 py-3.5">₹20.00 / member (Max: ₹180.00)</td>
                    <td class="px-4 py-3.5">₹4,000.00 / member (Max: ₹36,000.00)</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 3</td>
                    <td class="px-4 py-3.5 text-brand-400">27 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">3.0%</td>
                    <td class="px-4 py-3.5">₹15.00 / member (Max: ₹405.00)</td>
                    <td class="px-4 py-3.5">₹3,000.00 / member (Max: ₹81,000.00)</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 4</td>
                    <td class="px-4 py-3.5 text-brand-400">81 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">2.0%</td>
                    <td class="px-4 py-3.5">₹10.00 / member (Max: ₹810.00)</td>
                    <td class="px-4 py-3.5">₹2,000.00 / member (Max: ₹1,62,000.00)</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 5</td>
                    <td class="px-4 py-3.5 text-brand-400">243 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">1.0%</td>
                    <td class="px-4 py-3.5">₹5.00 / member (Max: ₹1,215.00)</td>
                    <td class="px-4 py-3.5">₹1,000.00 / member (Max: ₹2,43,000.00)</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 6</td>
                    <td class="px-4 py-3.5 text-brand-400">729 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">1.0%</td>
                    <td class="px-4 py-3.5">₹5.00 / member (Max: ₹3,645.00)</td>
                    <td class="px-4 py-3.5">₹1,000.00 / member (Max: ₹7,29,000.00)</td>
                </tr>
            </tbody>
            <tfoot class="bg-gray-900 font-bold text-white font-mono border-t border-gray-700">
                <tr>
                    <td class="px-4 py-3.5 font-sans">TOTAL MATRIX</td>
                    <td class="px-4 py-3.5 text-brand-400">1,092 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400">16.0% Total</td>
                    <td class="px-4 py-3.5 text-emerald-400">Max Tier 1 Total: ₹6,330.00</td>
                    <td class="px-4 py-3.5 text-emerald-400">Max Tier 8 Total: ₹12,66,000.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Section 3: BFS Spillover & 25% Reserve Upgrade Workflow Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
    <!-- BFS Spillover Explanation -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="git-fork" class="w-5 h-5 text-brand-400"></i> Breadth-First Search (BFS) Spillover
        </h3>
        <p class="text-xs text-gray-300 leading-relaxed mb-4">
            Our company-wide BFS algorithm ensures fair spillover placement. When you refer more than 3 members, the 4th, 5th, and subsequent members automatically spill over to fill open slots in your downline tree from left to right, level by level.
        </p>
        <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800/80 text-xs text-gray-400 space-y-2">
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i> <strong>Strict 3-Child Constraint:</strong> Maximum 3 direct children per node.</div>
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i> <strong>Automatic Queue:</strong> Level completion prioritizes earliest joins.</div>
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i> <strong>Separate Trees:</strong> Independent tree structures for each tier package.</div>
        </div>
    </div>

    <!-- 25% Reserve Auto-Upgrade Pool -->
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <i data-lucide="shield-check" class="w-5 h-5 text-amber-400"></i> 25% Reserve Auto-Upgrade Mechanism
        </h3>
        <p class="text-xs text-gray-300 leading-relaxed mb-4">
            Every commission earned is automatically split: <strong>75%</strong> is credited to your instantly withdrawable Wallet Balance, and <strong>25%</strong> is allocated to your Reserve Balance Pool.
        </p>
        <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800/80 text-xs text-gray-400 space-y-2">
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i> <strong>Zero Out-of-Pocket:</strong> Higher package tiers unlock automatically.</div>
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i> <strong>Automated Tier Placement:</strong> Instant entry into Tier 2, Tier 3, etc.</div>
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i> <strong>Seamless Rollover:</strong> Excess reserve rolls over to the next tier requirement.</div>
        </div>
    </div>
</div>

<!-- Personal Referral Share Banner -->
<div class="glass-card rounded-3xl p-6 sm:p-8 border border-brand-500/30 bg-brand-500/5 relative overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h3 class="text-xl font-extrabold text-white mb-1">Share Pinnacle Hunt Business Plan</h3>
            <p class="text-xs text-gray-300">Invite new members using your personal referral link to earn 10% direct commissions and matrix level bonuses!</p>
        </div>

        <div class="flex items-center gap-3">
            <input type="text" readonly value="<?= htmlspecialchars($referralUrl) ?>" class="px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-2xl text-xs font-mono text-gray-200 focus:outline-none w-64">
            <button id="brochureCopyBtn" onclick="copyToClipboard('<?= htmlspecialchars($referralUrl) ?>', 'brochureCopyBtn')" class="px-5 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-extrabold text-xs transition shadow-md shadow-brand-500/20 whitespace-nowrap">
                Copy Joining Link
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
