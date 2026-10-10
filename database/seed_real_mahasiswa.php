<?php
/**
 * database/seed_real_mahasiswa.php
 *
 * Mengganti semua data mahasiswa dummy sebelumnya dengan data mahasiswa resmi
 * angkatan 2022 - 2025 sesuai permintaan:
 * - 2022 (1 mahasiswa)
 * - 2023 (8 mahasiswa)
 * - 2024 (4 mahasiswa)
 * - 2025 (16 mahasiswa)
 * Total: 29 mahasiswa.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/AturanKurikulumModel.php';

$db = Database::getInstance();
$aturanModel = new AturanKurikulumModel();

$dataMahasiswa = [
    2022 => [
        ['nim' => '22020900006', 'nama' => 'AHMAD JAIZ NAINI'],
    ],
    2023 => [
        ['nim' => '23020900001', 'nama' => 'LUSI FITRIA YUNANI'],
        ['nim' => '23020900002', 'nama' => 'SAYYIDAH HAJAR FAIQOTUL MUHIMMAH'],
        ['nim' => '23020900003', 'nama' => 'MUHAMMAD ATALLAH KANANG PUTERA'],
        ['nim' => '23020900004', 'nama' => 'KASIONO'],
        ['nim' => '23020900005', 'nama' => 'FAJAR ROMADHONI'],
        ['nim' => '23020900006', 'nama' => 'FRANSISKUS ALFRIADI NAMA'],
        ['nim' => '23020900007', 'nama' => 'IMAM AHMAD'],
        ['nim' => '23020900008', 'nama' => 'MISHBAHUL FUAD'],
    ],
    2024 => [
        ['nim' => '24020900001', 'nama' => 'MOHAMMAD ALIF RISKYANSAH'],
        ['nim' => '24020900002', 'nama' => 'FIRDAUS KAMILULLAH SUHADA'],
        ['nim' => '24020900003', 'nama' => 'ABD. WAHAB LEBEHARIA'],
        ['nim' => '24020900004', 'nama' => 'WIRA ADIPRATAMA'],
    ],
    2025 => [
        ['nim' => '25020900004', 'nama' => 'MOCH. LUKMAN HAKIM'],
        ['nim' => '25020900005', 'nama' => 'MUHAMMAD IRFAKNI HABIB SHOLAHUDDIN'],
        ['nim' => '25020900006', 'nama' => 'DEKITA ASIYAH PUTRI'],
        ['nim' => '25020900007', 'nama' => 'ALDI'],
        ['nim' => '25020900008', 'nama' => 'TUTA PATRISIA'],
        ['nim' => '25020900009', 'nama' => 'VINA KRISYANTI'],
        ['nim' => '25020900010', 'nama' => 'MAWESIH'],
        ['nim' => '25020900011', 'nama' => 'RISWANDI'],
        ['nim' => '25020900012', 'nama' => 'MANZILATUR ROHMAH'],
        ['nim' => '25020900013', 'nama' => 'LAILATU AFINA'],
        ['nim' => '25020900014', 'nama' => 'IZA MAR ATUL IFA'],
        ['nim' => '25020900015', 'nama' => 'SIRLY FATATI QONITA'],
        ['nim' => '25020900016', 'nama' => 'ISMAYANI IZZETUS SALAMAH'],
        ['nim' => '25020900017', 'nama' => 'VITA NUR RODHIYAH'],
        ['nim' => '25020900018', 'nama' => "M. ZAKARIYA MUA'RIF"],
        ['nim' => '25020900019', 'nama' => 'NURUL HIDAYAH'],
    ],
];

// Bersihkan spasi non-breaking jika ada
foreach ($dataMahasiswa as $year => &$list) {
    foreach ($list as &$m) {
        $m['nim'] = trim(preg_replace('/\s+/', '', str_replace("\xc2\xa0", '', $m['nim'])));
        $m['nama'] = trim(preg_replace('/\s+/', ' ', str_replace("\xc2\xa0", ' ', $m['nama'])));
    }
}
unset($list, $m);

$roleId = (int)$db->query("SELECT id FROM roles WHERE name = 'Mahasiswa' LIMIT 1")->fetchColumn();
if (!$roleId) {
    throw new RuntimeException('Role Mahasiswa tidak ditemukan.');
}
$prodiId = (int)$db->query('SELECT id FROM program_studi ORDER BY id LIMIT 1')->fetchColumn() ?: 1;

echo "=== MEMULAI SINKRONISASI DATA MAHASISWA 2022-2025 ===\n";

// 1. Ambil ID mahasiswa lama
$oldMhsIds = $db->query("
    SELECT u.id FROM users u
    WHERE u.role_id = {$roleId}
")->fetchAll(PDO::FETCH_COLUMN);

echo "Mahasiswa lama ditemukan: " . count($oldMhsIds) . "\n";

// 2. Backup tables
$db->exec('CREATE TABLE IF NOT EXISTS backup_all_old_mahasiswa LIKE users');
$db->exec('CREATE TABLE IF NOT EXISTS backup_all_old_kelas_mahasiswa LIKE kelas_mahasiswa');
$db->exec('CREATE TABLE IF NOT EXISTS backup_all_old_asesmen_nilai LIKE asesmen_nilai');

try {
    $db->beginTransaction();

    if (!empty($oldMhsIds)) {
        $inOld = implode(',', array_map('intval', $oldMhsIds));
        $db->exec("INSERT IGNORE INTO backup_all_old_mahasiswa SELECT * FROM users WHERE id IN ({$inOld})");
        $db->exec("INSERT IGNORE INTO backup_all_old_kelas_mahasiswa SELECT * FROM kelas_mahasiswa WHERE mahasiswa_id IN ({$inOld})");
        $db->exec("INSERT IGNORE INTO backup_all_old_asesmen_nilai SELECT * FROM asesmen_nilai WHERE mahasiswa_id IN ({$inOld})");

        // Hapus nilai dan pendaftaran kelas mahasiswa lama
        $db->exec("DELETE FROM asesmen_nilai WHERE mahasiswa_id IN ({$inOld})");
        $db->exec("DELETE FROM kelas_mahasiswa WHERE mahasiswa_id IN ({$inOld})");

        // Hapus akun mahasiswa lama
        $db->exec("DELETE FROM users WHERE id IN ({$inOld})");
        echo "Data mahasiswa lama & relasinya berhasil dibersihkan.\n";
    }

    // 3. Masukkan mahasiswa baru
    $insertUserStmt = $db->prepare("
        INSERT INTO users (username, password, nama_lengkap, prodi_id, role_id)
        VALUES (:username, :password, :nama_lengkap, :prodi_id, :role_id)
    ");

    $defaultPasswordHash = password_hash('password', PASSWORD_DEFAULT);
    $newStudentsMap = []; // angkatan => [ [id, nim, nama, index], ... ]

    $totalInserted = 0;
    foreach ($dataMahasiswa as $year => $students) {
        $newStudentsMap[$year] = [];
        foreach ($students as $idx => $s) {
            $insertUserStmt->execute([
                'username' => $s['nim'],
                'password' => $defaultPasswordHash,
                'nama_lengkap' => $s['nama'],
                'prodi_id' => $prodiId,
                'role_id' => $roleId
            ]);
            $newId = (int)$db->lastInsertId();
            $newStudentsMap[$year][] = [
                'id' => $newId,
                'nim' => $s['nim'],
                'nama' => $s['nama'],
                'index' => $idx
            ];
            $totalInserted++;
        }
        echo "Angkatan {$year}: " . count($students) . " mahasiswa diinput.\n";
    }
    echo "Total mahasiswa baru berhasil dibuat: {$totalInserted}\n";

    // 4. Enroll mahasiswa ke kelas Kurikulum OBE SI 2022
    // Mahasiswa angkatan 2022-2025 semuanya menggunakan Kurikulum 2 (OBE SI 2022)
    $kurikulumId = 2;

    $kelasKurikulum2 = $db->query("
        SELECT k.id as kelas_id, mk.id as mk_id, mk.kode, mk.nama, mk.semester
        FROM kelas k
        JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
        WHERE mk.kurikulum_id = {$kurikulumId}
        ORDER BY mk.semester, mk.kode
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($kelasKurikulum2)) {
        throw new RuntimeException("Kelas untuk Kurikulum ID {$kurikulumId} tidak ditemukan!");
    }

    echo "Total kelas Kurikulum 2: " . count($kelasKurikulum2) . "\n";

    // Siapkan sub-cpmk per mata kuliah
    $getSubCpmk = $db->prepare("
        SELECT sc.id
        FROM sub_cpmk sc
        JOIN cpmk cp ON cp.id = sc.cpmk_id
        WHERE cp.mk_id = ?
        ORDER BY cp.id, sc.urutan, sc.id
    ");

    $mkSubCpmk = [];
    foreach ($kelasKurikulum2 as $kls) {
        $mkId = (int)$kls['mk_id'];
        if (!isset($mkSubCpmk[$mkId])) {
            $getSubCpmk->execute([$mkId]);
            $mkSubCpmk[$mkId] = array_map('intval', $getSubCpmk->fetchAll(PDO::FETCH_COLUMN));
        }
    }

    $insertEnrollStmt = $db->prepare("
        INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id)
        VALUES (?, ?)
    ");

    $insertNilaiStmt = $db->prepare("
        INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
    ");

    $totalEnroll = 0;
    $totalNilai = 0;

    foreach ($newStudentsMap as $year => $students) {
        foreach ($students as $student) {
            $mhsId = $student['id'];
            $stIndex = $student['index'];

            // Base score bervariasi antar mahasiswa (antara 76 s/d 92)
            $baseScore = 76 + (($stIndex * 5 + $year * 3) % 17);

            foreach ($kelasKurikulum2 as $kls) {
                $kelasId = (int)$kls['kelas_id'];
                $mkId = (int)$kls['mk_id'];

                $insertEnrollStmt->execute([$kelasId, $mhsId]);
                $totalEnroll++;

                $subCpmkIds = $mkSubCpmk[$mkId] ?? [];
                foreach ($subCpmkIds as $subIdx => $subCpmkId) {
                    // Variasi nilai berbasis formula deterministik
                    $variation = (($mkId * 3 + $subIdx * 4 + $mhsId * 2) % 11) - 4;
                    $score = min(98, max(72, $baseScore + $variation));

                    $insertNilaiStmt->execute([$kelasId, $mhsId, $subCpmkId, $score]);
                    $totalNilai++;
                }
            }
        }
    }

    if ($db->inTransaction()) {
        $db->commit();
    }

    echo "Pendaftaran kelas baru berhasil: {$totalEnroll}\n";
    echo "Nilai asesmen baru berhasil diinput: {$totalNilai}\n";
    echo "=== SINKRONISASI SELESAI DENGAN SUKSES! ===\n";

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
