<?php
// app/Controllers/DosenController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class DosenController {
    private $userModel;
    private $auditModel;
    private $db;
    private $access;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->auditModel = new AuditLogModel();
        $this->db = Database::getInstance();
        $this->access = new AccessContext();
    }

    public function index() {
        $dosen = $this->userModel->getByRole('Dosen');
        if (!$this->access->isSuperAdmin()) $dosen = array_values(array_filter($dosen, fn($row) => $this->access->canAccessProdi($row['prodi_id'] ?? 0)));
        require __DIR__ . '/../../views/dosen/index.php';
    }

    public function create() {
        $prodiList = $this->db->query("SELECT * FROM program_studi ORDER BY jenjang ASC, nama ASC")->fetchAll();
        $prodiList = $this->access->filterProdiRows($prodiList);
        require __DIR__ . '/../../views/dosen/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $nidn = trim($_POST['nidn'] ?? '');
            $nuptk = trim($_POST['nuptk'] ?? '');
            $homebase = trim($_POST['homebase'] ?? '');
            $prodiId = $this->access->isSuperAdmin() ? $this->resolveProdiId($homebase) : (int)$this->access->primaryManagedProdiId();
            if (!$this->access->isSuperAdmin()) $this->access->assertProdi($prodiId);

            // default password is same as username
            $password = password_hash($username, PASSWORD_BCRYPT);
            
            // Get role_id for 'Dosen'
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = 'Dosen'");
            $stmt->execute();
            $role_id = $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, nama_lengkap, nidn, nuptk, homebase, prodi_id, role_id)
                VALUES (:username, :password, :nama_lengkap, :nidn, :nuptk, :homebase, :prodi_id, :role_id)
            ");
            $stmt->execute([
                'username' => $username,
                'password' => $password,
                'nama_lengkap' => $nama_lengkap,
                'nidn' => $nidn ?: null,
                'nuptk' => $nuptk ?: null,
                'homebase' => $homebase ?: null,
                'prodi_id' => $prodiId ?: null,
                'role_id' => $role_id
            ]);

            $this->auditModel->log('DOSEN', 'CREATE', "Menambahkan data dosen: $nama_lengkap (NIDN: $nidn, Homebase: $homebase)");

            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/dosen?success=1');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin()) $this->access->assertMahasiswa($id);
            $dosen = $this->userModel->getById($id);
            if ($dosen) {
                $prodiList = $this->db->query("SELECT * FROM program_studi ORDER BY jenjang ASC, nama ASC")->fetchAll();
                $prodiList = $this->access->filterProdiRows($prodiList);
                require __DIR__ . '/../../views/dosen/form.php';
                return;
            }
        }
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        header('Location: ' . $baseUrl . '/dosen');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            if (!$this->access->isSuperAdmin()) $this->access->assertMahasiswa($id);
            $username = trim($_POST['username'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $nidn = trim($_POST['nidn'] ?? '');
            $nuptk = trim($_POST['nuptk'] ?? '');
            $homebase = trim($_POST['homebase'] ?? '');
            $prodiId = $this->access->isSuperAdmin() ? $this->resolveProdiId($homebase) : (int)$this->access->primaryManagedProdiId();

            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = :username, 
                    nama_lengkap = :nama_lengkap, 
                    nidn = :nidn, 
                    nuptk = :nuptk, 
                    homebase = :homebase,
                    prodi_id = :prodi_id
                WHERE id = :id
            ");
            $stmt->execute([
                'username' => $username,
                'nama_lengkap' => $nama_lengkap,
                'nidn' => $nidn ?: null,
                'nuptk' => $nuptk ?: null,
                'homebase' => $homebase ?: null,
                'prodi_id' => $prodiId ?: null,
                'id' => $id
            ]);

            $this->auditModel->log('DOSEN', 'UPDATE', "Memperbarui data dosen: $nama_lengkap (NIDN: $nidn, Homebase: $homebase)");

            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/dosen?success=2');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin()) $this->access->assertMahasiswa($id);
            $dosen = $this->userModel->getById($id);
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($dosen) {
                $this->auditModel->log('DOSEN', 'DELETE', "Menghapus dosen: {$dosen['nama_lengkap']}");
            }
        }
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
        header('Location: ' . $baseUrl . '/dosen?success=3');
    }

    private function resolveProdiId(string $homebase): ?int {
        if ($homebase === '') return null;
        $stmt = $this->db->prepare("SELECT id FROM program_studi WHERE CONCAT(jenjang, ' ', nama) = ? OR nama = ? LIMIT 1");
        $stmt->execute([$homebase, $homebase]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }
}
