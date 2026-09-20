<?php
class KurikulumModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT k.*, p.nama as nama_prodi 
            FROM kurikulum k 
            JOIN program_studi p ON k.prodi_id = p.id 
            ORDER BY k.tahun_mulai DESC, k.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM kurikulum WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO kurikulum (prodi_id, kode, nama, tahun_mulai, versi, status)
            VALUES (:prodi_id, :kode, :nama, :tahun_mulai, :versi, :status)
        ");
        return $stmt->execute([
            'prodi_id' => $data['prodi_id'],
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'tahun_mulai' => $data['tahun_mulai'],
            'versi' => $data['versi'],
            'status' => $data['status']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE kurikulum 
            SET prodi_id = :prodi_id, kode = :kode, nama = :nama, 
                tahun_mulai = :tahun_mulai, versi = :versi, status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'prodi_id' => $data['prodi_id'],
            'kode' => $data['kode'],
            'nama' => $data['nama'],
            'tahun_mulai' => $data['tahun_mulai'],
            'versi' => $data['versi'],
            'status' => $data['status'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM kurikulum WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
