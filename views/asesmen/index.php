<?php
// views/asesmen/index.php
$title = "Input Nilai Asesmen & Rubrik - Sistem OBE";
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Asesmen & Penilaian Sub-CPMK</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Input penilaian mahasiswa berbasis bukti asesmen dan penilaian rubrik analitik/holistik (PRD FR-12, FR-13).
        </p>
    </div>
</div>

<!-- Filter Pilih Kurikulum & Kelas -->
<div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form action="<?= $baseUrl ?>/asesmen" method="GET" id="formFilterAsesmen" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Filter Kurikulum -->
        <div style="width: 270px; min-width: 230px;">
            <label for="filterKurikulum" style="display: block; margin-bottom: 8px; font-size: 0.88rem; font-weight: 600; color: var(--text-main);">
                <i class="fa-solid fa-book-bookmark" style="color: var(--primary); margin-right: 6px;"></i> Filter Kurikulum:
            </label>
            <select name="kurikulum_id" id="filterKurikulum" onchange="handleKurikulumFilterChange(this)" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 500;">
                <option value="">-- Semua Kurikulum --</option>
                <?php if (!empty($kurikulumList)): ?>
                    <?php foreach($kurikulumList as $kur): ?>
                        <option value="<?= $kur['id'] ?>" <?= (!empty($selectedKurikulumId) && $selectedKurikulumId == $kur['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kur['nama'] . ' (' . $kur['kode'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Pilih Kelas Kuliah -->
        <div style="flex: 1; min-width: 320px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label for="selectKelas" style="font-size: 0.88rem; font-weight: 600; color: var(--text-main);">
                    <i class="fa-solid fa-chalkboard-user" style="color: var(--primary); margin-right: 6px;"></i> Pilih Kelas Kuliah:
                </label>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">
                    <?= count($kelasList) ?> Kelas Tersedia
                </span>
            </div>
            <select name="kelas_id" id="selectKelas" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); border-radius: var(--radius-sm); font-size: 0.9rem;">
                <option value="">-- Pilih Kelas yang Anda Ampu --</option>
                <?php 
                if (empty($selectedKurikulumId)) {
                    // Kelompokkan per kurikulum jika opsi "Semua Kurikulum"
                    $groupedKelas = [];
                    foreach ($kelasList as $k) {
                        $kNama = !empty($k['kurikulum_nama']) ? $k['kurikulum_nama'] : 'Kurikulum Lain';
                        $groupedKelas[$kNama][] = $k;
                    }
                    foreach ($groupedKelas as $kNama => $items): ?>
                        <optgroup label="Kurikulum: <?= htmlspecialchars($kNama) ?>">
                            <?php foreach ($items as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (!empty($kelas) && $kelas['id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['tahun_akademik_nama'] . ' - ' . $k['mata_kuliah_kode'] . ' ' . $k['mata_kuliah_nama'] . ' (' . $k['nama_kelas'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach;
                } else {
                    foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= (!empty($kelas) && $kelas['id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['tahun_akademik_nama'] . ' - ' . $k['mata_kuliah_kode'] . ' ' . $k['mata_kuliah_nama'] . ' (' . $k['nama_kelas'] . ')') ?>
                        </option>
                    <?php endforeach;
                } ?>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                <i class="fa-solid fa-table-list"></i> Tampilkan Form Penilaian
            </button>
            <?php if (!empty($selectedKurikulumId) || !empty($kelas_id)): ?>
                <a href="<?= $baseUrl ?>/asesmen" class="btn btn-secondary" style="padding: 10px 14px; display: inline-flex; align-items: center; gap: 6px;" title="Reset Filter">
                    <i class="fa-solid fa-arrows-rotate"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
        <div>
            <div style="font-weight: 600;">Berhasil Disimpan!</div>
            <div style="font-size: 0.85rem; opacity: 0.9;">Seluruh nilai asesmen mahasiswa telah tersimpan ke basis data dan terintegrasi dengan perhitungan capaian CPL.</div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($kelas)): ?>
    <!-- Info Ringkas Kelas Aktif -->
    <div style="background: var(--surface); padding: 18px 24px; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: center;">
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Mata Kuliah</span>
                <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    <?= htmlspecialchars($kelas['mata_kuliah_kode']) ?> - <?= htmlspecialchars($kelas['mata_kuliah_nama']) ?>
                </div>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Kurikulum</span>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary);">
                    <i class="fa-solid fa-book" style="margin-right: 4px;"></i>
                    <?= htmlspecialchars($kelas['kurikulum_nama'] ?? 'Kurikulum OBE') ?>
                </div>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Kelas & Semester</span>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--primary);">
                    Kelas <?= htmlspecialchars($kelas['nama_kelas']) ?> (<?= htmlspecialchars($kelas['tahun_akademik_nama']) ?>)
                </div>
            </div>
            <div>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Peserta</span>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--text-main);">
                    <?= count($peserta) ?> Mahasiswa
                </div>
            </div>
        </div>

        <div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()" title="Cetak Rekap Nilai">
                <i class="fa-solid fa-print"></i> Cetak Rekap
            </button>
        </div>
    </div>

    <div style="background: var(--surface); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <?php if (empty($sub_cpmk_list)): ?>
            <div style="text-align: center; color: var(--danger); padding: 40px 20px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 12px; display: block;"></i>
                <strong>Perhatian:</strong> Mata kuliah ini belum memiliki Sub-CPMK terdaftar di RPS.<br>
                <span style="font-size: 0.88rem; color: var(--text-muted); margin-top: 4px; display: inline-block;">
                    Silakan lengkapi master Sub-CPMK pada modul <a href="<?= $baseUrl ?>/rps" style="color: var(--primary);">RPS</a> terlebih dahulu sebelum melakukan penilaian.
                </span>
            </div>
        <?php elseif (empty($peserta)): ?>
            <div style="text-align: center; color: var(--text-muted); padding: 40px 20px;">
                <i class="fa-solid fa-user-xmark" style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 12px; display: block;"></i>
                Belum ada mahasiswa yang terdaftar di kelas ini.<br>
                <a href="<?= $baseUrl ?>/kelas/peserta?id=<?= $kelas['id'] ?>" class="btn btn-sm btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-user-plus"></i> Kelola Peserta Kelas
                </a>
            </div>
        <?php else: ?>
            <form action="<?= $baseUrl ?>/asesmen/save" method="POST" id="formNilaiAsesmen">
                <input type="hidden" name="kelas_id" value="<?= $kelas['id'] ?>">
                
                <div style="margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">Matriks Penilaian Capaian Pembelajaran</h3>
                        <p style="margin: 2px 0 0 0; font-size: 0.82rem; color: var(--text-muted);">
                            Skor rentang 0 - 100. Klik tombol <span style="color: var(--primary); font-weight: 600;">🎯</span> pada tiap kolom untuk menilai menggunakan Rubrik OBE.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 9px 20px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Nilai
                    </button>
                </div>

                <table style="width: 100%; border-collapse: collapse; min-width: 850px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="padding: 12px 14px; border: 1px solid var(--border); color: var(--text-muted); background: var(--bg-hover); text-align: center; width: 45px; font-size: 0.82rem;">NO</th>
                            <th rowspan="2" style="padding: 12px 14px; border: 1px solid var(--border); color: var(--text-muted); background: var(--bg-hover); text-align: left; width: 220px; font-size: 0.82rem;">NIM & NAMA MAHASISWA</th>
                            <th colspan="<?= count($sub_cpmk_list) ?>" style="padding: 10px 14px; border: 1px solid var(--border); color: var(--text-main); background: var(--bg-hover); text-align: center; font-size: 0.85rem; font-weight: 700;">
                                BUKTI ASESMEN SUB-CPMK
                            </th>
                        </tr>
                        <tr>
                            <?php foreach($sub_cpmk_list as $sc): ?>
                                <th style="padding: 10px 12px; border: 1px solid var(--border); color: var(--text-main); background: var(--surface); text-align: center; font-size: 0.82rem; min-width: 135px;" title="<?= htmlspecialchars($sc['rumusan']) ?>">
                                    <div style="font-weight: 700; color: var(--primary); font-family: monospace;"><?= htmlspecialchars($sc['kode']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Bobot: <?= floatval($sc['bobot']) ?>%</div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($peserta as $p): ?>
                            <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                                <td style="padding: 10px 12px; border: 1px solid var(--border); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                                    <?= $no++ ?>
                                </td>
                                <td style="padding: 10px 14px; border: 1px solid var(--border);">
                                    <div style="font-family: monospace; font-size: 0.85rem; font-weight: 700; color: var(--primary);">
                                        <?= htmlspecialchars($p['username']) ?>
                                    </div>
                                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--text-main); margin-top: 2px;">
                                        <?= htmlspecialchars($p['nama_lengkap']) ?>
                                    </div>
                                </td>
                                <?php foreach($sub_cpmk_list as $sc): 
                                    $skor = isset($nilai_matrix[$p['id']][$sc['id']]) ? floatval($nilai_matrix[$p['id']][$sc['id']]) : '';
                                    $cellId = "input_{$p['id']}_{$sc['id']}";
                                ?>
                                    <td style="padding: 8px 10px; border: 1px solid var(--border); text-align: center;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <input type="number" step="0.01" min="0" max="100" 
                                                   id="<?= $cellId ?>"
                                                   name="nilai[<?= $p['id'] ?>][<?= $sc['id'] ?>]" 
                                                   value="<?= $skor !== '' ? $skor : '' ?>"
                                                   placeholder="-"
                                                   style="width: 72px; padding: 7px 6px; border: 1px solid var(--border); background: var(--bg-hover); color: var(--text-main); border-radius: var(--radius-sm); text-align: center; font-size: 0.9rem; font-weight: 600;">
                                            
                                            <!-- Tombol Evaluasi Rubrik -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary" 
                                                    style="padding: 6px 8px; font-size: 0.75rem; border-radius: 4px;"
                                                    title="Buka Rubrik Penilaian untuk Sub-CPMK ini"
                                                    onclick="window.openRubrikModal('<?= $p['id'] ?>', '<?= htmlspecialchars($p['nama_lengkap'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['username'], ENT_QUOTES) ?>', '<?= $sc['id'] ?>', '<?= htmlspecialchars($sc['kode'], ENT_QUOTES) ?>', '<?= htmlspecialchars($sc['rumusan'], ENT_QUOTES) ?>', '<?= $cellId ?>')">
                                                🎯
                                            </button>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- MODAL EVALUASI PENILAIAN RUBRIK (PRD FR-13) -->
<div id="rubrikModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 99999; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); max-width: 860px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        <!-- Modal Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background: rgba(59, 130, 246, 0.15); color: var(--primary); padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700; font-family: monospace;">
                        OBE RUBRIC EVALUATOR
                    </span>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
                        Penilaian Berbasis Rubrik
                    </h3>
                </div>
                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;" id="modalMahasiswaInfo">
                    Mahasiswa: -
                </div>
            </div>
            <button type="button" onclick="window.closeRubrikModal()" style="background: transparent; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; padding: 4px 8px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 20px 24px; overflow-y: auto; flex: 1;">
            <!-- Info Sub-CPMK -->
            <div style="background: var(--bg-hover); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 18px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Sub-CPMK yang Diukur:</span>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-main); margin-top: 2px;" id="modalSubCpmkText">
                    -
                </div>
            </div>

            <!-- Selector Rubrik -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                    Pilih Instrumen Rubrik:
                </label>
                <select id="rubrikSelector" onchange="window.renderRubrikMatrix()" style="width: 100%; padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.88rem;">
                    <?php if (!empty($rubrikData)): ?>
                        <?php foreach ($rubrikData as $idx => $r): ?>
                            <option value="<?= $idx ?>"><?= htmlspecialchars($r['nama']) ?> (<?= htmlspecialchars($r['jenis'] ?? 'Analitik') ?>)</option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">(Belum ada rubrik dibuat di master data)</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Matriks Kriteria & Level Penilaian -->
            <div id="rubrikMatrixContainer">
                <!-- Diisi via JavaScript -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 16px 24px; border-top: 1px solid var(--border); background: var(--bg-hover); display: flex; justify-content: space-between; align-items: center; border-bottom-left-radius: var(--radius-md); border-bottom-right-radius: var(--radius-md);">
            <div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Total Skor Terkalkulasi:</span>
                <span id="calculatedRubrikScore" style="font-size: 1.35rem; font-weight: 700; color: var(--primary); margin-left: 8px;">0.00</span>
                <span style="font-size: 0.85rem; color: var(--text-muted);">/ 100</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.closeRubrikModal()">
                    Batal
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.applyRubrikScore()">
                    <i class="fa-solid fa-check"></i> Gunakan Nilai Ini
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CLIENT SCRIPT UNTUK MODAL RUBRIK INTERAKTIF & FILTER -->
<script>
function handleKurikulumFilterChange(elem) {
    const form = elem.form;
    const kelasSelect = form.querySelector('#selectKelas');
    if (kelasSelect) {
        kelasSelect.removeAttribute('required');
        kelasSelect.value = '';
    }
    form.submit();
}

(function() {
    const rubrikData = <?= json_encode($rubrikData ?? []) ?>;
    let currentTargetInputId = null;

    window.openRubrikModal = function(mhsId, mhsNama, mhsNim, subCpmkId, subCpmkKode, subCpmkRumusan, targetInputId) {
        currentTargetInputId = targetInputId;

        document.getElementById('modalMahasiswaInfo').innerHTML = 
            `<strong>${mhsNim}</strong> - ${mhsNama}`;
        document.getElementById('modalSubCpmkText').innerHTML = 
            `<strong style="color: var(--primary);">${subCpmkKode}</strong>: ${subCpmkRumusan}`;

        window.renderRubrikMatrix();

        const modal = document.getElementById('rubrikModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeRubrikModal = function() {
        const modal = document.getElementById('rubrikModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.renderRubrikMatrix = function() {
        const selector = document.getElementById('rubrikSelector');
        const container = document.getElementById('rubrikMatrixContainer');
        if (!selector || !container || rubrikData.length === 0) {
            container.innerHTML = '<div style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 20px;">Belum ada master rubrik yang tersedia.</div>';
            return;
        }

        const selectedIdx = parseInt(selector.value, 10);
        const r = rubrikData[selectedIdx];
        if (!r) return;

        let html = '';

        (r.kriteria || []).forEach((k, kIdx) => {
            html += `
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem;">
                            ${kIdx + 1}. ${k.kriteria}
                        </div>
                        <span style="background: rgba(59, 130, 246, 0.1); color: var(--primary); padding: 2px 8px; border-radius: 4px; font-size: 0.78rem; font-weight: 600;">
                            Bobot: ${parseFloat(k.bobot)}%
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px;">
            `;

            (r.levels || []).forEach(lev => {
                const desc = (r.descriptors && r.descriptors[k.id] && r.descriptors[k.id][lev.id]) 
                    ? r.descriptors[k.id][lev.id] 
                    : '-';
                
                const radioName = `rubrik_crit_${k.id}`;

                html += `
                    <label style="border: 1px solid var(--border); border-radius: 6px; padding: 10px; cursor: pointer; display: flex; flex-direction: column; justify-content: space-between; background: var(--bg-hover); transition: all 0.15s ease;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <strong style="font-size: 0.82rem; color: var(--text-main);">${lev.level_nama}</strong>
                                <span style="font-size: 0.78rem; font-weight: 700; color: var(--success); font-family: monospace;">${parseFloat(lev.skor)}</span>
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.35;">
                                ${desc}
                            </div>
                        </div>
                        <div style="margin-top: 10px; text-align: right;">
                            <input type="radio" name="${radioName}" value="${lev.skor}" data-bobot="${k.bobot}" onchange="window.calculateLiveRubrikScore()" style="cursor: pointer;">
                        </div>
                    </label>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        window.calculateLiveRubrikScore();
    };

    window.calculateLiveRubrikScore = function() {
        const selector = document.getElementById('rubrikSelector');
        if (!selector || rubrikData.length === 0) return;

        const selectedIdx = parseInt(selector.value, 10);
        const r = rubrikData[selectedIdx];
        if (!r) return;

        let totalScore = 0;
        let totalBobotUsed = 0;

        (r.kriteria || []).forEach(k => {
            const checked = document.querySelector(`input[name="rubrik_crit_${k.id}"]:checked`);
            if (checked) {
                const skor = parseFloat(checked.value) || 0;
                const bobot = parseFloat(checked.getAttribute('data-bobot')) || 0;
                totalScore += (skor * (bobot / 100));
                totalBobotUsed += bobot;
            }
        });

        const scoreEl = document.getElementById('calculatedRubrikScore');
        if (scoreEl) {
            scoreEl.textContent = totalScore.toFixed(2);
        }
    };

    window.applyRubrikScore = function() {
        if (!currentTargetInputId) return;

        const scoreEl = document.getElementById('calculatedRubrikScore');
        const score = scoreEl ? parseFloat(scoreEl.textContent) : 0;

        const targetInput = document.getElementById(currentTargetInputId);
        if (targetInput) {
            targetInput.value = score.toFixed(2);
            targetInput.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
            targetInput.style.borderColor = 'var(--success)';
            setTimeout(() => {
                targetInput.style.backgroundColor = 'var(--bg-hover)';
                targetInput.style.borderColor = 'var(--border)';
            }, 1200);
        }

        window.closeRubrikModal();
    };
})();
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
