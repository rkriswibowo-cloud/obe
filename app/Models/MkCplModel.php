<?php
// app/Models/MkCplModel.php

class MkCplModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllMapping() {
        // Ambil semua data pemetaan
        $stmt = $this->db->query("SELECT * FROM mk_cpl");
        $results = $stmt->fetchAll();
        
        $mapping = [];
        foreach($results as $row) {
            $mapping[$row['mk_id']][$row['cpl_id']] = [
                'bobot_kontribusi' => $row['bobot_kontribusi'],
                'is_diukur' => $row['is_diukur']
            ];
        }
        return $mapping;
    }

    public function getMappingByKurikulum($kurikulum_id) {
        // Ambil data pemetaan untuk kurikulum tertentu
        $stmt = $this->db->prepare("
            SELECT mc.* 
            FROM mk_cpl mc
            JOIN mata_kuliah m ON mc.mk_id = m.id
            WHERE m.kurikulum_id = :kurikulum_id
        ");
        $stmt->execute(['kurikulum_id' => $kurikulum_id]);
        $results = $stmt->fetchAll();
        
        $mapping = [];
        foreach($results as $row) {
            $mapping[$row['mk_id']][$row['cpl_id']] = [
                'bobot_kontribusi' => $row['bobot_kontribusi'],
                'is_diukur' => $row['is_diukur']
            ];
        }
        return $mapping;
    }

    public function clearMappingForMKs($mk_ids) {
        if (empty($mk_ids)) return true;
        
        // Hapus pemetaan lama untuk daftar MK yang diberikan
        $inQuery = implode(',', array_fill(0, count($mk_ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM mk_cpl WHERE mk_id IN ($inQuery)");
        return $stmt->execute($mk_ids);
    }

    public function saveMapping($mk_id, $cpl_id, $bobot = 1, $is_diukur = 1) {
        // Simpan pemetaan baru
        $stmt = $this->db->prepare("
            INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi, is_diukur)
            VALUES (:mk_id, :cpl_id, :bobot, :is_diukur)
            ON DUPLICATE KEY UPDATE 
            bobot_kontribusi = VALUES(bobot_kontribusi),
            is_diukur = VALUES(is_diukur)
        ");
        return $stmt->execute([
            'mk_id' => $mk_id,
            'cpl_id' => $cpl_id,
            'bobot' => $bobot,
            'is_diukur' => $is_diukur
        ]);
    }
}
