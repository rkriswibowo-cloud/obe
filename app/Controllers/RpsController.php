<?php
// app/Controllers/RpsController.php

require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/CpmkModel.php';
require_once __DIR__ . '/../Models/SubCpmkModel.php';
require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/RpsPertemuanModel.php';
require_once __DIR__ . '/../Models/RpsModel.php';

class RpsController {
    private $mkModel;
    private $cpmkModel;
    private $subCpmkModel;
    private $cplModel;
    private $pertemuanModel;
    private $rpsModel;

    public function __construct() {
        $this->mkModel = new MataKuliahModel();
        $this->cpmkModel = new CpmkModel();
        $this->subCpmkModel = new SubCpmkModel();
        $this->cplModel = new CplModel();
        $this->pertemuanModel = new RpsPertemuanModel();
        $this->rpsModel = new RpsModel();
    }

    public function index() {
        $mkId = $_GET['mk_id'] ?? null;
        if (!$mkId) {
            $stmt = Database::getInstance()->query("
                SELECT m.id FROM mata_kuliah m
                LEFT JOIN rps r ON m.id = r.mata_kuliah_id
                ORDER BY (m.kode = 'SIF604') DESC, r.id DESC, m.id ASC
                LIMIT 1
            ");
            $defaultId = $stmt->fetchColumn();
            if ($defaultId) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $defaultId);
                exit;
            }
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $fullData = $this->rpsModel->getFullRpsData($mkId);
        if (!$fullData || !$fullData['mk']) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $fullData['mk'];
        $rps = $fullData['rps'];
        $cpls = $fullData['cpls'];
        $cpmkList = $fullData['cpmks'];
        $allSubCpmks = $fullData['allSubCpmks'];
        $totalSubBobot = $fullData['totalSubBobot'];
        $pertemuanList = $fullData['pertemuanList'];
        $referensiList = $fullData['referensiList'];
        $rencanaEvaluasi = $fullData['rencanaEvaluasi'];
        $rubrik = $fullData['rubrik'];
        $timTeaching = $fullData['timTeaching'] ?? [];
        $detectedKelasTimTeaching = $fullData['detectedKelasTimTeaching'] ?? [];
        $allDosen = $fullData['allDosen'] ?? [];

        // Ambil CPL kurikulum untuk mata kuliah ini
        $kurikulumCpls = $fullData['kurikulumCpls'] ?? $this->cplModel->getAll(['kurikulum_id' => $mk['kurikulum_id']]);
        $allCpls = $kurikulumCpls;

        $activeTab = $_GET['tab'] ?? 'identitas';

        require __DIR__ . '/../../views/rps/index.php';
    }

    public function createCpmk() {
        $mkId = $_GET['mk_id'] ?? null;
        if (!$mkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $this->mkModel->getById($mkId);
        $fullRps = $this->rpsModel->getFullRpsData($mkId);
        $mappedCpls = $fullRps['cpls'] ?? [];
        $mappedCplIds = array_column($mappedCpls, 'id');
        $cplList = $this->cplModel->getAll(['kurikulum_id' => $mk['kurikulum_id']]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'mk_id' => $mkId,
                'kode' => $_POST['kode'] ?? '',
                'rumusan' => $_POST['rumusan'] ?? ''
            ];
            $cplIds = $_POST['cpl_ids'] ?? [];

            if ($this->cpmkModel->create($data, $cplIds)) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId);
                exit;
            } else {
                $error = "Gagal menambah CPMK.";
            }
        }

        require __DIR__ . '/../../views/rps/cpmk_form.php';
    }

    public function editCpmk() {
        $id = $_GET['id'] ?? null;
        $cpmk = $this->cpmkModel->getById($id);
        if (!$cpmk) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mkId = $cpmk['mk_id'];
        $mk = $this->mkModel->getById($mkId);
        $fullRps = $this->rpsModel->getFullRpsData($mkId);
        $mappedCpls = $fullRps['cpls'] ?? [];
        $mappedCplIds = array_column($mappedCpls, 'id');
        $cplList = $this->cplModel->getAll(['kurikulum_id' => $mk['kurikulum_id']]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => $_POST['kode'] ?? '',
                'rumusan' => $_POST['rumusan'] ?? ''
            ];
            $cplIds = $_POST['cpl_ids'] ?? [];

            if ($this->cpmkModel->update($id, $data, $cplIds)) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId);
                exit;
            } else {
                $error = "Gagal memperbarui CPMK.";
            }
        }

        require __DIR__ . '/../../views/rps/cpmk_form.php';
    }

    public function deleteCpmk() {
        $id = $_GET['id'] ?? null;
        $cpmk = $this->cpmkModel->getById($id);
        if ($cpmk) {
            $this->cpmkModel->delete($id);
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $cpmk['mk_id']);
        } else {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
        }
        exit;
    }

    public function createSubCpmk() {
        $cpmkId = $_GET['cpmk_id'] ?? null;
        if (!$cpmkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $cpmk = $this->cpmkModel->getById($cpmkId);
        if (!$cpmk) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mkId = $cpmk['mk_id'];
        $mk = $this->mkModel->getById($mkId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cpmk_id' => $cpmkId,
                'kode' => $_POST['kode'] ?? '',
                'rumusan' => $_POST['rumusan'] ?? '',
                'level_taksonomi' => $_POST['level_taksonomi'] ?? '',
                'pengalaman_belajar' => $_POST['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
                'asesmen_penilaian' => $_POST['asesmen_penilaian'] ?? 'Pretes dan postes',
                'bobot' => $_POST['bobot'] ?? 0,
                'urutan' => $_POST['urutan'] ?? 0
            ];
            $indikators = $_POST['indikators'] ?? [];

            if ($this->subCpmkModel->create($data, $indikators)) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=cpmk');
                exit;
            } else {
                $error = "Gagal menambah Sub-CPMK.";
            }
        }

        require __DIR__ . '/../../views/rps/sub_cpmk_form.php';
    }

    public function editSubCpmk() {
        $id = $_GET['id'] ?? null;
        $subCpmk = $this->subCpmkModel->getById($id);
        if (!$subCpmk) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $cpmkId = $subCpmk['cpmk_id'];
        $cpmk = $this->cpmkModel->getById($cpmkId);
        $mkId = $cpmk['mk_id'];
        $mk = $this->mkModel->getById($mkId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => $_POST['kode'] ?? '',
                'rumusan' => $_POST['rumusan'] ?? '',
                'level_taksonomi' => $_POST['level_taksonomi'] ?? '',
                'pengalaman_belajar' => $_POST['pengalaman_belajar'] ?? 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus',
                'asesmen_penilaian' => $_POST['asesmen_penilaian'] ?? 'Pretes dan postes',
                'bobot' => $_POST['bobot'] ?? 0,
                'urutan' => $_POST['urutan'] ?? 0
            ];
            $indikators = $_POST['indikators'] ?? [];

            if ($this->subCpmkModel->update($id, $data, $indikators)) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=cpmk');
                exit;
            } else {
                $error = "Gagal memperbarui Sub-CPMK.";
            }
        }

        require __DIR__ . '/../../views/rps/sub_cpmk_form.php';
    }

    public function deleteSubCpmk() {
        $id = $_GET['id'] ?? null;
        $subCpmk = $this->subCpmkModel->getById($id);
        if ($subCpmk) {
            $cpmk = $this->cpmkModel->getById($subCpmk['cpmk_id']);
            $this->subCpmkModel->delete($id);
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $cpmk['mk_id']);
        } else {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
        }
        exit;
    }

    // ==========================================
    // RPS Rincian Aktivitas Pertemuan (1 s.d. 16)
    // ==========================================

    public function storePertemuan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $mkId = $_POST['mata_kuliah_id'] ?? null;
        if (!$mkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $data = [
            'mata_kuliah_id' => $mkId,
            'pertemuan_ke' => intval($_POST['pertemuan_ke'] ?? 1),
            'sub_cpmk_id' => !empty($_POST['sub_cpmk_id']) ? $_POST['sub_cpmk_id'] : null,
            'bahan_kajian' => trim($_POST['bahan_kajian'] ?? ''),
            'bentuk_metode' => trim($_POST['bentuk_metode'] ?? 'Kuliah & Diskusi'),
            'mode_pembelajaran' => trim($_POST['mode_pembelajaran'] ?? 'ASM dan TM'),
            'pengalaman_belajar' => trim($_POST['pengalaman_belajar'] ?? ''),
            'alokasi_waktu' => trim($_POST['alokasi_waktu'] ?? '2 x 50 menit'),
            'durasi_detail' => trim($_POST['durasi_detail'] ?? ''),
            'pustaka' => trim($_POST['pustaka'] ?? ''),
            'indikator_penilaian' => trim($_POST['indikator_penilaian'] ?? ''),
            'bobot_penilaian' => floatval($_POST['bobot_penilaian'] ?? 0)
        ];

        $this->pertemuanModel->create($data);
        $_SESSION['success'] = "Rincian Pertemuan {$data['pertemuan_ke']} berhasil disimpan.";
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
        exit;
    }

    public function updatePertemuan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $id = $_POST['id'] ?? null;
        $mkId = $_POST['mata_kuliah_id'] ?? null;

        if (!$id || !$mkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $data = [
            'pertemuan_ke' => intval($_POST['pertemuan_ke'] ?? 1),
            'sub_cpmk_id' => !empty($_POST['sub_cpmk_id']) ? $_POST['sub_cpmk_id'] : null,
            'bahan_kajian' => trim($_POST['bahan_kajian'] ?? ''),
            'bentuk_metode' => trim($_POST['bentuk_metode'] ?? 'Kuliah & Diskusi'),
            'mode_pembelajaran' => trim($_POST['mode_pembelajaran'] ?? 'ASM dan TM'),
            'pengalaman_belajar' => trim($_POST['pengalaman_belajar'] ?? ''),
            'alokasi_waktu' => trim($_POST['alokasi_waktu'] ?? '2 x 50 menit'),
            'durasi_detail' => trim($_POST['durasi_detail'] ?? ''),
            'pustaka' => trim($_POST['pustaka'] ?? ''),
            'indikator_penilaian' => trim($_POST['indikator_penilaian'] ?? ''),
            'bobot_penilaian' => floatval($_POST['bobot_penilaian'] ?? 0)
        ];

        $this->pertemuanModel->update($id, $data);
        $_SESSION['success'] = "Rincian Pertemuan {$data['pertemuan_ke']} berhasil diperbarui.";
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
        exit;
    }

    public function deletePertemuan() {
        $id = $_GET['id'] ?? null;
        $mkId = $_GET['mk_id'] ?? null;
        if ($id && $mkId) {
            $this->pertemuanModel->delete($id);
            $_SESSION['success'] = "Pertemuan berhasil dihapus.";
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
            exit;
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
        exit;
    }

    public function generateTemplatePertemuan() {
        $mkId = $_GET['mk_id'] ?? null;
        if ($mkId) {
            $success = $this->pertemuanModel->generateTemplate16($mkId);
            if ($success) {
                $_SESSION['success'] = "Template 16 Pertemuan otomatis berhasil di-generate!";
            } else {
                $_SESSION['error'] = "Pertemuan sudah ada atau gagal membuat template.";
            }
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
            exit;
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
        exit;
    }

    // ==========================================
    // Pengaturan Identitas & Metadata RPS
    // ==========================================
    public function saveSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $mkId = $_POST['mata_kuliah_id'] ?? null;
        if (!$mkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $tab = $_POST['tab'] ?? 'identitas';

        // 1. Pemrosesan Dosen Pengampu & Tim Teaching
        $dosenPjmk = trim($_POST['dosen_pjmk'] ?? '');
        $hasTimTeaching = !empty($_POST['has_tim_teaching']);
        $timTeachingList = [];
        $dosenLines = [];

        if (!empty($dosenPjmk)) {
            // Beri label PJMK jika belum ada
            if (stripos($dosenPjmk, 'PJMK') === false && stripos($dosenPjmk, 'Koordinator') === false) {
                $dosenLines[] = "1. " . $dosenPjmk . " (PJMK)";
            } else {
                $dosenLines[] = (preg_match('/^\d+\./', $dosenPjmk) ? "" : "1. ") . $dosenPjmk;
            }
        }

        if ($hasTimTeaching && !empty($_POST['tim_teaching_dosen'])) {
            $ttDosenArray = $_POST['tim_teaching_dosen'];
            $ttPeranArray = $_POST['tim_teaching_peran'] ?? [];
            $nomor = count($dosenLines) + 1;

            foreach ($ttDosenArray as $idx => $dosenName) {
                $dosenName = trim($dosenName);
                if (!empty($dosenName)) {
                    $peran = trim($ttPeranArray[$idx] ?? 'Anggota Tim Teaching');
                    if (empty($peran)) $peran = 'Anggota Tim Teaching';

                    $timTeachingList[] = [
                        'nama' => $dosenName,
                        'peran' => $peran
                    ];
                    $dosenLines[] = "{$nomor}. {$dosenName} ({$peran})";
                    $nomor++;
                }
            }
        }

        // Jika dosen_pengampu diketik langsung atau ada daftar tim
        if (!empty($dosenLines)) {
            $finalDosenPengampu = implode("\n", $dosenLines);
        } else {
            $finalDosenPengampu = trim($_POST['dosen_pengampu'] ?? '');
        }

        // Ambil data RPS yang sudah ada agar tab lain tidak ter-overwrite
        $existingRps = $this->rpsModel->getByMkId($mkId);
        $data = $existingRps;

        if ($tab === 'identitas') {
            $data['versi'] = trim($_POST['versi'] ?? ($data['versi'] ?? '3/0'));
            $data['fakultas'] = trim($_POST['fakultas'] ?? ($data['fakultas'] ?? 'Fakultas Sains dan Teknologi'));
            $data['program_studi'] = trim($_POST['program_studi'] ?? ($data['program_studi'] ?? 'S1 Sistem Informasi'));
            $data['kelas'] = trim($_POST['kelas'] ?? ($data['kelas'] ?? 'I-23'));
            $data['sifat'] = trim($_POST['sifat'] ?? ($data['sifat'] ?? 'Wajib'));
            $data['semester_ke'] = trim($_POST['semester_ke'] ?? ($data['semester_ke'] ?? '6'));
            $data['ketersediaan'] = trim($_POST['ketersediaan'] ?? ($data['ketersediaan'] ?? 'Program Studi'));
            $data['bentuk_pembelajaran'] = trim($_POST['bentuk_pembelajaran'] ?? ($data['bentuk_pembelajaran'] ?? ''));
            $data['media_pembelajaran'] = trim($_POST['media_pembelajaran'] ?? ($data['media_pembelajaran'] ?? ''));
            $data['kelompok_keilmuan'] = trim($_POST['kelompok_keilmuan'] ?? ($data['kelompok_keilmuan'] ?? 'Sistem Informasi'));
            $data['prasyarat'] = trim($_POST['prasyarat'] ?? ($data['prasyarat'] ?? '-'));
            $data['dosen_pengampu'] = $finalDosenPengampu;
            $data['tim_teaching_json'] = !empty($timTeachingList) ? json_encode($timTeachingList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null;
            $data['tahun_akademik'] = trim($_POST['tahun_akademik'] ?? ($data['tahun_akademik'] ?? 'Genap 2025/2026'));
            $_SESSION['success'] = "Identitas & Tim Pengajar RPS berhasil disimpan!";
        } elseif ($tab === 'referensi') {
            $data['referensi'] = trim($_POST['referensi'] ?? '');
            $_SESSION['success'] = "Daftar Referensi & Pustaka RPS berhasil disimpan!";
        } elseif ($tab === 'penilaian') {
            $data['sistem_penilaian_desc'] = trim($_POST['sistem_penilaian_desc'] ?? ($data['sistem_penilaian_desc'] ?? ''));
            $data['bobot_keaktifan'] = floatval($_POST['bobot_keaktifan'] ?? ($data['bobot_keaktifan'] ?? 5.00));
            $data['bobot_tugas'] = floatval($_POST['bobot_tugas'] ?? ($data['bobot_tugas'] ?? 15.00));
            $data['bobot_kuis'] = floatval($_POST['bobot_kuis'] ?? ($data['bobot_kuis'] ?? 20.00));
            $data['bobot_uts'] = floatval($_POST['bobot_uts'] ?? ($data['bobot_uts'] ?? 30.00));
            $data['bobot_uas'] = floatval($_POST['bobot_uas'] ?? ($data['bobot_uas'] ?? 30.00));
            $data['sistem_evaluasi_desc'] = trim($_POST['sistem_evaluasi_desc'] ?? ($data['sistem_evaluasi_desc'] ?? ''));
            if (isset($_POST['rencana_evaluasi_json'])) {
                $data['rencana_evaluasi_json'] = $_POST['rencana_evaluasi_json'];
            }
            $totalEvaluasi = $data['bobot_keaktifan'] + $data['bobot_tugas'] + $data['bobot_kuis'] + $data['bobot_uts'] + $data['bobot_uas'];
            $_SESSION['success'] = "Sistem Penilaian RPS berhasil disimpan! Total evaluasi: {$totalEvaluasi}%.";
        } elseif ($tab === 'rubrik') {
            if (!empty($_POST['rubrik_penilaian_json'])) {
                $data['rubrik_penilaian_json'] = $_POST['rubrik_penilaian_json'];
            } elseif (isset($_POST['rubrik']) && is_array($_POST['rubrik'])) {
                $rubrikPost = $_POST['rubrik'];
                // Format ulang boolean aktif
                foreach (['holistik', 'analitik', 'skala_persepsi', 'portofolio'] as $rKey) {
                    if (isset($rubrikPost[$rKey])) {
                        $rubrikPost[$rKey]['aktif'] = !empty($rubrikPost[$rKey]['aktif']);
                    }
                }
                $data['rubrik_penilaian_json'] = json_encode($rubrikPost, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            $_SESSION['success'] = "Pengaturan dan kustomisasi Rubrik Penilaian RPS Mata Kuliah berhasil disimpan!";
        } else {
            // Fallback default
            if (isset($_POST['rubrik_penilaian_json'])) {
                $data['rubrik_penilaian_json'] = $_POST['rubrik_penilaian_json'];
            }
            $_SESSION['success'] = "Pengaturan RPS berhasil diperbarui!";
        }

        $this->rpsModel->saveOrUpdate($mkId, $data);
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=' . $tab);
        exit;
    }

    // ==========================================
    // Tampilan Dokumen RPS Resmi (PDF Layout)
    // ==========================================
    public function document() {
        $mkId = $_GET['mk_id'] ?? null;
        if (!$mkId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $fullData = $this->rpsModel->getFullRpsData($mkId);
        if (!$fullData || !$fullData['mk']) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $fullData['mk'];
        $rps = $fullData['rps'];
        $cpls = $fullData['cpls'];
        $cpmks = $fullData['cpmks'];
        $allSubCpmks = $fullData['allSubCpmks'];
        $totalSubBobot = $fullData['totalSubBobot'];
        $pertemuanList = $fullData['pertemuanList'];
        $referensiList = $fullData['referensiList'];
        $rencanaEvaluasi = $fullData['rencanaEvaluasi'];
        $rubrik = $fullData['rubrik'];

        // Daftar CPL kurikulum untuk kolom matriks hubungan CPL dan Sub-CPMK
        $kurikulumCpls = $fullData['kurikulumCpls'] ?? $this->cplModel->getAll(['kurikulum_id' => $mk['kurikulum_id']]);
        $allCpls = $kurikulumCpls;

        require __DIR__ . '/../../views/rps/document.php';
    }
}
