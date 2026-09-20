<?php
$title = "Import Data Master";
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
ob_start();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Import Data Master</h2>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <strong>Berhasil mengimpor <?= htmlspecialchars($_GET['count'] ?? '0') ?> baris data.</strong>
        <?php if (isset($_GET['errors']) && $_GET['errors'] > 0): ?>
            <br><small>Terdapat <?= htmlspecialchars($_GET['errors']) ?> baris data yang gagal/dilewati (format salah, data tidak lengkap, atau tidak ditemukan referensi).</small>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 15px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <strong>Gagal melakukan import:</strong> <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<!-- Tab Switcher -->
<div style="display: flex; gap: 0; margin-bottom: 24px; border-bottom: 2px solid var(--border);">
    <button onclick="switchTab('file')" id="tab-file" class="import-tab active-tab" style="padding: 12px 24px; background: none; border: none; color: var(--text-main); cursor: pointer; font-size: 0.95rem; font-weight: 600; border-bottom: 2px solid var(--primary); margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-file-csv"></i> Upload File CSV
    </button>
    <button onclick="switchTab('text')" id="tab-text" class="import-tab" style="padding: 12px 24px; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.95rem; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;">
        <i class="fa-solid fa-keyboard"></i> Input Teks CSV
    </button>
</div>

<!-- ============ PANEL 1: FILE UPLOAD ============ -->
<div id="panel-file">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- CPL -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
                <h4 style="margin: 0; color: var(--primary);">Import CPL</h4>
                <a href="<?= $baseUrl ?>/import/template?type=cpl" class="btn btn-sm btn-secondary" title="Download Template CSV">
                    <i class="fa-solid fa-download"></i> Template
                </a>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 15px 0;">Upload file CSV yang berisi Master CPL (Capaian Pembelajaran Lulusan).</p>
                <form action="<?= $baseUrl ?>/import/upload" method="POST" enctype="multipart/form-data" style="margin-top: auto;">
                    <input type="hidden" name="type" value="cpl">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">File CSV</label>
                        <input type="file" name="csv_file" accept=".csv" required style="width: 100%; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;"><i class="fa-solid fa-upload" style="margin-right: 5px;"></i> Upload & Proses</button>
                </form>
            </div>
        </div>

        <!-- CPMK -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
                <h4 style="margin: 0; color: var(--success);">Import CPMK</h4>
                <a href="<?= $baseUrl ?>/import/template?type=cpmk" class="btn btn-sm btn-secondary" title="Download Template CSV">
                    <i class="fa-solid fa-download"></i> Template
                </a>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 15px 0;">Upload file CSV yang berisi CPMK untuk suatu Mata Kuliah.</p>
                <form action="<?= $baseUrl ?>/import/upload" method="POST" enctype="multipart/form-data" style="margin-top: auto;">
                    <input type="hidden" name="type" value="cpmk">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Mata Kuliah</label>
                        <select name="mk_id" required style="width: 100%;">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($mataKuliah as $mk): ?>
                                <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">File CSV</label>
                        <input type="file" name="csv_file" accept=".csv" required style="width: 100%; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: var(--success); border-color: var(--success); width: 100%; justify-content: center;"><i class="fa-solid fa-upload" style="margin-right: 5px;"></i> Upload & Proses</button>
                </form>
            </div>
        </div>

        <!-- Sub-CPMK -->
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
                <h4 style="margin: 0; color: var(--info);">Import Sub-CPMK</h4>
                <a href="<?= $baseUrl ?>/import/template?type=sub_cpmk" class="btn btn-sm btn-secondary" title="Download Template CSV">
                    <i class="fa-solid fa-download"></i> Template
                </a>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 15px 0;">Upload file CSV yang berisi Sub-CPMK untuk suatu Mata Kuliah.</p>
                <form action="<?= $baseUrl ?>/import/upload" method="POST" enctype="multipart/form-data" style="margin-top: auto;">
                    <input type="hidden" name="type" value="sub_cpmk">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Mata Kuliah</label>
                        <select name="mk_id" required style="width: 100%;">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($mataKuliah as $mk): ?>
                                <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">File CSV</label>
                        <input type="file" name="csv_file" accept=".csv" required style="width: 100%; box-sizing: border-box;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: var(--info); border-color: var(--info); width: 100%; justify-content: center;"><i class="fa-solid fa-upload" style="margin-right: 5px;"></i> Upload & Proses</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============ PANEL 2: TEXTAREA CSV ============ -->
<div id="panel-text" style="display: none;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; max-width: 800px;">
        <form action="<?= $baseUrl ?>/import/upload-text" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Jenis Data</label>
                <select name="type" id="text-import-type" required style="width: 100%;" onchange="updatePlaceholder()">
                    <option value="">-- Pilih Jenis Data --</option>
                    <option value="cpl">CPL</option>
                    <option value="cpmk">CPMK</option>
                    <option value="sub_cpmk">Sub-CPMK</option>
                </select>
            </div>

            <div id="text-mk-select" style="margin-bottom: 20px; display: none;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Mata Kuliah</label>
                <select name="mk_id" id="text-mk-id" style="width: 100%;">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php foreach ($mataKuliah as $mk): ?>
                        <option value="<?= $mk['id'] ?>"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                <label style="font-weight: 600;">Data CSV</label>
                <small style="color: var(--text-muted);">Baris pertama = header, dipisah koma</small>
            </div>

            <!-- Hint box -->
            <div id="csv-hint" style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 5px;"></i>
                Pilih <strong>Jenis Data</strong> di atas untuk melihat contoh format CSV.
            </div>

            <textarea name="csv_text" id="csv-textarea" rows="12" required
                style="width: 100%; font-family: 'Courier New', monospace; font-size: 0.9rem; resize: vertical; box-sizing: border-box;"
                placeholder="kode,rumusan&#10;CPL01,Mampu menerapkan pengetahuan...&#10;CPL02,Mampu mengidentifikasi..."></textarea>

            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 5px;"></i> Proses Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('panel-file').style.display = tab === 'file' ? 'block' : 'none';
    document.getElementById('panel-text').style.display = tab === 'text' ? 'block' : 'none';

    const tabFile = document.getElementById('tab-file');
    const tabText = document.getElementById('tab-text');

    if (tab === 'file') {
        tabFile.style.color = 'var(--text-main)';
        tabFile.style.fontWeight = '600';
        tabFile.style.borderBottomColor = 'var(--primary)';
        tabText.style.color = 'var(--text-muted)';
        tabText.style.fontWeight = '500';
        tabText.style.borderBottomColor = 'transparent';
    } else {
        tabText.style.color = 'var(--text-main)';
        tabText.style.fontWeight = '600';
        tabText.style.borderBottomColor = 'var(--primary)';
        tabFile.style.color = 'var(--text-muted)';
        tabFile.style.fontWeight = '500';
        tabFile.style.borderBottomColor = 'transparent';
    }
}

function updatePlaceholder() {
    const type = document.getElementById('text-import-type').value;
    const textarea = document.getElementById('csv-textarea');
    const mkSelect = document.getElementById('text-mk-select');
    const mkId = document.getElementById('text-mk-id');
    const hint = document.getElementById('csv-hint');

    // Show/hide MK select
    if (type === 'cpmk' || type === 'sub_cpmk') {
        mkSelect.style.display = 'block';
        mkId.required = true;
    } else {
        mkSelect.style.display = 'none';
        mkId.required = false;
    }

    // Update placeholder and hint
    if (type === 'cpl') {
        textarea.placeholder = "kode,rumusan\nCPL01,Mampu menerapkan pengetahuan matematika dan sains\nCPL02,Mampu mengidentifikasi dan menganalisis permasalahan";
        hint.innerHTML = '<i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 5px;"></i> Format: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:3px;">kode,rumusan</code>';
    } else if (type === 'cpmk') {
        textarea.placeholder = "kode_cpl,kode_cpmk,rumusan_cpmk\n\"CPL01, CPL02\",CPMK01,Mampu menjelaskan konsep dasar\nCPL03,CPMK02,Mampu membuat program sederhana";
        hint.innerHTML = '<i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 5px;"></i> Format: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:3px;">kode_cpl,kode_cpmk,rumusan_cpmk</code> &mdash; Untuk multi-CPL gunakan tanda kutip: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:3px;">"CPL01, CPL02"</code>';
    } else if (type === 'sub_cpmk') {
        textarea.placeholder = "kode_cpmk,kode_sub_cpmk,rumusan_sub_cpmk\nCPMK01,Sub-CPMK01,Mampu mendefinisikan konsep dasar\nCPMK01,Sub-CPMK02,Mampu menjelaskan jenis-jenis SI";
        hint.innerHTML = '<i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 5px;"></i> Format: <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:3px;">kode_cpmk,kode_sub_cpmk,rumusan_sub_cpmk</code>';
    } else {
        textarea.placeholder = "";
        hint.innerHTML = '<i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 5px;"></i> Pilih <strong>Jenis Data</strong> di atas untuk melihat contoh format CSV.';
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
