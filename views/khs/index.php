<?php 
$title = "KHS & Ketercapaian CPL Mahasiswa";
$userRole = $_SESSION['role'] ?? '';
ob_start(); 
?>

<div class="content-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary);"></i>
                KHS & Portofolio CPL Mahasiswa
            </h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.88rem;">
                Pemantauan terintegrasi Kartu Hasil Studi (KHS), Indeks Prestasi (IPS/IPK), dan Ketercapaian Capaian Pembelajaran Lulusan (CPL).
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <?php if ($mahasiswa): ?>
                <a href="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/khs/cetak?id=<?= $mahasiswa['id'] ?><?= !empty($filterTaId) ? '&ta_id=' . $filterTaId : '' ?>" target="_blank" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border); display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                    <i class="fa-solid fa-print"></i> Cetak KHS & Portofolio
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filter & Pemilihan Mahasiswa (Tampil jika Admin / Dosen / Kaprodi) -->
<?php if ($userRole !== 'Mahasiswa'): ?>
    <div class="card" style="padding: 16px 20px; margin-bottom: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
        <form method="GET" action="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/khs" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <label for="selectMhs" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">
                    <i class="fa-solid fa-user-graduate" style="margin-right: 4px;"></i> Pilih Mahasiswa
                </label>
                <select name="id" id="selectMhs" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.9rem;">
                    <?php foreach ($daftarMahasiswa as $dm): ?>
                        <option value="<?= $dm['id'] ?>" <?= ($selectedMhsId == $dm['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dm['username']) ?> - <?= htmlspecialchars($dm['nama_lengkap']) ?> (Angkatan <?= htmlspecialchars($dm['angkatan']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width: 220px;">
                <label for="selectTa" style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">
                    <i class="fa-solid fa-calendar-days" style="margin-right: 4px;"></i> Filter Semester
                </label>
                <select name="ta_id" id="selectTa" class="form-control" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-main); font-size: 0.9rem;">
                    <option value="">Semua Semester (Kumulatif)</option>
                    <?php foreach ($daftarTahunAkademik as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($filterTaId == $ta['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ta['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; font-weight: 500;">
                <i class="fa-solid fa-filter"></i> Terapkan
            </button>
        </form>
    </div>
<?php endif; ?>

<?php if ($mahasiswa && $khsData): ?>
    <!-- Profil Mahasiswa Banner -->
    <div class="card" style="padding: 20px 24px; margin-bottom: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 18px;">
            <div style="width: 58px; height: 58px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <?= strtoupper(substr($mahasiswa['nama_lengkap'], 0, 1)) ?>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">
                    <?= htmlspecialchars($mahasiswa['nama_lengkap']) ?>
                </h3>
                <div style="display: flex; align-items: center; gap: 12px; margin-top: 5px; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted);">
                    <span><i class="fa-solid fa-id-card"></i> NIM: <strong><?= htmlspecialchars($mahasiswa['username']) ?></strong></span>
                    <span>•</span>
                    <span><i class="fa-solid fa-graduation-cap"></i> Program Studi Sistem Informasi</span>
                    <span>•</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 2px 8px; border-radius: 6px; font-weight: 600;">Status: Aktif</span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="text-align: right;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.05em;">Predikat Akademik</div>
                <div style="font-size: 1.05rem; font-weight: 700; color: var(--primary);">
                    <?= htmlspecialchars($khsData['ringkasan']['predikat']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan KPI Cards (IPK & Capaian CPL Terintegrasi) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- Card 1: IPK -->
        <div class="card" style="padding: 18px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.04em;">IPK Kumulatif</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: var(--text-main); margin-top: 4px;">
                        <?= number_format($khsData['ringkasan']['ipk'], 2) ?>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-muted);">/ 4.00</span>
                    </div>
                </div>
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-award"></i>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.78rem; color: var(--text-muted);">
                Total Mutu Kumulatif: <strong><?= $khsData['ringkasan']['total_mutu'] ?></strong>
            </div>
        </div>

        <!-- Card 2: SKS Lulus -->
        <div class="card" style="padding: 18px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.04em;">Total SKS Lulus</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: var(--text-main); margin-top: 4px;">
                        <?= $khsData['ringkasan']['total_sks_lulus'] ?>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-muted);">/ <?= $khsData['ringkasan']['total_sks_ditempuh'] ?> SKS</span>
                    </div>
                </div>
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.78rem; color: var(--text-muted);">
                Persentase Lulus: <strong><?= $khsData['ringkasan']['total_sks_ditempuh'] > 0 ? round(($khsData['ringkasan']['total_sks_lulus'] / $khsData['ringkasan']['total_sks_ditempuh']) * 100, 1) : 0 ?>%</strong>
            </div>
        </div>

        <!-- Card 3: Rata-rata Skor CPL -->
        <div class="card" style="padding: 18px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.04em;">Rata-rata Skor CPL</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: var(--text-main); margin-top: 4px;">
                        <?= $cplData['ringkasan']['rata_rata_skor'] ?>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-muted);">/ 100</span>
                    </div>
                </div>
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.78rem; color: var(--text-muted);">
                Tingkat Capaian: <strong><?= $cplData['ringkasan']['persentase_tercapai'] ?>% CPL Memenuhi</strong>
            </div>
        </div>

        <!-- Card 4: Status CPL -->
        <div class="card" style="padding: 18px 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.04em;">CPL Tercapai</div>
                    <div style="font-size: 1.9rem; font-weight: 800; color: var(--text-main); margin-top: 4px;">
                        <?= $cplData['ringkasan']['cpl_tercapai'] ?>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-muted);">/ <?= $cplData['ringkasan']['total_cpl'] ?> CPL</span>
                    </div>
                </div>
                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>
            <div style="margin-top: 10px; font-size: 0.78rem; color: var(--text-muted);">
                Belum Tercapai: <strong><?= $cplData['ringkasan']['cpl_belum_tercapai'] ?></strong> | Belum Diukur: <strong><?= $cplData['ringkasan']['cpl_belum_diukur'] ?></strong>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
        <button type="button" class="tab-btn active" onclick="switchKhsTab('tab-khs', this)" style="padding: 10px 18px; border: none; background: transparent; color: var(--text-main); font-weight: 600; font-size: 0.92rem; cursor: pointer; border-bottom: 2px solid var(--primary); transition: all 0.2s;">
            <i class="fa-solid fa-list-check" style="margin-right: 6px;"></i> Kartu Hasil Studi (KHS)
        </button>
        <button type="button" class="tab-btn" onclick="switchKhsTab('tab-cpl', this)" style="padding: 10px 18px; border: none; background: transparent; color: var(--text-muted); font-weight: 500; font-size: 0.92rem; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;">
            <i class="fa-solid fa-spider" style="margin-right: 6px;"></i> Radar & Rekap CPL Mahasiswa
        </button>
        <button type="button" class="tab-btn" onclick="switchKhsTab('tab-trace', this)" style="padding: 10px 18px; border: none; background: transparent; color: var(--text-muted); font-weight: 500; font-size: 0.92rem; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;">
            <i class="fa-solid fa-network-wired" style="margin-right: 6px;"></i> Matriks Traceability (MK ke CPL)
        </button>
    </div>

    <!-- TAB 1: KARTU HASIL STUDI (KHS) -->
    <div id="tab-khs" class="tab-pane active">
        <!-- Navigasi Semester 1 - 8 & Ringkasan IPS -->
        <?php if (!empty($khsData['per_semester'])): ?>
            <div style="margin-bottom: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.04em;">
                        <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 6px;"></i> Progres Studi Per Semester (Semester 1 s/d 8)
                    </span>
                    <a href="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/khs?id=<?= $selectedMhsId ?>" style="font-size: 0.82rem; color: var(--primary); text-decoration: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; background: rgba(var(--primary-rgb), 0.08);">
                        <i class="fa-solid fa-arrows-rotate" style="margin-right: 4px;"></i> Tampilkan Semua (Kumulatif 1-8)
                    </a>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;">
                    <?php 
                    $semIdx = 1;
                    foreach ($daftarTahunAkademik as $ta): 
                        $taData = $khsData['per_semester'][$ta['id']] ?? null;
                        $isSelected = ($filterTaId == $ta['id']);
                    ?>
                        <a href="<?= ($GLOBALS['config']['base_url'] ?? '/obe/public') ?>/khs?id=<?= $selectedMhsId ?>&ta_id=<?= $ta['id'] ?>" 
                           style="display: block; text-decoration: none; padding: 10px 12px; border-radius: 10px; border: 1.5px solid <?= $isSelected ? 'var(--primary)' : 'var(--border)' ?>; background: <?= $isSelected ? 'rgba(var(--primary-rgb), 0.12)' : 'var(--surface)' ?>; transition: all 0.2s; box-shadow: <?= $isSelected ? '0 0 0 2px rgba(var(--primary-rgb), 0.2)' : 'none' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.82rem; font-weight: 700; color: <?= $isSelected ? 'var(--primary)' : 'var(--text-main)' ?>;">
                                    Semester <?= $semIdx++ ?>
                                </span>
                                <?php if ($taData): ?>
                                    <span style="font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 700;">
                                        IPS <?= number_format($taData['ips'], 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.7rem; color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                                <span><?= $taData ? $taData['total_sks'] . ' SKS' : '0 SKS' ?></span>
                                <span style="font-size: 0.7rem;"><?= $taData ? count($taData['mata_kuliah']) . ' MK' : '' ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    Daftar Mata Kuliah & Nilai Hasil Belajar <?= !empty($filterTaId) ? '(' . htmlspecialchars($daftarTahunAkademik[array_search($filterTaId, array_column($daftarTahunAkademik, 'id'))]['nama'] ?? '') . ')' : '(Kumulatif Semester 1 - 8)' ?>
                </h4>
                <span style="font-size: 0.82rem; color: var(--text-muted);">
                    Menampilkan <?= count($khsData['semua_mk']) ?> Mata Kuliah (Total <?= array_sum(array_column($khsData['semua_mk'], 'sks')) ?> SKS)
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left; background: var(--table-header-bg);">
                            <th style="padding: 12px 14px;">No</th>
                            <th style="padding: 12px 14px;">Semester</th>
                            <th style="padding: 12px 14px;">Kode MK</th>
                            <th style="padding: 12px 14px;">Nama Mata Kuliah</th>
                            <th style="padding: 12px 14px; text-align: center;">Kelas</th>
                            <th style="padding: 12px 14px; text-align: center;">SKS</th>
                            <th style="padding: 12px 14px; text-align: center;">Nilai Angka</th>
                            <th style="padding: 12px 14px; text-align: center;">Nilai Huruf</th>
                            <th style="padding: 12px 14px; text-align: center;">Bobot Mutu</th>
                            <th style="padding: 12px 14px; text-align: center;">SKS x Bobot</th>
                            <th style="padding: 12px 14px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($khsData['semua_mk'])): ?>
                            <?php $no = 1; foreach ($khsData['semua_mk'] as $mk): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 12px 14px;"><?= $no++ ?></td>
                                    <td style="padding: 12px 14px; font-weight: 600; font-size: 0.82rem; white-space: nowrap;">
                                        <span class="badge" style="background: var(--surface-hover); color: var(--text-main); border: 1px solid var(--border); padding: 3px 8px; border-radius: 6px;">
                                            Sem <?= $mk['mk_semester'] ?? '-' ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 600; font-family: monospace;">
                                        <?= htmlspecialchars($mk['mk_kode']) ?>
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 600; color: var(--text-main);">
                                        <?= htmlspecialchars($mk['mk_nama']) ?>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <span class="badge" style="background: var(--surface-hover); color: var(--text-main); padding: 2px 8px; border-radius: 4px;">
                                            <?= htmlspecialchars($mk['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center; font-weight: 600;">
                                        <?= $mk['sks'] ?>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center; font-weight: 600;">
                                        <?= $mk['nilai_angka'] !== null ? number_format($mk['nilai_angka'], 2) : '-' ?>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <span style="display: inline-block; min-width: 28px; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; <?= $mk['nilai_huruf'] === 'A' || $mk['nilai_huruf'] === 'A-' ? 'background: rgba(16, 185, 129, 0.15); color: #10b981;' : ($mk['nilai_huruf'] === 'B+' || $mk['nilai_huruf'] === 'B' ? 'background: rgba(59, 130, 246, 0.15); color: #3b82f6;' : ($mk['nilai_huruf'] === 'C+' || $mk['nilai_huruf'] === 'C' ? 'background: rgba(245, 158, 11, 0.15); color: #f59e0b;' : 'background: rgba(239, 68, 68, 0.15); color: #ef4444;')) ?>">
                                            <?= $mk['nilai_huruf'] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <?= number_format($mk['bobot'], 2) ?>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center; font-weight: 600;">
                                        <?= number_format($mk['sks_x_bobot'], 2) ?>
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <?php if ($mk['is_lulus']): ?>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fa-solid fa-check"></i> Lulus
                                            </span>
                                        <?php elseif ($mk['nilai_angka'] !== null): ?>
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Mengulang
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: var(--surface-hover); color: var(--text-muted); padding: 3px 10px; border-radius: 12px; font-size: 0.75rem;">
                                                Belum Ada Nilai
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                    Belum ada mata kuliah yang diambil atau terdaftar di kelas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Rekapitulasi Indeks Prestasi Semester & Kumulatif -->
            <div style="margin-top: 24px; padding: 16px 20px; background: var(--table-header-bg); border: 1px solid var(--border); border-radius: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Total SKS Ditempuh:</span>
                        <strong style="font-size: 1.1rem; color: var(--text-main);"><?= $khsData['ringkasan']['total_sks_ditempuh'] ?> SKS</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Total SKS Lulus:</span>
                        <strong style="font-size: 1.1rem; color: #10b981;"><?= $khsData['ringkasan']['total_sks_lulus'] ?> SKS</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block;">Total Bobot Mutu:</span>
                        <strong style="font-size: 1.1rem; color: var(--text-main);"><?= $khsData['ringkasan']['total_mutu'] ?></strong>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="text-align: right;">
                        <span style="font-size: 0.78rem; color: var(--text-muted); display: block; font-weight: 600;">INDEKS PRESTASI KUMULATIF (IPK)</span>
                        <strong style="font-size: 1.6rem; color: var(--primary); font-weight: 800;"><?= number_format($khsData['ringkasan']['ipk'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: RADAR & REKAPITULASI CPL MAHASISWA -->
    <div id="tab-cpl" class="tab-pane" style="display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <!-- Radar Spider Chart Canvas -->
            <div class="card" style="padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                        <i class="fa-solid fa-chart-radar" style="color: var(--primary);"></i> Spider Radar Chart Ketercapaian CPL
                    </h4>
                </div>
                <div style="position: relative; height: 360px;">
                    <canvas id="cplRadarChart"></canvas>
                </div>
                <div style="display: flex; justify-content: center; gap: 20px; margin-top: 14px; font-size: 0.8rem;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(51, 104, 160, 0.6); display: inline-block;"></span>
                        Capaian Aktual Mahasiswa
                    </span>
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #ef4444; border: 1px dashed #ef4444; display: inline-block;"></span>
                        Target Capaian Prodi (70%)
                    </span>
                </div>
            </div>

            <!-- Tabel Rekapitulasi Skor CPL -->
            <div class="card" style="padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
                <h4 style="margin: 0 0 16px; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    Rincian Ketercapaian Capaian Pembelajaran Lulusan
                </h4>
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left; background: var(--table-header-bg);">
                                <th style="padding: 10px 12px;">Kode</th>
                                <th style="padding: 10px 12px;">Deskripsi CPL</th>
                                <th style="padding: 10px 12px; text-align: center;">Target</th>
                                <th style="padding: 10px 12px; text-align: center;">Skor</th>
                                <th style="padding: 10px 12px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cplData['cpl_list'] as $c): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 10px 12px; font-weight: 700; color: var(--primary); white-space: nowrap;">
                                        <?= htmlspecialchars($c['kode']) ?>
                                    </td>
                                    <td style="padding: 10px 12px; color: var(--text-main); font-size: 0.82rem;">
                                        <?= htmlspecialchars($c['rumusan']) ?>
                                    </td>
                                    <td style="padding: 10px 12px; text-align: center; font-weight: 600; color: var(--text-muted);">
                                        <?= $c['target'] ?>%
                                    </td>
                                    <td style="padding: 10px 12px; text-align: center; font-weight: 700;">
                                        <?= $c['is_measured'] ? number_format($c['score'], 2) . '%' : '-' ?>
                                    </td>
                                    <td style="padding: 10px 12px; text-align: center;">
                                        <?php if ($c['status'] === 'Tercapai'): ?>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">
                                                Tercapai
                                            </span>
                                        <?php elseif ($c['status'] === 'Belum Tercapai'): ?>
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">
                                                Belum Tercapai
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: var(--surface-hover); color: var(--text-muted); padding: 3px 8px; border-radius: 6px; font-size: 0.75rem;">
                                                Belum Diukur
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: TRACEABILITY MATRIKS (MK KE CPL) -->
    <div id="tab-trace" class="tab-pane" style="display: none;">
        <div class="card" style="padding: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
            <div style="margin-bottom: 16px;">
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                    Matriks Traceability & Pemetaan Kontribusi Mata Kuliah ke CPL
                </h4>
                <p style="margin: 4px 0 0; font-size: 0.84rem; color: var(--text-muted);">
                    Rincian sumber asesmen nilai dari mata kuliah, CPMK, hingga Sub-CPMK yang berkontribusi terhadap pencapaian tiap CPL.
                </p>
            </div>

            <?php if (!empty($cplData['traceability'])): ?>
                <?php foreach ($cplData['traceability'] as $cplId => $mkList): ?>
                    <?php 
                        // Ambil kode CPL dari cpl_list
                        $cplKode = 'CPL-' . $cplId;
                        foreach ($cplData['cpl_list'] as $cp) {
                            if ($cp['id'] == $cplId) {
                                $cplKode = $cp['kode'] . ' - ' . $cp['rumusan'];
                                break;
                            }
                        }
                    ?>
                    <div style="margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; overflow: hidden;">
                        <div style="background: var(--table-header-bg); padding: 10px 16px; font-weight: 700; font-size: 0.9rem; color: var(--primary); border-bottom: 1px solid var(--border);">
                            <i class="fa-solid fa-bullseye" style="margin-right: 6px;"></i> <?= htmlspecialchars($cplKode) ?>
                        </div>
                        <div style="padding: 12px 16px;">
                            <?php foreach ($mkList as $mkId => $mkInfo): ?>
                                <div style="margin-bottom: 12px;">
                                    <div style="font-weight: 600; font-size: 0.86rem; color: var(--text-main); margin-bottom: 6px;">
                                        <?= htmlspecialchars($mkInfo['mk_kode']) ?> - <?= htmlspecialchars($mkInfo['mk_nama']) ?>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php foreach ($mkInfo['cpmks'] as $cpmkId => $cpmkInfo): ?>
                                            <?php foreach ($cpmkInfo['sub_cpmks'] as $sub): ?>
                                                <div style="background: var(--surface-hover); border: 1px solid var(--border); border-radius: 6px; padding: 6px 10px; font-size: 0.78rem;">
                                                    <span style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($sub['sub_kode']) ?></span>
                                                    <span style="color: var(--text-muted); margin: 0 4px;">•</span>
                                                    <span>Bobot: <?= $sub['sub_bobot'] ?>%</span>
                                                    <span style="color: var(--text-muted); margin: 0 4px;">•</span>
                                                    <span>Nilai: <strong><?= $sub['nilai'] !== null ? number_format($sub['nilai'], 2) : 'Belum Dinilai' ?></strong></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                    Belum ada data pemetaan ketercapaian mata kuliah ke CPL untuk mahasiswa ini.
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <div class="card" style="padding: 40px; text-align: center; background: var(--surface); border: 1px solid var(--border); border-radius: 14px;">
        <i class="fa-solid fa-user-xmark" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px;"></i>
        <h3 style="margin: 0; color: var(--text-main);">Data Mahasiswa Belum Dipilih</h3>
        <p style="color: var(--text-muted); margin-top: 6px;">Silakan pilih mahasiswa dari menu filter di atas untuk melihat KHS dan ketercapaian CPL.</p>
    </div>
<?php endif; ?>

<!-- Chart.js & Tab Interactivity Script -->
<script>
function switchKhsTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.style.borderBottomColor = 'transparent';
        el.style.color = 'var(--text-muted)';
        el.style.fontWeight = '500';
    });

    const target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
    }

    if (btn) {
        btn.style.borderBottomColor = 'var(--primary)';
        btn.style.color = 'var(--text-main)';
        btn.style.fontWeight = '600';
    }

    // Trigger chart render jika buka tab CPL
    if (tabId === 'tab-cpl' && window.renderRadarChart) {
        window.renderRadarChart();
    }
}

// Inisialisasi Spider/Radar Chart CPL
document.addEventListener('DOMContentLoaded', function() {
    const radarPayload = <?= json_encode($radarPayload) ?>;

    window.renderRadarChart = function() {
        const canvas = document.getElementById('cplRadarChart');
        if (!canvas) return;
        if (window.cplChartInstance) {
            window.cplChartInstance.destroy();
        }

        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.16)' : 'rgba(0, 0, 0, 0.12)';
        const textColor = isDark ? '#ffffff' : '#000000';
        const primaryColor = isDark ? '#38bdf8' : '#0284c7';

        const ctx = canvas.getContext('2d');
        window.cplChartInstance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: radarPayload.labels || [],
                datasets: [
                    {
                        label: 'Capaian Aktual (%)',
                        data: radarPayload.scores || [],
                        backgroundColor: isDark ? 'rgba(56, 189, 248, 0.25)' : 'rgba(2, 132, 199, 0.2)',
                        borderColor: primaryColor,
                        borderWidth: 2.5,
                        pointBackgroundColor: primaryColor,
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: primaryColor
                    },
                    {
                        label: 'Target Capaian Prodi (%)',
                        data: radarPayload.targets || [],
                        backgroundColor: 'transparent',
                        borderColor: '#ef4444',
                        borderWidth: 1.8,
                        borderDash: [5, 5],
                        pointRadius: 3,
                        pointBackgroundColor: '#ef4444'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: gridColor },
                        grid: { color: gridColor },
                        pointLabels: {
                            color: textColor,
                            font: { size: 12, weight: 'bold' }
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100,
                            stepSize: 20,
                            backdropColor: 'transparent',
                            color: textColor
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    };

    // Render awal
    window.renderRadarChart();
});
</script>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main.php'; 
?>
