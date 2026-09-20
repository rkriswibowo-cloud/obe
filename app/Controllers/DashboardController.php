<?php
require_once __DIR__ . '/../Models/LaporanModel.php';

class DashboardController {
    private $laporanModel;

    public function __construct() {
        $this->laporanModel = new LaporanModel();
    }

    public function index() {
        $stats = $this->laporanModel->getStatistikDashboard();
        
        $rekapCpl = $this->laporanModel->getRekapCplAngkatan();
        
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
