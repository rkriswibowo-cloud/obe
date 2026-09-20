<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Master Dosen</h2>
        <a href="/obe/public/dosen/create" class="btn" style="background: var(--primary); color: white;">+ Tambah Dosen</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                <th style="padding: 12px; color: var(--text-secondary);">Nama Lengkap</th>
                <th style="padding: 12px; color: var(--text-secondary);">Username</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dosen as $d): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; font-weight: bold;"><?= htmlspecialchars($d['nama_lengkap']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($d['username']) ?></td>
                    <td style="padding: 12px; text-align: right;">
                        <a href="/obe/public/dosen/edit?id=<?= $d['id'] ?>" class="btn" style="background: var(--warning); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; margin-right: 5px;">Edit</a>
                        <a href="/obe/public/dosen/delete?id=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus dosen ini?');" class="btn" style="background: var(--danger); color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9em;">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($dosen)): ?>
                <tr>
                    <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-secondary);">Belum ada data dosen.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
