<?php
$title = 'Mahasiswa RPL';
ob_start();
$base = $GLOBALS['config']['base_url'] ?? '/obe/public';
$canManage = in_array($_SESSION['role'] ?? '', ['Super Admin', 'Admin Prodi'], true);
$totalDiakui = array_sum(array_map(fn($p) => (int)$p['sks_diakui'], $profiles));
$totalPending = array_sum(array_map(fn($p) => (int)$p['menunggu'], $profiles));
$threeSemester = count(array_filter($profiles, fn($p) => (int)$p['skema_semester'] === 3));
?>
<style>
.rpl-list-page{display:flex;flex-direction:column;gap:20px}.rpl-list-header{display:flex;justify-content:space-between;align-items:flex-start;gap:18px}.rpl-list-eyebrow{margin:0 0 5px;color:var(--primary);font-size:.76rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.rpl-list-header h2{margin:0;font-size:1.55rem;color:var(--text-main)}.rpl-list-header p{margin:7px 0 0;color:var(--text-muted);font-size:.88rem;max-width:650px}.rpl-list-actions{display:flex;gap:9px;flex-wrap:wrap}.rpl-list-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.rpl-list-stat{padding:17px 19px;position:relative;overflow:hidden}.rpl-list-stat:after{content:"";position:absolute;right:-18px;bottom:-26px;width:78px;height:78px;border-radius:50%;background:var(--primary);opacity:.055}.rpl-list-stat-label{display:flex;align-items:center;gap:7px;color:var(--text-muted);font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.035em}.rpl-list-stat strong{display:block;margin-top:8px;font-size:1.65rem;line-height:1.1;color:var(--text-main)}.rpl-list-panel{padding:0;overflow:hidden}.rpl-list-panel-head{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:17px 20px;border-bottom:1px solid var(--border)}.rpl-list-panel-head h3{margin:0;font-size:1rem}.rpl-list-panel-head p{margin:4px 0 0;color:var(--text-muted);font-size:.78rem}.rpl-enroll-form{display:grid;grid-template-columns:minmax(240px,1.25fr) minmax(220px,1fr) auto;gap:14px;align-items:end;padding:20px}.rpl-list-field{display:flex;flex-direction:column;gap:7px;font-size:.8rem;font-weight:650;color:var(--text-main)}.rpl-list-field .form-control{width:100%;min-height:42px}.rpl-list-toolbar{display:flex;align-items:center;gap:12px;padding:13px 20px;background:var(--surface-hover);border-bottom:1px solid var(--border)}.rpl-search{position:relative;width:min(370px,100%)}.rpl-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem}.rpl-search input{width:100%;min-height:38px;padding-left:36px}.rpl-list-count{margin-left:auto;color:var(--text-muted);font-size:.78rem}.rpl-table-wrap{overflow-x:auto}.rpl-list-table{width:100%;border-collapse:collapse;font-size:.84rem}.rpl-list-table th{padding:11px 14px;background:var(--surface-hover);border-bottom:1px solid var(--border);color:var(--text-muted);font-size:.71rem;text-align:left;text-transform:uppercase;letter-spacing:.045em;white-space:nowrap}.rpl-list-table td{padding:14px;border-bottom:1px solid var(--border-subtle);vertical-align:middle;color:var(--text-main)}.rpl-list-table tbody tr:last-child td{border-bottom:0}.rpl-list-table tbody tr:hover td{background:rgba(99,102,241,.035)}.rpl-avatar{display:flex;align-items:center;gap:11px}.rpl-avatar-icon{display:flex;align-items:center;justify-content:center;flex:0 0 36px;height:36px;border-radius:10px;background:rgba(99,102,241,.12);color:var(--primary);font-weight:800}.rpl-person strong{display:block;font-size:.85rem}.rpl-person span{display:block;margin-top:3px;color:var(--text-muted);font-size:.75rem}.rpl-code{display:inline-block;padding:4px 7px;border:1px solid var(--border);border-radius:6px;background:var(--surface-hover);font:700 .76rem monospace}.rpl-sks-value{font-size:1rem;font-weight:800}.rpl-sks-value small{font-size:.68rem;color:var(--text-muted);font-weight:600}.rpl-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:999px;font-size:.72rem;font-weight:750;white-space:nowrap}.rpl-badge.ready{color:#10b981;background:rgba(16,185,129,.12)}.rpl-badge.waiting{color:#f59e0b;background:rgba(245,158,11,.12)}.rpl-badge.pending{color:#ef4444;background:rgba(239,68,68,.11)}.rpl-badge.clear{color:var(--text-muted);background:var(--surface-hover)}.rpl-empty{padding:42px 20px;text-align:center;color:var(--text-muted)}.rpl-empty i{display:block;margin-bottom:10px;font-size:1.8rem;opacity:.55}.rpl-row-action{text-align:right}.rpl-row-action-group{display:inline-flex;align-items:center;justify-content:flex-end;gap:6px}.rpl-hidden{display:none}.rpl-modal-dialog{width:min(500px,calc(100vw - 24px));border:1px solid var(--border);border-radius:14px;padding:0;background:var(--card-bg);color:var(--text-main);box-shadow:var(--shadow-lg)}.rpl-modal-dialog::backdrop{background:rgba(0,0,0,.72);backdrop-filter:blur(3px)}.rpl-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}.rpl-modal-head h3{margin:0;font-size:1.02rem}.rpl-modal-close{border:0;background:transparent;color:var(--text-muted);cursor:pointer;font-size:1.1rem}.rpl-modal-form{padding:20px;display:flex;flex-direction:column;gap:14px}@media(max-width:1000px){.rpl-list-stats{grid-template-columns:repeat(2,1fr)}.rpl-enroll-form{grid-template-columns:1fr 1fr}.rpl-enroll-form .btn{grid-column:1/-1;justify-content:center}}@media(max-width:680px){.rpl-list-header{flex-direction:column}.rpl-list-actions{width:100%}.rpl-list-actions .btn{flex:1;justify-content:center}.rpl-list-stats{grid-template-columns:1fr 1fr}.rpl-enroll-form{grid-template-columns:1fr}.rpl-enroll-form .btn{grid-column:auto}.rpl-list-toolbar{align-items:stretch;flex-direction:column}.rpl-search{width:100%}.rpl-list-count{margin-left:0}.rpl-list-table th:nth-child(3),.rpl-list-table td:nth-child(3){display:none}}@media(max-width:430px){.rpl-list-stats{grid-template-columns:1fr}}
</style>

<div class="rpl-list-page">
    <header class="rpl-list-header">
        <div>
            <p class="rpl-list-eyebrow">Rekognisi Pembelajaran Lampau</p>
            <h2>Konversi SKS Mahasiswa RPL</h2>
            <p>Kelola pengakuan mata kuliah, keputusan Kaprodi, dan rencana masa studi mahasiswa RPL dalam satu halaman.</p>
        </div>
        <div class="rpl-list-actions">
            <?php if ($canManage): ?><a class="btn btn-secondary" href="<?= $base ?>/rpl-setting"><i class="fa-solid fa-sliders"></i> Pengaturan MK</a><?php endif; ?>
        </div>
    </header>

    <section class="rpl-list-stats">
        <div class="card rpl-list-stat"><span class="rpl-list-stat-label"><i class="fa-solid fa-user-graduate"></i> Mahasiswa RPL</span><strong><?= count($profiles) ?></strong></div>
        <div class="card rpl-list-stat"><span class="rpl-list-stat-label"><i class="fa-solid fa-circle-check"></i> Total SKS Diakui</span><strong><?= $totalDiakui ?></strong></div>
        <div class="card rpl-list-stat"><span class="rpl-list-stat-label"><i class="fa-solid fa-hourglass-half"></i> Menunggu Keputusan</span><strong><?= $totalPending ?></strong></div>
        <div class="card rpl-list-stat"><span class="rpl-list-stat-label"><i class="fa-solid fa-calendar-check"></i> Skema 3 Semester</span><strong><?= $threeSemester ?></strong></div>
    </section>

    <?php if ($canManage): ?>
    <section class="card rpl-list-panel">
        <div class="rpl-list-panel-head"><div><h3><i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:7px"></i> Tetapkan Mahasiswa RPL</h3><p>Pilih mahasiswa dan kurikulum yang menjadi dasar rekognisi.</p></div></div>
        <form class="rpl-enroll-form" method="post" action="<?= $base ?>/rpl/profile/store">
            <label class="rpl-list-field"><span>Mahasiswa <span style="color:#ef4444">*</span></span><select name="mahasiswa_id" class="form-control" required><option value="">Pilih mahasiswa...</option><?php foreach ($students as $student): if ($student['rpl_id']) continue; ?><option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['username'] . ' — ' . $student['nama_lengkap']) ?></option><?php endforeach; ?></select></label>
            <label class="rpl-list-field"><span>Kurikulum <span style="color:#ef4444">*</span></span><select name="kurikulum_id" class="form-control" required><option value="">Pilih kurikulum...</option><?php foreach ($kurikulum as $item): ?><option value="<?= $item['id'] ?>"><?= htmlspecialchars(($item['kode'] ?? '') . ' — ' . $item['nama']) ?></option><?php endforeach; ?></select></label>
            <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tetapkan RPL</button>
        </form>
    </section>
    <?php endif; ?>

    <section class="card rpl-list-panel">
        <div class="rpl-list-panel-head"><div><h3>Daftar Mahasiswa RPL</h3><p>Pilih mahasiswa untuk mengelola konversi dan melihat rencana studinya.</p></div></div>
        <div class="rpl-list-toolbar"><div class="rpl-search"><i class="fa-solid fa-magnifying-glass"></i><input id="rplStudentSearch" class="form-control" type="search" placeholder="Cari NIM, nama, atau kurikulum..."></div><span class="rpl-list-count" id="rplVisibleCount"><?= count($profiles) ?> mahasiswa</span></div>
        <?php if (empty($profiles)): ?>
            <div class="rpl-empty"><i class="fa-solid fa-user-graduate"></i>Belum ada mahasiswa yang ditetapkan sebagai mahasiswa RPL.</div>
        <?php else: ?>
        <div class="rpl-table-wrap"><table class="rpl-list-table"><thead><tr><th>Mahasiswa</th><th>Kurikulum</th><th>SKS diakui</th><th>Skema studi</th><th>Pengajuan</th><th style="text-align:right">Aksi</th></tr></thead><tbody id="rplStudentRows">
            <?php foreach ($profiles as $profile): 
                $initial = mb_strtoupper(mb_substr(trim($profile['nama_lengkap']), 0, 1)); 
                $editData = [
                    'id' => (int)$profile['id'],
                    'nama_lengkap' => $profile['nama_lengkap'],
                    'nim' => $profile['nim'],
                    'kurikulum_id' => (int)$profile['kurikulum_id'],
                    'status' => $profile['status'] ?? 'AKTIF'
                ];
            ?>
            <tr data-search="<?= htmlspecialchars(mb_strtolower($profile['nim'] . ' ' . $profile['nama_lengkap'] . ' ' . $profile['kurikulum_nama']), ENT_QUOTES) ?>">
                <td><div class="rpl-avatar"><span class="rpl-avatar-icon"><?= htmlspecialchars($initial) ?></span><span class="rpl-person"><strong><?= htmlspecialchars($profile['nama_lengkap']) ?></strong><span><?= htmlspecialchars($profile['nim']) ?></span></span></div></td>
                <td><span class="rpl-code"><?= htmlspecialchars($profile['kurikulum_nama']) ?></span></td>
                <td><span class="rpl-sks-value"><?= (int)$profile['sks_diakui'] ?> <small>SKS</small></span></td>
                <td><?php if ($profile['skema_semester']): ?><span class="rpl-badge ready"><i class="fa-solid fa-calendar-check"></i><?= (int)$profile['skema_semester'] ?> semester</span><?php else: ?><span class="rpl-badge waiting"><i class="fa-regular fa-clock"></i>Belum ditetapkan</span><?php endif; ?></td>
                <td><?php if ((int)$profile['menunggu'] > 0): ?><span class="rpl-badge pending"><i class="fa-solid fa-hourglass-half"></i><?= (int)$profile['menunggu'] ?> menunggu</span><?php else: ?><span class="rpl-badge clear"><i class="fa-solid fa-check"></i>Tidak ada</span><?php endif; ?></td>
                <td class="rpl-row-action">
                    <div class="rpl-row-action-group">
                        <a class="btn btn-sm btn-primary" href="<?= $base ?>/rpl/detail?id=<?= $profile['id'] ?>" title="Kelola konversi & rencana studi">Kelola <i class="fa-solid fa-arrow-right"></i></a>
                        <?php if ($canManage): ?>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="openEditRplStudent(this)" data-profile="<?= htmlspecialchars(json_encode($editData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" title="Edit mahasiswa RPL"><i class="fa-solid fa-pen"></i></button>
                        <form method="post" action="<?= $base ?>/rpl/profile/delete" style="display:inline;margin:0" class="form-delete-rpl-profile">
                            <input type="hidden" name="id" value="<?= (int)$profile['id'] ?>">
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteRplStudent(this, '<?= htmlspecialchars(addslashes($profile['nama_lengkap'] . ' (' . $profile['nim'] . ')'), ENT_QUOTES, 'UTF-8') ?>')" title="Hapus penetapan RPL"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </section>
</div>

<?php if ($canManage): ?>
<dialog class="rpl-modal-dialog" id="dialogEditRplProfile">
    <div class="rpl-modal-head">
        <h3><i class="fa-solid fa-user-pen" style="color:var(--primary);margin-right:7px"></i> Edit Mahasiswa RPL</h3>
        <button type="button" class="rpl-modal-close" onclick="document.getElementById('dialogEditRplProfile').close()" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form class="rpl-modal-form" method="post" action="<?= $base ?>/rpl/profile/update">
        <input type="hidden" name="id" id="editProfileId">
        <div style="padding:10px 14px;border-radius:8px;background:var(--surface-hover);border:1px solid var(--border)">
            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase">Mahasiswa:</div>
            <div id="editProfileStudentText" style="font-weight:700;font-size:.92rem;margin-top:2px;color:var(--text-main)"></div>
        </div>
        <label class="rpl-list-field">
            <span>Kurikulum Acuan <span style="color:#ef4444">*</span></span>
            <select name="kurikulum_id" id="editProfileKurikulumId" class="form-control" required>
                <?php foreach ($kurikulum as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars(($item['kode'] ?? '') . ' — ' . $item['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="rpl-list-field">
            <span>Status <span style="color:#ef4444">*</span></span>
            <select name="status" id="editProfileStatus" class="form-control" required>
                <option value="AKTIF">AKTIF</option>
                <option value="SELESAI">SELESAI</option>
                <option value="NONAKTIF">NONAKTIF</option>
            </select>
        </label>
        <div style="display:flex;justify-content:flex-end;gap:9px;margin-top:10px">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('dialogEditRplProfile').close()">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
        </div>
    </form>
</dialog>
<script>
function openEditRplStudent(btn) {
    const data = JSON.parse(btn.dataset.profile);
    document.getElementById('editProfileId').value = data.id;
    document.getElementById('editProfileStudentText').textContent = `${data.nim} — ${data.nama_lengkap}`;
    document.getElementById('editProfileKurikulumId').value = data.kurikulum_id;
    document.getElementById('editProfileStatus').value = data.status || 'AKTIF';
    document.getElementById('dialogEditRplProfile').showModal();
}
function confirmDeleteRplStudent(btn, studentText) {
    const form = btn.closest('form');
    const dark = document.documentElement.getAttribute('data-theme') !== 'light';
    Swal.fire({
        title: 'Hapus penetapan mahasiswa RPL?',
        text: `Mahasiswa ${studentText} akan dihapus dari data RPL beserta seluruh riwayat konversi dan rencana studinya.`,
        icon: 'warning',
        background: dark ? '#111114' : '#ffffff',
        color: dark ? '#f4f4f5' : '#18181b',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rpl-swal-popup',
            title: 'rpl-swal-title',
            confirmButton: 'rpl-swal-confirm',
            cancelButton: 'rpl-swal-cancel'
        }
    }).then(result => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
<?php endif; ?>

<script>
(function(){const input=document.getElementById('rplStudentSearch');if(!input)return;const rows=[...document.querySelectorAll('#rplStudentRows tr')];const count=document.getElementById('rplVisibleCount');input.addEventListener('input',function(){const query=this.value.trim().toLocaleLowerCase('id-ID');let visible=0;rows.forEach(row=>{const show=!query||row.dataset.search.includes(query);row.classList.toggle('rpl-hidden',!show);if(show)visible++;});if(count)count.textContent=visible+' mahasiswa';});})();
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/main.php'; ?>
