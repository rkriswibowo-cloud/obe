<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByRole($role_name) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE r.name = :role_name
            ORDER BY u.nama_lengkap ASC
        ");
        $stmt->execute(['role_name' => $role_name]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    public function getAllWithRole() {
        return $this->db->query("SELECT u.*, r.name role_name, p.nama prodi_nama, p.jenjang
            FROM users u
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN program_studi p ON p.id = u.prodi_id
            ORDER BY r.id, u.nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoles() {
        return $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProdi() {
        return $this->db->query("SELECT id, nama, jenjang FROM program_studi WHERE status = 'ACTIVE' ORDER BY jenjang, nama")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleName($roleId) {
        $stmt = $this->db->prepare('SELECT name FROM roles WHERE id = ?');
        $stmt->execute([(int)$roleId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function createAccount($data) {
        $stmt = $this->db->prepare('INSERT INTO users (username, password, nama_lengkap, email, role_id, prodi_id) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['nama_lengkap'],
            !empty($data['email']) ? $data['email'] : null,
            $data['role_id'],
            $data['prodi_id'],
        ]);
    }

    public function updateAccount($id, $data) {
        $sql = 'UPDATE users SET username = ?, nama_lengkap = ?, email = ?, role_id = ?, prodi_id = ?';
        $params = [
            $data['username'],
            $data['nama_lengkap'],
            !empty($data['email']) ? $data['email'] : null,
            $data['role_id'],
            $data['prodi_id'],
        ];
        if ($data['password'] !== '') {
            if (strlen($data['password']) < 6) {
                throw new InvalidArgumentException('Password baru minimal 6 karakter.');
            }
            $sql .= ', password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $sql .= ' WHERE id = ?';
        $params[] = (int)$id;
        return $this->db->prepare($sql)->execute($params);
    }

    public function deleteAccount($id) {
        $user = $this->getById($id);
        if (!$user) {
            throw new RuntimeException('Akun tidak ditemukan.');
        }
        if ($this->getRoleName($user['role_id']) === 'Super Admin') {
            $count = (int)$this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'Super Admin'")->fetchColumn();
            if ($count <= 1) {
                throw new RuntimeException('Super Admin terakhir tidak dapat dihapus.');
            }
        }
        return $this->db->prepare('DELETE FROM users WHERE id = ?')->execute([(int)$id]);
    }
}
