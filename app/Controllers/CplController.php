<?php
// app/Controllers/CplController.php

require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';

class CplController {
    private $cplModel;
    private $kurikulumModel;
    private $prodiModel;
    private $access;

    public function __construct() {
        $this->cplModel = new CplModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->prodiModel = new ProdiModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $selectedProdiId = $_GET['prodi_id'] ?? ($this->access->isKaprodi() ? $this->access->primaryManagedProdiId() : '');
        $selectedKurikulumId = $_GET['kurikulum_id'] ?? '';
        if ($selectedProdiId && !$this->access->isSuperAdmin()) $this->access->assertProdi($selectedProdiId);
        if ($selectedKurikulumId && !$this->access->isSuperAdmin()) $this->access->assertKurikulum($selectedKurikulumId);

        $filters = [
            'prodi_id' => $selectedProdiId,
            'kurikulum_id' => $selectedKurikulumId,
            'status' => $_GET['status'] ?? ''
        ];

        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        $kurikulumList = $this->kurikulumModel->getAll($selectedProdiId ?: null);
        $cplList = $this->cplModel->getAll($filters);

        require_once __DIR__ . '/../../views/cpl/index.php';
    }

    public function create() {
        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        require_once __DIR__ . '/../../views/cpl/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($_POST['kurikulum_id'] ?? 0);
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
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . $baseUrl . '/cpl');
            exit;
        }

        $cpl = $this->cplModel->getById($id);
        if (!$cpl) {
            $_SESSION['error'] = "Data CPL tidak ditemukan.";
            header('Location: ' . $baseUrl . '/cpl');
            exit;
        }

        if (!$this->access->isSuperAdmin()) $this->access->assertCpl($id);
        $prodiList = $this->access->filterProdiRows($this->prodiModel->getAll());
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        require_once __DIR__ . '/../../views/cpl/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id) {
                if (!$this->access->isSuperAdmin()) {
                    $this->access->assertCpl($id);
                    $this->access->assertKurikulum($_POST['kurikulum_id'] ?? 0);
                }
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
            if (!$this->access->isSuperAdmin()) $this->access->assertCpl($id);
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
