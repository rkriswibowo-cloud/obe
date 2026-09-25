<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = $config['base_url'] ?? '/obe/public';

function isNavActive($path, $currentUri) {
    if (empty($path)) return false;
    $parsed = parse_url($path, PHP_URL_PATH);
    if ($parsed === '/obe/public' || $parsed === '/obe/public/') {
        return $currentUri === $parsed || $currentUri === $parsed . 'dashboard';
    }
    return strpos($currentUri, $parsed) !== false;
}

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
<aside class="sidebar" id="appSidebar">
    <!-- Search Bar with '/' Shortcut (Model Screenshot) -->
    <div class="sidebar-search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="sidebarSearchInput" placeholder="Cari menu... (Search)" autocomplete="off" spellcheck="false">
        <span class="sidebar-kbd" title="Tekan '/' untuk mencari">/</span>
    </div>

    <!-- Semester Aktif Dropdown Selector Pill (Model Screenshot) -->
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

    <div class="sidebar-scrollable" id="sidebarScrollable">
        <!-- Section: Menu Utama -->
        <div class="sidebar-group" data-group="main">
            <div class="sidebar-section-title">MENU UTAMA</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item <?= (isNavActive($baseUrl . '/dashboard', $currentUri) || $currentUri === $baseUrl || $currentUri === $baseUrl . '/') ? 'active' : '' ?>" data-label="dashboard beranda overview ringkasan statistik">
                    <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section: Master Data -->
        <div class="sidebar-group" data-group="master">
            <div class="sidebar-section-title">MASTER DATA</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/tahun-akademik', $currentUri) ? 'active' : '' ?>" data-label="tahun akademik semester periode kalender">
                    <a href="<?= $baseUrl ?>/tahun-akademik" class="sidebar-link">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Tahun Akademik</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/dosen', $currentUri) ? 'active' : '' ?>" data-label="dosen pengajar tenaga pendidik faculty">
                    <a href="<?= $baseUrl ?>/dosen" class="sidebar-link">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Dosen</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/mahasiswa', $currentUri) ? 'active' : '' ?>" data-label="mahasiswa student peserta didik nim">
                    <a href="<?= $baseUrl ?>/mahasiswa" class="sidebar-link">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Mahasiswa</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/kurikulum', $currentUri) ? 'active' : '' ?>" data-label="kurikulum struktur sks curriculum">
                    <a href="<?= $baseUrl ?>/kurikulum" class="sidebar-link">
                        <i class="fa-solid fa-book"></i>
                        <span>Kurikulum</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/profil-lulusan', $currentUri) ? 'active' : '' ?>" data-label="profil lulusan graduate profile peran profesi pl">
                    <a href="<?= $baseUrl ?>/profil-lulusan" class="sidebar-link">
                        <i class="fa-solid fa-id-card-clip"></i>
                        <span>Profil Lulusan</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/cpl', $currentUri) ? 'active' : '' ?>" data-label="cpl capaian pembelajaran lulusan clo plo">
                    <a href="<?= $baseUrl ?>/cpl" class="sidebar-link">
                        <i class="fa-solid fa-bullseye"></i>
                        <span>CPL</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/mata-kuliah', $currentUri) ? 'active' : '' ?>" data-label="mata kuliah matkul course modul">
                    <a href="<?= $baseUrl ?>/mata-kuliah" class="sidebar-link">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Mata Kuliah</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/rubrik', $currentUri) ? 'active' : '' ?>" data-label="rubrik penilaian kriteria grading scoring">
                    <a href="<?= $baseUrl ?>/rubrik" class="sidebar-link">
                        <i class="fa-solid fa-table-cells"></i>
                        <span>Rubrik Penilaian</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/import', $currentUri) ? 'active' : '' ?>" data-label="import data excel migrasi kurikulum">
                    <a href="<?= $baseUrl ?>/import" class="sidebar-link">
                        <i class="fa-solid fa-file-import"></i>
                        <span>Import Data</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section: Pembelajaran -->
        <div class="sidebar-group" data-group="pembelajaran">
            <div class="sidebar-section-title">PEMBELAJARAN</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/pemetaan-mk-cpl', $currentUri) ? 'active' : '' ?>" data-label="pemetaan mk cpl matrix mapping matriks relasi">
                    <a href="<?= $baseUrl ?>/pemetaan-mk-cpl" class="sidebar-link">
                        <i class="fa-solid fa-link"></i>
                        <span>Pemetaan MK-CPL</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/rps', $currentUri) ? 'active' : '' ?>" data-label="rps rencana pembelajaran semester silabus cpmk">
                    <a href="<?= $baseUrl ?>/rps" class="sidebar-link">
                        <i class="fa-solid fa-file-contract"></i>
                        <span>RPS</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/kelas', $currentUri) ? 'active' : '' ?>" data-label="kelas rombel rombongan belajar peserta course class">
                    <a href="<?= $baseUrl ?>/kelas" class="sidebar-link">
                        <i class="fa-solid fa-users-rectangle"></i>
                        <span>Kelas</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/asesmen', $currentUri) ? 'active' : '' ?>" data-label="asesmen penilaian nilai cpmk evaluasi monitoring">
                    <a href="<?= $baseUrl ?>/asesmen" class="sidebar-link">
                        <i class="fa-solid fa-check-to-slot"></i>
                        <span>Asesmen & Penilaian</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section: Capaian & Evaluasi -->
        <div class="sidebar-group" data-group="evaluasi">
            <div class="sidebar-section-title">CAPAIAN & EVALUASI</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/laporan', $currentUri) ? 'active' : '' ?>" data-label="laporan cpl spider chart radar ketercapaian rekap">
                    <a href="<?= $baseUrl ?>/laporan" class="sidebar-link">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Laporan CPL</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/khs', $currentUri) ? 'active' : '' ?>" data-label="khs kartu hasil studi ipk ips portofolio cpl nilai mahasiswa transcript">
                    <a href="<?= $baseUrl ?>/khs" class="sidebar-link">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>KHS & Portofolio CPL</span>
                    </a>
                </li>
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/cqi', $currentUri) ? 'active' : '' ?>" data-label="cqi tindak lanjut perbaikan mutu continuous quality improvement">
                    <a href="<?= $baseUrl ?>/cqi" class="sidebar-link">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Tindak Lanjut CQI</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section: Sistem & Audit -->
        <div class="sidebar-group" data-group="sistem">
            <div class="sidebar-section-title">SISTEM & AUDIT</div>
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item <?= isNavActive($baseUrl . '/audit-log', $currentUri) ? 'active' : '' ?>" data-label="audit log trail riwayat aktivitas keamanan sistem">
                    <a href="<?= $baseUrl ?>/audit-log" class="sidebar-link">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Audit Log</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>

<!-- Client-side Interactive Search & Semester Logic -->
<script>
(function() {
    const searchInput = document.getElementById('sidebarSearchInput');
    const navItems = document.querySelectorAll('.sidebar-nav-item');
    const groups = document.querySelectorAll('.sidebar-group');

    // Live search filter
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = (e.target.value || '').toLowerCase().trim();
            
            navItems.forEach(item => {
                const text = (item.textContent || '').toLowerCase();
                const label = item.getAttribute('data-label') || '';
                const match = query === '' || text.includes(query) || label.includes(query);
                item.style.display = match ? '' : 'none';
            });

            // Sembunyikan section title jika semua item dalam group tersembunyi
            groups.forEach(group => {
                const visibleItems = group.querySelectorAll('.sidebar-nav-item:not([style*="display: none"])');
                const title = group.querySelector('.sidebar-section-title');
                if (visibleItems.length === 0 && query !== '') {
                    if (title) title.style.display = 'none';
                } else {
                    if (title) title.style.display = '';
                }
            });
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
        });
    }

    // Semester Dropdown Selector
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

    // User Pill Dropdown
    window.toggleUserDropdown = function(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('userPillMenu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
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
    });

    // Inisialisasi semester tersimpan dari localStorage jika ada
    const savedSemester = localStorage.getItem('active_semester');
    if (savedSemester) {
        const textEl = document.getElementById('activeSemesterText');
        if (textEl) textEl.textContent = savedSemester;
    }
})();
</script>
