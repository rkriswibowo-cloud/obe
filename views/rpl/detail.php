<?php
$title = 'Detail Studi RPL';
ob_start();
$base = $GLOBALS['config']['base_url'] ?? '/obe/public';
$role = $_SESSION['role'] ?? '';
$isAdmin = in_array($role, ['Super Admin', 'Admin Prodi'], true);
$rplAccess = new AccessContext();
$isApprover = $rplAccess->isSuperAdmin() || $rplAccess->isKaprodi();
$isSuperAdmin = $role === 'Super Admin';
$existingTargetIds = array_map('intval', array_column($conversions, 'mata_kuliah_id'));
$availableCourses = array_values(array_filter($courses, fn($mk) => !in_array((int)$mk['id'], $existingTargetIds, true)));
$conversionPerPage = 5;
$conversionTotal = count($conversions);
$conversionTotalPages = max(1, (int)ceil($conversionTotal / $conversionPerPage));
$conversionPage = min($conversionTotalPages, max(1, (int)($_GET['konversi_page'] ?? 1)));
$conversionOffset = ($conversionPage - 1) * $conversionPerPage;
$pagedConversions = array_slice($conversions, $conversionOffset, $conversionPerPage);
$statusStyles = [
    'DIAJUKAN' => ['Menunggu', '#f59e0b', 'rgba(245,158,11,.12)', 'fa-clock'],
    'DISETUJUI' => ['Disetujui', '#10b981', 'rgba(16,185,129,.12)', 'fa-circle-check'],
    'DITOLAK' => ['Ditolak', '#ef4444', 'rgba(239,68,68,.12)', 'fa-circle-xmark'],
    'DRAFT' => ['Draft', '#64748b', 'rgba(100,116,139,.12)', 'fa-file-lines'],
    'DIBATALKAN' => ['Dibatalkan', '#64748b', 'rgba(100,116,139,.12)', 'fa-ban'],
];
?>
<style>
.rpl-page{display:flex;flex-direction:column;gap:20px}.rpl-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.rpl-eyebrow{margin:0 0 5px;color:var(--primary);font-size:.76rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.rpl-title{margin:0;font-size:1.55rem;color:var(--text-main)}.rpl-subtitle{margin:7px 0 0;color:var(--text-muted);font-size:.9rem}.rpl-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.rpl-stat{position:relative;padding:18px 20px;overflow:hidden}.rpl-stat:after{content:"";position:absolute;right:-18px;bottom:-26px;width:80px;height:80px;border-radius:50%;background:var(--primary);opacity:.06}.rpl-stat-label{display:flex;align-items:center;gap:7px;color:var(--text-muted);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.035em}.rpl-stat-value{display:block;margin-top:9px;font-size:1.65rem;line-height:1.1;color:var(--text-main)}.rpl-panel{padding:0;overflow:hidden}.rpl-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;border-bottom:1px solid var(--border)}.rpl-panel-head h3{margin:0;font-size:1.02rem;color:var(--text-main)}.rpl-panel-head p{margin:4px 0 0;color:var(--text-muted);font-size:.8rem}.rpl-form{padding:20px}.rpl-form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px}.rpl-field{grid-column:span 4;display:flex;flex-direction:column;gap:7px;color:var(--text-main);font-size:.82rem;font-weight:650}.rpl-field.span-6{grid-column:span 6}.rpl-field.span-12{grid-column:1/-1}.rpl-required{color:#ef4444}.rpl-field .form-control{width:100%;min-height:42px}.rpl-field textarea.form-control{min-height:82px;resize:vertical}.rpl-form-actions{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding-top:2px}.rpl-help{margin-right:auto;color:var(--text-muted);font-size:.77rem}.rpl-table-wrap{overflow-x:auto}.rpl-table{width:100%;border-collapse:collapse;font-size:.84rem}.rpl-table th{padding:11px 14px;background:var(--surface-hover);color:var(--text-muted);font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;text-align:left;white-space:nowrap;border-bottom:1px solid var(--border)}.rpl-table td{padding:14px;border-bottom:1px solid var(--border-subtle);vertical-align:middle;color:var(--text-main)}.rpl-table tbody tr:last-child td{border-bottom:0}.rpl-code{display:inline-block;padding:3px 7px;border:1px solid var(--border);border-radius:6px;background:var(--surface-hover);font:700 .76rem monospace}.rpl-primary-text{display:block;margin-top:5px;font-weight:650}.rpl-muted{color:var(--text-muted);font-size:.76rem}.rpl-number{text-align:center!important;font-variant-numeric:tabular-nums}.rpl-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;font-size:.72rem;font-weight:750;white-space:nowrap}.rpl-decision{display:grid;grid-template-columns:minmax(150px,1fr) auto auto;gap:7px;min-width:360px}.rpl-decision .form-control{min-height:35px}.rpl-empty{padding:36px 20px;text-align:center;color:var(--text-muted)}.rpl-empty i{display:block;margin-bottom:10px;font-size:1.6rem;opacity:.55}.rpl-row-actions{display:flex;gap:6px;white-space:nowrap}.rpl-pagination{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 16px;border-top:1px solid var(--border);background:var(--surface-hover)}.rpl-pagination-info{color:var(--text-muted);font-size:.76rem}.rpl-pagination-links{display:flex;align-items:center;gap:5px}.rpl-page-link{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 9px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-main);font-size:.76rem;font-weight:700;text-decoration:none}.rpl-page-link:hover{border-color:var(--primary);color:var(--primary);text-decoration:none}.rpl-page-link.active{border-color:var(--primary);background:var(--primary);color:var(--text-inverse);pointer-events:none}.rpl-page-link.disabled{opacity:.4;pointer-events:none}.rpl-edit-dialog{width:min(720px,calc(100vw - 24px));max-width:720px;max-height:calc(100vh - 40px);overflow-y:auto;overflow-x:hidden;box-sizing:border-box;border:1px solid var(--border);border-radius:14px;padding:0;background:var(--card-bg);color:var(--text-main);box-shadow:var(--shadow-lg)}.rpl-edit-dialog::backdrop{background:rgba(0,0,0,.72);backdrop-filter:blur(3px)}.rpl-edit-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}.rpl-edit-head h3{margin:0;font-size:1rem}.rpl-edit-close{border:0;background:transparent;color:var(--text-muted);cursor:pointer;font-size:1.1rem}.rpl-edit-form{padding:20px;box-sizing:border-box}.rpl-edit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.rpl-edit-grid .rpl-field{grid-column:auto;min-width:0}.rpl-edit-grid .span-2{grid-column:1/-1}.rpl-edit-grid .form-control{display:block;width:100%;min-width:0;height:42px;box-sizing:border-box;padding:9px 13px;border:1px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font:inherit}.rpl-edit-grid .form-control:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(var(--primary-rgb),.12)}.rpl-edit-grid .form-control:disabled{background:var(--surface-hover);color:var(--text-muted);opacity:1;cursor:not-allowed}.rpl-edit-grid select.form-control{padding-right:34px}.rpl-edit-grid textarea.form-control{height:auto;min-height:82px;padding-top:11px;padding-bottom:11px}.rpl-edit-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}.rpl-semester{margin:0 20px 18px;border:1px solid var(--border);border-radius:10px;overflow:hidden}.rpl-semester:first-of-type{margin-top:20px}.rpl-semester-head{display:flex;justify-content:space-between;align-items:center;padding:12px 15px;background:var(--surface-hover)}.rpl-semester-head h4{margin:0;font-size:.9rem}.rpl-sks-pill{padding:4px 9px;border-radius:999px;background:rgba(99,102,241,.12);color:var(--primary);font-size:.72rem;font-weight:750}.rpl-plan-table{table-layout:fixed}.rpl-plan-table th:nth-child(1){width:12%}.rpl-plan-table th:nth-child(2){width:45%}.rpl-plan-table th:nth-child(3){width:17%}.rpl-plan-table th:nth-child(4){width:8%}.rpl-plan-table th:nth-child(5){width:18%}@media(max-width:1050px){.rpl-summary{grid-template-columns:repeat(2,1fr)}.rpl-field{grid-column:span 6}}@media(max-width:680px){.rpl-header{flex-direction:column}.rpl-summary{grid-template-columns:1fr 1fr}.rpl-field,.rpl-field.span-6{grid-column:1/-1}.rpl-form-actions{align-items:stretch;flex-direction:column}.rpl-help{margin:0}.rpl-form-actions .btn{width:100%;justify-content:center}.rpl-decision{grid-template-columns:1fr;min-width:230px}.rpl-pagination{align-items:flex-start;flex-direction:column}.rpl-pagination-links{width:100%;overflow-x:auto;padding-bottom:2px}.rpl-edit-grid{grid-template-columns:1fr}.rpl-edit-grid .span-2{grid-column:auto}.rpl-semester{margin:0 12px 14px}.rpl-semester:first-of-type{margin-top:14px}.rpl-plan-table,.rpl-plan-table tbody,.rpl-plan-table tr,.rpl-plan-table td{display:block;width:100%}.rpl-plan-table thead{display:none}.rpl-plan-table tbody{padding:4px 12px}.rpl-plan-table tr{display:grid;grid-template-columns:1fr auto;gap:8px 12px;padding:13px 0;border-bottom:1px solid var(--border-subtle)}.rpl-plan-table tbody tr:last-child{border-bottom:0}.rpl-plan-table td{padding:0;border:0;text-align:left!important}.rpl-plan-table td:nth-child(1){grid-column:1}.rpl-plan-table td:nth-child(2){grid-column:1/-1;font-size:.9rem}.rpl-plan-table td:nth-child(3),.rpl-plan-table td:nth-child(4){display:flex;align-items:center;gap:6px;color:var(--text-muted);font-size:.76rem}.rpl-plan-table td:nth-child(3):before{content:'Semester kurikulum:'}.rpl-plan-table td:nth-child(4):before{content:'SKS:'}.rpl-plan-table td:nth-child(5){grid-column:1/-1}.rpl-plan-table .rpl-status{font-size:.68rem}}
.rpl-field.metric{grid-column:span 3}.rpl-form .rpl-field{min-width:0}.rpl-form .form-control{display:block;width:100%;min-width:0;height:42px;box-sizing:border-box;padding:9px 13px;border:1px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font:inherit}.rpl-form .form-control:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(var(--primary-rgb),.12)}.rpl-form select.form-control{padding-right:34px}.rpl-form textarea.form-control{height:auto;min-height:82px;padding-top:11px;padding-bottom:11px}.rpl-semester-actions{display:flex;align-items:center;gap:8px}.rpl-print-plan{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-main);font-size:.72rem;font-weight:700;text-decoration:none}.rpl-print-plan:hover{border-color:var(--primary);color:var(--primary);text-decoration:none}.rpl-swal-popup{border:1px solid var(--border)!important;border-radius:14px!important}.rpl-swal-title{font-size:1.2rem!important}.rpl-swal-confirm,.rpl-swal-cancel{border-radius:8px!important;font-weight:700!important;box-shadow:none!important}.rpl-swal-cancel{background:var(--surface-hover)!important;color:var(--text-main)!important;border:1px solid var(--border)!important}.rpl-plan-header-controls{display:flex;align-items:center;flex-wrap:wrap;gap:10px}.rpl-scheme-form{display:inline-flex;align-items:center;gap:7px;margin:0}.rpl-scheme-select{height:34px;padding:4px 10px;font-size:.78rem;font-weight:700;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text-main)}.rpl-move-btn{padding:5px 10px;font-size:.74rem;display:inline-flex;align-items:center;gap:5px;border-radius:6px;font-weight:650}.rpl-course-multiselect{border:1px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);padding:10px 12px;max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:6px}.rpl-course-item{display:flex;align-items:flex-start;gap:10px;padding:7px 9px;border-radius:6px;cursor:pointer;user-select:none;transition:background .15s ease}.rpl-course-item:hover{background:var(--surface-hover)}.rpl-course-item input[type="checkbox"]{margin-top:3px;cursor:pointer;accent-color:var(--primary);width:16px;height:16px}.rpl-course-item-info{display:flex;flex-direction:column;gap:2px}.rpl-course-item-title{font-size:.82rem;font-weight:700;color:var(--text-main)}.rpl-course-item-meta{font-size:.73rem;color:var(--text-muted)}.rpl-ms-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}.rpl-ms-btn{border:0;background:none;color:var(--primary);cursor:pointer;font-size:.74rem;font-weight:700;padding:2px 4px;border-radius:4px}.rpl-ms-btn:hover{text-decoration:underline}@media(max-width:1050px){.rpl-field.metric{grid-column:span 6}}@media(max-width:680px){.rpl-field.metric{grid-column:1/-1}.rpl-print-plan span{display:none}}
</style>

<div class="rpl-page">
    <header class="rpl-header">
        <div>
            <p class="rpl-eyebrow">Rekognisi Pembelajaran Lampau</p>
            <h2 class="rpl-title"><?= htmlspecialchars($profile['nama_lengkap']) ?></h2>
            <p class="rpl-subtitle"><i class="fa-regular fa-id-card"></i> <?= htmlspecialchars($profile['nim']) ?> &nbsp;&bull;&nbsp; <i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($profile['kurikulum_nama']) ?></p>
        </div>
        <?php if ($role !== 'Mahasiswa'): ?><a class="btn btn-secondary" href="<?= $base ?>/rpl"><i class="fa-solid fa-arrow-left"></i> Kembali</a><?php endif; ?>
    </header>

    <section class="rpl-summary">
        <?php
        $stats = [
            ['Total SKS Kurikulum', $summary['total_sks'], 'fa-layer-group'],
            ['SKS Diakui', $summary['sks_diakui'], 'fa-circle-check'],
            ['SKS Tersisa', $summary['sks_sisa'], 'fa-book'],
            ['Masa Studi', $profile['skema_semester'] ? $profile['skema_semester'] . ' Semester' : 'Belum ditetapkan', 'fa-calendar-days'],
        ];
        foreach ($stats as $stat): ?>
            <div class="card rpl-stat"><span class="rpl-stat-label"><i class="fa-solid <?= $stat[2] ?>"></i><?= $stat[0] ?></span><strong class="rpl-stat-value"><?= $stat[1] ?></strong></div>
        <?php endforeach; ?>
    </section>

    <?php if ($isAdmin): ?>
    <section class="card rpl-panel">
        <div class="rpl-panel-head"><div><h3><i class="fa-solid fa-plus-circle" style="color:var(--primary)"></i> Ajukan Konversi Mata Kuliah</h3><p>Masukkan pembelajaran asal dan pilih mata kuliah tujuan yang setara.</p></div></div>
        <?php if (empty($availableCourses)): ?>
            <div class="rpl-empty"><i class="fa-solid fa-circle-info"></i><?= empty($courses) ? 'Belum ada mata kuliah yang diaktifkan pada Pengaturan MK RPL.' : 'Semua mata kuliah yang tersedia sudah pernah diajukan.' ?></div>
        <?php else: ?>
        <form class="rpl-form" method="post" action="<?= $base ?>/rpl/conversion/store" id="formRplConversion">
            <input type="hidden" name="rpl_mahasiswa_id" value="<?= $profile['id'] ?>">
            <div class="rpl-form-grid">
                <div class="rpl-field span-12">
                    <div class="rpl-ms-toolbar">
                        <span>Pilih Mata Kuliah Tujuan (Bisa Lebih dari 1) <span class="rpl-required">*</span></span>
                        <div>
                            <button type="button" class="rpl-ms-btn" onclick="toggleAllRplCourses(true)">Pilih Semua</button>
                            <span style="color:var(--text-muted);font-size:.74rem">|</span>
                            <button type="button" class="rpl-ms-btn" onclick="toggleAllRplCourses(false)">Batal Semua</button>
                        </div>
                    </div>
                    <div class="rpl-course-multiselect" id="rplCourseMultiselect">
                        <?php foreach ($availableCourses as $mk): $sks = (int)$mk['sks_teori'] + (int)$mk['sks_praktek']; ?>
                            <label class="rpl-course-item">
                                <input type="checkbox" name="mata_kuliah_ids[]" value="<?= (int)$mk['id'] ?>" class="rpl-course-cb" onchange="updateSelectedCount()">
                                <div class="rpl-course-item-info">
                                    <span class="rpl-course-item-title"><?= htmlspecialchars($mk['kode'] . ' — ' . $mk['nama']) ?></span>
                                    <span class="rpl-course-item-meta">Semester kurikulum <?= (int)$mk['semester'] ?> &bull; <?= $sks ?> SKS &bull; Min. Nilai <?= (float)$mk['minimal_nilai_angka'] ?> &bull; Min. Ekuivalensi <?= (float)$mk['minimal_persentase_ekuivalensi'] ?>%</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div id="rplSelectedCounter" style="font-size:.76rem;color:var(--primary);font-weight:700;margin-top:2px">0 mata kuliah dipilih</div>
                </div>

                <label class="rpl-field span-6"><span>Nama MK/pembelajaran asal <span class="rpl-required">*</span></span><input class="form-control" name="nama_asal" placeholder="Contoh: Dasar Pemrograman" required></label>
                <label class="rpl-field span-6">Institusi asal<input class="form-control" name="institusi_asal" placeholder="Nama perguruan tinggi/lembaga"></label>
                <label class="rpl-field">Kode MK asal<input class="form-control" name="kode_asal" placeholder="Contoh: IF101"></label>
                <label class="rpl-field"><span>Dasar rekognisi <span class="rpl-required">*</span></span><select class="form-control" name="dasar_rekognisi"><option value="PENDIDIKAN_FORMAL">Pendidikan formal</option><option value="PENGALAMAN_KERJA">Pengalaman kerja</option><option value="PELATIHAN">Pelatihan</option><option value="SERTIFIKASI">Sertifikasi</option><option value="PORTOFOLIO">Portofolio</option></select></label>
                <label class="rpl-field metric"><span>SKS asal <span class="rpl-required">*</span></span><input class="form-control" type="number" min="0" name="sks_asal" placeholder="0" required></label>
                <label class="rpl-field metric">Nilai asal<input class="form-control" name="nilai_asal" placeholder="Contoh: A atau 85"></label>
                <label class="rpl-field metric"><span>Nilai angka konversi <span class="rpl-required">*</span></span><input class="form-control" type="number" min="0" max="100" step="0.01" name="nilai_angka" placeholder="0–100" required></label>
                <label class="rpl-field metric"><span>Ekuivalensi <span class="rpl-required">*</span></span><div style="position:relative"><input class="form-control" style="padding-right:34px" type="number" min="0" max="100" step="0.01" name="persentase_ekuivalensi" value="100" required><span style="position:absolute;right:13px;top:11px;color:var(--text-muted)">%</span></div></label>
                <label class="rpl-field span-12">Catatan pengajuan<textarea class="form-control" name="catatan_pengaju" placeholder="Tambahkan informasi pendukung bila diperlukan"></textarea></label>
                <div class="rpl-form-actions" style="grid-column:1/-1"><span class="rpl-help"><i class="fa-solid fa-shield-halved"></i> Pengajuan akan menunggu keputusan Kaprodi.</span><button class="btn btn-primary" id="btnSubmitConversion"><i class="fa-solid fa-paper-plane"></i> Ajukan Konversi</button></div>
            </div>
        </form>
        <script>
        function toggleAllRplCourses(check) {
            document.querySelectorAll('.rpl-course-cb').forEach(cb => cb.checked = check);
            updateSelectedCount();
        }
        function updateSelectedCount() {
            const count = document.querySelectorAll('.rpl-course-cb:checked').length;
            const counter = document.getElementById('rplSelectedCounter');
            if (counter) counter.textContent = `${count} mata kuliah dipilih`;
        }
        document.getElementById('formRplConversion')?.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.rpl-course-cb:checked').length;
            if (checked === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Pilih Mata Kuliah',
                    text: 'Silakan centang minimal 1 mata kuliah tujuan konversi.',
                    icon: 'warning',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
        </script>
        <?php endif; ?>

    </section>
    <?php endif; ?>

    <section class="card rpl-panel" id="hasil-konversi">
        <div class="rpl-panel-head"><div><h3>Hasil Konversi</h3><p><?= count($conversions) ?> mata kuliah diajukan dalam rekognisi mahasiswa ini.</p></div></div>
        <?php if (empty($conversions)): ?><div class="rpl-empty"><i class="fa-regular fa-folder-open"></i>Belum ada pengajuan konversi.</div><?php else: ?>
        <div class="rpl-table-wrap"><table class="rpl-table"><thead><tr><th>MK tujuan</th><th>Pembelajaran asal</th><th>Hasil nilai</th><th class="rpl-number">SKS</th><th class="rpl-number">Kesetaraan</th><th>Status</th><?php if ($isApprover): ?><th>Keputusan Kaprodi</th><?php endif; ?><?php if ($isSuperAdmin): ?><th>Aksi</th><?php endif; ?></tr></thead><tbody>
        <?php foreach ($pagedConversions as $c): $badge = $statusStyles[$c['status']] ?? [$c['status'], '#64748b', 'rgba(100,116,139,.12)', 'fa-circle']; ?>
            <tr><td><span class="rpl-code"><?= htmlspecialchars($c['mk_kode']) ?></span><span class="rpl-primary-text"><?= htmlspecialchars($c['mk_nama']) ?></span><span class="rpl-muted">Semester kurikulum <?= (int)$c['semester'] ?></span></td><td><span class="rpl-primary-text" style="margin-top:0"><?= htmlspecialchars($c['nama_asal']) ?></span><span class="rpl-muted"><?= htmlspecialchars(trim(($c['kode_asal'] ?? '') . ' • ' . ($c['institusi_asal'] ?? ''), ' •')) ?></span></td><td><strong><?= number_format((float)$c['nilai_angka'], 2) ?></strong> <i class="fa-solid fa-arrow-right" style="font-size:.65rem;color:var(--text-muted)"></i> <strong style="color:var(--primary)"><?= htmlspecialchars($c['nilai_huruf']) ?></strong><div class="rpl-muted">Bobot <?= number_format((float)$c['bobot_mutu'], 2) ?></div></td><td class="rpl-number"><strong><?= (int)$c['sks_diakui'] ?></strong></td><td class="rpl-number"><strong><?= number_format((float)$c['persentase_ekuivalensi'], 0) ?>%</strong></td><td><span class="rpl-status" style="color:<?= $badge[1] ?>;background:<?= $badge[2] ?>"><i class="fa-solid <?= $badge[3] ?>"></i><?= $badge[0] ?></span><?php if ($c['catatan_kaprodi']): ?><div class="rpl-muted" style="margin-top:7px;max-width:180px"><?= htmlspecialchars($c['catatan_kaprodi']) ?></div><?php endif; ?></td>
            <?php if ($isApprover): ?><td><?php if ($c['status'] === 'DIAJUKAN'): ?><form class="rpl-decision" method="post" action="<?= $base ?>/rpl/conversion/decide"><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="rpl_mahasiswa_id" value="<?= $profile['id'] ?>"><input class="form-control" name="catatan" placeholder="Catatan keputusan"><button class="btn btn-sm btn-primary" name="status" value="DISETUJUI" title="Setujui"><i class="fa-solid fa-check"></i> Setujui</button><button class="btn btn-sm btn-danger" name="status" value="DITOLAK" title="Tolak"><i class="fa-solid fa-xmark"></i></button></form><?php else: ?><span class="rpl-muted">Keputusan sudah ditetapkan</span><?php endif; ?></td><?php endif; ?>
            <?php if ($isSuperAdmin): $editData=['id'=>(int)$c['id'],'kode_asal'=>$c['kode_asal']??'','nama_asal'=>$c['nama_asal']??'','institusi_asal'=>$c['institusi_asal']??'','sks_asal'=>(int)$c['sks_asal'],'nilai_asal'=>$c['nilai_asal']??'','nilai_angka'=>(float)$c['nilai_angka'],'persentase_ekuivalensi'=>(float)$c['persentase_ekuivalensi'],'dasar_rekognisi'=>$c['dasar_rekognisi'],'catatan_pengaju'=>$c['catatan_pengaju']??'','mk'=>$c['mk_kode'].' — '.$c['mk_nama']]; ?><td><div class="rpl-row-actions"><button type="button" class="btn btn-sm btn-secondary" data-conversion="<?= htmlspecialchars(json_encode($editData,JSON_UNESCAPED_UNICODE),ENT_QUOTES,'UTF-8') ?>" onclick="openRplEdit(this)" title="Edit data"><i class="fa-solid fa-pen"></i></button><form method="post" action="<?= $base ?>/rpl/conversion/delete" class="rpl-delete-form"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="rpl_mahasiswa_id" value="<?= (int)$profile['id'] ?>"><button type="button" class="btn btn-sm btn-danger" onclick="confirmRplDelete(this,'<?= htmlspecialchars($c['mk_kode'],ENT_QUOTES,'UTF-8') ?>')" title="Hapus data"><i class="fa-solid fa-trash"></i></button></form></div></td><?php endif; ?></tr>
        <?php endforeach; ?></tbody></table></div>
        <?php if ($conversionTotalPages > 1): $pageStart=max(1,$conversionPage-2);$pageEnd=min($conversionTotalPages,$conversionPage+2); ?>
            <nav class="rpl-pagination" aria-label="Navigasi hasil konversi">
                <span class="rpl-pagination-info">Menampilkan <?= $conversionOffset+1 ?>–<?= min($conversionOffset+$conversionPerPage,$conversionTotal) ?> dari <?= $conversionTotal ?> data</span>
                <div class="rpl-pagination-links">
                    <a class="rpl-page-link <?= $conversionPage<=1?'disabled':'' ?>" href="<?= $base ?>/rpl/detail?id=<?= (int)$profile['id'] ?>&konversi_page=<?= max(1,$conversionPage-1) ?>#hasil-konversi" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php for($page=$pageStart;$page<=$pageEnd;$page++): ?><a class="rpl-page-link <?= $page===$conversionPage?'active':'' ?>" href="<?= $base ?>/rpl/detail?id=<?= (int)$profile['id'] ?>&konversi_page=<?= $page ?>#hasil-konversi"><?= $page ?></a><?php endfor; ?>
                    <a class="rpl-page-link <?= $conversionPage>=$conversionTotalPages?'disabled':'' ?>" href="<?= $base ?>/rpl/detail?id=<?= (int)$profile['id'] ?>&konversi_page=<?= min($conversionTotalPages,$conversionPage+1) ?>#hasil-konversi" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </nav>
        <?php endif; endif; ?>
    </section>

    <?php if ($isSuperAdmin): ?>
    <dialog class="rpl-edit-dialog" id="rplEditDialog">
        <div class="rpl-edit-head"><h3>Edit Data Konversi</h3><button type="button" class="rpl-edit-close" onclick="document.getElementById('rplEditDialog').close()" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button></div>
        <form class="rpl-edit-form" method="post" action="<?= $base ?>/rpl/conversion/update" id="rplEditForm">
            <input type="hidden" name="id" id="editConversionId">
            <input type="hidden" name="rpl_mahasiswa_id" value="<?= (int)$profile['id'] ?>">
            <div class="rpl-edit-grid">
                <label class="rpl-field span-2">Mata kuliah tujuan<input class="form-control" id="editMkTarget" disabled></label>
                <label class="rpl-field">Kode MK asal<input class="form-control" name="kode_asal" id="editKodeAsal"></label>
                <label class="rpl-field"><span>Nama pembelajaran asal <span class="rpl-required">*</span></span><input class="form-control" name="nama_asal" id="editNamaAsal" required></label>
                <label class="rpl-field span-2">Institusi asal<input class="form-control" name="institusi_asal" id="editInstitusiAsal"></label>
                <label class="rpl-field"><span>SKS asal <span class="rpl-required">*</span></span><input class="form-control" type="number" min="0" name="sks_asal" id="editSksAsal" required></label>
                <label class="rpl-field">Nilai asal<input class="form-control" name="nilai_asal" id="editNilaiAsal"></label>
                <label class="rpl-field"><span>Nilai angka <span class="rpl-required">*</span></span><input class="form-control" type="number" min="0" max="100" step="0.01" name="nilai_angka" id="editNilaiAngka" required></label>
                <label class="rpl-field"><span>Ekuivalensi (%) <span class="rpl-required">*</span></span><input class="form-control" type="number" min="0" max="100" step="0.01" name="persentase_ekuivalensi" id="editEkuivalensi" required></label>
                <label class="rpl-field span-2">Dasar rekognisi<select class="form-control" name="dasar_rekognisi" id="editDasar"><option value="PENDIDIKAN_FORMAL">Pendidikan formal</option><option value="PENGALAMAN_KERJA">Pengalaman kerja</option><option value="PELATIHAN">Pelatihan</option><option value="SERTIFIKASI">Sertifikasi</option><option value="PORTOFOLIO">Portofolio</option></select></label>
                <label class="rpl-field span-2">Catatan pengajuan<textarea class="form-control" name="catatan_pengaju" id="editCatatan"></textarea></label>
            </div>
            <div class="rpl-edit-actions"><button type="button" class="btn btn-secondary" onclick="document.getElementById('rplEditDialog').close()">Batal</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button></div>
        </form>
    </dialog>
    <script>
    function openRplEdit(button) {
        const data = JSON.parse(button.dataset.conversion);
        const fields = {editConversionId:'id',editMkTarget:'mk',editKodeAsal:'kode_asal',editNamaAsal:'nama_asal',editInstitusiAsal:'institusi_asal',editSksAsal:'sks_asal',editNilaiAsal:'nilai_asal',editNilaiAngka:'nilai_angka',editEkuivalensi:'persentase_ekuivalensi',editDasar:'dasar_rekognisi',editCatatan:'catatan_pengaju'};
        Object.entries(fields).forEach(([id,key]) => document.getElementById(id).value = data[key] ?? '');
        document.getElementById('rplEditDialog').showModal();
    }
    function confirmRplDelete(button, code) {
        const form = button.closest('form');
        const dark = document.documentElement.getAttribute('data-theme') !== 'light';
        Swal.fire({title:'Hapus data konversi?',text:`Konversi ${code} akan dihapus dan rencana studi dihitung ulang.`,icon:'warning',background:dark?'#111114':'#ffffff',color:dark?'#f4f4f5':'#18181b',showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,customClass:{popup:'rpl-swal-popup',title:'rpl-swal-title',confirmButton:'rpl-swal-confirm',cancelButton:'rpl-swal-cancel'}}).then(result => {if(result.isConfirmed) form.submit();});
    }
    </script>
    <?php endif; ?>

    <section class="card rpl-panel" id="rencana-studi">
        <div class="rpl-panel-head">
            <div>
                <h3>Rencana Mata Kuliah</h3>
                <p>Pembagian mata kuliah yang masih harus ditempuh.</p>
            </div>
            <div class="rpl-plan-header-controls">
                <?php if ($isAdmin || $isApprover): ?>
                <form class="rpl-scheme-form" method="post" action="<?= $base ?>/rpl/plan/update-scheme">
                    <input type="hidden" name="rpl_mahasiswa_id" value="<?= (int)$profile['id'] ?>">
                    <label for="skemaSemesterSelect" style="font-size:.78rem;font-weight:700;color:var(--text-muted);display:flex;align-items:center;gap:5px">
                        <i class="fa-solid fa-sliders"></i> Setting Skema:
                    </label>
                    <select id="skemaSemesterSelect" class="rpl-scheme-select" name="skema_semester" onchange="this.form.submit()">
                        <option value="3" <?= ((int)$profile['skema_semester'] === 3) ? 'selected' : '' ?>>3 Semester</option>
                        <option value="4" <?= ((int)$profile['skema_semester'] === 4) ? 'selected' : '' ?>>4 Semester</option>
                    </select>
                </form>
                <form method="post" action="<?= $base ?>/rpl/plan/rebuild" style="display:inline;margin:0" id="formRebuildPlan">
                    <input type="hidden" name="rpl_mahasiswa_id" value="<?= (int)$profile['id'] ?>">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="confirmRebuildPlan()" title="Hitung ulang pembagian MK otomatis">
                        <i class="fa-solid fa-arrows-rotate"></i> Reset Otomatis
                    </button>
                </form>
                <?php endif; ?>
                <?php if ($profile['skema_semester']): ?>
                    <span class="rpl-sks-pill"><i class="fa-solid fa-calendar-check" style="margin-right:4px"></i> <?= $profile['skema_semester'] ?> semester</span>
                <?php endif; ?>
            </div>
        </div>
        <?php if (empty($plan)): ?>
            <div class="rpl-empty"><i class="fa-regular fa-calendar"></i>Rencana studi akan terbentuk setelah SKS konversi memenuhi syarat skema 3 atau 4 semester.</div>
        <?php else: ?>
        <?php 
        $currentScheme = max(1, (int)($profile['skema_semester'] ?? 4));
        for ($sem = 1; $sem <= $currentScheme; $sem++): 
            $items = array_values(array_filter($plan, fn($p) => (int)$p['semester_rpl'] === $sem)); 
        ?>
            <div class="rpl-semester">
                <div class="rpl-semester-head">
                    <h4><i class="fa-solid fa-calendar-week" style="color:var(--primary);margin-right:6px"></i> Semester RPL <?= $sem ?></h4>
                    <div class="rpl-semester-actions">
                        <a class="rpl-print-plan" href="<?= $base ?>/rpl/plan/print?id=<?= (int)$profile['id'] ?>&semester=<?= $sem ?>" target="_blank" title="Cetak KRS Semester <?= $sem ?>">
                            <i class="fa-solid fa-print"></i><span>Cetak KRS</span>
                        </a>
                        <span class="rpl-sks-pill"><?= array_sum(array_column($items, 'sks')) ?> SKS</span>
                    </div>
                </div>
                <div class="rpl-table-wrap">
                    <table class="rpl-table rpl-plan-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata kuliah</th>
                                <th class="rpl-number">Semester kurikulum</th>
                                <th class="rpl-number">SKS</th>
                                <th>Status</th>
                                <?php if ($isAdmin || $isApprover): ?><th style="width:16%;text-align:center">Aksi / Pindah</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="<?= ($isAdmin || $isApprover) ? 6 : 5 ?>" class="rpl-muted" style="text-align:center;padding:16px"><i class="fa-regular fa-circle-pause" style="margin-right:5px"></i> Tidak ada mata kuliah di semester ini. Anda dapat memindahkan MK ke semester ini.</td></tr>
                        <?php else: ?>
                        <?php foreach ($items as $p): ?>
                            <tr>
                                <td><span class="rpl-code"><?= htmlspecialchars($p['kode']) ?></span></td>
                                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                <td class="rpl-number"><?= (int)$p['semester_kurikulum'] ?></td>
                                <td class="rpl-number"><strong><?= (int)$p['sks'] ?></strong></td>
                                <td><span class="rpl-status" style="color:#3b82f6;background:rgba(59,130,246,.12)"><i class="fa-regular fa-calendar-check"></i><?= htmlspecialchars($p['status']) ?></span></td>
                                <?php if ($isAdmin || $isApprover): ?>
                                <td style="text-align:center">
                                    <button type="button" class="btn btn-sm btn-secondary rpl-move-btn" onclick="openMoveCourseModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['kode'] . ' - ' . $p['nama']), ENT_QUOTES, 'UTF-8') ?>', <?= $sem ?>, <?= (int)$p['sks'] ?>)" title="Pindahkan MK ke semester lain">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Pindah
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endfor; endif; ?>
    </section>

    <?php if ($isAdmin || $isApprover): ?>
    <dialog class="rpl-edit-dialog" id="rplMoveDialog" style="max-width:480px">
        <div class="rpl-edit-head">
            <h3><i class="fa-solid fa-arrows-split-up-and-left" style="color:var(--primary);margin-right:7px"></i> Pindahkan Semester Mata Kuliah</h3>
            <button type="button" class="rpl-edit-close" onclick="document.getElementById('rplMoveDialog').close()" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form class="rpl-edit-form" method="post" action="<?= $base ?>/rpl/plan/move-course" id="rplMoveForm">
            <input type="hidden" name="rpl_mahasiswa_id" value="<?= (int)$profile['id'] ?>">
            <input type="hidden" name="plan_id" id="movePlanId">
            <div style="display:flex;flex-direction:column;gap:14px">
                <div style="padding:12px 14px;border-radius:8px;background:var(--surface-hover);border:1px solid var(--border)">
                    <div style="font-size:.76rem;color:var(--text-muted);font-weight:700;text-transform:uppercase">Mata Kuliah:</div>
                    <div id="moveCourseName" style="font-weight:700;font-size:.92rem;margin-top:3px;color:var(--text-main)"></div>
                    <div id="moveCourseMeta" style="font-size:.78rem;color:var(--text-muted);margin-top:2px"></div>
                </div>
                <label class="rpl-field span-12" style="grid-column:auto">
                    <span>Pindahkan ke Semester RPL: <span class="rpl-required">*</span></span>
                    <select class="form-control" name="target_semester" id="moveTargetSemester" required style="font-weight:700">
                        <?php for($i = 1; $i <= max(1, (int)($profile['skema_semester'] ?? 4)); $i++): ?>
                            <option value="<?= $i ?>">Semester RPL <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
            </div>
            <div class="rpl-edit-actions" style="margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rplMoveDialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Pemindahan</button>
            </div>
        </form>
    </dialog>
    <script>
    function openMoveCourseModal(planId, courseName, currentSem, sks) {
        document.getElementById('movePlanId').value = planId;
        document.getElementById('moveCourseName').textContent = courseName;
        document.getElementById('moveCourseMeta').textContent = `Saat ini: Semester RPL ${currentSem} • Beban: ${sks} SKS`;
        const select = document.getElementById('moveTargetSemester');
        select.value = currentSem;
        document.getElementById('rplMoveDialog').showModal();
    }
    function confirmRebuildPlan() {
        const dark = document.documentElement.getAttribute('data-theme') !== 'light';
        Swal.fire({
            title: 'Reset pembagian rencana studi?',
            text: 'Mata kuliah akan dihitung dan dibagi ulang secara otomatis berdasarkan beban SKS.',
            icon: 'question',
            background: dark ? '#111114' : '#ffffff',
            color: dark ? '#f4f4f5' : '#18181b',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Ya, reset pembagian',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rpl-swal-popup',
                title: 'rpl-swal-title',
                confirmButton: 'rpl-swal-confirm',
                cancelButton: 'rpl-swal-cancel'
            }
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('formRebuildPlan').submit();
            }
        });
    }
    </script>
    <?php endif; ?>

</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
