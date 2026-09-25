<?php
// app/Models/CplModel.php

class CplModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        // If single argument is scalar, treat as kurikulum_id for backward compatibility
        if (!is_array($filters) && $filters !== null) {
            $filters = ['kurikulum_id' => $filters];
        }

        $sql = "
            SELECT c.*, 
                   k.nama as kurikulum_nama, 
                   k.kode as kurikulum_kode,
                   k.prodi_id,
                   p.nama as prodi_nama, 
                   p.jenjang as prodi_jenjang, 
                   p.kode as prodi_kode,
                   GROUP_CONCAT(DISTINCT pl.kode ORDER BY pl.kode SEPARATOR ', ') as pl_kodes
            FROM cpl c 
            JOIN kurikulum k ON c.kurikulum_id = k.id 
            JOIN program_studi p ON k.prodi_id = p.id
            LEFT JOIN pl_cpl pc ON c.id = pc.cpl_id
            LEFT JOIN profil_lulusan pl ON pc.pl_id = pl.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['prodi_id'])) {
            $sql .= " AND k.prodi_id = :prodi_id";
            $params['prodi_id'] = (int)$filters['prodi_id'];
        }

        if (!empty($filters['kurikulum_id'])) {
            $sql .= " AND c.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$filters['kurikulum_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " GROUP BY c.id ORDER BY p.nama ASC, c.kode ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
