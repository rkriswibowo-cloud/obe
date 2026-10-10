<?php
$title = 'Laporan Pengukuran Semester';
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
$summary = $report['summary'];
$rekap = $report['rekap'];
$selectedTa = null;
foreach ($tahunAkademik as $ta) {
    if ((int)$ta['id'] === (int)$selectedTaId) $selectedTa = $ta;
}
?>

<div class="semester-report-page">
    <div class="semester-report-header">
        <div>
            <h2><i class="fa-solid fa-calendar-check"></i> Laporan Pengukuran Semester</h2>
            <p>Laporan read-only berdasarkan nilai asesmen pada kelas di periode akademik yang dipilih.</p>
        </div>
        <span class="read-only-badge"><i class="fa-solid fa-shield-halved"></i> Tidak mengubah data</span>
    </div>

    <div class="semester-filter-card">
        <form method="get" action="<?= $baseUrl ?>/laporan-semester">
            <div class="filter-field">
                <label for="tahun_akademik_id">Tahun Akademik / Semester</label>
                <select id="tahun_akademik_id" name="tahun_akademik_id" required>
                    <?php foreach ($tahunAkademik as $ta): ?>
                        <option value="<?= (int)$ta['id'] ?>" <?= (int)$ta['id'] === (int)$selectedTaId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ta['nama']) ?><?= !empty($ta['is_active']) ? ' — Aktif' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label for="kurikulum_id">Kurikulum</label>
                <select id="kurikulum_id" name="kurikulum_id">
                    <option value="">Semua kurikulum</option>
                    <?php foreach ($kurikulumList as $kurikulum): ?>
                        <option value="<?= (int)$kurikulum['id'] ?>" <?= (int)$kurikulum['id'] === (int)$selectedKurikulumId ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($kurikulum['jenjang'] . ' ' . $kurikulum['prodi_nama'] . ' — ' . $kurikulum['nama'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Tampilkan</button>
        </form>
    </div>

    <div class="period-note">
        <i class="fa-solid fa-circle-info"></i>
        <div><strong><?= htmlspecialchars($selectedTa['nama'] ?? 'Belum ada periode') ?></strong><br><span>Hanya asesmen dari kelas pada periode ini yang dihitung.</span></div>
    </div>

    <div class="semester-kpi-grid">
        <div class="semester-kpi"><span>Rata-rata CPL</span><strong><?= number_format((float)$summary['rata_rata'], 2) ?>%</strong><small>Skor CPL terukur</small></div>
        <div class="semester-kpi"><span>Mahasiswa Terukur</span><strong><?= (int)$summary['mahasiswa'] ?></strong><small>Memiliki nilai asesmen</small></div>
        <div class="semester-kpi"><span>Kelas & Mata Kuliah</span><strong><?= (int)$summary['kelas'] ?> / <?= (int)$summary['mata_kuliah'] ?></strong><small>Kelas / mata kuliah</small></div>
        <div class="semester-kpi"><span>CPL Memenuhi Target</span><strong><?= (int)$summary['cpl_tercapai'] ?> / <?= (int)$summary['cpl_terukur'] ?></strong><small>Dari CPL yang terukur</small></div>
    </div>

    <?php if (empty($rekap)): ?>
        <div class="semester-empty">
            <i class="fa-solid fa-chart-column"></i>
            <h3>Belum ada pengukuran pada periode ini</h3>
            <p>Pilih periode atau kurikulum lain, atau lengkapi nilai asesmen pada kelas semester tersebut.</p>
        </div>
    <?php else: ?>
        <div class="semester-content-grid">
            <div class="semester-panel chart-panel">
                <div class="panel-heading"><div><h3>Grafik Ketercapaian CPL</h3><p>Rata-rata skor dibandingkan target masing-masing CPL.</p></div></div>
                <div class="chart-wrap"><canvas id="semesterCplChart"></canvas></div>
            </div>
            <div class="semester-panel course-panel">
                <div class="panel-heading"><div><h3>Mata Kuliah Berkontribusi</h3><p>Sumber nilai pada semester terpilih.</p></div><span><?= count($report['mata_kuliah']) ?> MK</span></div>
                <div class="course-list">
                    <?php foreach ($report['mata_kuliah'] as $mk): ?>
                        <div><strong><?= htmlspecialchars($mk['kode']) ?></strong><span><?= htmlspecialchars($mk['nama']) ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="semester-panel table-panel">
            <div class="panel-heading"><div><h3>Rincian Pengukuran CPL</h3><p>Agregasi Sub-CPMK → CPMK → CPL khusus periode terpilih.</p></div></div>
            <div class="table-responsive">
                <table class="semester-table">
                    <thead><tr><th>CPL</th><th>Rumusan</th><th>Target</th><th>Rata-rata</th><th>Mahasiswa</th><th>Kelulusan</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($rekap as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['kode']) ?></strong></td>
                            <td><?= htmlspecialchars($item['rumusan']) ?></td>
                            <td><?= number_format((float)$item['target'], 2) ?>%</td>
                            <td><strong><?= number_format((float)$item['rata_rata'], 2) ?>%</strong></td>
                            <td><?= (int)$item['mahasiswa_terukur'] ?></td>
                            <td><?= number_format((float)$item['persentase_lulus'], 1) ?>%</td>
                            <td><span class="status-pill status-<?= strtolower(str_replace(' ', '-', $item['status'])) ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.semester-report-page{display:flex;flex-direction:column;gap:20px}.semester-report-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}.semester-report-header h2{margin:0;color:var(--text-main);font-size:1.55rem}.semester-report-header h2 i{color:var(--primary);margin-right:8px}.semester-report-header p,.panel-heading p{margin:5px 0 0;color:var(--text-muted);font-size:.88rem}.read-only-badge{padding:7px 11px;border-radius:999px;color:var(--success);background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.28);font-size:.78rem;font-weight:700}.semester-filter-card,.semester-panel,.semester-kpi,.semester-empty{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);box-shadow:var(--shadow-card)}.semester-filter-card{padding:18px}.semester-filter-card form{display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap}.filter-field{flex:1;min-width:230px}.filter-field label{display:block;margin-bottom:6px;font-size:.8rem;font-weight:700;color:var(--text-main)}.filter-field select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main)}.period-note{display:flex;gap:11px;align-items:center;padding:12px 15px;border:1px solid rgba(var(--primary-rgb),.25);border-radius:var(--radius-sm);background:rgba(var(--primary-rgb),.07);color:var(--text-main);font-size:.85rem}.period-note i{color:var(--primary)}.period-note span{color:var(--text-muted)}.semester-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}.semester-kpi{padding:18px}.semester-kpi span{display:block;color:var(--text-muted);font-size:.76rem;font-weight:700;text-transform:uppercase}.semester-kpi strong{display:block;color:var(--primary);font-size:1.75rem;margin:5px 0}.semester-kpi small{color:var(--text-muted)}.semester-content-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(260px,1fr);gap:18px}.semester-panel{padding:20px}.panel-heading{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:16px}.panel-heading h3{font-size:1rem;margin:0;color:var(--text-main)}.panel-heading>span{color:var(--primary);font-weight:700}.chart-wrap{position:relative;min-height:320px}.course-list{display:flex;flex-direction:column;gap:8px;max-height:320px;overflow:auto}.course-list div{display:flex;flex-direction:column;padding:10px 12px;background:var(--surface-hover);border:1px solid var(--border);border-radius:var(--radius-sm)}.course-list strong{color:var(--primary);font-size:.8rem}.course-list span{color:var(--text-main);font-size:.82rem;margin-top:2px}.table-responsive{overflow:auto}.semester-table{width:100%;border-collapse:collapse;font-size:.84rem}.semester-table th,.semester-table td{padding:11px 12px;border-bottom:1px solid var(--border);text-align:left;color:var(--text-main)}.semester-table th{color:var(--text-muted);font-size:.73rem;text-transform:uppercase;background:var(--table-header-bg)}.semester-table td:nth-child(2){min-width:300px}.status-pill{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:.72rem;font-weight:700;white-space:nowrap}.status-tercapai{color:var(--success);background:rgba(16,185,129,.12)}.status-belum-tercapai{color:var(--danger);background:rgba(239,68,68,.12)}.status-belum-diukur{color:var(--text-muted);background:var(--surface-hover)}.semester-empty{text-align:center;padding:45px 20px}.semester-empty i{font-size:2rem;color:var(--text-muted)}.semester-empty h3{color:var(--text-main);margin:12px 0 5px}.semester-empty p{color:var(--text-muted);margin:0}@media(max-width:850px){.semester-content-grid{grid-template-columns:1fr}.semester-filter-card form .btn{width:100%;justify-content:center}}
[data-theme="light"] .semester-filter-card,[data-theme="light"] .semester-panel,[data-theme="light"] .semester-kpi,[data-theme="light"] .semester-empty{border-color:#d4d4d8;box-shadow:0 3px 12px rgba(15,23,42,.07)}
</style>

<?php if (!empty($rekap)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('semesterCplChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const labels = <?= json_encode(array_column($rekap, 'kode'), JSON_UNESCAPED_UNICODE) ?>;
    const scores = <?= json_encode(array_map('floatval', array_column($rekap, 'rata_rata'))) ?>;
    const targets = <?= json_encode(array_map('floatval', array_column($rekap, 'target'))) ?>;
    const buildColors = () => {
        const light = document.documentElement.getAttribute('data-theme') === 'light';
        return { text: light ? '#3f3f46' : '#d4d4d8', grid: light ? 'rgba(15,23,42,.1)' : 'rgba(255,255,255,.1)' };
    };
    const colors = buildColors();
    const chart = new Chart(canvas, {type:'bar',data:{labels,datasets:[{label:'Rata-rata CPL',data:scores,backgroundColor:'rgba(56,189,248,.55)',borderColor:'#0284c7',borderWidth:1,borderRadius:5},{label:'Target',data:targets,type:'line',borderColor:'#f59e0b',backgroundColor:'#f59e0b',borderDash:[6,4],pointRadius:3,tension:.2}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,max:100,ticks:{color:colors.text},grid:{color:colors.grid}},x:{ticks:{color:colors.text},grid:{display:false}}},plugins:{legend:{labels:{color:colors.text}}}}});
    window.addEventListener('themeChanged', function () {
        const nextColors = buildColors();
        chart.options.scales.y.ticks.color = nextColors.text;
        chart.options.scales.y.grid.color = nextColors.grid;
        chart.options.scales.x.ticks.color = nextColors.text;
        chart.options.plugins.legend.labels.color = nextColors.text;
        chart.update('none');
    });
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
