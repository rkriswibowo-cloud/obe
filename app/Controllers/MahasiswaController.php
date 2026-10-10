<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/AturanKurikulumModel.php';

class MahasiswaController {
    private $userModel;
    private $db;
    private $access;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->db = Database::getInstance();
        $this->access = new AccessContext();
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $filterAngkatan = trim($_GET['angkatan'] ?? '');
        $filterKurikulum = trim($_GET['kurikulum_id'] ?? '');

        $allMahasiswa = $this->userModel->getByRole('Mahasiswa');
        if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) {
            $allMahasiswa = array_values(array_filter($allMahasiswa, fn($m) => $this->access->canAccessProdi($m['prodi_id'] ?? 0)));
        }
        $aturanModel = new AturanKurikulumModel();

        $daftarKurikulum = $this->db->query("
            SELECT id, kode, nama, tahun_mulai, prodi_id
            FROM kurikulum
            ORDER BY tahun_mulai DESC, id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $daftarKurikulum = $this->access->filterKurikulumRows($daftarKurikulum);

        $enrichedList = [];
        $daftarAngkatanSet = [];

        foreach ($allMahasiswa as $m) {
            $angkatan = AturanKurikulumModel::extractAngkatan($m['username']);
            $kurikulumMhs = $angkatan ? $aturanModel->getKurikulumByAngkatan($angkatan) : null;
            if ($angkatan) {
                $daftarAngkatanSet[$angkatan] = true;
            }
            $enrichedList[] = array_merge($m, [
                'angkatan' => $angkatan,
                'kurikulum_id' => $kurikulumMhs['id'] ?? null,
                'kurikulum_nama' => $kurikulumMhs['nama'] ?? 'Belum ditentukan'
            ]);
        }

        krsort($daftarAngkatanSet);
        $daftarAngkatan = array_keys($daftarAngkatanSet);

        // Filter data untuk render server-side
        $mahasiswa = array_values(array_filter($enrichedList, function ($m) use ($search, $filterAngkatan, $filterKurikulum) {
            if ($search !== '') {
                $q = mb_strtolower($search);
                $matchNama = mb_stripos($m['nama_lengkap'], $q) !== false;
                $matchNim = mb_stripos($m['username'], $q) !== false;
                if (!$matchNama && !$matchNim) {
                    return false;
                }
            }
            if ($filterAngkatan !== '') {
                if ((string)$m['angkatan'] !== (string)$filterAngkatan) {
                    return false;
                }
            }
            if ($filterKurikulum !== '') {
                if ((string)$m['kurikulum_id'] !== (string)$filterKurikulum) {
                    return false;
                }
            }
            return true;
        }));

        $totalMahasiswa = count($enrichedList);
        $filteredCount = count($mahasiswa);

        require __DIR__ . '/../../views/mahasiswa/index.php';
    }

    public function create() {
        require __DIR__ . '/../../views/mahasiswa/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = trim($_POST['username'] ?? ''); // NIM
                $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
                $this->validateIdentity($username, $nama_lengkap);
                $password = password_hash($username, PASSWORD_BCRYPT);

                $role_id = $this->db->query("SELECT id FROM roles WHERE name = 'Mahasiswa'")->fetchColumn();
                $prodi_id = $this->access->isKaprodi() && !$this->access->isSuperAdmin()
                    ? $this->access->primaryManagedProdiId()
                    : $this->db->query('SELECT id FROM program_studi ORDER BY id LIMIT 1')->fetchColumn();
                $stmt = $this->db->prepare("INSERT INTO users (username, password, nama_lengkap, prodi_id, role_id) VALUES (:username, :password, :nama_lengkap, :prodi_id, :role_id)");
                $stmt->execute([
                    'username' => $username,
                    'password' => $password,
                    'nama_lengkap' => $nama_lengkap,
                    'prodi_id' => $prodi_id ?: null,
                    'role_id' => $role_id
                ]);
                $_SESSION['success'] = 'Mahasiswa berhasil ditambahkan.';
            } catch (Throwable $e) {
                $_SESSION['error'] = 'Gagal menambahkan mahasiswa: ' . $e->getMessage();
            }

            header('Location: /obe/public/mahasiswa');
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($id);
            $mahasiswa = $this->userModel->getById($id);
            if ($mahasiswa) {
                require __DIR__ . '/../../views/mahasiswa/form.php';
                return;
            }
        }
        header('Location: /obe/public/mahasiswa');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = (int)($_POST['id'] ?? 0);
                if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($id);
                $username = trim($_POST['username'] ?? '');
                $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
                $this->validateIdentity($username, $nama_lengkap);

                $existing = $this->userModel->getById($id);
                if (!$existing) {
                    throw new InvalidArgumentException('Mahasiswa tidak ditemukan.');
                }
                $aturan = new AturanKurikulumModel();
                $oldKurikulum = $aturan->getKurikulumIdByNim($existing['username']);
                $newKurikulum = $aturan->getKurikulumIdByNim($username);
                if ($oldKurikulum !== $newKurikulum) {
                    $stmtCount = $this->db->prepare('SELECT COUNT(*) FROM kelas_mahasiswa WHERE mahasiswa_id = ?');
                    $stmtCount->execute([$id]);
                    if ((int)$stmtCount->fetchColumn() > 0) {
                        throw new RuntimeException('Angkatan tidak dapat diubah ke kurikulum lain karena mahasiswa sudah memiliki riwayat kelas.');
                    }
                }

                $stmt = $this->db->prepare("UPDATE users SET username = :username, nama_lengkap = :nama_lengkap WHERE id = :id");
                $stmt->execute(['username' => $username, 'nama_lengkap' => $nama_lengkap, 'id' => $id]);
                $_SESSION['success'] = 'Mahasiswa berhasil diperbarui.';
            } catch (Throwable $e) {
                $_SESSION['error'] = 'Gagal memperbarui mahasiswa: ' . $e->getMessage();
            }

            header('Location: /obe/public/mahasiswa');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if (!$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertMahasiswa($id);
            $stmtCount = $this->db->prepare("
                SELECT (SELECT COUNT(*) FROM kelas_mahasiswa WHERE mahasiswa_id = :id1)
                     + (SELECT COUNT(*) FROM asesmen_nilai WHERE mahasiswa_id = :id2)
            ");
            $stmtCount->execute(['id1' => $id, 'id2' => $id]);
            if ((int)$stmtCount->fetchColumn() > 0) {
                $_SESSION['error'] = 'Mahasiswa tidak dapat dihapus karena sudah memiliki riwayat kelas atau nilai.';
            } else {
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['success'] = 'Mahasiswa berhasil dihapus.';
            }
        }
        header('Location: /obe/public/mahasiswa');
        exit;
    }

    private function validateIdentity($username, $namaLengkap) {
        if (!preg_match('/^\d{7,15}$/', $username)) {
            throw new InvalidArgumentException('NIM harus berupa angka minimal 7 digit.');
        }
        if ($namaLengkap === '') {
            throw new InvalidArgumentException('Nama lengkap wajib diisi.');
        }
        $angkatan = AturanKurikulumModel::extractAngkatan($username);
        if (!$angkatan) {
            throw new InvalidArgumentException('Format NIM tidak valid untuk menentukan tahun angkatan.');
        }
        if (!(new AturanKurikulumModel())->getKurikulumIdByAngkatan($angkatan)) {
            throw new InvalidArgumentException("Belum ada aturan kurikulum untuk angkatan {$angkatan}.");
        }
    }
}
