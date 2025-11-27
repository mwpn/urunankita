<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Form Penggalang') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $tenant ? 'Edit Penggalang' : 'Tambah Penggalang Dana Baru' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/tenants') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                    </a>
                </div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="<?= $tenant ? base_url("admin/tenants/{$tenant['id']}/update") : base_url('admin/tenants/store') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                
                <!-- Informasi Dasar -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Dasar</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Nama Penggalang <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required 
                                    value="<?= esc($tenant['name'] ?? old('name')) ?>"
                                    class="form-control"
                                    placeholder="Contoh: Yayasan Peduli Anak"
                                >
                                <div class="invalid-feedback">Nama penggalang wajib diisi</div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="slug">Subdomain <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    id="slug" 
                                    name="slug" 
                                    required
                                    value="<?= esc($tenant['slug'] ?? old('slug')) ?>" 
                                    class="form-control"
                                    placeholder="contoh-penggalang"
                                    pattern="[a-z0-9-]+"
                                    title="Hanya huruf kecil, angka, dan tanda strip"
                                >
                                <small class="form-text text-muted">URL lengkap: <span class="font-mono"><?= esc($tenant['slug'] ?? 'subdomain') ?>.<?= env('app.baseDomain', 'urunankita.id') ?></span></small>
                                <div class="invalid-feedback">Subdomain wajib diisi</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="domain">Domain Kustom (Opsional)</label>
                                <input 
                                    type="text" 
                                    id="domain" 
                                    name="domain" 
                                    value="<?= esc($tenant['domain'] ?? old('domain')) ?>"
                                    class="form-control"
                                    placeholder="contoh.com"
                                >
                            </div>

                            <div class="form-group col-md-6">
                                <label for="youtube_url">URL Channel YouTube (Opsional)</label>
                                <input 
                                    type="url" 
                                    id="youtube_url" 
                                    name="youtube_url" 
                                    value="<?= esc($tenant['youtube_url'] ?? old('youtube_url')) ?>"
                                    class="form-control"
                                    placeholder="https://www.youtube.com/@channelname"
                                >
                                <small class="form-text text-muted">URL channel YouTube konten kreator</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <select 
                                    id="status" 
                                    name="status"
                                    class="form-control"
                                >
                                    <option value="active" <?= ($tenant['status'] ?? old('status')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="inactive" <?= ($tenant['status'] ?? old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                    <option value="suspended" <?= ($tenant['status'] ?? old('status')) === 'suspended' ? 'selected' : '' ?>>Ditangguhkan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Khusus -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Pengaturan Khusus</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input" 
                                    id="can_create_without_verification" 
                                    name="can_create_without_verification" 
                                    value="1"
                                    <?= (isset($tenant['can_create_without_verification']) && ($tenant['can_create_without_verification'] == 1 || $tenant['can_create_without_verification'] === '1')) ? 'checked' : '' ?>
                                >
                                <label class="custom-control-label" for="can_create_without_verification">
                                    Bisa membuat urunan tanpa verifikasi
                                </label>
                                <small class="form-text text-muted d-block">Jika aktif, penggalang ini bisa membuat urunan yang langsung aktif tanpa menunggu verifikasi dari admin.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input" 
                                    id="can_use_own_bank_account" 
                                    name="can_use_own_bank_account" 
                                    value="1"
                                    <?= (isset($tenant['can_use_own_bank_account']) && ($tenant['can_use_own_bank_account'] == 1 || $tenant['can_use_own_bank_account'] === '1')) ? 'checked' : '' ?>
                                >
                                <label class="custom-control-label" for="can_use_own_bank_account">
                                    Bisa menggunakan rekening sendiri
                                </label>
                                <small class="form-text text-muted d-block">Jika aktif, penggalang ini bisa menggunakan rekening bank sendiri untuk menerima donasi (selain rekening platform).</small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$tenant): ?>
                <!-- Akun Owner (wajib saat create) -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Akun Owner</strong>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-3">Wajib diisi saat membuat penggalang pertama kali.</small>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="owner_name">Nama Owner</label>
                                <input 
                                    type="text" 
                                    id="owner_name" 
                                    name="owner_name" 
                                    value="<?= esc(old('owner_name')) ?>"
                                    class="form-control"
                                    placeholder="Nama lengkap owner"
                                >
                            </div>

                            <div class="form-group col-md-6">
                                <label for="owner_email">Email Owner <span class="text-danger">*</span></label>
                                <input 
                                    type="email" 
                                    id="owner_email" 
                                    name="owner_email" 
                                    value="<?= esc(old('owner_email')) ?>"
                                    required
                                    class="form-control"
                                    placeholder="owner@penggalang.test"
                                >
                                <div class="invalid-feedback">Email owner wajib diisi</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="owner_password">Password Owner</label>
                                <input 
                                    type="text" 
                                    id="owner_password" 
                                    name="owner_password" 
                                    value="<?= esc(old('owner_password') ?: 'admin123') ?>"
                                    class="form-control"
                                    placeholder="admin123"
                                >
                                <small class="form-text text-muted">Jika kosong, default <span class="font-mono">admin123</span>.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Kelola Akun Owner (Edit) -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Kelola Akun Owner</strong>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-3">Opsional: perbarui email/password akun owner untuk penggalang ini.</small>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="owner_name">Nama Owner</label>
                                <input 
                                    type="text" 
                                    id="owner_name" 
                                    name="owner_name" 
                                    value="<?= esc(old('owner_name') ?? ($owner_user['name'] ?? '')) ?>"
                                    class="form-control"
                                    placeholder="Nama lengkap owner"
                                >
                            </div>

                            <div class="form-group col-md-6">
                                <label for="owner_email">Email Owner</label>
                                <input 
                                    type="email" 
                                    id="owner_email" 
                                    name="owner_email" 
                                    value="<?= esc(old('owner_email') ?? ($owner_user['email'] ?? '')) ?>"
                                    class="form-control"
                                    placeholder="owner@penggalang.test"
                                >
                                <small class="form-text text-muted">Jika kosong, email owner tidak diubah.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="owner_password">Password Owner (baru)</label>
                                <input 
                                    type="text" 
                                    id="owner_password" 
                                    name="owner_password" 
                                    value="<?= esc(old('owner_password')) ?>"
                                    class="form-control"
                                    placeholder="(kosongkan jika tidak mengubah)"
                                >
                                <small class="form-text text-muted">Kosongkan jika tidak mengubah password.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($tenant): ?>
                <!-- Kelola Staff Users -->
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="card-title">Kelola Staff</strong>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAddStaff">
                            <span class="fe fe-plus fe-12"></span> Tambah Staff
                        </button>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-3">Staff dapat mengelola donasi masuk dan laporan untuk penggalang ini.</small>
                        
                        <?php if (empty($staff_users)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">Belum ada staff. Klik "Tambah Staff" untuk menambahkan.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staff_users as $staff): ?>
                                            <tr>
                                                <td><?= esc($staff['name'] ?? '-') ?></td>
                                                <td><?= esc($staff['email'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge badge-info"><?= esc($staff['role'] ?? 'staff') ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= ($staff['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= esc(ucfirst($staff['status'] ?? 'active')) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= $staff['created_at'] ? date('d M Y', strtotime($staff['created_at'])) : '-' ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteStaff(<?= (int) $staff['id'] ?>, '<?= esc($staff['name'] ?? '', 'js') ?>')"
                                                    >
                                                        <span class="fe fe-trash-2 fe-12"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/tenants') ?>" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= $tenant ? 'Perbarui Penggalang' : 'Buat Penggalang' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?php if ($tenant): ?>
<!-- Modal Add Staff -->
<div class="modal fade" id="modalAddStaff" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Staff</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAddStaff" method="POST" action="<?= base_url("admin/tenants/{$tenant['id']}/staff/create") ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="staff_name">Nama Staff <span class="text-danger">*</span></label>
                        <input type="text" id="staff_name" name="name" class="form-control" required placeholder="Nama lengkap staff">
                    </div>
                    <div class="form-group">
                        <label for="staff_email">Email <span class="text-danger">*</span></label>
                        <input type="email" id="staff_email" name="email" class="form-control" required placeholder="staff@example.com">
                    </div>
                    <div class="form-group">
                        <label for="staff_password">Password</label>
                        <input type="text" id="staff_password" name="password" class="form-control" value="admin123" placeholder="admin123">
                        <small class="form-text text-muted">Default: admin123</small>
                    </div>
                    <div class="form-group">
                        <label for="staff_role">Role</label>
                        <select id="staff_role" name="role" class="form-control">
                            <option value="staff">Staff</option>
                            <option value="tenant_staff">Tenant Staff</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($tenant): ?>
<script>
function deleteStaff(userId, userName) {
    if (!confirm('Hapus staff "' + userName + '"?\n\nStaff ini tidak akan bisa login lagi.')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("admin/tenants/{$tenant['id']}/staff/") ?>' + userId + '/delete';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '<?= csrf_token() ?>';
    csrfInput.value = '<?= csrf_hash() ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Handle form add staff
document.getElementById('formAddStaff')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const formDataObj = {};
    formData.forEach((value, key) => {
        formDataObj[key] = value;
    });
    
    // Add CSRF
    formDataObj['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formDataObj).toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Gagal menambahkan staff'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>

