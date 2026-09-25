<?php
// views/audit_log/index.php
$title = 'Audit Log Aktivitas - Sistem OBE';
ob_start();
$baseUrl = $config['base_url'] ?? '/obe/public';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Log Audit & Trail Aktivitas</h2>
        <p style="color: var(--text-muted); margin-top: 4px; font-size: 0.9rem;">
            Catatan kronologis perubahan data master, RPS, penilaian, dan aktivitas sistem (PRD FR-18).
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan <?= count($logs) ?> rekaman terbaru
        </span>
    </div>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid var(--border); background: var(--bg-hover);">
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 170px;">WAKTU</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 160px;">PENGGUNA</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 130px;">MODUL</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 100px;">AKSI</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted);">DESKRIPSI AKTIVITAS</th>
                <th style="padding: 14px 18px; font-weight: 600; font-size: 0.85rem; color: var(--text-muted); width: 110px;">IP ADDRESS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-clock-rotate-left" style="font-size: 2rem; opacity: 0.4; margin-bottom: 8px; display: block;"></i>
                        Belum ada rekaman audit log.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php
                        $badgeActionBg = 'rgba(59, 130, 246, 0.1)';
                        $badgeActionColor = 'var(--primary)';
                        if ($log['aksi'] === 'CREATE') {
                            $badgeActionBg = 'rgba(16, 185, 129, 0.12)';
                            $badgeActionColor = 'var(--success)';
                        } elseif ($log['aksi'] === 'UPDATE') {
                            $badgeActionBg = 'rgba(245, 158, 11, 0.12)';
                            $badgeActionColor = '#f59e0b';
                        } elseif ($log['aksi'] === 'DELETE') {
                            $badgeActionBg = 'rgba(239, 68, 68, 0.12)';
                            $badgeActionColor = 'var(--danger)';
                        }
                    ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.15s ease;">
                        <td style="padding: 14px 18px; font-size: 0.82rem; color: var(--text-muted); font-family: monospace;">
                            <?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">
                                <?= htmlspecialchars($log['nama_lengkap'] ?? $log['username'] ?? 'System') ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                @<?= htmlspecialchars($log['username'] ?? 'system') ?>
                            </div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <span style="background: rgba(100, 116, 139, 0.15); border: 1px solid rgba(100, 116, 139, 0.3); color: var(--text-main); padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.4px;">
                                <?= htmlspecialchars($log['modul']) ?>
                            </span>
                        </td>
                        <td style="padding: 14px 18px;">
                            <span style="background: <?= $badgeActionBg ?>; border: 1px solid <?= $badgeActionColor ?>40; color: <?= $badgeActionColor ?>; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                <?= htmlspecialchars($log['aksi']) ?>
                            </span>
                        </td>
                        <td style="padding: 14px 18px; font-size: 0.88rem; color: var(--text-main); line-height: 1.4;">
                            <?= htmlspecialchars($log['deskripsi']) ?>
                        </td>
                        <td style="padding: 14px 18px; font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                            <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
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
