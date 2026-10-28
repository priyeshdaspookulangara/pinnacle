<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="wallet" class="w-8 h-8 text-brand-400"></i> Financial Ledger & Payouts
        </h1>
        <p class="text-xs text-gray-400 mt-1">Audit log of commission credits, reserve holds, upgrades, and withdrawal requests</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Withdrawal Request Form -->
    <div class="lg:col-span-1">
        <div class="glass-card rounded-3xl p-6 border border-gray-800 relative overflow-hidden">
            <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                <i data-lucide="arrow-up-right" class="w-5 h-5 text-brand-400"></i> Request Payout
            </h3>
            <p class="text-xs text-gray-400 mb-6">Withdraw earnings from your withdrawable balance pool.</p>

            <div class="p-4 mb-6 rounded-2xl bg-gray-900/80 border border-gray-800 text-xs font-mono space-y-1.5">
                <div class="flex justify-between text-gray-400"><span>Withdrawable Balance:</span> <strong class="text-emerald-400"><?= formatCurrency($user['wallet_balance']) ?></strong></div>
                <div class="flex justify-between text-gray-400"><span>Reserve Pool Hold:</span> <strong class="text-amber-400"><?= formatCurrency($user['reserve_balance']) ?></strong></div>
            </div>

            <form action="/index.php?action=request_withdrawal" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Amount (INR ₹)</label>
                    <input type="number" step="0.01" name="amount" required max="<?= $user['wallet_balance'] ?>" min="100" placeholder="e.g. 500.00" class="w-full px-4 py-2.5 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Payout Details (UPI / Bank Account)</label>
                    <textarea name="payout_details" required rows="3" placeholder="UPI ID: user@upi OR Bank A/C: 12345678, IFSC: SBIN000123" class="w-full px-4 py-2.5 bg-gray-900/90 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-xs"></textarea>
                </div>

                <button type="submit" <?= (float)$user['wallet_balance'] < 100 ? 'disabled' : '' ?> class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 disabled:opacity-50 text-black font-extrabold text-xs transition shadow-md shadow-brand-500/20 flex items-center justify-center gap-1.5">
                    Submit Withdrawal Request <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Withdrawal History -->
    <div class="lg:col-span-2">
        <div class="glass-card rounded-3xl p-6 border border-gray-800">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-brand-400"></i> Payout Request Status
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-300">
                    <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Payout Details</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Requested At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60 font-mono">
                        <?php if (empty($withdrawals)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 font-sans">No withdrawal requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($withdrawals as $w): ?>
                                <tr class="hover:bg-gray-800/30 transition">
                                    <td class="px-4 py-3 text-gray-400">#<?= $w['id'] ?></td>
                                    <td class="px-4 py-3 font-bold text-white"><?= formatCurrency($w['amount']) ?></td>
                                    <td class="px-4 py-3 text-gray-300 font-sans text-[11px] max-w-[200px] truncate" title="<?= htmlspecialchars($w['payout_details']) ?>"><?= htmlspecialchars($w['payout_details']) ?></td>
                                    <td class="px-4 py-3 font-sans">
                                        <?php if ($w['status'] === 'approved'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Approved</span>
                                        <?php elseif ($w['status'] === 'rejected'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">Rejected & Refunded</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Pending Review</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 font-sans text-[11px]"><?= $w['created_at'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Transactions Table -->
<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i data-lucide="file-text" class="w-5 h-5 text-brand-400"></i> Transaction Audit Log
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Wallet After</th>
                    <th class="px-4 py-3">Reserve After</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($ledgerEntries)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 font-sans">No transactions recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ledgerEntries as $entry): ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-3 text-gray-400">#<?= $entry['id'] ?></td>
                            <td class="px-4 py-3 font-sans">
                                <?php if ($entry['type'] === 'credit'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400">Credit</span>
                                <?php elseif ($entry['type'] === 'debit'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400">Debit</span>
                                <?php elseif ($entry['type'] === 'upgrade_payment'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-400">Auto Upgrade</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400">Hold</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-bold text-white"><?= formatCurrency($entry['amount']) ?></td>
                            <td class="px-4 py-3 text-emerald-400"><?= formatCurrency($entry['balance_after']) ?></td>
                            <td class="px-4 py-3 text-amber-400"><?= formatCurrency($entry['reserve_after']) ?></td>
                            <td class="px-4 py-3 text-gray-300 font-sans text-[11px] max-w-xs truncate" title="<?= htmlspecialchars($entry['description']) ?>"><?= htmlspecialchars($entry['description']) ?></td>
                            <td class="px-4 py-3 text-gray-500 font-sans text-[11px]"><?= $entry['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
