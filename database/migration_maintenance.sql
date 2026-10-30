-- Migrasi Mode Pemeliharaan (Maintenance Mode) OBE (SIP-OKE)
-- Dibuat: 2026-10-30

CREATE TABLE IF NOT EXISTS `app_settings` (
    `setting_key` VARCHAR(64) NOT NULL PRIMARY KEY,
    `setting_value` LONGTEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Settings Pemeliharaan
INSERT INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('maintenance_mode', '0'),
('maintenance_title', 'Website Sedang Dalam Pemeliharaan'),
('maintenance_message', 'Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi (SIP-OKE) saat ini sedang dalam proses pemeliharaan berkala untuk peningkatan performa, pembaruan fitur, dan keandalan sistem. Seluruh menu dan layanan sementara dinonaktifkan hingga proses pemeliharaan selesai.'),
('maintenance_until', ''),
('maintenance_img', 'assets/images/maintenance.png')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

