<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Running migration for Notifikasi...\n";

$db->exec("
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` varchar(30) DEFAULT 'info',
  `icon` varchar(50) DEFAULT 'fa-solid fa-bell',
  `tautan` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "- Tabel notifikasi siap.\n";

// Populate sample notifications if empty
$count = $db->query("SELECT COUNT(*) FROM notifikasi")->fetchColumn();
if ($count == 0) {
    echo "- Mengisi notifikasi awal sistem OBE...\n";
    $items = [
        [
            'judul' => 'Pemetaan CPL ke PL Kurikulum 2022',
            'pesan' => 'Capaian Pembelajaran Lulusan (CPL) berhasil dititipkan ke Profil Lulusan Kurikulum 2022.',
            'tipe' => 'success',
            'icon' => 'fa-solid fa-graduation-cap',
            'tautan' => '/profil-lulusan',
            'is_read' => 0
        ],
        [
            'judul' => 'Sinkronisasi Data Dosen Homebase SI',
            'pesan' => 'Data 3 dosen homebase Sistem Informasi (Rusina Widha, Agung Budi, Rony Kriswibowo) telah terverifikasi.',
            'tipe' => 'info',
            'icon' => 'fa-solid fa-chalkboard-user',
            'tautan' => '/dosen',
            'is_read' => 0
        ],
        [
            'judul' => 'Pembaruan Data Mahasiswa 2022 - 2025',
            'pesan' => 'Seluruh data mahasiswa angkatan 2022 sampai dengan 2025 telah dimutakhirkan.',
            'tipe' => 'info',
            'icon' => 'fa-solid fa-users',
            'tautan' => '/mahasiswa',
            'is_read' => 0
        ],
        [
            'judul' => 'Semester Aktif Berjalan',
            'pesan' => 'Tahun Akademik aktif saat ini adalah 2026/2027 Ganjil.',
            'tipe' => 'warning',
            'icon' => 'fa-solid fa-calendar-check',
            'tautan' => '/tahun-akademik',
            'is_read' => 0
        ],
    ];

    $stmt = $db->prepare("
        INSERT INTO notifikasi (user_id, judul, pesan, tipe, icon, tautan, is_read) 
        VALUES (NULL, :judul, :pesan, :tipe, :icon, :tautan, :is_read)
    ");
    foreach ($items as $it) {
        $stmt->execute($it);
    }
    echo "- " . count($items) . " notifikasi awal berhasil ditambahkan.\n";
}

echo "Migrasi notifikasi selesai!\n";
