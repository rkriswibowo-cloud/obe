<?php
// views/profil_lulusan/form.php
require_once __DIR__ . '/../../app/Support/Maintenance.php';
$config = $config ?? (require __DIR__ . '/../../config/app.php');
$baseUrl = Maintenance::baseUrl($config['base_url'] ?? null);

$isEdit = isset($pl);
$title = ($isEdit ? 'Edit Profil Lulusan' : 'Tambah Profil Lulusan') . ' - Sistem OBE';
ob_start();
?>

<div style="width: 100%; box-sizing: border-box;">
    <!-- Header & Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <a href="<?= $baseUrl ?>/profil-lulusan<?= !empty($selectedKurikulumId) ? '?kurikulum_id=' . (int)$selectedKurikulumId : '' ?>" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Profil Lulusan
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em;">
                <?= $isEdit ? 'Edit Profil Lulusan' : 'Tambah Profil Lulusan Baru' ?>
            </h2>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.88rem;">
                <?= $isEdit ? 'Perbarui data profil dan pemetaan Capaian Pembelajaran Lulusan (CPL) terkait.' : 'Definisikan profil peran lulusan dan hubungkan dengan Capaian Pembelajaran Lulusan (CPL) kurikulum.' ?>
            </p>
        </div>
    </div>

    <!-- Main Full-Width Form Card -->
    <div style="width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 28px 32px; box-shadow: var(--shadow-sm); box-sizing: border-box;">
        <form action="<?= $baseUrl ?>/profil-lulusan/<?= $isEdit ? 'update' : 'store' ?>" method="POST">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
            <?php endif; ?>

            <!-- Baris 1: Kurikulum & Kode Profil -->
            <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 20px; margin-bottom: 20px;">
                <!-- Kurikulum -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 7px;">
                        Kurikulum Program Studi <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="kurikulum_id" id="kurikulum_select" required <?= !$isEdit ? 'onchange="onKurikulumChange(this.value)"' : '' ?> style="width: 100%; padding: 11px 14px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--input-border, var(--border)); background: var(--input-bg); color: var(--text-main); box-sizing: border-box;">
                        <?php 
                        $groupedKurikulum = [];
                        foreach ($kurikulumList as $k) {
                            $prodiKey = trim(($k['jenjang_prodi'] ?? '') . ' ' . ($k['nama_prodi'] ?? 'Umum'));
                            if (empty($prodiKey)) $prodiKey = 'Program Studi';
                            $groupedKurikulum[$prodiKey][] = $k;
                        }
                        foreach ($groupedKurikulum as $prodiName => $kList): ?>
                            <optgroup label="Program Studi: <?= htmlspecialchars($prodiName) ?>">
                                <?php foreach ($kList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= (($isEdit ? $pl['kurikulum_id'] : $selectedKurikulumId) == $k['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kode Profil -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 7px;">
                        Kode Profil Lulusan <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="kode" value="<?= htmlspecialchars($pl['kode'] ?? '') ?>" required placeholder="Contoh: PL01" style="width: 100%; padding: 11px 14px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--input-border, var(--border)); background: var(--input-bg); color: var(--text-main); box-sizing: border-box;">
                </div>
            </div>

            <!-- Baris 2: Nama Profil -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 7px;">
                    Nama Profil Lulusan / Peran Profesi <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="nama" value="<?= htmlspecialchars($pl['nama'] ?? '') ?>" required placeholder="Contoh: System Analyst & Solution Architect" style="width: 100%; padding: 11px 14px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--input-border, var(--border)); background: var(--input-bg); color: var(--text-main); box-sizing: border-box;">
            </div>

            <!-- Baris 3: Deskripsi Peran -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 7px;">
                    Deskripsi Profil & Kemampuan Profesional Lulusan <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="deskripsi" rows="4" required placeholder="Deskripsikan peran, lingkup kerja, dan kemampuan profesional lulusan..." style="width: 100%; padding: 12px 14px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--input-border, var(--border)); background: var(--input-bg); color: var(--text-main); resize: vertical; line-height: 1.5; box-sizing: border-box;"><?= htmlspecialchars($pl['deskripsi'] ?? '') ?></textarea>
            </div>

            <!-- Baris 4: Pemetaan ke CPL (Checkboxes) -->
            <div style="margin-bottom: 28px; border-top: 1px solid var(--border-subtle); padding-top: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                            <i class="fa-solid fa-bullseye" style="color: var(--primary); margin-right: 6px;"></i> CPL Pendukung Profil Ini
                        </label>
                        <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                            Pilih Capaian Pembelajaran Lulusan (CPL) yang dibekalkan untuk membentuk profil ini:
                        </p>
                    </div>
                    <?php if (!empty($cplList)): ?>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="toggleAllCpl(true)" class="btn btn-sm btn-secondary" style="font-size: 0.76rem; padding: 5px 12px;">
                            <i class="fa-solid fa-check-double"></i> Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllCpl(false)" class="btn btn-sm btn-secondary" style="font-size: 0.76rem; padding: 5px 12px;">
                            <i class="fa-solid fa-xmark"></i> Batalkan Semua
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <?php $selectedCplIds = $pl['cpl_ids'] ?? []; ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 10px; max-height: 380px; overflow-y: auto; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--input-bg); box-sizing: border-box;">
                    <?php if (empty($cplList)): ?>
                        <div style="grid-column: 1 / -1; padding: 24px; color: var(--text-muted); font-size: 0.88rem; text-align: center;">
                            <i class="fa-solid fa-circle-exclamation" style="font-size: 1.5rem; margin-bottom: 8px; display: block; opacity: 0.6;"></i>
                            Belum ada data CPL pada kurikulum yang dipilih. Silakan tambahkan CPL pada kurikulum ini terlebih dahulu.
                        </div>
                    <?php else: ?>
                        <?php foreach ($cplList as $c): ?>
                            <?php $checked = in_array($c['id'], $selectedCplIds); ?>
                            <label class="cpl-checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border-radius: 8px; cursor: pointer; transition: all 0.15s ease; background: <?= $checked ? 'rgba(var(--primary-rgb), 0.12)' : 'var(--surface)' ?>; border: 1px solid <?= $checked ? 'rgba(var(--primary-rgb), 0.4)' : 'var(--border-subtle)' ?>;">
                                <input type="checkbox" name="cpl_ids[]" value="<?= $c['id'] ?>" <?= $checked ? 'checked' : '' ?> onchange="updateCplCardStyle(this)" style="margin-top: 3px; accent-color: var(--primary);">
                                <div style="font-size: 0.85rem; line-height: 1.45;">
                                    <strong style="color: var(--primary); font-family: monospace; font-size: 0.9rem; display: inline-block; margin-right: 6px;"><?= htmlspecialchars($c['kode']) ?></strong>
                                    <span style="color: var(--text-main);"><?= htmlspecialchars($c['rumusan']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; border-top: 1px solid var(--border-subtle); padding-top: 20px;">
                <a href="<?= $baseUrl ?>/profil-lulusan<?= !empty($selectedKurikulumId) ? '?kurikulum_id=' . (int)$selectedKurikulumId : '' ?>" class="btn btn-secondary" style="padding: 10px 20px;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Profil' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function onKurikulumChange(kurikulumId) {
    if (!kurikulumId) return;
    const baseUrl = '<?= $baseUrl ?>';
    window.location.href = baseUrl + '/profil-lulusan/create?kurikulum_id=' + encodeURIComponent(kurikulumId);
}

function updateCplCardStyle(checkbox) {
    const card = checkbox.closest('.cpl-checkbox-label');
    if (!card) return;
    if (checkbox.checked) {
        card.style.background = 'rgba(var(--primary-rgb), 0.12)';
        card.style.borderColor = 'rgba(var(--primary-rgb), 0.4)';
    } else {
        card.style.background = 'var(--surface)';
        card.style.borderColor = 'var(--border-subtle)';
    }
}

function toggleAllCpl(selectAll) {
    const checkboxes = document.querySelectorAll('input[name="cpl_ids[]"]');
    checkboxes.forEach(function(cb) {
        cb.checked = selectAll;
        updateCplCardStyle(cb);
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
