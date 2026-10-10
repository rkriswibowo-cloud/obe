<?php 
ob_start(); 
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Manajemen Kelas Perkuliahan</h2>
            <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
                Kelola jadwal kelas, pembagian dosen pengampu, tim teaching, dan peserta mahasiswa per kurikulum.
            </p>
        </div>
        <?php if (($_SESSION['role'] ?? '') !== 'Dosen'): ?>
            <a href="<?= $baseUrl ?>/kelas/create" class="btn" style="background: var(--primary); color: white; padding: 10px 16px; border-radius: var(--radius-sm); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Tambah Kelas
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Card -->
<div style="background: var(--surface); padding: 18px 20px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
    <form action="<?= $baseUrl ?>/kelas" method="GET" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Filter Kurikulum -->
        <div style="flex: 1; min-width: 220px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-book-bookmark" style="color: var(--primary); margin-right: 4px;"></i> Kurikulum
            </label>
            <select name="kurikulum_id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-weight: 500;">
                <option value="">-- Semua Kurikulum --</option>
                <?php if (!empty($kurikulumList)): ?>
                    <?php foreach ($kurikulumList as $kur): ?>
                        <option value="<?= $kur['id'] ?>" <?= ($selectedKurikulumId == $kur['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kur['nama'] . ' (' . $kur['kode'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Filter Tahun Akademik / Semester -->
        <div style="flex: 1; min-width: 220px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-calendar-days" style="color: var(--primary); margin-right: 4px;"></i> Tahun Akademik
            </label>
            <select name="ta_id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); font-weight: 500;">
                <option value="">-- Semua Tahun Akademik --</option>
                <?php if (!empty($tahunAkademikList)): ?>
                    <?php foreach ($tahunAkademikList as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($selectedTaId == $ta['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ta['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Search Box -->
        <div style="flex: 1.5; min-width: 240px;">
            <label style="display: block; margin-bottom: 6px; font-size: 0.82rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--primary); margin-right: 4px;"></i> Cari Kelas / MK / Dosen
            </label>
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Ketik kode, nama MK, kelas, atau dosen..." style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main);">
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="padding: 9px 16px; font-size: 0.88rem;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($selectedKurikulumId) || !empty($selectedTaId) || !empty($search)): ?>
                <a href="<?= $baseUrl ?>/kelas" class="btn btn-secondary" style="padding: 9px 14px; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;" title="Reset Filter">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card" style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow-x: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <span style="font-size: 0.88rem; color: var(--text-muted); font-weight: 600;">
            Menampilkan <strong><?= count($kelas) ?></strong> Kelas Perkuliahan
            <?php if (!empty($selectedKurikulumId)): ?>
                <?php 
                $activeKurName = '';
                foreach ($kurikulumList as $kItem) {
                    if ($kItem['id'] == $selectedKurikulumId) { $activeKurName = $kItem['nama']; break; }
                }
                ?>
                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: var(--primary); margin-left: 6px; padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-book" style="margin-right: 3px;"></i> Kurikulum: <?= htmlspecialchars($activeKurName) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($selectedTaId)): ?>
                <?php 
                $activeTaName = '';
                foreach ($tahunAkademikList as $taItem) {
                    if ($taItem['id'] == $selectedTaId) { $activeTaName = $taItem['nama']; break; }
                }
                ?>
                <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: var(--success); margin-left: 6px; padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                    <i class="fa-solid fa-calendar-check" style="margin-right: 3px;"></i> <?= htmlspecialchars($activeTaName) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($search)): ?>
                <span class="badge" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; margin-left: 6px; padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                    Pencarian: "<?= htmlspecialchars($search) ?>"
                </span>
            <?php endif; ?>
        </span>
    </div>

    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                <th style="padding: 12px; color: var(--text-secondary);">Tahun Akademik</th>
                <th style="padding: 12px; color: var(--text-secondary);">Mata Kuliah</th>
                <th style="padding: 12px; color: var(--text-secondary);">Nama Kelas</th>
                <th style="padding: 12px; color: var(--text-secondary);">Dosen Pengampu</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: center;">Mhs</th>
                <th style="padding: 12px; color: var(--text-secondary); text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelas as $k): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; font-size: 0.9rem; color: var(--text-muted); white-space: nowrap;">
                        <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
                        <?= htmlspecialchars($k['tahun_akademik_nama']) ?>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 2px;">
                            <strong style="color: var(--text-main); font-size: 0.95rem;"><?= htmlspecialchars($k['mata_kuliah_kode']) ?></strong>
                            <?php if (!empty($k['kurikulum_nama'])): ?>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: var(--primary); font-size: 0.72rem; padding: 1.5px 6px; border-radius: 4px; font-weight: 600;">
                                    <?= htmlspecialchars($k['kurikulum_nama']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.88rem; color: var(--text-muted);"><?= htmlspecialchars($k['mata_kuliah_nama']) ?></div>
                    </td>
                    <td style="padding: 12px; font-weight: 600; color: var(--text-main); white-space: nowrap;">
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.9rem;">
                        <i class="fa-solid fa-user-tie" style="color: var(--text-muted); margin-right: 5px; font-size: 0.85rem;"></i>
                        <?= htmlspecialchars($k['dosen_nama']) ?>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="background: var(--bg-hover); padding: 4px 10px; border-radius: 12px; font-size: 0.85em; font-weight: 600; color: var(--text-main); border: 1px solid var(--border);">
                            <?= htmlspecialchars($k['jumlah_mahasiswa']) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: right; white-space: nowrap;">
                        <a href="<?= $baseUrl ?>/kelas/peserta?id=<?= $k['id'] ?>" class="btn" style="padding: 5px 9px; font-size: 0.85em; background: var(--success); color: white; margin-right: 4px; border-radius: var(--radius-sm);" title="Kelola Peserta Kelas">
                            <i class="fa-solid fa-users"></i> Peserta
                        </a>
                        <a href="<?= $baseUrl ?>/asesmen?kelas_id=<?= $k['id'] ?>&kurikulum_id=<?= $k['kurikulum_id'] ?>" class="btn" style="padding: 5px 9px; font-size: 0.85em; background: var(--primary); color: white; margin-right: 4px; border-radius: var(--radius-sm);" title="Input Nilai Asesmen">
                            <i class="fa-solid fa-check-to-slot"></i> Input Nilai
                        </a>
                        <?php if (($_SESSION['role'] ?? '') !== 'Dosen'): ?>
                            <a href="<?= $baseUrl ?>/kelas/edit?id=<?= $k['id'] ?>" class="btn" style="padding: 5px 9px; font-size: 0.85em; background: var(--bg-hover); color: var(--text-main); border: 1px solid var(--border); margin-right: 4px; border-radius: var(--radius-sm);" title="Edit Kelas">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="<?= $baseUrl ?>/kelas/delete?id=<?= $k['id'] ?>" class="btn" style="padding: 5px 9px; font-size: 0.85em; background: var(--danger); color: white; border-radius: var(--radius-sm);" onclick="return confirm('Hapus kelas ini?');" title="Hapus Kelas">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($kelas)): ?>
                <tr>
                    <td colspan="6" style="padding: 36px 20px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-chalkboard-user" style="font-size: 2rem; opacity: 0.4; margin-bottom: 10px; display: block;"></i>
                        Tidak ada data kelas yang sesuai dengan filter.
                        <?php if (!empty($selectedKurikulumId) || !empty($selectedTaId) || !empty($search)): ?>
                            <div style="margin-top: 10px;">
                                <a href="<?= $baseUrl ?>/kelas" class="btn btn-sm btn-secondary">
                                    <i class="fa-solid fa-arrows-rotate"></i> Reset Filter
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
