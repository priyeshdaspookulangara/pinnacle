<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="max-w-3xl mx-auto my-8">
    <div class="glass-card rounded-3xl p-8 sm:p-10 shadow-2xl relative overflow-hidden border border-gray-800">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-semibold mb-3">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> 2026 BFS 5-Matrix Engine
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Join Pinnacle Hunt</h1>
            <p class="text-sm text-gray-400 mt-2">Register your account and activate your package directly via USDT</p>
        </div>

        <?php if (!empty($sponsor)): ?>
            <div class="p-3.5 mb-6 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4"></i> You were invited by sponsor <span class="font-bold underline">@<?= htmlspecialchars($sponsor['username']) ?></span> (<?= htmlspecialchars($sponsor['full_name']) ?>)
            </div>
        <?php endif; ?>

        <form action="index.php?action=register" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <!-- Section 1: Member Credentials -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2 border-b border-gray-800 pb-2">
                    <i data-lucide="user" class="w-4 h-4 text-brand-400"></i> Account Information
                </h3>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Sponsor Username (Optional)</label>
                    <div class="relative">
                        <i data-lucide="user-plus" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500"></i>
                        <input type="text" name="sponsor" value="<?= htmlspecialchars($_GET['ref'] ?? ($sponsor['username'] ?? '')) ?>" placeholder="e.g. admin" class="w-full pl-11 pr-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Username *</label>
                        <input type="text" name="username" required placeholder="john_doe" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                        <input type="text" name="full_name" required placeholder="John Doe" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                        <input type="email" name="email" required placeholder="john@example.com" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" placeholder="+91 9876543210" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                        <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                    </div>
                </div>
            </div>

            <!-- Section 2: USDT Package Payment -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between border-b border-gray-800 pb-2">
                    <h3 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i> Package Activation via USDT
                    </h3>
                    <span class="text-xs text-brand-400 font-medium">1 USDT = ₹<?= number_format($usdtRate ?? 90, 2) ?></span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Select Investment Package</label>
                    <select name="package_id" id="regPackageSelect" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white focus:outline-none focus:border-brand-500 transition text-sm">
                        <?php foreach (($packages ?? []) as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>" data-price="<?= $pkg['price'] ?>" <?= $pkg['id'] == 1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pkg['name']) ?> (<?= formatCurrency($pkg['price']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Transfer Network</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-gray-900/60 border border-gray-700 cursor-pointer hover:border-brand-500 transition">
                            <input type="radio" name="network" value="TRC20" checked class="text-brand-500 focus:ring-brand-500 bg-gray-800 border-gray-700">
                            <div>
                                <p class="text-sm font-bold text-white">TRC20 (Tron)</p>
                                <p class="text-[11px] text-gray-400">Fast & Low Gas Fee</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-gray-900/60 border border-gray-700 cursor-pointer hover:border-brand-500 transition">
                            <input type="radio" name="network" value="BEP20" class="text-brand-500 focus:ring-brand-500 bg-gray-800 border-gray-700">
                            <div>
                                <p class="text-sm font-bold text-white">BEP20 (BNB Smart Chain)</p>
                                <p class="text-[11px] text-gray-400">Binance Smart Chain</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payable amount card -->
                <div class="p-4 rounded-2xl bg-gray-900/90 border border-gray-800 space-y-2 text-sm">
                    <div class="flex justify-between items-center text-gray-400">
                        <span>Package Price (INR):</span>
                        <span id="regPkgPrice" class="font-bold text-white">₹500.00</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-400">
                        <span>Conversion Rate:</span>
                        <span class="text-gray-300">1 USDT = ₹<span id="regUsdtRate"><?= number_format($usdtRate ?? 90, 2) ?></span></span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-800">
                        <span class="font-bold text-white">Payable USDT Amount:</span>
                        <span id="regUsdtAmount" class="text-lg font-black text-brand-400">5.56 USDT</span>
                    </div>
                </div>

                <!-- QR Code & Wallet Address Display -->
                <div class="p-4 rounded-2xl bg-gray-950 border border-gray-800 flex flex-col sm:flex-row items-center gap-5">
                    <div class="flex-shrink-0 text-center">
                        <div class="p-2 bg-white rounded-2xl inline-block shadow-md">
                            <img id="regQrImage" src="public/assets/images/usdt_bep20_qr_clean.png" alt="Deposit QR Code" class="w-32 h-32 object-contain rounded-xl">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5 font-semibold">Scan to Deposit</p>
                    </div>

                    <div class="flex-1 w-full space-y-2">
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">
                            Deposit Address (<span id="regNetworkLabel">TRC20</span>)
                        </label>
                        <div class="relative">
                            <input type="text" id="regWalletAddress" readonly value="<?= htmlspecialchars($trc20Address ?? '') ?>" class="w-full px-4 py-2.5 pr-20 bg-gray-900 border border-gray-800 rounded-2xl text-xs font-mono text-gray-300 select-all">
                            <button type="button" onclick="copyRegAddress()" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy
                            </button>
                        </div>
                        <p class="text-[11px] text-amber-400 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i> Only send USDT (<span id="regNetworkWarn">TRC20</span>) assets to this address. Other assets will be lost forever.
                        </p>
                    </div>
                </div>

                <!-- Blockchain Transaction Hash -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                        Transaction Hash (TXID / Reference)
                    </label>
                    <input type="text" name="txid" placeholder="Paste your blockchain transaction hash (or leave blank to pay after registration)" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm font-mono">
                    <p class="text-[11px] text-gray-400 mt-1">If provided now, your activation will be queued immediately for admin verification upon registration.</p>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold rounded-2xl shadow-lg shadow-brand-500/20 transition duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Register & Submit USDT Activation <i data-lucide="check-circle" class="w-5 h-5"></i>
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-400">
            Already registered? <a href="index.php?page=login" class="text-brand-400 font-semibold hover:underline">Sign In</a>
        </div>
    </div>
</div>

<script>
const regTrc20 = "<?= htmlspecialchars($trc20Address ?? '') ?>";
const regBep20 = "<?= htmlspecialchars($bep20Address ?? '') ?>";
const regTrc20Qr = "<?= htmlspecialchars($trc20Qr ?? 'public/assets/images/usdt_bep20_qr_clean.png') ?>";
const regBep20Qr = "<?= htmlspecialchars($bep20Qr ?? 'public/assets/images/usdt_bep20_qr_clean.png') ?>";
const regRate = <?= (float)($usdtRate ?? 90) ?>;

function updateRegCalculation() {
    const select = document.getElementById('regPackageSelect');
    const selectedOption = select.options[select.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price')) || 500;

    document.getElementById('regPkgPrice').innerText = '₹' + price.toLocaleString('en-IN', {minimumFractionDigits: 2});

    const usdt = (price / regRate).toFixed(2);
    document.getElementById('regUsdtAmount').innerText = usdt + ' USDT';
}

function updateRegNetwork() {
    const network = document.querySelector('input[name="network"]:checked').value;
    const addressInput = document.getElementById('regWalletAddress');
    const label = document.getElementById('regNetworkLabel');

    label.innerText = network;
    const warn = document.getElementById('regNetworkWarn');
    if (warn) warn.innerText = network;
    const qrImg = document.getElementById('regQrImage');
    if (network === 'TRC20') {
        addressInput.value = regTrc20;
        if (qrImg) qrImg.src = regTrc20Qr;
    } else {
        addressInput.value = regBep20;
        if (qrImg) qrImg.src = regBep20Qr;
    }
}

function copyRegAddress() {
    const input = document.getElementById('regWalletAddress');
    navigator.clipboard.writeText(input.value);
    alert('Deposit address copied to clipboard!');
}

document.getElementById('regPackageSelect').addEventListener('change', updateRegCalculation);
document.querySelectorAll('input[name="network"]').forEach(el => {
    el.addEventListener('change', updateRegNetwork);
});

// Run once on load
updateRegCalculation();
updateRegNetwork();
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
