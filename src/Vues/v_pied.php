<?php
/**
 * Pied de page (Bootstrap 5.3 + sidebar layout)
 */
?>
    </main>
</div>
<footer class="mt-auto py-3 bg-light border-top">
    <div class="container text-center">
        <p class="text-muted mb-0">© GSB 2026</p>
    </div>
</footer>
<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script>
/* Toggle Sidebar (mobile) */
(function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (toggleBtn && sidebar) {
        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-open');
            backdrop.classList.toggle('active');
            document.body.classList.toggle('overflow-hidden');
        }
        toggleBtn.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);
    }
})();
</script>
</body>
</html>