<?php
$title = 'Data Kurikulum - Sistem OBE';
ob_start();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2>Master Data Kurikulum</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Kelola data kurikulum program studi.</p>
    </div>
    <a href="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum/create" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Kurikulum
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
                <th style="padding: 15px 20px; font-weight: 600;">Prodi</th>
                <th style="padding: 15px 20px; font-weight: 600;">Kode</th>
                <th style="padding: 15px 20px; font-weight: 600;">Nama Kurikulum</th>
                <th style="padding: 15px 20px; font-weight: 600;">Tahun</th>
                <th style="padding: 15px 20px; font-weight: 600;">Versi</th>
                <th style="padding: 15px 20px; font-weight: 600;">Status</th>
                <th style="padding: 15px 20px; font-weight: 600;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kurikulumList)): ?>
            <tr>
                <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">Belum ada data kurikulum.</td>
            </tr>
            <?php else: ?>
                <?php foreach($kurikulumList as $k): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($k['nama_prodi'] ?? '') ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($k['kode']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($k['nama']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($k['tahun_mulai']) ?></td>
                    <td style="padding: 15px 20px;"><?= htmlspecialchars($k['versi']) ?></td>
                    <td style="padding: 15px 20px;">
                        <span style="background: <?= $k['status'] == 'ACTIVE' ? 'var(--success)' : ($k['status'] == 'DRAFT' ? 'var(--warning)' : 'var(--border)') ?>; color: <?= $k['status'] == 'DRAFT' ? '#000' : '#fff' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($k['status']) ?></span>
                    </td>
                    <td style="padding: 15px 20px;">
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum/edit?id=<?= $k['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 4px 8px;"><i class="fa-solid fa-edit"></i> Edit</a>
                        <a href="<?= $config['base_url'] ?? '/obe/public' ?>/kurikulum/delete?id=<?= $k['id'] ?>" class="btn btn-sm btn-danger" style="padding: 4px 8px;" onclick="return confirm('Yakin ingin menghapus kurikulum ini?')"><i class="fa-solid fa-trash"></i></a>
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
