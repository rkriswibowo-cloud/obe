<?php
// app/Controllers/MataKuliahController.php

require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class MataKuliahController {
    private $mkModel;
    private $kurikulumModel;
    private $prodiModel;
    private $auditModel;

    public function __construct() {
        $this->mkModel = new MataKuliahModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->prodiModel = new ProdiModel();
        $this->auditModel = new AuditLogModel();
    }

    public function index() {
        $selectedProdiId = $_GET['prodi_id'] ?? '';
        $selectedKurikulumId = $_GET['kurikulum_id'] ?? '';

        $filters = [
            'prodi_id' => $selectedProdiId,
            'kurikulum_id' => $selectedKurikulumId,
            'semester' => $_GET['semester'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $totalData = $this->mkModel->countAll($filters);
        $totalPages = ceil($totalData / $limit);

        $prodiList = $this->prodiModel->getAll();
        $kurikulumList = $this->kurikulumModel->getAll($selectedProdiId ?: null);
        $mkList = $this->mkModel->getAll($filters, $limit, $offset);

        require_once __DIR__ . '/../../views/mata_kuliah/index.php';
    }

    public function create() {
        $prodiList = $this->prodiModel->getAll();
        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/mata_kuliah/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                'kode' => strtoupper(trim($_POST['kode'] ?? '')),
                'nama' => trim($_POST['nama'] ?? ''),
                'sks_teori' => (int)($_POST['sks_teori'] ?? 0),
                'sks_praktek' => (int)($_POST['sks_praktek'] ?? 0),
                'semester' => (int)($_POST['semester'] ?? 1),
                'sifat' => $_POST['sifat'] ?? 'Wajib',
                'kelompok_keilmuan' => trim($_POST['kelompok_keilmuan'] ?? ''),
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'status' => $_POST['status'] ?? 'ACTIVE'
            ];

            if ($this->mkModel->create($data)) {
                $this->auditModel->log('MATA_KULIAH', 'CREATE', "Menambahkan mata kuliah: {$data['kode']} - {$data['nama']}");
                $_SESSION['success'] = "Mata Kuliah berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan Mata Kuliah.";
            }
            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/mata-kuliah');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        if (!$id) {
            header('Location: ' . $baseUrl . '/mata-kuliah');
            exit;
        }

        $mk = $this->mkModel->getById($id);
        if (!$mk) {
            header('Location: ' . $baseUrl . '/mata-kuliah');
            exit;
        }

        $prodiList = $this->prodiModel->getAll();
        $kurikulumList = $this->kurikulumModel->getAll();
        require_once __DIR__ . '/../../views/mata_kuliah/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $data = [
                'kurikulum_id' => $_POST['kurikulum_id'] ?? '',
                'kode' => strtoupper(trim($_POST['kode'] ?? '')),
                'nama' => trim($_POST['nama'] ?? ''),
                'sks_teori' => (int)($_POST['sks_teori'] ?? 0),
                'sks_praktek' => (int)($_POST['sks_praktek'] ?? 0),
                'semester' => (int)($_POST['semester'] ?? 1),
                'sifat' => $_POST['sifat'] ?? 'Wajib',
                'kelompok_keilmuan' => trim($_POST['kelompok_keilmuan'] ?? ''),
                'deskripsi' => trim($_POST['deskripsi'] ?? ''),
                'status' => $_POST['status'] ?? 'ACTIVE'
            ];

            if ($this->mkModel->update($id, $data)) {
                $this->auditModel->log('MATA_KULIAH', 'UPDATE', "Memperbarui mata kuliah: {$data['kode']} - {$data['nama']}");
                $_SESSION['success'] = "Mata Kuliah berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui Mata Kuliah.";
            }
            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/mata-kuliah');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        if ($id) {
            $mk = $this->mkModel->getById($id);
            if ($this->mkModel->delete($id)) {
                if ($mk) {
                    $this->auditModel->log('MATA_KULIAH', 'DELETE', "Menghapus mata kuliah: {$mk['kode']} - {$mk['nama']}");
                }
                $_SESSION['success'] = "Mata Kuliah berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus Mata Kuliah. Mungkin masih ada data RPS atau nilai terkait.";
            }
        }
        header('Location: ' . $baseUrl . '/mata-kuliah');
        exit;
    }
}
