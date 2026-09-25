<?php
class KurikulumModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($prodi_id = null) {
        $sql = "
            SELECT k.*, p.nama as nama_prodi, p.jenjang as jenjang_prodi 
            FROM kurikulum k 
            JOIN program_studi p ON k.prodi_id = p.id 
        ";
        $params = [];
        if ($prodi_id) {
            $sql .= " WHERE k.prodi_id = :pid";
            $params['pid'] = (int)$prodi_id;
        }
        $sql .= " ORDER BY k.tahun_mulai DESC, k.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
