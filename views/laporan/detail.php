<?php
$title = "Portofolio CPL: " . htmlspecialchars($mahasiswa['nama_lengkap']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Mahasiswa'): ?>
                <a href="<?= $baseUrl ?>/laporan" class="btn btn-sm btn-secondary" style="padding: 4px 8px;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            <?php endif; ?>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Portofolio Capaian Pembelajaran Lulusan (CPL)</h2>
        </div>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
            Analisis radar chart dan penelusuran ketercapaian kompetensi mahasiswa individual.
        </p>
    </div>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Mahasiswa'): ?>
        <a href="<?= $baseUrl ?>/laporan" class="btn btn-secondary">
            <i class="fa-solid fa-users"></i> Kembali ke Dashboard Angkatan
        </a>
    <?php endif; ?>
</div>

<!-- Identitas Mahasiswa & Summary Cards -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <h4 style="margin: 0 0 14px 0; color: var(--text-main); font-size: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <i class="fa-solid fa-id-card" style="color: var(--primary); margin-right: 8px;"></i> Identitas Mahasiswa
        </h4>
        <table style="width: 100%; font-size: 0.9rem;">
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted); width: 140px;">NIM / Username</td>
                <td style="padding: 6px 0; font-weight: 700; color: var(--text-main); font-family: monospace; font-size: 1rem;">
                    : <?= htmlspecialchars($mahasiswa['username']) ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted);">Nama Lengkap</td>
                <td style="padding: 6px 0; font-weight: 600; color: var(--text-main);">
                    : <?= htmlspecialchars($mahasiswa['nama_lengkap']) ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted);">Program Studi</td>
                <td style="padding: 6px 0; color: var(--text-main);">
                    : Sistem Informasi
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: var(--text-muted);">Angkatan</td>
                <td style="padding: 6px 0; color: var(--text-main);">
                    : <?= htmlspecialchars(substr($mahasiswa['username'], 0, 4)) ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">TOTAL CPL KURIKULUM</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-top: 4px;"><?= $totalCpl ?></div>
            <div style="color: var(--text-muted); font-size: 0.75rem;">Standar kelulusan prodi</div>
        </div>

        <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">CPL MEMENUHI TARGET</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--success); margin-top: 4px;"><?= $tercapaiCount ?></div>
            <div style="color: var(--success); font-size: 0.75rem;">Capai target ≥ 70%</div>
        </div>

        <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">CPL DI BAWAH TARGET</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--danger); margin-top: 4px;"><?= $belumTercapaiCount ?></div>
            <div style="color: var(--danger); font-size: 0.75rem;">Perlu evaluasi/remediasi</div>
        </div>

        <div style="background: var(--surface); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: center;">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">CPL BELUM DIUKUR</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--warning); margin-top: 4px;"><?= $belumDiukurCount ?></div>
            <div style="color: var(--warning); font-size: 0.75rem;">Mata kuliah belum ditempuh</div>
        </div>
    </div>
</div>

<!-- Main Section: Radar Chart & Progress Bars -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- RADAR CHART CPL -->
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-chart-pie" style="color: var(--primary); margin-right: 8px;"></i>
                Radar Chart Ketercapaian CPL
            </h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Visualisasi Multi-Dimensi</span>
        </div>
        
        <div style="position: relative; flex: 1; min-height: 350px; display: flex; justify-content: center; align-items: center;">
            <canvas id="cplRadarChart"></canvas>
        </div>

        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 15px; font-size: 0.8rem; border-top: 1px solid var(--border); padding-top: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background: rgba(99, 102, 241, 0.4); border: 2px solid #6366f1; border-radius: 3px;"></span>
                <span style="color: var(--text-main); font-weight: 500;">Capaian Mahasiswa</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 18px; height: 0; border-top: 2px dashed #f59e0b;"></span>
                <span style="color: var(--text-main); font-weight: 500;">Batas Target CPL</span>
            </div>
        </div>
    </div>

    <!-- DETAIL KETERCAPAIAN PROGRESS BAR -->
    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-bars-progress" style="color: var(--success); margin-right: 8px;"></i>
                Rincian Skor Pemenuhan CPL
            </h3>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Skor Aktual vs Target</span>
        </div>

        <?php if (empty($cpl_data)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 30px;">Belum ada data capaian CPL yang tercatat.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px; max-height: 420px; overflow-y: auto; padding-right: 6px;">
                <?php foreach ($cpl_data as $cpl): 
                    $score = $cpl['score'];
                    $target = $cpl['target'] ?? 70.00;
                    $isPassed = $cpl['is_measured'] && ($score >= $target);
                    $color = !$cpl['is_measured'] ? 'var(--text-muted)' : ($isPassed ? 'var(--success)' : 'var(--danger)');
                ?>
                    <div style="background: var(--background); padding: 12px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <div>
                                <span style="font-weight: 700; color: var(--primary); font-size: 0.9rem;"><?= htmlspecialchars($cpl['kode']) ?></span>
                                <span style="color: var(--text-muted); font-size: 0.75rem; margin-left: 8px;">
                                    Target: <?= $target ?>%
                                </span>
                            </div>
                            <div>
                                <?php if ($cpl['is_measured']): ?>
                                    <span style="font-weight: 700; font-size: 0.95rem; color: <?= $color ?>;">
                                        <?= number_format($score, 1) ?>%
                                    </span>
                                    <span style="font-size: 0.75rem; margin-left: 6px; padding: 2px 6px; border-radius: 3px; font-weight: 600; background: <?= $isPassed ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= $color ?>;">
                                        <?= $isPassed ? 'Tercapai' : 'Belum Capai' ?>
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic; background: var(--surface-hover); padding: 2px 6px; border-radius: 3px;">
                                        Belum Diukur
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px; line-height: 1.3;">
                            <?= htmlspecialchars($cpl['rumusan']) ?>
                        </div>

                        <!-- Dual Progress Bar (Score vs Target) -->
                        <div style="width: 100%; height: 8px; background: var(--surface); border-radius: 999px; overflow: hidden; position: relative;">
                            <?php if ($cpl['is_measured']): ?>
                                <div style="width: <?= min($score, 100) ?>%; height: 100%; background: <?= $color ?>; border-radius: 999px;"></div>
                            <?php else: ?>
                                <div style="width: 0%; height: 100%;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION TRACEABILITY & DRILL-DOWN AUDIT OBE -->
<div style="background: var(--surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-sitemap" style="color: var(--primary); margin-right: 8px;"></i>
                Traceability Audit: Penelusuran Sumber Nilai CPL
            </h3>
            <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">
                Diverifikasi langsung: CPL &rarr; Mata Kuliah &rarr; CPMK &rarr; Sub-CPMK &rarr; Bukti Asesmen & Nilai Mahasiswa
            </p>
        </div>
        <span style="font-size: 0.8rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 999px; border: 1px solid rgba(99, 102, 241, 0.2); font-weight: 600;">
            Single Source of Truth
        </span>
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($cpl_data as $cpl): 
            $cplId = $cpl['id'];
            $cplTrace = $traceability[$cplId] ?? [];
        ?>
            <details style="background: var(--background); border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden;">
                <summary style="padding: 14px 18px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; font-weight: 600; background: var(--surface-hover);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: var(--primary); color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;"><?= htmlspecialchars($cpl['kode']) ?></span>
                        <span style="color: var(--text-main); font-size: 0.95rem;"><?= htmlspecialchars($cpl['rumusan']) ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 0.85rem; color: <?= $cpl['is_measured'] ? (($cpl['score'] >= ($cpl['target'] ?? 70)) ? 'var(--success)' : 'var(--danger)') : 'var(--text-muted)' ?>; font-weight: 700;">
                            <?= $cpl['is_measured'] ? number_format($cpl['score'], 1) . '%' : 'Belum Diukur' ?>
                        </span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-muted);"></i>
                    </div>
                </summary>

                <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                    <?php if (empty($cplTrace)): ?>
                        <div style="color: var(--text-muted); font-size: 0.85rem; font-style: italic; padding: 10px 0;">
                            Belum ada mata kuliah atau CPMK yang terhubung ke CPL ini pada kurikulum aktif.
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($cplTrace as $mkId => $mkData): ?>
                                <div style="border-left: 3px solid var(--primary); padding-left: 14px;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 8px;">
                                        <i class="fa-solid fa-book" style="color: var(--primary); margin-right: 6px;"></i>
                                        <?= htmlspecialchars($mkData['mk_kode']) ?> - <?= htmlspecialchars($mkData['mk_nama']) ?>
                                    </div>

                                    <?php foreach ($mkData['cpmks'] as $cpmkId => $cpmkData): ?>
                                        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 10px;">
                                            <div style="font-weight: 600; color: var(--info); font-size: 0.85rem; margin-bottom: 6px;">
                                                <?= htmlspecialchars($cpmkData['cpmk_kode']) ?>: <?= htmlspecialchars($cpmkData['cpmk_rumusan']) ?>
                                            </div>

                                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-top: 8px;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); text-align: left;">
                                                        <th style="padding: 6px 8px;">Sub-CPMK</th>
                                                        <th style="padding: 6px 8px;">Deskripsi / Indikator</th>
                                                        <th style="padding: 6px 8px; text-align: center;">Bobot</th>
                                                        <th style="padding: 6px 8px; text-align: center;">Kelas</th>
                                                        <th style="padding: 6px 8px; text-align: right;">Nilai Mahasiswa</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($cpmkData['sub_cpmks'] as $sub): 
                                                        $nilaiVal = $sub['nilai'];
                                                        $hasNilai = $nilaiVal !== null;
                                                    ?>
                                                        <tr style="border-bottom: 1px dashed rgba(255,255,255,0.07);">
                                                            <td style="padding: 6px 8px; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($sub['sub_kode']) ?></td>
                                                            <td style="padding: 6px 8px; color: var(--text-muted);"><?= htmlspecialchars($sub['sub_rumusan']) ?></td>
                                                            <td style="padding: 6px 8px; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($sub['sub_bobot']) ?>%</td>
                                                            <td style="padding: 6px 8px; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($sub['nama_kelas'] ?? '-') ?></td>
                                                            <td style="padding: 6px 8px; text-align: right;">
                                                                <?php if ($hasNilai): ?>
                                                                    <span style="font-weight: 700; color: <?= $nilaiVal >= 70 ? 'var(--success)' : 'var(--danger)' ?>;">
                                                                        <?= number_format($nilaiVal, 1) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span style="color: var(--text-muted); font-style: italic;">Belum Dinilai</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</div>

<!-- Chart.js Radar Chart Initialization -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const radarData = <?= json_encode($radarData) ?>;

    const ctx = document.getElementById('cplRadarChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [
                {
                    label: 'Capaian Mahasiswa',
                    data: radarData.scores,
                    backgroundColor: 'rgba(99, 102, 241, 0.25)',
                    borderColor: '#6366f1',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#6366f1',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Batas Target CPL',
                    data: radarData.targets,
                    backgroundColor: 'transparent',
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#f59e0b',
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
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    pointLabels: {
                        color: '#f8fafc',
                        font: {
                            family: "'Inter', sans-serif",
                            size: 11,
                            weight: '600'
                        }
                    },
                    ticks: {
                        color: '#94a3b8',
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
                    backgroundColor: 'rgba(15, 17, 26, 0.95)',
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
                    borderColor: '#2e334a',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        title: function(context) {
                            const idx = context[0].dataIndex;
                            return radarData.labels[idx];
                        },
                        afterTitle: function(context) {
                            const idx = context[0].dataIndex;
                            const rumusan = radarData.rumusans[idx] || '';
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
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
