<?php
$isEdit = !empty($cqi);
$title = $isEdit ? "Edit Rekomendasi CQI" : "Catat Rekomendasi CQI Baru";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<div style="max-width: 850px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <a href="<?= $baseUrl ?>/cqi" class="btn btn-sm btn-secondary" style="padding: 6px 10px;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700;"><?= $title ?></h2>
            <p style="color: var(--text-muted); margin: 2px 0 0 0; font-size: 0.85rem;">
                Rumuskan rencana aksi perbaikan mutu berkesinambungan (Plan-Do-Check-Act) untuk pemenuhan target CPL.
            </p>
        </div>
    </div>

    <!-- Form Card -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px;">
        <form method="POST" action="<?= $baseUrl ?>/cqi/<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $cqi['id'] ?>">
            <?php endif; ?>

            <!-- Row 1: CPL & Periode -->
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        CPL Sasaran Perbaikan <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="cpl_id" id="selectCpl" required style="width: 100%;" onchange="updateCplDefaults()">
                        <option value="">-- Pilih CPL --</option>
                        <?php foreach ($cplList as $c): 
                            $isSelected = $isEdit ? ($cqi['cpl_id'] == $c['id']) : ($selectedCplId == $c['id']);
                        ?>
                            <option value="<?= $c['id'] ?>" 
                                    data-target="<?= $c['target_capaian'] ?>" 
                                    <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['kode']) ?> - <?= htmlspecialchars(mb_substr($c['rumusan'], 0, 50)) ?>... (Target: <?= $c['target_capaian'] ?>%)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Tahun Akademik
                    </label>
                    <select name="tahun_akademik_id" style="width: 100%;">
                        <option value="">-- Pilih TA --</option>
                        <?php foreach ($taList as $ta): 
                            $isSelected = $isEdit ? ($cqi['tahun_akademik_id'] == $ta['id']) : (!empty($ta['is_active']));
                        ?>
                            <option value="<?= $ta['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ta['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Semester
                    </label>
                    <select name="semester" style="width: 100%;">
                        <?php 
                        $currSem = $isEdit ? $cqi['semester'] : 'Ganjil';
                        ?>
                        <option value="Ganjil" <?= $currSem === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                        <option value="Genap" <?= $currSem === 'Genap' ? 'selected' : '' ?>>Genap</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Target vs Aktual -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px; background: rgba(0,0,0,0.15); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Target Capaian Standar (%) <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="number" step="0.1" min="0" max="100" name="target_capaian" id="inputTarget" 
                           value="<?= $isEdit ? $cqi['target_capaian'] : $defaultTarget ?>" required style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Capaian Aktual Saat Ini (%) <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="number" step="0.1" min="0" max="100" name="capaian_aktual" id="inputAktual" 
                           value="<?= $isEdit ? $cqi['capaian_aktual'] : $defaultAktual ?>" required style="width: 100%;">
                </div>
            </div>

            <!-- Row 3: Analisis & Akar Masalah -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                    1. Analisis Masalah (Identifikasi Kesenjangan Capaian) <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="analisis_masalah" rows="3" required placeholder="Contoh: Rata-rata mahasiswa pada CPL03 hanya mencapai 62.4%, terutama rendah pada asesmen Sub-CPMK perancangan basis data..." style="width: 100%;"><?= $isEdit ? htmlspecialchars($cqi['analisis_masalah']) : '' ?></textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                    2. Akar Masalah (Root Cause Analysis) <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="akar_masalah" rows="3" required placeholder="Contoh: Mahasiswa kurang latihan studi kasus riil, bobot asesmen praktikum belum proporsional, atau prasyarat algoritma belum kuat..." style="width: 100%;"><?= $isEdit ? htmlspecialchars($cqi['akar_masalah']) : '' ?></textarea>
            </div>

            <!-- Row 4: Rencana Tindak Lanjut -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                    3. Rencana Tindak Lanjut Perbaikan (Action Plan) <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="rencana_tindak_lanjut" rows="4" required placeholder="Contoh: 1. Penambahan 4 sesi studi kasus flipped classroom. 2. Revisi rubrik asesmen proyek. 3. Sesi tutorial asistensi mahasiswa..." style="width: 100%;"><?= $isEdit ? htmlspecialchars($cqi['rencana_tindak_lanjut']) : '' ?></textarea>
            </div>

            <!-- Row 5: PIC, Deadline, Status -->
            <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Penanggung Jawab (PIC) <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="pic" required placeholder="Contoh: Tim KBK Rekayasa SI / Dosen Pengampu" 
                           value="<?= $isEdit ? htmlspecialchars($cqi['pic']) : '' ?>" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Target Waktu (Deadline) <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="date" name="deadline" required 
                           value="<?= $isEdit ? $cqi['deadline'] : date('Y-m-d', strtotime('+3 months')) ?>" style="width: 100%;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Status Pelaksanaan <span style="color: var(--danger);">*</span>
                    </label>
                    <select name="status" style="width: 100%;">
                        <?php 
                        $curSt = $isEdit ? $cqi['status'] : 'OPEN';
                        ?>
                        <option value="OPEN" <?= $curSt === 'OPEN' ? 'selected' : '' ?>>OPEN (Baru)</option>
                        <option value="IN_PROGRESS" <?= $curSt === 'IN_PROGRESS' ? 'selected' : '' ?>>IN PROGRESS (Berjalan)</option>
                        <option value="COMPLETED" <?= $curSt === 'COMPLETED' ? 'selected' : '' ?>>COMPLETED (Selesai)</option>
                        <option value="RE_MEASURED" <?= $curSt === 'RE_MEASURED' ? 'selected' : '' ?>>RE-MEASURED (Diukur Ulang)</option>
                    </select>
                </div>
            </div>

            <!-- Row 6: Monitoring & Re-measurement (Optional/Progression) -->
            <div style="background: rgba(0,0,0,0.1); border: 1px dashed var(--border); padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                <div style="font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 12px;">
                    <i class="fa-solid fa-clipboard-check" style="color: var(--primary); margin-right: 6px;"></i>
                    Monitoring & Pengukuran Ulang (Re-measurement)
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 4px;">Catatan Monitoring Berkala</label>
                        <textarea name="monitoring_evaluasi" rows="2" placeholder="Catatan hasil pemantauan perkembangan implementasi..." style="width: 100%; font-size: 0.85rem;"><?= $isEdit ? htmlspecialchars($cqi['monitoring_evaluasi'] ?? '') : '' ?></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 4px;">Skor Re-measurement (%)</label>
                        <input type="number" step="0.1" min="0" max="100" name="hasil_re_measurement" 
                               value="<?= $isEdit ? $cqi['hasil_re_measurement'] : '' ?>" 
                               placeholder="Contoh: 75.5" style="width: 100%; font-size: 0.85rem;">
                        <span style="color: var(--text-muted); font-size: 0.7rem; margin-top: 4px; display: block;">Diisi setelah asesmen siklus berikutnya diukur.</span>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 18px;">
                <a href="<?= $baseUrl ?>/cqi" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> <?= $isEdit ? 'Perbarui Rekam CQI' : 'Simpan Rencana CQI' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCplDefaults() {
    const select = document.getElementById('selectCpl');
    const selectedOption = select.options[select.selectedIndex];
    const target = selectedOption.getAttribute('data-target');
    if (target && !document.getElementById('inputTarget').value) {
        document.getElementById('inputTarget').value = target;
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>

