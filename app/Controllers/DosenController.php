<?php
// app/Controllers/DosenController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class DosenController {
    private $userModel;
    private $auditModel;
    private $db;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->auditModel = new AuditLogModel();
        $this->db = Database::getInstance();
    }

    public function index() {
        $dosen = $this->userModel->getByRole('Dosen');
        require __DIR__ . '/../../views/dosen/index.php';
    }

    public function create() {
        $prodiList = $this->db->query("SELECT * FROM program_studi ORDER BY jenjang ASC, nama ASC")->fetchAll();
        require __DIR__ . '/../../views/dosen/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $nidn = trim($_POST['nidn'] ?? '');
            $nuptk = trim($_POST['nuptk'] ?? '');
            $homebase = trim($_POST['homebase'] ?? '');

            // default password is same as username
            $password = password_hash($username, PASSWORD_BCRYPT);
            
            // Get role_id for 'Dosen'
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = 'Dosen'");
            $stmt->execute();
            $role_id = $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, nama_lengkap, nidn, nuptk, homebase, role_id) 
                VALUES (:username, :password, :nama_lengkap, :nidn, :nuptk, :homebase, :role_id)
            ");
            $stmt->execute([
                'username' => $username,
                'password' => $password,
                'nama_lengkap' => $nama_lengkap,
                'nidn' => $nidn ?: null,
                'nuptk' => $nuptk ?: null,
                'homebase' => $homebase ?: null,
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
            $dosen = $this->userModel->getById($id);
            if ($dosen) {
                $prodiList = $this->db->query("SELECT * FROM program_studi ORDER BY jenjang ASC, nama ASC")->fetchAll();
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
            $username = trim($_POST['username'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $nidn = trim($_POST['nidn'] ?? '');
            $nuptk = trim($_POST['nuptk'] ?? '');
            $homebase = trim($_POST['homebase'] ?? '');

            $stmt = $this->db->prepare("
                UPDATE users 
                SET username = :username, 
                    nama_lengkap = :nama_lengkap, 
                    nidn = :nidn, 
                    nuptk = :nuptk, 
                    homebase = :homebase 
                WHERE id = :id
            ");
            $stmt->execute([
                'username' => $username,
                'nama_lengkap' => $nama_lengkap,
                'nidn' => $nidn ?: null,
                'nuptk' => $nuptk ?: null,
                'homebase' => $homebase ?: null,
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
}
