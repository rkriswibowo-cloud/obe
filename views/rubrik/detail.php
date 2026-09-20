<?php
$title = "Detail Matriks Rubrik: " . htmlspecialchars($rubrik['nama']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <a href="<?= $baseUrl ?>/rubrik" class="btn btn-sm btn-secondary" style="padding: 4px 8px;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
                Matriks Rubrik Penilaian OBE
            </h2>
        </div>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
            <?= htmlspecialchars($rubrik['nama']) ?> &bull; Jenis: <strong><?= htmlspecialchars($rubrik['jenis']) ?></strong>
        </p>
    </div>

    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-print"></i> Cetak / Ekspor PDF
        </button>
        <a href="<?= $baseUrl ?>/rubrik" class="btn btn-primary">
            <i class="fa-solid fa-list"></i> Semua Rubrik
        </a>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
        Rubrik penilaian baru berhasil dibuat dan disimpan ke database!
    </div>
<?php endif; ?>

<!-- Header Info Card -->
<div class="card" style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">NAMA RUBRIK</div>
            <div style="font-weight: 700; color: var(--text-main); font-size: 1.1rem; margin-top: 3px;">
                <?= htmlspecialchars($rubrik['nama']) ?>
            </div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">JENIS PENILAIAN</div>
            <div style="margin-top: 5px;">
                <span style="background: rgba(99, 102, 241, 0.15); color: var(--primary); padding: 3px 10px; border-radius: 999px; font-weight: 600; font-size: 0.85rem; border: 1px solid rgba(99, 102, 241, 0.3);">
                    Rubrik <?= htmlspecialchars($rubrik['jenis']) ?>
                </span>
            </div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">JUMLAH TINGKATAN (LEVEL)</div>
            <div style="font-weight: 700; color: var(--info); font-size: 1.1rem; margin-top: 3px;">
                <?= count($rubrik['levels']) ?> Level Skala
            </div>
        </div>
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">TOTAL BOBOT KRITERIA</div>
            <?php 
                $totalBobot = array_sum(array_column($rubrik['kriteria'], 'bobot'));
            ?>
            <div style="font-weight: 700; color: <?= $totalBobot == 100 ? 'var(--success)' : 'var(--warning)' ?>; font-size: 1.1rem; margin-top: 3px;">
                <?= $totalBobot ?>% <?= $totalBobot == 100 ? '✓ (Valid)' : '⚠' ?>
            </div>
        </div>
    </div>
</div>

<!-- Rubric Matrix Table -->
<div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
    <div style="padding: 16px 20px; background: var(--surface-hover); border-bottom: 2px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0; color: var(--text-main); font-size: 1rem; font-weight: 700;">
            <i class="fa-solid fa-table" style="color: var(--primary); margin-right: 8px;"></i>
            Tabel Matriks Kriteria & Deskriptor Kinerja
        </h4>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Format Standar Akreditasi OBE</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.15); color: var(--text-muted);">
                    <th style="padding: 14px 16px; width: 22%; min-width: 180px;">Kriteria Penilaian</th>
                    <th style="padding: 14px 12px; width: 8%; text-align: center;">Bobot</th>
                    <?php foreach ($rubrik['levels'] as $lvl): ?>
                        <th style="padding: 14px 16px; text-align: center; border-left: 1px solid var(--border);">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">
                                <?= htmlspecialchars($lvl['level_nama']) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--warning); margin-top: 2px; font-weight: 600;">
                                Skor: <?= $lvl['skor'] ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rubrik['kriteria'] as $krit): 
                    $kId = $krit['id'];
                ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 16px; vertical-align: top;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; line-height: 1.4;">
                                <?= htmlspecialchars($krit['kriteria']) ?>
                            </div>
                        </td>
                        <td style="padding: 16px 12px; text-align: center; vertical-align: top;">
                            <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.85rem;">
                                <?= $krit['bobot'] ?>%
                            </span>
                        </td>
                        <?php foreach ($rubrik['levels'] as $lvl): 
                            $lId = $lvl['id'];
                            $desc = $rubrik['descriptors'][$kId][$lId] ?? '-';
                        ?>
                            <td style="padding: 16px; vertical-align: top; border-left: 1px solid var(--border); line-height: 1.5; color: var(--text-main); font-size: 0.83rem;">
                                <?= nl2br(htmlspecialchars($desc)) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: var(--surface-hover); font-weight: 700; border-top: 2px solid var(--border);">
                    <td style="padding: 12px 16px; color: var(--text-main);">Total Kontribusi</td>
                    <td style="padding: 12px; text-align: center; color: var(--success); font-size: 0.9rem;">
                        <?= $totalBobot ?>%
                    </td>
                    <td colspan="<?= count($rubrik['levels']) ?>" style="padding: 12px 16px; color: var(--text-muted); font-size: 0.8rem; font-style: italic;">
                        * Penilaian aktual mahasiswa dilakukan dengan memilih level performa pada setiap kriteria penilaian.
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .btn {
        display: none !important;
    }
    .main-content, .content-area {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .card {
        border: 1px solid #ddd !important;
        background: #fff !important;
        color: #000 !important;
        box-shadow: none !important;
    }
    th, td {
        color: #000 !important;
        border-color: #ddd !important;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
