<?php ob_start(); 
$isEdit = isset($dosen);
$action = $isEdit ? '/obe/public/dosen/update' : '/obe/public/dosen/store';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?= $isEdit ? 'Edit Dosen' : 'Tambah Dosen' ?></h2>
        <a href="/obe/public/dosen" class="btn" style="background: var(--bg-hover); color: var(--text-primary);">Kembali</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border); max-width: 500px;">
    <form action="<?= $action ?>" method="POST">
        <?php if($isEdit): ?>
            <input type="hidden" name="id" value="<?= $dosen['id'] ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Nama Lengkap (beserta gelar)</label>
            <input type="text" name="nama_lengkap" required value="<?= $isEdit ? htmlspecialchars($dosen['nama_lengkap']) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Username (NIP / NIDN)</label>
            <input type="text" name="username" required value="<?= $isEdit ? htmlspecialchars($dosen['username']) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
            <?php if(!$isEdit): ?>
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">*Password default akan disamakan dengan username.</small>
            <?php endif; ?>
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
