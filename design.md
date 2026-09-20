# Design Specification

## Sistem Informasi OBE & Pengukuran Pemenuhan CPL

**Versi:** 1.0\
**Referensi:** PRD v1.0\
**Stack:** PHP Native + MySQL + HTML/CSS/JavaScript\
**Visual Direction:** DarkPro-style dashboard + shadcn-inspired
component language

------------------------------------------------------------------------

## 1. Design Goals

Desain antarmuka harus: 1. memudahkan admin mengelola master OBE yang
kompleks; 2. memperjelas hierarki
`MK → CPL → CPMK → Sub-CPMK → indikator → rubrik`; 3. membedakan data
master yang terkunci dan data RPS yang editable; 4. membuat proses
penilaian rubrik cepat untuk banyak mahasiswa; 5. menampilkan
ketercapaian CPL secara ringkas tetapi dapat di-drill-down; 6. menjaga
konsistensi UI pada seluruh modul; 7. mendukung dark/light content area
dengan sidebar bergaya DarkPro.

------------------------------------------------------------------------

## 2. Design Principles

### 2.1 Single Source of Truth

Data CPL, CPMK, Sub-CPMK, indikator, dan rubrik yang muncul pada RPS
harus berasal dari master, bukan diketik ulang.

### 2.2 Locked vs Editable

Komponen master diberi indikator visual **Locked**. Komponen RPS yang
dapat diubah menggunakan kontrol CRUD normal.

### 2.3 Traceability First

Setiap angka CPL pada dashboard harus dapat diklik untuk melihat sumber
perhitungannya.

### 2.4 Progressive Disclosure

Detail teknis tidak ditampilkan sekaligus. Gunakan tabs, accordion,
drawer, dialog, dan drill-down.

### 2.5 Consistent Feedback

-   Toast: keberhasilan/gagal operasi biasa.
-   SweetAlert2: konfirmasi aksi destruktif/finalisasi.
-   Inline validation: kesalahan form dan bobot.
-   Badge: status workflow.

------------------------------------------------------------------------

## 3. Visual Language

### 3.1 Layout

Struktur utama:

``` text
┌───────────────────────────────────────────────────────────────┐
│ Topbar: Breadcrumb / Search / Notification / User            │
├──────────────────┬────────────────────────────────────────────┤
│ Sidebar          │ Main Content                               │
│                  │                                            │
│ Dashboard        │ Page Header                                │
│ Master Data      │ Stats / Filter / Action                    │
│ Kurikulum        │                                            │
│ RPS              │ Main Card / Table / Form                   │
│ Penilaian        │                                            │
│ CPL & CQI        │                                            │
└──────────────────┴────────────────────────────────────────────┘
```

### 3.2 Sidebar

-   Dark background.
-   Collapsible.
-   Icon + label.
-   Active item jelas.
-   Nested menu untuk Master Data.
-   Tooltip ketika collapsed.

Kelompok menu:

``` text
Dashboard

MASTER DATA
- Kurikulum
- Profil Lulusan
- CPL
- Mata Kuliah
- CPMK
- Sub-CPMK
- Indikator
- Rubrik

RPS
- Generate RPS
- Daftar RPS
- Validasi RPS

PEMBELAJARAN
- Kelas
- Asesmen
- Penilaian

CAPAIAN
- CPL Mahasiswa
- Pemenuhan CPL
- Traceability

EVALUASI
- CQI

SISTEM
- User & Role
- Tahun Akademik
- Audit Log
- Pengaturan
```

------------------------------------------------------------------------

## 4. UI Libraries

### Required

-   **Font Awesome** untuk icon aksi/menu.
-   **Material Symbols** sebagai alternatif icon sistem.
-   **SweetAlert2** untuk confirmation dialog.
-   **Toast library** atau lightweight custom toast untuk notifikasi.
-   **Chart.js** untuk chart.
-   **DataTables** bila tabel membutuhkan sorting/filter/pagination
    kompleks.

### shadcn-inspired

Karena aplikasi menggunakan PHP Native, shadcn/ui React tidak digunakan
secara langsung. Sistem mengadopsi pola visual komponennya melalui
HTML/CSS/JavaScript:

-   Button
-   Card
-   Badge
-   Alert
-   Dialog
-   Drawer
-   Dropdown Menu
-   Tabs
-   Accordion
-   Tooltip
-   Breadcrumb
-   Pagination
-   Skeleton
-   Command/Search
-   Data Table
-   Select
-   Checkbox
-   Radio Group
-   Progress
-   Sheet/Side Panel

------------------------------------------------------------------------

## 5. Typography & Spacing

### Typography

Font utama: **Inter** atau system sans-serif fallback.

Hierarchy: - Page Title: 24--28px / semibold - Section Title: 18--20px /
semibold - Card Title: 14--16px / semibold - Body: 14px - Small/Meta:
12px

### Spacing

Gunakan sistem kelipatan 4px: `4, 8, 12, 16, 20, 24, 32, 40`.

Card radius dan control radius dibuat konsisten, modern, dan tidak
berlebihan.

------------------------------------------------------------------------

## 6. Core Components

### 6.1 Page Header

Berisi: - breadcrumb; - judul; - deskripsi singkat; - primary action.

Contoh:

``` text
Master Data / Mata Kuliah

Mata Kuliah
Kelola mata kuliah pada Kurikulum SI 2026.

[ + Tambah Mata Kuliah ]
```

### 6.2 Status Badge

Status standar: - Draft - Active - Inactive - Archived - Diajukan -
Divalidasi - Disetujui - Tercapai - Belum Tercapai - Belum Diukur -
Perlu Evaluasi

### 6.3 Locked Field

Master data pada RPS ditampilkan dengan: - read-only input/card; - lock
icon; - tooltip: "Data berasal dari Master Kurikulum".

### 6.4 Empty State

Contoh: \> Belum ada CPMK untuk mata kuliah ini. Tambahkan CPMK pada
Master OBE sebelum membuat RPS.

### 6.5 Weight Indicator

Setiap area pembobotan menampilkan:

``` text
Total Bobot     95 / 100%
███████████████████░

⚠ Masih kurang 5%.
```

Saat 100%:

``` text
✓ Total bobot valid: 100%
```

------------------------------------------------------------------------

## 7. Dashboard Design

### 7.1 Admin Dashboard

Top cards: - Total CPL - Total MK - RPS Aktif - Mahasiswa Terukur -
Persentase CPL Tercapai

Section: 1. grafik ketercapaian CPL; 2. CPL perlu evaluasi; 3.
kelengkapan RPS; 4. aktivitas terbaru; 5. warning konfigurasi master.

Contoh:

``` text
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────────┐
│ 9 CPL    │ │ 42 MK    │ │ 38 RPS   │ │ 87% CPL    │
└──────────┘ └──────────┘ └──────────┘ └─────────────┘

Ketercapaian CPL
CPL01 █████████████████░ 82%
CPL02 ████████████████░░ 78%
CPL03 ██████████████░░░░ 71%
```

------------------------------------------------------------------------

## 8. Master Kurikulum UI

### List View

Kolom: - kode; - nama kurikulum; - tahun mulai; - versi; - jumlah CPL; -
jumlah MK; - status; - aksi.

### Detail View

Tabs: 1. Ringkasan 2. Profil Lulusan 3. CPL 4. Mata Kuliah 5. Matriks
CPL--MK 6. Validasi 7. Riwayat Versi

### Activation

Aktivasi kurikulum menggunakan SweetAlert2 karena berdampak pada data
akademik.

------------------------------------------------------------------------

## 9. Mata Kuliah Detail

Header:

``` text
SIF605
Perencanaan Strategis Sistem Informasi

1 SKS • Semester 6 • Wajib
[ Edit ] [ Mapping CPL ] [ Lihat RPS ]
```

Tabs: - Overview - CPL - CPMK - Sub-CPMK - Rubrik - RPS - Riwayat

Gunakan hierarchy tree untuk menunjukkan:

``` text
SIF605
└── CPL04
    ├── CPMK01
    │   ├── SUB-CPMK 01
    │   ├── SUB-CPMK 02
    │   └── SUB-CPMK 03
    └── CPMK02
        ├── SUB-CPMK 04
        ├── SUB-CPMK 05
        ├── SUB-CPMK 06
        └── SUB-CPMK 07
```

------------------------------------------------------------------------

## 10. CPMK/Sub-CPMK Builder

Gunakan split layout:

``` text
┌───────────────────────┬──────────────────────────────────────┐
│ Struktur              │ Detail                               │
│                       │                                      │
│ CPMK01                │ SUB-CPMK 02                          │
│  ├ SUB01              │ Rumusan: ...                         │
│  ├ SUB02  ← selected  │ Bobot: 9%                            │
│  └ SUB03              │ Indikator: 4                         │
│                       │ Rubrik: Analisis Lingkungan           │
│ CPMK02                │                                      │
│  ├ SUB04              │ [ Edit ] [ Kelola Indikator ]        │
│  └ ...                │                                      │
└───────────────────────┴──────────────────────────────────────┘
```

Bobot diperbarui secara real-time pada progress indicator.

------------------------------------------------------------------------

## 11. Rubric Builder

### Structure

Rubrik analitik menggunakan matrix editor:

``` text
Rubrik: Analisis Lingkungan SI

Kriteria             Bobot    Level 1   Level 2   Level 3   Level 4
--------------------------------------------------------------------
Identifikasi         30%      ...       ...       ...       ...
Analisis             30%      ...       ...       ...       ...
Solusi               25%      ...       ...       ...       ...
Penyajian            15%      ...       ...       ...       ...

Total                100%
```

### Interactions

-   tambah/hapus kriteria;
-   tambah/edit level;
-   edit deskriptor;
-   ubah bobot;
-   preview rubrik;
-   validasi 100%;
-   duplicate rubric;
-   archive rubric.

Rubrik yang sudah digunakan pada transaksi penilaian tidak dihapus
fisik.

------------------------------------------------------------------------

## 12. RPS Generator UI

### Step 1 --- Select Course

``` text
Buat RPS

Kurikulum
[ Kurikulum SI 2026                       ▼ ]

Tahun Akademik
[ 2026/2027                               ▼ ]

Mata Kuliah
[ SIF605 - Perencanaan Strategis SI       ▼ ]

[ Generate RPS ]
```

### Step 2 --- Generated RPS

Gunakan tabs/step navigation:

1.  Identitas
2.  CPL & CPMK
3.  Hubungan CPL--Sub-CPMK
4.  Indikator
5.  Aktivitas Pembelajaran
6.  Referensi
7.  Sistem Penilaian
8.  Rubrik
9.  Validasi

Data master ditampilkan read-only.

------------------------------------------------------------------------

## 13. Rincian Aktivitas Pembelajaran

CRUD aktif.

List berbentuk cards/table per pertemuan:

``` text
Pertemuan 03
SUB-CPMK 02

Teknik Analisis Lingkungan
Flipped Learning • TM
100 menit

[ Edit ] [ Duplicate ] [ Delete ] [ ⋮ ]
```

### Form

Fields: - pertemuan; - Sub-CPMK; - bahan kajian/topik; - metode; -
rancangan aktivitas; - durasi; - mode; - media; - referensi.

Sub-CPMK dipilih dari master yang valid untuk MK tersebut.

Support: - add; - edit; - delete; - duplicate; - reorder.

Delete menggunakan SweetAlert2.

------------------------------------------------------------------------

## 14. Assessment Designer

Halaman asesmen menampilkan:

``` text
Proyek Analisis Strategis
Bobot Nilai Akhir: 20%

Mengukur:
✓ SUB-CPMK 05   kontribusi 40%
✓ SUB-CPMK 06   kontribusi 60%

Rubrik:
Analisis Strategis SI
```

Validasi: - asesmen wajib memiliki Sub-CPMK; - mapping hanya ke Sub-CPMK
MK terkait; - bobot kontribusi sesuai aturan; - rubrik harus valid.

------------------------------------------------------------------------

## 15. Rubric Scoring UI

Desain harus cepat digunakan untuk satu kelas.

Header:

``` text
SIF605 • Kelas SI-26-A
Proyek Analisis Strategis
SUB-CPMK 05

Mahasiswa 12 dari 40
```

Student card:

``` text
Budi Santoso • 2600123

Identifikasi Masalah (30%)
○ 1   ○ 2   ○ 3   ● 4

Analisis (30%)
○ 1   ○ 2   ● 3   ○ 4

Solusi (25%)
○ 1   ○ 2   ● 3   ○ 4

Penyajian (15%)
○ 1   ○ 2   ○ 3   ● 4

Nilai: 85.00

[ Sebelumnya ] [ Simpan & Berikutnya ]
```

Klik/hover level menampilkan deskriptor.

Tambahkan mode **table/bulk scoring** bila dosen perlu menilai banyak
mahasiswa lebih cepat.

------------------------------------------------------------------------

## 16. CPL Student Dashboard

Header:

``` text
Budi Santoso
NIM 2600123 • Angkatan 2026 • Kurikulum SI 2026
```

Cards: - CPL terukur - CPL tercapai - CPL belum tercapai - CPL belum
diukur

Chart: - radar chart untuk profil CPL; - bar/progress untuk nilai vs
target.

Table: \| CPL \| Target \| Capaian \| Coverage \| Status \|
\|---\|---:\|---:\|---:\|---\|

Klik baris CPL membuka **Traceability Drawer**.

------------------------------------------------------------------------

## 17. Traceability Drawer

Contoh:

``` text
CPL04 — Capaian 82.4

SIF203 — Basis Data I
  CPMK01 — 84
    SUB01 — 82
      Tugas Normalisasi — 80
      UTS Item 3–8 — 84
    SUB02 — 86
      Proyek Basis Data — 86

SIF605 — Perencanaan Strategis SI
  CPMK02 — 80
    SUB06 — 78
      Proyek Risiko — 78
```

Pengguna dapat terus membuka detail hingga: - asesmen; - kriteria
rubrik; - skor; - bobot; - formula kontribusi.

------------------------------------------------------------------------

## 18. Curriculum CPL Dashboard

Page title: **Pemenuhan CPL Kurikulum SI 2026**

Filters: - angkatan; - tahun akademik; - semester; - CPL; - status.

Summary cards: - mahasiswa terukur; - jumlah CPL; - CPL memenuhi
target; - CPL perlu evaluasi.

Main table: \| CPL \| Target \| Avg \| Mahasiswa Terukur \| % Tercapai
\| Coverage \| Status \|

Klik CPL membuka detail: - tren; - distribusi; - MK pengukur; -
CPMK/Sub-CPMK; - mahasiswa belum memenuhi; - asesmen dengan kontribusi
terkait.

------------------------------------------------------------------------

## 19. CQI Design

CPL yang perlu evaluasi dapat membuat record CQI.

Wizard: 1. Hasil pengukuran 2. Analisis masalah 3. Akar masalah 4.
Rencana tindak lanjut 5. PIC & deadline 6. Monitoring 7. Hasil
re-measurement

Status: `OPEN → IN_PROGRESS → COMPLETED → RE_MEASURED`

------------------------------------------------------------------------

## 20. Toast Standard

Contoh: - `Data berhasil disimpan.` - `RPS berhasil diperbarui.` -
`Nilai berhasil disimpan.` - `Total bobot belum 100%.` -
`Terjadi kesalahan saat menyimpan data.`

Toast tampil singkat dan tidak menghalangi pekerjaan.

------------------------------------------------------------------------

## 21. SweetAlert2 Standard

Digunakan untuk: - delete; - aktivasi kurikulum; - finalisasi RPS; -
membatalkan RPS; - reset nilai; - tindakan irreversible/sensitif.

Contoh:

``` text
Finalisasi RPS?

Setelah difinalisasi, data master pada RPS
tidak dapat diubah tanpa membuat revisi.

[ Batal ] [ Ya, Finalisasi ]
```

------------------------------------------------------------------------

## 22. CRUD Standard

Semua master menggunakan pola:

``` text
[ Search........................ ] [ Filter ] [ + Tambah ]

Kode      Nama                       Status       Aksi
-------------------------------------------------------
CPL01     ...                        Active       ⋮
CPL02     ...                        Active       ⋮
```

Dropdown aksi: - Detail - Edit - Mapping - Duplicate (jika relevan) -
Archive - Delete (hanya jika aman)

Form dapat menggunakan modal untuk data sederhana dan full page/drawer
untuk data kompleks.

------------------------------------------------------------------------

## 23. Responsive Behavior

### Desktop

Sidebar tetap terlihat; tabel lengkap.

### Tablet

Sidebar collapsible; tabel dapat horizontal scroll.

### Mobile

Bukan target utama v1.0, tetapi halaman monitoring harus tetap readable.
Form penilaian menggunakan stacked cards.

------------------------------------------------------------------------

## 24. Accessibility

-   Label pada seluruh form.
-   Focus state jelas.
-   Keyboard navigation untuk dialog/menu.
-   Tooltip bukan satu-satunya sumber informasi.
-   Status tidak hanya dibedakan dengan warna.
-   Contrast dark/light memenuhi keterbacaan.
-   Icon aksi memiliki `aria-label`/title.

------------------------------------------------------------------------

## 25. Frontend Architecture

``` text
public/assets/
├── css/
│   ├── app.css
│   ├── tokens.css
│   ├── components.css
│   └── pages/
├── js/
│   ├── app.js
│   ├── components/
│   ├── modules/
│   │   ├── kurikulum.js
│   │   ├── rps.js
│   │   ├── rubrik.js
│   │   ├── asesmen.js
│   │   └── penilaian.js
│   └── vendor/
└── images/
```

Gunakan reusable PHP partial/components:

``` text
views/components/
├── alert.php
├── badge.php
├── breadcrumb.php
├── button.php
├── card.php
├── data-table.php
├── empty-state.php
├── modal.php
├── pagination.php
├── sidebar.php
├── topbar.php
└── weight-progress.php
```

------------------------------------------------------------------------

## 26. Backend Design

PHP Native menggunakan MVC ringan.

### Controllers

Mengatur HTTP request/response.

### Models/Repositories

Mengakses database menggunakan PDO prepared statements.

### Services

Business logic penting ditempatkan di service, terutama: -
`ObeValidationService` - `CplCalculationService` -
`RpsGeneratorService` - `RubricScoringService` - `AuditService`

Calculation engine **tidak boleh diletakkan langsung di
view/controller**.

------------------------------------------------------------------------

## 27. Suggested Service Boundaries

``` text
KurikulumService
CplMappingService
RpsGeneratorService
RpsValidationService
RubricService
AssessmentService
RubricScoringService
CplCalculationService
CplTraceabilityService
CqiService
AuditService
```

------------------------------------------------------------------------

## 28. Security Design

-   PDO prepared statements.
-   CSRF token pada semua form POST/PUT/DELETE.
-   Session ID regeneration.
-   Password `password_hash()`/`password_verify()`.
-   Role + permission check server-side.
-   Output escaping.
-   Input validation.
-   Transaction DB untuk operasi multi-table.
-   Audit log.
-   Soft delete untuk master yang sudah direferensikan.
-   Jangan percaya hidden field untuk otorisasi/mapping master.

------------------------------------------------------------------------

## 29. Database Transaction Rules

Gunakan transaction untuk: - membuat kurikulum + mapping; - generate
RPS; - finalisasi RPS; - simpan bulk nilai rubrik; - kalkulasi ulang
CPL; - membuat revisi.

Jika salah satu proses gagal, seluruh perubahan terkait harus rollback.

------------------------------------------------------------------------

## 30. Calculation & Traceability Design

Calculation engine harus menyimpan cukup metadata agar nilai dapat
direproduksi.

Untuk hasil kalkulasi, simpan: - mahasiswa; - periode; - sumber MK; -
CPMK; - Sub-CPMK; - asesmen; - nilai; - bobot; - versi konfigurasi; -
waktu kalkulasi.

Dashboard tidak hanya menampilkan angka agregat; sistem harus mampu
membangun kembali jalur sumber nilai.

------------------------------------------------------------------------

## 31. Validation UX

Contoh panel sebelum finalisasi:

``` text
Validasi OBE

✓ CPL tersedia
✓ CPMK tersedia
✓ Seluruh CPMK memiliki Sub-CPMK
✓ Seluruh Sub-CPMK memiliki indikator
✓ Rubrik lengkap
✕ Bobot asesmen baru 95%
✓ Seluruh Sub-CPMK terjadwal

RPS belum dapat difinalisasi.
```

Error dapat diklik untuk membawa pengguna ke section terkait.

------------------------------------------------------------------------

## 32. Audit UI

Audit log: \| Waktu \| User \| Modul \| Aksi \| Record \| Perubahan \|

Detail perubahan menggunakan drawer:

``` text
SUB-CPMK 06
Bobot:
20% → 26%

Diubah oleh: Admin Prodi
```

------------------------------------------------------------------------

## 33. Design Acceptance Criteria

Desain dinyatakan berhasil apabila:

1.  Pengguna dapat membedakan data master terkunci dan data RPS editable
    tanpa penjelasan tambahan.
2.  Admin dapat memahami hierarki MK--CPL--CPMK--Sub-CPMK melalui UI.
3.  Dosen dapat membuat RPS tanpa mengetik ulang CPL/CPMK/Sub-CPMK.
4.  Dosen dapat menilai mahasiswa menggunakan rubrik dengan alur yang
    efisien.
5.  Dashboard dapat menjawab ketercapaian CPL.
6.  Setiap angka CPL dapat di-drill-down hingga asesmen dan skor rubrik.
7.  Sistem memberikan warning sebelum konfigurasi OBE yang tidak valid
    difinalisasi.
8.  Tampilan konsisten menggunakan DarkPro-style layout dan
    shadcn-inspired components.
9.  UI tetap usable untuk tabel akademik yang besar.
10. Desain mendukung tujuan utama produk: **menjawab bagaimana pemenuhan
    CPL dari kurikulum**.
