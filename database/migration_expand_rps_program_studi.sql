-- Perbesar kapasitas daftar program studi pada identitas dan header RPS.
ALTER TABLE `rps`
  MODIFY COLUMN `program_studi` varchar(1000) DEFAULT 'S1 Sistem Informasi';
