<?php
require_once __DIR__ . '/../Models/PengampuMataKuliahModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class PengampuMataKuliahController
{
    private PengampuMataKuliahModel $model;
    private AuditLogModel $audit;
    private AccessContext $access;
    private PDO $db;

    public function __construct()
    {
        $this->model=new PengampuMataKuliahModel();$this->audit=new AuditLogModel();$this->access=new AccessContext();$this->db=Database::getInstance();
        if(!$this->access->isSuperAdmin()&&!$this->access->isKaprodi())$this->access->forbidden();
    }

    public function index(): void
    {
        $prodiIds = $this->access->isSuperAdmin() ? [] : $this->access->managedProdiIds();
        $search = trim((string)($_GET['search'] ?? ''));
        $perPage = 10;
        $totalAssignments = $this->model->countAll($prodiIds, $search);
        $totalPages = max(1, (int)ceil($totalAssignments / $perPage));
        $page = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
        $offset = ($page - 1) * $perPage;
        $assignments = $this->model->getAll($prodiIds, $search, $perPage, $offset);
        $scopeSql=$prodiIds?' WHERE k.prodi_id IN ('.implode(',',array_fill(0,count($prodiIds),'?')).')':'';
        $stmt=$this->db->prepare("SELECT mk.id,mk.kode,mk.nama,k.nama kurikulum_nama,k.prodi_id,p.kode prodi_kode FROM mata_kuliah mk JOIN kurikulum k ON k.id=mk.kurikulum_id JOIN program_studi p ON p.id=k.prodi_id{$scopeSql} ORDER BY p.nama,k.tahun_mulai DESC,mk.kode");$stmt->execute($prodiIds);$courses=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $dosenSql="SELECT u.id,u.nama_lengkap,u.nidn,u.prodi_id,p.kode prodi_kode FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN program_studi p ON p.id=u.prodi_id WHERE r.name IN ('Dosen','Kaprodi')";$params=[];if($prodiIds){$dosenSql.=' AND u.prodi_id IN ('.implode(',',array_fill(0,count($prodiIds),'?')).')';$params=$prodiIds;}$dosenSql.=' ORDER BY p.nama,u.nama_lengkap';$stmt=$this->db->prepare($dosenSql);$stmt->execute($params);$lecturers=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $academicYears=$this->db->query('SELECT id,nama,is_active FROM tahun_akademik ORDER BY is_active DESC,id DESC')->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__.'/../../views/pengampu_mk/index.php';
    }

    public function save(): void
    {
        try{$mkId=(int)($_POST['mata_kuliah_id']??0);if(!$this->access->isSuperAdmin())$this->access->assertMataKuliah($mkId);$id=$this->model->save(['mata_kuliah_id'=>$mkId,'tahun_akademik_id'=>(int)($_POST['tahun_akademik_id']??0),'pjmk_user_id'=>(int)($_POST['pjmk_user_id']??0),'tim_ids'=>$_POST['tim_ids']??[],'status'=>$_POST['status']??'ACTIVE']);$this->audit->log('PENGAMPU_MK','SAVE','Menyimpan penugasan pengampu MK #'.$mkId);$_SESSION['success']='Penugasan PJMK dan tim teaching berhasil disimpan. RPS telah disinkronkan.';}catch(Throwable $e){$_SESSION['error']='Gagal menyimpan penugasan: '.$e->getMessage();}$this->redirect();
    }

    public function sync(): void
    {
        try{$id=(int)($_POST['id']??0);$assignment=$this->model->getById($id);if(!$assignment)throw new RuntimeException('Penugasan tidak ditemukan.');if(!$this->access->isSuperAdmin())$this->access->assertProdi($assignment['prodi_id']);$count=$this->model->syncClasses($id);$this->audit->log('PENGAMPU_MK','SYNC','Sinkronisasi penugasan #'.$id.' ke '.$count.' kelas');$_SESSION['success']='PJMK dan tim teaching berhasil disinkronkan ke '.$count.' kelas.';}catch(Throwable $e){$_SESSION['error']='Sinkronisasi gagal: '.$e->getMessage();}$this->redirect();
    }

    private function redirect(): void {header('Location: '.($GLOBALS['config']['base_url']??'/obe/public').'/pengampu-mk');exit;}
}
