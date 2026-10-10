<?php
$title = "Rubric Builder: " . htmlspecialchars($rubrik['nama']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <a href="<?= $baseUrl ?>/rubrik" class="btn btn-sm btn-secondary" style="padding: 4px 8px;">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;"><?= htmlspecialchars($rubrik['nama']) ?></h2>
            <span style="background: rgba(99, 102, 241, 0.15);
                         color: var(--primary);
                         border: 1px solid rgba(99, 102, 241, 0.3);
                         padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                <?= htmlspecialchars($rubrik['jenis']) ?>
            </span>
        </div>
        <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
            <?= htmlspecialchars($rubrik['deskripsi'] ?? 'Susun kriteria penilaian, tentukan level performa dan skor, serta rumuskan deskriptor indikator capaian.') ?>
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" onclick="togglePreviewMode()" id="btnTogglePreview" class="btn btn-secondary">
            <i class="fa-solid fa-eye"></i> Mode Pratinjau
        </button>
        <button type="button" onclick="submitRubricForm()" class="btn btn-primary" style="background: var(--success); border-color: var(--success);">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Matriks Rubrik
        </button>
    </div>
</div>

<!-- Flash Notifications -->
<?php if (!empty($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
    </div>
<?php endif; ?>

<!-- Weight Indicator Card (PRD 6.5) -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">
            <i class="fa-solid fa-scale-balanced" style="color: var(--primary); margin-right: 8px;"></i>
            Indikator Bobot Kriteria
        </div>
        <div id="weightSummaryText" style="font-weight: 700; font-size: 1rem;">
            0 / 100%
        </div>
    </div>
    <div style="width: 100%; height: 10px; background: var(--border); border-radius: 999px; overflow: hidden; position: relative;">
        <div id="weightProgressBar" style="height: 100%; width: 0%; background: var(--primary); transition: width 0.3s ease, background 0.3s ease;"></div>
    </div>
    <div id="weightAlertText" style="font-size: 0.8rem; margin-top: 6px; font-weight: 500;"></div>
</div>

<!-- Matrix Editor View -->
<div id="matrixEditorContainer">
    <form id="formRubrikMatrix" action="<?= $baseUrl ?>/rubrik/save-matrix" method="POST">
        <input type="hidden" name="rubrik_id" value="<?= $rubrik['id'] ?>">

        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; margin-bottom: 20px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
                <div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Matriks Kriteria & Deskriptor Level</h3>
                    <p style="margin: 3px 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                        Setiap sel mendefinisikan capaian yang diharapkan mahasiswa pada level skor tersebut.
                    </p>
                </div>
                <button type="button" onclick="addNewLevelColumn()" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-plus"></i> Tambah Kolom Level
                </button>
            </div>

            <div style="overflow-x: auto; padding: 10px;">
                <table id="tableRubricMatrix" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 900px;">
                    <thead>
                        <tr id="matrixHeaderRow">
                            <th style="width: 220px; min-width: 200px; padding: 12px; background: var(--surface-hover); border-bottom: 2px solid var(--border); border-right: 1px solid var(--border); text-align: left; vertical-align: top;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">KRITERIA PENILAIAN</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Dimensi asesmen</div>
                            </th>
                            <th style="width: 100px; min-width: 90px; padding: 12px; background: var(--surface-hover); border-bottom: 2px solid var(--border); border-right: 1px solid var(--border); text-align: center; vertical-align: top;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">BOBOT (%)</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Kontribusi</div>
                            </th>
                            <!-- Level Columns inserted dynamically / from PHP -->
                            <?php foreach ($levels as $lIdx => $lvl): ?>
                                <th class="level-header-col" data-level-key="<?= $lIdx ?>" style="min-width: 180px; padding: 10px; background: var(--surface-hover); border-bottom: 2px solid var(--border); border-right: 1px solid var(--border); vertical-align: top;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <span style="font-size: 0.75rem; font-weight: 600; color: var(--primary);">LEVEL <?= $lIdx + 1 ?></span>
                                        <button type="button" onclick="removeLevelColumn(<?= $lIdx ?>)" class="btn-remove-level" title="Hapus Level" style="background: transparent; border: none; color: var(--danger); cursor: pointer; padding: 2px 4px; font-size: 0.8rem;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                    <div style="display: flex; gap: 6px;">
                                        <input type="text" name="levels[<?= $lIdx ?>][level_nama]" value="<?= htmlspecialchars($lvl['level_nama']) ?>" placeholder="Nama Level" style="width: 65%; font-size: 0.85rem; padding: 6px;" required>
                                        <input type="number" step="0.1" name="levels[<?= $lIdx ?>][skor]" value="<?= htmlspecialchars($lvl['skor']) ?>" placeholder="Skor" style="width: 35%; font-size: 0.85rem; padding: 6px; text-align: center;" required>
                                    </div>
                                    <input type="hidden" name="levels[<?= $lIdx ?>][key]" value="<?= $lIdx ?>">
                                    <input type="hidden" name="levels[<?= $lIdx ?>][urutan]" value="<?= $lvl['urutan'] ?? ($lIdx + 1) ?>">
                                </th>
                            <?php endforeach; ?>
                            <th style="width: 50px; padding: 12px; background: var(--surface-hover); border-bottom: 2px solid var(--border); text-align: center; vertical-align: middle;">
                                <i class="fa-solid fa-ellipsis" style="color: var(--text-muted);"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="matrixBody">
                        <?php foreach ($kriterias as $kIdx => $krit): ?>
                            <tr class="criteria-row" data-criteria-key="<?= $kIdx ?>" style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 10px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top;">
                                    <input type="hidden" name="kriterias[<?= $kIdx ?>][key]" value="<?= $kIdx ?>">
                                    <input type="hidden" name="kriterias[<?= $kIdx ?>][urutan]" value="<?= $krit['urutan'] ?? ($kIdx + 1) ?>">
                                    <textarea name="kriterias[<?= $kIdx ?>][kriteria]" rows="3" required placeholder="Tulis kriteria penilaian..." style="width: 100%; resize: vertical; font-size: 0.85rem; padding: 8px; font-weight: 500;"><?= htmlspecialchars($krit['kriteria']) ?></textarea>
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top; text-align: center;">
                                    <input type="number" step="0.1" min="0" max="100" name="kriterias[<?= $kIdx ?>][bobot]" value="<?= htmlspecialchars($krit['bobot']) ?>" class="criteria-bobot-input" oninput="calculateTotalWeight()" style="width: 80px; text-align: center; font-weight: 700; font-size: 0.9rem;" required>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">%</div>
                                </td>
                                <?php foreach ($levels as $lIdx => $lvl): 
                                    $descVal = $deskriptors[$krit['id']][$lvl['id']] ?? '';
                                ?>
                                    <td class="descriptor-cell" data-level-key="<?= $lIdx ?>" style="padding: 8px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top;">
                                        <textarea name="deskriptors[<?= $kIdx ?>][<?= $lIdx ?>]" rows="4" placeholder="Deskripsi indikator level <?= htmlspecialchars($lvl['level_nama']) ?>..." style="width: 100%; font-size: 0.8rem; line-height: 1.4; resize: vertical; padding: 8px;"><?= htmlspecialchars($descVal) ?></textarea>
                                    </td>
                                <?php endforeach; ?>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center; vertical-align: middle;">
                                    <button type="button" onclick="removeCriteriaRow(<?= $kIdx ?>)" class="btn-remove-criteria" title="Hapus Kriteria" style="background: transparent; border: none; color: var(--danger); cursor: pointer; padding: 6px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding: 14px 20px; border-top: 1px solid var(--border); background: rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                <button type="button" onclick="addNewCriteriaRow()" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-plus"></i> Tambah Kriteria Baru
                </button>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    Tip: Bobot seluruh kriteria analitik wajib berjumlah 100% agar perhitungan OBE valid.
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Mode View (Clean Read-Only Layout) -->
<div id="matrixPreviewContainer" style="display: none;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 20px;">
        <div style="text-align: center; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
            <h3 style="margin: 0 0 6px 0; font-size: 1.3rem;"><?= htmlspecialchars($rubrik['nama']) ?></h3>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                Format: <strong><?= htmlspecialchars($rubrik['jenis']) ?></strong> &bull; Status: Active
            </p>
        </div>

        <div id="previewTableWrapper" style="overflow-x: auto;">
            <!-- Generated dynamically by togglePreviewMode() -->
        </div>
    </div>
</div>

<script>
let currentLevelCount = <?= count($levels) ?>;
let currentCriteriaCount = <?= count($kriterias) ?>;

// Inisialisasi perhitungan bobot
document.addEventListener('DOMContentLoaded', () => {
    calculateTotalWeight();
});

function calculateTotalWeight() {
    const inputs = document.querySelectorAll('.criteria-bobot-input');
    let total = 0;
    inputs.forEach(inp => {
        const val = parseFloat(inp.value) || 0;
        total += val;
    });

    total = Math.round(total * 10) / 10;
    const summaryText = document.getElementById('weightSummaryText');
    const progressBar = document.getElementById('weightProgressBar');
    const alertText = document.getElementById('weightAlertText');

    summaryText.innerText = total + ' / 100%';
    const pctClamped = Math.min(total, 100);
    progressBar.style.width = pctClamped + '%';

    if (Math.abs(total - 100) < 0.01) {
        summaryText.style.color = 'var(--success)';
        progressBar.style.background = 'var(--success)';
        alertText.style.color = 'var(--success)';
        alertText.innerHTML = '<i class="fa-solid fa-circle-check"></i> Total bobot valid: tepat 100%.';
    } else if (total < 100) {
        summaryText.style.color = 'var(--warning)';
        progressBar.style.background = 'var(--warning)';
        alertText.style.color = 'var(--warning)';
        const kurang = (100 - total).toFixed(1);
        alertText.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Total bobot saat ini ${total}%. Masih kurang ${kurang}% untuk mencapai 100%.`;
    } else {
        summaryText.style.color = 'var(--danger)';
        progressBar.style.background = 'var(--danger)';
        alertText.style.color = 'var(--danger)';
        const lebih = (total - 100).toFixed(1);
        alertText.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> Total bobot melebihi batas (${total}%). Harap kurangi ${lebih}%.`;
    }
}

function addNewLevelColumn() {
    const newIdx = currentLevelCount;
    currentLevelCount++;

    const headerRow = document.getElementById('matrixHeaderRow');
    const lastTh = headerRow.lastElementChild; // Aksi header

    const th = document.createElement('th');
    th.className = 'level-header-col';
    th.dataset.levelKey = newIdx;
    th.style.minWidth = '180px';
    th.style.padding = '10px';
    th.style.background = 'var(--surface-hover)';
    th.style.borderBottom = '2px solid var(--border)';
    th.style.borderRight = '1px solid var(--border)';
    th.style.verticalAlign = 'top';

    th.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <span style="font-size: 0.75rem; font-weight: 600; color: var(--primary);">LEVEL ${newIdx + 1}</span>
            <button type="button" onclick="removeLevelColumn(${newIdx})" class="btn-remove-level" title="Hapus Level" style="background: transparent; border: none; color: var(--danger); cursor: pointer; padding: 2px 4px; font-size: 0.8rem;">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>
        <div style="display: flex; gap: 6px;">
            <input type="text" name="levels[${newIdx}][level_nama]" value="Level Baru" placeholder="Nama Level" style="width: 65%; font-size: 0.85rem; padding: 6px;" required>
            <input type="number" step="0.1" name="levels[${newIdx}][skor]" value="${(newIdx + 1) * 25}" placeholder="Skor" style="width: 35%; font-size: 0.85rem; padding: 6px; text-align: center;" required>
        </div>
        <input type="hidden" name="levels[${newIdx}][key]" value="${newIdx}">
        <input type="hidden" name="levels[${newIdx}][urutan]" value="${newIdx + 1}">
    `;

    headerRow.insertBefore(th, lastTh);

    // Tambah sel pada setiap baris kriteria
    const rows = document.querySelectorAll('.criteria-row');
    rows.forEach(r => {
        const kIdx = r.dataset.criteriaKey;
        const lastTd = r.lastElementChild; // Aksi cell

        const td = document.createElement('td');
        td.className = 'descriptor-cell';
        td.dataset.levelKey = newIdx;
        td.style.padding = '8px';
        td.style.borderBottom = '1px solid var(--border)';
        td.style.borderRight = '1px solid var(--border)';
        td.style.verticalAlign = 'top';

        td.innerHTML = `<textarea name="deskriptors[${kIdx}][${newIdx}]" rows="4" placeholder="Deskripsi indikator capaian..." style="width: 100%; font-size: 0.8rem; line-height: 1.4; resize: vertical; padding: 8px;"></textarea>`;
        r.insertBefore(td, lastTd);
    });
}

function removeLevelColumn(levelKey) {
    const levelHeaders = document.querySelectorAll('.level-header-col');
    if (levelHeaders.length <= 2) {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Minimum Level',
            text: 'Rubrik harus memiliki minimal 2 level performa.'
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Kolom Level Ini?',
        text: 'Deskriptor pada kolom ini akan dihapus dari seluruh kriteria.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus Kolom',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Hapus th
            const th = document.querySelector(`th.level-header-col[data-level-key="${levelKey}"]`);
            if (th) th.remove();

            // Hapus seluruh td pada kriteria
            const tds = document.querySelectorAll(`td.descriptor-cell[data-level-key="${levelKey}"]`);
            tds.forEach(td => td.remove());
        }
    });
}

function addNewCriteriaRow() {
    const kIdx = currentCriteriaCount;
    currentCriteriaCount++;

    const tbody = document.getElementById('matrixBody');
    const tr = document.createElement('tr');
    tr.className = 'criteria-row';
    tr.dataset.criteriaKey = kIdx;
    tr.style.borderBottom = '1px solid var(--border)';

    let html = `
        <td style="padding: 10px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top;">
            <input type="hidden" name="kriterias[${kIdx}][key]" value="${kIdx}">
            <input type="hidden" name="kriterias[${kIdx}][urutan]" value="${kIdx + 1}">
            <textarea name="kriterias[${kIdx}][kriteria]" rows="3" required placeholder="Tulis kriteria penilaian baru..." style="width: 100%; resize: vertical; font-size: 0.85rem; padding: 8px; font-weight: 500;"></textarea>
        </td>
        <td style="padding: 10px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top; text-align: center;">
            <input type="number" step="0.1" min="0" max="100" name="kriterias[${kIdx}][bobot]" value="0" class="criteria-bobot-input" oninput="calculateTotalWeight()" style="width: 80px; text-align: center; font-weight: 700; font-size: 0.9rem;" required>
            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">%</div>
        </td>
    `;

    // Ambil kolom level yang aktif
    const levelHeaders = document.querySelectorAll('.level-header-col');
    levelHeaders.forEach(th => {
        const lKey = th.dataset.levelKey;
        html += `
            <td class="descriptor-cell" data-level-key="${lKey}" style="padding: 8px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); vertical-align: top;">
                <textarea name="deskriptors[${kIdx}][${lKey}]" rows="4" placeholder="Deskripsi indikator capaian..." style="width: 100%; font-size: 0.8rem; line-height: 1.4; resize: vertical; padding: 8px;"></textarea>
            </td>
        `;
    });

    html += `
        <td style="padding: 10px; border-bottom: 1px solid var(--border); text-align: center; vertical-align: middle;">
            <button type="button" onclick="removeCriteriaRow(${kIdx})" class="btn-remove-criteria" title="Hapus Kriteria" style="background: transparent; border: none; color: var(--danger); cursor: pointer; padding: 6px; font-size: 0.9rem;">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;

    tr.innerHTML = html;
    tbody.appendChild(tr);
    calculateTotalWeight();
}

function removeCriteriaRow(kIdx) {
    const rows = document.querySelectorAll('.criteria-row');
    if (rows.length <= 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Minimum Kriteria',
            text: 'Rubrik harus memiliki minimal 1 kriteria penilaian.'
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Kriteria Ini?',
        text: 'Seluruh deskriptor pada kriteria ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const row = document.querySelector(`tr.criteria-row[data-criteria-key="${kIdx}"]`);
            if (row) {
                row.remove();
                calculateTotalWeight();
            }
        }
    });
}

function submitRubricForm() {
    const inputs = document.querySelectorAll('.criteria-bobot-input');
    let total = 0;
    inputs.forEach(inp => total += (parseFloat(inp.value) || 0));
    total = Math.round(total * 10) / 10;

    if (Math.abs(total - 100) > 0.01) {
        Swal.fire({
            title: 'Perhatian: Total Bobot Belum 100%',
            html: `Total bobot kriteria saat ini adalah <b>${total}%</b>.<br>Apakah Anda tetap ingin menyimpan matriks ini sebagai draf?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Tetap Simpan',
            cancelButtonText: 'Perbaiki Bobot'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formRubrikMatrix').submit();
            }
        });
    } else {
        document.getElementById('formRubrikMatrix').submit();
    }
}

let isPreview = false;
function togglePreviewMode() {
    isPreview = !isPreview;
    const editor = document.getElementById('matrixEditorContainer');
    const preview = document.getElementById('matrixPreviewContainer');
    const btn = document.getElementById('btnTogglePreview');

    if (isPreview) {
        buildPreviewTable();
        editor.style.display = 'none';
        preview.style.display = 'block';
        btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Kembali ke Editor';
        btn.className = 'btn btn-primary';
    } else {
        preview.style.display = 'none';
        editor.style.display = 'block';
        btn.innerHTML = '<i class="fa-solid fa-eye"></i> Mode Pratinjau';
        btn.className = 'btn btn-secondary';
    }
}

function buildPreviewTable() {
    const levelHeaders = document.querySelectorAll('.level-header-col');
    const criteriaRows = document.querySelectorAll('.criteria-row');

    let tableHtml = `
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: var(--surface-hover); border-bottom: 2px solid var(--border);">
                    <th style="padding: 12px 16px; border: 1px solid var(--border); width: 22%;">Kriteria</th>
                    <th style="padding: 12px 16px; border: 1px solid var(--border); width: 10%; text-align: center;">Bobot</th>
    `;

    levelHeaders.forEach(th => {
        const nameInput = th.querySelector('input[name*="[level_nama]"]');
        const scoreInput = th.querySelector('input[name*="[skor]"]');
        const name = nameInput ? nameInput.value : 'Level';
        const score = scoreInput ? scoreInput.value : '0';
        tableHtml += `
            <th style="padding: 12px 16px; border: 1px solid var(--border); text-align: center;">
                <div style="font-weight: 700; color: var(--primary);">${name}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">Skor: ${score}</div>
            </th>
        `;
    });

    tableHtml += `</tr></thead><tbody>`;

    criteriaRows.forEach(row => {
        const kritTextarea = row.querySelector('textarea[name*="[kriteria]"]');
        const bobotInput = row.querySelector('.criteria-bobot-input');
        const kritName = kritTextarea ? kritTextarea.value : '-';
        const bobotVal = bobotInput ? bobotInput.value : '0';

        tableHtml += `
            <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 12px 16px; border: 1px solid var(--border); font-weight: 600; vertical-align: top;">
                    ${kritName.replace(/\n/g, '<br>')}
                </td>
                <td style="padding: 12px 16px; border: 1px solid var(--border); text-align: center; vertical-align: top; font-weight: 700;">
                    ${bobotVal}%
                </td>
        `;

        levelHeaders.forEach(th => {
            const lKey = th.dataset.levelKey;
            const descTd = row.querySelector(`td.descriptor-cell[data-level-key="${lKey}"]`);
            const descTextarea = descTd ? descTd.querySelector('textarea') : null;
            const descText = descTextarea && descTextarea.value.trim() !== '' ? descTextarea.value.replace(/\n/g, '<br>') : '<span style="color: var(--text-muted); font-style: italic;">-</span>';

            tableHtml += `
                <td style="padding: 12px 16px; border: 1px solid var(--border); vertical-align: top; font-size: 0.85rem; line-height: 1.5;">
                    ${descText}
                </td>
            `;
        });

        tableHtml += `</tr>`;
    });

    tableHtml += `</tbody></table>`;
    document.getElementById('previewTableWrapper').innerHTML = tableHtml;
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
