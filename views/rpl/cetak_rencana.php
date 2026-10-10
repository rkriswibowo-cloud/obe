<?php
$base = $GLOBALS['config']['base_url'] ?? '/obe/public';
$kopPath = __DIR__ . '/../../public/assets/images/kop_surat_uam.jpg';
$kopSrc = file_exists($kopPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath)) : $base . '/assets/images/kop_surat_uam.jpg';
$year = (int)date('Y');
$academicStart = (int)date('n') >= 7 ? $year : $year - 1;
$academicYear = $academicStart . '/' . ($academicStart + 1);
$nim = (string)($profile['nim'] ?? '');
$angkatan = preg_match('/^(20\d{2})/', $nim, $match) ? $match[1] : (preg_match('/^(\d{2})/', $nim, $match) ? '20' . $match[1] : '-');
$totalSks = array_sum(array_map(fn($row) => (int)$row['sks'], $plan));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>KRS RPL Semester <?= $semester ?> - <?= htmlspecialchars($profile['nama_lengkap']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page{size:A4 portrait;margin:12mm 14mm}*{box-sizing:border-box}body{margin:0;padding:20px;background:#eef1f5;color:#111;font-family:Arial,Helvetica,sans-serif;font-size:12px}.toolbar{max-width:850px;margin:0 auto 14px;padding:12px 14px;background:#fff;border:1px solid #d9dee7;border-radius:8px;display:flex;align-items:end;gap:12px;box-shadow:0 3px 12px rgba(15,23,42,.08)}.toolbar-field{flex:1}.toolbar label{display:block;margin-bottom:5px;font-size:11px;font-weight:700;color:#374151}.toolbar input{width:100%;height:36px;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px}.toolbar button,.toolbar a{height:36px;display:inline-flex;align-items:center;gap:6px;padding:0 13px;border-radius:6px;border:0;text-decoration:none;font-weight:700;cursor:pointer;white-space:nowrap}.btn-back{background:#e5e7eb;color:#374151}.btn-print{background:#2563eb;color:#fff}.sheet{max-width:850px;min-height:1100px;margin:auto;padding:34px 38px;background:#fff;box-shadow:0 5px 20px rgba(15,23,42,.12)}.kop img{display:block;width:100%;max-height:100px;object-fit:contain}.title{text-align:center;margin:12px 0 16px}.title h1{font-family:"Times New Roman",serif;font-size:17px;letter-spacing:.04em;text-decoration:underline;margin:0}.title p{margin:4px 0 0;font-size:11px}.bio{width:100%;border-collapse:collapse;margin-bottom:14px}.bio td{padding:3px 4px;vertical-align:top}.bio .label{width:15%;font-weight:700}.bio .colon{width:2%}.bio .value{width:33%}.course-table{width:100%;border-collapse:collapse;font-family:"Times New Roman",serif;font-size:12px}.course-table th,.course-table td{border:1px solid #9ca3af;padding:6px 8px}.course-table th{text-align:center;background:#f3f4f6;font-size:12px}.course-table .number,.course-table .code,.course-table .sks{text-align:center}.course-table .number{width:6%}.course-table .code{width:17%;font-weight:700}.course-table .sks{width:10%}.course-table .total-label{text-align:right;font-weight:700}.notes{margin-top:22px;min-height:55px}.notes strong{display:block;margin-bottom:5px}.signature{display:grid;grid-template-columns:1fr 1fr;gap:100px;margin-top:25px;page-break-inside:avoid}.signature-box{text-align:center;min-height:130px}.signature-space{height:70px}.signature-name{font-weight:700;text-decoration:underline}.signature-id{font-size:10px;margin-top:3px}.watermark{position:absolute;inset:285px 0 auto;text-align:center;opacity:.035;pointer-events:none}.watermark img{width:390px}.sheet{position:relative;overflow:hidden}.sheet>*:not(.watermark){position:relative;z-index:1}@media(max-width:700px){body{padding:8px}.toolbar{align-items:stretch;flex-direction:column}.toolbar button,.toolbar a{justify-content:center}.sheet{padding:20px 16px;min-height:0}.signature{gap:20px}.bio .label{width:auto}}@media print{body{padding:0;background:#fff}.toolbar{display:none!important}.sheet{max-width:none;min-height:0;padding:0;box-shadow:none}.course-table th{background:#f3f4f6!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="btn-back" href="<?= $base ?>/rpl/detail?id=<?= (int)$profile['id'] ?>#rencana-studi"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <div class="toolbar-field"><label for="paNameInput">Dosen Penasihat Akademik</label><input id="paNameInput" placeholder="Masukkan nama dan gelar Dosen PA" oninput="syncAdvisor()"></div>
        <div class="toolbar-field"><label for="paIdInput">NIDN/NUPTK (opsional)</label><input id="paIdInput" placeholder="Masukkan NIDN atau NUPTK" oninput="syncAdvisor()"></div>
        <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak / PDF</button>
    </div>
    <main class="sheet">
        <div class="kop"><img src="<?= $kopSrc ?>" alt="Kop Universitas Anwar Medika"></div>
        <div class="title"><h1>KARTU RENCANA STUDI (KRS) RPL</h1><p>Rencana Perkuliahan Semester <?= $semester ?></p></div>
        <table class="bio">
            <tr><td class="label">NIM</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($nim) ?></td><td class="label">Tahun Akademik</td><td class="colon">:</td><td class="value"><?= $academicYear ?></td></tr>
            <tr><td class="label">Nama</td><td class="colon">:</td><td class="value"><strong><?= htmlspecialchars($profile['nama_lengkap']) ?></strong></td><td class="label">Semester RPL</td><td class="colon">:</td><td class="value"><?= $semester ?></td></tr>
            <tr><td class="label">Program Studi</td><td class="colon">:</td><td class="value">S1 Sistem Informasi</td><td class="label">Tahun Angkatan</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($angkatan) ?></td></tr>
            <tr><td class="label">Kurikulum</td><td class="colon">:</td><td class="value"><?= htmlspecialchars($profile['kurikulum_nama']) ?></td><td class="label">Dosen PA</td><td class="colon">:</td><td class="value" id="paBio">Belum diisi</td></tr>
        </table>
        <table class="course-table">
            <thead><tr><th class="number">#</th><th class="code">KODE</th><th>MATA KULIAH</th><th class="sks">SKS</th></tr></thead>
            <tbody><?php foreach($plan as $index=>$course): ?><tr><td class="number"><?= $index+1 ?>.</td><td class="code"><?= htmlspecialchars($course['kode']) ?></td><td><?= htmlspecialchars($course['nama']) ?></td><td class="sks"><?= (int)$course['sks'] ?></td></tr><?php endforeach; ?><?php if(empty($plan)): ?><tr><td colspan="4" style="text-align:center;padding:18px">Belum ada mata kuliah pada semester ini.</td></tr><?php endif; ?><tr><td colspan="3" class="total-label">Jumlah SKS</td><td class="sks"><strong><?= $totalSks ?></strong></td></tr></tbody>
        </table>
        <div class="notes"><strong>Catatan:</strong><div>................................................................................................................................................................................</div><div>................................................................................................................................................................................</div></div>
        <div class="signature">
            <div class="signature-box">Mahasiswa,<div class="signature-space"></div><div class="signature-name"><?= htmlspecialchars($profile['nama_lengkap']) ?></div><div class="signature-id">NIM. <?= htmlspecialchars($nim) ?></div></div>
            <div class="signature-box">Mengetahui,<br>Dosen Penasihat Akademik<div class="signature-space"></div><div class="signature-name" id="paSignature">........................................</div><div class="signature-id" id="paIdSignature"></div></div>
        </div>
    </main>
    <script>function syncAdvisor(){const name=document.getElementById('paNameInput').value.trim();const id=document.getElementById('paIdInput').value.trim();document.getElementById('paBio').textContent=name||'Belum diisi';document.getElementById('paSignature').textContent=name||'........................................';document.getElementById('paIdSignature').textContent=id?('NIDN/NUPTK. '+id):'';}</script>
</body>
</html>
