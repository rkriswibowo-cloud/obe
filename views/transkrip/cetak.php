<?php
$kopImgPath = __DIR__ . '/../../public/assets/images/kop_surat_uam.jpg';
$kopImgSrc = file_exists($kopImgPath) 
    ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopImgPath))
    : (($GLOBALS['config']['base_url'] ?? '/obe/public') . '/assets/images/kop_surat_uam.jpg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Akademik & Capaian CPL - <?= htmlspecialchars($mahasiswa['nama_lengkap']) ?> (<?= htmlspecialchars($mahasiswa['username']) ?>)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            margin: 0;
            padding: 24px 10px;
            font-size: 11px;
            line-height: 1.35;
        }

        /* Top Action Bar (Hanya tampil di layar) */
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-print {
            background: #4f46e5;
            color: white;
        }
        .btn-print:hover {
            background: #4338ca;
        }

        /* Kertas Dokumen A4 */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            background: white;
            padding: 18mm 16mm 18mm 16mm;
            border-radius: 4px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Page Break untuk Halaman Kedua saat Cetak */
        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* Kop Surat Banner Resmi Universitas Anwar Medika */
        .kop-banner-wrapper {
            width: 100%;
            margin-bottom: 10px;
        }
        .kop-banner-img {
            width: 100%;
            height: auto;
            max-height: 98px;
            object-fit: contain;
            display: block;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title-box h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0f172a;
            text-decoration: underline;
        }
        .doc-title-box .doc-sub {
            font-size: 10px;
            color: #475569;
            font-style: italic;
            margin-top: 1px;
        }
        .doc-title-box .doc-no {
            font-size: 10px;
            font-weight: 600;
            color: #334155;
            margin-top: 3px;
            letter-spacing: 0.04em;
        }

        /* Biodata Mahasiswa */
        .bio-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .bio-grid td {
            padding: 4px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .bio-label {
            width: 14%;
            font-weight: 600;
            color: #475569;
        }
        .bio-sep {
            width: 1.5%;
            text-align: center;
            color: #94a3b8;
        }
        .bio-val {
            width: 34.5%;
            font-weight: 600;
            color: #0f172a;
        }

        /* 2 Kolom Tabel Transkrip Nilai */
        .transcript-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 14px;
            flex: 1;
        }
        .table-mk {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .table-mk th {
            background-color: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-weight: 700;
            text-align: center;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .table-mk td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 5px;
            color: #1e293b;
            font-size: 8.5px;
        }
        .table-mk tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .mk-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            color: #4f46e5;
            text-align: center;
            font-size: 8px;
        }
        .mk-nama {
            font-weight: 500;
            line-height: 1.25;
        }

        /* Kotak Rekapitulasi Prestasi */
        .rekap-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 14px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rekap-item {
            font-size: 10px;
            color: #334155;
        }
        .rekap-item strong {
            color: #0f172a;
            font-size: 10.5px;
        }
        .rekap-ipk {
            text-align: right;
            border-left: 2px solid #cbd5e1;
            padding-left: 16px;
        }
        .ipk-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .ipk-score {
            font-size: 18px;
            font-weight: 800;
            color: #4f46e5;
            line-height: 1;
            margin-top: 2px;
        }

        /* Tanda Tangan */
        .ttd-row {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 10px;
            page-break-inside: avoid;
        }
        .ttd-col {
            text-align: center;
            width: 200px;
            font-size: 10px;
        }
        .ttd-gap {
            height: 48px;
        }
        .ttd-nama {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }
        .ttd-nip {
            font-size: 9px;
            color: #64748b;
        }

        /* Footer Halaman */
        .sheet-footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* ===== HALAMAN 2: LAMPIRAN CPL / SKPI ===== */
        .lampiran-banner {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(59, 130, 246, 0.04) 100%);
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .lampiran-badge {
            display: inline-block;
            background: #4f46e5;
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Radar & CPL Layout */
        .cpl-top-grid {
            display: grid;
            grid-template-columns: 210px 1fr;
            gap: 16px;
            margin-bottom: 14px;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 4px;
        }
        .radar-box {
            width: 210px;
            height: 200px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cpl-summary-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .cpl-kpi-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
        }
        .kpi-title {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }
        .kpi-val {
            font-size: 16px;
            font-weight: 800;
            margin-top: 2px;
            line-height: 1;
        }

        /* Tabel Capaian CPL */
        .table-cpl {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 14px;
        }
        .table-cpl th {
            background-color: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-weight: 700;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        .table-cpl td {
            border: 1px solid #e2e8f0;
            padding: 4.5px 8px;
            color: #1e293b;
        }
        .cpl-badge-ok {
            background: #dcfce7;
            color: #15803d;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5px;
            white-space: nowrap;
        }
        .cpl-badge-no {
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5px;
            white-space: nowrap;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .page-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 12mm 14mm !important;
                width: 100% !important;
                min-height: auto !important;
                page-break-after: always;
                break-after: page;
            }
            .page-sheet:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi & Cetak Layar -->
    <div class="no-print-bar">
        <a href="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/transkrip?id=<?= $mahasiswa['id'] ?>" class="btn-action btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Menu Transkrip
        </a>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span style="font-size: 12px; color: #64748b;">Pratinjau Cetak 2 Halaman (A4 Resmi)</span>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- HALAMAN 1: TRANSKRIP AKADEMIK RESMI        -->
    <!-- ========================================== -->
    <div class="page-sheet">
        <!-- Kop Surat Banner -->
        <div class="kop-banner-wrapper">
            <img src="<?= $kopImgSrc ?>" alt="Kop Surat Universitas Anwar Medika" class="kop-banner-img">
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title-box">
            <h3>TRANSKRIP AKADEMIK MAHASISWA</h3>
            <div class="doc-sub">ACADEMIC TRANSCRIPT OF RECORDS</div>
            <div class="doc-no">NOMOR: TR-UAM/FTB/SI/<?= htmlspecialchars((string)($angkatan ?? date('Y'))) ?>/<?= str_pad((string)$mahasiswa['id'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>

        <!-- Identitas Mahasiswa -->
        <table class="bio-grid">
            <tr>
                <td class="bio-label">Nama Lengkap</td>
                <td class="bio-sep">:</td>
                <td class="bio-val" style="text-transform: uppercase; font-weight: 700;"><?= htmlspecialchars($mahasiswa['nama_lengkap']) ?></td>
                <td class="bio-label">Fakultas</td>
                <td class="bio-sep">:</td>
                <td class="bio-val">Fakultas Teknologi dan Bisnis</td>
            </tr>
            <tr>
                <td class="bio-label">NIM (Nomor Induk)</td>
                <td class="bio-sep">:</td>
                <td class="bio-val" style="font-family: monospace; font-size: 11px;"><?= htmlspecialchars($mahasiswa['username']) ?></td>
                <td class="bio-label">Program Studi</td>
                <td class="bio-sep">:</td>
                <td class="bio-val">S1 - Sistem Informasi</td>
            </tr>
            <tr>
                <td class="bio-label">Tahun Masuk / Angkatan</td>
                <td class="bio-sep">:</td>
                <td class="bio-val"><?= htmlspecialchars((string)($angkatan ?? '-')) ?></td>
                <td class="bio-label">Kurikulum Acuan</td>
                <td class="bio-sep">:</td>
                <td class="bio-val"><?= htmlspecialchars($kurikulumMhs['nama'] ?? 'Kurikulum OBE') ?></td>
            </tr>
        </table>

        <!-- Tabel Nilai Akademik (2 Kolom Seimbang) -->
        <div class="transcript-columns">
            <!-- Kolom Kiri -->
            <div>
                <table class="table-mk">
                    <thead>
                        <tr>
                            <th style="width: 7%;">No</th>
                            <th style="width: 18%;">Kode</th>
                            <th style="width: 47%; text-align: left;">Mata Kuliah</th>
                            <th style="width: 9%;">SKS</th>
                            <th style="width: 9%;">HM</th>
                            <th style="width: 10%;">AM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($splitMk['left'])): ?>
                            <?php foreach ($splitMk['left'] as $idx => $mk): ?>
                                <tr>
                                    <td style="text-align: center; color: #64748b;"><?= $idx + 1 ?></td>
                                    <td class="mk-kode"><?= htmlspecialchars($mk['mk_kode']) ?></td>
                                    <td class="mk-nama"><?= htmlspecialchars($mk['mk_nama']) ?></td>
                                    <td style="text-align: center; font-weight: 600;"><?= $mk['sks'] ?></td>
                                    <td style="text-align: center; font-weight: 700; color: <?= $mk['nilai_huruf'] === 'A' ? '#15803d' : '#0f172a' ?>;"><?= $mk['nilai_huruf'] ?></td>
                                    <td style="text-align: center;"><?= number_format($mk['bobot'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 12px; color: #94a3b8;">Belum ada data mata kuliah.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Kolom Kanan -->
            <div>
                <table class="table-mk">
                    <thead>
                        <tr>
                            <th style="width: 7%;">No</th>
                            <th style="width: 18%;">Kode</th>
                            <th style="width: 47%; text-align: left;">Mata Kuliah</th>
                            <th style="width: 9%;">SKS</th>
                            <th style="width: 9%;">HM</th>
                            <th style="width: 10%;">AM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($splitMk['right'])): ?>
                            <?php $offset = count($splitMk['left']); ?>
                            <?php foreach ($splitMk['right'] as $idx => $mk): ?>
                                <tr>
                                    <td style="text-align: center; color: #64748b;"><?= $offset + $idx + 1 ?></td>
                                    <td class="mk-kode"><?= htmlspecialchars($mk['mk_kode']) ?></td>
                                    <td class="mk-nama"><?= htmlspecialchars($mk['mk_nama']) ?></td>
                                    <td style="text-align: center; font-weight: 600;"><?= $mk['sks'] ?></td>
                                    <td style="text-align: center; font-weight: 700; color: <?= $mk['nilai_huruf'] === 'A' ? '#15803d' : '#0f172a' ?>;"><?= $mk['nilai_huruf'] ?></td>
                                    <td style="text-align: center;"><?= number_format($mk['bobot'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 12px; color: #94a3b8;">-</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rekapitulasi Prestasi Akademik Kumulatif -->
        <div class="rekap-card">
            <div>
                <div class="rekap-item">Total SKS Ditempuh: <strong><?= $khsData['ringkasan']['total_sks_ditempuh'] ?? 0 ?> SKS</strong></div>
                <div class="rekap-item">Total SKS Lulus Kumulatif: <strong><?= $khsData['ringkasan']['total_sks_lulus'] ?? 0 ?> SKS</strong></div>
            </div>
            <div>
                <div class="rekap-item">Total Bobot Mutu (SKS x AM): <strong><?= number_format($khsData['ringkasan']['total_mutu'] ?? 0, 2) ?></strong></div>
                <div class="rekap-item">Predikat Kelulusan: <strong style="color: #4f46e5;"><?= htmlspecialchars($khsData['ringkasan']['predikat'] ?? '-') ?></strong></div>
            </div>
            <div class="rekap-ipk">
                <div class="ipk-label">INDEKS PRESTASI KUMULATIF</div>
                <div class="ipk-score"><?= number_format($khsData['ringkasan']['ipk'] ?? 0, 2) ?></div>
            </div>
        </div>

        <!-- Pedoman Skala Penilaian Akademik Resmi UAM -->
        <div style="margin-bottom: 12px; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #f8fafc; font-size: 7.5px;">
            <div style="font-weight: 700; color: #334155; margin-bottom: 3px; text-transform: uppercase;">Pedoman Penilaian & Angka Mutu (AM):</div>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4px; color: #475569;">
                <span><strong>A</strong> (4.0): 76-100 (Istimewa)</span>
                <span>•</span>
                <span><strong>AB</strong> (3.5): 70-75 (Baik Sekali)</span>
                <span>•</span>
                <span><strong>B</strong> (3.0): 65-69 (Baik)</span>
                <span>•</span>
                <span><strong>BC</strong> (2.5): 60-64 (Cukup Baik)</span>
                <span>•</span>
                <span><strong>C</strong> (2.0): 50-59 (Cukup)</span>
                <span>•</span>
                <span><strong>D</strong> (1.0): 40-49 (Kurang)</span>
                <span>•</span>
                <span><strong>E</strong> (0.0): 0-39 (Kurang Sekali)</span>
            </div>
        </div>

        <!-- Tanda Tangan Halaman 1 -->
        <div class="ttd-row">
            <div class="ttd-col">
                <div>Mengetahui,</div>
                <div style="font-weight: 700;">Dekan Fakultas Teknologi dan Bisnis</div>
                <div class="ttd-gap"></div>
                <div class="ttd-nama"><?= htmlspecialchars($pejabat['nama_dekan_display'] ?? 'Belum diatur') ?></div>
                <div class="ttd-nip"><?= !empty($pejabat['nidn_dekan_display']) ? 'NIDN/NUPTK. ' . htmlspecialchars($pejabat['nidn_dekan_display']) : '' ?></div>
            </div>

            <div class="ttd-col">
                <div>Sidoarjo, <?= date('d F Y') ?></div>
                <div style="font-weight: 700;">Ketua Program Studi Sistem Informasi</div>
                <div class="ttd-gap"></div>
                <div class="ttd-nama"><?= htmlspecialchars($pejabat['nama_kaprodi_display'] ?? 'Belum diatur') ?></div>
                <div class="ttd-nip"><?= !empty($pejabat['nidn_kaprodi_display']) ? 'NIDN/NUPTK. ' . htmlspecialchars($pejabat['nidn_kaprodi_display']) : '' ?></div>
            </div>
        </div>

        <!-- Footer Halaman 1 -->
        <div class="sheet-footer">
            <span>Universitas Anwar Medika • Sistem Penjaminan Mutu & Kurikulum OBE</span>
            <span style="font-style: italic;">* Lembar Lanjutan Portofolio Capaian Pembelajaran Lulusan (CPL) terlampir pada Halaman 2</span>
            <span>Halaman 1 dari 2</span>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- HALAMAN 2: LAMPIRAN CAPAIAN CPL (OBE/SKPI) -->
    <!-- ========================================== -->
    <div class="page-sheet page-break">
        <!-- Kop Surat Banner -->
        <div class="kop-banner-wrapper">
            <img src="<?= $kopImgSrc ?>" alt="Kop Surat Universitas Anwar Medika" class="kop-banner-img">
        </div>

        <!-- Judul Lampiran -->
        <div class="doc-title-box">
            <h3>LAMPIRAN HASIL PENGUKURAN CAPAIAN PEMBELAJARAN LULUSAN (CPL)</h3>
            <div class="doc-sub">PORTFOLIO OF GRADUATE LEARNING OUTCOME ATTAINMENT (OBE ATTAINMENT MATRIX)</div>
            <div class="doc-no">Lampiran Menjadi Satu Kesatuan dengan Transkrip Nomor: TR-UAM/FTB/SI/<?= htmlspecialchars((string)($angkatan ?? date('Y'))) ?>/<?= str_pad((string)$mahasiswa['id'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>

        <!-- Identitas Mahasiswa Singkat -->
        <div class="lampiran-banner">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #0f172a;">
                    <?= htmlspecialchars($mahasiswa['nama_lengkap']) ?> • NIM: <?= htmlspecialchars($mahasiswa['username']) ?>
                </div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">
                    Fakultas: <strong>Fakultas Teknologi dan Bisnis</strong> • 
                    Program Studi: <strong>S1 Sistem Informasi</strong> • 
                    Kurikulum: <strong><?= htmlspecialchars($kurikulumMhs['nama'] ?? 'OBE') ?></strong> (Angkatan <?= htmlspecialchars((string)($angkatan ?? '-')) ?>)
                </div>
            </div>
            <div>
                <span class="lampiran-badge">SKPI / OBE TERVALIDASI</span>
            </div>
        </div>

        <!-- Grid Radar Chart & Ringkasan Capaian CPL -->
        <div class="cpl-top-grid">
            <div class="radar-box">
                <canvas id="cplPrintRadarChart"></canvas>
            </div>

            <div class="cpl-summary-box">
                <div class="cpl-kpi-card">
                    <div class="kpi-title">Rata-rata Skor CPL</div>
                    <div class="kpi-val" style="color: <?= ($cplData['ringkasan']['rata_rata_skor'] ?? 0) >= 70 ? '#15803d' : '#d97706' ?>;">
                        <?= number_format($cplData['ringkasan']['rata_rata_skor'] ?? 0, 2) ?>%
                    </div>
                    <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Target standar mutu kelulusan ≥ 70%</div>
                </div>

                <div class="cpl-kpi-card">
                    <div class="kpi-title">CPL Memenuhi Standar</div>
                    <div class="kpi-val" style="color: #15803d;">
                        <?= $cplData['ringkasan']['cpl_tercapai'] ?? 0 ?> <span style="font-size: 11px; color: #64748b; font-weight: normal;">/ <?= $cplData['ringkasan']['total_cpl'] ?? 0 ?> CPL</span>
                    </div>
                    <div style="font-size: 8px; color: #15803d; margin-top: 2px;">
                        Tercapai: <?= $cplData['ringkasan']['persentase_tercapai'] ?? 0 ?>% dari seluruh CPL
                    </div>
                </div>

                <div class="cpl-kpi-card">
                    <div class="kpi-title">CPL Belum Memenuhi</div>
                    <div class="kpi-val" style="color: <?= ($cplData['ringkasan']['cpl_belum_tercapai'] ?? 0) > 0 ? '#b91c1c' : '#64748b' ?>;">
                        <?= $cplData['ringkasan']['cpl_belum_tercapai'] ?? 0 ?> CPL
                    </div>
                    <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Perlu perbaikan mutu/remediasi</div>
                </div>

                <div class="cpl-kpi-card">
                    <div class="kpi-title">Status Kualifikasi OBE</div>
                    <div class="kpi-val" style="font-size: 12px; color: #4f46e5; margin-top: 4px;">
                        <?= ($cplData['ringkasan']['cpl_tercapai'] ?? 0) >= ($cplData['ringkasan']['total_cpl'] ?? 9) ? 'MEMENUHI (LULUS OBE)' : 'DALAM PEMENUHAN' ?>
                    </div>
                    <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Standar Asosiasi Pendidikan SI</div>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Capaian CPL -->
        <table class="table-cpl">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Kode</th>
                    <th style="width: 53%; text-align: left;">Deskripsi Capaian Pembelajaran Lulusan (CPL)</th>
                    <th style="width: 10%;">Standar</th>
                    <th style="width: 11%;">Capaian</th>
                    <th style="width: 11%;">Status Mutu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cplData['cpl_list'])): ?>
                    <?php $noCpl = 1; foreach ($cplData['cpl_list'] as $c): 
                        $isPassed = ($c['status'] === 'Tercapai');
                    ?>
                        <tr>
                            <td style="text-align: center; color: #64748b;"><?= $noCpl++ ?></td>
                            <td style="text-align: center; font-weight: 700; color: #4f46e5;"><?= htmlspecialchars($c['kode']) ?></td>
                            <td style="line-height: 1.35;"><?= htmlspecialchars($c['rumusan']) ?></td>
                            <td style="text-align: center; color: #64748b; font-weight: 600;"><?= $c['target'] ?>%</td>
                            <td style="text-align: center; font-weight: 700; color: <?= $isPassed ? '#15803d' : ($c['is_measured'] ? '#b91c1c' : '#64748b') ?>;">
                                <?= $c['is_measured'] ? number_format($c['score'], 2) . '%' : '-' ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($isPassed): ?>
                                    <span class="cpl-badge-ok"><i class="fa-solid fa-check"></i> Tercapai</span>
                                <?php elseif ($c['is_measured']): ?>
                                    <span class="cpl-badge-no"><i class="fa-solid fa-xmark"></i> Belum</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 8px;">Belum Diukur</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 12px; color: #94a3b8;">Belum ada CPL yang terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan Halaman 2 (Dekan dan Kaprodi) -->
        <div class="ttd-row">
            <div class="ttd-col">
                <div>Mengetahui & Mengesahkan,</div>
                <div style="font-weight: 700;">Dekan Fakultas Teknologi dan Bisnis</div>
                <div class="ttd-gap"></div>
                <div class="ttd-nama"><?= htmlspecialchars($pejabat['nama_dekan_display'] ?? 'Belum diatur') ?></div>
                <div class="ttd-nip"><?= !empty($pejabat['nidn_dekan_display']) ? 'NIDN/NUPTK. ' . htmlspecialchars($pejabat['nidn_dekan_display']) : '' ?></div>
            </div>

            <div class="ttd-col">
                <div>Sidoarjo, <?= date('d F Y') ?></div>
                <div style="font-weight: 700;">Ketua Program Studi Sistem Informasi</div>
                <div class="ttd-gap"></div>
                <div class="ttd-nama"><?= htmlspecialchars($pejabat['nama_kaprodi_display'] ?? 'Belum diatur') ?></div>
                <div class="ttd-nip"><?= !empty($pejabat['nidn_kaprodi_display']) ? 'NIDN/NUPTK. ' . htmlspecialchars($pejabat['nidn_kaprodi_display']) : '' ?></div>
            </div>
        </div>

        <!-- Footer Halaman 2 -->
        <div class="sheet-footer">
            <span>Universitas Anwar Medika • Sistem Penjaminan Mutu & Kurikulum OBE</span>
            <span style="font-style: italic;">Lampiran Resmi Penilaian Berbasis Capaian Hasil Belajar Mahasiswa (OBE)</span>
            <span>Halaman 2 dari 2</span>
        </div>
    </div>

    <!-- Script Inisialisasi Chart.js Radar CPL (Instant Render for Printing) -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const radarPayload = <?= json_encode($radarPayload) ?>;
        const ctx = document.getElementById('cplPrintRadarChart').getContext('2d');

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: radarPayload.labels,
                datasets: [
                    {
                        label: 'Skor Mahasiswa',
                        data: radarPayload.scores,
                        backgroundColor: 'rgba(79, 70, 229, 0.22)',
                        borderColor: '#4f46e5',
                        borderWidth: 2,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#ffffff',
                        pointRadius: 3
                    },
                    {
                        label: 'Standar Target',
                        data: radarPayload.targets,
                        backgroundColor: 'transparent',
                        borderColor: '#ef4444',
                        borderWidth: 1.5,
                        borderDash: [4, 3],
                        pointBackgroundColor: '#ef4444',
                        pointRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Wajib false agar langsung ter-render saat window.print() dipanggil
                scales: {
                    r: {
                        angleLines: { color: 'rgba(0, 0, 0, 0.1)' },
                        grid: { color: 'rgba(0, 0, 0, 0.08)' },
                        pointLabels: {
                            font: { family: "'Inter', sans-serif", size: 8, weight: '700' },
                            color: '#1e293b'
                        },
                        ticks: {
                            display: false,
                            stepSize: 20
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
    </script>
</body>
</html>
