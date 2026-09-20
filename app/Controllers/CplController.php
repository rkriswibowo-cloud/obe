<?php
// app/Controllers/CplController.php

class CplController {
    private $cplModel;
    private $kurikulumModel;

    public function __construct() {
        require_once __DIR__ . '/../Models/CplModel.php';
        require_once __DIR__ . '/../Models/KurikulumModel.php';
        $this->cplModel = new CplModel();
        $this->kurikulumModel = new KurikulumModel();
    }

    public function index() {
        $cplList = $this->cplModel->getAll();
        require_once __DIR__ . '/../../views/cpl/index.php';
    }

    public function create() {
        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/cpl/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                'kode' => $_POST['kode'] ?? '',
                'rumusan' => $_POST['rumusan'] ?? '',
                'kategori' => $_POST['kategori'] ?? '',
                'target_capaian' => $_POST['target_capaian'] ?? 70.00,
                'status' => $_POST['status'] ?? 'ACTIVE'
            ];

            if ($this->cplModel->create($data)) {
                $_SESSION['success'] = "CPL berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan CPL.";
            }
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/cpl');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/cpl');
            exit;
        }

        $cpl = $this->cplModel->getById($id);
        if (!$cpl) {
            $_SESSION['error'] = "Data CPL tidak ditemukan.";
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/cpl');
            exit;
        }

        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/cpl/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $data = [
                    'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                    'kode' => $_POST['kode'] ?? '',
                    'rumusan' => $_POST['rumusan'] ?? '',
                    'kategori' => $_POST['kategori'] ?? '',
                    'target_capaian' => $_POST['target_capaian'] ?? 70.00,
                    'status' => $_POST['status'] ?? 'ACTIVE'
                ];

                if ($this->cplModel->update($id, $data)) {
                    $_SESSION['success'] = "CPL berhasil diperbarui.";
                } else {
                    $_SESSION['error'] = "Gagal memperbarui CPL.";
                }
            }
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/cpl');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->cplModel->delete($id)) {
                $_SESSION['success'] = "CPL berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus CPL.";
            }
        }
        header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/cpl');
        exit;
    }
}
