<?php
// app/Models/KhsModel.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/LaporanModel.php';

class KhsModel {
    private $db;
    private $laporanModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->laporanModel = new LaporanModel();
    }

    public function getAllMahasiswa() {
        $sql = "
            SELECT u.id, u.username, u.nama_lengkap,
                   CASE 
                       WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                       WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                       ELSE NULL
                   END as angkatan
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Mahasiswa'
            ORDER BY u.username ASC
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMahasiswaById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTahunAkademik() {
        return $this->db->query("SELECT * FROM tahun_akademik ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rincian read-only nilai Sub-CPMK beserta instrumen dan rubrik dari snapshot RPS.
     */
    public function getRincianSubCpmkMahasiswa($mahasiswa_id, $filter_ta_id = null, $kurikulum_id = null) {
        $sql = "
            SELECT an.nilai, an.updated_at, k.id AS kelas_id, k.nama_kelas,
                   ta.id AS ta_id, ta.nama AS ta_nama,
                   mk.id AS mk_id, mk.kode AS mk_kode, mk.nama AS mk_nama, mk.semester AS mk_semester,
                   cp.kode AS cpmk_kode, cp.rumusan AS cpmk_rumusan,
                   sc.id AS sub_cpmk_id, sc.kode AS sub_cpmk_kode,
                   sc.rumusan AS sub_cpmk_rumusan, sc.bobot AS sub_cpmk_bobot,
                   r.jenis_penilaian_json, r.rencana_evaluasi_json, r.rubrik_mk_json
            FROM asesmen_nilai an
            JOIN kelas k ON k.id = an.kelas_id
            JOIN tahun_akademik ta ON ta.id = k.tahun_akademik_id
            JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
            JOIN sub_cpmk sc ON sc.id = an.sub_cpmk_id
            JOIN cpmk cp ON cp.id = sc.cpmk_id AND cp.mk_id = mk.id
            LEFT JOIN rps r ON r.mata_kuliah_id = mk.id
            WHERE an.mahasiswa_id = :mahasiswa_id
        ";
        $params = ['mahasiswa_id' => (int)$mahasiswa_id];
        if ($filter_ta_id) {
            $sql .= " AND ta.id = :ta_id";
            $params['ta_id'] = (int)$filter_ta_id;
        }
        if ($kurikulum_id) {
            $sql .= " AND mk.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$kurikulum_id;
        }
        $sql .= " ORDER BY ta.id, mk.semester, mk.kode, cp.kode, sc.urutan, sc.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rubrikMaster = [];
        foreach ($this->db->query("SELECT id, nama, jenis FROM rubrik")->fetchAll(PDO::FETCH_ASSOC) as $rubrik) {
            $rubrikMaster[(string)$rubrik['id']] = $rubrik;
        }
        $jenisLabels = [
            'AKT' => 'Keaktifan', 'TGI' => 'Tugas Individu', 'KUI' => 'Kuis',
            'UTS' => 'UTS', 'UAS' => 'UAS', 'PRK' => 'Praktikum',
            'PRO' => 'Proyek', 'POR' => 'Portofolio', 'PRE' => 'Presentasi',
        ];

        $grouped = [];
        $configCache = [];
        foreach ($rows as $row) {
            $mkId = (int)$row['mk_id'];
            if (!isset($configCache[$mkId])) {
                $jenisConfig = json_decode($row['jenis_penilaian_json'] ?: '[]', true);
                $rencana = json_decode($row['rencana_evaluasi_json'] ?: '[]', true);
                $rubrikMk = json_decode($row['rubrik_mk_json'] ?: '[]', true);
                $jenisMap = [];
                foreach (is_array($jenisConfig) ? $jenisConfig : [] as $jenis) {
                    if (!empty($jenis['kode'])) $jenisMap[strtoupper($jenis['kode'])] = $jenis;
                }
                $rencanaMap = [];
                foreach (is_array($rencana) ? $rencana : [] as $kode => $item) {
                    $rencanaMap[$this->normalizeSubCpmkCode($kode)] = is_array($item) ? $item : [];
                }
                $configCache[$mkId] = [
                    'jenis' => $jenisMap,
                    'rencana' => $rencanaMap,
                    'rubrik' => is_array($rubrikMk) ? $rubrikMk : [],
                    'has_rps' => $row['rencana_evaluasi_json'] !== null || $row['jenis_penilaian_json'] !== null,
                ];
            }

            $config = $configCache[$mkId];
            $rencanaItem = $config['rencana'][$this->normalizeSubCpmkCode($row['sub_cpmk_kode'])] ?? [];
            $instrumentCodes = [];
            $questions = [];
            foreach (($rencanaItem['soal'] ?? []) as $soal) {
                $code = strtoupper(trim($soal['jenis'] ?? ''));
                if ($code !== '') $instrumentCodes[$code] = true;
                if (!empty($soal['pertanyaan'])) $questions[$code][] = $soal['pertanyaan'];
            }
            $legacyFields = ['tugas' => 'TGI', 'kuis' => 'KUI', 'uts' => 'UTS', 'uas' => 'UAS', 'keaktifan' => 'AKT'];
            foreach ($legacyFields as $field => $code) {
                $value = trim((string)($rencanaItem[$field] ?? ''));
                if ($value !== '' && $value !== '-') {
                    $instrumentCodes[$code] = true;
                    $questions[$code][] = $value;
                }
            }

            $instruments = [];
            foreach (array_keys($instrumentCodes) as $code) {
                $jenis = $config['jenis'][$code] ?? [];
                $rubrikId = isset($jenis['rubrik_id']) && $jenis['rubrik_id'] !== '' ? (string)$jenis['rubrik_id'] : null;
                $snapshot = $rubrikId !== null ? ($config['rubrik'][$rubrikId] ?? null) : null;
                $master = $rubrikId !== null ? ($rubrikMaster[$rubrikId] ?? null) : null;
                $instruments[] = [
                    'kode' => $code,
                    'nama' => $jenisLabels[$code] ?? $code,
                    'bobot' => isset($jenis['bobot']) ? (float)$jenis['bobot'] : null,
                    'rubrik' => $snapshot['nama'] ?? $master['nama'] ?? null,
                    'rubrik_jenis' => $snapshot['jenis'] ?? $master['jenis'] ?? null,
                    'pertanyaan' => $questions[$code] ?? [],
                ];
            }

            $taId = (int)$row['ta_id'];
            $kelasId = (int)$row['kelas_id'];
            if (!isset($grouped[$taId])) {
                $grouped[$taId] = ['ta_id' => $taId, 'ta_nama' => $row['ta_nama'], 'mata_kuliah' => []];
            }
            if (!isset($grouped[$taId]['mata_kuliah'][$kelasId])) {
                $grouped[$taId]['mata_kuliah'][$kelasId] = [
                    'kelas_id' => $kelasId, 'nama_kelas' => $row['nama_kelas'],
                    'mk_id' => $mkId, 'mk_kode' => $row['mk_kode'], 'mk_nama' => $row['mk_nama'],
                    'mk_semester' => (int)$row['mk_semester'], 'has_rps' => $config['has_rps'],
                    'sub_cpmk' => [],
                ];
            }
            $grouped[$taId]['mata_kuliah'][$kelasId]['sub_cpmk'][] = [
                'id' => (int)$row['sub_cpmk_id'], 'kode' => $row['sub_cpmk_kode'],
                'rumusan' => $row['sub_cpmk_rumusan'], 'bobot' => (float)$row['sub_cpmk_bobot'],
                'cpmk_kode' => $row['cpmk_kode'], 'cpmk_rumusan' => $row['cpmk_rumusan'],
                'nilai' => (float)$row['nilai'], 'updated_at' => $row['updated_at'],
                'instruments' => $instruments,
            ];
        }

        foreach ($grouped as &$semester) {
            $semester['mata_kuliah'] = array_values($semester['mata_kuliah']);
        }
        unset($semester);
        return array_values($grouped);
    }

    private function normalizeSubCpmkCode($code) {
        $normalized = strtolower(trim((string)$code));
        $normalized = str_replace(['sub-cpmk', 'sub cpmk', 'subcpmk'], '', $normalized);
        $normalized = preg_replace('/[^0-9.]+/', '', $normalized);
        $parts = array_values(array_filter(explode('.', $normalized), 'strlen'));
        return implode('.', array_map(function ($part) { return (string)(int)$part; }, $parts));
    }

    /**
     * Hitung KHS (Kartu Hasil Studi), IPS, dan IPK untuk seorang mahasiswa
     */
    public function getKhsMahasiswa($mahasiswa_id, $filter_ta_id = null, $kurikulum_id = null) {
        $sql = "
            SELECT 
                k.id as kelas_id,
                k.nama_kelas,
                k.tahun_akademik_id,
                ta.nama as tahun_akademik_nama,
                mk.id as mk_id,
                mk.kode as mk_kode,
                mk.nama as mk_nama,
                mk.sks_teori,
                mk.sks_praktek,
                (COALESCE(mk.sks_teori, 0) + COALESCE(mk.sks_praktek, 0)) as total_sks,
                mk.semester as mk_semester
            FROM kelas_mahasiswa km
            JOIN kelas k ON km.kelas_id = k.id
            JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
            JOIN tahun_akademik ta ON k.tahun_akademik_id = ta.id
            WHERE km.mahasiswa_id = :mhs_id
        ";
        $params = ['mhs_id' => $mahasiswa_id];
        if (!empty($kurikulum_id)) {
            $sql .= " AND mk.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$kurikulum_id;
        }
        $sql .= " ORDER BY ta.id ASC, mk.semester ASC, mk.kode ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $enrolledClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kelas dari kurikulum lain tidak boleh memengaruhi KHS maupun IPK.
        $ignoredEnrollmentCount = 0;
        if (!empty($kurikulum_id)) {
            $stmtIgnored = $this->db->prepare("
                SELECT COUNT(*)
                FROM kelas_mahasiswa km
                JOIN kelas k ON km.kelas_id = k.id
                JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
                WHERE km.mahasiswa_id = :mhs_id
                  AND mk.kurikulum_id <> :kurikulum_id
            ");
            $stmtIgnored->execute([
                'mhs_id' => $mahasiswa_id,
                'kurikulum_id' => (int)$kurikulum_id
            ]);
            $ignoredEnrollmentCount = (int)$stmtIgnored->fetchColumn();
        }

        $semuaNilai = [];
        $perSemester = [];

        $totalSksKumulatif = 0;
        $totalMutuKumulatif = 0;
        $totalSksLulusKumulatif = 0;

        foreach ($enrolledClasses as $row) {
            $kelasId = $row['kelas_id'];
            $taId = $row['tahun_akademik_id'];
            $taNama = $row['tahun_akademik_nama'];
            $sks = (int)$row['total_sks'] > 0 ? (int)$row['total_sks'] : 2;

            // Ambil rincian nilai asesmen per sub_cpmk di kelas ini
            $stmtNilai = $this->db->prepare("
                SELECT an.nilai, sc.bobot
                FROM asesmen_nilai an
                JOIN sub_cpmk sc ON an.sub_cpmk_id = sc.id
                WHERE an.kelas_id = :kelas_id AND an.mahasiswa_id = :mhs_id
            ");
            $stmtNilai->execute([
                'kelas_id' => $kelasId,
                'mhs_id' => $mahasiswa_id
            ]);
            $asesmenList = $stmtNilai->fetchAll(PDO::FETCH_ASSOC);

            $nilaiAkhirAngka = 0;
            $hasScore = false;

            if (!empty($asesmenList)) {
                $weightedSum = 0;
                $totalBobot = 0;
                foreach ($asesmenList as $a) {
                    if ($a['nilai'] !== null) {
                        $hasScore = true;
                        $w = floatval($a['bobot']);
                        $v = floatval($a['nilai']);
                        $weightedSum += ($v * $w);
                        $totalBobot += $w;
                    }
                }
                if ($totalBobot > 0 && $hasScore) {
                    $nilaiAkhirAngka = round($weightedSum / $totalBobot, 2);
                }
            }

            // Konversi ke Huruf Mutu & Bobot Nilai
            $grade = $this->konversiNilai($nilaiAkhirAngka, $hasScore);
            $huruf = $grade['huruf'];
            $bobotMutu = $grade['bobot'];
            $predikatMk = $grade['predikat'];
            $isLulus = $grade['is_lulus'];
            $sksXBobot = round($sks * $bobotMutu, 2);

            $item = [
                'kelas_id' => $kelasId,
                'nama_kelas' => $row['nama_kelas'],
                'tahun_akademik_id' => $taId,
                'tahun_akademik_nama' => $taNama,
                'mk_id' => $row['mk_id'],
                'mk_kode' => $row['mk_kode'],
                'mk_nama' => $row['mk_nama'],
                'mk_semester' => (int)$row['mk_semester'],
                'sks' => $sks,
                'nilai_angka' => $hasScore ? $nilaiAkhirAngka : null,
                'nilai_huruf' => $huruf,
                'bobot' => $bobotMutu,
                'predikat' => $predikatMk,
                'sks_x_bobot' => $sksXBobot,
                'is_lulus' => $isLulus,
                'status' => $hasScore ? ($isLulus ? 'LULUS' : 'MENGULANG') : 'BELUM ADA NILAI'
            ];

            $semuaNilai[] = $item;

            // Grouping per Semester / Tahun Akademik
            if (!isset($perSemester[$taId])) {
                $perSemester[$taId] = [
                    'ta_id' => $taId,
                    'ta_nama' => $taNama,
                    'mata_kuliah' => [],
                    'total_sks' => 0,
                    'total_mutu' => 0,
                    'total_sks_lulus' => 0,
                    'ips' => 0
                ];
            }

            $perSemester[$taId]['mata_kuliah'][] = $item;
            $perSemester[$taId]['total_sks'] += $sks;
            $perSemester[$taId]['total_mutu'] += $sksXBobot;
            if ($isLulus) {
                $perSemester[$taId]['total_sks_lulus'] += $sks;
            }

            // Akumulasi kumulatif
            $totalSksKumulatif += $sks;
            $totalMutuKumulatif += $sksXBobot;
            if ($isLulus) {
                $totalSksLulusKumulatif += $sks;
            }
        }

        // Hitung IPS per semester
        foreach ($perSemester as &$sem) {
            $sem['ips'] = $sem['total_sks'] > 0 ? round($sem['total_mutu'] / $sem['total_sks'], 2, PHP_ROUND_HALF_EVEN) : 0;
        }
        unset($sem);

        // Nilai RPL disimpan terpisah dari kelas/asesmen reguler, lalu digabungkan
        // hanya untuk KHS kumulatif dan transkrip mahasiswa RPL.
        try {
            $stmtRpl = $this->db->prepare("SELECT rk.*, mk.kode mk_kode, mk.nama mk_nama, mk.semester mk_semester
                FROM rpl_konversi rk
                JOIN rpl_mahasiswa rm ON rm.id=rk.rpl_mahasiswa_id
                JOIN mata_kuliah mk ON mk.id=rk.mata_kuliah_id
                WHERE rm.mahasiswa_id=:mhs_id AND rk.status='DISETUJUI'
                ORDER BY mk.semester,mk.kode");
            $stmtRpl->execute(['mhs_id' => $mahasiswa_id]);
            foreach ($stmtRpl->fetchAll(PDO::FETCH_ASSOC) as $rpl) {
                $sks=(int)$rpl['sks_diakui']; $mutu=round($sks*(float)$rpl['bobot_mutu'],2);
                $semuaNilai[]=[
                    'kelas_id'=>null,'nama_kelas'=>'Konversi RPL','tahun_akademik_id'=>0,
                    'tahun_akademik_nama'=>'Konversi RPL','mk_id'=>$rpl['mata_kuliah_id'],
                    'mk_kode'=>$rpl['mk_kode'],'mk_nama'=>$rpl['mk_nama'],
                    'mk_semester'=>(int)$rpl['mk_semester'],'sks'=>$sks,
                    'nilai_angka'=>(float)$rpl['nilai_angka'],'nilai_huruf'=>$rpl['nilai_huruf'],
                    'bobot'=>(float)$rpl['bobot_mutu'],'sks_x_bobot'=>$mutu,
                    'is_lulus'=>true,'status'=>'LULUS (RPL)','sumber'=>'RPL'
                ];
                $totalSksKumulatif += $sks; $totalSksLulusKumulatif += $sks; $totalMutuKumulatif += $mutu;
            }
        } catch (PDOException $e) {
            // Kompatibel saat migration RPL belum dijalankan.
        }

        // Hitung IPK Kumulatif
        $ipk = $totalSksKumulatif > 0 ? round($totalMutuKumulatif / $totalSksKumulatif, 2) : 0;

        // Predikat Kelulusan / Prestasi
        $predikat = 'Memuaskan';
        if ($ipk >= 3.75) {
            $predikat = 'Dengan Pujian (Cum Laude)';
        } elseif ($ipk >= 3.50) {
            $predikat = 'Sangat Memuaskan';
        } elseif ($ipk >= 3.00) {
            $predikat = 'Memuaskan';
        } elseif ($ipk >= 2.00) {
            $predikat = 'Cukup';
        } else {
            $predikat = 'Kurang';
        }

        // Filter jika user memilih semester tertentu
        $filteredItems = $semuaNilai;
        if (!empty($filter_ta_id)) {
            $filteredItems = array_values(array_filter($semuaNilai, function($it) use ($filter_ta_id) {
                return (int)$it['tahun_akademik_id'] === (int)$filter_ta_id;
            }));
        }

        return [
            'semua_mk' => $filteredItems,
            'per_semester' => $perSemester,
            'ringkasan' => [
                'total_sks_ditempuh' => $totalSksKumulatif,
                'total_sks_lulus' => $totalSksLulusKumulatif,
                'total_mutu' => round($totalMutuKumulatif, 2),
                'ipk' => $ipk,
                'predikat' => $predikat
            ],
            'ignored_enrollment_count' => $ignoredEnrollmentCount
        ];
    }

    /**
     * Mengambil tracking ketercapaian CPL per mahasiswa
     */
    public function getCplMahasiswa($mahasiswa_id) {
        $cplData = $this->laporanModel->getKetercapaianCplMahasiswa($mahasiswa_id);
        $traceability = $this->laporanModel->getTraceabilityDetail($mahasiswa_id);

        $totalCpl = count($cplData);
        $tercapai = 0;
        $belumTercapai = 0;
        $belumDiukur = 0;
        $totalSkor = 0;
        $measuredCount = 0;

        foreach ($cplData as $c) {
            if ($c['is_measured']) {
                $totalSkor += $c['score'];
                $measuredCount++;
                if ($c['status'] === 'Tercapai') {
                    $tercapai++;
                } else {
                    $belumTercapai++;
                }
            } else {
                $belumDiukur++;
            }
        }

        $avgCpl = $measuredCount > 0 ? round($totalSkor / $measuredCount, 2) : 0;
        $persenKetercapaian = $totalCpl > 0 ? round(($tercapai / $totalCpl) * 100, 1) : 0;

        return [
            'cpl_list' => $cplData,
            'traceability' => $traceability,
            'ringkasan' => [
                'total_cpl' => $totalCpl,
                'cpl_tercapai' => $tercapai,
                'cpl_belum_tercapai' => $belumTercapai,
                'cpl_belum_diukur' => $belumDiukur,
                'rata_rata_skor' => $avgCpl,
                'persentase_tercapai' => $persenKetercapaian
            ]
        ];
    }

    /**
     * Konversi nilai angka ke Huruf Mutu, Bobot Angka (AM), Predikat Resmi, dan Status Kelulusan
     * Sesuai Standar Pedoman Penilaian Akademik:
     * 1. A  : 76 - 100 (AM: 4.0) | Istimewa     (Lulus)
     * 2. AB : 70 - 75  (AM: 3.5) | Baik Sekali  (Lulus)
     * 3. B  : 65 - 69  (AM: 3.0) | Baik         (Lulus)
     * 4. BC : 60 - 64  (AM: 2.5) | Cukup Baik   (Lulus)
     * 5. C  : 50 - 59  (AM: 2.0) | Cukup        (Lulus)
     * 6. D  : 40 - 49  (AM: 1.0) | Kurang       (Mengulang)
     * 7. E  :  0 - 39  (AM: 0.0) | Kurang Sekali(Mengulang)
     */
    private function konversiNilai($angka, $hasScore) {
        if (!$hasScore) {
            return ['huruf' => '-', 'bobot' => 0.00, 'predikat' => '-', 'is_lulus' => false];
        }

        if ($angka >= 76) {
            return ['huruf' => 'A', 'bobot' => 4.00, 'predikat' => 'Istimewa', 'is_lulus' => true];
        } elseif ($angka >= 70) {
            return ['huruf' => 'AB', 'bobot' => 3.50, 'predikat' => 'Baik Sekali', 'is_lulus' => true];
        } elseif ($angka >= 65) {
            return ['huruf' => 'B', 'bobot' => 3.00, 'predikat' => 'Baik', 'is_lulus' => true];
        } elseif ($angka >= 60) {
            return ['huruf' => 'BC', 'bobot' => 2.50, 'predikat' => 'Cukup Baik', 'is_lulus' => true];
        } elseif ($angka >= 50) {
            return ['huruf' => 'C', 'bobot' => 2.00, 'predikat' => 'Cukup', 'is_lulus' => true];
        } elseif ($angka >= 40) {
            return ['huruf' => 'D', 'bobot' => 1.00, 'predikat' => 'Kurang', 'is_lulus' => false];
        } else {
            return ['huruf' => 'E', 'bobot' => 0.00, 'predikat' => 'Kurang Sekali', 'is_lulus' => false];
        }
    }
}
