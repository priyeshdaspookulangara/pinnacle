<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header Title -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center border border-brand-500/20">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            Pinnacle Hunt <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-400 to-emerald-300">Business Plan</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Comprehensive 6-Level 3-Matrix Forced Spillover Compensation Architecture</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="index.php?page=dashboard" class="px-4 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-extrabold text-xs transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Go to Dashboard
        </a>
    </div>
</div>

<!-- Plan Highlights Banner Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
        </div>
        <div class="text-xs font-semibold text-gray-400 uppercase">Direct Sponsor Bonus</div>
        <div class="text-2xl font-black text-white mt-1">10% Instant</div>
        <p class="text-xs text-emerald-400 mt-1">Paid on every direct referral activation</p>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-3">
            <i data-lucide="git-fork" class="w-5 h-5"></i>
        </div>
        <div class="text-xs font-semibold text-gray-400 uppercase">Matrix Matrix Structure</div>
        <div class="text-2xl font-black text-white mt-1">3 &times; 6 Forced Spillover</div>
        <p class="text-xs text-blue-400 mt-1">Company-wide BFS placement engine</p>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-3">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
        </div>
        <div class="text-xs font-semibold text-gray-400 uppercase">Auto-Upgrade Pool</div>
        <div class="text-2xl font-black text-amber-400 mt-1">25% Reserve Hold</div>
        <p class="text-xs text-gray-400 mt-1">Automated progression to higher tiers</p>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-3">
            <i data-lucide="layers" class="w-5 h-5"></i>
        </div>
        <div class="text-xs font-semibold text-gray-400 uppercase">Package Tiers</div>
        <div class="text-2xl font-black text-white mt-1">8 Tier Levels</div>
        <p class="text-xs text-purple-400 mt-1">From ₹500 up to ₹1,00,000</p>
    </div>
</div>

<!-- Section 1: Matrix Commission Levels -->
<div class="glass-card rounded-3xl p-6 border border-gray-800 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-brand-400"></i> 6-Level Matrix Payout Structure
            </h2>
            <p class="text-xs text-gray-400 mt-1">Every package tier maintains an independent 3-matrix forced spillover structure filled level by level via BFS algorithm.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-brand-500/10 text-brand-400 border border-brand-500/20">
            Total Capacity: 1,092 Members
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/80 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3.5">Matrix Level</th>
                    <th class="px-4 py-3.5">Members per Level (3&times;N)</th>
                    <th class="px-4 py-3.5">Level Commission %</th>
                    <th class="px-4 py-3.5">Payout Example (Tier 1 ₹500)</th>
                    <th class="px-4 py-3.5 text-right">Total Level Yield (Tier 1)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 1</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">3 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">5.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹25.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹75.00</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 2</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">9 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">4.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹20.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹180.00</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 3</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">27 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">3.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹15.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹405.00</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 4</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">81 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">2.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹10.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹810.00</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 5</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">243 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">1.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹5.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹1,215.00</td>
                </tr>
                <tr class="hover:bg-gray-800/30 transition">
                    <td class="px-4 py-3.5 font-bold text-white font-sans">Level 6</td>
                    <td class="px-4 py-3.5 text-brand-400 font-bold">729 Members</td>
                    <td class="px-4 py-3.5 text-emerald-400 font-bold">1.00%</td>
                    <td class="px-4 py-3.5 font-sans">₹5.00 per member</td>
                    <td class="px-4 py-3.5 text-right text-emerald-400 font-bold">₹3,645.00</td>
                </tr>
                <tr class="bg-brand-500/10 font-bold text-white border-t-2 border-brand-500/30">
                    <td class="px-4 py-4 font-sans text-brand-400">Total Matrix Pool</td>
                    <td class="px-4 py-4 font-mono text-white">1,092 Downline Members</td>
                    <td class="px-4 py-4 text-emerald-400 font-mono">16.00% Total Matrix %</td>
                    <td class="px-4 py-4 font-sans text-gray-300">Sum of 6 Levels</td>
                    <td class="px-4 py-4 text-right text-brand-400 text-sm font-mono font-extrabold">₹6,330.00 / matrix cycle</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: Package Tiers & Potential Matrix Yield -->
<div class="glass-card rounded-3xl p-6 border border-gray-800 mb-8">
    <div class="mb-6">
        <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
            <i data-lucide="box" class="w-5 h-5 text-brand-400"></i> Investment Package Tiers & Matrix Potential
        </h2>
        <p class="text-xs text-gray-400 mt-1">Members can activate any tier or progress automatically through earnings held in the reserve pool.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($packages as $pkg): ?>
            <?php
                $price = (float)$pkg['price'];
                $directComm = $price * 0.10;
                $totalMatrixYield = $price * ( (3*0.05) + (9*0.04) + (27*0.03) + (81*0.02) + (243*0.01) + (729*0.01) );
            ?>
            <div class="p-5 rounded-2xl bg-gray-900/80 border border-gray-800 hover:border-brand-500/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-brand-500/10 text-brand-400 border border-brand-500/20 uppercase">
                        Tier #<?= $pkg['sort_order'] ?>
                    </span>
                    <span class="text-xs text-gray-400 font-mono">Price</span>
                </div>

                <h3 class="text-lg font-bold text-white mb-1"><?= htmlspecialchars($pkg['name']) ?></h3>
                <div class="text-2xl font-black text-brand-400 mb-4"><?= formatCurrency($price) ?></div>

                <div class="space-y-2 text-xs border-t border-gray-800/80 pt-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Direct Bonus (10%):</span>
                        <span class="font-bold text-white font-mono"><?= formatCurrency($directComm) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Max Matrix Yield:</span>
                        <span class="font-bold text-emerald-400 font-mono"><?= formatCurrency($totalMatrixYield) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Spillover Type:</span>
                        <span class="font-semibold text-sky-400">BFS Automated</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Section 3: Core Mechanics & Rules -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-400 flex items-center justify-center mb-4 border border-brand-500/20">
            <i data-lucide="zap" class="w-5 h-5"></i>
        </div>
        <h3 class="text-base font-extrabold text-white mb-2">1. BFS Forced Spillover</h3>
        <p class="text-xs text-gray-400 leading-relaxed">
            The platform utilizes a company-wide Breadth-First Search (BFS) placement algorithm. Each node supports up to 3 direct children. Spillover members automatically fill open positions across the matrix left-to-right, level-by-level.
        </p>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4 border border-amber-500/20">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
        </div>
        <h3 class="text-base font-extrabold text-white mb-2">2. Auto-Upgrade Reserve Pool</h3>
        <p class="text-xs text-gray-400 leading-relaxed">
            25% of every earned commission is directed into the member's Reserve Pool. Upon accumulating sufficient reserve funds for the next tier price, the system automatically purchases and activates the higher tier package.
        </p>
    </div>

    <div class="glass-card rounded-3xl p-6 border border-gray-800">
        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4 border border-emerald-500/20">
            <i data-lucide="wallet" class="w-5 h-5"></i>
        </div>
        <h3 class="text-base font-extrabold text-white mb-2">3. Withdrawable Earnings & Payouts</h3>
        <p class="text-xs text-gray-400 leading-relaxed">
            75% of earned commissions flow directly into your Main Wallet Balance. Funds in your Main Wallet can be requested for withdrawal at any time with complete ledger transparency and rapid processing.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
