<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = $config['base_url'] ?? '/obe/public';
$sidebarAccess = new AccessContext();
$sidebarRole = $_SESSION['role'] ?? '';
$sidebarIsKaprodi = $sidebarAccess->isKaprodi();
$sidebarAllowed = $sidebarAccess->isSuperAdmin()
    ? ['*']
    : ($sidebarIsKaprodi
        ? ['dashboard','tahun-akademik','dosen','mahasiswa','prodi','kurikulum','aturan-kurikulum','profil-lulusan','cpl','mata-kuliah','rubrik','pemetaan-mk-cpl','pengampu-mk','rps','kelas','asesmen','laporan','laporan-semester','monitoring-cpl-mk','khs','transkrip','rpl','cqi']
        : ($sidebarRole === 'Admin Prodi'
            ? ['dashboard','tahun-akademik','dosen','mahasiswa','prodi','kurikulum','aturan-kurikulum','profil-lulusan','cpl','mata-kuliah','rubrik','import','pemetaan-mk-cpl','rps','kelas','asesmen','laporan','laporan-semester','monitoring-cpl-mk','khs','transkrip','rpl','cqi']
            : ($sidebarRole === 'Dosen'
            ? ['dashboard','rps','rubrik','kelas','asesmen','laporan','laporan-semester','monitoring-cpl-mk','khs','transkrip']
            : ($sidebarRole === 'Mahasiswa' ? ['dashboard','laporan','khs','transkrip','rpl'] : []))));
$sidebarMenuAllowed = static function (string $path) use ($sidebarAllowed): bool {
    return in_array('*', $sidebarAllowed, true) || in_array($path, $sidebarAllowed, true);
};

if (!function_exists('isNavActive')) {
    function isNavActive($path, $currentUri) {
        if (empty($path)) return false;
        $parsed = parse_url($path, PHP_URL_PATH);
        if ($parsed === '/obe/public' || $parsed === '/obe/public/') {
            return $currentUri === $parsed || $currentUri === $parsed . 'dashboard';
        }
        return strpos($currentUri, $parsed) !== false;
    }
}

// Deteksi kategori aktif
$isMasterActive = isNavActive($baseUrl . '/tahun-akademik', $currentUri)
    || isNavActive($baseUrl . '/dosen', $currentUri)
    || isNavActive($baseUrl . '/mahasiswa', $currentUri)
    || isNavActive($baseUrl . '/prodi', $currentUri)
    || isNavActive($baseUrl . '/kurikulum', $currentUri)
    || isNavActive($baseUrl . '/aturan-kurikulum', $currentUri)
    || isNavActive($baseUrl . '/profil-lulusan', $currentUri)
    || isNavActive($baseUrl . '/cpl', $currentUri)
    || isNavActive($baseUrl . '/mata-kuliah', $currentUri)
    || isNavActive($baseUrl . '/rubrik', $currentUri)
    || isNavActive($baseUrl . '/import', $currentUri);

$isPembelajaranActive = isNavActive($baseUrl . '/pemetaan-mk-cpl', $currentUri)
    || isNavActive($baseUrl . '/rps', $currentUri)
    || isNavActive($baseUrl . '/kelas', $currentUri)
    || isNavActive($baseUrl . '/asesmen', $currentUri);

$isLaporanSemesterActive = isNavActive($baseUrl . '/laporan-semester', $currentUri);
$isMonitoringCplMkActive = isNavActive($baseUrl . '/monitoring-cpl-mk', $currentUri);
$isEvaluasiActive = isNavActive($baseUrl . '/laporan', $currentUri)
    || $isLaporanSemesterActive
    || $isMonitoringCplMkActive
    || isNavActive($baseUrl . '/khs', $currentUri)
    || isNavActive($baseUrl . '/transkrip', $currentUri)
    || isNavActive($baseUrl . '/cqi', $currentUri)
    || isNavActive($baseUrl . '/rpl', $currentUri);

$isSistemActive = isNavActive($baseUrl . '/audit-log', $currentUri) || isNavActive($baseUrl . '/user-management', $currentUri) || isNavActive($baseUrl . '/branding', $currentUri);

// Ambil daftar semester dari database secara dinamis
$listTahunAkademik = [];
$activeSemesterNama = '2026/2027 Ganjil';
$activeSemesterId = null;

try {
    require_once __DIR__ . '/../../app/Models/TahunAkademikModel.php';
    $taModel = new TahunAkademikModel();
    $listTahunAkademik = $taModel->getAll();
    foreach ($listTahunAkademik as $ta) {
        if (!empty($ta['is_active'])) {
            $activeSemesterNama = $ta['nama'];
            $activeSemesterId = $ta['id'];
            break;
        }
    }
    if (empty($activeSemesterId) && !empty($listTahunAkademik)) {
        $activeSemesterNama = $listTahunAkademik[0]['nama'];
        $activeSemesterId = $listTahunAkademik[0]['id'];
    }
} catch (Exception $e) {
    $activeSemesterNama = '2026/2027 Ganjil';
}
?>
<?php
$allSidebarPaths = ['tahun-akademik','dosen','mahasiswa','prodi','kurikulum','aturan-kurikulum','profil-lulusan','cpl','mata-kuliah','rubrik','import','pemetaan-mk-cpl','pengampu-mk','rps','kelas','asesmen','laporan','laporan-semester','monitoring-cpl-mk','khs','transkrip','rpl','cqi','audit-log','user-management','branding'];
$hiddenSidebarPaths = array_values(array_filter($allSidebarPaths, fn($item) => !$sidebarMenuAllowed($item)));
?>
<?php if ($hiddenSidebarPaths): ?>
<style>
<?php foreach ($hiddenSidebarPaths as $hiddenPath): ?>
#appSidebar .sidebar-nav-item:has(>a[href$="/<?= $hiddenPath ?>"]),
#appSidebar .sidebar-nav-item:has(>a[href*="/<?= $hiddenPath ?>?"]){display:none!important}
<?php endforeach; ?>
<?php if (!$sidebarAccess->isSuperAdmin()): ?>#appSidebar .sidebar-group[data-group="sistem"]{display:none!important}<?php endif; ?>
</style>
<?php endif; ?>
<aside class="sidebar" id="appSidebar">
    <!-- Mobile Drawer Header (Only visible on mobile/tablet <= 900px) -->
    <div class="sidebar-mobile-header">
        <div class="sidebar-mobile-brand">
            <div class="brand-squircle" style="width: 32px; height: 32px; font-size: 0.9rem;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="brand-text-wrapper">
                <span class="brand-title" style="font-size: 0.95rem;">SIP-OKE</span>
                <span class="brand-subtitle-line" style="font-size: 0.62rem;">Menu Navigasi</span>
            </div>
        </div>
        <button type="button" class="sidebar-mobile-close-btn" id="sidebarMobileCloseBtn" onclick="window.closeMobileSidebar()" title="Tutup Menu (Esc)" aria-label="Tutup Menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Search Bar with '/' Shortcut -->
    <div class="sidebar-search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="sidebarSearchInput" placeholder="Cari menu... (Search)" autocomplete="off" spellcheck="false">
        <span class="sidebar-kbd" title="Tekan '/' untuk mencari">/</span>
    </div>

    <!-- Semester Aktif Dropdown Selector Pill -->
    <div class="sidebar-branch-pill-wrapper" id="branchPillWrapper">
        <button type="button" class="sidebar-branch-pill" id="branchPillBtn" onclick="window.toggleBranchDropdown(event)" title="Semester Aktif: <?= htmlspecialchars($activeSemesterNama) ?>">
            <span class="branch-pill-left">
                <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                <span id="activeSemesterText"><?= htmlspecialchars($activeSemesterNama) ?></span>
            </span>
            <i class="fa-solid fa-chevron-up-down branch-pill-icon"></i>
        </button>
        <div class="branch-dropdown-menu" id="branchDropdownMenu" style="display: none;">
            <div class="branch-dropdown-header">Semester / Tahun Akademik</div>
            <?php if (!empty($listTahunAkademik)): ?>
                <?php foreach ($listTahunAkademik as $ta): ?>
                    <?php $isActive = !empty($ta['is_active']); ?>
                    <button type="button" class="branch-dropdown-item <?= $isActive ? 'active' : '' ?>" onclick="window.switchSemester('<?= htmlspecialchars($ta['nama'], ENT_QUOTES) ?>', <?= (int)$ta['id'] ?>)">
                        <?php if ($isActive): ?>
                            <i class="fa-solid fa-check check-mark"></i>
                        <?php else: ?>
                            <i class="fa-regular fa-circle check-placeholder" style="font-size: 0.55rem; color: var(--text-muted); opacity: 0.5; margin-left: 2px;"></i>
                        <?php endif; ?>
                        <div>
                            <div class="branch-item-name"><?= htmlspecialchars($ta['nama']) ?></div>
                            <div class="branch-item-sub"><?= $isActive ? 'Semester Berjalan (Aktif)' : 'Semester Akademik' ?></div>
                        </div>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="branch-dropdown-item active">
                    <i class="fa-solid fa-check check-mark"></i>
                    <div>
                        <div class="branch-item-name">2026/2027 Ganjil</div>
                        <div class="branch-item-sub">Semester Berjalan (Aktif)</div>
                    </div>
                </div>
            <?php endif; ?>
            <div style="border-top: 1px solid var(--border-subtle); margin-top: 4px; padding-top: 4px;">
                <a href="<?= $baseUrl ?>/tahun-akademik" style="display: flex; align-items: center; gap: 6px; padding: 6px 8px; font-size: 0.78rem; color: var(--primary); text-decoration: none; border-radius: 6px;">
                    <i class="fa-solid fa-sliders" style="font-size: 0.75rem;"></i>
                    <span>Kelola Tahun Akademik</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Action: Toggle Collapse All Categories -->
    <div class="sidebar-action-bar">
        <button type="button" class="sidebar-mini-action" id="toggleAllGroupsBtn" onclick="window.toggleAllSidebarGroups()" title="Ciutkan atau bentangkan semua kelompok menu">
            <i class="fa-solid fa-arrows-up-down"></i>
            <span id="toggleAllGroupsText">Ciutkan Semua</span>
        </button>
    </div>

    <!-- Scrollable Navigation Body -->
    <div class="sidebar-scrollable" id="sidebarScrollable">
        <!-- Section: Menu Utama (Selalu Terbuka) -->
        <div class="sidebar-group always-open expanded" data-group="main">
            <div class="sidebar-section-title">
                <i class="fa-solid fa-compass group-icon"></i>
                <span>MENU UTAMA</span>
            </div>
            <div class="sidebar-group-content">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item <?= (isNavActive($baseUrl . '/dashboard', $currentUri) || $currentUri === $baseUrl || $currentUri === $baseUrl . '/') ? 'active' : '' ?>" data-label="dashboard beranda overview ringkasan statistik">
                        <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link" title="Dashboard">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Section: Master Data (Collapsible Dropdown) -->
        <div class="sidebar-group <?= $isMasterActive ? 'expanded' : '' ?>" data-group="master">
            <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup('master')" title="Klik untuk ciutkan/buka Master Data">
                <span class="sidebar-section-title">
                    <i class="fa-solid fa-database group-icon"></i>
                    <span>MASTER DATA</span>
                </span>
                <span class="sidebar-group-meta">
                    <span class="sidebar-group-badge">11</span>
                    <i class="fa-solid fa-chevron-down group-chevron"></i>
                </span>
            </button>
            <div class="sidebar-group-content">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/tahun-akademik', $currentUri) ? 'active' : '' ?>" data-label="tahun akademik semester periode kalender">
                        <a href="<?= $baseUrl ?>/tahun-akademik" class="sidebar-link" title="Tahun Akademik">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span>Tahun Akademik</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/dosen', $currentUri) ? 'active' : '' ?>" data-label="dosen pengajar tenaga pendidik faculty">
                        <a href="<?= $baseUrl ?>/dosen" class="sidebar-link" title="Dosen">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Dosen</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/mahasiswa', $currentUri) ? 'active' : '' ?>" data-label="mahasiswa student peserta didik nim">
                        <a href="<?= $baseUrl ?>/mahasiswa" class="sidebar-link" title="Mahasiswa">
                            <i class="fa-solid fa-user-graduate"></i>
                            <span>Mahasiswa</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/prodi', $currentUri) ? 'active' : '' ?>" data-label="program studi prodi jurusan fakultas department">
                        <a href="<?= $baseUrl ?>/prodi" class="sidebar-link" title="Program Studi">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Program Studi</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/kurikulum', $currentUri) ? 'active' : '' ?>" data-label="kurikulum struktur sks curriculum">
                        <a href="<?= $baseUrl ?>/kurikulum" class="sidebar-link" title="Kurikulum">
                            <i class="fa-solid fa-book"></i>
                            <span>Kurikulum</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/aturan-kurikulum', $currentUri) ? 'active' : '' ?>" data-label="aturan kurikulum angkatan mapping rule pengaturan">
                        <a href="<?= $baseUrl ?>/aturan-kurikulum" class="sidebar-link" title="Aturan Kurikulum">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Aturan Kurikulum</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/profil-lulusan', $currentUri) ? 'active' : '' ?>" data-label="profil lulusan graduate profile peran profesi pl">
                        <a href="<?= $baseUrl ?>/profil-lulusan" class="sidebar-link" title="Profil Lulusan">
                            <i class="fa-solid fa-id-card-clip"></i>
                            <span>Profil Lulusan</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/cpl', $currentUri) ? 'active' : '' ?>" data-label="cpl capaian pembelajaran lulusan clo plo">
                        <a href="<?= $baseUrl ?>/cpl" class="sidebar-link" title="CPL">
                            <i class="fa-solid fa-bullseye"></i>
                            <span>CPL</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/mata-kuliah', $currentUri) ? 'active' : '' ?>" data-label="mata kuliah matkul course modul">
                        <a href="<?= $baseUrl ?>/mata-kuliah" class="sidebar-link" title="Mata Kuliah">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Mata Kuliah</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/rubrik', $currentUri) ? 'active' : '' ?>" data-label="rubrik penilaian kriteria grading scoring">
                        <a href="<?= $baseUrl ?>/rubrik" class="sidebar-link" title="Rubrik Penilaian">
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Rubrik Penilaian</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/import', $currentUri) ? 'active' : '' ?>" data-label="import data excel migrasi kurikulum">
                        <a href="<?= $baseUrl ?>/import" class="sidebar-link" title="Import Data">
                            <i class="fa-solid fa-file-import"></i>
                            <span>Import Data</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Section: Pembelajaran (Collapsible Dropdown) -->
        <div class="sidebar-group <?= $isPembelajaranActive ? 'expanded' : '' ?>" data-group="pembelajaran">
            <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup('pembelajaran')" title="Klik untuk ciutkan/buka Pembelajaran">
                <span class="sidebar-section-title">
                    <i class="fa-solid fa-book-open-reader group-icon"></i>
                    <span>PEMBELAJARAN</span>
                </span>
                <span class="sidebar-group-meta">
                    <span class="sidebar-group-badge"><?= in_array($_SESSION['role'] ?? '', ['Super Admin', 'Admin Prodi', 'Dosen', 'Kaprodi'], true) ? '5' : '4' ?></span>
                    <i class="fa-solid fa-chevron-down group-chevron"></i>
                </span>
            </button>
            <div class="sidebar-group-content">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/pemetaan-mk-cpl', $currentUri) ? 'active' : '' ?>" data-label="pemetaan mk cpl matrix mapping matriks relasi">
                        <a href="<?= $baseUrl ?>/pemetaan-mk-cpl" class="sidebar-link" title="Pemetaan MK-CPL">
                            <i class="fa-solid fa-link"></i>
                            <span>Pemetaan MK-CPL</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/pengampu-mk', $currentUri) ? 'active' : '' ?>" data-label="pengampu mata kuliah pjmk tim teaching dosen">
                        <a href="<?= $baseUrl ?>/pengampu-mk" class="sidebar-link" title="Pengampu Mata Kuliah">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Pengampu Mata Kuliah</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/rps', $currentUri) ? 'active' : '' ?>" data-label="rps rencana pembelajaran semester silabus cpmk">
                        <a href="<?= $baseUrl ?>/rps" class="sidebar-link" title="RPS">
                            <i class="fa-solid fa-file-contract"></i>
                            <span>RPS</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/kelas', $currentUri) ? 'active' : '' ?>" data-label="kelas rombel rombongan belajar peserta course class">
                        <a href="<?= $baseUrl ?>/kelas" class="sidebar-link" title="Kelas Perkuliahan">
                            <i class="fa-solid fa-users-rectangle"></i>
                            <span>Kelas</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/asesmen', $currentUri) ? 'active' : '' ?>" data-label="asesmen penilaian nilai cpmk evaluasi monitoring">
                        <a href="<?= $baseUrl ?>/asesmen" class="sidebar-link" title="Asesmen & Penilaian">
                            <i class="fa-solid fa-check-to-slot"></i>
                            <span>Asesmen & Penilaian</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Section: Capaian & Evaluasi (Collapsible Dropdown) -->
        <div class="sidebar-group <?= $isEvaluasiActive ? 'expanded' : '' ?>" data-group="evaluasi">
            <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup('evaluasi')" title="Klik untuk ciutkan/buka Capaian & Evaluasi">
                <span class="sidebar-section-title">
                    <i class="fa-solid fa-chart-pie group-icon"></i>
                    <span>CAPAIAN & EVALUASI</span>
                </span>
                <span class="sidebar-group-meta">
                    <span class="sidebar-group-badge"><?= in_array($_SESSION['role'] ?? '', ['Super Admin', 'Admin Prodi', 'Dosen', 'Kaprodi'], true) ? '6' : '4' ?></span>
                    <i class="fa-solid fa-chevron-down group-chevron"></i>
                </span>
            </button>
            <div class="sidebar-group-content">
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/laporan', $currentUri) && !$isLaporanSemesterActive && !$isMonitoringCplMkActive ? 'active' : '' ?>" data-label="laporan cpl spider chart radar ketercapaian rekap">
                        <a href="<?= $baseUrl ?>/laporan" class="sidebar-link" title="Laporan CPL">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Laporan CPL</span>
                        </a>
                    </li>
                    <?php if (in_array($_SESSION['role'] ?? '', ['Super Admin', 'Admin Prodi', 'Dosen', 'Kaprodi'], true)): ?>
                    <li class="sidebar-nav-item <?= $isLaporanSemesterActive ? 'active' : '' ?>" data-label="laporan semester periode pengukuran cpl">
                        <a href="<?= $baseUrl ?>/laporan-semester" class="sidebar-link" title="Laporan Pengukuran Semester">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Laporan Semester</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= $isMonitoringCplMkActive ? 'active' : '' ?>" data-label="monitoring cpl mata kuliah mk ketercapaian capaian mahasiswa">
                        <a href="<?= $baseUrl ?>/monitoring-cpl-mk" class="sidebar-link" title="Monitoring CPL per Mata Kuliah">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Monitoring CPL MK</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/khs', $currentUri) ? 'active' : '' ?>" data-label="khs kartu hasil studi ipk ips portofolio cpl nilai mahasiswa">
                        <a href="<?= $baseUrl ?>/khs" class="sidebar-link" title="KHS & Portofolio CPL">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>KHS & Portofolio CPL</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/transkrip', $currentUri) ? 'active' : '' ?>" data-label="transkrip akademik cetak nilai ijazah kelulusan skpi cpl capaian mahasiswa">
                        <a href="<?= $baseUrl ?>/transkrip" class="sidebar-link" title="Transkrip Mahasiswa & Capaian CPL">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>Transkrip Mahasiswa</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/rpl', $currentUri) ? 'active' : '' ?>" data-label="rpl rekognisi pembelajaran lampau konversi sks mahasiswa">
                        <a href="<?= $baseUrl ?>/rpl" class="sidebar-link" title="Konversi SKS RPL">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Konversi SKS RPL</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/cqi', $currentUri) ? 'active' : '' ?>" data-label="cqi tindak lanjut perbaikan mutu continuous quality improvement">
                        <a href="<?= $baseUrl ?>/cqi" class="sidebar-link" title="Tindak Lanjut CQI">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Tindak Lanjut CQI</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Section: Sistem & Audit (Collapsible Dropdown) -->
        <div class="sidebar-group <?= $isSistemActive ? 'expanded' : '' ?>" data-group="sistem">
            <button type="button" class="sidebar-group-header" onclick="window.toggleSidebarGroup('sistem')" title="Klik untuk ciutkan/buka Sistem & Audit">
                <span class="sidebar-section-title">
                    <i class="fa-solid fa-shield-halved group-icon"></i>
                    <span>SISTEM & AUDIT</span>
                </span>
                <span class="sidebar-group-meta">
                    <span class="sidebar-group-badge"><?= ($_SESSION['role']??'')==='Super Admin'?'3':'1' ?></span>
                    <i class="fa-solid fa-chevron-down group-chevron"></i>
                </span>
            </button>
            <div class="sidebar-group-content">
                <ul class="sidebar-nav">
                    <?php if (($_SESSION['role']??'')==='Super Admin'): ?><li class="sidebar-nav-item <?= isNavActive($baseUrl.'/user-management',$currentUri)?'active':'' ?>" data-label="manajemen user akun login role password"><a href="<?= $baseUrl ?>/user-management" class="sidebar-link" title="Manajemen User"><i class="fa-solid fa-users-gear"></i><span>Manajemen User</span></a></li><?php endif; ?>
                    <?php if (($_SESSION['role']??'')==='Super Admin'): ?><li class="sidebar-nav-item <?= isNavActive($baseUrl.'/branding',$currentUri)?'active':'' ?>" data-label="logo favicon identitas branding aplikasi"><a href="<?= $baseUrl ?>/branding" class="sidebar-link" title="Logo & Favicon"><i class="fa-solid fa-icons"></i><span>Logo &amp; Favicon</span></a></li><?php endif; ?>
                    <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/audit-log', $currentUri) ? 'active' : '' ?>" data-label="audit log trail riwayat aktivitas keamanan sistem">
                        <a href="<?= $baseUrl ?>/audit-log" class="sidebar-link" title="Audit Log">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Audit Log</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar Bottom Footer: Trigger Ciutkan Lebar Sidebar -->
    <div class="sidebar-footer">
        <button type="button" class="sidebar-footer-collapse-btn" id="sidebarFooterCollapseBtn" onclick="window.toggleSidebarCollapse()" title="Ciutkan / Buka Sidebar (Ctrl+B)">
            <i class="fa-solid fa-angles-left collapse-icon" id="sidebarFooterIcon"></i>
            <span class="collapse-label" id="sidebarFooterLabel">Ciutkan Sidebar</span>
        </button>
    </div>
</aside>

<!-- Client-side Interactive Search, Dropdown & Collapse Logic -->
<script>
(function() {
    const searchInput = document.getElementById('sidebarSearchInput');
    const navItems = document.querySelectorAll('.sidebar-nav-item');
    const groups = document.querySelectorAll('.sidebar-group');

    // 1. Live Search Filter & Otomatis Buka Dropdown saat Mencari
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = (e.target.value || '').toLowerCase().trim();
            
            navItems.forEach(item => {
                const text = (item.textContent || '').toLowerCase();
                const label = item.getAttribute('data-label') || '';
                const match = query === '' || text.includes(query) || label.includes(query);
                item.style.display = match ? '' : 'none';
            });

            // Handle visibility and expansion of groups
            groups.forEach(group => {
                const visibleItems = group.querySelectorAll('.sidebar-nav-item:not([style*="display: none"])');
                const header = group.querySelector('.sidebar-group-header') || group.querySelector('.sidebar-section-title');
                
                if (query !== '') {
                    if (visibleItems.length === 0) {
                        group.style.display = 'none';
                    } else {
                        group.style.display = '';
                        // Otomatis buka accordion jika ada hasil yang cocok
                        group.classList.add('expanded');
                    }
                } else {
                    group.style.display = '';
                    // Pulihkan status tersimpan saat input pencarian kosong
                    restoreGroupStates();
                }
            });
            updateToggleAllLabel();
        });

        // Keyboard shortcut: tekan '/' untuk fokus ke kotak pencarian
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement !== searchInput) {
                const tag = (document.activeElement && document.activeElement.tagName) || '';
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && !document.activeElement.isContentEditable) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
            }
            // Shortcut Ctrl+B untuk ciutkan/buka lebar sidebar
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                window.toggleSidebarCollapse();
            }
        });
    }

    // 2. Toggle Dropdown / Accordion per Kategori Menu
    window.toggleSidebarGroup = function(groupId) {
        const group = document.querySelector(`.sidebar-group[data-group="${groupId}"]`);
        if (!group) return;
        
        group.classList.toggle('expanded');
        saveGroupStates();
        updateToggleAllLabel();
    };

    // 3. Toggle Ciutkan / Bentangkan Semua Kategori Sekaligus
    window.toggleAllSidebarGroups = function() {
        const collapsibles = document.querySelectorAll('.sidebar-group:not(.always-open)');
        const anyExpanded = Array.from(collapsibles).some(g => g.classList.contains('expanded'));
        
        collapsibles.forEach(g => {
            if (anyExpanded) {
                g.classList.remove('expanded');
            } else {
                g.classList.add('expanded');
            }
        });
        
        saveGroupStates();
        updateToggleAllLabel();
    };

    function updateToggleAllLabel() {
        const collapsibles = document.querySelectorAll('.sidebar-group:not(.always-open)');
        const anyExpanded = Array.from(collapsibles).some(g => g.classList.contains('expanded'));
        const label = document.getElementById('toggleAllGroupsText');
        if (label) {
            label.textContent = anyExpanded ? 'Ciutkan Semua' : 'Bentangkan Semua';
        }
    }

    function saveGroupStates() {
        try {
            const states = {};
            document.querySelectorAll('.sidebar-group[data-group]:not(.always-open)').forEach(g => {
                const id = g.getAttribute('data-group');
                states[id] = g.classList.contains('expanded');
            });
            localStorage.setItem('sidebar_group_states', JSON.stringify(states));
        } catch(e) {}
    }

    function restoreGroupStates() {
        try {
            const saved = localStorage.getItem('sidebar_group_states');
            if (saved) {
                const states = JSON.parse(saved);
                document.querySelectorAll('.sidebar-group[data-group]:not(.always-open)').forEach(g => {
                    const id = g.getAttribute('data-group');
                    // Jangan ciutkan group jika di dalamnya ada item aktif
                    const hasActive = g.querySelector('.sidebar-nav-item.active') !== null;
                    if (hasActive) {
                        g.classList.add('expanded');
                    } else if (states[id] !== undefined) {
                        if (states[id]) {
                            g.classList.add('expanded');
                        } else {
                            g.classList.remove('expanded');
                        }
                    }
                });
            }
        } catch(e) {}
    }

    // Inisialisasi status group dari localStorage
    restoreGroupStates();
    updateToggleAllLabel();

    // Mobile Drawer Open / Close Functions
    window.openMobileSidebar = function() {
        document.documentElement.classList.add('sidebar-mobile-open');
        document.body.style.overflow = 'hidden';
    };

    window.closeMobileSidebar = function() {
        document.documentElement.classList.remove('sidebar-mobile-open');
        document.body.style.overflow = '';
    };

    // 4. Global Sidebar Mini/Collapse Mode & Mobile Drawer
    window.toggleSidebarCollapse = function() {
        if (window.innerWidth <= 900) {
            if (document.documentElement.classList.contains('sidebar-mobile-open')) {
                window.closeMobileSidebar();
            } else {
                window.openMobileSidebar();
            }
            return;
        }

        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
        const newState = !isCollapsed;

        if (newState) {
            document.documentElement.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', 'true');
        } else {
            document.documentElement.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', 'false');
        }

        updateSidebarCollapseUI(newState);
        window.dispatchEvent(new Event('resize'));
    };

    function updateSidebarCollapseUI(collapsed) {
        const footerIcon = document.getElementById('sidebarFooterIcon');
        const footerLabel = document.getElementById('sidebarFooterLabel');
        const topbarBtn = document.getElementById('sidebarToggleBtn');

        if (footerIcon) {
            footerIcon.className = collapsed ? 'fa-solid fa-angles-right collapse-icon' : 'fa-solid fa-angles-left collapse-icon';
        }
        if (footerLabel) {
            footerLabel.textContent = collapsed ? 'Lebarkan' : 'Ciutkan Sidebar';
        }
        if (topbarBtn) {
            topbarBtn.setAttribute('title', collapsed ? 'Buka Lebar Sidebar (Ctrl+B)' : 'Ciutkan Sidebar (Ctrl+B)');
        }
    }

    // Inisialisasi ikon collapse awal
    const initCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
    updateSidebarCollapseUI(initCollapsed);

    // 5. Semester Dropdown Selector
    window.toggleBranchDropdown = function(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('branchDropdownMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    };

    window.switchSemester = function(semesterNama, semesterId) {
        const textEl = document.getElementById('activeSemesterText');
        if (textEl) textEl.textContent = semesterNama;
        localStorage.setItem('active_semester', semesterNama);

        const items = document.querySelectorAll('.branch-dropdown-item');
        items.forEach(btn => {
            const isTarget = btn.querySelector('.branch-item-name')?.textContent.trim() === semesterNama;
            if (isTarget) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const menu = document.getElementById('branchDropdownMenu');
        if (menu) menu.style.display = 'none';
    };

    // 6. User Pill Dropdown
    window.toggleUserDropdown = function(e) {
        if (e) e.stopPropagation();
        const notifMenu = document.getElementById('notifDropdownMenu');
        if (notifMenu) notifMenu.style.display = 'none';
        const palMenu = document.getElementById('paletteDropdownMenu');
        if (palMenu) palMenu.style.display = 'none';

        const menu = document.getElementById('userPillMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    };

    // 7. Notification Bell Dropdown
    window.toggleNotifDropdown = function(e) {
        if (e) e.stopPropagation();
        const userMenu = document.getElementById('userPillMenu');
        if (userMenu) userMenu.style.display = 'none';
        const palMenu = document.getElementById('paletteDropdownMenu');
        if (palMenu) palMenu.style.display = 'none';

        const notifMenu = document.getElementById('notifDropdownMenu');
        if (notifMenu) {
            notifMenu.style.display = (notifMenu.style.display === 'none' || notifMenu.style.display === '') ? 'block' : 'none';
        }
    };

    // 8. Tandai Semua Notifikasi Dibaca
    window.markAllNotifRead = function(e) {
        if (e) e.stopPropagation();
        const badge = document.getElementById('notifBadgeCount');
        const pulse = document.getElementById('notifPulseDot');
        const pill = document.getElementById('notifPillCount');
        const markBtn = document.getElementById('notifMarkReadBtn');
        const unreadDots = document.querySelectorAll('.notif-unread-dot');
        const unreadItems = document.querySelectorAll('.notif-item.unread');

        if (badge) badge.style.display = 'none';
        if (pulse) pulse.style.display = 'none';
        if (pill) pill.style.display = 'none';
        if (markBtn) markBtn.style.display = 'none';
        unreadDots.forEach(function(dot) { dot.style.display = 'none'; });
        unreadItems.forEach(function(item) { item.classList.remove('unread'); });

        const baseUrl = '<?= $config['base_url'] ?? '/obe/public' ?>';
        fetch(baseUrl + '/notifikasi/mark-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>',
                'Content-Type': 'application/json'
            }
        }).catch(function(err) {
            console.log('Error marking notifications read:', err);
        });
    };

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        const branchWrapper = document.getElementById('branchPillWrapper');
        const branchMenu = document.getElementById('branchDropdownMenu');
        if (branchWrapper && branchMenu && !branchWrapper.contains(e.target)) {
            branchMenu.style.display = 'none';
        }

        const userWrapper = document.getElementById('userPillWrapper');
        const userMenu = document.getElementById('userPillMenu');
        if (userWrapper && userMenu && !userWrapper.contains(e.target)) {
            userMenu.style.display = 'none';
        }

        const notifWrapper = document.getElementById('notifDropdownWrapper');
        const notifMenu = document.getElementById('notifDropdownMenu');
        if (notifWrapper && notifMenu && !notifWrapper.contains(e.target)) {
            notifMenu.style.display = 'none';
        }
    });

    // Otomatis tutup sidebar mobile saat navigasi menu diklik
    document.querySelectorAll('#appSidebar a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 900) {
                window.closeMobileSidebar();
            }
        });
    });

    // Tutup mobile drawer dengan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.documentElement.classList.contains('sidebar-mobile-open')) {
            window.closeMobileSidebar();
        }
    });

    // Reset overflow saat resize ke layar desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 900 && document.documentElement.classList.contains('sidebar-mobile-open')) {
            window.closeMobileSidebar();
        }
    });

    // Inisialisasi semester tersimpan dari localStorage jika ada
    const savedSemester = localStorage.getItem('active_semester');
    if (savedSemester) {
        const textEl = document.getElementById('activeSemesterText');
        if (textEl) textEl.textContent = savedSemester;
    }
})();
</script>
