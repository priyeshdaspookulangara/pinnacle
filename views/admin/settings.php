<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="sliders" class="w-8 h-8 text-brand-400"></i> Commission & Auto-Progression Configuration
        </h1>
        <p class="text-xs text-gray-400 mt-1">Dynamic rule builder for reserve pool percentage, 8-level matrix rates, and package pricing</p>
    </div>
</div>

<form action="index.php?action=update_settings" method="POST" enctype="multipart/form-data">
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
                <p class="text-[11px] text-gray-400 mt-1">Percentage of matrix level payouts automatically directed to the member's reserve upgrade pool (referral income has 0% cutting).</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Auto-Upgrade Milestone Level Trigger</label>
                <input type="number" name="auto_upgrade_milestone_level" value="<?= htmlspecialchars($settings['auto_upgrade_milestone_level'] ?? '3') ?>" min="1" max="8" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1">Matrix depth completion level that triggers automated progression verification.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Global Direct Sponsor Bonus (%)</label>
                <input type="number" step="0.01" name="direct_sponsor_commission_percent" value="<?= htmlspecialchars($settings['direct_sponsor_commission_percent'] ?? '20.00') ?>" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white font-mono text-sm focus:outline-none focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1">Default percentage rewarded to direct sponsors (100% credited to wallet, zero cutting/reserve deduction).</p>
            </div>
        </div>

        <!-- 7-Level Matrix Commission Percentages -->
        <div class="glass-card rounded-3xl p-6 border border-gray-800 space-y-4">
            <div>
                <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-brand-400"></i> Matrix Level Commission Rates (Levels 1 to 8)
                </h3>
                <p class="text-xs text-gray-400">Tiered payout percentages awarded to matrix uplines across 8 levels.</p>
            </div>

            <?php for ($lvl = 1; $lvl <= 8; $lvl++): ?>
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

        <!-- USDT Payment Gateways & Conversion Settings -->
        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i> USDT Payment Gateway & Conversion Rules
            </h3>
            <p class="text-xs text-gray-400">Configure corporate crypto addresses and exchange rates for automated member USDT activation.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">USDT Conversion Rate (₹ per 1 USDT)</label>
                    <input type="number" step="0.01" name="usdt_inr_rate" value="<?= htmlspecialchars($settings['usdt_inr_rate'] ?? '90.00') ?>" required class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-emerald-400 font-mono text-sm focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">TRC20 (Tron) Deposit Address</label>
                    <input type="text" name="usdt_trc20_address" value="<?= htmlspecialchars($settings['usdt_trc20_address'] ?? '') ?>" required class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-gray-200 font-mono text-xs focus:border-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">BEP20 (BSC) Deposit Address</label>
                    <input type="text" name="usdt_bep20_address" value="<?= htmlspecialchars($settings['usdt_bep20_address'] ?? '') ?>" required class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-gray-200 font-mono text-xs focus:border-brand-500 focus:outline-none">
                </div>
            </div>

            <!-- Corporate QR Code Uploads -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-800">
                <div class="p-4 bg-gray-900/60 rounded-2xl border border-gray-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-gray-300">BEP20 QR Code (BNB Smart Chain)</label>
                        <span class="text-[10px] text-brand-400 font-mono">PNG / JPG / WEBP</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <img src="<?= htmlspecialchars($settings['usdt_bep20_qr'] ?? 'public/assets/images/usdt_bep20_qr_clean.png') ?>" alt="BEP20 QR" class="w-20 h-20 object-contain rounded-xl bg-white p-1.5 shadow">
                        <div class="flex-1">
                            <input type="file" name="usdt_bep20_qr_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-800 file:text-brand-400 hover:file:bg-gray-700 cursor-pointer">
                            <p class="text-[10px] text-gray-500 mt-1">Displayed to users during registration & USDT package activation</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-900/60 rounded-2xl border border-gray-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-gray-300">TRC20 QR Code (Tron Network)</label>
                        <span class="text-[10px] text-brand-400 font-mono">PNG / JPG / WEBP</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <img src="<?= htmlspecialchars($settings['usdt_trc20_qr'] ?? 'public/assets/images/usdt_bep20_qr_clean.png') ?>" alt="TRC20 QR" class="w-20 h-20 object-contain rounded-xl bg-white p-1.5 shadow">
                        <div class="flex-1">
                            <input type="file" name="usdt_trc20_qr_file" accept="image/*" class="w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-800 file:text-brand-400 hover:file:bg-gray-700 cursor-pointer">
                            <p class="text-[10px] text-gray-500 mt-1">Displayed when users switch payment network to TRC20</p>
                        </div>
                    </div>
                </div>
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
