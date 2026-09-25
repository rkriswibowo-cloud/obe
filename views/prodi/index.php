<?php
// views/prodi/index.php
$title = "Master Program Studi - Sistem OBE";
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';

// Hitung ringkasan statistik prodi
$totalProdi = count($prodiList);
$activeProdi = count(array_filter($prodiList, fn($p) => $p['status'] === 'ACTIVE'));
$totalKurikulumAll = array_sum(array_column($prodiList, 'total_kurikulum'));
$totalDosenAll = array_sum(array_column($prodiList, 'total_dosen'));
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Program Studi</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Kelola data program studi untuk membagi dan mengorganisasi kurikulum OBE per jurusan/prodi.
        </p>
    </div>
    <a href="<?= $baseUrl ?>/prodi/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Program Studi
    </a>
</div>

<!-- KPI Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--surface); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Program Studi</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text-main); margin-top: 4px;">
            <?= $totalProdi ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
            <?= $activeProdi ?> berstatus aktif
        </div>
    </div>

    <div style="background: var(--surface); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Kurikulum Terkelola</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--primary); margin-top: 4px;">
            <?= $totalKurikulumAll ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
            Dokumen kurikulum OBE
        </div>
    </div>

    <div style="background: var(--surface); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Dosen Homebase</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--success); margin-top: 4px;">
            <?= $totalDosenAll ?>
        </div>
        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
            Tenaga pendidik terdaftar
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>
            <?php
                if ($_GET['success'] == 1) echo "Program studi baru berhasil ditambahkan!";
                elseif ($_GET['success'] == 2) echo "Data program studi berhasil diperbarui!";
                elseif ($_GET['success'] == 3) echo "Program studi berhasil dihapus!";
            ?>
        </span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 80px;">KODE</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 220px;">PROGRAM STUDI</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 220px;">FAKULTAS</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">KETUA PRODI (KAPRODI)</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 130px; text-align: center;">KURIKULUM</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 90px; text-align: center;">STATUS</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); text-align: right; width: 110px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prodiList)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-building-columns" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada program studi terdaftar.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($prodiList as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                        <td style="padding: 14px 18px; font-weight: 700;">
                            <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-family: monospace;">
                                <?= htmlspecialchars($p['kode']) ?>
                            </span>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                <?= htmlspecialchars($p['nama']) ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                Jenjang: <strong><?= htmlspecialchars($p['jenjang']) ?></strong>
                            </div>
                        </td>
                        <td style="padding: 14px 18px; font-size: 0.88rem; color: var(--text-muted);">
                            <i class="fa-solid fa-school" style="font-size: 0.75rem; margin-right: 5px; opacity: 0.6;"></i>
                            <?= htmlspecialchars($p['fakultas']) ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <?php if (!empty($p['nama_kaprodi'])): ?>
                                <div style="font-weight: 600; color: var(--text-main); font-size: 0.88rem;">
                                    <?= htmlspecialchars($p['nama_kaprodi']) ?>
                                </div>
                                <?php if (!empty($p['nidn_kaprodi'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace; margin-top: 2px;">
                                        NIDN: <?= htmlspecialchars($p['nidn_kaprodi']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Belum ditentukan</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            <a href="<?= $baseUrl ?>/kurikulum?prodi_id=<?= $p['id'] ?>" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--primary); padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Lihat Kurikulum Prodi Ini">
                                <i class="fa-solid fa-book" style="font-size: 0.72rem;"></i>
                                <?= (int)$p['total_kurikulum'] ?> Kurikulum
                            </a>
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            <span style="background: <?= $p['status'] == 'ACTIVE' ? 'rgba(16, 185, 129, 0.12)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= $p['status'] == 'ACTIVE' ? 'var(--success)' : 'var(--text-muted)' ?>; border: 1px solid <?= $p['status'] == 'ACTIVE' ? 'rgba(16, 185, 129, 0.3)' : 'var(--border)' ?>; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= $baseUrl ?>/prodi/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Program Studi">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="<?= $baseUrl ?>/prodi/delete?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Program Studi" onclick="return confirm('Apakah Anda yakin ingin menghapus Program Studi <?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
