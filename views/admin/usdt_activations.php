<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="shield-check" class="w-8 h-8 text-brand-400"></i> USDT Package Activations Management
        </h1>
        <p class="text-xs text-gray-400 mt-1">Verify blockchain TXIDs, activate user packages, and automate 5-matrix BFS placement and commission distribution.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="index.php?page=admin_settings" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-xs text-gray-300 font-semibold transition flex items-center gap-2">
            <i data-lucide="settings" class="w-4 h-4"></i> Edit USDT Settings
        </a>
    </div>
</div>

<!-- Config Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="glass-card rounded-2xl p-4 border border-gray-800">
        <div class="text-[10px] text-gray-400 uppercase font-semibold">Active Exchange Rate</div>
        <div class="text-lg font-extrabold text-emerald-400 mt-1 font-mono">1 USDT = ₹<?= number_format($usdtConfig['inr_rate'], 2) ?></div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-gray-800">
        <div class="text-[10px] text-gray-400 uppercase font-semibold">TRC20 Wallet Address</div>
        <div class="text-xs text-gray-300 mt-1 font-mono truncate" title="<?= htmlspecialchars($usdtConfig['trc20_address']) ?>">
            <?= htmlspecialchars($usdtConfig['trc20_address']) ?>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 border border-gray-800">
        <div class="text-[10px] text-gray-400 uppercase font-semibold">BEP20 Wallet Address</div>
        <div class="text-xs text-gray-300 mt-1 font-mono truncate" title="<?= htmlspecialchars($usdtConfig['bep20_address']) ?>">
            <?= htmlspecialchars($usdtConfig['bep20_address']) ?>
        </div>
    </div>
</div>

<!-- Activations Table -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-3 py-3">ID</th>
                    <th class="px-3 py-3">Member</th>
                    <th class="px-3 py-3">Package Tier</th>
                    <th class="px-3 py-3">Amount (USDT / INR)</th>
                    <th class="px-3 py-3">Network & Address</th>
                    <th class="px-3 py-3">TXID / Hash</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 font-sans">No USDT activation requests found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-3 py-3 text-gray-400">#<?= $pay['id'] ?></td>
                            <td class="px-3 py-3 font-sans">
                                <div class="font-bold text-white">@<?= htmlspecialchars($pay['username']) ?></div>
                                <div class="text-[10px] text-gray-400"><?= htmlspecialchars($pay['full_name']) ?></div>
                            </td>
                            <td class="px-3 py-3 font-sans text-brand-400 font-semibold">
                                <?= htmlspecialchars($pay['package_name']) ?>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-bold text-emerald-400"><?= $pay['amount_usdt'] ?> USDT</div>
                                <div class="text-[10px] text-gray-500">₹<?= number_format($pay['amount_inr'], 2) ?></div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-800 text-gray-300 font-bold"><?= $pay['network'] ?></span>
                                <div class="text-[9px] text-gray-500 truncate max-w-[120px]" title="<?= htmlspecialchars($pay['wallet_address']) ?>">
                                    <?= htmlspecialchars($pay['wallet_address']) ?>
                                </div>
                            </td>
                            <td class="px-3 py-3 font-mono">
                                <a href="https://tronscan.org/#/transaction/<?= urlencode($pay['txid']) ?>" target="_blank" class="text-blue-400 hover:underline truncate block max-w-[140px]" title="<?= htmlspecialchars($pay['txid']) ?>">
                                    <?= htmlspecialchars($pay['txid']) ?>
                                </a>
                            </td>
                            <td class="px-3 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $pay['status'] === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($pay['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') ?>">
                                    <?= ucfirst($pay['status']) ?>
                                </span>
                            </td>
                            <td class="px-3 py-3 font-sans text-center">
                                <?php if ($pay['status'] === 'pending'): ?>
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form action="index.php?action=process_usdt_activation" method="POST" onsubmit="return confirm('Approve USDT payment and activate member package into BFS matrix?');">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                                            <input type="hidden" name="decision" value="approve">
                                            <button type="submit" class="px-2.5 py-1 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-black text-xs font-bold transition flex items-center gap-1 shadow-md shadow-emerald-500/20">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
                                            </button>
                                        </form>

                                        <form action="index.php?action=process_usdt_activation" method="POST" onsubmit="return confirm('Reject this USDT payment submission?');">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                                            <input type="hidden" name="decision" value="reject">
                                            <button type="submit" class="px-2.5 py-1 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-md shadow-red-600/20">
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-500 italic"><?= ucfirst($pay['status']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
