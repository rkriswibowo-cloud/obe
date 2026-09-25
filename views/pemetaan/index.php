<?php
$title = 'Pemetaan MK ke CPL - Sistem OBE';
ob_start();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2>Pemetaan Mata Kuliah ke CPL</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Petakan mata kuliah dengan Capaian Pembelajaran Lulusan (CPL) pada kurikulum tertentu.</p>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="background: var(--surface); padding: 20px; border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 20px;">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/pemetaan-mk-cpl" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Pilih Kurikulum Program Studi</label>
            <select name="kurikulum_id" onchange="this.form.submit()" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
                <?php if (empty($kurikulumList)): ?>
                    <option value="">-- Belum ada Kurikulum --</option>
                <?php else: ?>
                    <?php 
                    $groupedKurikulum = [];
                    foreach ($kurikulumList as $k) {
                        $prodiKey = trim(($k['jenjang_prodi'] ?? '') . ' ' . ($k['nama_prodi'] ?? 'Umum'));
                        if (empty($prodiKey)) $prodiKey = 'Program Studi';
                        $groupedKurikulum[$prodiKey][] = $k;
                    }
                    foreach ($groupedKurikulum as $prodiName => $kList): ?>
                        <optgroup label="Program Studi: <?= htmlspecialchars($prodiName) ?>">
                            <?php foreach($kList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $k['id'] == $kurikulum_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </form>
</div>

<?php if($kurikulum_id && !empty($mkList) && !empty($cplList)): ?>
<form action="<?= $config['base_url'] ?? '/obe/public' ?>/pemetaan-mk-cpl/save" method="POST">
    <input type="hidden" name="kurikulum_id" value="<?= htmlspecialchars($kurikulum_id) ?>">
    
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow-x: auto; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                    <th style="padding: 15px 20px; text-align: left; position: sticky; left: 0; background: var(--surface); z-index: 10; border-right: 1px solid var(--border); min-width: 250px;">
                        Mata Kuliah \ CPL
                    </th>
                    <?php foreach($cplList as $cpl): ?>
                    <th style="padding: 10px; font-weight: 600; min-width: 60px;" title="<?= htmlspecialchars($cpl['rumusan']) ?>">
                        <?= htmlspecialchars($cpl['kode']) ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($mkList as $mk): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px 16px; text-align: left; position: sticky; left: 0; background: var(--surface); border-right: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                            <div>
                                <div style="font-weight: 600;"><?= htmlspecialchars($mk['kode']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($mk['nama']) ?></div>
                            </div>
                            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/rps?mk_id=<?= $mk['id'] ?>&tab=matriks" target="_blank" class="btn btn-sm btn-secondary" style="padding: 3px 8px; font-size: 0.75rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" title="Lihat Matriks RPS untuk <?= htmlspecialchars($mk['kode']) ?>">
                                <i class="fa-solid fa-table-cells"></i> RPS
                            </a>
                        </div>
                    </td>
                    
                    <?php foreach($cplList as $cpl): ?>
                        <?php 
                            $isChecked = isset($mapping[$mk['id']][$cpl['id']]); 
                        ?>
                    <td style="padding: 10px;">
                        <input type="checkbox" name="mapping[<?= $mk['id'] ?>][]" value="<?= $cpl['id'] ?>" <?= $isChecked ? 'checked' : '' ?> style="transform: scale(1.3); cursor: pointer;">
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Pemetaan</button>
    </div>
</form>
<?php elseif($kurikulum_id): ?>
    <div style="background: var(--surface); padding: 30px; text-align: center; border: 1px solid var(--border); border-radius: var(--radius-md); color: var(--text-muted);">
        <i class="fa-solid fa-circle-exclamation fa-3x" style="margin-bottom: 15px; opacity: 0.5;"></i>
        <p>Data Mata Kuliah atau CPL pada Kurikulum ini belum lengkap.</p>
        <p style="font-size: 0.9rem; margin-top: 10px;">Pastikan Anda sudah menginput data <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah" style="color: var(--primary);">Mata Kuliah</a> dan <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl" style="color: var(--primary);">CPL</a> untuk kurikulum ini.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
