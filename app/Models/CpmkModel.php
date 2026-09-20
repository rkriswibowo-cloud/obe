<?php
// app/Models/CpmkModel.php

class CpmkModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByMkId($mkId) {
        $stmt = $this->db->prepare("SELECT * FROM cpmk WHERE mk_id = :mk_id ORDER BY kode ASC");
        $stmt->execute(['mk_id' => $mkId]);
        $cpmkList = $stmt->fetchAll();

        // Get related CPLs for each CPMK
        foreach ($cpmkList as &$cpmk) {
            $stmt = $this->db->prepare("
                SELECT c.* 
                FROM cpl c 
                JOIN cpmk_cpl cc ON c.id = cc.cpl_id 
                WHERE cc.cpmk_id = :cpmk_id
                ORDER BY c.kode ASC
            ");
            $stmt->execute(['cpmk_id' => $cpmk['id']]);
            $cpmk['cpls'] = $stmt->fetchAll();
        }

        return $cpmkList;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM cpmk WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cpmk = $stmt->fetch();
        
        if ($cpmk) {
            $stmt = $this->db->prepare("SELECT cpl_id FROM cpmk_cpl WHERE cpmk_id = :cpmk_id");
            $stmt->execute(['cpmk_id' => $id]);
            $cpmk['cpl_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        return $cpmk;
    }

    public function create($data, $cplIds = []) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (:mk_id, :kode, :rumusan)");
            $stmt->execute([
                'mk_id' => $data['mk_id'],
                'kode' => $data['kode'],
                'rumusan' => $data['rumusan']
            ]);
            $cpmkId = $this->db->lastInsertId();

            if (!empty($cplIds)) {
                $stmt = $this->db->prepare("INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
                foreach ($cplIds as $cplId) {
                    $stmt->execute(['cpmk_id' => $cpmkId, 'cpl_id' => $cplId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update($id, $data, $cplIds = []) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE cpmk SET kode = :kode, rumusan = :rumusan WHERE id = :id");
            $stmt->execute([
                'kode' => $data['kode'],
                'rumusan' => $data['rumusan'],
                'id' => $id
            ]);

            // Sync CPLs
            $stmt = $this->db->prepare("DELETE FROM cpmk_cpl WHERE cpmk_id = :cpmk_id");
            $stmt->execute(['cpmk_id' => $id]);

            if (!empty($cplIds)) {
                $stmt = $this->db->prepare("INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (:cpmk_id, :cpl_id)");
                foreach ($cplIds as $cplId) {
                    $stmt->execute(['cpmk_id' => $id, 'cpl_id' => $cplId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cpmk WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
