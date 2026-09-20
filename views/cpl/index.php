<?php
$title = 'Data CPL - Sistem OBE';
ob_start();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2>Master Data CPL</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Kelola data Capaian Pembelajaran Lulusan (CPL).</p>
    </div>
    <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah CPL
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

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                <th style="padding: 15px 20px; font-weight: 600;">Kode</th>
                <th style="padding: 15px 20px; font-weight: 600;">Kurikulum</th>
                <th style="padding: 15px 20px; font-weight: 600;">Rumusan</th>
                <th style="padding: 15px 20px; font-weight: 600;">Target</th>
                <th style="padding: 15px 20px; font-weight: 600;">Status</th>
                <th style="padding: 15px 20px; font-weight: 600;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cplList)): ?>
            <tr>
                <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">Belum ada data CPL.</td>
            </tr>
            <?php else: ?>
                <?php foreach($cplList as $c): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($c['kode']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($c['kurikulum_nama'] ?? '') ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars(strlen($c['rumusan']) > 50 ? substr($c['rumusan'], 0, 50) . '...' : $c['rumusan']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($c['target_capaian']) ?>%</td>
                    <td style="padding: 15px 20px;">
                        <span style="background: <?= $c['status'] == 'ACTIVE' ? 'var(--success)' : 'var(--border)' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($c['status']) ?></span>
                    </td>
                    <td style="padding: 15px 20px;">
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px;"><i class="fa-solid fa-edit"></i> Edit</a>
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/cpl/delete?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" style="padding: 4px 8px;" onclick="return confirm('Yakin ingin menghapus CPL ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
