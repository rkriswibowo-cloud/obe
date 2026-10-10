<?php
// app/Models/AturanKurikulumModel.php
require_once __DIR__ . '/../../config/database.php';

class AturanKurikulumModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Extract 4-digit angkatan from NIM/username.
     * Supports both 4-digit year prefix (e.g. 2024001 -> 2024)
     * and 2-digit year prefix (e.g. 22020900006 -> 2022).
     */
    public static function extractAngkatan($username) {
        $username = trim((string)$username);
        if (preg_match('/^(19|20)(\d{2})/', $username, $matches)) {
            return (int)($matches[1] . $matches[2]);
        }
        if (preg_match('/^(\d{2})/', $username, $matches)) {
            return 2000 + (int)$matches[1];
        }
        return null;
    }

    /**
     * Dapatkan batas semester maksimal yang sudah ditempuh berdasarkan angkatan mahasiswa:
     * - Angkatan 2022: Semester 8 (Sudah Penuh / Lulus)
     * - Angkatan 2023: Semester 7 (Aktif berjalan)
     * - Angkatan 2024: Semester 5 (Aktif berjalan)
     * - Angkatan 2025: Semester 3 (Aktif berjalan)
     */
    public static function getMaxSemesterByAngkatan($angkatan) {
        $angkatan = (int)$angkatan;
        if ($angkatan <= 0) return 1;

        // Hitung secara dinamis dan realtime berdasarkan Tahun Akademik yang sedang aktif di database
        try {
            $db = Database::getInstance();
            $activeTa = $db->query("SELECT nama FROM tahun_akademik WHERE is_active = 1 LIMIT 1")->fetchColumn();
            if ($activeTa && preg_match('/(\d{4})\/\d{4}\s+(Ganjil|Genap)/i', $activeTa, $matches)) {
                $year = (int)$matches[1];
                $isGenap = (strcasecmp($matches[2], 'Genap') === 0);
                $calculatedSem = (($year - $angkatan) * 2) + ($isGenap ? 2 : 1);
                if ($calculatedSem >= 8) {
                    return 8; // Sudah mencapai semester akhir / penuh (lulus)
                }
                if ($calculatedSem >= 1) {
                    return $calculatedSem;
                }
            }
        } catch (\Exception $e) {}

        // Fallback default jika TA aktif belum diset
        if ($angkatan <= 2022) return 8;
        if ($angkatan === 2023) return 7;
        if ($angkatan === 2024) return 5;
        if ($angkatan === 2025) return 3;
        return 1;
    }

    public static function getStatusStudiByAngkatan($angkatan) {
        $angkatan = (int)$angkatan;
        $maxSem = self::getMaxSemesterByAngkatan($angkatan);

        if ($maxSem >= 8 && $angkatan <= 2022) {
            return [
                'semester' => 8,
                'status' => 'Lulus',
                'badge' => 'Sudah Lulus (Semester 1-8)',
                'badge_color' => '#10b981',
                'bg_color' => 'rgba(16, 185, 129, 0.12)'
            ];
        }

        // Penentuan warna aksen per semester
        $colors = [
            1 => ['#64748b', 'rgba(100, 116, 139, 0.12)'],
            2 => ['#0ea5e9', 'rgba(14, 165, 233, 0.12)'],
            3 => ['#f59e0b', 'rgba(245, 158, 11, 0.12)'],
            4 => ['#8b5cf6', 'rgba(139, 92, 246, 0.12)'],
            5 => ['#6366f1', 'rgba(99, 102, 241, 0.12)'],
            6 => ['#ec4899', 'rgba(236, 72, 153, 0.12)'],
            7 => ['#0284c7', 'rgba(2, 132, 199, 0.12)'],
            8 => ['#10b981', 'rgba(16, 185, 129, 0.12)'],
        ];

        $palette = $colors[$maxSem] ?? ['#0284c7', 'rgba(2, 132, 199, 0.12)'];

        return [
            'semester' => $maxSem,
            'status' => 'Aktif',
            'badge' => "Semester {$maxSem} (Aktif)",
            'badge_color' => $palette[0],
            'bg_color' => $palette[1]
        ];
    }

    public function getKurikulumByNim($username) {
        $angkatan = self::extractAngkatan($username);
        return $angkatan ? $this->getKurikulumByAngkatan($angkatan) : null;
    }

    public function getKurikulumIdByNim($username) {
        $angkatan = self::extractAngkatan($username);
        return $angkatan ? $this->getKurikulumIdByAngkatan($angkatan) : null;
    }

    /**
     * Get all rules with kurikulum name, ordered by angkatan_mulai
     */
    public function getAll() {
        return $this->db->query("
            SELECT ak.*, k.nama as nama_kurikulum, k.kode as kode_kurikulum, k.status as status_kurikulum
            FROM aturan_kurikulum ak
            JOIN kurikulum k ON ak.kurikulum_id = k.id
            ORDER BY ak.angkatan_mulai ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single rule by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT ak.*, k.nama as nama_kurikulum 
            FROM aturan_kurikulum ak
            JOIN kurikulum k ON ak.kurikulum_id = k.id
            WHERE ak.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new rule
     */
    public function create($data) {
        $this->validateRule($data);
        $stmt = $this->db->prepare("
            INSERT INTO aturan_kurikulum (angkatan_mulai, angkatan_selesai, kurikulum_id, keterangan, prioritas)
            VALUES (:mulai, :selesai, :kid, :ket, :pri)
        ");
        return $stmt->execute([
            'mulai' => $data['angkatan_mulai'],
            'selesai' => $data['angkatan_selesai'] ?: null,
            'kid' => $data['kurikulum_id'],
            'ket' => $data['keterangan'] ?? null,
            'pri' => $data['prioritas'] ?? 0
        ]);
    }

    /**
     * Update an existing rule
     */
    public function update($id, $data) {
        $this->validateRule($data, (int)$id);
        $stmt = $this->db->prepare("
            UPDATE aturan_kurikulum 
            SET angkatan_mulai = :mulai, angkatan_selesai = :selesai, 
                kurikulum_id = :kid, keterangan = :ket, prioritas = :pri
            WHERE id = :id
        ");
        return $stmt->execute([
            'mulai' => $data['angkatan_mulai'],
            'selesai' => $data['angkatan_selesai'] ?: null,
            'kid' => $data['kurikulum_id'],
            'ket' => $data['keterangan'] ?? null,
            'pri' => $data['prioritas'] ?? 0,
            'id' => $id
        ]);
    }

    /**
     * Delete a rule
     */
    public function delete($id) {
        $rule = $this->getById($id);
        if (!$rule) {
            throw new InvalidArgumentException('Aturan kurikulum tidak ditemukan.');
        }

        $end = $rule['angkatan_selesai'] !== null ? (int)$rule['angkatan_selesai'] : 9999;
        $stmtMahasiswa = $this->db->prepare("
            SELECT COUNT(*) FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE r.name = 'Mahasiswa'
              AND (
                  CASE 
                      WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                      WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                      ELSE NULL
                  END
              ) BETWEEN :mulai AND :selesai
        ");
        $stmtMahasiswa->execute([
            'mulai' => (int)$rule['angkatan_mulai'],
            'selesai' => $end
        ]);
        if ((int)$stmtMahasiswa->fetchColumn() > 0) {
            throw new RuntimeException('Aturan tidak dapat dihapus karena masih digunakan oleh mahasiswa.');
        }

        $stmt = $this->db->prepare("DELETE FROM aturan_kurikulum WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function validateRule($data, $excludeId = null) {
        $mulai = (int)($data['angkatan_mulai'] ?? 0);
        $selesai = !empty($data['angkatan_selesai']) ? (int)$data['angkatan_selesai'] : null;
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);

        if ($mulai < 1900 || $mulai > 9999 || ($selesai !== null && $selesai < $mulai)) {
            throw new InvalidArgumentException('Rentang angkatan tidak valid.');
        }

        $stmtKurikulum = $this->db->prepare('SELECT COUNT(*) FROM kurikulum WHERE id = ?');
        $stmtKurikulum->execute([$kurikulumId]);
        if ((int)$stmtKurikulum->fetchColumn() !== 1) {
            throw new InvalidArgumentException('Kurikulum yang dipilih tidak ditemukan.');
        }

        $sql = "
            SELECT COUNT(*) FROM aturan_kurikulum
            WHERE angkatan_mulai <= :selesai
              AND COALESCE(angkatan_selesai, 9999) >= :mulai
        ";
        $params = ['mulai' => $mulai, 'selesai' => $selesai ?? 9999];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        $stmtOverlap = $this->db->prepare($sql);
        $stmtOverlap->execute($params);
        if ((int)$stmtOverlap->fetchColumn() > 0) {
            throw new InvalidArgumentException('Rentang angkatan bertumpang tindih dengan aturan yang sudah ada.');
        }
    }

    /**
     * Find the kurikulum_id for a given angkatan (enrollment year).
     * Returns the kurikulum_id based on matching rules, prioritized by 'prioritas' DESC.
     * Returns null if no matching rule found.
     */
    public function getKurikulumIdByAngkatan($angkatan) {
        $angkatan = (int)$angkatan;
        $stmt = $this->db->prepare("
            SELECT kurikulum_id 
            FROM aturan_kurikulum
            WHERE angkatan_mulai <= :angkatan
              AND (angkatan_selesai IS NULL OR angkatan_selesai >= :angkatan2)
            ORDER BY prioritas DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute(['angkatan' => $angkatan, 'angkatan2' => $angkatan]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int)$result : null;
    }

    /**
     * Get the full kurikulum record for a given angkatan
     */
    public function getKurikulumByAngkatan($angkatan) {
        $kurikulumId = $this->getKurikulumIdByAngkatan($angkatan);
        if (!$kurikulumId) return null;

        $stmt = $this->db->prepare("SELECT * FROM kurikulum WHERE id = :id");
        $stmt->execute(['id' => $kurikulumId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get a summary: how many students per angkatan map to which kurikulum
     */
    public function getDistribusiMahasiswa() {
        $rows = $this->db->query("
            SELECT 
                CASE 
                    WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                    WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                    ELSE NULL
                END as angkatan,
                COUNT(*) as jumlah_mahasiswa
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Mahasiswa'
            GROUP BY angkatan
            HAVING angkatan IS NOT NULL
            ORDER BY angkatan ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $angkatan = $row['angkatan'];
            $kurikulumId = $this->getKurikulumIdByAngkatan($angkatan);
            
            $namaKurikulum = 'Belum Ditentukan';
            if ($kurikulumId) {
                $stmt = $this->db->prepare("SELECT nama FROM kurikulum WHERE id = :id");
                $stmt->execute(['id' => $kurikulumId]);
                $namaKurikulum = $stmt->fetchColumn() ?: 'Tidak Ditemukan';
            }

            $result[] = [
                'angkatan' => $angkatan,
                'jumlah_mahasiswa' => (int)$row['jumlah_mahasiswa'],
                'kurikulum_id' => $kurikulumId,
                'nama_kurikulum' => $namaKurikulum
            ];
        }

        return $result;
    }
}
