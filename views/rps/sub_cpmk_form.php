<?php
$isEdit = isset($subCpmk);
$title = ($isEdit ? "Edit" : "Tambah") . " Sub-CPMK: " . htmlspecialchars($cpmk['kode']);
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h4><?= $isEdit ? "Edit" : "Tambah" ?> Sub-CPMK</h4>
    <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?><?= !empty($_GET['ta_id']) ? '&ta_id=' . (int)$_GET['ta_id'] : '' ?>&tab=cpmk" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke RPS
    </a>
</div>

<?php if (isset($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
        <h6 style="margin: 0; font-weight: 600;">Formulir Sub-CPMK</h6>
    </div>
    <div style="padding: 20px;">
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border); color: var(--text-muted);">
            Mata Kuliah: <strong style="color: var(--text-main);"><?= htmlspecialchars($mk['kode'] . ' - ' . $mk['nama']) ?></strong><br>
            CPMK Induk: <strong style="color: var(--text-main);"><?= htmlspecialchars($cpmk['kode']) ?></strong>
        </div>
        <form method="POST" action="">
            <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 200px;">
                    <label for="kode" style="display: block; margin-bottom: 8px; font-weight: 500;">Kode Sub-CPMK <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="kode" name="kode" value="<?= $isEdit ? htmlspecialchars($subCpmk['kode']) : '' ?>" required placeholder="Contoh: L-1 atau Sub-CPMK 01" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 120px;">
                    <label for="urutan" style="display: block; margin-bottom: 8px; font-weight: 500;">Urutan</label>
                    <input type="number" id="urutan" name="urutan" value="<?= $isEdit ? htmlspecialchars($subCpmk['urutan']) : '1' ?>" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 140px;">
                    <label for="bobot" style="display: block; margin-bottom: 8px; font-weight: 500;">Bobot (%)</label>
                    <div style="display: flex;">
                        <input type="number" step="0.01" id="bobot" name="bobot" value="<?= $isEdit ? htmlspecialchars($subCpmk['bobot']) : '0' ?>" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm) 0 0 var(--radius-sm); box-sizing: border-box; border-right: none;">
                        <span style="background: var(--surface-hover); border: 1px solid var(--border); color: var(--text-muted); padding: 10px 15px; border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">%</span>
                    </div>
                </div>
            </div>

            <!-- Level Taksonomi Bloom (Mode Opsi Interaktif C1-C6, A1-A5, P1-P5) -->
            <style>
                .taksonomi-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 7px 12px;
                    border-radius: var(--radius-sm);
                    font-size: 0.83rem;
                    cursor: pointer;
                    background: var(--surface);
                    border: 1px solid var(--border);
                    color: var(--text-muted);
                    transition: all 0.15s ease-in-out;
                    user-select: none;
                }
                .taksonomi-pill:hover {
                    border-color: #38bdf8;
                    color: var(--text-main);
                    background: var(--surface-hover);
                }
                .taksonomi-pill strong {
                    font-weight: 700;
                    letter-spacing: 0.3px;
                }
                .taksonomi-pill .pill-desc {
                    font-size: 0.75rem;
                    opacity: 0.85;
                }
                /* Active Kognitif */
                .taksonomi-pill.active {
                    background: rgba(56, 189, 248, 0.18) !important;
                    border-color: #38bdf8 !important;
                    color: #38bdf8 !important;
                    font-weight: 600;
                    box-shadow: 0 0 0 1px #38bdf8;
                }
                /* Active Afektif */
                .taksonomi-pill.pill-afektif:hover {
                    border-color: #10b981;
                }
                .taksonomi-pill.pill-afektif.active {
                    background: rgba(16, 185, 129, 0.18) !important;
                    border-color: #10b981 !important;
                    color: #10b981 !important;
                    font-weight: 600;
                    box-shadow: 0 0 0 1px #10b981;
                }
                /* Active Psikomotorik */
                .taksonomi-pill.pill-psikomotorik:hover {
                    border-color: #a855f7;
                }
                .taksonomi-pill.pill-psikomotorik.active {
                    background: rgba(168, 85, 247, 0.18) !important;
                    border-color: #a855f7 !important;
                    color: #a855f7 !important;
                    font-weight: 600;
                    box-shadow: 0 0 0 1px #a855f7;
                }
            </style>

            <div class="taksonomi-box" style="margin-bottom: 22px; padding: 18px; border: 1px solid var(--border); border-radius: var(--radius-md); background: rgba(255,255,255,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.95rem; margin: 0; color: var(--text-main);">
                            <i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Level Taksonomi Bloom (C1-C6, A1-A5, P1-P5)
                        </label>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                            Klik opsi tombol di bawah untuk memilih ranah Kognitif, Afektif, atau Psikomotorik secara otomatis.
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <select id="preset_taksonomi" onchange="applyPresetTaksonomi(this.value)" style="padding: 6px 12px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); font-size: 0.82rem; cursor: pointer;">
                            <option value="">-- Pilihan Preset Cepat --</option>
                            <optgroup label="Ranah Kognitif Tunggal">
                                <option value="C1 - Mengingat">C1 - Mengingat</option>
                                <option value="C2 - Memahami">C2 - Memahami</option>
                                <option value="C3 - Menerapkan">C3 - Menerapkan</option>
                                <option value="C4 - Menganalisis">C4 - Menganalisis</option>
                                <option value="C5 - Mengevaluasi">C5 - Mengevaluasi</option>
                                <option value="C6 - Mengkreasi">C6 - Mengkreasi</option>
                            </optgroup>
                            <optgroup label="Kombinasi Umum RPS (C, A, P)">
                                <option value="C3, A3, P2">C3, A3, P2 (Penerapan, Menghargai, Manipulasi)</option>
                                <option value="C3, A3, P4">C3, A3, P4 (Penerapan, Menghargai, Artikulasi)</option>
                                <option value="C4, A3, P4">C4, A3, P4 (Analisis, Menghargai, Artikulasi)</option>
                                <option value="C5, A3, P4">C5, A3, P4 (Evaluasi, Menghargai, Artikulasi)</option>
                                <option value="C6, A4, P5">C6, A4, P5 (Kreasi, Pengorganisasian, Naturalisasi)</option>
                            </optgroup>
                        </select>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="resetTaksonomi()" title="Bersihkan Pilihan" style="padding: 6px 12px; font-size: 0.82rem;">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Ranah Kognitif (C1 - C6) -->
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: #38bdf8; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#38bdf8;"></span>
                        Ranah Kognitif (C1 - C6)
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button type="button" class="taksonomi-pill" data-code="C1" data-label="Mengingat" onclick="toggleTaksonomiPill(this)" title="C1: Mengingat (Remembering)">
                            <strong>C1</strong> <span class="pill-desc">Mengingat</span>
                        </button>
                        <button type="button" class="taksonomi-pill" data-code="C2" data-label="Memahami" onclick="toggleTaksonomiPill(this)" title="C2: Memahami (Understanding)">
                            <strong>C2</strong> <span class="pill-desc">Memahami</span>
                        </button>
                        <button type="button" class="taksonomi-pill" data-code="C3" data-label="Menerapkan" onclick="toggleTaksonomiPill(this)" title="C3: Menerapkan (Applying)">
                            <strong>C3</strong> <span class="pill-desc">Menerapkan</span>
                        </button>
                        <button type="button" class="taksonomi-pill" data-code="C4" data-label="Menganalisis" onclick="toggleTaksonomiPill(this)" title="C4: Menganalisis (Analyzing)">
                            <strong>C4</strong> <span class="pill-desc">Menganalisis</span>
                        </button>
                        <button type="button" class="taksonomi-pill" data-code="C5" data-label="Mengevaluasi" onclick="toggleTaksonomiPill(this)" title="C5: Mengevaluasi (Evaluating)">
                            <strong>C5</strong> <span class="pill-desc">Mengevaluasi</span>
                        </button>
                        <button type="button" class="taksonomi-pill" data-code="C6" data-label="Mengkreasi" onclick="toggleTaksonomiPill(this)" title="C6: Mengkreasi / Mencipta (Creating)">
                            <strong>C6</strong> <span class="pill-desc">Mengkreasi</span>
                        </button>
                    </div>
                </div>

                <!-- Ranah Afektif (A1 - A5) -->
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: #10b981; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
                        Ranah Afektif (A1 - A5)
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button type="button" class="taksonomi-pill pill-afektif" data-code="A1" data-label="Menerima" onclick="toggleTaksonomiPill(this)" title="A1: Menerima (Receiving)">
                            <strong>A1</strong> <span class="pill-desc">Menerima</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-afektif" data-code="A2" data-label="Merespon" onclick="toggleTaksonomiPill(this)" title="A2: Merespon (Responding)">
                            <strong>A2</strong> <span class="pill-desc">Merespon</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-afektif" data-code="A3" data-label="Menghargai" onclick="toggleTaksonomiPill(this)" title="A3: Menghargai / Menilai (Valuing)">
                            <strong>A3</strong> <span class="pill-desc">Menghargai</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-afektif" data-code="A4" data-label="Mengorganisasi" onclick="toggleTaksonomiPill(this)" title="A4: Mengorganisasikan (Organization)">
                            <strong>A4</strong> <span class="pill-desc">Mengorganisasi</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-afektif" data-code="A5" data-label="Karakterisasi" onclick="toggleTaksonomiPill(this)" title="A5: Karakterisasi (Characterization)">
                            <strong>A5</strong> <span class="pill-desc">Karakterisasi</span>
                        </button>
                    </div>
                </div>

                <!-- Ranah Psikomotorik (P1 - P5) -->
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: #a855f7; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#a855f7;"></span>
                        Ranah Psikomotorik (P1 - P5)
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <button type="button" class="taksonomi-pill pill-psikomotorik" data-code="P1" data-label="Menirukan" onclick="toggleTaksonomiPill(this)" title="P1: Menirukan (Imitation)">
                            <strong>P1</strong> <span class="pill-desc">Menirukan</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-psikomotorik" data-code="P2" data-label="Memanipulasi" onclick="toggleTaksonomiPill(this)" title="P2: Memanipulasi (Manipulation)">
                            <strong>P2</strong> <span class="pill-desc">Memanipulasi</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-psikomotorik" data-code="P3" data-label="Presisi" onclick="toggleTaksonomiPill(this)" title="P3: Presisi (Precision)">
                            <strong>P3</strong> <span class="pill-desc">Presisi</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-psikomotorik" data-code="P4" data-label="Artikulasi" onclick="toggleTaksonomiPill(this)" title="P4: Artikulasi (Articulation)">
                            <strong>P4</strong> <span class="pill-desc">Artikulasi</span>
                        </button>
                        <button type="button" class="taksonomi-pill pill-psikomotorik" data-code="P5" data-label="Naturalisasi" onclick="toggleTaksonomiPill(this)" title="P5: Naturalisasi (Naturalization)">
                            <strong>P5</strong> <span class="pill-desc">Naturalisasi</span>
                        </button>
                    </div>
                </div>

                <!-- Text Input & Live Preview Result -->
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--border);">
                    <div style="display: flex; gap: 10px; align-items: center; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: var(--radius-sm); padding: 8px 14px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap; font-weight: 500;">
                            <i class="fa-solid fa-code"></i> Nilai Input:
                        </span>
                        <input type="text" id="level_taksonomi" name="level_taksonomi" value="<?= $isEdit ? htmlspecialchars($subCpmk['level_taksonomi'] ?? '') : '' ?>" placeholder="Pilih opsi tombol di atas atau ketik manual (misal: C4, A3, P2)..." style="flex: 1; padding: 4px 0; background: transparent; border: none; color: var(--text-main); font-weight: 600; font-size: 0.95rem; outline: none;" oninput="syncPillsFromInput()">
                        <span id="taksonomi-count-badge" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); color: var(--primary); font-weight: 600; display: none;"></span>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="rumusan" style="display: block; margin-bottom: 8px; font-weight: 500;">Rumusan Sub-CPMK <span style="color: var(--danger);">*</span></label>
                <textarea id="rumusan" name="rumusan" rows="3" required placeholder="Tuliskan rumusan kemampuan akhir yang diharapkan..." style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box; font-family: inherit;"><?= $isEdit ? htmlspecialchars($subCpmk['rumusan']) : '' ?></textarea>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 250px;">
                    <label for="pengalaman_belajar" style="display: block; margin-bottom: 8px; font-weight: 500;">Pengalaman Pembelajaran</label>
                    <input type="text" id="pengalaman_belajar" name="pengalaman_belajar" value="<?= $isEdit ? htmlspecialchars($subCpmk['pengalaman_belajar'] ?? '') : 'Ceramah, Presentasi, Tugas, Video, Diskusi kasus' ?>" placeholder="Contoh: Ceramah, Presentasi, Tugas, Video, Diskusi kasus" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label for="asesmen_penilaian" style="display: block; margin-bottom: 8px; font-weight: 500;">Bentuk Asesmen / Penilaian</label>
                    <input type="text" id="asesmen_penilaian" name="asesmen_penilaian" value="<?= $isEdit ? htmlspecialchars($subCpmk['asesmen_penilaian'] ?? '') : 'Pretes dan postes' ?>" placeholder="Contoh: Pretes dan postes" style="width: 100%; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); border-radius: var(--radius-sm); box-sizing: border-box;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 15px; background: var(--surface);">
                <label style="display: block; margin-bottom: 15px; font-weight: 500;">Indikator Penilaian</label>
                <div id="indikator-container">
                    <?php if ($isEdit && !empty($subCpmk['indikators'])): ?>
                        <?php foreach ($subCpmk['indikators'] as $ind): ?>
                            <div style="display: flex; margin-bottom: 10px;">
                                <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
                                <input type="text" name="indikators[]" value="<?= htmlspecialchars($ind['indikator']) ?>" placeholder="Deskripsi indikator..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
                                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="display: flex; margin-bottom: 10px;">
                            <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
                            <input type="text" name="indikators[]" placeholder="Deskripsi indikator (opsional)..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
                            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-secondary" style="margin-top: 10px;" onclick="addIndikator()">
                    <i class="fa-solid fa-plus"></i> Tambah Indikator Baru
                </button>
            </div>

            <div style="border-top: 1px solid var(--border); margin-top: 30px; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="<?= $baseUrl ?>/rps?mk_id=<?= $mk['id'] ?>" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTaksonomiPill(btn) {
    btn.classList.toggle('active');
    updateInputFromPills();
}

function updateInputFromPills() {
    const activePills = Array.from(document.querySelectorAll('.taksonomi-pill.active'));
    // Canonical order: C1..C6, A1..A5, P1..P5
    const order = [
        'C1','C2','C3','C4','C5','C6',
        'A1','A2','A3','A4','A5',
        'P1','P2','P3','P4','P5'
    ];
    activePills.sort((a, b) => {
        const codeA = a.getAttribute('data-code');
        const codeB = b.getAttribute('data-code');
        return order.indexOf(codeA) - order.indexOf(codeB);
    });

    const codes = activePills.map(p => p.getAttribute('data-code'));
    const input = document.getElementById('level_taksonomi');
    input.value = codes.join(', ');

    updateCountBadge(codes.length);
}

function applyPresetTaksonomi(val) {
    if (!val) return;
    const input = document.getElementById('level_taksonomi');
    input.value = val;
    syncPillsFromInput();
}

function resetTaksonomi() {
    document.querySelectorAll('.taksonomi-pill').forEach(p => p.classList.remove('active'));
    const input = document.getElementById('level_taksonomi');
    input.value = '';
    const presetSelect = document.getElementById('preset_taksonomi');
    if (presetSelect) presetSelect.value = '';
    updateCountBadge(0);
}

function syncPillsFromInput() {
    const input = document.getElementById('level_taksonomi');
    const val = input ? input.value : '';
    let matchCount = 0;

    document.querySelectorAll('.taksonomi-pill').forEach(pill => {
        const code = pill.getAttribute('data-code');
        // Match exact token like C3 or word boundary in string
        const regex = new RegExp('(?:^|[\\s,\\-\\(\\[])' + code + '(?:[\\s,\\-\\)\\]]|$)', 'i');
        if (regex.test(val)) {
            pill.classList.add('active');
            matchCount++;
        } else {
            pill.classList.remove('active');
        }
    });

    updateCountBadge(matchCount);
}

function updateCountBadge(count) {
    const badge = document.getElementById('taksonomi-count-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count + ' Ranah Terpilih';
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    syncPillsFromInput();
});

function addIndikator() {
    const container = document.getElementById('indikator-container');
    const div = document.createElement('div');
    div.style.cssText = 'display: flex; margin-bottom: 10px;';
    div.innerHTML = `
        <span style="background: var(--surface-hover); border: 1px solid var(--border); border-right: none; color: var(--success); padding: 10px; border-radius: var(--radius-sm) 0 0 var(--radius-sm);"><i class="fa-solid fa-check-circle"></i></span>
        <input type="text" name="indikators[]" placeholder="Deskripsi indikator..." style="flex: 1; padding: 10px; background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-main); box-sizing: border-box;">
        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()" title="Hapus Indikator" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;"><i class="fa-solid fa-trash"></i></button>
    `;
    container.appendChild(div);
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
