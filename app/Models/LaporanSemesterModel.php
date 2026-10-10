<?php

require_once __DIR__ . '/../../config/database.php';

class LaporanSemesterModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTahunAkademik()
    {
        return $this->db->query("SELECT id, nama, is_active FROM tahun_akademik ORDER BY is_active DESC, id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKurikulum()
    {
        return $this->db->query("
            SELECT k.id, k.nama, k.kode, k.tahun_mulai, k.prodi_id, p.nama AS prodi_nama, p.jenjang
            FROM kurikulum k
            JOIN program_studi p ON p.id = k.prodi_id
            ORDER BY k.status = 'ACTIVE' DESC, k.tahun_mulai DESC, k.nama ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReport($tahunAkademikId, $kurikulumId = null)
    {
        $params = ['ta_id' => (int)$tahunAkademikId];
        $kurikulumFilter = '';
        if ($kurikulumId) {
            $kurikulumFilter = ' AND mk.kurikulum_id = :kurikulum_id';
            $params['kurikulum_id'] = (int)$kurikulumId;
        }

        $stmt = $this->db->prepare("
            SELECT
                an.mahasiswa_id,
                an.kelas_id,
                k.nama_kelas,
                mk.id AS mk_id,
                mk.kode AS mk_kode,
                mk.nama AS mk_nama,
                sc.id AS sub_cpmk_id,
                sc.cpmk_id,
                sc.bobot AS sub_bobot,
                AVG(an.nilai) AS nilai_sub,
                c.id AS cpl_id,
                c.kode AS cpl_kode,
                c.rumusan AS cpl_rumusan,
                COALESCE(c.target_capaian, 70) AS target
            FROM asesmen_nilai an
            JOIN kelas k ON k.id = an.kelas_id
            JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
            JOIN sub_cpmk sc ON sc.id = an.sub_cpmk_id
            JOIN cpmk_cpl cc ON cc.cpmk_id = sc.cpmk_id
            JOIN cpl c ON c.id = cc.cpl_id AND c.kurikulum_id = mk.kurikulum_id
            WHERE k.tahun_akademik_id = :ta_id {$kurikulumFilter}
            GROUP BY an.mahasiswa_id, an.kelas_id, k.nama_kelas, mk.id, mk.kode, mk.nama,
                     sc.id, sc.cpmk_id, sc.bobot, c.id, c.kode, c.rumusan, c.target_capaian
            ORDER BY c.kode, mk.kode, an.mahasiswa_id
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cpmkScores = [];
        $classes = [];
        $courses = [];
        $students = [];
        $cplMeta = [];

        foreach ($rows as $row) {
            $cplId = (int)$row['cpl_id'];
            $studentId = (int)$row['mahasiswa_id'];
            $cpmkId = (int)$row['cpmk_id'];
            $key = $cplId . ':' . $studentId . ':' . $cpmkId;
            $weight = max(0, (float)$row['sub_bobot']);

            if (!isset($cpmkScores[$key])) {
                $cpmkScores[$key] = ['cpl_id' => $cplId, 'student_id' => $studentId, 'sum' => 0.0, 'weight' => 0.0];
            }
            $cpmkScores[$key]['sum'] += (float)$row['nilai_sub'] * $weight;
            $cpmkScores[$key]['weight'] += $weight;

            $classes[(int)$row['kelas_id']] = true;
            $courses[(int)$row['mk_id']] = ['kode' => $row['mk_kode'], 'nama' => $row['mk_nama']];
            $students[$studentId] = true;
            $cplMeta[$cplId] = [
                'id' => $cplId,
                'kode' => $row['cpl_kode'],
                'rumusan' => $row['cpl_rumusan'],
                'target' => (float)$row['target'],
            ];
        }

        $studentCpl = [];
        foreach ($cpmkScores as $score) {
            if ($score['weight'] <= 0) {
                continue;
            }
            $key = $score['cpl_id'] . ':' . $score['student_id'];
            if (!isset($studentCpl[$key])) {
                $studentCpl[$key] = ['cpl_id' => $score['cpl_id'], 'sum' => 0.0, 'count' => 0];
            }
            $studentCpl[$key]['sum'] += $score['sum'] / $score['weight'];
            $studentCpl[$key]['count']++;
        }

        $rekap = [];
        foreach ($cplMeta as $cplId => $meta) {
            $rekap[$cplId] = $meta + ['score_sum' => 0.0, 'mahasiswa_terukur' => 0, 'mahasiswa_tercapai' => 0];
        }
        foreach ($studentCpl as $item) {
            if ($item['count'] <= 0 || !isset($rekap[$item['cpl_id']])) {
                continue;
            }
            $score = $item['sum'] / $item['count'];
            $rekap[$item['cpl_id']]['score_sum'] += $score;
            $rekap[$item['cpl_id']]['mahasiswa_terukur']++;
            if ($score >= $rekap[$item['cpl_id']]['target']) {
                $rekap[$item['cpl_id']]['mahasiswa_tercapai']++;
            }
        }

        foreach ($rekap as &$item) {
            $count = $item['mahasiswa_terukur'];
            $item['rata_rata'] = $count ? round($item['score_sum'] / $count, 2) : 0;
            $item['persentase_lulus'] = $count ? round(($item['mahasiswa_tercapai'] / $count) * 100, 1) : 0;
            $item['status'] = $count === 0 ? 'Belum Diukur' : ($item['rata_rata'] >= $item['target'] ? 'Tercapai' : 'Belum Tercapai');
            unset($item['score_sum']);
        }
        unset($item);

        $rekap = array_values($rekap);
        usort($rekap, function ($a, $b) { return strnatcasecmp($a['kode'], $b['kode']); });
        $measured = array_values(array_filter($rekap, function ($item) { return $item['mahasiswa_terukur'] > 0; }));

        return [
            'rekap' => $rekap,
            'summary' => [
                'mahasiswa' => count($students),
                'kelas' => count($classes),
                'mata_kuliah' => count($courses),
                'cpl_terukur' => count($measured),
                'cpl_tercapai' => count(array_filter($measured, function ($item) { return $item['status'] === 'Tercapai'; })),
                'rata_rata' => $measured ? round(array_sum(array_column($measured, 'rata_rata')) / count($measured), 2) : 0,
            ],
            'mata_kuliah' => array_values($courses),
        ];
    }
}
