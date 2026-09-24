<?php
$title = 'Tambah CPL - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Tambah CPL</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Tambahkan data Capaian Pembelajaran Lulusan baru.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/cpl/store" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kurikulum</label>
            <select name="kurikulum_id" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">-- Pilih Kurikulum --</option>
                <?php foreach($kurikulumList as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode CPL</label>
            <input type="text" name="kode" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: CPL-01">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Rumusan</label>
            <textarea name="rumusan" required rows="4" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Masukkan rumusan CPL..."></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kategori</label>
            <input type="text" name="kategori" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: Sikap, Pengetahuan, Keterampilan Umum...">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Target Capaian (%)</label>
            <input type="number" step="0.01" name="target_capaian" value="70.00" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
