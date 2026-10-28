<?php
$title = 'Pengampu Mata Kuliah';
ob_start();
$base = $GLOBALS['config']['base_url'] ?? '/obe/public';
$queryBase = $search !== '' ? '&search=' . rawurlencode($search) : '';
?>
<style>
.pm-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.pm-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}
.pm-head h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-main);
  display: flex;
  align-items: center;
  gap: 10px;
}
.pm-head p {
  margin: 5px 0 0;
  color: var(--text-muted);
  font-size: 0.88rem;
}
.pm-card {
  padding: 0;
  overflow: hidden;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
}
.pm-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-subtle);
  background: var(--surface);
  flex-wrap: wrap;
}
.pm-search {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  max-width: 680px;
  min-width: 280px;
}
.pm-search-box {
  position: relative;
  flex: 1;
}
.pm-search-box i.fa-magnifying-glass {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 0.9rem;
  pointer-events: none;
  z-index: 2;
}
.pm-search-box input[type="search"] {
  width: 100% !important;
  height: 42px !important;
  padding: 9px 40px 9px 40px !important;
  background-color: var(--input-bg) !important;
  color: var(--text-main) !important;
  border: 1px solid var(--input-border, var(--border)) !important;
  border-radius: var(--radius-md) !important;
  box-sizing: border-box !important;
  font-size: 0.88rem !important;
  transition: border-color 0.2s, box-shadow 0.2s !important;
}
.pm-search-box input[type="search"]:focus {
  outline: none !important;
  border-color: var(--primary) !important;
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.18) !important;
}
.pm-search-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  border-radius: 50%;
  text-decoration: none;
  font-size: 0.88rem;
  transition: all 0.15s ease;
  z-index: 2;
}
.pm-search-clear:hover {
  color: var(--text-main);
  background: var(--surface-hover);
}
.pm-search-btn {
  height: 42px;
  padding: 0 16px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: var(--radius-md);
  white-space: nowrap;
}
.pm-reset-btn {
  height: 42px;
  padding: 0 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: var(--radius-md);
  white-space: nowrap;
}
.pm-summary-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.84rem;
  color: var(--text-muted);
  flex-wrap: wrap;
}
.pm-search-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 6px;
  background: rgba(var(--primary-rgb), 0.12);
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 600;
}
.pm-table-wrap {
  overflow-x: auto;
}
.pm-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.84rem;
}
.pm-table th,
.pm-table td {
  padding: 13px 16px;
  border-bottom: 1px solid var(--border-subtle);
  text-align: left;
  vertical-align: middle;
}
.pm-table th {
  background: var(--table-header-bg, var(--surface-hover));
  color: var(--text-muted);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  white-space: nowrap;
}
.pm-table tbody tr:hover {
  background: var(--table-hover-bg, rgba(var(--primary-rgb), 0.035));
}
.pm-course strong,
.pm-pjmk strong {
  display: block;
  color: var(--text-main);
  font-weight: 600;
}
.pm-course small,
.pm-pjmk small {
  color: var(--text-muted);
  font-size: 0.78rem;
}
.pm-actions {
  display: flex;
  gap: 6px;
  white-space: nowrap;
}
.pm-badge {
  display: inline-flex;
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(var(--primary-rgb), 0.12);
  color: var(--primary);
  font-weight: 700;
  font-size: 0.76rem;
}
.pm-status-badge {
  display: inline-flex;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}
.pm-status-active {
  background: rgba(16, 185, 129, 0.12);
  color: var(--success);
  border: 1px solid rgba(16, 185, 129, 0.25);
}
.pm-status-inactive {
  background: rgba(239, 68, 68, 0.12);
  color: var(--danger);
  border: 1px solid rgba(239, 68, 68, 0.25);
}
.pm-empty {
  text-align: center !important;
  padding: 45px 20px !important;
  color: var(--text-muted);
}
.pm-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 14px 20px;
  border-top: 1px solid var(--border-subtle);
  background: var(--surface);
  flex-wrap: wrap;
}
.pm-pages {
  display: flex;
  align-items: center;
  gap: 5px;
}
.pm-page-link {
  min-width: 36px;
  height: 36px;
  padding: 0 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-main);
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 600;
  transition: all 0.15s ease;
}
.pm-page-link:hover:not(.disabled):not(.active) {
  background: var(--surface-hover);
  border-color: var(--primary);
  color: var(--primary);
}
.pm-page-link.active {
  background: var(--primary) !important;
  border-color: var(--primary) !important;
  color: #ffffff !important;
  box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.35);
}
.pm-page-link.disabled {
  opacity: 0.35;
  pointer-events: none;
  cursor: not-allowed;
  border-color: var(--border-subtle);
  color: var(--text-muted);
}
.pm-page-dots {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  color: var(--text-muted);
  font-size: 0.85rem;
}
.pm-dialog {
  width: min(720px, calc(100vw - 24px));
  padding: 0;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: var(--card-bg);
  color: var(--text-main);
  box-shadow: var(--shadow-lg);
}
.pm-dialog::backdrop {
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(2px);
}
.pm-dialog-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.pm-dialog-head h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-main);
}
.pm-form {
  padding: 20px;
}
.pm-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.pm-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-main);
  min-width: 0;
}
.pm-field.full {
  grid-column: 1 / -1;
}
.pm-field select {
  width: 100%;
  min-height: 42px;
  padding: 9px 12px;
}
.pm-field select[multiple] {
  min-height: 150px;
}
.pm-help {
  color: var(--text-muted);
  font-size: 0.74rem;
  font-weight: 400;
}
.pm-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid var(--border-subtle);
}
@media(max-width: 768px) {
  .pm-head, .pm-toolbar, .pm-pagination {
    align-items: stretch;
    flex-direction: column;
  }
  .pm-search {
    max-width: none;
  }
  .pm-grid {
    grid-template-columns: 1fr;
  }
  .pm-field.full {
    grid-column: auto;
  }
  .pm-table {
    min-width: 780px;
  }
  .pm-pages {
    overflow-x: auto;
    padding-bottom: 4px;
  }
}
</style>

<div class="pm-page">
  <header class="pm-head">
    <div>
      <h2><i class="fa-solid fa-chalkboard-user" style="color:var(--primary)"></i> Pengampu Mata Kuliah</h2>
      <p>Sumber penugasan resmi PJMK dan tim teaching untuk RPS, kelas, dan asesmen perkuliahan.</p>
    </div>
    <button class="btn btn-primary" onclick="openAssignment()" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;font-weight:600;">
      <i class="fa-solid fa-plus"></i> Atur Pengampu
    </button>
  </header>

  <section class="card pm-card">
    <!-- Toolbar Pencarian Server-Side -->
    <div class="pm-toolbar">
      <form class="pm-search" method="get" action="<?= $base ?>/pengampu-mk">
        <div class="pm-search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input 
            type="search" 
            name="search" 
            value="<?= htmlspecialchars($search) ?>" 
            placeholder="Cari kode MK, nama mata kuliah, prodi, PJMK, atau tahun akademik..." 
            autocomplete="off"
            aria-label="Cari pengampu mata kuliah"
          >
          <?php if($search !== ''): ?>
            <a class="pm-search-clear" href="<?= $base ?>/pengampu-mk" title="Hapus pencarian">
              <i class="fa-solid fa-circle-xmark"></i>
            </a>
          <?php endif; ?>
        </div>
        <button class="btn btn-primary pm-search-btn" type="submit">
          <i class="fa-solid fa-magnifying-glass"></i> Cari
        </button>
        <?php if($search !== ''): ?>
          <a class="btn btn-secondary pm-reset-btn" href="<?= $base ?>/pengampu-mk" title="Reset pencarian">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        <?php endif; ?>
      </form>
      <div class="pm-summary-bar">
        <span>Menampilkan <strong><?= $totalAssignments > 0 ? (($page - 1) * $perPage) + 1 : 0 ?>–<?= min($page * $perPage, $totalAssignments) ?></strong> dari <strong><?= $totalAssignments ?></strong> data</span>
        <?php if($search !== ''): ?>
          <span class="pm-search-badge">
            <i class="fa-solid fa-filter"></i> Kata kunci: "<?= htmlspecialchars($search) ?>"
          </span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Tabel Daftar Pengampu MK -->
    <div class="pm-table-wrap">
      <table class="pm-table">
        <thead>
          <tr>
            <th style="min-width: 220px;">Mata Kuliah</th>
            <th style="min-width: 140px;">Tahun Akademik</th>
            <th style="min-width: 200px;">PJMK</th>
            <th style="min-width: 110px;">Tim Teaching</th>
            <th style="min-width: 90px;">Status</th>
            <th style="width: 100px; text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if(empty($assignments)): ?>
          <tr>
            <td colspan="6" class="pm-empty">
              <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:30px 0;">
                <i class="fa-solid fa-inbox" style="font-size:2.2rem;opacity:0.35;color:var(--text-muted);"></i>
                <div style="font-size:0.95rem;color:var(--text-main);font-weight:600;">
                  <?= $search !== '' ? 'Data pengampu tidak ditemukan' : 'Belum ada data penugasan pengampu' ?>
                </div>
                <div style="font-size:0.82rem;color:var(--text-muted);max-width:400px;line-height:1.4;">
                  <?= $search !== '' 
                      ? 'Tidak ada data penugasan yang cocok dengan kata kunci "' . htmlspecialchars($search) . '". Silakan periksa kembali kata kunci pencarian Anda.' 
                      : 'Klik tombol "Atur Pengampu" untuk mulai menugaskan PJMK dan tim teaching.' ?>
                </div>
                <?php if($search !== ''): ?>
                  <a href="<?= $base ?>/pengampu-mk" class="btn btn-sm btn-secondary" style="margin-top:6px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-rotate-left"></i> Reset Pencarian
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endif; ?>

        <?php foreach($assignments as $row): 
          $payload = [
            'mata_kuliah_id' => (int)$row['mata_kuliah_id'],
            'tahun_akademik_id' => (int)$row['tahun_akademik_id'],
            'pjmk_user_id' => (int)$row['pjmk_user_id'],
            'tim_ids' => $row['tim_ids'] ?? [],
            'status' => $row['status']
          ];
        ?>
          <tr>
            <td class="pm-course">
              <strong><?= htmlspecialchars($row['mk_kode'] . ' — ' . $row['mk_nama']) ?></strong>
              <small><?= htmlspecialchars($row['prodi_kode'] . ' · ' . $row['kurikulum_nama']) ?></small>
            </td>
            <td><?= htmlspecialchars($row['tahun_akademik']) ?></td>
            <td class="pm-pjmk">
              <strong><?= htmlspecialchars($row['pjmk_nama']) ?></strong>
              <small><?= htmlspecialchars($row['pjmk_nidn'] ?: '-') ?></small>
            </td>
            <td>
              <span class="pm-badge"><?= (int)$row['total_tim'] ?> dosen</span>
            </td>
            <td>
              <span class="pm-status-badge <?= $row['status'] === 'ACTIVE' ? 'pm-status-active' : 'pm-status-inactive' ?>">
                <?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Tidak aktif' ?>
              </span>
            </td>
            <td style="text-align: right;">
              <div class="pm-actions" style="justify-content: flex-end;">
                <button 
                  type="button" 
                  class="btn btn-sm btn-secondary" 
                  data-assignment="<?= htmlspecialchars(json_encode($payload), ENT_QUOTES) ?>" 
                  onclick="openAssignment(this)" 
                  title="Edit penugasan PJMK"
                >
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= $base ?>/pengampu-mk/sync" style="display:inline;margin:0;">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <button 
                    type="button" 
                    class="btn btn-sm btn-primary" 
                    title="Sinkronkan penugasan ke kelas perkuliahan" 
                    onclick="syncAssignment(this)"
                  >
                    <i class="fa-solid fa-rotate"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Navigasi Pagination 10 Data Server-Side -->
    <?php if($totalAssignments > 0 && $totalPages > 1): ?>
      <div class="pm-pagination">
        <div style="font-size:0.82rem;color:var(--text-muted);">
          Halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong> (Total <?= $totalAssignments ?> penugasan)
        </div>
        <nav class="pm-pages" aria-label="Pagination">
          <a 
            class="pm-page-link <?= $page <= 1 ? 'disabled' : '' ?>" 
            href="<?= $base ?>/pengampu-mk?page=<?= $page - 1 ?><?= $queryBase ?>" 
            title="Halaman Sebelumnya"
            <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>
          >
            <i class="fa-solid fa-chevron-left"></i>
          </a>

          <?php 
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          if ($start > 1): ?>
            <a class="pm-page-link" href="<?= $base ?>/pengampu-mk?page=1<?= $queryBase ?>">1</a>
            <?php if ($start > 2): ?><span class="pm-page-dots">…</span><?php endif; ?>
          <?php endif; ?>

          <?php for($i = $start; $i <= $end; $i++): ?>
            <a 
              class="pm-page-link <?= $i === $page ? 'active' : '' ?>" 
              href="<?= $base ?>/pengampu-mk?page=<?= $i ?><?= $queryBase ?>"
              <?= $i === $page ? 'aria-current="page"' : '' ?>
            >
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?><span class="pm-page-dots">…</span><?php endif; ?>
            <a class="pm-page-link" href="<?= $base ?>/pengampu-mk?page=<?= $totalPages ?><?= $queryBase ?>"><?= $totalPages ?></a>
          <?php endif; ?>

          <a 
            class="pm-page-link <?= $page >= $totalPages ? 'disabled' : '' ?>" 
            href="<?= $base ?>/pengampu-mk?page=<?= $page + 1 ?><?= $queryBase ?>" 
            title="Halaman Berikutnya"
            <?= $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>
          >
            <i class="fa-solid fa-chevron-right"></i>
          </a>
        </nav>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Form Pengaturan PJMK & Tim Teaching (Dipertahankan) -->
<dialog class="pm-dialog" id="assignmentDialog">
  <div class="pm-dialog-head">
    <h3><i class="fa-solid fa-chalkboard-user" style="color:var(--primary);margin-right:6px;"></i> Atur PJMK & Tim Teaching</h3>
    <button class="btn btn-sm btn-secondary" type="button" onclick="assignmentDialog.close()" title="Tutup dialog">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
  <form class="pm-form" method="post" action="<?= $base ?>/pengampu-mk/save">
    <div class="pm-grid">
      <label class="pm-field full">
        Mata kuliah
        <select class="form-control" name="mata_kuliah_id" id="pmCourse" required onchange="filterLecturers()">
          <option value="">Pilih mata kuliah...</option>
          <?php foreach($courses as $course): ?>
            <option value="<?= (int)$course['id'] ?>" data-prodi="<?= (int)$course['prodi_id'] ?>">
              <?= htmlspecialchars($course['prodi_kode'] . ' · ' . $course['kode'] . ' — ' . $course['nama'] . ' (' . $course['kurikulum_nama'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="pm-field">
        Tahun akademik
        <select class="form-control" name="tahun_akademik_id" id="pmTa" required>
          <?php foreach($academicYears as $ta): ?>
            <option value="<?= (int)$ta['id'] ?>">
              <?= htmlspecialchars($ta['nama'] . (!empty($ta['is_active']) ? ' · Aktif' : '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="pm-field">
        Status penugasan
        <select class="form-control" name="status" id="pmStatus">
          <option value="ACTIVE">Aktif</option>
          <option value="INACTIVE">Tidak aktif</option>
        </select>
      </label>

      <label class="pm-field full">
        Dosen PJMK
        <select class="form-control pm-lecturer" name="pjmk_user_id" id="pmPjmk" required>
          <option value="">Pilih PJMK...</option>
          <?php foreach($lecturers as $d): ?>
            <?php $prodiLabel = !empty($d['prodi_nama']) ? ' [' . $d['prodi_nama'] . ']' : (!empty($d['prodi_kode']) ? ' [' . $d['prodi_kode'] . ']' : ''); ?>
            <option value="<?= (int)$d['id'] ?>" data-prodi="<?= (int)$d['prodi_id'] ?>">
              <?= htmlspecialchars($d['nama_lengkap'] . ($d['nidn'] ? ' · ' . $d['nidn'] : '') . $prodiLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="pm-field full">
        Tim teaching <span class="pm-help">Gunakan Ctrl/Command untuk memilih lebih dari satu dosen lintas prodi.</span>
        <select class="form-control pm-lecturer" name="tim_ids[]" id="pmTeam" multiple>
          <?php foreach($lecturers as $d): ?>
            <?php $prodiLabel = !empty($d['prodi_nama']) ? ' [' . $d['prodi_nama'] . ']' : (!empty($d['prodi_kode']) ? ' [' . $d['prodi_kode'] . ']' : ''); ?>
            <option value="<?= (int)$d['id'] ?>" data-prodi="<?= (int)$d['prodi_id'] ?>">
              <?= htmlspecialchars($d['nama_lengkap'] . ($d['nidn'] ? ' · ' . $d['nidn'] : '') . $prodiLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <div class="pm-footer">
      <button type="button" class="btn btn-secondary" onclick="assignmentDialog.close()">Batal</button>
      <button class="btn btn-primary" type="submit">
        <i class="fa-solid fa-floppy-disk"></i> Simpan Penugasan
      </button>
    </div>
  </form>
</dialog>

<script>
const assignmentDialog = document.getElementById('assignmentDialog');

function filterLecturers() {
    // Seluruh dosen tetap dapat dipilih untuk mendukung penugasan tim teaching lintas prodi & MKDU.
}

function openAssignment(btn = null) {
    const d = btn ? JSON.parse(btn.dataset.assignment) : null;
    document.getElementById('pmCourse').value = d?.mata_kuliah_id || '';
    document.getElementById('pmTa').value = d?.tahun_akademik_id || document.querySelector('#pmTa option')?.value || '';
    document.getElementById('pmStatus').value = d?.status || 'ACTIVE';
    filterLecturers();
    document.getElementById('pmPjmk').value = d?.pjmk_user_id || '';
    const ids = (d?.tim_ids || []).map(String);
    document.querySelectorAll('#pmTeam option').forEach(o => o.selected = ids.includes(o.value));
    assignmentDialog.showModal();
}

function syncAssignment(btn) {
    const dark = document.documentElement.getAttribute('data-theme') !== 'light';
    Swal.fire({
        title: 'Sinkronkan pengampu?',
        text: 'PJMK dan tim teaching pada seluruh kelas di tahun akademik ini akan diperbarui sesuai penugasan ini.',
        icon: 'question',
        background: dark ? '#111114' : '#ffffff',
        color: dark ? '#f4f4f5' : '#18181b',
        showCancelButton: true,
        confirmButtonText: 'Ya, sinkronkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: 'var(--primary)'
    }).then(r => {
        if (r.isConfirmed) {
            btn.closest('form').submit();
        }
    });
}
</script>
<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/main.php'; 
?>
