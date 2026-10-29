<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="max-w-lg mx-auto my-8">
    <div class="glass-card rounded-3xl p-8 shadow-2xl relative overflow-hidden border border-gray-800">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Join Pinnacle Hunt</h1>
            <p class="text-sm text-gray-400 mt-2">Create your account and enter the 3-Matrix BFS spillover system</p>
        </div>

        <?php if (!empty($sponsor)): ?>
            <div class="p-3 mb-6 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4"></i> You were invited by sponsor <span class="font-bold underline">@<?= htmlspecialchars($sponsor['username']) ?></span> (<?= htmlspecialchars($sponsor['full_name']) ?>)
            </div>
        <?php endif; ?>

        <form action="index.php?action=register" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Sponsor Username (Optional)</label>
                <div class="relative">
                    <i data-lucide="user-plus" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="sponsor" value="<?= htmlspecialchars($_GET['ref'] ?? ($sponsor['username'] ?? '')) ?>" placeholder="e.g. admin" class="w-full pl-11 pr-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" name="username" required placeholder="john_doe" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Full Name</label>
                    <input type="text" name="full_name" required placeholder="John Doe" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" placeholder="+91 9876543210" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full px-4 py-2.5 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 transition text-sm">
                </div>
            </div>

            <button type="submit" class="w-full mt-2 py-3.5 px-4 bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold rounded-2xl shadow-lg shadow-brand-500/20 transition duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Create Account & Join <i data-lucide="check" class="w-5 h-5"></i>
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-400">
            Already registered? <a href="/index.php?page=login" class="text-brand-400 font-semibold hover:underline">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
