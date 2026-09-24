<?php
// app/Controllers/KhsController.php
require_once __DIR__ . '/../Models/KhsModel.php';

class KhsController {
    private $khsModel;

    public function __construct() {
        $this->khsModel = new KhsModel();
    }

    public function index() {
        $userRole = $_SESSION['role'] ?? '';
        $currentUserId = $_SESSION['user_id'] ?? null;

        // Jika mahasiswa, kunci ke ID dirinya sendiri
        if ($userRole === 'Mahasiswa') {
            $selectedMhsId = $currentUserId;
        } else {
            // Admin, Dosen, atau Kaprodi bisa memilih mahasiswa
            $selectedMhsId = $_GET['id'] ?? null;
        }

        $daftarMahasiswa = $this->khsModel->getAllMahasiswa();
        $daftarTahunAkademik = $this->khsModel->getAllTahunAkademik();
        $filterTaId = $_GET['ta_id'] ?? null;

        // Default ke mahasiswa pertama jika belum ada yang dipilih
        if (!$selectedMhsId && !empty($daftarMahasiswa)) {
            $selectedMhsId = $daftarMahasiswa[0]['id'];
        }

        $mahasiswa = null;
        $khsData = null;
        $cplData = null;
        $radarPayload = [];

        if ($selectedMhsId) {
            $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
            $khsData = $this->khsModel->getKhsMahasiswa($selectedMhsId, $filterTaId);
            $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);

            // Format data untuk Radar Chart CPL
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
        }

        require __DIR__ . '/../../views/khs/index.php';
    }

    public function cetak() {
        $selectedMhsId = $_GET['id'] ?? null;
        $filterTaId = $_GET['ta_id'] ?? null;

        if (!$selectedMhsId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/khs');
            exit;
        }

        $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
        if (!$mahasiswa) {
            echo "Mahasiswa tidak ditemukan.";
            exit;
        }

        $khsData = $this->khsModel->getKhsMahasiswa($selectedMhsId, $filterTaId);
        $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);

        // Format data Radar Chart
        $labels = [];
        $scores = [];
        $targets = [];
        if (!empty($cplData['cpl_list'])) {
            foreach ($cplData['cpl_list'] as $c) {
                $labels[] = $c['kode'];
                $scores[] = $c['is_measured'] ? $c['score'] : 0;
                $targets[] = $c['target'];
            }
        }

        $radarPayload = [
            'labels' => $labels,
            'scores' => $scores,
            'targets' => $targets
        ];

        require __DIR__ . '/../../views/khs/cetak.php';
    }
}
