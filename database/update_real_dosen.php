<?php
/**
 * database/update_real_dosen.php
 *
 * Mengganti semua data dosen menjadi 3 dosen homebased Sistem Informasi:
 * 1. RUSINA WIDHA FEBRIANA | NIDN: 0717028802 | NUPTK: 2549766333230072
 * 2. AGUNG BUDI SETYAWAN   | NIDN: 0722069203 | NUPTK: 4954770671130312
 * 3. RONY KRISWIBOWO       | NIDN: 0707069402 | NUPTK: 7939772673130272
 */

require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=== MEMULAI PEMBARUAN DATA DOSEN RESMI PRODI SISTEM INFORMASI ===\n";

$dosenBaru = [
    [
        'target_id' => 3,
        'nama' => 'RUSINA WIDHA FEBRIANA',
        'nidn' => '0717028802',
        'nuptk' => '2549766333230072',
        'homebase' => 'S1 Sistem Informasi'
    ],
    [
        'target_id' => 4,
        'nama' => 'AGUNG BUDI SETYAWAN',
        'nidn' => '0722069203',
        'nuptk' => '4954770671130312',
        'homebase' => 'S1 Sistem Informasi'
    ],
    [
        'target_id' => 2,
        'nama' => 'RONY KRISWIBOWO',
        'nidn' => '0707069402',
        'nuptk' => '7939772673130272',
        'homebase' => 'S1 Sistem Informasi'
    ],
];

// Backup dosen lama
$db->exec('CREATE TABLE IF NOT EXISTS backup_old_dosen LIKE users');
$db->exec("INSERT IGNORE INTO backup_old_dosen SELECT * FROM users WHERE role_id = 3");

$passwordHash = password_hash('password', PASSWORD_DEFAULT);

// 1. Update 3 akun dosen utama (ID 2, 3, 4)
$updateStmt = $db->prepare("
    UPDATE users 
    SET username = :username,
        password = :password,
        nama_lengkap = :nama_lengkap,
        nidn = :nidn,
        nuptk = :nuptk,
        homebase = :homebase,
        prodi_id = 1,
        role_id = 3
    WHERE id = :id
");

foreach ($dosenBaru as $d) {
    $updateStmt->execute([
        'username' => $d['nidn'],
        'password' => $passwordHash,
        'nama_lengkap' => $d['nama'],
        'nidn' => $d['nidn'],
        'nuptk' => $d['nuptk'],
        'homebase' => $d['homebase'],
        'id' => $d['target_id']
    ]);
    echo "- Berhasil memperbarui ID {$d['target_id']}: {$d['nama']} (NIDN: {$d['nidn']})\n";
}

// 2. Re-assign kelas kuliah ke 3 dosen ini secara merata
$kelasRows = $db->query("SELECT id FROM kelas ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
$validDosenIds = [3, 4, 2]; // Rusina, Agung, Rony

$updateKelasStmt = $db->prepare("UPDATE kelas SET dosen_id = :dosen_id WHERE id = :id");
foreach ($kelasRows as $idx => $kelasId) {
    $assignedDosenId = $validDosenIds[$idx % count($validDosenIds)];
    $updateKelasStmt->execute([
        'dosen_id' => $assignedDosenId,
        'id' => $kelasId
    ]);
}
echo "- Berhasil mendistribusikan " . count($kelasRows) . " kelas ke 3 dosen homebased.\n";

// 3. Re-assign kelas_tim_teaching jika ada yang di luar 3 dosen
$db->exec("UPDATE kelas_tim_teaching SET dosen_id = 3 WHERE dosen_id NOT IN (2, 3, 4)");

// 4. Hapus dosen lama lainnya (ID di luar 2, 3, 4 yang memiliki role_id = 3)
$deletedCount = $db->exec("DELETE FROM users WHERE role_id = 3 AND id NOT IN (2, 3, 4)");
echo "- Menghapus dosen di luar homebase Sistem Informasi: {$deletedCount} akun dihapus.\n";

// 5. Update data Kaprodi di tabel program_studi ID 1
$db->exec("
    UPDATE program_studi 
    SET nama_kaprodi = 'RUSINA WIDHA FEBRIANA', 
        nidn_kaprodi = '0717028802'
    WHERE id = 1
");
echo "- Berhasil menyelaraskan data Kaprodi SI di tabel program_studi.\n";

// 6. Update dosen_pengampu di tabel RPS
$db->exec("
    UPDATE rps 
    SET dosen_pengampu = '1. RUSINA WIDHA FEBRIANA (PJMK)\n2. AGUNG BUDI SETYAWAN (Anggota Tim Teaching)\n3. RONY KRISWIBOWO (Dosen Praktikum)'
    WHERE id = 2
");
$db->exec("
    UPDATE rps 
    SET dosen_pengampu = '1. RONY KRISWIBOWO (PJMK)'
    WHERE id IN (1, 7)
");
echo "- Berhasil menyelaraskan dosen pengampu pada tabel RPS.\n";

echo "=== PEMBARUAN DATA DOSEN SELESAI DENGAN SUKSES! ===\n";
