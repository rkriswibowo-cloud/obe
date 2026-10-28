<?php
require_once __DIR__ . '/../../config/database.php';

class PengampuMataKuliahModel
{
    private PDO $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function countAll(array $prodiIds = [], string $search = ''): int
    {
        $sql = "SELECT COUNT(DISTINCT pm.id)
                FROM mata_kuliah_pengampu pm
                JOIN mata_kuliah mk ON mk.id = pm.mata_kuliah_id
                JOIN kurikulum k ON k.id = mk.kurikulum_id
                JOIN program_studi p ON p.id = k.prodi_id
                JOIN tahun_akademik ta ON ta.id = pm.tahun_akademik_id
                JOIN users u ON u.id = pm.pjmk_user_id
                LEFT JOIN mata_kuliah_pengampu_tim pt ON pt.pengampu_id = pm.id
                LEFT JOIN users ut ON ut.id = pt.dosen_id";
        $where = [];
        $params = [];
        if (!empty($prodiIds)) {
            $where[] = 'k.prodi_id IN (' . implode(',', array_fill(0, count($prodiIds), '?')) . ')';
            $params = array_map('intval', $prodiIds);
        }
        $search = trim($search);
        if ($search !== '') {
            $where[] = '(mk.kode LIKE ? OR mk.nama LIKE ? OR p.kode LIKE ? OR p.nama LIKE ? OR k.nama LIKE ? OR ta.nama LIKE ? OR u.nama_lengkap LIKE ? OR u.nidn LIKE ? OR ut.nama_lengkap LIKE ? OR pm.status LIKE ?)';
            $term = '%' . $search . '%';
            for ($i = 0; $i < 10; $i++) {
                $params[] = $term;
            }
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getAll(array $prodiIds = [], string $search = '', ?int $limit = null, ?int $offset = null): array
    {
        $sql = "SELECT pm.*, mk.kode AS mk_kode, mk.nama AS mk_nama, k.id AS kurikulum_id, k.nama AS kurikulum_nama,
                       k.prodi_id, p.kode AS prodi_kode, p.nama AS prodi_nama, ta.nama AS tahun_akademik,
                       u.nama_lengkap AS pjmk_nama, u.nidn AS pjmk_nidn,
                       COUNT(DISTINCT pt.dosen_id) AS total_tim,
                       GROUP_CONCAT(DISTINCT pt.dosen_id) AS tim_ids_str
                FROM mata_kuliah_pengampu pm
                JOIN mata_kuliah mk ON mk.id = pm.mata_kuliah_id
                JOIN kurikulum k ON k.id = mk.kurikulum_id
                JOIN program_studi p ON p.id = k.prodi_id
                JOIN tahun_akademik ta ON ta.id = pm.tahun_akademik_id
                JOIN users u ON u.id = pm.pjmk_user_id
                LEFT JOIN mata_kuliah_pengampu_tim pt ON pt.pengampu_id = pm.id
                LEFT JOIN users ut ON ut.id = pt.dosen_id";
        $where = [];
        $params = [];
        if (!empty($prodiIds)) {
            $where[] = 'k.prodi_id IN (' . implode(',', array_fill(0, count($prodiIds), '?')) . ')';
            $params = array_map('intval', $prodiIds);
        }
        $search = trim($search);
        if ($search !== '') {
            $where[] = '(mk.kode LIKE ? OR mk.nama LIKE ? OR p.kode LIKE ? OR p.nama LIKE ? OR k.nama LIKE ? OR ta.nama LIKE ? OR u.nama_lengkap LIKE ? OR u.nidn LIKE ? OR ut.nama_lengkap LIKE ? OR pm.status LIKE ?)';
            $term = '%' . $search . '%';
            for ($i = 0; $i < 10; $i++) {
                $params[] = $term;
            }
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY pm.id ORDER BY ta.is_active DESC, ta.id DESC, p.nama ASC, mk.kode ASC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
            if ($offset !== null) {
                $sql .= ' OFFSET ' . (int)$offset;
            }
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['tim_ids'] = !empty($row['tim_ids_str'])
                ? array_map('intval', explode(',', $row['tim_ids_str']))
                : [];
        }
        unset($row);
        return $rows;
    }

    public function getById(int $id): ?array
    {
        $stmt=$this->db->prepare("SELECT pm.*,k.prodi_id FROM mata_kuliah_pengampu pm JOIN mata_kuliah mk ON mk.id=pm.mata_kuliah_id JOIN kurikulum k ON k.id=mk.kurikulum_id WHERE pm.id=?");
        $stmt->execute([$id]);$row=$stmt->fetch(PDO::FETCH_ASSOC);if(!$row)return null;
        $tim=$this->db->prepare('SELECT dosen_id FROM mata_kuliah_pengampu_tim WHERE pengampu_id=? ORDER BY dosen_id');$tim->execute([$id]);
        $row['tim_ids']=array_map('intval',$tim->fetchAll(PDO::FETCH_COLUMN));return $row;
    }

    public function findActive(int $mkId, int $taId): ?array
    {
        $stmt=$this->db->prepare("SELECT pm.*,k.prodi_id,u.nama_lengkap pjmk_nama FROM mata_kuliah_pengampu pm JOIN mata_kuliah mk ON mk.id=pm.mata_kuliah_id JOIN kurikulum k ON k.id=mk.kurikulum_id JOIN users u ON u.id=pm.pjmk_user_id WHERE pm.mata_kuliah_id=? AND pm.tahun_akademik_id=? AND pm.status='ACTIVE'");
        $stmt->execute([$mkId,$taId]);$row=$stmt->fetch(PDO::FETCH_ASSOC);if(!$row)return null;
        $tim=$this->db->prepare('SELECT pt.dosen_id,u.nama_lengkap FROM mata_kuliah_pengampu_tim pt JOIN users u ON u.id=pt.dosen_id WHERE pt.pengampu_id=? ORDER BY u.nama_lengkap');$tim->execute([$row['id']]);$row['tim']=$tim->fetchAll(PDO::FETCH_ASSOC);return $row;
    }

    public function getRpsAssignmentsForLecturer(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT pm.id,pm.mata_kuliah_id,pm.tahun_akademik_id,pm.status,
                   mk.kode mk_kode,mk.nama mk_nama,mk.semester,mk.sks_teori,mk.sks_praktek,
                   k.nama kurikulum_nama,p.kode prodi_kode,p.nama prodi_nama,
                   ta.nama tahun_akademik,ta.is_active,
                   r.id rps_id,r.versi,
                   (SELECT COUNT(*) FROM rps_pertemuan rp WHERE rp.mata_kuliah_id=mk.id) total_pertemuan
            FROM mata_kuliah_pengampu pm
            JOIN mata_kuliah mk ON mk.id=pm.mata_kuliah_id
            JOIN kurikulum k ON k.id=mk.kurikulum_id
            JOIN program_studi p ON p.id=k.prodi_id
            JOIN tahun_akademik ta ON ta.id=pm.tahun_akademik_id
            LEFT JOIN rps r ON r.mata_kuliah_id=mk.id
            WHERE pm.pjmk_user_id=? AND pm.status='ACTIVE'
            ORDER BY ta.is_active DESC,ta.id DESC,p.nama,mk.kode");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForLecturer(int $mkId, int $taId, int $userId): ?array
    {
        $stmt=$this->db->prepare("SELECT pm.*,ta.nama tahun_akademik,ta.is_active,k.prodi_id,
                   u.nama_lengkap pjmk_nama
            FROM mata_kuliah_pengampu pm
            JOIN tahun_akademik ta ON ta.id=pm.tahun_akademik_id
            JOIN mata_kuliah mk ON mk.id=pm.mata_kuliah_id
            JOIN kurikulum k ON k.id=mk.kurikulum_id
            JOIN users u ON u.id=pm.pjmk_user_id
            WHERE pm.mata_kuliah_id=? AND pm.tahun_akademik_id=?
              AND pm.pjmk_user_id=? AND pm.status='ACTIVE' LIMIT 1");
        $stmt->execute([$mkId,$taId,$userId]);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function save(array $data): int
    {
        $mkId=(int)$data['mata_kuliah_id'];$taId=(int)$data['tahun_akademik_id'];$pjmk=(int)$data['pjmk_user_id'];
        $tim=array_values(array_unique(array_filter(array_map('intval',$data['tim_ids']??[]),fn($id)=>$id>0&&$id!==$pjmk)));
        $this->validateActiveLecturers($mkId,array_merge([$pjmk],$tim));
        $this->db->beginTransaction();
        try {
            $stmt=$this->db->prepare("INSERT INTO mata_kuliah_pengampu(mata_kuliah_id,tahun_akademik_id,pjmk_user_id,status) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE pjmk_user_id=VALUES(pjmk_user_id),status=VALUES(status),id=LAST_INSERT_ID(id)");
            $stmt->execute([$mkId,$taId,$pjmk,$data['status']??'ACTIVE']);$id=(int)$this->db->lastInsertId();
            $this->db->prepare('DELETE FROM mata_kuliah_pengampu_tim WHERE pengampu_id=?')->execute([$id]);
            $insert=$this->db->prepare('INSERT INTO mata_kuliah_pengampu_tim(pengampu_id,dosen_id) VALUES(?,?)');foreach($tim as $dosenId)$insert->execute([$id,$dosenId]);
            $this->syncRps($id);$this->db->commit();return $id;
        } catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function syncClasses(int $id): int
    {
        $assignment=$this->getById($id);if(!$assignment)throw new RuntimeException('Penugasan tidak ditemukan.');
        $stmt=$this->db->prepare('SELECT id FROM kelas WHERE mata_kuliah_id=? AND tahun_akademik_id=?');$stmt->execute([$assignment['mata_kuliah_id'],$assignment['tahun_akademik_id']]);$classIds=array_map('intval',$stmt->fetchAll(PDO::FETCH_COLUMN));
        $this->db->beginTransaction();
        try {$update=$this->db->prepare('UPDATE kelas SET dosen_id=? WHERE id=?');$delete=$this->db->prepare('DELETE FROM kelas_tim_teaching WHERE kelas_id=?');$insert=$this->db->prepare('INSERT IGNORE INTO kelas_tim_teaching(kelas_id,dosen_id) VALUES(?,?)');foreach($classIds as $classId){$update->execute([$assignment['pjmk_user_id'],$classId]);$delete->execute([$classId]);foreach($assignment['tim_ids'] as $dosenId)$insert->execute([$classId,$dosenId]);}$this->syncRps($id);$this->db->commit();return count($classIds);}catch(Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function applyToClass(int $classId, int $mkId, int $taId): bool
    {
        $assignment=$this->findActive($mkId,$taId);if(!$assignment)return false;
        $this->db->prepare('UPDATE kelas SET dosen_id=? WHERE id=?')->execute([$assignment['pjmk_user_id'],$classId]);
        $this->db->prepare('DELETE FROM kelas_tim_teaching WHERE kelas_id=?')->execute([$classId]);
        $insert=$this->db->prepare('INSERT IGNORE INTO kelas_tim_teaching(kelas_id,dosen_id) VALUES(?,?)');foreach($assignment['tim'] as $member)$insert->execute([$classId,$member['dosen_id']]);return true;
    }

    private function syncRps(int $id): void
    {
        $assignment=$this->getById($id);if(!$assignment)return;
        $pjmk=$this->db->prepare('SELECT nama_lengkap FROM users WHERE id=?');$pjmk->execute([$assignment['pjmk_user_id']]);$pjmkName=(string)$pjmk->fetchColumn();
        $tim=[];$lines=['1. '.$pjmkName.' (PJMK)'];$q=$this->db->prepare('SELECT u.id,u.nama_lengkap FROM mata_kuliah_pengampu_tim pt JOIN users u ON u.id=pt.dosen_id WHERE pt.pengampu_id=? ORDER BY u.nama_lengkap');$q->execute([$id]);foreach($q->fetchAll(PDO::FETCH_ASSOC) as $i=>$member){$tim[]=['dosen_id'=>(int)$member['id'],'nama'=>$member['nama_lengkap'],'peran'=>'Anggota Tim Teaching'];$lines[]=($i+2).'. '.$member['nama_lengkap'].' (Anggota Tim Teaching)';}
        $this->db->prepare('UPDATE rps SET dosen_pengampu=?,tim_teaching_json=? WHERE mata_kuliah_id=?')->execute([implode("\n",$lines),$tim?json_encode($tim,JSON_UNESCAPED_UNICODE):null,$assignment['mata_kuliah_id']]);
    }

    private function validateActiveLecturers(int $mkId, array $dosenIds): void
    {
        $stmt=$this->db->prepare('SELECT k.prodi_id FROM mata_kuliah mk JOIN kurikulum k ON k.id=mk.kurikulum_id WHERE mk.id=?');
        $stmt->execute([$mkId]);
        $prodi=(int)$stmt->fetchColumn();
        if(!$prodi) throw new InvalidArgumentException('Mata kuliah tidak ditemukan.');

        $dosenIds = array_values(array_filter(array_map('intval', $dosenIds)));
        if(empty($dosenIds)) return;

        $in = implode(',', array_fill(0, count($dosenIds), '?'));
        $check = $this->db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id IN ($in) AND r.name IN ('Dosen','Kaprodi')");
        $check->execute($dosenIds);
        if((int)$check->fetchColumn() !== count($dosenIds)) {
            throw new InvalidArgumentException('PJMK atau anggota tim teaching harus berupa dosen aktif.');
        }
    }
}
