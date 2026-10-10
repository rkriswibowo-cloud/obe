<?php
// app/Controllers/TranskripController.php

require_once __DIR__ . '/../Models/KhsModel.php';
require_once __DIR__ . '/../Models/LaporanModel.php';
require_once __DIR__ . '/../Models/AturanKurikulumModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';

class TranskripController {
    private $khsModel;
    private $laporanModel;
    private $aturanModel;
    private $kurikulumModel;
    private $userModel;
    private $prodiModel;
    private $access;

    public function __construct() {
        $this->khsModel = new KhsModel();
        $this->laporanModel = new LaporanModel();
        $this->aturanModel = new AturanKurikulumModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->userModel = new UserModel();
        $this->prodiModel = new ProdiModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $userRole = $_SESSION['role'] ?? '';
        $currentUserId = $_SESSION['user_id'] ?? null;

        $daftarKurikulum = $this->laporanModel->getDaftarKurikulum();
        $daftarKurikulum = $this->access->filterKurikulumRows($daftarKurikulum);

        // 1. Tentukan Mahasiswa yang Dipilih
        if ($userRole === 'Mahasiswa') {
            $selectedMhsId = $currentUserId;
        } else {
            $selectedMhsId = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        }

        // 2. Filter Kurikulum & Angkatan
        $selectedKurikulumId = !empty($_GET['kurikulum_id']) ? (int)$_GET['kurikulum_id'] : null;
        if ($selectedKurikulumId && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKurikulum($selectedKurikulumId);
        $selectedAngkatan = $_GET['angkatan'] ?? '';

        // Jika ada mahasiswa dipilih, sesuaikan kurikulum otomatis jika belum diset
        if ($selectedMhsId && !$selectedKurikulumId) {
            $selectedKurikulumId = $this->laporanModel->getKurikulumIdForMahasiswa($selectedMhsId);
        }

        // Jika masih belum ada, default ke kurikulum dengan mahasiswa terbanyak
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

        $daftarAngkatan = $this->laporanModel->getAngkatanByKurikulum($selectedKurikulumId);
        if ($selectedAngkatan && !in_array((int)$selectedAngkatan, array_map('intval', $daftarAngkatan))) {
            $selectedAngkatan = '';
        }

        // Dapatkan mahasiswa sesuai kurikulum & angkatan
        $allMahasiswaInKurikulum = [];
        $allMhsRaw = $this->laporanModel->getAllMahasiswa($selectedAngkatan ?: null);
        foreach ($allMhsRaw as $m) {
            if ($this->laporanModel->getKurikulumIdForMahasiswa($m['id']) == $selectedKurikulumId) {
                $allMahasiswaInKurikulum[] = $m;
            }
        }

        // Jika mahasiswa terpilih tidak ada atau tidak cocok, pilih mahasiswa pertama dari kurikulum ini
        if ($userRole !== 'Mahasiswa') {
            $foundSelected = false;
            foreach ($allMahasiswaInKurikulum as $m) {
                if ($m['id'] == $selectedMhsId) {
                    $foundSelected = true;
                    break;
                }
            }
            if (!$foundSelected && !empty($allMahasiswaInKurikulum)) {
                $selectedMhsId = $allMahasiswaInKurikulum[0]['id'];
            }
        }

        // 3. Ambil Data Mahasiswa, Transkrip Akademik, dan CPL
        $mahasiswa = null;
        $kurikulumMhs = null;
        $khsData = null;
        $cplData = null;
        $radarPayload = [];
        $splitMk = ['left' => [], 'right' => []];

        if ($selectedMhsId) {
            if ($userRole !== 'Mahasiswa' && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($selectedMhsId);
            $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
            $angkatan = AturanKurikulumModel::extractAngkatan($mahasiswa['username'] ?? '');
            if ($angkatan) {
                $kurikulumMhs = $this->aturanModel->getKurikulumByAngkatan($angkatan);
            }
            $targetKid = $kurikulumMhs['id'] ?? $selectedKurikulumId;

            // Transkrip kumulatif (tanpa filter semester/ta_id agar semua MK masuk)
            $khsData = $this->khsModel->getKhsMahasiswa($selectedMhsId, null, $targetKid);
            $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);

            // Siapkan payload radar chart CPL
            $labels = [];
            $scores = [];
            $targets = [];
            $rumusans = [];
            if (!empty($cplData['cpl_list'])) {
                foreach ($cplData['cpl_list'] as $c) {
                    $labels[] = $c['kode'];
                    $scores[] = $c['is_measured'] ? $c['score'] : 0;
                    $targets[] = $c['target'];
                    $rumusans[] = $c['rumusan'];
                }
            }
            $radarPayload = [
                'labels' => $labels,
                'scores' => $scores,
                'targets' => $targets,
                'rumusans' => $rumusans
            ];

            // Split mata kuliah menjadi 2 kolom seimbang untuk layout transkrip A4 profesional
            if (!empty($khsData['semua_mk'])) {
                $totalMk = count($khsData['semua_mk']);
                $half = (int)ceil($totalMk / 2);
                $splitMk['left'] = array_slice($khsData['semua_mk'], 0, $half);
                $splitMk['right'] = array_slice($khsData['semua_mk'], $half);
            }
        }

        require __DIR__ . '/../../views/transkrip/index.php';
    }

    public function cetak() {
        $userRole = $_SESSION['role'] ?? '';
        $currentUserId = $_SESSION['user_id'] ?? null;

        $selectedMhsId = ($userRole === 'Mahasiswa')
            ? $currentUserId
            : (!empty($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$selectedMhsId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/transkrip');
            exit;
        }
        if ($userRole !== 'Mahasiswa' && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($selectedMhsId);

        $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
        if (!$mahasiswa) {
            echo "Mahasiswa tidak ditemukan.";
            exit;
        }

        $angkatan = AturanKurikulumModel::extractAngkatan($mahasiswa['username'] ?? '');
        $kurikulumMhs = $angkatan ? $this->aturanModel->getKurikulumByAngkatan($angkatan) : null;
        $targetKid = $kurikulumMhs['id'] ?? null;
        $pejabat = !empty($kurikulumMhs['prodi_id']) ? $this->prodiModel->getById($kurikulumMhs['prodi_id']) : null;

        $khsData = $this->khsModel->getKhsMahasiswa($selectedMhsId, null, $targetKid);
        $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);

        // Siapkan payload radar chart CPL
        $labels = [];
        $scores = [];
        $targets = [];
        $rumusans = [];
        if (!empty($cplData['cpl_list'])) {
            foreach ($cplData['cpl_list'] as $c) {
                $labels[] = $c['kode'];
                $scores[] = $c['is_measured'] ? $c['score'] : 0;
                $targets[] = $c['target'];
                $rumusans[] = $c['rumusan'];
            }
        }
        $radarPayload = [
            'labels' => $labels,
            'scores' => $scores,
            'targets' => $targets,
            'rumusans' => $rumusans
        ];

        // Split mata kuliah untuk 2 kolom transkrip A4
        $splitMk = ['left' => [], 'right' => []];
        if (!empty($khsData['semua_mk'])) {
            $totalMk = count($khsData['semua_mk']);
            $half = (int)ceil($totalMk / 2);
            $splitMk['left'] = array_slice($khsData['semua_mk'], 0, $half);
            $splitMk['right'] = array_slice($khsData['semua_mk'], $half);
        }

        require __DIR__ . '/../../views/transkrip/cetak.php';
    }
}
