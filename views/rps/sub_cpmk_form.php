<?php
$isEdit = isset($subCpmk);
$title = ($isEdit ? "Edit" : "Tambah") . " Sub-CPMK: " . htmlspecialchars($cpmk['kode']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h4><?= $isEdit ? "Edit" : "Tambah" ?> Sub-CPMK</h4>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke RPS
    </a>
</div>

<?php if (isset($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
        <h6 style="margin: 0; font-weight: 600;">Formulir Sub-CPMK</h6>
    </div>
    <div style="padding: 20px;">
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border); color: var(--text-muted);">
            Mata Kuliah: <strong style="color: var(--text-main);"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></strong><br>
            CPMK Induk: <strong style="color: var(--text-main);"><?= htmlspecialchars($cpmk['kode']) ?></strong>
        </div>
        <form method="POST" action="">
            <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="kode" style="display: block; margin-bottom: 8px; font-weight: 500;">Kode Sub-CPMK <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="kode" name="kode" value="<?= $isEdit ? htmlspecialchars($subCpmk['kode']) : '' ?>" required placeholder="Contoh: L-1" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 100px;">
                    <label for="urutan" style="display: block; margin-bottom: 8px; font-weight: 500;">Urutan</label>
                    <input type="number" id="urutan" name="urutan" value="<?= $isEdit ? htmlspecialchars($subCpmk['urutan']) : '1' ?>" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 120px;">
                    <label for="bobot" style="display: block; margin-bottom: 8px; font-weight: 500;">Bobot (%)</label>
                    <div style="display: flex;">
                        <input type="number" step="0.01" id="bobot" name="bobot" value="<?= $isEdit ? htmlspecialchars($subCpmk['bobot']) : '0' ?>" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm) 0 0 var(--radius-sm); box-sizing: border-box; border-right: none;">
                        <span style="background: var(--surface-hover); border: 1px solid var(--border); color: var(--text-muted); padding: 10px 15px; border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">%</span>
                    </div>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label for="level_taksonomi" style="display: block; margin-bottom: 8px; font-weight: 500;">Level Taksonomi</label>
                    <input type="text" id="level_taksonomi" name="level_taksonomi" value="<?= $isEdit ? htmlspecialchars($subCpmk['level_taksonomi']) : '' ?>" placeholder="Contoh: C4, A3, P2" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="rumusan" style="display: block; margin-bottom: 8px; font-weight: 500;">Rumusan Sub-CPMK <span style="color: var(--danger);">*</span></label>
                <textarea id="rumusan" name="rumusan" rows="3" required placeholder="Tuliskan rumusan kemampuan akhir yang diharapkan..." style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box; font-family: inherit;"><?= $isEdit ? htmlspecialchars($subCpmk['rumusan']) : '' ?></textarea>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 250px;">
                    <label for="pengalaman_belajar" style="display: block; margin-bottom: 8px; font-weight: 500;">Pengalaman Pembelajaran</label>
                    <input type="text" id="pengalaman_belajar" name="pengalaman_belajar" value="<?= $isEdit ? htmlspecialchars($subCpmk['pengalaman_belajar'] ?? '') : 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus' ?>" placeholder="Contoh: Ceramah, Presentasi, Tugas, Video, Diskusi kasus" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label for="asesmen_penilaian" style="display: block; margin-bottom: 8px; font-weight: 500;">Bentuk Asesmen / Penilaian</label>
                    <input type="text" id="asesmen_penilaian" name="asesmen_penilaian" value="<?= $isEdit ? htmlspecialchars($subCpmk['asesmen_penilaian'] ?? '') : 'Pretes dan postes' ?>" placeholder="Contoh: Pretes dan postes" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 15px; background: var(--surface);">
                <label style="display: block; margin-bottom: 15px; font-weight: 500;">Indikator Penilaian</label>
                <div id="indikator-container">
                    <?php if ($isEdit && !empty($subCpmk['indikators'])): ?>
                        <?php foreach ($subCpmk['indikators'] as $ind): ?>
                            <div style="display: flex; margin-bottom: 10px;">
                                <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
                                <input type="text" name="indikators[]" value="<?= htmlspecialchars($ind['indikator']) ?>" placeholder="Deskripsi indikator..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="display: flex; margin-bottom: 10px;">
                            <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
                            <input type="text" name="indikators[]" placeholder="Deskripsi indikator (opsional)..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
                            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-secondary" style="margin-top: 10px;" onclick="addIndikator()">
                    <i class="fa-solid fa-plus"></i> Tambah Indikator Baru
                </button>
            </div>

            <div style="border-top: 1px solid var(--border); margin-top: 30px; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function addIndikator() {
    const container = document.getElementById('indikator-container');
    const div = document.createElement('div');
    div.style.cssText = 'display: flex; margin-bottom: 10px;';
    div.innerHTML = `
        <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
        <input type="text" name="indikators[]" placeholder="Deskripsi indikator..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
    `;
    container.appendChild(div);
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
