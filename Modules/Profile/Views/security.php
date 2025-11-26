<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Pengaturan Keamanan</h2>
                </div>
            </div>

            <div class="row">
                <!-- Ubah Password -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Ubah Password</strong>
                        </div>
                        <div class="card-body">
                            <form id="formUbahPassword" class="needs-validation" novalidate>
                                <div class="form-group">
                                    <label>Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="current_password" id="currentPassword" required>
                                    <div class="invalid-feedback">Password saat ini wajib diisi</div>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="passwordBaru" name="new_password" required minlength="8">
                                    <div class="invalid-feedback">Password baru minimal 8 karakter</div>
                                    <small class="form-text text-muted">Minimal 8 karakter, kombinasi huruf dan angka</small>
                                </div>
                                <div class="form-group">
                                    <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="konfirmasiPasswordBaru" name="confirm_password" required>
                                    <div class="invalid-feedback">Konfirmasi password harus sama dengan password baru</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <span class="fe fe-save fe-12 mr-1"></span>Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Keamanan -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Pengaturan Keamanan</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="twoFactorAuth" <?= ($user['two_factor_enabled'] ?? false) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="twoFactorAuth">
                                        <strong>Two Factor Authentication (2FA)</strong>
                                        <small class="d-block text-muted">Tingkatkan keamanan akun dengan 2FA</small>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emailNotifikasi" <?= ($user['email_notifications'] ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="emailNotifikasi">
                                        <strong>Email Notifikasi</strong>
                                        <small class="d-block text-muted">Terima notifikasi via email</small>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="loginNotifikasi" <?= ($user['login_notifications'] ?? true) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="loginNotifikasi">
                                        <strong>Notifikasi Login</strong>
                                        <small class="d-block text-muted">Dapatkan notifikasi saat ada login baru</small>
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveSecuritySettings()">
                                <span class="fe fe-refresh-cw fe-12 mr-1"></span>Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sesi Aktif -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Sesi Aktif</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Perangkat</th>
                                            <th>Browser</th>
                                            <th>IP Address</th>
                                            <th>Lokasi</th>
                                            <th>Waktu Login</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sessions)): ?>
                                            <?php foreach ($sessions as $session): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fe fe-<?= strpos(strtolower($session['device'] ?? ''), 'phone') !== false || strpos(strtolower($session['device'] ?? ''), 'mobile') !== false ? 'smartphone' : 'monitor' ?> fe-16 mr-2"></span>
                                                        <strong><?= esc($session['device'] ?? 'Unknown') ?></strong>
                                                    </td>
                                                    <td><?= esc($session['browser'] ?? 'Unknown') ?></td>
                                                    <td><?= esc($session['ip'] ?? 'Unknown') ?></td>
                                                    <td><?= esc($session['location'] ?? 'Unknown') ?></td>
                                                    <td><?= esc($session['last_login'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= ($session['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                                            <?= ($session['status'] ?? '') === 'active' ? 'Aktif' : 'Tidak Aktif' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (($session['status'] ?? '') === 'active'): ?>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="openLogoutSessionModal('<?= esc($session['ip'] ?? '', 'js') ?>', '<?= esc($session['user_agent'] ?? 'Unknown', 'js') ?>')">
                                                                <span class="fe fe-log-out fe-12"></span> Logout
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                                <span class="fe fe-log-out fe-12"></span> Logout
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    Tidak ada sesi aktif
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<!-- Logout Session Modal -->
<div id="logoutSessionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Logout dari Sesi</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeLogoutSessionModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan logout dari sesi berikut:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>IP Address:</span>
                        <span id="ls-ip" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>User Agent:</span>
                        <span id="ls-user-agent" class="text-muted">-</span>
                    </div>
                </div>
                <p class="text-warning"><small><strong>Peringatan:</strong> Anda akan logout dari sesi ini. Pastikan Anda memiliki akses ke sesi lain jika ini adalah sesi aktif Anda.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeLogoutSessionModal()">Batal</button>
                <button type="button" id="ls-submit" class="btn btn-danger">Ya, Logout</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Form Validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var form = document.getElementById('formUbahPassword');
            var passwordBaru = document.getElementById('passwordBaru');
            var konfirmasiPasswordBaru = document.getElementById('konfirmasiPasswordBaru');
            
            function validatePasswordMatch() {
                if (konfirmasiPasswordBaru.value !== passwordBaru.value) {
                    konfirmasiPasswordBaru.setCustomValidity('Password tidak cocok');
                } else {
                    konfirmasiPasswordBaru.setCustomValidity('');
                }
            }
            
            passwordBaru.addEventListener('input', validatePasswordMatch);
            konfirmasiPasswordBaru.addEventListener('input', validatePasswordMatch);
            
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                validatePasswordMatch();
                if (konfirmasiPasswordBaru.value !== passwordBaru.value) {
                    event.stopPropagation();
                    konfirmasiPasswordBaru.focus();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Submit password change
                const formData = new FormData(form);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                
                fetch('/profile/change-password', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        form.reset();
                        form.classList.remove('was-validated');
                        console.log('Password berhasil diubah');
                    } else {
                        console.error('Error:', data.message || 'Gagal mengubah password');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }, false);
        }, false);
    })();

    function saveSecuritySettings() {
        const settings = {
            two_factor_auth: document.getElementById('twoFactorAuth').checked,
            email_notifications: document.getElementById('emailNotifikasi').checked,
            login_notifications: document.getElementById('loginNotifikasi').checked,
        };
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('two_factor_auth', settings.two_factor_auth ? '1' : '0');
        formData.append('email_notifications', settings.email_notifications ? '1' : '0');
        formData.append('login_notifications', settings.login_notifications ? '1' : '0');
        
        fetch('/profile/security-settings', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Pengaturan keamanan berhasil disimpan');
            } else {
                console.error('Error:', data.message || 'Gagal menyimpan pengaturan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Logout Session Modal Functions
    function openLogoutSessionModal(ip, userAgent) {
        const modal = $('#logoutSessionModal');
        window.currentLogoutSessionIp = ip;
        document.getElementById('ls-ip').textContent = ip || '-';
        document.getElementById('ls-user-agent').textContent = userAgent || '-';
        modal.modal('show');
    }

    function closeLogoutSessionModal() {
        $('#logoutSessionModal').modal('hide');
    }

    // Submit Logout Session
    document.getElementById('ls-submit').addEventListener('click', function() {
        const ip = window.currentLogoutSessionIp;
        if (!ip) return;
        
        closeLogoutSessionModal();
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('ip', ip);
        
        fetch('/profile/logout-session', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                console.error('Error:', data.message || 'Gagal logout sesi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
<?= $this->endSection() ?>

