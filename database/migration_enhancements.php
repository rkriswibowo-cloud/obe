<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "Running migrations for missing components...\n";

// 1. Profil Lulusan: pastikan ada kolom nama & keterangan jika belum ada
$cols = $db->query("SHOW COLUMNS FROM profil_lulusan LIKE 'nama'")->fetchAll();
if (empty($cols)) {
    $db->exec("ALTER TABLE profil_lulusan ADD COLUMN `nama` varchar(100) NOT NULL AFTER `kode`");
    echo "- Kolom 'nama' ditambahkan ke profil_lulusan\n";
}

// 2. Tabel Pivot pl_cpl (Profil Lulusan <-> CPL)
$db->exec("
CREATE TABLE IF NOT EXISTS `pl_cpl` (
  `pl_id` int(11) NOT NULL,
  `cpl_id` int(11) NOT NULL,
  PRIMARY KEY (`pl_id`,`cpl_id`),
  KEY `idx_plcpl_cpl` (`cpl_id`),
  CONSTRAINT `plcpl_pl_fk` FOREIGN KEY (`pl_id`) REFERENCES `profil_lulusan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plcpl_cpl_fk` FOREIGN KEY (`cpl_id`) REFERENCES `cpl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "- Tabel pl_cpl dipastikan ada\n";

// 3. Tabel Audit Logs
$db->exec("
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `modul` varchar(50) NOT NULL,
  `aksi` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_modul` (`modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "- Tabel audit_logs dipastikan ada\n";

// 4. Seed Profil Lulusan jika masih kosong
$countPl = $db->query("SELECT COUNT(*) FROM profil_lulusan")->fetchColumn();
if ($countPl == 0) {
    echo "- Menyiapkan initial data Profil Lulusan...\n";
    $plData = [
        [
            'kode' => 'PL01',
            'nama' => 'System Analyst & Solution Architect',
            'deskripsi' => 'Mampu menganalisis kebutuhan proses bisnis organisasi, merancang arsitektur sistem informasi yang skalabel, dan menyusun spesifikasi kebutuhan perangkat lunak secara profesional.',
            'cpls' => ['CPL01', 'CPL02', 'CPL03']
        ],
        [
            'kode' => 'PL02',
            'nama' => 'Database Administrator & Data Specialist',
            'deskripsi' => 'Mampu merancang, mengimplementasikan, dan mengelola basis data enterprise yang aman, handal, serta mengoptimalkan integritas data dan analitik informasi.',
            'cpls' => ['CPL02', 'CPL04']
        ],
        [
            'kode' => 'PL03',
            'nama' => 'IS Project Manager & IT Leader',
            'deskripsi' => 'Mampu memimpin, mengoordinasikan, dan mengelola proyek pengembangan sistem informasi menggunakan metodologi terstruktur, etika profesional, dan tata kelola TI.',
            'cpls' => ['CPL04', 'CPL05']
        ],
        [
            'kode' => 'PL04',
            'nama' => 'Enterprise System (ERP) Specialist',
            'deskripsi' => 'Mampu mengkustomisasi, mengintegrasikan, dan mengevaluasi sistem enterprise (ERP/CRM/SCM) untuk mendukung keunggulan operasional dan strategi korporat.',
            'cpls' => ['CPL02', 'CPL03', 'CPL05']
        ],
        [
            'kode' => 'PL05',
            'nama' => 'IT Consultant & Cybersecurity Technopreneur',
            'deskripsi' => 'Mampu memberikan konsultasi strategis TI, tata kelola keamanan informasi & kepatuhan risiko siber, serta mengembangkan solusi digital inovatif bernilai bisnis.',
            'cpls' => ['CPL05', 'CPL06']
        ],
    ];

    $stmtPl = $db->prepare("INSERT INTO profil_lulusan (kurikulum_id, kode, nama, deskripsi) VALUES (1, :kode, :nama, :deskripsi)");
    $stmtPlCpl = $db->prepare("INSERT INTO pl_cpl (pl_id, cpl_id) VALUES (:pl_id, :cpl_id) ON DUPLICATE KEY UPDATE pl_id = VALUES(pl_id)");

    $cplsDb = $db->query("SELECT id, kode FROM cpl WHERE kurikulum_id = 1")->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($plData as $p) {
        $stmtPl->execute([
            'kode' => $p['kode'],
            'nama' => $p['nama'],
            'deskripsi' => $p['deskripsi']
        ]);
        $plId = $db->lastInsertId();

        foreach ($p['cpls'] as $cplKode) {
            $cplId = array_search($cplKode, $cplsDb);
            if ($cplId !== false) {
                $stmtPlCpl->execute(['pl_id' => $plId, 'cpl_id' => $cplId]);
            }
        }
    }
    echo "  -> Berhasil membuat 5 Profil Lulusan & relasi CPL!\n";
}

// 5. Seed initial audit log jika kosong
$countAudit = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
if ($countAudit == 0) {
    $db->exec("
        INSERT INTO audit_logs (user_id, username, modul, aksi, deskripsi, ip_address) VALUES 
        (1, 'admin', 'SISTEM', 'INISIALISASI', 'Inisialisasi sistem OBE, kurikulum, dan pemetaan CPL', '127.0.0.1'),
        (1, 'admin', 'RPS', 'UPDATE', 'Penyusunan RPS Rencana Pembelajaran Semester PSSI', '127.0.0.1'),
        (1, 'admin', 'ASESMEN', 'INPUT', 'Input nilai asesmen mahasiswa kelas SIF102', '127.0.0.1')
    ");
    echo "- Initial audit logs seeded\n";
}

echo "Migrasi & Penyiapan Selesai!\n";
