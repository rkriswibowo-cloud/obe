<?php
// app/Models/RpsModel.php

require_once __DIR__ . '/../../config/database.php';

class RpsModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getJenisPenilaianMaster() {
        return [
            'AKT' => 'Keaktifan', 'KUI' => 'Kuis', 'TGI' => 'Tugas Individu',
            'TGK' => 'Tugas Kelompok', 'PRS' => 'Presentasi', 'STK' => 'Studi Kasus',
            'PRJ' => 'Proyek', 'PRK' => 'Praktikum', 'RSP' => 'Responsi',
            'LPR' => 'Laporan Praktikum', 'UK' => 'Unjuk Kerja', 'POR' => 'Portofolio',
            'OBS' => 'Observasi', 'TL' => 'Tes Lisan', 'TT' => 'Tes Tertulis',
            'UTS' => 'UTS', 'UAS' => 'UAS', 'UPR' => 'Ujian Praktik', 'PRO' => 'Produk/Karya'
        ];
    }

    public function getByMkId($mkId) {
        $stmt = $this->db->prepare("SELECT * FROM rps WHERE mata_kuliah_id = :mk_id LIMIT 1");
        $stmt->execute(['mk_id' => $mkId]);
        $rps = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rps) {
            // Ambil data MK beserta kurikulum dan program studi
            $stmtMk = $this->db->prepare("
                SELECT m.*, k.prodi_id, p.nama as prodi_nama, p.jenjang as prodi_jenjang, p.fakultas as prodi_fakultas
                FROM mata_kuliah m
                JOIN kurikulum k ON m.kurikulum_id = k.id
                JOIN program_studi p ON k.prodi_id = p.id
                WHERE m.id = :mk_id
            ");
            $stmtMk->execute(['mk_id' => $mkId]);
            $mkRow = $stmtMk->fetch(PDO::FETCH_ASSOC);

            // Cek dosen pengampu utama dan kelas dari tabel kelas
            $stmtKelas = $this->db->prepare("
                SELECT k.nama_kelas, u.nama_lengkap as dosen_nama 
                FROM kelas k
                LEFT JOIN users u ON k.dosen_id = u.id
                WHERE k.mata_kuliah_id = :mk_id
                LIMIT 1
            ");
            $stmtKelas->execute(['mk_id' => $mkId]);
            $kelasRow = $stmtKelas->fetch(PDO::FETCH_ASSOC);

            $defaultDosen = !empty($kelasRow['dosen_nama']) ? "1. " . $kelasRow['dosen_nama'] . " (PJMK)" : "1. Dosen Pengampu (PJMK)";
            $defaultKelas = !empty($kelasRow['nama_kelas']) ? $kelasRow['nama_kelas'] : "A";
            $defaultSemester = !empty($mkRow['semester']) ? $mkRow['semester'] : "1";
            $defaultSifat = !empty($mkRow['sifat']) ? $mkRow['sifat'] : "Wajib";
            $defaultKK = !empty($mkRow['kelompok_keilmuan']) ? $mkRow['kelompok_keilmuan'] : ($mkRow['prodi_nama'] ?? 'Program Studi');
            $defaultProdi = !empty($mkRow['prodi_nama']) 
                ? (trim(($mkRow['prodi_jenjang'] ? $mkRow['prodi_jenjang'] . ' ' : '') . $mkRow['prodi_nama'])) 
                : 'S1 Program Studi';
            $defaultFakultas = !empty($mkRow['prodi_fakultas']) ? $mkRow['prodi_fakultas'] : 'Fakultas Sains dan Teknologi';

            return [
                'mata_kuliah_id' => $mkId,
                'versi' => '1/0',
                'fakultas' => $defaultFakultas,
                'program_studi' => $defaultProdi,
                'kelas' => $defaultKelas,
                'sifat' => $defaultSifat,
                'semester_ke' => $defaultSemester,
                'ketersediaan' => 'Program Studi',
                'bentuk_pembelajaran' => 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara/ Praktikum/ Praktik Studio/Praktek Lapangan/ Penelitian/ Pengabdian Kepada Masyarakat',
                'media_pembelajaran' => '*Audio/Visual/Audiovisual',
                'kelompok_keilmuan' => $defaultKK,
                'prasyarat' => '-',
                'dosen_pengampu' => $defaultDosen,
                'tahun_akademik' => 'Ganjil 2025/2026',
                'referensi' => "1. Referensi Utama Mata Kuliah.\n2. Buku Referensi Pendukung.",
                'sistem_penilaian_desc' => 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.',
                'bobot_keaktifan' => 5.00,
                'bobot_tugas' => 15.00,
                'bobot_kuis' => 20.00,
                'bobot_uts' => 30.00,
                'bobot_uas' => 30.00,
                'jenis_penilaian_json' => null,
                'rubrik_mk_json' => null,
                'sistem_evaluasi_desc' => 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.',
                'rencana_evaluasi_json' => null,
                'rubrik_penilaian_json' => null,
                'tim_teaching_json' => null
            ];
        }

        return $rps;
    }

    public function saveOrUpdate($mkId, $data) {
        $sql = "INSERT INTO rps (
            mata_kuliah_id, versi, fakultas, program_studi, kelas, sifat, semester_ke,
            ketersediaan, bentuk_pembelajaran, media_pembelajaran, kelompok_keilmuan,
            prasyarat, dosen_pengampu, tim_teaching_json, tahun_akademik, referensi,
            sistem_penilaian_desc, bobot_keaktifan, bobot_tugas, bobot_kuis, bobot_uts, bobot_uas,
            sistem_evaluasi_desc, rencana_evaluasi_json, rubrik_penilaian_json, jenis_penilaian_json, rubrik_mk_json
        ) VALUES (
            :mk_id, :versi, :fakultas, :program_studi, :kelas, :sifat, :semester_ke,
            :ketersediaan, :bentuk_pembelajaran, :media_pembelajaran, :kelompok_keilmuan,
            :prasyarat, :dosen_pengampu, :tim_teaching_json, :tahun_akademik, :referensi,
            :sistem_penilaian_desc, :bobot_keaktifan, :bobot_tugas, :bobot_kuis, :bobot_uts, :bobot_uas,
            :sistem_evaluasi_desc, :rencana_evaluasi_json, :rubrik_penilaian_json, :jenis_penilaian_json, :rubrik_mk_json
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
            tim_teaching_json = VALUES(tim_teaching_json),
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
            rubrik_penilaian_json = VALUES(rubrik_penilaian_json),
            jenis_penilaian_json = VALUES(jenis_penilaian_json),
            rubrik_mk_json = VALUES(rubrik_mk_json)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'mk_id' => $mkId,
            'versi' => $data['versi'] ?? '3/0',
            'fakultas' => $data['fakultas'] ?? 'Fakultas Sains dan Teknologi',
            'program_studi' => $data['program_studi'] ?? 'S1 Sistem Informasi',
            'kelas' => $data['kelas'] ?? 'I-23',
            'sifat' => $data['sifat'] ?? 'Wajib',
            'semester_ke' => $data['semester_ke'] ?? '6',
            'ketersediaan' => $data['ketersediaan'] ?? 'Program Studi',
            'bentuk_pembelajaran' => $data['bentuk_pembelajaran'] ?? 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara',
            'media_pembelajaran' => $data['media_pembelajaran'] ?? '*Audio/Visual/Audiovisual',
            'kelompok_keilmuan' => $data['kelompok_keilmuan'] ?? 'Sistem Informasi',
            'prasyarat' => $data['prasyarat'] ?? '-',
            'dosen_pengampu' => $data['dosen_pengampu'] ?? '1. Rony Kriswibowo, M.Kom (PJMK)',
            'tim_teaching_json' => !empty($data['tim_teaching_json']) ? $data['tim_teaching_json'] : null,
            'tahun_akademik' => $data['tahun_akademik'] ?? 'Genap 2025/2026',
            'referensi' => $data['referensi'] ?? '',
            'sistem_penilaian_desc' => $data['sistem_penilaian_desc'] ?? 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.',
            'bobot_keaktifan' => floatval($data['bobot_keaktifan'] ?? 5.00),
            'bobot_tugas' => floatval($data['bobot_tugas'] ?? 15.00),
            'bobot_kuis' => floatval($data['bobot_kuis'] ?? 20.00),
            'bobot_uts' => floatval($data['bobot_uts'] ?? 30.00),
            'bobot_uas' => floatval($data['bobot_uas'] ?? 30.00),
            'sistem_evaluasi_desc' => $data['sistem_evaluasi_desc'] ?? 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK.',
            'rencana_evaluasi_json' => !empty($data['rencana_evaluasi_json']) ? $data['rencana_evaluasi_json'] : null,
            'rubrik_penilaian_json' => !empty($data['rubrik_penilaian_json']) ? $data['rubrik_penilaian_json'] : null,
            'jenis_penilaian_json' => !empty($data['jenis_penilaian_json']) ? $data['jenis_penilaian_json'] : null,
            'rubrik_mk_json' => !empty($data['rubrik_mk_json']) ? $data['rubrik_mk_json'] : null
        ]);
    }

    public function saveSemesterArchive(int $assignmentId, int $mkId, int $taId): void {
        $rps = $this->getByMkId($mkId);
        $pertemuan = $this->db->prepare("SELECT rp.*,
            GROUP_CONCAT(sc.id ORDER BY sc.urutan,sc.id) sub_cpmk_ids,
            GROUP_CONCAT(sc.kode ORDER BY sc.urutan,sc.id SEPARATOR ', ') sub_cpmk_kode,
            GROUP_CONCAT(sc.rumusan ORDER BY sc.urutan,sc.id SEPARATOR ' | ') sub_cpmk_rumusan
            FROM rps_pertemuan rp
            LEFT JOIN rps_pertemuan_sub_cpmk rpsc ON rpsc.rps_pertemuan_id=rp.id
            LEFT JOIN sub_cpmk sc ON sc.id=rpsc.sub_cpmk_id
            WHERE rp.mata_kuliah_id=? GROUP BY rp.id ORDER BY rp.pertemuan_ke");
        $pertemuan->execute([$mkId]);
        $stmt=$this->db->prepare("INSERT INTO rps_semester_arsip
            (pengampu_id,mata_kuliah_id,tahun_akademik_id,rps_json,pertemuan_json)
            VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE rps_json=VALUES(rps_json),
            pertemuan_json=VALUES(pertemuan_json),updated_at=CURRENT_TIMESTAMP");
        $stmt->execute([$assignmentId,$mkId,$taId,
            json_encode($rps,JSON_UNESCAPED_UNICODE),
            json_encode($pertemuan->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE)]);
    }

    public function getSemesterArchive(int $assignmentId): ?array {
        $stmt=$this->db->prepare('SELECT * FROM rps_semester_arsip WHERE pengampu_id=? LIMIT 1');
        $stmt->execute([$assignmentId]);$row=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row)return null;
        $row['rps']=json_decode($row['rps_json']??'[]',true)?:[];
        $row['pertemuan']=json_decode($row['pertemuan_json']??'[]',true)?:[];
        return $row;
    }

    /**
     * Mengambil paket data lengkap untuk kompilasi RPS resmi
     */
    public function getFullRpsData($mkId) {
        // 1. Data MK beserta Kurikulum dan Prodi
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   k.prodi_id, 
                   k.nama as kurikulum_nama, 
                   k.kode as kurikulum_kode,
                   p.nama as prodi_nama, 
                   p.jenjang as prodi_jenjang, 
                   p.fakultas as prodi_fakultas
            FROM mata_kuliah m
            JOIN kurikulum k ON m.kurikulum_id = k.id
            JOIN program_studi p ON k.prodi_id = p.id
            WHERE m.id = ?
        ");
        $stmt->execute([$mkId]);
        $mk = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mk) return null;

        // 2. Data RPS Metadata
        $rps = $this->getByMkId($mkId);

        // 3. Data CPL yang terhubung dengan MK ini (via mk_cpl)
        $stmtCpl = $this->db->prepare("
            SELECT c.*, mc.bobot_kontribusi
            FROM cpl c
            JOIN mk_cpl mc ON c.id = mc.cpl_id
            WHERE mc.mk_id = ?
            ORDER BY c.kode ASC
        ");
        $stmtCpl->execute([$mkId]);
        $cpls = $stmtCpl->fetchAll(PDO::FETCH_ASSOC);

        // Seluruh CPL Kurikulum Mata Kuliah ini
        $stmtKurCpl = $this->db->prepare("
            SELECT c.* 
            FROM cpl c 
            WHERE c.kurikulum_id = ? 
            ORDER BY c.kode ASC
        ");
        $stmtKurCpl->execute([$mk['kurikulum_id']]);
        $kurikulumCpls = $stmtKurCpl->fetchAll(PDO::FETCH_ASSOC);

        // 4. Data CPMK & Sub-CPMK dengan Indikator dan keterhubungan CPL
        $stmtCpmk = $this->db->prepare("SELECT * FROM cpmk WHERE mk_id = ? ORDER BY id ASC");
        $stmtCpmk->execute([$mkId]);
        $cpmks = $stmtCpmk->fetchAll(PDO::FETCH_ASSOC);

        $allSubCpmks = [];
        $totalSubBobot = 0;

        foreach ($cpmks as &$cpmk) {
            // Ambil CPL yang terkait dengan CPMK ini
            $stmtCpmkCpl = $this->db->prepare("
                SELECT c.id, c.kode, c.rumusan 
                FROM cpl c
                JOIN cpmk_cpl cc ON c.id = cc.cpl_id
                WHERE cc.cpmk_id = ?
                ORDER BY c.kode ASC
            ");
            $stmtCpmkCpl->execute([$cpmk['id']]);
            $cpmk['cpls'] = $stmtCpmkCpl->fetchAll(PDO::FETCH_ASSOC);
            $cpmk['cpl_ids'] = array_column($cpmk['cpls'], 'id');
            $cpmk['cpl_kodes'] = array_column($cpmk['cpls'], 'kode');

            $stmtSub = $this->db->prepare("
                SELECT * FROM sub_cpmk 
                WHERE cpmk_id = ? 
                ORDER BY urutan ASC, id ASC
            ");
            $stmtSub->execute([$cpmk['id']]);
            $cpmk['sub_cpmks'] = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cpmk['sub_cpmks'] as &$sub) {
                // Ambil indikator
                $stmtInd = $this->db->prepare("SELECT * FROM sub_cpmk_indikator WHERE sub_cpmk_id = ? ORDER BY id ASC");
                $stmtInd->execute([$sub['id']]);
                $sub['indikators'] = $stmtInd->fetchAll(PDO::FETCH_ASSOC);

                // Warisi CPL dari CPMK induk
                $sub['cpmk_kode'] = $cpmk['kode'];
                $sub['cpl_ids'] = $cpmk['cpl_ids'];
                $sub['cpl_kodes'] = $cpmk['cpl_kodes'];

                $totalSubBobot += floatval($sub['bobot']);
                $allSubCpmks[] = $sub;
            }
        }

        // 5. Rincian Pertemuan 1 s.d. 16
        $stmtPertemuan = $this->db->prepare("
            SELECT rp.*, GROUP_CONCAT(sc.id ORDER BY sc.urutan, sc.id) AS sub_cpmk_ids,
                   GROUP_CONCAT(sc.kode ORDER BY sc.urutan, sc.id SEPARATOR ', ') AS sub_cpmk_kode,
                   GROUP_CONCAT(sc.rumusan ORDER BY sc.urutan, sc.id SEPARATOR ' | ') AS sub_cpmk_rumusan
            FROM rps_pertemuan rp
            LEFT JOIN rps_pertemuan_sub_cpmk rpsc ON rpsc.rps_pertemuan_id = rp.id
            LEFT JOIN sub_cpmk sc ON rpsc.sub_cpmk_id = sc.id
            WHERE rp.mata_kuliah_id = ?
            GROUP BY rp.id
            ORDER BY rp.pertemuan_ke ASC
        ");
        $stmtPertemuan->execute([$mkId]);
        $pertemuanList = $stmtPertemuan->fetchAll(PDO::FETCH_ASSOC);

        // 6. Parsing Referensi ke Array
        $referensiList = [];
        if (!empty($rps['referensi'])) {
            $lines = explode("\n", trim($rps['referensi']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $referensiList[] = $line;
                }
            }
        }

        // 7. Rencana Evaluasi Matrix
        $rencanaEvaluasi = [];
        if (!empty($rps['rencana_evaluasi_json'])) {
            $rencanaEvaluasi = json_decode($rps['rencana_evaluasi_json'], true) ?: [];
        }

        // 8. Komponen jenis penilaian dan rubrik yang dihubungkan
        $jenisPenilaian = [];
        if (!empty($rps['jenis_penilaian_json'])) {
            $parsedJenis = json_decode($rps['jenis_penilaian_json'], true);
            $jenisPenilaian = is_array($parsedJenis) ? $parsedJenis : [];
        }
        if (empty($jenisPenilaian)) {
            $jenisPenilaian = [
                ['kode' => 'AKT', 'bobot' => (float)($rps['bobot_keaktifan'] ?? 5), 'rubrik_id' => null],
                ['kode' => 'TGI', 'bobot' => (float)($rps['bobot_tugas'] ?? 15), 'rubrik_id' => null],
                ['kode' => 'KUI', 'bobot' => (float)($rps['bobot_kuis'] ?? 20), 'rubrik_id' => null],
                ['kode' => 'UTS', 'bobot' => (float)($rps['bobot_uts'] ?? 30), 'rubrik_id' => null],
                ['kode' => 'UAS', 'bobot' => (float)($rps['bobot_uas'] ?? 30), 'rubrik_id' => null]
            ];
        }
        $rubrikMaster = $this->db->query("
            SELECT r.*,
                   (SELECT COUNT(*) FROM rubrik_kriteria rk WHERE rk.rubrik_id=r.id) AS total_kriteria,
                   (SELECT COUNT(*) FROM rubrik_level rl WHERE rl.rubrik_id=r.id) AS total_level,
                   (SELECT COALESCE(SUM(rk.bobot),0) FROM rubrik_kriteria rk WHERE rk.rubrik_id=r.id) AS total_bobot
            FROM rubrik r
            WHERE r.status='ACTIVE'
            ORDER BY FIELD(r.kode_jenis,'RB-ANA','RB-HOL','RB-SKL','RB-CHK','RB-SKR','RB-KUN','RB-CMP','RB-PRF','RB-PEER','RB-SELF'), r.id
        ")->fetchAll(PDO::FETCH_ASSOC);
        $rubrikMk = [];
        if (!empty($rps['rubrik_mk_json'])) {
            $parsedRubrikMk = json_decode($rps['rubrik_mk_json'], true);
            $rubrikMk = is_array($parsedRubrikMk) ? $parsedRubrikMk : [];
        }

        // 9. Rubrik Penilaian
        $defaultRubrik = self::getDefaultRubrikTemplates();
        $rubrik = [];
        if (!empty($rps['rubrik_penilaian_json'])) {
            $parsed = json_decode($rps['rubrik_penilaian_json'], true);
            if (is_array($parsed)) {
                // Konversi jika masih menggunakan format lama
                if (isset($parsed['kriteria']) && !isset($parsed['holistik'])) {
                    $rubrik = $defaultRubrik;
                    $rubrik['holistik']['kriteria'] = $parsed['kriteria'];
                } else {
                    $rubrik = array_replace_recursive($defaultRubrik, $parsed);
                }
            }
        }
        if (empty($rubrik)) {
            $rubrik = $defaultRubrik;
        }

        // 9. Data Tim Teaching Terdaftar
        $timTeaching = [];
        if (!empty($rps['tim_teaching_json'])) {
            $timTeaching = json_decode($rps['tim_teaching_json'], true) ?: [];
        }

        // 10. Deteksi Tim Teaching dari Kelas Aktif
        $stmtKelasTT = $this->db->prepare("
            SELECT DISTINCT u.id, u.nama_lengkap, u.username as nidn
            FROM kelas k
            JOIN kelas_tim_teaching ktt ON k.id = ktt.kelas_id
            JOIN users u ON ktt.dosen_id = u.id
            WHERE k.mata_kuliah_id = ?
        ");
        $stmtKelasTT->execute([$mkId]);
        $detectedKelasTimTeaching = $stmtKelasTT->fetchAll(PDO::FETCH_ASSOC);

        // 11. Seluruh Dosen untuk Dropdown Pilihan (Mendukung Dosen & Kaprodi Lintas Prodi)
        $stmtDosen = $this->db->query("
            SELECT u.id, u.nama_lengkap, u.username as nidn, p.nama as prodi_nama, p.kode as prodi_kode
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            LEFT JOIN program_studi p ON p.id = u.prodi_id
            WHERE r.name IN ('Dosen', 'Kaprodi')
            ORDER BY p.nama ASC, u.nama_lengkap ASC
        ");
        $allDosen = $stmtDosen->fetchAll(PDO::FETCH_ASSOC);

        return [
            'mk' => $mk,
            'rps' => $rps,
            'cpls' => $cpls,
            'kurikulumCpls' => $kurikulumCpls,
            'cpmks' => $cpmks,
            'allSubCpmks' => $allSubCpmks,
            'totalSubBobot' => $totalSubBobot,
            'pertemuanList' => $pertemuanList,
            'referensiList' => $referensiList,
            'rencanaEvaluasi' => $rencanaEvaluasi,
            'jenisPenilaian' => $jenisPenilaian,
            'jenisPenilaianMaster' => self::getJenisPenilaianMaster(),
            'rubrikMaster' => $rubrikMaster,
            'rubrikMk' => $rubrikMk,
            'rubrik' => $rubrik,
            'timTeaching' => $timTeaching,
            'detectedKelasTimTeaching' => $detectedKelasTimTeaching,
            'allDosen' => $allDosen
        ];
    }

    public static function getDefaultRubrikTemplates() {
        return [
            'holistik' => [
                'aktif' => true,
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
                'aktif' => true,
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
                'aktif' => true,
                'judul' => 'Rubrik Penilaian Skala Persepsi (Afektif & Kerjasama Tim)',
                'aspek' => [
                    [
                        'nama' => 'Kerjasama Tim & Kontribusi Tugas',
                        'bobot' => 25,
                        'skala' => [
                            1 => 'Tidak berkontribusi dan lepas tangan dari tanggung jawab tim.',
                            2 => 'Kontribusi pasif, sering terlambat menyelesaikan bagian tugasnya.',
                            3 => 'Menjalankan tugas jika didorong atau diingatkan oleh ketua/rekan.',
                            4 => 'Menjalankan bagian tugasnya dengan baik dan kooperatif.',
                            5 => 'Sangat proaktif, memimpin inisiatif, membantu rekan, komitmen penuh.'
                        ]
                    ],
                    [
                        'nama' => 'Keaktifan & Argumentasi Ilmiah',
                        'bobot' => 25,
                        'skala' => [
                            1 => 'Pasif sama sekali dalam diskusi dan sesi tanya jawab.',
                            2 => 'Jarang berpendapat kecuali saat ditunjuk langsung oleh dosen.',
                            3 => 'Cukup aktif berbicara, namun argumen bersifat opini tanpa rujukan.',
                            4 => 'Aktif menyampaikan pendapat berbasis materi kuliah dengan runtut.',
                            5 => 'Selalu aktif, kritis, argumentasi berlandaskan referensi ilmiah kuat.'
                        ]
                    ],
                    [
                        'nama' => 'Etika Akademik & Menghargai Pendapat',
                        'bobot' => 25,
                        'skala' => [
                            1 => 'Melanggar etika/plagiarisme atau bersikap tidak sopan.',
                            2 => 'Kurang menghargai pendapat rekan atau acuh terhadap etika.',
                            3 => 'Sopan namun terkadang defensif saat menerima kritik dari orang lain.',
                            4 => 'Santun, menghormati perbedaan pendapat, menjunjung kejujuran akademik.',
                            5 => 'Integritas tinggi, sangat santun, terbuka pada kritik, apresiatif.'
                        ]
                    ],
                    [
                        'nama' => 'Kedisiplinan & Manajemen Waktu',
                        'bobot' => 25,
                        'skala' => [
                            1 => 'Tingkat kehadiran rendah, sering melanggar tata tertib.',
                            2 => 'Sering terlambat hadir dan mengabaikan jadwal yang disepakati.',
                            3 => 'Hadir tepat waktu namun terkadang terlambat pada pengumpulan progres.',
                            4 => 'Disiplin hadir tepat waktu dan menaati batas waktu pengumpulan tugas.',
                            5 => 'Selalu hadir tepat waktu, siap materi, mengumpulkan selalu sebelum deadline.'
                        ]
                    ]
                ]
            ],
            'portofolio' => [
                'aktif' => true,
                'judul' => 'Rubrik Penilaian Portofolio Mahasiswa (Kumpulan Artefak Proyek SI)',
                'artefak' => [
                    ['no' => 1, 'nama' => 'Laporan Analisis Lingkungan Bisnis & SI', 'sub_cpmk' => 'Sub-CPMK 01 & 02', 'bobot' => 20, 'minggu' => 'Minggu Ke-4', 'kriteria' => 'Ketajaman analisis SWOT/Value Chain, keselarasan gap kebutuhan SI, validitas data observasi organisasi.', 'bukti' => 'Dokumen Laporan Bab 1-2 + Lembar Kerja Analisis Kasus'],
                    ['no' => 2, 'nama' => 'Blueprint Arsitektur Sistem Informasi', 'sub_cpmk' => 'Sub-CPMK 03 & 04', 'bobot' => 30, 'minggu' => 'Minggu Ke-8 (UTS)', 'kriteria' => 'Kelengkapan domain TOGAF (Arsitektur Bisnis, Data, Aplikasi, Teknologi), ketepatan diagram modeling dan katalog aplikasi.', 'bukti' => 'Dokumen Blueprint SI + Diagram Arsitektur (UML/ArchiMate)'],
                    ['no' => 3, 'nama' => 'Roadmap Implementasi & Risk Register', 'sub_cpmk' => 'Sub-CPMK 05 & 06', 'bobot' => 25, 'minggu' => 'Minggu Ke-12', 'kriteria' => 'Realisme jadwal implementasi multi-tahun, prioritas proyek logis, pemetaan matriks risiko dan rencana kontingensi.', 'bukti' => 'Gantt Chart Roadmap + Tabel Matriks Manajemen Risiko'],
                    ['no' => 4, 'nama' => 'Laporan Akhir Terpadu & Refleksi Diri', 'sub_cpmk' => 'Sub-CPMK 07', 'bobot' => 15, 'minggu' => 'Minggu Ke-15', 'kriteria' => 'Keterpaduan integrasi seluruh dokumen dari hulu ke hilir, kualitas executive summary, dan kedalaman refleksi pembelajaran mahasiswa.', 'bukti' => 'Bundel Laporan Final Hardcover/PDF + Lembar Refleksi Diri'],
                    ['no' => 5, 'nama' => 'Kelengkapan Repositori & Orisinalitas', 'sub_cpmk' => 'Seluruh Sub-CPMK', 'bobot' => 10, 'minggu' => 'Minggu Ke-16 (UAS)', 'kriteria' => 'Kerapian struktur folder e-portfolio pada LMS/Google Drive, kelengkapan berkas pendukung, dan uji kesamaan (Turnitin < 20%).', 'bukti' => 'Tautan Repositori Digital + Bukti Skor Plagiarisme']
                ]
            ]
        ];
    }
}
