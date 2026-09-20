<?php
// app/Controllers/KurikulumController.php
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';

class KurikulumController {
    private $kurikulumModel;
    private $prodiModel;

    public function __construct() {
        $this->kurikulumModel = new KurikulumModel();
        $this->prodiModel = new ProdiModel();
    }

    public function index() {
        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/kurikulum/index.php';
    }

    public function create() {
        $prodiList = $this->prodiModel->getAll();
        require_once __DIR__ . '/../../views/kurikulum/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'prodi_id' => $_POST['prodi_id'],
                'kode' => $_POST['kode'],
                'nama' => $_POST['nama'],
                'tahun_mulai' => $_POST['tahun_mulai'],
                'versi' => $_POST['versi'],
                'status' => $_POST['status'] ?? 'DRAFT'
            ];
            
            if ($this->kurikulumModel->create($data)) {
                $_SESSION['success'] = "Kurikulum berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan kurikulum.";
            }
            header("Location: /obe/public/kurikulum");
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /obe/public/kurikulum");
            exit;
        }

        $kurikulum = $this->kurikulumModel->getById($id);
        if (!$kurikulum) {
            header("Location: /obe/public/kurikulum");
            exit;
        }

        $prodiList = $this->prodiModel->getAll();
        require_once __DIR__ . '/../../views/kurikulum/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'prodi_id' => $_POST['prodi_id'],
                'kode' => $_POST['kode'],
                'nama' => $_POST['nama'],
                'tahun_mulai' => $_POST['tahun_mulai'],
                'versi' => $_POST['versi'],
                'status' => $_POST['status'] ?? 'DRAFT'
            ];
            
            if ($this->kurikulumModel->update($id, $data)) {
                $_SESSION['success'] = "Kurikulum berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui kurikulum.";
            }
            header("Location: /obe/public/kurikulum");
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->kurikulumModel->delete($id)) {
                $_SESSION['success'] = "Kurikulum berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus kurikulum. Mungkin masih ada data yang berelasi.";
            }
        }
        header("Location: /obe/public/kurikulum");
        exit;
    }
}
