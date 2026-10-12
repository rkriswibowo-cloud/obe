<?php
require_once __DIR__ . '/../../app/Support/Branding.php';
$baseUrl = $config['base_url'] ?? '/obe/public';
$logoUrl = Branding::assetUrl($branding['logo'] ?? null, $baseUrl);
$faviconUrl = Branding::assetUrl($branding['favicon'] ?? null, $baseUrl);
ob_start();
?>
<style>
    .branding-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:24px}.branding-head h1{margin:0 0 7px;font-size:1.5rem}.branding-head p{margin:0;color:var(--text-muted);font-size:.86rem;line-height:1.6}.branding-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.branding-card{padding:22px}.branding-card h2{margin:0;font-size:1rem}.branding-card>p{margin:7px 0 18px;color:var(--text-muted);font-size:.78rem;line-height:1.55}.asset-preview{height:180px;display:grid;place-items:center;margin-bottom:18px;border:1px dashed var(--border);border-radius:var(--radius-md);background:var(--input-bg);overflow:hidden}.asset-preview.logo-preview img{max-width:75%;max-height:110px;object-fit:contain}.asset-preview.favicon-preview img{width:64px;height:64px;object-fit:contain;image-rendering:auto}.asset-placeholder{text-align:center;color:var(--text-muted)}.asset-placeholder i{display:block;margin-bottom:9px;font-size:2rem;opacity:.55}.asset-placeholder span{font-size:.75rem}.upload-label{display:block;margin-bottom:8px;color:var(--text-main);font-size:.78rem;font-weight:600}.file-input{display:block;width:100%;padding:10px;border:1px solid var(--input-border);border-radius:var(--radius-sm);background:var(--input-bg);color:var(--text-main);font-size:.78rem}.file-input::file-selector-button{margin-right:12px;padding:7px 11px;border:0;border-radius:6px;background:var(--sidebar-active);color:var(--text-main);font-weight:600;cursor:pointer}.field-help{margin:7px 0 0;color:var(--text-muted);font-size:.7rem;line-height:1.5}.branding-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:18px;border-top:1px solid var(--border-subtle)}.branding-note{display:flex;gap:11px;margin-top:18px;padding:14px 16px;border:1px solid rgba(var(--primary-rgb),.28);border-radius:var(--radius-md);background:rgba(var(--primary-rgb),.08);font-size:.76rem;line-height:1.55;color:var(--text-muted)}.branding-note i{margin-top:2px;color:var(--primary)}@media(max-width:760px){.branding-grid{grid-template-columns:1fr}.branding-head{display:block}.branding-actions{flex-direction:column-reverse}.branding-actions .btn{width:100%}}
</style>

<div class="branding-head">
    <div>
        <h1>Logo &amp; Favicon</h1>
        <p>Kelola identitas visual yang digunakan pada halaman login, navigasi, dan tab browser.</p>
    </div>
</div>

<form action="<?= htmlspecialchars($baseUrl) ?>/branding/save" method="post" enctype="multipart/form-data">
    <div class="branding-grid">
        <section class="card branding-card">
            <h2>Logo aplikasi</h2>
            <p>Gunakan logo horizontal atau simbol institusi dengan latar transparan.</p>
            <div class="asset-preview logo-preview" id="logoPreview">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo aplikasi saat ini">
                <?php else: ?>
                    <div class="asset-placeholder"><i class="fa-solid fa-graduation-cap"></i><span>Menggunakan simbol bawaan SIP-OKE</span></div>
                <?php endif; ?>
            </div>
            <label class="upload-label" for="logo">Pilih logo baru</label>
            <input class="file-input" id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" data-preview="logoPreview">
            <p class="field-help">PNG, JPG, atau WebP · maksimal 2 MB · rekomendasi 600 × 200 piksel.</p>
        </section>

        <section class="card branding-card">
            <h2>Favicon</h2>
            <p>Ikon kecil yang tampil pada tab browser dan bookmark.</p>
            <div class="asset-preview favicon-preview" id="faviconPreview">
                <?php if ($faviconUrl): ?>
                    <img src="<?= htmlspecialchars($faviconUrl) ?>" alt="Favicon saat ini">
                <?php else: ?>
                    <div class="asset-placeholder"><i class="fa-solid fa-window-restore"></i><span>Belum ada favicon khusus</span></div>
                <?php endif; ?>
            </div>
            <label class="upload-label" for="favicon">Pilih favicon baru</label>
            <input class="file-input" id="favicon" name="favicon" type="file" accept="image/png,image/x-icon,.ico" data-preview="faviconPreview">
            <p class="field-help">PNG atau ICO · maksimal 2 MB · rekomendasi persegi 32 × 32 atau 64 × 64 piksel.</p>
        </section>
    </div>

    <div class="branding-note">
        <i class="fa-solid fa-circle-info"></i>
        <span>Jika hanya memilih salah satu berkas, aset lainnya tidak akan berubah. Browser mungkin memerlukan muat ulang paksa untuk menampilkan favicon terbaru.</span>
    </div>

    <div class="branding-actions">
        <?php if ($logoUrl || $faviconUrl): ?>
            <button class="btn btn-danger" type="submit" formaction="<?= htmlspecialchars($baseUrl) ?>/branding/reset" onclick="return confirm('Kembalikan logo dan favicon ke tampilan bawaan?')"><i class="fa-solid fa-rotate-left"></i> Gunakan bawaan</button>
        <?php endif; ?>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan perubahan</button>
    </div>
</form>

<script>
document.querySelectorAll('[data-preview]').forEach(function(input) {
    input.addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) return;
        const target = document.getElementById(this.dataset.preview);
        const image = document.createElement('img');
        image.alt = 'Pratinjau berkas terpilih';
        image.src = URL.createObjectURL(file);
        image.onload = function() { URL.revokeObjectURL(image.src); };
        target.replaceChildren(image);
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
