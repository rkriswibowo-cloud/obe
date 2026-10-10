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

    public function validateNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, $nilai) {
        if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
            throw new InvalidArgumentException('Nilai asesmen harus berada pada rentang 0 sampai 100.');
        }

        $validasi = $this->db->prepare("
            SELECT COUNT(*)
            FROM kelas k
            JOIN kelas_mahasiswa km
              ON km.kelas_id = k.id AND km.mahasiswa_id = :mahasiswa_id
            JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
            JOIN cpmk cp ON cp.mk_id = mk.id
            JOIN sub_cpmk sc ON sc.cpmk_id = cp.id AND sc.id = :sub_cpmk_id
            WHERE k.id = :kelas_id
        ");
        $validasi->execute([
            'kelas_id' => $kelas_id,
            'mahasiswa_id' => $mahasiswa_id,
            'sub_cpmk_id' => $sub_cpmk_id
        ]);
        if ((int)$validasi->fetchColumn() !== 1) {
            throw new InvalidArgumentException('Nilai ditolak: mahasiswa, kelas, atau Sub-CPMK tidak saling terkait.');
        }
        return true;
    }

    public function saveNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, $nilai) {
        $this->validateNilai($kelas_id, $mahasiswa_id, $sub_cpmk_id, $nilai);

        $stmt = $this->db->prepare("
            INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
            VALUES (:kelas_id, :mahasiswa_id, :sub_cpmk_id, :nilai)
            ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
        ");
        return $stmt->execute([
            'kelas_id' => $kelas_id,
            'mahasiswa_id' => $mahasiswa_id,
            'sub_cpmk_id' => $sub_cpmk_id,
            'nilai' => $nilai
        ]);
    }
}
