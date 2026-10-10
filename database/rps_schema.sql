-- Database migration for RPS Builder & Official PDF-identical Document Viewer

CREATE TABLE IF NOT EXISTS `rps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mata_kuliah_id` int(11) NOT NULL,
  `versi` varchar(20) DEFAULT '3/0',
  `fakultas` varchar(100) DEFAULT 'Fakultas Sains dan Teknologi',
  `program_studi` varchar(1000) DEFAULT 'S1 Sistem Informasi',
  `kelas` varchar(50) DEFAULT 'I-23',
  `sifat` varchar(50) DEFAULT 'Wajib',
  `semester_ke` varchar(20) DEFAULT '6',
  `ketersediaan` varchar(50) DEFAULT 'Program Studi',
  `bentuk_pembelajaran` text DEFAULT 'Kuliah/ Responsi/ Tutorial/ Seminar, atau yang setara',
  `media_pembelajaran` varchar(100) DEFAULT 'Audiovisual (Zoom, WAG, Youtube)',
  `kelompok_keilmuan` varchar(100) DEFAULT 'Sistem Informasi',
  `prasyarat` varchar(255) DEFAULT '-',
  `dosen_pengampu` text DEFAULT '1. Rony Kriswibowo, M.Kom (PJMK)',
  `tahun_akademik` varchar(50) DEFAULT 'Genap 2025/2026',
  `referensi` text DEFAULT NULL,
  `sistem_penilaian_desc` text DEFAULT 'Sistem penilaian dilakukan pada CPMK masing-masing sesuai dengan rubrik yang dibuat.',
  `bobot_keaktifan` decimal(5,2) DEFAULT 5.00,
  `bobot_tugas` decimal(5,2) DEFAULT 15.00,
  `bobot_kuis` decimal(5,2) DEFAULT 20.00,
  `bobot_uts` decimal(5,2) DEFAULT 30.00,
  `bobot_uas` decimal(5,2) DEFAULT 30.00,
  `sistem_evaluasi_desc` text DEFAULT 'Setiap mahasiswa harus meraih nilai/ predikat 60/C untuk masing-masing CPMK. Jika belum memenuhi maka mahasiswa wajib melakukan asesmen perbaikan maksimal sebanyak dua kali.',
  `rencana_evaluasi_json` longtext DEFAULT NULL,
  `rubrik_penilaian_json` longtext DEFAULT NULL,
  `jenis_penilaian_json` longtext DEFAULT NULL,
  `rubrik_mk_json` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rps_mk` (`mata_kuliah_id`),
  CONSTRAINT `rps_mk_fk` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambahan kolom untuk sub_cpmk jika belum ada
ALTER TABLE `sub_cpmk` ADD COLUMN IF NOT EXISTS `pengalaman_belajar` text DEFAULT NULL AFTER `level_taksonomi`;
ALTER TABLE `sub_cpmk` ADD COLUMN IF NOT EXISTS `asesmen_penilaian` varchar(100) DEFAULT NULL AFTER `pengalaman_belajar`;

-- Tambahan kolom pada rps_pertemuan jika belum ada
ALTER TABLE `rps_pertemuan` ADD COLUMN IF NOT EXISTS `mode_pembelajaran` varchar(50) DEFAULT 'ASM dan TM' AFTER `bentuk_metode`;
ALTER TABLE `rps_pertemuan` ADD COLUMN IF NOT EXISTS `durasi_detail` text DEFAULT NULL AFTER `alokasi_waktu`;
