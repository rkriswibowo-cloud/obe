ALTER TABLE `rps`
    ADD COLUMN IF NOT EXISTS `rubrik_mk_json` LONGTEXT NULL AFTER `jenis_penilaian_json`;
