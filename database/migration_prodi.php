<?php
// database/migration_prodi.php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Running migration: Master Program Studi & Multi-Prodi Architecture...\n";

// 1. Tambah kolom ke tabel program_studi
$prodiCols = $db->query("SHOW COLUMNS FROM program_studi")->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('fakultas', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `fakultas` varchar(100) NOT NULL DEFAULT 'Fakultas Sains dan Teknologi' AFTER `jenjang`");
    echo "- Kolom 'fakultas' berhasil ditambahkan ke program_studi.\n";
}

if (!in_array('nama_kaprodi', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `nama_kaprodi` varchar(100) DEFAULT NULL AFTER `fakultas`");
    echo "- Kolom 'nama_kaprodi' berhasil ditambahkan ke program_studi.\n";
}

if (!in_array('nidn_kaprodi', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `nidn_kaprodi` varchar(30) DEFAULT NULL AFTER `nama_kaprodi`");
    echo "- Kolom 'nidn_kaprodi' berhasil ditambahkan ke program_studi.\n";
}

if (!in_array('status', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE' AFTER `nidn_kaprodi`");
    echo "- Kolom 'status' berhasil ditambahkan ke program_studi.\n";
}

if (!in_array('created_at', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `status`");
    echo "- Kolom 'created_at' berhasil ditambahkan ke program_studi.\n";
}

if (!in_array('updated_at', $prodiCols)) {
    $db->exec("ALTER TABLE `program_studi` ADD COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
    echo "- Kolom 'updated_at' berhasil ditambahkan ke program_studi.\n";
}

// 2. Tambah kolom prodi_id ke tabel users jika belum ada
$userCols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('prodi_id', $userCols)) {
    $db->exec("ALTER TABLE `users` ADD COLUMN `prodi_id` int(11) DEFAULT NULL AFTER `homebase`");
    $db->exec("ALTER TABLE `users` ADD KEY `idx_users_prodi` (`prodi_id`)");
    echo "- Kolom 'prodi_id' berhasil ditambahkan ke tabel users.\n";
}

// 3. Sanitasi & Data Awal Program Studi
// Hapus duplikat ID 2 jika ada dan tidak direferensikan
$db->exec("DELETE FROM program_studi WHERE id = 2 AND NOT EXISTS (SELECT 1 FROM kurikulum WHERE prodi_id = 2)");

// Update data Prodi 1 (Sistem Informasi)
$db->prepare("
    UPDATE program_studi 
    SET kode = 'SI', 
        nama = 'Sistem Informasi', 
        jenjang = 'S1', 
        fakultas = 'Fakultas Sains dan Teknologi', 
        nama_kaprodi = 'Dr. Budi Santoso, M.Kom.', 
        nidn_kaprodi = '0708056701', 
        status = 'ACTIVE' 
    WHERE id = 1
")->execute();

// Update atau insert Prodi Informatika
$stmtIf = $db->prepare("SELECT id FROM program_studi WHERE kode = 'IF' LIMIT 1");
$stmtIf->execute();
$ifId = $stmtIf->fetchColumn();

if ($ifId) {
    $db->prepare("
        UPDATE program_studi 
        SET nama = 'Informatika', 
            jenjang = 'S1', 
            fakultas = 'Fakultas Sains dan Teknologi', 
            nama_kaprodi = 'Andi Prasetyo, M.Kom.', 
            nidn_kaprodi = '0710089204', 
            status = 'ACTIVE' 
        WHERE id = :id
    ")->execute(['id' => $ifId]);
} else {
    $db->prepare("
        INSERT INTO program_studi (kode, nama, jenjang, fakultas, nama_kaprodi, nidn_kaprodi, status)
        VALUES ('IF', 'Informatika', 'S1', 'Fakultas Sains dan Teknologi', 'Andi Prasetyo, M.Kom.', '0710089204', 'ACTIVE')
    ")->execute();
}

// Insert prodi tambahan (Bisnis Digital) jika belum ada
$countBd = $db->query("SELECT COUNT(*) FROM program_studi WHERE kode = 'BD'")->fetchColumn();
if ($countBd == 0) {
    $db->exec("
        INSERT INTO program_studi (kode, nama, jenjang, fakultas, nama_kaprodi, nidn_kaprodi, status)
        VALUES ('BD', 'Bisnis Digital', 'S1', 'Fakultas Sains dan Teknologi', 'Dewi Lestari, S.T., M.Cs.', '0711098505', 'ACTIVE')
    ");
    echo "- Program Studi 'Bisnis Digital' berhasil ditambahkan sebagai sample multi-prodi.\n";
}

// 4. Update relasi prodi_id pada dosen & mahasiswa
$db->exec("UPDATE users SET prodi_id = 1 WHERE prodi_id IS NULL AND (homebase LIKE '%Sistem Informasi%' OR homebase IS NULL)");
$db->exec("UPDATE users SET prodi_id = (SELECT id FROM program_studi WHERE kode = 'IF' LIMIT 1) WHERE homebase LIKE '%Informatika%'");

echo "Migrasi Program Studi Selesai dengan Sukses!\n";
