<?php
require 'config/database.php';
$db = Database::getInstance();
$db->exec("INSERT INTO program_studi (kode, nama, jenjang) VALUES ('SI', 'Sistem Informasi', 'S1'), ('IF', 'Informatika', 'S1')");
echo 'Seeded prodi.';
