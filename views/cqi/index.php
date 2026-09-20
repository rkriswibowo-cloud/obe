<?php
$title = "Continuous Quality Improvement (CQI)";
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Continuous Quality Improvement (CQI)</h2>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 0.9rem;">
            Pencatatan siklus evaluasi mutu, analisis akar masalah, dan monitoring tindak lanjut perbaikan CPL yang belum mencapai target.
        </p>
    </div>
    <a href="<?= $baseUrl ?>/cqi/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Catat Rekomendasi CQI
    </a>
</div>

<!-- Flash Notifications -->
<?php if (!empty($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
    </div>
<?php endif; ?>

<!-- Smart Alert: Deteksi Otomatis CPL Di Bawah Target -->
<?php if (!empty($underperformingCpls)): ?>
    <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid var(--warning); border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 24px;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div style="background: rgba(245, 158, 11, 0.2); color: var(--warning); padding: 10px; border-radius: var(--radius-sm); font-size: 1.2rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 6px 0; color: var(--warning); font-size: 1rem; font-weight: 700;">
                    Deteksi Otomatis OBE: Ditemukan <?= count($underperformingCpls) ?> CPL Belum Memenuhi Target Mutu
                </h4>
                <p style="margin: 0 0 12px 0; font-size: 0.85rem; color: var(--text-main); line-height: 1.5;">
                    Berdasarkan rekap nilai asesmen mahasiswa, CPL berikut memiliki rata-rata capaian di bawah target standar. Segera susun rencana perbaikan mutu (CQI):
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($underperformingCpls as $up): ?>
                        <div style="background: var(--surface); border: 1px solid var(--border); padding: 8px 12px; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 12px;">
                            <div>
                                <span style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($up['cpl_kode']) ?></span>:
                                <span style="color: var(--danger); font-weight: 600;"><?= $up['rata_rata_capaian'] ?>%</span>
                                <span style="color: var(--text-muted); font-size: 0.75rem;">(Target: <?= $up['target_capaian'] ?>%)</span>
                            </div>
                            <a href="<?= $baseUrl ?>/cqi/create?cpl_id=<?= $up['cpl_id'] ?>&aktual=<?= $up['rata_rata_capaian'] ?>" 
                               class="btn btn-sm btn-primary" style="padding: 3px 8px; font-size: 0.75rem;">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Tindak Lanjut
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- KPI Status Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600;">TOTAL KASUS CQI</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--text-main); margin-top: 4px;"><?= $statusCounts['TOTAL'] ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem;">Seluruh rencana perbaikan</div>
    </div>

    <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--danger); font-size: 0.8rem; font-weight: 600;">OPEN (BARU)</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--danger); margin-top: 4px;"><?= $statusCounts['OPEN'] ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem;">Menunggu implementasi</div>
    </div>

    <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--warning); font-size: 0.8rem; font-weight: 600;">IN PROGRESS</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--warning); margin-top: 4px;"><?= $statusCounts['IN_PROGRESS'] ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem;">Sedang dilaksanakan</div>
    </div>

    <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--info); font-size: 0.8rem; font-weight: 600;">COMPLETED</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--info); margin-top: 4px;"><?= $statusCounts['COMPLETED'] ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem;">Tindakan selesai</div>
    </div>

    <div style="background: var(--surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border);">
        <div style="color: var(--success); font-size: 0.8rem; font-weight: 600;">RE-MEASURED</div>
        <div style="font-size: 1.8rem; font-weight: 700; color: var(--success); margin-top: 4px;"><?= $statusCounts['RE_MEASURED'] ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem;">Telah diukur ulang</div>
    </div>
</div>

<!-- Filter Bar -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= $baseUrl ?>/cqi" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Status:</label>
            <select name="status" onchange="this.form.submit()" style="padding: 6px 12px; font-size: 0.85rem;">
                <option value="">-- Semua Status --</option>
                <option value="OPEN" <?= ($filter['status'] ?? '') === 'OPEN' ? 'selected' : '' ?>>OPEN</option>
                <option value="IN_PROGRESS" <?= ($filter['status'] ?? '') === 'IN_PROGRESS' ? 'selected' : '' ?>>IN PROGRESS</option>
                <option value="COMPLETED" <?= ($filter['status'] ?? '') === 'COMPLETED' ? 'selected' : '' ?>>COMPLETED</option>
                <option value="RE_MEASURED" <?= ($filter['status'] ?? '') === 'RE_MEASURED' ? 'selected' : '' ?>>RE-MEASURED</option>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">CPL:</label>
            <select name="cpl_id" onchange="this.form.submit()" style="padding: 6px 12px; font-size: 0.85rem;">
                <option value="">-- Semua CPL --</option>
                <?php foreach ($cplList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filter['cpl_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['kode']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Tahun Akademik:</label>
            <select name="tahun_akademik_id" onchange="this.form.submit()" style="padding: 6px 12px; font-size: 0.85rem;">
                <option value="">-- Semua TA --</option>
                <?php foreach ($taList as $ta): ?>
                    <option value="<?= $ta['id'] ?>" <?= ($filter['tahun_akademik_id'] ?? '') == $ta['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ta['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($filter['status']) || !empty($filter['cpl_id']) || !empty($filter['tahun_akademik_id'])): ?>
            <a href="<?= $baseUrl ?>/cqi" class="btn btn-sm btn-secondary" title="Reset Filter">
                <i class="fa-solid fa-xmark"></i> Reset
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.1);">
        <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700;">Daftar Rekam Jejak Tindak Lanjut Mutu CPL</h3>
        <span style="font-size: 0.85rem; color: var(--text-muted);"><?= count($cqiList) ?> Kasus</span>
    </div>

    <?php if (empty($cqiList)): ?>
        <div style="text-align: center; padding: 48px 20px;">
            <i class="fa-solid fa-clipboard-check" style="font-size: 3rem; color: var(--border); margin-bottom: 16px;"></i>
            <h4 style="margin: 0 0 8px 0; color: var(--text-main);">Belum ada catatan CQI</h4>
            <p style="color: var(--text-muted); margin: 0 0 20px 0; max-width: 450px; margin-inline: auto; font-size: 0.9rem;">
                Gunakan modul CQI untuk mencatat evaluasi dan rencana perbaikan terhadap CPL yang memerlukan peningkatan capaian.
            </p>
            <a href="<?= $baseUrl ?>/cqi/create" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Buat Catatan CQI Baru
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="background: var(--surface-hover); border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px 16px; width: 12%;">CPL & Periode</th>
                        <th style="padding: 12px 16px; width: 10%; text-align: center;">Target vs Aktual</th>
                        <th style="padding: 12px 16px; width: 22%;">Akar Masalah</th>
                        <th style="padding: 12px 16px; width: 24%;">Rencana Tindak Lanjut</th>
                        <th style="padding: 12px 16px; width: 12%;">PIC & Deadline</th>
                        <th style="padding: 12px 16px; width: 10%; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; width: 10%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cqiList as $item): 
                        $statusBadge = [
                            'OPEN' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'color' => 'var(--danger)', 'border' => 'rgba(239, 68, 68, 0.3)'],
                            'IN_PROGRESS' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'color' => 'var(--warning)', 'border' => 'rgba(245, 158, 11, 0.3)'],
                            'COMPLETED' => ['bg' => 'rgba(59, 130, 246, 0.15)', 'color' => 'var(--info)', 'border' => 'rgba(59, 130, 246, 0.3)'],
                            'RE_MEASURED' => ['bg' => 'rgba(16, 185, 129, 0.15)', 'color' => 'var(--success)', 'border' => 'rgba(16, 185, 129, 0.3)'],
                        ];
                        $st = $statusBadge[$item['status']] ?? $statusBadge['OPEN'];
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 16px; vertical-align: top;">
                                <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem; margin-bottom: 3px;">
                                    <?= htmlspecialchars($item['cpl_kode']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?= htmlspecialchars($item['tahun_akademik_nama'] ?? 'TA -') ?> (<?= htmlspecialchars($item['semester']) ?>)
                                </div>
                            </td>

                            <td style="padding: 12px 16px; vertical-align: top; text-align: center;">
                                <div style="font-weight: 700; color: var(--danger); font-size: 0.95rem;">
                                    <?= $item['capaian_aktual'] ?>%
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    Target: <?= $item['target_capaian'] ?>%
                                </div>
                                <?php if ($item['hasil_re_measurement'] !== null): ?>
                                    <div style="font-size: 0.75rem; color: var(--success); font-weight: 700; margin-top: 4px;">
                                        Ukur Ulang: <?= $item['hasil_re_measurement'] ?>%
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px 16px; vertical-align: top; line-height: 1.4;">
                                <div style="font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                                    <?= nl2br(htmlspecialchars($item['akar_masalah'])) ?>
                                </div>
                                <?php if (!empty($item['analisis_masalah'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3;">
                                        <em>Analisis:</em> <?= htmlspecialchars(mb_substr($item['analisis_masalah'], 0, 75)) ?>...
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px 16px; vertical-align: top; line-height: 1.4;">
                                <div style="color: var(--text-main); font-weight: 500;">
                                    <?= nl2br(htmlspecialchars($item['rencana_tindak_lanjut'])) ?>
                                </div>
                                <?php if (!empty($item['monitoring_evaluasi'])): ?>
                                    <div style="margin-top: 4px; font-size: 0.75rem; background: rgba(0,0,0,0.2); padding: 4px 8px; border-radius: 4px; color: var(--info);">
                                        <strong>Monitoring:</strong> <?= htmlspecialchars($item['monitoring_evaluasi']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: var(--text-main);">
                                    <?= htmlspecialchars($item['pic']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
                                    <?= date('d M Y', strtotime($item['deadline'])) ?>
                                </div>
                            </td>

                            <td style="padding: 12px 16px; text-align: center; vertical-align: top;">
                                <span style="background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>; border: 1px solid <?= $st['border'] ?>; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; display: inline-block;">
                                    <?= htmlspecialchars($item['status']) ?>
                                </span>
                            </td>

                            <td style="padding: 12px 16px; text-align: center; vertical-align: top;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-sm btn-secondary" style="padding: 4px 8px;" title="Update Status & Monitoring"
                                            onclick='openModalStatus(<?= json_encode($item) ?>)'>
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                    <a href="<?= $baseUrl ?>/cqi/edit?id=<?= $item['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px;" title="Edit Lengkap">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="<?= $baseUrl ?>/cqi/delete?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" style="padding: 4px 8px;" title="Hapus" onclick="return confirm('Hapus rekaman CQI ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Quick Update Status & Monitoring -->
<div id="modalStatusCqi" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Update Progres & Monitoring CQI</h3>
            <button onclick="document.getElementById('modalStatusCqi').style.display='none'" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/cqi/status" style="padding: 20px;">
            <input type="hidden" name="id" id="modal_cqi_id" value="">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Status Progres <span style="color: var(--danger);">*</span></label>
                <select name="status" id="modal_cqi_status" style="width: 100%;" required>
                    <option value="OPEN">OPEN (Baru Dirumuskan)</option>
                    <option value="IN_PROGRESS">IN_PROGRESS (Sedang Dijalankan)</option>
                    <option value="COMPLETED">COMPLETED (Tindakan Perbaikan Selesai)</option>
                    <option value="RE_MEASURED">RE_MEASURED (Telah Diukur Ulang)</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Catatan Monitoring / Evaluasi Berkala</label>
                <textarea name="monitoring_evaluasi" id="modal_cqi_monitoring" rows="3" placeholder="Hasil pemantauan implementasi tindakan perbaikan..." style="width: 100%;"></textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Skor Hasil Pengukuran Ulang (%) [Opsional]</label>
                <input type="number" step="0.1" min="0" max="100" name="hasil_re_measurement" id="modal_cqi_remeasure" placeholder="Contoh: 76.5 (setelah tindakan perbaikan)" style="width: 100%;">
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Isi ketika status telah mencapai RE_MEASURED.</div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 14px;">
                <button type="button" onclick="document.getElementById('modalStatusCqi').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalStatus(item) {
    document.getElementById('modal_cqi_id').value = item.id;
    document.getElementById('modal_cqi_status').value = item.status;
    document.getElementById('modal_cqi_monitoring').value = item.monitoring_evaluasi || '';
    document.getElementById('modal_cqi_remeasure').value = item.hasil_re_measurement || '';
    document.getElementById('modalStatusCqi').style.display = 'flex';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>

