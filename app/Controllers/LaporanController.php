<?php
// app/Controllers/LaporanController.php

require_once __DIR__ . '/../Models/LaporanModel.php';
require_once __DIR__ . '/../Models/UserModel.php';

class LaporanController {
    private $laporanModel;
    private $userModel;

    public function __construct() {
        $this->laporanModel = new LaporanModel();
        $this->userModel = new UserModel();
    }

    public function index() {
        // Jika login sebagai mahasiswa, langsung lihat laporannya sendiri
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Mahasiswa') {
            $mahasiswa_id = $_SESSION['user_id'];
            $mahasiswa = $this->userModel->getById($mahasiswa_id);
            $cpl_data = $this->laporanModel->getKetercapaianCplMahasiswa($mahasiswa_id);
            $traceability = $this->laporanModel->getTraceabilityDetail($mahasiswa_id);
            
            // Format radar chart data
            $radarData = $this->buildRadarPayload($cpl_data);
            require __DIR__ . '/../../views/laporan/detail.php';
            return;
        }

        // Jika Dosen / Admin: Tampilkan Dashboard Angkatan + Daftar Mahasiswa
        $selectedAngkatan = $_GET['angkatan'] ?? '';
        $daftarAngkatan = $this->laporanModel->getDaftarAngkatan();
        $mahasiswaList = $this->laporanModel->getAllMahasiswa($selectedAngkatan ?: null);
        $rekapAngkatan = $this->laporanModel->getRekapCplAngkatan($selectedAngkatan ?: null);

        // Format radar chart data untuk Dashboard Angkatan
        $chartLabels = [];
        $chartScores = [];
        $chartTargets = [];
        $chartRumusans = [];

        foreach ($rekapAngkatan as $r) {
            $chartLabels[] = $r['kode'];
            $chartScores[] = $r['avg_score'];
            $chartTargets[] = $r['target'];
            $chartRumusans[] = $r['rumusan'];
        }

        $radarAngkatanPayload = [
            'labels' => $chartLabels,
            'scores' => $chartScores,
            'targets' => $chartTargets,
            'rumusans' => $chartRumusans
        ];

        // Hitung ringkasan angkatan
        $totalCpl = count($rekapAngkatan);
        $cplTercapai = count(array_filter($rekapAngkatan, fn($r) => $r['status'] === 'Tercapai'));
        $cplBelumTercapai = count(array_filter($rekapAngkatan, fn($r) => $r['status'] === 'Belum Tercapai'));
        $avgAllCpl = $totalCpl > 0 ? round(array_sum($chartScores) / $totalCpl, 2) : 0;

        require __DIR__ . '/../../views/laporan/index.php';
    }

    public function detail() {
        // Cek ID Mahasiswa dari parameter
        $mahasiswa_id = $_GET['id'] ?? null;
        if (!$mahasiswa_id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/laporan');
            exit;
        }

        $mahasiswa = $this->userModel->getById($mahasiswa_id);
        if (!$mahasiswa) {
            echo "Mahasiswa tidak ditemukan.";
            exit;
        }

        $cpl_data = $this->laporanModel->getKetercapaianCplMahasiswa($mahasiswa_id);
        $traceability = $this->laporanModel->getTraceabilityDetail($mahasiswa_id);
        $radarData = $this->buildRadarPayload($cpl_data);

        // Ringkasan ketercapaian
        $totalCpl = count($cpl_data);
        $tercapaiCount = count(array_filter($cpl_data, fn($c) => $c['status'] === 'Tercapai'));
        $belumTercapaiCount = count(array_filter($cpl_data, fn($c) => $c['status'] === 'Belum Tercapai'));
        $belumDiukurCount = count(array_filter($cpl_data, fn($c) => $c['status'] === 'Belum Diukur'));

        require __DIR__ . '/../../views/laporan/detail.php';
    }

    private function buildRadarPayload($cpl_data) {
        $labels = [];
        $scores = [];
        $targets = [];
        $rumusans = [];

        foreach ($cpl_data as $c) {
            $labels[] = $c['kode'];
            $scores[] = $c['is_measured'] ? $c['score'] : 0;
            $targets[] = $c['target'] ?? 70.00;
            $rumusans[] = $c['rumusan'];
        }

        return [
            'labels' => $labels,
            'scores' => $scores,
            'targets' => $targets,
            'rumusans' => $rumusans
        ];
    }
}
