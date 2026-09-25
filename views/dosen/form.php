<?php
// views/dosen/form.php
$isEdit = isset($dosen);
$title = ($isEdit ? 'Edit Data Dosen' : 'Tambah Data Dosen') . ' - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
$action = $isEdit ? ($baseUrl . '/dosen/update') : ($baseUrl . '/dosen/store');
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="<?= $baseUrl ?>/dosen" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Dosen
        </a>
        <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: var(--text-main);">
            <?= $isEdit ? 'Edit Identitas Dosen' : 'Tambah Dosen Pengampu Baru' ?>
        </h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.88rem;">
            <?= $isEdit ? 'Perbarui data identitas akademik dosen (NIDN, NUPTK, Homebase).' : 'Masukkan identitas dosen untuk penugasan RPS, pengampu kelas, dan akun asesmen.' ?>
        </p>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form action="<?= $action ?>" method="POST">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $dosen['id'] ?>">
            <?php endif; ?>

            <!-- Nama Lengkap beserta Gelar -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Nama Lengkap & Gelar Akademik <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="nama_lengkap" required value="<?= $isEdit ? htmlspecialchars($dosen['nama_lengkap']) : '' ?>" placeholder="Contoh: Dr. Budi Santoso, S.Kom., M.Kom." style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <!-- NIDN -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        NIDN (Nomor Induk Dosen Nasional)
                    </label>
                    <input type="text" name="nidn" value="<?= $isEdit ? htmlspecialchars($dosen['nidn'] ?? '') : '' ?>" placeholder="Contoh: 0707069402" maxlength="30" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-family: monospace;">
                </div>

                <!-- NUPTK -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        NUPTK (Nomor Unik Pendidik)
                    </label>
                    <input type="text" name="nuptk" value="<?= $isEdit ? htmlspecialchars($dosen['nuptk'] ?? '') : '' ?>" placeholder="Contoh: 9438752654200032" maxlength="30" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-family: monospace;">
                </div>
            </div>

            <!-- Homebase Program Studi -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Homebase Program Studi <span style="color: var(--danger);">*</span>
                </label>
                <div style="display: flex; gap: 10px;">
                    <select id="homebaseSelect" onchange="window.handleHomebaseSelect(this.value)" style="flex: 1; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                        <option value="">-- Pilih Homebase Program Studi --</option>
                        <?php 
                        $currentHomebase = $dosen['homebase'] ?? '';
                        $isCustom = true;
                        $standardHomebases = ['S1 Sistem Informasi', 'S1 Informatika', 'D3 Sistem Informasi'];
                        
                        if (!empty($prodiList)) {
                            foreach ($prodiList as $pr) {
                                $prodiName = ($pr['jenjang'] ? ($pr['jenjang'] . ' ') : '') . $pr['nama'];
                                if (!in_array($prodiName, $standardHomebases)) {
                                    $standardHomebases[] = $prodiName;
                                }
                            }
                        }

                        foreach ($standardHomebases as $hb):
                            $selected = ($currentHomebase === $hb);
                            if ($selected) $isCustom = false;
                        ?>
                            <option value="<?= htmlspecialchars($hb) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($hb) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="__custom__" <?= ($isCustom && !empty($currentHomebase)) ? 'selected' : '' ?>>
                            Lainnya (Ketik Manual)...
                        </option>
                    </select>
                </div>
                <input type="text" id="homebaseInput" name="homebase" value="<?= htmlspecialchars($currentHomebase) ?>" required placeholder="Ketik nama prodi / instansi homebase..." style="width: 100%; margin-top: 8px; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); <?= ($isCustom && !empty($currentHomebase)) ? 'display: block;' : 'display: none;' ?>">
            </div>

            <!-- Username / Akun Login -->
            <div style="margin-bottom: 24px; border-top: 1px solid var(--border); padding-top: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Username / NIP (Akun Login) <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="username" required value="<?= $isEdit ? htmlspecialchars($dosen['username']) : '' ?>" placeholder="Contoh: 0707069402" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-family: monospace;">
                <?php if(!$isEdit): ?>
                    <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Password default akun dosen baru akan disamakan dengan username.
                    </small>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 18px;">
                <a href="<?= $baseUrl ?>/dosen" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Dosen' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.handleHomebaseSelect = function(val) {
    const input = document.getElementById('homebaseInput');
    if (val === '__custom__') {
        input.style.display = 'block';
        input.value = '';
        input.focus();
    } else if (val) {
        input.style.display = 'none';
        input.value = val;
    } else {
        input.style.display = 'none';
        input.value = '';
    }
};
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
