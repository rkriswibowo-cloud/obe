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
                   SUBSTRING(u.username, 1, 4) as angkatan
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
     * Hitung KHS (Kartu Hasil Studi), IPS, dan IPK untuk seorang mahasiswa
     */
    public function getKhsMahasiswa($mahasiswa_id, $filter_ta_id = null) {
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
            ORDER BY ta.id ASC, mk.semester ASC, mk.kode ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mhs_id' => $mahasiswa_id]);
        $enrolledClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $sem['ips'] = $sem['total_sks'] > 0 ? round($sem['total_mutu'] / $sem['total_sks'], 2) : 0;
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
            ]
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
     * Konversi nilai angka ke Huruf Mutu, Bobot Angka, dan Status Kelulusan
     */
    private function konversiNilai($angka, $hasScore) {
        if (!$hasScore) {
            return ['huruf' => '-', 'bobot' => 0.00, 'is_lulus' => false];
        }

        if ($angka >= 85) {
            return ['huruf' => 'A', 'bobot' => 4.00, 'is_lulus' => true];
        } elseif ($angka >= 80) {
            return ['huruf' => 'A-', 'bobot' => 3.75, 'is_lulus' => true];
        } elseif ($angka >= 75) {
            return ['huruf' => 'B+', 'bobot' => 3.50, 'is_lulus' => true];
        } elseif ($angka >= 70) {
            return ['huruf' => 'B', 'bobot' => 3.00, 'is_lulus' => true];
        } elseif ($angka >= 65) {
            return ['huruf' => 'B-', 'bobot' => 2.75, 'is_lulus' => true];
        } elseif ($angka >= 60) {
            return ['huruf' => 'C+', 'bobot' => 2.50, 'is_lulus' => true];
        } elseif ($angka >= 55) {
            return ['huruf' => 'C', 'bobot' => 2.00, 'is_lulus' => true];
        } elseif ($angka >= 40) {
            return ['huruf' => 'D', 'bobot' => 1.00, 'is_lulus' => false];
        } else {
            return ['huruf' => 'E', 'bobot' => 0.00, 'is_lulus' => false];
        }
    }
}
