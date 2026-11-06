<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
            <i data-lucide="users" class="w-8 h-8 text-brand-400"></i> Member & Matrix Oversight
        </h1>
        <p class="text-xs text-gray-400 mt-1">Directory of system users, balances, edit/delete administration, and WhatsApp welcome messaging</p>
    </div>
</div>

<div class="glass-card rounded-3xl p-6 border border-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-gray-900/60 uppercase text-[10px] text-gray-400 font-semibold border-b border-gray-800">
                <tr>
                    <th class="px-3 py-3">ID</th>
                    <th class="px-3 py-3">Member Details</th>
                    <th class="px-3 py-3">Direct Sponsor</th>
                    <th class="px-3 py-3">Wallet</th>
                    <th class="px-3 py-3">Reserve</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3 text-center">Actions & Welcome</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60 font-mono">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 font-sans">No members registered yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $waMsg = generateWhatsAppWelcomeMessage($u);
                            $cleanPhone = preg_replace('/[^0-9]/', '', $u['phone'] ?? '');
                            $waUrl = !empty($cleanPhone) ? "https://wa.me/" . $cleanPhone . "?text=" . urlencode($waMsg) : "https://wa.me/?text=" . urlencode($waMsg);
                        ?>
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-3 py-3 text-gray-400 font-mono">#<?= $u['id'] ?></td>
                            <td class="px-3 py-3 font-sans">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    @<?= htmlspecialchars($u['username']) ?>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="text-[9px] bg-purple-500/20 text-purple-300 px-1.5 py-0.2 rounded font-mono font-bold">ADMIN</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div class="text-[10px] text-gray-500"><?= htmlspecialchars($u['email']) ?> <?= !empty($u['phone']) ? '• ' . htmlspecialchars($u['phone']) : '' ?></div>
                            </td>
                            <td class="px-3 py-3 text-brand-400 font-sans text-xs">
                                <?= !empty($u['sponsor_username']) ? '@' . htmlspecialchars($u['sponsor_username']) : '<span class="text-gray-500">None (Root)</span>' ?>
                            </td>
                            <td class="px-3 py-3 text-emerald-400 font-bold"><?= formatCurrency($u['wallet_balance']) ?></td>
                            <td class="px-3 py-3 text-amber-400 font-bold"><?= formatCurrency($u['reserve_balance']) ?></td>
                            <td class="px-3 py-3 font-sans">
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                                <?php elseif ($u['status'] === 'inactive'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Inactive</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-3 font-sans text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <?php if ($u['id'] !== 1): ?>
                                        <!-- Activation / Deactivation Toggle Button -->
                                        <form action="index.php?action=toggle_member_status" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <?php if ($u['status'] === 'active'): ?>
                                                <input type="hidden" name="status" value="inactive">
                                                <button type="submit" title="Deactivate Member" class="p-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 transition flex items-center gap-1 text-[11px] font-semibold">
                                                    <i data-lucide="power-off" class="w-3.5 h-3.5"></i>
                                                    <span>Deactivate</span>
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" title="Activate Member" class="p-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition flex items-center gap-1 text-[11px] font-semibold">
                                                    <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                                    <span>Activate</span>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                    <!-- WhatsApp Welcome Button -->
                                    <button onclick="openWhatsAppModal(<?= htmlspecialchars(json_encode([
                                        'id' => $u['id'],
                                        'username' => $u['username'],
                                        'full_name' => $u['full_name'],
                                        'phone' => $u['phone'],
                                        'cleanPhone' => $cleanPhone,
                                        'waUrl' => $waUrl,
                                        'message' => $waMsg
                                    ])) ?>)"
                                    title="WhatsApp Welcome Message"
                                    class="p-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition flex items-center gap-1 text-[11px] font-semibold">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>WhatsApp</span>
                                    </button>

                                    <!-- Edit Member Button -->
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)"
                                    title="Edit Member Details"
                                    class="p-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 transition flex items-center gap-1 text-[11px] font-semibold">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </button>

                                    <?php if ($u['id'] !== 1): ?>
                                        <!-- Delete Member Button (with deep clean confirmation) -->
                                        <button onclick="openDeleteModal(<?= htmlspecialchars(json_encode($u)) ?>)"
                                        title="Delete Member & Purge All Amounts"
                                        class="p-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 transition flex items-center gap-1 text-[11px] font-semibold">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Delete</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: WhatsApp Welcome Message -->
<div id="waModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full rounded-3xl p-6 border border-emerald-500/30 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="message-circle" class="w-5 h-5 text-emerald-400"></i> WhatsApp Welcome Message
            </h3>
            <button onclick="closeWhatsAppModal()" class="text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div>
            <p class="text-xs text-gray-400 mb-2">Formatted WhatsApp template with bolding, emojis, and account links ready to send to <span id="waModalRecipient" class="font-bold text-emerald-400"></span>:</p>
            <textarea id="waMessageText" rows="11" readonly class="w-full p-3 bg-gray-950 border border-gray-800 rounded-2xl text-xs font-mono text-gray-200 focus:outline-none select-all leading-relaxed"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button onclick="copyWhatsAppText()" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="copy" class="w-4 h-4"></i> Copy Text
            </button>
            <a id="waDirectLink" href="#" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-black text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                <i data-lucide="send" class="w-4 h-4"></i> Send via WhatsApp Web / App
            </a>
        </div>
    </div>
</div>

<!-- Modal: Edit Member -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full rounded-3xl p-6 border border-blue-500/30 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-5 h-5 text-blue-400"></i> Edit Member Profile & Balances
            </h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="index.php?action=edit_member" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="user_id" id="editUserId">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Full Name</label>
                    <input type="text" name="full_name" id="editFullName" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Phone Number</label>
                    <input type="text" name="phone" id="editPhone" placeholder="+91..." class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" id="editStatus" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Wallet Balance (₹)</label>
                    <input type="number" step="0.01" min="0" name="wallet_balance" id="editWalletBalance" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-emerald-400 font-mono text-xs focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Reserve Hold (₹)</label>
                    <input type="number" step="0.01" min="0" name="reserve_balance" id="editReserveBalance" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-amber-400 font-mono text-xs focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Reset Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold transition shadow-lg shadow-blue-500/20">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Delete Member Confirmation -->
<div id="deleteModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-md w-full rounded-3xl p-6 border border-red-500/40 shadow-2xl space-y-4 text-center">
        <div class="w-14 h-14 bg-red-500/10 text-red-400 rounded-2xl flex items-center justify-center mx-auto border border-red-500/20">
            <i data-lucide="alert-triangle" class="w-7 h-7"></i>
        </div>

        <div>
            <h3 class="text-lg font-bold text-white">Permanently Delete Member?</h3>
            <p class="text-xs text-gray-400 mt-2">
                This will delete user <span id="deleteUsername" class="font-bold text-red-400"></span> and completely purge/clawback all commissions and amounts this member generated to uplines, restoring balances and matrix positions cleanly.
            </p>
        </div>

        <form action="index.php?action=delete_member" method="POST" class="pt-2">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="user_id" id="deleteUserId">

            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-red-600/20 flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Confirm Deep Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openWhatsAppModal(data) {
    document.getElementById('waModalRecipient').innerText = '@' + data.username + ' (' + data.full_name + ')';
    document.getElementById('waMessageText').value = data.message;
    document.getElementById('waDirectLink').href = data.waUrl;
    document.getElementById('waModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeWhatsAppModal() {
    document.getElementById('waModal').classList.add('hidden');
}

function copyWhatsAppText() {
    const text = document.getElementById('waMessageText').value;
    navigator.clipboard.writeText(text).then(() => {
        alert('WhatsApp welcome message copied to clipboard!');
    });
}

function openEditModal(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFullName').value = user.full_name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editStatus').value = user.status;
    document.getElementById('editWalletBalance').value = user.wallet_balance;
    document.getElementById('editReserveBalance').value = user.reserve_balance;
    document.getElementById('editModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function openDeleteModal(user) {
    document.getElementById('deleteUserId').value = user.id;
    document.getElementById('deleteUsername').innerText = '@' + user.username;
    document.getElementById('deleteModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
