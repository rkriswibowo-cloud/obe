<?php
require_once __DIR__ . '/../../app/Support/Branding.php';
$loginConfig = isset($config) && is_array($config) ? $config : require __DIR__ . '/../../config/app.php';
$loginBranding = Branding::all();
$loginLogoUrl = Branding::assetUrl($loginBranding['logo'] ?? null, $loginConfig['base_url'] ?? '/obe/public');
$loginFaviconUrl = Branding::assetUrl($loginBranding['favicon'] ?? null, $loginConfig['base_url'] ?? '/obe/public');
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIP-OKE</title>
    <meta name="description" content="Masuk ke Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi.">
    <?php if ($loginFaviconUrl): ?><link rel="icon" href="<?= htmlspecialchars($loginFaviconUrl) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        (function () {
            try {
                document.documentElement.dataset.theme = localStorage.getItem('theme') || 'dark';
            } catch (error) {}
        })();
    </script>
    <style>
        :root {
            --background: #f4f6f8;
            --surface: #ffffff;
            --surface-subtle: #f8fafc;
            --border: #dfe3e8;
            --border-strong: #c7cdd4;
            --text: #17202a;
            --muted: #647180;
            --muted-strong: #465362;
            --primary: #126da3;
            --primary-hover: #0c5a89;
            --primary-soft: #e8f3f9;
            --danger: #b42318;
            --danger-bg: #fff1f0;
            --danger-border: #f2c7c3;
            --shadow: 0 20px 55px rgba(15, 32, 46, .09);
        }

        [data-theme="dark"] {
            --background: #080b0e;
            --surface: #101419;
            --surface-subtle: #151a20;
            --border: #293039;
            --border-strong: #3a444f;
            --text: #f2f5f7;
            --muted: #9aa6b2;
            --muted-strong: #c0c8d0;
            --primary: #4fb3e8;
            --primary-hover: #75c5ed;
            --primary-soft: #102c3b;
            --danger: #ff8b82;
            --danger-bg: #2b1515;
            --danger-border: #69302d;
            --shadow: 0 25px 70px rgba(0, 0, 0, .34);
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        button, input { font: inherit; }
        button { color: inherit; }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(360px, 42%) 1fr;
        }

        .auth-panel {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 34px clamp(28px, 5vw, 76px);
            background: var(--surface);
            border-right: 1px solid var(--border);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            color: white;
            background: #126da3;
            border-radius: 10px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.16);
        }

        .brand-copy { display: grid; gap: 2px; }
        .brand-name { font-size: .95rem; font-weight: 700; letter-spacing: .01em; }
        .brand-unit { color: var(--muted); font-size: .72rem; }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            margin: auto;
            padding: 70px 0;
        }

        .login-header { margin-bottom: 31px; }
        .login-header h1 {
            margin: 0 0 10px;
            font-size: clamp(1.75rem, 3vw, 2.1rem);
            line-height: 1.18;
            letter-spacing: -.035em;
        }
        .login-header p {
            margin: 0;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.65;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
            padding: 12px 14px;
            color: var(--danger);
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: 9px;
            font-size: .82rem;
            line-height: 1.5;
        }
        .alert i { margin-top: 2px; }

        .field { margin-bottom: 19px; }
        .field-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            margin-bottom: 8px;
        }
        .field label { color: var(--muted-strong); font-size: .79rem; font-weight: 600; }
        .field-note { color: var(--muted); font-size: .7rem; }
        .input-wrap { position: relative; }
        .input-wrap > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: .85rem;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 43px;
            color: var(--text);
            background: var(--surface-subtle);
            border: 1px solid var(--border-strong);
            border-radius: 9px;
            outline: none;
            font-size: .88rem;
            transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        .input-wrap input::placeholder { color: var(--muted); opacity: .75; }
        .input-wrap input:hover { border-color: var(--muted); }
        .input-wrap input:focus {
            background: var(--surface);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 17%, transparent);
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 7px;
            width: 34px;
            height: 34px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 7px;
            color: var(--muted);
            background: transparent;
            cursor: pointer;
        }
        .password-toggle:hover { color: var(--text); background: var(--primary-soft); }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 2px 0 24px;
            font-size: .78rem;
        }
        .remember { display: flex; align-items: center; gap: 9px; color: var(--muted-strong); cursor: pointer; }
        .remember input { width: 16px; height: 16px; margin: 0; accent-color: var(--primary); }
        .help-link { color: var(--primary); font-weight: 600; text-decoration: none; }
        .help-link:hover { text-decoration: underline; }

        .submit-button {
            width: 100%;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: white;
            background: #126da3;
            border: 1px solid #126da3;
            border-radius: 9px;
            font-weight: 600;
            font-size: .86rem;
            cursor: pointer;
            transition: background .15s, transform .1s;
        }
        .submit-button:hover { background: #0c5a89; }
        .submit-button:active { transform: translateY(1px); }

        .login-assistance {
            margin: 22px 0 0;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .76rem;
            line-height: 1.6;
        }

        .panel-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            color: var(--muted);
            font-size: .68rem;
        }
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 8px;
            border: 0;
            border-radius: 7px;
            color: var(--muted);
            background: transparent;
            cursor: pointer;
        }
        .theme-toggle:hover { color: var(--text); background: var(--surface-subtle); }

        .context-panel {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: clamp(38px, 6vw, 82px);
            color: #eaf4f9;
            background: #0b2738;
        }
        .context-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .17;
            background-image:
                linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom right, black, transparent 78%);
        }
        .context-panel::after {
            content: "";
            position: absolute;
            right: -14%;
            bottom: -18%;
            width: min(52vw, 680px);
            aspect-ratio: 1;
            border: 1px solid rgba(112, 197, 238, .22);
            border-radius: 50%;
            box-shadow: 0 0 0 80px rgba(112,197,238,.035), 0 0 0 160px rgba(112,197,238,.025);
        }
        .context-top, .context-content, .context-footer { position: relative; z-index: 1; }
        .context-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(234,244,249,.68);
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .status { display: inline-flex; align-items: center; gap: 8px; }
        .status::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #58c89a; }
        .context-content { max-width: 630px; margin: auto 0; padding: 70px 0; }
        .context-kicker { margin: 0 0 18px; color: #70c5ee; font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .context-content h2 { max-width: 590px; margin: 0; font-size: clamp(2.15rem, 4.4vw, 4.45rem); line-height: 1.05; letter-spacing: -.055em; font-weight: 600; }
        .context-description { max-width: 530px; margin: 25px 0 0; color: rgba(234,244,249,.7); font-size: clamp(.88rem, 1.35vw, 1rem); line-height: 1.75; }
        .capabilities { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 34px; }
        .capabilities span { padding: 8px 11px; border: 1px solid rgba(234,244,249,.17); border-radius: 6px; color: rgba(234,244,249,.82); background: rgba(255,255,255,.035); font-size: .72rem; }
        .context-footer { max-width: 560px; padding-top: 20px; border-top: 1px solid rgba(234,244,249,.14); color: rgba(234,244,249,.5); font-size: .69rem; line-height: 1.6; }

        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-panel { min-height: 100vh; border-right: 0; }
            .context-panel { display: none; }
            .login-wrap { max-width: 440px; }
        }
        @media (max-width: 520px) {
            .auth-panel { padding: 24px 20px; }
            .login-wrap { padding: 52px 0; }
            .brand-unit { display: none; }
            .panel-footer { align-items: flex-end; }
            .panel-footer > span { max-width: 210px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-panel" aria-labelledby="login-title">
            <a class="brand" href="/obe/public/login" aria-label="SIP-OKE">
                <span class="brand-mark">
                    <?php if ($loginLogoUrl): ?>
                        <img src="<?= htmlspecialchars($loginLogoUrl) ?>" alt="" style="width:100%;height:100%;object-fit:contain;padding:5px;">
                    <?php else: ?>
                        <i class="fa-solid fa-graduation-cap"></i>
                    <?php endif; ?>
                </span>
                <span class="brand-copy">
                    <span class="brand-name">SIP-OKE</span>
                    <span class="brand-unit">Sistem Informasi OBE</span>
                </span>
            </a>

            <div class="login-wrap">
                <header class="login-header">
                    <h1 id="login-title">Masuk ke sistem</h1>
                    <p>Gunakan akun yang telah diberikan oleh administrator program studi.</p>
                </header>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form action="/obe/public/login/submit" method="POST">
                    <div class="field">
                        <div class="field-head">
                            <label for="username">Username atau NIDN</label>
                            <span class="field-note">Wajib diisi</span>
                        </div>
                        <div class="input-wrap">
                            <i class="fa-regular fa-user" aria-hidden="true"></i>
                            <input id="username" name="username" type="text" autocomplete="username" placeholder="Masukkan username atau NIDN" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-head">
                            <label for="password">Kata sandi</label>
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                            <button class="password-toggle" type="button" id="passwordToggle" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember"><input type="checkbox" name="rememberMe"> Ingat saya di perangkat ini</label>
                    </div>

                    <button class="submit-button" type="submit">
                        <span>Masuk</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="login-assistance" id="login-help">Belum memiliki akun atau lupa kata sandi? Pendaftaran, pemulihan akun, dan perubahan hak akses dikelola oleh administrator program studi.</p>
            </div>

            <footer class="panel-footer">
                <span>&copy; <?= date('Y') ?> SIP-OKE · Akses terbatas untuk pengguna terdaftar</span>
                <button type="button" class="theme-toggle" id="themeToggle" aria-label="Ubah tema tampilan">
                    <i class="fa-solid fa-moon" aria-hidden="true"></i>
                    <span>Gelap</span>
                </button>
            </footer>
        </section>

        <aside class="context-panel" aria-label="Tentang SIP-OKE">
            <div class="context-top">
                <span>Outcome-Based Education</span>
                <span class="status">Sistem aktif</span>
            </div>

            <div class="context-content">
                <p class="context-kicker">Platform akademik terintegrasi</p>
                <h2>Data capaian yang dapat ditelusuri.</h2>
                <p class="context-description">Kelola kurikulum, RPS, asesmen, dan ketercapaian CPL dalam satu alur kerja yang terukur dan akuntabel.</p>
                <div class="capabilities" aria-label="Fitur utama">
                    <span>Kurikulum OBE</span>
                    <span>RPS Digital</span>
                    <span>Asesmen</span>
                    <span>Evaluasi CPL</span>
                    <span>CQI</span>
                </div>
            </div>

            <p class="context-footer">Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi mendukung pengelolaan mutu pembelajaran berbasis bukti.</p>
        </aside>
    </main>

    <script>
        (function () {
            const root = document.documentElement;
            const password = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const themeToggle = document.getElementById('themeToggle');

            function updateThemeControl() {
                const isDark = root.dataset.theme !== 'light';
                themeToggle.querySelector('i').className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
                themeToggle.querySelector('span').textContent = isDark ? 'Gelap' : 'Terang';
            }

            passwordToggle.addEventListener('click', function () {
                const reveal = password.type === 'password';
                password.type = reveal ? 'text' : 'password';
                this.setAttribute('aria-pressed', String(reveal));
                this.setAttribute('aria-label', reveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                this.querySelector('i').className = reveal ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            });

            themeToggle.addEventListener('click', function () {
                root.dataset.theme = root.dataset.theme === 'light' ? 'dark' : 'light';
                localStorage.setItem('theme', root.dataset.theme);
                updateThemeControl();
            });

            updateThemeControl();
        })();
    </script>
</body>
</html>
