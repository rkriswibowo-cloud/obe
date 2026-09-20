<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Kelola Peserta Kelas: <?= htmlspecialchars($kelas['nama_kelas']) ?> (<?= htmlspecialchars($kelas['mata_kuliah_kode']) ?>)</h2>
        <a href="/obe/public/kelas" class="btn" style="background: var(--bg-hover); color: var(--text-primary);">Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    
    <!-- Bagian Tambah Peserta -->
    <div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0; color: var(--text-primary); border-bottom: 1px solid var(--border); padding-bottom: 10px;">Tambah Mahasiswa</h3>
        
        <form action="/obe/public/kelas/peserta/add" method="POST" style="margin-top: 15px;">
            <input type="hidden" name="kelas_id" value="<?= $kelas['id'] ?>">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Pilih Mahasiswa</label>
                <select name="mahasiswa_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px;">
                    <option value="">-- Pilih Mahasiswa --</option>
                    <?php foreach($mahasiswa_belum as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            <?= htmlspecialchars($m['username']) ?> - <?= htmlspecialchars($m['nama_lengkap']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($mahasiswa_belum)): ?>
                    <p style="font-size: 0.85em; color: var(--danger); margin-top: 5px;">Tidak ada mahasiswa yang tersedia untuk ditambahkan.</p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;" <?= empty($mahasiswa_belum) ? 'disabled' : '' ?>>
                + Tambahkan ke Kelas
            </button>
        </form>
    </div>

    <!-- Bagian Daftar Peserta -->
    <div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0; color: var(--text-primary); border-bottom: 1px solid var(--border); padding-bottom: 10px;">Daftar Peserta (<?= count($peserta) ?>)</h3>
        
        <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                    <th style="padding: 10px; color: var(--text-secondary);">NIM / Username</th>
                    <th style="padding: 10px; color: var(--text-secondary);">Nama Lengkap</th>
                    <th style="padding: 10px; color: var(--text-secondary); text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($peserta as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px; font-family: monospace;"><?= htmlspecialchars($p['username']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                        <td style="padding: 10px; text-align: right;">
                            <a href="/obe/public/kelas/peserta/remove?kelas_id=<?= $kelas['id'] ?>&mahasiswa_id=<?= $p['id'] ?>" class="btn" style="padding: 4px 8px; font-size: 0.85em; background: var(--danger); color: white;" onclick="return confirm('Keluarkan mahasiswa ini dari kelas?');">Keluarkan</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($peserta)): ?>
                    <tr>
                        <td colspan="3" style="padding: 15px; text-align: center; color: var(--text-secondary);">Belum ada peserta di kelas ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
