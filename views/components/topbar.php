<?php
// views/components/topbar.php
require_once __DIR__ . '/../../app/Support/Branding.php';
require_once __DIR__ . '/../../app/Models/NotificationModel.php';
$topbarBranding = Branding::all();
$topbarLogoUrl = Branding::assetUrl($topbarBranding['logo'] ?? null, $config['base_url'] ?? '/obe/public');
$notifModel = new NotificationModel();
$notifUserId = $_SESSION['user_id'] ?? null;
$unreadNotifCount = $notifModel->getUnreadCount($notifUserId);
$recentNotifs = $notifModel->getLatest($notifUserId, 6);
$topbarAccess = new AccessContext();
$topbarRoleLabel = $topbarAccess->isKaprodi() && !$topbarAccess->isSuperAdmin()
    ? 'Dosen / Kaprodi'
    : ($_SESSION['role'] ?? 'Admin');
$topbarUserEmail = $_SESSION['email'] ?? 'admin@obe.ac.id';
?>
<div class="topbar">
    <div class="topbar-left">
        <!-- Sidebar Toggle Collapse / Expand Button -->
        <button type="button" class="topbar-sidebar-toggle-btn" id="sidebarToggleBtn" onclick="window.toggleSidebarCollapse()" title="Ciutkan / Buka Lebar Sidebar (Ctrl+B)" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <!-- Brand Squircle Logo & Name -->
        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/dashboard" class="brand-squircle-link" title="SIP-OKE : Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi">
            <div class="brand-squircle">
                <?php if ($topbarLogoUrl): ?>
                    <img src="<?= htmlspecialchars($topbarLogoUrl) ?>" alt="" style="width:100%;height:100%;object-fit:contain;padding:5px;border-radius:inherit;">
                <?php else: ?>
                    <i class="fa-solid fa-graduation-cap"></i>
                <?php endif; ?>
            </div>
            <div class="brand-text-wrapper">
                <span class="brand-title">SIP-OKE</span>
                <span class="brand-subtitle-line">Sistem Informasi Pengukuran</span>
                <span class="brand-subtitle-line">Outcome Kompetensi dan Evaluasi</span>
            </div>
        </a>

        <!-- User / Account Pill Profile Dropdown -->
        <div class="user-pill-dropdown-wrapper" id="userPillWrapper">
            <button type="button" class="user-pill-btn" id="userPillBtn" onclick="window.toggleUserDropdown(event)" title="Informasi Akun" aria-label="Akun Pengguna">
                <span class="user-avatar-circle">
                    <i class="fa-regular fa-user"></i>
                </span>
                <span class="user-email-text">
                    <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['email'] ?? 'Administrator') ?>
                </span>
                <span class="user-plan-badge">
                    <?= htmlspecialchars($topbarRoleLabel) ?>
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
                <a href="mailto:<?= htmlspecialchars($topbarUserEmail) ?>" class="user-pill-menu-item" title="Kirim email ke <?= htmlspecialchars($topbarUserEmail) ?>">
                    <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($topbarUserEmail) ?>
                </a>
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

        <!-- Notification Bell & Dropdown (di sebelah kanan profil) -->
        <div class="notif-dropdown-wrapper" id="notifDropdownWrapper">
            <button type="button" class="notif-bell-btn" id="notifBellBtn" onclick="window.toggleNotifDropdown(event)" title="Notifikasi Sistem (<?= $unreadNotifCount ?> Belum Dibaca)" aria-label="Notifikasi">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadNotifCount > 0): ?>
                    <span class="notif-badge-count" id="notifBadgeCount"><?= $unreadNotifCount > 9 ? '9+' : $unreadNotifCount ?></span>
                    <span class="notif-pulse-dot" id="notifPulseDot"></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown-menu" id="notifDropdownMenu" style="display: none;">
                <div class="notif-header">
                    <div class="notif-header-title">
                        <span>Notifikasi</span>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="notif-badge-pill" id="notifPillCount"><?= $unreadNotifCount ?> Baru</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($unreadNotifCount > 0): ?>
                        <button type="button" class="notif-mark-read-btn" id="notifMarkReadBtn" onclick="window.markAllNotifRead(event)" title="Tandai semua sudah dibaca">
                            <i class="fa-solid fa-check-double"></i> Tandai Dibaca
                        </button>
                    <?php endif; ?>
                </div>

                <div class="notif-list-container">
                    <?php if (!empty($recentNotifs)): ?>
                        <?php foreach ($recentNotifs as $n): ?>
                            <a href="<?= !empty($n['tautan']) ? ($config['base_url'] ?? '/obe/public') . $n['tautan'] : 'javascript:void(0)' ?>" class="notif-item <?= empty($n['is_read']) ? 'unread' : '' ?>">
                                <div class="notif-icon-box notif-type-<?= htmlspecialchars($n['tipe'] ?? 'info') ?>">
                                    <i class="<?= htmlspecialchars($n['icon'] ?? 'fa-solid fa-bell') ?>"></i>
                                </div>
                                <div class="notif-content">
                                    <div class="notif-title-row">
                                        <span class="notif-title"><?= htmlspecialchars($n['judul']) ?></span>
                                        <?php if (empty($n['is_read'])): ?>
                                            <span class="notif-unread-dot"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-desc"><?= htmlspecialchars($n['pesan']) ?></div>
                                    <div class="notif-time">
                                        <i class="fa-regular fa-clock" style="font-size: 0.68rem; margin-right: 3px;"></i>
                                        <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notif-empty-state">
                            <i class="fa-regular fa-bell-slash" style="font-size: 1.8rem; opacity: 0.35; margin-bottom: 8px;"></i>
                            <div>Belum ada notifikasi baru</div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (($_SESSION['username'] ?? '') !== 'demo' && empty($_SESSION['hide_sistem_audit'])): ?>
                <div class="notif-footer">
                    <a href="<?= $config['base_url'] ?? '/obe/public' ?>/audit-log" class="notif-footer-link">
                        <span>Lihat Riwayat Log & Aktivitas</span>
                        <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem; margin-left: 4px;"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="obe-topbar-clock" id="obeTopbarClock" aria-label="Tanggal dan waktu saat ini">
            <i class="fa-regular fa-clock" aria-hidden="true"></i>
            <span class="obe-topbar-clock-date" id="obeTopbarClockDate"></span>
            <strong class="obe-topbar-clock-time" id="obeTopbarClockTime"></strong>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Accent Palette Switcher Dropdown (Gradient / Palet) -->
        <div class="palette-dropdown-wrapper" id="paletteDropdownWrapper">
            <button type="button" id="paletteToggleBtn" class="palette-toggle-btn" onclick="window.togglePaletteDropdown(event)" title="Pilih Tema Palet Warna" aria-label="Palette Selector">
                <span class="palette-dot-preview" id="paletteActiveDot" style="background-color: #3368A0;"></span>
                <i class="fa-solid fa-palette" style="font-size: 0.85rem; opacity: 0.85;"></i>
                <span id="paletteActiveName" style="font-size: 0.82rem; font-weight: 600;">Slate Blue</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; opacity: 0.6; margin-left: 2px;"></i>
            </button>
            <div id="paletteDropdownMenu" class="palette-dropdown-menu" style="display: none;">
                <div class="palette-dropdown-header">Warna Aksen</div>
                <div class="palette-options-list">
                    <button type="button" class="palette-option active" data-pal="slate" onclick="window.applyPalette('slate')">
                        <span class="palette-swatch" style="background: #3368A0;"></span>
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
    </div>
</div>
<script>
(function () {
    const dateNode = document.getElementById('obeTopbarClockDate');
    const timeNode = document.getElementById('obeTopbarClockTime');
    if (!dateNode || !timeNode) return;

    function updateObeTopbarClock() {
        const now = new Date();
        dateNode.textContent = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        }).format(now);
        timeNode.textContent = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(now).replace(/\./g, ':');
    }

    updateObeTopbarClock();
    if (window.obeTopbarClockTimer) clearInterval(window.obeTopbarClockTimer);
    window.obeTopbarClockTimer = setInterval(updateObeTopbarClock, 1000);
})();
</script>
