<div class="topbar">
    <div class="topbar-left">
        <!-- Breadcrumbs could go here -->
    </div>
    <div class="topbar-right" style="display: flex; align-items: center;">
        <span style="margin-right: 15px; cursor: pointer;"><i class="fa-regular fa-bell"></i></span>
        <div style="position: relative; display: inline-block;">
            <span style="cursor: pointer;"><i class="fa-regular fa-user-circle"></i> <?= $_SESSION['nama_lengkap'] ?? 'Admin' ?></span>
            <a href="<?= $config['base_url'] ?? '/obe/public' ?>/logout" style="margin-left: 15px; color: var(--danger); font-size: 0.9rem;"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>
