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
}
