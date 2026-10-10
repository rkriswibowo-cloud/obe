<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class UserManagementController {
    private UserModel $users;
    private AuditLogModel $audit;
    public function __construct(){if(($_SESSION['role']??'')!=='Super Admin'){http_response_code(403);exit('403 Forbidden');}$this->users=new UserModel();$this->audit=new AuditLogModel();}
    private function base(){return $GLOBALS['config']['base_url']??'/obe/public';}
    public function index(){ $users=$this->users->getAllWithRole();$roles=$this->users->getRoles();$prodi=$this->users->getProdi();require __DIR__.'/../../views/user_management/index.php'; }
    public function save(){
        $id=(int)($_POST['id']??0);$data=['username'=>trim($_POST['username']??''),'nama_lengkap'=>trim($_POST['nama_lengkap']??''),'role_id'=>(int)($_POST['role_id']??0),'prodi_id'=>($_POST['prodi_id']??'')!==''?(int)$_POST['prodi_id']:null,'password'=>$_POST['password']??''];
        try{if($data['username']===''||$data['nama_lengkap']===''||!$data['role_id'])throw new InvalidArgumentException('Username, nama, dan role wajib diisi.');if(!$id&&strlen($data['password'])<6)throw new InvalidArgumentException('Password awal minimal 6 karakter.');if($id===(int)($_SESSION['user_id']??0)&&$this->users->getRoleName($data['role_id'])!=='Super Admin')throw new RuntimeException('Role akun yang sedang digunakan tidak dapat diturunkan dari Super Admin.');$id?$this->users->updateAccount($id,$data):$this->users->createAccount($data);$this->audit->log('USER',$id?'UPDATE':'CREATE',($id?'Mengubah':'Membuat').' akun '.$data['username']);$_SESSION['success']='Akun pengguna berhasil disimpan.';}catch(Throwable $e){$_SESSION['error']='Gagal menyimpan akun: '.$e->getMessage();}header('Location: '.$this->base().'/user-management');exit;
    }
    public function delete(){
        $id=(int)($_POST['id']??0);try{if($id===(int)($_SESSION['user_id']??0))throw new RuntimeException('Akun yang sedang digunakan tidak dapat dihapus.');$user=$this->users->getById($id);$this->users->deleteAccount($id);$this->audit->log('USER','DELETE','Menghapus akun '.($user['username']??'#'.$id));$_SESSION['success']='Akun berhasil dihapus.';}catch(Throwable $e){$_SESSION['error']='Akun tidak dapat dihapus: '.$e->getMessage();}header('Location: '.$this->base().'/user-management');exit;
    }
}
