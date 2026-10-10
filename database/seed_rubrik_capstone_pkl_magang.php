<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/RubrikModel.php';

$db = Database::getInstance();
$nama = 'Rubrik Kompetensi Proyek dan Dunia Kerja';

$check = $db->prepare('SELECT id FROM rubrik WHERE nama = :nama LIMIT 1');
$check->execute(['nama' => $nama]);
$existingId = $check->fetchColumn();
if ($existingId) {
    echo "Rubrik sudah tersedia dengan ID {$existingId}.\n";
    exit(0);
}

$levels = [
    ['nama' => 'Belum Memadai', 'skor' => 25],
    ['nama' => 'Berkembang', 'skor' => 50],
    ['nama' => 'Kompeten', 'skor' => 75],
    ['nama' => 'Unggul', 'skor' => 100],
];

$kriterias = [
    ['nama' => 'Analisis masalah dan kebutuhan pemangku kepentingan', 'bobot' => 15],
    ['nama' => 'Penerapan kompetensi teknis dan standar profesi', 'bobot' => 20],
    ['nama' => 'Kualitas solusi, produk, atau hasil kerja', 'bobot' => 15],
    ['nama' => 'Perencanaan dan pengelolaan pekerjaan/proyek', 'bobot' => 10],
    ['nama' => 'Kolaborasi, kontribusi, dan tanggung jawab tim', 'bobot' => 10],
    ['nama' => 'Komunikasi profesional dan dokumentasi', 'bobot' => 10],
    ['nama' => 'Etika, disiplin, keselamatan, dan budaya kerja', 'bobot' => 10],
    ['nama' => 'Adaptabilitas, inisiatif, inovasi, dan refleksi', 'bobot' => 10],
];

$descriptors = [
    [
        'Belum mampu mengidentifikasi masalah, kebutuhan, dan batasan utama secara tepat.',
        'Mengidentifikasi sebagian masalah dan kebutuhan, tetapi analisis belum didukung bukti yang memadai.',
        'Menganalisis masalah, kebutuhan, dan batasan berdasarkan data serta mengonfirmasi hasilnya kepada pemangku kepentingan.',
        'Menghasilkan analisis mendalam, terukur, dan berwawasan yang mengantisipasi kebutuhan serta risiko pemangku kepentingan.',
    ],
    [
        'Penerapan pengetahuan dan keterampilan teknis belum memenuhi prosedur atau standar dasar.',
        'Mampu menjalankan pekerjaan teknis dengan arahan intensif dan masih terdapat kesalahan mendasar.',
        'Menerapkan metode, alat, dan standar profesi secara tepat dengan supervisi minimal.',
        'Menunjukkan penguasaan teknis tinggi, memilih pendekatan terbaik, dan meningkatkan praktik kerja tim/organisasi.',
    ],
    [
        'Hasil kerja tidak berfungsi atau tidak menjawab kebutuhan utama.',
        'Hasil kerja berfungsi sebagian, tetapi mutu, ketuntasan, atau validasinya belum konsisten.',
        'Hasil kerja berfungsi baik, memenuhi kebutuhan, teruji, dan memiliki bukti kualitas yang memadai.',
        'Hasil kerja sangat andal, bernilai guna tinggi, tervalidasi menyeluruh, dan siap diterapkan atau dikembangkan.',
    ],
    [
        'Tidak memiliki rencana kerja yang jelas dan sering melewati target tanpa pengendalian.',
        'Rencana kerja tersedia tetapi estimasi, prioritas, risiko, atau pemantauannya belum konsisten.',
        'Mengelola ruang lingkup, waktu, sumber daya, risiko, dan perubahan secara terstruktur.',
        'Mengelola proyek secara proaktif, adaptif, terukur, serta mampu menjaga mutu dan target dalam situasi kompleks.',
    ],
    [
        'Kontribusi rendah, sulit bekerja sama, atau tidak menuntaskan tanggung jawab.',
        'Berpartisipasi dalam tim tetapi komunikasi dan tindak lanjut tanggung jawab belum konsisten.',
        'Berkolaborasi efektif, menghargai peran, menyelesaikan tugas, dan membantu pencapaian tim.',
        'Menjadi penggerak kolaborasi, menyelesaikan konflik secara konstruktif, dan meningkatkan kinerja tim.',
    ],
    [
        'Komunikasi tidak jelas dan dokumentasi penting tidak tersedia atau tidak dapat ditelusuri.',
        'Informasi pokok tersampaikan, tetapi struktur, ketepatan, audiens, atau dokumentasinya masih lemah.',
        'Berkomunikasi jelas dan profesional serta menghasilkan dokumentasi lengkap, akurat, dan mudah ditelusuri.',
        'Menyampaikan gagasan kompleks secara persuasif kepada berbagai audiens dan menghasilkan dokumentasi berstandar profesional.',
    ],
    [
        'Melanggar disiplin, etika, kerahasiaan, keselamatan, atau ketentuan tempat kerja.',
        'Memahami aturan tetapi penerapannya belum konsisten dan masih memerlukan pengawasan.',
        'Menunjukkan integritas, disiplin, tanggung jawab, kepatuhan, dan perilaku profesional secara konsisten.',
        'Menjadi teladan etika dan budaya kerja serta aktif menjaga keamanan, keselamatan, dan kepentingan organisasi.',
    ],
    [
        'Pasif menghadapi perubahan, tidak mencari solusi, dan tidak melakukan refleksi atas hasil kerja.',
        'Mulai menerima umpan balik dan mencoba perbaikan, tetapi inisiatif dan refleksinya masih terbatas.',
        'Adaptif, proaktif mencari solusi, memanfaatkan umpan balik, dan menyusun refleksi serta rencana pengembangan diri.',
        'Menghasilkan inovasi bernilai, cepat belajar dari konteks baru, dan mengubah refleksi menjadi perbaikan berkelanjutan yang terukur.',
    ],
];

$model = new RubrikModel();
$rubrikId = $model->create($nama, 'RB-ANA', 'ACTIVE', $levels, $kriterias, $descriptors);
if (!$rubrikId) {
    fwrite(STDERR, "Gagal membuat rubrik.\n");
    exit(1);
}

$description = 'Rubrik analitik terpadu untuk Capstone Project, PKL, dan Magang/Praktik Kerja dengan penekanan pada kompetensi teknis, kualitas hasil, pengelolaan proyek, kolaborasi, komunikasi, etika profesi, budaya kerja, adaptabilitas, inovasi, dan refleksi.';
$update = $db->prepare('UPDATE rubrik SET deskripsi = :deskripsi, contoh_penggunaan = :contoh WHERE id = :id');
$update->execute([
    'deskripsi' => $description,
    'contoh' => 'Capstone Project, PKL, Magang, Praktik Kerja, dan proyek berbasis kebutuhan mitra',
    'id' => $rubrikId,
]);

echo "Rubrik berhasil dibuat dengan ID {$rubrikId}.\n";
