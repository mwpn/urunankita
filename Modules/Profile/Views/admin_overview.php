<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Profil Saya</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalEditProfil">
                        <span class="fe fe-edit fe-12 mr-1"></span>Edit Profil
                    </button>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Sidebar Profil -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img id="mainAvatar" src="<?= esc($avatar) ?>?t=<?= time() ?>" alt="Avatar" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e9ecef;">
                            </div>
                            <h5 class="mb-1"><?= esc($user['name'] ?? 'Admin') ?></h5>
                            <p class="text-muted small mb-3"><?= esc($user['email'] ?? '') ?></p>
                            <div class="mb-3">
                                <span class="badge badge-success">Admin</span>
                                <span class="badge badge-primary">Aktif</span>
                            </div>
                            <div class="d-flex justify-content-center mb-3">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="modal" data-target="#modalUbahFoto">
                                    <span class="fe fe-camera fe-12 mr-1"></span>Ubah Foto
                                </button>
                            </div>
                            <hr>
                            <div class="text-left">
                                <small class="text-muted d-block mb-2">Terdaftar Sejak</small>
                                <p class="mb-3"><strong>
                                    <?php
                                    if (!empty($user['created_at']) && $user['created_at'] !== '0000-00-00 00:00:00' && $user['created_at'] !== '0000-00-00') {
                                        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        $timestamp = strtotime($user['created_at']);
                                        if ($timestamp !== false && $timestamp > 0) {
                                            $day = date('d', $timestamp);
                                            $month = $months[(int)date('n', $timestamp) - 1];
                                            $year = date('Y', $timestamp);
                                            echo $day . ' ' . $month . ' ' . $year;
                                        } else {
                                            echo '-';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </strong></p>
                                <small class="text-muted d-block mb-2">Terakhir Login</small>
                                <p class="mb-0"><strong>
                                    <?php
                                    if (!empty($user['last_login']) && $user['last_login'] !== '0000-00-00 00:00:00' && $user['last_login'] !== '0000-00-00') {
                                        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        $timestamp = strtotime($user['last_login']);
                                        if ($timestamp !== false && $timestamp > 0) {
                                            $day = date('d', $timestamp);
                                            $month = $months[(int)date('n', $timestamp) - 1];
                                            $year = date('Y', $timestamp);
                                            $time = date('H:i', $timestamp);
                                            echo $day . ' ' . $month . ' ' . $year . ', ' . $time . ' WIB';
                                        } else {
                                            echo 'Belum pernah login';
                                        }
                                    } else {
                                        echo 'Belum pernah login';
                                    }
                                    ?>
                                </strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <?php if ($isAdmin && isset($stats)): ?>
                        <div class="card shadow mt-3">
                            <div class="card-header">
                                <strong class="card-title">Statistik Platform</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Total Tenant</small>
                                        <strong><?= number_format($stats['total_tenants']) ?></strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $stats['total_tenants'] > 0 ? 100 : 0 ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Total Campaign</small>
                                        <strong><?= number_format($stats['total_campaigns']) ?></strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $stats['total_campaigns'] > 0 ? 100 : 0 ?>%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Total Donasi</small>
                                        <strong>Rp <?= number_format($stats['total_donations'], 0, ',', '.') ?></strong>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats['total_donations'] > 0 ? min(100, ($stats['total_donations'] / 1000000000) * 100) : 0 ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Informasi Pribadi -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <strong class="card-title">Informasi Pribadi</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                    <p class="mb-0"><strong><?= esc($user['name'] ?? '-') ?></strong></p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Email</small>
                                    <p class="mb-0"><strong><?= esc($user['email'] ?? '-') ?></strong></p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-1">Nomor Telepon</small>
                                    <p class="mb-0"><strong><?= esc($user['phone'] ?? '-') ?></strong></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Role</small>
                                    <p class="mb-0">
                                        <span class="badge badge-success">Admin</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Status Akun</small>
                                    <p class="mb-0"><span class="badge badge-primary"><?= esc($user['status'] ?? 'Aktif') ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aktivitas Terkini -->
                    <?php if (!empty($activities)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Aktivitas Terkini</strong>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($activities, 0, 10) as $activity): ?>
                                        <?php
                                        $actionType = $activity['action_type'] ?? 'default';
                                        $iconClass = 'check-circle';
                                        $textColor = 'info';
                                        
                                        if ($actionType === 'create') {
                                            $iconClass = 'plus-circle';
                                            $textColor = 'success';
                                        } elseif ($actionType === 'update' || $actionType === 'edit') {
                                            $iconClass = 'edit';
                                            $textColor = 'primary';
                                        }
                                        ?>
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="avatar avatar-sm">
                                                        <span class="fe fe-<?= $iconClass ?> fe-16 text-<?= $textColor ?>"></span>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted"><?= esc($activity['description'] ?? $activity['action'] ?? $activity['title'] ?? 'Aktivitas') ?></small>
                                                    <?php if (!empty($activity['entity_type'])): ?>
                                                        <p class="mb-0"><strong><?= esc($activity['entity_type']) ?> #<?= esc($activity['entity_id'] ?? '') ?></strong></p>
                                                    <?php elseif (!empty($activity['campaign_title'])): ?>
                                                        <p class="mb-0"><strong><?= esc($activity['campaign_title']) ?></strong></p>
                                                    <?php elseif (!empty($activity['message'])): ?>
                                                        <p class="mb-0"><strong><?= esc($activity['message']) ?></strong></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-auto">
                                                    <small class="text-muted"><?= !empty($activity['created_at']) ? date('d M Y, H:i', strtotime($activity['created_at'])) . ' WIB' : '-' ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<!-- Modal Edit Profil -->
<div class="modal fade" id="modalEditProfil" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profil</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditProfil">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= esc($user['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="<?= esc($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone" value="<?= esc($user['phone'] ?? '') ?>" placeholder="081234567890" required>
                        <small class="form-text text-muted">Digunakan untuk mengirim notifikasi</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitEditProfile()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ubah Foto -->
<div class="modal fade" id="modalUbahFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Foto Profil</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formUbahFoto">
                    <div class="form-group text-center">
                        <img id="avatarPreview" src="<?= esc($avatar) ?>" alt="Current Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;">
                        <div class="form-group">
                            <label>Pilih Foto</label>
                            <input type="file" class="form-control-file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewAvatar(this)">
                            <small class="form-text text-muted">Max: 2MB, Format: JPG/PNG/WEBP</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAvatar()">Simpan Foto</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Preview avatar before upload
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitEditProfile() {
        const form = document.getElementById('formEditProfil');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        // Show loading state
        const submitBtn = form.closest('.modal').querySelector('button[onclick="submitEditProfile()"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...';

        fetch('<?= base_url('admin/profile/update') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server mengembalikan response yang tidak valid');
            }
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'HTTP ' + response.status }));
                throw new Error(errorData.message || 'Network response was not ok');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                $('#modalEditProfil').modal('hide');
                // Redirect to show flash message
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    location.reload();
                }
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                const errorMsg = data.message || 'Gagal memperbarui profil';
                console.error('Update profile error:', data);
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                } else {
                    console.error('Error:', errorMsg);
                }
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            console.error('Error detail:', error);
            const errorMsg = error.message || 'Terjadi kesalahan saat memperbarui profil';
            if (typeof showToast === 'function') {
                showToast('error', errorMsg);
            } else {
                console.error('Error:', errorMsg);
            }
        });
    }

    function submitAvatar() {
        const form = document.getElementById('formUbahFoto');
        const fileInput = form.querySelector('input[type="file"]');
        
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            const errorMsg = 'Silakan pilih foto terlebih dahulu';
            console.warn(errorMsg);
            if (typeof showToast === 'function') {
                showToast('warning', errorMsg);
            } else {
                alert(errorMsg);
            }
            return;
        }

        // Validate file size (max 2MB)
        const file = fileInput.files[0];
        if (file.size > 2097152) {
            const errorMsg = 'Ukuran file maksimal 2MB';
            console.warn(errorMsg);
            if (typeof showToast === 'function') {
                showToast('error', errorMsg);
            } else {
                alert(errorMsg);
            }
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type) && !['jpg', 'jpeg', 'png', 'webp'].includes(file.name.split('.').pop().toLowerCase())) {
            const errorMsg = 'Format file harus JPG, PNG, atau WEBP';
            console.warn(errorMsg);
            if (typeof showToast === 'function') {
                showToast('error', errorMsg);
            } else {
                alert(errorMsg);
            }
            return;
        }

        const formData = new FormData(form);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        // Show loading state
        const submitBtn = form.closest('.modal').querySelector('button[onclick="submitAvatar()"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Mengupload...';

        fetch('<?= base_url('admin/profile/avatar') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server mengembalikan response yang tidak valid');
            }
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'HTTP ' + response.status }));
                throw new Error(errorData.message || 'Network response was not ok');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                $('#modalUbahFoto').modal('hide');
                // Update avatar image immediately if URL is provided
                if (data.avatar_url) {
                    const timestamp = '?t=' + new Date().getTime();
                    const avatarUrl = data.avatar_url + timestamp;
                    
                    // Update main avatar
                    const mainAvatar = document.getElementById('mainAvatar');
                    if (mainAvatar) {
                        mainAvatar.src = avatarUrl;
                    }
                    // Update preview avatar in modal
                    const previewAvatar = document.getElementById('avatarPreview');
                    if (previewAvatar) {
                        previewAvatar.src = avatarUrl;
                    }
                    // Update header avatar
                    const headerAvatar = document.getElementById('headerAvatar');
                    if (headerAvatar) {
                        headerAvatar.src = avatarUrl;
                    }
                    // Also update any other avatar images
                    document.querySelectorAll('img[alt="Avatar"], img[alt="Current Avatar"]').forEach(img => {
                        if (img.id !== 'mainAvatar' && img.id !== 'avatarPreview' && img.id !== 'headerAvatar') {
                            img.src = avatarUrl;
                        }
                    });
                }
                // Redirect to show flash message
                if (data.redirect) {
                    // Small delay to show updated image before redirect
                    setTimeout(() => {
                        window.location.href = data.redirect + '?updated=' + new Date().getTime();
                    }, 500);
                } else {
                    location.reload();
                }
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                const errorMsg = data.message || 'Gagal mengubah foto';
                console.error('Update avatar error:', data);
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            console.error('Error detail:', error);
            const errorMsg = error.message || 'Terjadi kesalahan saat mengubah foto';
            if (typeof showToast === 'function') {
                showToast('error', errorMsg);
            } else {
                alert(errorMsg);
            }
        });
    }
</script>
<?= $this->endSection() ?>

