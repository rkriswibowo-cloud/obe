# Sistem Informasi OBE & Pengukuran Pemenuhan CPL

Sistem Informasi berbasis web untuk pengelolaan kurikulum **Outcome-Based Education (OBE)**, penyusunan **Rencana Pembelajaran Semester (RPS)**, pelaksanaan **asesmen berbasis rubrik**, serta pengukuran dan penelusuran (**traceability**) ketercapaian **CPL (Capaian Pembelajaran Lulusan)** mahasiswa secara transparan dan akuntabel.

---

## 📌 Ringkasan Sistem & Prinsip OBE

Aplikasi ini dibangun dengan prinsip:
> **"Master Kurikulum menentukan apa yang harus dicapai (MK → CPL → CPMK → Sub-CPMK → Indikator → Rubrik). RPS menentukan bagaimana pembelajaran dilaksanakan. Asesmen menghasilkan bukti pencapaian. Sistem Penilaian menghitung seberapa jauh mahasiswa mencapai CPL."**

### Prinsip Utama Pengukuran:
- **Nilai akhir mata kuliah tidak otomatis menjadi nilai CPL.**
- Nilai CPL diperoleh dari bukti asesmen yang dipetakan ke Sub-CPMK dan dinilai dengan rubrik/komponen asesmen yang terukur.
- **Traceability Lengkap**: Setiap capaian CPL dapat ditelusuri kembali hingga bukti asesmen, sub-CPMK, CPMK, mata kuliah, dan skor rubriknya.

---

## 🚀 Fitur Utama

1. **Master Kurikulum & OBE**
   - Manajemen Program Studi, Kurikulum (versi kurikulum, profil lulusan, PL-CPL mapping).
   - Pengelolaan Mata Kuliah, CPL, CPMK, Sub-CPMK, dan pemetaan CPMK-CPL.
   - Aturan Kurikulum & Konversi Nilai Akademik (A, AB, B, BC, C, D, E).
   - Fitur Import Kurikulum dari berkas spreadsheet (Excel).

2. **RPS Digital & Pembelajaran**
   - Generator & manajemen RPS terintegrasi langsung dengan Master Kurikulum tanpa input ulang data inti.
   - Pemetaan rencana pertemuan mingguan, bahan kajian, metode pembelajaran, dan teknik asesmen.
   - Master Rubrik Penilaian (Analitik, Holistik, Skala Persepsi, Capstone/PKL/Magang).

3. **Pelaksanaan Pembelajaran & Kelas**
   - Pengelolaan Tahun Akademik, Dosen Pengampu MK, dan Kelas Perkuliahan.
   - Pendaftaran mahasiswa per kelas (Enrolment) & distribusi portofolio penilaian.

4. **Asesmen, Penilaian & Evaluasi CPL**
   - Input nilai berbasis komponen asesmen, Sub-CPMK, dan rubrik.
   - Perhitungan otomatis ketercapaian Sub-CPMK, CPMK, dan CPL per mata kuliah.
   - Perhitungan capaian CPL individual mahasiswa, angkatan, dan program studi.
   - Kartu Hasil Studi (KHS) & Transkrip CPL Mahasiswa.

5. **Laporan & Continuous Quality Improvement (CQI)**
   - Dashboard analitik pemenuhan CPL (visualisasi radar chart dan bar chart).
   - Traceability audit nilai dan bukti asesmen.
   - Rekomendasi perbaikan berkelanjutan (CQI) berdasarkan evaluasi ketercapaian CPL.
   - Rekognisi Pembelajaran Lampau (RPL) & log aktivitas sistem (Audit Log).

---

## 🛠️ Tech Stack

- **Backend**: PHP Native (OOP, MVC Architecture)
- **Database**: MySQL / MariaDB (PDO Prepared Statements)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla ES6+)
- **UI & Dashboard**: DarkPro-style Admin Dashboard, komponen terinspirasi dari shadcn UI
- **Grafik & Visualisasi**: Chart.js
- **Dialog & Notifikasi**: SweetAlert2, Toast UI
- **Icons**: Font Awesome 6 & Material Symbols

---

## 📁 Struktur Direktori

```text
obe/
├── app/
│   ├── Controllers/       # Controller logika bisnis (RPS, Asesmen, CPL, KHS, dll.)
│   ├── Models/            # Data layer & query database
│   └── Support/           # Helper, auth guard, flash message, dll.
├── components/            # Komponen view reusable (navbar, sidebar, card, dll.)
├── config/
│   └── database.php       # Konfigurasi koneksi database (PDO)
├── database/
│   ├── schema.sql         # Skema database utama
│   ├── seed.sql           # Data awal / dummy
│   └── migrations/scripts # Skrip migrasi dan sinkronisasi kurikulum
├── public/                # Static assets (CSS, JS, images, vendor libs)
├── views/                 # Template view UI aplikasi
├── PRD.md                 # Product Requirements Document
├── design.md              # Design system & arsitektur UI/UX
└── index.php              # Front controller / router aplikasi
```

---

## ⚙️ Panduan Instalasi Lokal

### Prasyarat
- Web Server (Apache/Nginx) — contoh: **XAMPP / Laragon**
- **PHP** versi 7.4 ke atas (direkomendasikan PHP 8.x) dengan ekstensi PDO MySQL aktif
- **MySQL / MariaDB**

### Langkah-langkah:
1. **Clone repository ini** ke direktori web server Anda (`htdocs` atau `www`):
   ```bash
   git clone https://github.com/rkriswibowo-cloud/obe.git
   ```

2. **Konfigurasi Database**:
   - Buka phpMyAdmin atau MySQL CLI.
   - Buat database baru bernama `obe_cpl`.
   - Import skema database dari `database/backup_obe_cpl_latest.sql` atau gunakan `database/schema.sql` dan `database/seed.sql`.

3. **Pengaturan Koneksi**:
   - Buka berkas [config/database.php](config/database.php).
   - Sesuaikan kredensial database Anda jika berbeda:
     ```php
     private $host = 'localhost';
     private $user = 'root';
     private $pass = '';
     private $name = 'obe_cpl';
     ```

4. **Jalankan Aplikasi**:
   - Pastikan service Apache dan MySQL pada XAMPP/Laragon berjalan.
   - Buka browser dan akses:
     ```text
     http://localhost/obe/
     ```

---

## 🔒 Lisensi & Hak Cipta

Dikembangkan untuk pengelolaan penjaminan mutu kurikulum dan evaluasi CPL Program Studi Sistem Informasi berbasis OBE.
