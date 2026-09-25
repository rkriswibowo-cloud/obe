<?php
// database/seed_all_semesters.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=== MEMULAI SINKRONISASI & INPUT MK SEMESTER 1 S/D 8 ===\n";

// 1. UPDATE SKS MATA KULIAH
echo "1. Memperbarui SKS Mata Kuliah...\n";
// SIF606: Product Analytics dan Growth UX (MK Pilihan) -> 3 SKS
$db->exec("UPDATE mata_kuliah SET sks_teori = 3, sks_praktek = 0 WHERE kode = 'SIF606' AND (sks_teori = 0 OR sks_teori IS NULL)");
// SIF604: Perencanaan Strategis Sistem Informasi -> 2 SKS
$db->exec("UPDATE mata_kuliah SET sks_teori = 2, sks_praktek = 0 WHERE kode = 'SIF604' AND sks_teori = 1");

$mkList = $db->query("SELECT id, kode, nama, sks_teori, sks_praktek, (COALESCE(sks_teori,0)+COALESCE(sks_praktek,0)) as total_sks, semester FROM mata_kuliah ORDER BY semester, kode")->fetchAll(PDO::FETCH_ASSOC);

$sksPerSemester = [];
foreach ($mkList as $mk) {
    $sem = (int)$mk['semester'];
    $sks = (int)$mk['total_sks'];
    if (!isset($sksPerSemester[$sem])) {
        $sksPerSemester[$sem] = ['count' => 0, 'sks' => 0];
    }
    $sksPerSemester[$sem]['count']++;
    $sksPerSemester[$sem]['sks'] += $sks;
}

foreach ($sksPerSemester as $sem => $info) {
    echo "- Semester $sem: {$info['count']} Mata Kuliah, Total {$info['sks']} SKS\n";
}

// 2. SETUP TAHUN AKADEMIK UNTUK SEMESTER 1 S/D 8
echo "\n2. Mengatur Tahun Akademik Semester 1 s/d 8...\n";
$semTaMap = [
    1 => ['nama' => '2023/2024 Ganjil (Semester 1)', 'is_active' => 0],
    2 => ['nama' => '2023/2024 Genap (Semester 2)', 'is_active' => 0],
    3 => ['nama' => '2024/2025 Ganjil (Semester 3)', 'is_active' => 0],
    4 => ['nama' => '2024/2025 Genap (Semester 4)', 'is_active' => 0],
    5 => ['nama' => '2025/2026 Ganjil (Semester 5)', 'is_active' => 0],
    6 => ['nama' => '2025/2026 Genap (Semester 6)', 'is_active' => 0],
    7 => ['nama' => '2026/2027 Ganjil (Semester 7)', 'is_active' => 0],
    8 => ['nama' => '2026/2027 Genap (Semester 8)', 'is_active' => 1]
];

$taIdPerSemester = [];
foreach ($semTaMap as $sem => $data) {
    // Cek apakah TA dengan nama ini sudah ada
    $stmt = $db->prepare("SELECT id FROM tahun_akademik WHERE nama = :nama OR id = :id");
    $stmt->execute(['nama' => $data['nama'], 'id' => $sem]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $db->prepare("UPDATE tahun_akademik SET nama = :nama, is_active = :is_active WHERE id = :id")->execute([
            'nama' => $data['nama'],
            'is_active' => $data['is_active'],
            'id' => $existingId
        ]);
        $taIdPerSemester[$sem] = (int)$existingId;
    } else {
        $stmtInsert = $db->prepare("INSERT INTO tahun_akademik (id, nama, is_active) VALUES (:id, :nama, :is_active)");
        $stmtInsert->execute([
            'id' => $sem,
            'nama' => $data['nama'],
            'is_active' => $data['is_active']
        ]);
        $taIdPerSemester[$sem] = $sem;
    }
}
echo "- 8 Periode Tahun Akademik (Semester 1 - 8) berhasil disiapkan.\n";

// 3. GENERATE CPMK & SUB-CPMK UNTUK SELURUH MATA KULIAH YANG BELUM MEMILIKI
echo "\n3. Memastikan setiap Mata Kuliah memiliki CPMK dan Sub-CPMK...\n";

// CPL reference
$cpls = $db->query("SELECT id, kode FROM cpl ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
// e.g. 1=>CPL01, 2=>CPL02, 3=>CPL03, 4=>CPL04, 5=>CPL05, 6=>CPL06

$stmtCpmk = $db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (:mk_id, :kode, :rumusan)");
$stmtCpmkCpl = $db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
$stmtMkCpl = $db->prepare("INSERT IGNORE INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur) VALUES (:mk_id, :cpl_id, 1, 1)");
$stmtSubCpmk = $db->prepare("
    INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, level_taksonomi, pengalaman_belajar, asesmen_penilaian, bobot, urutan)
    VALUES (:cpmk_id, :kode, :rumusan, :level_taksonomi, :pengalaman_belajar, :asesmen_penilaian, :bobot, :urutan)
");

$cpmkCreated = 0;
$subCpmkCreated = 0;

foreach ($mkList as $mk) {
    $mkId = (int)$mk['id'];
    $sem = (int)$mk['semester'];
    $kodeMk = $mk['kode'];
    $namaMk = $mk['nama'];

    // Tentukan CPL target berdasarkan karakteristik MK
    $targetCplIds = [];
    if (strpos($kodeMk, 'UAM') === 0) {
        $targetCplIds = [1, 5]; // Pengetahuan umum & Sikap
    } elseif (strpos($kodeMk, 'FTB') === 0) {
        $targetCplIds = [1, 2]; // Pengetahuan dasar & Analisis
    } elseif (in_array($kodeMk, ['SIF102', 'SIF202', 'SIF302', 'SIF305', 'SIF401', 'SIF503'])) {
        $targetCplIds = [1, 3]; // Pemrograman / Rekayasa Perangkat Lunak
    } elseif (in_array($kodeMk, ['SIF203', 'SIF301', 'SIF204', 'SIF307', 'SIF501', 'SIF601'])) {
        $targetCplIds = [1, 4]; // Basis Data & Infrastruktur
    } elseif (in_array($kodeMk, ['SIF405', 'SIF408', 'SIF507', 'SIF602', 'SIF604'])) {
        $targetCplIds = [2, 6]; // Keamanan & Tata Kelola
    } elseif (in_array($kodeMk, ['SIF103', 'SIF104', 'SIF504', 'SIF505', 'SIF506', 'SIF607'])) {
        $targetCplIds = [2, 5]; // Manajemen & Sikap Kolaborasi
    } elseif (in_array($kodeMk, ['SIF701', 'SIF702', 'SIF703', 'SIF801'])) {
        $targetCplIds = [1, 2, 3, 5]; // Capstone, PKL & Skripsi
    } else {
        $targetCplIds = [1, 2];
    }

    // Pastikan mk_cpl terisi
    foreach ($targetCplIds as $cplId) {
        $stmtMkCpl->execute(['mk_id' => $mkId, 'cpl_id' => $cplId]);
    }

    // Periksa apakah MK sudah memiliki CPMK
    $checkCpmk = $db->prepare("SELECT COUNT(*) FROM cpmk WHERE mk_id = :mk_id");
    $checkCpmk->execute(['mk_id' => $mkId]);
    $hasCpmk = (int)$checkCpmk->fetchColumn();

    if ($hasCpmk === 0) {
        // Buat 2 CPMK
        // CPMK 1
        $cpl1 = $targetCplIds[0];
        $stmtCpmk->execute([
            'mk_id' => $mkId,
            'kode' => 'CPMK-1',
            'rumusan' => "Mampu memahami prinsip dasar, konsep teoretis, dan metodologi dalam $namaMk."
        ]);
        $cpmk1Id = $db->lastInsertId();
        $stmtCpmkCpl->execute(['cpmk_id' => $cpmk1Id, 'cpl_id' => $cpl1]);
        $cpmkCreated++;

        // Sub-CPMK 1.1 & 1.2
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk1Id,
            'kode' => 'Sub-CPMK 1.1',
            'rumusan' => "Mampu menjelaskan terminologi, kerangka kerja, dan komponen utama pada $namaMk.",
            'level_taksonomi' => 'C2 - Memahami',
            'pengalaman_belajar' => 'Kuliah interaktif, telaah pustaka, dan kuis mandiri',
            'asesmen_penilaian' => 'Kuis & Tugas 1',
            'bobot' => 25.00,
            'urutan' => 1
        ]);
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk1Id,
            'kode' => 'Sub-CPMK 1.2',
            'rumusan' => "Mampu menerapkan metode analisis dan pemecahan masalah dasar terkait $namaMk.",
            'level_taksonomi' => 'C3 - Menerapkan',
            'pengalaman_belajar' => 'Studi kasus terbimbing dan diskusi kelompok',
            'asesmen_penilaian' => 'Ujian Tengah Semester (UTS)',
            'bobot' => 25.00,
            'urutan' => 2
        ]);
        $subCpmkCreated += 2;

        // CPMK 2
        $cpl2 = $targetCplIds[1] ?? $targetCplIds[0];
        $stmtCpmk->execute([
            'mk_id' => $mkId,
            'kode' => 'CPMK-2',
            'rumusan' => "Mampu merancang, mengimplementasikan, dan mengevaluasi luaran praktis dalam domain $namaMk."
        ]);
        $cpmk2Id = $db->lastInsertId();
        $stmtCpmkCpl->execute(['cpmk_id' => $cpmk2Id, 'cpl_id' => $cpl2]);
        $cpmkCreated++;

        // Sub-CPMK 2.1 & 2.2
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk2Id,
            'kode' => 'Sub-CPMK 2.1',
            'rumusan' => "Mampu merancang solusi sistematis dan mempraktikkan keterampilan teknis pada $namaMk.",
            'level_taksonomi' => 'C4 - Menganalisis',
            'pengalaman_belajar' => 'Praktikum laboratorium / tugas proyek perancangan',
            'asesmen_penilaian' => 'Tugas Proyek / Unjuk Kerja',
            'bobot' => 25.00,
            'urutan' => 3
        ]);
        $stmtSubCpmk->execute([
            'cpmk_id' => $cpmk2Id,
            'kode' => 'Sub-CPMK 2.2',
            'rumusan' => "Mampu menyusun dokumentasi komprehensif dan mempertanggungjawabkan hasil evaluasi pada $namaMk.",
            'level_taksonomi' => 'C5 - Mengevaluasi',
            'pengalaman_belajar' => 'Presentasi luaran akhir dan penyusunan laporan komprehensif',
            'asesmen_penilaian' => 'Ujian Akhir Semester (UAS)',
            'bobot' => 25.00,
            'urutan' => 4
        ]);
        $subCpmkCreated += 2;
    }
}
echo "- Dibuat $cpmkCreated CPMK baru dan $subCpmkCreated Sub-CPMK baru untuk MK yang belum lengkap.\n";

// 4. MEMBUAT KELAS PERKULIAHAN UNTUK SETIAP MK PADA SEMESTER TERKAIT
echo "\n4. Membuat Kelas Perkuliahan untuk Semua MK Semester 1 s/d 8...\n";
$dosenList = $db->query("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Dosen'")->fetchAll(PDO::FETCH_COLUMN);
if (empty($dosenList)) {
    $dosenList = [2, 3, 4, 5, 6];
}

$stmtCheckKelas = $db->prepare("SELECT id FROM kelas WHERE mata_kuliah_id = :mk_id AND tahun_akademik_id = :ta_id LIMIT 1");
$stmtInsertKelas = $db->prepare("INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id) VALUES (:mk_id, :ta_id, :nama_kelas, :dosen_id)");
$stmtUpdateKelasTa = $db->prepare("UPDATE kelas SET tahun_akademik_id = :ta_id WHERE id = :id");

$kelasMap = []; // mk_id => kelas_id
$kelasCreated = 0;
$dosenIdx = 0;

foreach ($mkList as $mk) {
    $mkId = (int)$mk['id'];
    $sem = (int)$mk['semester'];
    $taId = $taIdPerSemester[$sem];
    $dosenId = $dosenList[$dosenIdx % count($dosenList)];
    $dosenIdx++;

    // Cek apakah sudah ada kelas untuk MK ini di TA ini
    $stmtCheckKelas->execute(['mk_id' => $mkId, 'ta_id' => $taId]);
    $kelasId = $stmtCheckKelas->fetchColumn();

    if (!$kelasId) {
        // Cek apakah ada kelas untuk MK ini di TA lain (misal dari data lama)
        $stmtOld = $db->prepare("SELECT id FROM kelas WHERE mata_kuliah_id = :mk_id LIMIT 1");
        $stmtOld->execute(['mk_id' => $mkId]);
        $oldKelasId = $stmtOld->fetchColumn();

        if ($oldKelasId) {
            // Update TA ke semester yang tepat
            $stmtUpdateKelasTa->execute(['ta_id' => $taId, 'id' => $oldKelasId]);
            $kelasId = $oldKelasId;
        } else {
            $stmtInsertKelas->execute([
                'mk_id' => $mkId,
                'ta_id' => $taId,
                'nama_kelas' => 'A',
                'dosen_id' => $dosenId
            ]);
            $kelasId = $db->lastInsertId();
            $kelasCreated++;
        }
    }

    $kelasMap[$mkId] = (int)$kelasId;
}
echo "- Total kelas aktif sekarang: " . count($kelasMap) . " kelas ($kelasCreated baru dibuat).\n";

// 5. ENROLL MAHASISWA KE SELURUH KELAS SEMESTER 1 S/D 8
echo "\n5. Mendaftarkan Mahasiswa ke Seluruh Kelas Perkuliahan...\n";
$mhsList = $db->query("SELECT u.id, u.username, u.nama_lengkap FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Mahasiswa'")->fetchAll(PDO::FETCH_ASSOC);

$stmtEnroll = $db->prepare("INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES (:kelas_id, :mhs_id)");
$enrollCount = 0;

foreach ($kelasMap as $mkId => $kelasId) {
    foreach ($mhsList as $mhs) {
        $stmtEnroll->execute(['kelas_id' => $kelasId, 'mhs_id' => $mhs['id']]);
        if ($stmtEnroll->rowCount() > 0) {
            $enrollCount++;
        }
    }
}
echo "- Pendaftaran mahasiswa berhasil ($enrollCount data pendaftaran baru).\n";

// 6. GENERATE NILAI ASESMEN UNTUK SELURUH SUB-CPMK SETIAP KELAS
echo "\n6. Mengisi Nilai Asesmen untuk Setiap Sub-CPMK agar KHS & CPL Lengkap...\n";
$stmtCheckNilai = $db->prepare("SELECT COUNT(*) FROM asesmen_nilai WHERE kelas_id = :kelas_id AND mahasiswa_id = :mhs_id AND sub_cpmk_id = :sub_cpmk_id");
$stmtInsertNilai = $db->prepare("INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES (:kelas_id, :mhs_id, :sub_cpmk_id, :nilai)");

$nilaiInserted = 0;

foreach ($kelasMap as $mkId => $kelasId) {
    // Ambil daftar sub-CPMK untuk MK ini
    $subCpmks = $db->query("
        SELECT sc.id, sc.bobot
        FROM sub_cpmk sc
        JOIN cpmk c ON sc.cpmk_id = c.id
        WHERE c.mk_id = $mkId
        ORDER BY sc.urutan ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($subCpmks)) continue;

    foreach ($mhsList as $mhs) {
        $mhsId = (int)$mhs['id'];
        
        // Buat variasi nilai yang realistis berdasarkan mahasiswa & mata kuliah
        // Ahmad Fauzi (id 7): Mahasiswa berprestasi (nilai 84 - 96) -> A & A-
        // Mahasiswa lain: Nilai 74 - 92 -> B, B+, A-, A
        $baseScore = ($mhsId === 7) ? 86 : (76 + (($mhsId * 3) % 12));

        foreach ($subCpmks as $idx => $sc) {
            $scId = (int)$sc['id'];

            $stmtCheckNilai->execute([
                'kelas_id' => $kelasId,
                'mhs_id' => $mhsId,
                'sub_cpmk_id' => $scId
            ]);
            $exists = $stmtCheckNilai->fetchColumn();

            if (!$exists) {
                // Variasi skor sedikit antar sub-cpmk (+- 4)
                $variation = (($idx * 3 + $mkId) % 9) - 4;
                $score = min(98, max(72, $baseScore + $variation));

                $stmtInsertNilai->execute([
                    'kelas_id' => $kelasId,
                    'mhs_id' => $mhsId,
                    'sub_cpmk_id' => $scId,
                    'nilai' => $score
                ]);
                $nilaiInserted++;
            }
        }
    }
}
echo "- Dihasilkan $nilaiInserted skor asesmen baru.\n";

echo "\n=== PROSES SELESAI DENGAN SUKSES! ===\n";
echo "Semua MK Semester 1 s/d 8 sudah aktif dan siap dideteksi di KHS!\n";
