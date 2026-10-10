<?php
require_once __DIR__ . '/../Models/LaporanModel.php';

class DashboardController {
    private $laporanModel;

    public function __construct() {
        $this->laporanModel = new LaporanModel();
    }

    public function index() {
        $access = new AccessContext();
        $scopeProdiIds = !$access->isSuperAdmin() && $access->isKaprodi() ? $access->managedProdiIds() : null;
        $stats = $this->laporanModel->getStatistikDashboard($scopeProdiIds);
        
        $kurikulumId = null;
        if ($scopeProdiIds) {
            $stmt = Database::getInstance()->prepare("SELECT id FROM kurikulum WHERE prodi_id IN (" . implode(',', array_fill(0, count($scopeProdiIds), '?')) . ") ORDER BY status='ACTIVE' DESC,tahun_mulai DESC LIMIT 1");
            $stmt->execute($scopeProdiIds);
            $kurikulumId = $stmt->fetchColumn() ?: null;
        }
        $rekapCpl = $this->laporanModel->getRekapCplAngkatan(null, $kurikulumId);
        
        // Prepare chart payload
        $chartLabels = [];
        $chartScores = [];
        $chartTargets = [];
        $chartRumusans = [];

        foreach ($rekapCpl as $r) {
            $chartLabels[] = $r['kode'];
            $chartScores[] = $r['avg_score'];
            $chartTargets[] = $r['target'];
            $chartRumusans[] = $r['rumusan'];
        }

        $cplChartData = [
            'labels' => $chartLabels,
            'scores' => $chartScores,
            'targets' => $chartTargets,
            'rumusans' => $chartRumusans
        ];

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Mahasiswa') {
            $mahasiswa_id = $_SESSION['user_id'];
            $cpl_data = $this->laporanModel->getKetercapaianCplMahasiswa($mahasiswa_id);
            $total_score = 0;
            $count = 0;
            foreach ($cpl_data as $cpl) {
                if ($cpl['is_measured']) {
                    $total_score += $cpl['score'];
                    $count++;
                }
            }
            $stats['avg_cpl'] = $count > 0 ? round($total_score / $count, 2) : 0;
        }

        require __DIR__ . '/../../views/dashboard/index.php';
    }
}
