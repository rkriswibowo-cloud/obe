ALTER TABLE `rps`
    ADD COLUMN IF NOT EXISTS `jenis_penilaian_json` LONGTEXT NULL AFTER `rubrik_penilaian_json`;
