<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance();

echo "--- KELAS FOR MK 6 ---\n";
$stmt = $db->prepare('SELECT k.id as kelas_id, k.nama as nama_kelas, k.mata_kuliah_id, u.nama_lengkap as dosen_utama, tt.dosen_id as tt_dosen_id, u_tt.nama_lengkap as tt_dosen_nama FROM kelas k LEFT JOIN users u ON k.dosen_id = u.id LEFT JOIN kelas_tim_teaching tt ON k.id = tt.kelas_id LEFT JOIN users u_tt ON tt.dosen_id = u_tt.id WHERE k.mata_kuliah_id = ?');
$stmt->execute([6]);
print_r($stmt->fetchAll());
