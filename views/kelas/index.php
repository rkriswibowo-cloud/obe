<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Manajemen Kelas</h2>
        <a href="/obe/public/kelas/create" class="btn" style="background: var(--primary); color: white;">+ Tambah Kelas</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                <th style="padding: 12px; color: var(--text-secondary);">Tahun Akademik</th>
                <th style="padding: 12px; color: var(--text-secondary);">Mata Kuliah</th>
                <th style="padding: 12px; color: var(--text-secondary);">Nama Kelas</th>
                <th style="padding: 12px; color: var(--text-secondary);">Dosen Pengampu</th>
                <th style="padding: 12px; color: var(--text-secondary);">Mhs</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px;"><?= htmlspecialchars($k['tahun_akademik_nama']) ?></td>
                    <td style="padding: 12px;">
                        <strong><?= htmlspecialchars($k['mata_kuliah_kode']) ?></strong><br>
                        <?= htmlspecialchars($k['mata_kuliah_nama']) ?>
                    </td>
                    <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($k['nama_kelas']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($k['dosen_nama']) ?></td>
                    <td style="padding: 12px;">
                        <span style="background: var(--bg-hover); padding: 4px 8px; border-radius: 4px; font-size: 0.9em;">
                            <?= htmlspecialchars($k['jumlah_mahasiswa']) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: right;">
                        <a href="/obe/public/kelas/peserta?id=<?= $k['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 0.9em; background: var(--success); color: white; margin-right: 4px;">👤 Peserta</a>
                        <a href="/obe/public/kelas/edit?id=<?= $k['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 0.9em; background: var(--bg-hover); color: var(--text-primary); margin-right: 4px;">✏️ Edit</a>
                        <a href="/obe/public/kelas/delete?id=<?= $k['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 0.9em; background: var(--danger); color: white;" onclick="return confirm('Hapus kelas ini?');">🗑️ Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($kelas)): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-secondary);">Belum ada data kelas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
