<?php
$title = 'Data Mata Kuliah - Sistem OBE';
ob_start();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2>Master Data Mata Kuliah</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Kelola data Mata Kuliah program studi.</p>
    </div>
    <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Mata Kuliah
    </a>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah" style="display: flex; gap: 15px; align-items: flex-end;">
        <div style="flex: 1;">
            <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">Cari Nama / Kode MK</label>
            <input type="text" id="search" name="search" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Masukkan kata kunci...">
        </div>
        <div style="width: 200px;">
            <label for="semester" style="display: block; margin-bottom: 5px; font-weight: 500;">Semester</label>
            <select id="semester" name="semester" style="width: 100%; padding: 10px; background: var(--background); border: 1px solid var(--border); color: white; border-radius: var(--radius-sm);">
                <option value="">Semua Semester</option>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?= $i ?>" <?= (isset($_GET['semester']) && $_GET['semester'] == $i) ? 'selected' : '' ?>>Semester <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 15px;"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah" class="btn btn-secondary" style="padding: 10px 15px;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
    </form>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                <th style="padding: 15px 20px; font-weight: 600;">Smt</th>
                <th style="padding: 15px 20px; font-weight: 600;">Kode</th>
                <th style="padding: 15px 20px; font-weight: 600;">Mata Kuliah</th>
                <th style="padding: 15px 20px; font-weight: 600;">SKS (T/P)</th>
                <th style="padding: 15px 20px; font-weight: 600;">Sifat</th>
                <th style="padding: 15px 20px; font-weight: 600;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mkList)): ?>
            <tr>
                <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">Belum ada data mata kuliah.</td>
            </tr>
            <?php else: ?>
                <?php foreach($mkList as $m): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($m['semester']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($m['kode']) ?></td>
                    <td style="padding: 15px 20px;">
                        <div style="font-weight: 500;"><?= htmlspecialchars($m['nama']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars($m['kurikulum_nama'] ?? '') ?></div>
                    </td>
                    <td style="padding: 15px 20px;">
                        <?= htmlspecialchars($m['sks_teori'] + $m['sks_praktek']) ?> (<?= htmlspecialchars($m['sks_teori']) ?>/<?= htmlspecialchars($m['sks_praktek']) ?>)
                    </td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($m['sifat']) ?></td>
                    <td style="padding: 15px 20px;">
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/rps?mk_id=<?= $m['id'] ?>" class="btn btn-sm btn-primary" style="padding: 4px 8px;"><i class="fa-solid fa-book"></i> RPS</a>
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah/edit?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px;"><i class="fa-solid fa-edit"></i> Edit</a>
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/mata-kuliah/delete?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" style="padding: 4px 8px;" onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    <div style="color: var(--text-muted); font-size: 0.9rem;">
        Menampilkan halaman <?= $page ?> dari <?= $totalPages ?> (Total: <?= $totalData ?> data)
    </div>
    <div style="display: flex; gap: 5px;">
        <?php 
            $queryParams = $_GET; 
            unset($queryParams['url']); // Remove rewrite param if present
        ?>
        
        <?php if ($page > 1): ?>
            <?php $queryParams['page'] = $page - 1; ?>
            <a href="?<?= http_build_query($queryParams) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9rem;"><i class="fa-solid fa-chevron-left"></i> Prev</a>
        <?php endif; ?>

        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php $queryParams['page'] = $i; ?>
            <?php if ($i == $page): ?>
                <span class="btn btn-primary" style="padding: 6px 12px; font-size: 0.9rem; cursor: default; opacity: 0.8;"><?= $i ?></span>
            <?php else: ?>
                <a href="?<?= http_build_query($queryParams) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9rem;"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <?php $queryParams['page'] = $page + 1; ?>
            <a href="?<?= http_build_query($queryParams) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.9rem;">Next <i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
