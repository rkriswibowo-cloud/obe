<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/RubrikModel.php';

$db = Database::getInstance();
$master = RubrikModel::getJenisMaster();

$templates = [
    'RB-ANA' => [
        'nama' => 'Rubrik Analitik Proyek, Laporan, Presentasi, dan Praktikum',
        'levels' => [['Sangat Kurang', 25], ['Cukup', 50], ['Baik', 75], ['Sangat Baik', 100]],
        'criteria' => [['Ketepatan konsep dan substansi', 35], ['Proses analisis atau pelaksanaan', 35], ['Kualitas hasil dan komunikasi', 30]],
    ],
    'RB-HOL' => [
        'nama' => 'Rubrik Holistik Presentasi Singkat dan Performa',
        'levels' => [['Belum Memadai', 25], ['Cukup', 50], ['Baik', 75], ['Unggul', 100]],
        'criteria' => [['Kualitas kinerja secara keseluruhan', 100]],
    ],
    'RB-SKL' => [
        'nama' => 'Rating Scale Sikap, Keaktifan, dan Observasi',
        'levels' => [['Sangat Rendah', 20], ['Rendah', 40], ['Cukup', 60], ['Tinggi', 80], ['Sangat Tinggi', 100]],
        'criteria' => [['Keaktifan', 25], ['Kedisiplinan', 25], ['Kerja sama', 25], ['Tanggung jawab', 25]],
    ],
    'RB-CHK' => [
        'nama' => 'Checklist Prosedur Praktikum dan SOP',
        'levels' => [['Tidak Terpenuhi', 0], ['Terpenuhi', 100]],
        'criteria' => [['Persiapan alat dan bahan', 25], ['Kepatuhan terhadap prosedur', 30], ['Keselamatan kerja', 25], ['Dokumentasi hasil', 20]],
    ],
    'RB-SKR' => [
        'nama' => 'Skor Langsung Kuis, UTS, dan UAS',
        'levels' => [['Tidak Menjawab', 0], ['Jawaban Kurang Tepat', 25], ['Jawaban Sebagian Benar', 50], ['Jawaban Hampir Lengkap', 75], ['Jawaban Benar dan Lengkap', 100]],
        'criteria' => [['Ketepatan jawaban terhadap soal', 100]],
    ],
    'RB-KUN' => [
        'nama' => 'Kunci Jawaban Pilihan Ganda',
        'levels' => [['Salah', 0], ['Benar', 100]],
        'criteria' => [['Kesesuaian jawaban dengan kunci', 100]],
    ],
    'RB-CMP' => [
        'nama' => 'Rubrik Kompetensi Uji Keterampilan',
        'levels' => [['Belum Kompeten', 0], ['Kompeten', 100]],
        'criteria' => [['Penguasaan prosedur', 35], ['Ketepatan hasil kerja', 35], ['Keselamatan dan etika kerja', 30]],
    ],
    'RB-PRF' => [
        'nama' => 'Rubrik Portofolio Mahasiswa',
        'levels' => [['Kurang', 25], ['Cukup', 50], ['Baik', 75], ['Sangat Baik', 100]],
        'criteria' => [['Kelengkapan bukti karya', 30], ['Kualitas dan relevansi karya', 30], ['Perkembangan capaian', 25], ['Refleksi mahasiswa', 15]],
    ],
    'RB-PEER' => [
        'nama' => 'Rubrik Peer Assessment Kerja Kelompok',
        'levels' => [['Sangat Kurang', 25], ['Cukup', 50], ['Baik', 75], ['Sangat Baik', 100]],
        'criteria' => [['Kontribusi terhadap tugas', 30], ['Kolaborasi dan komunikasi', 30], ['Tanggung jawab', 25], ['Sikap menghargai rekan', 15]],
    ],
    'RB-SELF' => [
        'nama' => 'Rubrik Self Assessment dan Refleksi Pembelajaran',
        'levels' => [['Belum Berkembang', 25], ['Mulai Berkembang', 50], ['Berkembang Baik', 75], ['Sangat Berkembang', 100]],
        'criteria' => [['Pemahaman capaian diri', 30], ['Kejujuran refleksi', 25], ['Identifikasi kekuatan dan kelemahan', 25], ['Rencana tindak lanjut', 20]],
    ],
];

$preferredIds = ['RB-ANA' => 1, 'RB-HOL' => 2, 'RB-SKL' => 3, 'RB-CHK' => 4];
$db->beginTransaction();
try {
    foreach ($templates as $kode => $template) {
        $meta = $master[$kode];
        $stmt = $db->prepare('SELECT id FROM rubrik WHERE kode_jenis = ? ORDER BY id LIMIT 1');
        $stmt->execute([$kode]);
        $rubrikId = $stmt->fetchColumn();
        if (!$rubrikId && isset($preferredIds[$kode])) {
            $check = $db->prepare('SELECT id FROM rubrik WHERE id = ?');
            $check->execute([$preferredIds[$kode]]);
            $rubrikId = $check->fetchColumn();
        }
        if ($rubrikId) {
            $update = $db->prepare('UPDATE rubrik SET nama=?, kode_jenis=?, jenis=?, karakteristik=?, contoh_penggunaan=?, deskripsi=?, status="ACTIVE" WHERE id=?');
            $update->execute([$template['nama'], $kode, $meta['jenis'], $meta['karakteristik'], $meta['contoh'], $meta['karakteristik'] . '. Contoh penggunaan: ' . $meta['contoh'] . '.', $rubrikId]);
            $db->prepare('DELETE FROM rubrik_level WHERE rubrik_id=?')->execute([$rubrikId]);
            $db->prepare('DELETE FROM rubrik_kriteria WHERE rubrik_id=?')->execute([$rubrikId]);
        } else {
            $insert = $db->prepare('INSERT INTO rubrik (nama,kode_jenis,jenis,karakteristik,contoh_penggunaan,deskripsi,status) VALUES (?,?,?,?,?,?,"ACTIVE")');
            $insert->execute([$template['nama'], $kode, $meta['jenis'], $meta['karakteristik'], $meta['contoh'], $meta['karakteristik'] . '. Contoh penggunaan: ' . $meta['contoh'] . '.']);
            $rubrikId = $db->lastInsertId();
        }

        $levelIds = [];
        $insertLevel = $db->prepare('INSERT INTO rubrik_level (rubrik_id,level_nama,skor) VALUES (?,?,?)');
        foreach ($template['levels'] as $level) {
            $insertLevel->execute([$rubrikId, $level[0], $level[1]]);
            $levelIds[] = $db->lastInsertId();
        }
        $insertCriteria = $db->prepare('INSERT INTO rubrik_kriteria (rubrik_id,kriteria,bobot) VALUES (?,?,?)');
        $insertDescriptor = $db->prepare('INSERT INTO rubrik_deskriptor (kriteria_id,level_id,deskripsi) VALUES (?,?,?)');
        foreach ($template['criteria'] as $criterion) {
            $insertCriteria->execute([$rubrikId, $criterion[0], $criterion[1]]);
            $criterionId = $db->lastInsertId();
            foreach ($template['levels'] as $index => $level) {
                $insertDescriptor->execute([$criterionId, $levelIds[$index], $level[0] . ': capaian pada aspek ' . strtolower($criterion[0]) . '.']);
            }
        }
    }

    // Hapus salinan analitik yang tidak lagi diperlukan dan belum dipakai RPS.
    $duplicates = $db->query("SELECT id FROM rubrik WHERE kode_jenis='RB-ANA' AND id <> (SELECT keep_id FROM (SELECT MIN(id) keep_id FROM rubrik WHERE kode_jenis='RB-ANA') x)")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($duplicates as $duplicateId) {
        $db->prepare('DELETE FROM rubrik WHERE id=?')->execute([$duplicateId]);
    }
    $db->commit();
    echo "Master rubrik berhasil disinkronkan.\n";
} catch (Throwable $e) {
    $db->rollBack();
    throw $e;
}
