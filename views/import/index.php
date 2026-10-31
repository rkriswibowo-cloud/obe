<?php
$title = 'Import Data Master';
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';

$importResult = $_SESSION['import_result'] ?? null;
unset($_SESSION['import_result']);

$kurikulumList = $kurikulumList ?? [];
$mataKuliah = $mataKuliah ?? [];

$importTypes = [
    'cpl' => [
        'title' => 'CPL',
        'name' => 'Capaian Pembelajaran Lulusan',
        'description' => 'Impor kode dan rumusan CPL sebagai fondasi pemetaan kurikulum.',
        'icon' => 'fa-bullseye',
        'columns' => 'kode, rumusan, kategori, target_capaian',
        'requires_course' => false,
        'requires_kurikulum' => true,
    ],
    'cpmk' => [
        'title' => 'CPMK',
        'name' => 'Capaian Pembelajaran Mata Kuliah',
        'description' => 'Impor CPMK beserta relasinya terhadap CPL untuk mata kuliah terpilih.',
        'icon' => 'fa-diagram-project',
        'columns' => 'kode_cpl, kode_cpmk, rumusan_cpmk',
        'requires_course' => true,
        'requires_kurikulum' => false,
    ],
    'sub_cpmk' => [
        'title' => 'Sub-CPMK',
        'name' => 'Kemampuan Akhir Tiap Tahapan',
        'description' => 'Impor rincian Sub-CPMK yang terhubung dengan CPMK mata kuliah.',
        'icon' => 'fa-list-check',
        'columns' => 'kode_cpmk, kode_sub_cpmk, rumusan_sub_cpmk, bobot, level_taksonomi, pengalaman_belajar, asesmen_penilaian',
        'requires_course' => true,
        'requires_kurikulum' => false,
    ],
];
ob_start();
?>
<style>
    .import-page {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    /* Ensure [hidden] is never overridden by display flex/grid */
    .import-page [hidden] {
        display: none !important;
    }
    
    /* Header */
    .import-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }
    .import-eyebrow {
        margin: 0 0 5px;
        color: var(--primary);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .import-title {
        margin: 0;
        font-size: 1.55rem;
        color: var(--text-main);
        font-weight: 700;
        letter-spacing: -.02em;
    }
    .import-subtitle {
        margin: 6px 0 0;
        color: var(--text-muted);
        font-size: .88rem;
        max-width: 700px;
        line-height: 1.55;
    }

    /* Alerts */
    .import-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        border-radius: var(--radius-md);
        font-size: .82rem;
        line-height: 1.55;
        border: 1px solid transparent;
    }
    .import-alert i {
        font-size: 1.05rem;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .import-alert-success {
        color: #10b981;
        background: rgba(16, 185, 129, .1);
        border-color: rgba(16, 185, 129, .25);
    }
    .import-alert-danger {
        color: #ef4444;
        background: rgba(239, 68, 68, .1);
        border-color: rgba(239, 68, 68, .25);
    }

    /* Guide Workflow Cards */
    .import-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    .import-step-card {
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--card-bg);
    }
    .import-step-num {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: rgba(var(--primary-rgb), .12);
        color: var(--primary);
        font-weight: 800;
        font-size: .85rem;
    }
    .import-step-body strong {
        display: block;
        font-size: .84rem;
        color: var(--text-main);
        margin-bottom: 3px;
    }
    .import-step-body span {
        display: block;
        color: var(--text-muted);
        font-size: .75rem;
        line-height: 1.45;
    }

    /* Tabs */
    .import-nav-tabs {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        align-self: flex-start;
    }
    .import-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: var(--text-muted);
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }
    .import-nav-btn:hover {
        color: var(--text-main);
        background: var(--surface-hover);
    }
    .import-nav-btn.active {
        color: var(--text-main);
        background: var(--card-bg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
    }

    /* Type Selector Pills */
    .type-selector-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .type-card-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--card-bg);
        color: var(--text-main);
        text-align: left;
        cursor: pointer;
        transition: all .18s ease;
    }
    .type-card-btn:hover {
        border-color: rgba(var(--primary-rgb), .45);
        background: var(--surface-hover);
        transform: translateY(-1px);
    }
    .type-card-btn.active {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), .06);
        box-shadow: 0 0 0 2px rgba(var(--primary-rgb), .12);
    }
    .type-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: rgba(var(--primary-rgb), .12);
        color: var(--primary);
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .type-card-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
    }
    .type-card-title {
        font-size: .88rem;
        font-weight: 750;
        color: var(--text-main);
    }
    .type-card-sub {
        font-size: .72rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .type-card-check {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1.5px solid var(--border);
        display: grid;
        place-items: center;
        font-size: .62rem;
        color: transparent;
        flex-shrink: 0;
        transition: all .15s ease;
    }
    .type-card-btn.active .type-card-check {
        border-color: var(--primary);
        background: var(--primary);
        color: #ffffff;
    }

    /* Main Workspace Panels */
    .import-workspace-card {
        padding: 0;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: var(--card-bg);
    }
    .import-workspace-split {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(320px, 1fr);
    }

    /* Left Form Area */
    .import-main-form {
        padding: 26px 30px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .workspace-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-subtle);
    }
    .workspace-head h2 {
        margin: 0 0 5px;
        font-size: 1.05rem;
        color: var(--text-main);
    }
    .workspace-head p {
        margin: 0;
        color: var(--text-muted);
        font-size: .78rem;
        line-height: 1.5;
    }
    .workspace-badge {
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(var(--primary-rgb), .12);
        color: var(--primary);
        font-size: .72rem;
        font-weight: 750;
        white-space: nowrap;
    }

    /* Form Fields Grid */
    .import-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .import-form-grid.with-course {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }
    .form-group-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .form-group-field label {
        font-size: .78rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .form-group-field .form-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--input-border);
        border-radius: var(--radius-sm);
        background: var(--input-bg);
        color: var(--text-main);
        padding: 9px 13px;
        font-size: .8rem;
    }

    /* Modern Dropzone File Picker */
    .modern-dropzone {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px 20px;
        border: 2px dashed var(--input-border);
        border-radius: var(--radius-md);
        background: var(--surface);
        cursor: pointer;
        transition: all .2s ease;
        min-height: 140px;
    }
    .modern-dropzone:hover, .modern-dropzone.has-file {
        border-color: var(--primary);
        background: rgba(var(--primary-rgb), .035);
    }
    .modern-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    .modern-dropzone-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(var(--primary-rgb), .1);
        color: var(--primary);
        display: grid;
        place-items: center;
        font-size: 1.25rem;
        margin-bottom: 10px;
    }
    .modern-dropzone strong {
        display: block;
        font-size: .82rem;
        color: var(--text-main);
        margin-bottom: 3px;
    }
    .modern-dropzone span.file-name-text {
        font-size: .74rem;
        color: var(--text-muted);
        max-width: 90%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Form Actions */
    .import-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 16px;
        border-top: 1px solid var(--border-subtle);
        margin-top: 4px;
    }
    .import-form-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 40px;
        padding: 0 18px;
        font-weight: 650;
        font-size: .8rem;
    }

    /* Right Reference Aside */
    .import-side-reference {
        padding: 24px;
        background: var(--surface-hover);
        border-left: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .import-side-reference h3 {
        margin: 0 0 5px;
        font-size: .9rem;
        color: var(--text-main);
    }
    .import-side-reference p {
        margin: 0;
        font-size: .75rem;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .ref-box {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .ref-label {
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
    }
    .ref-code-block {
        display: block;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--card-bg);
        color: var(--text-main);
        font-size: .73rem;
        font-family: monospace;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }
    .ref-checklist {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }
    .ref-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: .74rem;
        color: var(--text-muted);
        line-height: 1.45;
    }
    .ref-checklist li i {
        color: #10b981;
        margin-top: 3px;
        font-size: .72rem;
        flex-shrink: 0;
    }

    /* Text Import Layout */
    .text-split-workspace {
        display: grid;
        grid-template-columns: minmax(260px, .7fr) minmax(0, 1.3fr);
    }
    .text-left-config {
        padding: 24px;
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .text-left-config h3 {
        margin: 0 0 4px;
        font-size: .95rem;
    }
    .text-left-config p {
        margin: 0 0 10px;
        font-size: .76rem;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .text-right-editor {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .text-editor-title h3 {
        margin: 0 0 4px;
        font-size: .95rem;
    }
    .text-editor-title p {
        margin: 0;
        font-size: .76rem;
        color: var(--text-muted);
    }
    .csv-format-hint {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 14px;
        border-radius: var(--radius-sm);
        background: rgba(var(--primary-rgb), .07);
        border: 1px solid rgba(var(--primary-rgb), .2);
        color: var(--text-muted);
        font-size: .75rem;
    }
    .csv-format-hint i {
        color: var(--primary);
    }
    .csv-format-hint code {
        color: var(--text-main);
        font-weight: 700;
    }
    .modern-csv-textarea {
        min-height: 280px;
        resize: vertical;
        font-family: Consolas, "Liberation Mono", Menlo, monospace;
        font-size: .8rem;
        line-height: 1.55;
        padding: 12px;
        border: 1px solid var(--input-border);
        border-radius: var(--radius-sm);
        background: var(--input-bg);
        color: var(--text-main);
    }
    .text-editor-footer {
        display: flex;
        justify-content: flex-end;
    }

    /* Responsiveness Breakpoints */
    @media (max-width: 960px) {
        .import-steps {
            grid-template-columns: 1fr;
        }
        .import-workspace-split {
            grid-template-columns: 1fr;
        }
        .import-side-reference {
            border-left: 0;
            border-top: 1px solid var(--border);
        }
        .text-split-workspace {
            grid-template-columns: 1fr;
        }
        .text-left-config {
            border-right: 0;
            border-bottom: 1px solid var(--border);
        }
    }

    @media (max-width: 680px) {
        .import-header {
            flex-direction: column;
        }
        .type-selector-grid {
            grid-template-columns: 1fr;
        }
        .import-form-grid.with-course {
            grid-template-columns: 1fr;
        }
        .import-nav-tabs {
            width: 100%;
        }
        .import-nav-btn {
            flex: 1;
            justify-content: center;
        }
        .import-form-footer {
            flex-direction: column;
            align-items: stretch;
        }
        .import-form-footer .btn {
            width: 100%;
            justify-content: center;
        }
        .text-editor-footer .btn {
            width: 100%;
            justify-content: center;
        }
        .import-main-form, .import-side-reference, .text-left-config, .text-right-editor {
            padding: 18px 16px;
        }
    }
</style>

<div class="import-page">
    <header class="import-header">
        <div>
            <p class="import-eyebrow">Integrasi Kurikulum & OBE</p>
            <h1 class="import-title">Import Data Master</h1>
            <p class="import-subtitle">Tambahkan data CPL, CPMK, dan Sub-CPMK secara massal melalui berkas CSV atau tempel teks data CSV secara langsung.</p>
        </div>
    </header>

    <?php if ($importResult): ?>
        <?php if ($importResult['success_count'] > 0 && $importResult['error_count'] === 0): ?>
            <div class="import-alert import-alert-success" role="status">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Berhasil! <?= (int)$importResult['success_count'] ?> baris data <?= strtoupper($importResult['type']) ?> berhasil diimpor ke sistem.</strong>
                </div>
            </div>
        <?php elseif ($importResult['success_count'] > 0 && $importResult['error_count'] > 0): ?>
            <div class="import-alert" style="color:#d97706; background:rgba(245,158,11,.1); border-color:rgba(245,158,11,.3);" role="status">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong>Sebagian Berhasil: <?= (int)$importResult['success_count'] ?> baris berhasil diimpor, tetapi <?= (int)$importResult['error_count'] ?> baris dilewati.</strong>
                    <?php if (!empty($importResult['errors'])): ?>
                        <ul style="margin: 6px 0 0; padding-left: 18px; font-size: 0.76rem;">
                            <?php foreach (array_slice($importResult['errors'], 0, 5) as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                            <?php if (count($importResult['errors']) > 5): ?>
                                <li>... dan <?= count($importResult['errors']) - 5 ?> baris lainnya.</li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="import-alert import-alert-danger" role="alert">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>
                    <strong>Import tidak berhasil (0 baris data tersimpan). Periksa catatan kesalahan berikut:</strong>
                    <?php if (!empty($importResult['errors'])): ?>
                        <ul style="margin: 6px 0 0; padding-left: 18px; font-size: 0.76rem;">
                            <?php foreach (array_slice($importResult['errors'], 0, 8) as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="margin:4px 0 0; font-size:0.76rem;">Format kolom tidak sesuai dengan template atau data yang diunggah kosong.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="import-alert import-alert-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <strong>Import tidak dapat diproses:</strong><br>
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="import-steps" aria-label="Langkah-langkah import">
        <div class="import-step-card">
            <div class="import-step-num">1</div>
            <div class="import-step-body">
                <strong>Pilih Jenis Data</strong>
                <span>Pilih CPL, CPMK, atau Sub-CPMK sesuai hierarki kurikulum.</span>
            </div>
        </div>
        <div class="import-step-card">
            <div class="import-step-num">2</div>
            <div class="import-step-body">
                <strong>Unduh Format Template</strong>
                <span>Gunakan berkas template resmi agar urutan kolom sesuai standar sistem.</span>
            </div>
        </div>
        <div class="import-step-card">
            <div class="import-step-num">3</div>
            <div class="import-step-body">
                <strong>Unggah & Verifikasi</strong>
                <span>Sistem memvalidasi relasi kode secara otomatis dan memperbarui data.</span>
            </div>
        </div>
    </section>

    <div class="import-nav-tabs" role="tablist" aria-label="Metode import">
        <button type="button" class="import-nav-btn active" id="tab-file" role="tab" aria-selected="true" aria-controls="panel-file" data-tab="file">
            <i class="fa-solid fa-file-arrow-up"></i> Unggah Berkas CSV
        </button>
        <button type="button" class="import-nav-btn" id="tab-text" role="tab" aria-selected="false" aria-controls="panel-text" data-tab="text">
            <i class="fa-solid fa-paste"></i> Tempel Teks CSV
        </button>
    </div>

    <!-- Panel 1: Unggah CSV -->
    <section id="panel-file" role="tabpanel" aria-labelledby="tab-file">
        <div class="type-selector-grid" role="radiogroup" aria-label="Pilih jenis data">
            <?php foreach ($importTypes as $type => $item): ?>
                <button type="button" class="type-card-btn <?= $type === 'cpl' ? 'active' : '' ?>" data-import-type="<?= htmlspecialchars($type) ?>" role="radio" aria-checked="<?= $type === 'cpl' ? 'true' : 'false' ?>">
                    <div class="type-card-icon"><i class="fa-solid <?= $item['icon'] ?>"></i></div>
                    <div class="type-card-info">
                        <span class="type-card-title"><?= htmlspecialchars($item['title']) ?></span>
                        <span class="type-card-sub"><?= htmlspecialchars($item['name']) ?></span>
                    </div>
                    <div class="type-card-check"><i class="fa-solid fa-check"></i></div>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="import-workspace-card">
            <div class="import-workspace-split">
                <form class="import-main-form" action="<?= htmlspecialchars($baseUrl) ?>/import/upload" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="type" id="file-import-type" value="cpl">
                    
                    <header class="workspace-head">
                        <div>
                            <h2 id="upload-title">Import CPL</h2>
                            <p id="upload-description"><?= htmlspecialchars($importTypes['cpl']['description']) ?></p>
                        </div>
                        <span class="workspace-badge" id="selected-type-badge">CPL</span>
                    </header>

                    <div class="import-form-grid" id="upload-fields">
                        <div class="form-group-field" id="file-kurikulum-field">
                            <label for="file-kurikulum-id">Kurikulum Tujuan <span style="color:#ef4444">*</span></label>
                            <select class="form-control" name="kurikulum_id" id="file-kurikulum-id">
                                <option value="">-- Pilih Kurikulum --</option>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= (int)$kur['id'] ?>"><?= htmlspecialchars($kur['kode'] . ' — ' . $kur['nama'] . ' (' . ($kur['nama_prodi'] ?? 'Prodi') . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-field" id="file-mk-field" hidden>
                            <label for="file-mk-id">Mata Kuliah Tujuan <span style="color:#ef4444">*</span></label>
                            <select class="form-control" name="mk_id" id="file-mk-id">
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php foreach ($mataKuliah as $mk): ?>
                                    <option value="<?= (int)$mk['id'] ?>">
                                        <?= htmlspecialchars($mk['kode'] . ' — ' . $mk['nama'] . ' (' . ($mk['kurikulum_nama'] ?? 'Kurikulum ' . $mk['kurikulum_id']) . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-field" style="grid-column: 1/-1;">
                            <label>Berkas CSV / Excel (.xlsx) <span style="color:#ef4444">*</span></label>
                            <label class="modern-dropzone" id="filePickerLabel">
                                <input type="file" name="csv_file" accept=".csv,.xlsx,.xls,.txt,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required id="csvFileInput">
                                <div class="modern-dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <strong>Pilih atau Seret Berkas CSV / Excel ke Sini</strong>
                                <span class="file-name-text" id="fileNameDisplay">Format CSV atau Excel (.xlsx) dengan pemisah koma (,) atau titik-koma (;). Ukuran maks 5MB.</span>
                            </label>
                        </div>
                    </div>

                    <div class="import-form-footer">
                        <a class="btn btn-secondary" id="file-template-link" href="<?= htmlspecialchars($baseUrl) ?>/import/template?type=cpl">
                            <i class="fa-solid fa-download"></i> Unduh Format Template
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> <span id="file-submit-label">Import CPL</span>
                        </button>
                    </div>
                </form>

                <aside class="import-side-reference">
                    <div>
                        <h3>Format Kolom & Panduan</h3>
                        <p>Pastikan urutan baris pertama (header) sesuai spesifikasi di bawah ini:</p>
                    </div>

                    <div class="ref-box">
                        <span class="ref-label">Header Kolom</span>
                        <code class="ref-code-block" id="file-columns"><?= htmlspecialchars($importTypes['cpl']['columns']) ?></code>
                    </div>

                    <div class="ref-box">
                        <span class="ref-label">Ketentuan Validasi</span>
                        <ul class="ref-checklist">
                            <li><i class="fa-solid fa-circle-check"></i><span>Gunakan tanda koma (<code>,</code>) sebagai pemisah nilai kolom.</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>Header baris pertama tidak boleh diubah atau diganti namanya.</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>Jika kode sudah ada di sistem, deskripsi/rumusan akan diperbarui otomatis.</span></li>
                            <li><i class="fa-solid fa-circle-check"></i><span>Untuk CPMK/Sub-CPMK, pastikan kode CPL/CPMK induk sudah terdaftar di sistem.</span></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Panel 2: Tempel Teks CSV -->
    <section id="panel-text" role="tabpanel" aria-labelledby="tab-text" hidden>
        <div class="import-workspace-card">
            <form action="<?= htmlspecialchars($baseUrl) ?>/import/upload-text" method="post" class="text-split-workspace">
                <div class="text-left-config">
                    <div>
                        <h3>Konfigurasi Data</h3>
                        <p>Tentukan tipe data dan mata kuliah acuan sebelum memproses teks.</p>
                    </div>

                    <div class="form-group-field">
                        <label for="text-import-type">Jenis Data <span style="color:#ef4444">*</span></label>
                        <select class="form-control" name="type" id="text-import-type" required>
                            <option value="">-- Pilih Jenis Data --</option>
                            <option value="cpl">CPL (Capaian Pembelajaran Lulusan)</option>
                            <option value="cpmk">CPMK (Capaian Pembelajaran Mata Kuliah)</option>
                            <option value="sub_cpmk">Sub-CPMK (Kemampuan Akhir Tahapan)</option>
                        </select>
                    </div>

                    <div class="form-group-field" id="text-kurikulum-select" hidden>
                        <label for="text-kurikulum-id">Kurikulum Tujuan <span style="color:#ef4444">*</span></label>
                        <select class="form-control" name="kurikulum_id" id="text-kurikulum-id">
                            <option value="">-- Pilih Kurikulum --</option>
                            <?php foreach ($kurikulumList as $kur): ?>
                                <option value="<?= (int)$kur['id'] ?>"><?= htmlspecialchars($kur['kode'] . ' — ' . $kur['nama'] . ' (' . ($kur['nama_prodi'] ?? 'Prodi') . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-field" id="text-mk-select" hidden>
                        <label for="text-mk-id">Mata Kuliah Tujuan <span style="color:#ef4444">*</span></label>
                        <select class="form-control" name="mk_id" id="text-mk-id">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($mataKuliah as $mk): ?>
                                <option value="<?= (int)$mk['id'] ?>">
                                    <?= htmlspecialchars($mk['kode'] . ' — ' . $mk['nama'] . ' (' . ($mk['kurikulum_nama'] ?? 'Kurikulum ' . $mk['kurikulum_id']) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="text-right-editor">
                    <div class="text-editor-title">
                        <h3>Editor Teks CSV</h3>
                        <p>Tempelkan data CSV lengkap dengan baris header.</p>
                    </div>

                    <div class="csv-format-hint" id="csv-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Pilih jenis data pada menu sebelah kiri untuk melihat struktur kolom yang diperlukan.</span>
                    </div>

                    <textarea class="modern-csv-textarea" name="csv_text" id="csv-textarea" required spellcheck="false" placeholder="Pilih jenis data terlebih dahulu..."></textarea>

                    <div class="text-editor-footer">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-gears"></i> Proses Data CSV
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>


<script>
(function () {
    const importTypes = <?= json_encode(array_map(static function ($item) {
        return [
            'title' => $item['title'],
            'description' => $item['description'],
            'columns' => $item['columns'],
            'requiresCourse' => $item['requires_course'],
            'requiresKurikulum' => $item['requires_kurikulum'] ?? false,
        ];
    }, $importTypes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const tabs = document.querySelectorAll('[data-tab]');
    tabs.forEach(tab => tab.addEventListener('click', function () {
        tabs.forEach(item => { item.classList.toggle('active', item === this); item.setAttribute('aria-selected', item === this ? 'true' : 'false'); });
        document.getElementById('panel-file').hidden = this.dataset.tab !== 'file';
        document.getElementById('panel-text').hidden = this.dataset.tab !== 'text';
    }));

    const fileInput = document.getElementById('csvFileInput');
    const dropzone = document.getElementById('filePickerLabel');
    const fileName = document.getElementById('fileNameDisplay');
    if (fileInput && dropzone && fileName) {
        fileInput.addEventListener('change', function () {
            const selected = this.files && this.files[0];
            dropzone.classList.toggle('has-file', Boolean(selected));
            if (selected) {
                fileName.textContent = selected.name + ' (' + (selected.size / 1024).toFixed(1) + ' KB)';
            } else {
                fileName.textContent = 'Format CSV dengan pemisah koma (,) atau titik-koma (;). Ukuran maks 5MB.';
            }
        });
    }

    const fileType = document.getElementById('file-import-type');
    const fileKurField = document.getElementById('file-kurikulum-field');
    const fileKur = document.getElementById('file-kurikulum-id');
    const fileCourseField = document.getElementById('file-mk-field');
    const fileCourse = document.getElementById('file-mk-id');
    const uploadFields = document.getElementById('upload-fields');

    document.querySelectorAll('[data-import-type]').forEach(option => option.addEventListener('click', function () {
        const type = this.dataset.importType;
        const config = importTypes[type];
        document.querySelectorAll('[data-import-type]').forEach(item => {
            const active = item === this;
            item.classList.toggle('active', active);
            item.setAttribute('aria-checked', active ? 'true' : 'false');
        });
        fileType.value = type;

        if (fileKurField && fileKur) {
            fileKurField.hidden = !config.requiresKurikulum;
            fileKur.required = Boolean(config.requiresKurikulum);
        }
        if (fileCourseField && fileCourse) {
            fileCourseField.hidden = !config.requiresCourse;
            fileCourse.required = Boolean(config.requiresCourse);
        }

        uploadFields.classList.toggle('no-course', !config.requiresCourse && !config.requiresKurikulum);
        document.getElementById('upload-title').textContent = 'Import ' + config.title;
        document.getElementById('upload-description').textContent = config.description;
        document.getElementById('selected-type-badge').textContent = config.title;
        document.getElementById('file-columns').textContent = config.columns;
        document.getElementById('file-template-link').href = <?= json_encode(rtrim($baseUrl, '/')) ?> + '/import/template?type=' + encodeURIComponent(type);
        document.getElementById('file-submit-label').textContent = 'Import ' + config.title;
    }));

    const typeSelect = document.getElementById('text-import-type');
    const textKurField = document.getElementById('text-kurikulum-select');
    const textKurSelect = document.getElementById('text-kurikulum-id');
    const courseField = document.getElementById('text-mk-select');
    const courseSelect = document.getElementById('text-mk-id');
    const textarea = document.getElementById('csv-textarea');
    const hint = document.getElementById('csv-hint');
    const formats = {
        cpl: { 
            columns: 'kode,rumusan,kategori,target_capaian', 
            example: 'kode,rumusan,kategori,target_capaian\nCPL-01,"Mampu menerapkan pemikiran logis dan inovatif dalam memecahkan masalah rekayasa",Keterampilan Umum,70.00\nCPL-02,"Mampu menunjukkan kinerja mandiri dan profesional",Keterampilan Umum,75.00' 
        },
        cpmk: { 
            columns: 'kode_cpl,kode_cpmk,rumusan_cpmk', 
            example: 'kode_cpl,kode_cpmk,rumusan_cpmk\n"CPL-01, CPL-02",CPMK-01,"Mampu menjelaskan konsep dasar dan arsitektur sistem"\nCPL-02,CPMK-02,"Mampu merancang modul perangkat lunak berbasis standar industri"' 
        },
        sub_cpmk: { 
            columns: 'kode_cpmk,kode_sub_cpmk,rumusan_sub_cpmk,bobot,level_taksonomi,pengalaman_belajar,asesmen_penilaian', 
            example: 'kode_cpmk,kode_sub_cpmk,rumusan_sub_cpmk,bobot,level_taksonomi,pengalaman_belajar,asesmen_penilaian\nCPMK-01,Sub-CPMK 1.1,"Mampu mendefinisikan modul dan struktur data",15.00,"C3, A2, P4","Ceramah dan Diskusi","Pretes dan Kuis"\nCPMK-01,Sub-CPMK 1.2,"Mampu menganalisis kebutuhan sistem",15.00,"C4, A3, P4","Studi Kasus","Tugas Proyek"' 
        }
    };

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            const isCpl = this.value === 'cpl';
            const needsCourse = this.value === 'cpmk' || this.value === 'sub_cpmk';

            if (textKurField && textKurSelect) {
                textKurField.hidden = !isCpl;
                textKurSelect.required = isCpl;
            }
            if (courseField && courseSelect) {
                courseField.hidden = !needsCourse;
                courseSelect.required = needsCourse;
            }

            const format = formats[this.value];
            textarea.placeholder = format ? format.example : 'Pilih jenis data terlebih dahulu...';
            hint.innerHTML = format 
                ? '<i class="fa-solid fa-circle-info"></i><span>Struktur kolom: <code>' + format.columns + '</code></span>' 
                : '<i class="fa-solid fa-circle-info"></i><span>Pilih jenis data pada menu sebelah kiri untuk melihat struktur kolom yang diperlukan.</span>';
        });
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
