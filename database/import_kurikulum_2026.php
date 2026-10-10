<?php
// database/import_kurikulum_2026.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=======================================================\n";
echo " IMPORT & SINKRONISASI KURIKULUM OBE SI 2026 DARI EXCEL\n";
echo "=======================================================\n";

$excelPath = __DIR__ . '/../Kurikulum OBE SI 2026.xlsx';
if (!file_exists($excelPath)) {
    die("Error: File '$excelPath' tidak ditemukan.\n");
}

$zip = new ZipArchive();
if ($zip->open($excelPath) !== true) {
    die("Error: Tidak dapat membuka file excel '$excelPath'.\n");
}

// 1. Baca shared strings
$strings = [];
if ($zip->locateName('xl/sharedStrings.xml') !== false) {
    $sXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
    foreach ($sXml->si as $si) {
        if (isset($si->t)) {
            $strings[] = (string)$si->t;
        } elseif (isset($si->r)) {
            $text = '';
            foreach ($si->r as $r) $text .= (string)$r->t;
            $strings[] = $text;
        } else {
            $strings[] = '';
        }
    }
}

// 2. Baca relationships
$relsXml = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relMap = [];
foreach ($relsXml->Relationship as $r) {
    $relMap[(string)$r['Id']] = (string)$r['Target'];
}

$wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
$sheetFiles = [];
foreach ($wb->sheets->sheet as $s) {
    $name = (string)$s['name'];
    $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $sheetFiles[$name] = 'xl/' . $relMap[$rId];
}

function getSheetRows($zip, $filePath, $strings) {
    if (!$filePath || $zip->locateName($filePath) === false) return [];
    $sheetXml = simplexml_load_string($zip->getFromName($filePath));
    $rows = [];
    foreach ($sheetXml->sheetData->row as $r) {
        $rowNum = (int)$r['r'];
        $cols = [];
        foreach ($r->c as $c) {
            $ref = preg_replace('/[0-9]/', '', (string)$c['r']);
            $val = '';
            if (isset($c->v)) {
                $raw = (string)$c->v;
                $val = ((string)$c['t'] === 's') ? ($strings[(int)$raw] ?? '') : $raw;
            }
            $cols[$ref] = trim($val);
        }
        $rows[$rowNum] = $cols;
    }
    return $rows;
}

// -------------------------------------------------------------
// 1. UPDATE METADATA KURIKULUM 2026 (ID = 1)
// -------------------------------------------------------------
echo "\n--- 1. MENYIAPKAN DATA KURIKULUM 2026 ---\n";
$kurikulumId = 1;
$db->prepare("
    UPDATE kurikulum 
    SET nama = 'KURIKULUM OBE 2026', 
        kode = 'KURIKULUM OBE 2026', 
        tahun_mulai = 2026, 
        versi = '1.0', 
        status = 'ACTIVE',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id
")->execute(['id' => $kurikulumId]);
echo "- Kurikulum ID $kurikulumId berhasil diperbarui ke 'KURIKULUM OBE 2026'.\n";

// -------------------------------------------------------------
// 2. IMPORT / SINKRONISASI PROFIL LULUSAN
// -------------------------------------------------------------
echo "\n--- 2. IMPORT PROFIL LULUSAN DARI EXCEL 2026 ---\n";
$plDefinitions = [
    'PL01' => [
        'nama' => 'Eksekutif Profesional (kekhasan universitas)',
        'deskripsi' => "Lulusan mampu bekerja secara profesional, beretika, berkomunikasi efektif, berkolaborasi multidisiplin, serta memimpin pengembangan solusi digital sesuai kebutuhan.\nProfesi: Eksekutif Profesional (kekhasan universitas)"
    ],
    'PL02' => [
        'nama' => 'System Analyst, IT Auditor, Data Analyst',
        'deskripsi' => "Lulusan memiliki kemampuan menganalisis, merancang, membuat, mengaudit, dan melakukan evaluasi sistem informasi yang selaras dengan tujuan organisasi.\nProfesi: System Analyst, IT Auditor, IT Governance, Data Analyst (Aptikom: IS2020)"
    ],
    'PL03' => [
        'nama' => 'IT Consultant and Developer, Project Manager',
        'deskripsi' => "Lulusan memiliki kemampuan memahami, menerapkan dan mengintegrasikan model sistem, menggunakan metode dan berbagai teknik peningkatan bisnis proses yang mendatangkan suatu nilai untuk organisasi.\nProfesi: IT Consultant and Developer, Project Manager, Computer Support (Aptikom: IS2020)"
    ],
    'PL04' => [
        'nama' => 'Health Informatics Specialist (kekhasan prodi)',
        'deskripsi' => "Lulusan memiliki kemampuan mengembangkan dan menerapkan solusi Health Informatics berbasis teknologi cerdas untuk mendukung transformasi digital, pengelolaan data, dan peningkatan kualitas layanan kesehatan.\nProfesi: Health Informatics Specialist (kekhasan prodi)"
    ],
    'PL05' => [
        'nama' => 'Technopreneur (kekhasan prodi)',
        'deskripsi' => "Lulusan memiliki kemampuan mengidentifikasi peluang, merancang, mengembangkan, dan mengelola produk atau layanan digital yang inovatif dan bernilai bisnis dengan menjunjung etika profesi, karakter humanis, dan tanggung jawab sosial.\nProfesi: Technopreneur (kekhasan prodi)"
    ]
];

$plMap = []; // kode => id
foreach ($plDefinitions as $kode => $plData) {
    $existingPlId = $db->query("SELECT id FROM profil_lulusan WHERE kurikulum_id = $kurikulumId AND kode = '$kode'")->fetchColumn();
    if ($existingPlId) {
        $db->prepare("UPDATE profil_lulusan SET nama = :nama, deskripsi = :deskripsi WHERE id = :id")->execute([
            'nama' => $plData['nama'],
            'deskripsi' => $plData['deskripsi'],
            'id' => $existingPlId
        ]);
        $plMap[$kode] = (int)$existingPlId;
    } else {
        $stmtIns = $db->prepare("INSERT INTO profil_lulusan (kurikulum_id, kode, nama, deskripsi) VALUES (:kur_id, :kode, :nama, :deskripsi)");
        $stmtIns->execute([
            'kur_id' => $kurikulumId,
            'kode' => $kode,
            'nama' => $plData['nama'],
            'deskripsi' => $plData['deskripsi']
        ]);
        $plMap[$kode] = (int)$db->lastInsertId();
    }
}
echo "- 5 Profil Lulusan (PL01 - PL05) berhasil disinkronisasi untuk Kurikulum ID $kurikulumId.\n";

// -------------------------------------------------------------
// 3. IMPORT / SINKRONISASI CPL 2026 (CPL01 - CPL09)
// -------------------------------------------------------------
echo "\n--- 3. IMPORT CPL PRODI (CPL01 - CPL09) DARI EXCEL 2026 ---\n";
$cplDefinitions = [
    'CPL01' => [
        'rumusan' => 'Lulusan Bertaqwa kepada Tuhan yang Maha Esa dengan menjunjung tinggi nasionalisme, humanisme, entrepreneurship, dan 7 nilai dasar Universitas Anwar Medika (profesional, visioner, quality focus, trust, integrity, moral, exellence) di bidang Sistem Informasi',
        'kategori' => 'Sikap',
        'target' => 70.00
    ],
    'CPL02' => [
        'rumusan' => 'Lulusan mampu menerapkan pemikiran logis, kritis, dan inovatif dalam pengembangan IPTEK berbasis nilai humaniora; menunjukkan kinerja mandiri dan terukur; menghasilkan serta mengkomunikasikan karya ilmiah sesuai kaidah; mengambil keputusan berbasis data; bekerja sama dan bertanggung jawab dalam tim; serta mengelola pembelajaran dan data secara mandiri, sahih, dan aman',
        'kategori' => 'Keterampilan Umum',
        'target' => 70.00
    ],
    'CPL03' => [
        'rumusan' => 'Lulusan mampu memahami metodologi penelitian, pengembangan produk digital, serta perkembangan teknologi dan bisnis, dan mampu menerapkan pemikiran logis, kritis, sistematis, kreatif, dan inovatif dalam mengidentifikasi masalah, menyusun proposal, melakukan penelitian, mengembangkan produk atau jasa berbasis solusi, menganalisis data, serta menyusun dan mengomunikasikan karya ilmiah secara mandiri maupun kolaboratif dengan memanfaatkan teknologi informasi sesuai kaidah akademik',
        'kategori' => 'Pengetahuan',
        'target' => 70.00
    ],
    'CPL04' => [
        'rumusan' => 'Lulusan mampu memahami dan menerapkan kode etik dalam penggunaan informasi dan data pada perancangan, implementasi, dan penggunaan suatu sistem (Aptikom: IS2020)',
        'kategori' => 'Pengetahuan',
        'target' => 70.00
    ],
    'CPL05' => [
        'rumusan' => 'Lulusan mampu memahami, menganalisis, dan menilai konsep dasar dan peran sistem informasi dalam mengelola data dan memberikan rekomendasi pengambilan keputusan pada proses dan sistem organisasi (Aptikom: IS2020)',
        'kategori' => 'Pengetahuan',
        'target' => 70.00
    ],
    'CPL06' => [
        'rumusan' => 'Lulusan mampu memahami dan menggunakan berbagai metodologi pengembangan sistem beserta alat pemodelan sistem dan menganalisa kebutuhan pengguna dalam membangun sistem informasi untuk mencapai tujuan organisasi (Aptikom: IS2020)',
        'kategori' => 'Keterampilan Khusus',
        'target' => 70.00
    ],
    'CPL07' => [
        'rumusan' => 'Lulusan mampu menganalisis, merancang, mengimplementasikan, dan mengevaluasi solusi Health Informatics System yang mendukung transformasi digital, pengelolaan informasi, dan peningkatan kualitas layanan kesehatan',
        'kategori' => 'Keterampilan Khusus',
        'target' => 70.00
    ],
    'CPL08' => [
        'rumusan' => 'Lulusan mampu mengelola, menganalisis, dan memanfaatkan data serta teknologi cerdas yang mendukung pengambilan keputusan organisasi, khususnya pada sektor kesehatan',
        'kategori' => 'Keterampilan Khusus',
        'target' => 70.00
    ],
    'CPL09' => [
        'rumusan' => 'Lulusan mampu mengembangkan produk, layanan, dan solusi digital, serta mengelola usaha mandiri melalui pemanfaatan teknologi informasi untuk menjawab kebutuhan organisasi dan masyarakat',
        'kategori' => 'Keterampilan Khusus',
        'target' => 70.00
    ],
];

$cplMap = []; // kode => id
foreach ($cplDefinitions as $kode => $cData) {
    $existingCplId = $db->query("SELECT id FROM cpl WHERE kurikulum_id = $kurikulumId AND kode = '$kode'")->fetchColumn();
    if ($existingCplId) {
        $db->prepare("
            UPDATE cpl 
            SET rumusan = :rumusan, 
                kategori = :kategori, 
                target_capaian = :target, 
                status = 'ACTIVE' 
            WHERE id = :id
        ")->execute([
            'rumusan' => $cData['rumusan'],
            'kategori' => $cData['kategori'],
            'target' => $cData['target'],
            'id' => $existingCplId
        ]);
        $cplMap[$kode] = (int)$existingCplId;
    } else {
        $stmtInsCpl = $db->prepare("
            INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status)
            VALUES (:kur_id, :kode, :rumusan, :kategori, :target, 'ACTIVE')
        ");
        $stmtInsCpl->execute([
            'kur_id' => $kurikulumId,
            'kode' => $kode,
            'rumusan' => $cData['rumusan'],
            'kategori' => $cData['kategori'],
            'target' => $cData['target']
        ]);
        $cplMap[$kode] = (int)$db->lastInsertId();
    }
}
echo "- 9 CPL resmi Kurikulum 2026 (CPL01 - CPL09) berhasil disimpan untuk Kurikulum ID $kurikulumId.\n";
foreach ($cplMap as $k => $id) {
    echo "  * $k => ID $id\n";
}

// -------------------------------------------------------------
// 4. PEMETAAN PL - CPL DARI TABEL 5 EXCEL 2026
// -------------------------------------------------------------
echo "\n--- 4. SINKRONISASI PEMETAAN PL - CPL (Tabel 5) ---\n";
// CPL01 -> PL01
// CPL02 -> PL01, PL05
// CPL03 -> PL02, PL03
// CPL04 -> PL02
// CPL05 -> PL02, PL03
// CPL06 -> PL02, PL04
// CPL07 -> PL04
// CPL08 -> PL04, PL05
// CPL09 -> PL05

$plCplRelations = [
    'CPL01' => ['PL01'],
    'CPL02' => ['PL01', 'PL05'],
    'CPL03' => ['PL02', 'PL03'],
    'CPL04' => ['PL02'],
    'CPL05' => ['PL02', 'PL03'],
    'CPL06' => ['PL02', 'PL04'],
    'CPL07' => ['PL04'],
    'CPL08' => ['PL04', 'PL05'],
    'CPL09' => ['PL05'],
];

$plIdsKur1 = implode(',', array_values($plMap));
if (!empty($plIdsKur1)) {
    $db->exec("DELETE FROM pl_cpl WHERE pl_id IN ($plIdsKur1)");
}

$stmtInsertPlCpl = $db->prepare("INSERT IGNORE INTO pl_cpl (pl_id, cpl_id) VALUES (:pl_id, :cpl_id)");
$plCplCount = 0;
foreach ($plCplRelations as $cplK => $pls) {
    if (!isset($cplMap[$cplK])) continue;
    $cId = $cplMap[$cplK];
    foreach ($pls as $plK) {
        if (!isset($plMap[$plK])) continue;
        $pId = $plMap[$plK];
        $stmtInsertPlCpl->execute(['pl_id' => $pId, 'cpl_id' => $cId]);
        $plCplCount++;
    }
}
echo "- $plCplCount relasi PL-CPL berhasil diperbarui.\n";

// -------------------------------------------------------------
// 5. IMPORT / SINKRONISASI PEMETAAN MK - CPL (Sheet MATRIKS KURIKULUM & PEMBENTUKAN MK)
// -------------------------------------------------------------
echo "\n--- 5. IMPORT PEMETAAN MK - CPL DARI EXCEL 2026 ---\n";

// Ambil semua MK untuk kurikulum 1
$mksDb = $db->query("SELECT id, kode, nama FROM mata_kuliah WHERE kurikulum_id = $kurikulumId")->fetchAll(PDO::FETCH_ASSOC);
$mkByKode = [];
$mkByNama = [];
foreach ($mksDb as $m) {
    $mkByKode[trim($m['kode'])] = (int)$m['id'];
    $mkByNama[strtolower(trim($m['nama']))] = (int)$m['id'];
}

// 5a. Baca dari MATRIKS KURIKULUM (Table 13)
$matriksRows = getSheetRows($zip, $sheetFiles['MATRIKS KURIKULUM'] ?? null, $strings);
$mkCplMatrix = []; // mk_id => [cpl_id => true]

$colToCpl = [
    'F' => 'CPL01',
    'G' => 'CPL02',
    'H' => 'CPL03',
    'I' => 'CPL04',
    'J' => 'CPL05',
    'K' => 'CPL06',
    'L' => 'CPL07',
    'M' => 'CPL08',
    'N' => 'CPL09'
];

foreach ($matriksRows as $rNum => $cols) {
    if ($rNum <= 3) continue;
    $kodeMk = trim($cols['B'] ?? '');
    $namaMk = trim($cols['C'] ?? '');
    if (!$kodeMk && !$namaMk) continue;

    $mkId = $mkByKode[$kodeMk] ?? ($mkByNama[strtolower($namaMk)] ?? null);
    if (!$mkId) continue;

    foreach ($colToCpl as $col => $cplKode) {
        $val = trim($cols[$col] ?? '');
        if ($val !== '' && isset($cplMap[$cplKode])) {
            $mkCplMatrix[$mkId][$cplMap[$cplKode]] = true;
        }
    }
}

// 5b. Baca dari PEMBENTUKAN MK DAN BOBOT SKS (Table 7)
$pembentukanRows = getSheetRows($zip, $sheetFiles['PEMBENTUKAN MK DAN BOBOT SKS'] ?? null, $strings);
$colToCplTabel7 = [
    'C' => 'CPL01',
    'D' => 'CPL02',
    'E' => 'CPL03',
    'F' => 'CPL04',
    'G' => 'CPL05',
    'H' => 'CPL06',
    'I' => 'CPL07',
    'J' => 'CPL08',
    'K' => 'CPL09'
];

foreach ($pembentukanRows as $rNum => $cols) {
    if ($rNum < 5 || $rNum > 70) continue;
    $namaMk = trim($cols['B'] ?? '');
    if (!$namaMk) continue;

    $mkId = $mkByNama[strtolower($namaMk)] ?? null;
    if (!$mkId) {
        // Coba match partial nama
        foreach ($mkByNama as $n => $id) {
            if (stripos($n, $namaMk) !== false || stripos($namaMk, $n) !== false) {
                $mkId = $id;
                break;
            }
        }
    }

    if ($mkId) {
        foreach ($colToCplTabel7 as $col => $cplKode) {
            $val = trim($cols[$col] ?? '');
            if ($val !== '' && isset($cplMap[$cplKode])) {
                $mkCplMatrix[$mkId][$cplMap[$cplKode]] = true;
            }
        }
    }
}

// Khusus UAM003 (Aplikasi Komputer, mk_id = 3): pastikan CPL01 dan CPL02 masuk
if (isset($mkByKode['UAM003'])) {
    $uamId = $mkByKode['UAM003'];
    $mkCplMatrix[$uamId][$cplMap['CPL01']] = true;
    $mkCplMatrix[$uamId][$cplMap['CPL02']] = true;
}

// Bersihkan mk_cpl lama untuk kurikulum 1
$mkIdsKur1 = implode(',', array_column($mksDb, 'id'));
if (!empty($mkIdsKur1)) {
    $db->exec("DELETE FROM mk_cpl WHERE mk_id IN ($mkIdsKur1)");
}

$stmtInsertMkCpl = $db->prepare("
    INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur)
    VALUES (:mk_id, :cpl_id, :bobot, 1)
");

$mkCplCount = 0;
foreach ($mkCplMatrix as $mkId => $cpls) {
    $numCpls = count($cpls);
    if ($numCpls === 0) continue;
    $bobotPerCpl = round(100.0 / $numCpls, 2);

    foreach (array_keys($cpls) as $cplId) {
        $stmtInsertMkCpl->execute([
            'mk_id' => $mkId,
            'cpl_id' => $cplId,
            'bobot' => $bobotPerCpl
        ]);
        $mkCplCount++;
    }
}
echo "- $mkCplCount pemetaan MK-CPL berhasil diperbarui untuk seluruh mata kuliah Kurikulum 2026.\n";

// -------------------------------------------------------------
// 6. PERBARUI PEMETAAN CPMK - CPL (cpmk_cpl) UNTUK KURIKULUM 2026
// -------------------------------------------------------------
echo "\n--- 6. PERBARUI CPMK_CPL UNTUK KURIKULUM 2026 ---\n";

// Khusus CPMK 48 (MK 3 - Aplikasi Komputer)
// Hubungkan ke CPL01 dan/atau CPL02 Kurikulum 2026
$cpmk48 = $db->query("SELECT * FROM cpmk WHERE id = 48")->fetch();
if ($cpmk48) {
    $db->exec("DELETE FROM cpmk_cpl WHERE cpmk_id = 48");
    $stmtCpmkCplIns = $db->prepare("INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (48, :cpl_id)");
    $stmtCpmkCplIns->execute(['cpl_id' => $cplMap['CPL01']]);
    $stmtCpmkCplIns->execute(['cpl_id' => $cplMap['CPL02']]);
    echo "- CPMK 48 (Aplikasi Komputer) berhasil dipetakan ke CPL01 (ID: {$cplMap['CPL01']}) dan CPL02 (ID: {$cplMap['CPL02']}).\n";
}

// Periksa juga CPMK lain di Kurikulum 1 yang cpl_id-nya mungkin mengarah ke CPL yang tidak terpetakan di MK-nya
$allCpmkKur1 = $db->query("
    SELECT c.id, c.mk_id, m.kode as mk_kode, m.nama as mk_nama 
    FROM cpmk c 
    JOIN mata_kuliah m ON c.mk_id = m.id 
    WHERE m.kurikulum_id = $kurikulumId
")->fetchAll(PDO::FETCH_ASSOC);

$updatedCpmkCpl = 0;
foreach ($allCpmkKur1 as $cpmk) {
    $cpmkId = (int)$cpmk['id'];
    $mkId = (int)$cpmk['mk_id'];
    
    // Cek apakah CPMK sudah memiliki CPL yang valid di mk_cpl
    $validMkCpls = $db->query("SELECT cpl_id FROM mk_cpl WHERE mk_id = $mkId")->fetchAll(PDO::FETCH_COLUMN);
    $currentCpmkCpls = $db->query("SELECT cpl_id FROM cpmk_cpl WHERE cpmk_id = $cpmkId")->fetchAll(PDO::FETCH_COLUMN);

    // Filter yang masih valid
    $validAssigned = array_intersect($currentCpmkCpls, $validMkCpls);
    if (empty($validAssigned) && !empty($validMkCpls)) {
        // Berikan CPL pertama dari MK
        $db->exec("DELETE FROM cpmk_cpl WHERE cpmk_id = $cpmkId");
        $stmtCpmkCplIns = $db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
        $stmtCpmkCplIns->execute(['cpmk_id' => $cpmkId, 'cpl_id' => $validMkCpls[0]]);
        $updatedCpmkCpl++;
    }
}
echo "- $updatedCpmkCpl CPMK lainnya disinkronkan dengan CPL MK 2026.\n";

echo "\n=======================================================\n";
echo " SINKRONISASI KURIKULUM OBE SI 2026 SELESAI DENGAN SUKSES!\n";
echo "=======================================================\n";
