<?php ob_start(); 
$isEdit = isset($kelas);
$action = $isEdit ? '/obe/public/kelas/update' : '/obe/public/kelas/store';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><?= $isEdit ? 'Edit Kelas' : 'Tambah Kelas Baru' ?></h2>
        <a href="/obe/public/kelas" class="btn" style="background: var(--bg-hover); color: var(--text-primary);"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border); max-width: 600px;">
    <form action="<?= $action ?>" method="POST">
        <?php if($isEdit): ?>
            <input type="hidden" name="id" value="<?= $kelas['id'] ?>">
        <?php endif; ?>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Tahun Akademik</label>
            <select name="tahun_akademik_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
                <option value="" style="background: var(--surface); color: var(--text-primary);">-- Pilih Tahun Akademik --</option>
                <?php foreach($tahun_akademik as $ta): ?>
                    <option value="<?= $ta['id'] ?>" <?= ($isEdit && $kelas['tahun_akademik_id'] == $ta['id']) ? 'selected' : '' ?> style="background: var(--surface); color: var(--text-primary);">
                        <?= htmlspecialchars($ta['nama']) ?> <?= $ta['is_active'] ? '(Aktif)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Mata Kuliah</label>
            <select name="mata_kuliah_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
                <option value="" style="background: var(--surface); color: var(--text-primary);">-- Pilih Mata Kuliah --</option>
                <?php foreach($mata_kuliah as $mk): ?>
                    <option value="<?= $mk['id'] ?>" <?= ($isEdit && $kelas['mata_kuliah_id'] == $mk['id']) ? 'selected' : '' ?> style="background: var(--surface); color: var(--text-primary);">
                        <?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Nama Kelas</label>
            <input type="text" name="nama_kelas" required placeholder="Contoh: A, B, Reguler 1" value="<?= $isEdit ? htmlspecialchars($kelas['nama_kelas']) : '' ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Dosen Pengampu (PJMK)</label>
            <select name="dosen_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box;">
                <option value="" style="background: var(--surface); color: var(--text-primary);">-- Pilih Dosen PJMK --</option>
                <?php foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($isEdit && $kelas['dosen_id'] == $d['id']) ? 'selected' : '' ?> style="background: var(--surface); color: var(--text-primary);">
                        <?= htmlspecialchars($d['nama_lengkap']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Tim Teaching (Dosen Anggota - Opsional)</label>
            <small style="display: block; margin-bottom: 8px; color: var(--text-muted);">Tahan tombol Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu dosen.</small>
            <select name="tim_teaching[]" multiple style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; box-sizing: border-box; height: 120px;">
                <?php 
                $timIds = isset($tim_teaching_ids) ? $tim_teaching_ids : [];
                foreach($dosen as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (in_array($d['id'], $timIds)) ? 'selected' : '' ?> style="background: var(--surface); color: var(--text-primary); padding: 5px;">
                        <?= htmlspecialchars($d['nama_lengkap']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
            <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Update Kelas' : 'Simpan Kelas' ?>
        </button>
    </form>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
