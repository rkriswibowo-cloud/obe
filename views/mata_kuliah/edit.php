<?php
$title = 'Edit Mata Kuliah - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Edit Mata Kuliah</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Perbarui data mata kuliah.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah/update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($mk['id']) ?>">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kurikulum</label>
            <select name="kurikulum_id" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">-- Pilih Kurikulum --</option>
                <?php foreach($kurikulumList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $k['id'] == $mk['kurikulum_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode MK</label>
                <input type="text" name="kode" value="<?= htmlspecialchars($mk['kode']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
            <div style="flex: 2;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nama Mata Kuliah</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($mk['nama']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">SKS Teori</label>
                <input type="number" name="sks_teori" min="0" value="<?= htmlspecialchars($mk['sks_teori']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">SKS Praktek</label>
                <input type="number" name="sks_praktek" min="0" value="<?= htmlspecialchars($mk['sks_praktek']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Semester</label>
                <input type="number" name="semester" min="1" max="14" value="<?= htmlspecialchars($mk['semester']) ?>" required style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Sifat</label>
                <select name="sifat" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                    <option value="Wajib" <?= $mk['sifat'] == 'Wajib' ? 'selected' : '' ?>>Wajib</option>
                    <option value="Pilihan" <?= $mk['sifat'] == 'Pilihan' ? 'selected' : '' ?>>Pilihan</option>
                </select>
            </div>
            <div style="flex: 2;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kelompok Keilmuan</label>
                <input type="text" name="kelompok_keilmuan" value="<?= htmlspecialchars($mk['kelompok_keilmuan'] ?? '') ?>" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Deskripsi</label>
            <textarea name="deskripsi" rows="3" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);"><?= htmlspecialchars($mk['deskripsi'] ?? '') ?></textarea>
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="ACTIVE" <?= $mk['status'] == 'ACTIVE' ? 'selected' : '' ?>>Active</option>
                <option value="INACTIVE" <?= $mk['status'] == 'INACTIVE' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
