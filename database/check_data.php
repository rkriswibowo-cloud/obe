<?php
require __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=== KURIKULUM ===\n";
$rows = $db->query('SELECT id, kode, nama, tahun_mulai, status FROM kurikulum ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['kode'] . ' | ' . $r['nama'] . ' | ' . $r['tahun_mulai'] . ' | ' . $r['status'] . "\n";
}

echo "\n=== USERS TABLE SCHEMA ===\n";
$cols = $db->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Key'] . "\n";
}

echo "\n=== MAHASISWA (sample) ===\n";
$mhs = $db->query("
    SELECT u.id, u.username, u.nama_lengkap, SUBSTRING(u.username, 1, 4) as angkatan
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'Mahasiswa'
    ORDER BY u.username ASC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($mhs as $m) {
    echo $m['id'] . ' | ' . $m['username'] . ' | ' . $m['nama_lengkap'] . ' | angkatan: ' . $m['angkatan'] . "\n";
}

echo "\n=== ATURAN KURIKULUM TABLE CHECK ===\n";
try {
    $db->query('SELECT 1 FROM aturan_kurikulum LIMIT 1');
    echo "Table aturan_kurikulum EXISTS\n";
} catch (Exception $e) {
    echo "Table aturan_kurikulum NOT EXISTS\n";
}

// Check if users table has kurikulum_id
$hasKurikulumId = false;
foreach ($cols as $c) {
    if ($c['Field'] === 'kurikulum_id') {
        $hasKurikulumId = true;
    }
}
echo "\nusers.kurikulum_id column: " . ($hasKurikulumId ? "EXISTS" : "NOT EXISTS") . "\n";
