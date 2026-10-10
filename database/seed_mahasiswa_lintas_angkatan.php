<?php
/**
 * Menambahkan tiga mahasiswa per angkatan untuk mendemonstrasikan aturan
 * dua kurikulum. Angkatan 2024 sengaja tidak disentuh.
 *
 * Angkatan yang dibuat: 2022, 2023, 2025, 2026, dan 2027.
 * Password akun seed: password
 */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Models/AturanKurikulumModel.php';

$db = Database::getInstance();
$aturanModel = new AturanKurikulumModel();

$cohorts = [
    2022 => ['Aditya Pranata', 'Bunga Maharani', 'Candra Wijaya'],
    2023 => ['Dimas Saputra', 'Elsa Nuraini', 'Farhan Akbar'],
    2025 => ['Gita Maharani', 'Hendra Setiawan', 'Intan Permata'],
    2026 => ['Jovan Kurnia', 'Keisya Ramadhani', 'Lukman Hakim'],
    2027 => ['Maya Larasati', 'Naufal Rizky', 'Olivia Anindya'],
];

$roleId = (int)$db->query("SELECT id FROM roles WHERE name = 'Mahasiswa' LIMIT 1")->fetchColumn();
if (!$roleId) {
    throw new RuntimeException('Role Mahasiswa tidak ditemukan.');
}

$prodiId = $db->query('SELECT id FROM program_studi ORDER BY id LIMIT 1')->fetchColumn();
$passwordHash = password_hash('password', PASSWORD_DEFAULT);

$findUser = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$insertUser = $db->prepare("
    INSERT INTO users (username, password, nama_lengkap, prodi_id, role_id)
    VALUES (?, ?, ?, ?, ?)
");
$updateUser = $db->prepare("
    UPDATE users SET nama_lengkap = ?, prodi_id = ?, role_id = ? WHERE id = ?
");
$getCourses = $db->prepare("
    SELECT id, kode, semester
    FROM mata_kuliah
    WHERE kurikulum_id = ? AND status = 'ACTIVE'
    ORDER BY semester, kode, id
");
$findClass = $db->prepare("
    SELECT id FROM kelas WHERE mata_kuliah_id = ? ORDER BY id LIMIT 1
");
$insertClass = $db->prepare("
    INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id)
    VALUES (?, ?, 'A', ?)
");
$insertEnrollment = $db->prepare("
    INSERT IGNORE INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES (?, ?)
");
$getSubCpmk = $db->prepare("
    SELECT sc.id
    FROM sub_cpmk sc
    JOIN cpmk cp ON cp.id = sc.cpmk_id
    WHERE cp.mk_id = ?
    ORDER BY cp.id, sc.urutan, sc.id
");
$upsertScore = $db->prepare("
    INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)
");

$semesterTa = [];
foreach ($db->query("
    SELECT mk.semester, MIN(k.tahun_akademik_id) AS ta_id
    FROM kelas k
    JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
    GROUP BY mk.semester
")->fetchAll(PDO::FETCH_ASSOC) as $row) {
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
    throw new RuntimeException('Data dosen tidak tersedia.');
}

$createdUsers = 0;
$createdClasses = 0;
$createdEnrollments = 0;
$writtenScores = 0;
$summary = [];

try {
    $db->beginTransaction();

    foreach ($cohorts as $year => $names) {
        $kurikulumId = $aturanModel->getKurikulumIdByAngkatan($year);
        if (!$kurikulumId) {
            throw new RuntimeException("Aturan kurikulum angkatan {$year} tidak ditemukan.");
        }

        $getCourses->execute([$kurikulumId]);
        $courses = $getCourses->fetchAll(PDO::FETCH_ASSOC);
        if (!$courses) {
            throw new RuntimeException("Mata kuliah kurikulum ID {$kurikulumId} tidak tersedia.");
        }

        $students = [];
        foreach ($names as $index => $name) {
            $username = sprintf('%d%03d', $year, $index + 1);
            $findUser->execute([$username]);
            $userId = $findUser->fetchColumn();
            if (!$userId) {
                $insertUser->execute([$username, $passwordHash, $name, $prodiId ?: null, $roleId]);
                $userId = (int)$db->lastInsertId();
                $createdUsers++;
            } else {
                $userId = (int)$userId;
                $updateUser->execute([$name, $prodiId ?: null, $roleId, $userId]);
            }
            $students[] = ['id' => $userId, 'index' => $index, 'username' => $username];
        }

        foreach ($courses as $courseIndex => $course) {
            $courseId = (int)$course['id'];
            $findClass->execute([$courseId]);
            $classId = $findClass->fetchColumn();
            if (!$classId) {
                $semester = (int)$course['semester'];
                $taId = $semesterTa[$semester] ?? ($fallbackTaIds[$semester - 1] ?? null);
                if (!$taId) {
                    throw new RuntimeException("Tahun akademik semester {$semester} tidak tersedia.");
                }
                $dosenId = (int)$dosenIds[$courseIndex % count($dosenIds)];
                $insertClass->execute([$courseId, $taId, $dosenId]);
                $classId = (int)$db->lastInsertId();
                $createdClasses++;
            } else {
                $classId = (int)$classId;
            }

            $getSubCpmk->execute([$courseId]);
            $subCpmkIds = array_map('intval', $getSubCpmk->fetchAll(PDO::FETCH_COLUMN));
            if (!$subCpmkIds) {
                throw new RuntimeException("Sub-CPMK {$course['kode']} tidak tersedia.");
            }

            foreach ($students as $student) {
                $insertEnrollment->execute([$classId, $student['id']]);
                $createdEnrollments += $insertEnrollment->rowCount();

                // Tiga profil nilai agar visualisasi CPL tidak identik.
                $baseScore = [78, 84, 90][$student['index']];
                foreach ($subCpmkIds as $subIndex => $subCpmkId) {
                    $variation = (($courseId + $subIndex * 3 + $year) % 9) - 4;
                    $score = min(98, max(70, $baseScore + $variation));
                    $upsertScore->execute([$classId, $student['id'], $subCpmkId, $score]);
                    $writtenScores++;
                }
            }
        }

        $summary[] = [
            'angkatan' => $year,
            'kurikulum_id' => $kurikulumId,
            'mahasiswa' => count($students),
            'mata_kuliah' => count($courses),
        ];
    }

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $e;
}

foreach ($summary as $row) {
    echo "Angkatan {$row['angkatan']}: {$row['mahasiswa']} mahasiswa, "
        . "kurikulum ID {$row['kurikulum_id']}, {$row['mata_kuliah']} MK\n";
}
echo "Mahasiswa baru: {$createdUsers}\n";
echo "Kelas baru: {$createdClasses}\n";
echo "Pendaftaran baru: {$createdEnrollments}\n";
echo "Nilai ditulis: {$writtenScores}\n";
echo "Angkatan 2024 tidak diubah.\n";
