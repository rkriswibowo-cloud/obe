<?php ob_start(); 
$isEdit = isset($ta);
$action = $isEdit ? '/obe/public/tahun-akademik/update' : '/obe/public/tahun-akademik/store';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?= $isEdit ? 'Edit Tahun Akademik' : 'Tambah Tahun Akademik' ?></h2>
        <a href="/obe/public/tahun-akademik" class="btn" style="background: var(--bg-hover); color: var(--text-primary);">Kembali</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border); max-width: 500px;">
    <form action="<?= $action ?>" method="POST">
        <?php if($isEdit): ?>
            <input type="hidden" name="id" value="<?= $ta['id'] ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Nama Tahun Akademik (Misal: 2024/2025 Ganjil)</label>
            <input type="text" name="nama" required value="<?= $isEdit ? htmlspecialchars($ta['nama']) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; color: var(--text-primary); cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" <?= ($isEdit && $ta['is_active']) ? 'checked' : '' ?> style="margin-right: 10px; width: 18px; height: 18px;">
                Tandai sebagai Tahun Akademik Aktif saat ini
            </label>
            <small style="color: var(--text-muted); display: block; margin-top: 5px;">Jika dicentang, tahun akademik lain otomatis akan dinonaktifkan.</small>
        </div>

        <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
            <?= $isEdit ? 'Update' : 'Simpan' ?>
        </button>
    </form>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
