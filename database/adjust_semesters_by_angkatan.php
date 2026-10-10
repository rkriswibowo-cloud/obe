<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Memulai penyesuaian data semester perkuliahan sesuai angkatan..." . PHP_EOL;

// 1. Buat tabel backup untuk keamanan data
$db->exec("DROP TABLE IF EXISTS backup_unrealized_semesters_km");
$db->exec("
    CREATE TABLE backup_unrealized_semesters_km AS 
    SELECT km.* FROM kelas_mahasiswa km
    JOIN users u ON km.mahasiswa_id = u.id
    JOIN kelas k ON km.kelas_id = k.id
    WHERE (
        (u.username LIKE '23%' AND k.tahun_akademik_id > 7) OR
        (u.username LIKE '24%' AND k.tahun_akademik_id > 5) OR
        (u.username LIKE '25%' AND k.tahun_akademik_id > 3)
    )
");
echo "- Backup kelas_mahasiswa selesai.\n";

$db->exec("DROP TABLE IF EXISTS backup_unrealized_semesters_an");
$db->exec("
    CREATE TABLE backup_unrealized_semesters_an AS 
    SELECT an.* FROM asesmen_nilai an
    JOIN users u ON an.mahasiswa_id = u.id
    JOIN kelas k ON an.kelas_id = k.id
    WHERE (
        (u.username LIKE '23%' AND k.tahun_akademik_id > 7) OR
        (u.username LIKE '24%' AND k.tahun_akademik_id > 5) OR
        (u.username LIKE '25%' AND k.tahun_akademik_id > 3)
    )
");
echo "- Backup asesmen_nilai selesai.\n";

// 2. Hapus nilai asesmen untuk semester yang belum ditempuh
$deletedAn = $db->exec("
    DELETE an FROM asesmen_nilai an
    JOIN users u ON an.mahasiswa_id = u.id
    JOIN kelas k ON an.kelas_id = k.id
    WHERE (
        (u.username LIKE '23%' AND k.tahun_akademik_id > 7) OR
        (u.username LIKE '24%' AND k.tahun_akademik_id > 5) OR
        (u.username LIKE '25%' AND k.tahun_akademik_id > 3)
    )
");
echo "- Dihapus $deletedAn baris asesmen_nilai pada semester yang belum ditempuh.\n";

// 3. Hapus pendaftaran kelas untuk semester yang belum ditempuh
$deletedKm = $db->exec("
    DELETE km FROM kelas_mahasiswa km
    JOIN users u ON km.mahasiswa_id = u.id
    JOIN kelas k ON km.kelas_id = k.id
    WHERE (
        (u.username LIKE '23%' AND k.tahun_akademik_id > 7) OR
        (u.username LIKE '24%' AND k.tahun_akademik_id > 5) OR
        (u.username LIKE '25%' AND k.tahun_akademik_id > 3)
    )
");
echo "- Dihapus $deletedKm baris kelas_mahasiswa pada semester yang belum ditempuh.\n";

echo PHP_EOL . "=== VERIFIKASI HASIL PENYESUAIAN ===" . PHP_EOL;
$verif = $db->query("
    SELECT SUBSTRING(u.username, 1, 2) as angk,
           MIN(k.tahun_akademik_id) as min_sem,
           MAX(k.tahun_akademik_id) as max_sem,
           COUNT(DISTINCT k.tahun_akademik_id) as total_semesters,
           COUNT(DISTINCT u.id) as total_mhs
    FROM users u
    JOIN kelas_mahasiswa km ON u.id = km.mahasiswa_id
    JOIN kelas k ON km.kelas_id = k.id
    WHERE u.role_id = 5
    GROUP BY angk
    ORDER BY angk ASC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($verif as $v) {
    echo "Angkatan 20{$v['angk']}: Semester {$v['min_sem']} s/d {$v['max_sem']} ({$v['total_semesters']} semester aktif untuk {$v['total_mhs']} mahasiswa)\n";
}
echo PHP_EOL . "Selesai!" . PHP_EOL;
