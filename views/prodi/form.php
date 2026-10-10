<?php
// views/prodi/form.php
$isEdit = isset($prodi);
$title = ($isEdit ? 'Edit Program Studi' : 'Tambah Program Studi') . ' - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
$action = $isEdit ? ($baseUrl . '/prodi/update') : ($baseUrl . '/prodi/store');
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="<?= $baseUrl ?>/prodi" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Program Studi
        </a>
        <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: var(--text-main);">
            <?= $isEdit ? 'Edit Program Studi' : 'Tambah Program Studi Baru' ?>
        </h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.88rem;">
            <?= $isEdit ? 'Perbarui identitas program studi, fakultas, dan pimpinan program studi.' : 'Daftarkan program studi baru untuk mengelola kurikulum OBE mandiri.' ?>
        </p>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form action="<?= $action ?>" method="POST">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $prodi['id'] ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 140px 1fr; gap: 16px; margin-bottom: 18px;">
                <!-- Kode Prodi -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Kode Prodi <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="kode" required maxlength="10" value="<?= $isEdit ? htmlspecialchars($prodi['kode']) : '' ?>" placeholder="Contoh: SI" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-family: monospace; text-transform: uppercase;">
                </div>

                <!-- Jenjang Pendidikan -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Jenjang Pendidikan <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="jenjang" required style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                        <?php 
                        $jenjangList = ['D3', 'D4', 'S1', 'S2', 'S3'];
                        $currentJenjang = $prodi['jenjang'] ?? 'S1';
                        foreach ($jenjangList as $j):
                        ?>
                            <option value="<?= $j ?>" <?= $currentJenjang === $j ? 'selected' : '' ?>>
                                <?= $j ?> (Strata <?= $j === 'S1' ? '1 - Sarjana' : ($j === 'S2' ? '2 - Magister' : ($j === 'D3' ? 'Diploma 3' : 'Terapan')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Nama Program Studi -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Nama Program Studi <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="nama" required value="<?= $isEdit ? htmlspecialchars($prodi['nama']) : '' ?>" placeholder="Contoh: Sistem Informasi" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
            </div>

            <!-- Fakultas -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Fakultas / Unit Pengelola Program Studi (UPPS) <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="fakultas" required value="<?= $isEdit ? htmlspecialchars($prodi['fakultas']) : 'Fakultas Sains dan Teknologi' ?>" placeholder="Contoh: Fakultas Sains dan Teknologi" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px; border-top: 1px solid var(--border); padding-top: 18px;">
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:var(--text-main);margin-bottom:6px">Dekan Fakultas</label>
                    <select name="dekan_user_id" id="dekanUserSelect" onchange="syncDekanPreview()" style="width:100%;padding:10px 12px;font-size:.9rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--bg-hover);color:var(--text-main)">
                        <option value="">Pilih dosen sebagai Dekan...</option>
                        <?php foreach ($dosenList as $dosen): ?>
                            <option value="<?= (int)$dosen['id'] ?>" data-nidn="<?= htmlspecialchars($dosen['nidn'] ?? '',ENT_QUOTES) ?>" data-nuptk="<?= htmlspecialchars($dosen['nuptk'] ?? '',ENT_QUOTES) ?>" <?= $isEdit && (int)($prodi['dekan_user_id']??0)===(int)$dosen['id']?'selected':'' ?>><?= htmlspecialchars($dosen['nama_lengkap']) ?><?= !empty($dosen['homebase'])?' — '.htmlspecialchars($dosen['homebase']):'' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:var(--text-main);margin-bottom:6px">NIDN/NUPTK Dekan</label>
                    <input type="text" id="dekanIdentityPreview" value="" placeholder="Terisi otomatis dari data dosen" disabled style="width:100%;padding:10px 12px;font-size:.9rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface-hover);color:var(--text-muted);font-family:monospace;box-sizing:border-box">
                </div>
                <!-- Nama Kaprodi -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Ketua Program Studi (Kaprodi)
                    </label>
                    <select name="kaprodi_user_id" id="kaprodiUserSelect" onchange="syncKaprodiPreview()" style="width:100%;padding:10px 12px;font-size:.9rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--bg-hover);color:var(--text-main)">
                        <option value="">Pilih dosen sebagai Kaprodi...</option>
                        <?php foreach ($dosenList as $dosen): ?>
                            <option value="<?= (int)$dosen['id'] ?>" data-nidn="<?= htmlspecialchars($dosen['nidn'] ?? '',ENT_QUOTES) ?>" data-nuptk="<?= htmlspecialchars($dosen['nuptk'] ?? '',ENT_QUOTES) ?>" <?= $isEdit && (int)($prodi['kaprodi_user_id']??0)===(int)$dosen['id']?'selected':'' ?>><?= htmlspecialchars($dosen['nama_lengkap']) ?><?= !empty($dosen['homebase'])?' — '.htmlspecialchars($dosen['homebase']):'' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- NIDN Kaprodi -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        NIDN/NUPTK Kaprodi
                    </label>
                    <input type="text" id="kaprodiIdentityPreview" value="" placeholder="Terisi otomatis dari data dosen" disabled style="width:100%;padding:10px 12px;font-size:.9rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface-hover);color:var(--text-muted);font-family:monospace;box-sizing:border-box">
                </div>
            </div>

            <!-- Status Prodi -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Status Program Studi <span style="color: var(--danger);">*</span>
                </label>
                <div style="display: flex; gap: 20px; align-items: center; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-hover);">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; color: var(--text-main);">
                        <input type="radio" name="status" value="ACTIVE" <?= (!$isEdit || $prodi['status'] === 'ACTIVE') ? 'checked' : '' ?>>
                        <span style="font-weight: 600; color: var(--success);"><i class="fa-solid fa-circle-check"></i> ACTIVE (Aktif Berjalan)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; color: var(--text-main);">
                        <input type="radio" name="status" value="INACTIVE" <?= ($isEdit && $prodi['status'] === 'INACTIVE') ? 'checked' : '' ?>>
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-circle-pause"></i> INACTIVE (Tidak Aktif)</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 18px;">
                <a href="<?= $baseUrl ?>/prodi" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Program Studi' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function syncDekanPreview(){
    const select=document.getElementById('dekanUserSelect');
    const option=select.options[select.selectedIndex];
    const nidn=option?.dataset.nidn||'';
    const nuptk=option?.dataset.nuptk||'';
    document.getElementById('dekanIdentityPreview').value=nuptk ? `NUPTK: ${nuptk}` : (nidn ? `NIDN: ${nidn}` : 'Identitas belum diisi pada data dosen');
}
syncDekanPreview();
function syncKaprodiPreview(){
    const select=document.getElementById('kaprodiUserSelect');
    const option=select.options[select.selectedIndex];
    const nidn=option?.dataset.nidn||'';
    const nuptk=option?.dataset.nuptk||'';
    document.getElementById('kaprodiIdentityPreview').value=nuptk ? `NUPTK: ${nuptk}` : (nidn ? `NIDN: ${nidn}` : 'Identitas belum diisi pada data dosen');
}
syncKaprodiPreview();
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
