<?php
session_start();
$config = require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Support/AccessContext.php';

// CSRF token is injected into every server-rendered POST form below.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
ob_start(static function ($html) use ($csrfToken) {
    $html = preg_replace_callback(
        '/<form\b([^>]*)\bmethod\s*=\s*(["\']?)post\2([^>]*)>/i',
        static function ($match) use ($csrfToken) {
            if (stripos($match[0], 'name="csrf_token"') !== false) {
                return $match[0];
            }
            return $match[0] . '<input type="hidden" name="csrf_token" value="'
                . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">';
        },
        $html
    );
    return preg_replace_callback(
        '/href=(["\'])([^"\']*(?:\/delete|\/remove|\/generate-template|\/logout)[^"\']*)\1/i',
        static function ($match) use ($csrfToken) {
            if (strpos($match[2], 'csrf_token=') !== false) {
                return $match[0];
            }
            $separator = strpos($match[2], '?') === false ? '?' : '&amp;';
            return 'href=' . $match[1] . $match[2] . $separator . 'csrf_token=' . rawurlencode($csrfToken) . $match[1];
        },
        $html
    );
});

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

// Semua mutasi wajib berasal dari form/tautan aplikasi yang sah.
$getMutationPaths = [
    '/logout', '/prodi/delete', '/kurikulum/delete', '/profil-lulusan/delete',
    '/cpl/delete', '/mata-kuliah/delete', '/rps/cpmk/delete', '/rps/sub_cpmk/delete',
    '/rps/pertemuan/delete', '/rps/pertemuan/generate-template', '/rubrik/delete',
    '/cqi/delete', '/kelas/delete', '/kelas/peserta/remove', '/tahun-akademik/delete',
    '/dosen/delete', '/mahasiswa/delete', '/aturan-kurikulum/delete',
];
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST' || ($requestMethod === 'GET' && in_array($path, $getMutationPaths, true))) {
    $submittedToken = $requestMethod === 'POST'
        ? ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))
        : ($_GET['csrf_token'] ?? '');
    if (!is_string($submittedToken) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(419);
        echo 'Permintaan tidak valid atau sesi formulir telah kedaluwarsa. Silakan muat ulang halaman.';
        exit;
    }
}

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

// Read-only RPS preview shared by DEIS. This route deliberately sits before
// the authentication middleware and only renders the document view.
if ($requestMethod === 'GET' && preg_match('#^/rps/preview/(\d+)$#', $path, $matches)) {
    require_once __DIR__ . '/../app/Controllers/RpsController.php';
    $r = new RpsController();
    $r->document((int)$matches[1], true);
    exit;
}

// Protected routes middleware
if (!isset($_SESSION['user_id'])) {
    header("Location: $base_path/login");
    exit;
}

// Otorisasi server-side berdasarkan tanggung jawab pada PRD.
$role = $_SESSION['role'] ?? '';
$access = new AccessContext();
$isKaprodi = $access->isKaprodi();
$rolePrefixes = [
    'Super Admin' => ['/'],
    'Admin Prodi' => ['/dashboard', '/prodi', '/kurikulum', '/aturan-kurikulum', '/profil-lulusan', '/cpl', '/mata-kuliah', '/pemetaan-mk-cpl', '/import', '/rps', '/rubrik', '/kelas', '/asesmen', '/laporan', '/laporan-semester', '/monitoring-cpl-mk', '/khs', '/transkrip', '/cqi', '/tahun-akademik', '/dosen', '/mahasiswa', '/rpl', '/rpl-setting'],
    'Dosen' => ['/dashboard', '/rps', '/rubrik', '/kelas', '/asesmen', '/laporan', '/laporan-semester', '/monitoring-cpl-mk', '/khs', '/transkrip'],
    'Kaprodi' => ['/dashboard', '/rps', '/rubrik', '/laporan', '/laporan-semester', '/monitoring-cpl-mk', '/khs', '/transkrip', '/cqi', '/rpl'],
    'Mahasiswa' => ['/dashboard', '/laporan', '/khs', '/transkrip', '/rpl'],
];
$kaprodiPrefixes = ['/dashboard', '/tahun-akademik', '/dosen', '/mahasiswa', '/prodi', '/kurikulum', '/aturan-kurikulum', '/profil-lulusan', '/cpl', '/mata-kuliah', '/pemetaan-mk-cpl', '/pengampu-mk', '/rps', '/rubrik', '/kelas', '/asesmen', '/laporan', '/laporan-semester', '/monitoring-cpl-mk', '/khs', '/transkrip', '/cqi', '/rpl'];
$allowedPrefixes = $rolePrefixes[$role] ?? [];
if ($isKaprodi) {
    $allowedPrefixes = array_values(array_unique(array_merge($allowedPrefixes, $kaprodiPrefixes)));
}
$allowed = false;
foreach ($allowedPrefixes as $prefix) {
    if ($prefix === '/' || $path === $prefix || strpos($path, $prefix . '/') === 0) {
        $allowed = true;
        break;
    }
}
if (!$allowed) {
    http_response_code(403);
    echo '403 Forbidden: Anda tidak memiliki akses ke proses ini.';
    exit;
}

$roleDeniedPaths = [
    'Dosen' => [
        '/kelas/create', '/kelas/store', '/kelas/edit', '/kelas/update', '/kelas/delete',
        '/kelas/peserta/add', '/kelas/peserta/remove',
        '/rps/cpmk/create', '/rps/cpmk/edit', '/rps/cpmk/delete',
        '/rps/sub_cpmk/create', '/rps/sub_cpmk/edit', '/rps/sub_cpmk/delete',
        '/rubrik/create', '/rubrik/store', '/rubrik/builder', '/rubrik/save-matrix',
        '/rubrik/duplicate', '/rubrik/delete',
    ],
    'Kaprodi' => [
        '/rps/cpmk/create', '/rps/cpmk/edit', '/rps/cpmk/delete',
        '/rps/sub_cpmk/create', '/rps/sub_cpmk/edit', '/rps/sub_cpmk/delete',
        '/rps/pertemuan/store', '/rps/pertemuan/update', '/rps/pertemuan/delete',
        '/rps/pertemuan/generate-template', '/rps/pertemuan/import-csv', '/rps/save-settings',
        '/rubrik/create', '/rubrik/store', '/rubrik/builder', '/rubrik/save-matrix',
        '/rubrik/duplicate', '/rubrik/delete',
    ],
];
if (in_array($path, $roleDeniedPaths[$role] ?? [], true)) {
    http_response_code(403);
    echo '403 Forbidden: Peran Anda hanya memiliki akses baca pada proses ini.';
    exit;
}

if ($isKaprodi && !$access->isSuperAdmin()) {
    $kaprodiDeniedPaths = [
        '/prodi/create', '/prodi/store', '/prodi/edit', '/prodi/update', '/prodi/delete',
        '/tahun-akademik/create', '/tahun-akademik/store', '/tahun-akademik/edit',
        '/tahun-akademik/update', '/tahun-akademik/delete',
    ];
    if (in_array($path, $kaprodiDeniedPaths, true)) {
        http_response_code(403);
        echo '403 Forbidden: Pengaturan global hanya dapat diubah oleh Super Admin.';
        exit;
    }
}

switch ($path) {
    case '/pengampu-mk':
        require_once __DIR__ . '/../app/Controllers/PengampuMataKuliahController.php';(new PengampuMataKuliahController())->index();break;
    case '/pengampu-mk/save':
        require_once __DIR__ . '/../app/Controllers/PengampuMataKuliahController.php';(new PengampuMataKuliahController())->save();break;
    case '/pengampu-mk/sync':
        require_once __DIR__ . '/../app/Controllers/PengampuMataKuliahController.php';(new PengampuMataKuliahController())->sync();break;
    case '/user-management':
        require_once __DIR__ . '/../app/Controllers/UserManagementController.php';(new UserManagementController())->index();break;
    case '/user-management/save':
        require_once __DIR__ . '/../app/Controllers/UserManagementController.php';(new UserManagementController())->save();break;
    case '/user-management/delete':
        require_once __DIR__ . '/../app/Controllers/UserManagementController.php';(new UserManagementController())->delete();break;
    case '/branding':
        require_once __DIR__ . '/../app/Controllers/BrandingController.php';(new BrandingController())->index();break;
    case '/branding/save':
        require_once __DIR__ . '/../app/Controllers/BrandingController.php';(new BrandingController())->save();break;
    case '/branding/reset':
        require_once __DIR__ . '/../app/Controllers/BrandingController.php';(new BrandingController())->reset();break;
    case '/dashboard':
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $d = new DashboardController();
        $d->index();
        break;

    // Program Studi Routes
    case '/prodi':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->index();
        break;
    case '/prodi/create':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->create();
        break;
    case '/prodi/store':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->store();
        break;
    case '/prodi/edit':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->edit();
        break;
    case '/prodi/update':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->update();
        break;
    case '/prodi/delete':
        require_once __DIR__ . '/../app/Controllers/ProdiController.php';
        $pr = new ProdiController();
        $pr->delete();
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

    // Profil Lulusan Routes
    case '/profil-lulusan':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->index();
        break;
    case '/profil-lulusan/create':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->create();
        break;
    case '/profil-lulusan/store':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->store();
        break;
    case '/profil-lulusan/edit':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->edit();
        break;
    case '/profil-lulusan/update':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->update();
        break;
    case '/profil-lulusan/delete':
        require_once __DIR__ . '/../app/Controllers/ProfilLulusanController.php';
        $pl = new ProfilLulusanController();
        $pl->delete();
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
    case '/rps/pertemuan/import-csv':
        require_once __DIR__ . '/../app/Controllers/RpsController.php';
        $r = new RpsController();
        $r->importPertemuanCsv();
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
    case '/laporan-semester':
        require_once __DIR__ . '/../app/Controllers/LaporanSemesterController.php';
        $laporanSemester = new LaporanSemesterController();
        $laporanSemester->index();
        break;
    case '/monitoring-cpl-mk':
        require_once __DIR__ . '/../app/Controllers/MonitoringCplMkController.php';
        $mcpl = new MonitoringCplMkController();
        $mcpl->index();
        break;
    case '/monitoring-cpl-mk/detail':
        require_once __DIR__ . '/../app/Controllers/MonitoringCplMkController.php';
        $mcpl = new MonitoringCplMkController();
        $mcpl->detail();
        break;
    case '/monitoring-cpl-mk/export':
        require_once __DIR__ . '/../app/Controllers/MonitoringCplMkController.php';
        $mcpl = new MonitoringCplMkController();
        $mcpl->export();
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

    // Aturan Kurikulum Routes
    case '/aturan-kurikulum':
        require_once __DIR__ . '/../app/Controllers/AturanKurikulumController.php';
        $ak = new AturanKurikulumController();
        $ak->index();
        break;
    case '/aturan-kurikulum/store':
        require_once __DIR__ . '/../app/Controllers/AturanKurikulumController.php';
        $ak = new AturanKurikulumController();
        $ak->store();
        break;
    case '/aturan-kurikulum/update':
        require_once __DIR__ . '/../app/Controllers/AturanKurikulumController.php';
        $ak = new AturanKurikulumController();
        $ak->update();
        break;
    case '/aturan-kurikulum/delete':
        require_once __DIR__ . '/../app/Controllers/AturanKurikulumController.php';
        $ak = new AturanKurikulumController();
        $ak->delete();
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

    // Transkrip Akademik & Capaian CPL Mahasiswa Routes
    case '/transkrip':
        require_once __DIR__ . '/../app/Controllers/TranskripController.php';
        $transkripCtrl = new TranskripController();
        $transkripCtrl->index();
        break;
    case '/transkrip/cetak':
        require_once __DIR__ . '/../app/Controllers/TranskripController.php';
        $transkripCtrl = new TranskripController();
        $transkripCtrl->cetak();
        break;

    // Rekognisi Pembelajaran Lampau (modul tambahan, terpisah dari kelas reguler)
    case '/rpl':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->index();
        break;
    case '/rpl/profile/store':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->storeProfile();
        break;
    case '/rpl/profile/update':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->updateProfile();
        break;
    case '/rpl/profile/delete':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->deleteProfile();
        break;
    case '/rpl/detail':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->detail();
        break;
    case '/rpl/plan/print':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->printPlan();
        break;
    case '/rpl/plan/update-scheme':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->updateScheme();
        break;
    case '/rpl/plan/move-course':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->moveCourse();
        break;
    case '/rpl/plan/rebuild':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->rebuildPlanAction();
        break;
    case '/rpl/conversion/store':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->addConversion();
        break;
    case '/rpl/conversion/decide':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->decide();
        break;
    case '/rpl/conversion/update':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->updateConversion();
        break;
    case '/rpl/conversion/delete':
        require_once __DIR__ . '/../app/Controllers/RplController.php';
        (new RplController())->deleteConversion();
        break;
    case '/rpl-setting':
        require_once __DIR__ . '/../app/Controllers/RplSettingController.php';
        (new RplSettingController())->index();
        break;
    case '/rpl-setting/save':
        require_once __DIR__ . '/../app/Controllers/RplSettingController.php';
        (new RplSettingController())->save();
        break;

    // Audit Log Route
    case '/audit-log':
        require_once __DIR__ . '/../app/Controllers/AuditLogController.php';
        $auditCtrl = new AuditLogController();
        $auditCtrl->index();
        break;

    // Notifikasi Routes
    case '/notifikasi/mark-read':
        require_once __DIR__ . '/../app/Controllers/NotificationController.php';
        $notifCtrl = new NotificationController();
        $notifCtrl->markAllRead();
        break;
    
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
