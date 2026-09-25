<?php
// views/dosen/index.php
$title = "Master Dosen - Sistem OBE";
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Data Dosen</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Daftar dosen pengajar kurikulum OBE beserta identitas akademik (NIDN, NUPTK, Homebase).
        </p>
    </div>
    <a href="<?= $baseUrl ?>/dosen/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Dosen
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>
            <?php
                if ($_GET['success'] == 1) echo "Data dosen baru berhasil ditambahkan!";
                elseif ($_GET['success'] == 2) echo "Data dosen berhasil diperbarui!";
                elseif ($_GET['success'] == 3) echo "Data dosen berhasil dihapus!";
            ?>
        </span>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 45px; text-align: center;">NO</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 260px;">NAMA DOSEN & GELAR</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 140px;">NIDN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 180px;">NUPTK</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">HOMEBASE</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 140px;">AKUN LOGIN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); text-align: right; width: 110px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dosen)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-chalkboard-user" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada data dosen terdaftar.
                    </td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($dosen as $d): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                        <td style="padding: 14px 18px; text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                            <?= $no++ ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem;">
                                <?= htmlspecialchars($d['nama_lengkap']) ?>
                            </div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <?php if (!empty($d['nidn'])): ?>
                                <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--primary); padding: 3px 8px; border-radius: 4px; font-family: monospace; font-size: 0.82rem; font-weight: 700;">
                                    <?= htmlspecialchars($d['nidn']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <?php if (!empty($d['nuptk'])): ?>
                                <span style="font-family: monospace; font-size: 0.82rem; color: var(--text-main); letter-spacing: 0.3px;">
                                    <?= htmlspecialchars($d['nuptk']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <?php if (!empty($d['homebase'])): ?>
                                <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fa-solid fa-building-columns" style="font-size: 0.72rem; margin-right: 4px;"></i>
                                    <?= htmlspecialchars($d['homebase']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 18px; font-family: monospace; font-size: 0.85rem; color: var(--text-muted);">
                            @<?= htmlspecialchars($d['username']) ?>
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= $baseUrl ?>/dosen/edit?id=<?= $d['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Dosen">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="<?= $baseUrl ?>/dosen/delete?id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Dosen" onclick="return confirm('Apakah Anda yakin ingin menghapus data dosen <?= htmlspecialchars($d['nama_lengkap'], ENT_QUOTES) ?>?')">
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
