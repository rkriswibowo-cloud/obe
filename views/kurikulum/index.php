<?php
// views/kurikulum/index.php
$title = 'Data Kurikulum - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Kurikulum OBE</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Kelola dokumen kurikulum OBE terstruktur berdasarkan masing-masing Program Studi.
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Filter Program Studi -->
        <form method="GET" action="<?= $baseUrl ?>/kurikulum" style="display: flex; gap: 8px; align-items: center;">
            <select name="prodi_id" onchange="this.form.submit()" style="padding: 8px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main);">
                <option value="">-- Semua Program Studi --</option>
                <?php foreach ($prodiList as $pr): ?>
                    <option value="<?= $pr['id'] ?>" <?= ($selectedProdiId == $pr['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pr['jenjang'] . ' ' . $pr['nama']) ?> (<?= htmlspecialchars($pr['kode']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($selectedProdiId)): ?>
                <a href="<?= $baseUrl ?>/kurikulum" class="btn btn-sm btn-secondary" title="Reset Filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </form>

        <a href="<?= $baseUrl ?>/kurikulum/create<?= !empty($selectedProdiId) ? ('?prodi_id=' . $selectedProdiId) : '' ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Kurikulum
        </a>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= $_SESSION['success'] ?></span>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= $_SESSION['error'] ?></span>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 220px;">PROGRAM STUDI</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 140px;">KODE KURIKULUM</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">NAMA DOKUMEN KURIKULUM</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 90px; text-align: center;">TAHUN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 90px; text-align: center;">VERSI</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 110px; text-align: center;">STATUS</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); text-align: right; width: 110px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kurikulumList)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-book-open" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada data kurikulum terdaftar<?= !empty($selectedProdiId) ? ' pada program studi ini' : '' ?>.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($kurikulumList as $k): ?>
                <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                    <td style="padding: 14px 18px;">
                        <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fa-solid fa-building-columns" style="font-size: 0.72rem; margin-right: 4px;"></i>
                            <?= htmlspecialchars(($k['jenjang_prodi'] ?? '') . ' ' . ($k['nama_prodi'] ?? '')) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; font-weight: 700;">
                        <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 0.82rem; font-family: monospace;">
                            <?= htmlspecialchars($k['kode']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; font-weight: 600; color: var(--text-main); font-size: 0.92rem;">
                        <?= htmlspecialchars($k['nama']) ?>
                    </td>
                    <td style="padding: 14px 18px; text-align: center; font-family: monospace; font-size: 0.88rem; color: var(--text-main);">
                        <?= htmlspecialchars($k['tahun_mulai']) ?>
                    </td>
                    <td style="padding: 14px 18px; text-align: center; font-family: monospace; font-size: 0.85rem; color: var(--text-muted);">
                        v<?= htmlspecialchars($k['versi']) ?>
                    </td>
                    <td style="padding: 14px 18px; text-align: center;">
                        <?php
                            $statusBg = 'rgba(100, 116, 139, 0.15)';
                            $statusColor = 'var(--text-muted)';
                            if ($k['status'] === 'ACTIVE') {
                                $statusBg = 'rgba(16, 185, 129, 0.15)';
                                $statusColor = 'var(--success)';
                            } elseif ($k['status'] === 'DRAFT') {
                                $statusBg = 'rgba(245, 158, 11, 0.15)';
                                $statusColor = '#f59e0b';
                            }
                        ?>
                        <span style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                            <?= htmlspecialchars($k['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="<?= $baseUrl ?>/kurikulum/edit?id=<?= $k['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Kurikulum">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="<?= $baseUrl ?>/kurikulum/delete?id=<?= $k['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Kurikulum" onclick="return confirm('Apakah Anda yakin ingin menghapus kurikulum <?= htmlspecialchars($k['nama'], ENT_QUOTES) ?>?')">
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
