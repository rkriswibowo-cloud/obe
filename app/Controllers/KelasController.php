<?php
require_once __DIR__ . '/../Models/KelasModel.php';
require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/TahunAkademikModel.php';
require_once __DIR__ . '/../Models/UserModel.php';

class KelasController {
    private $kelasModel;
    private $mkModel;
    private $taModel;
    private $userModel;

    public function __construct() {
        $this->kelasModel = new KelasModel();
        $this->mkModel = new MataKuliahModel();
        $this->taModel = new TahunAkademikModel();
        $this->userModel = new UserModel();
    }

    public function index() {
        $kelas = $this->kelasModel->getAll();
        require __DIR__ . '/../../views/kelas/index.php';
    }

    public function create() {
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

            header('Location: /obe/public/kelas');
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
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

            header('Location: /obe/public/kelas');
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->kelasModel->delete($id);
            header('Location: /obe/public/kelas');
        }
    }

    // --- Kelola Peserta ---
    
    public function peserta() {
        $id = $_GET['id'] ?? null;
        if ($id) {
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
            $this->kelasModel->addPeserta($kelas_id, $mahasiswa_id);
            header('Location: /obe/public/kelas/peserta?id=' . $kelas_id);
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
