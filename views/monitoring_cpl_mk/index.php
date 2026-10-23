<?php
// views/monitoring_cpl_mk/index.php
$title = "Monitoring Ketercapaian CPL per Mata Kuliah";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';

$hasFilter = !empty($selectedTaId) || !empty($selectedKurikulumId) || !empty($selectedProdiId) || $statusFilter !== '' || $search !== '';
?>

<div class="cpl-mk-monitoring-page">
    <!-- Header Banner -->
    <div class="monitoring-header">
        <div class="header-left">
            <div class="badge-role-tag">
                <i class="fa-solid fa-award"></i> Penjaminan Mutu & Ketercapaian OBE
            </div>
            <h2>
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                Monitoring Ketercapaian CPL per Mata Kuliah
            </h2>
            <p>
                Pantau keselarasan asesmen mata kuliah terhadap Capaian Pembelajaran Lulusan (CPL) dan cek daftar mahasiswa pengambil MK beserta status kelulusan CPL-nya.
            </p>
        </div>
        <div class="header-right">
            <span class="indicator-pill">
                <i class="fa-solid fa-bullseye"></i> Standar Target: ≥ 70%
            </span>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8;">
                <i class="fa-solid fa-books"></i>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Mata Kuliah</span>
                <div class="kpi-number"><?= number_format($summary['total_courses']) ?></div>
                <small class="kpi-sub">Master Kurikulum Terdaftar</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">MK Terukur Asesmen</span>
                <div class="kpi-number text-success">
                    <?= number_format($summary['measured_courses']) ?>
                    <span class="kpi-badge"><?= $summary['total_courses'] > 0 ? round(($summary['measured_courses'] / $summary['total_courses']) * 100, 1) : 0 ?>%</span>
                </div>
                <small class="kpi-sub"><?= $summary['tercapai_courses'] ?> MK Memenuhi Target CPL</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Mahasiswa Pengambil MK</span>
                <div class="kpi-number text-primary"><?= number_format($summary['total_students']) ?></div>
                <small class="kpi-sub"><?= number_format($summary['tercapai_students']) ?> Mahasiswa CPL Tercapai</small>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Rata-rata Ketercapaian CPL</span>
                <div class="kpi-number text-warning"><?= number_format($summary['avg_attainment'], 1) ?>%</div>
                <small class="kpi-sub">Agregasi Seluruh Asesmen Sub-CPMK</small>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <form method="GET" action="<?= $baseUrl ?>/monitoring-cpl-mk" id="filterForm">
            <div class="filter-grid">
                <!-- Filter Tahun Akademik / Semester -->
                <div class="filter-col">
                    <label for="filter_ta"><i class="fa-solid fa-calendar-days"></i> Tahun Akademik / Periode</label>
                    <select name="tahun_akademik_id" id="filter_ta" onchange="this.form.submit()">
                        <option value="">-- Semua Periode Akademik --</option>
                        <?php foreach ($tahunAkademik as $ta): ?>
                            <option value="<?= (int)$ta['id'] ?>" <?= $selectedTaId === (int)$ta['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ta['nama']) ?><?= !empty($ta['is_active']) ? ' (Aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kurikulum -->
                <div class="filter-col">
                    <label for="filter_kurikulum"><i class="fa-solid fa-book"></i> Kurikulum</label>
                    <select name="kurikulum_id" id="filter_kurikulum" onchange="this.form.submit()">
                        <option value="">-- Semua Kurikulum --</option>
                        <?php foreach ($kurikulumList as $k): ?>
                            <option value="<?= (int)$k['id'] ?>" <?= $selectedKurikulumId === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['prodi_nama']) ?> — <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Status Ketercapaian -->
                <div class="filter-col">
                    <label for="filter_status"><i class="fa-solid fa-sliders"></i> Status Ketercapaian</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="tercapai" <?= $statusFilter === 'tercapai' ? 'selected' : '' ?>>Tercapai (≥ 80%)</option>
                        <option value="sebagian" <?= $statusFilter === 'sebagian' ? 'selected' : '' ?>>Sebagian Tercapai (50% - 79%)</option>
                        <option value="belum_tercapai" <?= $statusFilter === 'belum_tercapai' ? 'selected' : '' ?>>Belum Tercapai (&lt; 50%)</option>
                        <option value="belum_dinilai" <?= $statusFilter === 'belum_dinilai' ? 'selected' : '' ?>>Belum Ada Nilai Asesmen</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="filter-col search-col">
                    <label for="filter_search"><i class="fa-solid fa-magnifying-glass"></i> Cari Mata Kuliah</label>
                    <div class="search-input-wrap">
                        <input type="text" name="search" id="filter_search" placeholder="Ketik kode atau nama MK..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <?php if ($search !== ''): ?>
                            <a href="<?= $baseUrl ?>/monitoring-cpl-mk<?= $selectedTaId ? '?tahun_akademik_id=' . $selectedTaId : '' ?>" class="clear-search-btn" title="Hapus pencarian">&times;</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
                <?php if ($hasFilter): ?>
                    <a href="<?= $baseUrl ?>/monitoring-cpl-mk" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filter
                    </a>
                <?php endif; ?>
                <span class="filter-count-note">
                    Menampilkan <strong><?= count($courses) ?></strong> dari <?= $summary['total_courses'] ?> Mata Kuliah
                </span>
            </div>
        </form>
    </div>

    <!-- Table of Courses -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-title">
                <i class="fa-solid fa-table-list" style="color: var(--primary);"></i>
                <span>Daftar Mata Kuliah & Ketercapaian CPL Mahasiswa</span>
            </div>
            <div class="table-meta">
                <span class="hint-text"><i class="fa-solid fa-hand-pointer"></i> Klik baris MK untuk melihat daftar mahasiswa & CPL tercapai</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="monitoring-table" id="courseMonitoringTable">
                <thead>
                    <tr>
                        <th style="width: 48px; text-align: center;">No</th>
                        <th>Kode & Mata Kuliah</th>
                        <th style="width: 100px; text-align: center;">SKS / Sem</th>
                        <th>Program Studi & Kurikulum</th>
                        <th>CPL yang Dibebankan</th>
                        <th style="width: 120px; text-align: center;">Mahasiswa Pengambil</th>
                        <th style="min-width: 200px;">Ketercapaian Mahasiswa</th>
                        <th style="width: 150px; text-align: center;">Status CPL MK</th>
                        <th style="width: 130px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="9" class="empty-state-cell">
                                <div class="empty-state">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <h4>Tidak Ada Data Mata Kuliah</h4>
                                    <p>Tidak ditemukan mata kuliah yang sesuai dengan kriteria filter yang Anda pilih.</p>
                                    <?php if ($hasFilter): ?>
                                        <a href="<?= $baseUrl ?>/monitoring-cpl-mk" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                                            <i class="fa-solid fa-rotate-left"></i> Reset Filter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($courses as $c): ?>
                            <?php 
                            $detailUrl = $baseUrl . '/monitoring-cpl-mk/detail?id=' . $c['id'] . ($selectedTaId ? '&tahun_akademik_id=' . $selectedTaId : '');
                            ?>
                            <tr class="course-row" onclick="window.location.href='<?= $detailUrl ?>'" title="Klik untuk membuka detail mahasiswa MK <?= htmlspecialchars($c['kode']) ?>">
                                <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                                    <?= $no++ ?>
                                </td>
                                <td>
                                    <div class="course-name-cell">
                                        <a href="<?= $detailUrl ?>" class="course-title-link" onclick="event.stopPropagation();">
                                            <?= htmlspecialchars($c['nama']) ?>
                                        </a>
                                        <div class="course-code-badge">
                                            <span class="code-tag"><?= htmlspecialchars($c['kode']) ?></span>
                                            <?php if (!empty($c['sifat'])): ?>
                                                <span class="sifat-tag sifat-<?= strtolower($c['sifat']) ?>"><?= htmlspecialchars($c['sifat']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="sks-sem-badge">
                                        <strong><?= (int)$c['sks'] ?> SKS</strong>
                                        <small>Sem <?= (int)$c['semester'] ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="prodi-kuri-cell">
                                        <span class="prodi-text"><?= htmlspecialchars($c['prodi_nama']) ?></span>
                                        <span class="kurikulum-sub"><?= htmlspecialchars($c['kurikulum_nama']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cpl-pills-wrap">
                                        <?php if (!empty($c['cpl_list'])): ?>
                                            <?php foreach ($c['cpl_list'] as $cp): ?>
                                                <span class="cpl-badge" title="<?= htmlspecialchars($cp['kode']) ?>: <?= htmlspecialchars($cp['rumusan'] ?? '') ?> (Target: <?= $cp['target'] ?>%)">
                                                    <?= htmlspecialchars($cp['kode']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.78rem;">Belum dipetakan</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="student-count-cell">
                                        <span class="count-number <?= $c['total_mahasiswa'] > 0 ? 'has-students' : 'no-students' ?>">
                                            <i class="fa-solid fa-users"></i> <?= (int)$c['total_mahasiswa'] ?>
                                        </span>
                                        <small><?= $c['total_kelas'] ?> Kelas</small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($c['total_mahasiswa'] === 0): ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">Belum ada peserta</span>
                                    <?php elseif ($c['mahasiswa_terukur'] === 0): ?>
                                        <div class="unassessed-notice">
                                            <i class="fa-regular fa-clock"></i> Belum ada nilai asesmen
                                        </div>
                                    <?php else: ?>
                                        <div class="attainment-progress-wrap">
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.78rem; margin-bottom: 4px;">
                                                <span>
                                                    <strong class="text-success"><?= $c['mahasiswa_tercapai'] ?></strong> / <?= $c['mahasiswa_terukur'] ?> Mahasiswa
                                                </span>
                                                <span class="fw-bold <?= $c['persentase_tercapai'] >= 80 ? 'text-success' : ($c['persentase_tercapai'] >= 50 ? 'text-warning' : 'text-danger') ?>">
                                                    <?= $c['persentase_tercapai'] ?>%
                                                </span>
                                            </div>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill progress-<?= $c['status_badge'] ?>" style="width: <?= min(100, $c['persentase_tercapai']) ?>%;"></div>
                                            </div>
                                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                                Rata-rata Skor: <strong><?= $c['avg_cpl_score'] ?>%</strong>
                                                <?php if ($c['mahasiswa_belum_tercapai'] > 0): ?>
                                                    · <span class="text-danger"><?= $c['mahasiswa_belum_tercapai'] ?> Belum Tercapai</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="status-pill status-pill-<?= $c['status_badge'] ?>">
                                        <?php if ($c['status'] === 'Tercapai'): ?>
                                            <i class="fa-solid fa-circle-check"></i>
                                        <?php elseif ($c['status'] === 'Sebagian Tercapai'): ?>
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                        <?php elseif ($c['status'] === 'Belum Tercapai'): ?>
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;" onclick="event.stopPropagation();">
                                    <a href="<?= $detailUrl ?>" class="btn-detail-action" title="Lihat Mahasiswa & Nilai CPL">
                                        <span>Lihat CPL</span>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* CSS Stylesheet Modern & High Contrast untuk Halaman Monitoring CPL MK */
.cpl-mk-monitoring-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

/* Header */
.monitoring-header {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: var(--shadow-card);
}
[data-theme="light"] .monitoring-header,
html.light .monitoring-header {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.07) !important;
}
.badge-role-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.35);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
[data-theme="light"] .badge-role-tag,
html.light .badge-role-tag {
    background: #e0f2fe !important;
    color: #0284c7 !important;
    border: 1px solid #bae6fd !important;
}
.monitoring-header h2 {
    margin: 0;
    font-size: 1.55rem;
    font-weight: 800;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 10px;
}
[data-theme="light"] .monitoring-header h2,
html.light .monitoring-header h2 {
    color: #0f172a !important;
}
.monitoring-header p {
    margin: 6px 0 0;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8) !important;
    max-width: 780px;
}
[data-theme="light"] .monitoring-header p,
html.light .monitoring-header p {
    color: #475569 !important;
}
.indicator-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
}
[data-theme="light"] .indicator-pill,
html.light .indicator-pill {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}
.kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
[data-theme="light"] .kpi-card,
html.light .kpi-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(var(--primary-rgb), 0.4);
}
.kpi-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.kpi-info {
    flex: 1;
    min-width: 0;
}
.kpi-label {
    display: block;
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
[data-theme="light"] .kpi-label,
html.light .kpi-label {
    color: #64748b !important;
}
.kpi-number {
    font-size: 1.7rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.2;
    display: flex;
    align-items: baseline;
    gap: 8px;
}
[data-theme="light"] .kpi-number,
html.light .kpi-number {
    color: #0f172a !important;
}
.kpi-badge {
    font-size: 0.78rem;
    font-weight: 700;
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    padding: 2px 8px;
    border-radius: 999px;
}
.kpi-sub {
    display: block;
    font-size: 0.76rem;
    color: var(--text-muted);
    margin-top: 4px;
}
[data-theme="light"] .kpi-sub,
html.light .kpi-sub {
    color: #64748b !important;
}

/* Filter Card */
.filter-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
    box-shadow: var(--shadow-sm);
}
[data-theme="light"] .filter-card,
html.light .filter-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}
.filter-col label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 6px;
}
[data-theme="light"] .filter-col label,
html.light .filter-col label {
    color: #334155 !important;
}
.filter-col label i {
    color: var(--primary);
    margin-right: 4px;
}
.filter-col select,
.filter-col input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--input-bg);
    color: var(--text-main);
    font-size: 0.88rem;
    transition: border-color 0.2s ease;
}
[data-theme="light"] .filter-col select,
[data-theme="light"] .filter-col input,
html.light .filter-col select,
html.light .filter-col input {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
}
[data-theme="light"] .filter-col select option,
html.light .filter-col select option {
    background: #ffffff !important;
    color: #0f172a !important;
}
.filter-col select:focus,
.filter-col input:focus {
    outline: none;
    border-color: var(--primary);
}
.search-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.clear-search-btn {
    position: absolute;
    right: 10px;
    color: var(--text-muted);
    font-size: 1.2rem;
    text-decoration: none;
    line-height: 1;
}
.clear-search-btn:hover {
    color: var(--text-main);
}
.filter-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid var(--border-subtle);
}
.filter-count-note {
    margin-left: auto;
    font-size: 0.82rem;
    color: var(--text-muted);
}
[data-theme="light"] .filter-count-note,
html.light .filter-count-note {
    color: #64748b !important;
}

/* Table Card */
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-card);
    overflow: hidden;
}
[data-theme="light"] .table-card,
html.light .table-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06) !important;
}
.table-card-header {
    padding: 18px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 1px solid var(--border);
    background: var(--table-header-bg);
}
[data-theme="light"] .table-card-header,
html.light .table-card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.table-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
}
[data-theme="light"] .table-title,
html.light .table-title {
    color: #0f172a !important;
}
.hint-text {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}
[data-theme="light"] .hint-text,
html.light .hint-text {
    color: #64748b !important;
}
.hint-text i {
    color: var(--primary);
}

.table-responsive {
    overflow-x: auto;
}
.monitoring-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.monitoring-table th {
    background: var(--table-header-bg);
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 13px 16px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
[data-theme="light"] .monitoring-table th,
html.light .monitoring-table th {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border-bottom: 1px solid #cbd5e1 !important;
}
.monitoring-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
    vertical-align: middle;
}
[data-theme="light"] .monitoring-table td,
html.light .monitoring-table td {
    color: #1e293b !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.course-row {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.course-row:hover {
    background: var(--table-hover-bg);
}
[data-theme="light"] .course-row:hover,
html.light .course-row:hover {
    background: #f8fafc !important;
}

/* Cell Components */
.course-name-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.course-title-link {
    font-weight: 700;
    color: var(--text-main);
    text-decoration: none;
    font-size: 0.95rem;
    transition: color 0.15s ease;
}
[data-theme="light"] .course-title-link,
html.light .course-title-link {
    color: #0f172a !important;
}
.course-row:hover .course-title-link {
    color: var(--primary);
}
.course-code-badge {
    display: flex;
    align-items: center;
    gap: 6px;
}
.code-tag {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.78rem;
    background: rgba(var(--primary-rgb), 0.12);
    color: var(--primary);
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid rgba(var(--primary-rgb), 0.25);
}
[data-theme="light"] .code-tag,
html.light .code-tag {
    background: #e0f2fe !important;
    color: #0284c7 !important;
    border: 1px solid #bae6fd !important;
}
.sifat-tag {
    font-size: 0.7rem;
    padding: 1px 6px;
    border-radius: 4px;
    font-weight: 600;
}
.sifat-wajib {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}
[data-theme="light"] .sifat-wajib,
html.light .sifat-wajib {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #047857 !important;
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
}
.sifat-pilihan {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}
[data-theme="light"] .sifat-pilihan,
html.light .sifat-pilihan {
    background: rgba(245, 158, 11, 0.15) !important;
    color: #b45309 !important;
    border: 1px solid rgba(245, 158, 11, 0.25) !important;
}
.sks-sem-badge {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    background: var(--surface-hover);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 6px;
}
[data-theme="light"] .sks-sem-badge,
html.light .sks-sem-badge {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}
.sks-sem-badge strong {
    font-size: 0.85rem;
    color: var(--text-main);
}
[data-theme="light"] .sks-sem-badge strong,
html.light .sks-sem-badge strong {
    color: #0f172a !important;
}
.sks-sem-badge small {
    font-size: 0.72rem;
    color: var(--text-muted);
}
[data-theme="light"] .sks-sem-badge small,
html.light .sks-sem-badge small {
    color: #64748b !important;
}
.prodi-kuri-cell {
    display: flex;
    flex-direction: column;
}
.prodi-text {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-main);
}
[data-theme="light"] .prodi-text,
html.light .prodi-text {
    color: #0f172a !important;
}
.kurikulum-sub {
    font-size: 0.75rem;
    color: var(--text-muted);
}
[data-theme="light"] .kurikulum-sub,
html.light .kurikulum-sub {
    color: #64748b !important;
}

/* CPL Badges */
.cpl-pills-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    max-width: 240px;
}
.cpl-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    background: rgba(99, 102, 241, 0.12);
    color: #818cf8;
    border: 1px solid rgba(99, 102, 241, 0.3);
}
[data-theme="light"] .cpl-badge,
html.light .cpl-badge {
    background: #eef2ff !important;
    color: #4338ca !important;
    border: 1px solid #c7d2fe !important;
}

/* Student Count Cell */
.student-count-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.count-number {
    font-weight: 700;
    font-size: 0.95rem;
}
.count-number.has-students {
    color: var(--primary);
}
[data-theme="light"] .count-number.has-students,
html.light .count-number.has-students {
    color: #0284c7 !important;
}
.count-number.no-students {
    color: var(--text-muted);
}
[data-theme="light"] .count-number.no-students,
html.light .count-number.no-students {
    color: #64748b !important;
}
.student-count-cell small {
    font-size: 0.72rem;
    color: var(--text-muted);
}
[data-theme="light"] .student-count-cell small,
html.light .student-count-cell small {
    color: #64748b !important;
}

/* Attainment Progress */
.attainment-progress-wrap {
    min-width: 180px;
}
.progress-bar-bg {
    width: 100%;
    height: 7px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 99px;
    overflow: hidden;
}
[data-theme="light"] .progress-bar-bg,
html.light .progress-bar-bg {
    background: #e4e4e7 !important;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.3s ease;
}
.progress-success { background: #10b981; }
.progress-warning { background: #f59e0b; }
.progress-danger  { background: #ef4444; }
.progress-secondary { background: #71717a; }
[data-theme="light"] .progress-success, html.light .progress-success { background: #047857 !important; }
[data-theme="light"] .progress-warning, html.light .progress-warning { background: #b45309 !important; }
[data-theme="light"] .progress-danger,  html.light .progress-danger  { background: #dc2626 !important; }

.unassessed-notice {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-style: italic;
}
[data-theme="light"] .unassessed-notice,
html.light .unassessed-notice {
    color: #64748b !important;
}

/* Status Pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}
.status-pill-success {
    background: rgba(16, 185, 129, 0.14);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-pill-warning {
    background: rgba(245, 158, 11, 0.14);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.status-pill-danger {
    background: rgba(239, 68, 68, 0.14);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.status-pill-secondary {
    background: rgba(113, 113, 122, 0.14);
    color: var(--text-muted);
    border: 1px solid var(--border);
}
[data-theme="light"] .status-pill-success,
html.light .status-pill-success {
    background: #ecfdf5 !important;
    color: #047857 !important;
    border: 1px solid #a7f3d0 !important;
}
[data-theme="light"] .status-pill-warning,
html.light .status-pill-warning {
    background: #fffbeb !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
}
[data-theme="light"] .status-pill-danger,
html.light .status-pill-danger {
    background: #fef2f2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
}
[data-theme="light"] .status-pill-secondary,
html.light .status-pill-secondary {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
}

/* Action Button */
.btn-detail-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(var(--primary-rgb), 0.12);
    color: var(--primary);
    border: 1px solid rgba(var(--primary-rgb), 0.3);
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-detail-action:hover {
    background: var(--primary);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.4);
}
[data-theme="light"] .btn-detail-action,
html.light .btn-detail-action {
    background: #e0f2fe !important;
    color: #0284c7 !important;
    border: 1px solid #bae6fd !important;
}
[data-theme="light"] .btn-detail-action:hover,
html.light .btn-detail-action:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
}
.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.6;
}
.empty-state h4 {
    color: var(--text-main);
    margin: 0 0 6px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
