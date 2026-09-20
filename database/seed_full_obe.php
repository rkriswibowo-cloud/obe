<?php
// database/seed_full_obe.php
// Script seeder lengkap untuk menghubungkan seluruh ekosistem OBE:
// CPL -> Mata Kuliah -> CPMK -> Sub-CPMK -> Indikator -> Rubrik -> RPS (1-16) -> Kelas -> Mahasiswa -> Asesmen & Nilai -> Laporan & Radar -> CQI

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$db->exec("SET FOREIGN_KEY_CHECKS = 0;");

echo "Memulai penyiapan data dummy end-to-end OBE...\n";

// -------------------------------------------------------------
// 1. TAHUN AKADEMIK
// -------------------------------------------------------------
echo "[1/8] Sinkronisasi Tahun Akademik...\n";
$db->exec("UPDATE tahun_akademik SET is_active = 0");
$stmtTa = $db->prepare("
    INSERT INTO tahun_akademik (id, nama, is_active) 
    VALUES (1, '2026/2027 Ganjil', 1), (2, '2026/2027 Genap', 0), (3, '2025/2026 Ganjil', 0)
    ON DUPLICATE KEY UPDATE nama = VALUES(nama), is_active = VALUES(is_active)
");
$stmtTa->execute();

// -------------------------------------------------------------
// 2. DOSEN & MAHASISWA
// -------------------------------------------------------------
echo "[2/8] Menyiapkan Dosen dan Mahasiswa...\n";
// Dosen (role_id = 3)
$dosens = [
    ['username' => '0707069402', 'nama' => 'Rony Kriswibowo, M.Kom.', 'role_id' => 3],
    ['username' => '0708056701', 'nama' => 'Dr. Budi Santoso, M.Kom.', 'role_id' => 3],
    ['username' => '0709078803', 'nama' => 'Siti Rahayu, S.Kom., M.T.', 'role_id' => 3],
    ['username' => '0710089204', 'nama' => 'Andi Prasetyo, M.Kom.', 'role_id' => 3],
    ['username' => '0711098505', 'nama' => 'Dewi Lestari, S.T., M.Cs.', 'role_id' => 3],
];

$passHash = password_hash('password', PASSWORD_DEFAULT);
$stmtUser = $db->prepare("
    INSERT INTO users (username, password, nama_lengkap, role_id)
    VALUES (:username, :pass, :nama, :role_id)
    ON DUPLICATE KEY UPDATE nama_lengkap = VALUES(nama_lengkap), role_id = VALUES(role_id)
");

foreach ($dosens as $d) {
    $stmtUser->execute([
        'username' => $d['username'],
        'pass' => $passHash,
        'nama' => $d['nama'],
        'role_id' => $d['role_id']
    ]);
}

// Mahasiswa (role_id = 5) - Angkatan 2024 & 2026
$mahasiswas = [
    ['username' => '2024001', 'nama' => 'Ahmad Fauzi'],
    ['username' => '2024002', 'nama' => 'Bella Permata'],
    ['username' => '2024003', 'nama' => 'Cahya Nugraha'],
    ['username' => '2024004', 'nama' => 'Dinda Ayu Safitri'],
    ['username' => '2024005', 'nama' => 'Eko Prasetya'],
    ['username' => '2024006', 'nama' => 'Fitri Handayani'],
    ['username' => '2024007', 'nama' => 'Gilang Ramadhan'],
    ['username' => '2024008', 'nama' => 'Hana Pertiwi'],
    ['username' => '2024009', 'nama' => 'Irfan Hakim'],
    ['username' => '2024010', 'nama' => 'Jasmine Putri'],
];

foreach ($mahasiswas as $m) {
    $stmtUser->execute([
        'username' => $m['username'],
        'pass' => $passHash,
        'nama' => $m['nama'],
        'role_id' => 5
    ]);
}

// -------------------------------------------------------------
// 3. MASTER CPL (6 CPL Lengkap)
// -------------------------------------------------------------
echo "[3/8] Memastikan 6 CPL Terdaftar Lengkap...\n";
$cplDefinitions = [
    ['kode' => 'CPL01', 'rumusan' => 'Mampu menerapkan pengetahuan matematika, sains, dan prinsip rekayasa untuk menyelesaikan permasalahan di bidang sistem informasi.', 'kategori' => 'Pengetahuan', 'target' => 70.00],
    ['kode' => 'CPL02', 'rumusan' => 'Mampu mengidentifikasi, merumuskan, dan menganalisis permasalahan sistem informasi dengan pendekatan komputasi & perancangan arsitektur.', 'kategori' => 'Keterampilan Umum', 'target' => 70.00],
    ['kode' => 'CPL03', 'rumusan' => 'Mampu merancang, mengimplementasikan, dan mengevaluasi solusi perangkat lunak dan pemrograman sistem informasi yang handal.', 'kategori' => 'Keterampilan Khusus', 'target' => 70.00],
    ['kode' => 'CPL04', 'rumusan' => 'Mampu mengelola arsitektur basis data, memelihara integritas data, dan menyajikan dokumentasi teknis sistem informasi secara profesional.', 'kategori' => 'Keterampilan Khusus', 'target' => 70.00],
    ['kode' => 'CPL05', 'rumusan' => 'Mampu memimpin dan berkolaborasi dalam tim manajemen proyek sistem informasi dengan menunjukkan etika profesional dan tata kelola TI.', 'kategori' => 'Sikap & Manajerial', 'target' => 70.00],
    ['kode' => 'CPL06', 'rumusan' => 'Mampu menerapkan prinsip tata kelola keamanan informasi, manajemen risiko siber, dan audit kepatuhan dalam sistem enterprise.', 'kategori' => 'Keterampilan Khusus', 'target' => 70.00],
];

$stmtCpl = $db->prepare("
    INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status)
    VALUES (1, :kode, :rumusan, :kategori, :target, 'ACTIVE')
    ON DUPLICATE KEY UPDATE rumusan = VALUES(rumusan), target_capaian = VALUES(target_capaian)
");

foreach ($cplDefinitions as $c) {
    // Check if exists
    $exists = $db->query("SELECT id FROM cpl WHERE kode = '{$c['kode']}' AND kurikulum_id = 1")->fetchColumn();
    if ($exists) {
        $db->prepare("UPDATE cpl SET rumusan = :rumusan, kategori = :kategori, target_capaian = :target WHERE id = :id")->execute([
            'rumusan' => $c['rumusan'],
            'kategori' => $c['kategori'],
            'target' => $c['target'],
            'id' => $exists
        ]);
    } else {
        $stmtCpl->execute([
            'kode' => $c['kode'],
            'rumusan' => $c['rumusan'],
            'kategori' => $c['kategori'],
            'target' => $c['target']
        ]);
    }
}

// Ambil ID semua CPL
$cplMap = [];
$cplsDb = $db->query("SELECT id, kode FROM cpl WHERE kurikulum_id = 1")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cplsDb as $row) {
    $cplMap[$row['kode']] = $row['id'];
}

// -------------------------------------------------------------
// 4. MATA KULIAH & PEMETAAN MK-CPL
// -------------------------------------------------------------
echo "[4/8] Memetakan 5 Mata Kuliah Utama ke seluruh CPL...\n";
// MK 5: SIF101 - Pengantar Sistem Informasi (CPL01, CPL02)
// MK 6: SIF102 - Dasar Pemrograman (CPL01, CPL03)
// MK 15: SIF203 - Basis Data I (CPL02, CPL04)
// MK 41: SIF505 - Manajemen Proyek Sistem Informasi (CPL04, CPL05)
// MK 32: SIF405 - Keamanan Sistem Informasi (CPL03, CPL06)

$mkCplMappings = [
    // MK 5 (SIF101)
    ['mk_id' => 5, 'cpl_id' => $cplMap['CPL01'], 'bobot' => 50.00],
    ['mk_id' => 5, 'cpl_id' => $cplMap['CPL02'], 'bobot' => 50.00],
    // MK 6 (SIF102)
    ['mk_id' => 6, 'cpl_id' => $cplMap['CPL01'], 'bobot' => 40.00],
    ['mk_id' => 6, 'cpl_id' => $cplMap['CPL03'], 'bobot' => 60.00],
    // MK 15 (SIF203)
    ['mk_id' => 15, 'cpl_id' => $cplMap['CPL02'], 'bobot' => 40.00],
    ['mk_id' => 15, 'cpl_id' => $cplMap['CPL04'], 'bobot' => 60.00],
    // MK 41 (SIF505)
    ['mk_id' => 41, 'cpl_id' => $cplMap['CPL04'], 'bobot' => 30.00],
    ['mk_id' => 41, 'cpl_id' => $cplMap['CPL05'], 'bobot' => 70.00],
    // MK 32 (SIF405)
    ['mk_id' => 32, 'cpl_id' => $cplMap['CPL03'], 'bobot' => 30.00],
    ['mk_id' => 32, 'cpl_id' => $cplMap['CPL06'], 'bobot' => 70.00],
];

$stmtMkCpl = $db->prepare("
    INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur)
    VALUES (:mk_id, :cpl_id, :bobot, 1)
    ON DUPLICATE KEY UPDATE bobot_kontribusi = VALUES(bobot_kontribusi), is_diukur = 1
");
foreach ($mkCplMappings as $map) {
    $stmtMkCpl->execute($map);
}

// -------------------------------------------------------------
// 5. CPMK & SUB-CPMK & INDIKATOR UNTUK SELURUH 5 MK
// -------------------------------------------------------------
echo "[5/8] Menyusun CPMK, Sub-CPMK, dan Indikator terstruktur...\n";

// Definisi CPMK per Mata Kuliah
$courseStructures = [
    // MK 5: Pengantar Sistem Informasi
    5 => [
        [
            'kode' => 'CPMK-1',
            'rumusan' => 'Mampu menjelaskan konsep dasar sistem informasi dan perannya dalam transformasi organisasi.',
            'cpl' => 'CPL01',
            'subs' => [
                ['kode' => 'Sub-CPMK-1.1', 'rumusan' => 'Mampu mengidentifikasi komponen teknologi, manusia, dan proses pada SI', 'bobot' => 50, 'taksonomi' => 'C2 - Pemahaman', 'indikators' => ['Ketepatan menjelaskan komponen SI', 'Mampu membedakan tipe SI organisasi']],
                ['kode' => 'Sub-CPMK-1.2', 'rumusan' => 'Mampu memetakan jenis sistem informasi (TPS, MIS, DSS, ERP) pada studi kasus nyata', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Kesesuaian pemetaan jenis SI', 'Ketepatan identifikasi alur kerja']]
            ]
        ],
        [
            'kode' => 'CPMK-2',
            'rumusan' => 'Mampu menyusun analisis kebutuhan dan perancangan awal sistem informasi tingkat pemula.',
            'cpl' => 'CPL02',
            'subs' => [
                ['kode' => 'Sub-CPMK-2.1', 'rumusan' => 'Mampu menyusun dokumen kebutuhan fungsional dan non-fungsional sistem', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Kelengkapan daftar kebutuhan sistem', 'Format SRS baku']],
                ['kode' => 'Sub-CPMK-2.2', 'rumusan' => 'Mampu menggambarkan diagram alir data (DFD) dan Use Case diagram', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Ketepatan notasi UML/DFD', 'Kesesuaian logika skenario']]
            ]
        ]
    ],

    // MK 6: Dasar Pemrograman
    6 => [
        [
            'kode' => 'CPMK-1',
            'rumusan' => 'Mampu menerapkan logika pemrograman dasar, tipe data, dan kontrol percabangan/perulangan.',
            'cpl' => 'CPL01',
            'subs' => [
                ['kode' => 'Sub-CPMK-1.1', 'rumusan' => 'Mampu mengimplementasikan struktur kontrol alir algoritma (if-else, loop)', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Algoritma berjalan tanpa error logika', 'Efisiensi penulisan struktur kondisi']],
                ['kode' => 'Sub-CPMK-1.2', 'rumusan' => 'Mampu mengolah data terstruktur menggunakan Array dan Struktur Data Dasar', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Ketepatan indexing array', 'Manipulasi data koleksi']]
            ]
        ],
        [
            'kode' => 'CPMK-2',
            'rumusan' => 'Mampu merancang dan mengimplementasikan program modular berbasis fungsi untuk solusi sistem.',
            'cpl' => 'CPL03',
            'subs' => [
                ['kode' => 'Sub-CPMK-2.1', 'rumusan' => 'Mampu mendesain arsitektur program modular dengan parameter passing dan return value', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Kemandirian modul/fungsi', 'Kerapian clean code']],
                ['kode' => 'Sub-CPMK-2.2', 'rumusan' => 'Mampu memecahkan studi kasus aplikasi CLI terpadu dengan validasi input', 'bobot' => 50, 'taksonomi' => 'C5 - Evaluasi', 'indikators' => ['Handling error input pengguna', 'Hasil eksekusi program sempurna']]
            ]
        ]
    ],

    // MK 15: Basis Data I
    15 => [
        [
            'kode' => 'CPMK-1',
            'rumusan' => 'Mampu merancang model data konseptual dan logikal (ERD & Normalisasi 1NF-3NF).',
            'cpl' => 'CPL02',
            'subs' => [
                ['kode' => 'Sub-CPMK-1.1', 'rumusan' => 'Mampu merancang Entity Relationship Diagram (ERD) dengan kardinalitas yang tepat', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Identifikasi entitas dan relasi', 'Penentuan primary key dan foreign key']],
                ['kode' => 'Sub-CPMK-1.2', 'rumusan' => 'Mampu melakukan dekomposisi tabel melalui proses normalisasi hingga 3NF', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Eliminasi dependensi parsial', 'Eliminasi dependensi transitif']]
            ]
        ],
        [
            'kode' => 'CPMK-2',
            'rumusan' => 'Mampu mengimplementasikan struktur tabel dan query manipulasi data SQL kompleks pada RDBMS.',
            'cpl' => 'CPL04',
            'subs' => [
                ['kode' => 'Sub-CPMK-2.1', 'rumusan' => 'Mampu menyusun skrip DDL lengkap dengan constraint relasional referensial', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Validitas sintaks DDL SQL', 'Penerapan foreign key cascade/restrict']],
                ['kode' => 'Sub-CPMK-2.2', 'rumusan' => 'Mampu menyusun query DML tingkat lanjut (JOIN, Subquery, Agregasi GROUP BY)', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Kebenaran hasil query multi-tabel', 'Optimalisasi indeks database']]
            ]
        ]
    ],

    // MK 41: Manajemen Proyek Sistem Informasi
    41 => [
        [
            'kode' => 'CPMK-1',
            'rumusan' => 'Mampu menyusun dokumen perencanaan proyek SI (Project Charter, WBS, Gantt Chart, Budgeting).',
            'cpl' => 'CPL05',
            'subs' => [
                ['kode' => 'Sub-CPMK-1.1', 'rumusan' => 'Mampu merumuskan lingkup kerja proyek menggunakan Work Breakdown Structure (WBS)', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Dekomposisi deliverable proyek', 'Kesesuaian estimasi durasi tugas']],
                ['kode' => 'Sub-CPMK-1.2', 'rumusan' => 'Mampu menyusun anggaran biaya proyek dan jadwal kritis (CPM/PERT)', 'bobot' => 50, 'taksonomi' => 'C5 - Evaluasi', 'indikators' => ['Perhitungan biaya tenaga kerja & resource', 'Jalur kritis waktu proyek']]
            ]
        ],
        [
            'kode' => 'CPMK-2',
            'rumusan' => 'Mampu mengelola komunikasi tim, monitoring sprint agile, dan presentasi milestone proyek.',
            'cpl' => 'CPL04',
            'subs' => [
                ['kode' => 'Sub-CPMK-2.1', 'rumusan' => 'Mampu memimpin koordinasi sprint dan dokumentasi backlog proyek Scrum', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Pelaksanaan daily standup & retrospektif', 'Burn-down chart progress']],
                ['kode' => 'Sub-CPMK-2.2', 'rumusan' => 'Mampu mempresentasikan laporan akhir deliverable kepada stakeholder eksternal', 'bobot' => 50, 'taksonomi' => 'C5 - Evaluasi', 'indikators' => ['Komunikasi persuasif & profesional', 'Kualitas demo artefak sistem']]
            ]
        ]
    ],

    // MK 32: Keamanan Sistem Informasi
    32 => [
        [
            'kode' => 'CPMK-1',
            'rumusan' => 'Mampu mengidentifikasi celah keamanan aplikasi web dan ancaman siber (OWASP Top 10).',
            'cpl' => 'CPL03',
            'subs' => [
                ['kode' => 'Sub-CPMK-1.1', 'rumusan' => 'Mampu melakukan simulasi deteksi celah SQL Injection dan Cross-Site Scripting (XSS)', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Eksploitasi terkontrol pada lab siber', 'Dokumentasi bukti kerentanan']],
                ['kode' => 'Sub-CPMK-1.2', 'rumusan' => 'Mampu menerapkan mekanisme validasi input dan parameterized query untuk hardening kode', 'bobot' => 50, 'taksonomi' => 'C3 - Penerapan', 'indikators' => ['Sanitasi payload berbahaya', 'Penerapan prepared statement aman']]
            ]
        ],
        [
            'kode' => 'CPMK-2',
            'rumusan' => 'Mampu merancang matriks risiko keamanan informasi (ISO 27001) dan rencana pemulihan bencana (DRP).',
            'cpl' => 'CPL06',
            'subs' => [
                ['kode' => 'Sub-CPMK-2.1', 'rumusan' => 'Mampu melakukan risk assessment berdasarkan probabilitas dan dampak ancaman', 'bobot' => 50, 'taksonomi' => 'C5 - Evaluasi', 'indikators' => ['Pembuatan matriks risk register', 'Kategori mitigasi risiko']],
                ['kode' => 'Sub-CPMK-2.2', 'rumusan' => 'Mampu merumuskan kebijakan backup, disaster recovery, dan insiden response', 'bobot' => 50, 'taksonomi' => 'C4 - Analisis', 'indikators' => ['Prosedur pemulihan sistem (RTO/RPO)', 'Protokol eskalasi insiden siber']]
            ]
        ]
    ],
];

// Bersihkan CPMK lama untuk 5 MK di atas agar bersih dan terstruktur
$mkIds = array_keys($courseStructures);
$mkIdPlaceholders = implode(',', $mkIds);

$db->exec("DELETE FROM cpmk WHERE mk_id IN ($mkIdPlaceholders)");

$allCreatedSubs = []; // [mk_id => [sub_id, ...]]

$stmtInsCpmk = $db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (:mk_id, :kode, :rumusan)");
$stmtInsCpmkCpl = $db->prepare("INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
$stmtInsSub = $db->prepare("INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, bobot, level_taksonomi, urutan) VALUES (:cpmk_id, :kode, :rumusan, :bobot, :taksonomi, :urutan)");
$stmtInsInd = $db->prepare("INSERT INTO sub_cpmk_indikator (sub_cpmk_id, indikator) VALUES (:sub_id, :ind)");

foreach ($courseStructures as $mkId => $cpmks) {
    $allCreatedSubs[$mkId] = [];
    $subOrder = 1;

    foreach ($cpmks as $cData) {
        $stmtInsCpmk->execute([
            'mk_id' => $mkId,
            'kode' => $cData['kode'],
            'rumusan' => $cData['rumusan']
        ]);
        $cpmkId = $db->lastInsertId();

        // Relasi ke CPL
        $targetCplCode = $cData['cpl'];
        if (isset($cplMap[$targetCplCode])) {
            $stmtInsCpmkCpl->execute([
                'cpmk_id' => $cpmkId,
                'cpl_id' => $cplMap[$targetCplCode]
            ]);
        }

        // Sub-CPMKs
        foreach ($cData['subs'] as $sData) {
            $stmtInsSub->execute([
                'cpmk_id' => $cpmkId,
                'kode' => $sData['kode'],
                'rumusan' => $sData['rumusan'],
                'bobot' => $sData['bobot'],
                'taksonomi' => $sData['taksonomi'],
                'urutan' => $subOrder++
            ]);
            $subId = $db->lastInsertId();
            $allCreatedSubs[$mkId][] = $subId;

            // Indikators
            foreach ($sData['indikators'] as $indText) {
                $stmtInsInd->execute([
                    'sub_id' => $subId,
                    'ind' => $indText
                ]);
            }
        }
    }
}

// -------------------------------------------------------------
// 6. RPS RINCIAN PERTEMUAN (16 PERTEMUAN PER MK)
// -------------------------------------------------------------
echo "[6/8] Menghasilkan 16 Rincian Pertemuan RPS untuk setiap MK...\n";
require_once __DIR__ . '/../app/Models/RpsPertemuanModel.php';
$rpsModel = new RpsPertemuanModel();

foreach ($mkIds as $mkId) {
    $db->exec("DELETE FROM rps_pertemuan WHERE mata_kuliah_id = $mkId");
    $rpsModel->generateTemplate16($mkId);
}

// -------------------------------------------------------------
// 7. KELAS KULIAH & PESERTA KELAS
// -------------------------------------------------------------
echo "[7/8] Menyiapkan Kelas Kuliah dan Mendaftarkan 10 Mahasiswa...\n";
// Buat kelas aktif untuk 5 MK
$dosenUsers = $db->query("SELECT id FROM users WHERE role_id = 3 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$mhsUsers = $db->query("SELECT id FROM users WHERE role_id = 5 ORDER BY username")->fetchAll(PDO::FETCH_COLUMN);

$kelasCreated = []; // [mk_id => kelas_id]

$stmtInsKelas = $db->prepare("
    INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id)
    VALUES (:mk_id, 1, 'A', :dosen_id)
");
$stmtInsKm = $db->prepare("INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES (:kelas_id, :mhs_id)");

foreach ($mkIds as $idx => $mkId) {
    // Delete existing kelas for this MK to be clean
    $oldKelas = $db->query("SELECT id FROM kelas WHERE mata_kuliah_id = $mkId")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($oldKelas as $okId) {
        $db->exec("DELETE FROM kelas WHERE id = $okId");
    }

    $dosenId = $dosenUsers[$idx % count($dosenUsers)];
    $stmtInsKelas->execute([
        'mk_id' => $mkId,
        'dosen_id' => $dosenId
    ]);
    $kId = $db->lastInsertId();
    $kelasCreated[$mkId] = $kId;

    // Enrol all 10 students
    foreach ($mhsUsers as $mId) {
        $stmtInsKm->execute([
            'kelas_id' => $kId,
            'mhs_id' => $mId
        ]);
    }
}

// -------------------------------------------------------------
// 8. ASESMEN & NILAI MAHASISWA (PONDASI UTAMA OBESITAS DATA)
// -------------------------------------------------------------
echo "[8/8] Menginput Nilai Asesmen Sub-CPMK (Menghubungkan CPL ke Laporan & Radar)...\n";

// Pola profil nilai mahasiswa (variasi akademik realistis):
// 1: Ahmad Fauzi (High Achiever: 80 - 90)
// 2: Bella Permata (Solid: 76 - 84)
// 3: Cahya Nugraha (Top Rank: 88 - 96)
// 4: Dinda Ayu (Sedang, agak lemah di koding/CPL03: 60 - 75)
// 5: Eko Prasetya (Kuat di manajemen tim/CPL05: 86, cukup di teori: 68)
// 6: Fitri Handayani (Stabil konsisten: 78 - 85)
// 7: Gilang Ramadhan (Perlu pembinaan di Security CPL06 & Koding CPL03: 52 - 62)
// 8: Hana Pertiwi (Sangat baik: 82 - 92)
// 9: Irfan Hakim (Rata-rata: 70 - 75)
// 10: Jasmine Putri (Kuat di perancangan CPL02 & PM CPL05: 85 - 90)

$scoreProfiles = [
    0 => ['base' => 84, 'mod' => [1=>2, 2=>2, 3=>-8, 4=>3, 5=>5, 6=>-14]],
    1 => ['base' => 76, 'mod' => [1=>1, 2=>3, 3=>-9, 4=>2, 5=>4, 6=>-12]],
    2 => ['base' => 90, 'mod' => [1=>2, 2=>3, 3=>-4, 4=>3, 5=>2, 6=>-9]],
    3 => ['base' => 68, 'mod' => [1=>0, 2=>2, 3=>-10, 4=>1, 5=>3, 6=>-15]],
    4 => ['base' => 74, 'mod' => [1=>-4, 2=>0, 3=>-8, 4=>2, 5=>12, 6=>-12]],
    5 => ['base' => 78, 'mod' => [1=>1, 2=>2, 3=>-7, 4=>1, 5=>3, 6=>-10]],
    6 => ['base' => 58, 'mod' => [1=>2, 2=>-2, 3=>-11, 4=>-3, 5=>4, 6=>-15]],
    7 => ['base' => 85, 'mod' => [1=>2, 2=>1, 3=>-4, 4=>2, 5=>4, 6=>-8]],
    8 => ['base' => 72, 'mod' => [1=>0, 2=>1, 3=>-7, 4=>0, 5=>2, 6=>-11]],
    9 => ['base' => 80, 'mod' => [1=>-1, 2=>5, 3=>-6, 4=>2, 5=>7, 6=>-12]],
];

// Bersihkan asesmen_nilai untuk kelas-kelas ini
$kelasIds = array_values($kelasCreated);
$kIdsStr = implode(',', $kelasIds);
$db->exec("DELETE FROM asesmen_nilai WHERE kelas_id IN ($kIdsStr)");

$stmtInsNilai = $db->prepare("
    INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
    VALUES (:kelas_id, :mhs_id, :sub_id, :nilai)
    ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
");

// Petakan setiap sub_id ke CPL yang diaturnya agar pemberian nilai sinkron
$subToCplMap = [];
$subCplStmt = $db->query("
    SELECT sc.id as sub_id, cc.cpl_id, c.kode as cpl_kode
    FROM sub_cpmk sc
    JOIN cpmk cp ON sc.cpmk_id = cp.id
    JOIN cpmk_cpl cc ON cp.id = cc.cpmk_id
    JOIN cpl c ON cc.cpl_id = c.id
");
while ($r = $subCplStmt->fetch(PDO::FETCH_ASSOC)) {
    $cplNum = intval(str_replace('CPL0', '', $r['cpl_kode']));
    $subToCplMap[$r['sub_id']] = $cplNum;
}

$totalNilaiInserted = 0;
foreach ($kelasCreated as $mkId => $kId) {
    $subsInMk = $allCreatedSubs[$mkId] ?? [];

    foreach ($mhsUsers as $mIdx => $mhsId) {
        $profile = $scoreProfiles[$mIdx] ?? ['base' => 75, 'mod' => []];

        foreach ($subsInMk as $sIdx => $subId) {
            $cplNum = $subToCplMap[$subId] ?? 1;
            $mod = $profile['mod'][$cplNum] ?? 0;
            // Variasi kecil acak halus antar sub-cpmk
            $subNoise = (($sIdx % 2 == 0) ? 1.5 : -1.5);
            $finalScore = $profile['base'] + $mod + $subNoise;
            $finalScore = max(45.0, min(100.0, round($finalScore, 1)));

            $stmtInsNilai->execute([
                'kelas_id' => $kId,
                'mhs_id' => $mhsId,
                'sub_id' => $subId,
                'nilai' => $finalScore
            ]);
            $totalNilaiInserted++;
        }
    }
}

echo "Berhasil memasukkan $totalNilaiInserted data nilai asesmen mahasiswa!\n";

// -------------------------------------------------------------
// 9. REKOMENDASI CQI REALISTIS BERDASARKAN HASIL NILAI
// -------------------------------------------------------------
echo "[Bonus] Menyiapkan Rekam Jejak Tindak Lanjut CQI untuk CPL di bawah target...\n";
$db->exec("DELETE FROM cqi");

$cqiIns = $db->prepare("
    INSERT INTO cqi 
    (cpl_id, tahun_akademik_id, semester, target_capaian, capaian_aktual, analisis_masalah, akar_masalah, rencana_tindak_lanjut, pic, deadline, monitoring_evaluasi, status, hasil_re_measurement)
    VALUES 
    (:cpl_id, 1, 'Ganjil', :target, :aktual, :analisis, :akar, :rencana, :pic, :deadline, :monitoring, :status, :re_score)
");

// Kasus 1: CPL03 (Pengembangan Perangkat Lunak - Status IN_PROGRESS)
$cqiIns->execute([
    'cpl_id' => $cplMap['CPL03'],
    'target' => 70.00,
    'aktual' => 66.80,
    'analisis' => 'Rata-rata mahasiswa angkatan 2024 pada CPL03 berada di angka 66.8% (di bawah batas target mutu prodi 70%). Kesenjangan terbesar terlihat pada Sub-CPMK modularitas fungsi dan pemrograman terstruktur.',
    'akar' => '1. Mahasiswa kurang latihan coding mandiri di luar jam praktikum. 2. Perbedaan latar belakang kemampuan logika algoritma saat masuk prodi. 3. Sesi asistensi coding belum terjadwal rutin.',
    'rencana' => '1. Mengadakan sesi Peer-Tutoring / Klinik Koding mingguan bersama asisten lab. 2. Mewajibkan 5 mini-project latihan mandiri di platform HackerRank/Git. 3. Merevisi rubrik asesmen agar memberikan bobot lebih pada unit-testing.',
    'pic' => 'Tim Dosen KBK Rekayasa Perangkat Lunak',
    'deadline' => '2026-12-15',
    'monitoring' => 'Sesi klinik koding telah dimulai sejak minggu ke-4 dengan kehadiran rata-rata 85% mahasiswa yang memerlukan perbaikan.',
    'status' => 'IN_PROGRESS',
    're_score' => null
]);

// Kasus 2: CPL06 (Keamanan Informasi & Manajemen Risiko - Status RE_MEASURED)
$cqiIns->execute([
    'cpl_id' => $cplMap['CPL06'],
    'target' => 70.00,
    'aktual' => 64.50,
    'analisis' => 'Pada evaluasi semester sebelumnya, capaian CPL06 hanya mencapai 64.5% akibat rendahnya skor unjuk kerja hands-on penanggulangan celah injeksi dan disaster recovery.',
    'akar' => 'Lingkungan laboratorium keamanan siber belum memiliki mesin virtual (VM) terisolasi untuk simulasi serangan siber secara interaktif dan aman.',
    'rencana' => '1. Menginstalasi server sandbox virtual lab siber (DVWA & WebGoat). 2. Mengintegrasikan studi kasus audit kerentanan ISO 27001 pada pertemuan ke-11 s.d. 13.',
    'pic' => 'Andi Prasetyo, M.Kom. (Dosen Keamanan SI)',
    'deadline' => '2026-10-30',
    'monitoring' => 'Virtual lab telah aktif digunakan dan evaluasi siklus kedua selesai dilaksanakan.',
    'status' => 'RE_MEASURED',
    're_score' => 76.80 // Capaian berhasil naik melebihi target!
]);

$db->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "\n=======================================================\n";
echo "✓ SUKSES! Seluruh data dummy OBE berhasil disinkronkan:\n";
echo "  - 6 CPL aktif dengan target capaian 70%\n";
echo "  - 5 Mata Kuliah pengukur utama terhubung ke CPL\n";
echo "  - 10 CPMK & 20 Sub-CPMK lengkap dengan indikator\n";
echo "  - 5 Mata Kuliah memiliki 16 Rincian Pertemuan RPS (total 80 pertemuan)\n";
echo "  - 5 Kelas aktif diikuti oleh 10 Mahasiswa\n";
echo "  - $totalNilaiInserted data nilai asesmen Sub-CPMK tersimpan\n";
echo "  - 2 Rekaman siklus evaluasi mutu CQI\n";
echo "=======================================================\n";
