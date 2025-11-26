<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title>Metode Pembayaran - Admin</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Metode Pembayaran</h2>
                    <p class="text-muted mb-0">Kelola metode pembayaran untuk tiap tenant/platform.</p>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/settings') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali ke Pengaturan
                    </a>
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

            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= base_url('admin/settings/payment-methods') ?>" class="form-inline mb-3">
                        <label class="mr-2 font-weight-semibold text-muted">Pilih Tenant:</label>
                        <select name="tenant_id" class="form-control select2" onchange="this.form.submit()">
                            <?php foreach ($tenants as $tenant): ?>
                                <option value="<?= $tenant['id'] ?>" <?= $tenant['id'] == $selectedTenantId ? 'selected' : '' ?>>
                                    <?= esc($tenant['name']) ?><?= $tenant['slug'] === 'platform' ? ' (Platform)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0"><?= esc($selectedTenant['name'] ?? 'Tenant') ?></h5>
                            <small class="text-muted">Total Metode: <?= count($payment_methods) ?></small>
                        </div>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAddPaymentMethod">
                            <span class="fe fe-plus fe-12 mr-1"></span>Tambah Metode Pembayaran
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <th>Jenis</th>
                                    <th>Provider</th>
                                    <th>Biaya Admin</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payment_methods)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada metode pembayaran untuk tenant ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($payment_methods as $method): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($method['name']) ?></strong>
                                                <?php if (!empty($method['description'])): ?>
                                                    <div class="text-muted small"><?= esc($method['description']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc(ucwords(str_replace('-', ' ', $method['type'] ?? ''))) ?></td>
                                            <td><?= esc($method['provider'] ?? '-') ?></td>
                                            <td>
                                                <div class="small text-muted mb-1">Persentase: <?= number_format($method['admin_fee_percent'] ?? 0, 2) ?>%</div>
                                                <div class="small text-muted">Tetap: Rp <?= number_format($method['admin_fee_fixed'] ?? 0, 2, ',', '.') ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $method['enabled'] ? 'success' : 'secondary' ?>">
                                                    <?= $method['enabled'] ? 'Aktif' : 'Nonaktif' ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <button class="btn btn-sm btn-outline-secondary mr-1" data-toggle="modal" data-target="#modalEditPaymentMethod" onclick="editPaymentMethod(<?= $method['id'] ?>)">
                                                    <span class="fe fe-edit fe-12"></span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning mr-1" onclick="togglePaymentMethod(<?= $method['id'] ?>, <?= $method['enabled'] ? '0' : '1' ?>)">
                                                    <span class="fe fe-toggle-<?= $method['enabled'] ? 'right' : 'left' ?> fe-12"></span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePaymentMethod(<?= $method['id'] ?>)">
                                                    <span class="fe fe-trash fe-12"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAddPaymentMethod" tabindex="-1" role="dialog" aria-labelledby="modalAddPaymentMethodLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modalAddPaymentMethodContent">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddPaymentMethodLabel">Tambah Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="<?= base_url('admin/settings/payment-methods/save') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tenant_id" value="<?= esc($selectedTenantId) ?>">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama Metode Pembayaran <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Transfer Bank BCA" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jenis Metode Pembayaran</label>
                            <select name="type" class="form-control">
                                <option value="bank-transfer">Transfer Bank</option>
                                <option value="virtual-account">Virtual Account</option>
                                <option value="e-wallet">E-Wallet</option>
                                <option value="credit-card">Kartu Kredit</option>
                                <option value="retail">Gerai Retail</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="enabled" class="form-control">
                                <option value="1" selected>Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Provider</label>
                            <input type="text" name="provider" class="form-control" placeholder="Contoh: Midtrans, Xendit, Manual">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Biaya Admin (%)</label>
                            <input type="number" step="0.01" name="admin_fee_percent" class="form-control" value="0">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Biaya Admin Tetap (Rp)</label>
                            <input type="number" step="0.01" name="admin_fee_fixed" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (opsional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Instruksi tambahan atau catatan"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="requireVerificationAdd" name="require_verification" value="1">
                        <label class="form-check-label" for="requireVerificationAdd">Perlu verifikasi manual sebelum donasi dianggap sah</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditPaymentMethod" tabindex="-1" role="dialog" aria-labelledby="modalEditPaymentMethodLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modalEditPaymentMethodContent">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditPaymentMethodLabel">Edit Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="<?= base_url('admin/settings/payment-methods/save') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tenant_id" value="<?= esc($selectedTenantId) ?>">
                <input type="hidden" name="method_id" id="edit_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama Metode Pembayaran <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jenis Metode Pembayaran</label>
                            <select name="type" id="edit_type" class="form-control">
                                <option value="bank-transfer">Transfer Bank</option>
                                <option value="virtual-account">Virtual Account</option>
                                <option value="e-wallet">E-Wallet</option>
                                <option value="credit-card">Kartu Kredit</option>
                                <option value="retail">Gerai Retail</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="enabled" id="edit_enabled" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Provider</label>
                            <input type="text" name="provider" id="edit_provider" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Biaya Admin (%)</label>
                            <input type="number" step="0.01" name="admin_fee_percent" id="edit_admin_fee_percent" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Biaya Admin Tetap (Rp)</label>
                            <input type="number" step="0.01" name="admin_fee_fixed" id="edit_admin_fee_fixed" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="requireVerificationEdit" name="require_verification" value="1">
                        <label class="form-check-label" for="requireVerificationEdit">Perlu verifikasi manual sebelum donasi dianggap sah</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modalDeletePaymentMethod" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Metode pembayaran akan dihapus permanen. Lanjutkan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    /* Ensure modal overlay is full screen */
    #modalAddPaymentMethod.show,
    #modalEditPaymentMethod.show {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 1050 !important;
    }

    /* Mobile - modal bisa di-geser */
    @media (max-width: 640px) {
        #modalAddPaymentMethod.show,
        #modalEditPaymentMethod.show {
            align-items: flex-start !important;
            overflow: hidden !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        
        #modalAddPaymentMethodContent,
        #modalEditPaymentMethodContent {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            max-height: 100vh !important;
            overflow: visible !important;
            touch-action: none !important;
            transition: transform 0.2s ease-out !important;
        }
    }
</style>
<script>
    const paymentMethods = <?= json_encode($payment_methods) ?>;
    const paymentMethodsById = {};
    paymentMethods.forEach(method => paymentMethodsById[method.id] = method);

    // Touch drag untuk modal pada mobile
    function initModalDrag(modalContentId) {
        const modalContent = document.getElementById(modalContentId);
        if (!modalContent) return;

        let touchStartY = 0;
        let touchCurrentY = 0;
        let modalOffsetY = 0;
        let isDragging = false;

        // Touch start
        modalContent.addEventListener('touchstart', function(e) {
            if (window.innerWidth <= 640) {
                touchStartY = e.touches[0].clientY;
                isDragging = true;
                modalContent.style.transition = 'none';
            }
        }, { passive: false });

        // Touch move
        modalContent.addEventListener('touchmove', function(e) {
            if (window.innerWidth <= 640 && isDragging) {
                e.preventDefault(); // Prevent default scroll
                touchCurrentY = e.touches[0].clientY;
                const deltaY = touchCurrentY - touchStartY;
                
                // Hanya bisa geser ke atas (positif) untuk lihat konten bawah, tidak bisa geser ke bawah
                if (deltaY > 0) {
                    // Batasi pergerakan maksimal ke atas
                    const maxOffset = 150;
                    modalOffsetY = Math.min(deltaY, maxOffset);
                    modalContent.style.transform = `translateY(${modalOffsetY}px)`;
                }
            }
        }, { passive: false });

        // Touch end
        modalContent.addEventListener('touchend', function() {
            if (window.innerWidth <= 640 && isDragging) {
                isDragging = false;
                modalContent.style.transition = 'transform 0.3s ease-out';
                
                // Reset jika pergerakan kecil
                if (Math.abs(modalOffsetY) < 50) {
                    modalContent.style.transform = 'translateY(0)';
                    modalOffsetY = 0;
                } else {
                    // Snap kembali ke posisi awal
                    modalContent.style.transform = 'translateY(0)';
                    modalOffsetY = 0;
                }
            }
        }, { passive: false });
    }

    // Initialize drag untuk modal Add
    $('#modalAddPaymentMethod').on('shown.bs.modal', function() {
        initModalDrag('modalAddPaymentMethodContent');
    });

    // Initialize drag untuk modal Edit
    $('#modalEditPaymentMethod').on('shown.bs.modal', function() {
        initModalDrag('modalEditPaymentMethodContent');
    });

    function editPaymentMethod(id) {
        const method = paymentMethodsById[id];
        if (!method) {
            alert('Metode pembayaran tidak ditemukan.');
            return;
        }
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = method.name || '';
        document.getElementById('edit_type').value = method.type || 'bank-transfer';
        document.getElementById('edit_enabled').value = method.enabled ? '1' : '0';
        document.getElementById('edit_provider').value = method.provider || '';
        document.getElementById('edit_admin_fee_percent').value = method.admin_fee_percent || 0;
        document.getElementById('edit_admin_fee_fixed').value = method.admin_fee_fixed || 0;
        document.getElementById('edit_description').value = method.description || '';
        document.getElementById('requireVerificationEdit').checked = method.require_verification == 1;
    }

    async function togglePaymentMethod(id, enabled) {
        const formData = new FormData();
        formData.append('enabled', enabled);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        try {
            const response = await fetch('<?= base_url('admin/settings/payment-methods') ?>/' + id + '/toggle', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Gagal mengubah status.');
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mengubah status.');
        }
    }

    let deleteId = null;
    function deletePaymentMethod(id) {
        deleteId = id;
        $('#modalDeletePaymentMethod').modal('show');
    }

    document.getElementById('btnConfirmDelete').addEventListener('click', async function () {
        if (!deleteId) return;
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        try {
            const response = await fetch('<?= base_url('admin/settings/payment-methods') ?>/' + deleteId + '/delete', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Gagal menghapus metode pembayaran.');
            }
        } catch (error) {
            alert('Terjadi kesalahan saat menghapus metode pembayaran.');
        }
    });
</script>
<?= $this->endSection() ?>

