<?php
// views/aturan-kurikulum/index.php
$title = 'Aturan Kurikulum per Angkatan - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">
            <i class="fa-solid fa-scale-balanced" style="color: var(--primary); margin-right: 6px;"></i>
            Aturan Kurikulum per Angkatan
        </h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Atur kurikulum yang berlaku berdasarkan tahun angkatan mahasiswa. Aturan ini menentukan CPL, MK, dan RPS yang digunakan.
        </p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('modalTambah').style.display='flex'">
        <i class="fa-solid fa-plus"></i> Tambah Aturan
    </button>
</div>

<?php if(isset($_SESSION['flash_success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--success); color: var(--success); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['flash_error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); color: var(--danger); padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Info Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--primary), #818cf8);"></div>
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-gears" style="font-size: 1.1rem; color: var(--primary);"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);"><?= count($aturan) ?></div>
                <div style="font-size: 0.82rem; color: var(--text-muted);">Total Aturan Aktif</div>
            </div>
        </div>
    </div>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-book" style="font-size: 1.1rem; color: #10b981;"></i>
            </div>
            <div>
                <?php
                $uniqueKurikulum = [];
                foreach ($aturan as $a) { $uniqueKurikulum[$a['kurikulum_id']] = $a['nama_kurikulum']; }
                ?>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);"><?= count($uniqueKurikulum) ?></div>
                <div style="font-size: 0.82rem; color: var(--text-muted);">Kurikulum Terhubung</div>
            </div>
        </div>
    </div>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-user-graduate" style="font-size: 1.1rem; color: #f59e0b;"></i>
            </div>
            <div>
                <?php $totalMhs = array_sum(array_column($distribusi, 'jumlah_mahasiswa')); ?>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);"><?= $totalMhs ?></div>
                <div style="font-size: 0.82rem; color: var(--text-muted);">Total Mahasiswa Tercover</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Aturan Kurikulum -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 28px;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
        <span style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">Daftar Aturan Kurikulum</span>
    </div>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 50px;">#</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">ANGKATAN</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">KURIKULUM</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">KETERANGAN</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 90px;">PRIORITAS</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); width: 120px; text-align: center;">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($aturan)): ?>
                <tr>
                    <td colspan="6" style="padding: 40px 18px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                        Belum ada aturan kurikulum. Klik tombol "Tambah Aturan" untuk memulai.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($aturan as $i => $a): ?>
                    <tr style="border-bottom: 1px solid var(--border-subtle);" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''">
                        <td style="padding: 14px 18px; font-size: 0.88rem; color: var(--text-muted);"><?= $i + 1 ?></td>
                        <td style="padding: 14px 18px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="background: linear-gradient(135deg, var(--primary), #818cf8); color: white; padding: 4px 12px; border-radius: 6px; font-weight: 600; font-size: 0.88rem;">
                                    <?= $a['angkatan_mulai'] ?><?= $a['angkatan_selesai'] ? '–' . $a['angkatan_selesai'] : ' +' ?>
                                </span>
                                <?php if (!$a['angkatan_selesai']): ?>
                                    <span style="font-size: 0.72rem; color: var(--warning); background: rgba(245,158,11,0.12); padding: 2px 8px; border-radius: 4px;">Seterusnya</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;"><?= htmlspecialchars($a['nama_kurikulum']) ?></div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">
                                <?= htmlspecialchars($a['kode_kurikulum']) ?> · 
                                <span style="color: <?= $a['status_kurikulum'] === 'ACTIVE' ? 'var(--success)' : 'var(--text-muted)' ?>;">
                                    <?= $a['status_kurikulum'] ?>
                                </span>
                            </div>
                        </td>
                        <td style="padding: 14px 18px; font-size: 0.85rem; color: var(--text-secondary);">
                            <?= htmlspecialchars($a['keterangan'] ?? '-') ?>
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            <span style="background: var(--bg-hover); padding: 4px 10px; border-radius: 4px; font-size: 0.82rem; font-weight: 500; color: var(--text-main);"><?= $a['prioritas'] ?></span>
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button type="button" class="btn btn-sm btn-secondary" title="Edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($a)) ?>)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="<?= $baseUrl ?>/aturan-kurikulum/delete?id=<?= $a['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Hapus aturan ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Distribusi Mahasiswa per Angkatan -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-users" style="color: #f59e0b;"></i>
        <span style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">Distribusi Mahasiswa per Angkatan</span>
        <span style="font-size: 0.78rem; color: var(--text-muted); margin-left: auto;">Otomatis dihitung dari NIM mahasiswa</span>
    </div>
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">ANGKATAN</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">JUMLAH MHS</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">KURIKULUM BERLAKU</th>
                <th style="padding: 12px 18px; font-weight: 600; font-size: 0.82rem; color: var(--text-muted);">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($distribusi)): ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        Belum ada data mahasiswa.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($distribusi as $d): ?>
                    <tr style="border-bottom: 1px solid var(--border-subtle);" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''">
                        <td style="padding: 14px 18px;">
                            <span style="font-weight: 600; color: var(--text-main); font-size: 0.92rem;">
                                <i class="fa-solid fa-user-group" style="margin-right: 6px; color: var(--primary); font-size: 0.78rem;"></i>
                                Angkatan <?= $d['angkatan'] ?>
                            </span>
                        </td>
                        <td style="padding: 14px 18px;">
                            <span style="font-weight: 600; color: var(--text-main);"><?= $d['jumlah_mahasiswa'] ?></span>
                            <span style="color: var(--text-muted); font-size: 0.82rem;"> mahasiswa</span>
                        </td>
                        <td style="padding: 14px 18px; font-weight: 500; color: var(--text-main); font-size: 0.9rem;">
                            <?= htmlspecialchars($d['nama_kurikulum']) ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <?php if ($d['kurikulum_id']): ?>
                                <span style="background: rgba(16, 185, 129, 0.12); color: var(--success); padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Terhubung
                                </span>
                            <?php else: ?>
                                <span style="background: rgba(239, 68, 68, 0.12); color: var(--danger); padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Belum Ditentukan
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Aturan -->
<div id="modalTambah" style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 0; width: 520px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 6px;"></i>
                Tambah Aturan Kurikulum
            </h3>
            <button type="button" onclick="document.getElementById('modalTambah').style.display='none'" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.2rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/aturan-kurikulum/store" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Angkatan Mulai <span style="color: var(--danger);">*</span></label>
                    <input type="number" name="angkatan_mulai" required min="2000" max="2099" placeholder="Contoh: 2022"
                        style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Angkatan Selesai</label>
                    <input type="number" name="angkatan_selesai" min="2000" max="2099" placeholder="Kosong = seterusnya"
                        style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">Kosongkan jika berlaku seterusnya</div>
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Kurikulum <span style="color: var(--danger);">*</span></label>
                <select name="kurikulum_id" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                    <option value="">-- Pilih Kurikulum --</option>
                    <?php foreach ($kurikulumList as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?> (<?= htmlspecialchars($k['kode']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Keterangan</label>
                <textarea name="keterangan" rows="2" placeholder="Deskripsi aturan (opsional)"
                    style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; resize: vertical; box-sizing: border-box;"></textarea>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Prioritas</label>
                <input type="number" name="prioritas" value="0" min="0" max="100"
                    style="width: 100px; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px;">Angka lebih tinggi = lebih diprioritaskan</div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Aturan -->
<div id="modalEdit" style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 0; width: 520px; max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--warning); margin-right: 6px;"></i>
                Edit Aturan Kurikulum
            </h3>
            <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 1.2rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/aturan-kurikulum/update" style="padding: 24px;">
            <input type="hidden" name="id" id="editId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Angkatan Mulai <span style="color: var(--danger);">*</span></label>
                    <input type="number" name="angkatan_mulai" id="editMulai" required min="2000" max="2099"
                        style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Angkatan Selesai</label>
                    <input type="number" name="angkatan_selesai" id="editSelesai" min="2000" max="2099" placeholder="Kosong = seterusnya"
                        style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Kurikulum <span style="color: var(--danger);">*</span></label>
                <select name="kurikulum_id" id="editKurikulumId" required style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
                    <option value="">-- Pilih Kurikulum --</option>
                    <?php foreach ($kurikulumList as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?> (<?= htmlspecialchars($k['kode']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Keterangan</label>
                <textarea name="keterangan" id="editKeterangan" rows="2" placeholder="Deskripsi aturan (opsional)"
                    style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; resize: vertical; box-sizing: border-box;"></textarea>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 6px;">Prioritas</label>
                <input type="number" name="prioritas" id="editPrioritas" value="0" min="0" max="100"
                    style="width: 100px; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text-main); font-size: 0.9rem; box-sizing: border-box;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editMulai').value = data.angkatan_mulai;
    document.getElementById('editSelesai').value = data.angkatan_selesai || '';
    document.getElementById('editKurikulumId').value = data.kurikulum_id;
    document.getElementById('editKeterangan').value = data.keterangan || '';
    document.getElementById('editPrioritas').value = data.prioritas || 0;
    document.getElementById('modalEdit').style.display = 'flex';
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modalTambah').style.display = 'none';
        document.getElementById('modalEdit').style.display = 'none';
    }
});

// Close modals on backdrop click
['modalTambah', 'modalEdit'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
