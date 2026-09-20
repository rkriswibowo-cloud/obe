<?php
require_once __DIR__ . '/../../config/database.php';

class TahunAkademikModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM tahun_akademik ORDER BY nama DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tahun_akademik WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        if (isset($data['is_active']) && $data['is_active'] == 1) {
            $this->deactivateAll();
        }
        $stmt = $this->db->prepare("INSERT INTO tahun_akademik (nama, is_active) VALUES (:nama, :is_active)");
        return $stmt->execute([
            'nama' => $data['nama'],
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 0
        ]);
    }

    public function update($id, $data) {
        if (isset($data['is_active']) && $data['is_active'] == 1) {
            $this->deactivateAll();
        }
        $stmt = $this->db->prepare("UPDATE tahun_akademik SET nama = :nama, is_active = :is_active WHERE id = :id");
        return $stmt->execute([
            'nama' => $data['nama'],
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 0,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tahun_akademik WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function setActive($id) {
        $this->deactivateAll();
        $stmt = $this->db->prepare("UPDATE tahun_akademik SET is_active = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function deactivateAll() {
        $this->db->query("UPDATE tahun_akademik SET is_active = 0");
    }
}
