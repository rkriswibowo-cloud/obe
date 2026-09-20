<?php
// app/Controllers/CqiController.php

require_once __DIR__ . '/../Models/CqiModel.php';
require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/TahunAkademikModel.php';

class CqiController {
    private $cqiModel;
    private $cplModel;
    private $taModel;

    public function __construct() {
        $this->cqiModel = new CqiModel();
        $this->cplModel = new CplModel();
        $this->taModel = new TahunAkademikModel();
    }

    public function index() {
        $filter = [
            'status' => $_GET['status'] ?? '',
            'cpl_id' => $_GET['cpl_id'] ?? '',
            'tahun_akademik_id' => $_GET['tahun_akademik_id'] ?? ''
        ];

        $cqiList = $this->cqiModel->getAll($filter);
        $statusCounts = $this->cqiModel->getCountsByStatus();
        $underperformingCpls = $this->cqiModel->getUnderperformingCpls();

        $cplList = $this->cplModel->getAll();
        $taList = $this->taModel->getAll();

        require __DIR__ . '/../../views/cqi/index.php';
    }

    public function create() {
        $cplList = $this->cplModel->getAll();
        $taList = $this->taModel->getAll();

        $selectedCplId = $_GET['cpl_id'] ?? '';
        $selectedCpl = null;
        if ($selectedCplId) {
            $selectedCpl = $this->cplModel->getById($selectedCplId);
        }

        $defaultTarget = $selectedCpl ? $selectedCpl['target_capaian'] : 70.00;
        $defaultAktual = $_GET['aktual'] ?? 0.00;

        require __DIR__ . '/../../views/cqi/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $cplId = $_POST['cpl_id'] ?? null;
        if (!$cplId) {
            $_SESSION['error'] = "CPL wajib dipilih.";
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi/create');
            exit;
        }

        $data = [
            'cpl_id' => $cplId,
            'tahun_akademik_id' => $_POST['tahun_akademik_id'] ?? null,
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'target_capaian' => floatval($_POST['target_capaian'] ?? 70.00),
            'capaian_aktual' => floatval($_POST['capaian_aktual'] ?? 0.00),
            'analisis_masalah' => trim($_POST['analisis_masalah'] ?? ''),
            'akar_masalah' => trim($_POST['akar_masalah'] ?? ''),
            'rencana_tindak_lanjut' => trim($_POST['rencana_tindak_lanjut'] ?? ''),
            'pic' => trim($_POST['pic'] ?? ''),
            'deadline' => $_POST['deadline'] ?? date('Y-m-d', strtotime('+3 months')),
            'monitoring_evaluasi' => trim($_POST['monitoring_evaluasi'] ?? ''),
            'status' => $_POST['status'] ?? 'OPEN',
            'hasil_re_measurement' => !empty($_POST['hasil_re_measurement']) ? floatval($_POST['hasil_re_measurement']) : null
        ];

        if ($this->cqiModel->create($data)) {
            $_SESSION['success'] = "Tindak lanjut perbaikan mutu (CQI) berhasil dicatat!";
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
            exit;
        } else {
            $_SESSION['error'] = "Gagal menyimpan data CQI.";
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi/create');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
            exit;
        }

        $cqi = $this->cqiModel->getById($id);
        if (!$cqi) {
            $_SESSION['error'] = "Data CQI tidak ditemukan.";
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
            exit;
        }

        $cplList = $this->cplModel->getAll();
        $taList = $this->taModel->getAll();

        require __DIR__ . '/../../views/cqi/form.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
            exit;
        }

        $data = [
            'cpl_id' => $_POST['cpl_id'],
            'tahun_akademik_id' => $_POST['tahun_akademik_id'] ?? null,
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'target_capaian' => floatval($_POST['target_capaian'] ?? 70.00),
            'capaian_aktual' => floatval($_POST['capaian_aktual'] ?? 0.00),
            'analisis_masalah' => trim($_POST['analisis_masalah'] ?? ''),
            'akar_masalah' => trim($_POST['akar_masalah'] ?? ''),
            'rencana_tindak_lanjut' => trim($_POST['rencana_tindak_lanjut'] ?? ''),
            'pic' => trim($_POST['pic'] ?? ''),
            'deadline' => $_POST['deadline'] ?? date('Y-m-d'),
            'monitoring_evaluasi' => trim($_POST['monitoring_evaluasi'] ?? ''),
            'status' => $_POST['status'] ?? 'OPEN',
            'hasil_re_measurement' => !empty($_POST['hasil_re_measurement']) ? floatval($_POST['hasil_re_measurement']) : null
        ];

        if ($this->cqiModel->update($id, $data)) {
            $_SESSION['success'] = "Data CQI berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data CQI.";
        }

        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
        exit;
    }

    public function status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'OPEN';
        $notes = trim($_POST['monitoring_evaluasi'] ?? '');
        $reMeasure = !empty($_POST['hasil_re_measurement']) ? floatval($_POST['hasil_re_measurement']) : null;

        if ($id && $this->cqiModel->updateProgress($id, $status, $notes, $reMeasure)) {
            $_SESSION['success'] = "Status perbaikan CQI berhasil diperbarui ke: $status!";
        } else {
            $_SESSION['error'] = "Gagal memperbarui status CQI.";
        }

        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
        exit;
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->cqiModel->delete($id);
            $_SESSION['success'] = "Catatan CQI berhasil dihapus.";
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/cqi');
        exit;
    }
}

