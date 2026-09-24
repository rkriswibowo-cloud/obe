<?php
session_start();
$config = require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

// Routing fleksibel: mendukung akses via http://localhost/obe maupun http://localhost/obe/public
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$path = $request_uri;
if (strpos($path, '/obe/public') === 0) {
    $path = substr($path, strlen('/obe/public'));
} elseif (strpos($path, '/obe') === 0) {
    $path = substr($path, strlen('/obe'));
}

// Normalisasi path
$path = '/' . trim($path, '/');
if ($path === '' || $path === '/') {
    $path = '/dashboard';
}

$base_path = '/obe/public';

$authController = new AuthController();

// Public routes
if ($path === '/login') {
    $authController->loginView();
    exit;
}
if ($path === '/login/submit') {
    $authController->login();
    exit;
}
if ($path === '/logout') {
    $authController->logout();
    exit;
}

// Protected routes middleware
if (!isset($_SESSION['user_id'])) {
    header("Location: $base_path/login");
    exit;
}

switch ($path) {
    case '/dashboard':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $d = new DashboardController();
        $d->index();
        break;
    case '/kurikulum':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->index();
        break;
    case '/kurikulum/create':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->create();
        break;
    case '/kurikulum/store':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->store();
        break;
    case '/kurikulum/edit':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->edit();
        break;
    case '/kurikulum/update':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->update();
        break;
    case '/kurikulum/delete':
        require_once __DIR__ . '/../app/Controllers/KurikulumController.php';
        $k = new KurikulumController();
        $k->delete();
        break;
    case '/cpl':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->index();
        break;
    case '/cpl/create':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->create();
        break;
    case '/cpl/store':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->store();
        break;
    case '/cpl/edit':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->edit();
        break;
    case '/cpl/update':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->update();
        break;
    case '/cpl/delete':
        require_once __DIR__ . '/../app/Controllers/CplController.php';
        $c = new CplController();
        $c->delete();
        break;
    case '/mata-kuliah':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->index();
        break;
    case '/mata-kuliah/create':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->create();
        break;
    case '/mata-kuliah/store':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->store();
        break;
    case '/mata-kuliah/edit':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->edit();
        break;
    case '/mata-kuliah/update':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->update();
        break;
    case '/mata-kuliah/delete':
        require_once __DIR__ . '/../app/Controllers/MataKuliahController.php';
        $m = new MataKuliahController();
        $m->delete();
        break;
    case '/pemetaan-mk-cpl':
        require_once __DIR__ . '/../app/Controllers/PemetaanMkCplController.php';
        $p = new PemetaanMkCplController();
        $p->index();
        break;
    case '/pemetaan-mk-cpl/save':
        require_once __DIR__ . '/../app/Controllers/PemetaanMkCplController.php';
        $p = new PemetaanMkCplController();
        $p->save();
        break;
    case '/import':
        require_once __DIR__ . '/../app/Controllers/ImportController.php';
        $i = new ImportController();
        $i->index();
        break;
    case '/import/upload':
        require_once __DIR__ . '/../app/Controllers/ImportController.php';
        $i = new ImportController();
        $i->upload();
        break;
    case '/import/template':
        require_once __DIR__ . '/../app/Controllers/ImportController.php';
        $i = new ImportController();
        $i->template();
        break;
    case '/import/upload-text':
        require_once __DIR__ . '/../app/Controllers/ImportController.php';
        $i = new ImportController();
        $i->uploadText();
        break;
    case '/rps':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->index();
        break;
    case '/rps/cpmk/create':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->createCpmk();
        break;
    case '/rps/cpmk/edit':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->editCpmk();
        break;
    case '/rps/cpmk/delete':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->deleteCpmk();
        break;
    case '/rps/sub_cpmk/create':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->createSubCpmk();
        break;
    case '/rps/sub_cpmk/edit':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->editSubCpmk();
        break;
    case '/rps/sub_cpmk/delete':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->deleteSubCpmk();
        break;
    case '/rps/pertemuan/store':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->storePertemuan();
        break;
    case '/rps/pertemuan/update':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->updatePertemuan();
        break;
    case '/rps/pertemuan/delete':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->deletePertemuan();
        break;
    case '/rps/pertemuan/generate-template':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->generateTemplatePertemuan();
        break;
    case '/rps/document':
    case '/rps/view':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->document();
        break;
    case '/rps/save-settings':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->saveSettings();
        break;

    // Rubrik Routes
    case '/rubrik':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->index();
        break;
    case '/rubrik/create':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->create();
        break;
    case '/rubrik/detail':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->detail();
        break;
    case '/rubrik/store':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->store();
        break;
    case '/rubrik/builder':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->builder();
        break;
    case '/rubrik/save-matrix':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->saveMatrix();
        break;
    case '/rubrik/duplicate':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->duplicate();
        break;
    case '/rubrik/delete':
        require_once __DIR__ . '/../app/Controllers/RubrikController.php';
        $rubrikCtrl = new RubrikController();
        $rubrikCtrl->delete();
        break;

    // CQI Routes
    case '/cqi':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->index();
        break;
    case '/cqi/create':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->create();
        break;
    case '/cqi/store':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->store();
        break;
    case '/cqi/edit':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->edit();
        break;
    case '/cqi/update':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->update();
        break;
    case '/cqi/status':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->status();
        break;
    case '/cqi/delete':
        require_once __DIR__ . '/../app/Controllers/CqiController.php';
        $cqiCtrl = new CqiController();
        $cqiCtrl->delete();
        break;

    case '/asesmen':
        require_once __DIR__ . '/../app/Controllers/AsesmenController.php';
        $a = new AsesmenController();
        $a->index();
        break;
    case '/asesmen/save':
    case '/asesmen/nilai/submit':
        require_once __DIR__ . '/../app/Controllers/AsesmenController.php';
        $a = new AsesmenController();
        $a->save();
        break;

    // Laporan Routes
    case '/laporan':
        require_once __DIR__ . '/../app/Controllers/LaporanController.php';
        $l = new LaporanController();
        $l->index();
        break;
    case '/laporan/detail':
        require_once __DIR__ . '/../app/Controllers/LaporanController.php';
        $l = new LaporanController();
        $l->detail();
        break;
    case '/kelas':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->index();
        break;
    case '/kelas/create':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->create();
        break;
    case '/kelas/store':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->store();
        break;
    case '/kelas/edit':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->edit();
        break;
    case '/kelas/update':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->update();
        break;
    case '/kelas/delete':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->delete();
        break;
    case '/kelas/peserta':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->peserta();
        break;
    case '/kelas/peserta/add':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->addPeserta();
        break;
    case '/kelas/peserta/remove':
        require_once __DIR__ . '/../app/Controllers/KelasController.php';
        $k = new KelasController();
        $k->removePeserta();
        break;
    // Tahun Akademik Routes
    case '/tahun-akademik':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->index();
        break;
    case '/tahun-akademik/create':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->create();
        break;
    case '/tahun-akademik/store':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->store();
        break;
    case '/tahun-akademik/edit':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->edit();
        break;
    case '/tahun-akademik/update':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->update();
        break;
    case '/tahun-akademik/delete':
        require_once __DIR__ . '/../app/Controllers/TahunAkademikController.php';
        $t = new TahunAkademikController();
        $t->delete();
        break;

    // Dosen Routes
    case '/dosen':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->index();
        break;
    case '/dosen/create':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->create();
        break;
    case '/dosen/store':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->store();
        break;
    case '/dosen/edit':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->edit();
        break;
    case '/dosen/update':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->update();
        break;
    case '/dosen/delete':
        require_once __DIR__ . '/../app/Controllers/DosenController.php';
        $d = new DosenController();
        $d->delete();
        break;
    // Mahasiswa Routes
    case '/mahasiswa':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->index();
        break;
    case '/mahasiswa/create':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->create();
        break;
    case '/mahasiswa/store':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->store();
        break;
    case '/mahasiswa/edit':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->edit();
        break;
    case '/mahasiswa/update':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->update();
        break;
    case '/mahasiswa/delete':
        require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
        $m = new MahasiswaController();
        $m->delete();
        break;

    // KHS & Tracking CPL Mahasiswa Routes
    case '/khs':
        require_once __DIR__ . '/../app/Controllers/KhsController.php';
        $khsCtrl = new KhsController();
        $khsCtrl->index();
        break;
    case '/khs/cetak':
        require_once __DIR__ . '/../app/Controllers/KhsController.php';
        $khsCtrl = new KhsController();
        $khsCtrl->cetak();
        break;
    
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
