<?php
require_once __DIR__ . '/../Models/KelasModel.php';
require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/TahunAkademikModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/PengampuMataKuliahModel.php';

class KelasController {
    private $kelasModel;
    private $mkModel;
    private $taModel;
    private $userModel;
    private $kurikulumModel;
    private $access;
    private $pengampuModel;

    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->mkModel = new MataKuliahModel();
        $this->taModel = new TahunAkademikModel();
        $this->userModel = new UserModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->access = new AccessContext();
        $this->pengampuModel = new PengampuMataKuliahModel();
    }

    public function index() {
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        $tahunAkademikList = $this->taModel->getAll();

        $selectedKurikulumId = !empty($_GET['kurikulum_id']) ? (int)$_GET['kurikulum_id'] : null;
        if (!$selectedKurikulumId && $this->access->isKaprodi() && !$this->access->isSuperAdmin()) $selectedKurikulumId = $kurikulumList[0]['id'] ?? null;
        if ($selectedKurikulumId && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKurikulum($selectedKurikulumId);
        $selectedTaId = !empty($_GET['ta_id']) ? (int)$_GET['ta_id'] : null;
        $search = !empty($_GET['search']) ? trim($_GET['search']) : null;

        $dosenId = ($_SESSION['role'] ?? '') === 'Dosen' && !$this->access->isKaprodi()
            ? (int)($_SESSION['user_id'] ?? 0)
            : null;

        $kelas = $this->kelasModel->getAll($dosenId, $selectedKurikulumId, $selectedTaId, $search);
        require __DIR__ . '/../../views/kelas/index.php';
    }

    public function create() {
        if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->forbidden();
        $mata_kuliah = $this->mkModel->getAll();
        $tahun_akademik = $this->taModel->getAll();
        $dosen = $this->userModel->getByRole('Dosen');
        require __DIR__ . '/../../views/kelas/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'mata_kuliah_id' => $_POST['mata_kuliah_id'],
                'tahun_akademik_id' => $_POST['tahun_akademik_id'],
                'nama_kelas' => $_POST['nama_kelas'],
                'dosen_id' => $_POST['dosen_id']
            ];
            $kelas_id = $this->kelasModel->create($data);
            
            if ($kelas_id && isset($_POST['tim_teaching'])) {
                $this->kelasModel->setTimTeaching($kelas_id, $_POST['tim_teaching']);
            }
            if ($kelas_id) $this->pengampuModel->applyToClass((int)$kelas_id,(int)$data['mata_kuliah_id'],(int)$data['tahun_akademik_id']);

            header('Location: /obe/public/kelas');
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKelas($id);
            $kelas = $this->kelasModel->getById($id);
            $mata_kuliah = $this->mkModel->getAll();
            $tahun_akademik = $this->taModel->getAll();
            $dosen = $this->userModel->getByRole('Dosen');
            $tim_teaching_ids = $this->kelasModel->getTimTeachingIds($id);
            require __DIR__ . '/../../views/kelas/form.php';
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'mata_kuliah_id' => $_POST['mata_kuliah_id'],
                'tahun_akademik_id' => $_POST['tahun_akademik_id'],
                'nama_kelas' => $_POST['nama_kelas'],
                'dosen_id' => $_POST['dosen_id']
            ];
            $this->kelasModel->update($id, $data);
            
            $tim_teaching = isset($_POST['tim_teaching']) ? $_POST['tim_teaching'] : [];
            $this->kelasModel->setTimTeaching($id, $tim_teaching);
            $this->pengampuModel->applyToClass((int)$id,(int)$data['mata_kuliah_id'],(int)$data['tahun_akademik_id']);

            header('Location: /obe/public/kelas');
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKelas($id);
            $this->kelasModel->delete($id);
            header('Location: /obe/public/kelas');
        }
    }

    // --- Kelola Peserta ---
    
    public function peserta() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (($_SESSION['role'] ?? '') === 'Dosen'
                && !$this->kelasModel->canManage($id, $_SESSION['user_id'] ?? 0)) {
                http_response_code(403);
                echo '403 Forbidden: Kelas ini bukan penugasan Anda.';
                return;
            }
            $kelas = $this->kelasModel->getById($id);
            $peserta = $this->kelasModel->getPeserta($id);
            $mahasiswa_belum = $this->kelasModel->getMahasiswaBelumTerdaftar($id);
            require __DIR__ . '/../../views/kelas/peserta.php';
        }
    }

    public function addPeserta() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kelas_id = $_POST['kelas_id'];
            $mahasiswa_id = $_POST['mahasiswa_id'];
            try {
                $this->kelasModel->addPeserta($kelas_id, $mahasiswa_id);
                $_SESSION['flash_success'] = 'Mahasiswa berhasil ditambahkan ke kelas.';
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
            header('Location: /obe/public/kelas/peserta?id=' . $kelas_id);
            exit;
        }
    }

    public function removePeserta() {
        $kelas_id = $_GET['kelas_id'] ?? null;
        $mahasiswa_id = $_GET['mahasiswa_id'] ?? null;
        if ($kelas_id && $mahasiswa_id) {
            $this->kelasModel->removePeserta($kelas_id, $mahasiswa_id);
            header('Location: /obe/public/kelas/peserta?id=' . $kelas_id);
        }
    }
}
