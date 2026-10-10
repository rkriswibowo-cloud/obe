<?php
// app/Models/RpsPertemuanModel.php

require_once __DIR__ . '/../../config/database.php';

class RpsPertemuanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByMkId($mkId) {
        $sql = "
            SELECT rp.*, GROUP_CONCAT(sc.id ORDER BY sc.urutan, sc.id) AS sub_cpmk_ids,
                   GROUP_CONCAT(sc.kode ORDER BY sc.urutan, sc.id SEPARATOR ', ') AS sub_cpmk_kode,
                   GROUP_CONCAT(sc.rumusan ORDER BY sc.urutan, sc.id SEPARATOR ' | ') AS sub_cpmk_rumusan
            FROM rps_pertemuan rp
            LEFT JOIN rps_pertemuan_sub_cpmk rpsc ON rpsc.rps_pertemuan_id = rp.id
            LEFT JOIN sub_cpmk sc ON rpsc.sub_cpmk_id = sc.id
            WHERE rp.mata_kuliah_id = :mk_id
            GROUP BY rp.id
            ORDER BY rp.pertemuan_ke ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mk_id' => $mkId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rps_pertemuan WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "
            INSERT INTO rps_pertemuan 
            (mata_kuliah_id, pertemuan_ke, sub_cpmk_id, bahan_kajian, bentuk_metode, mode_pembelajaran,
             pengalaman_belajar, alokasi_waktu, durasi_detail, pustaka, indikator_penilaian, bobot_penilaian)
            VALUES 
            (:mk_id, :pertemuan_ke, :sub_cpmk_id, :bahan_kajian, :bentuk_metode, :mode_pembelajaran,
             :pengalaman_belajar, :alokasi_waktu, :durasi_detail, :pustaka, :indikator_penilaian, :bobot_penilaian)
        ";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'mk_id' => $data['mata_kuliah_id'],
            'pertemuan_ke' => $data['pertemuan_ke'],
            'sub_cpmk_id' => !empty($data['sub_cpmk_id']) ? $data['sub_cpmk_id'] : null,
            'bahan_kajian' => $data['bahan_kajian'],
            'bentuk_metode' => $data['bentuk_metode'] ?? 'Kuliah & Diskusi',
            'mode_pembelajaran' => $data['mode_pembelajaran'] ?? 'ASM dan TM',
            'pengalaman_belajar' => $data['pengalaman_belajar'] ?? null,
            'alokasi_waktu' => $data['alokasi_waktu'] ?? '2 x 50 menit',
            'durasi_detail' => $data['durasi_detail'] ?? null,
            'pustaka' => $data['pustaka'] ?? null,
            'indikator_penilaian' => $data['indikator_penilaian'] ?? null,
            'bobot_penilaian' => $data['bobot_penilaian'] ?? 0.00
        ]);
        if ($ok) $this->syncSubCpmks((int)$this->db->lastInsertId(), $data['sub_cpmk_ids'] ?? array_filter([$data['sub_cpmk_id'] ?? null]));
        return $ok;
    }

    public function update($id, $data) {
        $sql = "
            UPDATE rps_pertemuan 
            SET pertemuan_ke = :pertemuan_ke,
                sub_cpmk_id = :sub_cpmk_id,
                bahan_kajian = :bahan_kajian,
                bentuk_metode = :bentuk_metode,
                mode_pembelajaran = :mode_pembelajaran,
                pengalaman_belajar = :pengalaman_belajar,
                alokasi_waktu = :alokasi_waktu,
                durasi_detail = :durasi_detail,
                pustaka = :pustaka,
                indikator_penilaian = :indikator_penilaian,
                bobot_penilaian = :bobot_penilaian
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'pertemuan_ke' => $data['pertemuan_ke'],
            'sub_cpmk_id' => !empty($data['sub_cpmk_id']) ? $data['sub_cpmk_id'] : null,
            'bahan_kajian' => $data['bahan_kajian'],
            'bentuk_metode' => $data['bentuk_metode'] ?? 'Kuliah & Diskusi',
            'mode_pembelajaran' => $data['mode_pembelajaran'] ?? 'ASM dan TM',
            'pengalaman_belajar' => $data['pengalaman_belajar'] ?? null,
            'alokasi_waktu' => $data['alokasi_waktu'] ?? '2 x 50 menit',
            'durasi_detail' => $data['durasi_detail'] ?? null,
            'pustaka' => $data['pustaka'] ?? null,
            'indikator_penilaian' => $data['indikator_penilaian'] ?? null,
            'bobot_penilaian' => $data['bobot_penilaian'] ?? 0.00,
            'id' => $id
        ]);
        if ($ok) $this->syncSubCpmks((int)$id, $data['sub_cpmk_ids'] ?? array_filter([$data['sub_cpmk_id'] ?? null]));
        return $ok;
    }

    private function syncSubCpmks($pertemuanId, array $subCpmkIds) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $subCpmkIds))));
        $this->db->prepare("DELETE FROM rps_pertemuan_sub_cpmk WHERE rps_pertemuan_id = ?")->execute([$pertemuanId]);
        $insert = $this->db->prepare("INSERT INTO rps_pertemuan_sub_cpmk (rps_pertemuan_id, sub_cpmk_id) VALUES (?, ?)");
        foreach ($ids as $subCpmkId) $insert->execute([$pertemuanId, $subCpmkId]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rps_pertemuan WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function generateTemplate16($mkId) {
        // Ambil Sub-CPMK yang tersedia untuk MK ini
        $stmtSub = $this->db->prepare("
            SELECT sc.id FROM sub_cpmk sc
            JOIN cpmk c ON sc.cpmk_id = c.id
            WHERE c.mk_id = :mk_id
            ORDER BY sc.urutan ASC, sc.id ASC
        ");
        $stmtSub->execute(['mk_id' => $mkId]);
        $subIds = $stmtSub->fetchAll(PDO::FETCH_COLUMN);

        $templates = [
            1 => [
                'kajian' => 'Pengenalan Mata Kuliah, Kontrak Belajar, RPS, dan Tinjauan Umum Capaian Pembelajaran',
                'metode' => 'Kuliah Interaktif & Diskusi Kelas',
                'pengalaman' => 'Menyimak paparan silabus, berdiskusi mengenai target capaian dan asesmen',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Dokumen Kurikulum & Buku Referensi Utama Bab 1',
                'penilaian' => 'Keaktifan diskusi',
                'bobot' => 0.00
            ],
            2 => [
                'kajian' => 'Konsep Dasar, Terminologi, Prinsip-prinsip Teori dan Kerangka Kerja',
                'metode' => 'Kuliah & Flipped Learning',
                'pengalaman' => 'Membaca modul pra-kelas, merangkum konsep dasar, dan tanya-jawab',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Buku Referensi Utama Bab 2-3',
                'penilaian' => 'Kuis Pendahuluan',
                'bobot' => 5.00
            ],
            3 => [
                'kajian' => 'Metodologi Analisis, Perumusan Masalah, dan Identifikasi Kasus Nyata',
                'metode' => 'Case-Based Learning & Diskusi Kelompok',
                'pengalaman' => 'Menganalisis studi kasus nyata di industri dan merumuskan pokok permasalahan',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Jurnal & Artikel Studi Kasus Terkini',
                'penilaian' => 'Partisipasi dan argumen analisis kasus',
                'bobot' => 5.00
            ],
            4 => [
                'kajian' => 'Teknik Pengumpulan Data, Pemodelan, dan Pendekatan Analitis',
                'metode' => 'Kuliah, Simulasi, & Latihan Mandiri',
                'pengalaman' => 'Melakukan pemodelan masalah menggunakan diagram dan format standar',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Buku Referensi Bab 4 & Panduan Praktik',
                'penilaian' => 'Tugas Pemodelan 1',
                'bobot' => 10.00
            ],
            5 => [
                'kajian' => 'Perancangan Solusi, Alternatif Pendekatan, dan Evaluasi Kelayakan',
                'metode' => 'Problem-Based Learning & Presentasi Mini',
                'pengalaman' => 'Menyusun opsi solusi alternatif dan mengevaluasi kelebihan/kekurangan',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Buku Referensi Bab 5',
                'penilaian' => 'Rubrik Presentasi Alternatif Solusi',
                'bobot' => 5.00
            ],
            6 => [
                'kajian' => 'Penerapan Alat Bantu / Software Pendukung dan Best Practices Industri',
                'metode' => 'Praktik Terbimbing di Lab / Kelas Interaktif',
                'pengalaman' => 'Mengoperasikan tools/perangkat lunak pendukung analisis dan perancangan',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Manual Tools & Software Documentation',
                'penilaian' => 'Unjuk kerja praktik tools',
                'bobot' => 5.00
            ],
            7 => [
                'kajian' => 'Review Komprehensif Materi Pertemuan 1-6 dan Simulasi Evaluasi Tengah Semester',
                'metode' => 'Review Sesi, Diskusi Tanya-Jawab, & Umpan Balik',
                'pengalaman' => 'Mengidentifikasi gap pemahaman dan membahas solusi latihan soal',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Rangkuman Materi Pertemuan 1-6',
                'penilaian' => 'Refleksi mandiri',
                'bobot' => 0.00
            ],
            8 => [
                'kajian' => 'Evaluasi Tengah Semester (UTS) / Asesmen Capaian Pembelajaran Tahap I',
                'metode' => 'Ujian Tertulis / Penilaian Berbasis Proyek Bagian I',
                'pengalaman' => 'Menyelesaikan instrumen evaluasi capaian Sub-CPMK tahap pertama',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Soal & Rubrik Asesmen UTS',
                'penilaian' => 'Nilai UTS (Skor Rubrik/Ujian)',
                'bobot' => 20.00
            ],
            9 => [
                'kajian' => 'Evaluasi Hasil UTS, Pembahasan Solusi, dan Pengantar Topik Tingkat Lanjut',
                'metode' => 'Refleksi Feedback & Kuliah Interaktif',
                'pengalaman' => 'Memperbaiki kesalahan konsep berdasarkan hasil evaluasi UTS',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Buku Referensi Utama Bab 6',
                'penilaian' => 'Tindak lanjut perbaikan',
                'bobot' => 0.00
            ],
            10 => [
                'kajian' => 'Integrasi Sistem, Arsitektur Solusi Lanjutan, dan Analisis Risiko',
                'metode' => 'Kuliah & Diskusi Studi Kasus Lanjutan',
                'pengalaman' => 'Mengidentifikasi risiko implementasi dan strategi mitigasi',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Buku Referensi Bab 7 & Modul Risiko',
                'penilaian' => 'Laporan Analisis Risiko',
                'bobot' => 5.00
            ],
            11 => [
                'kajian' => 'Pengembangan Proyek Kolaboratif: Implementasi Spesifikasi & Rancangan',
                'metode' => 'Project-Based Learning Terbimbing',
                'pengalaman' => 'Bekerja dalam tim menyusun artefak implementasi proyek mata kuliah',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Panduan Proyek Semester & Template Laporan',
                'penilaian' => 'Monitoring progres proyek mingguan',
                'bobot' => 5.00
            ],
            12 => [
                'kajian' => 'Pengujian, Verifikasi, Validasi Kualitas, dan Benchmarking Solusi',
                'metode' => 'Workshop & Peer-Review Antar Kelompok',
                'pengalaman' => 'Menguji solusi yang dibangun dan memberikan tinjauan sejawat',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Standar Uji & Check-list Kualitas',
                'penilaian' => 'Rubrik Peer Review',
                'bobot' => 5.00
            ],
            13 => [
                'kajian' => 'Finalisasi Dokumen Proyek, Penyusunan Laporan Akhir, dan Rekomendasi',
                'metode' => 'Konsultasi & Asistensi Proyek dengan Dosen',
                'pengalaman' => 'Melakukan penyempurnaan dokumen akhir proyek sesuai masukan dosen',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Panduan Format Laporan Akhir',
                'penilaian' => 'Kelengkapan draf laporan akhir',
                'bobot' => 5.00
            ],
            14 => [
                'kajian' => 'Presentasi Proyek Kelompok, Demo Karya/Produk, dan Tanya-Jawab Kritis',
                'metode' => 'Seminar Proyek Kelas & Presentasi Kelompok',
                'pengalaman' => 'Mempresentasikan hasil proyek, mendemonstrasikan prototipe/hasil analisis',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Slide Presentasi & Laporan Proyek',
                'penilaian' => 'Rubrik Presentasi & Komunikasi Ilmiah',
                'bobot' => 10.00
            ],
            15 => [
                'kajian' => 'Sintesis Pembelajaran Semester, Refleksi Capaian CPL/CPMK, & Persiapan UAS',
                'metode' => 'Kuliah Rangkuman & Sesi Tanya-Jawab Komprehensif',
                'pengalaman' => 'Melakukan pemetaan mandiri ketercapaian kompetensi yang telah dipelajari',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Seluruh Buku Pustaka dan Materi Perkuliahan',
                'penilaian' => 'Partisipasi reflektif',
                'bobot' => 0.00
            ],
            16 => [
                'kajian' => 'Evaluasi Akhir Semester (UAS) / Penilaian Portofolio Capaian Pembelajaran',
                'metode' => 'Ujian Akhir Komprehensif / Penyerahan Portofolio Lengkap',
                'pengalaman' => 'Menyelesaikan evaluasi akhir sebagai bukti pemenuhan CPL mata kuliah',
                'waktu' => '2 x 50 menit',
                'pustaka' => 'Soal & Rubrik Asesmen UAS',
                'penilaian' => 'Nilai UAS / Portofolio Akhir',
                'bobot' => 25.00
            ],
        ];

        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM rps_pertemuan WHERE mata_kuliah_id = :mk_id");
        $stmtCheck->execute(['mk_id' => $mkId]);
        $existingCount = $stmtCheck->fetchColumn();

        if ($existingCount > 0) {
            // Sudah ada pertemuan, tidak overwrite kecuali dipanggil
            return false;
        }

        $this->db->beginTransaction();
        try {
            $numSubs = count($subIds);
            foreach ($templates as $pKe => $tpl) {
                // Map sub-CPMK evenly if available
                $subId = null;
                if ($numSubs > 0) {
                    $subIndex = ($pKe <= 8) ? intval(($pKe - 1) / 8 * ($numSubs / 2)) : intval(($pKe - 1) / 16 * $numSubs);
                    if (isset($subIds[$subIndex])) {
                        $subId = $subIds[$subIndex];
                    } else {
                        $subId = $subIds[array_key_last($subIds)];
                    }
                }

                $this->create([
                    'mata_kuliah_id' => $mkId,
                    'pertemuan_ke' => $pKe,
                    'sub_cpmk_id' => $subId,
                    'bahan_kajian' => $tpl['kajian'],
                    'bentuk_metode' => $tpl['metode'],
                    'pengalaman_belajar' => $tpl['pengalaman'],
                    'alokasi_waktu' => $tpl['waktu'],
                    'pustaka' => $tpl['pustaka'],
                    'indikator_penilaian' => $tpl['penilaian'],
                    'bobot_penilaian' => $tpl['bobot']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
