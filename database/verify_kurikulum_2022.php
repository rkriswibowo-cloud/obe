<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

$kur = $db->query("SELECT * FROM kurikulum WHERE kode = 'OBE SI 2022'")->fetch(PDO::FETCH_ASSOC);
echo "=== DATA KURIKULUM ===\n";
print_r($kur);

$kurId = $kur['id'];

echo "\n=== PROFIL LULUSAN (Kurikulum ID $kurId) ===\n";
$pls = $db->query("SELECT kode, nama, LEFT(deskripsi, 50) as d FROM profil_lulusan WHERE kurikulum_id = $kurId ORDER BY kode")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pls as $pl) {
    echo "- {$pl['kode']}: {$pl['nama']} ({$pl['d']}...)\n";
}

echo "\n=== CPL (Kurikulum ID $kurId) ===\n";
$cpls = $db->query("SELECT kode, kategori, target_capaian, LEFT(rumusan, 50) as r FROM cpl WHERE kurikulum_id = $kurId ORDER BY kode")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cpls as $c) {
    echo "- {$c['kode']} ({$c['kategori']}) [Target: {$c['target_capaian']}%]: {$c['r']}...\n";
}

echo "\n=== DISTRIBUSI MATA KULIAH & SKS PER SEMESTER (Kurikulum ID $kurId) ===\n";
$semList = $db->query("
    SELECT semester, 
           COUNT(*) as jml_mk, 
           SUM(sks_teori) as total_teori, 
           SUM(sks_praktek) as total_praktek, 
           SUM(sks_teori + sks_praktek) as total_sks 
    FROM mata_kuliah 
    WHERE kurikulum_id = $kurId 
    GROUP BY semester 
    ORDER BY semester
")->fetchAll(PDO::FETCH_ASSOC);

$totalAllSks = 0;
$totalAllMk = 0;
foreach ($semList as $s) {
    echo "Semester {$s['semester']}: {$s['jml_mk']} MK | Teori: {$s['total_teori']} SKS | Praktik: {$s['total_praktek']} SKS | Total: {$s['total_sks']} SKS\n";
    $totalAllSks += (int)$s['total_sks'];
    $totalAllMk += (int)$s['jml_mk'];
}
echo "--------------------------------------------------------\n";
echo "TOTAL: $totalAllMk Mata Kuliah | $totalAllSks SKS\n";

echo "\n=== PEMETAAN CPL-MK ===\n";
$countMkCpl = $db->query("SELECT count(*) FROM mk_cpl mc JOIN mata_kuliah m ON mc.mk_id = m.id WHERE m.kurikulum_id = $kurId")->fetchColumn();
echo "Total Relasi MK ke CPL: $countMkCpl relasi\n";
