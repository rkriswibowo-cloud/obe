<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();
$db->exec("CREATE TABLE IF NOT EXISTS rps_semester_arsip (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengampu_id BIGINT UNSIGNED NOT NULL,
    mata_kuliah_id BIGINT UNSIGNED NOT NULL,
    tahun_akademik_id BIGINT UNSIGNED NOT NULL,
    rps_json LONGTEXT NULL,
    pertemuan_json LONGTEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rps_semester_pengampu (pengampu_id),
    KEY idx_rps_semester_mk_ta (mata_kuliah_id,tahun_akademik_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Migration rps_semester_arsip selesai.\n";
