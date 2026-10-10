<?php
require_once __DIR__ . '/../../config/database.php';

class RubrikModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getJenisMaster() {
        return [
            'RB-ANA' => ['jenis' => 'Rubrik Analitik', 'karakteristik' => 'Beberapa kriteria dinilai terpisah', 'contoh' => 'Proyek, laporan, presentasi, praktikum'],
            'RB-HOL' => ['jenis' => 'Rubrik Holistik', 'karakteristik' => 'Kinerja dinilai secara keseluruhan', 'contoh' => 'Presentasi singkat, performa'],
            'RB-SKL' => ['jenis' => 'Rating Scale', 'karakteristik' => 'Setiap indikator diberi skala', 'contoh' => 'Sikap, keaktifan, observasi'],
            'RB-CHK' => ['jenis' => 'Checklist', 'karakteristik' => 'Ya/Tidak atau Terpenuhi/Tidak', 'contoh' => 'Prosedur praktikum, SOP'],
            'RB-SKR' => ['jenis' => 'Skor Langsung', 'karakteristik' => 'Jawaban memiliki skor tertentu', 'contoh' => 'Kuis, UTS, UAS'],
            'RB-KUN' => ['jenis' => 'Kunci Jawaban', 'karakteristik' => 'Benar/salah berdasarkan kunci', 'contoh' => 'Pilihan ganda'],
            'RB-CMP' => ['jenis' => 'Rubrik Kompetensi', 'karakteristik' => 'Kompeten/belum kompeten', 'contoh' => 'Uji kompetensi, keterampilan'],
            'RB-PRF' => ['jenis' => 'Rubrik Portofolio', 'karakteristik' => 'Menilai kumpulan karya/bukti', 'contoh' => 'Portofolio mahasiswa'],
            'RB-PEER' => ['jenis' => 'Rubrik Peer Assessment', 'karakteristik' => 'Dinilai oleh teman sejawat', 'contoh' => 'Kerja kelompok'],
            'RB-SELF' => ['jenis' => 'Rubrik Self Assessment', 'karakteristik' => 'Penilaian diri', 'contoh' => 'Refleksi pembelajaran']
        ];
    }

    public function getAll() {
        $sql = "
            SELECT 
                r.*,
                (SELECT COUNT(*) FROM rubrik_kriteria rk WHERE rk.rubrik_id = r.id) as total_kriteria,
                (SELECT COUNT(*) FROM rubrik_level rl WHERE rl.rubrik_id = r.id) as total_level,
                (SELECT COALESCE(SUM(bobot), 0) FROM rubrik_kriteria rk WHERE rk.rubrik_id = r.id) as total_bobot
            FROM rubrik r
            ORDER BY FIELD(r.kode_jenis, 'RB-ANA','RB-HOL','RB-SKL','RB-CHK','RB-SKR','RB-KUN','RB-CMP','RB-PRF','RB-PEER','RB-SELF'), r.id
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        // Rubrik header
        $stmt = $this->db->prepare("SELECT * FROM rubrik WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $rubrik = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rubrik) {
            return null;
        }

        // Levels
        $stmtLevel = $this->db->prepare("SELECT * FROM rubrik_level WHERE rubrik_id = :id ORDER BY skor ASC");
        $stmtLevel->execute(['id' => $id]);
        $rubrik['levels'] = $stmtLevel->fetchAll(PDO::FETCH_ASSOC);

        // Kriteria
        $stmtKriteria = $this->db->prepare("SELECT * FROM rubrik_kriteria WHERE rubrik_id = :id ORDER BY id ASC");
        $stmtKriteria->execute(['id' => $id]);
        $rubrik['kriteria'] = $stmtKriteria->fetchAll(PDO::FETCH_ASSOC);

        // Deskriptor Matrix
        $stmtDesc = $this->db->prepare("
            SELECT rd.* 
            FROM rubrik_deskriptor rd
            JOIN rubrik_kriteria rk ON rd.kriteria_id = rk.id
            WHERE rk.rubrik_id = :id
        ");
        $stmtDesc->execute(['id' => $id]);
        $descRows = $stmtDesc->fetchAll(PDO::FETCH_ASSOC);

        $descriptors = [];
        foreach ($descRows as $row) {
            $descriptors[$row['kriteria_id']][$row['level_id']] = $row['deskripsi'];
        }
        $rubrik['descriptors'] = $descriptors;

        return $rubrik;
    }

    public function create($nama, $kodeJenis, $status, $levels, $kriterias, $descriptors) {
        $master = self::getJenisMaster();
        if (!isset($master[$kodeJenis])) return false;
        $meta = $master[$kodeJenis];
        $this->db->beginTransaction();
        try {
            // 1. Insert Rubrik
            $stmt = $this->db->prepare("INSERT INTO rubrik (nama, kode_jenis, jenis, karakteristik, contoh_penggunaan, deskripsi, status) VALUES (:nama, :kode_jenis, :jenis, :karakteristik, :contoh, :deskripsi, :status)");
            $stmt->execute([
                'nama' => $nama,
                'kode_jenis' => $kodeJenis,
                'jenis' => $meta['jenis'],
                'karakteristik' => $meta['karakteristik'],
                'contoh' => $meta['contoh'],
                'deskripsi' => $meta['karakteristik'] . '. Contoh penggunaan: ' . $meta['contoh'] . '.',
                'status' => $status
            ]);
            $rubrikId = $this->db->lastInsertId();

            // 2. Insert Levels
            $levelIdMap = []; // index -> db_id
            $stmtLevel = $this->db->prepare("INSERT INTO rubrik_level (rubrik_id, level_nama, skor) VALUES (:rubrik_id, :level_nama, :skor)");
            foreach ($levels as $idx => $lvl) {
                $stmtLevel->execute([
                    'rubrik_id' => $rubrikId,
                    'level_nama' => $lvl['nama'],
                    'skor' => $lvl['skor']
                ]);
                $levelIdMap[$idx] = $this->db->lastInsertId();
            }

            // 3. Insert Kriteria
            $stmtKrit = $this->db->prepare("INSERT INTO rubrik_kriteria (rubrik_id, kriteria, bobot) VALUES (:rubrik_id, :kriteria, :bobot)");
            $stmtDesc = $this->db->prepare("INSERT INTO rubrik_deskriptor (kriteria_id, level_id, deskripsi) VALUES (:kriteria_id, :level_id, :deskripsi)");

            foreach ($kriterias as $kIdx => $krit) {
                $stmtKrit->execute([
                    'rubrik_id' => $rubrikId,
                    'kriteria' => $krit['nama'],
                    'bobot' => $krit['bobot']
                ]);
                $kriteriaId = $this->db->lastInsertId();

                // 4. Insert Descriptors for this kriteria
                if (isset($descriptors[$kIdx])) {
                    foreach ($descriptors[$kIdx] as $lIdx => $descText) {
                        if (isset($levelIdMap[$lIdx])) {
                            $stmtDesc->execute([
                                'kriteria_id' => $kriteriaId,
                                'level_id' => $levelIdMap[$lIdx],
                                'deskripsi' => $descText ?? ''
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
            return $rubrikId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Gagal membuat rubrik: " . $e->getMessage());
            return false;
        }
    }

    public function saveMatrix($rubrikId, $levels, $kriterias, $deskriptors) {
        $this->db->beginTransaction();
        try {
            // 1. Clear old levels and criterias (cascade clears descriptors)
            $stmtDelLvl = $this->db->prepare("DELETE FROM rubrik_level WHERE rubrik_id = :id");
            $stmtDelLvl->execute(['id' => $rubrikId]);

            $stmtDelKrit = $this->db->prepare("DELETE FROM rubrik_kriteria WHERE rubrik_id = :id");
            $stmtDelKrit->execute(['id' => $rubrikId]);

            // 2. Insert new levels
            $levelKeyMap = [];
            $stmtInsLvl = $this->db->prepare("INSERT INTO rubrik_level (rubrik_id, level_nama, skor) VALUES (:rubrik_id, :level_nama, :skor)");
            foreach ($levels as $lKey => $lvl) {
                $stmtInsLvl->execute([
                    'rubrik_id' => $rubrikId,
                    'level_nama' => $lvl['level_nama'],
                    'skor' => $lvl['skor']
                ]);
                $levelKeyMap[$lKey] = $this->db->lastInsertId();
            }

            // 3. Insert new criterias & descriptors
            $stmtInsKrit = $this->db->prepare("INSERT INTO rubrik_kriteria (rubrik_id, kriteria, bobot) VALUES (:rubrik_id, :kriteria, :bobot)");
            $stmtInsDesc = $this->db->prepare("INSERT INTO rubrik_deskriptor (kriteria_id, level_id, deskripsi) VALUES (:kriteria_id, :level_id, :deskripsi)");

            foreach ($kriterias as $kKey => $krit) {
                $stmtInsKrit->execute([
                    'rubrik_id' => $rubrikId,
                    'kriteria' => $krit['kriteria'],
                    'bobot' => $krit['bobot']
                ]);
                $kriteriaId = $this->db->lastInsertId();

                if (isset($deskriptors[$kKey])) {
                    foreach ($deskriptors[$kKey] as $lKey => $descText) {
                        if (isset($levelKeyMap[$lKey])) {
                            $stmtInsDesc->execute([
                                'kriteria_id' => $kriteriaId,
                                'level_id' => $levelKeyMap[$lKey],
                                'deskripsi' => $descText ?? ''
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Gagal saveMatrix: " . $e->getMessage());
            return false;
        }
    }

    public function duplicate($rubrikId) {
        $source = $this->getById($rubrikId);
        if (!$source) return false;

        $namaBaru = $source['nama'] . ' (Salinan)';
        
        // Format for create()
        $levels = [];
        foreach ($source['levels'] as $lvl) {
            $levels[] = [
                'nama' => $lvl['level_nama'],
                'skor' => $lvl['skor']
            ];
        }

        $kriterias = [];
        $descriptors = [];
        foreach ($source['kriteria'] as $kIdx => $krit) {
            $kriterias[] = [
                'nama' => $krit['kriteria'],
                'bobot' => $krit['bobot']
            ];
            foreach ($source['levels'] as $lIdx => $lvl) {
                $descriptors[$kIdx][$lIdx] = $source['descriptors'][$krit['id']][$lvl['id']] ?? '';
            }
        }

        return $this->create($namaBaru, $source['kode_jenis'] ?? 'RB-ANA', $source['status'], $levels, $kriterias, $descriptors);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rubrik WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
