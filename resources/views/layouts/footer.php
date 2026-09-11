    </main> <!-- End main-content -->
</div> <!-- End layout-wrapper -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="<?php echo URL_ROOT; ?>/assets/js/main.js"></script>

<!-- PWA Service Worker & Offline Sync Engine -->
<script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?php echo URL_ROOT; ?>/sw.js', { scope: '<?php echo URL_ROOT; ?>/' })
                .then(function(registration) {
                    // SW Registered successfully
                })
                .catch(function(err) {
                    console.warn('[PWA] Service Worker registration failed: ', err);
                });
        });
    }

    // Global Online / Offline Status Handler
    function handleNetworkStatusChange() {
        const banner = document.getElementById('pmsOfflineBanner');
        const msg = document.getElementById('pmsOfflineMsg');
        if (!banner) return;

        if (!navigator.onLine) {
            banner.classList.remove('restored');
            banner.classList.add('active');
            if (msg) msg.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline Mode Active — POS operations remain operational with cached inventory.';
        } else {
            if (banner.classList.contains('active')) {
                banner.classList.add('restored');
                if (msg) msg.innerHTML = '<i class="fas fa-wifi"></i> Connection Restored! Syncing cached data...';
                setTimeout(function() {
                    banner.classList.remove('active', 'restored');
                }, 3000);
            }
        }
    }

    window.addEventListener('online', handleNetworkStatusChange);
    window.addEventListener('offline', handleNetworkStatusChange);
    document.addEventListener('DOMContentLoaded', handleNetworkStatusChange);
</script>
</body>
</html>

