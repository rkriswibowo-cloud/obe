<?php
$title = "Dashboard Ketercapaian CPL Angkatan";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Dashboard Ketercapaian CPL Angkatan</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Analisis agregat pemenuhan Capaian Pembelajaran Lulusan (CPL) per angkatan mahasiswa dan evaluasi mutu kurikulum.
        </p>
    </div>

    <!-- Filter Angkatan -->
    <form method="GET" action="<?= $baseUrl ?>/laporan" style="display: flex; gap: 10px; align-items: center;">
        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); white-space: nowrap;">
            <i class="fa-solid fa-filter" style="margin-right: 4px;"></i> Angkatan:
        </label>
        <select name="angkatan" onchange="this.form.submit()" style="padding: 8px 12px; font-size: 0.9rem; min-width: 160px;">
            <option value="">-- Semua Angkatan --</option>
            <?php foreach ($daftarAngkatan as $ang): ?>
                <option value="<?= htmlspecialchars($ang) ?>" <?= $selectedAngkatan == $ang ? 'selected' : '' ?>>
                    Angkatan <?= htmlspecialchars($ang) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($selectedAngkatan)): ?>
            <a href="<?= $baseUrl ?>/laporan" class="btn btn-sm btn-secondary" title="Reset Filter">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- KPI Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">RATA-RATA CAPAIAN CPL</div>
        <div style="font-size: 2rem; font-weight: 700; color: <?= $avgAllCpl >= 70 ? 'var(--success)' : 'var(--warning)' ?>; margin-top: 4px;">
            <?= $avgAllCpl ?>%
        </div>
        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">
            <?= !empty($selectedAngkatan) ? "Angkatan $selectedAngkatan" : "Seluruh angkatan aktif" ?>
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
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger); margin-top: 4px;">
            <?= $cplBelumTercapai ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">CPL</span>
        </div>
        <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px;">Skor di bawah target mutu</div>
    </div>

    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">MAHASISWA TERUKUR</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--secondary); margin-top: 4px;">
            <?= count($mahasiswaList) ?>
        </div>
        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Mahasiswa terdaftar di kelas</div>
    </div>
</div>

<!-- SECTION 1: RADAR CHART ANGKATAN & TABEL REKAP CPL -->
<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 20px; margin-bottom: 30px;">
    <!-- RADAR CHART ANGKATAN -->
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-chart-pie" style="color: var(--secondary); margin-right: 8px;"></i>
                Radar Profil Ketercapaian CPL <?= !empty($selectedAngkatan) ? "Angkatan $selectedAngkatan" : "Prodi" ?>
            </h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Chart.js Radar</span>
        </div>

        <div style="position: relative; flex: 1; min-height: 340px; display: flex; justify-content: center; align-items: center;">
            <canvas id="angkatanRadarChart"></canvas>
        </div>

        <div style="display: flex; justify-content: center; gap: 24px; margin-top: 15px; font-size: 0.85rem; border-top: 1px solid var(--border); padding-top: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background: rgba(59, 130, 246, 0.35); border: 2px solid #3b82f6; border-radius: 3px;"></span>
                <span style="color: var(--text-main); font-weight: 600;">Rata-rata Capaian Angkatan</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 20px; height: 0; border-top: 2px dashed #ef4444;"></span>
                <span style="color: var(--text-main); font-weight: 600;">Standar Target Capaian (70%)</span>
            </div>
        </div>
    </div>

    <!-- TABEL STATUS CPL ANGKATAN -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 8px;"></i>
                Matriks Evaluasi Ketercapaian CPL
            </h3>
            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($rekapAngkatan) ?> CPL Terdaftar</span>
        </div>

        <div style="overflow-x: auto; flex: 1;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="background: var(--surface-hover); border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px 16px; width: 15%;">Kode CPL</th>
                        <th style="padding: 12px 16px; width: 40%;">Rumusan CPL</th>
                        <th style="padding: 12px 16px; width: 15%; text-align: center;">Target</th>
                        <th style="padding: 12px 16px; width: 15%; text-align: center;">Rata-rata</th>
                        <th style="padding: 12px 16px; width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: DAFTAR MAHASISWA PORTOFOLIO INDIVIDUAL -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <div style="padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
        <div>
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Daftar Mahasiswa & Portofolio CPL Individu</h3>
            <p style="margin: 3px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">
                Pilih mahasiswa untuk membuka rapor komprehensif pemenuhan CPL dan diagram radar individual.
            </p>
        </div>
        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
            Menampilkan <?= count($mahasiswaList) ?> mahasiswa
        </span>
    </div>

    <?php if (empty($mahasiswaList)): ?>
        <div style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
            <i class="fa-solid fa-user-graduate" style="font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
            Tidak ada mahasiswa ditemukan untuk filter yang dipilih.
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
                                Angkatan <?= htmlspecialchars($m['angkatan'] ?? substr($m['username'], 0, 4)) ?>
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
                    label: 'Rata-rata Capaian Angkatan',
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
