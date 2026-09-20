<?php
$title = 'Edit CPL - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Edit CPL</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Perbarui data Capaian Pembelajaran Lulusan.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/cpl/update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($cpl['id']) ?>">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kurikulum</label>
            <select name="kurikulum_id" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">-- Pilih Kurikulum --</option>
                <?php foreach($kurikulumList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $k['id'] == $cpl['kurikulum_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode CPL</label>
            <input type="text" name="kode" value="<?= htmlspecialchars($cpl['kode']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Rumusan</label>
            <textarea name="rumusan" required rows="4" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);"><?= htmlspecialchars($cpl['rumusan']) ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kategori</label>
            <input type="text" name="kategori" value="<?= htmlspecialchars($cpl['kategori'] ?? '') ?>" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Target Capaian (%)</label>
            <input type="number" step="0.01" name="target_capaian" value="<?= htmlspecialchars($cpl['target_capaian']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="ACTIVE" <?= $cpl['status'] == 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                <option value="INACTIVE" <?= $cpl['status'] == 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
