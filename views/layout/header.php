<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinnacle Hunt - High-Performance BFS Matrix Platform</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        },
                        darkbg: '#0b0f19',
                        darkcard: 'rgba(17, 24, 39, 0.75)',
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #0b0f19;
            color: #f3f4f6;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-glow {
            box-shadow: 0 0 25px -5px rgba(34, 197, 94, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-black">

<?php if (isLoggedIn()): $user = currentUser(); ?>
<!-- Desktop Sidebar (Aside) -->
<aside class="w-64 fixed inset-y-0 left-0 z-40 glass-card border-r border-gray-800/80 hidden md:flex flex-col justify-between p-4">
    <div class="space-y-6">
        <!-- Brand Logo -->
        <a href="index.php" class="flex items-center gap-3 px-2 py-1 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center font-extrabold text-black text-xl shadow-lg group-hover:scale-105 transition-transform">
                P
            </div>
            <span class="font-extrabold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-gray-200 to-brand-400">
                PINNACLE<span class="text-brand-500">HUNT</span>
            </span>
        </a>

        <!-- Logged In User Profile Box -->
        <div class="p-3.5 rounded-2xl bg-gray-900/80 border border-gray-800/80 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-400 flex items-center justify-center font-extrabold text-sm">
                <?= strtoupper(substr($user['username'] ?? 'U', 0, 2)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
                <div class="text-[10px] text-brand-400 font-mono truncate">@<?= htmlspecialchars($user['username'] ?? '') ?> (<?= strtoupper($user['role'] ?? 'USER') ?>)</div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5">
            <span class="px-3 text-[10px] font-extrabold text-gray-500 uppercase tracking-wider">Main Navigation</span>

            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="index.php?page=admin_dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'admin_dashboard' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Dashboard
                </a>
                <a href="index.php?page=admin_matrix_tree" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'admin_matrix_tree' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="git-fork" class="w-4 h-4"></i> Matrix Tree View
                </a>
                <a href="index.php?page=admin_settings" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'admin_settings' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="sliders" class="w-4 h-4"></i> Commission Rules
                </a>
                <a href="index.php?page=admin_members" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'admin_members' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="users" class="w-4 h-4"></i> All Members
                </a>
            <?php else: ?>
                <a href="index.php?page=dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'dashboard' || empty($_GET['page']) ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                </a>
                <a href="index.php?page=referrals" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'referrals' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="users-round" class="w-4 h-4"></i> My Referrals
                </a>
                <a href="index.php?page=genealogy" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'genealogy' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="git-fork" class="w-4 h-4"></i> Matrix Downline
                </a>
                <a href="index.php?page=ledger" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'ledger' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="wallet" class="w-4 h-4"></i> Ledger & Payouts
                </a>
                <a href="index.php?page=business_plan" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold transition hover:bg-gray-800/80 hover:text-white <?= ($_GET['page'] ?? '') === 'business_plan' ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-gray-300' ?>">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Business Plan
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Bottom Controls -->
    <div class="space-y-3 pt-4 border-t border-gray-800/80">
        <?php
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
            $asideRefUrl = "{$protocol}://{$host}/index.php?page=register&ref=" . urlencode($user['username'] ?? '');
        ?>
        <button id="asideShareBtn" onclick="copyToClipboard('<?= htmlspecialchars($asideRefUrl) ?>', 'asideShareBtn')" class="w-full py-2.5 px-3 rounded-2xl text-xs font-bold bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 transition flex items-center justify-center gap-2">
            <i data-lucide="share-2" class="w-4 h-4"></i> Copy Joining Link
        </button>

        <a href="index.php?action=logout" class="w-full py-2.5 px-3 rounded-2xl text-xs font-semibold bg-gray-900 hover:bg-red-500/20 text-gray-300 hover:text-red-400 border border-gray-800 hover:border-red-500/30 transition flex items-center justify-center gap-2">
            <i data-lucide="log-out" class="w-4 h-4"></i> Logout Account
        </a>
    </div>
</aside>

<!-- Main Outer Wrapper offset by Sidebar on desktop -->
<div class="md:ml-64 min-h-screen flex flex-col flex-1">

    <!-- Top Bar Header for Mobile & Desktop -->
    <header class="sticky top-0 z-30 glass-card border-b border-gray-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <!-- Mobile Drawer Toggle Button -->
                    <button onclick="document.getElementById('mobileSidebar').classList.toggle('hidden')" class="md:hidden p-2 rounded-xl bg-gray-800 text-gray-200">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                        <span class="text-xs font-mono text-gray-400 uppercase tracking-wider">Pinnacle Hunt Portal</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-mono text-xs border border-emerald-500/20">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> BFS 3-Matrix Active
                    </span>
                    <button id="topNavShareBtn" onclick="copyToClipboard('<?= htmlspecialchars($asideRefUrl) ?>', 'topNavShareBtn')" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-brand-500 hover:bg-brand-600 text-black shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Joining Link
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Overlay Sidebar -->
    <div id="mobileSidebar" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden md:hidden">
        <div class="w-72 h-full glass-card p-6 flex flex-col justify-between border-r border-gray-800">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <a href="index.php" class="font-extrabold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-brand-400">
                        PINNACLE<span class="text-brand-500">HUNT</span>
                    </a>
                    <button onclick="document.getElementById('mobileSidebar').classList.add('hidden')" class="text-gray-400 hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <nav class="space-y-2">
                    <?php if ($user && $user['role'] === 'admin'): ?>
                        <a href="index.php?page=admin_dashboard" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Admin Dashboard</a>
                    <a href="index.php?page=admin_matrix_tree" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="git-fork" class="w-5 h-5"></i> Matrix Tree View</a>
                        <a href="index.php?page=admin_settings" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="sliders" class="w-5 h-5"></i> Commission Rules</a>
                        <a href="index.php?page=admin_members" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="users" class="w-5 h-5"></i> All Members</a>
                    <?php else: ?>
                        <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
                        <a href="index.php?page=referrals" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="users-round" class="w-5 h-5"></i> My Referrals</a>
                        <a href="index.php?page=genealogy" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="git-fork" class="w-5 h-5"></i> Matrix Downline</a>
                        <a href="index.php?page=ledger" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="wallet" class="w-5 h-5"></i> Ledger & Payouts</a>
                        <a href="index.php?page=business_plan" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold text-gray-200 bg-gray-800/80"><i data-lucide="file-text" class="w-5 h-5"></i> Business Plan</a>
                    <?php endif; ?>
                </nav>
            </div>

            <a href="index.php?action=logout" class="w-full py-3 px-4 rounded-2xl text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 flex items-center justify-center gap-2">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </a>
        </div>
    </div>

<?php else: ?>
<!-- Standard Navigation Bar for Guest / Login / Register pages -->
<nav class="sticky top-0 z-50 glass-card border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center font-extrabold text-black text-xl shadow-lg group-hover:scale-105 transition-transform">
                    P
                </div>
                <span class="font-extrabold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-gray-200 to-brand-400">
                    PINNACLE<span class="text-brand-500">HUNT</span>
                </span>
            </a>

            <div class="flex items-center gap-3">
                <a href="index.php?page=login" class="px-4 py-2 text-sm font-semibold text-gray-300 hover:text-white transition">Sign In</a>
                <a href="index.php?page=register" class="px-4 py-2 text-sm font-bold bg-brand-500 hover:bg-brand-600 text-black rounded-xl transition shadow-md shadow-brand-500/20">Get Started</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- System Flash Notifications -->
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="p-4 mb-4 rounded-2xl bg-brand-500/10 border border-brand-500/30 text-brand-400 flex items-center justify-between text-sm glass-glow">
            <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-5 h-5"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-white">&times;</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="p-4 mb-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center justify-between text-sm">
            <div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-5 h-5"></i> <?= htmlspecialchars($_SESSION['error']) ?></div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-white">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
