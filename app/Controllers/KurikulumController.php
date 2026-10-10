<?php
// app/Controllers/KurikulumController.php
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';

class KurikulumController {
    private $kurikulumModel;
    private $prodiModel;
    private $access;

    public function __construct() {
        $this->kurikulumModel = new KurikulumModel();
        $this->prodiModel = new ProdiModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $selectedProdiId = $_GET['prodi_id'] ?? ($this->access->isKaprodi() ? $this->access->primaryManagedProdiId() : null);
        if ($selectedProdiId && !$this->access->isSuperAdmin()) $this->access->assertProdi($selectedProdiId);
        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        $kurikulumList = $this->kurikulumModel->getAll($selectedProdiId);
        require_once __DIR__ . '/../../views/kurikulum/index.php';
    }

    public function create() {
        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        require_once __DIR__ . '/../../views/kurikulum/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->access->isSuperAdmin()) $this->access->assertProdi($_POST['prodi_id'] ?? 0);
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

        if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($id);
        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        require_once __DIR__ . '/../../views/kurikulum/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            if (!$this->access->isSuperAdmin()) {
                $this->access->assertKurikulum($id);
                $this->access->assertProdi($_POST['prodi_id'] ?? 0);
            }
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
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($id);
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
