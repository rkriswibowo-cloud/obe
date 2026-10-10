<?php
/**
 * Migrasi data KHS angkatan 2024 ke kurikulum yang ditentukan oleh
 * aturan_kurikulum.
 *
 * Skrip ini idempotent dan hanya menyentuh mahasiswa dengan NIM 2024xxxx.
 * Data pendaftaran serta nilai lintas-kurikulum dicadangkan sebelum dihapus.
 */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/AturanKurikulumModel.php';

$db = Database::getInstance();
$angkatan = 2024;
$aturanModel = new AturanKurikulumModel();
$kurikulumId = $aturanModel->getKurikulumIdByAngkatan($angkatan);

if (!$kurikulumId) {
    throw new RuntimeException("Tidak ada aturan kurikulum untuk angkatan {$angkatan}.");
}

$stmtKurikulum = $db->prepare('SELECT kode, nama FROM kurikulum WHERE id = ?');
$stmtKurikulum->execute([$kurikulumId]);
$kurikulum = $stmtKurikulum->fetch(PDO::FETCH_ASSOC);
if (!$kurikulum) {
    throw new RuntimeException("Kurikulum ID {$kurikulumId} tidak ditemukan.");
}

$mahasiswa = $db->query("
    SELECT u.id, u.username
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'Mahasiswa'
      AND u.username LIKE '2024%'
    ORDER BY u.id
")->fetchAll(PDO::FETCH_ASSOC);

if (!$mahasiswa) {
    throw new RuntimeException('Mahasiswa angkatan 2024 tidak ditemukan.');
}

$mahasiswaIds = array_map('intval', array_column($mahasiswa, 'id'));
$studentPlaceholders = implode(',', array_fill(0, count($mahasiswaIds), '?'));

echo "Migrasi KHS angkatan {$angkatan} -> {$kurikulum['nama']} (ID {$kurikulumId})\n";
echo 'Mahasiswa: ' . count($mahasiswaIds) . "\n";

// Backup satu kali agar data awal dapat dipulihkan jika diperlukan.
$db->exec('CREATE TABLE IF NOT EXISTS backup_khs_2024_kelas_mahasiswa LIKE kelas_mahasiswa');
$db->exec('CREATE TABLE IF NOT EXISTS backup_khs_2024_asesmen_nilai LIKE asesmen_nilai');

$backupEnrollSql = "
    INSERT IGNORE INTO backup_khs_2024_kelas_mahasiswa
    SELECT km.*
    FROM kelas_mahasiswa km
    JOIN kelas k ON k.id = km.kelas_id
    JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
    WHERE km.mahasiswa_id IN ({$studentPlaceholders})
      AND mk.kurikulum_id <> ?
";
$stmt = $db->prepare($backupEnrollSql);
$stmt->execute([...$mahasiswaIds, $kurikulumId]);

$backupNilaiSql = "
    INSERT IGNORE INTO backup_khs_2024_asesmen_nilai
    SELECT an.*
    FROM asesmen_nilai an
    JOIN kelas k ON k.id = an.kelas_id
    JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
    WHERE an.mahasiswa_id IN ({$studentPlaceholders})
      AND mk.kurikulum_id <> ?
";
$stmt = $db->prepare($backupNilaiSql);
$stmt->execute([...$mahasiswaIds, $kurikulumId]);

// Tahun akademik tiap semester mengikuti data kelas yang sudah berjalan.
$semesterTa = [];
$rows = $db->query("
    SELECT mk.semester, MIN(k.tahun_akademik_id) AS ta_id
    FROM kelas k
    JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
    GROUP BY mk.semester
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $semesterTa[(int)$row['semester']] = (int)$row['ta_id'];
}

$fallbackTaIds = $db->query('SELECT id FROM tahun_akademik ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
$dosenIds = $db->query("
    SELECT u.id FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'Dosen'
    ORDER BY u.id
")->fetchAll(PDO::FETCH_COLUMN);

if (!$dosenIds) {
    throw new RuntimeException('Data dosen tidak tersedia untuk pembuatan kelas.');
}

$mataKuliah = $db->prepare("
    SELECT id, kode, nama, semester
    FROM mata_kuliah
    WHERE kurikulum_id = ? AND status = 'ACTIVE'
    ORDER BY semester, kode, id
");
$mataKuliah->execute([$kurikulumId]);
$mataKuliah = $mataKuliah->fetchAll(PDO::FETCH_ASSOC);

$findKelas = $db->prepare("
    SELECT id FROM kelas
    WHERE mata_kuliah_id = ? AND tahun_akademik_id = ? AND nama_kelas = 'A'
    LIMIT 1
");
$insertKelas = $db->prepare("
    INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id)
    VALUES (?, ?, 'A', ?)
");
$insertEnroll = $db->prepare("
    INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES (?, ?)
");
$getSubCpmk = $db->prepare("
    SELECT sc.id
    FROM sub_cpmk sc
    JOIN cpmk cp ON cp.id = sc.cpmk_id
    WHERE cp.mk_id = ?
    ORDER BY cp.id, sc.urutan, sc.id
");
$insertNilai = $db->prepare("
    INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
");

$kelasCreated = 0;
$enrollmentsCreated = 0;
$scoresWritten = 0;

try {
    $db->beginTransaction();

    foreach ($mataKuliah as $mkIndex => $mk) {
        $semester = (int)$mk['semester'];
        $taId = $semesterTa[$semester] ?? ($fallbackTaIds[$semester - 1] ?? null);
        if (!$taId) {
            throw new RuntimeException("Tahun akademik untuk semester {$semester} tidak tersedia.");
        }

        $findKelas->execute([(int)$mk['id'], $taId]);
        $kelasId = $findKelas->fetchColumn();
        if (!$kelasId) {
            $dosenId = (int)$dosenIds[$mkIndex % count($dosenIds)];
            $insertKelas->execute([(int)$mk['id'], $taId, $dosenId]);
            $kelasId = (int)$db->lastInsertId();
            $kelasCreated++;
        } else {
            $kelasId = (int)$kelasId;
        }

        $getSubCpmk->execute([(int)$mk['id']]);
        $subCpmkIds = array_map('intval', $getSubCpmk->fetchAll(PDO::FETCH_COLUMN));
        if (!$subCpmkIds) {
            throw new RuntimeException("Sub-CPMK {$mk['kode']} belum tersedia.");
        }

        foreach ($mahasiswa as $mhs) {
            $mhsId = (int)$mhs['id'];
            $insertEnroll->execute([$kelasId, $mhsId]);
            $enrollmentsCreated += $insertEnroll->rowCount();

            // Mengikuti pola nilai simulasi bawaan seed_all_semesters.php.
            $baseScore = $mhsId === 7 ? 86 : 76 + (($mhsId * 3) % 12);
            foreach ($subCpmkIds as $subIndex => $subCpmkId) {
                $variation = (($subIndex * 3 + (int)$mk['id']) % 9) - 4;
                $score = min(98, max(72, $baseScore + $variation));
                $insertNilai->execute([$kelasId, $mhsId, $subCpmkId, $score]);
                $scoresWritten++;
            }
        }
    }

    // Hapus data aktif lintas-kurikulum setelah data pengganti lengkap.
    $deleteNilaiSql = "
        DELETE an FROM asesmen_nilai an
        JOIN kelas k ON k.id = an.kelas_id
        JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
        WHERE an.mahasiswa_id IN ({$studentPlaceholders})
          AND mk.kurikulum_id <> ?
    ";
    $stmt = $db->prepare($deleteNilaiSql);
    $stmt->execute([...$mahasiswaIds, $kurikulumId]);
    $nilaiLamaDihapus = $stmt->rowCount();

    $deleteEnrollSql = "
        DELETE km FROM kelas_mahasiswa km
        JOIN kelas k ON k.id = km.kelas_id
        JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
        WHERE km.mahasiswa_id IN ({$studentPlaceholders})
          AND mk.kurikulum_id <> ?
    ";
    $stmt = $db->prepare($deleteEnrollSql);
    $stmt->execute([...$mahasiswaIds, $kurikulumId]);
    $enrollmentLamaDihapus = $stmt->rowCount();

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $e;
}

echo "Kelas kurikulum dibuat: {$kelasCreated}\n";
echo "Pendaftaran baru: {$enrollmentsCreated}\n";
echo "Nilai ditulis: {$scoresWritten}\n";
echo "Pendaftaran kurikulum salah dihapus: {$enrollmentLamaDihapus}\n";
echo "Nilai kurikulum salah dihapus: {$nilaiLamaDihapus}\n";
echo "Backup: backup_khs_2024_kelas_mahasiswa, backup_khs_2024_asesmen_nilai\n";
echo "Migrasi selesai.\n";
