<?php
require_once __DIR__ . '/../Models/RplModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class RplController {
    private $model; private $audit; private $db; private $access;
    public function __construct(){ $this->model=new RplModel();$this->audit=new AuditLogModel();$this->db=Database::getInstance();$this->access=new AccessContext(); }
    private function base(){return $GLOBALS['config']['base_url']??'/obe/public';}
    private function admin(){return in_array($_SESSION['role']??'',['Super Admin','Admin Prodi'],true);}
    public function index(){
        if(($_SESSION['role']??'')==='Mahasiswa'){$profile=$this->model->getByStudent($_SESSION['user_id']);if(!$profile){require __DIR__.'/../../views/rpl/not_registered.php';return;}$this->showProfile($profile);return;}
        $profiles=$this->model->getProfiles();$students=$this->model->getStudents();$kurikulum=(new KurikulumModel())->getAll();if(!$this->access->isSuperAdmin()&&$this->access->isKaprodi()){$profiles=array_values(array_filter($profiles,fn($x)=>$this->access->canAccessRplProfile($x['id'])));$students=array_values(array_filter($students,fn($x)=>$this->access->canAccessMahasiswa($x['id'])));$kurikulum=$this->access->filterKurikulumRows($kurikulum);}require __DIR__.'/../../views/rpl/index.php';
    }
    public function storeProfile(){if(!$this->admin())$this->forbidden();try{$p=$this->model->createProfile($_POST['mahasiswa_id']??0,$_POST['kurikulum_id']??0);$this->model->rebuildPlan($p['id']);$this->audit->log('RPL','CREATE_PROFILE','Menetapkan mahasiswa RPL: '.$p['nim']);$_SESSION['success']='Mahasiswa berhasil ditetapkan sebagai mahasiswa RPL.';}catch(Throwable $e){$_SESSION['error']=$e->getMessage();}header('Location: '.$this->base().'/rpl');exit;}
    public function detail(){
        $id=(int)($_GET['id']??0);$profile=$this->model->getProfile($id);if(!$profile){http_response_code(404);echo'Mahasiswa RPL tidak ditemukan.';return;}if(($_SESSION['role']??'')==='Mahasiswa'&&(int)$profile['mahasiswa_id']!==(int)$_SESSION['user_id'])$this->forbidden();if(!$this->access->isSuperAdmin()&&$this->access->isKaprodi())$this->access->assertRplProfile($id);$this->showProfile($profile);
    }
    public function printPlan(){
        $id=(int)($_GET['id']??0);$semester=(int)($_GET['semester']??0);$profile=$this->model->getProfile($id);
        if(!$profile||$semester<1||$semester>(int)$profile['skema_semester']){http_response_code(404);echo'Rencana semester RPL tidak ditemukan.';return;}
        if(($_SESSION['role']??'')==='Mahasiswa'&&(int)$profile['mahasiswa_id']!==(int)$_SESSION['user_id'])$this->forbidden();
        if(!$this->access->isSuperAdmin()&&$this->access->isKaprodi())$this->access->assertRplProfile($id);
        $plan=array_values(array_filter($this->model->getPlan($id),fn($row)=>(int)$row['semester_rpl']===$semester));
        require __DIR__.'/../../views/rpl/cetak_rencana.php';
    }
    private function showProfile($profile){$conversions=$this->model->getConversions($profile['id']);$courses=array_values(array_filter($this->model->getEligibleCourses($profile['kurikulum_id']),fn($x)=>(int)$x['rpl_active']===1));$plan=$this->model->getPlan($profile['id']);$summary=$this->model->summary($profile['id']);require __DIR__.'/../../views/rpl/detail.php';}
    public function addConversion(){if(!$this->admin())$this->forbidden();$id=(int)($_POST['rpl_mahasiswa_id']??0);try{$this->model->addConversion($id,$_POST,$_SESSION['user_id']??null);$this->audit->log('RPL','SUBMIT_CONVERSION','Mengajukan konversi RPL profil #'.$id);$_SESSION['success']='Konversi diajukan kepada Kaprodi.';}catch(Throwable $e){$_SESSION['error']='Gagal mengajukan: '.$e->getMessage();}header('Location: '.$this->base().'/rpl/detail?id='.$id);exit;}
    public function decide(){if(!$this->access->isSuperAdmin()&&!$this->access->isKaprodi())$this->forbidden();$profileId=$this->model->getProfileIdByConversion($_POST['id']??0);if(!$this->access->isSuperAdmin())$this->access->assertRplProfile($profileId);try{$id=$this->model->decide($_POST['id']??0,$_POST['status']??'',trim($_POST['catatan']??''),$_SESSION['user_id']??null);$this->audit->log('RPL','DECISION',($_POST['status']??'').' konversi #'.($_POST['id']??''));$_SESSION['success']='Keputusan Kaprodi berhasil disimpan.';}catch(Throwable $e){$_SESSION['error']=$e->getMessage();$id=(int)($_POST['rpl_mahasiswa_id']??0);}header('Location: '.$this->base().'/rpl/detail?id='.$id);exit;}
    public function updateConversion(){if(($_SESSION['role']??'')!=='Super Admin')$this->forbidden();$rplId=(int)($_POST['rpl_mahasiswa_id']??0);try{$this->model->updateConversion($_POST['id']??0,$rplId,$_POST);$this->audit->log('RPL','UPDATE_CONVERSION','Mengubah konversi #'.($_POST['id']??''));$_SESSION['success']='Data konversi berhasil diperbarui.';}catch(Throwable $e){$_SESSION['error']='Gagal memperbarui konversi: '.$e->getMessage();}header('Location: '.$this->base().'/rpl/detail?id='.$rplId.'#hasil-konversi');exit;}
    public function updateScheme(){
        if(!$this->admin()&&!$this->access->isKaprodi())$this->forbidden();
        $id=(int)($_POST['rpl_mahasiswa_id']??0);
        $scheme=(int)($_POST['skema_semester']??0);
        if(!$this->access->isSuperAdmin())$this->access->assertRplProfile($id);
        try{
            $this->model->setSemesterScheme($id,$scheme);
            $this->audit->log('RPL','UPDATE_SCHEME',"Mengubah skema semester profil #$id menjadi $scheme semester");
            $_SESSION['success']="Skema masa studi berhasil diatur menjadi $scheme semester.";
        }catch(Throwable $e){
            $_SESSION['error']='Gagal mengatur skema: '.$e->getMessage();
        }
        header('Location: '.$this->base().'/rpl/detail?id='.$id.'#rencana-studi');exit;
    }
    public function moveCourse(){
        if(!$this->admin()&&!$this->access->isKaprodi())$this->forbidden();
        $id=(int)($_POST['rpl_mahasiswa_id']??0);
        $planId=(int)($_POST['plan_id']??0);
        $target=(int)($_POST['target_semester']??0);
        if(!$this->access->isSuperAdmin())$this->access->assertRplProfile($id);
        try{
            $this->model->movePlanCourse($id,$planId,$target);
            $this->audit->log('RPL','MOVE_COURSE',"Memindahkan MK rencana studi #$planId ke semester $target (profil #$id)");
            $_SESSION['success']="Mata kuliah berhasil dipindahkan ke Semester RPL $target.";
        }catch(Throwable $e){
            $_SESSION['error']='Gagal memindahkan mata kuliah: '.$e->getMessage();
        }
        header('Location: '.$this->base().'/rpl/detail?id='.$id.'#rencana-studi');exit;
    }
    public function rebuildPlanAction(){
        if(!$this->admin()&&!$this->access->isKaprodi())$this->forbidden();
        $id=(int)($_POST['rpl_mahasiswa_id']??0);
        if(!$this->access->isSuperAdmin())$this->access->assertRplProfile($id);
        try{
            $this->model->rebuildPlan($id);
            $this->audit->log('RPL','REBUILD_PLAN',"Mereset ulang pembagian rencana studi profil #$id");
            $_SESSION['success']='Rencana studi berhasil di-generate ulang otomatis.';
        }catch(Throwable $e){
            $_SESSION['error']='Gagal reset rencana studi: '.$e->getMessage();
        }
        header('Location: '.$this->base().'/rpl/detail?id='.$id.'#rencana-studi');exit;
    }
    private function forbidden(){http_response_code(403);echo'403 Forbidden';exit;}
}
