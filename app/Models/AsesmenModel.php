<?php
class AsesmenModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Mengambil semua nilai mahasiswa di sebuah kelas
    public function getNilaiKelas($kelas_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM asesmen_nilai 
            WHERE kelas_id = :kelas_id
        ");
        $stmt->execute(['kelas_id' => $kelas_id]);
        $result = $stmt->fetchAll();

        // Reformat data for easy access in view:
        // $formatted[mahasiswa_id][sub_cpmk_id] = nilai
        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['mahasiswa_id']][$row['sub_cpmk_id']] = $row['nilai'];
        }
        return $formatted;
    }

    public function getDaftarSubCpmk($mata_kuliah_id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, c.kode as cpmk_kode
            FROM sub_cpmk sc
            JOIN cpmk c ON sc.cpmk_id = c.id
            WHERE c.mk_id = :mata_kuliah_id
            ORDER BY c.kode ASC, sc.kode ASC
        ");
        $stmt->execute(['mata_kuliah_id' => $mata_kuliah_id]);
        return $stmt->fetchAll();
    }

    public function saveNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, $nilai) {
        $stmt = $this->db->prepare("
            INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
            VALUES (:kelas_id, :mahasiswa_id, :sub_cpmk_id, :nilai)
            ON DUPLICATE KEY UPDATE nilai = :nilai
        ");
        return $stmt->execute([
            'kelas_id' => $kelas_id,
            'mahasiswa_id' => $mahasiswa_id,
            'sub_cpmk_id' => $sub_cpmk_id,
            'nilai' => $nilai
        ]);
    }
}
