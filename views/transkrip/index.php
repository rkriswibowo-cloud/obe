<?php
$title = "Transkrip Akademik & Capaian CPL - Sistem OBE";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
$userRole = $_SESSION['role'] ?? '';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
            <i class="fa-solid fa-file-invoice" style="color: var(--primary); margin-right: 8px;"></i>Transkrip Akademik & Capaian CPL
        </h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Cetak transkrip akademik resmi mahasiswa lengkap dengan lampiran portofolio pemenuhan CPL (OBE) pada halaman kedua.
        </p>
    </div>

    <?php if ($mahasiswa): ?>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= $baseUrl ?>/transkrip/cetak?id=<?= $mahasiswa['id'] ?>" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 600; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                <i class="fa-solid fa-print"></i> Cetak Transkrip Resmi (PDF)
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($userRole !== 'Mahasiswa'): ?>
    <!-- FILTER PANEL: KURIKULUM, ANGKATAN & PILIH MAHASISWA -->
    <div style="background: var(--surface); padding: 18px 22px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px;">
        <form method="GET" action="<?= $baseUrl ?>/transkrip" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <!-- Dropdown Opsi Kurikulum -->
            <div style="flex: 1.5; min-width: 250px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 4px;"></i> Kurikulum:
                </label>
                <select name="kurikulum_id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-weight: 600; cursor: pointer;">
                    <?php 
                    $groupedKurikulum = [];
                    foreach ($daftarKurikulum as $k) {
                        $prodiKey = trim(($k['jenjang_prodi'] ?? '') . ' ' . ($k['nama_prodi'] ?? 'Program Studi'));
                        if (empty($prodiKey)) $prodiKey = 'Program Studi';
                        $groupedKurikulum[$prodiKey][] = $k;
                    }
                    foreach ($groupedKurikulum as $prodiName => $kList): ?>
                        <optgroup label="Prodi: <?= htmlspecialchars($prodiName) ?>">
                            <?php foreach ($kList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $k['id'] == $selectedKurikulumId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?> (Tahun <?= htmlspecialchars((string)$k['tahun_mulai']) ?> · <?= $k['total_cpl'] ?> CPL · <?= $k['total_mahasiswa'] ?> Mhs)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dropdown Filter Angkatan -->
            <div style="flex: 1; min-width: 160px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="fa-solid fa-filter" style="margin-right: 4px; color: var(--secondary);"></i> Angkatan:
                </label>
                <select name="angkatan" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); cursor: pointer;">
                    <option value="">-- Semua Angkatan --</option>
                    <?php foreach ($daftarAngkatan as $ang): ?>
                        <option value="<?= htmlspecialchars((string)$ang) ?>" <?= $selectedAngkatan == $ang ? 'selected' : '' ?>>
                            Angkatan <?= htmlspecialchars((string)$ang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dropdown Pilih Mahasiswa -->
            <div style="flex: 2; min-width: 280px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                    <i class="fa-solid fa-user-graduate" style="margin-right: 4px; color: var(--success);"></i> Pilih Mahasiswa:
                </label>
                <select name="id" onchange="this.form.submit()" style="width: 100%; padding: 9px 12px; font-size: 0.88rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-weight: 600; cursor: pointer;">
                    <?php if (empty($allMahasiswaInKurikulum)): ?>
                        <option value="">-- Belum ada mahasiswa di kurikulum ini --</option>
                    <?php else: ?>
                        <?php foreach ($allMahasiswaInKurikulum as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $m['id'] == $selectedMhsId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['username']) ?> - <?= htmlspecialchars($m['nama_lengkap']) ?> (Angkatan <?= htmlspecialchars((string)($m['angkatan'] ?? '-')) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <?php if (!empty($selectedAngkatan)): ?>
                <div style="margin-bottom: 2px;">
                    <a href="<?= $baseUrl ?>/transkrip?kurikulum_id=<?= $selectedKurikulumId ?>" class="btn btn-secondary" style="padding: 9px 12px; font-size: 0.85rem;" title="Reset Filter Angkatan">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<?php if (!$mahasiswa): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 48px 24px; text-align: center; color: var(--text-muted);">
        <i class="fa-solid fa-user-slash" style="font-size: 3rem; margin-bottom: 14px; opacity: 0.4;"></i>
        <h3 style="margin: 0; color: var(--text-main);">Mahasiswa Tidak Ditemukan</h3>
        <p style="margin: 6px 0 0 0; font-size: 0.9rem;">Silakan pilih kurikulum atau mahasiswa lain melalui filter di atas.</p>
    </div>
<?php else: ?>
    <!-- CARD RINGKASAN CEPAT -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">TOTAL SKS LULUS</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 2px;">
                <?= $khsData['ringkasan']['total_sks_lulus'] ?? 0 ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">SKS</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                Dari <?= count($khsData['semua_mk'] ?? []) ?> Mata Kuliah
            </div>
        </div>

        <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">INDEKS PRESTASI (IPK)</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb; margin-top: 2px;">
                <?= number_format($khsData['ringkasan']['ipk'] ?? 0, 2) ?>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                Predikat: <strong><?= htmlspecialchars($khsData['ringkasan']['predikat'] ?? '-') ?></strong>
            </div>
        </div>

        <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">RATA-RATA SKOR CPL</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: <?= ($cplData['ringkasan']['rata_rata_skor'] ?? 0) >= 70 ? 'var(--success)' : 'var(--warning)' ?>; margin-top: 2px;">
                <?= number_format($cplData['ringkasan']['rata_rata_skor'] ?? 0, 2) ?>%
            </div>
            <div style="font-size: 0.75rem; color: var(--success); margin-top: 2px;">
                Target Standar Mutu: ≥ 70%
            </div>
        </div>

        <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">CPL MEMENUHI STANDAR</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--success); margin-top: 2px;">
                <?= $cplData['ringkasan']['cpl_tercapai'] ?? 0 ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">/ <?= $cplData['ringkasan']['total_cpl'] ?? 0 ?> CPL</span>
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                Tercapai <?= $cplData['ringkasan']['persentase_tercapai'] ?? 0 ?>% (Standar OBE)
            </div>
        </div>
    </div>

    <!-- PREVIEW DOKUMEN CETAK 2 HALAMAN -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 28px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    <i class="fa-solid fa-file-contract" style="color: var(--primary); margin-right: 6px;"></i>
                    Pratinjau Dokumen Transkrip & Lampiran CPL
                </span>
                <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; background: rgba(99, 102, 241, 0.15); color: var(--primary); font-weight: 600;">
                    2 Halaman A4 Resmi
                </span>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= $baseUrl ?>/transkrip/cetak?id=<?= $mahasiswa['id'] ?>" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Mode Cetak Penuh
                </a>
            </div>
        </div>

        <!-- Tab Navigasi Halaman Pratinjau -->
        <div style="padding: 12px 20px; border-bottom: 1px solid var(--border); background: var(--surface); display: flex; gap: 10px;">
            <button type="button" id="tabBtnHal1" onclick="switchPreviewPage(1)" style="padding: 8px 16px; border-radius: var(--radius-sm); border: none; font-size: 0.88rem; font-weight: 700; cursor: pointer; background: var(--primary); color: white; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-file-lines"></i> Halaman 1: Transkrip Nilai Akademik
            </button>
            <button type="button" id="tabBtnHal2" onclick="switchPreviewPage(2)" style="padding: 8px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); font-size: 0.88rem; font-weight: 600; cursor: pointer; background: var(--surface); color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-chart-pie"></i> Halaman 2: Lampiran Capaian CPL (OBE)
            </button>
        </div>

        <!-- Frame Preview -->
        <div style="padding: 24px; background: rgba(0,0,0,0.15); display: flex; justify-content: center; overflow-x: auto;">
            <iframe id="transcriptPreviewFrame" src="<?= $baseUrl ?>/transkrip/cetak?id=<?= $mahasiswa['id'] ?>" style="width: 100%; max-width: 860px; height: 950px; border: 1px solid var(--border); border-radius: 6px; background: white; box-shadow: 0 8px 30px rgba(0,0,0,0.2);"></iframe>
        </div>
    </div>

    <!-- TABEL DAFTAR MAHASISWA KELAS / ANGKATAN INI -->
    <?php if ($userRole !== 'Mahasiswa'): ?>
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    <i class="fa-solid fa-users" style="color: var(--secondary); margin-right: 8px;"></i>
                    Daftar Mahasiswa - <?= htmlspecialchars($selectedKurikulum['nama'] ?? '') ?>
                    <?= !empty($selectedAngkatan) ? " (Angkatan $selectedAngkatan)" : "" ?>
                </h3>
                <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 600;">
                    Total <?= count($allMahasiswaInKurikulum) ?> Mahasiswa
                </span>
            </div>

            <?php if (empty($allMahasiswaInKurikulum)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Tidak ada mahasiswa yang terdaftar pada kurikulum ini.
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                        <thead>
                            <tr style="background: var(--surface-hover); border-bottom: 1px solid var(--border);">
                                <th style="padding: 12px 16px; width: 5%;">No</th>
                                <th style="padding: 12px 16px; width: 16%;">NIM</th>
                                <th style="padding: 12px 16px; width: 34%;">Nama Mahasiswa</th>
                                <th style="padding: 12px 16px; width: 15%;">Angkatan</th>
                                <th style="padding: 12px 16px; width: 30%; text-align: right;">Aksi Transkrip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allMahasiswaInKurikulum as $idx => $m): 
                                $isThisActive = ($m['id'] == $selectedMhsId);
                            ?>
                                <tr style="border-bottom: 1px solid var(--border); background: <?= $isThisActive ? 'rgba(99, 102, 241, 0.08)' : 'transparent' ?>;">
                                    <td style="padding: 10px 16px; color: var(--text-muted);"><?= $idx + 1 ?></td>
                                    <td style="padding: 10px 16px; font-family: monospace; font-weight: 700; color: var(--primary);">
                                        <?= htmlspecialchars($m['username']) ?>
                                    </td>
                                    <td style="padding: 10px 16px; font-weight: 600; color: var(--text-main);">
                                        <?= htmlspecialchars($m['nama_lengkap']) ?>
                                        <?php if ($isThisActive): ?>
                                            <span style="font-size: 0.72rem; padding: 2px 6px; border-radius: 3px; background: rgba(99, 102, 241, 0.2); color: var(--primary); font-weight: 600; margin-left: 6px;">Sedang Dilihat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px 16px; color: var(--text-muted);">
                                        Angkatan <?= htmlspecialchars((string)($m['angkatan'] ?? '-')) ?>
                                    </td>
                                    <td style="padding: 10px 16px; text-align: right;">
                                        <div style="display: inline-flex; gap: 8px;">
                                            <a href="<?= $baseUrl ?>/transkrip?kurikulum_id=<?= $selectedKurikulumId ?>&angkatan=<?= urlencode((string)$selectedAngkatan) ?>&id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary" title="Buka Pratinjau Transkrip">
                                                <i class="fa-solid fa-eye"></i> Pratinjau
                                            </a>
                                            <a href="<?= $baseUrl ?>/transkrip/cetak?id=<?= $m['id'] ?>" target="_blank" class="btn btn-sm btn-primary" title="Cetak Transkrip & CPL">
                                                <i class="fa-solid fa-print"></i> Cetak PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
function switchPreviewPage(pageNum) {
    const btn1 = document.getElementById('tabBtnHal1');
    const btn2 = document.getElementById('tabBtnHal2');
    const iframe = document.getElementById('transcriptPreviewFrame');

    if (pageNum === 1) {
        btn1.style.background = 'var(--primary)';
        btn1.style.color = 'white';
        btn1.style.border = 'none';
        btn2.style.background = 'var(--surface)';
        btn2.style.color = 'var(--text-muted)';
        btn2.style.border = '1px solid var(--border)';
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } else {
        btn2.style.background = 'var(--primary)';
        btn2.style.color = 'white';
        btn2.style.border = 'none';
        btn1.style.background = 'var(--surface)';
        btn1.style.color = 'var(--text-muted)';
        btn1.style.border = '1px solid var(--border)';
        if (iframe && iframe.contentWindow) {
            // Scroll to second page sheet
            const target = iframe.contentDocument.querySelector('.page-break');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
}
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
