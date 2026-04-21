<footer class="footer mt-auto py-3 bg-white border-top shadow-sm" style="margin-left: var(--sidebar-width);">
    <div class="container-fluid text-center">
        <span class="text-muted small">&copy;
            <?= date('Y') ?>
            <?= theme('app_name') ?>. All rights reserved.
        </span>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle Sidebar on mobile
    document.querySelector('.navbar-toggler').addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('show');
    });
</script>
</body>

</html>