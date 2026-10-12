<?php
// views/dosen/form.php
$isEdit = isset($dosen);
$title = ($isEdit ? 'Edit Data Dosen' : 'Tambah Data Dosen') . ' - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
$action = $isEdit ? ($baseUrl . '/dosen/update') : ($baseUrl . '/dosen/store');

$currentHomebase = $dosen['homebase'] ?? '';
$isCustom = true;
$standardHomebases = ['S1 Sistem Informasi', 'S1 Informatika', 'D3 Sistem Informasi'];

if (!empty($prodiList)) {
    foreach ($prodiList as $pr) {
        $prodiName = ($pr['jenjang'] ? ($pr['jenjang'] . ' ') : '') . $pr['nama'];
        if (!in_array($prodiName, $standardHomebases, true)) {
            $standardHomebases[] = $prodiName;
        }
    }
}
?>

<style>
    .dosen-form-page {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    /* Header */
    .dosen-form-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .dosen-form-header-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: 8px;
        transition: color .15s ease;
    }
    .dosen-form-header-back:hover {
        color: var(--primary);
    }
    .dosen-form-eyebrow {
        margin: 0 0 5px;
        color: var(--primary);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .dosen-form-title {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 750;
        color: var(--text-main);
        letter-spacing: -.02em;
    }
    .dosen-form-subtitle {
        margin: 6px 0 0;
        color: var(--text-muted);
        font-size: .88rem;
        max-width: 720px;
        line-height: 1.5;
    }

    /* Main Responsive Grid: Form + Info Sidebar */
    .dosen-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.85fr) minmax(320px, 1fr);
        gap: 22px;
        align-items: start;
    }

    /* Card Panels */
    .dosen-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .dosen-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }
    .dosen-card-header h3 {
        margin: 0;
        font-size: .98rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .dosen-card-header h3 i {
        color: var(--primary);
    }
    .dosen-card-header span {
        font-size: .75rem;
        color: var(--text-muted);
    }
    .dosen-card-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    /* Section Subheadings */
    .form-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-subtle);
        margin: 4px 0 2px;
    }
    .form-section-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: rgba(var(--primary-rgb), .12);
        color: var(--primary);
        font-size: .75rem;
        font-weight: 800;
    }
    .form-section-title h4 {
        margin: 0;
        font-size: .88rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .form-section-title p {
        margin: 0;
        margin-left: auto;
        font-size: .74rem;
        color: var(--text-muted);
    }

    /* Form Fields Grid */
    .field-row-equal {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .dosen-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .dosen-field label {
        font-size: .8rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .dosen-field label span.req {
        color: var(--danger, #ef4444);
    }
    .dosen-field .form-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--input-border, var(--border));
        border-radius: var(--radius-sm);
        background: var(--input-bg, var(--surface));
        color: var(--text-main);
        padding: 9px 13px;
        font-size: .85rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dosen-field .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .12);
        outline: none;
    }
    .dosen-field small {
        font-size: .74rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Action Footer */
    .dosen-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        margin-top: 6px;
    }
    .dosen-form-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 20px;
        font-weight: 700;
        font-size: .84rem;
    }

    /* Aside Info Box */
    .dosen-aside {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .info-box {
        padding: 18px 20px;
        border-radius: var(--radius-md);
        background: var(--card-bg);
        border: 1px solid var(--border);
    }
    .info-box h4 {
        margin: 0 0 10px;
        font-size: .88rem;
        font-weight: 750;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-box h4 i {
        color: var(--primary);
    }
    .info-box p {
        margin: 0;
        font-size: .78rem;
        color: var(--text-muted);
        line-height: 1.55;
    }
    .info-checklist {
        margin: 12px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .info-checklist li {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        font-size: .76rem;
        color: var(--text-muted);
        line-height: 1.45;
    }
    .info-checklist li i {
        margin-top: 3px;
        font-size: .72rem;
        flex-shrink: 0;
        color: #10b981;
    }

    .preview-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px;
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .preview-card-avatar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 4px;
    }
    .avatar-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), .12);
        color: var(--primary);
        display: grid;
        place-items: center;
        font-size: 1.05rem;
    }
    .preview-card-title {
        font-size: .88rem;
        font-weight: 750;
        color: var(--text-main);
    }
    .preview-card-sub {
        font-size: .74rem;
        color: var(--text-muted);
        font-family: monospace;
    }

    /* Responsiveness */
    @media (max-width: 960px) {
        .dosen-form-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .field-row-equal {
            grid-template-columns: 1fr;
        }
        .dosen-card-body {
            padding: 18px 16px;
        }
        .dosen-form-footer {
            flex-direction: column-reverse;
            gap: 10px;
        }
        .dosen-form-footer .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="dosen-form-page">
    <header class="dosen-form-header">
        <div>
            <a href="<?= $baseUrl ?>/dosen" class="dosen-form-header-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Dosen
            </a>
            <p class="dosen-eyebrow">Manajemen Tenaga Pendidik</p>
            <h1 class="dosen-form-title"><?= $isEdit ? 'Edit Data Dosen' : 'Tambah Dosen Pengampu Baru' ?></h1>
            <p class="dosen-form-subtitle">
                <?= $isEdit ? 'Perbarui identitas akademik dosen (NIDN, NUPTK, Homebase, serta akun login sistem).' : 'Masukkan identitas akademik dosen untuk penugasan pengampu mata kuliah, tim teaching RPS, dan hak akses asesmen OBE.' ?>
            </p>
        </div>
    </header>

    <div class="dosen-form-grid">
        <!-- Main Form Card -->
        <main class="dosen-card">
            <div class="dosen-card-header">
                <h3><i class="fa-solid fa-chalkboard-user"></i> Formulir Tenaga Pendidik</h3>
                <span>Tanda (<span style="color: var(--danger, #ef4444); font-weight:700">*</span>) wajib diisi</span>
            </div>

            <form action="<?= $action ?>" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int)$dosen['id'] ?>">
                <?php endif; ?>

                <div class="dosen-card-body">
                    <!-- Section 1: Identitas Pribadi & Akademik -->
                    <div class="form-section-title">
                        <span class="form-section-badge">1</span>
                        <h4>Identitas Personal & Gelar Akademik</h4>
                        <p>Data identitas resmi</p>
                    </div>

                    <div class="dosen-field">
                        <label for="namaLengkapInput">Nama Lengkap & Gelar Akademik <span class="req">*</span></label>
                        <input type="text" id="namaLengkapInput" name="nama_lengkap" required value="<?= $isEdit ? htmlspecialchars($dosen['nama_lengkap']) : '' ?>" placeholder="Contoh: Dr. Budi Santoso, S.Kom., M.Kom." class="form-control">
                        <small>Cantumkan gelar akademik lengkap untuk keperluan cetak lembar pengesahan RPS dan Laporan Evaluasi.</small>
                    </div>

                    <div class="field-row-equal">
                        <div class="dosen-field">
                            <label for="nidnInput">NIDN (Nomor Induk Dosen Nasional)</label>
                            <input type="text" id="nidnInput" name="nidn" maxlength="30" value="<?= $isEdit ? htmlspecialchars($dosen['nidn'] ?? '') : '' ?>" placeholder="Contoh: 0707069402" class="form-control" style="font-family: monospace;">
                            <small>Digunakan dalam validasi pimpinan dan pengesahan.</small>
                        </div>

                        <div class="dosen-field">
                            <label for="nuptkInput">NUPTK (Nomor Unik Pendidik)</label>
                            <input type="text" id="nuptkInput" name="nuptk" maxlength="30" value="<?= $isEdit ? htmlspecialchars($dosen['nuptk'] ?? '') : '' ?>" placeholder="Contoh: 9438752654200032" class="form-control" style="font-family: monospace;">
                            <small>Opsional jika belum memiliki NIDN definitif.</small>
                        </div>
                    </div>

                    <!-- Section 2: Homebase Program Studi -->
                    <div class="form-section-title" style="margin-top: 6px;">
                        <span class="form-section-badge">2</span>
                        <h4>Homebase Program Studi</h4>
                        <p>Afiliasi akademik</p>
                    </div>

                    <div class="dosen-field">
                        <label for="homebaseSelect">Program Studi Homebase <span class="req">*</span></label>
                        <select id="homebaseSelect" onchange="window.handleHomebaseSelect(this.value)" class="form-control">
                            <option value="">-- Pilih Homebase Program Studi --</option>
                            <?php 
                            foreach ($standardHomebases as $hb):
                                $selected = ($currentHomebase === $hb);
                                if ($selected) $isCustom = false;
                            ?>
                                <option value="<?= htmlspecialchars($hb) ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($hb) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="__custom__" <?= ($isCustom && !empty($currentHomebase)) ? 'selected' : '' ?>>
                                Lainnya (Ketik Manual)...
                            </option>
                        </select>
                        <input type="text" id="homebaseInput" name="homebase" value="<?= htmlspecialchars($currentHomebase) ?>" required placeholder="Ketik nama prodi / instansi homebase..." class="form-control" style="margin-top: 8px; <?= ($isCustom && !empty($currentHomebase)) ? 'display: block;' : 'display: none;' ?>">
                        <small>Menentukan hak asesmen mata kuliah dan kepemimpinan di tingkat program studi.</small>
                    </div>

                    <!-- Section 3: Akun Login Sistem -->
                    <div class="form-section-title" style="margin-top: 6px;">
                        <span class="form-section-badge">3</span>
                        <h4>Akun Akses & Autentikasi</h4>
                        <p>Kredensial login portal</p>
                    </div>

                    <div class="dosen-field">
                        <label for="usernameInput">Username / NIP / NIDN (Akun Login) <span class="req">*</span></label>
                        <input type="text" id="usernameInput" name="username" required value="<?= $isEdit ? htmlspecialchars($dosen['username']) : '' ?>" placeholder="Contoh: 0707069402" class="form-control" style="font-family: monospace;">
                        <?php if (!$isEdit): ?>
                            <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Password default untuk akun dosen baru otomatis disamakan dengan username (dapat diperbarui setelah login).
                            </small>
                        <?php else: ?>
                            <small>Username bersifat unik dan digunakan saat masuk ke sistem SIP-OKE.</small>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="dosen-form-footer">
                        <a href="<?= $baseUrl ?>/dosen" class="btn btn-secondary">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Dosen' ?>
                        </button>
                    </div>
                </div>
            </form>
        </main>

        <!-- Sidebar / Aside -->
        <aside class="dosen-aside">
            <div class="info-box">
                <h4><i class="fa-solid fa-circle-info"></i> Panduan Registrasi Dosen</h4>
                <p>Data dosen digunakan secara terpadu di seluruh modul kurikulum OBE:</p>
                <ul class="info-checklist">
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Penugasan PJMK:</strong> Dosen dapat dipilih sebagai Koordinator Mata Kuliah (PJMK) maupun anggota Tim Teaching.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Pengesahan Pimpinan:</strong> NIDN dosen otomatis tertaut jika ditugaskan sebagai Dekan atau Kaprodi pada Master Program Studi.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Penyusunan RPS & Nilai:</strong> Akun ini memiliki wewenang menginput capaian CPMK/Sub-CPMK pada kelas yang diampu.</span>
                    </li>
                </ul>

                <div class="preview-card">
                    <div class="preview-card-avatar">
                        <div class="avatar-icon-box"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <div class="preview-card-title" id="previewDosenName"><?= $isEdit ? htmlspecialchars($dosen['nama_lengkap']) : 'Nama Dosen & Gelar' ?></div>
                            <div class="preview-card-sub" id="previewDosenNidn"><?= $isEdit ? 'NIDN: ' . htmlspecialchars($dosen['nidn'] ?: '-') : 'NIDN: -' ?></div>
                        </div>
                    </div>
                    <div style="font-size: .74rem; color: var(--text-muted); border-top: 1px dashed var(--border-subtle); padding-top: 6px;" id="previewDosenHomebase">
                        <?= $isEdit ? htmlspecialchars($dosen['homebase'] ?: 'Homebase: -') : 'Homebase: Belum dipilih' ?>
                    </div>
                </div>
            </div>

            <div class="info-box" style="background: var(--surface);">
                <h4><i class="fa-solid fa-key"></i> Hak Akses Dosen</h4>
                <p>Setelah ditambahkan, akun dosen memiliki akses langsung untuk melihat jadwal mengajar, menyusun Rencana Pembelajaran Semester (RPS), mengelola rubrik, serta merekap penilaian asesmen kelas.</p>
            </div>
        </aside>
    </div>
</div>

<script>
window.handleHomebaseSelect = function(val) {
    const input = document.getElementById('homebaseInput');
    const preview = document.getElementById('previewDosenHomebase');
    if (val === '__custom__') {
        input.style.display = 'block';
        input.value = '';
        input.focus();
        if (preview) preview.textContent = 'Homebase: (Ketik Manual)';
    } else if (val) {
        input.style.display = 'none';
        input.value = val;
        if (preview) preview.textContent = 'Homebase: ' + val;
    } else {
        input.style.display = 'none';
        input.value = '';
        if (preview) preview.textContent = 'Homebase: Belum dipilih';
    }
};

const namaInput = document.getElementById('namaLengkapInput');
const nidnInput = document.getElementById('nidnInput');
const hbInput = document.getElementById('homebaseInput');
const previewName = document.getElementById('previewDosenName');
const previewNidn = document.getElementById('previewDosenNidn');
const previewHb = document.getElementById('previewDosenHomebase');

if (namaInput) {
    namaInput.addEventListener('input', function() {
        if (previewName) previewName.textContent = this.value.trim() || 'Nama Dosen & Gelar';
    });
}
if (nidnInput) {
    nidnInput.addEventListener('input', function() {
        if (previewNidn) previewNidn.textContent = 'NIDN: ' + (this.value.trim() || '-');
    });
}
if (hbInput) {
    hbInput.addEventListener('input', function() {
        if (previewHb) previewHb.textContent = 'Homebase: ' + (this.value.trim() || '-');
    });
}
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
