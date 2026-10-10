<?php
require_once __DIR__ . '/AturanKurikulumModel.php';

class KelasModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($dosenId = null, $kurikulumId = null, $tahunAkademikId = null, $search = null) {
        $sql = "
            SELECT k.*, mk.nama as mata_kuliah_nama, mk.kode as mata_kuliah_kode,
                   mk.kurikulum_id, kur.nama as kurikulum_nama, kur.kode as kurikulum_kode,
                   ta.nama as tahun_akademik_nama, u.nama_lengkap as dosen_nama,
                   (SELECT COUNT(*) FROM kelas_mahasiswa km WHERE km.kelas_id = k.id) as jumlah_mahasiswa
            FROM kelas k
            JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
            LEFT JOIN kurikulum kur ON mk.kurikulum_id = kur.id
            JOIN tahun_akademik ta ON k.tahun_akademik_id = ta.id
            JOIN users u ON k.dosen_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($dosenId !== null) {
            $sql .= " AND (k.dosen_id = :dosen_id OR EXISTS (
                SELECT 1 FROM kelas_tim_teaching ktt
                WHERE ktt.kelas_id = k.id AND ktt.dosen_id = :tim_dosen_id
            ))";
            $params['dosen_id'] = (int)$dosenId;
            $params['tim_dosen_id'] = (int)$dosenId;
        }
        if (!empty($kurikulumId)) {
            $sql .= " AND mk.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = (int)$kurikulumId;
        }
        if (!empty($tahunAkademikId)) {
            $sql .= " AND k.tahun_akademik_id = :ta_id";
            $params['ta_id'] = (int)$tahunAkademikId;
        }
        if (!empty($search)) {
            $sql .= " AND (mk.nama LIKE :search1 OR mk.kode LIKE :search2 OR k.nama_kelas LIKE :search3 OR u.nama_lengkap LIKE :search4)";
            $term = '%' . trim($search) . '%';
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
            $params['search4'] = $term;
        }
        $sql .= " ORDER BY ta.nama DESC, mk.nama ASC, k.nama_kelas ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function canManage($kelasId, $dosenId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM kelas k
            WHERE k.id = :kelas_id
              AND (k.dosen_id = :dosen_id OR EXISTS (
                  SELECT 1 FROM kelas_tim_teaching ktt
                  WHERE ktt.kelas_id = k.id AND ktt.dosen_id = :tim_dosen_id
              ))
        ");
        $stmt->execute([
            'kelas_id' => (int)$kelasId,
            'dosen_id' => (int)$dosenId,
            'tim_dosen_id' => (int)$dosenId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT k.*, mk.nama as mata_kuliah_nama, mk.kode as mata_kuliah_kode,
                   mk.kurikulum_id, kur.nama as kurikulum_nama, kur.kode as kurikulum_kode,
                   ta.nama as tahun_akademik_nama, u.nama_lengkap as dosen_nama
            FROM kelas k
            JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
            LEFT JOIN kurikulum kur ON mk.kurikulum_id = kur.id
            JOIN tahun_akademik ta ON k.tahun_akademik_id = ta.id
            JOIN users u ON k.dosen_id = u.id
            WHERE k.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id)
            VALUES (:mata_kuliah_id, :tahun_akademik_id, :nama_kelas, :dosen_id)
        ");
        $success = $stmt->execute([
            'mata_kuliah_id' => $data['mata_kuliah_id'],
            'tahun_akademik_id' => $data['tahun_akademik_id'],
            'nama_kelas' => $data['nama_kelas'],
            'dosen_id' => $data['dosen_id']
        ]);

        if ($success) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE kelas 
            SET mata_kuliah_id = :mata_kuliah_id, 
                tahun_akademik_id = :tahun_akademik_id, 
                nama_kelas = :nama_kelas, 
                dosen_id = :dosen_id
            WHERE id = :id
        ");
        return $stmt->execute([
            'mata_kuliah_id' => $data['mata_kuliah_id'],
            'tahun_akademik_id' => $data['tahun_akademik_id'],
            'nama_kelas' => $data['nama_kelas'],
            'dosen_id' => $data['dosen_id'],
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM kelas WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // --- Tim Teaching ---
    public function setTimTeaching($kelas_id, $dosen_ids) {
        // Hapus tim teaching lama
        $stmt = $this->db->prepare("DELETE FROM kelas_tim_teaching WHERE kelas_id = :kelas_id");
        $stmt->execute(['kelas_id' => $kelas_id]);

        if (!empty($dosen_ids)) {
            $stmtInsert = $this->db->prepare("INSERT IGNORE INTO kelas_tim_teaching (kelas_id, dosen_id) VALUES (:kelas_id, :dosen_id)");
            foreach ($dosen_ids as $dosen_id) {
                $stmtInsert->execute([
                    'kelas_id' => $kelas_id,
                    'dosen_id' => $dosen_id
                ]);
            }
        }
    }

    public function getTimTeaching($kelas_id) {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM kelas_tim_teaching ktt
            JOIN users u ON ktt.dosen_id = u.id
            WHERE ktt.kelas_id = :kelas_id
        ");
        $stmt->execute(['kelas_id' => $kelas_id]);
        return $stmt->fetchAll();
    }

    public function getTimTeachingIds($kelas_id) {
        $stmt = $this->db->prepare("SELECT dosen_id FROM kelas_tim_teaching WHERE kelas_id = :kelas_id");
        $stmt->execute(['kelas_id' => $kelas_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // --- Peserta Kelas ---

    public function getPeserta($kelas_id) {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM kelas_mahasiswa km
            JOIN users u ON km.mahasiswa_id = u.id
            WHERE km.kelas_id = :kelas_id
            ORDER BY u.username ASC
        ");
        $stmt->execute(['kelas_id' => $kelas_id]);
        return $stmt->fetchAll();
    }

    public function getMahasiswaBelumTerdaftar($kelas_id) {
        $kelas = $this->getById($kelas_id);
        if (!$kelas) {
            return [];
        }
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Mahasiswa'
              AND u.id NOT IN (
                  SELECT mahasiswa_id FROM kelas_mahasiswa WHERE kelas_id = :kelas_id
              )
            ORDER BY u.username ASC
        ");
        $stmt->execute(['kelas_id' => $kelas_id]);
        $mahasiswa = $stmt->fetchAll();
        $aturan = new AturanKurikulumModel();
        return array_values(array_filter($mahasiswa, static function ($mhs) use ($aturan, $kelas) {
            $angkatan = AturanKurikulumModel::extractAngkatan($mhs['username'] ?? '');
            return $angkatan
                && $aturan->getKurikulumIdByAngkatan($angkatan) === (int)$kelas['kurikulum_id'];
        }));
    }

    public function addPeserta($kelas_id, $mahasiswa_id) {
        $kelas = $this->getById($kelas_id);
        if (!$kelas) {
            throw new InvalidArgumentException('Kelas tidak ditemukan.');
        }

        $stmtMahasiswa = $this->db->prepare("
            SELECT u.username
            FROM users u JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id AND r.name = 'Mahasiswa'
        ");
        $stmtMahasiswa->execute(['id' => $mahasiswa_id]);
        $username = $stmtMahasiswa->fetchColumn();
        $angkatan = AturanKurikulumModel::extractAngkatan((string)$username);
        if (!$username || !$angkatan) {
            throw new InvalidArgumentException('Peserta harus merupakan mahasiswa dengan format NIM yang valid.');
        }

        $kurikulumMahasiswa = (new AturanKurikulumModel())->getKurikulumIdByAngkatan($angkatan);
        if (!$kurikulumMahasiswa || $kurikulumMahasiswa !== (int)$kelas['kurikulum_id']) {
            throw new InvalidArgumentException('Mahasiswa tidak dapat dimasukkan karena kurikulumnya berbeda dengan mata kuliah kelas.');
        }

        $maxSemester = AturanKurikulumModel::getMaxSemesterByAngkatan($angkatan);
        if ($maxSemester !== null && (int)$kelas['tahun_akademik_id'] > $maxSemester) {
            throw new InvalidArgumentException("Mahasiswa angkatan {$angkatan} saat ini baru menempuh perkuliahan hingga semester {$maxSemester} dan belum dapat dimasukkan ke kelas semester selanjutnya.");
        }

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id)
            VALUES (:kelas_id, :mahasiswa_id)
        ");
        return $stmt->execute([
            'kelas_id' => $kelas_id,
            'mahasiswa_id' => $mahasiswa_id
        ]);
    }

    public function removePeserta($kelas_id, $mahasiswa_id) {
        $stmt = $this->db->prepare("
            DELETE FROM kelas_mahasiswa 
            WHERE kelas_id = :kelas_id AND mahasiswa_id = :mahasiswa_id
        ");
        return $stmt->execute([
            'kelas_id' => $kelas_id,
            'mahasiswa_id' => $mahasiswa_id
        ]);
    }
}
