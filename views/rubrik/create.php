<?php
$title = "Buat Rubrik Penilaian Baru - OBE";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
            <i class="fa-solid fa-plus" style="color: var(--primary); margin-right: 8px;"></i>
            Buat Rubrik Penilaian Baru
        </h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Rancang matriks rubrik analitik dengan kriteria terukur, level performa, dan deskriptor kualitas capaian.
        </p>
    </div>
    <div>
        <a href="<?= $baseUrl ?>/rubrik" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="<?= $baseUrl ?>/rubrik/store" method="POST" id="rubricForm" style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Card 1: Informasi Dasar -->
    <div class="card" style="background: var(--surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <h4 style="margin: 0 0 16px 0; color: var(--text-main); font-size: 1.05rem; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 8px;"></i>
            Informasi Dasar Rubrik
        </h4>

        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.88rem; color: var(--text-main);">
                    Nama Rubrik <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" name="nama" required placeholder="Contoh: Rubrik Penilaian Proyek Basis Data" 
                       style="width: 100%; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--background); color: var(--text-main); font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.88rem; color: var(--text-main);">
                    Jenis Rubrik
                </label>
                <select name="kode_jenis" id="kodeJenisRubrik" required style="width: 100%; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--background); color: var(--text-main); font-size: 0.9rem;">
                    <?php foreach ($jenisRubrikMaster as $kode => $meta): ?>
                        <option value="<?= htmlspecialchars($kode) ?>" <?= (($_POST['kode_jenis'] ?? 'RB-ANA') === $kode) ? 'selected' : '' ?>><?= htmlspecialchars($kode . ' - ' . $meta['jenis']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.88rem; color: var(--text-main);">
                    Status
                </label>
                <select name="status" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--background); color: var(--text-main); font-size: 0.9rem;">
                    <option value="ACTIVE" selected>ACTIVE (Dapat Digunakan)</option>
                    <option value="ARCHIVED">ARCHIVED (Diarsipkan)</option>
                </select>
            </div>
        </div>
        <div id="jenisRubrikInfo" style="margin-top:14px;padding:12px 14px;border-radius:var(--radius-sm);background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);font-size:.84rem;color:var(--text-muted);"></div>
    </div>

    <!-- Card 2: Pengaturan Level Capaian / Skor -->
    <div class="card" style="background: var(--surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <h4 style="margin: 0; color: var(--text-main); font-size: 1.05rem;">
                <i class="fa-solid fa-ranking-star" style="color: var(--warning); margin-right: 8px;"></i>
                Level Tingkat Capaian & Skor (Kolom Matriks)
            </h4>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addLevel()" style="font-size: 0.8rem;">
                <i class="fa-solid fa-plus"></i> Tambah Level
            </button>
        </div>

        <div id="levelsContainer" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <!-- Initial 4 standard OBE levels -->
            <div class="level-item" style="background: var(--background); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); position: relative;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">LEVEL 1</div>
                <input type="text" name="level_nama[]" value="Sangat Kurang" required placeholder="Nama level" style="width: 100%; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Skor:</span>
                    <input type="number" step="0.5" name="level_skor[]" value="50" required placeholder="Skor" style="width: 100%; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                </div>
            </div>

            <div class="level-item" style="background: var(--background); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); position: relative;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">LEVEL 2</div>
                <input type="text" name="level_nama[]" value="Cukup" required placeholder="Nama level" style="width: 100%; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Skor:</span>
                    <input type="number" step="0.5" name="level_skor[]" value="65" required placeholder="Skor" style="width: 100%; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                </div>
            </div>

            <div class="level-item" style="background: var(--background); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); position: relative;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">LEVEL 3</div>
                <input type="text" name="level_nama[]" value="Baik" required placeholder="Nama level" style="width: 100%; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Skor:</span>
                    <input type="number" step="0.5" name="level_skor[]" value="80" required placeholder="Skor" style="width: 100%; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                </div>
            </div>

            <div class="level-item" style="background: var(--background); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); position: relative;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">LEVEL 4</div>
                <input type="text" name="level_nama[]" value="Sangat Baik" required placeholder="Nama level" style="width: 100%; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Skor:</span>
                    <input type="number" step="0.5" name="level_skor[]" value="100" required placeholder="Skor" style="width: 100%; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Matriks Kriteria & Deskriptor -->
    <div class="card" style="background: var(--surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h4 style="margin: 0; color: var(--text-main); font-size: 1.05rem;">
                    <i class="fa-solid fa-list-check" style="color: var(--success); margin-right: 8px;"></i>
                    Matriks Kriteria & Deskriptor Kinerja
                </h4>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                    Total Bobot Kriteria saat ini: <strong id="totalBobotLabel" style="color: var(--primary);">0%</strong> 
                    <span id="bobotWarning" style="margin-left: 8px; font-weight: 600; color: var(--warning);">(Wajib berjumlah 100%)</span>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addKriteria()" style="font-size: 0.8rem;">
                <i class="fa-solid fa-plus"></i> Tambah Kriteria
            </button>
        </div>

        <div id="kriteriaContainer" style="display: flex; flex-direction: column; gap: 18px;">
            <!-- Kriteria rows will be populated via JS -->
        </div>
    </div>

    <!-- Submit Button -->
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
        <a href="<?= $baseUrl ?>/rubrik" class="btn btn-secondary" style="padding: 10px 24px;"><i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Batal</a>
        <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-size: 0.95rem;">
            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Rubrik Penilaian
        </button>
    </div>

</form>

<script>
const jenisRubrikMaster = <?= json_encode($jenisRubrikMaster, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
function updateJenisRubrikInfo() {
    const select = document.getElementById('kodeJenisRubrik');
    const info = document.getElementById('jenisRubrikInfo');
    const meta = jenisRubrikMaster[select?.value];
    if (info && meta) info.innerHTML = `<strong>Karakteristik:</strong> ${meta.karakteristik}<br><strong>Contoh penggunaan:</strong> ${meta.contoh}`;
}
document.getElementById('kodeJenisRubrik')?.addEventListener('change', updateJenisRubrikInfo);
updateJenisRubrikInfo();
let kriteriaCount = 0;

function updateLevelsFromUI() {
    const levelInputs = document.querySelectorAll('#levelsContainer input[name="level_nama[]"]');
    const levels = [];
    levelInputs.forEach((input, index) => {
        levels.push(input.value || `Level ${index + 1}`);
    });
    return levels;
}

function calculateTotalBobot() {
    const bobotInputs = document.querySelectorAll('input[name="kriteria_bobot[]"]');
    let total = 0;
    bobotInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    const label = document.getElementById('totalBobotLabel');
    const warning = document.getElementById('bobotWarning');
    
    label.innerText = total.toFixed(1) + '%';
    
    if (Math.abs(total - 100.0) < 0.01) {
        label.style.color = 'var(--success)';
        warning.innerHTML = '<span style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> Total bobot valid: 100%</span>';
    } else {
        label.style.color = 'var(--warning)';
        const diff = (100.0 - total).toFixed(1);
        warning.innerHTML = `<span style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation"></i> Selisih: ${diff}% (Harus 100%)</span>`;
    }
}

function addKriteria(nama = '', bobot = 25, defaultDescs = []) {
    const kIdx = kriteriaCount++;
    const levels = updateLevelsFromUI();

    const container = document.getElementById('kriteriaContainer');
    const item = document.createElement('div');
    item.className = 'kriteria-row';
    item.id = `kriteria-row-${kIdx}`;
    item.style.background = 'var(--background)';
    item.style.border = '1px solid var(--border)';
    item.style.borderRadius = 'var(--radius-sm)';
    item.style.padding = '18px';

    let descFieldsHtml = '';
    levels.forEach((lvl, lIdx) => {
        const val = defaultDescs[lIdx] || '';
        descFieldsHtml += `
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">
                    Deskriptor [${lvl}]
                </label>
                <textarea name="deskriptor[${kIdx}][${lIdx}]" rows="3" placeholder="Jelaskan kualitas kinerja untuk ${lvl}..."
                          style="width: 100%; padding: 8px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.8rem; resize: vertical;">${val}</textarea>
            </div>
        `;
    });

    item.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 600px;">
                <span style="font-weight: 700; color: var(--primary); font-size: 0.85rem; background: rgba(99, 102, 241, 0.1); padding: 4px 8px; border-radius: 4px;">
                    #${kIdx + 1}
                </span>
                <input type="text" name="kriteria_nama[${kIdx}]" value="${nama}" required placeholder="Nama Kriteria (contoh: Identifikasi Masalah & Analisis)" 
                       style="flex: 2; padding: 8px 12px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Bobot:</span>
                    <input type="number" step="0.5" name="kriteria_bobot[${kIdx}]" value="${bobot}" required oninput="calculateTotalBobot()"
                           style="width: 70px; padding: 8px 8px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem; text-align: center;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">%</span>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeKriteria(${kIdx})" style="padding: 4px 8px;">
                <i class="fa-solid fa-trash"></i> Hapus Kriteria
            </button>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
            ${descFieldsHtml}
        </div>
    `;

    container.appendChild(item);
    calculateTotalBobot();
}

function removeKriteria(kIdx) {
    const row = document.getElementById(`kriteria-row-${kIdx}`);
    if (row) {
        row.remove();
        calculateTotalBobot();
    }
}

function addLevel() {
    const container = document.getElementById('levelsContainer');
    const count = container.children.length + 1;
    const div = document.createElement('div');
    div.className = 'level-item';
    div.style.background = 'var(--background)';
    div.style.padding = '12px';
    div.style.borderRadius = 'var(--radius-sm)';
    div.style.border = '1px solid var(--border)';
    div.innerHTML = `
        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 4px;">LEVEL ${count}</div>
        <input type="text" name="level_nama[]" value="Level ${count}" required placeholder="Nama level" style="width: 100%; padding: 6px 10px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 0.75rem; color: var(--text-muted);">Skor:</span>
            <input type="number" step="0.5" name="level_skor[]" value="${count * 20}" required placeholder="Skor" style="width: 100%; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.85rem;">
        </div>
    `;
    container.appendChild(div);
}

// Inisialisasi awal 3 kriteria default
document.addEventListener('DOMContentLoaded', () => {
    addKriteria('Identifikasi Masalah & Teori', 30, [
        'Tidak mampu mengidentifikasi masalah utama dan tidak menggunakan teori.',
        'Mampu mengidentifikasi sebagian masalah namun teori kurang relevan.',
        'Mampu mengidentifikasi masalah dengan baik didukung konsep teori yang tepat.',
        'Identifikasi masalah sangat komprehensif, kritis, dan didukung rujukan teori mutakhir.'
    ]);
    addKriteria('Analisis & Perancangan Solusi', 40, [
        'Solusi tidak relevan dengan permasalahan yang dihadapi.',
        'Solusi bersifat konvensional dan analisis belum mendalam.',
        'Solusi terstruktur dengan analisis data yang jelas dan logis.',
        'Solusi inovatif, efisien, dengan analisis risiko dan metodologi yang sangat matang.'
    ]);
    addKriteria('Penyajian Laporan & Presentasi', 30, [
        'Laporan tidak terstruktur dan presentasi pasif.',
        'Format laporan cukup rapi namun argumentasi presentasi masih lemah.',
        'Laporan disusun sistematis dan mampu mempresentasikan hasil dengan percaya diri.',
        'Laporan sangat profesional, visualisasi data prima, dan argumentasi meyakinkan.'
    ]);
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
