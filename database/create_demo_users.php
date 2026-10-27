<?php
// database/create_demo_users.php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();

    echo "=== Membuat Akun Demo OBE ===\n";

    // Daftar role referensi:
    // 1: Super Admin
    // 2: Admin Prodi
    // 3: Dosen
    // 4: Kaprodi
    // 5: Mahasiswa

    $demoUsers = [
        [
            'username' => 'demo',
            'password' => 'demo123',
            'nama_lengkap' => 'Demo User (Full Access)',
            'email' => 'demo@obe.ac.id',
            'role_id' => 1,
            'prodi_id' => 1
        ],
        [
            'username' => 'demo_admin',
            'password' => 'password123',
            'nama_lengkap' => 'Demo Super Admin',
            'email' => 'demo_admin@obe.ac.id',
            'role_id' => 1,
            'prodi_id' => 1
        ],
        [
            'username' => 'demo_adminprodi',
            'password' => 'password123',
            'nama_lengkap' => 'Demo Admin Prodi (SI)',
            'email' => 'demo_adminprodi@obe.ac.id',
            'role_id' => 2,
            'prodi_id' => 1
        ],
        [
            'username' => 'demo_kaprodi',
            'password' => 'password123',
            'nama_lengkap' => 'Demo Kaprodi (SI)',
            'email' => 'demo_kaprodi@obe.ac.id',
            'role_id' => 4,
            'prodi_id' => 1
        ],
        [
            'username' => 'demo_dosen',
            'password' => 'password123',
            'nama_lengkap' => 'Demo Dosen Pengampu',
            'email' => 'demo_dosen@obe.ac.id',
            'role_id' => 3,
            'prodi_id' => 1
        ],
        [
            'username' => 'demo_mhs',
            'password' => 'password123',
            'nama_lengkap' => 'Demo Mahasiswa',
            'email' => 'demo_mhs@obe.ac.id',
            'role_id' => 5,
            'prodi_id' => 1
        ]
    ];

    foreach ($demoUsers as $user) {
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$user['username']]);
        $existing = $check->fetch();

        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

        if ($existing) {
            $stmt = $db->prepare("UPDATE users SET password = ?, nama_lengkap = ?, email = ?, role_id = ?, prodi_id = ? WHERE id = ?");
            $stmt->execute([
                $hashedPassword,
                $user['nama_lengkap'],
                $user['email'],
                $user['role_id'],
                $user['prodi_id'],
                $existing['id']
            ]);
            $userId = $existing['id'];
            echo "[UPDATE] {$user['username']} ({$user['nama_lengkap']})\n";
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, email, role_id, prodi_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['username'],
                $hashedPassword,
                $user['nama_lengkap'],
                $user['email'],
                $user['role_id'],
                $user['prodi_id']
            ]);
            $userId = $db->lastInsertId();
            echo "[INSERT] {$user['username']} ({$user['nama_lengkap']})\n";
        }

        // Jika demo_kaprodi, set sebagai kaprodi di program_studi ID 1 jika belum
        if ($user['role_id'] == 4) {
            $updateProdi = $db->prepare("UPDATE program_studi SET kaprodi_user_id = ? WHERE id = 1");
            $updateProdi->execute([$userId]);
            echo "  -> Assigned sebagai Kaprodi Prodi SI (id: 1)\n";
        }
    }

    echo "\n=== Berhasil Membuat/Memperbarui Semua Akun Demo! ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
