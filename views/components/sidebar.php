<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = $config['base_url'] ?? '/obe/public';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-graduation-cap"></i> OBE & CPL
    </div>
    <ul class="sidebar-menu">
        <li class="<?= strpos($currentUri, '/dashboard') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/dashboard"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        </li>
        
        <li style="padding: 10px 20px; font-size: 0.8rem; color: #64748b; margin-top: 10px; letter-spacing: 0.05em;">MASTER DATA</li>
        <li class="<?= strpos($currentUri, '/tahun-akademik') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/tahun-akademik"><i class="fa-solid fa-calendar-days"></i> Tahun Akademik</a>
        </li>
        <li class="<?= strpos($currentUri, '/dosen') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/dosen"><i class="fa-solid fa-chalkboard-user"></i> Dosen</a>
        </li>
        <li class="<?= strpos($currentUri, '/mahasiswa') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/mahasiswa"><i class="fa-solid fa-user-graduate"></i> Mahasiswa</a>
        </li>
        <li class="<?= strpos($currentUri, '/kurikulum') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/kurikulum"><i class="fa-solid fa-book"></i> Kurikulum</a>
        </li>
        <li class="<?= strpos($currentUri, '/cpl') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/cpl"><i class="fa-solid fa-bullseye"></i> CPL</a>
        </li>
        <li class="<?= strpos($currentUri, '/mata-kuliah') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/mata-kuliah"><i class="fa-solid fa-book-open"></i> Mata Kuliah</a>
        </li>
        <li class="<?= strpos($currentUri, '/rubrik') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/rubrik"><i class="fa-solid fa-table-cells"></i> Rubrik Penilaian</a>
        </li>
        <li class="<?= strpos($currentUri, '/import') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/import"><i class="fa-solid fa-file-import"></i> Import Data</a>
        </li>
        
        <li style="padding: 10px 20px; font-size: 0.8rem; color: #64748b; margin-top: 10px; letter-spacing: 0.05em;">PEMBELAJARAN</li>
        <li class="<?= strpos($currentUri, '/pemetaan-mk-cpl') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/pemetaan-mk-cpl"><i class="fa-solid fa-link"></i> Pemetaan MK-CPL</a>
        </li>
        <li class="<?= strpos($currentUri, '/rps') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/rps"><i class="fa-solid fa-file-contract"></i> RPS</a>
        </li>
        <li class="<?= strpos($currentUri, '/kelas') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/kelas"><i class="fa-solid fa-users-rectangle"></i> Kelas</a>
        </li>
        <li class="<?= strpos($currentUri, '/asesmen') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/asesmen"><i class="fa-solid fa-check-to-slot"></i> Asesmen & Penilaian</a>
        </li>

        <li style="padding: 10px 20px; font-size: 0.8rem; color: #64748b; margin-top: 10px; letter-spacing: 0.05em;">CAPAIAN & EVALUASI</li>
        <li class="<?= strpos($currentUri, '/laporan') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/laporan"><i class="fa-solid fa-chart-pie"></i> Laporan CPL</a>
        </li>
        <li class="<?= strpos($currentUri, '/cqi') !== false ? 'active' : '' ?>">
            <a href="<?= $baseUrl ?>/cqi"><i class="fa-solid fa-arrows-rotate"></i> Tindak Lanjut CQI</a>
        </li>
    </ul>
</div>
