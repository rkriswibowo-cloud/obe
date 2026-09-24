<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Master Tahun Akademik</h2>
        <a href="/obe/public/tahun-akademik/create" class="btn" style="background: var(--primary); color: white;"><i class="fa-solid fa-plus"></i> Tambah Tahun Akademik</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                <th style="padding: 12px; color: var(--text-secondary);">Nama / Semester</th>
                <th style="padding: 12px; color: var(--text-secondary);">Status Aktif</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tahun_akademik as $ta): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; font-weight: bold;"><?= htmlspecialchars($ta['nama']) ?></td>
                    <td style="padding: 12px;">
                        <?php if($ta['is_active']): ?>
                            <span style="background: var(--success); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">Aktif</span>
                        <?php else: ?>
                            <span style="background: var(--bg-hover); color: var(--text-muted); padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; text-align: right;">
                        <a href="/obe/public/tahun-akademik/edit?id=<?= $ta['id'] ?>" class="btn" style="background: var(--warning); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; margin-right: 5px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <a href="/obe/public/tahun-akademik/delete?id=<?= $ta['id'] ?>" onclick="return confirm('Yakin ingin menghapus?');" class="btn" style="background: var(--danger); color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9em;"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($tahun_akademik)): ?>
                <tr>
                    <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-secondary);">Belum ada data tahun akademik.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
