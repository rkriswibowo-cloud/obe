<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi OBE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/obe/public/assets/css/app.css">
    
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                document.documentElement.classList.add(savedTheme);

                const savedPalette = localStorage.getItem('accent_palette') || 'slate';
                document.documentElement.setAttribute('data-palette', savedPalette);
            } catch(e) {}
        })();
    </script>

    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--background);
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }
        .login-theme-switch {
            position: absolute;
            top: 24px;
            right: 24px;
        }
        .login-card {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: var(--shadow-md);
        }
        .login-card input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: var(--text-main) !important;
            border-radius: var(--radius-sm);
            box-sizing: border-box;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 18px;
            border: 1px solid var(--danger);
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-theme-switch">
            <button type="button" id="themeToggleBtn" class="theme-toggle-btn" onclick="toggleTheme()" title="Ubah Mode Gelap / Terang">
                <span class="theme-toggle-icon">
                    <i class="fa-solid fa-moon" id="themeToggleIcon"></i>
                </span>
                <span id="themeToggleText">Dark</span>
            </button>
        </div>

        <div class="login-card">
            <div style="font-size: 2.2rem; color: var(--primary); margin-bottom: 12px;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2 style="margin: 0 0 8px 0; color: var(--text-main); font-weight: 700;">Login OBE & CPL</h2>
            <p style="margin: 0 0 24px 0; color: var(--text-muted); font-size: 0.9rem;">Sistem Informasi Pemenuhan Capaian Pembelajaran</p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form action="/obe/public/login/submit" method="POST" style="text-align: left;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500;">Username / NIDN</label>
                <input type="text" name="username" placeholder="Masukkan username atau NIDN" required autofocus>
                
                <label style="display: block; margin-bottom: 6px; font-weight: 500;">Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 8px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem
                </button>
            </form>
        </div>
    </div>

    <script>
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(theme);
            localStorage.setItem('theme', theme);

            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (icon && text) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                }
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            applyTheme(savedTheme);
        });
    </script>
</body>
</html>
