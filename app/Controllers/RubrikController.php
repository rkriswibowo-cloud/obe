<?php
require_once __DIR__ . '/../Models/RubrikModel.php';

class RubrikController {
    private $rubrikModel;

    public function __construct() {
        $this->rubrikModel = new RubrikModel();
    }

    public function index() {
        $rubrikList = $this->rubrikModel->getAll();
        $jenisRubrikMaster = RubrikModel::getJenisMaster();
        require __DIR__ . '/../../views/rubrik/index.php';
    }

    public function create() {
        $jenisRubrikMaster = RubrikModel::getJenisMaster();
        require __DIR__ . '/../../views/rubrik/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = trim($_POST['nama'] ?? '');
            $kodeJenis = $_POST['kode_jenis'] ?? 'RB-ANA';
            $status = $_POST['status'] ?? 'ACTIVE';

            // Levels: array of ['nama' => ..., 'skor' => ...]
            $rawLevelNama = $_POST['level_nama'] ?? [];
            $rawLevelSkor = $_POST['level_skor'] ?? [];
            $levels = [];
            foreach ($rawLevelNama as $idx => $lvlNama) {
                if (trim($lvlNama) !== '') {
                    $levels[] = [
                        'nama' => trim($lvlNama),
                        'skor' => floatval($rawLevelSkor[$idx] ?? 0)
                    ];
                }
            }

            // Kriterias: array of ['nama' => ..., 'bobot' => ...]
            $rawKriteriaNama = $_POST['kriteria_nama'] ?? [];
            $rawKriteriaBobot = $_POST['kriteria_bobot'] ?? [];
            $kriterias = [];
            foreach ($rawKriteriaNama as $kIdx => $kritNama) {
                if (trim($kritNama) !== '') {
                    $kriterias[] = [
                        'nama' => trim($kritNama),
                        'bobot' => floatval($rawKriteriaBobot[$kIdx] ?? 0)
                    ];
                }
            }

            // Descriptors: descriptors[kIdx][lIdx] = text
            $descriptors = $_POST['deskriptor'] ?? [];

            if (empty($nama) || !isset(RubrikModel::getJenisMaster()[$kodeJenis]) || empty($levels) || empty($kriterias)) {
                $error = "Nama rubrik, minimal 1 level nilai, dan minimal 1 kriteria wajib diisi.";
                $jenisRubrikMaster = RubrikModel::getJenisMaster();
                require __DIR__ . '/../../views/rubrik/create.php';
                return;
            }

            $rubrikId = $this->rubrikModel->create($nama, $kodeJenis, $status, $levels, $kriterias, $descriptors);
            if ($rubrikId) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik/detail?id=' . $rubrikId . '&success=created');
                exit;
            } else {
                $error = "Gagal menyimpan rubrik ke database.";
                $jenisRubrikMaster = RubrikModel::getJenisMaster();
                require __DIR__ . '/../../views/rubrik/create.php';
            }
        }
    }

    public function detail() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik');
            exit;
        }

        $rubrik = $this->rubrikModel->getById($id);
        if (!$rubrik) {
            echo "Rubrik tidak ditemukan.";
            exit;
        }

        require __DIR__ . '/../../views/rubrik/detail.php';
    }

    public function builder() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik');
            exit;
        }

        $rubrik = $this->rubrikModel->getById($id);
        if (!$rubrik) {
            echo "Rubrik tidak ditemukan.";
            exit;
        }

        $levels = $rubrik['levels'];
        $kriterias = $rubrik['kriteria'];
        $deskriptors = $rubrik['descriptors'];

        require __DIR__ . '/../../views/rubrik/builder.php';
    }

    public function saveMatrix() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rubrikId = $_POST['rubrik_id'] ?? null;
            if (!$rubrikId) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik');
                exit;
            }

            $levels = $_POST['levels'] ?? [];
            $kriterias = $_POST['kriterias'] ?? [];
            $deskriptors = $_POST['deskriptors'] ?? [];

            $success = $this->rubrikModel->saveMatrix($rubrikId, $levels, $kriterias, $deskriptors);
            if ($success) {
                $_SESSION['success'] = "Matriks kriteria dan deskriptor rubrik berhasil disimpan!";
            } else {
                $_SESSION['error'] = "Terjadi kesalahan saat menyimpan matriks rubrik.";
            }

            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik/builder?id=' . $rubrikId);
            exit;
        }
    }

    public function duplicate() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $newId = $this->rubrikModel->duplicate($id);
            if ($newId) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik/detail?id=' . $newId . '&success=duplicated');
                exit;
            }
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik');
        exit;
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->rubrikModel->delete($id);
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rubrik?success=deleted');
        exit;
    }
}
