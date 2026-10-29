<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="max-w-md mx-auto my-12">
    <div class="glass-card rounded-3xl p-8 shadow-2xl relative overflow-hidden border border-gray-800">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Welcome Back</h1>
            <p class="text-sm text-gray-400 mt-2">Sign in to manage your Pinnacle Hunt matrix & earnings</p>
        </div>

        <form action="index.php?action=login" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Username or Email</label>
                <div class="relative">
                    <i data-lucide="user" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="login" required placeholder="Enter username or email" class="w-full pl-11 pr-4 py-3 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 bg-gray-900/80 border border-gray-700/80 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition text-sm">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-500 to-emerald-400 hover:from-brand-600 hover:to-emerald-500 text-black font-extrabold rounded-2xl shadow-lg shadow-brand-500/20 transition duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Sign In to Dashboard <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-gray-400">
            Don't have an account? <a href="/index.php?page=register" class="text-brand-400 font-semibold hover:underline">Register Now</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
