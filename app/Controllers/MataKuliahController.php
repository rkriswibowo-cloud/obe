<?php
// app/Controllers/MataKuliahController.php

class MataKuliahController {
    private $mkModel;
    private $kurikulumModel;

    public function __construct() {
        require_once __DIR__ . '/../Models/MataKuliahModel.php';
        require_once __DIR__ . '/../Models/KurikulumModel.php';
        $this->mkModel = new MataKuliahModel();
        $this->kurikulumModel = new KurikulumModel();
    }

    public function index() {
        $filters = [
            'semester' => $_GET['semester'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $totalData = $this->mkModel->countAll($filters);
        $totalPages = ceil($totalData / $limit);

        $mkList = $this->mkModel->getAll($filters, $limit, $offset);
        require_once __DIR__ . '/../../views/mata_kuliah/index.php';
    }

    public function create() {
        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/mata_kuliah/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                'kode' => $_POST['kode'] ?? '',
                'nama' => $_POST['nama'] ?? '',
                'sks_teori' => $_POST['sks_teori'] ?? 0,
                'sks_praktek' => $_POST['sks_praktek'] ?? 0,
                'semester' => $_POST['semester'] ?? 1,
                'sifat' => $_POST['sifat'] ?? 'Wajib',
                'kelompok_keilmuan' => $_POST['kelompok_keilmuan'] ?? '',
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'status' => $_POST['status'] ?? 'ACTIVE'
            ];

            if ($this->mkModel->create($data)) {
                $_SESSION['success'] = "Mata Kuliah berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan Mata Kuliah.";
            }
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $this->mkModel->getById($id);
        if (!$mk) {
            $_SESSION['error'] = "Data Mata Kuliah tidak ditemukan.";
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/mata_kuliah/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                $data = [
                    'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                    'kode' => $_POST['kode'] ?? '',
                    'nama' => $_POST['nama'] ?? '',
                    'sks_teori' => $_POST['sks_teori'] ?? 0,
                    'sks_praktek' => $_POST['sks_praktek'] ?? 0,
                    'semester' => $_POST['semester'] ?? 1,
                    'sifat' => $_POST['sifat'] ?? 'Wajib',
                    'kelompok_keilmuan' => $_POST['kelompok_keilmuan'] ?? '',
                    'deskripsi' => $_POST['deskripsi'] ?? '',
                    'status' => $_POST['status'] ?? 'ACTIVE'
                ];

                if ($this->mkModel->update($id, $data)) {
                    $_SESSION['success'] = "Mata Kuliah berhasil diperbarui.";
                } else {
                    $_SESSION['error'] = "Gagal memperbarui Mata Kuliah.";
                }
            }
            header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->mkModel->delete($id)) {
                $_SESSION['success'] = "Mata Kuliah berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus Mata Kuliah.";
            }
        }
        header('Location: ' . ($config['base_url'] ?? '/obe/public') . '/mata-kuliah');
        exit;
    }
}
