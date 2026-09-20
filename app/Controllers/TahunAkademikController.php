<?php
require_once __DIR__ . '/../Models/TahunAkademikModel.php';

class TahunAkademikController {
    private $model;

    public function __construct() {
        $this->model = new TahunAkademikModel();
    }

    public function index() {
        $tahun_akademik = $this->model->getAll();
        require __DIR__ . '/../../views/tahun_akademik/index.php';
    }

    public function create() {
        require __DIR__ . '/../../views/tahun_akademik/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama' => $_POST['nama'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            $this->model->create($data);
            header('Location: /obe/public/tahun-akademik');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $ta = $this->model->getById($id);
            if ($ta) {
                require __DIR__ . '/../../views/tahun_akademik/form.php';
                return;
            }
        }
        header('Location: /obe/public/tahun-akademik');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'nama' => $_POST['nama'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            $this->model->update($id, $data);
            header('Location: /obe/public/tahun-akademik');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->delete($id);
        }
        header('Location: /obe/public/tahun-akademik');
    }
}
