<?php
// database/migration_add_email_users.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Running migration: adding email column to users table...\n";
$cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('email', $cols, true)) {
    $db->exec("ALTER TABLE `users` ADD COLUMN `email` varchar(150) DEFAULT NULL AFTER `nama_lengkap`");
    echo "- Kolom 'email' berhasil ditambahkan ke tabel users.\n";
} else {
    echo "- Kolom 'email' sudah ada di tabel users.\n";
}

// Berikan email default untuk user yang belum memiliki email
$db->exec("UPDATE `users` SET `email` = 'admin@obe.ac.id' WHERE `username` = 'admin' AND (`email` IS NULL OR `email` = '')");
$db->exec("UPDATE `users` SET `email` = CONCAT(`username`, '@obe.ac.id') WHERE `email` IS NULL OR `email` = ''");
echo "- Email default berhasil diperbarui untuk data user yang ada.\n";
