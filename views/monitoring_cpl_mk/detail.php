<?php
// views/monitoring_cpl_mk/detail.php
$title = "Detail Mahasiswa & CPL: " . htmlspecialchars($course['kode']) . " - " . htmlspecialchars($course['nama']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div class="cpl-detail-page">
    <!-- Breadcrumb & Top Bar -->
    <div class="detail-top-nav">
        <a href="<?= $baseUrl ?>/monitoring-cpl-mk<?= $selectedTaId ? '?tahun_akademik_id=' . $selectedTaId : '' ?>" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Mata Kuliah
        </a>
        <div class="top-nav-actions">
            <a href="<?= $baseUrl ?>/monitoring-cpl-mk/export?id=<?= $course['id'] ?><?= $selectedTaId ? '&tahun_akademik_id=' . $selectedTaId : '' ?>" class="btn btn-secondary btn-sm" title="Ekspor data mahasiswa ke CSV">
                <i class="fa-solid fa-file-csv"></i> Ekspor CSV
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" title="Cetak laporan">
                <i class="fa-solid fa-print"></i> Cetak Dokumen
            </button>
        </div>
    </div>

    <!-- Course Profile Hero Card -->
    <div class="course-hero-card">
        <div class="hero-header-row">
            <div class="hero-left">
                <div class="hero-badge-group">
                    <span class="badge-mk-code"><?= htmlspecialchars($course['kode']) ?></span>
                    <span class="badge-mk-sem"><?= (int)$course['sks'] ?> SKS · Semester <?= (int)$course['semester'] ?></span>
                    <span class="badge-mk-sifat sifat-<?= strtolower($course['sifat'] ?? 'wajib') ?>"><?= htmlspecialchars($course['sifat'] ?? 'Wajib') ?></span>
                    <span class="badge-mk-prodi"><?= htmlspecialchars($course['prodi_nama']) ?></span>
                    <span class="badge-mk-kuri"><?= htmlspecialchars($course['kurikulum_nama']) ?></span>
                </div>
                <h1 class="course-hero-title"><?= htmlspecialchars($course['nama']) ?></h1>
                <div class="course-hero-meta">
                    <span>
                        <i class="fa-solid fa-chalkboard-user"></i> Dosen Pengampu / PJMK: <strong><?= htmlspecialchars($course['dosen_pengampu']) ?></strong>
                    </span>
                    <?php if (!empty($selectedTaId)): ?>
                        <?php 
                        $selectedTaName = '';
                        foreach ($tahunAkademik as $ta) {
                            if ((int)$ta['id'] === (int)$selectedTaId) {
                                $selectedTaName = $ta['nama'];
                                break;
                            }
                        }
                        ?>
                        <span>
                            <i class="fa-solid fa-calendar-check"></i> Periode: <strong><?= htmlspecialchars($selectedTaName) ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-right">
                <div class="pass-rate-circle">
                    <div class="circle-val"><?= $kpi['pass_rate'] ?>%</div>
                    <div class="circle-lbl">Kelulusan CPL</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course KPI Cards -->
    <div class="detail-kpi-grid">
        <div class="detail-kpi-card">
            <div class="kpi-icon icon-blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-title">Total Peserta MK</span>
                <div class="kpi-value"><?= $kpi['total_students'] ?> <small>Mahasiswa</small></div>
                <span class="kpi-note"><?= count($classes) ?> Kelas Perkuliahan</span>
            </div>
        </div>

        <div class="detail-kpi-card">
            <div class="kpi-icon icon-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-title">CPL Tercapai</span>
                <div class="kpi-value text-success"><?= $kpi['passed_students'] ?> <small>Mahasiswa</small></div>
                <span class="kpi-note">Memenuhi seluruh target CPL MK</span>
            </div>
        </div>

        <div class="detail-kpi-card">
            <div class="kpi-icon icon-red">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-title">CPL Belum Tercapai</span>
                <div class="kpi-value text-danger"><?= $kpi['failed_students'] ?> <small>Mahasiswa</small></div>
                <span class="kpi-note">Perlu perbaikan / remedial asesmen</span>
            </div>
        </div>

        <div class="detail-kpi-card">
            <div class="kpi-icon icon-amber">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-title">Rata-rata Skor CPL</span>
                <div class="kpi-value text-warning"><?= number_format($kpi['avg_score'], 1) ?>%</div>
                <span class="kpi-note">Ambang Batas Minimum: ≥ 70%</span>
            </div>
        </div>
    </div>

    <!-- CPL Benchmark Cards Grid -->
    <div class="cpl-benchmark-section">
        <div class="section-title">
            <i class="fa-solid fa-bullseye" style="color: var(--primary);"></i>
            <span>Capaian Pembelajaran Lulusan (CPL) yang Dibebankan pada MK Ini</span>
        </div>
        <div class="cpl-benchmark-grid">
            <?php foreach ($cpls as $cp): ?>
                <div class="cpl-bench-card <?= $cp['status'] === 'Tercapai' ? 'bench-pass' : ($cp['status'] === 'Belum Tercapai' ? 'bench-fail' : '') ?>">
                    <div class="cpl-bench-header">
                        <span class="cpl-code-pill"><?= htmlspecialchars($cp['kode']) ?></span>
                        <span class="cpl-target-pill">Target: ≥ <?= $cp['target'] ?>%</span>
                    </div>
                    <div class="cpl-rumusan-text">
                        <?= htmlspecialchars($cp['rumusan']) ?>
                    </div>
                    <div class="cpl-bench-metrics">
                        <div class="bench-metric">
                            <span class="bm-lbl">Rata-rata Skor:</span>
                            <strong class="bm-val <?= $cp['avg_score'] >= $cp['target'] ? 'text-success' : 'text-danger' ?>"><?= $cp['avg_score'] ?>%</strong>
                        </div>
                        <div class="bench-metric">
                            <span class="bm-lbl">Kelulusan:</span>
                            <strong class="bm-val"><?= $cp['pass_rate'] ?>% (<?= $cp['passed_count'] ?>/<?= $cp['assessed_count'] ?> Mhs)</strong>
                        </div>
                    </div>
                    <div class="bench-progress">
                        <div class="bench-progress-bar <?= $cp['avg_score'] >= $cp['target'] ? 'bar-success' : 'bar-danger' ?>" style="width: <?= min(100, $cp['avg_score']) ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Student Table Section -->
    <div class="students-section-card">
        <div class="section-card-header">
            <div class="section-header-left">
                <h3>
                    <i class="fa-solid fa-graduation-cap" style="color: var(--primary);"></i>
                    Daftar Mahasiswa & Ketercapaian CPL
                </h3>
                <span class="header-count-badge" id="displayedCountBadge">
                    <?= count($students) ?> Mahasiswa
                </span>
            </div>

            <!-- Instant Client-Side & Server-Side Filters -->
            <div class="section-filter-bar">
                <div class="quick-filter-item">
                    <i class="fa-solid fa-users-rectangle"></i>
                    <select id="classFilterSelect" onchange="applyFilters()">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($classes as $cl): ?>
                            <option value="<?= htmlspecialchars($cl['nama_kelas']) ?>" <?= $selectedKelasId === (int)$cl['id'] ? 'selected' : '' ?>>
                                Kelas <?= htmlspecialchars($cl['nama_kelas']) ?> (<?= (int)$cl['total_mhs'] ?> Mhs)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="quick-filter-item">
                    <i class="fa-solid fa-filter"></i>
                    <select id="statusFilterSelect" onchange="applyFilters()">
                        <option value="">Semua Status</option>
                        <option value="Tercapai" <?= $statusFilter === 'tercapai' ? 'selected' : '' ?>>Tercapai</option>
                        <option value="Belum Tercapai" <?= $statusFilter === 'belum_tercapai' ? 'selected' : '' ?>>Belum Tercapai</option>
                        <option value="Belum Dinilai" <?= $statusFilter === 'belum_dinilai' ? 'selected' : '' ?>>Belum Dinilai</option>
                    </select>
                </div>

                <div class="quick-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="studentSearchInput" placeholder="Cari NIM atau nama..." oninput="applyFilters()" value="<?= htmlspecialchars($searchMhs) ?>" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="students-table" id="studentCplTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 140px;">NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th style="width: 90px; text-align: center;">Kelas</th>
                        <?php foreach ($cpls as $cp): ?>
                            <th style="text-align: center; min-width: 120px;">
                                <div class="th-cpl-header">
                                    <span><?= htmlspecialchars($cp['kode']) ?></span>
                                    <small>≥ <?= $cp['target'] ?>%</small>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <th style="width: 130px; text-align: center;">Rata-rata CPL</th>
                        <th style="width: 150px; text-align: center;">Status Ketercapaian</th>
                        <th style="width: 110px; text-align: center;">Portofolio</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if (empty($students)): ?>
                        <tr id="emptyRow">
                            <td colspan="<?= 6 + count($cpls) ?>" class="text-center py-4" style="color: var(--text-muted);">
                                <i class="fa-solid fa-user-slash" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                                Tidak ada data mahasiswa yang mengambil mata kuliah ini sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($students as $st): ?>
                            <tr class="student-item-row" data-nim="<?= strtolower($st['nim']) ?>" data-nama="<?= strtolower($st['nama_lengkap']) ?>" data-kelas="<?= htmlspecialchars($st['nama_kelas'] ?? '') ?>" data-status="<?= htmlspecialchars($st['overall_status']) ?>">
                                <td style="text-align: center; color: var(--text-muted); font-size: 0.85rem;" class="row-number">
                                    <?= $no++ ?>
                                </td>
                                <td>
                                    <span class="nim-code"><?= htmlspecialchars($st['nim']) ?></span>
                                </td>
                                <td>
                                    <div class="student-name-box">
                                        <span class="student-name"><?= htmlspecialchars($st['nama_lengkap']) ?></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="class-pill">Kelas <?= htmlspecialchars($st['nama_kelas'] ?? '-') ?></span>
                                </td>

                                <?php foreach ($cpls as $cp): ?>
                                    <?php 
                                    $cid = $cp['id'];
                                    $cplInfo = $st['cpl_breakdown'][$cid] ?? null;
                                    ?>
                                    <td style="text-align: center;">
                                        <?php if ($cplInfo && $cplInfo['is_measured']): ?>
                                            <div class="cpl-score-cell">
                                                <span class="score-val <?= $cplInfo['status'] === 'Tercapai' ? 'text-success' : 'text-danger' ?>">
                                                    <?= number_format($cplInfo['score'], 1) ?>%
                                                </span>
                                                <span class="status-micro-badge badge-<?= $cplInfo['status_badge'] ?>">
                                                    <?= $cplInfo['status'] === 'Tercapai' ? '✓ Tercapai' : '✗ Belum' ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="unassessed-dash" title="Belum ada asesmen">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <td style="text-align: center;">
                                    <?php if ($st['overall_score'] !== null): ?>
                                        <strong class="overall-score <?= $st['overall_score'] >= 70 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($st['overall_score'], 1) ?>%
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center;">
                                    <span class="status-pill status-pill-<?= $st['overall_badge'] ?>">
                                        <?php if ($st['overall_status'] === 'Tercapai'): ?>
                                            <i class="fa-solid fa-circle-check"></i>
                                        <?php elseif ($st['overall_status'] === 'Belum Tercapai'): ?>
                                            <i class="fa-solid fa-circle-xmark"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($st['overall_status']) ?>
                                    </span>
                                </td>

                                <td style="text-align: center;">
                                    <a href="<?= $baseUrl ?>/laporan/detail?id=<?= $st['mhs_id'] ?>" class="btn-portfolio-link" target="_blank" title="Buka Portofolio CPL Mahasiswa">
                                        <i class="fa-solid fa-chart-radar"></i> Detail
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

<script>
// Filter interaktif instan di sisi klien
function applyFilters() {
    const classFilter = document.getElementById('classFilterSelect').value.toLowerCase().trim();
    const statusFilter = document.getElementById('statusFilterSelect').value.toLowerCase().trim();
    const searchVal = document.getElementById('studentSearchInput').value.toLowerCase().trim();

    const rows = document.querySelectorAll('.student-item-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const nim = row.getAttribute('data-nim') || '';
        const nama = row.getAttribute('data-nama') || '';
        const kelas = (row.getAttribute('data-kelas') || '').toLowerCase().trim();
        const status = (row.getAttribute('data-status') || '').toLowerCase().trim();

        const matchSearch = searchVal === '' || nim.includes(searchVal) || nama.includes(searchVal);
        const matchClass = classFilter === '' || kelas === classFilter;
        const matchStatus = statusFilter === '' || status === statusFilter;

        if (matchSearch && matchClass && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('displayedCountBadge');
    if (badge) {
        badge.textContent = visibleCount + ' Mahasiswa';
    }

    // Re-index displayed row numbers
    let currentIdx = 1;
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const numCell = row.querySelector('.row-number');
            if (numCell) numCell.textContent = currentIdx++;
        }
    });
}
</script>

<style>
/* CSS Stylesheet untuk Halaman Detail Monitoring CPL MK */
.cpl-detail-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

/* Detail Top Nav */
.detail-top-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    background: var(--surface);
    border: 1px solid var(--border);
    transition: all 0.2s ease;
}
.btn-back:hover {
    background: var(--surface-hover);
    border-color: var(--primary);
}
[data-theme="light"] .btn-back,
html.light .btn-back {
    color: #0284c7 !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
}
[data-theme="light"] .btn-back:hover,
html.light .btn-back:hover {
    background: #f1f5f9 !important;
    border-color: #0284c7 !important;
}
.top-nav-actions {
    display: flex;
    gap: 10px;
}

/* Color Utility Classes */
.text-success { color: #10b981 !important; }
.text-danger  { color: #ef4444 !important; }
.text-warning { color: #f59e0b !important; }
.text-primary { color: #38bdf8 !important; }

[data-theme="light"] .text-success, html.light .text-success { color: #047857 !important; }
[data-theme="light"] .text-danger,  html.light .text-danger  { color: #dc2626 !important; }
[data-theme="light"] .text-warning, html.light .text-warning { color: #b45309 !important; }
[data-theme="light"] .text-primary, html.light .text-primary { color: #0284c7 !important; }

/* Course Hero Card */
.course-hero-card {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.95) 100%);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    box-shadow: var(--shadow-card);
}
[data-theme="light"] .course-hero-card,
html.light .course-hero-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.07) !important;
}
.hero-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.hero-badge-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.badge-mk-code {
    font-family: monospace;
    font-weight: 800;
    background: #38bdf8;
    color: #0f172a;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.82rem;
}
[data-theme="light"] .badge-mk-code,
html.light .badge-mk-code {
    background: #0284c7 !important;
    color: #ffffff !important;
}
.badge-mk-sem, .badge-mk-sifat, .badge-mk-prodi, .badge-mk-kuri {
    font-size: 0.76rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.18);
}
[data-theme="light"] .badge-mk-sem,
[data-theme="light"] .badge-mk-sifat,
[data-theme="light"] .badge-mk-prodi,
[data-theme="light"] .badge-mk-kuri,
html.light .badge-mk-sem,
html.light .badge-mk-sifat,
html.light .badge-mk-prodi,
html.light .badge-mk-kuri {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}
.badge-mk-sifat.sifat-wajib {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.35);
}
[data-theme="light"] .badge-mk-sifat.sifat-wajib,
html.light .badge-mk-sifat.sifat-wajib {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #047857 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
}
.badge-mk-sifat.sifat-pilihan {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border-color: rgba(59, 130, 246, 0.35);
}
[data-theme="light"] .badge-mk-sifat.sifat-pilihan,
html.light .badge-mk-sifat.sifat-pilihan {
    background: rgba(59, 130, 246, 0.12) !important;
    color: #1d4ed8 !important;
    border: 1px solid rgba(59, 130, 246, 0.3) !important;
}
.course-hero-title {
    margin: 0 0 10px;
    font-size: 1.65rem;
    font-weight: 800;
    color: #ffffff !important;
}
[data-theme="light"] .course-hero-title,
html.light .course-hero-title {
    color: #0f172a !important;
}
.course-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    font-size: 0.88rem;
    color: rgba(255, 255, 255, 0.85);
}
[data-theme="light"] .course-hero-meta,
html.light .course-hero-meta {
    color: #475569 !important;
}
.course-hero-meta strong {
    color: #ffffff;
}
[data-theme="light"] .course-hero-meta strong,
html.light .course-hero-meta strong {
    color: #0f172a !important;
}
.course-hero-meta i {
    color: #38bdf8;
    margin-right: 4px;
}
[data-theme="light"] .course-hero-meta i,
html.light .course-hero-meta i {
    color: #0284c7 !important;
}
.pass-rate-circle {
    width: 105px;
    height: 105px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    border: 3px solid #10b981;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 18px rgba(16, 185, 129, 0.35);
    flex-shrink: 0;
}
[data-theme="light"] .pass-rate-circle,
html.light .pass-rate-circle {
    background: #f8fafc !important;
    border: 3px solid #059669 !important;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.18) !important;
}
.circle-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
}
[data-theme="light"] .circle-val,
html.light .circle-val {
    color: #0f172a !important;
}
.circle-lbl {
    font-size: 0.68rem;
    font-weight: 700;
    color: #34d399;
    text-transform: uppercase;
    margin-top: 4px;
}
[data-theme="light"] .circle-lbl,
html.light .circle-lbl {
    color: #047857 !important;
}

/* Detail KPI Grid */
.detail-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.detail-kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}
[data-theme="light"] .detail-kpi-card,
html.light .detail-kpi-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
}
.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.kpi-icon.icon-blue {
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
}
.kpi-icon.icon-green {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}
.kpi-icon.icon-red {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}
.kpi-icon.icon-amber {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}
[data-theme="light"] .kpi-icon.icon-blue,
html.light .kpi-icon.icon-blue {
    background: #e0f2fe !important;
    color: #0284c7 !important;
}
[data-theme="light"] .kpi-icon.icon-green,
html.light .kpi-icon.icon-green {
    background: #dcfce7 !important;
    color: #047857 !important;
}
[data-theme="light"] .kpi-icon.icon-red,
html.light .kpi-icon.icon-red {
    background: #fee2e2 !important;
    color: #dc2626 !important;
}
[data-theme="light"] .kpi-icon.icon-amber,
html.light .kpi-icon.icon-amber {
    background: #fef3c7 !important;
    color: #b45309 !important;
}
.kpi-title {
    display: block;
    font-size: 0.76rem;
    color: var(--text-muted);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
[data-theme="light"] .kpi-title,
html.light .kpi-title {
    color: #64748b !important;
}
.kpi-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.2;
    margin: 2px 0;
}
[data-theme="light"] .kpi-value,
html.light .kpi-value {
    color: #0f172a !important;
}
[data-theme="light"] .kpi-value.text-success,
html.light .kpi-value.text-success {
    color: #047857 !important;
}
[data-theme="light"] .kpi-value.text-danger,
html.light .kpi-value.text-danger {
    color: #dc2626 !important;
}
[data-theme="light"] .kpi-value.text-warning,
html.light .kpi-value.text-warning {
    color: #b45309 !important;
}
.kpi-value small {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted);
}
[data-theme="light"] .kpi-value small,
html.light .kpi-value small {
    color: #64748b !important;
}
.kpi-note {
    display: block;
    font-size: 0.74rem;
    color: var(--text-muted);
}
[data-theme="light"] .kpi-note,
html.light .kpi-note {
    color: #64748b !important;
}

/* CPL Benchmark Grid */
.cpl-benchmark-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
    box-shadow: var(--shadow-sm);
}
[data-theme="light"] .cpl-benchmark-section,
html.light .cpl-benchmark-section {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06) !important;
}
.section-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}
[data-theme="light"] .section-title,
html.light .section-title {
    color: #0f172a !important;
}
.cpl-benchmark-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}
.cpl-bench-card {
    background: var(--surface-hover);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: transform 0.2s ease, border-color 0.2s ease;
}
[data-theme="light"] .cpl-bench-card,
html.light .cpl-bench-card {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
}
.cpl-bench-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--primary-rgb), 0.5);
}
[data-theme="light"] .cpl-bench-card:hover,
html.light .cpl-bench-card:hover {
    background: #f1f5f9 !important;
    border-color: #0284c7 !important;
}
.cpl-bench-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cpl-code-pill {
    font-family: monospace;
    font-weight: 800;
    font-size: 0.85rem;
    background: rgba(99, 102, 241, 0.15);
    color: #818cf8;
    padding: 3px 9px;
    border-radius: 4px;
    border: 1px solid rgba(99, 102, 241, 0.3);
}
[data-theme="light"] .cpl-code-pill,
html.light .cpl-code-pill {
    background: #eef2ff !important;
    color: #4338ca !important;
    border: 1px solid #c7d2fe !important;
}
.cpl-target-pill {
    font-size: 0.74rem;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--surface);
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid var(--border);
}
[data-theme="light"] .cpl-target-pill,
html.light .cpl-target-pill {
    background: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
}
.cpl-rumusan-text {
    font-size: 0.82rem;
    color: var(--text-main);
    line-height: 1.45;
    min-height: 40px;
}
[data-theme="light"] .cpl-rumusan-text,
html.light .cpl-rumusan-text {
    color: #1e293b !important;
}
.cpl-bench-metrics {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    border-top: 1px solid var(--border-subtle);
    padding-top: 8px;
}
[data-theme="light"] .cpl-bench-metrics,
html.light .cpl-bench-metrics {
    border-top-color: #e2e8f0 !important;
}
.bm-lbl {
    color: var(--text-muted);
}
[data-theme="light"] .bm-lbl,
html.light .bm-lbl {
    color: #64748b !important;
}
.bm-val {
    color: var(--text-main);
}
[data-theme="light"] .bm-val,
html.light .bm-val {
    color: #0f172a !important;
}
[data-theme="light"] .bm-val.text-success,
html.light .bm-val.text-success {
    color: #047857 !important;
}
[data-theme="light"] .bm-val.text-danger,
html.light .bm-val.text-danger {
    color: #dc2626 !important;
}
.bench-progress {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 99px;
    overflow: hidden;
}
[data-theme="light"] .bench-progress,
html.light .bench-progress {
    background: #e2e8f0 !important;
}
.bench-progress-bar {
    height: 100%;
    border-radius: 99px;
}
.bar-success { background: #10b981; }
.bar-danger  { background: #ef4444; }
[data-theme="light"] .bar-success,
html.light .bar-success {
    background: #047857 !important;
}
[data-theme="light"] .bar-danger,
html.light .bar-danger {
    background: #dc2626 !important;
}

/* Students Section Card */
.students-section-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-card);
    overflow: hidden;
}
[data-theme="light"] .students-section-card,
html.light .students-section-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06) !important;
}
.section-card-header {
    padding: 18px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    border-bottom: 1px solid var(--border);
    background: var(--table-header-bg);
}
[data-theme="light"] .section-card-header,
html.light .section-card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.section-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.section-header-left h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
}
[data-theme="light"] .section-header-left h3,
html.light .section-header-left h3 {
    color: #0f172a !important;
}
.header-count-badge {
    background: rgba(var(--primary-rgb), 0.12);
    color: var(--primary);
    font-size: 0.76rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
}
[data-theme="light"] .header-count-badge,
html.light .header-count-badge {
    background: #e0f2fe !important;
    color: #0284c7 !important;
    border: 1px solid #bae6fd !important;
}

/* Quick Filter Bar */
.section-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.quick-filter-item {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    color: var(--text-muted);
}
[data-theme="light"] .quick-filter-item,
html.light .quick-filter-item {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
}
.quick-filter-item select {
    border: none;
    background: transparent;
    color: var(--text-main);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    outline: none;
}
[data-theme="light"] .quick-filter-item select,
html.light .quick-filter-item select {
    color: #0f172a !important;
}
.quick-filter-item select option {
    background: var(--surface);
    color: var(--text-main);
}
[data-theme="light"] .quick-filter-item select option,
html.light .quick-filter-item select option {
    background: #ffffff !important;
    color: #0f172a !important;
}
.quick-search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: var(--radius-sm);
}
[data-theme="light"] .quick-search-box,
html.light .quick-search-box {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
}
.quick-search-box i {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin-right: 6px;
}
[data-theme="light"] .quick-search-box i,
html.light .quick-search-box i {
    color: #64748b !important;
}
.quick-search-box input {
    border: none;
    background: transparent;
    color: var(--text-main);
    font-size: 0.82rem;
    outline: none;
    width: 170px;
}
[data-theme="light"] .quick-search-box input,
html.light .quick-search-box input {
    color: #0f172a !important;
}
[data-theme="light"] .quick-search-box input::placeholder,
html.light .quick-search-box input::placeholder {
    color: #94a3b8 !important;
}

/* Students Table */
.students-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.students-table th {
    background: var(--table-header-bg);
    color: var(--text-muted);
    font-size: 0.74rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 13px 14px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
[data-theme="light"] .students-table th,
html.light .students-table th {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border-bottom: 1px solid #cbd5e1 !important;
}
.students-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
    vertical-align: middle;
}
[data-theme="light"] .students-table td,
html.light .students-table td {
    color: #1e293b !important;
    border-bottom: 1px solid #e2e8f0 !important;
}
.students-table tbody tr:hover {
    background: var(--table-hover-bg);
}
[data-theme="light"] .students-table tbody tr:hover,
html.light .students-table tbody tr:hover {
    background: #f8fafc !important;
}

.th-cpl-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1.2;
}
.th-cpl-header span {
    font-weight: 700;
}
[data-theme="light"] .th-cpl-header span,
html.light .th-cpl-header span {
    color: #0f172a !important;
}
.th-cpl-header small {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--primary);
}
[data-theme="light"] .th-cpl-header small,
html.light .th-cpl-header small {
    color: #0284c7 !important;
}

.row-number {
    color: var(--text-muted);
}
[data-theme="light"] .row-number,
html.light .row-number {
    color: #64748b !important;
}
.nim-code {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.82rem;
    color: var(--primary);
}
[data-theme="light"] .nim-code,
html.light .nim-code {
    color: #0284c7 !important;
}
.student-name {
    font-weight: 600;
    color: var(--text-main);
}
[data-theme="light"] .student-name,
html.light .student-name {
    color: #0f172a !important;
}
.class-pill {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--surface-hover);
    color: var(--text-muted);
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid var(--border);
}
[data-theme="light"] .class-pill,
html.light .class-pill {
    background: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}

/* CPL Score Cell in Table */
.cpl-score-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.score-val {
    font-weight: 700;
    font-size: 0.88rem;
}
[data-theme="light"] .score-val.text-success,
html.light .score-val.text-success {
    color: #047857 !important;
}
[data-theme="light"] .score-val.text-danger,
html.light .score-val.text-danger {
    color: #dc2626 !important;
}
.status-micro-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 3px;
    line-height: 1.2;
}
.status-micro-badge.badge-success {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}
.status-micro-badge.badge-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}
[data-theme="light"] .status-micro-badge.badge-success,
html.light .status-micro-badge.badge-success {
    background: #ecfdf5 !important;
    color: #047857 !important;
    border: 1px solid #a7f3d0 !important;
}
[data-theme="light"] .status-micro-badge.badge-danger,
html.light .status-micro-badge.badge-danger {
    background: #fef2f2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
}
.unassessed-dash {
    color: var(--text-muted);
    font-weight: bold;
}
[data-theme="light"] .unassessed-dash,
html.light .unassessed-dash {
    color: #94a3b8 !important;
}

.overall-score {
    font-size: 0.95rem;
}
[data-theme="light"] .overall-score.text-success,
html.light .overall-score.text-success {
    color: #047857 !important;
}
[data-theme="light"] .overall-score.text-danger,
html.light .overall-score.text-danger {
    color: #dc2626 !important;
}
[data-theme="light"] .overall-score.text-muted,
html.light .overall-score.text-muted {
    color: #64748b !important;
}

/* Status Pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
}
.status-pill-success {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
}
.status-pill-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.35);
}
.status-pill-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.35);
}
.status-pill-secondary {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.35);
}
[data-theme="light"] .status-pill-success,
html.light .status-pill-success {
    background: #ecfdf5 !important;
    color: #047857 !important;
    border: 1px solid #a7f3d0 !important;
}
[data-theme="light"] .status-pill-danger,
html.light .status-pill-danger {
    background: #fef2f2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
}
[data-theme="light"] .status-pill-warning,
html.light .status-pill-warning {
    background: #fffbeb !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
}
[data-theme="light"] .status-pill-secondary,
html.light .status-pill-secondary {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
}

/* Portfolio Link */
.btn-portfolio-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
    border: 1px solid rgba(var(--primary-rgb), 0.25);
    padding: 4px 8px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-portfolio-link:hover {
    background: var(--primary);
    color: #ffffff;
}
[data-theme="light"] .btn-portfolio-link,
html.light .btn-portfolio-link {
    color: #0284c7 !important;
    background: #e0f2fe !important;
    border: 1px solid #bae6fd !important;
}
[data-theme="light"] .btn-portfolio-link:hover,
html.light .btn-portfolio-link:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
}

/* Print Styles */
@media print {
    #appSidebar, .sidebar, .topbar, .detail-top-nav, .section-filter-bar, .btn-portfolio-link {
        display: none !important;
    }
    .content-area, .cpl-detail-page {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .course-hero-card, .detail-kpi-card, .cpl-benchmark-section, .students-section-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        color: #000 !important;
        background: #fff !important;
    }
    .course-hero-title, .kpi-value, .student-name, .cpl-rumusan-text {
        color: #000 !important;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
