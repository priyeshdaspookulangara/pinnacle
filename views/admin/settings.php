<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="sliders" class="w-8 h-8 text-brand-400"></i> Commission & Auto-Progression Configuration
        </h1>
        <p class="text-xs text-gray-400 mt-1">Dynamic rule builder for reserve pool percentage, 7-level matrix rates, and package pricing</p>
    </div>
</div>

<form action="index.php?action=update_settings" method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Direct Sponsor & Auto-Progression Rules -->
        <div class="glass-card rounded-3xl p-6 border border-gray-800 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-400"></i> Reserve Pool & Progression Parameters
                </h3>
                <p class="text-xs text-gray-400">Configure reserve pool holds and milestone triggers for package auto-upgrades.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Reserve Pool Deduction Rate (%)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="reserve_percentage" value="<?= htmlspecialchars($settings['reserve_percentage'] ?? '25.00') ?>" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Percentage of all commission payouts automatically directed to the member's reserve upgrade pool.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Auto-Upgrade Milestone Level Trigger</label>
                <input type="number" name="auto_upgrade_milestone_level" value="<?= htmlspecialchars($settings['auto_upgrade_milestone_level'] ?? '3') ?>" min="1" max="7" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1">Matrix depth completion level that triggers automated progression verification.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Global Direct Sponsor Bonus (%)</label>
                <input type="number" step="0.01" name="direct_sponsor_commission_percent" value="<?= htmlspecialchars($settings['direct_sponsor_commission_percent'] ?? '20.00') ?>" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1">Default percentage rewarded to the direct sponsor upon package purchase.</p>
            </div>
        </div>

        <!-- 7-Level Matrix Commission Percentages -->
        <div class="glass-card rounded-3xl p-6 border border-gray-800 space-y-4">
            <div>
                <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-brand-400"></i> Matrix Level Commission Rates (Levels 1 to 7)
                </h3>
                <p class="text-xs text-gray-400">Tiered payout percentages awarded to matrix uplines across 7 levels.</p>
            </div>

            <?php for ($lvl = 1; $lvl <= 7; $lvl++): ?>
                <div class="flex items-center gap-4">
                    <span class="w-24 text-xs font-bold text-gray-300">Level <?= $lvl ?>:</span>
                    <input type="number" step="0.01" name="matrix_level_<?= $lvl ?>_percent" value="<?= htmlspecialchars($settings["matrix_level_{$lvl}_percent"] ?? '1.00') ?>" required class="w-full px-4 py-2.5 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                    <span class="text-xs text-gray-400 font-mono">%</span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Package Tiers & Prices Configuration -->
    <div class="glass-card rounded-3xl p-6 mb-8 border border-gray-800">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="package" class="w-5 h-5 text-brand-400"></i> Investment Packages & Pricing Tiers
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Package Name</th>
                        <th class="px-4 py-3">Price (INR ₹)</th>
                        <th class="px-4 py-3">Direct Sponsor %</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 font-mono">
                    <?php foreach ($packages as $pkg): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3 font-bold text-gray-400">Tier #<?= $pkg['sort_order'] ?></td>
                            <td class="px-4 py-3">
                                <input type="text" name="packages[<?= $pkg['id'] ?>][name]" value="<?= htmlspecialchars($pkg['name']) ?>" required class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-xl text-white font-sans text-xs w-full max-w-xs focus:outline-none focus:border-brand-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" name="packages[<?= $pkg['id'] ?>][price]" value="<?= $pkg['price'] ?>" required class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-xl text-emerald-400 font-bold text-xs w-36 focus:outline-none focus:border-brand-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" step="0.01" name="packages[<?= $pkg['id'] ?>][direct_commission_percent]" value="<?= $pkg['direct_commission_percent'] ?>" required class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs w-28 focus:outline-none focus:border-brand-500"> %
                            </td>
                            <td class="px-4 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end">
        <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold text-sm transition shadow-xl shadow-brand-500/20 flex items-center gap-2">
            <i data-lucide="save" class="w-5 h-5"></i> Save Settings & Rule Changes
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
