<?php
/**
 * Audit read-only untuk rantai bisnis utama OBE.
 * Exit code 1 menandakan sedikitnya satu pelanggaran integritas.
 */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/KhsModel.php';

$db = Database::getInstance();
$aturan = new AturanKurikulumModel();
$khs = new KhsModel();
$failures = [];

function checkCount(PDO $db, string $name, string $sql, array &$failures): void {
    $count = (int)$db->query($sql)->fetchColumn();
    echo sprintf("%-38s %s\n", $name, $count === 0 ? 'OK' : "GAGAL ({$count})");
    if ($count !== 0) {
        $failures[] = "{$name}: {$count}";
    }
}

echo "=== INTEGRITAS RELASI AKADEMIK ===\n";
checkCount($db, 'Pendaftaran beda kurikulum', "
    SELECT COUNT(*)
    FROM kelas_mahasiswa km
    JOIN users u ON u.id = km.mahasiswa_id
    JOIN kelas k ON k.id = km.kelas_id
    JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
    WHERE mk.kurikulum_id <> (
        SELECT ak.kurikulum_id
        FROM aturan_kurikulum ak
        WHERE ak.angkatan_mulai <= (
                CASE
                    WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                    WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                    ELSE NULL
                END
              )
          AND (ak.angkatan_selesai IS NULL OR ak.angkatan_selesai >= (
                CASE
                    WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
                    WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
                    ELSE NULL
                END
              ))
        ORDER BY ak.prioritas DESC, ak.id DESC LIMIT 1
    )
", $failures);
checkCount($db, 'Nilai tanpa peserta kelas', "
    SELECT COUNT(*) FROM asesmen_nilai an
    LEFT JOIN kelas_mahasiswa km
      ON km.kelas_id = an.kelas_id AND km.mahasiswa_id = an.mahasiswa_id
    WHERE km.kelas_id IS NULL
", $failures);
checkCount($db, 'Nilai beda mata kuliah', "
    SELECT COUNT(*) FROM asesmen_nilai an
    JOIN kelas k ON k.id = an.kelas_id
    JOIN sub_cpmk sc ON sc.id = an.sub_cpmk_id
    JOIN cpmk cp ON cp.id = sc.cpmk_id
    WHERE cp.mk_id <> k.mata_kuliah_id
", $failures);
checkCount($db, 'Mapping MK-CPL beda kurikulum', "
    SELECT COUNT(*) FROM mk_cpl mc
    JOIN mata_kuliah mk ON mk.id = mc.mk_id
    JOIN cpl c ON c.id = mc.cpl_id
    WHERE mk.kurikulum_id <> c.kurikulum_id
", $failures);
checkCount($db, 'Mapping CPMK-CPL beda kurikulum', "
    SELECT COUNT(*) FROM cpmk_cpl cc
    JOIN cpmk cp ON cp.id = cc.cpmk_id
    JOIN mata_kuliah mk ON mk.id = cp.mk_id
    JOIN cpl c ON c.id = cc.cpl_id
    WHERE mk.kurikulum_id <> c.kurikulum_id
", $failures);
checkCount($db, 'Mata kuliah tanpa CPL', "
    SELECT COUNT(*) FROM mata_kuliah mk
    LEFT JOIN mk_cpl mc ON mc.mk_id = mk.id
    WHERE mk.status = 'ACTIVE' AND mc.mk_id IS NULL
", $failures);
checkCount($db, 'Mata kuliah tanpa CPMK', "
    SELECT COUNT(*) FROM mata_kuliah mk
    LEFT JOIN cpmk cp ON cp.mk_id = mk.id
    WHERE mk.status = 'ACTIVE' AND cp.id IS NULL
", $failures);
checkCount($db, 'CPMK tanpa Sub-CPMK', "
    SELECT COUNT(*) FROM cpmk cp
    LEFT JOIN sub_cpmk sc ON sc.cpmk_id = cp.id
    WHERE sc.id IS NULL
", $failures);

echo "\n=== ALUR KHS DAN CPL PER ANGKATAN ===\n";
$angkatanList = $db->query("
    SELECT DISTINCT
        CASE
            WHEN u.username REGEXP '^(19|20)[0-9]{2}' THEN CAST(SUBSTRING(u.username, 1, 4) AS UNSIGNED)
            WHEN u.username REGEXP '^[0-9]{2}' THEN 2000 + CAST(SUBSTRING(u.username, 1, 2) AS UNSIGNED)
            ELSE NULL
        END AS angkatan
    FROM users u JOIN roles r ON r.id = u.role_id
    WHERE r.name = 'Mahasiswa' AND u.username REGEXP '^[0-9]{2}'
    HAVING angkatan IS NOT NULL
    ORDER BY angkatan
")->fetchAll(PDO::FETCH_COLUMN);

foreach ($angkatanList as $angkatan) {
    $stmt = $db->prepare("
        SELECT u.id, u.username FROM users u JOIN roles r ON r.id = u.role_id
        WHERE r.name = 'Mahasiswa'
          AND (
              u.username LIKE :prefix_four
              OR (u.username NOT REGEXP '^(19|20)[0-9]{2}' AND u.username LIKE :prefix_two)
          )
        ORDER BY u.username LIMIT 1
    ");
    $stmt->execute([
        'prefix_four' => $angkatan . '%',
        'prefix_two' => substr((string)$angkatan, -2) . '%'
    ]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $kurikulumId = $aturan->getKurikulumIdByAngkatan((int)$angkatan);
    if (!$student || !$kurikulumId) {
        $failures[] = "Angkatan {$angkatan} tidak memiliki mahasiswa/aturan";
        continue;
    }

    $khsData = $khs->getKhsMahasiswa((int)$student['id'], null, $kurikulumId);
    $cplData = $khs->getCplMahasiswa((int)$student['id']);
    $measured = count(array_filter($cplData['cpl_list'], static fn($cpl) => $cpl['is_measured']));
    $maxSemester = AturanKurikulumModel::getMaxSemesterByAngkatan((int)$angkatan);
    $mustBeComplete = $maxSemester >= 8;
    $cplValid = $measured > 0
        && (!$mustBeComplete || $measured === count($cplData['cpl_list']));
    $valid = count($khsData['semua_mk']) > 0
        && $khsData['ignored_enrollment_count'] === 0
        && $cplValid;
    echo sprintf(
        "%d | semester<=%d | kurikulum=%d | sampel=%s | MK=%d | CPL=%d/%d | %s\n",
        $angkatan,
        $maxSemester,
        $kurikulumId,
        $student['username'],
        count($khsData['semua_mk']),
        $measured,
        count($cplData['cpl_list']),
        $valid ? 'OK' : 'GAGAL'
    );
    if (!$valid) {
        $failures[] = "Alur KHS/CPL angkatan {$angkatan}";
    }
}

echo "\n=== HASIL ===\n";
if ($failures) {
    echo 'GAGAL: ' . implode('; ', $failures) . "\n";
    exit(1);
}
echo "SELURUH PEMERIKSAAN ALUR BISNIS LULUS.\n";
