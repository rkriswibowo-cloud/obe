<?php
// views/rps/document.php
// Tampilan Dokumen Rencana Pembelajaran Semester (RPS) Resmi
// Didesain persis menyerupai format dokumen universitas "RPS PSSI Teori Rev 3.0.pdf"

$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
$rubrikNamaById = [];
foreach (($rubrikMaster ?? []) as $rubrikItem) {
    $rubrikNamaById[(int)$rubrikItem['id']] = $rubrikItem['nama'];
}

// Siapkan Logo Universitas Anwar Medika
$logoFile = __DIR__ . '/../../public/assets/images/logo_uam.png';
$logoSrc = file_exists($logoFile) 
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) 
    : ($baseUrl . '/assets/images/logo_uam.png');

// Susun daftar prodi pada header dari isian Identitas RPS.
$headerProdiRaw = trim($rps['program_studi'] ?? 'S1 Sistem Informasi');
$headerProdiList = preg_split(
    '/(?:\r?\n|\s*[,;|]\s*|\s+(?=(?:D[1-4]|S[1-3])\s))/iu',
    $headerProdiRaw,
    -1,
    PREG_SPLIT_NO_EMPTY
);
$headerProdiList = $headerProdiList ?: [$headerProdiRaw];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPS - <?= htmlspecialchars($mk['kode']) ?> <?= htmlspecialchars($mk['nama']) ?> (Rev <?= htmlspecialchars($rps['versi'] ?? '3/0') ?>)</title>
    <!-- FontAwesome for UI buttons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Script Anti-FOUC untuk Dokumen RPS -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                document.documentElement.classList.add(savedTheme);
            } catch(e) {}
        })();
    </script>

    <style>
        /* ==========================================================================
           1. SCREEN & ACTION TOOLBAR STYLES
           ========================================================================== */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #2b3035;
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
            color: #000000;
            line-height: 1.35;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.2s ease;
        }
        body.theme-dark {
            background-color: #1e293b;
        }
        body.theme-light {
            background-color: #cbd5e1;
        }

        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .toolbar-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.05rem;
            font-weight: 600;
        }
        .toolbar-badge {
            background: #0284c7;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .toolbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-tb {
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-tb-back {
            background: #475569;
            color: #ffffff;
        }
        .btn-tb-back:hover {
            background: #64748b;
        }
        .btn-tb-print {
            background: #10b981;
            color: #ffffff;
        }
        .btn-tb-print:hover {
            background: #059669;
        }
        .btn-tb-mode {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-tb-mode:hover {
            background: #475569;
            color: #ffffff;
        }
        body.theme-light .no-print-toolbar {
            background: #ffffff;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body.theme-light .btn-tb-mode {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }
        body.theme-light .btn-tb-mode:hover {
            background: #e2e8f0;
            color: #000000;
        }

        /* ==========================================================================
           2. PAPER / DOCUMENT LAYOUT (EXACT A4 / PDF REPLICA)
           ========================================================================== */
        .paper-container {
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            margin: 24px auto;
            background: #ffffff;
            padding: 18mm 18mm 20mm 18mm;
            box-shadow: 0 8px 24px rgba(0,0,0,0.35);
            border-radius: 2px;
            box-sizing: border-box;
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
        }
        .paper-container,
        .paper-container table,
        .paper-container td,
        .paper-container th,
        .paper-container p,
        .paper-container span,
        .paper-container div,
        .paper-container li {
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
        }

        /* Responsive Table Wrapper for Screen & Print */
        .doc-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 12px;
        }

        /* Screen Responsiveness */
        @media screen and (max-width: 220mm) {
            .paper-container {
                width: 96% !important;
                margin: 16px auto !important;
                padding: 14mm 10mm !important;
            }
        }
        @media screen and (max-width: 768px) {
            .paper-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 14px 8px !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .no-print-toolbar {
                padding: 8px 12px !important;
                flex-direction: column !important;
                gap: 8px !important;
                align-items: flex-start !important;
            }
            .toolbar-actions {
                width: 100% !important;
                justify-content: space-between !important;
                flex-wrap: wrap !important;
            }
        }

        /* Institutional Header Grid (Sesuai Format Resmi RPS) */
        .doc-top-rule {
            border-top: 1.5px solid #000000;
            margin-bottom: 6px;
            width: 100%;
        }
        .doc-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1.5px solid #000000;
        }
        .doc-header-table td {
            border: 1.5px solid #000000;
            vertical-align: middle;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .doc-header-left {
            width: 50%;
            padding: 3px 12px !important;
        }
        .doc-header-left-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            width: 100%;
        }
        .doc-header-logo {
            width: 125px;
            height: 60px;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
            flex: 0 0 125px;
        }
        .doc-header-prodi-title {
            font-size: 9pt;
            font-weight: bold;
            color: #000000;
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
            letter-spacing: 0;
            line-height: 1.18;
            text-align: left;
            white-space: nowrap;
        }
        .doc-header-main-title {
            width: 50%;
            font-size: 12.5pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
            padding: 8px 4px !important;
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
            color: #000000;
        }
        .doc-header-subrow {
            background-color: #c0c0c0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .doc-header-subrow td {
            background-color: #c0c0c0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 10.5pt;
            padding: 4px 4px !important;
            text-align: center;
            font-family: "Bookman Old Style", "Bookman", "URW Bookman", serif;
            color: #000000;
        }
        .doc-ver-lbl {
            width: 18%;
        }
        .doc-ver-val {
            width: 14%;
        }
        .doc-ver-pad {
            width: 18%;
        }

        /* General Section Heading */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        /* Exact Crisp Academic Tables */
        .rps-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #000000;
            font-size: 9.5pt;
        }
        .rps-table th,
        .rps-table td {
            border: 1px solid #000000;
            padding: 5px 6px;
            vertical-align: top;
            color: #000000;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
        }
        .rps-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        /* Dedicated Styles for Rencana Evaluasi Penilaian (kolom dinamis) */
        .rps-table-eval {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #000000;
        }
        .rps-table-eval th,
        .rps-table-eval td {
            border: 1px solid #000000;
            padding: 4px 3px !important;
            vertical-align: top;
            color: #000000;
            overflow-wrap: anywhere !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            hyphens: auto;
        }
        .rps-table-eval th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt !important;
            line-height: 1.25;
            padding: 5px 2px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .rps-table-eval td {
            font-size: 7.5pt !important;
            line-height: 1.3;
        }
        .rps-table-eval .eval-rumusan-col {
            font-size: 7.5pt !important;
            line-height: 1.3;
            text-align: left;
        }

        .note-text {
            font-size: 8.5pt;
            font-style: italic;
            margin-top: -6px;
            margin-bottom: 10px;
        }

        /* Peta Analisis Diagram Styling */
        .peta-container {
            border: 1px solid #000;
            padding: 16px;
            margin-bottom: 14px;
            background: #ffffff;
        }
        .peta-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
        }
        .peta-node {
            border: 1.5px solid #000;
            padding: 8px 12px;
            background: #ffffff;
            font-size: 8.5pt;
            line-height: 1.3;
            text-align: center;
            border-radius: 4px;
            box-shadow: 1px 2px 4px rgba(0,0,0,0.06);
        }
        .peta-node-cpmk {
            background: #f8fafc;
            border: 2px solid #000;
            font-weight: bold;
            max-width: 480px;
        }
        .peta-node-sub {
            width: 100%;
            max-width: 650px;
            text-align: left;
        }
        .peta-arrow {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1;
            color: #000;
        }

        /* Page Break Rules for Print */
        .page-break {
            page-break-before: always;
            break-before: page;
            margin-top: 25px;
        }
        .keep-together {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ==========================================================================
           3. PRINT MEDIA STYLES
           ========================================================================== */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .paper-container {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
                padding-top: 0 !important;
                margin-top: 0 !important;
            }
            .keep-together {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .rps-table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .rps-table-eval {
                table-layout: fixed !important;
                width: 100% !important;
            }
            .doc-table-wrapper {
                overflow: visible !important;
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- TOP CONTROL TOOLBAR (Only shown on screen, hidden on print) -->
    <div class="no-print-toolbar">
        <div class="toolbar-title">
            <span class="toolbar-badge">DOKUMEN RESMI RPS</span>
            <span><?= htmlspecialchars($mk['kode']) ?> - <?= htmlspecialchars($mk['nama']) ?></span>
            <span style="font-size: 0.8rem; color: #94a3b8;">(Revisi <?= htmlspecialchars($rps['versi'] ?? '3/0') ?>)</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" onclick="toggleDocTheme()" class="btn-tb btn-tb-mode" id="btnDocTheme" title="Ganti Mode Latar (Dark / Light)">
                <i class="fa-solid fa-moon" id="docThemeIcon"></i> <span id="docThemeText">Dark</span>
            </button>
            <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>" class="btn-tb btn-tb-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Builder
            </a>
            <button onclick="window.print()" class="btn-tb btn-tb-print">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- MAIN PAPER CONTAINER -->
    <div class="paper-container">

        <!-- ====================================================================
             HEADER DOKUMEN RPS RESMI (SESUAI STANDAR FORMAT RPS ASLI)
             ==================================================================== -->
        <div class="doc-top-rule"></div>
        <table class="doc-header-table">
            <tr>
                <td rowspan="2" class="doc-header-left">
                    <div class="doc-header-left-wrap">
                        <img src="<?= $logoSrc ?>" alt="Logo Universitas Anwar Medika" class="doc-header-logo">
                        <div class="doc-header-prodi-title">
                            <?php foreach ($headerProdiList as $headerProdi): ?>
                                <div><?= htmlspecialchars(strtoupper(trim($headerProdi))) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
                <td colspan="3" class="doc-header-main-title">
                    RENCANA PEMBELAJARAN SEMESTER
                </td>
            </tr>
            <tr class="doc-header-subrow">
                <td class="doc-ver-lbl">Versi/Revisi</td>
                <td class="doc-ver-val"><?= htmlspecialchars($rps['versi'] ?? '3/0') ?></td>
                <td class="doc-ver-pad">&nbsp;</td>
            </tr>
        </table>

        <!-- ====================================================================
             1. IDENTITAS MATA KULIAH
             ==================================================================== -->
        <div class="section-title">1. IDENTITAS MATA KULIAH</div>
        <table class="rps-table">
            <tr>
                <td style="width: 22%; font-weight: bold;">Nama Mata Kuliah</td>
                <td colspan="3" style="font-weight: bold;"><?= htmlspecialchars($mk['nama']) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Fakultas</td>
                <td style="width: 38%;"><?= htmlspecialchars($rps['fakultas'] ?? 'Fakultas Sains dan Teknologi') ?></td>
                <td style="width: 18%; font-weight: bold;">Program Studi</td>
                <td style="width: 22%;"><?= htmlspecialchars($rps['program_studi'] ?? 'S1 Sistem Informasi') ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Kode Mata Kuliah</td>
                <td style="font-weight: bold;"><?= htmlspecialchars($mk['kode']) ?></td>
                <td style="font-weight: bold;">Bobot sks</td>
                <td>
                    <?= htmlspecialchars($mk['sks_teori'] + $mk['sks_praktek']) ?> sks 
                    (*<?= $mk['sks_teori'] > 0 ? '<u>Teori</u>' : 'Teori' ?>/ <?= $mk['sks_praktek'] > 0 ? '<u>Praktikum</u>' : 'Praktikum' ?>)
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Kelas</td>
                <td><?= htmlspecialchars($rps['kelas'] ?? 'I-23') ?></td>
                <td style="font-weight: bold;">Sifat Pengambilan</td>
                <td>
                    *<?= ($mk['sifat'] === 'Wajib' || ($rps['sifat'] ?? '') === 'Wajib') ? '<u>Wajib</u>/ Pilihan' : 'Wajib/ <u>Pilihan</u>' ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Semester Ke</td>
                <td><?= htmlspecialchars($mk['semester']) ?></td>
                <td style="font-weight: bold;">Ketersediaan</td>
                <td>
                    *Universitas/Fakultas/<u><?= htmlspecialchars($rps['ketersediaan'] ?? 'Program Studi') ?></u>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Bentuk Pembelajaran</td>
                <td colspan="3">
                    <?= nl2br(htmlspecialchars($rps['bentuk_pembelajaran'] ?? 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara/ Praktikum/ Praktik Studio/Praktek Lapangan/ Penelitian/ Pengabdian Kepada Masyarakat')) ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Media Pembelajaran</td>
                <td colspan="3">
                    <?= nl2br(htmlspecialchars($rps['media_pembelajaran'] ?? '*Audio/Visual/Audiovisual')) ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Kelompok Keilmuan</td>
                <td><?= htmlspecialchars($mk['kelompok_keilmuan'] ?: ($rps['kelompok_keilmuan'] ?? 'Sistem Informasi')) ?></td>
                <td style="font-weight: bold;">Mata Kuliah Prasyarat</td>
                <td><?= htmlspecialchars($rps['prasyarat'] ?? '-') ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Dosen Pengampu</td>
                <td><?= nl2br(htmlspecialchars(!empty($rps['dosen_pengampu']) ? $rps['dosen_pengampu'] : ('1. ' . ($mk['dosen_nama'] ?? 'Dosen Pengampu') . ' (PJMK)'))) ?></td>
                <td style="font-weight: bold;">Semester/ Tahun Akademik</td>
                <td><?= htmlspecialchars($rps['tahun_akademik'] ?? 'Genap 2025/2026') ?></td>
            </tr>
        </table>
        <div class="note-text">*) Pilih sesuai kebutuhan</div>

        <!-- ====================================================================
             2a. CAPAIAN PEMBELAJARAN LULUSAN (CPL) & CPMK
             ==================================================================== -->
        <div class="section-title">2a. CAPAIAN PEMBELAJARAN LULUSAN (CPL)</div>
        <table class="rps-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Kode CPL</th>
                    <th style="width: 80%;">Rumusan CPL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cpls)): ?>
                    <?php foreach ($cpls as $cpl): ?>
                        <tr>
                            <td class="text-center text-bold"><?= htmlspecialchars($cpl['kode']) ?></td>
                            <td><?= htmlspecialchars($cpl['rumusan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center" style="font-style: italic; color: #64748b;">
                            Belum ada CPL yang dipetakan untuk mata kuliah ini di menu Pemetaan MK ke CPL.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Capaian Pembelajaran Mata Kuliah (CPMK) & Sub-CPMK -->
        <table class="rps-table" style="margin-top: 8px;">
            <thead>
                <tr>
                    <th style="width: 40%;">Rumusan CPMK</th>
                    <th style="width: 18%;">Kode Sub-CPMK</th>
                    <th style="width: 42%;">Sub-CPMK</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cpmks)): ?>
                    <?php foreach ($cpmks as $cpmk): ?>
                        <?php 
                        $subs = $cpmk['sub_cpmks'] ?? [];
                        $rowCount = max(1, count($subs));
                        ?>
                        <tr>
                            <td rowspan="<?= $rowCount ?>" style="vertical-align: middle; font-weight: 500;">
                                <?= htmlspecialchars($cpmk['rumusan']) ?>
                            </td>
                            <?php if (!empty($subs)): ?>
                                <td class="text-center text-bold"><?= htmlspecialchars($subs[0]['kode']) ?></td>
                                <td>
                                    <?= htmlspecialchars($subs[0]['rumusan']) ?>
                                    <?php if (!empty($subs[0]['level_taksonomi'])): ?>
                                        <strong>(<?= htmlspecialchars($subs[0]['level_taksonomi']) ?>)</strong>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <td class="text-center">-</td>
                                <td>-</td>
                            <?php endif; ?>
                        </tr>
                        <?php for ($i = 1; $i < count($subs); $i++): ?>
                            <tr>
                                <td class="text-center text-bold"><?= htmlspecialchars($subs[$i]['kode']) ?></td>
                                <td>
                                    <?= htmlspecialchars($subs[$i]['rumusan']) ?>
                                    <?php if (!empty($subs[$i]['level_taksonomi'])): ?>
                                        <strong>(<?= htmlspecialchars($subs[$i]['level_taksonomi']) ?>)</strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center" style="color: #666; font-style: italic;">Belum ada rumusan CPMK dan Sub-CPMK.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ====================================================================
             2b. HUBUNGAN CPL DAN SUB-CPMK
             ==================================================================== -->
        <div class="keep-together">
            <div class="section-title">2b. HUBUNGAN CPL DAN SUB-CPMK</div>
            <?php 
            $matrixCpls = !empty($cpls) ? $cpls : (!empty($kurikulumCpls) ? $kurikulumCpls : []);
            $cplColsCount = count($matrixCpls);
            ?>
            <?php if ($cplColsCount === 0): ?>
                <div style="padding: 12px; text-align: center; font-style: italic; color: #64748b;">
                    Belum ada data CPL yang didefinisikan untuk kurikulum mata kuliah ini.
                </div>
            <?php else: ?>
            <div class="doc-table-wrapper">
            <table class="rps-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 25%; vertical-align: middle;">Kode SUB-CPMK</th>
                        <th colspan="<?= $cplColsCount ?>">Kode CPL / Bobot Kontribusi</th>
                        <th rowspan="2" style="width: 18%; vertical-align: middle;">Jumlah Bobot (%)</th>
                    </tr>
                    <tr>
                        <?php foreach ($matrixCpls as $mcpl): ?>
                            <th style="text-align: center;" title="<?= htmlspecialchars($mcpl['rumusan']) ?>">
                                <?= htmlspecialchars($mcpl['kode']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotal = 0;
                    $cplTotals = array_fill_keys(array_column($matrixCpls, 'id'), 0);

                    foreach ($allSubCpmks as $sub): 
                        $bobot = floatval($sub['bobot']);
                        $grandTotal += $bobot;
                        $subCplIds = $sub['cpl_ids'] ?? [];
                    ?>
                        <tr>
                            <td class="text-bold" style="padding-left: 10px;"><?= htmlspecialchars($sub['kode']) ?></td>
                            <?php foreach ($matrixCpls as $mcpl): 
                                $isLinked = in_array($mcpl['id'], $subCplIds);
                                if ($isLinked) {
                                    $cplTotals[$mcpl['id']] += $b = $bobot;
                                }
                            ?>
                                <td class="text-center <?= $isLinked ? 'text-bold' : '' ?>">
                                    <?= $isLinked ? ($bobot . '%') : '' ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-center text-bold"><?= $bobot ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #f8f9fa; font-weight: bold;">
                        <td class="text-center">Jumlah</td>
                        <?php foreach ($matrixCpls as $mcpl): ?>
                            <td class="text-center">
                                <?= $cplTotals[$mcpl['id']] > 0 ? ($cplTotals[$mcpl['id']] . '%') : '' ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center"><?= $grandTotal ?>%</td>
                    </tr>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- ====================================================================
             2c. INDIKATOR PENILAIAN
             ==================================================================== -->
        <div class="page-break"></div>
        <div class="section-title">2c. INDIKATOR PENILAIAN</div>
        <div class="doc-table-wrapper">
        <table class="rps-table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 12%;">Kode CPL</th>
                    <th style="width: 14%;">Kode Sub-CPMK</th>
                    <th style="width: 38%;">Rumusan Indikator</th>
                    <th style="width: 18%;">Pengalaman Pembelajaran</th>
                    <th style="width: 10%;">Asesmen/ penilaian</th>
                    <th style="width: 8%;">Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sumBobotIndikator = 0;
                foreach ($allSubCpmks as $sub): 
                    $bobot = floatval($sub['bobot']);
                    $sumBobotIndikator += $bobot;
                ?>
                    <tr>
                        <td class="text-center text-bold"><?= htmlspecialchars(implode(', ', $sub['cpl_kodes'] ?? []) ?: 'CPL 04') ?></td>
                        <td class="text-center text-bold"><?= htmlspecialchars($sub['kode']) ?></td>
                        <td>
                            <?php if (!empty($sub['indikators'])): ?>
                                <ol style="margin: 0; padding-left: 16px;">
                                    <?php foreach ($sub['indikators'] as $ind): ?>
                                        <li style="margin-bottom: 4px;"><?= htmlspecialchars($ind['indikator']) ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php else: ?>
                                <?= htmlspecialchars($sub['rumusan']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($sub['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus') ?></td>
                        <td class="text-center"><?= htmlspecialchars($sub['asesmen_penilaian'] ?? 'Pretes dan postes') ?></td>
                        <td class="text-center text-bold"><?= $bobot ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="5" class="text-right" style="padding-right: 14px;">TOTAL</td>
                    <td class="text-center"><?= $sumBobotIndikator ?>%</td>
                </tr>
            </tbody>
        </table>
        </div>

        <!-- ====================================================================
             3. PETA ANALISIS CAPAIAN PEMBELAJARAN
             ==================================================================== -->
        <div class="page-break"></div>
        <div class="section-title">3. PETA ANALISIS CAPAIAN PEMBELAJARAN</div>
        <div class="peta-container">
            <div class="peta-grid">
                <?php if (empty($cpmks)): ?>
                    <div style="padding: 14px; font-style: italic;">Belum ada data CPMK dan Sub-CPMK.</div>
                <?php else: ?>
                    <?php foreach (array_reverse($cpmks) as $cpmkIndex => $cpmk): ?>
                        <div class="peta-node peta-node-cpmk">
                            <div style="font-size: 10pt; text-transform: uppercase; margin-bottom: 4px; color: #0284c7;">Capaian Pembelajaran Mata Kuliah (CPMK)</div>
                            <div><strong>[<?= htmlspecialchars($cpmk['kode']) ?>]</strong> <?= htmlspecialchars($cpmk['rumusan']) ?></div>
                        </div>
                        <?php foreach (array_reverse($cpmk['sub_cpmks'] ?? []) as $sub): ?>
                            <div class="peta-arrow">&#9650;</div>
                            <div class="peta-node peta-node-sub" style="border-left: 5px solid #0284c7;">
                                <strong>[<?= htmlspecialchars($sub['kode']) ?>]</strong> <?= htmlspecialchars($sub['rumusan']) ?>
                                <?php if (!empty($sub['level_taksonomi'])): ?><em>(<?= htmlspecialchars($sub['level_taksonomi']) ?>)</em><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($cpmkIndex < count($cpmks) - 1): ?><div class="peta-arrow">&#9650;</div><?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====================================================================
             4. REFERENSI
             ==================================================================== -->
        <div class="section-title">4. REFERENSI</div>
        <div style="border: 1px solid #000; padding: 10px 14px; margin-bottom: 14px; font-size: 9.5pt;">
            <?php if (!empty($referensiList)): ?>
                <ol style="margin: 0; padding-left: 18px;">
                    <?php foreach ($referensiList as $ref): ?>
                        <li style="margin-bottom: 5px;"><?= htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $ref)) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p style="margin: 0; font-style: italic;">Belum ada daftar referensi yang diinputkan.</p>
            <?php endif; ?>
        </div>

        <!-- ====================================================================
             5. RINCIAN AKTIVITAS PEMBELAJARAN (16 PERTEMUAN)
             ==================================================================== -->
        <div class="page-break"></div>
        <div class="section-title">5. RINCIAN AKTIVITAS PEMBELAJARAN</div>
        <div class="doc-table-wrapper">
        <table class="rps-table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 8%;">Sesi dan Tanggal</th>
                    <th style="width: 12%;">Kode Sub-CPMK</th>
                    <th style="width: 18%;">Bahan Kajian/Topik</th>
                    <th style="width: 36%;">Rancangan Aktivitas dan Durasi</th>
                    <th style="width: 10%;">Mode</th>
                    <th style="width: 16%;">Media Pembelajaran/ Referensi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pertemuanList)): ?>
                    <?php foreach ($pertemuanList as $p): ?>
                        <tr style="<?= ($p['pertemuan_ke'] == 8 || $p['pertemuan_ke'] == 16) ? 'background-color: #faf5ff;' : '' ?>">
                            <td class="text-center text-bold"><?= htmlspecialchars($p['pertemuan_ke']) ?></td>
                            <td class="text-center text-bold">
                                <?= htmlspecialchars($p['sub_cpmk_kode'] ?? 'SUB-CPMK 01') ?>
                            </td>
                            <td class="text-bold"><?= nl2br(htmlspecialchars($p['bahan_kajian'])) ?></td>
                            <td>
                                <?php if (!empty($p['durasi_detail'])): ?>
                                    <?= nl2br(htmlspecialchars($p['durasi_detail'])) ?>
                                <?php else: ?>
                                    <?= nl2br(htmlspecialchars($p['pengalaman_belajar'])) ?>
                                    <?php if (!empty($p['alokasi_waktu'])): ?>
                                        <div style="margin-top: 4px; font-weight: bold; font-size: 8.5pt;">(<?= htmlspecialchars($p['alokasi_waktu']) ?>)</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-bold"><?= htmlspecialchars($p['mode_pembelajaran'] ?? 'ASM dan TM') ?></td>
                            <td><?= nl2br(htmlspecialchars($p['pustaka'] ?? "WAG, Zoom Meetings, Youtube\n[1], [2], [3]")) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="font-style: italic;">Belum ada rincian pertemuan mingguan. Silakan generate template 16 pertemuan di RPS Builder.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <!-- Keterangan Mode -->
        <div style="border: 1px solid #000; padding: 8px 12px; margin-bottom: 14px; font-size: 8.5pt; line-height: 1.4;">
            <strong>Keterangan:</strong><br>
            Untuk mode diisikan salah satu dari kode berikut:<br>
            • <strong>TM</strong> = Tatap Muka = aktivitas yang memerlukan tatap muka langsung di kelas;<br>
            • <strong>TMD</strong> = Tatap Muka Daring = aktivitas yang memerlukan tatap muka secara daring (tatap maya);<br>
            • <strong>ASM</strong> = aktivitas daring asinkron mandiri;<br>
            • <strong>ASK</strong> = aktivitas daring asinkron kolaborasi;<br>
            Media pembelajaran/referensi dapat berupa (1) hasil produksi sendiri, (2) hasil kurasi: media yang bersumber dari internet atau sumber lainnya yang dipilihkan oleh dosen, dan/atau (3) hasil eksplorasi mahasiswa sendiri.
        </div>

        <!-- ====================================================================
             6. SISTEM PENILAIAN DAN EVALUASI
             ==================================================================== -->
        <div class="keep-together">
            <div class="section-title">6. SISTEM PENILAIAN DAN EVALUASI</div>
            <table class="rps-table">
                <tr>
                    <td style="width: 25%; font-weight: bold;">Sistem Penilaian</td>
                    <td style="width: 75%;">
                        <p style="margin: 0 0 6px 0;"><?= htmlspecialchars($rps['sistem_penilaian_desc'] ?? 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.') ?></p>
                        <strong>Komponen Penilaian:</strong>
                        <ul style="margin: 4px 0 0 0; padding-left: 20px;">
                            <?php foreach (($jenisPenilaian ?? []) as $komponen):
                                $kodeJenis = $komponen['kode'] ?? '';
                                $rubrikId = (int)($komponen['rubrik_id'] ?? 0);
                            ?>
                            <li><?= htmlspecialchars(($jenisPenilaianMaster[$kodeJenis] ?? $kodeJenis) . ' [' . $kodeJenis . ']') ?> (<?= (float)($komponen['bobot'] ?? 0) ?>%)<?= $rubrikId && isset($rubrikNamaById[$rubrikId]) ? ' — Rubrik: ' . htmlspecialchars($rubrikNamaById[$rubrikId]) : '' ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Sistem Evaluasi</td>
                    <td>
                        <?= nl2br(htmlspecialchars($rps['sistem_evaluasi_desc'] ?? 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.')) ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ====================================================================
             7. RENCANA EVALUASI PENILAIAN MAHASISWA
             ==================================================================== -->
        <div class="page-break"></div>
        <table class="doc-header-table" style="margin-top: 10px;">
            <tr>
                <td rowspan="2" class="doc-header-left">
                    <div class="doc-header-left-wrap">
                        <img src="<?= $logoSrc ?>" alt="Logo Universitas Anwar Medika" class="doc-header-logo">
                        <div class="doc-header-prodi-title">
                            <?php foreach ($headerProdiList as $headerProdi): ?>
                                <div><?= htmlspecialchars(strtoupper(trim($headerProdi))) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
                <td colspan="3" class="doc-header-main-title">
                    Rencana Evaluasi Penilaian Mahasiswa
                </td>
            </tr>
            <tr class="doc-header-subrow">
                <td class="doc-ver-lbl">Versi/Revisi</td>
                <td class="doc-ver-val"><?= htmlspecialchars($rps['versi'] ?? '3/0') ?></td>
                <td class="doc-ver-pad">&nbsp;</td>
            </tr>
        </table>

        <div class="section-title">INDIKATOR PENILAIAN</div>
        <?php
        $printAssessmentComponents = array_values(array_filter($jenisPenilaian ?? [], static function ($item) use ($jenisPenilaianMaster) {
            return isset($jenisPenilaianMaster[$item['kode'] ?? '']) && (float)($item['bobot'] ?? 0) > 0;
        }));
        $assessmentColumnWidth = $printAssessmentComponents ? (53 / count($printAssessmentComponents)) : 53;
        ?>
        <div class="doc-table-wrapper">
            <table class="rps-table rps-table-eval">
                <thead>
                    <tr>
                        <th style="width: 7%;">Kode<br>CPL</th>
                        <th style="width: 9%;">Kode<br>Sub-CPMK</th>
                        <th style="width: 5.5%;">Bobot<br>(%)</th>
                        <th style="width: 25.5%;">Rumusan Indikator</th>
                        <?php foreach ($printAssessmentComponents as $assessment):
                            $assessmentCode = $assessment['kode'];
                        ?>
                        <th style="width:<?= $assessmentColumnWidth ?>%;"><?= htmlspecialchars($jenisPenilaianMaster[$assessmentCode]) ?><br><small><?= htmlspecialchars($assessmentCode) ?> — <?= (float)$assessment['bobot'] ?>%</small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $indicatorOffset = 1;
                    foreach ($allSubCpmks as $sub): 
                        $subKode = $sub['kode'];
                        $customEval = $rencanaEvaluasi[$subKode] ?? ['bobot' => $sub['bobot'], 'soal' => []];
                        $componentQuestions = array_fill_keys(array_column($printAssessmentComponents, 'kode'), []);
                        foreach (($customEval['soal'] ?? []) as $question) {
                            $rawType = trim($question['jenis'] ?? '');
                            $type = mb_strtolower($rawType);
                            $text = trim($question['pertanyaan'] ?? '');
                            if ($text === '') continue;
                            $component = null;
                            foreach ($printAssessmentComponents as $assessment) {
                                $code = $assessment['kode'];
                                $name = mb_strtolower($jenisPenilaianMaster[$code] ?? '');
                                if (mb_strtolower($code) === $type || ($name !== '' && strpos($type, $name) !== false)) { $component = $code; break; }
                            }
                            if ($component === null) {
                                $aliases = ['aktif'=>'AKT','kuis'=>'KUI','pretes'=>'KUI','postes'=>'KUI','individu'=>'TGI','tugas'=>'TGI','kelompok'=>'TGK','presentasi'=>'PRS','kasus'=>'STK','proyek'=>'PRJ','praktikum'=>'PRK','responsi'=>'RSP','laporan'=>'LPR','unjuk'=>'UK','portofolio'=>'POR','observasi'=>'OBS','lisan'=>'TL','tertulis'=>'TT','uts'=>'UTS','uas'=>'UAS','ujian praktik'=>'UPR','produk'=>'PRO','karya'=>'PRO'];
                                foreach ($aliases as $needle => $code) if (strpos($type, $needle) !== false && isset($componentQuestions[$code])) { $component = $code; break; }
                            }
                            if ($component !== null && isset($componentQuestions[$component])) $componentQuestions[$component][] = ($rawType !== '' ? $rawType . ': ' : '') . $text;
                        }
                        $componentValue = static function ($key) use ($customEval, $componentQuestions) {
                            if (!empty($componentQuestions[$key])) {
                                $lines = [];
                                foreach ($componentQuestions[$key] as $index => $text) $lines[] = ($index + 1) . '. ' . $text;
                                return implode("\n", $lines);
                            }
                            $legacyKeys = ['AKT'=>'keaktifan','KUI'=>'kuis','TGI'=>'tugas','TGK'=>'tugas','UTS'=>'uts','UAS'=>'uas'];
                            return $customEval[$key] ?? $customEval[mb_strtolower($key)] ?? (isset($legacyKeys[$key]) ? ($customEval[$legacyKeys[$key]] ?? '') : '');
                        };
                    ?>
                        <tr>
                            <td class="text-center text-bold"><?= htmlspecialchars(implode(', ', $sub['cpl_kodes'] ?? []) ?: '-') ?></td>
                            <td class="text-center text-bold"><?= htmlspecialchars($subKode) ?></td>
                            <td class="text-center text-bold"><?= htmlspecialchars($customEval['bobot'] ?? ($sub['bobot'].'%')) ?></td>
                            <td class="eval-rumusan-col">
                                <?php if (!empty($sub['indikators'])): ?>
                                    <?php foreach ($sub['indikators'] as $ind): ?>
                                        <div style="margin-bottom: 4px;">
                                            <strong><?= $indicatorOffset++ ?>.</strong> <?= htmlspecialchars($ind['indikator']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($sub['rumusan']) ?>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($printAssessmentComponents as $assessment): ?>
                            <td><?= nl2br(htmlspecialchars($componentValue($assessment['kode']))) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ====================================================================
             8. RUBRIK PENILAIAN
             ==================================================================== -->
        <div class="page-break"></div>
        <table class="doc-header-table" style="margin-top: 10px;">
            <tr>
                <td rowspan="2" class="doc-header-left">
                    <div class="doc-header-left-wrap">
                        <img src="<?= $logoSrc ?>" alt="Logo Universitas Anwar Medika" class="doc-header-logo">
                        <div class="doc-header-prodi-title">
                            <?php foreach ($headerProdiList as $headerProdi): ?>
                                <div><?= htmlspecialchars(strtoupper(trim($headerProdi))) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
                <td colspan="3" class="doc-header-main-title">
                    Rubrik Penilaian Pembelajaran (OBE)
                </td>
            </tr>
            <tr class="doc-header-subrow">
                <td class="doc-ver-lbl">Versi/Revisi</td>
                <td class="doc-ver-val"><?= htmlspecialchars($rps['versi'] ?? '3/0') ?></td>
                <td class="doc-ver-pad">&nbsp;</td>
            </tr>
        </table>

        <div class="section-title">8. RUBRIK PENILAIAN PEMBELAJARAN</div>

        <?php
        $documentRubrikUsage = [];
        foreach (($jenisPenilaian ?? []) as $komponen) {
            $linkedId = (int)($komponen['rubrik_id'] ?? 0);
            if (!$linkedId) continue;
            $kodeKomponen = $komponen['kode'] ?? '';
            $documentRubrikUsage[$linkedId][] = [
                'jenis' => $jenisPenilaianMaster[$kodeKomponen] ?? $kodeKomponen,
                'bobot' => (float)($komponen['bobot'] ?? 0)
            ];
        }
        // rubrikMaster sudah hanya berisi status ACTIVE. Cetak hanya yang dipilih
        // pada komponen penilaian dan sudah memiliki salinan khusus mata kuliah.
        $selectedRubrikRows = array_values(array_filter($rubrikMaster ?? [], static function ($item) use ($documentRubrikUsage, $rubrikMk) {
            $id = (int)$item['id'];
            return isset($documentRubrikUsage[$id]) && isset($rubrikMk[(string)$id]);
        }));
        ?>
        <?php if ($selectedRubrikRows): ?>
        <table class="rps-table" style="margin-bottom:16px;">
            <thead><tr><th>Kode</th><th>Instrumen Rubrik</th><th>Karakteristik</th><th>Komponen & Bobot Penilaian</th><th>Kriteria / Level</th></tr></thead>
            <tbody>
            <?php foreach ($selectedRubrikRows as $masterRubrik):
                $usageLabels = [];
                foreach ($documentRubrikUsage[(int)$masterRubrik['id']] as $usage) $usageLabels[] = $usage['jenis'] . ' (' . $usage['bobot'] . '%)';
                $courseRubrik = $rubrikMk[(string)(int)$masterRubrik['id']];
            ?>
                <tr>
                    <td style="font-weight:bold;text-align:center;"><?= htmlspecialchars($masterRubrik['kode_jenis']) ?></td>
                    <td><strong><?= htmlspecialchars($courseRubrik['nama'] ?? $masterRubrik['nama']) ?></strong><br><small><?= htmlspecialchars($masterRubrik['jenis']) ?></small></td>
                    <td><?= htmlspecialchars($masterRubrik['karakteristik']) ?><br><small>Contoh: <?= htmlspecialchars($masterRubrik['contoh_penggunaan']) ?></small></td>
                    <td><?= htmlspecialchars(implode(', ', $usageLabels)) ?></td>
                    <td style="text-align:center;"><?= $courseRubrik ? count($courseRubrik['criteria'] ?? []) : (int)$masterRubrik['total_kriteria'] ?> / <?= $courseRubrik ? count($courseRubrik['levels'] ?? []) : (int)$masterRubrik['total_level'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php foreach ($selectedRubrikRows as $index => $masterRubrik):
            $courseRubrik = $rubrikMk[(string)(int)$masterRubrik['id']];
        ?>
        <div class="page-break"></div>
        <div style="margin-bottom:16px;">
            <div style="font-weight:bold;font-size:10pt;margin-bottom:6px;">8.<?= $index + 1 ?> <?= htmlspecialchars($courseRubrik['nama']) ?></div>
            <table class="rps-table">
                <thead><tr><th style="width:22%;">Indikator/Kriteria</th><th style="width:8%;">Bobot</th><?php foreach (($courseRubrik['levels'] ?? []) as $level): ?><th><?= htmlspecialchars($level['nama']) ?><br><small>Skor <?= (float)$level['skor'] ?></small></th><?php endforeach; ?></tr></thead>
                <tbody><?php foreach (($courseRubrik['criteria'] ?? []) as $criterion): ?><tr>
                    <td><strong><?= htmlspecialchars($criterion['nama']) ?></strong></td><td style="text-align:center;"><?= (float)$criterion['bobot'] ?>%</td>
                    <?php foreach (($courseRubrik['levels'] ?? []) as $levelIndex => $level): ?><td><?= nl2br(htmlspecialchars($criterion['descriptors'][$levelIndex] ?? '-')) ?></td><?php endforeach; ?>
                </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div style="border:1px dashed #64748b;padding:16px;text-align:center;color:#475569;margin-bottom:20px;">Belum ada rubrik yang dihubungkan. Hubungkan rubrik pada tab Sistem Penilaian & Evaluasi.</div>
        <?php endif; ?>

        <?php if (false): // Template rubrik lama dinonaktifkan; master terbaru digunakan di atas. ?>
        <?php 
        $subRubrikIndex = 1;
        $hasActiveRubrik = false;
        ?>

        <!-- 8.1 RUBRIK PENILAIAN HOLISTIK -->
        <?php if (!empty($rubrik['holistik']['aktif'])): 
            $hasActiveRubrik = true;
            $holistikData = $rubrik['holistik'];
            $kriteriaList = $holistikData['kriteria'] ?? [];
        ?>
        <div class="keep-together" style="margin-bottom: 16px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 6px;">8.<?= $subRubrikIndex++ ?> <?= htmlspecialchars($holistikData['judul'] ?? 'Rubrik Penilaian Holistik') ?></div>
            <table class="rps-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">Kriteria Penilaian</th>
                        <th style="width: 54%;">Deskriptor Tingkat Ketercapaian</th>
                        <th style="width: 18%;">Rentang Skor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $formulaParts = [];
                    foreach ($kriteriaList as $kIdx => $krit): 
                        $skalaList = $krit['skala'] ?? [];
                        $rowCount = count($skalaList) + 1; // +1 untuk baris subtotal bobot
                        $bobotKrit = floatval($krit['bobot_persen'] ?? 0);
                        $formulaParts[] = "({$bobotKrit}% &times; Skor " . htmlspecialchars($krit['nama']) . ")";
                    ?>
                        <tr>
                            <td rowspan="<?= $rowCount ?>" class="text-bold" style="vertical-align: top;">
                                <?= htmlspecialchars($krit['nama']) ?>
                            </td>
                            <?php if (!empty($skalaList)): ?>
                                <td><?= htmlspecialchars($skalaList[0]['deskripsi']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($skalaList[0]['skor']) ?></td>
                            <?php else: ?>
                                <td>-</td>
                                <td class="text-center">-</td>
                            <?php endif; ?>
                        </tr>
                        <?php for ($s = 1; $s < count($skalaList); $s++): ?>
                            <tr>
                                <td><?= htmlspecialchars($skalaList[$s]['deskripsi']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($skalaList[$s]['skor']) ?></td>
                            </tr>
                        <?php endfor; ?>
                        <tr style="background: #f8f9fa;">
                            <td class="text-bold">Nilai <?= htmlspecialchars($krit['nama']) ?> (Bobot <?= $bobotKrit ?>%)</td>
                            <td class="text-center text-bold"><?= $bobotKrit ?>% &times; Skor Capaian</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e2e8f0; font-weight: bold; font-size: 10pt;">
                        <td colspan="2" class="text-right" style="padding-right: 14px;">
                            Nilai Akhir Tugas Holistik = <?= !empty($formulaParts) ? implode(' + ', $formulaParts) : '&sum; (Bobot % &times; Skor)' ?>
                        </td>
                        <td class="text-center">Maks 100</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 8.2 RUBRIK PENILAIAN ANALITIK -->
        <?php if (!empty($rubrik['analitik']['aktif'])): 
            $hasActiveRubrik = true;
            $analitikData = $rubrik['analitik'];
            $dimensiList = $analitikData['dimensi'] ?? [];
            $totalBobotAnalitik = 0;
            foreach ($dimensiList as $d) {
                $totalBobotAnalitik += floatval($d['bobot'] ?? 0);
            }
        ?>
        <div class="page-break"></div>
        <div class="keep-together" style="margin-bottom: 16px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 6px;">8.<?= $subRubrikIndex++ ?> <?= htmlspecialchars($analitikData['judul'] ?? 'Rubrik Penilaian Analitik') ?></div>
            <table class="rps-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Dimensi / Kriteria</th>
                        <th style="width: 7%;">Bobot</th>
                        <th style="width: 15%;">Sangat Baik<br>(81 - 100)</th>
                        <th style="width: 15%;">Baik<br>(66 - 80)</th>
                        <th style="width: 15%;">Cukup<br>(56 - 65)</th>
                        <th style="width: 15%;">Kurang<br>(41 - 55)</th>
                        <th style="width: 15%;">Sangat Kurang<br>(&le; 40)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dimensiList as $dIdx => $dim): ?>
                        <tr>
                            <td class="text-bold"><?= ($dIdx + 1) ?>. <?= htmlspecialchars($dim['nama']) ?></td>
                            <td class="text-center text-bold"><?= floatval($dim['bobot'] ?? 0) ?>%</td>
                            <td><?= htmlspecialchars($dim['sangat_baik'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dim['baik'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dim['cukup'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dim['kurang'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($dim['sangat_kurang'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e2e8f0; font-weight: bold; font-size: 9.5pt;">
                        <td class="text-center">Total Bobot</td>
                        <td class="text-center"><?= $totalBobotAnalitik ?>%</td>
                        <td colspan="5" style="padding-left: 10px;">
                            Nilai Akhir Analitik = &sum; (Skor Dimensi &times; Bobot Dimensi) / 100
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 8.3 RUBRIK PENILAIAN SKALA PERSEPSI -->
        <?php if (!empty($rubrik['skala_persepsi']['aktif'])): 
            $hasActiveRubrik = true;
            $persepsiData = $rubrik['skala_persepsi'];
            $aspekList = $persepsiData['aspek'] ?? [];
            $totalBobotPersepsi = 0;
            foreach ($aspekList as $a) {
                $totalBobotPersepsi += floatval($a['bobot'] ?? 0);
            }
            $defaultPersepsiDesc = [
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
        ?>
        <div class="keep-together" style="margin-bottom: 16px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 6px;">8.<?= $subRubrikIndex++ ?> <?= htmlspecialchars($persepsiData['judul'] ?? 'Rubrik Penilaian Skala Persepsi') ?></div>
            <table class="rps-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 26%; vertical-align: middle;">Aspek Pengamatan (Afektif & Kerjasama)</th>
                        <th rowspan="2" style="width: 8%; vertical-align: middle;">Bobot</th>
                        <th colspan="5">Tingkat Skala Capaian</th>
                    </tr>
                    <tr>
                        <th style="width: 13%;">1 - Sangat Kurang</th>
                        <th style="width: 13%;">2 - Kurang</th>
                        <th style="width: 13%;">3 - Cukup</th>
                        <th style="width: 13%;">4 - Baik</th>
                        <th style="width: 14%;">5 - Sangat Baik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aspekList as $aIdx => $asp): 
                        $aspNama = $asp['nama'] ?? '';
                        $descMap = $asp['skala'] ?? ($defaultPersepsiDesc[$aspNama] ?? [
                            1 => 'Kinerja dan sikap sangat tidak memuaskan pada aspek ini.',
                            2 => 'Kinerja dan sikap di bawah standar minimal yang diharapkan.',
                            3 => 'Memenuhi standar minimal ketercapaian aspek afektif.',
                            4 => 'Menunjukkan sikap dan kinerja yang baik serta konsisten.',
                            5 => 'Menunjukkan keunggulan, inisiatif tinggi, dan keteladanan.'
                        ]);
                    ?>
                        <tr>
                            <td class="text-bold"><?= ($aIdx + 1) ?>. <?= htmlspecialchars($aspNama) ?></td>
                            <td class="text-center text-bold"><?= floatval($asp['bobot'] ?? 0) ?>%</td>
                            <td><?= htmlspecialchars($descMap[1] ?? '-') ?></td>
                            <td><?= htmlspecialchars($descMap[2] ?? '-') ?></td>
                            <td><?= htmlspecialchars($descMap[3] ?? '-') ?></td>
                            <td><?= htmlspecialchars($descMap[4] ?? '-') ?></td>
                            <td><?= htmlspecialchars($descMap[5] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e2e8f0; font-weight: bold; font-size: 9.5pt;">
                        <td class="text-center">Jumlah Bobot</td>
                        <td class="text-center"><?= $totalBobotPersepsi ?>%</td>
                        <td colspan="5" style="padding-left: 10px;">
                            Formula Konversi: Nilai Skala Persepsi = [(&sum; (Skor 1..5 &times; Bobot Aspek)) / 5] &times; 100
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- 8.4 RUBRIK PENILAIAN PORTOFOLIO -->
        <?php if (!empty($rubrik['portofolio']['aktif'])): 
            $hasActiveRubrik = true;
            $portofolioData = $rubrik['portofolio'];
            $artefakList = $portofolioData['artefak'] ?? [];
            $totalBobotPortofolio = 0;
            foreach ($artefakList as $art) {
                $totalBobotPortofolio += floatval($art['bobot'] ?? 0);
            }
        ?>
        <div class="keep-together" style="margin-bottom: 16px;">
            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 6px;">8.<?= $subRubrikIndex++ ?> <?= htmlspecialchars($portofolioData['judul'] ?? 'Rubrik Penilaian Portofolio Mahasiswa') ?></div>
            <table class="rps-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">Komponen Artefak Pembelajaran</th>
                        <th style="width: 8%;">Bobot</th>
                        <th style="width: 32%;">Kriteria Penilaian Mutu Artefak</th>
                        <th style="width: 18%;">Bentuk Bukti Fisik</th>
                        <th style="width: 12%;">Batas Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artefakList as $idx => $art): ?>
                        <tr>
                            <td class="text-center text-bold"><?= htmlspecialchars($art['no'] ?? ($idx + 1)) ?></td>
                            <td class="text-bold">
                                <?= htmlspecialchars($art['nama']) ?>
                                <?php if (!empty($art['sub_cpmk'])): ?>
                                    <div style="font-size: 8.5pt; font-weight: normal; color: #444;">(<?= htmlspecialchars($art['sub_cpmk']) ?>)</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-bold"><?= floatval($art['bobot'] ?? 0) ?>%</td>
                            <td><?= htmlspecialchars($art['kriteria'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($art['bukti'] ?? '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($art['minggu'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e2e8f0; font-weight: bold; font-size: 9.5pt;">
                        <td colspan="2" class="text-right" style="padding-right: 14px;">Total Bobot Portofolio</td>
                        <td class="text-center text-bold"><?= $totalBobotPortofolio ?>%</td>
                        <td colspan="3" style="padding-left: 10px;">
                            Skor Akhir Portofolio = &sum; (Nilai Capaian Artefak [0-100] &times; Bobot Artefak)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!$hasActiveRubrik): ?>
            <div style="border: 1px dashed #64748b; padding: 20px; text-align: center; color: #475569; font-style: italic; background: #f8fafc; border-radius: 4px; margin-bottom: 20px;">
                Mata kuliah ini tidak menetapkan instrumen rubrik penilaian terpisah pada lampiran dokumen RPS ini. Instrumen asesmen dan rubrik penilaian disesuaikan secara langsung pada penugasan berkala di sistem pembelajaran (LMS).
            </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>

    <!-- Auto-trigger Print if requested via query parameter ?print=1 -->
    <?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            });
        </script>
    <?php endif; ?>

    <!-- Script Pengaturan Tema Dokumen (Dark / Light Preview) -->
    <script>
        function applyDocTheme(theme) {
            document.body.classList.remove('theme-dark', 'theme-light');
            document.body.classList.add(theme === 'light' ? 'theme-light' : 'theme-dark');
            localStorage.setItem('theme', theme);

            const icon = document.getElementById('docThemeIcon');
            const text = document.getElementById('docThemeText');
            const btn = document.getElementById('btnDocTheme');
            if (icon && text) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-sun';
                    text.textContent = 'Light';
                    if (btn) btn.setAttribute('title', 'Ganti ke Latar Gelap (Dark Mode)');
                } else {
                    icon.className = 'fa-solid fa-moon';
                    text.textContent = 'Dark';
                    if (btn) btn.setAttribute('title', 'Ganti ke Latar Terang (Light Mode)');
                }
            }
        }

        function toggleDocTheme() {
            const isLight = document.body.classList.contains('theme-light');
            applyDocTheme(isLight ? 'dark' : 'light');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            applyDocTheme(savedTheme);
        });
    </script>

</body>
</html>
