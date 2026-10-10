<?php 
ob_start(); 
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div class="content-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Master Mahasiswa</h2>
            <p style="margin: 4px 0 0; font-size: 0.88rem; color: var(--text-muted);">
                Kelola data mahasiswa, penelusuran angkatan, kurikulum OBE yang berlaku, serta akses cepat ke KHS & capaian CPL.
            </p>
        </div>
        <a href="<?= $baseUrl ?>/mahasiswa/create" class="btn" style="background: var(--primary); color: white; padding: 10px 18px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);">
            <i class="fa-solid fa-user-plus"></i> Tambah Mahasiswa
        </a>
    </div>
</div>

<!-- Toolbar Filter & Pencarian -->
<div class="card" style="background: var(--surface); padding: 18px 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 20px;">
    <form id="filterForm" method="GET" action="<?= $baseUrl ?>/mahasiswa" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <!-- Input Pencarian -->
        <div style="flex: 2; min-width: 240px;">
            <label for="searchInput" style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">
                <i class="fa-solid fa-magnifying-glass" style="margin-right: 4px;"></i> Pencarian
            </label>
            <div style="position: relative;">
                <input 
                    type="search" 
                    id="searchInput" 
                    name="search" 
                    value="<?= htmlspecialchars($search ?? '') ?>" 
                    placeholder="Ketik nama mahasiswa atau NIM..." 
                    autocomplete="off"
                    style="width: 100%; padding: 9px 14px 9px 36px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-color, rgba(255,255,255,0.03)); color: var(--text-main); font-size: 0.88rem; outline: none; box-sizing: border-box;"
                >
                <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; pointer-events: none;"></i>
            </div>
        </div>

        <!-- Filter Angkatan -->
        <div style="flex: 1; min-width: 160px;">
            <label for="angkatanFilter" style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">
                <i class="fa-solid fa-calendar" style="margin-right: 4px;"></i> Angkatan
            </label>
            <select 
                id="angkatanFilter" 
                name="angkatan" 
                style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-color, var(--surface)); color: var(--text-main); font-size: 0.88rem; outline: none; box-sizing: border-box;"
            >
                <option value="">Semua Angkatan</option>
                <?php foreach (($daftarAngkatan ?? []) as $thn): ?>
                    <option value="<?= htmlspecialchars((string)$thn) ?>" <?= ($filterAngkatan == $thn) ? 'selected' : '' ?>>
                        Angkatan <?= htmlspecialchars((string)$thn) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Kurikulum -->
        <div style="flex: 1.5; min-width: 190px;">
            <label for="kurikulumFilter" style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">
                <i class="fa-solid fa-book-bookmark" style="margin-right: 4px;"></i> Kurikulum
            </label>
            <select 
                id="kurikulumFilter" 
                name="kurikulum_id" 
                style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-color, var(--surface)); color: var(--text-main); font-size: 0.88rem; outline: none; box-sizing: border-box;"
            >
                <option value="">Semua Kurikulum</option>
                <?php foreach (($daftarKurikulum ?? []) as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= ($filterKurikulum == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn" style="background: var(--primary); color: white; padding: 9px 16px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($filterAngkatan) || !empty($filterKurikulum)): ?>
                <a href="<?= $baseUrl ?>/mahasiswa" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 9px 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;" title="Reset Semua Filter">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Bar Status & Info Hasil -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); font-size: 0.82rem; color: var(--text-muted); flex-wrap: wrap; gap: 10px;">
        <div>
            Menampilkan <strong id="visibleCount" style="color: var(--text-main); font-weight: 700;"><?= (int)($filteredCount ?? count($mahasiswa)) ?></strong> dari <strong style="color: var(--text-main);"><?= (int)($totalMahasiswa ?? count($mahasiswa)) ?></strong> total mahasiswa
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span style="font-size: 0.78rem; color: var(--text-muted);">
                <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Pencarian instan aktif saat mengetik
            </span>
        </div>
    </div>
</div>

<!-- Card Tabel Data Mahasiswa -->
<div class="card" style="background: var(--surface); padding: 0; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;">
    <div style="overflow-x: auto;">
        <table id="mahasiswaTable" class="table" style="width: 100%; border-collapse: collapse; margin: 0;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.03); text-align: left;">
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; width: 50px;">No</th>
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Nama Lengkap</th>
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">NIM / Username</th>
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; text-align: center;">Angkatan</th>
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Kurikulum OBE</th>
                    <th style="padding: 14px 16px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody id="mahasiswaTableBody">
                <?php $no = 1; ?>
                <?php foreach ($mahasiswa as $m): ?>
                    <?php 
                        $angkatanVal = (string)($m['angkatan'] ?? '');
                        $kurikulumIdVal = (string)($m['kurikulum_id'] ?? '');
                        $initial = strtoupper(mb_substr(trim($m['nama_lengkap']), 0, 1));
                    ?>
                    <tr 
                        class="mhs-row" 
                        data-nama="<?= htmlspecialchars(mb_strtolower($m['nama_lengkap'])) ?>" 
                        data-nim="<?= htmlspecialchars(mb_strtolower($m['username'])) ?>" 
                        data-angkatan="<?= htmlspecialchars($angkatanVal) ?>" 
                        data-kurikulum="<?= htmlspecialchars($kurikulumIdVal) ?>"
                        style="border-bottom: 1px solid var(--border); transition: background 0.15s ease;"
                    >
                        <td class="row-num" style="padding: 14px 16px; color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                            <?= $no++ ?>
                        </td>
                        <td style="padding: 14px 16px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #818cf8); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                                    <?= htmlspecialchars($initial) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.92rem;">
                                        <?= htmlspecialchars($m['nama_lengkap']) ?>
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 1px;">
                                        Mahasiswa S1 Sistem Informasi
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 16px;">
                            <code style="font-family: monospace; font-size: 0.88rem; background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border); font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars($m['username']) ?>
                            </code>
                        </td>
                        <td style="padding: 14px 16px; text-align: center;">
                            <?php 
                            $statusInfo = AturanKurikulumModel::getStatusStudiByAngkatan($angkatanVal);
                            ?>
                            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span style="background: linear-gradient(135deg, var(--primary), #818cf8); color: white; padding: 2px 10px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-block; box-shadow: 0 2px 6px rgba(99, 102, 241, 0.2);">
                                    <?= htmlspecialchars($angkatanVal) ?>
                                </span>
                                <span style="font-size: 0.68rem; font-weight: 600; color: <?= $statusInfo['badge_color'] ?>; background: <?= $statusInfo['bg_color'] ?>; padding: 1.5px 6px; border-radius: 4px; white-space: nowrap;">
                                    <?= htmlspecialchars($statusInfo['badge']) ?>
                                </span>
                            </div>
                        </td>
                        <td style="padding: 14px 16px;">
                            <?php if (!empty($m['kurikulum_nama']) && $m['kurikulum_nama'] !== 'Belum ditentukan'): ?>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: var(--primary); padding: 4px 10px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; border: 1px solid rgba(99, 102, 241, 0.25);">
                                    <i class="fa-solid fa-book" style="font-size: 0.75rem;"></i> <?= htmlspecialchars($m['kurikulum_nama']) ?>
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.82rem; color: var(--text-muted); font-style: italic;">Belum ditentukan</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 16px; text-align: right; white-space: nowrap;">
                            <a href="<?= $baseUrl ?>/khs?id=<?= $m['id'] ?>" class="btn" style="background: var(--primary); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; margin-right: 4px; display: inline-flex; align-items: center; gap: 5px;" title="Lihat KHS, IPK Kumulatif & Portofolio CPL">
                                <i class="fa-solid fa-graduation-cap"></i> KHS & CPL
                            </a>
                            <a href="<?= $baseUrl ?>/mahasiswa/edit?id=<?= $m['id'] ?>" class="btn" style="background: var(--warning, #f59e0b); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 0.82rem; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px;" title="Edit Mahasiswa">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="<?= $baseUrl ?>/mahasiswa/delete?id=<?= $m['id'] ?>" onclick="return confirm('Yakin ingin menghapus data mahasiswa <?= htmlspecialchars(addslashes($m['nama_lengkap'])) ?>?');" class="btn" style="background: var(--danger, #ef4444); color: white; padding: 6px 10px; border-radius: 6px; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 4px;" title="Hapus Mahasiswa">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Empty State Row (ditampilkan jika tidak ada data) -->
                <tr id="emptyStateRow" style="display: <?= empty($mahasiswa) ? '' : 'none' ?>;">
                    <td colspan="6" style="padding: 48px 20px; text-align: center;">
                        <div style="max-width: 420px; margin: 0 auto; color: var(--text-muted);">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(99, 102, 241, 0.08); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: var(--text-muted);">
                                <i class="fa-solid fa-user-slash" style="font-size: 1.6rem;"></i>
                            </div>
                            <h4 style="margin: 0 0 6px; font-size: 1rem; color: var(--text-main); font-weight: 600;">Mahasiswa Tidak Ditemukan</h4>
                            <p style="margin: 0 0 16px; font-size: 0.85rem; line-height: 1.4;">
                                Tidak ada data mahasiswa yang cocok dengan kata kunci pencarian atau filter yang dipilih.
                            </p>
                            <a href="<?= $baseUrl ?>/mahasiswa" class="btn" style="background: var(--primary); color: white; padding: 7px 16px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-rotate-left"></i> Reset Filter
                            </a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Script Pencarian & Filter Instan di Client-side -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const angkatanFilter = document.getElementById('angkatanFilter');
    const kurikulumFilter = document.getElementById('kurikulumFilter');
    const rows = document.querySelectorAll('.mhs-row');
    const emptyRow = document.getElementById('emptyStateRow');
    const visibleCountEl = document.getElementById('visibleCount');

    function applyLiveFilter() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const selectedAngkatan = (angkatanFilter.value || '').trim();
        const selectedKurikulum = (kurikulumFilter.value || '').trim();

        let visibleCount = 0;

        rows.forEach(function(row) {
            const nama = row.getAttribute('data-nama') || '';
            const nim = row.getAttribute('data-nim') || '';
            const angkatan = row.getAttribute('data-angkatan') || '';
            const kurikulum = row.getAttribute('data-kurikulum') || '';

            const matchQuery = !query || nama.includes(query) || nim.includes(query);
            const matchAngkatan = !selectedAngkatan || angkatan === selectedAngkatan;
            const matchKurikulum = !selectedKurikulum || kurikulum === selectedKurikulum;

            if (matchQuery && matchAngkatan && matchKurikulum) {
                row.style.display = '';
                visibleCount++;
                const numCell = row.querySelector('.row-num');
                if (numCell) {
                    numCell.textContent = visibleCount;
                }
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = visibleCount;
        }

        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
    }

    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyLiveFilter, 120);
    });

    angkatanFilter.addEventListener('change', applyLiveFilter);
    kurikulumFilter.addEventListener('change', applyLiveFilter);
});
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
