<?php
// views/profil_lulusan/form.php
$isEdit = isset($pl);
$title = ($isEdit ? 'Edit Profil Lulusan' : 'Tambah Profil Lulusan') . ' - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="<?= $baseUrl ?>/profil-lulusan" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Profil Lulusan
        </a>
        <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: var(--text-main);">
            <?= $isEdit ? 'Edit Profil Lulusan' : 'Tambah Profil Lulusan Baru' ?>
        </h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.88rem;">
            <?= $isEdit ? 'Perbarui data profil dan pemetaan CPL terkait.' : 'Definisikan profil peran lulusan dan hubungkan dengan CPL kurikulum.' ?>
        </p>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form action="<?= $baseUrl ?>/profil-lulusan/<?= $isEdit ? 'update' : 'store' ?>" method="POST">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $pl['id'] ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <!-- Kurikulum -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Kurikulum <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="kurikulum_id" required style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                        <?php foreach ($kurikulumList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= (($isEdit ? $pl['kurikulum_id'] : $selectedKurikulumId) == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?> (<?= htmlspecialchars($k['status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kode Profil -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Kode Profil <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="kode" value="<?= htmlspecialchars($pl['kode'] ?? '') ?>" required placeholder="Contoh: PL01" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                </div>
            </div>

            <!-- Nama Profil -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Nama Profil Lulusan / Peran Profesi <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="nama" value="<?= htmlspecialchars($pl['nama'] ?? '') ?>" required placeholder="Contoh: System Analyst & Solution Architect" style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
            </div>

            <!-- Deskripsi Peran -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Deskripsi Profil & Kemampuan Lulusan <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="deskripsi" rows="4" required placeholder="Deskripsikan peran, lingkup kerja, dan kemampuan profesional lulusan..." style="width: 100%; padding: 10px 12px; font-size: 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); resize: vertical; line-height: 1.45;"><?= htmlspecialchars($pl['deskripsi'] ?? '') ?></textarea>
            </div>

            <!-- Pemetaan ke CPL (Checkboxes) -->
            <div style="margin-bottom: 24px; border-top: 1px solid var(--border); padding-top: 18px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                    <i class="fa-solid fa-bullseye" style="color: var(--primary); margin-right: 6px;"></i> CPL Pendukung Profil Ini
                </label>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                    Pilih Capaian Pembelajaran Lulusan (CPL) yang dibekalkan untuk membentuk profil ini:
                </p>

                <?php 
                $selectedCplIds = $pl['cpl_ids'] ?? [];
                ?>

                <div style="display: grid; grid-template-columns: 1fr; gap: 8px; max-height: 280px; overflow-y: auto; padding: 6px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-hover);">
                    <?php if (empty($cplList)): ?>
                        <div style="padding: 12px; color: var(--text-muted); font-size: 0.85rem; text-align: center;">
                            Belum ada data CPL pada kurikulum ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($cplList as $c): ?>
                            <?php $checked = in_array($c['id'], $selectedCplIds); ?>
                            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: background 0.15s; background: <?= $checked ? 'rgba(59, 130, 246, 0.08)' : 'transparent' ?>; border: 1px solid <?= $checked ? 'rgba(59, 130, 246, 0.3)' : 'transparent' ?>;">
                                <input type="checkbox" name="cpl_ids[]" value="<?= $c['id'] ?>" <?= $checked ? 'checked' : '' ?> style="margin-top: 3px;">
                                <div style="font-size: 0.85rem;">
                                    <strong style="color: var(--primary); font-family: monospace; font-size: 0.88rem;"><?= htmlspecialchars($c['kode']) ?></strong>
                                    <span style="color: var(--text-main); margin-left: 6px;"><?= htmlspecialchars($c['rumusan']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 18px;">
                <a href="<?= $baseUrl ?>/profil-lulusan" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Profil' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
