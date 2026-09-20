# Product Requirements Document (PRD)

## Sistem Informasi OBE & Pengukuran Pemenuhan CPL

**Versi:** 1.0\
**Status:** Baseline / Locked Architecture\
**Platform:** Web Application\
**Backend:** PHP Native\
**Database:** MySQL\
**Frontend:** HTML5, CSS3, JavaScript\
**Dashboard:** DarkPro-style Admin Dashboard\
**UI Pattern:** shadcn-inspired components untuk PHP Native\
**Icons:** Font Awesome + Material Symbols\
**Notifications:** Toast\
**Confirmation Dialog:** SweetAlert2\
**Charts:** Chart.js\
**Tables:** DataTables (opsional sesuai kebutuhan)

------------------------------------------------------------------------

## 1. Latar Belakang

Program Studi Sistem Informasi telah memiliki dokumen Kurikulum OBE dan
format Rencana Pembelajaran Semester (RPS), tetapi data kurikulum, RPS,
asesmen, rubrik, dan hasil penilaian mahasiswa belum sepenuhnya
terintegrasi.

Permasalahan utama yang hendak diselesaikan bukan sekadar digitalisasi
RPS, melainkan bagaimana sistem dapat memberikan bukti yang terukur dan
dapat ditelusuri mengenai pemenuhan Capaian Pembelajaran Lulusan (CPL)
oleh mahasiswa.

Aplikasi dikembangkan sebagai satu ekosistem OBE yang menghubungkan
desain kurikulum, pelaksanaan pembelajaran, asesmen, rubrik, nilai
mahasiswa, dan evaluasi CPL.

------------------------------------------------------------------------

## 2. Product Vision

Pertanyaan utama yang wajib dapat dijawab aplikasi adalah:

> **"Bagaimana pemenuhan CPL dari kurikulum ini?"**

Sistem harus mampu menjawab pertanyaan tersebut pada tingkat:

1.  mahasiswa individual;
2.  mata kuliah;
3.  semester/periode akademik;
4.  angkatan;
5.  CPL;
6.  kurikulum/program studi.

Setiap nilai CPL harus dapat ditelusuri kembali hingga sumber bukti
asesmennya.

------------------------------------------------------------------------

## 3. Pedoman Arsitektur OBE --- LOCKED

Pedoman berikut menjadi **single source of truth** untuk seluruh
pengembangan:

> **Master Kurikulum menentukan "apa yang harus dicapai": MK → CPL →
> CPMK → Sub-CPMK → indikator → rubrik. RPS menentukan "bagaimana
> pembelajaran dilaksanakan". Asesmen menghasilkan "bukti pencapaian".
> Sistem Penilaian menghitung "seberapa jauh mahasiswa mencapai CPL".**

Pedoman ini tidak boleh diubah dalam implementasi tanpa keputusan
eksplisit pemilik produk.

Alur data utama:

``` text
Kurikulum
  ↓
Mata Kuliah
  ↓
CPL
  ↓
CPMK
  ↓
Sub-CPMK
  ↓
Indikator
  ↓
Rubrik
  ↓
RPS
  ↓
Asesmen
  ↓
Skor/Nilai Mahasiswa
  ↓
Capaian Sub-CPMK
  ↓
Capaian CPMK
  ↓
Capaian CPL per Mata Kuliah
  ↓
Capaian CPL Individual Mahasiswa
  ↓
Rekap CPL Angkatan/Prodi
  ↓
Evaluasi & CQI
```

### 3.1 Prinsip Pengukuran

**Nilai akhir mata kuliah tidak otomatis menjadi nilai CPL.**

Nilai CPL diperoleh dari bukti asesmen yang dipetakan terhadap Sub-CPMK
dan dinilai menggunakan rubrik/aturan penilaian yang telah ditetapkan.

Sistem harus mampu menjelaskan:

> "CPL mahasiswa bernilai 78 berasal dari MK apa, CPMK apa, Sub-CPMK
> apa, asesmen apa, dan skor rubrik apa?"

------------------------------------------------------------------------

## 4. Tujuan Produk

1.  Menjadikan kurikulum OBE sebagai master data terstruktur.
2.  Menjamin sinkronisasi MK, CPL, CPMK, Sub-CPMK, indikator, dan
    rubrik.
3.  Menghasilkan RPS berdasarkan master kurikulum tanpa pengetikan ulang
    data inti.
4.  Memberikan fleksibilitas CRUD pada implementasi pembelajaran di RPS.
5.  Mengelola asesmen sebagai bukti pencapaian Sub-CPMK.
6.  Melaksanakan penilaian berbasis rubrik.
7.  Menghitung ketercapaian Sub-CPMK, CPMK, dan CPL mahasiswa.
8.  Menampilkan dashboard pemenuhan CPL.
9.  Menyediakan traceability dari CPL hingga bukti asesmen.
10. Mendukung evaluasi dan Continuous Quality Improvement (CQI).

------------------------------------------------------------------------

## 5. Ruang Lingkup Sistem

### 5.1 Master Kurikulum

-   Program studi
-   Kurikulum dan versi
-   Profil lulusan
-   CPL
-   Mata kuliah
-   Pemetaan MK--CPL
-   CPMK
-   Pemetaan CPMK--CPL
-   Sub-CPMK
-   Indikator Sub-CPMK
-   Bobot kontribusi
-   Rubrik

### 5.2 RPS Generator

-   Generate RPS dari mata kuliah
-   Identitas MK otomatis
-   CPL otomatis
-   CPMK otomatis
-   Sub-CPMK otomatis
-   Indikator otomatis
-   Rubrik otomatis
-   CRUD rincian aktivitas pembelajaran
-   CRUD bahan kajian/topik
-   CRUD metode/aktivitas
-   CRUD media dan referensi
-   Pengaturan asesmen terkontrol
-   Validasi RPS
-   Versioning/status RPS

### 5.3 Asesmen

-   Jenis asesmen
-   Instrumen asesmen
-   Mapping asesmen--Sub-CPMK
-   Mapping rubrik
-   Bobot asesmen
-   Penugasan asesmen ke kelas

### 5.4 Penilaian

-   Input skor mahasiswa
-   Input berbasis rubrik
-   Kalkulasi nilai asesmen
-   Kalkulasi Sub-CPMK
-   Kalkulasi CPMK
-   Kalkulasi CPL per MK
-   Agregasi CPL individual

### 5.5 Dashboard CPL

-   CPL individual mahasiswa
-   CPL per MK
-   CPL per angkatan
-   CPL per periode
-   CPL program studi
-   Drill-down sumber nilai
-   Status ketercapaian
-   Coverage/pemenuhan pengukuran CPL

### 5.6 CQI

-   Target CPL
-   Hasil capaian
-   Analisis masalah
-   Akar masalah
-   Rencana tindak lanjut
-   PIC
-   Deadline
-   Monitoring
-   Re-measurement

------------------------------------------------------------------------

## 6. Role dan Hak Akses

### 6.1 Super Admin

-   Kelola user dan role
-   Konfigurasi aplikasi
-   Tahun akademik/periode
-   Audit log
-   Konfigurasi sistem

### 6.2 Admin/Prodi

-   CRUD master kurikulum
-   CRUD CPL
-   CRUD MK
-   Mapping MK--CPL
-   CRUD CPMK
-   CRUD Sub-CPMK
-   CRUD indikator
-   CRUD rubrik
-   Aktivasi/arsip kurikulum

### 6.3 Dosen

-   Membuat RPS dari MK
-   CRUD rincian aktivitas pembelajaran
-   Mengelola asesmen sesuai kewenangan
-   Input penilaian mahasiswa
-   Melihat ketercapaian kelas/MK

### 6.4 Kaprodi/Pimpinan

-   Review/validasi RPS
-   Monitoring CPL
-   Dashboard kurikulum
-   Evaluasi dan CQI

### 6.5 Mahasiswa (fase lanjutan)

-   Melihat profil CPL pribadi
-   Melihat status ketercapaian
-   Tidak dapat melihat nilai mahasiswa lain

------------------------------------------------------------------------

## 7. Batas CRUD Master vs RPS

  Komponen             Sumber             CRUD di RPS
  -------------------- ------------------ ---------------------------------
  Mata Kuliah          Master Kurikulum   Tidak
  CPL                  Master Kurikulum   Tidak
  CPMK                 Master OBE         Tidak
  Sub-CPMK             Master OBE         Tidak
  Indikator            Master OBE         Tidak/terbatas sesuai kebijakan
  Rubrik               Master OBE         Tidak
  Pertemuan            RPS                Ya
  Bahan kajian/topik   RPS                Ya
  Aktivitas/metode     RPS                Ya
  Durasi               RPS                Ya
  Mode pembelajaran    RPS                Ya
  Media                RPS                Ya
  Referensi            RPS                Ya
  Asesmen aktual       RPS/Assessment     Ya, terkontrol
  Nilai mahasiswa      Transaksi          Ya, sesuai otorisasi

------------------------------------------------------------------------

## 8. Functional Requirements

### FR-01 Authentication

Sistem harus menyediakan login, logout, session management, role-based
access control, dan password hashing.

### FR-02 Kurikulum

Sistem harus mendukung beberapa versi kurikulum dan mempertahankan
histori mahasiswa terhadap kurikulum yang berlaku saat mahasiswa
tersebut diproses.

Status: - DRAFT - ACTIVE - INACTIVE - ARCHIVED

### FR-03 CPL

Setiap CPL terikat pada satu versi kurikulum dan memiliki kode, rumusan,
kategori, target capaian, serta status.

### FR-04 Mata Kuliah

Setiap MK memiliki kode, nama, SKS teori/praktikum, semester, sifat,
kelompok keilmuan, deskripsi, dan status.

### FR-05 Mapping MK--CPL

Relasi many-to-many harus digunakan. Mapping dapat menyimpan tipe
kontribusi, bobot kontribusi, dan flag apakah CPL benar-benar diukur
pada MK tersebut.

### FR-06 CPMK

CPMK terikat pada MK. Relasi CPMK--CPL disimpan terpisah agar satu CPMK
dapat dipetakan secara eksplisit terhadap CPL.

### FR-07 Sub-CPMK

Setiap Sub-CPMK merupakan record terpisah dan memiliki kode, rumusan,
level taksonomi jika digunakan, bobot, urutan, dan status.

### FR-08 Indikator

Setiap indikator merupakan record terpisah yang terhubung ke Sub-CPMK.

### FR-09 Rubrik

Sistem harus mendukung rubrik analitik dan holistik. Rubrik memiliki
kriteria, bobot, level, deskriptor, dan skor.

### FR-10 RPS Generator

Ketika dosen memilih kurikulum dan MK, sistem harus otomatis
mengambil: - identitas MK; - CPL; - CPMK; - Sub-CPMK; - indikator; -
rubrik.

### FR-11 CRUD Rincian RPS

Dosen dapat menambah, mengubah, menghapus, menduplikasi, dan mengatur
urutan rincian pertemuan tanpa mengubah struktur akademik master.

### FR-12 Asesmen

Asesmen harus dapat dipetakan ke satu atau lebih Sub-CPMK dan dapat
menggunakan rubrik yang telah ditetapkan.

### FR-13 Penilaian Rubrik

Dosen dapat memilih level kinerja setiap kriteria. Sistem menghitung
skor berdasarkan bobot rubrik.

### FR-14 Calculation Engine

Sistem menghitung capaian secara berlapis: 1. rubrik/asesmen; 2.
Sub-CPMK; 3. CPMK; 4. CPL pada MK; 5. CPL mahasiswa.

### FR-15 Dashboard CPL

Sistem menyediakan agregasi, visualisasi, filter, dan drill-down CPL.

### FR-16 Traceability

Setiap capaian CPL harus dapat ditelusuri:
`CPL → MK → CPMK → Sub-CPMK → Asesmen → Rubrik → Skor`.

### FR-17 CQI

Sistem menyediakan pencatatan evaluasi dan tindak lanjut CPL.

### FR-18 Audit Trail

Perubahan penting harus dicatat dengan user, waktu, modul, aksi, record,
nilai lama, dan nilai baru.

------------------------------------------------------------------------

## 9. Calculation Requirements

### 9.1 Nilai Rubrik

Secara konseptual:

`NR = Σ (Skor Kriteria × Bobot Kriteria)`

### 9.2 Nilai Sub-CPMK

Jika satu Sub-CPMK dinilai melalui beberapa asesmen:

`NS = Σ (Nilai Asesmen × Bobot Kontribusi Asesmen)`

### 9.3 Nilai CPMK

`NCPMK = Σ (Nilai Sub-CPMK × Bobot Sub-CPMK yang dinormalisasi pada CPMK)`

### 9.4 Nilai CPL pada MK

`NCPL_MK = Σ (Nilai CPMK × Bobot Kontribusi CPMK terhadap CPL)`

### 9.5 CPL Individual

`NCPL_MHS = agregasi NCPL_MK dari seluruh MK pengukur CPL yang telah ditempuh mahasiswa`

**Catatan:** formula agregasi CPL lintas MK harus dapat dikonfigurasi
dan difinalisasi setelah matriks CPL--MK Kurikulum 2026 dinormalisasi.

### 9.6 Pemisahan Bobot

Sistem wajib membedakan: 1. bobot nilai akhir MK; 2. bobot kontribusi
Sub-CPMK/CPMK terhadap CPL; 3. bobot kontribusi MK terhadap CPL.

Ketiganya tidak boleh diperlakukan sebagai satu jenis bobot.

------------------------------------------------------------------------

## 10. OBE Validation Engine

Sebelum RPS difinalisasi sistem harus memeriksa:

-   MK memiliki CPL.
-   CPL pada MK memiliki CPMK yang relevan.
-   CPMK memiliki Sub-CPMK.
-   Sub-CPMK memiliki indikator.
-   Sub-CPMK memiliki rubrik.
-   Sub-CPMK memiliki bukti asesmen.
-   Bobot yang diwajibkan berjumlah 100%.
-   Bobot nilai akhir MK berjumlah 100%.
-   Seluruh Sub-CPMK terjadwal dalam RPS.
-   Mapping asesmen valid.
-   Tidak ada orphan mapping.
-   RPS menggunakan versi master yang valid.

RPS yang gagal validasi tidak dapat difinalisasi.

------------------------------------------------------------------------

## 11. RPS Workflow

``` text
Draft
  ↓
Diajukan
  ↓
Divalidasi
  ↓
Disetujui
  ↓
Aktif
  ↓
Arsip/Revisi
```

RPS aktif tidak boleh diubah secara substansial tanpa revisi/versioning.

------------------------------------------------------------------------

## 12. Dashboard Utama

Dashboard menampilkan minimal: - jumlah CPL; - jumlah MK; - jumlah
RPS; - jumlah mahasiswa/kelas aktif; - persentase CPL tercapai; - CPL
yang perlu evaluasi; - grafik ketercapaian CPL; - status kelengkapan
RPS; - notifikasi validasi.

------------------------------------------------------------------------

## 13. Dashboard CPL Individual

Menampilkan: - mahasiswa; - angkatan; - kurikulum; - CPL; - target; -
nilai capaian; - status; - coverage pengukuran; - jumlah MK pengukur.

Klik CPL membuka drill-down hingga asesmen dan rubrik.

------------------------------------------------------------------------

## 14. Dashboard Pemenuhan CPL Kurikulum

Filter minimal: - kurikulum; - angkatan; - tahun akademik; - semester; -
CPL; - status ketercapaian.

Tabel minimal: - kode CPL; - target; - rata-rata; - jumlah mahasiswa
terukur; - persentase mahasiswa memenuhi target; - coverage; - status
evaluasi.

------------------------------------------------------------------------

## 15. Database Requirements

-   DBMS: MySQL
-   Engine: InnoDB
-   Charset: utf8mb4
-   Foreign key wajib digunakan
-   Pivot table untuk relasi many-to-many
-   Soft delete untuk data master yang relevan
-   Timestamp `created_at`, `updated_at`, dan opsional `deleted_at`
-   Tidak menggunakan pola kolom `cpl1`, `cpl2`, `cpl3`, dst.
-   Histori transaksi tidak boleh rusak ketika master baru dibuat.
-   Prepared statements/PDO wajib digunakan.

------------------------------------------------------------------------

## 16. Struktur Database Konseptual

Tabel inti:

``` text
users
roles
user_roles

program_studi
tahun_akademik
kurikulum
profil_lulusan
cpl
mata_kuliah
mk_cpl

cpmk
cpmk_cpl
sub_cpmk
sub_cpmk_indikator

rubrik
rubrik_kriteria
rubrik_level
sub_cpmk_rubrik

rps
rps_pertemuan
rps_referensi

asesmen
asesmen_sub_cpmk

kelas_kuliah
mahasiswa
peserta_kelas

nilai_asesmen
nilai_rubrik_detail
nilai_sub_cpmk
nilai_cpmk
nilai_cpl_mk
nilai_cpl_mahasiswa

cqi
audit_logs
```

------------------------------------------------------------------------

## 17. Non-Functional Requirements

### Security

-   PDO prepared statements.
-   Password hashing menggunakan `password_hash()`.
-   CSRF protection untuk form mutasi.
-   Session regeneration setelah login.
-   Authorization pada server side.
-   Validasi upload.
-   Escape output HTML.
-   Audit trail untuk perubahan akademik penting.

### Performance

-   Pagination untuk tabel besar.
-   Index pada foreign key dan field pencarian.
-   Query agregasi dashboard dioptimalkan.
-   Hindari N+1 query.
-   Cache dapat ditambahkan pada fase lanjutan.

### Maintainability

Walaupun PHP Native, kode harus menggunakan struktur MVC ringan dan
separation of concerns.

### Usability

-   Responsive desktop/tablet.
-   Sidebar collapsible.
-   Consistent CRUD pattern.
-   Search/filter cepat.
-   Empty state yang informatif.
-   Feedback setiap operasi.

------------------------------------------------------------------------

## 18. Struktur Aplikasi PHP Native

``` text
obe-cpl/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Validators/
│   ├── Middleware/
│   └── Helpers/
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── migrations/
│   └── seeds/
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── images/
│       └── icons/
├── routes/
│   └── web.php
├── storage/
│   ├── uploads/
│   ├── exports/
│   └── logs/
└── views/
    ├── layouts/
    ├── components/
    ├── dashboard/
    ├── kurikulum/
    ├── cpl/
    ├── mata-kuliah/
    ├── cpmk/
    ├── sub-cpmk/
    ├── rubrik/
    ├── rps/
    ├── asesmen/
    ├── penilaian/
    ├── cpl-mahasiswa/
    └── cqi/
```

------------------------------------------------------------------------

## 19. MVP Scope

Versi 1.0 fokus pada: 1. authentication & role; 2. kurikulum; 3. CPL; 4.
mata kuliah; 5. mapping MK--CPL; 6. CPMK; 7. Sub-CPMK; 8. indikator; 9.
rubrik; 10. RPS Generator; 11. CRUD aktivitas RPS; 12. asesmen; 13.
input nilai/rubrik; 14. calculation engine; 15. dashboard CPL; 16.
traceability.

Fitur CQI penuh, portal mahasiswa, integrasi SIAKAD/API, dan analitik
lanjutan dapat dilanjutkan pada fase berikutnya.

------------------------------------------------------------------------

## 20. Definition of Done

Aplikasi versi pertama dinyatakan memenuhi tujuan apabila sistem dapat
memilih satu mahasiswa dan menjawab:

> **"Berapa capaian setiap CPL mahasiswa ini?"**

Kemudian pengguna dapat melakukan drill-down dan menjawab:

> **"Nilai CPL tersebut berasal dari mana?"**

Sistem wajib dapat menelusuri:

`CPL → MK → CPMK → Sub-CPMK → Asesmen → Rubrik → Skor mahasiswa`.

Pada tingkat prodi, sistem juga harus mampu menjawab:

> **"Bagaimana pemenuhan CPL Kurikulum Sistem Informasi untuk
> mahasiswa/angkatan/periode yang dipilih?"**

dengan data ketercapaian, coverage, sumber MK pengukur, dan bukti
penilaian yang dapat ditelusuri.
