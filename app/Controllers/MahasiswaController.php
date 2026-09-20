<?php
require_once __DIR__ . '/../Models/UserModel.php';

class MahasiswaController {
    private $userModel;
    private $db;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->db = Database::getInstance();
    }

    public function index() {
        $mahasiswa = $this->userModel->getByRole('Mahasiswa');
        require __DIR__ . '/../../views/mahasiswa/index.php';
    }

    public function create() {
        require __DIR__ . '/../../views/mahasiswa/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username']; // NIM
            $nama_lengkap = $_POST['nama_lengkap'];
            // default password is same as username
            $password = password_hash($username, PASSWORD_BCRYPT);
            
            // Get role_id for 'Mahasiswa'
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = 'Mahasiswa'");
            $stmt->execute();
            $role_id = $stmt->fetchColumn();

            $stmt = $this->db->prepare("INSERT INTO users (username, password, nama_lengkap, role_id) VALUES (:username, :password, :nama_lengkap, :role_id)");
            $stmt->execute([
                'username' => $username,
                'password' => $password,
                'nama_lengkap' => $nama_lengkap,
                'role_id' => $role_id
            ]);

            header('Location: /obe/public/mahasiswa');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $mahasiswa = $this->userModel->getById($id);
            if ($mahasiswa) {
                require __DIR__ . '/../../views/mahasiswa/form.php';
                return;
            }
        }
        header('Location: /obe/public/mahasiswa');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $username = $_POST['username'];
            $nama_lengkap = $_POST['nama_lengkap'];

            $stmt = $this->db->prepare("UPDATE users SET username = :username, nama_lengkap = :nama_lengkap WHERE id = :id");
            $stmt->execute([
                'username' => $username,
                'nama_lengkap' => $nama_lengkap,
                'id' => $id
            ]);

            header('Location: /obe/public/mahasiswa');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        header('Location: /obe/public/mahasiswa');
    }
}
