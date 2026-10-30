<?php
require_once __DIR__ . '/../../app/Support/Maintenance.php';
$baseUrl = Maintenance::baseUrl($config['base_url'] ?? null);
$imgUrl = Maintenance::assetUrl($settings['maintenance_img'] ?? null, $baseUrl);
$isCustomImg = !empty($settings['maintenance_img']) && strpos($settings['maintenance_img'], 'uploads/maintenance/') === 0;
ob_start();
?>
<style>
.maint-config-wrap {
    padding: 4px 0 32px;
    width: 100%;
    box-sizing: border-box;
}

.maint-config-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.maint-config-header h1 {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-main);
    margin: 0;
    letter-spacing: -0.02em;
}
.maint-config-header p {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 5px 0 0;
}

/* Status Banner Big Card */
.maint-status-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}
.maint-status-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    background: <?= $isActive ? 'var(--warning, #f59e0b)' : 'var(--success, #10b981)' ?>;
}

.maint-status-left {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
    flex: 1 1 340px;
}
.maint-status-icon-box {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
    background: <?= $isActive ? 'rgba(245, 158, 11, 0.14)' : 'rgba(16, 185, 129, 0.14)' ?>;
    color: <?= $isActive ? '#f59e0b' : '#10b981' ?>;
    border: 1px solid <?= $isActive ? 'rgba(245, 158, 11, 0.28)' : 'rgba(16, 185, 129, 0.28)' ?>;
}
.maint-status-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}
.maint-status-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.badge-active {
    background: rgba(245, 158, 11, 0.16);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.35);
}
.badge-inactive {
    background: rgba(16, 185, 129, 0.16);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.35);
}
.maint-status-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-main);
}
.maint-status-desc {
    font-size: 0.82rem;
    color: var(--text-muted);
    line-height: 1.5;
    margin: 0;
}

.maint-quick-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}

/* Two Column Grid for Settings & Preview */
.maint-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
    gap: 22px;
}
@media (max-width: 960px) {
    .maint-grid {
        grid-template-columns: 1fr;
    }
}

.maint-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.maint-box-head {
    padding: 16px 20px;
    background: var(--surface-hover);
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.maint-box-head h2 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.maint-box-body {
    padding: 22px;
}

.maint-form-group {
    margin-bottom: 18px;
}
.maint-form-group > label {
    display: block;
    margin-bottom: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-main);
}
.maint-hint {
    font-size: 0.73rem;
    color: var(--text-muted);
    margin-top: 5px;
}

.maint-switch-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-main);
}
.maint-switch-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    background: var(--border);
    border-radius: 20px;
    transition: background 0.2s ease;
    flex-shrink: 0;
}
.maint-switch-toggle::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 4px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s ease;
}
input[type="checkbox"]:checked + .maint-switch-toggle {
    background: #f59e0b;
}
input[type="checkbox"]:checked + .maint-switch-toggle::after {
    transform: translateX(18px);
}

.maint-preview-frame {
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    background: var(--background);
    padding: 24px 16px;
    max-height: 580px;
    overflow-y: auto;
}
</style>

<div class="maint-config-wrap">
    <div class="maint-config-header">
        <div>
            <h1>Mode Pemeliharaan (Maintenance Mode)</h1>
            <p>Kelola aksesibilitas sistem dan kustomisasi tampilan pemeliharaan website OBE (SIP-OKE)</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <a href="<?= htmlspecialchars($baseUrl) ?>/dashboard?preview_maintenance=1" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Mode Pengguna
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/branding" class="btn btn-secondary">
                <i class="fa-solid fa-icons"></i> Logo &amp; Favicon
            </a>
        </div>
    </div>

    <!-- Banner Status Besar -->
    <div class="maint-status-card">
        <div class="maint-status-left">
            <div class="maint-status-icon-box">
                <i class="fa-solid <?= $isActive ? 'fa-screwdriver-wrench' : 'fa-check' ?>"></i>
            </div>
            <div>
                <div class="maint-status-title-row">
                    <span class="maint-status-title">Status:</span>
                    <?php if ($isActive): ?>
                        <span class="maint-status-badge badge-active"><i class="fa-solid fa-triangle-exclamation"></i> AKTIF &bull; PEMELIHARAAN</span>
                    <?php else: ?>
                        <span class="maint-status-badge badge-inactive"><i class="fa-solid fa-circle-check"></i> NORMAL &bull; BERJALAN</span>
                    <?php endif; ?>
                </div>
                <p class="maint-status-desc">
                    <?= $isActive 
                        ? 'Website saat ini <strong>terkunci untuk pengguna umum</strong>. Seluruh dosen, kaprodi, dan mahasiswa melihat halaman pemeliharaan dan operasi data dinonaktifkan.'
                        : 'Sistem beroperasi normal. Seluruh pengguna dapat mengakses dan mengelola OBE sesuai hak akses masing-masing.' ?>
                </p>
            </div>
        </div>
        <div>
            <form action="<?= htmlspecialchars($baseUrl) ?>/maintenance/toggle" method="post" style="margin:0;">
                <input type="hidden" name="action" value="<?= $isActive ? 'disable' : 'enable' ?>">
                <button type="submit" class="btn <?= $isActive ? 'btn-success' : 'btn-danger' ?>" style="padding:10px 20px; font-weight:700;">
                    <i class="fa-solid <?= $isActive ? 'fa-power-off' : 'fa-screwdriver-wrench' ?>"></i>
                    <?= $isActive ? 'Nonaktifkan Maintenance' : 'Aktifkan Mode Maintenance' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Grid Pengaturan & Pratinjau -->
    <div class="maint-grid">
        <!-- Form Pengaturan -->
        <div class="maint-box">
            <div class="maint-box-head">
                <h2><i class="fa-solid fa-sliders"></i> Pengaturan Konten Pemeliharaan</h2>
            </div>
            <div class="maint-box-body">
                <form action="<?= htmlspecialchars($baseUrl) ?>/maintenance/save" method="post" enctype="multipart/form-data">
                    <div class="maint-form-group" style="padding: 12px 14px; background: var(--surface-hover); border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); margin-bottom: 20px;">
                        <label class="maint-switch-label">
                            <input type="checkbox" name="maintenance_mode" value="1" <?= $isActive ? 'checked' : '' ?> style="display:none;" id="field_toggle">
                            <span class="maint-switch-toggle"></span>
                            <span>Aktifkan Mode Pemeliharaan Sekarang</span>
                        </label>
                        <div class="maint-hint" style="margin-left: 56px;">Centang untuk mengunci akses seluruh pengguna non-Super Admin.</div>
                    </div>

                    <div class="maint-form-group">
                        <label for="field_title">Judul Tampilan Pemeliharaan</label>
                        <input type="text" class="form-control" id="field_title" name="maintenance_title" value="<?= htmlspecialchars($settings['maintenance_title'] ?? 'Website Sedang Dalam Pemeliharaan') ?>" required placeholder="Contoh: Website Sedang Dalam Pemeliharaan">
                        <div class="maint-hint">Judul utama berukuran besar yang muncul pada halaman pemeliharaan.</div>
                    </div>

                    <div class="maint-form-group">
                        <label for="field_message">Pesan / Penjelasan Pemeliharaan</label>
                        <textarea class="form-control" id="field_message" name="maintenance_message" rows="4" placeholder="Jelaskan alasan pemeliharaan kepada civitas akademika..."><?= htmlspecialchars($settings['maintenance_message'] ?? '') ?></textarea>
                        <div class="maint-hint">Penjelasan detail mengenai peningkatan mutu, migrasi data, atau perbaikan server.</div>
                    </div>

                    <div class="maint-form-group">
                        <label for="field_until">Estimasi / Jadwal Selesai (Opsional)</label>
                        <input type="text" class="form-control" id="field_until" name="maintenance_until" value="<?= htmlspecialchars($settings['maintenance_until'] ?? '') ?>" placeholder="Contoh: Jumat, 30 Oktober 2026, Pukul 15:00 WIB">
                        <div class="maint-hint">Informasi perkiraan waktu selesai agar pengguna mengetahui kapan sistem aktif kembali.</div>
                    </div>

                    <div class="maint-form-group">
                        <label>Gambar Ilustrasi Pemeliharaan</label>
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
                            <div style="width:72px;height:72px;border-radius:10px;background:var(--surface-hover);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" id="img_thumb" style="max-width:100%;max-height:100%;object-fit:contain;">
                            </div>
                            <div style="flex:1;">
                                <input type="file" name="maintenance_img" id="field_img" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                <div class="maint-hint">Format didukung: PNG, JPG, WebP, SVG. Maks 3 MB.</div>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:24px;padding-top:18px;border-top:1px solid var(--border-subtle);flex-wrap:wrap;">
                        <?php if ($isCustomImg): ?>
                            <button type="submit" formaction="<?= htmlspecialchars($baseUrl) ?>/maintenance/reset-image" class="btn btn-secondary" onclick="return confirm('Kembalikan ke gambar ilustrasi bawaan sistem?')">
                                <i class="fa-solid fa-rotate-left"></i> Gunakan Gambar Bawaan
                            </button>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-weight:700;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Live Preview Box -->
        <div class="maint-box">
            <div class="maint-box-head">
                <h2><i class="fa-solid fa-desktop"></i> Pratinjau Tampilan Pengguna</h2>
                <span style="font-size:0.75rem;color:var(--text-muted);"><i class="fa-solid fa-wand-magic-sparkles"></i> Live Real-Time</span>
            </div>
            <div class="maint-box-body">
                <div class="maint-preview-frame">
                    <div class="maint-card" style="box-shadow:none !important; border:none !important; background:transparent !important; padding:10px 0;">
                        <div class="maint-badge-pill">
                            <span class="maint-pulse-dot"></span>
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                            <span>MODE PEMELIHARAAN AKTIF</span>
                        </div>

                        <div class="maint-illustration-wrap" style="max-width:240px; margin-bottom:18px;">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Sedang Maintenance" class="maint-illustration-img" id="prev_img">
                        </div>

                        <h2 class="maint-title" id="prev_title" style="font-size:1.25rem !important;">
                            <?= htmlspecialchars($settings['maintenance_title'] ?? 'Website Sedang Dalam Pemeliharaan') ?>
                        </h2>
                        
                        <p class="maint-desc" id="prev_msg" style="font-size:0.8rem; margin-bottom:18px;">
                            <?= nl2br(htmlspecialchars($settings['maintenance_message'] ?? 'Sistem Penjaminan Mutu dan OBE saat ini sedang dalam proses pemeliharaan...')) ?>
                        </p>

                        <div class="maint-info-box" id="prev_until_box" style="margin-bottom:18px; <?= empty($settings['maintenance_until']) ? 'display:none;' : '' ?>">
                            <i class="fa-regular fa-clock"></i>
                            <div>
                                <span class="maint-info-label">Jadwal / Estimasi Selesai</span>
                                <strong class="maint-info-val" id="prev_until_val"><?= htmlspecialchars($settings['maintenance_until'] ?? '') ?></strong>
                            </div>
                        </div>

                        <div class="maint-actions" style="margin-bottom:16px;">
                            <button type="button" class="btn btn-primary maint-btn-reload" style="padding:6px 14px !important; font-size:0.78rem !important;">
                                <i class="fa-solid fa-rotate"></i> Muat Ulang Halaman
                            </button>
                            <span class="btn btn-secondary maint-btn-logout" style="padding:6px 14px !important; font-size:0.78rem !important; opacity:0.85;">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar (Logout)
                            </span>
                        </div>

                        <div class="maint-footer-note" style="font-size:0.68rem;">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Universitas Anwar Medika &bull; SIP-OKE (Outcome Based Education)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const fTitle = document.getElementById('field_title');
    const fMsg = document.getElementById('field_message');
    const fUntil = document.getElementById('field_until');
    const fImg = document.getElementById('field_img');

    const pTitle = document.getElementById('prev_title');
    const pMsg = document.getElementById('prev_msg');
    const pUntilBox = document.getElementById('prev_until_box');
    const pUntilVal = document.getElementById('prev_until_val');
    const pImg = document.getElementById('prev_img');
    const thumbImg = document.getElementById('img_thumb');

    if (fTitle && pTitle) {
        fTitle.addEventListener('input', function() {
            pTitle.textContent = this.value || 'Website Sedang Dalam Pemeliharaan';
        });
    }

    if (fMsg && pMsg) {
        fMsg.addEventListener('input', function() {
            pMsg.innerHTML = (this.value || '').replace(/\n/g, '<br>') || 'Sistem sedang dalam proses pemeliharaan...';
        });
    }

    if (fUntil && pUntilBox && pUntilVal) {
        fUntil.addEventListener('input', function() {
            const val = (this.value || '').trim();
            if (val === '') {
                pUntilBox.style.display = 'none';
            } else {
                pUntilBox.style.display = 'flex';
                pUntilVal.textContent = val;
            }
        });
    }

    if (fImg && pImg) {
        fImg.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                pImg.src = url;
                if (thumbImg) thumbImg.src = url;
            }
        });
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>

