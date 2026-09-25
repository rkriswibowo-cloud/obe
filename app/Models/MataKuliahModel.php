<?php
// app/Models/MataKuliahModel.php

class MataKuliahModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = [], $limit = null, $offset = null) {
        $sql = "
            SELECT m.*, 
                   k.nama as kurikulum_nama, 
                   k.kode as kurikulum_kode, 
                   k.prodi_id,
                   p.nama as prodi_nama, 
                   p.jenjang as prodi_jenjang, 
                   p.kode as prodi_kode
            FROM mata_kuliah m 
            JOIN kurikulum k ON m.kurikulum_id = k.id 
            JOIN program_studi p ON k.prodi_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['prodi_id'])) {
            $sql .= " AND k.prodi_id = :prodi_id";
            $params['prodi_id'] = (int)$filters['prodi_id'];
        }

        if (!empty($filters['kurikulum_id'])) {
            $sql .= " AND m.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$filters['kurikulum_id'];
        }

        if (!empty($filters['semester'])) {
            $sql .= " AND m.semester = :semester";
            $params['semester'] = $filters['semester'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (m.nama LIKE :search_nama OR m.kode LIKE :search_kode)";
            $params['search_nama'] = '%' . $filters['search'] . '%';
            $params['search_kode'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY p.nama ASC, m.semester ASC, m.kode ASC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($filters = []) {
        $sql = "
            SELECT COUNT(*) 
            FROM mata_kuliah m 
            JOIN kurikulum k ON m.kurikulum_id = k.id 
            JOIN program_studi p ON k.prodi_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['prodi_id'])) {
            $sql .= " AND k.prodi_id = :prodi_id";
            $params['prodi_id'] = (int)$filters['prodi_id'];
        }

        if (!empty($filters['kurikulum_id'])) {
            $sql .= " AND m.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$filters['kurikulum_id'];
        }

        if (!empty($filters['semester'])) {
            $sql .= " AND m.semester = :semester";
            $params['semester'] = $filters['semester'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (m.nama LIKE :search_nama OR m.kode LIKE :search_kode)";
            $params['search_nama'] = '%' . $filters['search'] . '%';
            $params['search_kode'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   k.nama as kurikulum_nama, 
                   k.prodi_id, 
                   p.nama as prodi_nama, 
                   p.jenjang as prodi_jenjang,
                   p.kode as prodi_kode
            FROM mata_kuliah m 
            JOIN kurikulum k ON m.kurikulum_id = k.id 
            JOIN program_studi p ON k.prodi_id = p.id
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO mata_kuliah (kurikulum_id, kode, nama, sks_teori, sks_praktek, semester, sifat, kelompok_keilmuan, deskripsi, status)
            VALUES (:kurikulum_id, :kode, :nama, :sks_teori, :sks_praktek, :semester, :sifat, :kelompok_keilmuan, :deskripsi, :status)
        ");
        return $stmt->execute([
            'kurikulum_id' => $data['kurikulum_id'],
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'sks_teori' => $data['sks_teori'] ?? 0,
            'sks_praktek' => $data['sks_praktek'] ?? 0,
            'semester' => $data['semester'],
            'sifat' => $data['sifat'] ?? 'Wajib',
            'kelompok_keilmuan' => $data['kelompok_keilmuan'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'status' => $data['status'] ?? 'ACTIVE'
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE mata_kuliah 
            SET kurikulum_id = :kurikulum_id, kode = :kode, nama = :nama, 
                sks_teori = :sks_teori, sks_praktek = :sks_praktek, semester = :semester, 
                sifat = :sifat, kelompok_keilmuan = :kelompok_keilmuan, deskripsi = :deskripsi, status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'kurikulum_id' => $data['kurikulum_id'],
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'sks_teori' => $data['sks_teori'] ?? 0,
            'sks_praktek' => $data['sks_praktek'] ?? 0,
            'semester' => $data['semester'],
            'sifat' => $data['sifat'] ?? 'Wajib',
            'kelompok_keilmuan' => $data['kelompok_keilmuan'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'status' => $data['status'] ?? 'ACTIVE',
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM mata_kuliah WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
