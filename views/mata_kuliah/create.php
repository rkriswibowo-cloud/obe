<?php
$title = 'Tambah Mata Kuliah - Sistem OBE';
ob_start();
?>

<div style="margin-bottom: 20px;">
    <h2>Tambah Mata Kuliah</h2>
    <p style="color: var(--text-muted); margin-top: 5px;">Tambahkan data mata kuliah baru ke dalam kurikulum.</p>
</div>

<div style="background: var(--surface); padding: 24px; border: 1px solid var(--border); border-radius: var(--radius-md);">
    <form action="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah/store" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kurikulum Program Studi</label>
            <select name="kurikulum_id" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
                <option value="">-- Pilih Kurikulum Program Studi --</option>
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
                            <option value="<?= $k['id'] ?>">
                                <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?> (<?= htmlspecialchars($k['versi'] ?? 'v1.0') ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kode MK</label>
                <input type="text" name="kode" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);" placeholder="Contoh: SI101">
            </div>
            <div style="flex: 2;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nama Mata Kuliah</label>
                <input type="text" name="nama" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);" placeholder="Contoh: Algoritma dan Pemrograman">
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">SKS Teori</label>
                <input type="number" name="sks_teori" min="0" value="0" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">SKS Praktek</label>
                <input type="number" name="sks_praktek" min="0" value="0" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Semester</label>
                <input type="number" name="semester" min="1" max="14" value="1" required style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
            </div>
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Sifat</label>
                <select name="sifat" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
                    <option value="Wajib">Wajib</option>
                    <option value="Pilihan">Pilihan</option>
                </select>
            </div>
            <div style="flex: 2;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Kelompok Keilmuan</label>
                <input type="text" name="kelompok_keilmuan" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);" placeholder="Contoh: Rekayasa Perangkat Lunak">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Deskripsi</label>
            <textarea name="deskripsi" rows="3" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);" placeholder="Deskripsi singkat tentang mata kuliah..."></textarea>
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Status</label>
            <select name="status" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm);">
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
                <option value="ACTIVE">Active</option>
                <option value="INACTIVE">Inactive</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
