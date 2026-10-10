<?php
// app/Controllers/KhsController.php
require_once __DIR__ . '/../Models/KhsModel.php';
require_once __DIR__ . '/../Models/ProdiModel.php';

class KhsController {
    private $khsModel;
    private $prodiModel;
    private $access;

    public function __construct() {
        $this->khsModel = new KhsModel();
        $this->prodiModel = new ProdiModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $userRole = $_SESSION['role'] ?? '';
        $currentUserId = $_SESSION['user_id'] ?? null;

        // Jika mahasiswa, kunci ke ID dirinya sendiri
        if ($userRole === 'Mahasiswa') {
            $selectedMhsId = $currentUserId;
        } else {
            // Admin, Dosen, atau Kaprodi bisa memilih mahasiswa
            $selectedMhsId = $_GET['id'] ?? null;
        }

        $daftarMahasiswa = $this->khsModel->getAllMahasiswa();
        if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) {
            $daftarMahasiswa = array_values(array_filter($daftarMahasiswa, fn($m) => $this->access->canAccessMahasiswa($m['id'])));
        }
        $daftarTahunAkademik = $this->khsModel->getAllTahunAkademik();
        $filterTaId = $_GET['ta_id'] ?? null;

        // Default ke mahasiswa pertama jika belum ada yang dipilih
        if (!$selectedMhsId && !empty($daftarMahasiswa)) {
            $selectedMhsId = $daftarMahasiswa[0]['id'];
        }

        $mahasiswa = null;
        $khsData = null;
        $cplData = null;
        $rincianSubCpmk = [];
        $radarPayload = [];

        $kurikulumMhs = null;

        if ($selectedMhsId) {
            if ($userRole !== 'Mahasiswa' && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($selectedMhsId);
            $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
            // Resolve kurikulum for this student's angkatan
            require_once __DIR__ . '/../Models/AturanKurikulumModel.php';
            $aturanModel = new AturanKurikulumModel();
            $angkatan = AturanKurikulumModel::extractAngkatan($mahasiswa['username'] ?? '');
            if ($angkatan) {
                $kurikulumMhs = $aturanModel->getKurikulumByAngkatan($angkatan);
            }

            $kurikulumId = $kurikulumMhs['id'] ?? null;
            $khsData = $this->khsModel->getKhsMahasiswa($selectedMhsId, $filterTaId, $kurikulumId);
            $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);
            $rincianSubCpmk = $this->khsModel->getRincianSubCpmkMahasiswa(
                $selectedMhsId,
                $filterTaId,
                $kurikulumId
            );

            // Format data untuk Radar Chart CPL
            $labels = [];
            $scores = [];
            $targets = [];
            $rumusans = [];

            if (!empty($cplData['cpl_list'])) {
                foreach ($cplData['cpl_list'] as $c) {
                    $labels[] = $c['kode'];
                    $scores[] = $c['is_measured'] ? $c['score'] : 0;
                    $targets[] = $c['target'];
                    $rumusans[] = $c['rumusan'];
                }
            }

            $radarPayload = [
                'labels' => $labels,
                'scores' => $scores,
                'targets' => $targets,
                'rumusans' => $rumusans
            ];
        }

        require __DIR__ . '/../../views/khs/index.php';
    }

    public function cetak() {
        $selectedMhsId = ($_SESSION['role'] ?? '') === 'Mahasiswa'
            ? ($_SESSION['user_id'] ?? null)
            : ($_GET['id'] ?? null);
        $filterTaId = $_GET['ta_id'] ?? null;

        if (!$selectedMhsId) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/khs');
            exit;
        }
        if (($_SESSION['role'] ?? '') !== 'Mahasiswa' && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($selectedMhsId);

        $mahasiswa = $this->khsModel->getMahasiswaById($selectedMhsId);
        if (!$mahasiswa) {
            echo "Mahasiswa tidak ditemukan.";
            exit;
        }

        require_once __DIR__ . '/../Models/AturanKurikulumModel.php';
        $aturanModel = new AturanKurikulumModel();
        $angkatan = AturanKurikulumModel::extractAngkatan($mahasiswa['username'] ?? '');
        $kurikulumMhs = $angkatan
            ? $aturanModel->getKurikulumByAngkatan($angkatan)
            : null;
        $pejabat = !empty($kurikulumMhs['prodi_id']) ? $this->prodiModel->getById($kurikulumMhs['prodi_id']) : null;

        $khsData = $this->khsModel->getKhsMahasiswa(
            $selectedMhsId,
            $filterTaId,
            $kurikulumMhs['id'] ?? null
        );
        $cplData = $this->khsModel->getCplMahasiswa($selectedMhsId);

        // Format data Radar Chart
        $labels = [];
        $scores = [];
        $targets = [];
        if (!empty($cplData['cpl_list'])) {
            foreach ($cplData['cpl_list'] as $c) {
                $labels[] = $c['kode'];
                $scores[] = $c['is_measured'] ? $c['score'] : 0;
                $targets[] = $c['target'];
            }
        }

        $radarPayload = [
            'labels' => $labels,
            'scores' => $scores,
            'targets' => $targets
        ];

        require __DIR__ . '/../../views/khs/cetak.php';
    }
}
