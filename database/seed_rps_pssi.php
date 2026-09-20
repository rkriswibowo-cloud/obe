<?php
// database/seed_rps_pssi.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Mempersiapkan data RPS PSSI SIF604 sesuai dokumen referensi Rev 3.0...\n";

// 1. Cek atau Update Mata Kuliah SIF604
$stmt = $db->prepare("SELECT id FROM mata_kuliah WHERE kode = 'SIF604' LIMIT 1");
$stmt->execute();
$mk = $stmt->fetch();

if ($mk) {
    $mkId = $mk['id'];
    $db->prepare("UPDATE mata_kuliah SET 
        nama = 'Perencanaan Strategis Sistem Informasi',
        sks_teori = 1,
        sks_praktek = 0,
        semester = 6,
        sifat = 'Wajib',
        kelompok_keilmuan = 'Sistem Informasi',
        deskripsi = 'Mata kuliah Perencanaan Strategis Sistem Informasi (PSSI) membekali mahasiswa dengan kompetensi analisis lingkungan bisnis, perumusan visi misi SI, perancangan arsitektur enterprise, penyusunan roadmap implementasi, manajemen risiko, serta evaluasi KPI sistem informasi yang selaras dengan tujuan strategis organisasi.'
        WHERE id = ?")->execute([$mkId]);
    echo "Mata Kuliah SIF604 diperbarui (ID: $mkId).\n";
} else {
    // Ambil kurikulum aktif pertama
    $kurikulumId = $db->query("SELECT id FROM kurikulum LIMIT 1")->fetchColumn() ?: 1;
    $db->prepare("INSERT INTO mata_kuliah (kurikulum_id, kode, nama, sks_teori, sks_praktek, semester, sifat, kelompok_keilmuan, deskripsi, status)
        VALUES (?, 'SIF604', 'Perencanaan Strategis Sistem Informasi', 1, 0, 6, 'Wajib', 'Sistem Informasi', 'Perencanaan Strategis Sistem Informasi', 'ACTIVE')")
        ->execute([$kurikulumId]);
    $mkId = $db->lastInsertId();
    echo "Mata Kuliah SIF604 baru dibuat (ID: $mkId).\n";
}

// 2. Hubungkan ke CPL04
$cplStmt = $db->prepare("SELECT id FROM cpl WHERE kode = 'CPL04' LIMIT 1");
$cplStmt->execute();
$cpl04Id = $cplStmt->fetchColumn();

if (!$cpl04Id) {
    // Buat CPL04 jika belum ada
    $kurikulumId = $db->query("SELECT id FROM kurikulum LIMIT 1")->fetchColumn() ?: 1;
    $db->prepare("INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status) 
        VALUES (?, 'CPL04', 'Mampu merancang dan menggunakan database, serta mengolah dan menganalisa data dengan alat dan teknik pengolahan data.', 'Keterampilan Khusus', 70.00, 'ACTIVE')")
        ->execute([$kurikulumId]);
    $cpl04Id = $db->lastInsertId();
}

$db->prepare("INSERT IGNORE INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur) VALUES (?, ?, 100.00, 1)")
   ->execute([$mkId, $cpl04Id]);

// 3. Simpan Metadata RPS
$referensiList = "1. Ward, J., & Peppard, J. (2016). Strategic Planning for Information Systems (4th ed.). Wiley.
2. Pearlson, K. E., Saunders, C. S., & Galletta, D. F. (2020). Managing and Using Information Systems: A Strategic Approach (7th ed.). Wiley.
3. O'Brien, J. A., & Marakas, G. M. (2017). Management Information Systems (10th ed.). McGraw-Hill Education.
4. TOGAF Standard, Version 9.2. (2018). The Open Group.
5. Laudon, K. C., & Laudon, J. P. (2020). Management Information Systems: Managing the Digital Firm (16th ed.). Pearson.
6. Andrew, J. (2011). TOGAF Version 9.1 A Pocket Guide. Berkshire: Van Haren Publishing.
7. Dietz, J. L., & Hoogervorst, J. A. (2013). An Enterprise Engineering Based Examination of TOGAF. Lecturer Notes on Bussiness Information Processing (LNBIP), 79.
8. Examination of TOGAF. Lecturer Notes on Bussiness Information Processing (LNBIP), 79.
9. Fitriani, L. (2016). Perencanaan Arsitektur Enterprise Sistem Informasi dengan Menggunakan TOGAF ADM (Studi Kasus Dinas Perhubungan Kab.Garut). Jurnal STT-Garut, 443-450.";

$rencanaEvaluasiJson = json_encode([
    'SUB-CPMK 01' => ['bobot' => 6, 'tugas' => 'Mengerjakan soal terkait Kerangka Kerja Strategis SI (Bentuk Tugas)', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Aktif Tanya-Jawab'],
    'SUB-CPMK 02' => ['bobot' => 6, 'tugas' => '-', 'kuis' => 'Mahasiswa mengerjakan 25 Soal terkait Metode Proyek (Bentuk Kuis)', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Diskusi Kasus SWOT'],
    'SUB-CPMK 03' => ['bobot' => 6, 'tugas' => '-', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Formulasi Visi Misi'],
    'SUB-CPMK 04' => ['bobot' => 6, 'tugas' => '-', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Desain Arsitektur'],
    'SUB-CPMK 05' => ['bobot' => 13, 'tugas' => 'Mengerjakan soal terkait roadmap implementasi SI (Bentuk Tugas)', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Penyusunan Timeline'],
    'SUB-CPMK 06' => ['bobot' => 13, 'tugas' => '-', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Matriks Mitigasi Risiko'],
    'SUB-CPMK 07' => ['bobot' => 13, 'tugas' => '-', 'kuis' => '-', 'uts' => '5 Soal', 'uas' => '5 Soal', 'keaktifan' => 'Presentasi Proyek Akhir KPI']
], JSON_PRETTY_PRINT);

$rubrikJson = json_encode([
    'holistik' => [
        'judul' => 'Rubrik Penilaian Holistik (Tugas Kerangka Kerja Strategis SI)',
        'kriteria' => [
            [
                'nama' => 'Kemampuan Analisis Kasus & Perancangan Kerangka Kerja Strategis Sistem Informasi',
                'bobot_persen' => 80,
                'skala' => [
                    ['skor' => '< 20', 'deskripsi' => 'Rancangan yang disajikan tidak teratur, tidak menggunakan kerangka kerja SI, dan tidak menyelesaikan permasalahan'],
                    ['skor' => '21 - 40', 'deskripsi' => 'Rancangan yang disajikan teratur namun kurang menjawab akar permasalahan strategis organisasi'],
                    ['skor' => '41 - 60', 'deskripsi' => 'Rancangan yang disajikan tersistematis, menyelesaikan sebagian masalah, namun kurang realistis untuk diimplementasikan'],
                    ['skor' => '61 - 80', 'deskripsi' => 'Rancangan yang disajikan sistematis, menyelesaikan masalah, dapat diimplementasikan, namun inovasi terbatas'],
                    ['skor' => '> 81', 'deskripsi' => 'Rancangan yang disajikan sangat sistematis, metodologi tepat, implementatif, dan memberikan inovasi bernilai tinggi bagi organisasi']
                ]
            ],
            [
                'nama' => 'Ketepatan Waktu & Kerjasama Kelompok',
                'bobot_persen' => 20,
                'skala' => [
                    ['skor' => '100', 'deskripsi' => 'Tepat waktu sesuai jadwal yang ditentukan'],
                    ['skor' => '80', 'deskripsi' => 'Terlambat <= 1 minggu dengan konfirmasi alasan akademis'],
                    ['skor' => '70', 'deskripsi' => 'Terlambat > 1 minggu'],
                    ['skor' => '0', 'deskripsi' => 'Tidak mengumpulkan tugas']
                ]
            ]
        ]
    ],
    'analitik' => [
        'judul' => 'Rubrik Penilaian Analitik (Penyusunan Rencana Strategis & Arsitektur SI)',
        'dimensi' => [
            [
                'nama' => 'Analisis Lingkungan Organisasi',
                'bobot' => 25,
                'sangat_baik' => 'Analisis SWOT/PESTEL dan Value Chain sangat tajam, data empiris lengkap, isu strategis terpetakan akurat.',
                'baik' => 'Analisis internal dan eksternal runtut, data memadai, isu strategis teridentifikasi dengan baik.',
                'cukup' => 'Analisis disajikan namun dangkal, data pendukung minim, korelasi isu strategis kurang kuat.',
                'kurang' => 'Analisis tidak lengkap, instrumen atau kerangka analisis keliru/tidak relevan.',
                'sangat_kurang' => 'Tidak melakukan analisis lingkungan organisasi sama sekali.'
            ],
            [
                'nama' => 'Perancangan Arsitektur SI',
                'bobot' => 30,
                'sangat_baik' => 'Blueprint arsitektur data, aplikasi, dan teknologi sangat selaras dengan visi-misi; adopsi TOGAF ADM tepat & modular.',
                'baik' => 'Blueprint arsitektur terstruktur baik, framework diterapkan benar, kebutuhan fungsional terpenuhi.',
                'cukup' => 'Blueprint disajikan namun ada komponen yang inkonsisten atau integrasi antar sistem kurang jelas.',
                'kurang' => 'Blueprint tidak terstruktur, tidak mengacu framework standar perencanaan SI.',
                'sangat_kurang' => 'Tidak menyajikan rancangan arsitektur sistem informasi.'
            ],
            [
                'nama' => 'Roadmap & Manajemen Risiko',
                'bobot' => 25,
                'sangat_baik' => 'Roadmap multi-tahun sangat realistis (timeline, milestone, PIC); matriks mitigasi risiko terperinci dan aplikatif.',
                'baik' => 'Roadmap implementasi jelas, prioritas proyek logis, strategi mitigasi risiko teridentifikasi baik.',
                'cukup' => 'Roadmap disajikan garis besar tanpa rincian milestone; mitigasi risiko minim.',
                'kurang' => 'Roadmap tidak realistis dan tidak ada rencana penanganan risiko.',
                'sangat_kurang' => 'Tidak menyusun roadmap implementasi dan analisis risiko.'
            ],
            [
                'nama' => 'Mutu Laporan & Presentasi',
                'bobot' => 20,
                'sangat_baik' => 'Format laporan akademik sempurna (APA Style), visual diagram profesional, presentasi sangat komunikatif & persuasif.',
                'baik' => 'Laporan rapi, sitasi lengkap, diagram informatif, presentasi berjalan lancar dan komunikatif.',
                'cukup' => 'Format ada inkonsistensi, sitasi minim, presentasi cukup namun kurang menguasai materi.',
                'kurang' => 'Format tidak sesuai panduan, tata bahasa buruk, presentasi pasif.',
                'sangat_kurang' => 'Laporan tidak layak dan tidak melakukan presentasi hasil.'
            ]
        ]
    ],
    'skala_persepsi' => [
        'judul' => 'Rubrik Penilaian Skala Persepsi (Afektif & Kerjasama Tim)',
        'aspek' => [
            ['nama' => 'Kerjasama Tim & Kontribusi Tugas', 'bobot' => 25],
            ['nama' => 'Keaktifan & Argumentasi Ilmiah', 'bobot' => 25],
            ['nama' => 'Etika Akademik & Menghargai Pendapat', 'bobot' => 25],
            ['nama' => 'Kedisiplinan & Manajemen Waktu', 'bobot' => 25]
        ]
    ],
    'portofolio' => [
        'judul' => 'Rubrik Penilaian Portofolio Mahasiswa (Kumpulan Artefak Proyek SI)',
        'artefak' => [
            ['no' => 1, 'nama' => 'Laporan Analisis Lingkungan Bisnis & SI', 'sub_cpmk' => 'Sub-CPMK 01 & 02', 'bobot' => 20, 'minggu' => 'Minggu Ke-4'],
            ['no' => 2, 'nama' => 'Blueprint Arsitektur Sistem Informasi', 'sub_cpmk' => 'Sub-CPMK 03 & 04', 'bobot' => 30, 'minggu' => 'Minggu Ke-8 (UTS)'],
            ['no' => 3, 'nama' => 'Roadmap Implementasi & Risk Register', 'sub_cpmk' => 'Sub-CPMK 05 & 06', 'bobot' => 25, 'minggu' => 'Minggu Ke-12'],
            ['no' => 4, 'nama' => 'Laporan Akhir Terpadu & Refleksi Diri', 'sub_cpmk' => 'Sub-CPMK 07', 'bobot' => 15, 'minggu' => 'Minggu Ke-15'],
            ['no' => 5, 'nama' => 'Kelengkapan Repositori & Orisinalitas', 'sub_cpmk' => 'Seluruh Sub-CPMK', 'bobot' => 10, 'minggu' => 'Minggu Ke-16 (UAS)']
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$db->prepare("INSERT INTO rps (
    mata_kuliah_id, versi, fakultas, program_studi, kelas, sifat, semester_ke,
    ketersediaan, bentuk_pembelajaran, media_pembelajaran, kelompok_keilmuan,
    prasyarat, dosen_pengampu, tahun_akademik, referensi,
    sistem_penilaian_desc, bobot_keaktifan, bobot_tugas, bobot_kuis, bobot_uts, bobot_uas,
    sistem_evaluasi_desc, rencana_evaluasi_json, rubrik_penilaian_json
) VALUES (
    ?, '3/0', 'Fakultas Sains dan Teknologi', 'S1 Sistem Informasi', 'I-23', 'Wajib', '6',
    'Program Studi', 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara/ Praktikum/ Praktik Studio/Praktek Lapangan/ Penelitian/ Pengabdian Kepada Masyarakat',
    '*Audio/Visual/Audiovisual', 'Sistem Informasi', '-', '1. Rony Kriswibowo, M.Kom (PJMK)', 'Genap 2025/2026',
    ?, 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.',
    5.00, 15.00, 20.00, 30.00, 30.00,
    'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.',
    ?, ?
) ON DUPLICATE KEY UPDATE 
    versi = VALUES(versi),
    fakultas = VALUES(fakultas),
    program_studi = VALUES(program_studi),
    kelas = VALUES(kelas),
    sifat = VALUES(sifat),
    semester_ke = VALUES(semester_ke),
    ketersediaan = VALUES(ketersediaan),
    bentuk_pembelajaran = VALUES(bentuk_pembelajaran),
    media_pembelajaran = VALUES(media_pembelajaran),
    kelompok_keilmuan = VALUES(kelompok_keilmuan),
    prasyarat = VALUES(prasyarat),
    dosen_pengampu = VALUES(dosen_pengampu),
    tahun_akademik = VALUES(tahun_akademik),
    referensi = VALUES(referensi),
    sistem_penilaian_desc = VALUES(sistem_penilaian_desc),
    bobot_keaktifan = VALUES(bobot_keaktifan),
    bobot_tugas = VALUES(bobot_tugas),
    bobot_kuis = VALUES(bobot_kuis),
    bobot_uts = VALUES(bobot_uts),
    bobot_uas = VALUES(bobot_uas),
    sistem_evaluasi_desc = VALUES(sistem_evaluasi_desc),
    rencana_evaluasi_json = VALUES(rencana_evaluasi_json),
    rubrik_penilaian_json = VALUES(rubrik_penilaian_json)
")->execute([$mkId, $referensiList, $rencanaEvaluasiJson, $rubrikJson]);

echo "Metadata tabel RPS tersimpan.\n";

// 4. Bersihkan CPMK & Sub-CPMK lama milik SIF604 untuk menyelaraskan dengan PDF Rev 3.0
$existingCpmks = $db->prepare("SELECT id FROM cpmk WHERE mk_id = ?");
$existingCpmks->execute([$mkId]);
$cpmkIds = $existingCpmks->fetchAll(PDO::FETCH_COLUMN);

if (!empty($cpmkIds)) {
    $placeholders = implode(',', array_fill(0, count($cpmkIds), '?'));
    $db->prepare("DELETE FROM cpmk WHERE id IN ($placeholders)")->execute($cpmkIds);
}

// 5. Buat CPMK 1 dan CPMK 2
// CPMK 1
$stmt = $db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (?, 'CPMK 1', 'Mampu mengolah data dengan alat dan teknik pengolahan data (C3, A3, P3)')");
$stmt->execute([$mkId]);
$cpmk1Id = $db->lastInsertId();
$db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (?, ?)")->execute([$cpmk1Id, $cpl04Id]);

// CPMK 2
$stmt = $db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (?, 'CPMK 2', 'Mampu menganalisa data dengan alat dan teknik pengolahan data (C3, A3, P3)')");
$stmt->execute([$mkId]);
$cpmk2Id = $db->lastInsertId();
$db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (?, ?)")->execute([$cpmk2Id, $cpl04Id]);

// 6. Buat 7 Sub-CPMK dengan Indikatornya
$subCpmkData = [
    // Sub-CPMK under CPMK 1
    [
        'cpmk_id' => $cpmk1Id,
        'kode' => 'SUB-CPMK 01',
        'rumusan' => 'Mahasiswa Memahami Konsep Dasar Perencanaan Strategis Sistem Informasi (C3, A3, P2)',
        'level_taksonomi' => 'C3, A3, P2',
        'bobot' => 4.00,
        'urutan' => 1,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat mendefinisikan perencanaan strategis sistem informasi dengan tepat.',
            'Mahasiswa dapat menjelaskan minimal tiga tujuan utama perencanaan strategis SI.',
            'Mahasiswa dapat menguraikan hubungan antara strategi SI dan strategi bisnis dengan contoh nyata.',
            'Mahasiswa dapat menyebutkan minimal dua kerangka kerja perencanaan strategis SI (misalnya Ward & Peppard, TOGAF).'
        ]
    ],
    [
        'cpmk_id' => $cpmk1Id,
        'kode' => 'SUB-CPMK 02',
        'rumusan' => 'Mahasiswa mampu melakukan analisis lingkungan internal dan eksternal organisasi misalnya SWOT untuk mengidentifikasi kebutuhan sistem informasi. (C3, A3, P4)',
        'level_taksonomi' => 'C3, A3, P4',
        'bobot' => 9.00,
        'urutan' => 2,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat melakukan analisis SWOT dengan mengidentifikasi minimal dua kekuatan, kelemahan, peluang, dan ancaman terkait SI.',
            'Mahasiswa dapat menerapkan analisis PESTEL untuk mengidentifikasi faktor eksternal yang memengaruhi kebutuhan SI.',
            'Mahasiswa dapat memetakan kebutuhan bisnis organisasi berdasarkan hasil analisis lingkungan.',
            'Mahasiswa dapat menyusun laporan analisis lingkungan dengan struktur yang jelas dan logis.'
        ]
    ],
    // Sub-CPMK under CPMK 2
    [
        'cpmk_id' => $cpmk2Id,
        'kode' => 'SUB-CPMK 03',
        'rumusan' => 'Mahasiswa mampu merumuskan visi dan misi sistem informasi yang selaras dengan strategi organisasi. (C3, A3, P4)',
        'level_taksonomi' => 'C3, A3, P4',
        'bobot' => 9.00,
        'urutan' => 3,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat merumuskan visi SI yang jelas, singkat, dan selaras dengan visi organisasi.',
            'Mahasiswa dapat menyusun misi SI yang mencakup tujuan teknologi dan dukungan terhadap bisnis.',
            'Mahasiswa dapat menunjukkan keselarasan antara visi/misi SI dengan strategi organisasi melalui studi kasus.',
            'Mahasiswa dapat mengevaluasi visi dan misi SI organisasi lain dengan kriteria yang relevan'
        ]
    ],
    [
        'cpmk_id' => $cpmk2Id,
        'kode' => 'SUB-CPMK 04',
        'rumusan' => 'Mahasiswa mampu menyusun arsitektur sistem informasi yang mendukung kebutuhan organisasi, termasuk infrastruktur teknologi dan aplikasi. (C3, A3, P4)',
        'level_taksonomi' => 'C3, A3, P4',
        'bobot' => 9.00,
        'urutan' => 4,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat mendesain diagram arsitektur SI yang mencakup infrastruktur dan aplikasi utama.',
            'Mahasiswa dapat menjelaskan komponen utama arsitektur SI berdasarkan model seperti TOGAF.',
            'Mahasiswa dapat mengidentifikasi kebutuhan teknologi yang sesuai dengan portofolio aplikasi organisasi.',
            'Mahasiswa dapat menyusun rencana pengembangan arsitektur SI dengan mempertimbangkan skalabilitas dan fleksibilitas.'
        ]
    ],
    [
        'cpmk_id' => $cpmk2Id,
        'kode' => 'SUB-CPMK 05',
        'rumusan' => 'Mahasiswa mampu menyusun rencana strategis sistem informasi yang mencakup roadmap implementasi dan prioritas proyek. (C4, A3, P4)',
        'level_taksonomi' => 'C4, A3, P4',
        'bobot' => 9.00,
        'urutan' => 5,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat menyusun roadmap implementasi SI dengan timeline yang realistis.',
            'Mahasiswa dapat menentukan prioritas proyek SI berdasarkan kriteria seperti dampak bisnis dan urgensi.',
            'Mahasiswa dapat mengidentifikasi sumber daya yang diperlukan untuk implementasi rencana strategis SI.',
            'Mahasiswa dapat menyusun dokumen rencana strategis SI yang terstruktur dan komprehensif.'
        ]
    ],
    [
        'cpmk_id' => $cpmk2Id,
        'kode' => 'SUB-CPMK 06',
        'rumusan' => 'Mahasiswa mampu mengidentifikasi, menganalisis, dan mengelola risiko yang terkait dengan implementasi strategi sistem informasi. (C3, A3, P4)',
        'level_taksonomi' => 'C3, A3, P4',
        'bobot' => 26.00,
        'urutan' => 6,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat mengidentifikasi minimal tiga risiko utama dalam implementasi strategi SI.',
            'Mahasiswa dapat menganalisis risiko menggunakan metode kualitatif atau kuantitatif (misalnya matriks risiko).',
            'Mahasiswa dapat mengusulkan strategi mitigasi risiko yang relevan dan efektif.',
            'Mahasiswa dapat menyusun rencana manajemen risiko untuk proyek SI dengan langkah-langkah yang jelas.'
        ]
    ],
    [
        'cpmk_id' => $cpmk2Id,
        'kode' => 'SUB-CPMK 07',
        'rumusan' => 'Mahasiswa mampu mengukur kinerja sistem informasi melalui indikator kinerja kunci (KPI) dan mengevaluasi keselarasan dengan tujuan strategis organisasi. (C3, A3, P4)',
        'level_taksonomi' => 'C3, A3, P4',
        'bobot' => 35.00,
        'urutan' => 7,
        'pengalaman' => 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
        'asesmen' => 'Pretes dan postes',
        'indikators' => [
            'Mahasiswa dapat merumuskan minimal tiga KPI yang relevan untuk mengukur kinerja SI.',
            'Mahasiswa dapat mengevaluasi keberhasilan implementasi SI berdasarkan KPI yang telah ditetapkan.',
            'Mahasiswa dapat menganalisis keselarasan hasil implementasi SI dengan tujuan strategis organisasi.',
            'Mahasiswa dapat mengusulkan penyesuaian strategi SI berdasarkan hasil evaluasi kinerja.'
        ]
    ]
];

$subCpmkMap = [];
$insSub = $db->prepare("INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, level_taksonomi, pengalaman_belajar, asesmen_penilaian, bobot, urutan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insInd = $db->prepare("INSERT INTO sub_cpmk_indikator (sub_cpmk_id, indikator) VALUES (?, ?)");

foreach ($subCpmkData as $s) {
    $insSub->execute([$s['cpmk_id'], $s['kode'], $s['rumusan'], $s['level_taksonomi'], $s['pengalaman'], $s['asesmen'], $s['bobot'], $s['urutan']]);
    $subId = $db->lastInsertId();
    $subCpmkMap[$s['kode']] = $subId;

    foreach ($s['indikators'] as $ind) {
        $insInd->execute([$subId, $ind]);
    }
}
echo "7 Sub-CPMK dan 28 Indikator berhasil disimpan.\n";

// 7. Simpan 16 Pertemuan Mingguan Persis PDF
$db->prepare("DELETE FROM rps_pertemuan WHERE mata_kuliah_id = ?")->execute([$mkId]);

$pertemuan16 = [
    [
        'sesi' => 1,
        'sub_kode' => 'SUB-CPMK 01',
        'topik' => "Kontrak Perkuliahan Dan Pengenalan Konsep Dasar (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan penjelasan tentang kontrak perkuliahan dan RPS (durasi 1x20 menit)\n• Mahasiswa mempelajari materi yang dibagikan oleh dosen (durasi 1x30 menit)\n• Mahasiswa mengerjakan tugas mandiri (durasi 1x60 menit)\n• Penugasan (1x60 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 2,
        'sub_kode' => 'SUB-CPMK 01',
        'topik' => "Hubungan Strategis SI dengan Strategis Bisnis (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Hubungan Strategis SI dengan Strategis Bisnis (1x50 menit)\n• Mahasiswa melakukan praktikum (durasi 1x60 menit)\n• Penugasan (1x60 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 3,
        'sub_kode' => 'SUB-CPMK 02',
        'topik' => "Teknik Analisis Lingkungan (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Teknik Analisis Lingkungan (1x50 menit)\n• Mahasiswa melakukan praktikum (durasi 1x60 menit)\n• Penugasan (1x60 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 4,
        'sub_kode' => 'SUB-CPMK 02',
        'topik' => "Identifikasi peluang dan ancaman teknologi (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Identifikasi peluang dan ancaman teknologi (1x50 menit)\n• Mahasiswa melakukan praktikum (durasi 1x60 menit)\n• Penugasan (1x60 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 5,
        'sub_kode' => 'SUB-CPMK 03',
        'topik' => "Penyusunan Visi dan Misi (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Penyusunan Visi dan Misi (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 6,
        'sub_kode' => 'SUB-CPMK 03',
        'topik' => "Penyelarasan dengan tujuan bisnis (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Penyelarasan dengan tujuan bisnis (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 7,
        'sub_kode' => 'SUB-CPMK 03',
        'topik' => "Pengenalan arsitektur SI (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Pengenalan arsitektur SI (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 8,
        'sub_kode' => 'SUB-CPMK 04',
        'topik' => "UTS (Ujian Tengah Semester)",
        'durasi' => "Computer Based Test CBT (60 menit)\nPelaksanaan evaluasi ujian tengah semester",
        'mode' => 'TM',
        'media' => "CBT Portal, Materi Pertemuan 1-7"
    ],
    [
        'sesi' => 9,
        'sub_kode' => 'SUB-CPMK 04',
        'topik' => "Pengenalan Model seperti TOGAF dan perencanaan portofolio aplikasi (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Pengenalan Model seperti TOGAF dan perencanaan portofolio aplikasi (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 10,
        'sub_kode' => 'SUB-CPMK 05',
        'topik' => "Penyusunan roadmap SI, penentuan prioritas proyek (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Penyusunan roadmap SI, penentuan prioritas proyek (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 11,
        'sub_kode' => 'SUB-CPMK 05',
        'topik' => "Pengelolaan Sumber Daya (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Pengelolaan Sumber Daya (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 12,
        'sub_kode' => 'SUB-CPMK 06',
        'topik' => "Manajemen Risiko (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Manajemen Risiko (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 13,
        'sub_kode' => 'SUB-CPMK 06',
        'topik' => "Evaluasi Kelayakan Proyek SI (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Evaluasi Kelayakan Proyek (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 14,
        'sub_kode' => 'SUB-CPMK 07',
        'topik' => "Pengenalan KPI untuk SI dan Proyek Akhir (Bagian 1) (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan menjelaskan ke mahasiswa Proyek Akhir (2x60 menit)\n• Mahasiswa melakukan praktikum (durasi 2x240 menit)\n• Penugasan (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 15,
        'sub_kode' => 'SUB-CPMK 07',
        'topik' => "Pengenalan KPI untuk SI dan Proyek Akhir (Bagian 2 - Evaluasi) (RK)",
        'durasi' => "Flipped learning:\n• Dosen memberikan materi dan review asistensi proyek akhir (2x60 menit)\n• Mahasiswa melakukan praktikum simulasi KPI (durasi 2x240 menit)\n• Finalisasi Laporan Proyek (2x40 menit)",
        'mode' => 'ASM dan TM',
        'media' => "WAG, Zoom Meetings, Youtube\n[1], [2], [3]"
    ],
    [
        'sesi' => 16,
        'sub_kode' => 'SUB-CPMK 07',
        'topik' => "UAS (Ujian Akhir Semester)",
        'durasi' => "Computer Based Test CBT (60 menit)\nPelaksanaan evaluasi akhir semester Sub-CPMK 01 - 07",
        'mode' => 'TM',
        'media' => "CBT Portal, Materi Pertemuan 8-15"
    ]
];

$insPertemuan = $db->prepare("INSERT INTO rps_pertemuan (
    mata_kuliah_id, pertemuan_ke, sub_cpmk_id, bahan_kajian, bentuk_metode, mode_pembelajaran,
    pengalaman_belajar, alokasi_waktu, durasi_detail, pustaka, indikator_penilaian, bobot_penilaian
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($pertemuan16 as $p) {
    $subId = $subCpmkMap[$p['sub_kode']] ?? null;
    $insPertemuan->execute([
        $mkId,
        $p['sesi'],
        $subId,
        $p['topik'],
        'Kuliah, Diskusi & Flipped Learning',
        $p['mode'],
        'Mahasiswa mempelajari modul, diskusi interaktif, dan praktikum studi kasus',
        '2 x 50 menit',
        $p['durasi'],
        $p['media'],
        'Ketepatan pemahaman konsep & implementasi analisis',
        ($p['sesi'] == 8 || $p['sesi'] == 16) ? 30.00 : 5.00
    ]);
}

echo "16 Sesi Pertemuan RPS persis PDF berhasil di-generate!\n";
echo "✓ SEEDING RPS PSSI REV 3.0 SELESAI DENGAN SUKSES!\n";
