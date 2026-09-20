<?php
// app/Models/SubCpmkModel.php

class SubCpmkModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByCpmkId($cpmkId) {
        $stmt = $this->db->prepare("SELECT * FROM sub_cpmk WHERE cpmk_id = :cpmk_id ORDER BY urutan ASC, kode ASC");
        $stmt->execute(['cpmk_id' => $cpmkId]);
        $subCpmkList = $stmt->fetchAll();

        // Get indikator for each Sub-CPMK
        foreach ($subCpmkList as &$sub) {
            $stmt = $this->db->prepare("SELECT * FROM sub_cpmk_indikator WHERE sub_cpmk_id = :sub_cpmk_id");
            $stmt->execute(['sub_cpmk_id' => $sub['id']]);
            $sub['indikators'] = $stmt->fetchAll();
        }

        return $subCpmkList;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sub_cpmk WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $subCpmk = $stmt->fetch();
        
        if ($subCpmk) {
            $stmt = $this->db->prepare("SELECT * FROM sub_cpmk_indikator WHERE sub_cpmk_id = :sub_cpmk_id");
            $stmt->execute(['sub_cpmk_id' => $id]);
            $subCpmk['indikators'] = $stmt->fetchAll();
        }
        
        return $subCpmk;
    }

    public function create($data, $indikators = []) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, level_taksonomi, pengalaman_belajar, asesmen_penilaian, bobot, urutan) 
                VALUES (:cpmk_id, :kode, :rumusan, :level_taksonomi, :pengalaman_belajar, :asesmen_penilaian, :bobot, :urutan)
            ");
            $stmt->execute([
                'cpmk_id' => $data['cpmk_id'],
                'kode' => $data['kode'],
                'rumusan' => $data['rumusan'],
                'level_taksonomi' => $data['level_taksonomi'] ?? null,
                'pengalaman_belajar' => $data['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
                'asesmen_penilaian' => $data['asesmen_penilaian'] ?? 'Pretes dan postes',
                'bobot' => $data['bobot'] ?? 0,
                'urutan' => $data['urutan'] ?? 0
            ]);
            $subCpmkId = $this->db->lastInsertId();

            if (!empty($indikators)) {
                $stmt = $this->db->prepare("INSERT INTO sub_cpmk_indikator (sub_cpmk_id, indikator) VALUES (:sub_cpmk_id, :indikator)");
                foreach ($indikators as $ind) {
                    if (trim($ind) !== '') {
                        $stmt->execute(['sub_cpmk_id' => $subCpmkId, 'indikator' => trim($ind)]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update($id, $data, $indikators = []) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE sub_cpmk 
                SET kode = :kode, rumusan = :rumusan, level_taksonomi = :level_taksonomi, 
                    pengalaman_belajar = :pengalaman_belajar, asesmen_penilaian = :asesmen_penilaian,
                    bobot = :bobot, urutan = :urutan 
                WHERE id = :id
            ");
            $stmt->execute([
                'kode' => $data['kode'],
                'rumusan' => $data['rumusan'],
                'level_taksonomi' => $data['level_taksonomi'] ?? null,
                'pengalaman_belajar' => $data['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
                'asesmen_penilaian' => $data['asesmen_penilaian'] ?? 'Pretes dan postes',
                'bobot' => $data['bobot'] ?? 0,
                'urutan' => $data['urutan'] ?? 0,
                'id' => $id
            ]);

            // Sync Indikators
            $stmt = $this->db->prepare("DELETE FROM sub_cpmk_indikator WHERE sub_cpmk_id = :sub_cpmk_id");
            $stmt->execute(['sub_cpmk_id' => $id]);

            if (!empty($indikators)) {
                $stmt = $this->db->prepare("INSERT INTO sub_cpmk_indikator (sub_cpmk_id, indikator) VALUES (:sub_cpmk_id, :indikator)");
                foreach ($indikators as $ind) {
                    if (trim($ind) !== '') {
                        $stmt->execute(['sub_cpmk_id' => $id, 'indikator' => trim($ind)]);
                    }
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
        $stmt = $this->db->prepare("DELETE FROM sub_cpmk WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
