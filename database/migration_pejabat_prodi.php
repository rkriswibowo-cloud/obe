<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();
$columns = $db->query("SHOW COLUMNS FROM program_studi")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('nama_dekan', $columns, true)) {
    $db->exec("ALTER TABLE program_studi ADD nama_dekan VARCHAR(150) NULL AFTER fakultas");
}
if (!in_array('nidn_dekan', $columns, true)) {
    $db->exec("ALTER TABLE program_studi ADD nidn_dekan VARCHAR(50) NULL AFTER nama_dekan");
}
if (!in_array('dekan_user_id', $columns, true)) {
    $db->exec("ALTER TABLE program_studi ADD dekan_user_id INT NULL AFTER nidn_dekan, ADD INDEX idx_prodi_dekan_user (dekan_user_id), ADD CONSTRAINT fk_prodi_dekan_user FOREIGN KEY (dekan_user_id) REFERENCES users(id) ON DELETE SET NULL");
}
if (!in_array('kaprodi_user_id', $columns, true)) {
    $db->exec("ALTER TABLE program_studi ADD kaprodi_user_id INT NULL AFTER nidn_kaprodi, ADD INDEX idx_prodi_kaprodi_user (kaprodi_user_id), ADD CONSTRAINT fk_prodi_kaprodi_user FOREIGN KEY (kaprodi_user_id) REFERENCES users(id) ON DELETE SET NULL");
}
$db->exec("UPDATE program_studi SET nama_dekan=COALESCE(NULLIF(nama_dekan,''),'Rony Kriswibowo, S.Kom., M.Kom'), nidn_dekan=COALESCE(NULLIF(nidn_dekan,''),'7939772673130272') WHERE id=1");
$db->exec("UPDATE program_studi p JOIN users u ON u.nama_lengkap LIKE 'Rony Kriswibowo%' SET p.dekan_user_id=u.id WHERE p.id=1 AND p.dekan_user_id IS NULL");
$db->exec("UPDATE program_studi p JOIN users u ON u.nama_lengkap LIKE 'Rusina Widha Febriana%' SET p.kaprodi_user_id=u.id WHERE p.id=1 AND p.kaprodi_user_id IS NULL");
echo "Migrasi pengaturan Dekan dan Kaprodi berhasil.\n";
