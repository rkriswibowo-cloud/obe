ALTER TABLE `rubrik`
    MODIFY COLUMN `jenis` VARCHAR(100) NOT NULL DEFAULT 'Rubrik Analitik',
    ADD COLUMN IF NOT EXISTS `kode_jenis` VARCHAR(20) NULL AFTER `nama`,
    ADD COLUMN IF NOT EXISTS `karakteristik` VARCHAR(255) NULL AFTER `jenis`,
    ADD COLUMN IF NOT EXISTS `contoh_penggunaan` VARCHAR(255) NULL AFTER `karakteristik`;

UPDATE `rubrik`
SET `kode_jenis` = CASE WHEN `jenis` IN ('Holistik', 'Rubrik Holistik') THEN 'RB-HOL' ELSE 'RB-ANA' END,
    `jenis` = CASE WHEN `jenis` IN ('Holistik', 'Rubrik Holistik') THEN 'Rubrik Holistik' ELSE 'Rubrik Analitik' END,
    `karakteristik` = CASE WHEN `jenis` IN ('Holistik', 'Rubrik Holistik') THEN 'Kinerja dinilai secara keseluruhan' ELSE 'Beberapa kriteria dinilai terpisah' END,
    `contoh_penggunaan` = CASE WHEN `jenis` IN ('Holistik', 'Rubrik Holistik') THEN 'Presentasi singkat, performa' ELSE 'Proyek, laporan, presentasi, praktikum' END
WHERE `kode_jenis` IS NULL OR `kode_jenis` = '';

ALTER TABLE `rubrik` MODIFY COLUMN `kode_jenis` VARCHAR(20) NOT NULL;
