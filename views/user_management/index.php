<?php 
$title = 'Manajemen User';
ob_start();
$base = $GLOBALS['config']['base_url'] ?? '/obe/public'; 
?>
<style>
.um-page{display:flex;flex-direction:column;gap:18px}
.um-head{display:flex;justify-content:space-between;align-items:center;gap:14px}
.um-head h2{margin:0}
.um-head p{margin:5px 0 0;color:var(--text-muted);font-size:.85rem}
.um-card{padding:0;overflow:hidden}
.um-toolbar{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--border);background:var(--surface-hover)}
.um-search{box-sizing:border-box;width:min(100%,460px);height:42px;flex:0 1 460px;padding:9px 42px 9px 40px!important;border:1px solid var(--border)!important;border-radius:10px!important;background-color:var(--card-bg)!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 512 512'%3E%3Cpath fill='%238b8b98' d='M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.1-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0s208 93.1 208 208M208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:14px center!important;color:var(--text-main)!important;font-size:.84rem;transition:border-color .2s,box-shadow .2s}
.um-search::placeholder{color:var(--text-muted);opacity:.8}
.um-search:focus{outline:0!important;border-color:var(--primary)!important;box-shadow:0 0 0 3px rgba(var(--primary-rgb),.13)!important}
.um-search::-webkit-search-cancel-button{cursor:pointer}
.um-table-wrap{overflow:auto}
.um-table{width:100%;border-collapse:collapse;font-size:.82rem}
.um-table th,.um-table td{padding:12px 14px;border-bottom:1px solid var(--border-subtle);text-align:left}
.um-table th{background:var(--surface-hover);color:var(--text-muted);font-size:.7rem;text-transform:uppercase}
.um-role{display:inline-flex;padding:4px 8px;border-radius:999px;background:rgba(var(--primary-rgb),.12);color:var(--primary);font-weight:700}
.um-actions{display:flex;gap:6px}
.um-dialog{width:min(640px,calc(100vw - 24px));padding:0;border:1px solid var(--border);border-radius:14px;background:var(--card-bg);color:var(--text-main);box-shadow:var(--shadow-lg)}
.um-dialog::backdrop{background:rgba(0,0,0,.72);backdrop-filter:blur(3px)}
.um-dialog-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border)}
.um-dialog-head h3{margin:0;font-size:1.02rem}
.um-dialog form{padding:20px}
.um-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.um-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.um-field.full{grid-column:1/-1}
.um-field label{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:.82rem;font-weight:700;color:var(--text-main);margin:0}
.um-label-left{display:inline-flex;align-items:center;gap:4px}
.um-field label span.req{color:#ef4444;font-weight:700;line-height:1}
.um-field .form-control{box-sizing:border-box;width:100%;min-height:42px;padding:9px 13px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--input-bg,var(--surface));color:var(--text-main);font-size:.85rem;transition:border-color .15s ease,box-shadow .15s ease}
.um-field .form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(var(--primary-rgb),.12);outline:0}
.um-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)}
@media(max-width:650px){
    .um-head{align-items:flex-start;flex-direction:column}
    .um-head .btn{width:100%;justify-content:center}
    .um-toolbar{padding:12px}
    .um-search{width:100%;max-width:none;flex-basis:auto}
    .um-grid{grid-template-columns:1fr}
    .um-field.full{grid-column:auto}
    .um-footer{flex-direction:column-reverse}
    .um-footer .btn{width:100%;justify-content:center}
}
</style>

<div class="um-page">
    <header class="um-head">
        <div>
            <h2>Manajemen User</h2>
            <p>Kelola akun login, email, peran, program studi, dan password pengguna.</p>
        </div>
        <button class="btn btn-primary" onclick="openUserForm()">
            <i class="fa-solid fa-user-plus"></i> Tambah User
        </button>
    </header>

    <section class="card um-card">
        <div class="um-toolbar">
            <input class="form-control um-search" id="userSearch" placeholder="Cari username, nama, email, role..." oninput="filterUsers()">
        </div>
        <div class="um-table-wrap">
            <table class="um-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Program Studi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): 
                        $payload = [
                            'id' => (int)$u['id'],
                            'username' => $u['username'],
                            'nama_lengkap' => $u['nama_lengkap'],
                            'email' => $u['email'] ?? '',
                            'role_id' => (int)$u['role_id'],
                            'prodi_id' => $u['prodi_id'] !== null ? (int)$u['prodi_id'] : ''
                        ]; 
                    ?>
                    <tr data-search="<?= htmlspecialchars(strtolower($u['username'].' '.$u['nama_lengkap'].' '.($u['email']??'').' '.$u['role_name'])) ?>">
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                        <td>
                            <?php if (!empty($u['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($u['email']) ?>" style="color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                    <i class="fa-regular fa-envelope" style="font-size:.78rem"></i>
                                    <span><?= htmlspecialchars($u['email']) ?></span>
                                </a>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-style:italic">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="um-role"><?= htmlspecialchars($u['role_name']) ?></span></td>
                        <td><?= htmlspecialchars(trim(($u['jenjang']??'').' '.($u['prodi_nama']??''))?:'—') ?></td>
                        <td>
                            <div class="um-actions">
                                <button class="btn btn-sm btn-secondary" data-user="<?= htmlspecialchars(json_encode($payload),ENT_QUOTES) ?>" onclick="openUserForm(this)" title="Edit User">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <?php if((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                    <form method="post" action="<?= $base ?>/user-management/delete">
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(this,'<?= htmlspecialchars($u['username'],ENT_QUOTES) ?>')" title="Hapus User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<dialog class="um-dialog" id="userDialog">
    <div class="um-dialog-head">
        <h3 id="userDialogTitle">Tambah User</h3>
        <button class="btn btn-sm btn-secondary" onclick="userDialog.close()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <form method="post" action="<?= $base ?>/user-management/save">
        <input type="hidden" name="id" id="umId">
        <div class="um-grid">
            <div class="um-field">
                <label for="umUsername">
                    <span class="um-label-left">Username <span class="req">*</span></span>
                </label>
                <input class="form-control" name="username" id="umUsername" required placeholder="Contoh: admin">
            </div>

            <div class="um-field">
                <label for="umName">
                    <span class="um-label-left">Nama Lengkap <span class="req">*</span></span>
                </label>
                <input class="form-control" name="nama_lengkap" id="umName" required placeholder="Contoh: Administrator">
            </div>

            <div class="um-field full">
                <label for="umEmail">
                    <span class="um-label-left">Alamat Email</span>
                    <small style="color:var(--text-muted);font-weight:400;font-size:.74rem">Opsional / default @obe.ac.id</small>
                </label>
                <input class="form-control" type="email" name="email" id="umEmail" placeholder="Contoh: user@kampus.ac.id">
            </div>

            <div class="um-field">
                <label for="umRole">
                    <span class="um-label-left">Role Pengguna <span class="req">*</span></span>
                </label>
                <select class="form-control" name="role_id" id="umRole" required>
                    <?php foreach($roles as $r): ?>
                        <option value="<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="um-field">
                <label for="umProdi">
                    <span class="um-label-left">Program Studi</span>
                </label>
                <select class="form-control" name="prodi_id" id="umProdi">
                    <option value="">Tidak terikat prodi</option>
                    <?php foreach($prodi as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['jenjang'].' '.$p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="um-field full">
                <label for="umPassword">
                    <span class="um-label-left">Password</span>
                    <small id="passwordHint" style="color:var(--text-muted);font-weight:400;font-size:.74rem">Minimal 6 karakter</small>
                </label>
                <input class="form-control" type="password" name="password" id="umPassword" placeholder="••••••••">
            </div>
        </div>
        <div class="um-footer">
            <button type="button" class="btn btn-secondary" onclick="userDialog.close()">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
            <button class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
        </div>
    </form>
</dialog>

<script>
const userDialog = document.getElementById('userDialog');
const userFields = {
    id: document.getElementById('umId'),
    username: document.getElementById('umUsername'),
    name: document.getElementById('umName'),
    email: document.getElementById('umEmail'),
    role: document.getElementById('umRole'),
    prodi: document.getElementById('umProdi'),
    password: document.getElementById('umPassword'),
    hint: document.getElementById('passwordHint'),
    title: document.getElementById('userDialogTitle'),
    search: document.getElementById('userSearch')
};

function openUserForm(btn = null) {
    const d = btn ? JSON.parse(btn.dataset.user) : null;
    userFields.id.value = d?.id || '';
    userFields.username.value = d?.username || '';
    userFields.name.value = d?.nama_lengkap || '';
    userFields.email.value = d?.email || '';
    userFields.role.value = d?.role_id || '1';
    userFields.prodi.value = d?.prodi_id || '';
    userFields.password.value = '';
    userFields.password.required = !d;
    userFields.hint.textContent = d ? 'Kosongkan jika password tidak diubah' : 'Minimal 6 karakter';
    userFields.title.textContent = d ? 'Edit User' : 'Tambah User';
    userDialog.showModal();
}

function filterUsers() {
    const q = userFields.search.value.toLowerCase();
    document.querySelectorAll('.um-table tbody tr').forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? '' : 'none';
    });
}

function deleteUser(btn, name) {
    const dark = document.documentElement.getAttribute('data-theme') !== 'light';
    Swal.fire({
        title: 'Hapus akun?',
        text: `Akun ${name} akan dihapus.`,
        icon: 'warning',
        background: dark ? '#111114' : '#fff',
        color: dark ? '#f4f4f5' : '#18181b',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (r.isConfirmed) btn.closest('form').submit();
    });
}
</script>
<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php'; 
?>
