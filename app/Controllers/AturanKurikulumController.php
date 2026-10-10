<?php
// app/Controllers/AturanKurikulumController.php
require_once __DIR__ . '/../Models/AturanKurikulumModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';

class AturanKurikulumController {
    private $model;
    private $kurikulumModel;
    private $access;

    public function __construct() {
        $this->model = new AturanKurikulumModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $aturan = $this->model->getAll();
        if (!$this->access->isSuperAdmin()) $aturan = array_values(array_filter($aturan, fn($row) => $this->access->canAccessKurikulum($row['kurikulum_id'])));
        $distribusi = $this->model->getDistribusiMahasiswa();
        if (!$this->access->isSuperAdmin()) $distribusi = array_values(array_filter($distribusi, fn($row) => !empty($row['kurikulum_id']) && $this->access->canAccessKurikulum($row['kurikulum_id'])));
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        require __DIR__ . '/../../views/aturan-kurikulum/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($_POST['kurikulum_id'] ?? 0);
            $data = [
                'angkatan_mulai' => (int)$_POST['angkatan_mulai'],
                'angkatan_selesai' => !empty($_POST['angkatan_selesai']) ? (int)$_POST['angkatan_selesai'] : null,
                'kurikulum_id' => (int)$_POST['kurikulum_id'],
                'keterangan' => $_POST['keterangan'] ?? '',
                'prioritas' => (int)($_POST['prioritas'] ?? 0)
            ];

            try {
                $this->model->create($data);
                $_SESSION['flash_success'] = 'Aturan kurikulum berhasil ditambahkan.';
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Gagal menambahkan aturan: ' . $e->getMessage();
            }
        }
        header('Location: /obe/public/aturan-kurikulum');
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $existing = $this->model->getById($id);
            if (!$this->access->isSuperAdmin()) {
                if (!$existing) $this->access->forbidden();
                $this->access->assertKurikulum($existing['kurikulum_id']);
                $this->access->assertKurikulum($_POST['kurikulum_id'] ?? 0);
            }
            $data = [
                'angkatan_mulai' => (int)$_POST['angkatan_mulai'],
                'angkatan_selesai' => !empty($_POST['angkatan_selesai']) ? (int)$_POST['angkatan_selesai'] : null,
                'kurikulum_id' => (int)$_POST['kurikulum_id'],
                'keterangan' => $_POST['keterangan'] ?? '',
                'prioritas' => (int)($_POST['prioritas'] ?? 0)
            ];

            try {
                $this->model->update($id, $data);
                $_SESSION['flash_success'] = 'Aturan kurikulum berhasil diperbarui.';
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Gagal memperbarui aturan: ' . $e->getMessage();
            }
        }
        header('Location: /obe/public/aturan-kurikulum');
        exit;
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $existing = $this->model->getById($id);
            if (!$this->access->isSuperAdmin()) {
                if (!$existing) $this->access->forbidden();
                $this->access->assertKurikulum($existing['kurikulum_id']);
            }
            try {
                $this->model->delete($id);
                $_SESSION['flash_success'] = 'Aturan kurikulum berhasil dihapus.';
            } catch (Exception $e) {
                $_SESSION['flash_error'] = 'Gagal menghapus aturan: ' . $e->getMessage();
            }
        }
        header('Location: /obe/public/aturan-kurikulum');
        exit;
    }
}
