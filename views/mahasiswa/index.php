<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Master Mahasiswa</h2>
        <a href="/obe/public/mahasiswa/create" class="btn" style="background: var(--primary); color: white;"><i class="fa-solid fa-plus"></i> Tambah Mahasiswa</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                <th style="padding: 12px; color: var(--text-secondary);">Nama Lengkap</th>
                <th style="padding: 12px; color: var(--text-secondary);">NIM / Username</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mahasiswa as $m): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; font-weight: bold;"><?= htmlspecialchars($m['nama_lengkap']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($m['username']) ?></td>
                    <td style="padding: 12px; text-align: right;">
                        <a href="/obe/public/khs?id=<?= $m['id'] ?>" class="btn" style="background: var(--primary); color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; margin-right: 5px;" title="Lihat KHS, IPK & Tracking CPL"><i class="fa-solid fa-graduation-cap"></i> KHS & CPL</a>
                        <a href="/obe/public/mahasiswa/edit?id=<?= $m['id'] ?>" class="btn" style="background: var(--warning); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.9em; margin-right: 5px;"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <a href="/obe/public/mahasiswa/delete?id=<?= $m['id'] ?>" onclick="return confirm('Yakin ingin menghapus mahasiswa ini?');" class="btn" style="background: var(--danger); color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.9em;"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($mahasiswa)): ?>
                <tr>
                    <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-secondary);">Belum ada data mahasiswa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
