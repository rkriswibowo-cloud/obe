<?php
$title = 'Data CPL - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Data CPL</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Kelola Capaian Pembelajaran Lulusan (CPL) per Program Studi dan keterhubungannya dengan Profil Lulusan.
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Filter Program Studi & Kurikulum -->
        <form method="GET" action="<?= $baseUrl ?>/cpl" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <select name="prodi_id" onchange="this.form.submit()" style="padding: 8px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main);">
                <option value="">-- Semua Program Studi --</option>
                <?php foreach ($prodiList as $pr): ?>
                    <option value="<?= $pr['id'] ?>" <?= ($selectedProdiId == $pr['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pr['jenjang'] . ' ' . $pr['nama']) ?> (<?= htmlspecialchars($pr['kode']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="kurikulum_id" onchange="this.form.submit()" style="padding: 8px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main);">
                <option value="">-- Semua Kurikulum --</option>
                <?php foreach ($kurikulumList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($selectedKurikulumId == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($selectedProdiId) || !empty($selectedKurikulumId)): ?>
                <a href="<?= $baseUrl ?>/cpl" class="btn btn-sm btn-secondary" title="Reset Filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </form>

        <a href="<?= $baseUrl ?>/cpl/create<?= !empty($selectedKurikulumId) ? ('?kurikulum_id=' . $selectedKurikulumId) : '' ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah CPL
        </a>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($_SESSION['success']) ?></span>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($_SESSION['error']) ?></span>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 85px;">KODE</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 130px;">KATEGORI</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted);">RUMUSAN CAPAIAN PEMBELAJARAN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 160px;">PROFIL LULUSAN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 170px;">PRODI & KURIKULUM</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 80px; text-align: center;">TARGET</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 80px; text-align: center;">STATUS</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); text-align: right; width: 100px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cplList)): ?>
                <tr>
                    <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-bullseye" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada data CPL terdaftar untuk filter ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($cplList as $c): ?>
                <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                    <td style="padding: 14px 18px; font-weight: 700;">
                        <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-family: monospace;">
                            <?= htmlspecialchars($c['kode']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; font-size: 0.85rem;">
                        <span style="background: rgba(100, 116, 139, 0.15); color: var(--text-main); padding: 3px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">
                            <?= htmlspecialchars($c['kategori'] ?? 'Umum') ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; font-size: 0.88rem; color: var(--text-main); line-height: 1.45;">
                        <?= htmlspecialchars($c['rumusan']) ?>
                    </td>
                    <td style="padding: 14px 18px;">
                        <?php if (!empty($c['pl_kodes'])): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                <?php foreach (explode(',', $c['pl_kodes']) as $plKode): ?>
                                    <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; font-family: monospace;">
                                        <?= htmlspecialchars(trim($plKode)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px 18px;">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 2px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; width: fit-content;">
                                <i class="fa-solid fa-building-columns" style="font-size: 0.68rem; margin-right: 3px;"></i>
                                <?= htmlspecialchars(($c['prodi_jenjang'] ?? '') . ' ' . ($c['prodi_nama'] ?? '')) ?>
                            </span>
                            <span style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                <?= htmlspecialchars($c['kurikulum_nama'] ?? '') ?>
                            </span>
                        </div>
                    </td>
                    <td style="padding: 14px 18px; text-align: center; font-weight: 700; font-family: monospace; color: var(--primary);">
                        <?= floatval($c['target_capaian']) ?>%
                    </td>
                    <td style="padding: 14px 18px; text-align: center;">
                        <span style="background: <?= $c['status'] == 'ACTIVE' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= $c['status'] == 'ACTIVE' ? 'var(--success)' : 'var(--text-muted)' ?>; border: 1px solid <?= $c['status'] == 'ACTIVE' ? 'rgba(16, 185, 129, 0.3)' : 'var(--border)' ?>; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                            <?= htmlspecialchars($c['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="<?= $baseUrl ?>/cpl/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary" title="Edit CPL">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="<?= $baseUrl ?>/cpl/delete?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" title="Hapus CPL" onclick="return confirm('Yakin ingin menghapus CPL <?= htmlspecialchars($c['kode']) ?>?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
