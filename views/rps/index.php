<?php
// views/rps/index.php
// RPS Builder & Curriculum Matrix Manager
$title = "RPS Builder: " . htmlspecialchars($mk['nama']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
$activeTab = $_GET['tab'] ?? 'identitas';
$rpsCanEdit = $rpsCanEdit ?? true;
$rpsIsArchive = $rpsIsArchive ?? false;
$assignmentQuery = !empty($rpsAssignment) ? '&ta_id='.(int)$rpsAssignment['tahun_akademik_id'] : '';

// Hitung total bobot asesmen dinamis dari master jenis penilaian.
$jenisPenilaianMaster = $jenisPenilaianMaster ?? RpsModel::getJenisPenilaianMaster();
$jenisPenilaian = $jenisPenilaian ?? [];
$totalBobotSistem = array_sum(array_map(static fn($item) => (float)($item['bobot'] ?? 0), $jenisPenilaian));
$ringkasanPenilaian = [];
foreach ($jenisPenilaian as $item) {
    $kode = $item['kode'] ?? '';
    $ringkasanPenilaian[] = ($jenisPenilaianMaster[$kode] ?? $kode) . ' ' . (float)($item['bobot'] ?? 0) . '%';
}
?>

<?php if(!empty($rpsAssignment)): ?>
<div style="margin-bottom:16px;padding:13px 16px;border:1px solid <?= $rpsCanEdit?'rgba(16,185,129,.35)':'rgba(148,163,184,.35)' ?>;border-radius:10px;background:<?= $rpsCanEdit?'rgba(16,185,129,.09)':'rgba(148,163,184,.09)' ?>;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid <?= $rpsCanEdit?'fa-circle-check':'fa-box-archive' ?>"></i>
    <div><strong><?= $rpsCanEdit?'RPS semester aktif — dapat diedit':'Arsip RPS — hanya dapat dilihat' ?></strong><div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?= htmlspecialchars($rpsAssignment['tahun_akademik']) ?> · PJMK <?= htmlspecialchars($rpsAssignment['pjmk_nama']) ?></div></div>
</div>
<?php endif; ?>

<!-- ========================================================================
     TOP HEADER & ACTION BAR
     ======================================================================== -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <a href="<?= $baseUrl ?><?= (($_SESSION['role']??'')==='Dosen')?'/rps':'/mata-kuliah' ?>" class="btn btn-sm btn-secondary" style="padding: 5px 10px;" title="Kembali">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <span style="background: rgba(59, 130, 246, 0.15); color: var(--primary); padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.5px;">
                    RPS BUILDER OBE
                </span>
                <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 3px 10px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                    Versi/Revisi: <?= htmlspecialchars($rps['versi'] ?? '3/0') ?>
                </span>
            </div>
            <h1 style="margin: 0 0 6px 0; font-size: 1.6rem; font-weight: 800; color: var(--text-main);">
                <?= htmlspecialchars($mk['kode']) ?> - <?= htmlspecialchars($mk['nama']) ?>
            </h1>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <span><i class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($rps['program_studi'] ?? 'S1 Sistem Informasi') ?></span>
                <span>&bull;</span>
                <span><i class="fa-solid fa-clock"></i> <?= htmlspecialchars($mk['sks_teori'] + $mk['sks_praktek']) ?> SKS (<?= $mk['sks_teori'] ?> Teori / <?= $mk['sks_praktek'] ?> Praktik)</span>
                <span>&bull;</span>
                <span><i class="fa-solid fa-calendar"></i> Semester <?= htmlspecialchars($mk['semester']) ?></span>
                <span>&bull;</span>
                <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($mk['sifat']) ?></span>
            </p>
        </div>

        <!-- Tombol Aksi Dokumen Resmi -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/rps/document?mk_id=<?= $mk['id'] ?><?= $assignmentQuery ?>" target="_blank" class="btn btn-primary" style="padding: 10px 18px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);">
                <i class="fa-solid fa-file-lines"></i> Lihat Dokumen Resmi RPS (PDF Layout)
            </a>
            <a href="<?= $baseUrl ?>/rps/document?mk_id=<?= $mk['id'] ?><?= $assignmentQuery ?>&print=1" target="_blank" class="btn btn-secondary" style="padding: 10px 16px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-print"></i> Cetak Dokumen RPS
            </a>
        </div>
    </div>

    <!-- Status Bar Kelengkapan RPS -->
    <div style="display: flex; gap: 20px; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border); flex-wrap: wrap;">
        <div style="font-size: 0.85rem;">
            <span style="color: var(--text-muted);">Total Bobot Sub-CPMK:</span>
            <strong style="color: <?= $totalSubBobot == 100 ? 'var(--success)' : 'var(--warning)' ?>; margin-left: 4px;">
                <?= $totalSubBobot ?>% <?= $totalSubBobot == 100 ? '✓ (Valid 100%)' : '⚠ (Harus 100%)' ?>
            </strong>
        </div>
        <div style="font-size: 0.85rem;">
            <span style="color: var(--text-muted);">Rincian Pertemuan:</span>
            <strong style="color: <?= count($pertemuanList) >= 16 ? 'var(--success)' : 'var(--warning)' ?>; margin-left: 4px;">
                <?= count($pertemuanList) ?> / 16 Sesi <?= count($pertemuanList) >= 16 ? '✓ (Lengkap)' : '' ?>
            </strong>
        </div>
        <div style="font-size: 0.85rem;">
            <span style="color: var(--text-muted);">Sistem Penilaian:</span>
            <strong style="color: <?= $totalBobotSistem == 100 ? 'var(--success)' : 'var(--danger)' ?>; margin-left: 4px;">
                <?= $totalBobotSistem ?>%<?= $ringkasanPenilaian ? ' (' . htmlspecialchars(implode(', ', $ringkasanPenilaian)) . ')' : '' ?>
            </strong>
        </div>
    </div>
</div>

<!-- Flash Notifications -->
<?php if (!empty($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
    </div>
<?php endif; ?>

<style>
/* Global RPS Builder Preset Buttons & Pills */
.preset-pill {
    background: var(--surface-hover, rgba(255, 255, 255, 0.05)) !important;
    color: var(--text-main, #e2e8f0) !important;
    border: 1px solid var(--border, rgba(255, 255, 255, 0.12)) !important;
    padding: 6px 13px !important;
    border-radius: 8px !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    line-height: 1.2 !important;
    text-decoration: none !important;
    outline: none !important;
    box-shadow: none !important;
}
.preset-pill:hover {
    background: var(--primary, #6366f1) !important;
    color: #ffffff !important;
    border-color: var(--primary, #6366f1) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
}
.preset-pill:active {
    transform: translateY(0);
}
.preset-pill-primary {
    background: rgba(99, 102, 241, 0.18) !important;
    color: #a5b4fc !important;
    border: 1px solid rgba(99, 102, 241, 0.45) !important;
    padding: 6px 13px !important;
    border-radius: 8px !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    line-height: 1.2 !important;
    text-decoration: none !important;
    outline: none !important;
}
.preset-pill-primary:hover {
    background: var(--primary, #6366f1) !important;
    color: #ffffff !important;
    border-color: var(--primary, #6366f1) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35) !important;
}
.preset-pill-primary:active {
    transform: translateY(0);
}
.preset-pill-clear {
    background: rgba(239, 68, 68, 0.1) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    padding: 6px 13px !important;
    border-radius: 8px !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    line-height: 1.2 !important;
    text-decoration: none !important;
    outline: none !important;
}
.preset-pill-clear:hover {
    background: #ef4444 !important;
    color: #ffffff !important;
    border-color: #ef4444 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35) !important;
}
.preset-pill-clear:active {
    transform: translateY(0);
}
</style>

<!-- ========================================================================
     NAVIGATION TABS
     ======================================================================== -->
<div style="display: flex; gap: 6px; border-bottom: 2px solid var(--border); margin-bottom: 24px; overflow-x: auto; padding-bottom: 2px;">
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=identitas" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'identitas' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'identitas' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-id-card"></i> 1. Identitas MK
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=cpmk" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'cpmk' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'cpmk' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-diagram-project"></i> 2a & 2c. CPMK & Sub-CPMK
        <span style="background: var(--surface-hover); color: var(--primary); padding: 2px 7px; border-radius: 999px; font-size: 0.75rem;">
            <?= count($allSubCpmks) ?> Sub
        </span>
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=matriks" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'matriks' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'matriks' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-table-cells"></i> 2b & 3. Matriks & Peta Analisis
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=referensi" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'referensi' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'referensi' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-book-bookmark"></i> 4. Referensi Pustaka
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=pertemuan" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'pertemuan' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'pertemuan' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-calendar-days"></i> 5. 16 Rincian Pertemuan
        <span style="background: var(--surface-hover); color: var(--primary); padding: 2px 7px; border-radius: 999px; font-size: 0.75rem;">
            <?= count($pertemuanList) ?>/16
        </span>
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=penilaian" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'penilaian' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'penilaian' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-calculator"></i> 6 & 7. Sistem Penilaian & Evaluasi
    </a>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=rubrik" 
       style="padding: 10px 18px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'rubrik' ? 'var(--primary)' : 'transparent' ?>; color: <?= $activeTab === 'rubrik' ? 'var(--text-main)' : 'var(--text-muted)' ?>; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
        <i class="fa-solid fa-list-check"></i> 8. Rubrik Penilaian
    </a>
</div>

<!-- ========================================================================
     TAB 1: IDENTITAS MATA KULIAH
     ======================================================================== -->
<?php if ($activeTab === 'identitas'): ?>
    <?php
    // Persiapan & Parsing Data Dosen Pengampu & Tim Teaching
    $currentDosenLines = [];
    if (!empty($rps['dosen_pengampu'])) {
        $rawLines = explode("\n", str_replace("\r", "", $rps['dosen_pengampu']));
        foreach ($rawLines as $l) {
            $l = trim($l);
            if (!empty($l)) $currentDosenLines[] = $l;
        }
    }

    // Ekstrak nama PJMK
    $currentPjmk = '';
    if (!empty($currentDosenLines)) {
        $firstLine = $currentDosenLines[0];
        $cleanFirst = preg_replace('/^\d+[\.\)]\s*/', '', $firstLine);
        $cleanFirst = preg_replace('/\s*\(?(PJMK|Koordinator)\)?\s*$/i', '', $cleanFirst);
        $currentPjmk = trim($cleanFirst);
    }
    if (empty($currentPjmk)) {
        $currentPjmk = $mk['dosen_nama'] ?? '';
    }

    // Ekstrak Anggota Tim Teaching
    $timTeachingRows = [];
    if (!empty($timTeaching) && is_array($timTeaching)) {
        foreach ($timTeaching as $tt) {
            if (!empty($tt['nama'])) {
                $timTeachingRows[] = [
                    'nama' => $tt['nama'],
                    'peran' => $tt['peran'] ?? 'Anggota Tim Teaching'
                ];
            }
        }
    } elseif (count($currentDosenLines) > 1) {
        for ($i = 1; $i < count($currentDosenLines); $i++) {
            $line = $currentDosenLines[$i];
            $cleanLine = preg_replace('/^\d+[\.\)]\s*/', '', $line);
            $peran = 'Anggota Tim Teaching';
            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $cleanLine, $m)) {
                $nama = trim($m[1]);
                $peran = trim($m[2]);
            } else {
                $nama = trim($cleanLine);
            }
            if (!empty($nama)) {
                $timTeachingRows[] = ['nama' => $nama, 'peran' => $peran];
            }
        }
    }

    $hasTimTeaching = !empty($timTeachingRows);

    // Penugasan resmi MK menjadi sumber utama, bukan teks lama pada tabel RPS.
    if (!empty($officialAssignment)) {
        $currentPjmk = $officialAssignment['pjmk_nama'];
        $timTeachingRows = [];
        foreach (($officialAssignment['tim'] ?? []) as $member) {
            $timTeachingRows[] = [
                'nama' => $member['nama_lengkap'],
                'peran' => 'Anggota Tim Teaching'
            ];
        }
        $hasTimTeaching = !empty($timTeachingRows);
        $officialLines = ['1. ' . $currentPjmk . ' (PJMK)'];
        foreach ($timTeachingRows as $index => $member) {
            $officialLines[] = ($index + 2) . '. ' . $member['nama'] . ' (' . $member['peran'] . ')';
        }
        $officialDosenText = implode("\n", $officialLines);
    } else {
        $officialDosenText = $rps['dosen_pengampu'] ?? ('1. ' . $currentPjmk . ' (PJMK)');
    }
    ?>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Header Info Tab -->
        <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: var(--radius-md); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                    <i class="fa-solid fa-id-card"></i> Bagian 1 Dokumen RPS
                </div>
                <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 700;">Identitas Mata Kuliah & Tim Pengajar</h2>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                    Kelola identitas dokumen kurikulum, karakteristik mata kuliah, dosen koordinator (PJMK), serta tim teaching perkuliahan.
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= $baseUrl ?>/rps/document?mk_id=<?= $mk['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 9px 18px; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-file-pdf" style="color: #ef4444;"></i> Lihat PDF Dokumen
                </a>
            </div>
        </div>

        <form action="<?= $baseUrl ?>/rps/save-settings" method="POST" id="formIdentitasRps">
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="identitas">
            <input type="hidden" name="dosen_pengampu" id="final_dosen_pengampu_input" value="<?= htmlspecialchars($rps['dosen_pengampu'] ?? '') ?>">

            <!-- CARD 1: INFORMASI DOKUMEN & KURIKULUM -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: #ffffff; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                            <img src="<?= $baseUrl ?>/assets/images/logo_uam.png" alt="Logo Universitas Anwar Medika" style="height: 36px; object-fit: contain;">
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700;">A. Informasi Dokumen & Institusi</h4>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Informasi versi dokumen, program studi, fakultas, dan tahun akademik</span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Versi / Revisi Dokumen:</label>
                        <input type="text" name="versi" class="form-control" value="<?= htmlspecialchars($rps['versi'] ?? '3/0') ?>" placeholder="Contoh: 3/0 atau 3.0" required>
                        <small style="color: var(--text-muted); font-size: 0.78rem;">Tercetak di pojok kanan atas dokumen RPS resmi</small>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Fakultas:</label>
                        <input type="text" name="fakultas" class="form-control" value="<?= htmlspecialchars($rps['fakultas'] ?? 'Fakultas Sains dan Teknologi') ?>" required>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Program Studi:</label>
                        <textarea name="program_studi" class="form-control" rows="3" maxlength="1000" placeholder="Pisahkan setiap program studi dengan baris baru atau koma" required><?= htmlspecialchars($rps['program_studi'] ?? 'S1 Sistem Informasi') ?></textarea>
                        <small style="color: var(--text-muted); font-size: 0.78rem;">Maksimal 1.000 karakter. Setiap program studi akan ditampilkan pada baris tersendiri di header cetak.</small>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Semester / Tahun Akademik:</label>
                        <select name="tahun_akademik_id" class="form-control" required>
                            <?php foreach (($activeAcademicYears ?? []) as $ta): ?>
                                <option value="<?= (int)$ta['id'] ?>" <?= !empty($officialAssignment) && (int)$officialAssignment['tahun_akademik_id']===(int)$ta['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ta['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: var(--text-muted); font-size: 0.78rem;">Diambil dari Tahun Akademik aktif agar periode RPS konsisten.</small>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Ketersediaan Dokumen:</label>
                        <select name="ketersediaan" class="form-control">
                            <option value="Program Studi" <?= ($rps['ketersediaan'] ?? 'Program Studi') === 'Program Studi' ? 'selected' : '' ?>>Program Studi</option>
                            <option value="Fakultas" <?= ($rps['ketersediaan'] ?? '') === 'Fakultas' ? 'selected' : '' ?>>Fakultas</option>
                            <option value="Universitas" <?= ($rps['ketersediaan'] ?? '') === 'Universitas' ? 'selected' : '' ?>>Universitas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Kelas Perkuliahan:</label>
                        <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($rps['kelas'] ?? ($mk['nama_kelas'] ?? 'A')) ?>" placeholder="Contoh: A, B, atau I-23">
                        <small style="color: var(--text-muted); font-size: 0.78rem;">Label kode kelas perkuliahan RPS</small>
                    </div>
                </div>
            </div>

            <!-- CARD 2: KARAKTERISTIK MATA KULIAH -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700;">B. Karakteristik & Beban Mata Kuliah</h4>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Atribut kurikulum, SKS, semester, dan prasyarat akademik</span>
                        </div>
                    </div>

                    <!-- Badge Ringkasan SKS -->
                    <div style="background: var(--surface-hover); border: 1px solid var(--border); padding: 6px 14px; border-radius: var(--radius-sm); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($mk['kode']) ?></span>
                        <span style="color: var(--border);">|</span>
                        <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($mk['nama']) ?></span>
                        <span style="color: var(--border);">|</span>
                        <span class="badge" style="background: rgba(99, 102, 241, 0.2); color: var(--primary); padding: 2px 8px; font-weight: 700; border-radius: 4px;">
                            <?= htmlspecialchars($mk['sks_total'] ?? ($mk['sks_teori'] + $mk['sks_praktek'])) ?> SKS
                        </span>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">(Teori: <?= $mk['sks_teori'] ?>, Praktek: <?= $mk['sks_praktek'] ?>)</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Semester Ke:</label>
                        <input type="number" name="semester_ke" class="form-control" value="<?= htmlspecialchars($mk['semester']) ?>" min="1" max="8" required>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Sifat Pengambilan:</label>
                        <select name="sifat" class="form-control">
                            <option value="Wajib" <?= ($rps['sifat'] ?? $mk['sifat'] ?? 'Wajib') === 'Wajib' ? 'selected' : '' ?>>Wajib</option>
                            <option value="Pilihan" <?= ($rps['sifat'] ?? $mk['sifat'] ?? '') === 'Pilihan' ? 'selected' : '' ?>>Pilihan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Kelompok Keilmuan (KBK):</label>
                        <input type="text" name="kelompok_keilmuan" class="form-control" value="<?= htmlspecialchars($mk['kelompok_keilmuan'] ?: ($rps['kelompok_keilmuan'] ?? 'Sistem Informasi')) ?>" placeholder="Contoh: Sistem Informasi / Rekayasa Perangkat Lunak">
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.88rem;">Mata Kuliah Prasyarat:</label>
                        <input type="text" name="prasyarat" class="form-control" value="<?= htmlspecialchars($rps['prasyarat'] ?? '-') ?>" placeholder="Tuliskan '-' jika tidak ada">
                    </div>
                </div>
            </div>

            <!-- CARD 3: DOSEN PENGAMPU & TIM TEACHING -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700;">C. Dosen Pengampu & Tim Teaching</h4>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Tentukan Dosen Koordinator/PJMK dan anggota dosen tim teaching jika ada</span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($officialAssignment)): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:18px">
                        <div style="display:flex;align-items:center;gap:10px"><i class="fa-solid fa-circle-check" style="color:#10b981"></i><div><strong>Sudah tersinkron dengan Pengampu Mata Kuliah</strong><div style="font-size:.8rem;color:var(--text-muted)">PJMK dan tim teaching mengikuti penugasan tahun akademik aktif.</div></div></div>
                        <a href="<?= $baseUrl ?>/pengampu-mk" class="btn btn-sm btn-secondary"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Pengaturan</a>
                    </div>
                <?php else: ?>
                    <div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:18px;color:var(--warning)"><i class="fa-solid fa-triangle-exclamation"></i> Belum ada penugasan pengampu untuk mata kuliah ini pada tahun akademik aktif.</div>
                <?php endif; ?>

                <!-- Notifikasi Auto-Detect Dosen dari Kelas Perkuliahan Aktif -->
                <?php if (!empty($detectedKelasTimTeaching) && empty($officialAssignment)): ?>
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-lightbulb" style="color: var(--info); font-size: 1.2rem;"></i>
                            <div style="font-size: 0.88rem;">
                                <strong style="color: var(--info);">Dosen Terdeteksi di Kelas Perkuliahan:</strong>
                                <span style="color: var(--text-muted);">
                                    <?php 
                                    $detectedNames = array_map(function($d) {
                                        return htmlspecialchars($d['nama_lengkap']);
                                    }, $detectedKelasTimTeaching);
                                    echo implode(', ', $detectedNames);
                                    ?>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-info" onclick="importAllDetectedTimTeaching()" style="padding: 6px 14px; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-user-plus"></i> Masukkan ke Tim Teaching
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Dosen Pengampu Utama / PJMK -->
                <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <label style="margin: 0; font-weight: 700; font-size: 0.92rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-user-tie" style="color: var(--primary);"></i>
                            Dosen Koordinator / PJMK (Penanggung Jawab Mata Kuliah) <span style="color: var(--danger);">*</span>
                        </label>
                        <span class="badge" style="background: rgba(99, 102, 241, 0.2); color: var(--primary); font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                            URUTAN 1 (PJMK)
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;">Pilih dari Daftar Dosen Terdaftar:</label>
                            <select id="select_pjmk" class="form-control" onchange="onPjmkSelectChange(this)" style="font-size: 0.9rem;" <?= !empty($officialAssignment) ? 'disabled' : '' ?>>
                                <option value="">-- Pilih Dosen Pengampu Sistem --</option>
                                <?php foreach ($allDosen as $dsn): ?>
                                    <option value="<?= htmlspecialchars($dsn['nama_lengkap']) ?>" <?= (trim($currentPjmk) === trim($dsn['nama_lengkap'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dsn['nama_lengkap']) ?> (NIDN: <?= htmlspecialchars($dsn['nidn'] ?: '-') ?>)
                                    </option>
                                <?php endforeach; ?>
                                <option value="__manual__">+ Ketik Nama Manual / Dosen Eksternal...</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;">Nama Lengkap & Gelar PJMK:</label>
                            <input type="text" name="dosen_pjmk" id="input_dosen_pjmk" class="form-control" value="<?= htmlspecialchars($currentPjmk) ?>" placeholder="Contoh: Dr. Budi Santoso, M.Kom." oninput="updateDosenPreview()" required <?= !empty($officialAssignment) ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>

                <!-- Toggle Checkbox Memiliki Tim Teaching -->
                <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="checkbox" id="has_tim_teaching" name="has_tim_teaching" value="1" <?= $hasTimTeaching ? 'checked' : '' ?> onchange="toggleTimTeachingSection()" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary);" <?= !empty($officialAssignment) ? 'disabled' : '' ?>>
                        <label for="has_tim_teaching" style="margin: 0; cursor: pointer; font-weight: 600; font-size: 0.92rem; color: var(--text-main);">
                            Mata Kuliah ini Diampu oleh Tim Teaching (Memiliki Anggota Dosen Tambahan)
                        </label>
                    </div>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                        Centang jika ada dosen paralel, dosen kelas lain, atau asisten/dosen praktikum
                    </span>
                </div>

                <!-- Bagian Tabel Anggota Tim Teaching (Collapsible / Dynamic) -->
                <div id="timTeachingSection" style="display: <?= $hasTimTeaching ? 'block' : 'none' ?>; margin-top: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-users"></i> Anggota Tim Teaching Tambahan:
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addTimTeachingRow()" style="font-size: 0.82rem; padding: 6px 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;" <?= !empty($officialAssignment) ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-plus"></i> Tambah Dosen Tim
                        </button>
                    </div>

                    <div style="overflow-x: auto; border: 1px solid var(--border); border-radius: var(--radius-sm); margin-bottom: 16px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;" id="timTeachingTable">
                            <thead>
                                <tr style="background: var(--surface-hover); border-bottom: 1px solid var(--border); text-align: left;">
                                    <th style="padding: 10px 14px; width: 45px; text-align: center;">#</th>
                                    <th style="padding: 10px 14px; width: 32%;">Pilih Dosen Sistem</th>
                                    <th style="padding: 10px 14px; width: 34%;">Nama Lengkap & Gelar</th>
                                    <th style="padding: 10px 14px; width: 26%;">Peran dalam Tim</th>
                                    <th style="padding: 10px 14px; width: 50px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="timTeachingBody">
                                <?php if (!empty($timTeachingRows)): ?>
                                    <?php foreach ($timTeachingRows as $idx => $row): ?>
                                        <tr class="tim-teaching-row" style="border-bottom: 1px solid var(--border);">
                                            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: var(--text-muted);" class="row-number">
                                                <?= ($idx + 2) ?>
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <select class="form-control select-dosen-row" onchange="onRowDosenSelectChange(this)" style="font-size: 0.85rem; padding: 6px 10px;" <?= !empty($officialAssignment) ? 'disabled' : '' ?>>
                                                    <option value="">-- Pilih Dosen --</option>
                                                    <?php foreach ($allDosen as $dsn): ?>
                                                        <option value="<?= htmlspecialchars($dsn['nama_lengkap']) ?>" <?= (trim($row['nama']) === trim($dsn['nama_lengkap'])) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($dsn['nama_lengkap']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <option value="__manual__">+ Dosen Luar / Manual</option>
                                                </select>
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <input type="text" name="tim_teaching_dosen[]" class="form-control input-dosen-nama" value="<?= htmlspecialchars($row['nama']) ?>" placeholder="Nama Lengkap & Gelar Dosen" oninput="updateDosenPreview()" style="font-size: 0.88rem; padding: 6px 10px;" required <?= !empty($officialAssignment) ? 'readonly' : '' ?>>
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <div style="display: flex; gap: 6px;">
                                                    <input type="text" name="tim_teaching_peran[]" class="form-control input-dosen-peran" value="<?= htmlspecialchars($row['peran']) ?>" placeholder="Contoh: Anggota Tim Teaching" oninput="updateDosenPreview()" style="font-size: 0.88rem; padding: 6px 10px;" list="peranOptions" <?= !empty($officialAssignment) ? 'readonly' : '' ?>>
                                                </div>
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center;">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimTeachingRow(this)" style="padding: 6px 10px; border-radius: 4px; color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08); cursor: pointer;" title="Hapus Baris" <?= !empty($officialAssignment) ? 'disabled' : '' ?>>
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Datalist Rekomendasi Peran -->
                    <datalist id="peranOptions">
                        <option value="Anggota Tim Teaching">
                        <option value="Dosen Praktikum">
                        <option value="Dosen Pengampu Kelas B">
                        <option value="Dosen Pengampu Kelas C">
                        <option value="Asisten Dosen">
                        <option value="Dosen Tamu / Praktisi">
                    </datalist>
                </div>

                <!-- Kotak Live Preview Tampilan di Dokumen RPS -->
                <div style="background: var(--surface-hover); border: 1px dashed rgba(99, 102, 241, 0.4); border-radius: var(--radius-sm); padding: 14px 18px; margin-top: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 0.82rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-eye"></i> Preview Tampilan di Dokumen RPS Resmi:
                        </span>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Otomatis terformat sesuai standar RPS A4</span>
                    </div>
                    <div id="previewDosenPengampu" style="background: var(--surface); border: 1px solid var(--border); padding: 12px 16px; border-radius: 4px; font-family: 'Bookman Old Style', 'Bookman', serif; font-size: 0.92rem; color: var(--text-main); white-space: pre-line; line-height: 1.6;">
                        <?= htmlspecialchars($officialDosenText) ?>
                    </div>
                </div>
            </div>

            <!-- CARD 4: BENTUK & MEDIA PEMBELAJARAN (RESPONSIF & LUAS) -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: var(--info); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">D. Bentuk & Media Pembelajaran</h4>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">Metode perkuliahan (pedagogi) dan sarana pendukung pembelajaran yang digunakan</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="resetBentukMediaDefault()" style="font-size: 0.8rem; padding: 6px 14px; display: inline-flex; align-items: center; gap: 6px;" title="Kembalikan ke susunan teks standar RPS resmi">
                        <i class="fa-solid fa-rotate-left"></i> Reset ke Format Baku RPS
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
                    <!-- Kolom 1: Bentuk Pembelajaran -->
                    <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                                <label style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-book-open-reader" style="color: var(--primary);"></i> Bentuk & Metode Pembelajaran: <span style="color: var(--danger);">*</span>
                                </label>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">(Tercetak pada Bagian 1 RPS)</span>
                            </div>

                            <textarea name="bentuk_pembelajaran" id="textarea_bentuk" rows="5" class="form-control" style="width: 100%; min-height: 125px; resize: vertical; font-size: 0.92rem; line-height: 1.6; padding: 12px; border-radius: var(--radius-sm); box-sizing: border-box;" placeholder="Tuliskan bentuk perkuliahan..."><?= htmlspecialchars($rps['bentuk_pembelajaran'] ?? 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara/ Praktikum/ Praktik Studio/Praktek Lapangan/ Penelitian/ Pengabdian Kepada Masyarakat') ?></textarea>
                        </div>

                        <!-- Quick Preset Pills -->
                        <div style="margin-top: 14px; border-top: 1px dashed var(--border); padding-top: 12px;">
                            <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--warning);"></i> Tambah Cepat Metode Pembelajaran:
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <button type="button" class="preset-pill" onclick="appendBentuk('Kuliah/ Responsi/ Tutorial')"><i class="fa-solid fa-plus"></i> Kuliah/Tutorial</button>
                                <button type="button" class="preset-pill" onclick="appendBentuk('Praktikum/ Praktik Studio')"><i class="fa-solid fa-plus"></i> Praktikum Lab</button>
                                <button type="button" class="preset-pill" onclick="appendBentuk('Problem Based Learning (PBL)')"><i class="fa-solid fa-plus"></i> PBL</button>
                                <button type="button" class="preset-pill" onclick="appendBentuk('Project Based Learning (PjBL)')"><i class="fa-solid fa-plus"></i> PjBL</button>
                                <button type="button" class="preset-pill" onclick="appendBentuk('Seminar & Diskusi Kelompok')"><i class="fa-solid fa-plus"></i> Diskusi/Seminar</button>
                                <button type="button" class="preset-pill" onclick="appendBentuk('Praktik Lapangan / Magang')"><i class="fa-solid fa-plus"></i> Praktik Lapangan</button>
                                <button type="button" class="preset-pill-clear" onclick="clearBentuk()"><i class="fa-solid fa-trash-can"></i> Kosongkan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom 2: Media Pembelajaran -->
                    <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                                <label style="margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-desktop" style="color: var(--secondary);"></i> Media & Sarana Pembelajaran: <span style="color: var(--danger);">*</span>
                                </label>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">(Hardware, Software, LMS)</span>
                            </div>

                            <textarea name="media_pembelajaran" id="input_media" rows="5" class="form-control" style="width: 100%; min-height: 125px; resize: vertical; font-size: 0.92rem; line-height: 1.6; padding: 12px; border-radius: var(--radius-sm); box-sizing: border-box;" placeholder="Tuliskan media pembelajaran..."><?= htmlspecialchars($rps['media_pembelajaran'] ?? '*Audio/Visual/Audiovisual, LCD Proyektor & Whiteboard, LMS / Google Classroom, Laboratorium Komputer') ?></textarea>
                        </div>

                        <!-- Quick Preset Pills -->
                        <div style="margin-top: 14px; border-top: 1px dashed var(--border); padding-top: 12px;">
                            <div style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--warning);"></i> Tambah Cepat Media & Sarana:
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <button type="button" class="preset-pill" onclick="appendMedia('*Audio/Visual/Audiovisual')"><i class="fa-solid fa-plus"></i> Audio/Visual</button>
                                <button type="button" class="preset-pill" onclick="appendMedia('LCD Proyektor & Whiteboard')"><i class="fa-solid fa-plus"></i> LCD Proyektor</button>
                                <button type="button" class="preset-pill" onclick="appendMedia('LMS / Google Classroom')"><i class="fa-solid fa-plus"></i> LMS Classroom</button>
                                <button type="button" class="preset-pill" onclick="appendMedia('Laboratorium Komputer')"><i class="fa-solid fa-plus"></i> Lab Komputer</button>
                                <button type="button" class="preset-pill" onclick="appendMedia('Zoom / Google Meet')"><i class="fa-solid fa-plus"></i> Zoom/GMeet</button>
                                <button type="button" class="preset-pill" onclick="appendMedia('Modul Praktikum & E-Book')"><i class="fa-solid fa-plus"></i> Modul & E-Book</button>
                                <button type="button" class="preset-pill-clear" onclick="clearMedia()"><i class="fa-solid fa-trash-can"></i> Kosongkan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN / ACTION BAR -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i>
                    Perubahan akan langsung terkompilasi ke dokumen RPS resmi dan tersinkronisasi di sistem OBE.
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="<?= $baseUrl ?>/mata-kuliah" class="btn btn-secondary" style="padding: 10px 18px; font-weight: 600;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Identitas & Tim Pengajar RPS
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT LOGIC UNTUK TAB 1 IDENTITAS & TIM TEACHING -->
    <script>
    // Daftar Dosen Lengkap untuk Template Row Dinamis
    const availableDosenList = <?= json_encode($allDosen ?? []) ?>;
    const detectedKelasTimTeachingList = <?= json_encode($detectedKelasTimTeaching ?? []) ?>;

    function onPjmkSelectChange(selectElem) {
        const val = selectElem.value;
        const inputElem = document.getElementById('input_dosen_pjmk');
        if (val && val !== '__manual__') {
            inputElem.value = val;
        } else if (val === '__manual__') {
            inputElem.value = '';
            inputElem.focus();
        }
        updateDosenPreview();
    }

    function toggleTimTeachingSection() {
        const check = document.getElementById('has_tim_teaching');
        const section = document.getElementById('timTeachingSection');
        if (check.checked) {
            section.style.display = 'block';
            // Jika tabel kosong, tambahkan 1 baris pertama
            const tbody = document.getElementById('timTeachingBody');
            if (tbody.querySelectorAll('tr').length === 0) {
                addTimTeachingRow();
            }
        } else {
            section.style.display = 'none';
        }
        updateDosenPreview();
    }

    function addTimTeachingRow(nama = '', peran = 'Anggota Tim Teaching') {
        const tbody = document.getElementById('timTeachingBody');
        const nextNum = tbody.querySelectorAll('tr').length + 2;

        let selectOptions = `<option value="">-- Pilih Dosen --</option>`;
        availableDosenList.forEach(dsn => {
            const isSel = (nama && nama.trim() === dsn.nama_lengkap.trim()) ? 'selected' : '';
            selectOptions += `<option value="${escapeHtml(dsn.nama_lengkap)}" ${isSel}>${escapeHtml(dsn.nama_lengkap)}</option>`;
        });
        selectOptions += `<option value="__manual__">+ Dosen Luar / Manual</option>`;

        const tr = document.createElement('tr');
        tr.className = 'tim-teaching-row';
        tr.style.borderBottom = '1px solid var(--border)';
        tr.innerHTML = `
            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: var(--text-muted);" class="row-number">
                ${nextNum}
            </td>
            <td style="padding: 10px 14px;">
                <select class="form-control select-dosen-row" onchange="onRowDosenSelectChange(this)" style="font-size: 0.85rem; padding: 6px 10px;">
                    ${selectOptions}
                </select>
            </td>
            <td style="padding: 10px 14px;">
                <input type="text" name="tim_teaching_dosen[]" class="form-control input-dosen-nama" value="${escapeHtml(nama)}" placeholder="Nama Lengkap & Gelar Dosen" oninput="updateDosenPreview()" style="font-size: 0.88rem; padding: 6px 10px;" required>
            </td>
            <td style="padding: 10px 14px;">
                <div style="display: flex; gap: 6px;">
                    <input type="text" name="tim_teaching_peran[]" class="form-control input-dosen-peran" value="${escapeHtml(peran)}" placeholder="Contoh: Anggota Tim Teaching" oninput="updateDosenPreview()" style="font-size: 0.88rem; padding: 6px 10px;" list="peranOptions">
                </div>
            </td>
            <td style="padding: 10px 14px; text-align: center;">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimTeachingRow(this)" style="padding: 6px 10px; border-radius: 4px; color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08); cursor: pointer;" title="Hapus Baris">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        reindexRows();
        updateDosenPreview();
    }

    function onRowDosenSelectChange(selectElem) {
        const row = selectElem.closest('tr');
        const inputNama = row.querySelector('.input-dosen-nama');
        const val = selectElem.value;
        if (val && val !== '__manual__') {
            inputNama.value = val;
        } else if (val === '__manual__') {
            inputNama.value = '';
            inputNama.focus();
        }
        updateDosenPreview();
    }

    function removeTimTeachingRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        reindexRows();
        updateDosenPreview();
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#timTeachingBody tr');
        rows.forEach((r, idx) => {
            const numTd = r.querySelector('.row-number');
            if (numTd) numTd.textContent = idx + 2;
        });
    }

    function importAllDetectedTimTeaching() {
        const check = document.getElementById('has_tim_teaching');
        check.checked = true;
        toggleTimTeachingSection();

        detectedKelasTimTeachingList.forEach(dsn => {
            // Cek apakah nama dosen sudah ada di baris
            const existingInputs = document.querySelectorAll('.input-dosen-nama');
            let exists = false;
            existingInputs.forEach(inp => {
                if (inp.value.trim().toLowerCase() === dsn.nama_lengkap.trim().toLowerCase()) {
                    exists = true;
                }
            });

            // Juga cek apakah sama dengan PJMK
            const pjmkVal = document.getElementById('input_dosen_pjmk').value.trim().toLowerCase();
            if (pjmkVal === dsn.nama_lengkap.trim().toLowerCase()) {
                exists = true;
            }

            if (!exists) {
                addTimTeachingRow(dsn.nama_lengkap, 'Anggota Tim Teaching');
            }
        });
    }

    function updateDosenPreview() {
        const pjmkInput = document.getElementById('input_dosen_pjmk');
        const pjmkVal = (pjmkInput ? pjmkInput.value.trim() : '');
        const check = document.getElementById('has_tim_teaching');
        const lines = [];

        if (pjmkVal) {
            lines.push(`1. ${pjmkVal} (PJMK)`);
        }

        if (check && check.checked) {
            const rows = document.querySelectorAll('#timTeachingBody tr');
            rows.forEach((r, idx) => {
                const nama = r.querySelector('.input-dosen-nama')?.value.trim();
                let peran = r.querySelector('.input-dosen-peran')?.value.trim();
                if (!peran) peran = 'Anggota Tim Teaching';
                if (nama) {
                    const no = lines.length + 1;
                    lines.push(`${no}. ${nama} (${peran})`);
                }
            });
        }

        const previewText = lines.length > 0 ? lines.join('\n') : '- Belum ditentukan -';
        const previewElem = document.getElementById('previewDosenPengampu');
        if (previewElem) {
            previewElem.textContent = previewText;
        }

        const hiddenInput = document.getElementById('final_dosen_pengampu_input');
        if (hiddenInput) {
            hiddenInput.value = lines.join('\n');
        }
    }

    function appendBentuk(text) {
        const ta = document.getElementById('textarea_bentuk');
        if (!ta.value.includes(text)) {
            ta.value = ta.value.trim() ? (ta.value.trim() + ' / ' + text) : text;
        }
        ta.focus();
    }

    function clearBentuk() {
        const ta = document.getElementById('textarea_bentuk');
        ta.value = '';
        ta.focus();
    }

    function appendMedia(text) {
        const inp = document.getElementById('input_media');
        if (!inp.value.includes(text)) {
            inp.value = inp.value.trim() ? (inp.value.trim() + ', ' + text) : text;
        }
        inp.focus();
    }

    function clearMedia() {
        const inp = document.getElementById('input_media');
        inp.value = '';
        inp.focus();
    }

    function resetBentukMediaDefault() {
        const defaultBentuk = 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara/ Praktikum/ Praktik Studio/Praktek Lapangan/ Penelitian/ Pengabdian Kepada Masyarakat';
        const defaultMedia = '*Audio/Visual/Audiovisual, LCD Proyektor & Whiteboard, LMS / Google Classroom, Laboratorium Komputer';
        document.getElementById('textarea_bentuk').value = defaultBentuk;
        document.getElementById('input_media').value = defaultMedia;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Inisialisasi awal saat load
    document.addEventListener('DOMContentLoaded', function() {
        updateDosenPreview();
    });
    </script>
<?php endif; ?>

<!-- ========================================================================
     TAB 2: CAPAIAN PEMBELAJARAN (CPMK & SUB-CPMK)
     ======================================================================== -->
<?php if ($activeTab === 'cpmk'): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 1.2rem;">2a & 2c. CPMK, Sub-CPMK, dan Indikator Penilaian</h3>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                Petakan CPMK ke CPL, rumuskan Sub-CPMK bertaksonomi (C/A/P), indikator terukur, serta bobot persentase.
            </p>
        </div>
        <a href="<?= $baseUrl ?>/rps/cpmk/create?mk_id=<?= $mk['id'] ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah CPMK
        </a>
    </div>

    <?php if (empty($cpmkList)): ?>
        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--info); color: var(--info); padding: 24px; border-radius: var(--radius-md); text-align: center;">
            <i class="fa-solid fa-circle-info" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
            <h4 style="margin: 0 0 6px 0;">Belum ada CPMK untuk mata kuliah ini</h4>
            <p style="margin: 0 0 16px 0; font-size: 0.9rem;">Tambahkan rumusan CPMK dan petakan ke CPL terkait sebelum membuat Sub-CPMK.</p>
            <a href="<?= $baseUrl ?>/rps/cpmk/create?mk_id=<?= $mk['id'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah CPMK Sekarang
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($cpmkList as $cpmk): ?>
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm);">
                    <!-- CPMK Header -->
                    <div style="padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; background: rgba(0,0,0,0.06);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <span style="background: var(--primary); color: #fff; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 0.85rem;">
                                    <?= htmlspecialchars($cpmk['kode']) ?>
                                </span>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">
                                    Terkait CPL: <strong><?= !empty($cpmk['cpls']) ? implode(', ', array_column($cpmk['cpls'], 'kode')) : '<em>(Belum dipilih)</em>' ?></strong>
                                </span>
                            </div>
                            <div style="font-size: 1.05rem; font-weight: 600; color: var(--text-main); line-height: 1.4;">
                                <?= htmlspecialchars($cpmk['rumusan']) ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px; margin-left: 20px;">
                            <a href="<?= $baseUrl ?>/rps/cpmk/edit?id=<?= $cpmk['id'] ?>" class="btn btn-sm btn-secondary" title="Edit CPMK">
                                <i class="fa-solid fa-edit"></i> Edit
                            </a>
                            <a href="<?= $baseUrl ?>/rps/cpmk/delete?id=<?= $cpmk['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus CPMK ini beserta semua Sub-CPMK di dalamnya?')" title="Hapus CPMK">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Sub-CPMK Table List -->
                    <div style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <h4 style="margin: 0; font-size: 0.95rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                                Sub-CPMK di bawah <?= htmlspecialchars($cpmk['kode']) ?>
                            </h4>
                            <a href="<?= $baseUrl ?>/rps/sub_cpmk/create?cpmk_id=<?= $cpmk['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus"></i> Tambah Sub-CPMK
                            </a>
                        </div>

                        <?php if (empty($cpmk['sub_cpmks'])): ?>
                            <div style="padding: 16px; border: 1px dashed var(--border); border-radius: var(--radius-md); text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                                Belum ada Sub-CPMK untuk CPMK ini.
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <?php foreach ($cpmk['sub_cpmks'] as $sub): ?>
                                    <div style="border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; background: var(--surface-hover);">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
                                            <div style="flex: 1;">
                                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                                    <span style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">
                                                        <?= htmlspecialchars($sub['kode']) ?>
                                                    </span>
                                                    <?php if (!empty($sub['level_taksonomi'])): ?>
                                                        <span style="background: rgba(124, 58, 237, 0.15); color: #8b5cf6; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                                            <?= htmlspecialchars($sub['level_taksonomi']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                                        Bobot: <?= floatval($sub['bobot']) ?>%
                                                    </span>
                                                </div>
                                                <div style="font-size: 0.95rem; color: var(--text-main); margin-bottom: 10px; line-height: 1.4;">
                                                    <?= htmlspecialchars($sub['rumusan']) ?>
                                                </div>

                                                <!-- Indikator -->
                                                <?php if (!empty($sub['indikators'])): ?>
                                                    <div style="margin-top: 8px; padding: 8px 12px; background: rgba(0,0,0,0.06); border-radius: 4px;">
                                                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">
                                                            Indikator Penilaian (<?= count($sub['indikators']) ?>):
                                                        </div>
                                                        <ol style="margin: 0; padding-left: 18px; font-size: 0.85rem; color: var(--text-main);">
                                                            <?php foreach ($sub['indikators'] as $ind): ?>
                                                                <li style="margin-bottom: 3px;"><?= htmlspecialchars($ind['indikator']) ?></li>
                                                            <?php endforeach; ?>
                                                        </ol>
                                                    </div>
                                                <?php endif; ?>

                                                <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 16px;">
                                                    <span><strong>Pengalaman Belajar:</strong> <?= htmlspecialchars($sub['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Diskusi kasus') ?></span>
                                                    <span><strong>Asesmen:</strong> <?= htmlspecialchars($sub['asesmen_penilaian'] ?? 'Pretes & Postes') ?></span>
                                                </div>
                                            </div>

                                            <div style="display: flex; gap: 6px;">
                                                <a href="<?= $baseUrl ?>/rps/sub_cpmk/edit?id=<?= $sub['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Sub-CPMK">
                                                    <i class="fa-solid fa-edit"></i>
                                                </a>
                                                <a href="<?= $baseUrl ?>/rps/sub_cpmk/delete?id=<?= $sub['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus Sub-CPMK <?= htmlspecialchars($sub['kode']) ?>?')" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ========================================================================
     TAB 3: MATRIKS HUBUNGAN CPL & PETA ANALISIS PEMBELAJARAN
     ======================================================================== -->
<?php if ($activeTab === 'matriks'): ?>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Matriks 2b: Hubungan CPL dan Sub-CPMK -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px;">
            <?php 
            // Prioritaskan CPL yang dibebankan pada mata kuliah ini berdasarkan Pemetaan MK-CPL ($cpls)
            $hasMappedCpl = !empty($cpls);
            $matrixCpls = $hasMappedCpl ? $cpls : (!empty($kurikulumCpls) ? $kurikulumCpls : []);
            $cplColsCount = count($matrixCpls);
            ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 style="margin: 0 0 6px 0; font-size: 1.25rem;">2b. Matriks Hubungan CPL dan Sub-CPMK</h3>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                        Pemetaan bobot kontribusi setiap Sub-CPMK terhadap Capaian Pembelajaran Lulusan (CPL) yang dibebankan pada mata kuliah ini. Total seluruh bobot harus mencapai 100%.
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if ($hasMappedCpl): ?>
                        <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid var(--success); padding: 5px 12px; border-radius: 999px; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-circle-check"></i> Sinkron: <?= count($matrixCpls) ?> CPL Terpetakan
                        </span>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/pemetaan-mk-cpl?kurikulum_id=<?= $mk['kurikulum_id'] ?>" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;" title="Atur Pemetaan MK ke CPL">
                        <i class="fa-solid fa-sliders"></i> Atur Pemetaan MK-CPL
                    </a>
                </div>
            </div>

            <?php if (!$hasMappedCpl): ?>
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning); color: var(--warning); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 0.9rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <strong>Mata kuliah ini belum dipetakan ke CPL</strong> di menu Pemetaan MK ke CPL. Kolom di bawah menampilkan seluruh CPL kurikulum sebagai acuan sementara.
                    </div>
                    <a href="<?= $baseUrl ?>/pemetaan-mk-cpl?kurikulum_id=<?= $mk['kurikulum_id'] ?>" class="btn btn-sm btn-warning">
                        <i class="fa-solid fa-link"></i> Petakan Sekarang
                    </a>
                </div>
            <?php endif; ?>

            <div style="overflow-x: auto;">
                <?php if ($cplColsCount === 0): ?>
                    <div style="padding: 24px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info" style="font-size: 1.5rem; margin-bottom: 8px; display: block; opacity: 0.6;"></i>
                        Belum ada CPL yang terdaftar pada kurikulum mata kuliah ini. Silakan tambahkan CPL pada Master CPL terlebih dahulu.
                    </div>
                <?php else: ?>
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--surface-hover);">
                            <th rowspan="2" style="border: 1px solid var(--border); padding: 10px; text-align: left;">Kode SUB-CPMK</th>
                            <th colspan="<?= $cplColsCount ?>" style="border: 1px solid var(--border); padding: 10px; text-align: center;">Kode CPL / Bobot Kontribusi</th>
                            <th rowspan="2" style="border: 1px solid var(--border); padding: 10px; text-align: center; width: 140px;">Jumlah Bobot (%)</th>
                        </tr>
                        <tr style="background: var(--surface-hover);">
                            <?php foreach ($matrixCpls as $mcpl): ?>
                                <th style="border: 1px solid var(--border); padding: 8px; text-align: center; min-width: 80px;" title="<?= htmlspecialchars($mcpl['rumusan']) ?>">
                                    <?= htmlspecialchars($mcpl['kode']) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalMatrix = 0;
                        $cplTotals = array_fill_keys(array_column($matrixCpls, 'id'), 0);

                        foreach ($allSubCpmks as $sub): 
                            $b = floatval($sub['bobot']);
                            $totalMatrix += $b;
                            $subCplIds = $sub['cpl_ids'] ?? [];
                        ?>
                            <tr>
                                <td style="border: 1px solid var(--border); padding: 10px; font-weight: 600;">
                                    <span style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($sub['kode']) ?></span>
                                    <div style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);"><?= htmlspecialchars(substr($sub['rumusan'], 0, 75)) ?>...</div>
                                </td>
                                <?php foreach ($matrixCpls as $mcpl): 
                                    $isLinked = in_array($mcpl['id'], $subCplIds);
                                    if ($isLinked) {
                                        $cplTotals[$mcpl['id']] += $b;
                                    }
                                ?>
                                    <td style="border: 1px solid var(--border); text-align: center; <?= $isLinked ? 'font-weight: 700; color: var(--primary); background: rgba(59, 130, 246, 0.06);' : 'color: var(--text-muted);' ?>">
                                        <?= $isLinked ? ($b . '%') : '-' ?>
                                    </td>
                                <?php endforeach; ?>
                                <td style="border: 1px solid var(--border); text-align: center; font-weight: 700; color: var(--success);">
                                    <?= $b ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: var(--surface-hover); font-weight: 700;">
                            <td style="border: 1px solid var(--border); padding: 12px; text-align: center;">Jumlah</td>
                            <?php foreach ($matrixCpls as $mcpl): ?>
                                <td style="border: 1px solid var(--border); text-align: center; color: <?= $cplTotals[$mcpl['id']] > 0 ? 'var(--primary)' : 'var(--text-muted)' ?>;">
                                    <?= $cplTotals[$mcpl['id']] > 0 ? ($cplTotals[$mcpl['id']] . '%') : '-' ?>
                                </td>
                            <?php endforeach; ?>
                            <td style="border: 1px solid var(--border); text-align: center; color: <?= $totalMatrix == 100 ? 'var(--success)' : 'var(--warning)' ?>;">
                                <?= $totalMatrix ?>%
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bagian 3: Peta Analisis Capaian Pembelajaran -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px;">
            <div style="margin-bottom: 20px;">
                <h3 style="margin: 0 0 6px 0; font-size: 1.25rem;">3. Peta Analisis Capaian Pembelajaran (Roadmap & Prasyarat)</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                    Visualisasi keterkaitan hierarkis, alur kompetensi, dan tahapan pencapaian CPMK dari Sub-CPMK tingkat dasar hingga tingkat lanjut untuk mata kuliah <strong><?= htmlspecialchars($mk['nama']) ?></strong>.
                </p>
            </div>

            <!-- Flowchart Container -->
            <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; color: var(--text-main);">
                <?php if (empty($cpmkList) || empty($allSubCpmks)): ?>
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <i class="fa-solid fa-diagram-project" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 12px; display: block;"></i>
                        <h4 style="margin: 0 0 6px 0;">Belum ada Data CPMK & Sub-CPMK</h4>
                        <p style="margin: 0 0 16px 0; font-size: 0.9rem;">Tambahkan rumusan CPMK dan Sub-CPMK pada tab CPMK agar roadmap analisis pembelajaran dapat divisualisasikan secara otomatis.</p>
                        <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=cpmk" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Kelola CPMK & Sub-CPMK
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 16px;">
                        <!-- CPMK Master Node -->
                        <div style="background: var(--surface); border: 2px solid var(--primary); border-radius: 8px; padding: 16px 22px; text-align: center; width: 100%; max-width: 720px; box-shadow: var(--shadow-sm);">
                            <span style="color: var(--primary); font-size: 0.78rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">
                                Capaian Pembelajaran Mata Kuliah (CPMK) &bull; <?= htmlspecialchars($mk['kode']) ?>
                            </span>
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                                <?php foreach ($cpmkList as $cpmk): ?>
                                    <div style="font-size: 0.92rem; text-align: left; background: var(--surface-hover); padding: 8px 12px; border-radius: 6px; border-left: 3px solid var(--primary);">
                                        <strong style="color: var(--primary);"><?= htmlspecialchars($cpmk['kode']) ?>:</strong> <?= htmlspecialchars($cpmk['rumusan']) ?>
                                        <?php if (!empty($cpmk['cpls'])): ?>
                                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 6px;">[<?= implode(', ', array_column($cpmk['cpls'], 'kode')) ?>]</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div style="color: var(--primary); font-size: 1.3rem;"><i class="fa-solid fa-arrow-up"></i></div>

                        <?php 
                        // Reverse urutan Sub-CPMK agar urutan evaluasi akhir di atas, dan pondasi di bawah
                        $reversedSubs = array_reverse($allSubCpmks);
                        $totalSubs = count($reversedSubs);
                        $colorPalettes = [
                            ['border' => '#10b981', 'badge' => 'rgba(16, 185, 129, 0.25)', 'text' => '#10b981'],
                            ['border' => '#f59e0b', 'badge' => 'rgba(245, 158, 11, 0.25)', 'text' => '#f59e0b'],
                            ['border' => '#3b82f6', 'badge' => 'rgba(59, 130, 246, 0.25)', 'text' => '#3b82f6'],
                            ['border' => '#8b5cf6', 'badge' => 'rgba(139, 92, 246, 0.25)', 'text' => '#8b5cf6'],
                            ['border' => '#06b6d4', 'badge' => 'rgba(6, 182, 212, 0.25)', 'text' => '#06b6d4'],
                        ];
                        
                        $tiers = [];
                        $first = true;
                        $temp = [];
                        foreach ($reversedSubs as $idx => $s) {
                            if ($first) {
                                $tiers[] = [$s];
                                $first = false;
                            } else {
                                $temp[] = $s;
                                if (count($temp) === 2 || $idx === $totalSubs - 1) {
                                    $tiers[] = $temp;
                                    $temp = [];
                                }
                            }
                        }

                        foreach ($tiers as $tIdx => $tierItems):
                            $cIdx = $tIdx % count($colorPalettes);
                            $cp = $colorPalettes[$cIdx];
                        ?>
                            <?php if ($tIdx > 0): ?>
                                <div style="color: <?= $cp['border'] ?>; font-size: 1.1rem;"><i class="fa-solid fa-arrow-up"></i></div>
                            <?php endif; ?>

                            <div style="display: grid; grid-template-columns: <?= count($tierItems) > 1 ? '1fr 1fr' : '1fr' ?>; gap: 14px; width: 100%; max-width: 720px;">
                                <?php foreach ($tierItems as $sub): ?>
                                    <div style="background: var(--surface); border: 1.5px solid <?= $cp['border'] ?>; border-radius: 8px; padding: 12px 16px; box-shadow: var(--shadow-sm);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                                            <strong style="color: <?= $cp['text'] ?>; font-size: 0.88rem;">[<?= htmlspecialchars($sub['kode']) ?>]</strong>
                                            <span style="background: <?= $cp['badge'] ?>; color: <?= $cp['text'] ?>; padding: 1px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                                <?= !empty($sub['level_taksonomi']) ? htmlspecialchars($sub['level_taksonomi']) . ' &bull; ' : '' ?>Bobot <?= floatval($sub['bobot']) ?>%
                                            </span>
                                        </div>
                                        <div style="font-size: 0.84rem; color: var(--text-main); line-height: 1.4;">
                                            <?= htmlspecialchars($sub['rumusan']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================================================
     TAB 4: REFERENSI PUSTAKA (RESPONSIF DENGAN LIVE PREVIEW)
     ======================================================================== -->
<?php if ($activeTab === 'referensi'): ?>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Header Info Tab -->
        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: var(--radius-md); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; font-weight: 700; color: var(--success); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                    <i class="fa-solid fa-book-bookmark"></i> Bagian 4 Dokumen RPS
                </div>
                <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 700;">Referensi & Daftar Pustaka Perkuliahan</h2>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                    Kelola daftar buku teks utama, jurnal ilmiah, pedoman standar industri, dan modul ajar untuk mata kuliah <strong><?= htmlspecialchars($mk['kode']) ?> - <?= htmlspecialchars($mk['nama']) ?></strong>.
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= $baseUrl ?>/rps/document?mk_id=<?= $mk['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 9px 18px; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-file-pdf" style="color: #ef4444;"></i> Lihat PDF Dokumen
                </a>
            </div>
        </div>

        <form action="<?= $baseUrl ?>/rps/save-settings" method="POST" id="formReferensiRps">
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="referensi">

            <!-- MAIN 2-COLUMN RESPONSIVE LAYOUT (EDITOR + LIVE PREVIEW) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px; margin-bottom: 24px;">
                
                <!-- KOLOM KIRI: EDITOR TEXTAREA LUAS & TOOLBAR -->
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <!-- Header Editor -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700;">Editor Daftar Pustaka</h4>
                                    <span style="font-size: 0.78rem; color: var(--text-muted);">Tuliskan atau tempel 1 referensi per baris</span>
                                </div>
                            </div>
                            <span id="refCounterBadge" style="background: rgba(99, 102, 241, 0.15); color: var(--primary); padding: 4px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700;">
                                0 Referensi
                            </span>
                        </div>

                        <!-- Toolbar Tombol Jalan Pintas (Pills) -->
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
                            <button type="button" class="preset-pill-primary" onclick="autoNumberReferences()" title="Urutkan kembali nomor 1, 2, 3... secara otomatis">
                                <i class="fa-solid fa-arrow-down-1-9"></i> Rapikan Penomoran
                            </button>
                            <button type="button" class="preset-pill" onclick="insertSampleCitation('book')">
                                <i class="fa-solid fa-book"></i> + Contoh Buku (APA)
                            </button>
                            <button type="button" class="preset-pill" onclick="insertSampleCitation('journal')">
                                <i class="fa-solid fa-newspaper"></i> + Contoh Jurnal
                            </button>
                            <button type="button" class="preset-pill" onclick="cleanEmptyRefLines()" title="Hapus baris kosong">
                                <i class="fa-solid fa-compress"></i> Bersihkan Baris Kosong
                            </button>
                            <button type="button" class="preset-pill-clear" onclick="clearReferences()" title="Kosongkan seluruh teks">
                                <i class="fa-solid fa-trash-can"></i> Kosongkan
                            </button>
                        </div>

                        <!-- Textarea Luas & Responsif -->
                        <div class="form-group" style="margin-bottom: 12px;">
                            <textarea name="referensi" id="textarea_referensi" rows="15" class="form-control" 
                                      style="width: 100%; min-height: 340px; resize: vertical; font-family: 'Bookman Old Style', 'Courier New', monospace; font-size: 0.92rem; line-height: 1.65; padding: 14px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface-hover); color: var(--text-main); box-sizing: border-box;" 
                                      placeholder="Tuliskan daftar pustaka di sini (1 referensi per baris)..." 
                                      oninput="updateReferensiLivePreview()"><?= htmlspecialchars($rps['referensi'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Footer Tip -->
                    <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed var(--border); padding-top: 10px; margin-top: 6px; flex-wrap: wrap; gap: 8px;">
                        <span><i class="fa-solid fa-circle-info" style="color: var(--info);"></i> Format standar: Nama Penulis. (Tahun). <em>Judul Buku</em>. Penerbit.</span>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="loadDefaultPSSIReferensi()" style="font-size: 0.75rem; padding: 3px 8px;">
                            <i class="fa-solid fa-rotate-left"></i> Contoh Standar PSSI
                        </button>
                    </div>
                </div>

                <!-- KOLOM KANAN: LIVE PREVIEW DOKUMEN RESMI RPS (SEKSI 4) -->
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px; display: flex; flex-direction: column;">
                    <!-- Header Preview -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                <i class="fa-solid fa-eye"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700;">Live Preview Dokumen RPS</h4>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">Tampilan persis seperti di Bagian 4 dokumen cetak A4</span>
                            </div>
                        </div>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                            A4 PREVIEW
                        </span>
                    </div>

                    <!-- Simulated White A4 Paper Section 4 -->
                    <div style="flex: 1; background: #ffffff; color: #000000; border: 1px solid #000000; border-radius: 4px; padding: 18px 22px; font-family: 'Bookman Old Style', 'Bookman', serif; min-height: 340px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <div style="font-size: 11pt; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1.5px solid #000000; padding-bottom: 4px;">
                            4. REFERENSI
                        </div>
                        
                        <div id="livePreviewRefContainer">
                            <ol id="liveRefList" style="margin: 0; padding-left: 20px; font-size: 9.5pt; line-height: 1.6;">
                                <!-- Item referensi dirender secara realtime via JavaScript -->
                            </ol>
                            <div id="liveRefEmptyMsg" style="display: none; padding: 20px; text-align: center; color: #64748b; font-style: italic; font-size: 9.5pt;">
                                Belum ada daftar referensi yang diinputkan. Ketik pada editor di sebelah kiri untuk melihat pratinjau.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN / ACTION BAR -->
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--success);"></i>
                    Daftar pustaka akan otomatis tersusun rapi dengan penomoran standar di dokumen RPS resmi.
                </div>
                <div style="display: flex; gap: 12px;">
                    <a href="<?= $baseUrl ?>/mata-kuliah" class="btn btn-secondary" style="padding: 10px 18px; font-weight: 600;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Daftar Referensi
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- JAVASCRIPT UNTUK TAB 4 REFERENSI -->
    <script>
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function getReferensiLines() {
        const ta = document.getElementById('textarea_referensi');
        if (!ta) return [];
        return ta.value.split('\n')
            .map(line => line.trim())
            .filter(line => line.length > 0);
    }

    function updateReferensiLivePreview() {
        const lines = getReferensiLines();
        const listElem = document.getElementById('liveRefList');
        const emptyMsg = document.getElementById('liveRefEmptyMsg');
        const badgeElem = document.getElementById('refCounterBadge');

        if (badgeElem) {
            badgeElem.textContent = `${lines.length} Referensi`;
        }

        if (!listElem) return;

        if (lines.length === 0) {
            listElem.innerHTML = '';
            if (emptyMsg) emptyMsg.style.display = 'block';
            return;
        }

        if (emptyMsg) emptyMsg.style.display = 'none';

        let html = '';
        lines.forEach(line => {
            // Bersihkan nomor di depan jika ada agar tidak double numbering dalam <ol>
            const clean = line.replace(/^\d+[\.\)]\s*/, '');
            html += `<li style="margin-bottom: 6px; text-align: justify;">${escapeHtml(clean)}</li>`;
        });
        listElem.innerHTML = html;
    }

    function autoNumberReferences() {
        const lines = getReferensiLines();
        if (lines.length === 0) return;

        const numbered = lines.map((line, idx) => {
            const clean = line.replace(/^\d+[\.\)]\s*/, '');
            return `${idx + 1}. ${clean}`;
        });

        const ta = document.getElementById('textarea_referensi');
        ta.value = numbered.join('\n');
        updateReferensiLivePreview();
    }

    function cleanEmptyRefLines() {
        const lines = getReferensiLines();
        const ta = document.getElementById('textarea_referensi');
        ta.value = lines.join('\n');
        updateReferensiLivePreview();
    }

    function insertSampleCitation(type) {
        const ta = document.getElementById('textarea_referensi');
        const count = getReferensiLines().length + 1;
        let sample = '';
        if (type === 'book') {
            sample = `${count}. Pressman, R. S., & Maxim, B. R. (2020). Software Engineering: A Practitioner's Approach (9th ed.). McGraw-Hill Education.`;
        } else if (type === 'journal') {
            sample = `${count}. Santoso, B., & Rahayu, S. (2023). Evaluasi Kinerja Sistem Informasi Manajemen Menggunakan COBIT 5. Jurnal Teknologi dan Sistem Informasi, 9(2), 115-124.`;
        }

        if (ta.value.trim().length > 0) {
            ta.value = ta.value.trim() + '\n' + sample;
        } else {
            ta.value = sample;
        }
        updateReferensiLivePreview();
        ta.scrollTop = ta.scrollHeight;
    }

    function clearReferences() {
        if (confirm('Kosongkan semua daftar pustaka?')) {
            const ta = document.getElementById('textarea_referensi');
            ta.value = '';
            updateReferensiLivePreview();
        }
    }

    function loadDefaultPSSIReferensi() {
        const defaultRef = `1. Ward, J., & Peppard, J. (2016). Strategic Planning for Information Systems (4th ed.). Wiley.
2. Pearlson, K. E., Saunders, C. S., & Galletta, D. F. (2020). Managing and Using Information Systems: A Strategic Approach (7th ed.). Wiley.
3. O'Brien, J. A., & Marakas, G. M. (2017). Management Information Systems (10th ed.). McGraw-Hill Education.
4. TOGAF Standard, Version 9.2. (2018). The Open Group.
5. Laudon, K. C., & Laudon, J. P. (2020). Management Information Systems: Managing the Digital Firm (16th ed.). Pearson.
6. Andrew, J. (2011). TOGAF Version 9.1 A Pocket Guide. Berkshire: Van Haren Publishing.
7. Dietz, J. L., & Hoogervorst, J. A. (2013). An Enterprise Engineering Based Examination of TOGAF. Lecturer Notes on Business Information Processing (LNBIP), 79.
8. Fitriani, L. (2016). Perencanaan Arsitektur Enterprise Sistem Informasi dengan Menggunakan TOGAF ADM. Jurnal STT-Garut, 443-450.`;
        const ta = document.getElementById('textarea_referensi');
        ta.value = defaultRef;
        updateReferensiLivePreview();
    }

    // Inisialisasi awal live preview saat halaman dibuka
    document.addEventListener('DOMContentLoaded', function() {
        updateReferensiLivePreview();
    });
    </script>
<?php endif; ?>

<!-- ========================================================================
     TAB 5: 16 RINCIAN PERTEMUAN MINGGUAN
     ======================================================================== -->
<?php if ($activeTab === 'pertemuan'): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 1.25rem;">5. Rincian Aktivitas Pembelajaran 16 Pertemuan</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                    Perencanaan jadwal perkuliahan 1 s.d. 16 pekan dengan alokasi Flipped Learning, CBT UTS (pekan 8) dan CBT UAS (pekan 16).
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="openCsvModal()" class="btn btn-secondary">
                    <i class="fa-solid fa-file-csv"></i> Tempel CSV
                </button>
                <button type="button" onclick="openCreateModal()" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Tambah Sesi Pertemuan
                </button>
                <a href="<?= $baseUrl ?>/rps/pertemuan/generate-template?mk_id=<?= $mk['id'] ?>" 
                   onclick="return confirm('Generate otomatis 16 pertemuan untuk mata kuliah ini?')"
                   class="btn btn-secondary">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Template 16 Sesi
                </a>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="background: var(--surface-hover);">
                        <th style="border: 1px solid var(--border); padding: 10px; width: 60px; text-align: center;">Pekan</th>
                        <th style="border: 1px solid var(--border); padding: 10px; width: 130px; text-align: center;">Sub-CPMK</th>
                        <th style="border: 1px solid var(--border); padding: 10px; text-align: left;">Bahan Kajian / Topik</th>
                        <th style="border: 1px solid var(--border); padding: 10px; text-align: left;">Rancangan Aktivitas & Durasi</th>
                        <th style="border: 1px solid var(--border); padding: 10px; width: 100px; text-align: center;">Mode</th>
                        <th style="border: 1px solid var(--border); padding: 10px; width: 150px; text-align: left;">Media / Pustaka</th>
                        <th style="border: 1px solid var(--border); padding: 10px; width: 80px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pertemuanList)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                Belum ada data pertemuan mingguan. Klik tombol "Template 16 Sesi" untuk mengisi otomatis.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pertemuanList as $p): ?>
                            <tr style="<?= ($p['pertemuan_ke'] == 8 || $p['pertemuan_ke'] == 16) ? 'background: rgba(124, 58, 237, 0.08); font-weight: 600;' : '' ?>">
                                <td style="border: 1px solid var(--border); text-align: center; font-weight: 700;">
                                    <?= htmlspecialchars($p['pertemuan_ke']) ?>
                                </td>
                                <td style="border: 1px solid var(--border); text-align: center;">
                                    <span style="background: rgba(59, 130, 246, 0.15); color: var(--primary); padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">
                                        <?= htmlspecialchars($p['sub_cpmk_kode'] ?? 'SUB-CPMK 01') ?>
                                    </span>
                                </td>
                                <td style="border: 1px solid var(--border); font-weight: 600;">
                                    <?= htmlspecialchars($p['bahan_kajian']) ?>
                                </td>
                                <td style="border: 1px solid var(--border); font-size: 0.85rem; line-height: 1.4;">
                                    <?= nl2br(htmlspecialchars($p['durasi_detail'] ?: $p['pengalaman_belajar'])) ?>
                                </td>
                                <td style="border: 1px solid var(--border); text-align: center; font-weight: 600;">
                                    <span style="background: rgba(16, 185, 129, 0.15); color: var(--success); padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">
                                        <?= htmlspecialchars($p['mode_pembelajaran'] ?? 'ASM dan TM') ?>
                                    </span>
                                </td>
                                <td style="border: 1px solid var(--border); font-size: 0.8rem;">
                                    <?= nl2br(htmlspecialchars($p['pustaka'] ?? "WAG, Zoom Meetings\n[1], [2]")) ?>
                                </td>
                                <td style="border: 1px solid var(--border); text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center;">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($p) ?>)' title="Edit Sesi">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <a href="<?= $baseUrl ?>/rps/pertemuan/delete?id=<?= $p['id'] ?>&mk_id=<?= $mk['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pertemuan ini?')" title="Hapus">
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
    </div>

    <!-- Modal Import CSV Pertemuan -->
    <div id="csvPertemuanModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.68);z-index:10000;justify-content:center;align-items:center;padding:20px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);width:min(1050px,100%);max-height:92vh;overflow:auto;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,.45);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;margin-bottom:16px;">
                <div><h3 style="margin:0 0 5px;font-size:1.2rem;"><i class="fa-solid fa-file-csv" style="color:var(--success);margin-right:8px;"></i>Impor Rincian Pertemuan dari CSV</h3><p style="margin:0;color:var(--text-muted);font-size:.84rem;">Tempel isi CSV dengan pemisah titik koma atau koma. Pertemuan yang sudah ada akan diperbarui berdasarkan nomor pekan.</p></div>
                <button type="button" onclick="closeCsvModal()" style="background:none;border:0;color:var(--text-muted);font-size:1.2rem;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="<?= $baseUrl ?>/rps/pertemuan/import-csv" method="POST" onsubmit="return validateCsvImport();">
                <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
                <div style="padding:12px 14px;border:1px solid rgba(59,130,246,.3);background:rgba(59,130,246,.08);border-radius:8px;margin-bottom:12px;font-size:.8rem;line-height:1.55;color:var(--text-muted);">
                    <strong style="color:var(--text-main);">Header wajib:</strong> pertemuan_ke, sub_cpmk, bahan_kajian.<br>
                    <strong style="color:var(--text-main);">Header opsional:</strong> bentuk_metode, mode_pembelajaran, pengalaman_belajar, alokasi_waktu, durasi_detail, pustaka, indikator_penilaian, bobot_penilaian.<br>
                    Gunakan tanda <strong>|</strong> untuk beberapa Sub-CPMK, contoh: <code>Sub-CPMK 1.1|Sub-CPMK 1.2</code>.
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:10px;">
                    <label for="csvPertemuanText" style="font-weight:700;font-size:.86rem;">Isi CSV</label>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="fillCsvExample()"><i class="fa-solid fa-wand-magic-sparkles"></i> Isi Contoh Format</button>
                </div>
                <textarea id="csvPertemuanText" name="csv_text" required spellcheck="false" placeholder="pertemuan_ke;sub_cpmk;bahan_kajian;..." style="width:100%;min-height:330px;box-sizing:border-box;padding:14px;border:1px solid var(--border);border-radius:8px;background:#070b12;color:#dbeafe;font:13px/1.55 Consolas,monospace;resize:vertical;white-space:pre;overflow:auto;"></textarea>
                <div id="csvPreviewStatus" style="margin-top:12px;font-size:.82rem;color:var(--text-muted);"></div>
                <div id="csvPreviewWrap" style="display:none;margin-top:8px;border:1px solid var(--border);border-radius:8px;overflow:auto;max-height:280px;">
                    <table id="csvPreviewTable" style="width:100%;border-collapse:collapse;min-width:850px;font-size:.76rem;"></table>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid var(--border);padding-top:15px;margin-top:15px;">
                    <button type="button" onclick="closeCsvModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-file-import"></i> Impor CSV</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Pertemuan -->
    <div id="pertemuanModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); width: 100%; max-width: 650px; padding: 24px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.2rem;">Form Sesi Pertemuan RPS</h3>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form id="formPertemuan" action="<?= $baseUrl ?>/rps/pertemuan/store" method="POST">
                <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
                <input type="hidden" name="id" id="field_id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Pekan Ke (1-16):</label>
                        <input type="number" name="pertemuan_ke" id="field_pertemuan_ke" class="form-control" min="1" max="16" required>
                    </div>

                    <div class="form-group">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Sub-CPMK:</label>
                        <select name="sub_cpmk_ids[]" id="field_sub_cpmk_id" class="form-control" multiple size="5">
                            <?php foreach ($allSubCpmks as $sub): ?>
                                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['kode']) ?> - <?= htmlspecialchars(substr($sub['rumusan'], 0, 45)) ?>...</option>
                            <?php endforeach; ?>
                        </select>
                        <small style="display:block; margin-top:4px; color:var(--text-muted);">Tahan Ctrl (Windows) atau Cmd (Mac) untuk memilih beberapa Sub-CPMK.</small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600;">Bahan Kajian / Topik Pembelajaran:</label>
                    <input type="text" name="bahan_kajian" id="field_bahan_kajian" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Mode Pembelajaran:</label>
                        <select name="mode_pembelajaran" id="field_mode_pembelajaran" class="form-control">
                            <option value="ASM dan TM">ASM dan TM (Asinkron & Tatap Muka)</option>
                            <option value="TM">TM (Tatap Muka Kelas)</option>
                            <option value="TMD">TMD (Tatap Muka Daring / Zoom)</option>
                            <option value="ASM">ASM (Asinkron Mandiri)</option>
                            <option value="ASK">ASK (Asinkron Kolaborasi)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 4px; font-weight: 600;">Alokasi Waktu / Sesi:</label>
                        <input type="text" name="alokasi_waktu" id="field_alokasi_waktu" class="form-control" value="2 x 50 menit">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600;">Rancangan Aktivitas & Durasi Flipped Learning:</label>
                    <textarea name="durasi_detail" id="field_durasi_detail" rows="4" class="form-control" placeholder="Contoh:&#10;Flipped learning:&#10;• Dosen memberikan materi (1x50 menit)&#10;• Praktikum mandiri (1x60 menit)&#10;• Penugasan (1x60 menit)"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 4px; font-weight: 600;">Media Pembelajaran / Referensi:</label>
                    <input type="text" name="pustaka" id="field_pustaka" class="form-control" value="WAG, Zoom Meetings, Youtube [1], [2], [3]">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 14px;">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;"><i class="fa-solid fa-floppy-disk"></i> Simpan Sesi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openCsvModal() {
        document.getElementById('csvPertemuanModal').style.display = 'flex';
        setTimeout(() => { document.getElementById('csvPertemuanText').focus(); renderCsvPreview(); }, 50);
    }
    function closeCsvModal() { document.getElementById('csvPertemuanModal').style.display = 'none'; }
    function fillCsvExample() {
        const example = <?= json_encode("pertemuan_ke;sub_cpmk;bahan_kajian;bentuk_metode;mode_pembelajaran;alokasi_waktu;durasi_detail;pustaka;indikator_penilaian;bobot_penilaian\n1;" . ($allSubCpmks[0]['kode'] ?? 'Sub-CPMK 1.1') . ";Pengantar dan ruang lingkup;Kuliah & Diskusi;TM;2 x 50 menit;Penjelasan materi dan diskusi;Referensi [1];Keaktifan dan pemahaman;5\n2;" . ($allSubCpmks[1]['kode'] ?? ($allSubCpmks[0]['kode'] ?? 'Sub-CPMK 1.2')) . ";Topik pertemuan kedua;Studi Kasus;ASM dan TM;2 x 50 menit;Analisis kasus dan presentasi;Referensi [1], [2];Ketepatan analisis;5", JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        const textarea = document.getElementById('csvPertemuanText');
        if (!textarea.value || confirm('Ganti isi textarea dengan contoh format CSV?')) { textarea.value = example; renderCsvPreview(); }
    }
    function detectCsvDelimiter(line) {
        const counts = {'\t':(line.match(/\t/g)||[]).length,';':(line.match(/;/g)||[]).length,',':(line.match(/,/g)||[]).length};
        return Object.entries(counts).sort((a,b)=>b[1]-a[1])[0][0];
    }
    function parseCsvPreviewLine(line, delimiter) {
        const cells=[]; let value=''; let quoted=false;
        for(let i=0;i<line.length;i++) { const char=line[i]; if(char==='"'){ if(quoted&&line[i+1]==='"'){value+='"';i++;}else quoted=!quoted; } else if(char===delimiter&&!quoted){cells.push(value.trim());value='';} else value+=char; }
        cells.push(value.trim()); return cells;
    }
    function normalizeCsvHeader(header) {
        let key=header.replace(/^\uFEFF/,'').trim().toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
        const aliases={pekan:'pertemuan_ke',pekan_ke:'pertemuan_ke',minggu:'pertemuan_ke',minggu_ke:'pertemuan_ke',pertemuan:'pertemuan_ke',sub_cpmk_kode:'sub_cpmk',kode_sub_cpmk:'sub_cpmk',topik:'bahan_kajian',bahan_kajian_topik:'bahan_kajian',materi:'bahan_kajian',metode:'bentuk_metode',mode:'mode_pembelajaran',waktu:'alokasi_waktu',aktivitas:'durasi_detail',aktivitas_durasi:'durasi_detail',rancangan_aktivitas:'durasi_detail',rancangan_aktivitas_durasi:'durasi_detail',rancangan_aktivitas_dan_durasi:'durasi_detail',media:'pustaka',referensi:'pustaka',media_pustaka:'pustaka',media_referensi:'pustaka',media_pembelajaran_referensi:'pustaka',indikator:'indikator_penilaian',bobot:'bobot_penilaian'};
        return aliases[key]||key;
    }
    function renderCsvPreview() {
        const text=document.getElementById('csvPertemuanText').value.trim(); const status=document.getElementById('csvPreviewStatus'); const wrap=document.getElementById('csvPreviewWrap'); const table=document.getElementById('csvPreviewTable');
        if(!text){status.textContent='Tempel data untuk melihat preview.';wrap.style.display='none';return;}
        const lines=text.replace(/\r\n?/g,'\n').split('\n').filter(line=>line.trim()!==''); const delimiter=detectCsvDelimiter(lines[0]);
        const headers=parseCsvPreviewLine(lines[0],delimiter).map(normalizeCsvHeader); const required=['pertemuan_ke','sub_cpmk','bahan_kajian']; const missing=required.filter(key=>!headers.includes(key));
        if(missing.length){status.innerHTML=`<span style="color:var(--danger)"><i class="fa-solid fa-circle-exclamation"></i> Header belum dikenali: ${missing.join(', ')}. Pemisah terdeteksi: ${delimiter==='\t'?'TAB':delimiter}</span>`;wrap.style.display='none';return;}
        const rows=lines.slice(1).map(line=>parseCsvPreviewLine(line,delimiter)); const shown=rows.slice(0,16);
        table.innerHTML=`<thead><tr>${headers.map(h=>`<th style="position:sticky;top:0;background:var(--surface-hover);padding:8px;border:1px solid var(--border);text-align:left;white-space:nowrap">${h}</th>`).join('')}</tr></thead><tbody>${shown.map(row=>`<tr>${headers.map((_,i)=>`<td style="padding:7px;border:1px solid var(--border);vertical-align:top">${String(row[i]||'').replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]))}</td>`).join('')}</tr>`).join('')}</tbody>`;
        status.innerHTML=`<span style="color:var(--success)"><i class="fa-solid fa-circle-check"></i> Format dikenali — ${rows.length} baris data, pemisah ${delimiter==='\t'?'TAB/Excel':delimiter}. ${rows.length>16?'Preview menampilkan 16 baris pertama.':''}</span>`; wrap.style.display='block';
    }
    function validateCsvImport() {
        renderCsvPreview();
        if(document.getElementById('csvPreviewWrap').style.display==='none'){ alert('Format CSV belum valid. Periksa header dan preview terlebih dahulu.'); return false; }
        return confirm('Impor data CSV ini? Pertemuan dengan nomor yang sama akan diperbarui.');
    }
    let csvPreviewTimer; document.getElementById('csvPertemuanText')?.addEventListener('input',()=>{clearTimeout(csvPreviewTimer);csvPreviewTimer=setTimeout(renderCsvPreview,180);});
    function openCreateModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Sesi Pertemuan RPS';
        document.getElementById('formPertemuan').action = '<?= $baseUrl ?>/rps/pertemuan/store';
        document.getElementById('field_id').value = '';
        document.getElementById('field_pertemuan_ke').value = '';
        Array.from(document.getElementById('field_sub_cpmk_id').options).forEach(option => option.selected = false);
        document.getElementById('field_bahan_kajian').value = '';
        document.getElementById('field_durasi_detail').value = '';
        document.getElementById('pertemuanModal').style.display = 'flex';
    }

    function openEditModal(p) {
        document.getElementById('modalTitle').innerText = 'Edit Sesi Pertemuan ' + p.pertemuan_ke;
        document.getElementById('formPertemuan').action = '<?= $baseUrl ?>/rps/pertemuan/update';
        document.getElementById('field_id').value = p.id;
        document.getElementById('field_pertemuan_ke').value = p.pertemuan_ke;
        const selectedSubCpmks = String(p.sub_cpmk_ids || p.sub_cpmk_id || '').split(',');
        Array.from(document.getElementById('field_sub_cpmk_id').options).forEach(option => option.selected = selectedSubCpmks.includes(option.value));
        document.getElementById('field_bahan_kajian').value = p.bahan_kajian || '';
        document.getElementById('field_mode_pembelajaran').value = p.mode_pembelajaran || 'ASM dan TM';
        document.getElementById('field_alokasi_waktu').value = p.alokasi_waktu || '2 x 50 menit';
        document.getElementById('field_durasi_detail').value = p.durasi_detail || p.pengalaman_belajar || '';
        document.getElementById('field_pustaka').value = p.pustaka || '';
        document.getElementById('pertemuanModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('pertemuanModal').style.display = 'none';
    }
    </script>
<?php endif; ?>

<!-- ========================================================================
     TAB 6: SISTEM PENILAIAN & EVALUASI (SEKSI 6 & 7)
     ======================================================================== -->
<?php if ($activeTab === 'penilaian'): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px;">
        <style>
        .preset-pill-penilaian {
            transition: all 0.15s ease;
        }
        .preset-pill-penilaian:hover {
            transform: translateY(-1px);
            filter: brightness(1.2);
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        </style>
        <div style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 6px 0; font-size: 1.25rem;">6 & 7. Sistem Penilaian dan Rencana Evaluasi Mahasiswa</h3>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                Pilih jenis penilaian dari master, tentukan bobot dan hubungkan rubrik yang digunakan. Total bobot wajib tepat 100%.
            </p>
        </div>

        <form action="<?= $baseUrl ?>/rps/save-settings" method="POST">
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="penilaian">

            <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <div>
                        <h4 style="margin:0 0 4px;font-size:1.05rem;color:var(--primary);">Komponen & Rubrik Penilaian</h4>
                        <div style="font-size:.82rem;color:var(--text-muted);">Satu jenis hanya dapat dipilih sekali. Rubrik bersifat opsional dan diambil dari Rubrik Penilaian aktif.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" id="addJenisPenilaian"><i class="fa-solid fa-plus"></i> Tambah Jenis</button>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:720px;">
                        <thead><tr style="text-align:left;color:var(--text-muted);font-size:.8rem;border-bottom:1px solid var(--border);"><th style="padding:8px;">Jenis Penilaian</th><th style="padding:8px;width:140px;">Bobot (%)</th><th style="padding:8px;">Rubrik Penilaian</th><th style="padding:8px;width:52px;"></th></tr></thead>
                        <tbody id="jenisPenilaianRows">
                        <?php foreach ($jenisPenilaian as $idx => $item): ?>
                            <tr class="jenis-penilaian-row" style="border-bottom:1px solid var(--border);">
                                <td style="padding:8px;"><select class="form-control jenis-select" name="jenis_penilaian[<?= $idx ?>][kode]" required><option value="">Pilih jenis...</option><?php foreach ($jenisPenilaianMaster as $kode => $nama): ?><option value="<?= htmlspecialchars($kode) ?>" <?= ($item['kode'] ?? '') === $kode ? 'selected' : '' ?>><?= htmlspecialchars($kode . ' - ' . $nama) ?></option><?php endforeach; ?></select></td>
                                <td style="padding:8px;"><input type="number" min="0.01" max="100" step="0.01" class="form-control calc-bobot" name="jenis_penilaian[<?= $idx ?>][bobot]" value="<?= htmlspecialchars($item['bobot'] ?? 0) ?>" required></td>
                                <td style="padding:8px;"><select class="form-control" name="jenis_penilaian[<?= $idx ?>][rubrik_id]"><option value="">Tanpa rubrik</option><?php foreach ($rubrikMaster as $r): ?><option value="<?= (int)$r['id'] ?>" <?= (string)($item['rubrik_id'] ?? '') === (string)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nama'] . ' (' . $r['jenis'] . ')') ?></option><?php endforeach; ?></select></td>
                                <td style="padding:8px;"><button type="button" class="btn btn-sm btn-danger remove-jenis" title="Hapus"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:14px;font-size:.95rem;font-weight:700;">Total Persentase: <span id="totalEvaluasiBadge"><?= $totalBobotSistem ?>%</span></div>
                <?php if (empty($rubrikMaster)): ?><div style="margin-top:10px;color:var(--warning);font-size:.82rem;"><i class="fa-solid fa-triangle-exclamation"></i> Belum ada rubrik aktif. Buat rubrik melalui menu Rubrik Penilaian agar dapat dihubungkan.</div><?php endif; ?>
            </div>

            <!-- RESPONSIVE 2-COLUMN CARDS UNTUK DESKRIPSI & KEBIJAKAN REMEDIAL -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; margin-bottom: 24px;">
                
                <!-- Card 1: Deskripsi Sistem Penilaian (Seksi 6) -->
                <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid var(--border); padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-file-circle-check"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 0.98rem; font-weight: 700; color: var(--text-main);">Deskripsi Sistem Penilaian</h4>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);">(Dicetak pada Bagian 6 Dokumen RPS)</span>
                                </div>
                            </div>
                            <span class="badge" style="background: rgba(99, 102, 241, 0.15); color: var(--primary); font-size: 0.75rem; font-weight: 600;">Seksi 6 RPS</span>
                        </div>
                        
                        <p style="color: var(--text-muted); font-size: 0.82rem; margin: 0 0 12px 0; line-height: 1.45;">
                            Penjelasan pelaksanaan evaluasi capaian pembelajaran mahasiswa berbasis CPMK dan penggunaan instrumen rubrik penilaian.
                        </p>

                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                            <button type="button" class="preset-pill-penilaian" onclick="setDeskripsiPreset('standar')" style="background: rgba(99, 102, 241, 0.12); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 6px; padding: 4px 10px; font-size: 0.76rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Template Standar OBE
                            </button>
                            <button type="button" class="preset-pill-penilaian" onclick="setDeskripsiPreset('proyek')" style="background: rgba(16, 185, 129, 0.12); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 6px; padding: 4px 10px; font-size: 0.76rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-users-gear"></i> Case Method & Project
                            </button>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <textarea name="sistem_penilaian_desc" id="textarea_penilaian_desc" rows="4" class="form-control" style="width: 100%; min-height: 125px; resize: vertical; font-size: 0.9rem; line-height: 1.6; padding: 12px 14px; border-radius: var(--radius-sm); box-sizing: border-box; background: var(--surface); color: var(--text-main); border: 1px solid var(--border);" placeholder="Tuliskan uraian sistem penilaian..." oninput="autoResizePenilaian(this)"><?= htmlspecialchars($rps['sistem_penilaian_desc'] ?? 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Kebijakan Sistem Evaluasi & Remedial (Seksi 7) -->
                <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid var(--border); padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                    <i class="fa-solid fa-scale-balanced"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 0.98rem; font-weight: 700; color: var(--text-main);">Kebijakan Sistem Evaluasi & Remedial</h4>
                                    <span style="font-size: 0.76rem; color: var(--text-muted);">(Dicetak pada Bagian 7 Dokumen RPS)</span>
                                </div>
                            </div>
                            <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 0.75rem; font-weight: 600;">Seksi 7 RPS</span>
                        </div>
                        
                        <p style="color: var(--text-muted); font-size: 0.82rem; margin: 0 0 12px 0; line-height: 1.45;">
                            Ketentuan batas ambang kelulusan kompetensi minimal CPMK (standar nilai 60/C) serta prosedur asesmen remedial bagi mahasiswa.
                        </p>

                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                            <button type="button" class="preset-pill-penilaian" onclick="setEvaluasiPreset('standar')" style="background: rgba(59, 130, 246, 0.12); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 6px; padding: 4px 10px; font-size: 0.76rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Standar Nilai 60/C (Remedial 2x)
                            </button>
                            <button type="button" class="preset-pill-penilaian" onclick="setEvaluasiPreset('ketat')" style="background: rgba(245, 158, 11, 0.12); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 6px; padding: 4px 10px; font-size: 0.76rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa-solid fa-shield-halved"></i> Ketuntasan Sub-CPMK
                            </button>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <textarea name="sistem_evaluasi_desc" id="textarea_evaluasi_desc" rows="4" class="form-control" style="width: 100%; min-height: 125px; resize: vertical; font-size: 0.9rem; line-height: 1.6; padding: 12px 14px; border-radius: var(--radius-sm); box-sizing: border-box; background: var(--surface); color: var(--text-main); border: 1px solid var(--border);" placeholder="Tuliskan kebijakan evaluasi & remedial..." oninput="autoResizePenilaian(this)"><?= htmlspecialchars($rps['sistem_evaluasi_desc'] ?? 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.') ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 24px;">
                <h4 style="margin:0 0 6px; color:var(--primary);">Indikator Penilaian & Bank Soal per Sub-CPMK</h4>
                <p style="margin:0 0 16px; color:var(--text-muted); font-size:.84rem;">Kode CPL, Sub-CPMK, dan indikator tersinkron dari Tab 2. Pilihan jenis soal otomatis mengikuti komponen penilaian yang aktif di atas dan dicetak pada kolom yang sama di dokumen RPS.</p>
                <?php foreach ($allSubCpmks as $evalIndex => $sub):
                    $eval = $rencanaEvaluasi[$sub['kode']] ?? [];
                    $questions = is_array($eval['soal'] ?? null) ? $eval['soal'] : [];
                    $targetId = 'soal_' . $evalIndex;
                ?>
                <div style="background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:14px;">
                    <div style="display:grid; grid-template-columns:140px 160px 100px 1fr; gap:12px; align-items:start;">
                        <div><small style="color:var(--text-muted);">CPL Terkait</small><div style="font-weight:700;"><?= htmlspecialchars(implode(', ', $sub['cpl_kodes'] ?? []) ?: '-') ?></div></div>
                        <div><small style="color:var(--text-muted);">Kode Sub-CPMK</small><div style="font-weight:700;"><?= htmlspecialchars($sub['kode']) ?></div></div>
                        <div><label style="font-size:.75rem;color:var(--text-muted);">Bobot (%)</label><input type="number" min="0" max="100" step="0.01" class="form-control" name="rencana_evaluasi[<?= htmlspecialchars($sub['kode']) ?>][bobot]" value="<?= htmlspecialchars($eval['bobot'] ?? $sub['bobot']) ?>"></div>
                        <div><small style="color:var(--text-muted);">Rumusan Indikator</small>
                            <?php if (!empty($sub['indikators'])): foreach ($sub['indikators'] as $ind): ?><div style="font-size:.84rem; margin-top:3px;">• <?= htmlspecialchars($ind['indikator']) ?></div><?php endforeach; else: ?><div style="font-size:.84rem;"><?= htmlspecialchars($sub['rumusan']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div id="<?= $targetId ?>" style="margin-top:12px;">
                        <?php foreach ($questions as $qIndex => $question): ?>
                        <div class="question-row" style="display:grid; grid-template-columns:190px 1fr 38px; gap:8px; margin-top:8px;">
                            <select class="form-control question-type-select" name="rencana_evaluasi[<?= htmlspecialchars($sub['kode']) ?>][soal][<?= $qIndex ?>][jenis]" required data-current="<?= htmlspecialchars($question['jenis'] ?? '') ?>"><option value="">Pilih jenis penilaian</option><?php foreach ($jenisPenilaian as $komponen): $kodeJenis=$komponen['kode']; $labelJenis=$kodeJenis.' - '.($jenisPenilaianMaster[$kodeJenis] ?? $kodeJenis); ?><option value="<?= htmlspecialchars($kodeJenis) ?>" <?= ($question['jenis'] ?? '') === $kodeJenis ? 'selected' : '' ?>><?= htmlspecialchars($labelJenis) ?></option><?php endforeach; ?></select>
                            <textarea class="form-control" rows="2" name="rencana_evaluasi[<?= htmlspecialchars($sub['kode']) ?>][soal][<?= $qIndex ?>][pertanyaan]" placeholder="Tuliskan soal atau instrumen penilaian"><?= htmlspecialchars($question['pertanyaan'] ?? '') ?></textarea>
                            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.question-row').remove()" title="Hapus soal"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" style="margin-top:10px;" onclick='addEvaluationQuestion(<?= json_encode($targetId) ?>, <?= json_encode($sub['kode']) ?>)'><i class="fa-solid fa-plus"></i> Tambah Soal</button>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 18px; flex-wrap: wrap; gap: 12px;">
                <span style="font-size: 0.84rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Seluruh deskripsi di atas akan otomatis tercetak rapi pada Dokumen RPS Resmi.
                </span>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Sistem Penilaian & Evaluasi
                </button>
            </div>
        </form>
    </div>

    <script>
    function currentAssessmentChoices() {
        return Array.from(document.querySelectorAll('#jenisPenilaianRows .jenis-select')).map(select => {
            const code = select.value;
            return code ? {code, label: `${code} - ${(typeof jenisMaster !== 'undefined' && jenisMaster[code]) || code}`} : null;
        }).filter(Boolean);
    }

    function refreshQuestionTypeOptions() {
        const choices = currentAssessmentChoices();
        document.querySelectorAll('.question-type-select').forEach(select => {
            const selected = select.value || select.dataset.current || '';
            select.innerHTML = '<option value="">Pilih jenis penilaian</option>' + choices.map(item => `<option value="${item.code}">${item.label}</option>`).join('');
            if (choices.some(item => item.code === selected)) select.value = selected;
            select.dataset.current = select.value;
        });
    }

    function addEvaluationQuestion(targetId, subCode) {
        const target = document.getElementById(targetId);
        const index = Date.now().toString() + Math.floor(Math.random() * 1000);
        const row = document.createElement('div');
        row.className = 'question-row';
        row.style.cssText = 'display:grid;grid-template-columns:190px 1fr 38px;gap:8px;margin-top:8px;';
        const type = document.createElement('select');
        type.className = 'form-control question-type-select'; type.name = `rencana_evaluasi[${subCode}][soal][${index}][jenis]`; type.required = true;
        type.innerHTML = '<option value="">Pilih jenis penilaian</option>' + currentAssessmentChoices().map(item => `<option value="${item.code}">${item.label}</option>`).join('');
        const question = document.createElement('textarea');
        question.className = 'form-control'; question.rows = 2; question.name = `rencana_evaluasi[${subCode}][soal][${index}][pertanyaan]`; question.placeholder = 'Tuliskan soal atau instrumen penilaian';
        const remove = document.createElement('button');
        remove.type = 'button'; remove.className = 'btn btn-sm btn-danger'; remove.title = 'Hapus soal'; remove.innerHTML = '<i class="fa-solid fa-trash"></i>'; remove.onclick = () => row.remove();
        row.append(type, question, remove); target.appendChild(row); type.focus();
    }

    function autoResizePenilaian(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = Math.max(125, el.scrollHeight + 4) + 'px';
    }

    function setDeskripsiPreset(type) {
        const ta = document.getElementById('textarea_penilaian_desc');
        if (!ta) return;
        if (type === 'standar') {
            ta.value = 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik dan indikator ketercapaian pembelajaran yang telah ditetapkan dalam RPS.';
        } else if (type === 'proyek') {
            ta.value = 'Sistem penilaian menggunakan kombinasi evaluasi proses (keaktifan dan tugas terstruktur) serta asesmen otentik berupa studi kasus (Case Method) dan Team-Based Project dengan panduan rubrik penilaian analitik dan holistik.';
        }
        autoResizePenilaian(ta);
        ta.focus();
    }

    function setEvaluasiPreset(type) {
        const ta = document.getElementById('textarea_evaluasi_desc');
        if (!ta) return;
        if (type === 'standar') {
            ta.value = 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.';
        } else if (type === 'ketat') {
            ta.value = 'Setiap mahasiswa wajib mencapai ambang batas kompetensi minimal nilai 60/C pada setiap Sub-CPMK. Mahasiswa yang belum memenuhi kriteria ketuntasan diberikan kesempatan asesmen remedial maksimal 2 (dua) kali sebelum batas akhir penginputan nilai semester.';
        }
        autoResizePenilaian(ta);
        ta.focus();
    }

    // Auto-resize on load
    document.addEventListener('DOMContentLoaded', () => {
        const pDesc = document.getElementById('textarea_penilaian_desc');
        const eDesc = document.getElementById('textarea_evaluasi_desc');
        if (pDesc) autoResizePenilaian(pDesc);
        if (eDesc) autoResizePenilaian(eDesc);
    });
    // In case script runs after DOMContentLoaded
    (function() {
        const pDesc = document.getElementById('textarea_penilaian_desc');
        const eDesc = document.getElementById('textarea_evaluasi_desc');
        if (pDesc) autoResizePenilaian(pDesc);
        if (eDesc) autoResizePenilaian(eDesc);
    })();

    const jenisMaster = <?= json_encode($jenisPenilaianMaster, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const rubrikMasterData = <?= json_encode($rubrikMaster, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const penilaianForm = document.querySelector('input[name="tab"][value="penilaian"]')?.closest('form');
    const rowsContainer = document.getElementById('jenisPenilaianRows');

    function escapeHtml(value) {
        return String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    }
    function updateTotalEvaluasi() {
        let sum = 0;
        document.querySelectorAll('#jenisPenilaianRows .calc-bobot').forEach(inp => sum += parseFloat(inp.value) || 0);
        sum = Math.round(sum * 100) / 100;
        const badge = document.getElementById('totalEvaluasiBadge');
        if (badge) {
            const valid = Math.abs(sum - 100) < 0.001;
            badge.style.color = valid ? 'var(--success)' : 'var(--danger)';
            badge.textContent = sum + '% ' + (valid ? '✓ (Valid)' : '⚠ (Harus 100%)');
        }
        return sum;
    }
    function refreshJenisOptions() {
        const selected = Array.from(document.querySelectorAll('.jenis-select')).map(el => el.value).filter(Boolean);
        document.querySelectorAll('.jenis-select').forEach(select => Array.from(select.options).forEach(option => {
            option.disabled = option.value !== '' && option.value !== select.value && selected.includes(option.value);
        }));
    }
    function bindJenisRow(row) {
        row.querySelector('.calc-bobot')?.addEventListener('input', updateTotalEvaluasi);
        row.querySelector('.jenis-select')?.addEventListener('change', () => { refreshJenisOptions(); refreshQuestionTypeOptions(); });
        row.querySelector('.remove-jenis')?.addEventListener('click', () => { row.remove(); refreshJenisOptions(); refreshQuestionTypeOptions(); updateTotalEvaluasi(); });
    }
    document.querySelectorAll('.jenis-penilaian-row').forEach(bindJenisRow);
    document.getElementById('addJenisPenilaian')?.addEventListener('click', () => {
        const index = Date.now();
        const jenisOptions = Object.entries(jenisMaster).map(([kode, nama]) => `<option value="${escapeHtml(kode)}">${escapeHtml(kode + ' - ' + nama)}</option>`).join('');
        const rubrikOptions = rubrikMasterData.map(r => `<option value="${Number(r.id)}">${escapeHtml(r.nama + ' (' + r.jenis + ')')}</option>`).join('');
        const row = document.createElement('tr');
        row.className = 'jenis-penilaian-row'; row.style.borderBottom = '1px solid var(--border)';
        row.innerHTML = `<td style="padding:8px"><select class="form-control jenis-select" name="jenis_penilaian[${index}][kode]" required><option value="">Pilih jenis...</option>${jenisOptions}</select></td><td style="padding:8px"><input type="number" min="0.01" max="100" step="0.01" class="form-control calc-bobot" name="jenis_penilaian[${index}][bobot]" required></td><td style="padding:8px"><select class="form-control" name="jenis_penilaian[${index}][rubrik_id]"><option value="">Tanpa rubrik</option>${rubrikOptions}</select></td><td style="padding:8px"><button type="button" class="btn btn-sm btn-danger remove-jenis" title="Hapus"><i class="fa-solid fa-trash"></i></button></td>`;
        rowsContainer.appendChild(row); bindJenisRow(row); refreshJenisOptions(); refreshQuestionTypeOptions(); row.querySelector('select').focus();
    });
    penilaianForm?.addEventListener('submit', event => {
        if (document.querySelectorAll('.jenis-penilaian-row').length === 0 || Math.abs(updateTotalEvaluasi() - 100) >= 0.001) {
            event.preventDefault(); alert('Total bobot seluruh jenis penilaian wajib tepat 100%.');
        }
    });
    refreshJenisOptions(); refreshQuestionTypeOptions(); updateTotalEvaluasi();

    document.querySelectorAll('.calc-bobot').forEach(input => {
        input.addEventListener('input', () => {
            let sum = 0;
            document.querySelectorAll('.calc-bobot').forEach(inp => {
                sum += parseFloat(inp.value) || 0;
            });
            const badge = document.getElementById('totalEvaluasiBadge');
            badge.innerText = sum + '%';
            if (sum === 100) {
                badge.style.color = 'var(--success)';
                badge.innerText += ' ✓ (Sempurna)';
            } else {
                badge.style.color = 'var(--danger)';
                badge.innerText += ' ⚠ (Harus 100%)';
            }
        });
    });
    </script>
<?php endif; ?>

<!-- ========================================================================
     TAB 7: RUBRIK PENILAIAN (SEKSI 8)
     ======================================================================== -->
<?php if ($activeTab === 'rubrik'): ?>
    <?php
    $rubrikUsage = [];
    foreach ($jenisPenilaian as $komponen) {
        $rubrikId = (int)($komponen['rubrik_id'] ?? 0);
        if (!$rubrikId) continue;
        $kodeJenis = $komponen['kode'] ?? '';
        $rubrikUsage[$rubrikId][] = [
            'jenis' => $jenisPenilaianMaster[$kodeJenis] ?? $kodeJenis,
            'bobot' => (float)($komponen['bobot'] ?? 0)
        ];
    }
    ?>
    <style>
    .rubrik-master-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:14px;align-items:start}
    .rubrik-master-card{border-radius:12px;padding:18px;display:flex;flex-direction:column;gap:12px;min-width:0;transition:.2s ease}
    .rubrik-master-card:has(.rubrik-mk-editor[open]){grid-column:1/-1}
    .rubrik-mk-editor{border-top:1px solid var(--border);padding-top:12px}
    .rubrik-mk-editor summary{cursor:pointer;font-weight:750;color:#38bdf8;list-style:none;display:flex;align-items:center;gap:7px;padding:3px 0}
    .rubrik-mk-editor summary::-webkit-details-marker{display:none}
    .rubrik-mk-editor summary:before{content:'\25B6';font-size:.68rem;transition:transform .15s ease}
    .rubrik-mk-editor[open] summary:before{transform:rotate(90deg)}
    .rubrik-mk-panel{margin-top:14px;padding:16px;border:1px solid var(--border);border-radius:10px;background:rgba(2,6,23,.28)}
    .rubrik-mk-field-label{display:block;font-size:.76rem;font-weight:700;color:var(--text-muted);margin-bottom:6px}
    .rubrik-mk-input{width:100%;box-sizing:border-box;border:1px solid var(--border);border-radius:7px;background:var(--surface);color:var(--text-main);padding:9px 11px;font:inherit;line-height:1.35;outline:none;transition:border-color .15s,box-shadow .15s}
    .rubrik-mk-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(99,102,241,.14)}
    textarea.rubrik-mk-input{resize:vertical;min-height:72px}
    .rubrik-mk-table-wrap{overflow:auto;margin-top:14px;border:1px solid var(--border);border-radius:9px;scrollbar-gutter:stable}
    .rubrik-mk-table{width:100%;min-width:900px;border-collapse:separate;border-spacing:0;font-size:.79rem}
    .rubrik-mk-table th{padding:10px;background:var(--surface-hover);color:var(--text-muted);text-align:left;vertical-align:top;border-bottom:1px solid var(--border)}
    .rubrik-mk-table td{padding:9px;vertical-align:top;border-bottom:1px solid var(--border);background:var(--surface)}
    .rubrik-mk-table tr:last-child td{border-bottom:0}
    .rubrik-mk-table .criteria-col{width:230px;min-width:230px;position:sticky;left:0;z-index:2}
    .rubrik-mk-table .weight-col{width:90px;min-width:90px;text-align:center}
    .rubrik-level-head{display:grid;grid-template-columns:minmax(125px,1fr) 78px;gap:6px;min-width:215px}
    .rubrik-level-head small{grid-column:1/-1;font-size:.69rem;color:var(--text-muted)}
    .rubrik-mk-help{display:flex;align-items:center;gap:6px;font-size:.76rem;color:var(--text-muted);margin-top:9px}
    @media(max-width:700px){.rubrik-master-grid{grid-template-columns:1fr}.rubrik-mk-panel{padding:12px}.rubrik-mk-table{min-width:780px}}
    </style>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <h3 style="margin:0 0 6px;font-size:1.25rem;"><i class="fa-solid fa-list-check" style="color:var(--primary);margin-right:8px;"></i>8. Rubrik Penilaian RPS</h3>
                <p style="color:var(--text-muted);margin:0;font-size:.88rem;max-width:760px;">Rubrik dari master disalin saat dipilih. Perubahan di halaman ini hanya berlaku untuk <?= htmlspecialchars($mk['kode']) ?> dan tidak mengubah template global.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>&tab=penilaian" class="btn btn-secondary"><i class="fa-solid fa-link"></i> Atur Hubungan & Bobot</a>
                <a href="<?= $baseUrl ?>/rubrik" class="btn btn-primary"><i class="fa-solid fa-table-cells"></i> Kelola Master Rubrik</a>
            </div>
        </div>

        <form action="<?= $baseUrl ?>/rps/save-settings" method="POST" id="rubrikMkForm">
        <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>"><input type="hidden" name="tab" value="rubrik">
        <div class="rubrik-master-grid">
            <?php foreach ($rubrikMaster as $item):
                $usages = $rubrikUsage[(int)$item['id']] ?? [];
                $usedWeight = array_sum(array_column($usages, 'bobot'));
                $snapshot = $rubrikMk[(string)(int)$item['id']] ?? null;
            ?>
            <div class="rubrik-master-card" style="border:1px solid <?= $usages ? 'rgba(16,185,129,.5)' : 'var(--border)' ?>;background:<?= $usages ? 'rgba(16,185,129,.05)' : 'var(--surface-hover)' ?>;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                    <div>
                        <div style="color:var(--primary);font-weight:800;font-size:.76rem;letter-spacing:.04em;"><?= htmlspecialchars($item['kode_jenis']) ?></div>
                        <div style="font-weight:750;color:var(--text-main);margin-top:3px;"><?= htmlspecialchars($item['nama']) ?></div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-top:5px;line-height:1.45;"><?= htmlspecialchars($item['karakteristik']) ?><br>Contoh: <?= htmlspecialchars($item['contoh_penggunaan']) ?></div>
                    </div>
                    <span style="white-space:nowrap;padding:4px 8px;border-radius:999px;font-size:.72rem;font-weight:700;background:<?= $usages ? 'rgba(16,185,129,.15)' : 'rgba(148,163,184,.12)' ?>;color:<?= $usages ? 'var(--success)' : 'var(--text-muted)' ?>;"><?= $usages ? 'DIGUNAKAN' : 'BELUM DIPAKAI' ?></span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;font-size:.78rem;">
                    <div style="padding:8px;background:var(--surface);border-radius:6px;"><span style="color:var(--text-muted);">Kriteria</span><br><strong><?= (int)$item['total_kriteria'] ?></strong></div>
                    <div style="padding:8px;background:var(--surface);border-radius:6px;"><span style="color:var(--text-muted);">Level</span><br><strong><?= (int)$item['total_level'] ?></strong></div>
                    <div style="padding:8px;background:var(--surface);border-radius:6px;"><span style="color:var(--text-muted);">Bobot Rubrik</span><br><strong style="color:var(--success);"><?= (float)$item['total_bobot'] ?>%</strong></div>
                </div>
                <?php if ($usages): ?>
                <div style="font-size:.8rem;padding:9px 10px;border-radius:6px;background:rgba(16,185,129,.1);color:var(--text-main);"><strong>Terhubung:</strong> <?php $labels=[]; foreach($usages as $usage) $labels[]=$usage['jenis'].' ('.$usage['bobot'].'%)'; echo htmlspecialchars(implode(', ', $labels)); ?> <strong>— total <?= $usedWeight ?>%</strong></div>
                <?php endif; ?>
                <div style="display:flex;gap:8px;margin-top:auto;">
                    <a href="<?= $baseUrl ?>/rubrik/detail?id=<?= (int)$item['id'] ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fa-solid fa-eye"></i> Lihat Template Master</a>
                </div>
                <?php if ($usages && $snapshot): ?>
                <details class="rubrik-mk-editor">
                    <summary><i class="fa-solid fa-pen-to-square"></i> Edit salinan rubrik untuk MK ini</summary>
                    <div class="rubrik-mk-panel">
                        <label class="rubrik-mk-field-label">Nama rubrik pada mata kuliah</label>
                        <input class="rubrik-mk-input" name="rubrik_mk[<?= (int)$item['id'] ?>][nama]" value="<?= htmlspecialchars($snapshot['nama'] ?? $item['nama']) ?>" required>
                        <div class="rubrik-mk-table-wrap"><table class="rubrik-mk-table">
                            <thead><tr><th class="criteria-col">Indikator / Kriteria</th><th class="weight-col">Bobot (%)</th><?php foreach (($snapshot['levels'] ?? []) as $lIndex => $level): ?><th><div class="rubrik-level-head"><input class="rubrik-mk-input" name="rubrik_mk[<?= (int)$item['id'] ?>][levels][<?= $lIndex ?>][nama]" value="<?= htmlspecialchars($level['nama']) ?>" required aria-label="Nama level"><input type="number" step="0.01" class="rubrik-mk-input" name="rubrik_mk[<?= (int)$item['id'] ?>][levels][<?= $lIndex ?>][skor]" value="<?= (float)$level['skor'] ?>" required aria-label="Skor level"><small>Nama level dan skor</small></div></th><?php endforeach; ?></tr></thead>
                            <tbody><?php foreach (($snapshot['criteria'] ?? []) as $cIndex => $criterion): ?><tr>
                                <td class="criteria-col"><textarea class="rubrik-mk-input" rows="3" name="rubrik_mk[<?= (int)$item['id'] ?>][criteria][<?= $cIndex ?>][nama]" required><?= htmlspecialchars($criterion['nama']) ?></textarea></td>
                                <td class="weight-col"><input type="number" min="0" max="100" step="0.01" class="rubrik-mk-input rubrik-mk-bobot" name="rubrik_mk[<?= (int)$item['id'] ?>][criteria][<?= $cIndex ?>][bobot]" value="<?= (float)$criterion['bobot'] ?>" required></td>
                                <?php foreach (($snapshot['levels'] ?? []) as $lIndex => $level): ?><td><textarea class="rubrik-mk-input" rows="4" name="rubrik_mk[<?= (int)$item['id'] ?>][criteria][<?= $cIndex ?>][descriptors][<?= $lIndex ?>]" placeholder="Deskriptor capaian pada level <?= htmlspecialchars($level['nama']) ?>"><?= htmlspecialchars($criterion['descriptors'][$lIndex] ?? '') ?></textarea></td><?php endforeach; ?>
                            </tr><?php endforeach; ?></tbody>
                        </table></div>
                        <div class="rubrik-mk-help"><i class="fa-solid fa-circle-info"></i> Total bobot kriteria: <strong class="rubrik-mk-total">0%</strong> — wajib 100%.</div>
                    </div>
                </details>
                <?php elseif ($usages): ?>
                <div style="font-size:.78rem;color:var(--warning);">Simpan kembali tab Sistem Penilaian untuk membuat salinan rubrik khusus mata kuliah ini.</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($rubrikMk)): ?><div style="display:flex;justify-content:flex-end;margin-top:18px;"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Rubrik Khusus Mata Kuliah</button></div><?php endif; ?>
        </form>
        <script>
        function updateRubrikMkTotals() {
            document.querySelectorAll('#rubrikMkForm .rubrik-mk-editor').forEach(editor => {
                const total = Array.from(editor.querySelectorAll('.rubrik-mk-bobot')).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                const badge = editor.querySelector('.rubrik-mk-total');
                if (badge) {
                    badge.textContent = (Math.round(total * 100) / 100) + '%';
                    badge.style.color = Math.abs(total - 100) < 0.001 ? 'var(--success)' : 'var(--danger)';
                }
            });
        }
        document.querySelectorAll('#rubrikMkForm .rubrik-mk-bobot').forEach(input => input.addEventListener('input', updateRubrikMkTotals));
        updateRubrikMkTotals();
        document.getElementById('rubrikMkForm')?.addEventListener('submit', event => {
            let valid = true;
            document.querySelectorAll('#rubrikMkForm details').forEach(editor => {
                const total = Array.from(editor.querySelectorAll('.rubrik-mk-bobot')).reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                if (Math.abs(total - 100) >= 0.001) valid = false;
            });
            if (!valid) {
                event.preventDefault();
                alert('Total bobot kriteria pada setiap rubrik wajib tepat 100%.');
            }
        });
        </script>
    </div>
<?php endif; ?>

<?php if (false && $activeTab === 'rubrik'): ?>
    <?php
    // Ambil template default untuk referensi reset
    $defaultRubrik = RpsModel::getDefaultRubrikTemplates();
    
    // Normalisasi judul jika sebelumnya tersimpan status dinonaktifkan
    $persepsiJudul = (!empty($rubrik['skala_persepsi']['judul']) && strpos($rubrik['skala_persepsi']['judul'], 'Dinonaktifkan') === false)
        ? $rubrik['skala_persepsi']['judul'] 
        : 'Rubrik Penilaian Skala Persepsi (Afektif & Kerjasama Tim)';
    ?>

    <style>
    /* ==========================================================================
       CUSTOM STYLES FOR RUBRIK BUILDER (TAB 8) - DARK THEME HARMONIZED
       ========================================================================== */
    .rubrik-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--surface);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-bottom: 16px;
    }

    .rubrik-edit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.83rem;
        background: var(--surface);
        table-layout: fixed;
    }

    .rubrik-edit-table th {
        background: var(--surface-hover);
        color: var(--text-main);
        font-weight: 700;
        padding: 12px 10px;
        border: 1px solid var(--border);
        vertical-align: middle;
        text-align: left;
    }

    .rubrik-edit-table td {
        padding: 8px 10px;
        border: 1px solid var(--border);
        vertical-align: top;
        background: var(--surface);
        word-break: break-word;
    }

    .rubrik-edit-table tr:hover td {
        background: var(--surface-hover);
    }

    /* Modern Dark Theme Responsive Textarea Styling */
    .rubrik-textarea {
        width: 100%;
        box-sizing: border-box;
        font-family: inherit;
        font-size: 0.83rem;
        line-height: 1.48;
        padding: 9px 11px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--surface);
        color: var(--text-main);
        resize: vertical;
        overflow-y: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }

    .rubrik-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        background: var(--surface-hover);
    }

    .rubrik-textarea::placeholder {
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    /* iOS-Style Modern Animated Toggle Switch */
    .rubrik-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 25px;
        flex-shrink: 0;
        margin: 0;
        cursor: pointer;
    }

    .rubrik-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .rubrik-switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--border);
        transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 25px;
    }

    .rubrik-switch-slider:before {
        position: absolute;
        content: "";
        height: 19px;
        width: 19px;
        left: 3px;
        bottom: 3px;
        background-color: var(--text-main);
        transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }

    .rubrik-switch input:checked + .rubrik-switch-slider {
        background-color: var(--success);
    }

    .rubrik-switch input:checked + .rubrik-switch-slider:before {
        transform: translateX(21px);
    }

    /* Polished Modern Buttons */
    .btn-soft-add {
        background: rgba(99, 102, 241, 0.12);
        color: #a5b4fc;
        border: 1.5px dashed var(--primary);
        padding: 9px 18px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-soft-add:hover {
        background: var(--primary);
        color: #ffffff;
        border-style: solid;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        transform: translateY(-1px);
    }

    .btn-soft-delete {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.12);
        color: #f87171;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.88rem;
    }

    .btn-soft-delete:hover {
        background: var(--danger);
        color: #ffffff;
        border-color: var(--danger);
        box-shadow: 0 3px 10px rgba(239, 68, 68, 0.4);
        transform: scale(1.06);
    }

    .btn-soft-reset {
        background: rgba(245, 158, 11, 0.12);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.3);
        padding: 7px 14px;
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-soft-reset:hover {
        background: var(--warning);
        color: #0f111a;
        border-color: var(--warning);
        box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);
    }

    .rubrik-card-active {
        border-color: var(--primary) !important;
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.15);
    }

    .rubrik-card-inactive {
        opacity: 0.6;
        border-color: var(--border) !important;
        background: rgba(26, 29, 45, 0.5) !important;
    }

    .level-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .btn-jump-editor {
        font-size: 0.76rem;
        font-weight: 600;
        color: #a5b4fc;
        background: rgba(99, 102, 241, 0.12);
        border: 1px solid rgba(99, 102, 241, 0.25);
        padding: 4px 10px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }
    .btn-jump-editor:hover {
        background: var(--primary);
        color: #ffffff;
    }
    </style>

    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Header Info Tab -->
        <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(16, 185, 129, 0.08) 100%); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 22px 26px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                    <i class="fa-solid fa-list-check"></i> Bagian 8 Dokumen RPS (Instrumen Evaluasi OBE)
                </div>
                <h2 style="margin: 0 0 6px 0; font-size: 1.35rem; font-weight: 700; color: var(--text-main);">Rubrik Penilaian Pembelajaran (Kustomisasi per MK)</h2>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem; max-width: 840px; line-height: 1.5;">
                    Rubrik evaluasi OBE bersifat fleksibel per mata kuliah. Dosen dapat memilih rubrik mana yang <strong>aktif dipakai</strong> pada dokumen RPS, serta menyesuaikan judul, kriteria, bobot, dan deskriptor capaian tanpa mengubah template master.
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <button type="submit" form="formRubrikRps" class="btn btn-primary" style="padding: 10px 22px; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Rubrik RPS
                </button>
                <a href="<?= $baseUrl ?>/rps/document?mk_id=<?= $mk['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 10px 16px; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-file-pdf" style="color: #ef4444;"></i> Preview Dokumen PDF
                </a>
                <a href="<?= $baseUrl ?>/rubrik" target="_blank" class="btn btn-secondary" style="padding: 10px 14px; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-cubes"></i> Master Rubrik
                </a>
            </div>
        </div>

        <!-- Ringkasan Status Rubrik (Pilihan Aktif/Nonaktif dengan Toggle Switch Modern) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
            <!-- Card 1: Holistik -->
            <div class="rubrik-summary-card <?= !empty($rubrik['holistik']['aktif']) ? 'rubrik-card-active' : 'rubrik-card-inactive' ?>" id="summary_card_holistik" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: all 0.25s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 9px; background: rgba(99, 102, 241, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">8.1 Rubrik Holistik</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Tugas Kasus & Kedisiplinan</div>
                        </div>
                    </div>
                    <span id="badge_summary_holistik" class="badge" style="background: <?= !empty($rubrik['holistik']['aktif']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= !empty($rubrik['holistik']['aktif']) ? 'var(--success)' : 'var(--text-muted)' ?>; font-weight: 700; font-size: 0.75rem;">
                        <i class="fa-solid <?= !empty($rubrik['holistik']['aktif']) ? 'fa-check-circle' : 'fa-circle-xmark' ?>"></i> <?= !empty($rubrik['holistik']['aktif']) ? 'AKTIF' : 'NONAKTIF' ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="rubrik-switch">
                            <input type="checkbox" onchange="syncToggle('holistik', this.checked)" <?= !empty($rubrik['holistik']['aktif']) ? 'checked' : '' ?>>
                            <span class="rubrik-switch-slider"></span>
                        </label>
                        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-main);">Pakai di RPS</span>
                    </div>
                    <a href="#section_holistik" class="btn-jump-editor">
                        Edit <i class="fa-solid fa-arrow-down"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Analitik -->
            <div class="rubrik-summary-card <?= !empty($rubrik['analitik']['aktif']) ? 'rubrik-card-active' : 'rubrik-card-inactive' ?>" id="summary_card_analitik" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: all 0.25s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 9px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-table-cells"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">8.2 Rubrik Analitik</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Multi-Kriteria Berbobot</div>
                        </div>
                    </div>
                    <span id="badge_summary_analitik" class="badge" style="background: <?= !empty($rubrik['analitik']['aktif']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= !empty($rubrik['analitik']['aktif']) ? 'var(--success)' : 'var(--text-muted)' ?>; font-weight: 700; font-size: 0.75rem;">
                        <i class="fa-solid <?= !empty($rubrik['analitik']['aktif']) ? 'fa-check-circle' : 'fa-circle-xmark' ?>"></i> <?= !empty($rubrik['analitik']['aktif']) ? 'AKTIF' : 'NONAKTIF' ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="rubrik-switch">
                            <input type="checkbox" onchange="syncToggle('analitik', this.checked)" <?= !empty($rubrik['analitik']['aktif']) ? 'checked' : '' ?>>
                            <span class="rubrik-switch-slider"></span>
                        </label>
                        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-main);">Pakai di RPS</span>
                    </div>
                    <a href="#section_analitik" class="btn-jump-editor">
                        Edit <i class="fa-solid fa-arrow-down"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Skala Persepsi -->
            <div class="rubrik-summary-card <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'rubrik-card-active' : 'rubrik-card-inactive' ?>" id="summary_card_skala_persepsi" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: all 0.25s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 9px; background: rgba(168, 85, 247, 0.15); color: #a855f7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">8.3 Skala Persepsi</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Afektif & Kerjasama Tim</div>
                        </div>
                    </div>
                    <span id="badge_summary_skala_persepsi" class="badge" style="background: <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'var(--success)' : 'var(--text-muted)' ?>; font-weight: 700; font-size: 0.75rem;">
                        <i class="fa-solid <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'fa-check-circle' : 'fa-circle-xmark' ?>"></i> <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'AKTIF' : 'NONAKTIF' ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="rubrik-switch">
                            <input type="checkbox" onchange="syncToggle('skala_persepsi', this.checked)" <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'checked' : '' ?>>
                            <span class="rubrik-switch-slider"></span>
                        </label>
                        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-main);">Pakai di RPS</span>
                    </div>
                    <a href="#section_persepsi" class="btn-jump-editor">
                        Edit <i class="fa-solid fa-arrow-down"></i>
                    </a>
                </div>
            </div>

            <!-- Card 4: Portofolio -->
            <div class="rubrik-summary-card <?= !empty($rubrik['portofolio']['aktif']) ? 'rubrik-card-active' : 'rubrik-card-inactive' ?>" id="summary_card_portofolio" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; transition: all 0.25s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 9px; background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">8.4 Portofolio</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Artefak Proyek Semester</div>
                        </div>
                    </div>
                    <span id="badge_summary_portofolio" class="badge" style="background: <?= !empty($rubrik['portofolio']['aktif']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)' ?>; color: <?= !empty($rubrik['portofolio']['aktif']) ? 'var(--success)' : 'var(--text-muted)' ?>; font-weight: 700; font-size: 0.75rem;">
                        <i class="fa-solid <?= !empty($rubrik['portofolio']['aktif']) ? 'fa-check-circle' : 'fa-circle-xmark' ?>"></i> <?= !empty($rubrik['portofolio']['aktif']) ? 'AKTIF' : 'NONAKTIF' ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label class="rubrik-switch">
                            <input type="checkbox" onchange="syncToggle('portofolio', this.checked)" <?= !empty($rubrik['portofolio']['aktif']) ? 'checked' : '' ?>>
                            <span class="rubrik-switch-slider"></span>
                        </label>
                        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-main);">Pakai di RPS</span>
                    </div>
                    <a href="#section_portofolio" class="btn-jump-editor">
                        Edit <i class="fa-solid fa-arrow-down"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- FORM KUSTOMISASI LENGKAP -->
        <form action="<?= $baseUrl ?>/rps/save-settings" method="POST" id="formRubrikRps">
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="rubrik">

            <!-- ================================================================
                 8.1 FORM RUBRIK PENILAIAN HOLISTIK
                 ================================================================ -->
            <div id="section_holistik" class="rubrik-section-box" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 9px; background: rgba(99, 102, 241, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">8.1 Rubrik Penilaian Holistik</h3>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">Asesmen terpadu untuk tugas analisis studi kasus kerangka kerja SI & kedisiplinan</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: var(--surface-hover); padding: 6px 14px; border-radius: 30px; border: 1px solid var(--border);">
                            <label class="rubrik-switch">
                                <input type="checkbox" name="rubrik[holistik][aktif]" id="main_toggle_holistik" value="1" <?= !empty($rubrik['holistik']['aktif']) ? 'checked' : '' ?> onchange="onRubrikStateChange('holistik', this.checked)">
                                <span class="rubrik-switch-slider"></span>
                            </label>
                            <span id="label_toggle_holistik" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                <?= !empty($rubrik['holistik']['aktif']) ? 'Aktif Digunakan di RPS' : 'Nonaktif (Tidak Dicetak)' ?>
                            </span>
                        </div>
                        <button type="button" class="btn-soft-reset" onclick="resetSingleRubrik('holistik')" title="Kembalikan rubrik ini ke template standar master">
                            <i class="fa-solid fa-rotate-left"></i> Reset Template
                        </button>
                    </div>
                </div>

                <div id="content_holistik" style="<?= empty($rubrik['holistik']['aktif']) ? 'opacity: 0.6;' : '' ?>">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                            <i class="fa-solid fa-heading" style="color: var(--primary);"></i> Judul Rubrik Holistik:
                        </label>
                        <input type="text" name="rubrik[holistik][judul]" id="judul_holistik" class="form-control" style="font-size: 0.95rem; font-weight: 600;" value="<?= htmlspecialchars($rubrik['holistik']['judul'] ?? 'Rubrik Penilaian Holistik (Tugas Kerangka Kerja Strategis SI)') ?>" required>
                    </div>

                    <?php 
                    $holistikKriteria = $rubrik['holistik']['kriteria'] ?? $defaultRubrik['holistik']['kriteria'];
                    foreach ($holistikKriteria as $kIdx => $krit):
                    ?>
                        <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 20px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 16px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 280px;">
                                    <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                                        <i class="fa-solid fa-tag" style="color: var(--primary);"></i> Nama Kriteria Penilaian <?= $kIdx + 1 ?>:
                                    </label>
                                    <textarea rows="2" name="rubrik[holistik][kriteria][<?= $kIdx ?>][nama]" class="rubrik-textarea" style="font-weight: 700; font-size: 0.9rem; min-height: 52px;" required oninput="autoResize(this)"><?= htmlspecialchars($krit['nama'] ?? '') ?></textarea>
                                </div>
                                <div style="width: 170px;">
                                    <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                                        <i class="fa-solid fa-percent" style="color: var(--warning);"></i> Bobot Kriteria:
                                    </label>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <input type="number" step="1" min="0" max="100" name="rubrik[holistik][kriteria][<?= $kIdx ?>][bobot_persen]" class="form-control calc-holistik-bobot text-center" style="font-weight: 700; font-size: 1rem; width: 90px;" value="<?= floatval($krit['bobot_persen'] ?? 0) ?>" required oninput="recalculateHolistikBobot()">
                                        <span style="font-weight: 700; color: var(--text-muted); font-size: 0.95rem;">%</span>
                                    </div>
                                </div>
                            </div>

                            <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; font-weight: 600; font-size: 0.84rem; color: var(--text-muted);">
                                <i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Skala Ketercapaian & Deskriptor Kinerja:
                            </label>
                            
                            <div class="rubrik-table-container">
                                <table class="rubrik-edit-table" style="min-width: 860px;">
                                    <colgroup>
                                        <col style="width: 120px;">
                                        <col>
                                        <col style="width: 150px;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">Tingkat</th>
                                            <th>Deskriptor Tingkat Ketercapaian (Uraian Indikator Kinerja)</th>
                                            <th style="text-align: center;">Rentang Skor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $skalaLevels = [
                                            0 => ['label' => 'Level 1', 'bg' => 'rgba(239, 68, 68, 0.15)', 'color' => 'var(--danger)'],
                                            1 => ['label' => 'Level 2', 'bg' => 'rgba(249, 115, 22, 0.15)', 'color' => '#fb923c'],
                                            2 => ['label' => 'Level 3', 'bg' => 'rgba(245, 158, 11, 0.15)', 'color' => '#fbbf24'],
                                            3 => ['label' => 'Level 4', 'bg' => 'rgba(59, 130, 246, 0.15)', 'color' => '#60a5fa'],
                                            4 => ['label' => 'Level 5', 'bg' => 'rgba(16, 185, 129, 0.15)', 'color' => 'var(--success)']
                                        ];
                                        foreach ($krit['skala'] as $sIdx => $sk): 
                                            $lvlInfo = $skalaLevels[$sIdx] ?? ['label' => 'Skala ' . ($sIdx + 1), 'bg' => 'rgba(100, 116, 139, 0.15)', 'color' => 'var(--text-muted)'];
                                        ?>
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <span class="level-badge-pill" style="background: <?= $lvlInfo['bg'] ?>; color: <?= $lvlInfo['color'] ?>;">
                                                        <i class="fa-solid fa-circle" style="font-size: 0.45rem;"></i> <?= $lvlInfo['label'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <textarea rows="2" name="rubrik[holistik][kriteria][<?= $kIdx ?>][skala][<?= $sIdx ?>][deskripsi]" class="rubrik-textarea" style="min-height: 56px;" placeholder="Tuliskan uraian capaian mahasiswa untuk skala ini..." oninput="autoResize(this)"><?= htmlspecialchars($sk['deskripsi'] ?? '') ?></textarea>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <div style="display: inline-flex; align-items: center; background: var(--surface); border: 1px solid var(--border); border-radius: 7px; padding: 2px 6px;">
                                                        <span style="color: var(--text-muted); font-size: 0.78rem; margin-right: 4px;"><i class="fa-solid fa-award"></i></span>
                                                        <input type="text" name="rubrik[holistik][kriteria][<?= $kIdx ?>][skala][<?= $sIdx ?>][skor]" class="form-control text-center" style="font-size: 0.88rem; font-weight: 700; width: 90px; border: none; padding: 4px; background: transparent !important;" value="<?= htmlspecialchars($sk['skor'] ?? '') ?>">
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface-hover); border: 1px solid var(--border); padding: 12px 20px; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 700; color: var(--text-main);">
                        <span><i class="fa-solid fa-calculator" style="color: var(--primary); margin-right: 8px;"></i> Total Bobot Kriteria Holistik:</span>
                        <span id="badge_total_holistik" style="color: var(--success); font-size: 1.05rem;">100%</span>
                    </div>
                </div>
            </div>

            <!-- ================================================================
                 8.2 FORM RUBRIK PENILAIAN ANALITIK
                 ================================================================ -->
            <div id="section_analitik" class="rubrik-section-box" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 9px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fa-solid fa-table-cells"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">8.2 Rubrik Penilaian Analitik</h3>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">Penilaian multi-kriteria berbobot untuk tugas proyek / perancangan dengan 5 rentang capaian mutu</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: var(--surface-hover); padding: 6px 14px; border-radius: 30px; border: 1px solid var(--border);">
                            <label class="rubrik-switch">
                                <input type="checkbox" name="rubrik[analitik][aktif]" id="main_toggle_analitik" value="1" <?= !empty($rubrik['analitik']['aktif']) ? 'checked' : '' ?> onchange="onRubrikStateChange('analitik', this.checked)">
                                <span class="rubrik-switch-slider"></span>
                            </label>
                            <span id="label_toggle_analitik" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                <?= !empty($rubrik['analitik']['aktif']) ? 'Aktif Digunakan di RPS' : 'Nonaktif (Tidak Dicetak)' ?>
                            </span>
                        </div>
                        <button type="button" class="btn-soft-reset" onclick="resetSingleRubrik('analitik')" title="Kembalikan rubrik ini ke template standar master">
                            <i class="fa-solid fa-rotate-left"></i> Reset Template
                        </button>
                    </div>
                </div>

                <div id="content_analitik" style="<?= empty($rubrik['analitik']['aktif']) ? 'opacity: 0.6;' : '' ?>">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                            <i class="fa-solid fa-heading" style="color: #3b82f6;"></i> Judul Rubrik Analitik:
                        </label>
                        <input type="text" name="rubrik[analitik][judul]" id="judul_analitik" class="form-control" style="font-size: 0.95rem; font-weight: 600;" value="<?= htmlspecialchars($rubrik['analitik']['judul'] ?? 'Rubrik Penilaian Analitik (Penyusunan Rencana Strategis & Arsitektur SI)') ?>" required>
                    </div>

                    <!-- Responsive Horizontal Scroll Table with Generous Min-Width and Fixed Layout -->
                    <div class="rubrik-table-container">
                        <table class="rubrik-edit-table" style="min-width: 1400px;">
                            <colgroup>
                                <col style="width: 240px;">
                                <col style="width: 85px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 50px;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>
                                        <div style="font-size: 0.88rem; font-weight: 700;">Dimensi / Kriteria Penilaian</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">Aspek hasil karya</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <div style="font-size: 0.88rem; font-weight: 700;">Bobot</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">(%)</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">Sangat Baik</span>
                                            <span class="level-badge-pill" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">81 - 100</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Mutu Sempurna</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">Baik</span>
                                            <span class="level-badge-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">66 - 80</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Sesuai Standar</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">Cukup</span>
                                            <span class="level-badge-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">56 - 65</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Cukup Memadai</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">Kurang</span>
                                            <span class="level-badge-pill" style="background: rgba(249, 115, 22, 0.15); color: #fb923c;">41 - 55</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Di Bawah Standar</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">Sangat Kurang</span>
                                            <span class="level-badge-pill" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">&le; 40</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Tidak Memenuhi</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <i class="fa-solid fa-gear" style="color: var(--text-muted);" title="Aksi Baris"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="analitik_tbody">
                                <?php 
                                $analitikDimensi = $rubrik['analitik']['dimensi'] ?? $defaultRubrik['analitik']['dimensi'];
                                foreach ($analitikDimensi as $dIdx => $dim):
                                ?>
                                    <tr class="analitik-row" data-idx="<?= $dIdx ?>">
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][nama]" class="rubrik-textarea" style="font-weight: 700; min-height: 85px;" placeholder="Nama Dimensi / Aspek Kriteria" required oninput="autoResize(this)"><?= htmlspecialchars($dim['nama'] ?? '') ?></textarea>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="1" min="0" max="100" name="rubrik[analitik][dimensi][<?= $dIdx ?>][bobot]" class="form-control calc-analitik-bobot text-center" style="font-size: 0.95rem; font-weight: 700; width: 65px; margin: 0 auto;" value="<?= floatval($dim['bobot'] ?? 0) ?>" required oninput="recalculateAnalitikBobot()">
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][sangat_baik]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Sangat Baik (81-100)..." oninput="autoResize(this)"><?= htmlspecialchars($dim['sangat_baik'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][baik]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Baik (66-80)..." oninput="autoResize(this)"><?= htmlspecialchars($dim['baik'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][cukup]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Cukup (56-65)..." oninput="autoResize(this)"><?= htmlspecialchars($dim['cukup'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][kurang]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Kurang (41-55)..." oninput="autoResize(this)"><?= htmlspecialchars($dim['kurang'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[analitik][dimensi][<?= $dIdx ?>][sangat_kurang]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Sangat Kurang (<=40)..." oninput="autoResize(this)"><?= htmlspecialchars($dim['sangat_kurang'] ?? '') ?></textarea>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculateAnalitikBobot)" title="Hapus Dimensi Ini">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 4px;">
                        <button type="button" class="btn-soft-add" onclick="addAnalitikRow()">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Dimensi Analitik
                        </button>
                        <div style="font-size: 0.92rem; font-weight: 700; background: var(--surface-hover); padding: 8px 18px; border-radius: 8px; border: 1px solid var(--border); color: var(--text-main);">
                            Total Bobot Analitik: <span id="badge_total_analitik" style="color: var(--success); font-size: 1.05rem;">100%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================================
                 8.3 FORM RUBRIK PENILAIAN SKALA PERSEPSI (SESUAI DOKUMEN RESMI)
                 ================================================================ -->
            <div id="section_persepsi" class="rubrik-section-box" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 9px; background: rgba(168, 85, 247, 0.15); color: #a855f7; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">8.3 Rubrik Penilaian Skala Persepsi</h3>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">Asesmen sikap afektif, etika akademik, dan kolaborasi tim (Skala Likert 1 s.d. 5)</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: var(--surface-hover); padding: 6px 14px; border-radius: 30px; border: 1px solid var(--border);">
                            <label class="rubrik-switch">
                                <input type="checkbox" name="rubrik[skala_persepsi][aktif]" id="main_toggle_skala_persepsi" value="1" <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'checked' : '' ?> onchange="onRubrikStateChange('skala_persepsi', this.checked)">
                                <span class="rubrik-switch-slider"></span>
                            </label>
                            <span id="label_toggle_skala_persepsi" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                <?= !empty($rubrik['skala_persepsi']['aktif']) ? 'Aktif Digunakan di RPS' : 'Nonaktif (Tidak Dicetak)' ?>
                            </span>
                        </div>
                        <button type="button" class="btn-soft-reset" onclick="resetSingleRubrik('skala_persepsi')" title="Kembalikan rubrik ini ke template standar master">
                            <i class="fa-solid fa-rotate-left"></i> Reset Template
                        </button>
                    </div>
                </div>

                <div id="content_skala_persepsi" style="<?= empty($rubrik['skala_persepsi']['aktif']) ? 'opacity: 0.6;' : '' ?>">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                            <i class="fa-solid fa-heading" style="color: #a855f7;"></i> Judul Rubrik Skala Persepsi:
                        </label>
                        <input type="text" name="rubrik[skala_persepsi][judul]" id="judul_skala_persepsi" class="form-control" style="font-size: 0.95rem; font-weight: 600;" value="<?= htmlspecialchars($persepsiJudul) ?>" required>
                    </div>

                    <!-- Responsive Table for Skala Persepsi matching Document 5-scale columns -->
                    <div class="rubrik-table-container">
                        <table class="rubrik-edit-table" style="min-width: 1400px;">
                            <colgroup>
                                <col style="width: 240px;">
                                <col style="width: 85px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 210px;">
                                <col style="width: 50px;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>
                                        <div style="font-size: 0.88rem; font-weight: 700;">Aspek Pengamatan</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">(Afektif & Kerjasama)</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <div style="font-size: 0.88rem; font-weight: 700;">Bobot</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">(%)</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">1 - Sangat Kurang</span>
                                            <span class="level-badge-pill" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">Skor 1</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Tidak Ada Kontribusi</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">2 - Kurang</span>
                                            <span class="level-badge-pill" style="background: rgba(249, 115, 22, 0.15); color: #fb923c;">Skor 2</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Pasif / Terlambat</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">3 - Cukup</span>
                                            <span class="level-badge-pill" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">Skor 3</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Cukup Kooperatif</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">4 - Baik</span>
                                            <span class="level-badge-pill" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa;">Skor 4</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Aktif & Bertanggungjawab</div>
                                    </th>
                                    <th>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 700;">5 - Sangat Baik</span>
                                            <span class="level-badge-pill" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">Skor 5</span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;">Inisiatif & Teladan</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <i class="fa-solid fa-gear" style="color: var(--text-muted);" title="Aksi Baris"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="persepsi_tbody">
                                <?php 
                                $persepsiAspek = $rubrik['skala_persepsi']['aspek'] ?? $defaultRubrik['skala_persepsi']['aspek'];
                                
                                // Default fallback descriptors jika skala belum terdefinisi di data lama
                                $defaultPersepsiDescFallback = [
                                    'Kerjasama Tim & Kontribusi Tugas' => [
                                        1 => 'Tidak berkontribusi dan lepas tangan dari tanggung jawab tim.',
                                        2 => 'Kontribusi pasif, sering terlambat menyelesaikan bagian tugasnya.',
                                        3 => 'Menjalankan tugas jika didorong atau diingatkan oleh ketua/rekan.',
                                        4 => 'Menjalankan bagian tugasnya dengan baik dan kooperatif.',
                                        5 => 'Sangat proaktif, memimpin inisiatif, membantu rekan, komitmen penuh.'
                                    ],
                                    'Keaktifan & Argumentasi Ilmiah' => [
                                        1 => 'Pasif sama sekali dalam diskusi dan sesi tanya jawab.',
                                        2 => 'Jarang berpendapat kecuali saat ditunjuk langsung oleh dosen.',
                                        3 => 'Cukup aktif berbicara, namun argumen bersifat opini tanpa rujukan.',
                                        4 => 'Aktif menyampaikan pendapat berbasis materi kuliah dengan runtut.',
                                        5 => 'Selalu aktif, kritis, argumentasi berlandaskan referensi ilmiah kuat.'
                                    ],
                                    'Etika Akademik & Menghargai Pendapat' => [
                                        1 => 'Melanggar etika/plagiarisme atau bersikap tidak sopan.',
                                        2 => 'Kurang menghargai pendapat rekan atau acuh terhadap etika.',
                                        3 => 'Sopan namun terkadang defensif saat menerima kritik dari orang lain.',
                                        4 => 'Santun, menghormati perbedaan pendapat, menjunjung kejujuran akademik.',
                                        5 => 'Integritas tinggi, sangat santun, terbuka pada kritik, apresiatif.'
                                    ],
                                    'Kedisiplinan & Manajemen Waktu' => [
                                        1 => 'Tingkat kehadiran rendah, sering melanggar tata tertib.',
                                        2 => 'Sering terlambat hadir dan mengabaikan jadwal yang disepakati.',
                                        3 => 'Hadir tepat waktu namun terkadang terlambat pada pengumpulan progres.',
                                        4 => 'Disiplin hadir tepat waktu dan menaati batas waktu pengumpulan tugas.',
                                        5 => 'Selalu hadir tepat waktu, siap materi, mengumpulkan selalu sebelum deadline.'
                                    ]
                                ];

                                foreach ($persepsiAspek as $aIdx => $asp):
                                    $aspNama = $asp['nama'] ?? '';
                                    $skalaMap = $asp['skala'] ?? ($defaultPersepsiDescFallback[$aspNama] ?? [
                                        1 => 'Kinerja dan sikap sangat tidak memuaskan pada aspek ini.',
                                        2 => 'Kinerja dan sikap di bawah standar minimal yang diharapkan.',
                                        3 => 'Memenuhi standar minimal ketercapaian aspek afektif.',
                                        4 => 'Menunjukkan sikap dan kinerja yang baik serta konsisten.',
                                        5 => 'Menunjukkan keunggulan, inisiatif tinggi, dan keteladanan.'
                                    ]);
                                ?>
                                    <tr class="persepsi-row" data-idx="<?= $aIdx ?>">
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][nama]" class="rubrik-textarea" style="font-weight: 700; min-height: 85px;" placeholder="Nama Aspek Afektif" required oninput="autoResize(this)"><?= htmlspecialchars($aspNama) ?></textarea>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="1" min="0" max="100" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][bobot]" class="form-control calc-persepsi-bobot text-center" style="font-weight: 700; font-size: 0.95rem; width: 65px; margin: 0 auto;" value="<?= floatval($asp['bobot'] ?? 0) ?>" required oninput="recalculatePersepsiBobot()">
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][skala][1]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 1 (Sangat Kurang)..." oninput="autoResize(this)"><?= htmlspecialchars($skalaMap[1] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][skala][2]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 2 (Kurang)..." oninput="autoResize(this)"><?= htmlspecialchars($skalaMap[2] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][skala][3]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 3 (Cukup)..." oninput="autoResize(this)"><?= htmlspecialchars($skalaMap[3] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][skala][4]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 4 (Baik)..." oninput="autoResize(this)"><?= htmlspecialchars($skalaMap[4] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[skala_persepsi][aspek][<?= $aIdx ?>][skala][5]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 5 (Sangat Baik)..." oninput="autoResize(this)"><?= htmlspecialchars($skalaMap[5] ?? '') ?></textarea>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculatePersepsiBobot)" title="Hapus Aspek Ini">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 4px;">
                        <button type="button" class="btn-soft-add" onclick="addPersepsiRow()">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Aspek Afektif
                        </button>
                        <div style="font-size: 0.92rem; font-weight: 700; background: var(--surface-hover); padding: 8px 18px; border-radius: 8px; border: 1px solid var(--border); color: var(--text-main);">
                            Total Bobot Skala Persepsi: <span id="badge_total_persepsi" style="color: var(--success); font-size: 1.05rem;">100%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================================
                 8.4 FORM RUBRIK PENILAIAN PORTOFOLIO
                 ================================================================ -->
            <div id="section_portofolio" class="rubrik-section-box" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 9px; background: rgba(16, 185, 129, 0.15); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">8.4 Rubrik Penilaian Portofolio Mahasiswa</h3>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">Asesmen autentik berkas artefak karya nyata dan progres capaian Sub-CPMK semester</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: var(--surface-hover); padding: 6px 14px; border-radius: 30px; border: 1px solid var(--border);">
                            <label class="rubrik-switch">
                                <input type="checkbox" name="rubrik[portofolio][aktif]" id="main_toggle_portofolio" value="1" <?= !empty($rubrik['portofolio']['aktif']) ? 'checked' : '' ?> onchange="onRubrikStateChange('portofolio', this.checked)">
                                <span class="rubrik-switch-slider"></span>
                            </label>
                            <span id="label_toggle_portofolio" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                <?= !empty($rubrik['portofolio']['aktif']) ? 'Aktif Digunakan di RPS' : 'Nonaktif (Tidak Dicetak)' ?>
                            </span>
                        </div>
                        <button type="button" class="btn-soft-reset" onclick="resetSingleRubrik('portofolio')" title="Kembalikan rubrik ini ke template standar master">
                            <i class="fa-solid fa-rotate-left"></i> Reset Template
                        </button>
                    </div>
                </div>

                <div id="content_portofolio" style="<?= empty($rubrik['portofolio']['aktif']) ? 'opacity: 0.6;' : '' ?>">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 700; font-size: 0.88rem; color: var(--text-main);">
                            <i class="fa-solid fa-heading" style="color: var(--success);"></i> Judul Rubrik Portofolio:
                        </label>
                        <input type="text" name="rubrik[portofolio][judul]" id="judul_portofolio" class="form-control" style="font-size: 0.95rem; font-weight: 600;" value="<?= htmlspecialchars($rubrik['portofolio']['judul'] ?? 'Rubrik Penilaian Portofolio Mahasiswa (Kumpulan Artefak Proyek SI)') ?>" required>
                    </div>

                    <!-- Responsive Horizontal Scroll Table for Portfolio with explicit Column Widths -->
                    <div class="rubrik-table-container">
                        <table class="rubrik-edit-table" style="min-width: 1240px;">
                            <colgroup>
                                <col style="width: 45px;">
                                <col style="width: 260px;">
                                <col style="width: 80px;">
                                <col style="width: 350px;">
                                <col style="width: 240px;">
                                <col style="width: 135px;">
                                <col style="width: 50px;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="text-align: center;">No</th>
                                    <th>
                                        <div style="font-weight: 700;">Komponen Artefak & Sub-CPMK</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">Nama berkas / karya nyata</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <div style="font-weight: 700;">Bobot</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">(%)</div>
                                    </th>
                                    <th>
                                        <div style="font-weight: 700;">Kriteria Penilaian Mutu Artefak</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">Standar kualitas ketercapaian</div>
                                    </th>
                                    <th>
                                        <div style="font-weight: 700;">Bentuk Bukti Fisik</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">File / Berkas / Repositori</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <div style="font-weight: 700;">Batas Waktu</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: normal;">Jadwal / Minggu</div>
                                    </th>
                                    <th style="text-align: center;">
                                        <i class="fa-solid fa-gear" style="color: var(--text-muted);" title="Aksi Baris"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="portofolio_tbody">
                                <?php 
                                $portofolioArtefak = $rubrik['portofolio']['artefak'] ?? $defaultRubrik['portofolio']['artefak'];
                                foreach ($portofolioArtefak as $pIdx => $art):
                                ?>
                                    <tr class="portofolio-row" data-idx="<?= $pIdx ?>">
                                        <td style="text-align: center; vertical-align: middle; font-weight: 700;">
                                            <input type="hidden" name="rubrik[portofolio][artefak][<?= $pIdx ?>][no]" value="<?= $art['no'] ?? ($pIdx + 1) ?>">
                                            <span class="row-number-badge" style="background: var(--surface-hover); color: var(--text-main); border: 1px solid var(--border); padding: 2px 7px; border-radius: 4px; font-size: 0.8rem;"><?= $art['no'] ?? ($pIdx + 1) ?></span>
                                        </td>
                                        <td>
                                            <input type="text" name="rubrik[portofolio][artefak][<?= $pIdx ?>][nama]" class="form-control" style="font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;" value="<?= htmlspecialchars($art['nama'] ?? '') ?>" placeholder="Nama Dokumen/Artefak" required>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fa-solid fa-link"></i></span>
                                                <input type="text" name="rubrik[portofolio][artefak][<?= $pIdx ?>][sub_cpmk]" class="form-control" style="font-size: 0.78rem;" value="<?= htmlspecialchars($art['sub_cpmk'] ?? '') ?>" placeholder="Sub-CPMK ..">
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="1" min="0" max="100" name="rubrik[portofolio][artefak][<?= $pIdx ?>][bobot]" class="form-control calc-portofolio-bobot text-center" style="font-size: 0.95rem; font-weight: 700; width: 65px; margin: 0 auto;" value="<?= floatval($art['bobot'] ?? 0) ?>" required oninput="recalculatePortofolioBobot()">
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[portofolio][artefak][<?= $pIdx ?>][kriteria]" class="rubrik-textarea" style="min-height: 75px;" placeholder="Kriteria penilaian mutu ketercapaian artefak..." oninput="autoResize(this)"><?= htmlspecialchars($art['kriteria'] ?? '') ?></textarea>
                                        </td>
                                        <td>
                                            <textarea rows="3" name="rubrik[portofolio][artefak][<?= $pIdx ?>][bukti]" class="rubrik-textarea" style="min-height: 75px;" placeholder="Bentuk berkas / bukti fisik / tautan..." oninput="autoResize(this)"><?= htmlspecialchars($art['bukti'] ?? '') ?></textarea>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="text" name="rubrik[portofolio][artefak][<?= $pIdx ?>][minggu]" class="form-control text-center" style="font-size: 0.82rem;" value="<?= htmlspecialchars($art['minggu'] ?? '') ?>" placeholder="Minggu Ke-..">
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculatePortofolioBobot)" title="Hapus Artefak Ini">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 4px;">
                        <button type="button" class="btn-soft-add" onclick="addPortofolioRow()">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Komponen Artefak
                        </button>
                        <div style="font-size: 0.92rem; font-weight: 700; background: var(--surface-hover); padding: 8px 18px; border-radius: 8px; border: 1px solid var(--border); color: var(--text-main);">
                            Total Bobot Portofolio: <span id="badge_total_portofolio" style="color: var(--success); font-size: 1.05rem;">100%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Bar with Floating Dark Glassmorphic Style -->
            <div style="position: sticky; bottom: 18px; z-index: 100; background: rgba(26, 29, 45, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 2px solid var(--primary); border-radius: var(--radius-md); padding: 16px 26px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i>
                        Status Rubrik Dokumen RPS Mata Kuliah:
                    </div>
                    <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 2px;" id="active_rubrik_count_text">
                        Menghitung status rubrik aktif...
                    </div>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary" onclick="resetAllRubrikToMaster()" style="padding: 10px 18px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px;">
                        <i class="fa-solid fa-rotate-left"></i> Reset Semua ke Master
                    </button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Rubrik
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Script Interaktif untuk Sinkronisasi, Auto-Resize, & Bobot -->
    <script>
    const DEFAULT_TEMPLATES = <?= json_encode($defaultRubrik, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function autoResize(el) {
        if (!el) return;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
    }

    function initAllTextareas() {
        document.querySelectorAll('.rubrik-textarea').forEach(el => {
            autoResize(el);
        });
    }

    function syncToggle(key, isChecked) {
        const mainToggle = document.getElementById('main_toggle_' + key);
        if (mainToggle) {
            mainToggle.checked = isChecked;
            onRubrikStateChange(key, isChecked);
        }
    }

    function onRubrikStateChange(key, isChecked) {
        // Update label
        const lbl = document.getElementById('label_toggle_' + key);
        if (lbl) {
            lbl.innerText = isChecked ? 'Aktif Digunakan di RPS' : 'Nonaktif (Tidak Dicetak)';
        }

        // Update content container opacity
        const content = document.getElementById('content_' + key);
        if (content) {
            content.style.opacity = isChecked ? '1' : '0.55';
        }

        // Update summary card styling
        const card = document.getElementById('summary_card_' + key);
        if (card) {
            if (isChecked) {
                card.classList.remove('rubrik-card-inactive');
                card.classList.add('rubrik-card-active');
            } else {
                card.classList.remove('rubrik-card-active');
                card.classList.add('rubrik-card-inactive');
            }
        }

        // Update summary badge
        const sumBadge = document.getElementById('badge_summary_' + key);
        if (sumBadge) {
            sumBadge.innerHTML = isChecked 
                ? '<i class="fa-solid fa-check-circle"></i> AKTIF' 
                : '<i class="fa-solid fa-circle-xmark"></i> NONAKTIF';
            sumBadge.style.background = isChecked ? 'rgba(16, 185, 129, 0.15)' : 'rgba(100, 116, 139, 0.15)';
            sumBadge.style.color = isChecked ? 'var(--success)' : 'var(--text-muted)';
        }

        // Sync top checkbox if triggered from main toggle
        const topCb = document.querySelector(`.rubrik-summary-card#summary_card_${key} input[type="checkbox"]`);
        if (topCb && topCb.checked !== isChecked) {
            topCb.checked = isChecked;
        }

        updateActiveRubrikCount();
    }

    function updateActiveRubrikCount() {
        const keys = ['holistik', 'analitik', 'skala_persepsi', 'portofolio'];
        let activeCount = 0;
        keys.forEach(k => {
            const cb = document.getElementById('main_toggle_' + k);
            if (cb && cb.checked) activeCount++;
        });

        const infoEl = document.getElementById('active_rubrik_count_text');
        if (infoEl) {
            if (activeCount === 0) {
                infoEl.innerHTML = '<span style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> 0 dari 4 Rubrik Aktif (Dokumen RPS tidak akan memuat lampiran rubrik)</span>';
            } else {
                infoEl.innerHTML = `<span style="color: var(--success); font-weight: 600;"><i class="fa-solid fa-check-circle"></i> ${activeCount} dari 4 Rubrik Aktif</span> akan otomatis dicetak berurutan (8.1 s.d 8.${activeCount}) di Dokumen Resmi RPS`;
            }
        }
    }

    function recalculateHolistikBobot() {
        let sum = 0;
        document.querySelectorAll('.calc-holistik-bobot').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });
        const badge = document.getElementById('badge_total_holistik');
        if (badge) {
            badge.innerText = sum + '%';
            badge.style.color = (sum === 100) ? 'var(--success)' : 'var(--danger)';
            if (sum !== 100) badge.innerText += ' (Harus 100%)';
        }
    }

    function recalculateAnalitikBobot() {
        let sum = 0;
        document.querySelectorAll('.calc-analitik-bobot').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });
        const badge = document.getElementById('badge_total_analitik');
        if (badge) {
            badge.innerText = sum + '%';
            badge.style.color = (sum === 100) ? 'var(--success)' : 'var(--danger)';
            if (sum !== 100) badge.innerText += ' (Harus 100%)';
        }
    }

    function recalculatePersepsiBobot() {
        let sum = 0;
        document.querySelectorAll('.calc-persepsi-bobot').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });
        const badge = document.getElementById('badge_total_persepsi');
        if (badge) {
            badge.innerText = sum + '%';
            badge.style.color = (sum === 100) ? 'var(--success)' : 'var(--danger)';
            if (sum !== 100) badge.innerText += ' (Harus 100%)';
        }
    }

    function recalculatePortofolioBobot() {
        let sum = 0;
        document.querySelectorAll('.calc-portofolio-bobot').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });
        const badge = document.getElementById('badge_total_portofolio');
        if (badge) {
            badge.innerText = sum + '%';
            badge.style.color = (sum === 100) ? 'var(--success)' : 'var(--danger)';
            if (sum !== 100) badge.innerText += ' (Harus 100%)';
        }
    }

    function deleteTableRow(btn, callback) {
        const tr = btn.closest('tr');
        if (tr) {
            tr.remove();
            if (callback) callback();
        }
    }

    function addAnalitikRow() {
        const tbody = document.getElementById('analitik_tbody');
        const count = tbody.querySelectorAll('tr').length;
        const newIdx = count + '_' + Date.now();
        const tr = document.createElement('tr');
        tr.className = 'analitik-row';
        tr.innerHTML = `
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][nama]" class="rubrik-textarea" style="font-weight: 700; min-height: 85px;" placeholder="Nama Dimensi / Aspek Baru" required oninput="autoResize(this)"></textarea>
            </td>
            <td style="text-align: center;">
                <input type="number" step="1" min="0" max="100" name="rubrik[analitik][dimensi][${newIdx}][bobot]" class="form-control calc-analitik-bobot text-center" style="font-size: 0.95rem; font-weight: 700; width: 65px; margin: 0 auto;" value="20" required oninput="recalculateAnalitikBobot()">
            </td>
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][sangat_baik]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Sangat Baik (81-100)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][baik]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Baik (66-80)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][cukup]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Cukup (56-65)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][kurang]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Kurang (41-55)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[analitik][dimensi][${newIdx}][sangat_kurang]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Sangat Kurang (<=40)..." oninput="autoResize(this)"></textarea>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculateAnalitikBobot)" title="Hapus Dimensi Ini">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recalculateAnalitikBobot();
    }

    function addPersepsiRow() {
        const tbody = document.getElementById('persepsi_tbody');
        const count = tbody.querySelectorAll('tr').length;
        const newIdx = count + '_' + Date.now();
        const tr = document.createElement('tr');
        tr.className = 'persepsi-row';
        tr.innerHTML = `
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][nama]" class="rubrik-textarea" style="font-weight: 700; min-height: 85px;" placeholder="Nama Aspek Afektif Baru" required oninput="autoResize(this)"></textarea>
            </td>
            <td style="text-align: center;">
                <input type="number" step="1" min="0" max="100" name="rubrik[skala_persepsi][aspek][${newIdx}][bobot]" class="form-control calc-persepsi-bobot text-center" style="font-weight: 700; font-size: 0.95rem; width: 65px; margin: 0 auto;" value="25" required oninput="recalculatePersepsiBobot()">
            </td>
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][skala][1]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 1 (Sangat Kurang)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][skala][2]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 2 (Kurang)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][skala][3]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 3 (Cukup)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][skala][4]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 4 (Baik)..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[skala_persepsi][aspek][${newIdx}][skala][5]" class="rubrik-textarea" style="min-height: 85px;" placeholder="Deskriptor Skor 5 (Sangat Baik)..." oninput="autoResize(this)"></textarea>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculatePersepsiBobot)" title="Hapus Aspek Ini">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recalculatePersepsiBobot();
    }

    function addPortofolioRow() {
        const tbody = document.getElementById('portofolio_tbody');
        const count = tbody.querySelectorAll('tr').length;
        const newIdx = count + '_' + Date.now();
        const tr = document.createElement('tr');
        tr.className = 'portofolio-row';
        tr.innerHTML = `
            <td style="text-align: center; vertical-align: middle; font-weight: 700;">
                <input type="hidden" name="rubrik[portofolio][artefak][${newIdx}][no]" value="${count + 1}">
                <span class="row-number-badge" style="background: var(--surface-hover); color: var(--text-main); border: 1px solid var(--border); padding: 2px 7px; border-radius: 4px; font-size: 0.8rem;">${count + 1}</span>
            </td>
            <td>
                <input type="text" name="rubrik[portofolio][artefak][${newIdx}][nama]" class="form-control" style="font-size: 0.85rem; font-weight: 700; margin-bottom: 6px;" placeholder="Nama Dokumen/Artefak Baru" required>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fa-solid fa-link"></i></span>
                    <input type="text" name="rubrik[portofolio][artefak][${newIdx}][sub_cpmk]" class="form-control" style="font-size: 0.78rem;" placeholder="Sub-CPMK ..">
                </div>
            </td>
            <td style="text-align: center;">
                <input type="number" step="1" min="0" max="100" name="rubrik[portofolio][artefak][${newIdx}][bobot]" class="form-control calc-portofolio-bobot text-center" style="font-size: 0.95rem; font-weight: 700; width: 65px; margin: 0 auto;" value="20" required oninput="recalculatePortofolioBobot()">
            </td>
            <td>
                <textarea rows="3" name="rubrik[portofolio][artefak][${newIdx}][kriteria]" class="rubrik-textarea" style="min-height: 75px;" placeholder="Kriteria penilaian mutu ketercapaian artefak..." oninput="autoResize(this)"></textarea>
            </td>
            <td>
                <textarea rows="3" name="rubrik[portofolio][artefak][${newIdx}][bukti]" class="rubrik-textarea" style="min-height: 75px;" placeholder="Bentuk berkas / bukti fisik / tautan..." oninput="autoResize(this)"></textarea>
            </td>
            <td style="text-align: center;">
                <input type="text" name="rubrik[portofolio][artefak][${newIdx}][minggu]" class="form-control text-center" style="font-size: 0.82rem;" placeholder="Minggu Ke-..">
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn-soft-delete" onclick="deleteTableRow(this, recalculatePortofolioBobot)" title="Hapus Artefak Ini">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recalculatePortofolioBobot();
    }

    function resetSingleRubrik(key) {
        if (!confirm(`Kembalikan Rubrik ${key.toUpperCase()} ke template standar master?`)) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/rps/save-settings';
        form.innerHTML = `
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="rubrik">
        `;
        const currentRubrik = <?= json_encode($rubrik, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        currentRubrik[key] = DEFAULT_TEMPLATES[key];
        const jsonInput = document.createElement('input');
        jsonInput.type = 'hidden';
        jsonInput.name = 'rubrik_penilaian_json';
        jsonInput.value = JSON.stringify(currentRubrik);
        form.appendChild(jsonInput);
        document.body.appendChild(form);
        form.submit();
    }

    function resetAllRubrikToMaster() {
        if (!confirm("Apakah Anda yakin ingin mengatur ulang SEMUA rubrik pada Mata Kuliah ini kembali ke Template Standar Master?")) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/rps/save-settings';
        form.innerHTML = `
            <input type="hidden" name="mata_kuliah_id" value="<?= $mk['id'] ?>">
            <input type="hidden" name="tab" value="rubrik">
            <input type="hidden" name="rubrik_penilaian_json" value='${JSON.stringify(DEFAULT_TEMPLATES)}'>
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Init recalculations, counters, and auto-resize on page load
    document.addEventListener('DOMContentLoaded', () => {
        recalculateHolistikBobot();
        recalculateAnalitikBobot();
        recalculatePersepsiBobot();
        recalculatePortofolioBobot();
        updateActiveRubrikCount();
        initAllTextareas();
    });
    </script>
<?php endif; ?>

<?php if(!empty($rpsAssignment)): ?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const taId=<?= (int)$rpsAssignment['tahun_akademik_id'] ?>;
  document.querySelectorAll('a[href*="/rps?mk_id="]').forEach(a=>{const u=new URL(a.href,location.origin);if(!u.searchParams.has('ta_id')){u.searchParams.set('ta_id',taId);a.href=u.toString()}});
  document.querySelectorAll('a[href*="/rps/cpmk/"],a[href*="/rps/sub_cpmk/"]').forEach(a=>a.style.display='none');
});
</script>
<?php endif; ?>
<?php if(!$rpsCanEdit): ?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('form').forEach(form=>{
    if(form.action.includes('/rps/')) form.querySelectorAll('input,select,textarea,button').forEach(el=>el.disabled=true);
  });
  document.querySelectorAll('a[href*="/rps/cpmk/"],a[href*="/rps/sub_cpmk/"],a[href*="/rps/pertemuan/"]').forEach(a=>{a.style.display='none'});
});
</script>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
