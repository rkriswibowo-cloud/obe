CREATE TABLE IF NOT EXISTS `rps_pertemuan_sub_cpmk` (
  `rps_pertemuan_id` int(11) NOT NULL, `sub_cpmk_id` int(11) NOT NULL,
  PRIMARY KEY (`rps_pertemuan_id`, `sub_cpmk_id`), KEY `idx_rpsc_sub_cpmk` (`sub_cpmk_id`),
  CONSTRAINT `rpsc_pertemuan_fk` FOREIGN KEY (`rps_pertemuan_id`) REFERENCES `rps_pertemuan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rpsc_sub_cpmk_fk` FOREIGN KEY (`sub_cpmk_id`) REFERENCES `sub_cpmk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `rps_pertemuan_sub_cpmk` (`rps_pertemuan_id`, `sub_cpmk_id`)
SELECT `id`, `sub_cpmk_id` FROM `rps_pertemuan` WHERE `sub_cpmk_id` IS NOT NULL;
