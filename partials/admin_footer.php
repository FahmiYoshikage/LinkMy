<?php
// partials/admin_footer.php
?>
    <script src="../assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Theme toggle functionality
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;

        const html = document.documentElement;
        const themeIcon = document.getElementById('themeIcon');

        function setTheme(theme) {
            html.setAttribute('data-theme', theme);
            // Use cookies for server-side rendering compatibility
            document.cookie = `theme=${theme};path=/;max-age=31536000;samesite=lax`;
            
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            }
            // Dispatch a custom event to notify other scripts (like charts)
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
        }

        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = html.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    })();
    </script>
</body>
</html>
