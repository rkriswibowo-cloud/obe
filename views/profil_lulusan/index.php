<?php
// views/profil_lulusan/index.php
require_once __DIR__ . '/../../app/Support/Maintenance.php';
$title = 'Profil Lulusan - Sistem OBE';
ob_start();
$baseUrl = Maintenance::baseUrl($config['base_url'] ?? null);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Profil Lulusan</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Daftar profil peran dan keahlian lulusan program studi yang menjadi acuan penyusunan CPL.
        </p>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center;">
        <!-- Filter Kurikulum -->
        <form method="GET" action="<?= $baseUrl ?>/profil-lulusan" style="display: flex; gap: 8px; align-items: center;">
            <select name="kurikulum_id" onchange="this.form.submit()" style="padding: 8px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main);">
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
                            <option value="<?= $k['id'] ?>" <?= $selectedKurikulumId == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['kode']) ?> - <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </form>

        <a href="<?= $baseUrl ?>/profil-lulusan/create?kurikulum_id=<?= $selectedKurikulumId ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Profil
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span>
            <?php
                if ($_GET['success'] == 1) echo "Profil Lulusan berhasil ditambahkan!";
                elseif ($_GET['success'] == 2) echo "Profil Lulusan berhasil diperbarui!";
                elseif ($_GET['success'] == 3) echo "Profil Lulusan berhasil dihapus!";
            ?>
        </span>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 80px;">KODE</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 250px;">PROFIL LULUSAN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted);">DESKRIPSI PROFIL & PERAN</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 220px;">CPL PENDUKUNG</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); text-align: right; width: 130px;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($profilLulusanList)): ?>
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-graduation-cap" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada data Profil Lulusan pada kurikulum ini. Silakan klik tombol <strong>Tambah Profil</strong> di atas.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($profilLulusanList as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                        <td style="padding: 16px 18px; font-weight: 700; color: var(--primary);">
                            <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-family: monospace;">
                                <?= htmlspecialchars($p['kode']) ?>
                            </span>
                        </td>
                        <td style="padding: 16px 18px;">
                            <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">
                                <?= htmlspecialchars($p['nama']) ?>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 3px;">
                                <?= htmlspecialchars($p['nama_kurikulum']) ?>
                            </div>
                        </td>
                        <td style="padding: 16px 18px; color: var(--text-muted); font-size: 0.88rem; line-height: 1.45;">
                            <?= nl2br(htmlspecialchars($p['deskripsi'])) ?>
                        </td>
                        <td style="padding: 16px 18px;">
                            <?php if (!empty($p['cpl_kodes'])): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                    <?php foreach (explode(',', $p['cpl_kodes']) as $cplKode): ?>
                                        <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--success); padding: 2px 7px; border-radius: 4px; font-size: 0.78rem; font-weight: 600; font-family: monospace;">
                                            <?= htmlspecialchars(trim($cplKode)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Belum dipetakan</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 16px 18px; text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <a href="<?= $baseUrl ?>/profil-lulusan/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Profil">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="<?= $baseUrl ?>/profil-lulusan/delete?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" title="Hapus Profil" onclick="return confirm('Apakah Anda yakin ingin menghapus profil lulusan <?= htmlspecialchars($p['kode']) ?>?')">
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
