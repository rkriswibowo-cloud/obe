<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Running migration: adding nidn, nuptk, homebase to users table...\n";

$cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('nidn', $cols)) {
    $db->exec("ALTER TABLE `users` ADD COLUMN `nidn` varchar(30) DEFAULT NULL AFTER `nama_lengkap`");
    echo "- Kolom 'nidn' berhasil ditambahkan ke tabel users.\n";
}

if (!in_array('nuptk', $cols)) {
    $db->exec("ALTER TABLE `users` ADD COLUMN `nuptk` varchar(30) DEFAULT NULL AFTER `nidn`");
    echo "- Kolom 'nuptk' berhasil ditambahkan ke tabel users.\n";
}

if (!in_array('homebase', $cols)) {
    $db->exec("ALTER TABLE `users` ADD COLUMN `homebase` varchar(100) DEFAULT NULL AFTER `nuptk`");
    echo "- Kolom 'homebase' berhasil ditambahkan ke tabel users.\n";
}

// Update data dosen yang sudah ada dengan data sample yang valid
$dosenSamples = [
    '0707069402' => [
        'nidn' => '0707069402',
        'nuptk' => '9438752654200032',
        'homebase' => 'S1 Sistem Informasi'
    ],
    '0708056701' => [
        'nidn' => '0708056701',
        'nuptk' => '8549763765300041',
        'homebase' => 'S1 Sistem Informasi'
    ],
    '0709078803' => [
        'nidn' => '0709078803',
        'nuptk' => '7650874876400052',
        'homebase' => 'S1 Sistem Informasi'
    ],
    '0710089204' => [
        'nidn' => '0710089204',
        'nuptk' => '6761985987500063',
        'homebase' => 'S1 Informatika'
    ],
    '0711098505' => [
        'nidn' => '0711098505',
        'nuptk' => '5872096098600074',
        'homebase' => 'S1 Sistem Informasi'
    ],
];

$stmtUpdate = $db->prepare("
    UPDATE users 
    SET nidn = :nidn, nuptk = :nuptk, homebase = :homebase 
    WHERE username = :username
");

foreach ($dosenSamples as $username => $data) {
    $stmtUpdate->execute([
        'nidn' => $data['nidn'],
        'nuptk' => $data['nuptk'],
        'homebase' => $data['homebase'],
        'username' => $username
    ]);
}

echo "Berhasil memperbarui data dosen awal dengan NIDN, NUPTK, dan Homebase!\n";
