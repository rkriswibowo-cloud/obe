<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

$statements = [
"CREATE TABLE IF NOT EXISTS rpl_pengaturan (
 id TINYINT UNSIGNED NOT NULL DEFAULT 1,
 minimal_sks_3_semester INT NOT NULL DEFAULT 80,
 minimal_sks_4_semester INT NOT NULL DEFAULT 40,
 maksimal_sks_per_semester INT NOT NULL DEFAULT 24,
 minimal_persentase_ekuivalensi DECIMAL(5,2) NOT NULL DEFAULT 70.00,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"INSERT IGNORE INTO rpl_pengaturan (id) VALUES (1)",
"CREATE TABLE IF NOT EXISTS rpl_mahasiswa (
 id INT NOT NULL AUTO_INCREMENT,
 mahasiswa_id INT NOT NULL,
 kurikulum_id INT NOT NULL,
 skema_semester TINYINT DEFAULT NULL,
 status ENUM('AKTIF','SELESAI','NONAKTIF') NOT NULL DEFAULT 'AKTIF',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_rpl_mahasiswa (mahasiswa_id),
 KEY idx_rpl_kurikulum (kurikulum_id),
 CONSTRAINT fk_rpl_mhs_user FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT fk_rpl_mhs_kurikulum FOREIGN KEY (kurikulum_id) REFERENCES kurikulum(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS rpl_mata_kuliah_setting (
 id INT NOT NULL AUTO_INCREMENT,
 mata_kuliah_id INT NOT NULL,
 minimal_nilai_angka DECIMAL(5,2) NOT NULL DEFAULT 55.00,
 minimal_persentase_ekuivalensi DECIMAL(5,2) NOT NULL DEFAULT 70.00,
 catatan VARCHAR(255) DEFAULT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_rpl_setting_mk (mata_kuliah_id),
 CONSTRAINT fk_rpl_setting_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"INSERT IGNORE INTO rpl_mata_kuliah_setting
 (mata_kuliah_id, minimal_nilai_angka, minimal_persentase_ekuivalensi, is_active)
 SELECT id, 55.00, 70.00, 1 FROM mata_kuliah WHERE status='ACTIVE'",
"CREATE TABLE IF NOT EXISTS rpl_konversi (
 id INT NOT NULL AUTO_INCREMENT,
 rpl_mahasiswa_id INT NOT NULL,
 mata_kuliah_id INT NOT NULL,
 kode_asal VARCHAR(50) DEFAULT NULL,
 nama_asal VARCHAR(150) NOT NULL,
 institusi_asal VARCHAR(150) DEFAULT NULL,
 sks_asal INT NOT NULL DEFAULT 0,
 nilai_asal VARCHAR(20) DEFAULT NULL,
 nilai_angka DECIMAL(5,2) NOT NULL,
 nilai_huruf VARCHAR(3) NOT NULL,
 bobot_mutu DECIMAL(3,2) NOT NULL,
 persentase_ekuivalensi DECIMAL(5,2) NOT NULL DEFAULT 100.00,
 sks_diakui INT NOT NULL,
 dasar_rekognisi ENUM('PENDIDIKAN_FORMAL','PENGALAMAN_KERJA','PELATIHAN','SERTIFIKASI','PORTOFOLIO') NOT NULL DEFAULT 'PENDIDIKAN_FORMAL',
 bukti_path VARCHAR(255) DEFAULT NULL,
 status ENUM('DRAFT','DIAJUKAN','DISETUJUI','DITOLAK','DIBATALKAN') NOT NULL DEFAULT 'DIAJUKAN',
 catatan_pengaju TEXT DEFAULT NULL,
 catatan_kaprodi TEXT DEFAULT NULL,
 dibuat_oleh INT DEFAULT NULL,
 diputuskan_oleh INT DEFAULT NULL,
 diputuskan_at DATETIME DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_rpl_target (rpl_mahasiswa_id, mata_kuliah_id),
 KEY idx_rpl_konversi_status (status),
 CONSTRAINT fk_rpl_konversi_mhs FOREIGN KEY (rpl_mahasiswa_id) REFERENCES rpl_mahasiswa(id) ON DELETE CASCADE,
 CONSTRAINT fk_rpl_konversi_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT,
 CONSTRAINT fk_rpl_konversi_creator FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_rpl_konversi_approver FOREIGN KEY (diputuskan_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS rpl_rencana_studi (
 id INT NOT NULL AUTO_INCREMENT,
 rpl_mahasiswa_id INT NOT NULL,
 mata_kuliah_id INT NOT NULL,
 semester_rpl TINYINT NOT NULL,
 status ENUM('DIRENCANAKAN','DIAMBIL','LULUS') NOT NULL DEFAULT 'DIRENCANAKAN',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (id), UNIQUE KEY uq_rpl_rencana_mk (rpl_mahasiswa_id, mata_kuliah_id),
 CONSTRAINT fk_rpl_rencana_mhs FOREIGN KEY (rpl_mahasiswa_id) REFERENCES rpl_mahasiswa(id) ON DELETE CASCADE,
 CONSTRAINT fk_rpl_rencana_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($statements as $sql) $db->exec($sql);
echo "Migration RPL berhasil.\n";
