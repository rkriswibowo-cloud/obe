<?php
// views/prodi/form.php
$isEdit = isset($prodi);
$title = ($isEdit ? 'Edit Program Studi' : 'Tambah Program Studi') . ' - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
$action = $isEdit ? ($baseUrl . '/prodi/update') : ($baseUrl . '/prodi/store');
$currentJenjang = $prodi['jenjang'] ?? 'S1';
$jenjangList = [
    'D3' => 'Diploma 3 (Ahli Madya)',
    'D4' => 'Diploma 4 (Sarjana Terapan)',
    'S1' => 'Strata 1 (Sarjana)',
    'S2' => 'Strata 2 (Magister)',
    'S3' => 'Strata 3 (Doktor)',
];
?>

<style>
    .prodi-form-page {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    /* Header */
    .prodi-form-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .prodi-form-header-back {
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
    .prodi-form-header-back:hover {
        color: var(--primary);
    }
    .prodi-form-eyebrow {
        margin: 0 0 5px;
        color: var(--primary);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .prodi-form-title {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 750;
        color: var(--text-main);
        letter-spacing: -.02em;
    }
    .prodi-form-subtitle {
        margin: 6px 0 0;
        color: var(--text-muted);
        font-size: .88rem;
        max-width: 720px;
        line-height: 1.5;
    }

    /* Main Responsive Grid: Form + Info Sidebar */
    .prodi-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.85fr) minmax(320px, 1fr);
        gap: 22px;
        align-items: start;
    }

    /* Card Panels */
    .prodi-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .prodi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }
    .prodi-card-header h3 {
        margin: 0;
        font-size: .98rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .prodi-card-header h3 i {
        color: var(--primary);
    }
    .prodi-card-header span {
        font-size: .75rem;
        color: var(--text-muted);
    }
    .prodi-card-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    /* Section Subheadings inside form */
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

    /* Form Rows & Fields */
    .field-row-2 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr);
        gap: 16px;
    }
    .field-row-equal {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .prodi-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .prodi-field label {
        font-size: .8rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .prodi-field label span.req {
        color: var(--danger, #ef4444);
    }
    .prodi-field .form-control {
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
    .prodi-field .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .12);
        outline: none;
    }
    .prodi-field .form-control:disabled,
    .prodi-field .form-control[readonly] {
        background: var(--surface-hover);
        color: var(--text-muted);
        cursor: not-allowed;
    }
    .prodi-field small {
        font-size: .74rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Radio Status Group */
    .status-radio-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .status-radio-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        cursor: pointer;
        transition: all .15s ease;
    }
    .status-radio-card:hover {
        border-color: rgba(var(--primary-rgb), .4);
        background: var(--surface-hover);
    }
    .status-radio-card input[type="radio"] {
        margin: 0;
        accent-color: var(--primary);
        width: 17px;
        height: 17px;
    }
    .status-radio-card strong {
        display: block;
        font-size: .84rem;
        color: var(--text-main);
    }
    .status-radio-card span {
        display: block;
        font-size: .72rem;
        color: var(--text-muted);
    }

    /* Action Footer */
    .prodi-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        margin-top: 6px;
    }
    .prodi-form-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 20px;
        font-weight: 700;
        font-size: .84rem;
    }

    /* Aside Info & Guidelines */
    .prodi-aside {
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
    }
    .info-checklist li i.fa-circle-check {
        color: #10b981;
    }
    .info-preview-box {
        background: var(--surface);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
        margin-top: 12px;
    }
    .info-preview-label {
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .info-preview-value {
        font-size: .86rem;
        font-weight: 700;
        color: var(--text-main);
        word-break: break-all;
    }

    /* Responsiveness */
    @media (max-width: 960px) {
        .prodi-form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .field-row-2,
        .field-row-equal,
        .status-radio-group {
            grid-template-columns: 1fr;
        }
        .prodi-card-body {
            padding: 18px 16px;
        }
        .prodi-form-footer {
            flex-direction: column-reverse;
            gap: 10px;
        }
        .prodi-form-footer .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="prodi-form-page">
    <header class="prodi-form-header">
        <div>
            <a href="<?= $baseUrl ?>/prodi" class="prodi-form-header-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Master Program Studi
            </a>
            <p class="prodi-form-eyebrow">Struktur Organisasi & Kurikulum</p>
            <h1 class="prodi-form-title"><?= $isEdit ? 'Edit Program Studi' : 'Tambah Program Studi Baru' ?></h1>
            <p class="prodi-form-subtitle">
                <?= $isEdit ? 'Perbarui informasi kode, jenjang, pimpinan UPPS, serta penanggung jawab program studi.' : 'Daftarkan entitas program studi baru untuk mengelola rancangan kurikulum OBE mandiri.' ?>
            </p>
        </div>
    </header>

    <div class="prodi-form-grid">
        <!-- Main Form Column -->
        <main class="prodi-card">
            <div class="prodi-card-header">
                <h3><i class="fa-solid fa-graduation-cap"></i> Formulir Data Program Studi</h3>
                <span>Tanda (<span style="color: var(--danger, #ef4444); font-weight:700">*</span>) wajib diisi</span>
            </div>

            <form action="<?= $action ?>" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int)$prodi['id'] ?>">
                <?php endif; ?>

                <div class="prodi-card-body">
                    <!-- Section 1: Identitas Prodi -->
                    <div class="form-section-title">
                        <span class="form-section-badge">1</span>
                        <h4>Identitas & Jenjang Pendidikan</h4>
                        <p>Kode unik dan jenjang prodi</p>
                    </div>

                    <div class="field-row-2">
                        <div class="prodi-field">
                            <label for="prodiKode">Kode Prodi <span class="req">*</span></label>
                            <input type="text" id="prodiKode" name="kode" required maxlength="10" value="<?= $isEdit ? htmlspecialchars($prodi['kode']) : '' ?>" placeholder="Contoh: SI" class="form-control" style="font-family: monospace; font-weight: 700; text-transform: uppercase;">
                            <small>Maks. 10 karakter alfabet/angka.</small>
                        </div>

                        <div class="prodi-field">
                            <label for="prodiJenjang">Jenjang Pendidikan <span class="req">*</span></label>
                            <select id="prodiJenjang" name="jenjang" required class="form-control">
                                <?php foreach ($jenjangList as $code => $label): ?>
                                    <option value="<?= $code ?>" <?= $currentJenjang === $code ? 'selected' : '' ?>>
                                        <?= $code ?> &mdash; <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Tingkat kualifikasi capaian KKNI.</small>
                        </div>
                    </div>

                    <div class="prodi-field">
                        <label for="prodiNama">Nama Program Studi <span class="req">*</span></label>
                        <input type="text" id="prodiNama" name="nama" required value="<?= $isEdit ? htmlspecialchars($prodi['nama']) : '' ?>" placeholder="Contoh: Sistem Informasi" class="form-control">
                        <small>Nama resmi program studi sesuai dokumen akreditasi/SK pendirian.</small>
                    </div>

                    <div class="prodi-field">
                        <label for="prodiFakultas">Fakultas / UPPS <span class="req">*</span></label>
                        <input type="text" id="prodiFakultas" name="fakultas" required value="<?= $isEdit ? htmlspecialchars($prodi['fakultas']) : 'Fakultas Sains dan Teknologi' ?>" placeholder="Contoh: Fakultas Sains dan Teknologi" class="form-control">
                        <small>Unit Pengelola Program Studi tingkat Fakultas.</small>
                    </div>

                    <!-- Section 2: Pimpinan & Pengesahan -->
                    <div class="form-section-title" style="margin-top: 6px;">
                        <span class="form-section-badge">2</span>
                        <h4>Struktur Pimpinan & Pengesahan</h4>
                        <p>Penandatangan dokumen OBE</p>
                    </div>

                    <div class="field-row-equal">
                        <div class="prodi-field">
                            <label for="dekanUserSelect">Dekan Fakultas</label>
                            <select name="dekan_user_id" id="dekanUserSelect" onchange="syncDekanPreview()" class="form-control">
                                <option value="">-- Pilih Dosen Dekan --</option>
                                <?php foreach ($dosenList as $dosen): ?>
                                    <option value="<?= (int)$dosen['id'] ?>" data-nidn="<?= htmlspecialchars($dosen['nidn'] ?? '', ENT_QUOTES) ?>" data-nuptk="<?= htmlspecialchars($dosen['nuptk'] ?? '', ENT_QUOTES) ?>" <?= $isEdit && (int)($prodi['dekan_user_id'] ?? 0) === (int)$dosen['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dosen['nama_lengkap']) ?><?= !empty($dosen['homebase']) ? ' (' . htmlspecialchars($dosen['homebase']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Pejabat pengesah RPS & dokumen CPL.</small>
                        </div>

                        <div class="prodi-field">
                            <label for="dekanIdentityPreview">NIDN / NUPTK Dekan</label>
                            <input type="text" id="dekanIdentityPreview" value="" placeholder="Otomatis dari data dosen" disabled class="form-control" style="font-family: monospace;">
                            <small>Diambil otomatis dari profil dosen.</small>
                        </div>
                    </div>

                    <div class="field-row-equal">
                        <div class="prodi-field">
                            <label for="kaprodiUserSelect">Ketua Program Studi (Kaprodi)</label>
                            <select name="kaprodi_user_id" id="kaprodiUserSelect" onchange="syncKaprodiPreview()" class="form-control">
                                <option value="">-- Pilih Dosen Kaprodi --</option>
                                <?php foreach ($dosenList as $dosen): ?>
                                    <option value="<?= (int)$dosen['id'] ?>" data-nidn="<?= htmlspecialchars($dosen['nidn'] ?? '', ENT_QUOTES) ?>" data-nuptk="<?= htmlspecialchars($dosen['nuptk'] ?? '', ENT_QUOTES) ?>" <?= $isEdit && (int)($prodi['kaprodi_user_id'] ?? 0) === (int)$dosen['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dosen['nama_lengkap']) ?><?= !empty($dosen['homebase']) ? ' (' . htmlspecialchars($dosen['homebase']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Koordinator utama kurikulum & evaluasi.</small>
                        </div>

                        <div class="prodi-field">
                            <label for="kaprodiIdentityPreview">NIDN / NUPTK Kaprodi</label>
                            <input type="text" id="kaprodiIdentityPreview" value="" placeholder="Otomatis dari data dosen" disabled class="form-control" style="font-family: monospace;">
                            <small>Diambil otomatis dari profil dosen.</small>
                        </div>
                    </div>

                    <!-- Section 3: Status Operasional -->
                    <div class="form-section-title" style="margin-top: 6px;">
                        <span class="form-section-badge">3</span>
                        <h4>Status Operasional Program Studi</h4>
                        <p>Visibilitas pada kurikulum</p>
                    </div>

                    <div class="prodi-field">
                        <div class="status-radio-group">
                            <label class="status-radio-card">
                                <input type="radio" name="status" value="ACTIVE" <?= (!$isEdit || ($prodi['status'] ?? 'ACTIVE') === 'ACTIVE') ? 'checked' : '' ?>>
                                <div>
                                    <strong style="color: #10b981;"><i class="fa-solid fa-circle-check"></i> ACTIVE</strong>
                                    <span>Aktif beroperasi dan dapat dipilih dalam kurikulum</span>
                                </div>
                            </label>
                            <label class="status-radio-card">
                                <input type="radio" name="status" value="INACTIVE" <?= ($isEdit && ($prodi['status'] ?? '') === 'INACTIVE') ? 'checked' : '' ?>>
                                <div>
                                    <strong style="color: var(--text-muted);"><i class="fa-solid fa-circle-pause"></i> INACTIVE</strong>
                                    <span>Non-aktif / diarsipkan sementara</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="prodi-form-footer">
                        <a href="<?= $baseUrl ?>/prodi" class="btn btn-secondary">
                            <i class="fa-solid fa-xmark"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Program Studi' ?>
                        </button>
                    </div>
                </div>
            </form>
        </main>

        <!-- Sidebar / Context Aside -->
        <aside class="prodi-aside">
            <div class="info-box">
                <h4><i class="fa-solid fa-circle-info"></i> Panduan Konfigurasi</h4>
                <p>Program studi bertindak sebagai unit dasar pengelompokan kurikulum, peta CPL, dan mata kuliah OBE.</p>
                
                <ul class="info-checklist">
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Kode Unik:</strong> Pastikan kode program studi belum digunakan oleh prodi lain.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Integrasi Dosen:</strong> Pilih pimpinan dari daftar dosen terverifikasi agar NIDN/NUPTK tersinkronisasi pada cetak laporan & RPS.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-circle-check"></i>
                        <span><strong>Struktur OBE:</strong> Setiap prodi dapat memiliki beberapa versi kurikulum (misal: Kurikulum 2020, 2024, RPL).</span>
                    </li>
                </ul>

                <div class="info-preview-box">
                    <div class="info-preview-label">Format Penamaan Ringkas</div>
                    <div class="info-preview-value" id="previewProdiName">
                        <?= $isEdit ? htmlspecialchars(($prodi['jenjang'] ?? 'S1') . ' ' . ($prodi['nama'] ?? '')) : 'S1 Program Studi' ?>
                    </div>
                </div>
            </div>

            <div class="info-box" style="background: var(--surface);">
                <h4><i class="fa-solid fa-shield-halved"></i> Dampak Status Aktif</h4>
                <p>Status <strong>ACTIVE</strong> memungkinkan dosen pengampu, admin, dan pimpinan untuk menyusun kurikulum baru, memetakan Sub-CPMK, serta melakukan asesmen nilai mahasiswa di prodi ini.</p>
            </div>
        </aside>
    </div>
</div>

<script>
function syncDekanPreview() {
    const select = document.getElementById('dekanUserSelect');
    if (!select) return;
    const option = select.options[select.selectedIndex];
    const nidn = option?.dataset.nidn || '';
    const nuptk = option?.dataset.nuptk || '';
    const target = document.getElementById('dekanIdentityPreview');
    if (!target) return;
    target.value = nuptk ? `NUPTK: ${nuptk}` : (nidn ? `NIDN: ${nidn}` : (select.value ? 'Identitas belum diisi pada data dosen' : ''));
}

function syncKaprodiPreview() {
    const select = document.getElementById('kaprodiUserSelect');
    if (!select) return;
    const option = select.options[select.selectedIndex];
    const nidn = option?.dataset.nidn || '';
    const nuptk = option?.dataset.nuptk || '';
    const target = document.getElementById('kaprodiIdentityPreview');
    if (!target) return;
    target.value = nuptk ? `NUPTK: ${nuptk}` : (nidn ? `NIDN: ${nidn}` : (select.value ? 'Identitas belum diisi pada data dosen' : ''));
}

// Live preview nama prodi
const namaInput = document.getElementById('prodiNama');
const jenjangInput = document.getElementById('prodiJenjang');
const previewEl = document.getElementById('previewProdiName');

function updateNamePreview() {
    if (!previewEl) return;
    const j = jenjangInput?.value || 'S1';
    const n = namaInput?.value?.trim() || 'Nama Program Studi';
    previewEl.textContent = `${j} ${n}`;
}

if (namaInput && jenjangInput) {
    namaInput.addEventListener('input', updateNamePreview);
    jenjangInput.addEventListener('change', updateNamePreview);
}

syncDekanPreview();
syncKaprodiPreview();
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
