-- Database Migration: Tahap 7 Features (Rubric Builder, RPS Rincian Pertemuan, CQI)

-- 1. Pastikan tabel rubrik memiliki kolom deskripsi jika belum ada
ALTER TABLE `rubrik` ADD COLUMN IF NOT EXISTS `deskripsi` text DEFAULT NULL AFTER `nama`;
ALTER TABLE `rubrik` ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `rubrik` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `rubrik_kriteria` ADD COLUMN IF NOT EXISTS `urutan` int(11) DEFAULT 0 AFTER `bobot`;
ALTER TABLE `rubrik_level` ADD COLUMN IF NOT EXISTS `urutan` int(11) DEFAULT 0 AFTER `skor`;

-- 2. Tabel RPS Rincian Pertemuan (1 s.d. 16)
CREATE TABLE IF NOT EXISTS `rps_pertemuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mata_kuliah_id` int(11) NOT NULL,
  `pertemuan_ke` int(11) NOT NULL,
  `sub_cpmk_id` int(11) DEFAULT NULL,
  `bahan_kajian` text NOT NULL,
  `bentuk_metode` varchar(255) DEFAULT 'Kuliah & Diskusi',
  `pengalaman_belajar` text DEFAULT NULL,
  `alokasi_waktu` varchar(50) DEFAULT '2 x 50 menit',
  `pustaka` text DEFAULT NULL,
  `indikator_penilaian` text DEFAULT NULL,
  `bobot_penilaian` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rps_mk` (`mata_kuliah_id`),
  KEY `idx_rps_sub_cpmk` (`sub_cpmk_id`),
  CONSTRAINT `rps_pertemuan_mk_fk` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rps_pertemuan_sub_fk` FOREIGN KEY (`sub_cpmk_id`) REFERENCES `sub_cpmk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel CQI (Continuous Quality Improvement)
CREATE TABLE IF NOT EXISTS `cqi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpl_id` int(11) NOT NULL,
  `tahun_akademik_id` int(11) DEFAULT NULL,
  `semester` varchar(20) DEFAULT 'Ganjil',
  `target_capaian` decimal(5,2) NOT NULL DEFAULT 70.00,
  `capaian_aktual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `analisis_masalah` text NOT NULL,
  `akar_masalah` text NOT NULL,
  `rencana_tindak_lanjut` text NOT NULL,
  `pic` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `monitoring_evaluasi` text DEFAULT NULL,
  `status` enum('OPEN','IN_PROGRESS','COMPLETED','RE_MEASURED') NOT NULL DEFAULT 'OPEN',
  `hasil_re_measurement` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cqi_cpl` (`cpl_id`),
  KEY `idx_cqi_ta` (`tahun_akademik_id`),
  CONSTRAINT `cqi_cpl_fk` FOREIGN KEY (`cpl_id`) REFERENCES `cpl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cqi_ta_fk` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

