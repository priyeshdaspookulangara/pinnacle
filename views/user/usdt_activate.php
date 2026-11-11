<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="shield-check" class="w-8 h-8 text-brand-400"></i> USDT Package & Account Activation
        </h1>
        <p class="text-xs text-gray-400 mt-1">Activate packages using USDT (TRC-20 or BEP-20) with automated INR conversion and instant admin ledger verification.</p>
    </div>
    <div>
        <a href="index.php?page=dashboard" class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 hover:border-gray-700 text-xs text-gray-300 font-semibold transition flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <!-- Payment Instructions & Form -->
    <div class="lg:col-span-7 space-y-6">
        <div class="glass-card rounded-3xl p-6 border border-brand-500/30 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl -z-10 pointer-events-none"></div>

            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-brand-500/10 flex items-center justify-center text-brand-400 border border-brand-500/20">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Deposit USDT for Package Activation</h2>
                    <p class="text-xs text-gray-400">1 USDT = ₹<?= number_format($usdtConfig['inr_rate'], 2) ?> INR</p>
                </div>
            </div>

            <form action="index.php?action=submit_usdt_payment" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Package Selection -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Select Investment Package</label>
                    <select name="package_id" id="pkgSelect" onchange="calculateUsdt()" required class="w-full px-4 py-3 bg-gray-900/90 border border-gray-700 rounded-2xl text-white text-sm focus:border-brand-500 focus:outline-none">
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>" data-price="<?= $pkg['price'] ?>">
                                <?= htmlspecialchars($pkg['name']) ?> (<?= formatCurrency($pkg['price']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Network Selection -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Select Transfer Network</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 bg-gray-900/80 border border-gray-700 rounded-2xl cursor-pointer hover:border-brand-500 transition">
                            <input type="radio" name="network" value="TRC20" checked onchange="switchNetwork('TRC20')" class="text-brand-500 focus:ring-0">
                            <div>
                                <span class="text-xs font-bold text-white block">TRC20 (Tron)</span>
                                <span class="text-[10px] text-gray-400">Fast & Low Gas Fee</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-gray-900/80 border border-gray-700 rounded-2xl cursor-pointer hover:border-brand-500 transition">
                            <input type="radio" name="network" value="BEP20" onchange="switchNetwork('BEP20')" class="text-brand-500 focus:ring-0">
                            <div>
                                <span class="text-xs font-bold text-white block">BEP20 (BNB Smart Chain)</span>
                                <span class="text-[10px] text-gray-400">Binance Smart Chain</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Calculation Card -->
                <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800 space-y-2">
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Package Price (INR):</span>
                        <span id="displayInr" class="text-white font-mono font-bold">₹600.00</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Conversion Rate:</span>
                        <span class="text-white font-mono">1 USDT = ₹<?= number_format($usdtConfig['inr_rate'], 2) ?></span>
                    </div>
                    <div class="border-t border-gray-800 pt-2 flex justify-between text-sm font-bold">
                        <span class="text-brand-400">Payable Amount (USDT):</span>
                        <span id="displayUsdt" class="text-emerald-400 font-mono text-base">6.40 USDT</span>
                    </div>
                </div>

                <!-- Deposit Address Box -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Deposit Wallet Address (<span id="networkLabel">TRC20</span>)</label>
                    <div class="flex items-center gap-2 bg-gray-950 p-2.5 rounded-2xl border border-gray-800">
                        <input type="text" id="depositAddress" readonly value="<?= htmlspecialchars($usdtConfig['trc20_address']) ?>" class="w-full bg-transparent text-xs font-mono text-gray-300 focus:outline-none">
                        <button type="button" onclick="copyAddress()" class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-brand-400 hover:text-white text-xs font-bold transition flex items-center gap-1">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy
                        </button>
                    </div>
                    <p class="text-[10px] text-amber-400 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Send only USDT over the selected network. Other assets will be lost.
                    </p>
                </div>

                <!-- Transaction Hash (TXID) Input -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Transaction Hash (TXID / Hash)</label>
                    <input type="text" name="txid" required placeholder="Paste your blockchain transaction hash here..." class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-2xl text-white text-xs font-mono focus:border-brand-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-extrabold text-sm transition shadow-lg shadow-brand-500/20 flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Submit USDT Payment Proof
                </button>
            </form>
        </div>
    </div>

    <!-- Payment History -->
    <div class="lg:col-span-5 space-y-6">
        <div class="glass-card rounded-3xl p-6 border border-gray-800 space-y-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-gray-400"></i> My USDT Payment History
            </h3>

            <div class="space-y-3 font-mono">
                <?php if (empty($userPayments)): ?>
                    <p class="text-xs text-gray-500 font-sans py-4 text-center">No USDT payment submissions yet.</p>
                <?php else: ?>
                    <?php foreach ($userPayments as $pay): ?>
                        <div class="p-3.5 rounded-2xl bg-gray-900/60 border border-gray-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white font-sans"><?= htmlspecialchars($pay['package_name']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-sans <?= $pay['status'] === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($pay['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') ?>">
                                    <?= ucfirst($pay['status']) ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400">
                                <span>Amount: <b class="text-emerald-400"><?= $pay['amount_usdt'] ?> USDT</b> (₹<?= number_format($pay['amount_inr'], 2) ?>)</span>
                                <span class="text-gray-500 text-[10px]"><?= $pay['network'] ?></span>
                            </div>
                            <div class="text-[10px] text-gray-500 truncate" title="<?= htmlspecialchars($pay['txid']) ?>">
                                TX: <?= htmlspecialchars($pay['txid']) ?>
                            </div>
                            <div class="text-[9px] text-gray-600 font-sans">
                                Submitted: <?= $pay['created_at'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const trc20Addr = <?= json_encode($usdtConfig['trc20_address']) ?>;
const bep20Addr = <?= json_encode($usdtConfig['bep20_address']) ?>;
const inrRate = <?= (float)$usdtConfig['inr_rate'] ?>;

function switchNetwork(net) {
    const input = document.getElementById('depositAddress');
    const label = document.getElementById('networkLabel');
    if (net === 'BEP20') {
        input.value = bep20Addr;
        label.innerText = 'BEP20';
    } else {
        input.value = trc20Addr;
        label.innerText = 'TRC20';
    }
}

function calculateUsdt() {
    const select = document.getElementById('pkgSelect');
    const price = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 600;
    const usdt = (price / (inrRate > 0 ? inrRate : 93.76)).toFixed(2);
    document.getElementById('displayInr').innerText = '₹' + price.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('displayUsdt').innerText = usdt + ' USDT';
}

function copyAddress() {
    const addr = document.getElementById('depositAddress').value;
    navigator.clipboard.writeText(addr).then(() => {
        alert('Deposit address copied to clipboard!');
    });
}

document.addEventListener('DOMContentLoaded', calculateUsdt);
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
