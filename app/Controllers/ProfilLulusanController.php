<?php
// app/Controllers/ProfilLulusanController.php

require_once __DIR__ . '/../Models/ProfilLulusanModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class ProfilLulusanController {
    private $plModel;
    private $kurikulumModel;
    private $auditModel;
    private $access;

    public function __construct() {
        $this->plModel = new ProfilLulusanModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->auditModel = new AuditLogModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        $selectedKurikulumId = $_GET['kurikulum_id'] ?? null;
        if ($selectedKurikulumId && !$this->access->isSuperAdmin()) $this->access->assertKurikulum($selectedKurikulumId);

        // Default to active curriculum if not selected
        if (!$selectedKurikulumId && !empty($kurikulumList)) {
            foreach ($kurikulumList as $k) {
                if ($k['status'] === 'ACTIVE') {
                    $selectedKurikulumId = $k['id'];
                    break;
                }
            }
            if (!$selectedKurikulumId) {
                $selectedKurikulumId = $kurikulumList[0]['id'];
            }
        }

        $profilLulusanList = $this->plModel->getAll($selectedKurikulumId);

        require __DIR__ . '/../../views/profil_lulusan/index.php';
    }

    public function create() {
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        $selectedKurikulumId = $_GET['kurikulum_id'] ?? ($kurikulumList[0]['id'] ?? 1);
        if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($selectedKurikulumId);
        $cplList = $this->plModel->getCplByKurikulum($selectedKurikulumId);

        require __DIR__ . '/../../views/profil_lulusan/form.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kurikulum_id = (int)$_POST['kurikulum_id'];
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($kurikulum_id);
            $kode = trim($_POST['kode'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $cpl_ids = $_POST['cpl_ids'] ?? [];

            if ($kode && $nama && $deskripsi) {
                $this->plModel->create([
                    'kurikulum_id' => $kurikulum_id,
                    'kode' => $kode,
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'cpl_ids' => $cpl_ids
                ]);

                $this->auditModel->log('PROFIL_LULUSAN', 'CREATE', "Menambahkan Profil Lulusan baru: $kode - $nama");
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan?kurikulum_id=' . $kurikulum_id . '&success=1');
                exit;
            }
        }

        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan?error=1');
        exit;
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $pl = $this->plModel->getById($id);

        if (!$pl) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan');
            exit;
        }

        if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($pl['kurikulum_id']);
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        $cplList = $this->plModel->getCplByKurikulum($pl['kurikulum_id']);

        require __DIR__ . '/../../views/profil_lulusan/form.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $kurikulum_id = (int)$_POST['kurikulum_id'];
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($kurikulum_id);
            $kode = trim($_POST['kode'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $cpl_ids = $_POST['cpl_ids'] ?? [];

            if ($id && $kode && $nama && $deskripsi) {
                $this->plModel->update($id, [
                    'kurikulum_id' => $kurikulum_id,
                    'kode' => $kode,
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'cpl_ids' => $cpl_ids
                ]);

                $this->auditModel->log('PROFIL_LULUSAN', 'UPDATE', "Memperbarui Profil Lulusan $kode - $nama");
                header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan?kurikulum_id=' . $kurikulum_id . '&success=2');
                exit;
            }
        }

        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan?error=1');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $pl = $this->plModel->getById($id);

        if ($pl) {
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($pl['kurikulum_id']);
            $this->plModel->delete($id);
            $this->auditModel->log('PROFIL_LULUSAN', 'DELETE', "Menghapus Profil Lulusan: {$pl['kode']} - {$pl['nama']}");
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan?kurikulum_id=' . $pl['kurikulum_id'] . '&success=3');
            exit;
        }

        header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/profil-lulusan');
        exit;
    }
}
