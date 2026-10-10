<?php
// app/Controllers/RpsController.php

require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/CpmkModel.php';
require_once __DIR__ . '/../Models/SubCpmkModel.php';
require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/RpsPertemuanModel.php';
require_once __DIR__ . '/../Models/RpsModel.php';
require_once __DIR__ . '/../Models/PengampuMataKuliahModel.php';

class RpsController {
    private $mkModel;
    private $cpmkModel;
    private $subCpmkModel;
    private $cplModel;
    private $pertemuanModel;
    private $rpsModel;
    private $pengampuModel;

    public function __construct() {
        $this->mkModel = new MataKuliahModel();
        $this->cpmkModel = new CpmkModel();
        $this->subCpmkModel = new SubCpmkModel();
        $this->cplModel = new CplModel();
        $this->pertemuanModel = new RpsPertemuanModel();
        $this->rpsModel = new RpsModel();
        $this->pengampuModel = new PengampuMataKuliahModel();
    }

    private function makeRubrikSnapshot($rubrikId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM rubrik WHERE id=? AND status='ACTIVE'");
        $stmt->execute([(int)$rubrikId]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$header) return null;
        $stmt = $db->prepare('SELECT id, level_nama, skor FROM rubrik_level WHERE rubrik_id=? ORDER BY skor,id');
        $stmt->execute([(int)$rubrikId]);
        $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $db->prepare('SELECT id, kriteria, bobot FROM rubrik_kriteria WHERE rubrik_id=? ORDER BY id');
        $stmt->execute([(int)$rubrikId]);
        $criteriaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $criteria = [];
        $descStmt = $db->prepare('SELECT deskripsi FROM rubrik_deskriptor WHERE kriteria_id=? AND level_id=? LIMIT 1');
        foreach ($criteriaRows as $criterion) {
            $descriptors = [];
            foreach ($levels as $level) {
                $descStmt->execute([$criterion['id'], $level['id']]);
                $descriptors[] = (string)($descStmt->fetchColumn() ?: '');
            }
            $criteria[] = ['nama' => $criterion['kriteria'], 'bobot' => (float)$criterion['bobot'], 'descriptors' => $descriptors];
        }
        return [
            'master_id' => (int)$header['id'], 'kode_jenis' => $header['kode_jenis'],
            'jenis' => $header['jenis'], 'nama' => $header['nama'],
            'karakteristik' => $header['karakteristik'], 'contoh_penggunaan' => $header['contoh_penggunaan'],
            'levels' => array_map(static fn($level) => ['nama' => $level['level_nama'], 'skor' => (float)$level['skor']], $levels),
            'criteria' => $criteria
        ];
    }

    private function requireAssignedCourse($mkId) {
        if (($_SESSION['role'] ?? '') !== 'Dosen') {
            return;
        }
        $stmt = Database::getInstance()->prepare("SELECT COUNT(*)
            FROM mata_kuliah_pengampu pm
            JOIN tahun_akademik ta ON ta.id=pm.tahun_akademik_id
            WHERE pm.mata_kuliah_id=? AND pm.pjmk_user_id=?
              AND pm.status='ACTIVE' AND ta.is_active=1");
        $stmt->execute([(int)$mkId,(int)($_SESSION['user_id'] ?? 0)]);
        if ((int)$stmt->fetchColumn() === 0) {
            http_response_code(403);
            echo '403 Forbidden: Hanya PJMK resmi pada tahun akademik aktif yang dapat mengubah RPS ini.';
            exit;
        }
    }

    public function index() {
        $access = new AccessContext();
        $mkId = $_GET['mk_id'] ?? null;
        if (!$mkId && $access->isDosen()) {
            $assignments=$this->pengampuModel->getRpsAssignmentsForLecturer($access->userId());
            require __DIR__ . '/../../views/rps/list_dosen.php';
            return;
        }
        if (!$mkId) {
            $sql = "
                SELECT m.id FROM mata_kuliah m
                JOIN kurikulum k ON k.id = m.kurikulum_id
                LEFT JOIN rps r ON m.id = r.mata_kuliah_id
            ";
            $params = [];
            if (!$access->isSuperAdmin() && $access->isKaprodi()) {
                $ids = $access->managedProdiIds();
                $sql .= ' WHERE k.prodi_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
                $params = $ids;
            }
            $sql .= " ORDER BY (m.kode = 'SIF604') DESC, r.id DESC, m.id ASC LIMIT 1";
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute($params);
            $defaultId = $stmt->fetchColumn();
            if ($defaultId) {
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $defaultId);
                exit;
            }
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }
        if (!$access->isSuperAdmin() && $access->isKaprodi() && !$access->isDosen()) $access->assertMataKuliah($mkId);

        $rpsAssignment=null;$rpsCanEdit=true;$rpsIsArchive=false;
        if($access->isDosen()){
            $taId=(int)($_GET['ta_id']??0);
            if(!$taId){
                $stmt=Database::getInstance()->prepare("SELECT pm.tahun_akademik_id FROM mata_kuliah_pengampu pm JOIN tahun_akademik ta ON ta.id=pm.tahun_akademik_id WHERE pm.mata_kuliah_id=? AND pm.pjmk_user_id=? AND pm.status='ACTIVE' ORDER BY ta.is_active DESC,ta.id DESC LIMIT 1");
                $stmt->execute([(int)$mkId,$access->userId()]);$taId=(int)$stmt->fetchColumn();
            }
            $rpsAssignment=$this->pengampuModel->findForLecturer((int)$mkId,$taId,$access->userId());
            if(!$rpsAssignment){http_response_code(403);exit('403 Forbidden: RPS ini bukan penugasan PJMK Anda.');}
            $rpsCanEdit=(bool)$rpsAssignment['is_active'];
            $rpsIsArchive=!$rpsCanEdit;
        }

        $fullData = $this->rpsModel->getFullRpsData($mkId);
        if (!$fullData || !$fullData['mk']) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $fullData['mk'];
        $activeTaId=(int)Database::getInstance()->query('SELECT id FROM tahun_akademik WHERE is_active=1 ORDER BY id DESC LIMIT 1')->fetchColumn();
        $officialAssignment=$activeTaId?$this->pengampuModel->findActive((int)$mkId,$activeTaId):null;
        $activeAcademicYears=Database::getInstance()->query('SELECT id,nama FROM tahun_akademik WHERE is_active=1 ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
        $rps = $fullData['rps'];
        $cpls = $fullData['cpls'];
        $cpmkList = $fullData['cpmks'];
        $allSubCpmks = $fullData['allSubCpmks'];
        $totalSubBobot = $fullData['totalSubBobot'];
        $pertemuanList = $fullData['pertemuanList'];
        $referensiList = $fullData['referensiList'];
        $rencanaEvaluasi = $fullData['rencanaEvaluasi'];
        $jenisPenilaian = $fullData['jenisPenilaian'] ?? [];
        $jenisPenilaianMaster = $fullData['jenisPenilaianMaster'] ?? RpsModel::getJenisPenilaianMaster();
        $rubrikMaster = $fullData['rubrikMaster'] ?? [];
        $rubrikMk = $fullData['rubrikMk'] ?? [];
        $rubrik = $fullData['rubrik'];
        $timTeaching = $fullData['timTeaching'] ?? [];
        $detectedKelasTimTeaching = $fullData['detectedKelasTimTeaching'] ?? [];
        $allDosen = $fullData['allDosen'] ?? [];

        if($rpsAssignment){
            if($rpsCanEdit){
                $this->rpsModel->saveSemesterArchive((int)$rpsAssignment['id'],(int)$mkId,(int)$rpsAssignment['tahun_akademik_id']);
            }else{
                $archive=$this->rpsModel->getSemesterArchive((int)$rpsAssignment['id']);
                if($archive){
                    $rps=array_replace($rps,$archive['rps']);
                    $pertemuanList=$archive['pertemuan'];
                    $referensiList=array_values(array_filter(array_map('trim',explode("\n",(string)($rps['referensi']??'')))));
                    $rencanaEvaluasi=json_decode($rps['rencana_evaluasi_json']??'[]',true)?:[];
                    $jenisPenilaian=json_decode($rps['jenis_penilaian_json']??'[]',true)?:$jenisPenilaian;
                    $rubrikMk=json_decode($rps['rubrik_mk_json']??'[]',true)?:[];
                    $timTeaching=json_decode($rps['tim_teaching_json']??'[]',true)?:[];
                }
            }
        }

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
        $this->requireAssignedCourse($mkId);

        $subCpmkIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['sub_cpmk_ids'] ?? [])))));
        $data = [
            'mata_kuliah_id' => $mkId,
            'pertemuan_ke' => intval($_POST['pertemuan_ke'] ?? 1),
            'sub_cpmk_id' => $subCpmkIds[0] ?? null,
            'sub_cpmk_ids' => $subCpmkIds,
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
        $this->requireAssignedCourse($mkId);

        $stmtOwner = Database::getInstance()->prepare('SELECT COUNT(*) FROM rps_pertemuan WHERE id = ? AND mata_kuliah_id = ?');
        $stmtOwner->execute([(int)$id, (int)$mkId]);
        if ((int)$stmtOwner->fetchColumn() !== 1) {
            throw new InvalidArgumentException('Pertemuan tidak terkait dengan mata kuliah yang dipilih.');
        }

        $subCpmkIds = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['sub_cpmk_ids'] ?? [])))));
        $data = [
            'pertemuan_ke' => intval($_POST['pertemuan_ke'] ?? 1),
            'sub_cpmk_id' => $subCpmkIds[0] ?? null,
            'sub_cpmk_ids' => $subCpmkIds,
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
            $this->requireAssignedCourse($mkId);
            $stmtOwner = Database::getInstance()->prepare('SELECT COUNT(*) FROM rps_pertemuan WHERE id = ? AND mata_kuliah_id = ?');
            $stmtOwner->execute([(int)$id, (int)$mkId]);
            if ((int)$stmtOwner->fetchColumn() !== 1) {
                throw new InvalidArgumentException('Pertemuan tidak terkait dengan mata kuliah yang dipilih.');
            }
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
            $this->requireAssignedCourse($mkId);
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

    public function importPertemuanCsv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $mkId = (int)($_POST['mata_kuliah_id'] ?? 0);
        $csvText = trim((string)($_POST['csv_text'] ?? ''));
        if (!$mkId || $csvText === '') {
            $_SESSION['error'] = 'Data CSV tidak boleh kosong.';
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
            exit;
        }
        $this->requireAssignedCourse($mkId);

        try {
        $firstLine = strtok(str_replace("\r\n", "\n", $csvText), "\n") ?: '';
        $delimiterCounts = ["\t" => substr_count($firstLine, "\t"), ';' => substr_count($firstLine, ';'), ',' => substr_count($firstLine, ',')];
        arsort($delimiterCounts);
        $delimiter = (string)array_key_first($delimiterCounts);
        $normalizeHeader = static function ($header) {
            $header = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$header)));
            $header = preg_replace('/[^a-z0-9]+/', '_', $header);
            $header = trim($header, '_');
            $aliases = [
                'pekan' => 'pertemuan_ke', 'pekan_ke' => 'pertemuan_ke', 'minggu' => 'pertemuan_ke', 'minggu_ke' => 'pertemuan_ke',
                'pertemuan' => 'pertemuan_ke', 'sub_cpmk_kode' => 'sub_cpmk', 'kode_sub_cpmk' => 'sub_cpmk',
                'topik' => 'bahan_kajian', 'bahan_kajian_topik' => 'bahan_kajian', 'materi' => 'bahan_kajian',
                'metode' => 'bentuk_metode', 'mode' => 'mode_pembelajaran', 'waktu' => 'alokasi_waktu',
                'aktivitas' => 'durasi_detail', 'aktivitas_durasi' => 'durasi_detail',
                'rancangan_aktivitas' => 'durasi_detail', 'rancangan_aktivitas_durasi' => 'durasi_detail',
                'rancangan_aktivitas_dan_durasi' => 'durasi_detail',
                'media' => 'pustaka', 'referensi' => 'pustaka', 'media_pustaka' => 'pustaka',
                'media_referensi' => 'pustaka', 'media_pembelajaran_referensi' => 'pustaka',
                'indikator' => 'indikator_penilaian', 'bobot' => 'bobot_penilaian'
            ];
            return $aliases[$header] ?? $header;
        };
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csvText);
        rewind($stream);
        $headers = fgetcsv($stream, 0, $delimiter);
        $headers = array_map($normalizeHeader, $headers ?: []);
        $required = ['pertemuan_ke', 'sub_cpmk', 'bahan_kajian'];
        if (array_diff($required, $headers)) {
            fclose($stream);
            $_SESSION['error'] = 'Header CSV wajib memuat: pertemuan_ke, sub_cpmk, bahan_kajian.';
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
            exit;
        }

        $db = Database::getInstance();
        $stmtSub = $db->prepare('SELECT sc.id, sc.kode FROM sub_cpmk sc JOIN cpmk cp ON cp.id=sc.cpmk_id WHERE cp.mk_id=?');
        $stmtSub->execute([$mkId]);
        $subMap = [];
        foreach ($stmtSub->fetchAll(PDO::FETCH_ASSOC) as $sub) $subMap[mb_strtolower(trim($sub['kode']))] = (int)$sub['id'];

        $rows = [];
        $line = 1;
        while (($values = fgetcsv($stream, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($values, static fn($v) => trim((string)$v) !== '')) === 0) continue;
            $values = array_pad($values, count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));
            $meeting = (int)($row['pertemuan_ke'] ?? 0);
            if ($meeting < 1 || $meeting > 16) throw new InvalidArgumentException("Baris {$line}: pertemuan_ke harus 1 sampai 16.");
            if (isset($rows[$meeting])) throw new InvalidArgumentException("Baris {$line}: pertemuan {$meeting} duplikat di dalam CSV.");
            $subIds = [];
            foreach (preg_split('/\s*[|+]\s*/', trim((string)($row['sub_cpmk'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) as $subCode) {
                $lookup = mb_strtolower($subCode);
                if (!isset($subMap[$lookup])) throw new InvalidArgumentException("Baris {$line}: kode Sub-CPMK '{$subCode}' tidak ditemukan pada mata kuliah ini.");
                $subIds[] = $subMap[$lookup];
            }
            if (!$subIds) throw new InvalidArgumentException("Baris {$line}: sub_cpmk wajib diisi.");
            $rows[$meeting] = [
                'mata_kuliah_id' => $mkId, 'pertemuan_ke' => $meeting,
                'sub_cpmk_id' => $subIds[0] ?? null, 'sub_cpmk_ids' => $subIds,
                'bahan_kajian' => trim((string)($row['bahan_kajian'] ?? '')),
                'bentuk_metode' => trim((string)($row['bentuk_metode'] ?? 'Kuliah & Diskusi')) ?: 'Kuliah & Diskusi',
                'mode_pembelajaran' => trim((string)($row['mode_pembelajaran'] ?? 'ASM dan TM')) ?: 'ASM dan TM',
                'pengalaman_belajar' => trim((string)($row['pengalaman_belajar'] ?? '')),
                'alokasi_waktu' => trim((string)($row['alokasi_waktu'] ?? '2 x 50 menit')) ?: '2 x 50 menit',
                'durasi_detail' => trim((string)($row['durasi_detail'] ?? '')),
                'pustaka' => trim((string)($row['pustaka'] ?? '')),
                'indikator_penilaian' => trim((string)($row['indikator_penilaian'] ?? '')),
                'bobot_penilaian' => round((float)($row['bobot_penilaian'] ?? 0), 2)
            ];
            if ($rows[$meeting]['bahan_kajian'] === '') throw new InvalidArgumentException("Baris {$line}: bahan_kajian wajib diisi.");
        }
        fclose($stream);
        if (!$rows) throw new InvalidArgumentException('CSV tidak memiliki baris data.');

        $db->beginTransaction();
        try {
            $find = $db->prepare('SELECT id FROM rps_pertemuan WHERE mata_kuliah_id=? AND pertemuan_ke=? ORDER BY id LIMIT 1');
            $created = 0; $updated = 0;
            foreach ($rows as $data) {
                $find->execute([$mkId, $data['pertemuan_ke']]);
                $existingId = $find->fetchColumn();
                if ($existingId) { $this->pertemuanModel->update((int)$existingId, $data); $updated++; }
                else { $this->pertemuanModel->create($data); $created++; }
            }
            $db->commit();
            $_SESSION['success'] = "Impor CSV berhasil: {$created} pertemuan ditambahkan dan {$updated} diperbarui.";
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
        } catch (Throwable $e) {
            if (isset($stream) && is_resource($stream)) fclose($stream);
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $_SESSION['error'] = 'Impor CSV gagal: ' . $e->getMessage();
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
            exit;
        }
        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=pertemuan');
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
        $this->requireAssignedCourse($mkId);

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
            $submittedTaId=(int)($_POST['tahun_akademik_id']??0);
            $taStmt=Database::getInstance()->prepare('SELECT nama FROM tahun_akademik WHERE id=? AND is_active=1');
            $taStmt->execute([$submittedTaId]);
            $selectedTaName=$taStmt->fetchColumn();
            if($selectedTaName===false){$_SESSION['error']='Tahun akademik yang dipilih tidak aktif atau tidak valid.';header('Location: '.($GLOBALS['config']['base_url']??'/obe/public').'/rps?mk_id='.(int)$mkId.'&tab=identitas');exit;}
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
            $data['tahun_akademik'] = (string)$selectedTaName;
            $activeTaId=(int)Database::getInstance()->query('SELECT id FROM tahun_akademik WHERE is_active=1 ORDER BY id DESC LIMIT 1')->fetchColumn();
            $officialAssignment=$activeTaId?$this->pengampuModel->findActive((int)$mkId,$activeTaId):null;
            if($officialAssignment){$lines=['1. '.$officialAssignment['pjmk_nama'].' (PJMK)'];$team=[];foreach($officialAssignment['tim'] as $i=>$member){$lines[]=($i+2).'. '.$member['nama_lengkap'].' (Anggota Tim Teaching)';$team[]=['dosen_id'=>(int)$member['dosen_id'],'nama'=>$member['nama_lengkap'],'peran'=>'Anggota Tim Teaching'];}$data['dosen_pengampu']=implode("\n",$lines);$data['tim_teaching_json']=$team?json_encode($team,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE):null;}
            $_SESSION['success'] = "Identitas & Tim Pengajar RPS berhasil disimpan!";
        } elseif ($tab === 'referensi') {
            $data['referensi'] = trim($_POST['referensi'] ?? '');
            $_SESSION['success'] = "Daftar Referensi & Pustaka RPS berhasil disimpan!";
        } elseif ($tab === 'penilaian') {
            $data['sistem_penilaian_desc'] = trim($_POST['sistem_penilaian_desc'] ?? ($data['sistem_penilaian_desc'] ?? ''));
            $masterJenis = RpsModel::getJenisPenilaianMaster();
            $komponen = [];
            $kodeTerpakai = [];
            $totalEvaluasi = 0.0;
            foreach (($_POST['jenis_penilaian'] ?? []) as $item) {
                $kode = strtoupper(trim($item['kode'] ?? ''));
                $bobot = round((float)($item['bobot'] ?? 0), 2);
                $rubrikId = filter_var($item['rubrik_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                if (!isset($masterJenis[$kode]) || isset($kodeTerpakai[$kode]) || $bobot <= 0 || $bobot > 100) continue;
                if ($rubrikId) {
                    $stmtRubrik = Database::getInstance()->prepare("SELECT COUNT(*) FROM rubrik WHERE id = ? AND status = 'ACTIVE'");
                    $stmtRubrik->execute([$rubrikId]);
                    if (!(int)$stmtRubrik->fetchColumn()) $rubrikId = null;
                }
                $kodeTerpakai[$kode] = true;
                $komponen[] = ['kode' => $kode, 'bobot' => $bobot, 'rubrik_id' => $rubrikId];
                $totalEvaluasi += $bobot;
            }
            if (empty($komponen) || abs($totalEvaluasi - 100) > 0.001) {
                $_SESSION['error'] = 'Komponen penilaian wajib diisi dan total bobot harus tepat 100%.';
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=penilaian');
                exit;
            }
            $data['jenis_penilaian_json'] = json_encode($komponen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $existingRubrikMk = json_decode($data['rubrik_mk_json'] ?? '[]', true);
            $existingRubrikMk = is_array($existingRubrikMk) ? $existingRubrikMk : [];
            $selectedRubrikMk = [];
            foreach ($komponen as $item) {
                if (empty($item['rubrik_id'])) continue;
                $key = (string)(int)$item['rubrik_id'];
                if (!isset($selectedRubrikMk[$key])) {
                    $selectedRubrikMk[$key] = $existingRubrikMk[$key] ?? $this->makeRubrikSnapshot((int)$item['rubrik_id']);
                }
            }
            $data['rubrik_mk_json'] = json_encode(array_filter($selectedRubrikMk), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            // Sinkronkan kolom lama agar modul yang belum dinamis tetap kompatibel.
            $legacy = ['AKT' => 'bobot_keaktifan', 'TGI' => 'bobot_tugas', 'KUI' => 'bobot_kuis', 'UTS' => 'bobot_uts', 'UAS' => 'bobot_uas'];
            foreach ($legacy as $field) $data[$field] = 0;
            foreach ($komponen as $item) if (isset($legacy[$item['kode']])) $data[$legacy[$item['kode']]] += $item['bobot'];
            $data['sistem_evaluasi_desc'] = trim($_POST['sistem_evaluasi_desc'] ?? ($data['sistem_evaluasi_desc'] ?? ''));
            if (isset($_POST['rencana_evaluasi']) && is_array($_POST['rencana_evaluasi'])) {
                $data['rencana_evaluasi_json'] = json_encode($_POST['rencana_evaluasi'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } elseif (isset($_POST['rencana_evaluasi_json'])) {
                $data['rencana_evaluasi_json'] = $_POST['rencana_evaluasi_json'];
            }
            $_SESSION['success'] = "Sistem Penilaian RPS berhasil disimpan! Total evaluasi: {$totalEvaluasi}%.";
        } elseif ($tab === 'rubrik') {
            $currentSnapshots = json_decode($data['rubrik_mk_json'] ?? '[]', true);
            $currentSnapshots = is_array($currentSnapshots) ? $currentSnapshots : [];
            foreach (($_POST['rubrik_mk'] ?? []) as $masterId => $posted) {
                $key = (string)(int)$masterId;
                if (!isset($currentSnapshots[$key])) continue;
                $snapshot = $currentSnapshots[$key];
                $snapshot['nama'] = trim($posted['nama'] ?? $snapshot['nama']);
                foreach (($posted['levels'] ?? []) as $lIndex => $level) {
                    if (!isset($snapshot['levels'][$lIndex])) continue;
                    $snapshot['levels'][$lIndex]['nama'] = trim($level['nama'] ?? $snapshot['levels'][$lIndex]['nama']);
                    $snapshot['levels'][$lIndex]['skor'] = round((float)($level['skor'] ?? $snapshot['levels'][$lIndex]['skor']), 2);
                }
                foreach (($posted['criteria'] ?? []) as $cIndex => $criterion) {
                    if (!isset($snapshot['criteria'][$cIndex])) continue;
                    $snapshot['criteria'][$cIndex]['nama'] = trim($criterion['nama'] ?? $snapshot['criteria'][$cIndex]['nama']);
                    $snapshot['criteria'][$cIndex]['bobot'] = round((float)($criterion['bobot'] ?? 0), 2);
                    foreach (($criterion['descriptors'] ?? []) as $lIndex => $description) {
                        if (isset($snapshot['levels'][$lIndex])) $snapshot['criteria'][$cIndex]['descriptors'][$lIndex] = trim($description);
                    }
                }
                $totalCriteria = array_sum(array_column($snapshot['criteria'], 'bobot'));
                if (abs($totalCriteria - 100) > 0.001) {
                    $_SESSION['error'] = 'Bobot kriteria pada setiap rubrik mata kuliah wajib tepat 100%.';
                    header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/rps?mk_id=' . $mkId . '&tab=rubrik');
                    exit;
                }
                $currentSnapshots[$key] = $snapshot;
            }
            $data['rubrik_mk_json'] = json_encode($currentSnapshots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $_SESSION['success'] = "Rubrik khusus mata kuliah berhasil disimpan tanpa mengubah template master.";
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
        $access = new AccessContext();
        $documentAssignment=null;
        if($access->isDosen()){
            $taId=(int)($_GET['ta_id']??0);
            if(!$taId){$taId=(int)Database::getInstance()->query('SELECT id FROM tahun_akademik WHERE is_active=1 ORDER BY id DESC LIMIT 1')->fetchColumn();}
            $documentAssignment=$this->pengampuModel->findForLecturer((int)$mkId,$taId,$access->userId());
            if(!$documentAssignment){http_response_code(403);exit('403 Forbidden: Dokumen RPS ini bukan penugasan PJMK Anda.');}
        }elseif (!$access->isSuperAdmin() && $access->isKaprodi()) $access->assertMataKuliah($mkId);

        $fullData = $this->rpsModel->getFullRpsData($mkId);
        if (!$fullData || !$fullData['mk']) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/mata-kuliah');
            exit;
        }

        $mk = $fullData['mk'];
        $activeTaId=(int)Database::getInstance()->query('SELECT id FROM tahun_akademik WHERE is_active=1 ORDER BY id DESC LIMIT 1')->fetchColumn();
        $officialAssignment=$activeTaId?$this->pengampuModel->findActive((int)$mkId,$activeTaId):null;
        $rps = $fullData['rps'];
        $cpls = $fullData['cpls'];
        $cpmks = $fullData['cpmks'];
        $allSubCpmks = $fullData['allSubCpmks'];
        $totalSubBobot = $fullData['totalSubBobot'];
        $pertemuanList = $fullData['pertemuanList'];
        $referensiList = $fullData['referensiList'];
        $rencanaEvaluasi = $fullData['rencanaEvaluasi'];
        $jenisPenilaian = $fullData['jenisPenilaian'] ?? [];
        $jenisPenilaianMaster = $fullData['jenisPenilaianMaster'] ?? RpsModel::getJenisPenilaianMaster();
        $rubrikMaster = $fullData['rubrikMaster'] ?? [];
        $rubrikMk = $fullData['rubrikMk'] ?? [];
        $rubrik = $fullData['rubrik'];

        if($documentAssignment && !(bool)$documentAssignment['is_active']){
            $archive=$this->rpsModel->getSemesterArchive((int)$documentAssignment['id']);
            if($archive){
                $rps=array_replace($rps,$archive['rps']);$pertemuanList=$archive['pertemuan'];
                $referensiList=array_values(array_filter(array_map('trim',explode("\n",(string)($rps['referensi']??'')))));
                $rencanaEvaluasi=json_decode($rps['rencana_evaluasi_json']??'[]',true)?:[];
                $jenisPenilaian=json_decode($rps['jenis_penilaian_json']??'[]',true)?:$jenisPenilaian;
                $rubrikMk=json_decode($rps['rubrik_mk_json']??'[]',true)?:[];
            }
        }

        // Daftar CPL kurikulum untuk kolom matriks hubungan CPL dan Sub-CPMK
        $kurikulumCpls = $fullData['kurikulumCpls'] ?? $this->cplModel->getAll(['kurikulum_id' => $mk['kurikulum_id']]);
        $allCpls = $kurikulumCpls;

        require __DIR__ . '/../../views/rps/document.php';
    }
}
