<?php
// app/Controllers/LaporanController.php

require_once __DIR__ . '/../Models/LaporanModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/AturanKurikulumModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';

class LaporanController {
    private $laporanModel;
    private $userModel;
    private $aturanModel;
    private $kurikulumModel;
    private $access;

    public function __construct() {
        $this->laporanModel = new LaporanModel();
        $this->userModel = new UserModel();
        $this->aturanModel = new AturanKurikulumModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->access = new AccessContext();
    }

    public function index() {
        // Jika login sebagai mahasiswa, langsung lihat laporannya sendiri
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Mahasiswa') {
            $mahasiswa_id = $_SESSION['user_id'];
            $mahasiswa = $this->userModel->getById($mahasiswa_id);
            $kurikulumId = $this->laporanModel->getKurikulumIdForMahasiswa($mahasiswa_id);
            $kurikulum = $kurikulumId ? $this->kurikulumModel->getById($kurikulumId) : null;
            $cpl_data = $this->laporanModel->getKetercapaianCplMahasiswa($mahasiswa_id);
            $traceability = $this->laporanModel->getTraceabilityDetail($mahasiswa_id);
            
            // Format radar chart data
            $radarData = $this->buildRadarPayload($cpl_data);
            require __DIR__ . '/../../views/laporan/detail.php';
            return;
        }

        // Jika Dosen / Admin: Tampilkan Dashboard Ketercapaian CPL per Kurikulum
        $daftarKurikulum = $this->laporanModel->getDaftarKurikulum();
        $daftarKurikulum = $this->access->filterKurikulumRows($daftarKurikulum);

        // Tentukan kurikulum yang dipilih
        $selectedKurikulumId = null;
        if (!empty($_GET['kurikulum_id'])) {
            $selectedKurikulumId = (int)$_GET['kurikulum_id'];
            if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKurikulum($selectedKurikulumId);
        } elseif (!empty($_GET['angkatan'])) {
            $selectedKurikulumId = $this->aturanModel->getKurikulumIdByAngkatan((int)$_GET['angkatan']);
        }

        // Jika tidak ditentukan, pilih kurikulum yang memiliki mahasiswa terbanyak / kurikulum aktif
        if (!$selectedKurikulumId) {
            $bestKid = null;
            $maxMhs = -1;
            foreach ($daftarKurikulum as $k) {
                if ($k['total_mahasiswa'] > $maxMhs) {
                    $maxMhs = $k['total_mahasiswa'];
                    $bestKid = $k['id'];
                }
            }
            $selectedKurikulumId = $bestKid ?? ($daftarKurikulum[0]['id'] ?? 1);
        }

        // Cari kurikulum yang aktif
        $selectedKurikulum = null;
        foreach ($daftarKurikulum as $k) {
            if ($k['id'] == $selectedKurikulumId) {
                $selectedKurikulum = $k;
                break;
            }
        }
        if (!$selectedKurikulum && !empty($daftarKurikulum)) {
            $selectedKurikulum = $daftarKurikulum[0];
            $selectedKurikulumId = $selectedKurikulum['id'];
        }

        // Dapatkan daftar angkatan yang terikat dengan kurikulum ini
        $daftarAngkatan = $this->laporanModel->getAngkatanByKurikulum($selectedKurikulumId);

        // Filter angkatan spesifik jika dipilih
        $selectedAngkatan = $_GET['angkatan'] ?? '';
        if ($selectedAngkatan && !in_array((int)$selectedAngkatan, array_map('intval', $daftarAngkatan))) {
            $selectedAngkatan = '';
        }

        // Ambil rekap CPL dan daftar mahasiswa untuk kurikulum & angkatan terpilih
        $dataKurikulum = $this->laporanModel->getRekapCplKurikulum($selectedKurikulumId, $selectedAngkatan ?: null);
        $rekapAngkatan = $dataKurikulum['rekap'];
        $mahasiswaList = $dataKurikulum['mahasiswa'];

        // Format radar chart data untuk Dashboard Kurikulum
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

        // Hitung ringkasan kurikulum
        $totalCpl = count($rekapAngkatan);
        $cplTercapai = count(array_filter($rekapAngkatan, fn($r) => $r['status'] === 'Tercapai'));
        $cplBelumTercapai = count(array_filter($rekapAngkatan, fn($r) => $r['status'] === 'Belum Tercapai'));
        $cplBelumDiukur = count(array_filter($rekapAngkatan, fn($r) => $r['status'] === 'Belum Diukur'));
        
        $measuredScores = array_filter($chartScores, fn($s) => $s > 0);
        $avgAllCpl = count($measuredScores) > 0 ? round(array_sum($measuredScores) / count($measuredScores), 2) : 0;

        require __DIR__ . '/../../views/laporan/index.php';
    }

    public function detail() {
        // Cek ID Mahasiswa dari parameter
        $mahasiswa_id = ($_SESSION['role'] ?? '') === 'Mahasiswa'
            ? ($_SESSION['user_id'] ?? null)
            : ($_GET['id'] ?? null);
        if (($_SESSION['role'] ?? '') !== 'Mahasiswa' && $mahasiswa_id && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($mahasiswa_id);
        if (!$mahasiswa_id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/laporan');
            exit;
        }

        $mahasiswa = $this->userModel->getById($mahasiswa_id);
        if (!$mahasiswa) {
            echo "Mahasiswa tidak ditemukan.";
            exit;
        }

        $kurikulumId = $this->laporanModel->getKurikulumIdForMahasiswa($mahasiswa_id);
        $kurikulum = $kurikulumId ? $this->kurikulumModel->getById($kurikulumId) : null;

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
