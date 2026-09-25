<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Informasi OBE & CPL' ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $config['base_url'] ?? '/obe/public' ?>/assets/css/app.css">
    
    <!-- Script Anti-FOUC: Eksekusi tema & palet sebelum DOM selesai render -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                document.documentElement.classList.remove('dark', 'light');
                document.documentElement.classList.add(savedTheme);

                const savedPalette = localStorage.getItem('accent_palette') || 'slate';
                document.documentElement.setAttribute('data-palette', savedPalette);

                if (localStorage.getItem('sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {}
        })();
    </script>
</head>
<body>
    <div class="app-layout" id="appLayout">
        <!-- Full-width Topbar matching screenshot -->
        <?php require __DIR__ . '/../components/topbar.php'; ?>

        <div class="app-container">
            <!-- Floating Sidebar Island Card -->
            <?php require __DIR__ . '/../components/sidebar.php'; ?>

            <!-- Main Content Area -->
            <div class="main-content">
                <div class="content-area">
                    <?= $content ?? '' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Theme Switcher Logic -->
    <script>
        function applyTheme(theme, animate = false) {
            if (animate) {
                document.documentElement.classList.add('theme-transition');
            }
            
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(theme);
            localStorage.setItem('theme', theme);

            // Update UI Topbar Button
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            const btn = document.getElementById('themeToggleBtn');

            if (icon && text && btn) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                    btn.setAttribute('title', 'Ganti ke Mode Gelap (Dark Mode)');
                    btn.setAttribute('aria-label', 'Switch to Dark Mode');
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                    btn.setAttribute('title', 'Ganti ke Mode Terang (Light Mode)');
                    btn.setAttribute('aria-label', 'Switch to Light Mode');
                }
            }

            // Dispatch global event untuk chart atau komponen lain
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));

            if (animate) {
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 350);
            }
        }

        window.toggleTheme = function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme, true);
        };

        /* ==========================================================================
           ACCENT COLOR PALETTE LOGIC
           ========================================================================== */
        const paletteConfig = {
            slate: { name: 'Slate Blue', color: '#0284c7' },
            indigo: { name: 'Indigo', color: '#6366f1' },
            emerald: { name: 'Emerald', color: '#10b981' },
            ocean: { name: 'Ocean Blue', color: '#0ea5e9' },
            violet: { name: 'Violet', color: '#8b5cf6' },
            amber: { name: 'Amber', color: '#f59e0b' },
            rose: { name: 'Rose', color: '#f43f5e' }
        };

        window.applyPalette = function(palette, animate = true) {
            if (!paletteConfig[palette]) {
                palette = 'slate';
            }

            if (animate) {
                document.documentElement.classList.add('theme-transition');
            }

            document.documentElement.setAttribute('data-palette', palette);
            localStorage.setItem('accent_palette', palette);

            // Update Topbar UI
            const nameEl = document.getElementById('paletteActiveName');
            const dotEl = document.getElementById('paletteActiveDot');
            if (nameEl) nameEl.textContent = paletteConfig[palette].name;
            if (dotEl) dotEl.style.backgroundColor = paletteConfig[palette].color;

            // Update active option in dropdown list
            document.querySelectorAll('.palette-option').forEach(btn => {
                if (btn.getAttribute('data-pal') === palette) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Tutup dropdown setelah memilih
            const menu = document.getElementById('paletteDropdownMenu');
            if (menu) menu.style.display = 'none';

            // Dispatch global event untuk Chart.js atau komponen lain
            window.dispatchEvent(new CustomEvent('paletteChanged', { 
                detail: { palette, color: paletteConfig[palette].color } 
            }));

            if (animate) {
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 350);
            }
        };

        window.togglePaletteDropdown = function(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('paletteDropdownMenu');
            if (menu) {
                const isHidden = menu.style.display === 'none' || menu.style.display === '';
                menu.style.display = isHidden ? 'block' : 'none';
            }
        };

        // Tutup menu dropdown jika user klik di luar area dropdown
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('paletteDropdownWrapper');
            const menu = document.getElementById('paletteDropdownMenu');
            if (wrapper && menu && !wrapper.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // Inisialisasi status tombol & tema saat DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme') || 'dark';
            applyTheme(currentTheme, false);

            const currentPalette = document.documentElement.getAttribute('data-palette') || localStorage.getItem('accent_palette') || 'slate';
            window.applyPalette(currentPalette, false);
        });
    </script>
</body>
</html>
