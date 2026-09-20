-- ============================================================
-- SEED DATA DUMMY - OBE CPL System
-- Jalankan script ini untuk mengisi data uji coba
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TAHUN AKADEMIK (set aktif)
-- ============================================================
UPDATE tahun_akademik SET is_active = 1 WHERE id = 1;
INSERT IGNORE INTO tahun_akademik (id, nama, is_active) VALUES
(2, '2025/2026 Genap', 0),
(3, '2025/2026 Ganjil', 0);

-- ============================================================
-- 2. DOSEN (role_id = 3)
-- ============================================================
-- password = 'password' (bcrypt hash sama dengan admin)
INSERT INTO users (username, password, nama_lengkap, role_id) VALUES
('0708056701', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Budi Santoso, M.Kom.', 3),
('0709078803', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Rahayu, S.Kom., M.T.', 3),
('0710089204', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Prasetyo, M.Kom.', 3),
('0711098505', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dewi Lestari, S.T., M.Cs.', 3);

-- ============================================================
-- 3. MAHASISWA (role_id = 5) - 10 orang
-- ============================================================
INSERT INTO users (username, password, nama_lengkap, role_id) VALUES
('2024001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Fauzi', 5),
('2024002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bella Permata', 5),
('2024003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cahya Nugraha', 5),
('2024004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dinda Ayu Safitri', 5),
('2024005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eko Prasetya', 5),
('2024006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fitri Handayani', 5),
('2024007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gilang Ramadhan', 5),
('2024008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hana Pertiwi', 5),
('2024009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Irfan Hakim', 5),
('2024010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jasmine Putri', 5);

-- ============================================================
-- 4. CPL (kurikulum_id = 1)
-- ============================================================
INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian) VALUES
(1, 'CPL01', 'Mampu menerapkan pengetahuan matematika, sains, dan prinsip rekayasa untuk menyelesaikan permasalahan di bidang sistem informasi.', 'Pengetahuan', 70.00),
(1, 'CPL02', 'Mampu mengidentifikasi, merumuskan, dan menganalisis permasalahan sistem informasi dengan pendekatan komputasi.', 'Keterampilan Umum', 70.00),
(1, 'CPL03', 'Mampu merancang, mengimplementasikan, dan mengevaluasi solusi sistem informasi yang memenuhi kebutuhan pengguna dan organisasi.', 'Keterampilan Khusus', 70.00),
(1, 'CPL04', 'Mampu berkomunikasi secara efektif dalam konteks profesional melalui dokumen teknis maupun presentasi.', 'Keterampilan Umum', 70.00),
(1, 'CPL05', 'Mampu bekerja sama dalam tim multidisiplin dan menunjukkan sikap profesional, etis, serta bertanggung jawab.', 'Sikap', 70.00),
(1, 'CPL06', 'Mampu menerapkan prinsip keamanan informasi dan manajemen risiko dalam pengembangan sistem informasi.', 'Keterampilan Khusus', 70.00);

-- ============================================================
-- 5. PEMETAAN MK-CPL (hanya 3 MK utama untuk demo)
--    MK: SIF101 (id=5), SIF102 (id=6), SIF203 (id=15)
-- ============================================================
-- Ambil ID CPL
SET @cpl1 = (SELECT id FROM cpl WHERE kode='CPL01' AND kurikulum_id=1);
SET @cpl2 = (SELECT id FROM cpl WHERE kode='CPL02' AND kurikulum_id=1);
SET @cpl3 = (SELECT id FROM cpl WHERE kode='CPL03' AND kurikulum_id=1);
SET @cpl4 = (SELECT id FROM cpl WHERE kode='CPL04' AND kurikulum_id=1);
SET @cpl5 = (SELECT id FROM cpl WHERE kode='CPL05' AND kurikulum_id=1);
SET @cpl6 = (SELECT id FROM cpl WHERE kode='CPL06' AND kurikulum_id=1);

-- SIF101 - Pengantar Sistem Informasi -> CPL01, CPL02, CPL04
INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi) VALUES
(5, @cpl1, 30.00), (5, @cpl2, 40.00), (5, @cpl4, 30.00);

-- SIF102 - Dasar Pemrograman -> CPL01, CPL03
INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi) VALUES
(6, @cpl1, 40.00), (6, @cpl3, 60.00);

-- SIF203 - Basis Data I -> CPL02, CPL03, CPL06
INSERT INTO mk_cpl (mk_id, cpl_id, bobot_kontribusi) VALUES
(15, @cpl2, 30.00), (15, @cpl3, 40.00), (15, @cpl6, 30.00);

-- ============================================================
-- 6. CPMK untuk MK: SIF101 (Pengantar Sistem Informasi)
-- ============================================================
INSERT INTO cpmk (mk_id, kode, rumusan) VALUES
(5, 'CPMK-1', 'Mampu menjelaskan konsep dasar sistem informasi dan perannya dalam organisasi.'),
(5, 'CPMK-2', 'Mampu menganalisis komponen-komponen sistem informasi dan keterkaitan antar subsistem.'),
(5, 'CPMK-3', 'Mampu menyusun dokumen analisis kebutuhan sistem informasi sederhana.');

-- Relasi CPMK-CPL
SET @cpmk1 = (SELECT id FROM cpmk WHERE mk_id=5 AND kode='CPMK-1');
SET @cpmk2 = (SELECT id FROM cpmk WHERE mk_id=5 AND kode='CPMK-2');
SET @cpmk3 = (SELECT id FROM cpmk WHERE mk_id=5 AND kode='CPMK-3');

INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES
(@cpmk1, @cpl1), (@cpmk2, @cpl2), (@cpmk3, @cpl4);

-- ============================================================
-- 7. SUB-CPMK untuk CPMK di SIF101
-- ============================================================
INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, bobot, urutan) VALUES
(@cpmk1, 'Sub-CPMK-1.1', 'Mampu mendefinisikan sistem informasi dan komponen-komponennya', 15.00, 1),
(@cpmk1, 'Sub-CPMK-1.2', 'Mampu menjelaskan jenis-jenis sistem informasi (TPS, MIS, DSS, ESS)', 15.00, 2),
(@cpmk2, 'Sub-CPMK-2.1', 'Mampu mengidentifikasi input, proses, dan output pada suatu SI', 20.00, 3),
(@cpmk2, 'Sub-CPMK-2.2', 'Mampu menggambarkan diagram alir data (DFD) tingkat konteks', 20.00, 4),
(@cpmk3, 'Sub-CPMK-3.1', 'Mampu menyusun dokumen SRS (Software Requirements Specification)', 30.00, 5);

-- ============================================================
-- 8. CPMK untuk MK: SIF102 (Dasar Pemrograman)
-- ============================================================
INSERT INTO cpmk (mk_id, kode, rumusan) VALUES
(6, 'CPMK-1', 'Mampu menerapkan konsep dasar pemrograman prosedural (variabel, tipe data, kontrol alir).'),
(6, 'CPMK-2', 'Mampu membuat program sederhana menggunakan fungsi dan array.');

SET @cpmk_dp1 = (SELECT id FROM cpmk WHERE mk_id=6 AND kode='CPMK-1');
SET @cpmk_dp2 = (SELECT id FROM cpmk WHERE mk_id=6 AND kode='CPMK-2');

INSERT INTO cpmk_cpl (cpmk_id, cpl_id) VALUES
(@cpmk_dp1, @cpl1), (@cpmk_dp2, @cpl3);

INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, bobot, urutan) VALUES
(@cpmk_dp1, 'Sub-CPMK-1.1', 'Mampu mendeklarasikan variabel dan menggunakan operator aritmatika', 20.00, 1),
(@cpmk_dp1, 'Sub-CPMK-1.2', 'Mampu mengimplementasikan percabangan (if-else, switch)', 20.00, 2),
(@cpmk_dp1, 'Sub-CPMK-1.3', 'Mampu mengimplementasikan perulangan (for, while, do-while)', 20.00, 3),
(@cpmk_dp2, 'Sub-CPMK-2.1', 'Mampu membuat dan memanggil fungsi/prosedur', 20.00, 4),
(@cpmk_dp2, 'Sub-CPMK-2.2', 'Mampu menggunakan array 1D dan 2D untuk pengelolaan data', 20.00, 5);

-- ============================================================
-- 9. KELAS (menggunakan TA aktif id=1 dan dosen yang ada)
-- ============================================================
SET @dosen_budi = (SELECT id FROM users WHERE username='0708056701');
SET @dosen_siti = (SELECT id FROM users WHERE username='0709078803');
SET @dosen_andi = (SELECT id FROM users WHERE username='0710089204');
SET @dosen_rony = (SELECT id FROM users WHERE username='0707069402');

-- Kelas SIF101 - Pengantar SI
INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id) VALUES
(5, 1, 'A', @dosen_budi);
SET @kelas_sif101 = LAST_INSERT_ID();

-- Tim teaching
INSERT INTO kelas_tim_teaching (kelas_id, dosen_id) VALUES
(@kelas_sif101, @dosen_siti);

-- Kelas SIF102 - Dasar Pemrograman
INSERT INTO kelas (mata_kuliah_id, tahun_akademik_id, nama_kelas, dosen_id) VALUES
(6, 1, 'A', @dosen_andi);
SET @kelas_sif102 = LAST_INSERT_ID();

INSERT INTO kelas_tim_teaching (kelas_id, dosen_id) VALUES
(@kelas_sif102, @dosen_rony);

-- ============================================================
-- 10. PESERTA KELAS (Mahasiswa masuk ke 2 kelas)
-- ============================================================
SET @mhs1 = (SELECT id FROM users WHERE username='2024001');
SET @mhs2 = (SELECT id FROM users WHERE username='2024002');
SET @mhs3 = (SELECT id FROM users WHERE username='2024003');
SET @mhs4 = (SELECT id FROM users WHERE username='2024004');
SET @mhs5 = (SELECT id FROM users WHERE username='2024005');
SET @mhs6 = (SELECT id FROM users WHERE username='2024006');
SET @mhs7 = (SELECT id FROM users WHERE username='2024007');
SET @mhs8 = (SELECT id FROM users WHERE username='2024008');
SET @mhs9 = (SELECT id FROM users WHERE username='2024009');
SET @mhs10 = (SELECT id FROM users WHERE username='2024010');

-- Semua mahasiswa masuk ke Kelas SIF101-A
INSERT INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES
(@kelas_sif101, @mhs1), (@kelas_sif101, @mhs2), (@kelas_sif101, @mhs3),
(@kelas_sif101, @mhs4), (@kelas_sif101, @mhs5), (@kelas_sif101, @mhs6),
(@kelas_sif101, @mhs7), (@kelas_sif101, @mhs8), (@kelas_sif101, @mhs9),
(@kelas_sif101, @mhs10);

-- 8 mahasiswa masuk ke Kelas SIF102-A
INSERT INTO kelas_mahasiswa (kelas_id, mahasiswa_id) VALUES
(@kelas_sif102, @mhs1), (@kelas_sif102, @mhs2), (@kelas_sif102, @mhs3),
(@kelas_sif102, @mhs4), (@kelas_sif102, @mhs5), (@kelas_sif102, @mhs6),
(@kelas_sif102, @mhs7), (@kelas_sif102, @mhs8);

-- ============================================================
-- 11. ASESMEN / NILAI
--     Masukkan nilai untuk kelas SIF101-A pada seluruh Sub-CPMK
-- ============================================================
SET @sub1_1 = (SELECT id FROM sub_cpmk WHERE cpmk_id=@cpmk1 AND kode='Sub-CPMK-1.1');
SET @sub1_2 = (SELECT id FROM sub_cpmk WHERE cpmk_id=@cpmk1 AND kode='Sub-CPMK-1.2');
SET @sub2_1 = (SELECT id FROM sub_cpmk WHERE cpmk_id=@cpmk2 AND kode='Sub-CPMK-2.1');
SET @sub2_2 = (SELECT id FROM sub_cpmk WHERE cpmk_id=@cpmk2 AND kode='Sub-CPMK-2.2');
SET @sub3_1 = (SELECT id FROM sub_cpmk WHERE cpmk_id=@cpmk3 AND kode='Sub-CPMK-3.1');

-- Nilai Sub-CPMK 1.1 (Definisi SI)
INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES
(@kelas_sif101, @mhs1, @sub1_1, 85.00),
(@kelas_sif101, @mhs2, @sub1_1, 78.00),
(@kelas_sif101, @mhs3, @sub1_1, 92.00),
(@kelas_sif101, @mhs4, @sub1_1, 70.00),
(@kelas_sif101, @mhs5, @sub1_1, 65.00),
(@kelas_sif101, @mhs6, @sub1_1, 88.00),
(@kelas_sif101, @mhs7, @sub1_1, 55.00),
(@kelas_sif101, @mhs8, @sub1_1, 90.00),
(@kelas_sif101, @mhs9, @sub1_1, 73.00),
(@kelas_sif101, @mhs10, @sub1_1, 81.00);

-- Nilai Sub-CPMK 1.2 (Jenis-jenis SI)
INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES
(@kelas_sif101, @mhs1, @sub1_2, 80.00),
(@kelas_sif101, @mhs2, @sub1_2, 75.00),
(@kelas_sif101, @mhs3, @sub1_2, 88.00),
(@kelas_sif101, @mhs4, @sub1_2, 60.00),
(@kelas_sif101, @mhs5, @sub1_2, 72.00),
(@kelas_sif101, @mhs6, @sub1_2, 85.00),
(@kelas_sif101, @mhs7, @sub1_2, 50.00),
(@kelas_sif101, @mhs8, @sub1_2, 91.00),
(@kelas_sif101, @mhs9, @sub1_2, 68.00),
(@kelas_sif101, @mhs10, @sub1_2, 77.00);

-- Nilai Sub-CPMK 2.1 (Input Proses Output)
INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES
(@kelas_sif101, @mhs1, @sub2_1, 90.00),
(@kelas_sif101, @mhs2, @sub2_1, 82.00),
(@kelas_sif101, @mhs3, @sub2_1, 95.00),
(@kelas_sif101, @mhs4, @sub2_1, 68.00),
(@kelas_sif101, @mhs5, @sub2_1, 74.00),
(@kelas_sif101, @mhs6, @sub2_1, 86.00),
(@kelas_sif101, @mhs7, @sub2_1, 60.00),
(@kelas_sif101, @mhs8, @sub2_1, 88.00),
(@kelas_sif101, @mhs9, @sub2_1, 71.00),
(@kelas_sif101, @mhs10, @sub2_1, 83.00);

-- Nilai Sub-CPMK 2.2 (DFD)
INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES
(@kelas_sif101, @mhs1, @sub2_2, 75.00),
(@kelas_sif101, @mhs2, @sub2_2, 80.00),
(@kelas_sif101, @mhs3, @sub2_2, 85.00),
(@kelas_sif101, @mhs4, @sub2_2, 55.00),
(@kelas_sif101, @mhs5, @sub2_2, 62.00),
(@kelas_sif101, @mhs6, @sub2_2, 78.00),
(@kelas_sif101, @mhs7, @sub2_2, 45.00),
(@kelas_sif101, @mhs8, @sub2_2, 82.00),
(@kelas_sif101, @mhs9, @sub2_2, 67.00),
(@kelas_sif101, @mhs10, @sub2_2, 76.00);

-- Nilai Sub-CPMK 3.1 (SRS)
INSERT INTO asesmen_nilai (kelas_id, mahasiswa_id, sub_cpmk_id, nilai) VALUES
(@kelas_sif101, @mhs1, @sub3_1, 82.00),
(@kelas_sif101, @mhs2, @sub3_1, 76.00),
(@kelas_sif101, @mhs3, @sub3_1, 90.00),
(@kelas_sif101, @mhs4, @sub3_1, 65.00),
(@kelas_sif101, @mhs5, @sub3_1, 70.00),
(@kelas_sif101, @mhs6, @sub3_1, 84.00),
(@kelas_sif101, @mhs7, @sub3_1, 52.00),
(@kelas_sif101, @mhs8, @sub3_1, 87.00),
(@kelas_sif101, @mhs9, @sub3_1, 69.00),
(@kelas_sif101, @mhs10, @sub3_1, 79.00);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Seed data berhasil dimasukkan!' AS result;
