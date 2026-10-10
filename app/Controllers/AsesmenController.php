<?php
// app/Controllers/AsesmenController.php

require_once __DIR__ . '/../Models/KelasModel.php';
require_once __DIR__ . '/../Models/AsesmenModel.php';
require_once __DIR__ . '/../Models/RubrikModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';

class AsesmenController {
    private $kelasModel;
    private $asesmenModel;
    private $rubrikModel;
    private $auditModel;
    private $kurikulumModel;
    private $access;

    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->asesmenModel = new AsesmenModel();
        $this->rubrikModel = new RubrikModel();
        $this->auditModel = new AuditLogModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $teachingProdiId = $this->access->userProdiId();
        $kurikulumList = $this->access->isSuperAdmin()
            ? $this->kurikulumModel->getAll()
            : ($teachingProdiId ? $this->kurikulumModel->getAll($teachingProdiId) : []);
        $selectedKurikulumId = !empty($_GET['kurikulum_id']) ? (int)$_GET['kurikulum_id'] : null;
        if (!$selectedKurikulumId && !$this->access->isSuperAdmin()) $selectedKurikulumId = $kurikulumList[0]['id'] ?? null;
        if ($selectedKurikulumId && !$this->access->isSuperAdmin()) {
            $selectedKurikulum = $this->kurikulumModel->getById($selectedKurikulumId);
            if (!$selectedKurikulum || !$this->access->canAccessTeachingProdi($selectedKurikulum['prodi_id'])) $this->access->forbidden();
        }
        $kelas_id = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : null;

        $dosenId = ($_SESSION['role'] ?? '') === 'Dosen' && !$this->access->isKaprodi()
            ? (int)($_SESSION['user_id'] ?? 0)
            : null;

        // Jika kelas_id diberikan tapi kurikulum_id belum dispesifikasikan di URL,
        // sinkronkan kurikulum_id otomatis dengan kurikulum kelas tersebut
        if ($kelas_id && empty($selectedKurikulumId) && !isset($_GET['kurikulum_id'])) {
            $currentKelasInfo = $this->kelasModel->getById($kelas_id);
            if ($currentKelasInfo && !empty($currentKelasInfo['kurikulum_id'])) {
                $selectedKurikulumId = (int)$currentKelasInfo['kurikulum_id'];
            }
        }

        // Dropdown filter kelas
        $kelasList = $this->kelasModel->getAll($dosenId, $selectedKurikulumId);
        
        $kelas = null;
        $peserta = [];
        $sub_cpmk_list = [];
        $nilai_matrix = [];
        $rubrikData = [];

        if ($kelas_id) {
            if (!$this->access->isSuperAdmin()) $this->access->assertTeachingKelas($kelas_id);
            if ($dosenId !== null && !$this->kelasModel->canManage($kelas_id,$dosenId)) {
                http_response_code(403);echo '403 Forbidden: Anda bukan PJMK atau tim teaching kelas ini.';return;
            }
            $kelasRow = $this->kelasModel->getById($kelas_id);
            if ($kelasRow) {
                $kelas = $kelasRow;
                // Jika kurikulum aktif difilter dan kelas tidak sesuai, sesuaikan kurikulum
                if ($selectedKurikulumId && $kelas['kurikulum_id'] != $selectedKurikulumId) {
                    $selectedKurikulumId = (int)$kelas['kurikulum_id'];
                    $kelasList = $this->kelasModel->getAll($dosenId, $selectedKurikulumId);
                }

                $peserta = $this->kelasModel->getPeserta($kelas_id);
                $sub_cpmk_list = $this->asesmenModel->getDaftarSubCpmk($kelas['mata_kuliah_id']);
                $nilai_matrix = $this->asesmenModel->getNilaiKelas($kelas_id);

                // Gunakan salinan rubrik khusus MK agar penilaian konsisten dengan RPS
                // dan perubahan dosen tidak mengubah template master global.
                $stmtRpsRubrik = Database::getInstance()->prepare('SELECT rubrik_mk_json FROM rps WHERE mata_kuliah_id=? LIMIT 1');
                $stmtRpsRubrik->execute([(int)$kelas['mata_kuliah_id']]);
                $courseSnapshots = json_decode($stmtRpsRubrik->fetchColumn() ?: '[]', true);
                if (is_array($courseSnapshots) && !empty($courseSnapshots)) {
                    foreach ($courseSnapshots as $masterId => $snapshot) {
                        $levels = [];
                        foreach (($snapshot['levels'] ?? []) as $lIndex => $level) {
                            $levels[] = ['id' => 'l' . $lIndex, 'level_nama' => $level['nama'], 'skor' => $level['skor']];
                        }
                        $criteria = [];
                        $descriptors = [];
                        foreach (($snapshot['criteria'] ?? []) as $cIndex => $criterion) {
                            $criterionId = 'c' . $cIndex;
                            $criteria[] = ['id' => $criterionId, 'kriteria' => $criterion['nama'], 'bobot' => $criterion['bobot']];
                            foreach (($criterion['descriptors'] ?? []) as $lIndex => $description) {
                                $descriptors[$criterionId]['l' . $lIndex] = $description;
                            }
                        }
                        $rubrikData[] = [
                            'id' => 'mk-' . $masterId,
                            'nama' => $snapshot['nama'] ?? 'Rubrik Mata Kuliah',
                            'jenis' => $snapshot['jenis'] ?? 'Rubrik',
                            'levels' => $levels,
                            'kriteria' => $criteria,
                            'descriptors' => $descriptors
                        ];
                    }
                } else {
                    // Kompatibilitas untuk MK lama yang belum memiliki snapshot rubrik.
                    $allRubriks = $this->rubrikModel->getAll();
                    foreach ($allRubriks as $r) {
                        $rubrikDetail = $this->rubrikModel->getById($r['id']);
                        if ($rubrikDetail) $rubrikData[] = $rubrikDetail;
                    }
                }
            }
        }

        require __DIR__ . '/../../views/asesmen/index.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kelas_id = (int)$_POST['kelas_id'];
            if (!$this->access->isSuperAdmin()) $this->access->assertTeachingKelas($kelas_id);
            if (($_SESSION['role'] ?? '') === 'Dosen' && !$this->kelasModel->canManage($kelas_id,$_SESSION['user_id']??0)) {
                http_response_code(403);echo '403 Forbidden: Anda bukan PJMK atau tim teaching kelas ini.';return;
            }
            $nilai_data = $_POST['nilai'] ?? []; // format: nilai[mahasiswa_id][sub_cpmk_id] = skor
            $savedCount = 0;

            try {
                // Validasi seluruh matriks lebih dahulu agar kegagalan tidak menghasilkan simpan parsial.
                foreach ($nilai_data as $mahasiswa_id => $sub_cpmks) {
                    foreach ($sub_cpmks as $sub_cpmk_id => $skor) {
                        if ($skor !== '') {
                            $this->asesmenModel->validateNilai($kelas_id, (int)$mahasiswa_id, (int)$sub_cpmk_id, (float)$skor);
                        }
                    }
                }
                foreach ($nilai_data as $mahasiswa_id => $sub_cpmks) {
                    foreach ($sub_cpmks as $sub_cpmk_id => $skor) {
                        // Only save if skor is explicitly set (even 0)
                        if ($skor !== '') {
                            $this->asesmenModel->saveNilai($kelas_id, (int)$mahasiswa_id, (int)$sub_cpmk_id, (float)$skor);
                            $savedCount++;
                        }
                    }
                }
                $_SESSION['flash_success'] = "$savedCount skor asesmen berhasil disimpan.";
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }

            $kelas = $this->kelasModel->getById($kelas_id);
            $namaKelas = $kelas ? ($kelas['mata_kuliah_kode'] . ' - ' . $kelas['nama_kelas']) : "Kelas #$kelas_id";
            if ($savedCount > 0) {
                $this->auditModel->log('ASESMEN', 'INPUT', "Menyimpan $savedCount skor asesmen mahasiswa untuk $namaKelas");
            }

            $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
            header('Location: ' . $baseUrl . '/asesmen?kelas_id=' . $kelas_id);
            exit;
        }
    }
}
