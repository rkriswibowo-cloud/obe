<?php
/**
 * Migration: Create aturan_kurikulum table and seed default rules.
 * 
 * Rules:
 *   - Angkatan 2022-2025 → Kurikulum "OBE SI 2022" (ID: 2)
 *   - Angkatan 2026+     → Kurikulum "KURIKULUM OBE 2026" (ID: 1)
 */
require __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=== Migrasi: Aturan Kurikulum ===\n\n";

// 1. Create table
$db->exec("
    CREATE TABLE IF NOT EXISTS aturan_kurikulum (
        id INT AUTO_INCREMENT PRIMARY KEY,
        angkatan_mulai INT NOT NULL COMMENT 'Tahun angkatan mulai berlaku (inklusif)',
        angkatan_selesai INT DEFAULT NULL COMMENT 'Tahun angkatan selesai berlaku (inklusif). NULL = tidak terbatas/berlaku seterusnya',
        kurikulum_id INT NOT NULL,
        keterangan VARCHAR(255) DEFAULT NULL,
        prioritas INT DEFAULT 0 COMMENT 'Semakin tinggi = semakin diprioritaskan',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (kurikulum_id) REFERENCES kurikulum(id) ON DELETE CASCADE,
        UNIQUE KEY uq_angkatan_range (angkatan_mulai, angkatan_selesai)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "[OK] Tabel aturan_kurikulum berhasil dibuat.\n";

// 2. Seed default rules
$checkStmt = $db->query("SELECT COUNT(*) FROM aturan_kurikulum");
$existingCount = (int)$checkStmt->fetchColumn();

if ($existingCount === 0) {
    // Rule 1: Angkatan 2022-2025 → OBE SI 2022 (ID: 2)
    $stmt = $db->prepare("
        INSERT INTO aturan_kurikulum (angkatan_mulai, angkatan_selesai, kurikulum_id, keterangan, prioritas)
        VALUES (:mulai, :selesai, :kid, :ket, :pri)
    ");
    $stmt->execute([
        'mulai' => 2022,
        'selesai' => 2025,
        'kid' => 2,
        'ket' => 'Mahasiswa angkatan 2022–2025 menggunakan kurikulum OBE SI 2022',
        'pri' => 10
    ]);
    echo "[OK] Rule 1: Angkatan 2022-2025 → OBE SI 2022\n";

    // Rule 2: Angkatan 2026+ → KURIKULUM OBE 2026 (ID: 1)
    $stmt->execute([
        'mulai' => 2026,
        'selesai' => null,
        'kid' => 1,
        'ket' => 'Mahasiswa angkatan 2026 ke atas menggunakan kurikulum OBE 2026 (terbaru)',
        'pri' => 20
    ]);
    echo "[OK] Rule 2: Angkatan 2026+ → KURIKULUM OBE 2026\n";
} else {
    echo "[SKIP] Tabel sudah memiliki $existingCount aturan, tidak perlu seed ulang.\n";
}

// 3. Verify
echo "\n=== Verifikasi ===\n";
$rows = $db->query("
    SELECT ak.*, k.nama as nama_kurikulum 
    FROM aturan_kurikulum ak 
    JOIN kurikulum k ON ak.kurikulum_id = k.id 
    ORDER BY ak.angkatan_mulai
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    $range = $r['angkatan_mulai'] . ($r['angkatan_selesai'] ? '–' . $r['angkatan_selesai'] : '+');
    echo "  Angkatan $range → {$r['nama_kurikulum']} (Kurikulum ID: {$r['kurikulum_id']})\n";
}

echo "\n=== Selesai ===\n";
