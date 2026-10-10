<?php
// app/Controllers/ProdiController.php

require_once __DIR__ . '/../Models/ProdiModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class ProdiController {
    private $prodiModel;
    private $auditModel;
    private $db;
    private $access;

    public function __construct() {
        $this->prodiModel = new ProdiModel();
        $this->auditModel = new AuditLogModel();
        $this->db = Database::getInstance();
        $this->access = new AccessContext();
    }

    public function index() {
        $prodiList = $this->prodiModel->getAll();
        $prodiList = $this->access->filterProdiRows($prodiList);
        require __DIR__ . '/../../views/prodi/index.php';
    }

    public function create() {
        $dosenList = $this->getDosenList();
        require __DIR__ . '/../../views/prodi/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama = trim($_POST['nama'] ?? '');
            $jenjang = trim($_POST['jenjang'] ?? 'S1');
            $fakultas = trim($_POST['fakultas'] ?? 'Fakultas Sains dan Teknologi');
            $dekan = $this->resolveDekan((int)($_POST['dekan_user_id'] ?? 0));
            $kaprodi = $this->resolveDekan((int)($_POST['kaprodi_user_id'] ?? 0));
            $status = trim($_POST['status'] ?? 'ACTIVE');

            if ($kode && $nama) {
                $this->prodiModel->create([
                    'kode' => $kode,
                    'nama' => $nama,
                    'jenjang' => $jenjang,
                    'fakultas' => $fakultas,
                    'nama_dekan' => $dekan['nama_lengkap'] ?? null,
                    'nidn_dekan' => $dekan['nomor'] ?? null,
                    'dekan_user_id' => $dekan['id'] ?? null,
                    'nama_kaprodi' => $kaprodi['nama_lengkap'] ?? null,
                    'nidn_kaprodi' => $kaprodi['nomor'] ?? null,
                    'kaprodi_user_id' => $kaprodi['id'] ?? null,
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

        $dosenList = $this->getDosenList();
        require __DIR__ . '/../../views/prodi/form.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $kode = strtoupper(trim($_POST['kode'] ?? ''));
            $nama = trim($_POST['nama'] ?? '');
            $jenjang = trim($_POST['jenjang'] ?? 'S1');
            $fakultas = trim($_POST['fakultas'] ?? 'Fakultas Sains dan Teknologi');
            $dekan = $this->resolveDekan((int)($_POST['dekan_user_id'] ?? 0));
            $kaprodi = $this->resolveDekan((int)($_POST['kaprodi_user_id'] ?? 0));
            $status = trim($_POST['status'] ?? 'ACTIVE');

            if ($id && $kode && $nama) {
                $this->prodiModel->update($id, [
                    'kode' => $kode,
                    'nama' => $nama,
                    'jenjang' => $jenjang,
                    'fakultas' => $fakultas,
                    'nama_dekan' => $dekan['nama_lengkap'] ?? null,
                    'nidn_dekan' => $dekan['nomor'] ?? null,
                    'dekan_user_id' => $dekan['id'] ?? null,
                    'nama_kaprodi' => $kaprodi['nama_lengkap'] ?? null,
                    'nidn_kaprodi' => $kaprodi['nomor'] ?? null,
                    'kaprodi_user_id' => $kaprodi['id'] ?? null,
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

    private function getDosenList() {
        return $this->db->query("SELECT u.id,u.nama_lengkap,u.nidn,u.nuptk,u.homebase FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='Dosen' ORDER BY u.nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resolveDekan($id) {
        if ($id <= 0) return null;
        $stmt=$this->db->prepare("SELECT u.id,u.nama_lengkap,COALESCE(NULLIF(u.nuptk,''),NULLIF(u.nidn,'')) nomor FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND r.name='Dosen'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
