<?php
require_once __DIR__ . '/../../config/database.php';

class RplModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getSettings() {
        return $this->db->query('SELECT * FROM rpl_pengaturan WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    }
    public function saveSettings($data) {
        $s=$this->db->prepare('UPDATE rpl_pengaturan SET minimal_sks_3_semester=?, minimal_sks_4_semester=?, maksimal_sks_per_semester=?, minimal_persentase_ekuivalensi=? WHERE id=1');
        return $s->execute([(int)$data['minimal_sks_3_semester'],(int)$data['minimal_sks_4_semester'],(int)$data['maksimal_sks_per_semester'],(float)$data['minimal_persentase_ekuivalensi']]);
    }
    public function getEligibleCourses($kurikulumId=null) {
        $sql='SELECT mk.*, k.nama kurikulum_nama, COALESCE(rs.is_active,0) rpl_active, rs.minimal_nilai_angka, rs.minimal_persentase_ekuivalensi, rs.catatan FROM mata_kuliah mk JOIN kurikulum k ON k.id=mk.kurikulum_id LEFT JOIN rpl_mata_kuliah_setting rs ON rs.mata_kuliah_id=mk.id WHERE mk.status="ACTIVE"';
        $p=[]; if($kurikulumId){$sql.=' AND mk.kurikulum_id=?';$p[]=(int)$kurikulumId;} $sql.=' ORDER BY k.tahun_mulai DESC,mk.semester,mk.kode';
        $s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    public function saveCourseSettings($ids,$min,$similarity) {
        $this->db->beginTransaction();
        try { $this->db->exec('UPDATE rpl_mata_kuliah_setting SET is_active=0');
            $s=$this->db->prepare('INSERT INTO rpl_mata_kuliah_setting(mata_kuliah_id,minimal_nilai_angka,minimal_persentase_ekuivalensi,is_active) VALUES(?,?,?,1) ON DUPLICATE KEY UPDATE minimal_nilai_angka=VALUES(minimal_nilai_angka),minimal_persentase_ekuivalensi=VALUES(minimal_persentase_ekuivalensi),is_active=1');
            foreach($ids as $id)$s->execute([(int)$id,(float)$min,(float)$similarity]); $this->db->commit();
        } catch(Throwable $e){$this->db->rollBack();throw $e;}
    }
    public function getStudents() {
        return $this->db->query("SELECT u.id,u.username,u.nama_lengkap,p.nama prodi_nama,rm.id rpl_id FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN program_studi p ON p.id=u.prodi_id LEFT JOIN rpl_mahasiswa rm ON rm.mahasiswa_id=u.id WHERE r.name='Mahasiswa' ORDER BY u.nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function createProfile($mahasiswaId,$kurikulumId) {
        $s=$this->db->prepare('INSERT INTO rpl_mahasiswa(mahasiswa_id,kurikulum_id) VALUES(?,?) ON DUPLICATE KEY UPDATE kurikulum_id=VALUES(kurikulum_id),status="AKTIF"');
        $s->execute([(int)$mahasiswaId,(int)$kurikulumId]); return $this->getByStudent($mahasiswaId);
    }
    public function getProfiles() {
        return $this->db->query("SELECT rm.*,u.username nim,u.nama_lengkap,k.nama kurikulum_nama,COALESCE(SUM(CASE WHEN rk.status='DISETUJUI' THEN rk.sks_diakui ELSE 0 END),0) sks_diakui,COUNT(CASE WHEN rk.status='DIAJUKAN' THEN 1 END) menunggu FROM rpl_mahasiswa rm JOIN users u ON u.id=rm.mahasiswa_id JOIN kurikulum k ON k.id=rm.kurikulum_id LEFT JOIN rpl_konversi rk ON rk.rpl_mahasiswa_id=rm.id GROUP BY rm.id ORDER BY u.nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByStudent($studentId) {
        $s=$this->db->prepare('SELECT rm.*,u.username nim,u.nama_lengkap,k.nama kurikulum_nama,k.kode kurikulum_kode FROM rpl_mahasiswa rm JOIN users u ON u.id=rm.mahasiswa_id JOIN kurikulum k ON k.id=rm.kurikulum_id WHERE rm.mahasiswa_id=?');$s->execute([(int)$studentId]);return $s->fetch(PDO::FETCH_ASSOC);
    }
    public function getProfile($id) {
        $s=$this->db->prepare('SELECT rm.*,u.username nim,u.nama_lengkap,k.nama kurikulum_nama,k.kode kurikulum_kode FROM rpl_mahasiswa rm JOIN users u ON u.id=rm.mahasiswa_id JOIN kurikulum k ON k.id=rm.kurikulum_id WHERE rm.id=?');$s->execute([(int)$id]);return $s->fetch(PDO::FETCH_ASSOC);
    }
    public function getConversions($rplId) {
        $s=$this->db->prepare('SELECT rk.*,mk.kode mk_kode,mk.nama mk_nama,(mk.sks_teori+mk.sks_praktek) mk_sks,mk.semester FROM rpl_konversi rk JOIN mata_kuliah mk ON mk.id=rk.mata_kuliah_id WHERE rk.rpl_mahasiswa_id=? ORDER BY mk.semester,mk.kode');$s->execute([(int)$rplId]);return $s->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProfileIdByConversion($conversionId) {
        $stmt=$this->db->prepare('SELECT rpl_mahasiswa_id FROM rpl_konversi WHERE id=?');$stmt->execute([(int)$conversionId]);return (int)$stmt->fetchColumn();
    }
    public function addConversion($rplId,$d,$userId) {
        $mk=$this->db->prepare('SELECT mk.*,rs.minimal_nilai_angka,rs.minimal_persentase_ekuivalensi FROM mata_kuliah mk JOIN rpl_mata_kuliah_setting rs ON rs.mata_kuliah_id=mk.id AND rs.is_active=1 JOIN rpl_mahasiswa rm ON rm.kurikulum_id=mk.kurikulum_id WHERE rm.id=? AND mk.id=?');$mk->execute([(int)$rplId,(int)$d['mata_kuliah_id']]);$target=$mk->fetch(PDO::FETCH_ASSOC);
        if(!$target) throw new RuntimeException('Mata kuliah tidak diizinkan untuk RPL atau tidak sesuai kurikulum.');
        if((float)$d['nilai_angka'] < (float)$target['minimal_nilai_angka']) throw new RuntimeException('Nilai konversi di bawah batas minimum MK.');
        if((float)$d['persentase_ekuivalensi'] < (float)$target['minimal_persentase_ekuivalensi']) throw new RuntimeException('Persentase ekuivalensi di bawah batas minimum MK.');
        $grade=$this->grade((float)$d['nilai_angka']); $sks=(int)$target['sks_teori']+(int)$target['sks_praktek'];
        $s=$this->db->prepare('INSERT INTO rpl_konversi(rpl_mahasiswa_id,mata_kuliah_id,kode_asal,nama_asal,institusi_asal,sks_asal,nilai_asal,nilai_angka,nilai_huruf,bobot_mutu,persentase_ekuivalensi,sks_diakui,dasar_rekognisi,catatan_pengaju,dibuat_oleh,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"DIAJUKAN")');
        return $s->execute([(int)$rplId,(int)$d['mata_kuliah_id'],$d['kode_asal'],$d['nama_asal'],$d['institusi_asal'],(int)$d['sks_asal'],$d['nilai_asal'],(float)$d['nilai_angka'],$grade[0],$grade[1],(float)$d['persentase_ekuivalensi'],$sks,$d['dasar_rekognisi'],$d['catatan_pengaju'],$userId]);
    }
    public function updateConversion($id,$rplId,$d) {
        $q=$this->db->prepare('SELECT rk.mata_kuliah_id,rs.minimal_nilai_angka,rs.minimal_persentase_ekuivalensi FROM rpl_konversi rk JOIN rpl_mata_kuliah_setting rs ON rs.mata_kuliah_id=rk.mata_kuliah_id WHERE rk.id=? AND rk.rpl_mahasiswa_id=?');
        $q->execute([(int)$id,(int)$rplId]);$current=$q->fetch(PDO::FETCH_ASSOC);
        if(!$current)throw new RuntimeException('Data konversi tidak ditemukan.');
        $namaAsal=trim($d['nama_asal']??'');$nilai=(float)($d['nilai_angka']??0);$ekuivalensi=(float)($d['persentase_ekuivalensi']??0);$sksAsal=(int)($d['sks_asal']??0);
        if($namaAsal==='')throw new InvalidArgumentException('Nama pembelajaran asal wajib diisi.');
        if($nilai<0||$nilai>100||$ekuivalensi<0||$ekuivalensi>100||$sksAsal<0)throw new InvalidArgumentException('Nilai, ekuivalensi, atau SKS asal tidak valid.');
        if($nilai<(float)$current['minimal_nilai_angka'])throw new RuntimeException('Nilai konversi di bawah batas minimum MK.');
        if($ekuivalensi<(float)$current['minimal_persentase_ekuivalensi'])throw new RuntimeException('Persentase ekuivalensi di bawah batas minimum MK.');
        $allowed=['PENDIDIKAN_FORMAL','PENGALAMAN_KERJA','PELATIHAN','SERTIFIKASI','PORTOFOLIO'];
        $dasar=$d['dasar_rekognisi']??'PENDIDIKAN_FORMAL';if(!in_array($dasar,$allowed,true))throw new InvalidArgumentException('Dasar rekognisi tidak valid.');
        $grade=$this->grade($nilai);
        $s=$this->db->prepare('UPDATE rpl_konversi SET kode_asal=?,nama_asal=?,institusi_asal=?,sks_asal=?,nilai_asal=?,nilai_angka=?,nilai_huruf=?,bobot_mutu=?,persentase_ekuivalensi=?,dasar_rekognisi=?,catatan_pengaju=? WHERE id=? AND rpl_mahasiswa_id=?');
        $s->execute([trim($d['kode_asal']??''),$namaAsal,trim($d['institusi_asal']??''),$sksAsal,trim($d['nilai_asal']??''),$nilai,$grade[0],$grade[1],$ekuivalensi,$dasar,trim($d['catatan_pengaju']??''),(int)$id,(int)$rplId]);
        $this->rebuildPlan($rplId);return $s->rowCount();
    }
    public function deleteConversion($id,$rplId) {
        $s=$this->db->prepare('DELETE FROM rpl_konversi WHERE id=? AND rpl_mahasiswa_id=?');$s->execute([(int)$id,(int)$rplId]);
        if(!$s->rowCount())throw new RuntimeException('Data konversi tidak ditemukan.');
        $this->rebuildPlan($rplId);return true;
    }
    public function decide($id,$status,$note,$userId) {
        if(!in_array($status,['DISETUJUI','DITOLAK'],true))throw new InvalidArgumentException('Keputusan tidak valid.');
        $s=$this->db->prepare("UPDATE rpl_konversi SET status=?,catatan_kaprodi=?,diputuskan_oleh=?,diputuskan_at=NOW() WHERE id=? AND status='DIAJUKAN'");$s->execute([$status,$note,$userId,(int)$id]);
        $q=$this->db->prepare('SELECT rpl_mahasiswa_id FROM rpl_konversi WHERE id=?');$q->execute([(int)$id]);$rplId=(int)$q->fetchColumn(); if($rplId)$this->rebuildPlan($rplId); return $rplId;
    }
    public function rebuildPlan($rplId) {
        $profile=$this->getProfile($rplId);$cfg=$this->getSettings();
        $q=$this->db->prepare("SELECT COALESCE(SUM(sks_diakui),0) FROM rpl_konversi WHERE rpl_mahasiswa_id=? AND status='DISETUJUI'");$q->execute([$rplId]);$recognized=(int)$q->fetchColumn();
        $q=$this->db->prepare("SELECT COALESCE(SUM(sks_teori+sks_praktek),0) FROM mata_kuliah WHERE kurikulum_id=? AND status='ACTIVE'");$q->execute([$profile['kurikulum_id']]);$total=(int)$q->fetchColumn();$remaining=max(0,$total-$recognized);
        $scheme=null;
        if($recognized >= (int)$cfg['minimal_sks_3_semester'] && $remaining <= 3*(int)$cfg['maksimal_sks_per_semester']) {
            $scheme=3;
        } elseif($recognized >= (int)$cfg['minimal_sks_4_semester'] && $remaining <= 4*(int)$cfg['maksimal_sks_per_semester']) {
            $scheme=4;
        }
        $this->db->prepare('UPDATE rpl_mahasiswa SET skema_semester=? WHERE id=?')->execute([$scheme,$rplId]);
        $this->db->prepare('DELETE FROM rpl_rencana_studi WHERE rpl_mahasiswa_id=?')->execute([$rplId]);
        if($scheme===null) return;
        $q=$this->db->prepare("SELECT mk.id,mk.kode,mk.nama,mk.semester,(mk.sks_teori+mk.sks_praktek) sks FROM mata_kuliah mk WHERE mk.kurikulum_id=? AND mk.status='ACTIVE' AND NOT EXISTS(SELECT 1 FROM rpl_konversi rk WHERE rk.rpl_mahasiswa_id=? AND rk.mata_kuliah_id=mk.id AND rk.status='DISETUJUI') ORDER BY mk.semester,mk.kode");$q->execute([$profile['kurikulum_id'],$rplId]);
        $courses=$q->fetchAll(PDO::FETCH_ASSOC);
        $finalCourses=[];$regularCourses=[];
        foreach($courses as $mk){
            $name=strtolower(trim($mk['nama']));
            if(str_contains($name,'proposal skripsi') || $name==='skripsi')$finalCourses[]=$mk;
            else $regularCourses[]=$mk;
        }
        $maxLoad=(int)$cfg['maksimal_sks_per_semester'];
        $regularSks=array_sum(array_column($regularCourses,'sks'));
        $regularSemesterCount=max(1,$scheme-1);
        if($regularSks>$regularSemesterCount*$maxLoad)$regularSemesterCount=$scheme;
        $loads=array_fill(1,$scheme,0);$ins=$this->db->prepare('INSERT INTO rpl_rencana_studi(rpl_mahasiswa_id,mata_kuliah_id,semester_rpl) VALUES(?,?,?)');
        $unassigned=$regularCourses;
        for($semester=1;$semester<=$regularSemesterCount;$semester++){
            if($semester===$regularSemesterCount){$selected=array_keys($unassigned);}
            else{
                // Beban dibuat menurun: 20 SKS, 16 SKS, 12 SKS, dst.
                $target=min($maxLoad,max(1,20-(($semester-1)*4)));
                $subsets=[0=>[]];
                foreach($unassigned as $idx=>$mk){
                    $snapshot=$subsets;
                    foreach($snapshot as $sum=>$indices){
                        $next=$sum+(int)$mk['sks'];
                        if($next<=$target&&!isset($subsets[$next]))$subsets[$next]=array_merge($indices,[$idx]);
                    }
                }
                $best=max(array_keys($subsets));$selected=$subsets[$best];
            }
            foreach($selected as $idx){$mk=$unassigned[$idx];$ins->execute([$rplId,$mk['id'],$semester]);$loads[$semester]+=(int)$mk['sks'];unset($unassigned[$idx]);}
        }
        foreach($finalCourses as $mk){$ins->execute([$rplId,$mk['id'],$scheme]);$loads[$scheme]+=(int)$mk['sks'];}
    }
    public function getPlan($rplId) {$s=$this->db->prepare('SELECT rr.*,mk.kode,mk.nama,(mk.sks_teori+mk.sks_praktek) sks,mk.semester semester_kurikulum FROM rpl_rencana_studi rr JOIN mata_kuliah mk ON mk.id=rr.mata_kuliah_id WHERE rr.rpl_mahasiswa_id=? ORDER BY rr.semester_rpl,mk.semester,mk.kode');$s->execute([(int)$rplId]);return $s->fetchAll(PDO::FETCH_ASSOC);}
    public function summary($rplId){$p=$this->getProfile($rplId);$q=$this->db->prepare("SELECT COALESCE(SUM(sks_teori+sks_praktek),0) FROM mata_kuliah WHERE kurikulum_id=? AND status='ACTIVE'");$q->execute([$p['kurikulum_id']]);$total=(int)$q->fetchColumn();$q=$this->db->prepare("SELECT COALESCE(SUM(sks_diakui),0),COALESCE(SUM(sks_diakui*bobot_mutu),0) FROM rpl_konversi WHERE rpl_mahasiswa_id=? AND status='DISETUJUI'");$q->execute([$rplId]);$x=$q->fetch(PDO::FETCH_NUM);return ['total_sks'=>$total,'sks_diakui'=>(int)$x[0],'sks_sisa'=>max(0,$total-(int)$x[0]),'mutu_rpl'=>(float)$x[1]];}
    private function grade($n){
        if($n>=76)return['A',4.0,'Istimewa'];
        if($n>=70)return['AB',3.5,'Baik Sekali'];
        if($n>=65)return['B',3.0,'Baik'];
        if($n>=60)return['BC',2.5,'Cukup Baik'];
        if($n>=50)return['C',2.0,'Cukup'];
        if($n>=40)return['D',1.0,'Kurang'];
        return['E',0.0,'Kurang Sekali'];
    }

    public function setSemesterScheme($rplId, $scheme) {
        $scheme = (int)$scheme;
        if (!in_array($scheme, [3, 4], true)) {
            throw new InvalidArgumentException('Skema semester hanya boleh 3 atau 4 semester.');
        }
        $profile = $this->getProfile($rplId);
        if (!$profile) throw new RuntimeException('Profil RPL tidak ditemukan.');

        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE rpl_mahasiswa SET skema_semester=? WHERE id=?')->execute([$scheme, (int)$rplId]);
            // Jika ada MK yang berada di semester > skema baru, pindahkan ke semester terakhir skema baru
            $this->db->prepare('UPDATE rpl_rencana_studi SET semester_rpl=? WHERE rpl_mahasiswa_id=? AND semester_rpl > ?')->execute([$scheme, (int)$rplId, $scheme]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function movePlanCourse($rplId, $planId, $targetSemester) {
        $rplId = (int)$rplId;
        $planId = (int)$planId;
        $targetSemester = (int)$targetSemester;
        $profile = $this->getProfile($rplId);
        if (!$profile) throw new RuntimeException('Profil RPL tidak ditemukan.');
        $maxSemester = (int)($profile['skema_semester'] ?? 4);
        if ($maxSemester < 1) $maxSemester = 4;
        if ($targetSemester < 1 || $targetSemester > $maxSemester) {
            throw new InvalidArgumentException("Semester tujuan harus antara 1 sampai $maxSemester.");
        }

        $stmt = $this->db->prepare('SELECT id FROM rpl_rencana_studi WHERE id=? AND rpl_mahasiswa_id=?');
        $stmt->execute([$planId, $rplId]);
        if (!$stmt->fetch()) {
            throw new RuntimeException('Mata kuliah dalam rencana studi tidak ditemukan.');
        }

        $upd = $this->db->prepare('UPDATE rpl_rencana_studi SET semester_rpl=? WHERE id=? AND rpl_mahasiswa_id=?');
        return $upd->execute([$targetSemester, $planId, $rplId]);
    }
}

