<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header Banner -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="terminal" class="w-8 h-8 text-brand-400"></i> SQL Member Console
        </h1>
        <p class="text-xs text-gray-400 mt-1">Execute direct SQL INSERT statements to bulk register members and auto-place them into the BFS 3-Matrix with spillover</p>
    </div>

    <!-- Active Member Badge -->
    <div class="flex items-center gap-3">
        <div class="glass-card px-4 py-2 rounded-2xl border border-gray-800 flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs text-gray-400">Total Registered Members:</span>
            <span class="text-sm font-bold font-mono text-white"><?= number_format($totalUsers) ?></span>
        </div>
    </div>
</div>

<!-- Last Execution Report (if available) -->
<?php if (!empty($_SESSION['sql_results'])): $res = $_SESSION['sql_results']; ?>
<div class="glass-card rounded-3xl p-6 mb-8 border border-brand-500/30 glass-glow bg-brand-500/5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-brand-400 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i> Last SQL Execution Summary
        </h3>
        <span class="text-xs text-gray-400 font-mono">Members Count: <strong class="text-white"><?= $res['current_member_count'] ?></strong></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div class="p-3 bg-gray-900/80 rounded-2xl border border-gray-800">
            <div class="text-[11px] text-gray-400">Members Inserted</div>
            <div class="text-xl font-bold font-mono text-white"><?= $res['inserted_count'] ?></div>
        </div>
        <div class="p-3 bg-gray-900/80 rounded-2xl border border-gray-800">
            <div class="text-[11px] text-gray-400">Matrix Placements (BFS)</div>
            <div class="text-xl font-bold font-mono text-emerald-400"><?= $res['placed_count'] ?></div>
        </div>
        <div class="p-3 bg-gray-900/80 rounded-2xl border border-gray-800">
            <div class="text-[11px] text-gray-400">Target Package Tier</div>
            <div class="text-xl font-bold font-mono text-brand-400">Tier #<?= $res['package_id'] ?></div>
        </div>
    </div>

    <?php if (!empty($res['placements'])): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/90 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-4 py-2.5">User ID</th>
                    <th class="px-4 py-2.5">Username</th>
                    <th class="px-4 py-2.5">Full Name</th>
                    <th class="px-4 py-2.5">Matrix Depth</th>
                    <th class="px-4 py-2.5">Parent Node</th>
                    <th class="px-4 py-2.5">Node ID</th>
                    <th class="px-4 py-2.5">Commissions Generated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 font-mono">
                <?php foreach ($res['placements'] as $p): ?>
                <tr class="hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-gray-400">#<?= $p['user_id'] ?></td>
                    <td class="px-4 py-2.5 font-bold text-white font-sans">@<?= htmlspecialchars($p['username']) ?></td>
                    <td class="px-4 py-2.5 text-gray-300 font-sans"><?= htmlspecialchars($p['full_name']) ?></td>
                    <td class="px-4 py-2.5 text-brand-400 font-bold"><?= $p['depth'] ?? 'N/A' ?></td>
                    <td class="px-4 py-2.5 text-gray-400"><?= !empty($p['parent_id']) ? '#' . $p['parent_id'] : 'Root' ?></td>
                    <td class="px-4 py-2.5 text-emerald-400 font-bold"><?= !empty($p['node_id']) ? '#' . $p['node_id'] : 'N/A' ?></td>
                    <td class="px-4 py-2.5 text-amber-400"><?= $p['payouts_count'] ?? 0 ?> upline payouts</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php unset($_SESSION['sql_results']); endif; ?>

<!-- Main SQL Input Form -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card rounded-3xl p-6 border border-gray-800">
            <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                <i data-lucide="code-2" class="w-5 h-5 text-brand-400"></i> Paste & Execute Member INSERT Statements
            </h3>
            <p class="text-xs text-gray-400 mb-6">
                Paste one or multiple SQL <code class="text-emerald-400 font-mono">INSERT INTO users (...) VALUES (...)</code> statements. The system will execute the inserts, hash passwords automatically, update member count, and trigger BFS forced spillover placement for each member.
            </p>

            <form action="index.php?action=execute_sql" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-2">Package Tier for Auto Matrix Placement</label>
                    <select name="package_id" class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-2xl text-white text-sm focus:outline-none focus:border-brand-500">
                        <?php foreach ($packages as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>">Tier #<?= $pkg['id'] ?> - <?= htmlspecialchars($pkg['name']) ?> (<?= formatCurrency($pkg['price']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-gray-300">SQL INSERT Statement(s)</label>
                        <button type="button" onclick="loadSampleTemplate()" class="text-[11px] font-bold text-brand-400 hover:text-brand-300 transition">
                            + Load Sample Template
                        </button>
                    </div>
                    <textarea id="sqlQueryTextarea" name="sql_query" rows="10" required placeholder="INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id) VALUES
('rahul99', 'rahul@example.com', 'Pass@123', 'Rahul Sharma', '+919876543210', 'user', 1),
('priya88', 'priya@example.com', 'Pass@123', 'Priya Patel', '+919876543211', 'user', 1);" class="w-full px-4 py-3 bg-gray-950 border border-gray-800 rounded-2xl text-white font-mono text-xs focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 leading-relaxed"><?= htmlspecialchars($_SESSION['last_sql_query'] ?? '') ?></textarea>
                    <?php unset($_SESSION['last_sql_query']); ?>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-gray-500">Supports standard MySQL/MariaDB INSERT syntax.</span>
                    <button type="submit" onclick="return confirm('Execute these INSERT statements and place members in BFS matrix?')" class="px-6 py-2.5 rounded-2xl bg-brand-500 hover:bg-brand-600 text-black font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-brand-500/20">
                        <i data-lucide="play" class="w-4 h-4"></i> Execute & Place Members
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructions & Syntax Guide -->
    <div class="space-y-6">
        <div class="glass-card rounded-3xl p-6 border border-gray-800">
            <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-brand-400"></i> Execution Features
            </h4>
            <ul class="text-xs text-gray-400 space-y-2.5">
                <li class="flex items-start gap-2">
                    <span class="text-emerald-400 font-bold">&check;</span>
                    <span><strong>Breadth-First Spillover:</strong> Each inserted member is placed under the first available open node ($<3$ children, depth $<7$) across the company tree.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-emerald-400 font-bold">&check;</span>
                    <span><strong>Automatic Password Hashing:</strong> Passwords provided as plain text are encrypted using standard Bcrypt.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-emerald-400 font-bold">&check;</span>
                    <span><strong>Real-Time Member Count:</strong> Total registered member metrics increment dynamically.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-emerald-400 font-bold">&check;</span>
                    <span><strong>Full Commission Accounting:</strong> Direct sponsor bonuses and 7-level matrix payouts are recorded in ledger.</span>
                </li>
            </ul>
        </div>

        <div class="glass-card rounded-3xl p-6 border border-gray-800">
            <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                <i data-lucide="file-code" class="w-4 h-4 text-brand-400"></i> Supported Columns
            </h4>
            <div class="p-3 bg-gray-950 rounded-2xl border border-gray-800 font-mono text-[11px] text-gray-300 space-y-1">
                <div><code>username</code> <span class="text-gray-500">(required, unique)</span></div>
                <div><code>email</code> <span class="text-gray-500">(required, unique)</span></div>
                <div><code>password</code> <span class="text-gray-500">(required)</span></div>
                <div><code>full_name</code> <span class="text-gray-500">(required)</span></div>
                <div><code>phone</code> <span class="text-gray-500">(optional)</span></div>
                <div><code>sponsor_id</code> <span class="text-gray-500">(optional, ID of sponsor)</span></div>
                <div><code>role</code> <span class="text-gray-500">('user' or 'admin')</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function loadSampleTemplate() {
    const timestamp = Date.now().toString().slice(-4);
    const sample = `INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id) VALUES
('member_${timestamp}_a', 'user_${timestamp}_a@example.com', 'Pass@123', 'Member Alpha ${timestamp}', '+919876500001', 'user', 1),
('member_${timestamp}_b', 'user_${timestamp}_b@example.com', 'Pass@123', 'Member Beta ${timestamp}', '+919876500002', 'user', 1),
('member_${timestamp}_c', 'user_${timestamp}_c@example.com', 'Pass@123', 'Member Gamma ${timestamp}', '+919876500003', 'user', 1);`;
    document.getElementById('sqlQueryTextarea').value = sample;
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
