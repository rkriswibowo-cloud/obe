<?php ob_start(); ?>

<div class="content-header">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Input Nilai Asesmen</h2>
    </div>
</div>

<!-- Filter Pilih Kelas -->
<div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 20px;">
    <form action="/obe/public/asesmen" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
        <div style="flex: 1;">
            <label style="display: block; margin-bottom: 8px; color: var(--text-secondary);">Pilih Kelas yang Anda Ampu</label>
            <select name="kelas_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px;">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach($kelasList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= (isset($kelas) && $kelas['id'] == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['tahun_akademik_nama'] . ' - ' . $k['mata_kuliah_kode'] . ' - ' . $k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
            Tampilkan Form Nilai
        </button>
    </form>
</div>

<?php if (isset($_GET['success'])): ?>
<div style="background: var(--success); color: white; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
    Nilai asesmen berhasil disimpan!
</div>
<?php endif; ?>

<?php if (isset($kelas)): ?>
    <div class="card" style="background: var(--surface); padding: 20px; border-radius: 8px; border: 1px solid var(--border); overflow-x: auto;">
        
        <?php if (empty($sub_cpmk_list)): ?>
            <div style="text-align: center; color: var(--danger); padding: 20px;">
                <strong>Perhatian:</strong> Mata kuliah ini belum memiliki Sub-CPMK di RPS. Silakan lengkapi RPS terlebih dahulu sebelum melakukan asesmen.
            </div>
        <?php elseif (empty($peserta)): ?>
            <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                Belum ada mahasiswa yang terdaftar di kelas ini.
            </div>
        <?php else: ?>
            
            <form action="/obe/public/asesmen/save" method="POST">
                <input type="hidden" name="kelas_id" value="<?= $kelas['id'] ?>">
                
                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: var(--text-primary);">Matriks Penilaian Sub-CPMK</h3>
                    <button type="submit" class="btn" style="background: var(--success); color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
                        💾 Simpan Semua Nilai
                    </button>
                </div>

                <table class="table" style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="padding: 10px; border: 1px solid var(--border); color: var(--text-secondary); background: var(--bg-hover);">No</th>
                            <th rowspan="2" style="padding: 10px; border: 1px solid var(--border); color: var(--text-secondary); background: var(--bg-hover); text-align: left;">NIM / Nama Mahasiswa</th>
                            <th colspan="<?= count($sub_cpmk_list) ?>" style="padding: 10px; border: 1px solid var(--border); color: var(--text-secondary); background: var(--bg-hover); text-align: center;">Skor Sub-CPMK (0 - 100)</th>
                        </tr>
                        <tr>
                            <?php foreach($sub_cpmk_list as $sc): ?>
                                <th style="padding: 10px; border: 1px solid var(--border); color: var(--text-secondary); background: var(--bg-color); text-align: center; font-size: 0.85em; min-width: 80px;" title="<?= htmlspecialchars($sc['rumusan']) ?>">
                                    <?= htmlspecialchars($sc['kode']) ?>
                                    <div style="font-weight: normal; font-size: 0.9em; color: var(--text-muted);">(<?= floatval($sc['bobot']) ?>%)</div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($peserta as $p): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid var(--border); text-align: center;"><?= $no++ ?></td>
                                <td style="padding: 10px; border: 1px solid var(--border);">
                                    <strong style="font-family: monospace;"><?= htmlspecialchars($p['username']) ?></strong><br>
                                    <?= htmlspecialchars($p['nama_lengkap']) ?>
                                </td>
                                <?php foreach($sub_cpmk_list as $sc): 
                                    $skor = isset($nilai_matrix[$p['id']][$sc['id']]) ? floatval($nilai_matrix[$p['id']][$sc['id']]) : '';
                                ?>
                                    <td style="padding: 5px; border: 1px solid var(--border); text-align: center;">
                                        <input type="number" step="0.01" min="0" max="100" 
                                               name="nilai[<?= $p['id'] ?>][<?= $sc['id'] ?>]" 
                                               value="<?= $skor ?>"
                                               style="width: 70px; padding: 6px; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-primary); border-radius: 4px; text-align: center;">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
