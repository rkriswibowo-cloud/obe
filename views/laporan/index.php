<?php
$title = "Dashboard Ketercapaian CPL per Kurikulum";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
            <i class="fa-solid fa-chart-pie" style="color: var(--primary); margin-right: 8px;"></i>Dashboard Ketercapaian CPL per Kurikulum
        </h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Analisis ketercapaian Capaian Pembelajaran Lulusan (CPL) per kurikulum dan angkatan mahasiswa.
        </p>
    </div>
</div>

<!-- FILTER PANEL: DROPDOWN OPSI KURIKULUM & ANGKATAN -->
<div style="background: var(--surface); padding: 20px 22px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 16px;">
    <form method="GET" action="<?= $baseUrl ?>/laporan" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Dropdown Opsi Kurikulum -->
        <div style="flex: 2; min-width: 280px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 5px;"></i> Pilih Kurikulum:
            </label>
            <select name="kurikulum_id" onchange="this.form.submit()" style="width: 100%; padding: 10px 14px; font-size: 0.92rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-weight: 600; cursor: pointer;">
                <?php 
                $groupedKurikulum = [];
                foreach ($daftarKurikulum as $k) {
                    $prodiKey = trim(($k['jenjang_prodi'] ?? '') . ' ' . ($k['nama_prodi'] ?? 'Program Studi'));
                    if (empty($prodiKey)) $prodiKey = 'Program Studi';
                    $groupedKurikulum[$prodiKey][] = $k;
                }
                foreach ($groupedKurikulum as $prodiName => $kList): ?>
                    <optgroup label="Program Studi: <?= htmlspecialchars($prodiName) ?>">
                        <?php foreach ($kList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $k['id'] == $selectedKurikulumId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?> (Tahun <?= htmlspecialchars((string)$k['tahun_mulai']) ?> · <?= $k['total_cpl'] ?> CPL · <?= $k['total_mahasiswa'] ?> Mahasiswa · Angkatan <?= htmlspecialchars($k['rentang_angkatan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Dropdown Filter Angkatan -->
        <div style="flex: 1.2; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                <i class="fa-solid fa-filter" style="margin-right: 5px; color: var(--secondary);"></i> Filter Angkatan:
            </label>
            <select name="angkatan" onchange="this.form.submit()" style="width: 100%; padding: 10px 14px; font-size: 0.92rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); cursor: pointer;">
                <option value="">-- Semua Angkatan (<?= htmlspecialchars($selectedKurikulum['rentang_angkatan'] ?? '') ?>) --</option>
                <?php foreach ($daftarAngkatan as $ang): ?>
                    <option value="<?= htmlspecialchars((string)$ang) ?>" <?= $selectedAngkatan == $ang ? 'selected' : '' ?>>
                        Angkatan <?= htmlspecialchars((string)$ang) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($selectedAngkatan)): ?>
            <div style="margin-bottom: 2px;">
                <a href="<?= $baseUrl ?>/laporan?kurikulum_id=<?= $selectedKurikulumId ?>" class="btn btn-secondary" style="padding: 10px 14px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.88rem;" title="Reset Filter Angkatan">
                    <i class="fa-solid fa-xmark"></i> Reset Angkatan
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- ACCORDION: RINCIAN & PILIHAN SEMUA KURIKULUM -->
<details style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 24px; overflow: hidden; transition: all 0.2s ease;">
    <summary style="padding: 12px 18px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; font-size: 0.88rem; font-weight: 600; color: var(--text-main); background: rgba(0,0,0,0.06);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 26px; height: 26px; border-radius: 6px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.85rem;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <span>Rincian & Informasi Kurikulum (<?= count($daftarKurikulum) ?> Kurikulum Tersedia)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-muted);">
            <span>Klik untuk buka/tutup rincian</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </summary>

    <div style="padding: 18px 20px; border-top: 1px solid var(--border); background: var(--surface);">
        <!-- Active Curriculum Highlight -->
        <div style="background: rgba(99, 102, 241, 0.06); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--primary); letter-spacing: 0.05em;">Kurikulum Sedang Aktif Dipilih:</span>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                    <?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>
                    <span style="font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: var(--success); margin-left: 6px;">
                        <?= htmlspecialchars($selectedKurikulum['status'] ?? 'ACTIVE') ?>
                    </span>
                </div>
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                    Program Studi: <strong><?= htmlspecialchars(($selectedKurikulum['jenjang_prodi'] ?? '') . ' ' . ($selectedKurikulum['nama_prodi'] ?? 'Sistem Informasi')) ?></strong> • 
                    Tahun Mulai: <strong><?= htmlspecialchars((string)($selectedKurikulum['tahun_mulai'] ?? '-')) ?></strong> • 
                    Rentang Angkatan: <strong><?= htmlspecialchars($selectedKurikulum['rentang_angkatan'] ?? '') ?></strong>
                </div>
            </div>
            <div style="display: flex; gap: 20px; text-align: right;">
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--primary);"><?= $selectedKurikulum['total_cpl'] ?></div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">CPL Terdaftar</div>
                </div>
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--secondary);"><?= $selectedKurikulum['total_mahasiswa'] ?></div>
                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Mahasiswa</div>
                </div>
            </div>
        </div>

        <!-- Quick Switch List / Grid of All Curricula -->
        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">
            Pilih Cepat Kurikulum Lainnya:
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px;">
            <?php foreach ($daftarKurikulum as $k): 
                $isCurActive = ($k['id'] == $selectedKurikulumId);
            ?>
                <a href="<?= $baseUrl ?>/laporan?kurikulum_id=<?= $k['id'] ?>" 
                   style="padding: 12px 16px; border-radius: var(--radius-sm); text-decoration: none; border: 1px solid <?= $isCurActive ? 'var(--primary)' : 'var(--border)' ?>; background: <?= $isCurActive ? 'rgba(99, 102, 241, 0.12)' : 'var(--surface-hover)' ?>; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s ease;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.92rem; color: <?= $isCurActive ? 'var(--primary)' : 'var(--text-main)' ?>;">
                            <?= htmlspecialchars($k['nama']) ?>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                            <?= $k['total_cpl'] ?> CPL • <?= $k['total_mahasiswa'] ?> Mahasiswa • Angkatan <?= htmlspecialchars($k['rentang_angkatan']) ?>
                        </div>
                    </div>
                    <?php if ($isCurActive): ?>
                        <span style="color: var(--primary); font-size: 0.9rem;"><i class="fa-solid fa-circle-check"></i></span>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.8rem;"><i class="fa-solid fa-arrow-right"></i></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</details>

<!-- KPI Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">RATA-RATA CAPAIAN CPL</div>
        <div style="font-size: 2rem; font-weight: 700; color: <?= $avgAllCpl >= 70 ? 'var(--success)' : ($avgAllCpl > 0 ? 'var(--warning)' : 'var(--text-muted)') ?>; margin-top: 4px;">
            <?= $avgAllCpl > 0 ? $avgAllCpl . '%' : '0%' ?>
        </div>
        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">
            <?= !empty($selectedAngkatan) ? "Angkatan $selectedAngkatan" : "Seluruh angkatan " . htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>
        </div>
    </div>

    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">CPL MEMENUHI TARGET</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success); margin-top: 4px;">
            <?= $cplTercapai ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">/ <?= $totalCpl ?> CPL</span>
        </div>
        <div style="color: var(--success); font-size: 0.8rem; margin-top: 4px;">Rata-rata skor ≥ target 70%</div>
    </div>

    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">CPL PERLU EVALUASI (CQI)</div>
        <div style="font-size: 2rem; font-weight: 700; color: <?= $cplBelumTercapai > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>; margin-top: 4px;">
            <?= $cplBelumTercapai ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">CPL</span>
        </div>
        <div style="color: <?= $cplBelumTercapai > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>; font-size: 0.8rem; margin-top: 4px;">
            <?= $cplBelumTercapai > 0 ? 'Skor di bawah target mutu' : 'Semua CPL terukur memenuhi target' ?>
        </div>
    </div>

    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">MAHASISWA TERUKUR</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--secondary); margin-top: 4px;">
            <?= count($mahasiswaList) ?>
        </div>
        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Mahasiswa pada kurikulum ini</div>
    </div>
</div>

<!-- SECTION 1: RADAR CHART KURIKULUM & TABEL REKAP CPL -->
<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 20px; margin-bottom: 30px;">
    <!-- RADAR CHART KURIKULUM -->
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-chart-radar" style="color: var(--secondary); margin-right: 8px;"></i>
                Radar Profil CPL (<?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>)
            </h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">
                <?= !empty($selectedAngkatan) ? "Angkatan $selectedAngkatan" : "Semua Angkatan" ?>
            </span>
        </div>

        <div style="position: relative; flex: 1; min-height: 340px; display: flex; justify-content: center; align-items: center;">
            <canvas id="angkatanRadarChart"></canvas>
        </div>

        <div style="display: flex; justify-content: center; gap: 24px; margin-top: 15px; font-size: 0.85rem; border-top: 1px solid var(--border); padding-top: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background: rgba(59, 130, 246, 0.35); border: 2px solid #3b82f6; border-radius: 3px;"></span>
                <span style="color: var(--text-main); font-weight: 600;">Rata-rata Capaian (<?= count($rekapAngkatan) ?> CPL)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 20px; height: 0; border-top: 2px dashed #ef4444;"></span>
                <span style="color: var(--text-main); font-weight: 600;">Standar Target Capaian (70%)</span>
            </div>
        </div>
    </div>

    <!-- TABEL STATUS CPL KURIKULUM -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 8px;"></i>
                Matriks Evaluasi Ketercapaian CPL
            </h3>
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                <?= count($rekapAngkatan) ?> CPL (<?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>)
            </span>
        </div>

        <div style="overflow-x: auto; flex: 1;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="background: var(--surface-hover); border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px 16px; width: 14%;">Kode CPL</th>
                        <th style="padding: 12px 16px; width: 44%;">Rumusan CPL</th>
                        <th style="padding: 12px 16px; width: 14%; text-align: center;">Target</th>
                        <th style="padding: 12px 16px; width: 14%; text-align: center;">Rata-rata</th>
                        <th style="padding: 12px 16px; width: 14%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekapAngkatan)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Belum ada CPL terdaftar untuk kurikulum ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapAngkatan as $c): 
                            $statusClass = $c['status'] === 'Tercapai' ? 'rgba(16, 185, 129, 0.15)' : ($c['status'] === 'Belum Tercapai' ? 'rgba(239, 68, 68, 0.15)' : 'rgba(148, 163, 184, 0.15)');
                            $statusColor = $c['status'] === 'Tercapai' ? 'var(--success)' : ($c['status'] === 'Belum Tercapai' ? 'var(--danger)' : 'var(--text-muted)');
                        ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px 16px; font-weight: 700; color: var(--primary);">
                                    <?= htmlspecialchars($c['kode']) ?>
                                </td>
                                <td style="padding: 12px 16px; line-height: 1.4; color: var(--text-main);">
                                    <?= htmlspecialchars($c['rumusan']) ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: center; color: var(--text-muted); font-weight: 600;">
                                    <?= $c['target'] ?>%
                                </td>
                                <td style="padding: 12px 16px; text-align: center; font-weight: 700; font-size: 0.95rem; color: <?= $statusColor ?>;">
                                    <?= $c['avg_score'] > 0 ? $c['avg_score'] . '%' : '-' ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <span style="background: <?= $statusClass ?>; color: <?= $statusColor ?>; padding: 4px 8px; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.75rem; white-space: nowrap;">
                                        <?= htmlspecialchars($c['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: DAFTAR MAHASISWA PORTOFOLIO INDIVIDUAL -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <div style="padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1); flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-users" style="color: var(--secondary); margin-right: 8px;"></i>
                Daftar Mahasiswa - <?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>
                <?= !empty($selectedAngkatan) ? " (Angkatan $selectedAngkatan)" : "" ?>
            </h3>
            <p style="margin: 3px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">
                Pilih mahasiswa untuk membuka rapor komprehensif pemenuhan CPL dan diagram radar individual.
            </p>
        </div>
        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
            Menampilkan <?= count($mahasiswaList) ?> mahasiswa
        </span>
    </div>

    <?php if (empty($mahasiswaList)): ?>
        <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
            <i class="fa-solid fa-user-graduate" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
            Belum ada mahasiswa yang terdaftar pada kurikulum <?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>
            <?= !empty($selectedAngkatan) ? " untuk Angkatan $selectedAngkatan" : "" ?>.
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--surface-hover); border-bottom: 1px solid var(--border);">
                        <th style="padding: 12px 18px; width: 5%;">No</th>
                        <th style="padding: 12px 18px; width: 18%;">NIM (Username)</th>
                        <th style="padding: 12px 18px; width: 35%;">Nama Lengkap</th>
                        <th style="padding: 12px 18px; width: 18%;">Angkatan</th>
                        <th style="padding: 12px 18px; width: 24%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mahasiswaList as $idx => $m): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 18px; color: var(--text-muted);"><?= $idx + 1 ?></td>
                            <td style="padding: 12px 18px; font-family: monospace; font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                <?= htmlspecialchars($m['username']) ?>
                            </td>
                            <td style="padding: 12px 18px; font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars($m['nama_lengkap']) ?>
                            </td>
                            <td style="padding: 12px 18px; color: var(--text-muted);">
                                Angkatan <?= htmlspecialchars((string)($m['angkatan'] ?? substr($m['username'], 0, 4))) ?>
                            </td>
                            <td style="padding: 12px 18px; text-align: right;">
                                <a href="<?= $baseUrl ?>/laporan/detail?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-chart-pie"></i> Lihat Portofolio CPL
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Chart.js Radar Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const radarPayload = <?= json_encode($radarAngkatanPayload) ?>;
    const ctx = document.getElementById('angkatanRadarChart').getContext('2d');

    function getThemeChartConfig(theme) {
        const isLight = theme === 'light';
        return {
            lineColor: isLight ? 'rgba(0, 0, 0, 0.12)' : 'rgba(255, 255, 255, 0.16)',
            labelColor: isLight ? '#000000' : '#ffffff',
            tickColor: isLight ? '#3f3f46' : '#a1a1aa',
            tooltipBg: isLight ? 'rgba(255, 255, 255, 0.98)' : 'rgba(10, 10, 12, 0.98)',
            tooltipTitle: isLight ? '#000000' : '#ffffff',
            tooltipBody: isLight ? '#18181b' : '#f4f4f5',
            tooltipBorder: isLight ? '#e4e4e7' : '#27272a'
        };
    }

    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    let themeConfig = getThemeChartConfig(currentTheme);

    const radarChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: radarPayload.labels,
            datasets: [
                {
                    label: 'Rata-rata Capaian Kurikulum',
                    data: radarPayload.scores,
                    backgroundColor: 'rgba(59, 130, 246, 0.25)',
                    borderColor: '#3b82f6',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Standar Target CPL',
                    data: radarPayload.targets,
                    backgroundColor: 'transparent',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: {
                        color: themeConfig.lineColor
                    },
                    grid: {
                        color: themeConfig.lineColor
                    },
                    pointLabels: {
                        color: themeConfig.labelColor,
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11,
                            weight: '600'
                        }
                    },
                    ticks: {
                        color: themeConfig.tickColor,
                        backdropColor: 'transparent',
                        stepSize: 20,
                        font: {
                            size: 10
                        }
                    },
                    suggestedMin: 0,
                    suggestedMax: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: themeConfig.tooltipBg,
                    titleColor: themeConfig.tooltipTitle,
                    bodyColor: themeConfig.tooltipBody,
                    borderColor: themeConfig.tooltipBorder,
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        title: function(context) {
                            const idx = context[0].dataIndex;
                            return radarPayload.labels[idx];
                        },
                        afterTitle: function(context) {
                            const idx = context[0].dataIndex;
                            const rumusan = radarPayload.rumusans[idx] || '';
                            return rumusan.length > 70 ? rumusan.substring(0, 70) + '...' : rumusan;
                        },
                        label: function(context) {
                            return ` ${context.dataset.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Update chart ketika user mengklik tombol toggle mode
    window.addEventListener('themeChanged', (e) => {
        const newTheme = e.detail?.theme || 'dark';
        const cfg = getThemeChartConfig(newTheme);
        radarChart.options.scales.r.angleLines.color = cfg.lineColor;
        radarChart.options.scales.r.grid.color = cfg.lineColor;
        radarChart.options.scales.r.pointLabels.color = cfg.labelColor;
        radarChart.options.scales.r.ticks.color = cfg.tickColor;
        radarChart.options.plugins.tooltip.backgroundColor = cfg.tooltipBg;
        radarChart.options.plugins.tooltip.titleColor = cfg.tooltipTitle;
        radarChart.options.plugins.tooltip.bodyColor = cfg.tooltipBody;
        radarChart.options.plugins.tooltip.borderColor = cfg.tooltipBorder;
        radarChart.update();
    });
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
