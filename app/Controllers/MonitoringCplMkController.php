<?php
// app/Controllers/MonitoringCplMkController.php

require_once __DIR__ . '/../Models/MonitoringCplMkModel.php';
require_once __DIR__ . '/../Support/AccessContext.php';

class MonitoringCplMkController
{
    private MonitoringCplMkModel $model;
    private AccessContext $access;

    public function __construct()
    {
        $this->model = new MonitoringCplMkModel();
        $this->access = new AccessContext();
    }

    /**
     * Halaman Utama: Daftar Monitoring Ketercapaian CPL per Mata Kuliah
     */
    public function index(): void
    {
        $tahunAkademik = $this->model->getTahunAkademik();
        $activeTa = $this->model->getActiveTahunAkademik();
        $kurikulumList = $this->model->getKurikulumList();
        $prodiList = $this->model->getProgramStudiList();

        // Parameter Filter
        $selectedTaId = isset($_GET['tahun_akademik_id']) && $_GET['tahun_akademik_id'] !== ''
            ? (int)$_GET['tahun_akademik_id']
            : null;

        $selectedKurikulumId = isset($_GET['kurikulum_id']) && $_GET['kurikulum_id'] !== ''
            ? (int)$_GET['kurikulum_id']
            : null;

        $selectedProdiId = isset($_GET['prodi_id']) && $_GET['prodi_id'] !== ''
            ? (int)$_GET['prodi_id']
            : null;

        $search = trim($_GET['search'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        // Validasi Otorisasi Kaprodi terhadap Kurikulum/Prodi terpilih
        if ($selectedKurikulumId && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) {
            $this->access->assertKurikulum($selectedKurikulumId);
        }

        $monitoringData = $this->model->getCoursesMonitoring(
            $selectedTaId,
            $selectedKurikulumId,
            $selectedProdiId,
            $search,
            $statusFilter
        );

        $courses = $monitoringData['courses'];
        $summary = $monitoringData['summary'];

        require __DIR__ . '/../../views/monitoring_cpl_mk/index.php';
    }

    /**
     * Halaman Detail: Rincian Mahasiswa & Ketercapaian CPL per Mata Kuliah
     */
    public function detail(): void
    {
        $mkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($mkId <= 0) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/monitoring-cpl-mk');
            exit;
        }

        $tahunAkademik = $this->model->getTahunAkademik();
        $selectedTaId = isset($_GET['tahun_akademik_id']) && $_GET['tahun_akademik_id'] !== ''
            ? (int)$_GET['tahun_akademik_id']
            : null;

        $selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== ''
            ? (int)$_GET['kelas_id']
            : null;

        $statusFilter = trim($_GET['status'] ?? '');
        $searchMhs = trim($_GET['search'] ?? '');

        $detailData = $this->model->getCourseStudentDetail(
            $mkId,
            $selectedTaId,
            $selectedKelasId,
            $statusFilter,
            $searchMhs
        );

        if (!$detailData) {
            echo '<div style="padding: 30px; font-family: sans-serif; text-align: center;">';
            echo '<h3>Mata Kuliah tidak ditemukan atau berada di luar hak akses Anda.</h3>';
            echo '<a href="' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/monitoring-cpl-mk" style="color: #0284c7;">Kembali ke Monitoring</a>';
            echo '</div>';
            exit;
        }

        $course = $detailData['course'];
        $cpls = $detailData['cpls'];
        $classes = $detailData['classes'];
        $students = $detailData['students'];
        $kpi = $detailData['kpi'];

        require __DIR__ . '/../../views/monitoring_cpl_mk/detail.php';
    }

    /**
     * Ekspor Laporan CSV Ketercapaian CPL Mahasiswa per Mata Kuliah
     */
    public function export(): void
    {
        $mkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($mkId <= 0) {
            exit('ID Mata Kuliah tidak valid.');
        }

        $selectedTaId = isset($_GET['tahun_akademik_id']) && $_GET['tahun_akademik_id'] !== ''
            ? (int)$_GET['tahun_akademik_id']
            : null;

        $detailData = $this->model->getCourseStudentDetail($mkId, $selectedTaId);
        if (!$detailData) {
            exit('Data Mata Kuliah tidak ditemukan.');
        }

        $course = $detailData['course'];
        $cpls = $detailData['cpls'];
        $students = $detailData['students'];

        $filename = 'monitoring_cpl_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $course['kode']) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 untuk Excel
        fputs($out, "\xEF\xBB\xBF");

        // Info Dokumen
        fputcsv($out, ['LAPORAN MONITORING KETERCAPAIAN CPL MAHASISWA']);
        fputcsv($out, ['Mata Kuliah', $course['kode'] . ' - ' . $course['nama']]);
        fputcsv($out, ['SKS / Semester', $course['sks'] . ' SKS / Semester ' . $course['semester']]);
        fputcsv($out, ['Program Studi', $course['prodi_nama'] . ' (' . $course['kurikulum_nama'] . ')']);
        fputcsv($out, ['Dosen PJMK', $course['dosen_pengampu']]);
        fputcsv($out, ['Tanggal Unduh', date('d-m-Y H:i:s')]);
        fputcsv($out, []);

        // Header Tabel
        $header = ['No', 'NIM', 'Nama Mahasiswa', 'Kelas'];
        foreach ($cpls as $cp) {
            $header[] = $cp['kode'] . ' (Target: ' . $cp['target'] . '%)';
            $header[] = 'Status ' . $cp['kode'];
        }
        $header[] = 'Rata-rata CPL (%)';
        $header[] = 'Status Keseluruhan CPL MK';
        fputcsv($out, $header);

        // Baris Data Mahasiswa
        $no = 1;
        foreach ($students as $st) {
            $row = [
                $no++,
                $st['nim'],
                $st['nama_lengkap'],
                $st['nama_kelas'] ?? '-',
            ];

            foreach ($cpls as $cp) {
                $cid = $cp['id'];
                $cplData = $st['cpl_breakdown'][$cid] ?? null;
                if ($cplData && $cplData['is_measured']) {
                    $row[] = number_format($cplData['score'], 2);
                    $row[] = $cplData['status'];
                } else {
                    $row[] = '-';
                    $row[] = 'Belum Dinilai';
                }
            }

            $row[] = $st['overall_score'] !== null ? number_format($st['overall_score'], 2) : '-';
            $row[] = $st['overall_status'];

            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }
}
