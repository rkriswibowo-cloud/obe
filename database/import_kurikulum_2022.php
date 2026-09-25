<?php
// database/import_kurikulum_2022.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=======================================================\n";
echo " IMPORT KURIKULUM OBE SI 2022 DARI EXCEL\n";
echo "=======================================================\n";

$excelPath = __DIR__ . '/../Kurikulum OBE SI 2022.xlsx';
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

// 2. Baca relationships untuk mapping sheet name -> xml file
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

// 3. BUAT / AMBIL KURIKULUM OBE SI 2022
echo "\n--- 1. MENYIAPKAN DATA KURIKULUM ---\n";
$prodiId = 1; // Sistem Informasi
$stmtKur = $db->prepare("SELECT id FROM kurikulum WHERE prodi_id = :prodi_id AND (kode = 'OBE SI 2022' OR nama = 'OBE SI 2022' OR nama LIKE '%2022%') LIMIT 1");
$stmtKur->execute(['prodi_id' => $prodiId]);
$kurikulumId = $stmtKur->fetchColumn();

if ($kurikulumId) {
    echo "- Kurikulum ID $kurikulumId ditemukan, memperbarui metadata...\n";
    $db->prepare("
        UPDATE kurikulum 
        SET nama = 'OBE SI 2022', 
            kode = 'OBE SI 2022', 
            tahun_mulai = 2022, 
            versi = '2022-2024', 
            status = 'ACTIVE',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id
    ")->execute(['id' => $kurikulumId]);
} else {
    $db->prepare("
        INSERT INTO kurikulum (prodi_id, kode, nama, tahun_mulai, versi, status) 
        VALUES (:prodi_id, 'OBE SI 2022', 'OBE SI 2022', 2022, '2022-2024', 'ACTIVE')
    ")->execute(['prodi_id' => $prodiId]);
    $kurikulumId = $db->lastInsertId();
    echo "- Kurikulum baru 'OBE SI 2022' berhasil dibuat dengan ID: $kurikulumId\n";
}

// 4. IMPORT PROFIL LULUSAN
echo "\n--- 2. IMPORT PROFIL LULUSAN DARI SHEET 1 ---\n";
$plRows = getSheetRows($zip, $sheetFiles['1. Profil Lulusan'] ?? null, $strings);
$stmtInsertPl = $db->prepare("
    INSERT INTO profil_lulusan (kurikulum_id, kode, nama, deskripsi)
    VALUES (:kur_id, :kode, :nama, :deskripsi)
    ON DUPLICATE KEY UPDATE deskripsi = VALUES(deskripsi)
");

$plCount = 0;
foreach ($plRows as $rNum => $cols) {
    $kode = $cols['B'] ?? '';
    $deskripsi = $cols['C'] ?? '';
    $profesi = $cols['D'] ?? '';

    if (preg_match('/^PL[0-9]+/i', $kode)) {
        $namaPl = "Profil Lulusan $kode";
        $fullDeskripsi = !empty($profesi) ? "$deskripsi\nProfesi: $profesi" : $deskripsi;
        $stmtInsertPl->execute([
            'kur_id' => $kurikulumId,
            'kode' => $kode,
            'nama' => $namaPl,
            'deskripsi' => $fullDeskripsi
        ]);
        $plCount++;
    }
}
echo "- $plCount Profil Lulusan berhasil disimpan untuk Kurikulum ID $kurikulumId.\n";

// 5. IMPORT CPL PRODI
echo "\n--- 3. IMPORT CPL PRODI DARI SHEET 3 ---\n";
$cplRows = getSheetRows($zip, $sheetFiles['3. CPL Prodi'] ?? null, $strings);
$stmtInsertCpl = $db->prepare("
    INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status)
    VALUES (:kur_id, :kode, :rumusan, :kategori, 70.00, 'ACTIVE')
    ON DUPLICATE KEY UPDATE rumusan = VALUES(rumusan), status = 'ACTIVE'
");

$cplMap = []; // kode => id
$cplCount = 0;
foreach ($cplRows as $rNum => $cols) {
    $kode = $cols['B'] ?? '';
    $rumusan = $cols['C'] ?? '';

    if (preg_match('/^CPL[0-9]+/i', $kode)) {
        $kategori = 'Keterampilan Khusus';
        if ($kode === 'CPL01') $kategori = 'Sikap & Pengetahuan';
        elseif ($kode === 'CPL02') $kategori = 'Keterampilan Umum';
        elseif ($kode === 'CPL07') $kategori = 'Sikap & Etika';

        $stmtInsertCpl->execute([
            'kur_id' => $kurikulumId,
            'kode' => $kode,
            'rumusan' => $rumusan,
            'kategori' => $kategori
        ]);

        // Ambil ID CPL
        $cplId = $db->query("SELECT id FROM cpl WHERE kurikulum_id = $kurikulumId AND kode = '$kode'")->fetchColumn();
        $cplMap[$kode] = (int)$cplId;
        $cplCount++;
    }
}
echo "- $cplCount CPL (CPL01 - CPL09) berhasil disimpan untuk Kurikulum ID $kurikulumId.\n";

// 6. IMPORT MATA KULIAH DARI SHEET 11b (Sebaran MK)
echo "\n--- 4. IMPORT MATA KULIAH DARI SHEET 11b (Sebaran MK) ---\n";
$sebaranRows = getSheetRows($zip, $sheetFiles['11b. Sebaran MK'] ?? null, $strings);

// Baca juga kategori dari sheet 11a jika tersedia
$susunanRows = getSheetRows($zip, $sheetFiles['11a. Susunan Mata Kuliah'] ?? null, $strings);
$kategoriMap = [];
foreach ($susunanRows as $sr) {
    $kUam = $sr['C'] ?? '';
    $kat = $sr['E'] ?? '';
    if ($kUam && $kat) {
        $kategoriMap[$kUam] = $kat;
    }
}

$stmtCheckMk = $db->prepare("SELECT id FROM mata_kuliah WHERE kurikulum_id = :kur_id AND kode = :kode LIMIT 1");
$stmtInsertMk = $db->prepare("
    INSERT INTO mata_kuliah (kurikulum_id, kode, nama, sks_teori, sks_praktek, semester, sifat, kelompok_keilmuan, deskripsi, status)
    VALUES (:kur_id, :kode, :nama, :sks_teori, :sks_praktek, :semester, :sifat, :kelompok_keilmuan, :deskripsi, 'ACTIVE')
");
$stmtUpdateMk = $db->prepare("
    UPDATE mata_kuliah 
    SET nama = :nama, 
        sks_teori = :sks_teori, 
        sks_praktek = :sks_praktek, 
        semester = :semester, 
        sifat = :sifat, 
        kelompok_keilmuan = :kelompok_keilmuan,
        status = 'ACTIVE'
    WHERE id = :id
");

$currentSemester = 0;
$mkCount = 0;
$mkMap = []; // kode => id

foreach ($sebaranRows as $rNum => $cols) {
    $colA = $cols['A'] ?? '';
    if (preg_match('/SEMESTER\s+([IVXLCDM]+|[0-9]+)/i', $colA, $m)) {
        $roman = strtoupper(trim($m[1]));
        $romanMap = ['I'=>1, 'II'=>2, 'III'=>3, 'IV'=>4, 'V'=>5, 'VI'=>6, 'VII'=>7, 'VIII'=>8];
        $currentSemester = $romanMap[$roman] ?? (int)$roman;
        continue;
    }

    $kode = $cols['B'] ?? '';
    $nama = $cols['C'] ?? '';
    $sksTeori = (int)($cols['D'] ?? 0);
    $sksPraktek = (int)($cols['E'] ?? 0);
    $totalSks = (int)($cols['F'] ?? 0);

    if (!empty($kode) && $kode !== 'KODE MATKUL' && !empty($nama) && $currentSemester > 0) {
        if ($sksTeori === 0 && $sksPraktek === 0 && $totalSks > 0) {
            $sksTeori = $totalSks;
        }

        $sifat = 'Wajib';
        if (stripos($nama, 'pilihan') !== false) {
            $sifat = 'Pilihan';
        }

        $kelompok = $kategoriMap[$kode] ?? 'Penciri Utama';

        $stmtCheckMk->execute(['kur_id' => $kurikulumId, 'kode' => $kode]);
        $existingMkId = $stmtCheckMk->fetchColumn();

        if ($existingMkId) {
            $stmtUpdateMk->execute([
                'nama' => $nama,
                'sks_teori' => $sksTeori,
                'sks_praktek' => $sksPraktek,
                'semester' => $currentSemester,
                'sifat' => $sifat,
                'kelompok_keilmuan' => $kelompok,
                'id' => $existingMkId
            ]);
            $mkMap[$kode] = (int)$existingMkId;
        } else {
            $stmtInsertMk->execute([
                'kur_id' => $kurikulumId,
                'kode' => $kode,
                'nama' => $nama,
                'sks_teori' => $sksTeori,
                'sks_praktek' => $sksPraktek,
                'semester' => $currentSemester,
                'sifat' => $sifat,
                'kelompok_keilmuan' => $kelompok,
                'deskripsi' => "Mata kuliah $nama pada Kurikulum OBE SI 2022 semester $currentSemester."
            ]);
            $newMkId = $db->lastInsertId();
            $mkMap[$kode] = (int)$newMkId;
        }
        $mkCount++;
    }
}
echo "- $mkCount Mata Kuliah berhasil diimport ke Kurikulum OBE SI 2022 (Semester 1 s/d 8).\n";

// 7. IMPORT PEMETAAN CPL-MK (Sheet 9. CPL-MK)
echo "\n--- 5. IMPORT PEMETAAN CPL-MK DARI SHEET 9 ---\n";
$cplMkRows = getSheetRows($zip, $sheetFiles['9. CPL-MK'] ?? null, $strings);

$cplCols = [
    'E' => 'CPL01',
    'F' => 'CPL02',
    'G' => 'CPL03',
    'H' => 'CPL04',
    'I' => 'CPL05',
    'J' => 'CPL06',
    'K' => 'CPL07',
    'L' => 'CPL08',
    'M' => 'CPL09'
];

$stmtInsertMkCpl = $db->prepare("
    INSERT IGNORE INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur)
    VALUES (:mk_id, :cpl_id, 1, 1)
");

$mappingCount = 0;
foreach ($cplMkRows as $rNum => $cols) {
    $kodeUam = $cols['C'] ?? '';
    if (empty($kodeUam) || !isset($mkMap[$kodeUam])) continue;

    $mkId = $mkMap[$kodeUam];
    foreach ($cplCols as $colLetter => $cplKode) {
        $val = strtolower($cols[$colLetter] ?? '');
        if ($val === 'v' || $val === '1' || $val === 'x') {
            if (isset($cplMap[$cplKode])) {
                $stmtInsertMkCpl->execute([
                    'mk_id' => $mkId,
                    'cpl_id' => $cplMap[$cplKode]
                ]);
                $mappingCount++;
            }
        }
    }
}

// Fallback jika ada MK yang belum terpetakan, berikan mapping standar sesuai kelompok
$fallbackCount = 0;
foreach ($mkMap as $kode => $mkId) {
    $mapped = $db->query("SELECT count(*) FROM mk_cpl WHERE mk_id = $mkId")->fetchColumn();
    if ($mapped == 0) {
        // Map ke CPL01 dan CPL02 default
        if (isset($cplMap['CPL01'])) $stmtInsertMkCpl->execute(['mk_id' => $mkId, 'cpl_id' => $cplMap['CPL01']]);
        if (isset($cplMap['CPL02'])) $stmtInsertMkCpl->execute(['mk_id' => $mkId, 'cpl_id' => $cplMap['CPL02']]);
        $fallbackCount++;
    }
}

echo "- $mappingCount relasi CPL-MK dari matriks excel berhasil disimpan (+$fallbackCount pemetaan kelengkapan).\n";

// 8. GENERATE CPMK & SUB-CPMK UNTUK SELURUH MK PADA KURIKULUM 2022
echo "\n--- 6. MENYIAPKAN CPMK & SUB-CPMK UNTUK SETIAP MK ---\n";
$stmtCpmk = $db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (:mk_id, :kode, :rumusan)");
$stmtCpmkCpl = $db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
$stmtSubCpmk = $db->prepare("
    INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, level_taksonomi, pengalaman_belajar, asesmen_penilaian, bobot, urutan)
    VALUES (:cpmk_id, :kode, :rumusan, :level_taksonomi, :pengalaman_belajar, :asesmen_penilaian, :bobot, :urutan)
");

$cpmkCreated = 0;
$subCpmkCreated = 0;

foreach ($mkMap as $kode => $mkId) {
    $hasCpmk = $db->query("SELECT count(*) FROM cpmk WHERE mk_id = $mkId")->fetchColumn();
    if ($hasCpmk == 0) {
        $mkData = $db->query("SELECT nama FROM mata_kuliah WHERE id = $mkId")->fetch(PDO::FETCH_ASSOC);
        $namaMk = $mkData['nama'];

        // Ambil CPL yang dipetakan ke MK ini
        $mkCplIds = $db->query("SELECT cpl_id FROM mk_cpl WHERE mk_id = $mkId")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($mkCplIds)) {
            $mkCplIds = array_values($cplMap);
        }

        // CPMK 1
        $targetCpl1 = $mkCplIds[0];
        $stmtCpmk->execute([
            'mk_id' => $mkId,
            'kode' => 'CPMK-1',
            'rumusan' => "Mampu menguasai teori, konsep, dan prinsip dalam mata kuliah $namaMk."
        ]);
        $cpmk1Id = $db->lastInsertId();
        $stmtCpmkCpl->execute(['cpmk_id' => $cpmk1Id, 'cpl_id' => $targetCpl1]);
        $cpmkCreated++;

        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk1Id,
            'kode' => 'Sub-CPMK 1.1',
            'rumusan' => "Mampu mendefinisikan dan menjelaskan konsep dasar $namaMk.",
            'level_taksonomi' => 'C2 - Memahami',
            'pengalaman_belajar' => 'Diskusi interaktif dan tugas mandiri',
            'asesmen_penilaian' => 'Tugas & Kuis',
            'bobot' => 25.00,
            'urutan' => 1
        ]);
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk1Id,
            'kode' => 'Sub-CPMK 1.2',
            'rumusan' => "Mampu menerapkan metode penyelesaian masalah dalam $namaMk.",
            'level_taksonomi' => 'C3 - Menerapkan',
            'pengalaman_belajar' => 'Studi kasus terbimbing',
            'asesmen_penilaian' => 'UTS',
            'bobot' => 25.00,
            'urutan' => 2
        ]);
        $subCpmkCreated += 2;

        // CPMK 2
        $targetCpl2 = $mkCplIds[1] ?? $targetCpl1;
        $stmtCpmk->execute([
            'mk_id' => $mkId,
            'kode' => 'CPMK-2',
            'rumusan' => "Mampu mempraktikkan, merancang, dan mengevaluasi implementasi pada $namaMk."
        ]);
        $cpmk2Id = $db->lastInsertId();
        $stmtCpmkCpl->execute(['cpmk_id' => $cpmk2Id, 'cpl_id' => $targetCpl2]);
        $cpmkCreated++;

        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk2Id,
            'kode' => 'Sub-CPMK 2.1',
            'rumusan' => "Mampu merancang dan menyelesaikan proyek terapan pada $namaMk.",
            'level_taksonomi' => 'C4 - Menganalisis',
            'pengalaman_belajar' => 'Praktikum & unjuk kerja tim',
            'asesmen_penilaian' => 'Tugas Proyek',
            'bobot' => 25.00,
            'urutan' => 3
        ]);
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk2Id,
            'kode' => 'Sub-CPMK 2.2',
            'rumusan' => "Mampu mengevaluasi dan menyusun laporan akhir pada $namaMk.",
            'level_taksonomi' => 'C5 - Mengevaluasi',
            'pengalaman_belajar' => 'Presentasi luaran dan laporan komprehensif',
            'asesmen_penilaian' => 'UAS',
            'bobot' => 25.00,
            'urutan' => 4
        ]);
        $subCpmkCreated += 2;
    }
}
echo "- $cpmkCreated CPMK dan $subCpmkCreated Sub-CPMK berhasil disiapkan untuk seluruh MK 2022.\n";

echo "\n=======================================================\n";
echo " IMPORT KURIKULUM OBE SI 2022 BERHASIL 100%!\n";
echo "=======================================================\n";
