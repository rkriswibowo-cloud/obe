<?php
// app/Controllers/AsesmenController.php

require_once __DIR__ . '/../Models/KelasModel.php';
require_once __DIR__ . '/../Models/AsesmenModel.php';
require_once __DIR__ . '/../Models/RubrikModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class AsesmenController {
    private $kelasModel;
    private $asesmenModel;
    private $rubrikModel;
    private $auditModel;

    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->asesmenModel = new AsesmenModel();
        $this->rubrikModel = new RubrikModel();
        $this->auditModel = new AuditLogModel();
    }

    public function index() {
        // Dropdown filter kelas
        $kelasList = $this->kelasModel->getAll();
        
        $kelas_id = $_GET['kelas_id'] ?? null;
        
        $kelas = null;
        $peserta = [];
        $sub_cpmk_list = [];
        $nilai_matrix = [];
        $rubrikData = [];

        if ($kelas_id) {
            $kelas = $this->kelasModel->getById($kelas_id);
            if ($kelas) {
                $peserta = $this->kelasModel->getPeserta($kelas_id);
                $sub_cpmk_list = $this->asesmenModel->getDaftarSubCpmk($kelas['mata_kuliah_id']);
                $nilai_matrix = $this->asesmenModel->getNilaiKelas($kelas_id);

                // Load all rubrics with full detail for interactive rubric scoring
                $allRubriks = $this->rubrikModel->getAll();
                foreach ($allRubriks as $r) {
                    $rubrikDetail = $this->rubrikModel->getById($r['id']);
                    if ($rubrikDetail) {
                        $rubrikData[] = $rubrikDetail;
                    }
                }
            }
        }

        require __DIR__ . '/../../views/asesmen/index.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kelas_id = (int)$_POST['kelas_id'];
            $nilai_data = $_POST['nilai'] ?? []; // format: nilai[mahasiswa_id][sub_cpmk_id] = skor
            $savedCount = 0;

            foreach ($nilai_data as $mahasiswa_id => $sub_cpmks) {
                foreach ($sub_cpmks as $sub_cpmk_id => $skor) {
                    // Only save if skor is explicitly set (even 0)
                    if ($skor !== '') {
                        $this->asesmenModel->saveNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, (float)$skor);
                        $savedCount++;
                    }
                }
            }

            $kelas = $this->kelasModel->getById($kelas_id);
            $namaKelas = $kelas ? ($kelas['mata_kuliah_kode'] . ' - ' . $kelas['nama_kelas']) : "Kelas #$kelas_id";
            $this->auditModel->log('ASESMEN', 'INPUT', "Menyimpan $savedCount skor asesmen mahasiswa untuk $namaKelas");

            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/asesmen?kelas_id=' . $kelas_id . '&success=1');
            exit;
        }
    }
}
