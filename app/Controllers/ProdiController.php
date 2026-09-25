<?php
// app/Controllers/ProdiController.php

require_once __DIR__ . '/../Models/ProdiModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class ProdiController {
    private $prodiModel;
    private $auditModel;

    public function __construct() {
        $this->prodiModel = new ProdiModel();
        $this->auditModel = new AuditLogModel();
    }

    public function index() {
        $prodiList = $this->prodiModel->getAll();
        require __DIR__ . '/../../views/prodi/index.php';
    }

    public function create() {
        require __DIR__ . '/../../views/prodi/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama = trim($_POST['nama'] ?? '');
            $jenjang = trim($_POST['jenjang'] ?? 'S1');
            $fakultas = trim($_POST['fakultas'] ?? 'Fakultas Sains dan Teknologi');
            $nama_kaprodi = trim($_POST['nama_kaprodi'] ?? '');
            $nidn_kaprodi = trim($_POST['nidn_kaprodi'] ?? '');
            $status = trim($_POST['status'] ?? 'ACTIVE');

            if ($kode && $nama) {
                $this->prodiModel->create([
                    'kode' => $kode,
                    'nama' => $nama,
                    'jenjang' => $jenjang,
                    'fakultas' => $fakultas,
                    'nama_kaprodi' => $nama_kaprodi ?: null,
                    'nidn_kaprodi' => $nidn_kaprodi ?: null,
                    'status' => $status
                ]);

                $this->auditModel->log('PROGRAM_STUDI', 'CREATE', "Menambahkan Program Studi baru: $jenjang $nama ($kode)");
                
                $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
                header('Location: ' . $baseUrl . '/prodi?success=1');
                exit;
            }
        }

        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        header('Location: ' . $baseUrl . '/prodi?error=Form+tidak+lengkap');
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $prodi = $this->prodiModel->getById($id);

        if (!$prodi) {
            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/prodi');
            exit;
        }

        require __DIR__ . '/../../views/prodi/form.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama = trim($_POST['nama'] ?? '');
            $jenjang = trim($_POST['jenjang'] ?? 'S1');
            $fakultas = trim($_POST['fakultas'] ?? 'Fakultas Sains dan Teknologi');
            $nama_kaprodi = trim($_POST['nama_kaprodi'] ?? '');
            $nidn_kaprodi = trim($_POST['nidn_kaprodi'] ?? '');
            $status = trim($_POST['status'] ?? 'ACTIVE');

            if ($id && $kode && $nama) {
                $this->prodiModel->update($id, [
                    'kode' => $kode,
                    'nama' => $nama,
                    'jenjang' => $jenjang,
                    'fakultas' => $fakultas,
                    'nama_kaprodi' => $nama_kaprodi ?: null,
                    'nidn_kaprodi' => $nidn_kaprodi ?: null,
                    'status' => $status
                ]);

                $this->auditModel->log('PROGRAM_STUDI', 'UPDATE', "Memperbarui Program Studi: $jenjang $nama ($kode)");

                $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
                header('Location: ' . $baseUrl . '/prodi?success=2');
                exit;
            }
        }

        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        header('Location: ' . $baseUrl . '/prodi?error=Form+tidak+lengkap');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';

        if ($id) {
            try {
                $prodi = $this->prodiModel->getById($id);
                $this->prodiModel->delete($id);

                if ($prodi) {
                    $this->auditModel->log('PROGRAM_STUDI', 'DELETE', "Menghapus Program Studi: {$prodi['nama']} ({$prodi['kode']})");
                }

                header('Location: ' . $baseUrl . '/prodi?success=3');
                exit;
            } catch (Exception $e) {
                header('Location: ' . $baseUrl . '/prodi?error=' . urlencode($e->getMessage()));
                exit;
            }
        }

        header('Location: ' . $baseUrl . '/prodi');
        exit;
    }
}
