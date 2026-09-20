<?php
require_once __DIR__ . '/../../config/database.php';

class RubrikModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $sql = "
            SELECT 
                r.*,
                (SELECT COUNT(*) FROM rubrik_kriteria rk WHERE rk.rubrik_id = r.id) as total_kriteria,
                (SELECT COUNT(*) FROM rubrik_level rl WHERE rl.rubrik_id = r.id) as total_level,
                (SELECT COALESCE(SUM(bobot), 0) FROM rubrik_kriteria rk WHERE rk.rubrik_id = r.id) as total_bobot
            FROM rubrik r
            ORDER BY r.id DESC
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

    public function create($nama, $jenis, $status, $levels, $kriterias, $descriptors) {
        $this->db->beginTransaction();
        try {
            // 1. Insert Rubrik
            $stmt = $this->db->prepare("INSERT INTO rubrik (nama, jenis, status) VALUES (:nama, :jenis, :status)");
            $stmt->execute([
                'nama' => $nama,
                'jenis' => $jenis,
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

        return $this->create($namaBaru, $source['jenis'], $source['status'], $levels, $kriterias, $descriptors);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rubrik WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
