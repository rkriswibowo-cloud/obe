<?php
$isEdit = isset($cpmk);
$title = ($isEdit ? "Edit" : "Tambah") . " CPMK: " . htmlspecialchars($mk['nama']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h4><?= $isEdit ? "Edit" : "Tambah" ?> CPMK</h4>
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
        <h6 style="margin: 0; font-weight: 600;">Formulir CPMK</h6>
    </div>
    <div style="padding: 20px;">
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border); color: var(--text-muted);">
            Mata Kuliah: <strong style="color: var(--text-main);"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></strong>
        </div>
        <form method="POST" action="">
            <div style="margin-bottom: 20px;">
                <label for="kode" style="display: block; margin-bottom: 8px; font-weight: 500;">Kode CPMK <span style="color: var(--danger);">*</span></label>
                <input type="text" id="kode" name="kode" value="<?= $isEdit ? htmlspecialchars($cpmk['kode']) : '' ?>" required placeholder="Contoh: CPMK01" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Gunakan format yang konsisten, misal CPMK01, CPMK02, dsb.</div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="rumusan" style="display: block; margin-bottom: 8px; font-weight: 500;">Rumusan CPMK <span style="color: var(--danger);">*</span></label>
                <textarea id="rumusan" name="rumusan" rows="4" required placeholder="Tuliskan rumusan capaian pembelajaran secara spesifik dan dapat diukur..." style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box; font-family: inherit;"><?= $isEdit ? htmlspecialchars($cpmk['rumusan']) : '' ?></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Terkait dengan CPL (Capaian Pembelajaran Lulusan):</label>
                <div style="border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 15px; max-height: 250px; overflow-y: auto; background: var(--background);">
                    <?php if (empty($cplList)): ?>
                        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size: 2rem; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                            <p style="margin: 0;">Tidak ada CPL yang tersedia. Tambahkan CPL terlebih dahulu di menu Master CPL.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $selectedCplIds = $isEdit ? ($cpmk['cpl_ids'] ?? []) : [];
                        foreach ($cplList as $cpl): 
                        ?>
                            <div style="display: flex; align-items: flex-start; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                                <input type="checkbox" name="cpl_ids[]" value="<?= $cpl['id'] ?>" id="cpl_<?= $cpl['id'] ?>" <?= in_array($cpl['id'], $selectedCplIds) ? 'checked' : '' ?> style="margin-top: 5px; margin-right: 15px; transform: scale(1.2);">
                                <label for="cpl_<?= $cpl['id'] ?>" style="cursor: pointer; display: block;">
                                    <span style="background: var(--surface-hover); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 5px;"><?= htmlspecialchars($cpl['kode']) ?></span><br>
                                    <span style="color: var(--text-main); line-height: 1.4;"><?= htmlspecialchars($cpl['rumusan']) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Centang CPL yang relevan dan dibebankan pada CPMK ini.</div>
            </div>

            <div style="border-top: 1px solid var(--border); margin-top: 30px; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
