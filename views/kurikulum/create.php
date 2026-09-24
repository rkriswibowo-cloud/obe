<?php
$title = 'Tambah Kurikulum - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Tambah Kurikulum</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Tambahkan data kurikulum baru untuk program studi.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum/store" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Program Studi</label>
            <select name="prodi_id" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">-- Pilih Program Studi --</option>
                <?php foreach($prodiList as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['jenjang']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode Kurikulum</label>
            <input type="text" name="kode" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: KUR-SI-2026">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nama Kurikulum</label>
            <input type="text" name="nama" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: Kurikulum OBE Sistem Informasi 2026">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Tahun Mulai</label>
            <input type="number" name="tahun_mulai" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: 2026">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Versi</label>
            <input type="text" name="versi" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" placeholder="Contoh: 1.0">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="DRAFT">Draft</option>
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
