<?php
$title = "Master Rubrik Penilaian - OBE";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
            <i class="fa-solid fa-table-cells-large" style="color: var(--primary); margin-right: 8px;"></i>
            Master Rubrik Penilaian
        </h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Instrumen penilaian autentik OBE berbasis kriteria, level capaian, dan deskriptor performa mahasiswa.
        </p>
    </div>
    <div>
        <a href="<?= $baseUrl ?>/rubrik/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Tambah Rubrik Baru
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
        <?php if ($_GET['success'] === 'deleted'): ?>
            Rubrik berhasil dihapus.
        <?php else: ?>
            Data rubrik berhasil diproses.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <?php if (empty($rubrikList)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 16px auto;">
                <i class="fa-solid fa-table-list"></i>
            </div>
            <h4 style="margin: 0 0 6px 0; color: var(--text-main);">Belum Ada Rubrik Penilaian</h4>
            <p style="color: var(--text-muted); font-size: 0.9rem; max-width: 480px; margin: 0 auto 20px auto;">
                Buat rubrik analitik untuk mengukur kinerja mahasiswa pada Sub-CPMK tugas, proyek, presentasi, atau ujian praktikum.
            </p>
            <a href="<?= $baseUrl ?>/rubrik/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Buat Rubrik Sekarang
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: var(--surface-hover); border-bottom: 2px solid var(--border); color: var(--text-muted);">
                        <th style="padding: 14px 18px; width: 35%;">Nama Rubrik</th>
                        <th style="padding: 14px 18px; width: 15%;">Jenis Rubrik</th>
                        <th style="padding: 14px 18px; width: 15%; text-align: center;">Kriteria</th>
                        <th style="padding: 14px 18px; width: 15%; text-align: center;">Level Skor</th>
                        <th style="padding: 14px 18px; width: 10%; text-align: center;">Bobot</th>
                        <th style="padding: 14px 18px; width: 10%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rubrikList as $r): 
                        $totalBobot = floatval($r['total_bobot']);
                        $isBobotValid = ($totalBobot == 100.00);
                    ?>
                        <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                    <a href="<?= $baseUrl ?>/rubrik/detail?id=<?= $r['id'] ?>" style="color: var(--text-main); text-decoration: none;">
                                        <?= htmlspecialchars($r['nama']) ?>
                                    </a>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                    Status: <span style="color: var(--success); font-weight: 600;"><?= htmlspecialchars($r['status']) ?></span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span style="background: rgba(99, 102, 241, 0.12); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.25); padding: 4px 10px; border-radius: 999px; font-size: 0.8rem; font-weight: 600;">
                                    <?= htmlspecialchars($r['jenis']) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 18px; text-align: center; font-weight: 600; color: var(--text-main);">
                                <?= $r['total_kriteria'] ?> Kriteria
                            </td>
                            <td style="padding: 14px 18px; text-align: center; color: var(--info); font-weight: 600;">
                                <?= $r['total_level'] ?> Level
                            </td>
                            <td style="padding: 14px 18px; text-align: center;">
                                <span style="font-weight: 700; color: <?= $isBobotValid ? 'var(--success)' : 'var(--warning)' ?>;">
                                    <?= $totalBobot ?>%
                                </span>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="<?= $baseUrl ?>/rubrik/detail?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary" title="Lihat Matriks Rubrik">
                                        <i class="fa-solid fa-eye"></i> Matriks
                                    </a>
                                    <a href="<?= $baseUrl ?>/rubrik/delete?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus rubrik ini beserta kriteria dan deskriptor di dalamnya?')" title="Hapus Rubrik">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
