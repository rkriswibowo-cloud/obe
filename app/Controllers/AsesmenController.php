<?php
require_once __DIR__ . '/../Models/KelasModel.php';
require_once __DIR__ . '/../Models/AsesmenModel.php';

class AsesmenController {
    private $kelasModel;
    private $asesmenModel;

    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->asesmenModel = new AsesmenModel();
    }

    public function index() {
        // Dropdown filter kelas
        $kelasList = $this->kelasModel->getAll();
        
        $kelas_id = $_GET['kelas_id'] ?? null;
        
        $kelas = null;
        $peserta = [];
        $sub_cpmk_list = [];
        $nilai_matrix = [];

        if ($kelas_id) {
            $kelas = $this->kelasModel->getById($kelas_id);
            if ($kelas) {
                $peserta = $this->kelasModel->getPeserta($kelas_id);
                $sub_cpmk_list = $this->asesmenModel->getDaftarSubCpmk($kelas['mata_kuliah_id']);
                $nilai_matrix = $this->asesmenModel->getNilaiKelas($kelas_id);
            }
        }

        require __DIR__ . '/../../views/asesmen/index.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kelas_id = $_POST['kelas_id'];
            $nilai_data = $_POST['nilai'] ?? []; // format: nilai[mahasiswa_id][sub_cpmk_id] = skor

            foreach ($nilai_data as $mahasiswa_id => $sub_cpmks) {
                foreach ($sub_cpmks as $sub_cpmk_id => $skor) {
                    // Only save if skor is explicitly set (even 0)
                    if ($skor !== '') {
                        $this->asesmenModel->saveNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, $skor);
                    }
                }
            }

            header('Location: /obe/public/asesmen?kelas_id=' . $kelas_id . '&success=1');
        }
    }
}
