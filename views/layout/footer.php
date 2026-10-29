</main>

<footer class="border-t border-gray-800 glass-card mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-400 gap-4">
        <div class="flex items-center gap-2">
            <span class="font-bold text-gray-300">Pinnacle Hunt</span> &copy; 2026. All rights reserved. Built with 3-Matrix BFS Forced Spillover Engine.
        </div>
        <div class="flex items-center gap-6">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Platform Active
            </span>
            <span>Security: 256-bit Encrypted</span>
        </div>
    </div>
</footer>

<?php if (isLoggedIn()): ?>
</div> <!-- End Outer Wrapper .md:ml-64 -->
<?php endif; ?>

<script>
    lucide.createIcons();

    function copyToClipboard(text, elementId) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById(elementId);
            if (btn) {
                const origText = btn.innerHTML;
                btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline"></i> Copied!';
                lucide.createIcons();
                setTimeout(() => {
                    btn.innerHTML = origText;
                    lucide.createIcons();
                }, 2000);
            } else {
                alert('Copied to clipboard!');
            }
        });
    }
</script>
</body>
</html>
