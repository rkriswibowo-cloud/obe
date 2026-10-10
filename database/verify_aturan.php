<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/AturanKurikulumModel.php';

$m = new AturanKurikulumModel();

echo "=== RULES ===\n";
$rules = $m->getAll();
foreach ($rules as $r) {
    $range = $r['angkatan_mulai'] . ($r['angkatan_selesai'] ? '-' . $r['angkatan_selesai'] : '+');
    echo "Angkatan $range → {$r['nama_kurikulum']} (ID:{$r['kurikulum_id']})\n";
}

echo "\n=== DISTRIBUSI MAHASISWA ===\n";
$dist = $m->getDistribusiMahasiswa();
foreach ($dist as $d) {
    echo "Angkatan {$d['angkatan']}: {$d['jumlah_mahasiswa']} mhs → {$d['nama_kurikulum']}\n";
}

echo "\n=== TEST ANGKATAN LOOKUPS ===\n";
foreach ([2022, 2023, 2024, 2025, 2026, 2027] as $yr) {
    $kid = $m->getKurikulumIdByAngkatan($yr);
    echo "Angkatan $yr → Kurikulum ID: " . ($kid ?? 'NULL') . "\n";
}

echo "\n=== TEST CPL RESOLUTION (LaporanModel) ===\n";
require __DIR__ . '/../app/Models/LaporanModel.php';
$lm = new LaporanModel();
// Test with a sample mahasiswa (ID:7, NIM:2024001, angkatan 2024)
$kid = $lm->getKurikulumIdForMahasiswa(7);
echo "Mahasiswa ID:7 (NIM:2024001) → Kurikulum ID: " . ($kid ?? 'NULL') . "\n";

echo "\n=== DONE ===\n";
