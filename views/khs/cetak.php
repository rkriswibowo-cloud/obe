<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak KHS & Portofolio CPL - <?= htmlspecialchars($mahasiswa['nama_lengkap']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            color: #111827;
            background: #f9fafb;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .print-container {
            max-width: 860px;
            margin: 0 auto;
            background: white;
            padding: 36px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .kop-header {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop-header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .kop-header h3 {
            margin: 3px 0;
            font-size: 14px;
            font-weight: 600;
        }
        .kop-header p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #4b5563;
        }
        .doc-title {
            text-align: center;
            margin: 16px 0 20px;
        }
        .doc-title h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .bio-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .bio-table td {
            padding: 3px 6px;
            font-size: 11px;
            vertical-align: top;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .table-data th, .table-data td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 10.5px;
        }
        .table-data th {
            background-color: #f3f4f6;
            font-weight: 700;
            text-align: center;
        }
        .rekap-box {
            display: flex;
            justify-content: space-between;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 11px;
        }
        .ttd-container {
            display: flex;
            justify-content: space-between;
            margin-top: 36px;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            width: 220px;
        }
        .ttd-space {
            height: 60px;
        }
        .no-print-bar {
            max-width: 860px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/khs?id=<?= $mahasiswa['id'] ?>" style="color: #4b5563; text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Sistem
        </a>
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Cetak Dokumen (PDF)
        </button>
    </div>

    <div class="print-container">
        <!-- Kop Surat -->
        <div class="kop-header">
            <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h3>PROGRAM STUDI SISTEM INFORMASI</h3>
            <p>SISTEM INFORMASI OUTCOME-BASED EDUCATION (OBE) & CAPAIAN PEMBELAJARAN LULUSAN (CPL)</p>
        </div>

        <div class="doc-title">
            <h4>KARTU HASIL STUDI (KHS) & PORTOFOLIO KETERCAPAIAN CPL</h4>
        </div>

        <!-- Biodata Mahasiswa -->
        <table class="bio-table">
            <tr>
                <td style="width: 15%; font-weight: 600;">Nama Mahasiswa</td>
                <td style="width: 2%;">:</td>
                <td style="width: 43%; font-weight: 700;"><?= htmlspecialchars($mahasiswa['nama_lengkap']) ?></td>
                <td style="width: 15%; font-weight: 600;">Tahun Akademik</td>
                <td style="width: 2%;">:</td>
                <td style="width: 23%;"><?= !empty($filterTaId) ? htmlspecialchars($khsData['semua_mk'][0]['tahun_akademik_nama'] ?? 'Semua') : 'Semua Semester (Kumulatif)' ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600;">NIM</td>
                <td>:</td>
                <td><?= htmlspecialchars($mahasiswa['username']) ?></td>
                <td style="font-weight: 600;">Program Studi</td>
                <td>:</td>
                <td>Sistem Informasi (S1)</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Dosen PA</td>
                <td>:</td>
                <td>Dr. Budi Santoso, M.Kom.</td>
                <td style="font-weight: 600;">Status Mahasiswa</td>
                <td>:</td>
                <td>Aktif</td>
            </tr>
        </table>

        <!-- BAGIAN 1: KARTU HASIL STUDI -->
        <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px; text-transform: uppercase;">
            I. Rincian Nilai Mata Kuliah (KHS)
        </div>
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Kode MK</th>
                    <th style="width: 38%; text-align: left;">Nama Mata Kuliah</th>
                    <th style="width: 8%;">SKS</th>
                    <th style="width: 9%;">Nilai</th>
                    <th style="width: 9%;">Huruf</th>
                    <th style="width: 9%;">Bobot</th>
                    <th style="width: 10%;">SKS x Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($khsData['semua_mk'])): ?>
                    <?php $no = 1; foreach ($khsData['semua_mk'] as $mk): ?>
                        <tr>
                            <td style="text-align: center;"><?= $no++ ?></td>
                            <td style="text-align: center; font-family: monospace; font-weight: 600;"><?= htmlspecialchars($mk['mk_kode']) ?></td>
                            <td><?= htmlspecialchars($mk['mk_nama']) ?></td>
                            <td style="text-align: center;"><?= $mk['sks'] ?></td>
                            <td style="text-align: center;"><?= $mk['nilai_angka'] !== null ? number_format($mk['nilai_angka'], 2) : '-' ?></td>
                            <td style="text-align: center; font-weight: 700;"><?= $mk['nilai_huruf'] ?></td>
                            <td style="text-align: center;"><?= number_format($mk['bobot'], 2) ?></td>
                            <td style="text-align: center; font-weight: 600;"><?= number_format($mk['sks_x_bobot'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 12px;">Belum ada mata kuliah yang terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Rekapitulasi Hasil Belajar -->
        <div class="rekap-box">
            <div>
                <div>Total SKS Ditempuh: <strong><?= $khsData['ringkasan']['total_sks_ditempuh'] ?> SKS</strong></div>
                <div>Total SKS Lulus: <strong><?= $khsData['ringkasan']['total_sks_lulus'] ?> SKS</strong></div>
            </div>
            <div>
                <div>Total Bobot Mutu: <strong><?= $khsData['ringkasan']['total_mutu'] ?></strong></div>
                <div>Predikat: <strong><?= htmlspecialchars($khsData['ringkasan']['predikat']) ?></strong></div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 10px; color: #4b5563; font-weight: 600;">INDEKS PRESTASI KUMULATIF (IPK)</div>
                <div style="font-size: 18px; font-weight: 800; color: #2563eb;"><?= number_format($khsData['ringkasan']['ipk'], 2) ?></div>
            </div>
        </div>

        <!-- BAGIAN 2: KETERCAPAIAN CPL -->
        <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px; text-transform: uppercase;">
            II. Portofolio Ketercapaian Capaian Pembelajaran Lulusan (CPL)
        </div>
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 10%;">Kode</th>
                    <th style="width: 55%; text-align: left;">Deskripsi Capaian Pembelajaran (CPL)</th>
                    <th style="width: 11%;">Target</th>
                    <th style="width: 12%;">Skor Mahasiswa</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cplData['cpl_list'] as $c): ?>
                    <tr>
                        <td style="text-align: center; font-weight: 700;"><?= htmlspecialchars($c['kode']) ?></td>
                        <td><?= htmlspecialchars($c['rumusan']) ?></td>
                        <td style="text-align: center;"><?= $c['target'] ?>%</td>
                        <td style="text-align: center; font-weight: 700;">
                            <?= $c['is_measured'] ? number_format($c['score'], 2) . '%' : '-' ?>
                        </td>
                        <td style="text-align: center; font-weight: 600; color: <?= $c['status'] === 'Tercapai' ? '#059669' : ($c['status'] === 'Belum Tercapai' ? '#dc2626' : '#6b7280') ?>;">
                            <?= $c['status'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 600;">Dosen Pembimbing Akademik</div>
                <div class="ttd-space"></div>
                <div style="font-weight: 700; text-decoration: underline;">Dr. Budi Santoso, M.Kom.</div>
                <div>NIP. 198005122005011002</div>
            </div>

            <div class="ttd-box">
                <div>Surabaya, <?= date('d F Y') ?></div>
                <div style="font-weight: 600;">Ketua Program Studi</div>
                <div class="ttd-space"></div>
                <div style="font-weight: 700; text-decoration: underline;">Rony Kriswibowo, M.Kom.</div>
                <div>NIP. 198507202010121003</div>
            </div>
        </div>
    </div>

</body>
</html>
