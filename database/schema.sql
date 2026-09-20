-- obe_cpl database schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Tabel Users & Roles
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Master Kurikulum
CREATE TABLE `program_studi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenjang` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tahun_akademik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kurikulum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tahun_mulai` int(4) NOT NULL,
  `versi` varchar(10) NOT NULL,
  `status` enum('DRAFT','ACTIVE','INACTIVE','ARCHIVED') DEFAULT 'DRAFT',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `kurik_prodi_fk` FOREIGN KEY (`prodi_id`) REFERENCES `program_studi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profil_lulusan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `deskripsi` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `pl_kurik_fk` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `rumusan` text NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `target_capaian` decimal(5,2) DEFAULT 70.00,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  CONSTRAINT `cpl_kurik_fk` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mata_kuliah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `sks_teori` int(2) DEFAULT 0,
  `sks_praktek` int(2) DEFAULT 0,
  `semester` int(2) NOT NULL,
  `sifat` enum('Wajib','Pilihan') DEFAULT 'Wajib',
  `kelompok_keilmuan` varchar(100) DEFAULT NULL,
  `deskripsi` text,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  CONSTRAINT `mk_kurik_fk` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mk_cpl` (
  `mk_id` int(11) NOT NULL,
  `cpl_id` int(11) NOT NULL,
  `bobot_kontribusi` decimal(5,2) DEFAULT NULL,
  `is_diukur` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`mk_id`,`cpl_id`),
  CONSTRAINT `mkcpl_cpl_fk` FOREIGN KEY (`cpl_id`) REFERENCES `cpl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mkcpl_mk_fk` FOREIGN KEY (`mk_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cpmk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mk_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `rumusan` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cpmk_mk_fk` FOREIGN KEY (`mk_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cpmk_cpl` (
  `cpmk_id` int(11) NOT NULL,
  `cpl_id` int(11) NOT NULL,
  PRIMARY KEY (`cpmk_id`,`cpl_id`),
  CONSTRAINT `cpmkcpl_cpl_fk` FOREIGN KEY (`cpl_id`) REFERENCES `cpl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cpmkcpl_cpmk_fk` FOREIGN KEY (`cpmk_id`) REFERENCES `cpmk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sub_cpmk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpmk_id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `rumusan` text NOT NULL,
  `level_taksonomi` varchar(50) DEFAULT NULL,
  `bobot` decimal(5,2) NOT NULL,
  `urutan` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `sub_cpmk_fk` FOREIGN KEY (`cpmk_id`) REFERENCES `cpmk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sub_cpmk_indikator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_cpmk_id` int(11) NOT NULL,
  `indikator` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ind_sub_cpmk_fk` FOREIGN KEY (`sub_cpmk_id`) REFERENCES `sub_cpmk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rubrik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `jenis` enum('Analitik','Holistik') DEFAULT 'Analitik',
  `status` enum('ACTIVE','ARCHIVED') DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rubrik_kriteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rubrik_id` int(11) NOT NULL,
  `kriteria` varchar(255) NOT NULL,
  `bobot` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `rkrit_rubrik_fk` FOREIGN KEY (`rubrik_id`) REFERENCES `rubrik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rubrik_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rubrik_id` int(11) NOT NULL,
  `level_nama` varchar(50) NOT NULL,
  `skor` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `rlev_rubrik_fk` FOREIGN KEY (`rubrik_id`) REFERENCES `rubrik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rubrik_deskriptor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kriteria_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `deskripsi` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `rdesc_kriteria_fk` FOREIGN KEY (`kriteria_id`) REFERENCES `rubrik_kriteria` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rdesc_level_fk` FOREIGN KEY (`level_id`) REFERENCES `rubrik_level` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`name`) VALUES ('Super Admin'), ('Admin Prodi'), ('Dosen'), ('Kaprodi'), ('Mahasiswa');
-- pass: password
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role_id`) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 1); 

COMMIT;
