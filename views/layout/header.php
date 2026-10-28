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
            background: rgba(17, 24, 39, 0.65);
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

<!-- Navigation Bar -->
<nav class="sticky top-0 z-50 glass-card border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <a href="/index.php" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center font-extrabold text-black text-xl shadow-lg group-hover:scale-105 transition-transform">
                        P
                    </div>
                    <span class="font-extrabold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-gray-200 to-brand-400">
                        PINNACLE<span class="text-brand-500">HUNT</span>
                    </span>
                </a>
            </div>

            <?php if (isLoggedIn()): $user = currentUser(); ?>
            <div class="hidden md:flex items-center gap-6">
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/index.php?page=admin_dashboard" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Dashboard</a>
                    <a href="/index.php?page=admin_settings" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="sliders" class="w-4 h-4"></i> Commission Rules</a>
                    <a href="/index.php?page=admin_members" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> All Members</a>
                <?php else: ?>
                    <a href="/index.php?page=dashboard" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard</a>
                    <a href="/index.php?page=genealogy" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="git-fork" class="w-4 h-4"></i> Matrix Downline</a>
                    <a href="/index.php?page=ledger" class="text-sm font-medium hover:text-brand-400 transition flex items-center gap-1.5"><i data-lucide="wallet" class="w-4 h-4"></i> Ledger & Payouts</a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-sm font-semibold text-gray-200"><?= htmlspecialchars($user['full_name']) ?></span>
                    <span class="text-xs text-brand-400 font-mono">@<?= htmlspecialchars($user['username']) ?> (<?= strtoupper($user['role']) ?>)</span>
                </div>
                <a href="/index.php?action=logout" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 hover:bg-red-500/20 hover:text-red-400 border border-gray-700 hover:border-red-500/30 transition flex items-center gap-1">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Logout
                </a>
            </div>
            <?php else: ?>
            <div class="flex items-center gap-3">
                <a href="/index.php?page=login" class="px-4 py-2 text-sm font-semibold text-gray-300 hover:text-white transition">Sign In</a>
                <a href="/index.php?page=register" class="px-4 py-2 text-sm font-bold bg-brand-500 hover:bg-brand-600 text-black rounded-xl transition shadow-md shadow-brand-500/20">Get Started</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- System Flash Notifications -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
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
