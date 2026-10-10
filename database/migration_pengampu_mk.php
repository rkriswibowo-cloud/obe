<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$db->exec("CREATE TABLE IF NOT EXISTS mata_kuliah_pengampu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    tahun_akademik_id INT NOT NULL,
    pjmk_user_id INT NOT NULL,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mk_ta (mata_kuliah_id, tahun_akademik_id),
    KEY idx_pengampu_pjmk (pjmk_user_id),
    CONSTRAINT fk_pengampu_mk FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    CONSTRAINT fk_pengampu_ta FOREIGN KEY (tahun_akademik_id) REFERENCES tahun_akademik(id),
    CONSTRAINT fk_pengampu_pjmk FOREIGN KEY (pjmk_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->exec("CREATE TABLE IF NOT EXISTS mata_kuliah_pengampu_tim (
    pengampu_id INT NOT NULL,
    dosen_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pengampu_id, dosen_id),
    CONSTRAINT fk_pengampu_tim_master FOREIGN KEY (pengampu_id) REFERENCES mata_kuliah_pengampu(id) ON DELETE CASCADE,
    CONSTRAINT fk_pengampu_tim_dosen FOREIGN KEY (dosen_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Jadikan penugasan kelas yang sudah ada sebagai data awal tanpa mengubah histori kelas.
$db->exec("INSERT IGNORE INTO mata_kuliah_pengampu(mata_kuliah_id,tahun_akademik_id,pjmk_user_id,status)
    SELECT mata_kuliah_id,tahun_akademik_id,MIN(dosen_id),'ACTIVE'
    FROM kelas GROUP BY mata_kuliah_id,tahun_akademik_id");
$db->exec("INSERT IGNORE INTO mata_kuliah_pengampu_tim(pengampu_id,dosen_id)
    SELECT DISTINCT pm.id,ktt.dosen_id
    FROM kelas_tim_teaching ktt
    JOIN kelas kl ON kl.id=ktt.kelas_id
    JOIN mata_kuliah_pengampu pm ON pm.mata_kuliah_id=kl.mata_kuliah_id AND pm.tahun_akademik_id=kl.tahun_akademik_id
    WHERE ktt.dosen_id<>pm.pjmk_user_id");

echo "Migrasi pengampu mata kuliah berhasil.\n";
