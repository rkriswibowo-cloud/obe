<?php
// app/Models/CqiModel.php

require_once __DIR__ . '/../../config/database.php';

class CqiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filter = []) {
        $sql = "
            SELECT cqi.*, 
                   c.kode as cpl_kode, 
                   c.rumusan as cpl_rumusan,
                   ta.nama as tahun_akademik_nama
            FROM cqi
            JOIN cpl c ON cqi.cpl_id = c.id
            LEFT JOIN tahun_akademik ta ON cqi.tahun_akademik_id = ta.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filter['status'])) {
            $sql .= " AND cqi.status = :status";
            $params['status'] = $filter['status'];
        }
        if (!empty($filter['cpl_id'])) {
            $sql .= " AND cqi.cpl_id = :cpl_id";
            $params['cpl_id'] = $filter['cpl_id'];
        }
        if (!empty($filter['tahun_akademik_id'])) {
            $sql .= " AND cqi.tahun_akademik_id = :ta_id";
            $params['ta_id'] = $filter['tahun_akademik_id'];
        }

        $sql .= " ORDER BY cqi.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "
            SELECT cqi.*, 
                   c.kode as cpl_kode, 
                   c.rumusan as cpl_rumusan,
                   c.target_capaian as cpl_default_target,
                   ta.nama as tahun_akademik_nama
            FROM cqi
            JOIN cpl c ON cqi.cpl_id = c.id
            LEFT JOIN tahun_akademik ta ON cqi.tahun_akademik_id = ta.id
            WHERE cqi.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "
            INSERT INTO cqi 
            (cpl_id, tahun_akademik_id, semester, target_capaian, capaian_aktual,
             analisis_masalah, akar_masalah, rencana_tindak_lanjut, pic, deadline,
             monitoring_evaluasi, status, hasil_re_measurement)
            VALUES 
            (:cpl_id, :ta_id, :semester, :target_capaian, :capaian_aktual,
             :analisis_masalah, :akar_masalah, :rencana_tindak_lanjut, :pic, :deadline,
             :monitoring_evaluasi, :status, :hasil_re_measurement)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'cpl_id' => $data['cpl_id'],
            'ta_id' => !empty($data['tahun_akademik_id']) ? $data['tahun_akademik_id'] : null,
            'semester' => $data['semester'] ?? 'Ganjil',
            'target_capaian' => $data['target_capaian'] ?? 70.00,
            'capaian_aktual' => $data['capaian_aktual'] ?? 0.00,
            'analisis_masalah' => $data['analisis_masalah'],
            'akar_masalah' => $data['akar_masalah'],
            'rencana_tindak_lanjut' => $data['rencana_tindak_lanjut'],
            'pic' => $data['pic'],
            'deadline' => $data['deadline'],
            'monitoring_evaluasi' => $data['monitoring_evaluasi'] ?? null,
            'status' => $data['status'] ?? 'OPEN',
            'hasil_re_measurement' => !empty($data['hasil_re_measurement']) ? $data['hasil_re_measurement'] : null
        ]);
    }

    public function update($id, $data) {
        $sql = "
            UPDATE cqi 
            SET cpl_id = :cpl_id,
                tahun_akademik_id = :ta_id,
                semester = :semester,
                target_capaian = :target_capaian,
                capaian_aktual = :capaian_aktual,
                analisis_masalah = :analisis_masalah,
                akar_masalah = :akar_masalah,
                rencana_tindak_lanjut = :rencana_tindak_lanjut,
                pic = :pic,
                deadline = :deadline,
                monitoring_evaluasi = :monitoring_evaluasi,
                status = :status,
                hasil_re_measurement = :hasil_re_measurement
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'cpl_id' => $data['cpl_id'],
            'ta_id' => !empty($data['tahun_akademik_id']) ? $data['tahun_akademik_id'] : null,
            'semester' => $data['semester'] ?? 'Ganjil',
            'target_capaian' => $data['target_capaian'] ?? 70.00,
            'capaian_aktual' => $data['capaian_aktual'] ?? 0.00,
            'analisis_masalah' => $data['analisis_masalah'],
            'akar_masalah' => $data['akar_masalah'],
            'rencana_tindak_lanjut' => $data['rencana_tindak_lanjut'],
            'pic' => $data['pic'],
            'deadline' => $data['deadline'],
            'monitoring_evaluasi' => $data['monitoring_evaluasi'] ?? null,
            'status' => $data['status'] ?? 'OPEN',
            'hasil_re_measurement' => !empty($data['hasil_re_measurement']) ? $data['hasil_re_measurement'] : null,
            'id' => $id
        ]);
    }

    public function updateProgress($id, $status, $monitoringNotes, $reMeasure = null) {
        $sql = "
            UPDATE cqi 
            SET status = :status,
                monitoring_evaluasi = :monitoring_evaluasi,
                hasil_re_measurement = :hasil_re_measurement
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'monitoring_evaluasi' => $monitoringNotes,
            'hasil_re_measurement' => !empty($reMeasure) ? $reMeasure : null,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM cqi WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getCountsByStatus() {
        $sql = "
            SELECT status, COUNT(*) as total 
            FROM cqi 
            GROUP BY status
        ";
        $stmt = $this->db->query($sql);
        $raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'TOTAL' => array_sum($raw),
            'OPEN' => $raw['OPEN'] ?? 0,
            'IN_PROGRESS' => $raw['IN_PROGRESS'] ?? 0,
            'COMPLETED' => $raw['COMPLETED'] ?? 0,
            'RE_MEASURED' => $raw['RE_MEASURED'] ?? 0,
        ];
    }

    public function getUnderperformingCpls() {
        // Cari CPL yang nilai asesmen aktualnya di bawah target_capaian
        $sql = "
            SELECT 
                c.id as cpl_id,
                c.kode as cpl_kode,
                c.rumusan as cpl_rumusan,
                c.target_capaian,
                ROUND(SUM(an.nilai * sc.bobot) / NULLIF(SUM(sc.bobot), 0), 2) as rata_rata_capaian,
                COUNT(DISTINCT an.mahasiswa_id) as total_mahasiswa_terukur
            FROM cpl c
            JOIN cpmk_cpl cc ON c.id = cc.cpl_id
            JOIN cpmk cp ON cc.cpmk_id = cp.id
            JOIN sub_cpmk sc ON cp.id = sc.cpmk_id
            JOIN asesmen_nilai an ON sc.id = an.sub_cpmk_id
            GROUP BY c.id
            HAVING rata_rata_capaian < c.target_capaian
            ORDER BY rata_rata_capaian ASC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
