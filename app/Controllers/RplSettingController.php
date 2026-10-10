<?php
require_once __DIR__ . '/../Models/RplModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';
class RplSettingController {
 private $m; public function __construct(){$this->m=new RplModel();}
 private function guard(){if(!in_array($_SESSION['role']??'',['Super Admin','Admin Prodi'],true)){http_response_code(403);exit('403 Forbidden');}}
 public function index(){$this->guard();$kid=(int)($_GET['kurikulum_id']??0);$settings=$this->m->getSettings();$courses=$this->m->getEligibleCourses($kid?:null);$kurikulum=(new KurikulumModel())->getAll();require __DIR__.'/../../views/rpl_setting/index.php';}
 public function save(){$this->guard();try{$this->m->saveSettings($_POST);$this->m->saveCourseSettings($_POST['mk_ids']??[],$_POST['minimal_nilai_angka']??55,$_POST['mk_minimal_persentase']??70);(new AuditLogModel())->log('RPL','UPDATE_SETTING','Memperbarui pengaturan RPL');$_SESSION['success']='Pengaturan RPL berhasil disimpan.';}catch(Throwable $e){$_SESSION['error']=$e->getMessage();}header('Location: '.($GLOBALS['config']['base_url']??'/obe/public').'/rpl-setting');exit;}
}
