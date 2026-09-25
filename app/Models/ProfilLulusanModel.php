<?php
// app/Models/ProfilLulusanModel.php

class ProfilLulusanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($kurikulumId = null) {
        $sql = "
            SELECT pl.*, k.nama as nama_kurikulum, k.kode as kode_kurikulum,
                   GROUP_CONCAT(c.kode ORDER BY c.kode SEPARATOR ', ') as cpl_kodes,
                   COUNT(c.id) as total_cpl
            FROM profil_lulusan pl
            JOIN kurikulum k ON pl.kurikulum_id = k.id
            LEFT JOIN pl_cpl pc ON pl.id = pc.pl_id
            LEFT JOIN cpl c ON pc.cpl_id = c.id
        ";

        $params = [];
        if ($kurikulumId) {
            $sql .= " WHERE pl.kurikulum_id = :kid";
            $params['kid'] = $kurikulumId;
        }

        $sql .= " GROUP BY pl.id ORDER BY pl.kode ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT pl.*, k.nama as nama_kurikulum 
            FROM profil_lulusan pl 
            JOIN kurikulum k ON pl.kurikulum_id = k.id 
            WHERE pl.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            $stmtCpl = $this->db->prepare("SELECT cpl_id FROM pl_cpl WHERE pl_id = :id");
            $stmtCpl->execute(['id' => $id]);
            $row['cpl_ids'] = $stmtCpl->fetchAll(PDO::FETCH_COLUMN);
        }

        return $row;
    }

    public function create($data) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO profil_lulusan (kurikulum_id, kode, nama, deskripsi)
                VALUES (:kurikulum_id, :kode, :nama, :deskripsi)
            ");
            $stmt->execute([
                'kurikulum_id' => $data['kurikulum_id'],
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'deskripsi' => $data['deskripsi']
            ]);
            $plId = $this->db->lastInsertId();

            if (!empty($data['cpl_ids'])) {
                $stmtCpl = $this->db->prepare("INSERT INTO pl_cpl (pl_id, cpl_id) VALUES (?, ?)");
                foreach ($data['cpl_ids'] as $cplId) {
                    $stmtCpl->execute([$plId, $cplId]);
                }
            }

            $this->db->commit();
            return $plId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE profil_lulusan
                SET kurikulum_id = :kurikulum_id, kode = :kode, nama = :nama, deskripsi = :deskripsi
                WHERE id = :id
            ");
            $stmt->execute([
                'kurikulum_id' => $data['kurikulum_id'],
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'deskripsi' => $data['deskripsi'],
                'id' => $id
            ]);

            // Sync CPL
            $this->db->prepare("DELETE FROM pl_cpl WHERE pl_id = :id")->execute(['id' => $id]);
            if (!empty($data['cpl_ids'])) {
                $stmtCpl = $this->db->prepare("INSERT INTO pl_cpl (pl_id, cpl_id) VALUES (?, ?)");
                foreach ($data['cpl_ids'] as $cplId) {
                    $stmtCpl->execute([$id, $cplId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM profil_lulusan WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCplByKurikulum($kurikulumId) {
        $stmt = $this->db->prepare("SELECT id, kode, rumusan FROM cpl WHERE kurikulum_id = :kid ORDER BY kode ASC");
        $stmt->execute(['kid' => $kurikulumId]);
        return $stmt->fetchAll();
    }
}
