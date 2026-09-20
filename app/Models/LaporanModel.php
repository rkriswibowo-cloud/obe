<?php
// app/Models/LaporanModel.php
require_once __DIR__ . '/../../config/database.php';

class LaporanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getStatistikDashboard() {
        $stats = [
            'total_cpl' => 0,
            'total_mk' => 0,
            'rps_aktif' => 0,
            'avg_cpl' => 0
        ];

        // Total CPL
        $stmt = $this->db->query("SELECT COUNT(*) FROM cpl");
        $stats['total_cpl'] = (int) $stmt->fetchColumn();

        // Total MK
        $stmt = $this->db->query("SELECT COUNT(*) FROM mata_kuliah");
        $stats['total_mk'] = (int) $stmt->fetchColumn();

        // RPS Aktif (MK yang punya setidaknya 1 CPMK atau pertemuan)
        $stmt = $this->db->query("SELECT COUNT(DISTINCT mk_id) FROM cpmk");
        $stats['rps_aktif'] = (int) $stmt->fetchColumn();

        // Rata-rata CPL Keseluruhan dari nilai asesmen
        $stmtAvg = $this->db->query("SELECT AVG(nilai) FROM asesmen_nilai");
        $avgVal = $stmtAvg->fetchColumn();
        $stats['avg_cpl'] = $avgVal !== null ? round(floatval($avgVal), 2) : 0;

        return $stats;
    }

    public function getKetercapaianCplMahasiswa($mahasiswa_id) {
        // Step 1: Ambil semua nilai Asesmen Sub-CPMK untuk mahasiswa ini
        $query = "
            SELECT 
                sc.cpmk_id,
                AVG(an.nilai) as avg_sub_cpmk_nilai
            FROM asesmen_nilai an
            JOIN sub_cpmk sc ON an.sub_cpmk_id = sc.id
            WHERE an.mahasiswa_id = :mhs_id
            GROUP BY sc.cpmk_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['mhs_id' => $mahasiswa_id]);
        $cpmk_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cpmk_score_map = [];
        foreach ($cpmk_scores as $row) {
            $cpmk_score_map[$row['cpmk_id']] = floatval($row['avg_sub_cpmk_nilai']);
        }

        // Step 2: Ambil semua CPL beserta CPMK yang terkait ke CPL tersebut dan target_capaian
        $queryCpl = "
            SELECT 
                c.id as cpl_id,
                c.kode as cpl_kode,
                c.rumusan as cpl_rumusan,
                c.target_capaian,
                cc.cpmk_id
            FROM cpl c
            LEFT JOIN cpmk_cpl cc ON c.id = cc.cpl_id
            ORDER BY c.id
        ";
        $stmtCpl = $this->db->query($queryCpl);
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
            ORDER BY c.id, mk.kode, cp.kode, sc.urutan
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mhs_id' => $mahasiswa_id]);
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
            SELECT DISTINCT SUBSTRING(u.username, 1, 4) as angkatan
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Mahasiswa' AND u.username REGEXP '^[0-9]{4}'
            ORDER BY angkatan DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllMahasiswa($angkatan = null) {
        $sql = "
            SELECT u.id, u.username, u.nama_lengkap, '-' as email,
                   SUBSTRING(u.username, 1, 4) as angkatan
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE r.name = 'Mahasiswa'
        ";
        $params = [];
        if ($angkatan) {
            $sql .= " AND u.username LIKE :angkatan";
            $params['angkatan'] = $angkatan . '%';
        }
        $sql .= " ORDER BY u.username ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRekapCplAngkatan($angkatan = null) {
        // Ambil mahasiswa yang sesuai angkatan
        $mahasiswaList = $this->getAllMahasiswa($angkatan);
        
        // Ambil semua CPL
        $cplStmt = $this->db->query("SELECT id, kode, rumusan, target_capaian FROM cpl ORDER BY id ASC");
        $allCpls = $cplStmt->fetchAll(PDO::FETCH_ASSOC);

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

        // Iterasi tiap mahasiswa untuk hitung capaian
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

        // Finalisasi rata-rata & persentase
        foreach ($rekap as &$r) {
            if ($r['count_measured'] > 0) {
                $r['avg_score'] = round($r['total_score'] / $r['count_measured'], 2);
                $r['persen_tercapai'] = round(($r['count_passed'] / $r['count_measured']) * 100, 1);
                $r['status'] = ($r['avg_score'] >= $r['target']) ? 'Tercapai' : 'Belum Tercapai';
            }
        }

        return array_values($rekap);
    }
}
