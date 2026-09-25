<?php
// app/Models/ProdiModel.php

class ProdiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $sql = "
            SELECT p.*,
                   (SELECT COUNT(*) FROM kurikulum k WHERE k.prodi_id = p.id) as total_kurikulum,
                   (SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE u.prodi_id = p.id AND r.name = 'Dosen') as total_dosen,
                   (SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE u.prodi_id = p.id AND r.name = 'Mahasiswa') as total_mahasiswa
            FROM program_studi p
            ORDER BY p.jenjang ASC, p.nama ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM program_studi WHERE status = 'ACTIVE' ORDER BY jenjang ASC, nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   (SELECT COUNT(*) FROM kurikulum k WHERE k.prodi_id = p.id) as total_kurikulum
            FROM program_studi p
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO program_studi (kode, nama, jenjang, fakultas, nama_kaprodi, nidn_kaprodi, status)
            VALUES (:kode, :nama, :jenjang, :fakultas, :nama_kaprodi, :nidn_kaprodi, :status)
        ");
        return $stmt->execute([
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'jenjang' => $data['jenjang'] ?? 'S1',
            'fakultas' => $data['fakultas'] ?? 'Fakultas Sains dan Teknologi',
            'nama_kaprodi' => $data['nama_kaprodi'] ?? null,
            'nidn_kaprodi' => $data['nidn_kaprodi'] ?? null,
            'status' => $data['status'] ?? 'ACTIVE'
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE program_studi 
            SET kode = :kode, 
                nama = :nama, 
                jenjang = :jenjang, 
                fakultas = :fakultas, 
                nama_kaprodi = :nama_kaprodi, 
                nidn_kaprodi = :nidn_kaprodi, 
                status = :status 
            WHERE id = :id
        ");
        return $stmt->execute([
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'jenjang' => $data['jenjang'],
            'fakultas' => $data['fakultas'],
            'nama_kaprodi' => $data['nama_kaprodi'],
            'nidn_kaprodi' => $data['nidn_kaprodi'],
            'status' => $data['status'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        // Cek ketergantungan kurikulum
        $count = $this->db->prepare("SELECT COUNT(*) FROM kurikulum WHERE prodi_id = ?");
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            throw new Exception("Program studi tidak dapat dihapus karena masih memiliki kurikulum terdaftar.");
        }

        $stmt = $this->db->prepare("DELETE FROM program_studi WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
