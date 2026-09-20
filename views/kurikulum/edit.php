<?php
$title = 'Edit Kurikulum - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Edit Kurikulum</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Perbarui data kurikulum.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum/update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($kurikulum['id']) ?>">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Program Studi</label>
            <select name="prodi_id" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">-- Pilih Program Studi --</option>
                <?php foreach($prodiList as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $kurikulum['prodi_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['jenjang']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode Kurikulum</label>
            <input type="text" name="kode" value="<?= htmlspecialchars($kurikulum['kode']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nama Kurikulum</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($kurikulum['nama']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Tahun Mulai</label>
            <input type="number" name="tahun_mulai" value="<?= htmlspecialchars($kurikulum['tahun_mulai']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Versi</label>
            <input type="text" name="versi" value="<?= htmlspecialchars($kurikulum['versi']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="DRAFT" <?= $kurikulum['status'] == 'DRAFT' ? 'selected' : '' ?>>Draft</option>
                <option value="ACTIVE" <?= $kurikulum['status'] == 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                <option value="INACTIVE" <?= $kurikulum['status'] == 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
                <option value="ARCHIVED" <?= $kurikulum['status'] == 'ARCHIVED' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
