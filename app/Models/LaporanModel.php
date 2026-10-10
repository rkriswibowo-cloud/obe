<?php
// app/Models/LaporanModel.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AturanKurikulumModel.php';

class LaporanModel {
    private $db;

    private $aturanModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->aturanModel = new AturanKurikulumModel();
    }

    /**
     * Helper: Resolve kurikulum_id from mahasiswa_id via angkatan rules.
     * Returns kurikulum_id or null if no rule matches.
     */
    public function getKurikulumIdForMahasiswa($mahasiswa_id) {
        $stmt = $this->db->prepare("
            SELECT u.username FROM users u WHERE u.id = :id
        ");
        $stmt->execute(['id' => $mahasiswa_id]);
        $username = $stmt->fetchColumn();
        if (!$username) return null;
        $angkatan = AturanKurikulumModel::extractAngkatan($username);
        if (!$angkatan) return null;
        return $this->aturanModel->getKurikulumIdByAngkatan($angkatan);
    }

    public function getStatistikDashboard($prodiIds = null) {
        $stats = [
            'total_cpl' => 0,
            'total_mk' => 0,
            'rps_aktif' => 0,
            'avg_cpl' => 0
        ];

        $prodiIds = is_array($prodiIds) ? array_values(array_filter(array_map('intval', $prodiIds))) : [];
        $scope = $prodiIds ? ' WHERE k.prodi_id IN (' . implode(',', array_fill(0, count($prodiIds), '?')) . ')' : '';
        $andScope = $prodiIds ? ' AND k.prodi_id IN (' . implode(',', array_fill(0, count($prodiIds), '?')) . ')' : '';

        // Total CPL
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cpl c JOIN kurikulum k ON k.id=c.kurikulum_id{$scope}");
        $stmt->execute($prodiIds);
        $stats['total_cpl'] = (int) $stmt->fetchColumn();

        // Total MK
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM mata_kuliah mk JOIN kurikulum k ON k.id=mk.kurikulum_id{$scope}");
        $stmt->execute($prodiIds);
        $stats['total_mk'] = (int) $stmt->fetchColumn();

        // RPS Aktif (MK yang punya setidaknya 1 CPMK atau pertemuan)
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT cp.mk_id) FROM cpmk cp JOIN mata_kuliah mk ON mk.id=cp.mk_id JOIN kurikulum k ON k.id=mk.kurikulum_id WHERE 1=1{$andScope}");
        $stmt->execute($prodiIds);
        $stats['rps_aktif'] = (int) $stmt->fetchColumn();

        // Rata-rata CPL Keseluruhan dari nilai asesmen
        $stmtAvg = $this->db->prepare("SELECT AVG(an.nilai) FROM asesmen_nilai an JOIN kelas kl ON kl.id=an.kelas_id JOIN mata_kuliah mk ON mk.id=kl.mata_kuliah_id JOIN kurikulum k ON k.id=mk.kurikulum_id WHERE 1=1{$andScope}");
        $stmtAvg->execute($prodiIds);
        $avgVal = $stmtAvg->fetchColumn();
        $stats['avg_cpl'] = $avgVal !== null ? round(floatval($avgVal), 2) : 0;

        return $stats;
    }

    public function getKetercapaianCplMahasiswa($mahasiswa_id) {
        // Resolve kurikulum_id for this student
        $kurikulumId = $this->getKurikulumIdForMahasiswa($mahasiswa_id);

        // Step 1: hitung nilai CPMK dari Sub-CPMK dengan bobot yang dinormalisasi.
        // Bobot tidak wajib tersimpan tepat 100; normalisasi menjaga proporsi aktual.
        $query = "
            SELECT 
                sc.cpmk_id,
                sc.id AS sub_cpmk_id,
                sc.bobot,
                AVG(an.nilai) AS nilai_sub_cpmk
            FROM asesmen_nilai an
            JOIN sub_cpmk sc ON an.sub_cpmk_id = sc.id
            WHERE an.mahasiswa_id = :mhs_id
            GROUP BY sc.cpmk_id, sc.id, sc.bobot
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['mhs_id' => $mahasiswa_id]);
        $subCpmkScores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cpmkWeighted = [];
        foreach ($subCpmkScores as $row) {
            $cpmkId = (int)$row['cpmk_id'];
            $weight = max(0, (float)$row['bobot']);
            if (!isset($cpmkWeighted[$cpmkId])) {
                $cpmkWeighted[$cpmkId] = ['weighted_sum' => 0.0, 'weight_sum' => 0.0];
            }
            $cpmkWeighted[$cpmkId]['weighted_sum'] += (float)$row['nilai_sub_cpmk'] * $weight;
            $cpmkWeighted[$cpmkId]['weight_sum'] += $weight;
        }

        $cpmk_score_map = [];
        foreach ($cpmkWeighted as $cpmkId => $value) {
            if ($value['weight_sum'] > 0) {
                $cpmk_score_map[$cpmkId] = $value['weighted_sum'] / $value['weight_sum'];
            }
        }

        // Step 2: Ambil CPL sesuai kurikulum mahasiswa (filter by kurikulum_id if available)
        $queryCpl = "
            SELECT 
                c.id as cpl_id,
                c.kode as cpl_kode,
                c.rumusan as cpl_rumusan,
                c.target_capaian,
                cc.cpmk_id
            FROM cpl c
            LEFT JOIN cpmk_cpl cc ON c.id = cc.cpl_id
        ";
        $cplParams = [];
        if ($kurikulumId) {
            $queryCpl .= " WHERE c.kurikulum_id = :kid";
            $cplParams['kid'] = $kurikulumId;
        }
        $queryCpl .= " ORDER BY c.id";
        $stmtCpl = $this->db->prepare($queryCpl);
        $stmtCpl->execute($cplParams);
        $cpl_relations = $stmtCpl->fetchAll(PDO::FETCH_ASSOC);

        // Group CPMKs by CPL
        $cpl_data = [];
        foreach ($cpl_relations as $row) {
            $cpl_id = $row['cpl_id'];
            if (!isset($cpl_data[$cpl_id])) {
                $cpl_data[$cpl_id] = [
                    'id' => $cpl_id,
                    'kode' => $row['cpl_kode'],
                    'rumusan' => $row['cpl_rumusan'],
                    'target' => floatval($row['target_capaian'] ?? 70.00),
                    'cpmk_ids' => [],
                    'score' => 0,
                    'is_measured' => false,
                    'status' => 'Belum Diukur'
                ];
            }
            if ($row['cpmk_id']) {
                $cpl_data[$cpl_id]['cpmk_ids'][] = $row['cpmk_id'];
            }
        }

        // Step 3: Hitung skor CPL (Rata-rata dari skor CPMK yang menyusunnya)
        foreach ($cpl_data as &$cpl) {
            $total_score = 0;
            $count = 0;

            foreach ($cpl['cpmk_ids'] as $cpmk_id) {
                if (isset($cpmk_score_map[$cpmk_id])) {
                    $total_score += $cpmk_score_map[$cpmk_id];
                    $count++;
                }
            }

            if ($count > 0) {
                $cpl['score'] = round($total_score / $count, 2);
                $cpl['is_measured'] = true;
                $cpl['status'] = ($cpl['score'] >= $cpl['target']) ? 'Tercapai' : 'Belum Tercapai';
            }
        }

        return array_values($cpl_data);
    }

    public function getTraceabilityDetail($mahasiswa_id) {
        // Resolve kurikulum_id for this student
        $kurikulumId = $this->getKurikulumIdForMahasiswa($mahasiswa_id);

        $sql = "
            SELECT 
                c.id as cpl_id,
                c.kode as cpl_kode,
                mk.id as mk_id,
                mk.kode as mk_kode,
                mk.nama as mk_nama,
                cp.id as cpmk_id,
                cp.kode as cpmk_kode,
                cp.rumusan as cpmk_rumusan,
                sc.id as sub_cpmk_id,
                sc.kode as sub_cpmk_kode,
                sc.rumusan as sub_cpmk_rumusan,
                sc.bobot as sub_cpmk_bobot,
                an.nilai as nilai_asesmen,
                k.nama_kelas
            FROM cpl c
            JOIN cpmk_cpl cc ON c.id = cc.cpl_id
            JOIN cpmk cp ON cc.cpmk_id = cp.id
            JOIN mata_kuliah mk ON cp.mk_id = mk.id
            JOIN sub_cpmk sc ON cp.id = sc.cpmk_id
            LEFT JOIN asesmen_nilai an ON sc.id = an.sub_cpmk_id AND an.mahasiswa_id = :mhs_id
            LEFT JOIN kelas k ON an.kelas_id = k.id
        ";
        if ($kurikulumId) {
            $sql .= " WHERE c.kurikulum_id = :kid";
        }
        $sql .= " ORDER BY c.id, mk.kode, cp.kode, sc.urutan";
        $stmt = $this->db->prepare($sql);
        $params = ['mhs_id' => $mahasiswa_id];
        if ($kurikulumId) {
            $params['kid'] = $kurikulumId;
        }
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $trace = [];
        foreach ($rows as $r) {
            $cpl_id = $r['cpl_id'];
            $mk_id = $r['mk_id'];
            $cpmk_id = $r['cpmk_id'];

            if (!isset($trace[$cpl_id])) {
                $trace[$cpl_id] = [];
            }
            if (!isset($trace[$cpl_id][$mk_id])) {
                $trace[$cpl_id][$mk_id] = [
                    'mk_kode' => $r['mk_kode'],
                    'mk_nama' => $r['mk_nama'],
                    'cpmks' => []
                ];
            }
            if (!isset($trace[$cpl_id][$mk_id]['cpmks'][$cpmk_id])) {
                $trace[$cpl_id][$mk_id]['cpmks'][$cpmk_id] = [
                    'cpmk_kode' => $r['cpmk_kode'],
                    'cpmk_rumusan' => $r['cpmk_rumusan'],
                    'sub_cpmks' => []
                ];
            }
            $trace[$cpl_id][$mk_id]['cpmks'][$cpmk_id]['sub_cpmks'][] = [
                'sub_kode' => $r['sub_cpmk_kode'],
                'sub_rumusan' => $r['sub_cpmk_rumusan'],
                'sub_bobot' => $r['sub_cpmk_bobot'],
                'nilai' => $r['nilai_asesmen'] !== null ? floatval($r['nilai_asesmen']) : null,
                'nama_kelas' => $r['nama_kelas']
            ];
        }
        return $trace;
    }

    public function getDaftarAngkatan() {
        $sql = "
            SELECT DISTINCT 
                CASE 
                    WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                    WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                    ELSE NULL
                END as angkatan
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Mahasiswa'
            HAVING angkatan IS NOT NULL
            ORDER BY angkatan DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllMahasiswa($angkatan = null) {
        $sql = "
            SELECT u.id, u.username, u.nama_lengkap, '-' as email,
                   CASE 
                       WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                       WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                       ELSE NULL
                   END as angkatan
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE r.name = 'Mahasiswa'
        ";
        $params = [];
        if ($angkatan) {
            $sql .= " AND (
                CASE 
                    WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                    WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                    ELSE NULL
                END
            ) = :angkatan";
            $params['angkatan'] = (int)$angkatan;
        }
        $sql .= " ORDER BY u.username ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDaftarKurikulum() {
        $sql = "
            SELECT k.*, p.nama as nama_prodi, p.jenjang as jenjang_prodi
            FROM kurikulum k
            JOIN program_studi p ON k.prodi_id = p.id
            ORDER BY k.tahun_mulai DESC, k.id DESC
        ";
        $kurikulums = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $allMahasiswa = $this->getAllMahasiswa();

        foreach ($kurikulums as &$k) {
            // Hitung total CPL
            $stmtCpl = $this->db->prepare("SELECT COUNT(*) FROM cpl WHERE kurikulum_id = ?");
            $stmtCpl->execute([$k['id']]);
            $k['total_cpl'] = (int)$stmtCpl->fetchColumn();

            // Hitung rentang angkatan dari aturan_kurikulum
            $stmtAturan = $this->db->prepare("
                SELECT angkatan_mulai, angkatan_selesai 
                FROM aturan_kurikulum 
                WHERE kurikulum_id = ? 
                ORDER BY prioritas DESC 
                LIMIT 1
            ");
            $stmtAturan->execute([$k['id']]);
            $rule = $stmtAturan->fetch(PDO::FETCH_ASSOC);
            if ($rule) {
                if ($rule['angkatan_selesai']) {
                    $k['rentang_angkatan'] = $rule['angkatan_mulai'] . '–' . $rule['angkatan_selesai'];
                } else {
                    $k['rentang_angkatan'] = $rule['angkatan_mulai'] . '+';
                }
            } else {
                $k['rentang_angkatan'] = 'Tahun ' . $k['tahun_mulai'];
            }

            // Hitung jumlah mahasiswa yang terdaftar di kurikulum ini
            $mhsCount = 0;
            foreach ($allMahasiswa as $m) {
                if ($this->getKurikulumIdForMahasiswa($m['id']) == $k['id']) {
                    $mhsCount++;
                }
            }
            $k['total_mahasiswa'] = $mhsCount;
        }

        return $kurikulums;
    }

    public function getAngkatanByKurikulum($kurikulumId) {
        $angkatans = [];
        
        // 1. Dari aturan_kurikulum
        $stmtAturan = $this->db->prepare("
            SELECT angkatan_mulai, angkatan_selesai 
            FROM aturan_kurikulum 
            WHERE kurikulum_id = ? 
            ORDER BY angkatan_mulai DESC
        ");
        $stmtAturan->execute([$kurikulumId]);
        $rules = $stmtAturan->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rules as $r) {
            $mulai = (int)$r['angkatan_mulai'];
            $selesai = $r['angkatan_selesai'] !== null ? (int)$r['angkatan_selesai'] : $mulai;
            for ($yr = $selesai; $yr >= $mulai; $yr--) {
                $angkatans[$yr] = true;
            }
        }

        // 2. Dari mahasiswa aktual yang terdaftar pada kurikulum ini
        $allMhs = $this->getAllMahasiswa();
        foreach ($allMhs as $m) {
            if ($this->getKurikulumIdForMahasiswa($m['id']) == $kurikulumId && !empty($m['angkatan'])) {
                $angkatans[(int)$m['angkatan']] = true;
            }
        }

        // Jika kosong sama sekali, gunakan tahun_mulai kurikulum
        if (empty($angkatans)) {
            $stmtK = $this->db->prepare("SELECT tahun_mulai FROM kurikulum WHERE id = ?");
            $stmtK->execute([$kurikulumId]);
            $thn = $stmtK->fetchColumn();
            if ($thn) $angkatans[(int)$thn] = true;
        }

        krsort($angkatans);
        return array_keys($angkatans);
    }

    public function getRekapCplKurikulum($kurikulumId, $angkatan = null) {
        // 1. Ambil CPL hanya untuk kurikulum ini
        $stmtCpl = $this->db->prepare("
            SELECT id, kode, rumusan, target_capaian 
            FROM cpl 
            WHERE kurikulum_id = :kid 
            ORDER BY kode ASC, id ASC
        ");
        $stmtCpl->execute(['kid' => $kurikulumId]);
        $allCpls = $stmtCpl->fetchAll(PDO::FETCH_ASSOC);

        $rekap = [];
        foreach ($allCpls as $c) {
            $rekap[$c['id']] = [
                'id' => $c['id'],
                'kode' => $c['kode'],
                'rumusan' => $c['rumusan'],
                'target' => floatval($c['target_capaian'] ?? 70.00),
                'total_score' => 0,
                'count_measured' => 0,
                'count_passed' => 0,
                'avg_score' => 0,
                'persen_tercapai' => 0,
                'status' => 'Belum Diukur'
            ];
        }

        // 2. Ambil mahasiswa yang terdaftar di kurikulum ini (dan sesuai angkatan jika difilter)
        $allMahasiswa = $this->getAllMahasiswa($angkatan);
        $mahasiswaList = [];
        foreach ($allMahasiswa as $mhs) {
            if ($this->getKurikulumIdForMahasiswa($mhs['id']) == $kurikulumId) {
                $mahasiswaList[] = $mhs;
            }
        }

        // 3. Iterasi tiap mahasiswa untuk hitung capaian CPL
        foreach ($mahasiswaList as $mhs) {
            $mhsCpl = $this->getKetercapaianCplMahasiswa($mhs['id']);
            foreach ($mhsCpl as $c) {
                if ($c['is_measured'] && isset($rekap[$c['id']])) {
                    $rekap[$c['id']]['total_score'] += $c['score'];
                    $rekap[$c['id']]['count_measured']++;
                    if ($c['score'] >= $rekap[$c['id']]['target']) {
                        $rekap[$c['id']]['count_passed']++;
                    }
                }
            }
        }

        // 4. Finalisasi rata-rata & status
        foreach ($rekap as &$r) {
            if ($r['count_measured'] > 0) {
                $r['avg_score'] = round($r['total_score'] / $r['count_measured'], 2);
                $r['persen_tercapai'] = round(($r['count_passed'] / $r['count_measured']) * 100, 1);
                $r['status'] = ($r['avg_score'] >= $r['target']) ? 'Tercapai' : 'Belum Tercapai';
            }
        }

        return [
            'rekap' => array_values($rekap),
            'mahasiswa' => $mahasiswaList
        ];
    }

    public function getRekapCplAngkatan($angkatan = null, $kurikulumId = null) {
        if (!$kurikulumId && $angkatan && is_numeric($angkatan)) {
            $kurikulumId = $this->aturanModel->getKurikulumIdByAngkatan((int)$angkatan);
        }

        if ($kurikulumId) {
            $res = $this->getRekapCplKurikulum($kurikulumId, $angkatan);
            return $res['rekap'];
        }

        // Fallback jika tidak ada kurikulumId: pilih kurikulum yang ada mahasiswa
        $daftarKurikulum = $this->getDaftarKurikulum();
        $bestKid = null;
        $maxMhs = -1;
        foreach ($daftarKurikulum as $k) {
            if ($k['total_mahasiswa'] > $maxMhs) {
                $maxMhs = $k['total_mahasiswa'];
                $bestKid = $k['id'];
            }
        }
        $targetKid = $bestKid ?? ($daftarKurikulum[0]['id'] ?? 1);
        $res = $this->getRekapCplKurikulum($targetKid, $angkatan);
        return $res['rekap'];
    }
}
