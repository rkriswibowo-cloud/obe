<div class="topbar">
    <div class="topbar-left">
        <!-- Brand Squircle Logo -->
        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/dashboard" class="brand-squircle-link" title="Sistem Informasi OBE & CPL">
            <div class="brand-squircle">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </a>

        <span class="topbar-divider-slash">/</span>

        <!-- User / Account Pill Selector (matching screenshot) -->
        <div class="user-pill-dropdown-wrapper" id="userPillWrapper">
            <button type="button" class="user-pill-btn" id="userPillBtn" onclick="window.toggleUserDropdown(event)" title="Informasi Akun" aria-label="Akun Pengguna">
                <span class="user-avatar-circle">
                    <i class="fa-regular fa-user"></i>
                </span>
                <span class="user-email-text">
                    <?= htmlspecialchars($_SESSION['email'] ?? ($_SESSION['nama_lengkap'] ? strtolower(str_replace(' ', '', $_SESSION['nama_lengkap'])) . '@gmail.com' : 'cansaas@gmail.com')) ?>
                </span>
                <span class="user-plan-badge">
                    <?= htmlspecialchars($_SESSION['role'] ?? 'Free') ?>
                </span>
                <i class="fa-solid fa-chevron-down pill-chevron"></i>
            </button>
            <div class="user-pill-menu" id="userPillMenu" style="display: none;">
                <div class="user-pill-menu-header">
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.88rem;">
                        <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Administrator OBE') ?>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                        <?= htmlspecialchars($_SESSION['email'] ?? 'admin@obe.ac.id') ?>
                    </div>
                </div>
                <div class="user-pill-menu-divider"></div>
                <a href="<?= $config['base_url'] ?? '/obe/public' ?>/dashboard" class="user-pill-menu-item">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <a href="<?= $config['base_url'] ?? '/obe/public' ?>/tahun-akademik" class="user-pill-menu-item">
                    <i class="fa-solid fa-calendar-days"></i> Tahun Akademik
                </a>
                <div class="user-pill-menu-divider"></div>
                <a href="<?= $config['base_url'] ?? '/obe/public' ?>/logout" class="user-pill-menu-item danger">
                    <i class="fa-solid fa-sign-out-alt"></i> Keluar (Logout)
                </a>
            </div>
        </div>
    </div>

    <div class="topbar-right" style="display: flex; align-items: center; gap: 12px;">
        <!-- Accent Palette Switcher Dropdown -->
        <div class="palette-dropdown-wrapper" id="paletteDropdownWrapper">
            <button type="button" id="paletteToggleBtn" class="palette-toggle-btn" onclick="window.togglePaletteDropdown(event)" title="Pilih Tema Palet Warna" aria-label="Palette Selector">
                <span class="palette-dot-preview" id="paletteActiveDot" style="background-color: #0284c7;"></span>
                <i class="fa-solid fa-palette" style="font-size: 0.85rem; opacity: 0.85;"></i>
                <span id="paletteActiveName" style="font-size: 0.82rem; font-weight: 600;">Slate Blue</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; opacity: 0.6; margin-left: 2px;"></i>
            </button>
            <div id="paletteDropdownMenu" class="palette-dropdown-menu" style="display: none;">
                <div class="palette-dropdown-header">Warna Aksen</div>
                <div class="palette-options-list">
                    <button type="button" class="palette-option active" data-pal="slate" onclick="window.applyPalette('slate')">
                        <span class="palette-swatch" style="background: #0284c7;"></span>
                        <span class="palette-name">Slate Blue (Utama)</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="indigo" onclick="window.applyPalette('indigo')">
                        <span class="palette-swatch" style="background: #6366f1;"></span>
                        <span class="palette-name">Indigo</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="emerald" onclick="window.applyPalette('emerald')">
                        <span class="palette-swatch" style="background: #10b981;"></span>
                        <span class="palette-name">Emerald</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="ocean" onclick="window.applyPalette('ocean')">
                        <span class="palette-swatch" style="background: #0ea5e9;"></span>
                        <span class="palette-name">Ocean Blue</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="violet" onclick="window.applyPalette('violet')">
                        <span class="palette-swatch" style="background: #8b5cf6;"></span>
                        <span class="palette-name">Violet</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="amber" onclick="window.applyPalette('amber')">
                        <span class="palette-swatch" style="background: #f59e0b;"></span>
                        <span class="palette-name">Amber</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                    <button type="button" class="palette-option" data-pal="rose" onclick="window.applyPalette('rose')">
                        <span class="palette-swatch" style="background: #f43f5e;"></span>
                        <span class="palette-name">Rose</span>
                        <i class="fa-solid fa-check palette-check"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dark / Light Mode Toggle Button -->
        <button type="button" id="themeToggleBtn" class="theme-toggle-btn" onclick="window.toggleTheme()" title="Ganti Mode Gelap / Terang (Dark / Light Mode)" aria-label="Toggle Theme">
            <span class="theme-toggle-icon">
                <i class="fa-solid fa-moon" id="themeToggleIcon"></i>
            </span>
            <span id="themeToggleText" style="font-size: 0.82rem; letter-spacing: 0.3px;">Dark</span>
        </button>

        <!-- Notification Bell -->
        <span class="topbar-action-icon" title="Notifikasi">
            <i class="fa-regular fa-bell"></i>
        </span>

        <!-- Quick Logout Link -->
        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/logout" class="topbar-logout-link" title="Keluar">
            <i class="fa-solid fa-sign-out-alt"></i>
        </a>
    </div>
</div>
