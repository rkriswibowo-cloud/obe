<?php
// views/mata_kuliah/index.php
$title = 'Data Mata Kuliah - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Mata Kuliah</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Daftar mata kuliah kurikulum OBE terstruktur dan tersinkronisasi per Program Studi.
        </p>
    </div>
    <a href="<?= $baseUrl ?>/mata-kuliah/create<?= !empty($selectedKurikulumId) ? ('?kurikulum_id=' . $selectedKurikulumId) : '' ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Mata Kuliah
    </a>
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

<!-- Filter Multi-Kriteria (Prodi, Kurikulum, Semester, Pencarian) -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form method="GET" action="<?= $baseUrl ?>/mata-kuliah" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Filter Prodi -->
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-building-columns" style="color: var(--primary); margin-right: 4px;"></i> Program Studi
            </label>
            <select name="prodi_id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                <option value="">-- Semua Prodi --</option>
                <?php foreach ($prodiList as $pr): ?>
                    <option value="<?= $pr['id'] ?>" <?= ($selectedProdiId == $pr['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pr['jenjang'] . ' ' . $pr['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Kurikulum -->
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-book" style="color: var(--primary); margin-right: 4px;"></i> Kurikulum
            </label>
            <select name="kurikulum_id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                <option value="">-- Semua Kurikulum --</option>
                <?php foreach ($kurikulumList as $kr): ?>
                    <option value="<?= $kr['id'] ?>" <?= ($selectedKurikulumId == $kr['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kr['nama']) ?> (<?= htmlspecialchars($kr['nama_prodi'] ?? '') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Semester -->
        <div style="width: 130px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                Semester
            </label>
            <select name="semester" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
                <option value="">Semua</option>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?= $i ?>" <?= (isset($_GET['semester']) && $_GET['semester'] == $i) ? 'selected' : '' ?>>Semester <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Search -->
        <div style="flex: 1.5; min-width: 200px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                Cari Nama / Kode MK
            </label>
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Ketik kode atau nama MK..." style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="padding: 9px 16px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($selectedProdiId) || !empty($selectedKurikulumId) || !empty($_GET['semester']) || !empty($_GET['search'])): ?>
                <a href="<?= $baseUrl ?>/mata-kuliah" class="btn btn-secondary" style="padding: 9px 14px;" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 60px; text-align: center;">SMT</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 95px;">KODE</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">MATA KULIAH</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 100px; text-align: center;">SKS (T/P)</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 85px; text-align: center;">SIFAT</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 230px;">PRODI & KURIKULUM</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); text-align: right; width: 110px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mkList)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-book-open" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada data mata kuliah yang sesuai filter.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($mkList as $m): ?>
                <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                    <td style="padding: 14px 18px; text-align: center;">
                        <span style="background: rgba(100, 116, 139, 0.15); color: var(--text-main); font-weight: 700; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem;">
                            S<?= htmlspecialchars($m['semester']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px; font-weight: 700;">
                        <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 0.82rem; font-family: monospace;">
                            <?= htmlspecialchars($m['kode']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem;">
                            <?= htmlspecialchars($m['nama']) ?>
                        </div>
                        <?php if (!empty($m['kelompok_keilmuan'])): ?>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                <i class="fa-solid fa-layer-group" style="font-size: 0.7rem; margin-right: 3px; opacity: 0.7;"></i>
                                <?= htmlspecialchars($m['kelompok_keilmuan']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px 18px; text-align: center; font-size: 0.88rem; font-family: monospace;">
                        <strong><?= $m['sks_teori'] + $m['sks_praktek'] ?></strong>
                        <span style="color: var(--text-muted); font-size: 0.78rem;">(<?= $m['sks_teori'] ?>/<?= $m['sks_praktek'] ?>)</span>
                    </td>
                    <td style="padding: 14px 18px; text-align: center;">
                        <span style="background: <?= $m['sifat'] == 'Wajib' ? 'rgba(59, 130, 246, 0.12)' : 'rgba(245, 158, 11, 0.12)' ?>; color: <?= $m['sifat'] == 'Wajib' ? 'var(--primary)' : '#f59e0b' ?>; border: 1px solid <?= $m['sifat'] == 'Wajib' ? 'rgba(59, 130, 246, 0.3)' : 'rgba(245, 158, 11, 0.3)' ?>; padding: 2px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                            <?= htmlspecialchars($m['sifat']) ?>
                        </span>
                    </td>
                    <td style="padding: 14px 18px;">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 2px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; width: fit-content;">
                                <i class="fa-solid fa-building-columns" style="font-size: 0.68rem; margin-right: 3px;"></i>
                                <?= htmlspecialchars(($m['prodi_jenjang'] ?? '') . ' ' . ($m['prodi_nama'] ?? '')) ?>
                            </span>
                            <span style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                <?= htmlspecialchars($m['kurikulum_nama'] ?? '') ?>
                            </span>
                        </div>
                    </td>
                    <td style="padding: 14px 18px; text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                            <a href="<?= $baseUrl ?>/rps?mk_id=<?= $m['id'] ?>" class="btn btn-sm btn-primary" title="Kelola RPS" style="padding: 4px 9px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-book"></i> RPS
                            </a>
                            <a href="<?= $baseUrl ?>/mata-kuliah/edit?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Mata Kuliah">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="<?= $baseUrl ?>/mata-kuliah/delete?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Mata Kuliah" onclick="return confirm('Apakah Anda yakin ingin menghapus mata kuliah <?= htmlspecialchars($m['nama'], ENT_QUOTES) ?>?')">
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 6px; margin-top: 24px; align-items: center;">
        <?php if ($page > 1): ?>
            <a href="<?= $baseUrl ?>/mata-kuliah?page=<?= $page - 1 ?>&prodi_id=<?= urlencode($selectedProdiId) ?>&kurikulum_id=<?= urlencode($selectedKurikulumId) ?>&semester=<?= urlencode($_GET['semester'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        <?php endif; ?>

        <?php for($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= $baseUrl ?>/mata-kuliah?page=<?= $p ?>&prodi_id=<?= urlencode($selectedProdiId) ?>&kurikulum_id=<?= urlencode($selectedKurikulumId) ?>&semester=<?= urlencode($_GET['semester'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>" class="btn btn-sm <?= $p == $page ? 'btn-primary' : 'btn-secondary' ?>" style="min-width: 32px; text-align: center;">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl ?>/mata-kuliah?page=<?= $page + 1 ?>&prodi_id=<?= urlencode($selectedProdiId) ?>&kurikulum_id=<?= urlencode($selectedKurikulumId) ?>&semester=<?= urlencode($_GET['semester'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>" class="btn btn-sm btn-secondary">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
