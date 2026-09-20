<?php
// app/Models/CplModel.php

class CplModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT c.*, k.nama as kurikulum_nama 
            FROM cpl c 
            JOIN kurikulum k ON c.kurikulum_id = k.id 
            ORDER BY c.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM cpl WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status)
            VALUES (:kurikulum_id, :kode, :rumusan, :kategori, :target_capaian, :status)
        ");
        return $stmt->execute([
            'kurikulum_id' => $data['kurikulum_id'],
            'kode' => $data['kode'],
            'rumusan' => $data['rumusan'],
            'kategori' => $data['kategori'] ?? null,
            'target_capaian' => $data['target_capaian'] ?? 70.00,
            'status' => $data['status']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE cpl 
            SET kurikulum_id = :kurikulum_id, kode = :kode, rumusan = :rumusan, 
                kategori = :kategori, target_capaian = :target_capaian, status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'kurikulum_id' => $data['kurikulum_id'],
            'kode' => $data['kode'],
            'rumusan' => $data['rumusan'],
            'kategori' => $data['kategori'] ?? null,
            'target_capaian' => $data['target_capaian'] ?? 70.00,
            'status' => $data['status'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cpl WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
