<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Logo Sponsor') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Logo Sponsor</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addSponsorModal">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Sponsor
                    </button>
                </div>
            </div>

            <!-- Sponsor List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Sponsor</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="sponsorTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Logo</th>
                                    <th>Nama</th>
                                    <th>Website</th>
                                    <th>Urutan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sponsors)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Belum ada sponsor. Klik "Tambah Sponsor" untuk menambahkan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sponsors as $index => $sponsor): ?>
                                        <tr data-sponsor-id="<?= $sponsor['id'] ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?php if (!empty($sponsor['logo'])): ?>
                                                    <?php
                                                    $logoUrl = $sponsor['logo'];
                                                    if (!preg_match('~^https?://~', $logoUrl) && strpos($logoUrl, '/uploads/') !== 0) {
                                                        $logoUrl = '/uploads/' . ltrim($logoUrl, '/');
                                                    }
                                                    ?>
                                                    <img src="<?= esc(base_url(ltrim($logoUrl, '/'))) ?>" alt="<?= esc($sponsor['name']) ?>" style="max-height: 50px; max-width: 100px;">
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($sponsor['name']) ?></td>
                                            <td>
                                                <?php if (!empty($sponsor['website'])): ?>
                                                    <a href="<?= esc($sponsor['website']) ?>" target="_blank" rel="noopener noreferrer"><?= esc($sponsor['website']) ?></a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($sponsor['order']) ?></td>
                                            <td>
                                                <?php if ($sponsor['active']): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="editSponsor(<?= $sponsor['id'] ?>)">
                                                    <span class="fe fe-edit fe-12"></span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSponsor(<?= $sponsor['id'] ?>)">
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

<!-- Add/Edit Sponsor Modal -->
<div class="modal fade" id="addSponsorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sponsorModalTitle">Tambah Sponsor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="sponsorForm" enctype="multipart/form-data">
                    <input type="hidden" id="sponsor_id" name="id">
                    <div class="form-group">
                        <label for="sponsor_name">Nama Sponsor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sponsor_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="sponsor_logo">Logo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="sponsor_logo" name="logo" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, GIF, WEBP, SVG. Max: 5MB</small>
                        <div id="sponsor_logo_preview" class="mt-2"></div>
                    </div>
                    <div class="form-group">
                        <label for="sponsor_website">Website (opsional)</label>
                        <input type="url" class="form-control" id="sponsor_website" name="website" placeholder="https://example.com">
                    </div>
                    <div class="form-group">
                        <label for="sponsor_order">Urutan</label>
                        <input type="number" class="form-control" id="sponsor_order" name="order" value="0" min="0">
                        <small class="form-text text-muted">Angka lebih kecil akan ditampilkan lebih dulu</small>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sponsor_active" name="active" checked>
                            <label class="custom-control-label" for="sponsor_active">Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveSponsor()">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let editingSponsorId = null;

    $(document).ready(function() {
        // Preview logo when file is selected
        $('#sponsor_logo').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#sponsor_logo_preview').html('<img src="' + e.target.result + '" style="max-height: 100px; max-width: 200px;" class="img-thumbnail">');
                };
                reader.readAsDataURL(file);
            }
        });

        // Reset form when modal is closed
        $('#addSponsorModal').on('hidden.bs.modal', function() {
            resetSponsorForm();
        });
    });

    function resetSponsorForm() {
        $('#sponsorForm')[0].reset();
        $('#sponsor_id').val('');
        $('#sponsor_logo_preview').html('');
        editingSponsorId = null;
        $('#sponsorModalTitle').text('Tambah Sponsor');
    }

    function editSponsor(id) {
        editingSponsorId = id;
        $('#sponsorModalTitle').text('Edit Sponsor');
        
        // Fetch sponsor data
        fetch('<?= base_url('admin/content/sponsors/get/') ?>' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const sponsor = data.data;
                    $('#sponsor_id').val(sponsor.id);
                    $('#sponsor_name').val(sponsor.name);
                    $('#sponsor_website').val(sponsor.website || '');
                    $('#sponsor_order').val(sponsor.order || 0);
                    $('#sponsor_active').prop('checked', sponsor.active == 1);
                    
                    // Show existing logo
                    if (sponsor.logo) {
                        const logoUrl = sponsor.logo.startsWith('http') ? sponsor.logo : '<?= base_url() ?>' + sponsor.logo;
                        $('#sponsor_logo_preview').html('<img src="' + logoUrl + '" style="max-height: 100px; max-width: 200px;" class="img-thumbnail"><br><small class="text-muted">Ganti logo dengan memilih file baru</small>');
                    }
                    
                    $('#addSponsorModal').modal('show');
                } else {
                    alert('Gagal memuat data sponsor');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data sponsor');
            });
    }

    function saveSponsor() {
        const form = $('#sponsorForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const formData = new FormData(form);
        
        // Add active checkbox value
        formData.append('active', $('#sponsor_active').is(':checked') ? '1' : '0');
        
        // Add CSRF token
        const csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
        const csrfName = '<?= csrf_token() ?>';
        if (csrfToken) {
            formData.append(csrfName, csrfToken);
        }
        
        const url = editingSponsorId 
            ? '<?= base_url('admin/content/sponsors/update/') ?>' + editingSponsorId
            : '<?= base_url('admin/content/sponsors/store') ?>';
        
        // If editing and no new logo, remove logo from formData
        if (editingSponsorId && !$('#sponsor_logo')[0].files.length) {
            formData.delete('logo');
        }
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Gagal menyimpan sponsor');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('success', data.message || 'Sponsor berhasil disimpan');
                } else {
                    alert(data.message || 'Sponsor berhasil disimpan');
                }
                $('#addSponsorModal').modal('hide');
                resetSponsorForm();
                // Reload to show new/updated sponsor
                setTimeout(() => location.reload(), 500);
            } else {
                const errorMsg = data.message || 'Gagal menyimpan sponsor';
                if (typeof showToast === 'function') {
                    showToast('error', errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || 'Terjadi kesalahan saat menyimpan sponsor';
            if (typeof showToast === 'function') {
                showToast('error', errorMsg);
            } else {
                alert(errorMsg);
            }
        });
    }

    function deleteSponsor(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus sponsor ini?')) {
            return;
        }
        
        // Find the row to remove
        const row = document.querySelector(`tr[data-sponsor-id="${id}"]`);
        if (!row) {
            alert('Baris sponsor tidak ditemukan');
            return;
        }
        
        // Disable the delete button to prevent double-click
        const deleteBtn = row.querySelector('button.btn-danger');
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }
        
        // Get CSRF token
        const csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
        const csrfName = '<?= csrf_token() ?>';
        
        const formData = new FormData();
        formData.append(csrfName, csrfToken);
        
        fetch('<?= base_url('admin/content/sponsors/delete/') ?>' + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from table
                row.remove();
                
                // Check if table is now empty
                const tbody = document.querySelector('#sponsorTable tbody');
                const remainingRows = tbody.querySelectorAll('tr[data-sponsor-id]');
                
                if (remainingRows.length === 0) {
                    // Show empty message
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Belum ada sponsor. Klik "Tambah Sponsor" untuk menambahkan.</td></tr>';
                } else {
                    // Update row numbers
                    remainingRows.forEach((r, index) => {
                        r.querySelector('td:first-child').textContent = index + 1;
                    });
                }
                
                // Show success notification
                if (typeof showToast === 'function') {
                    showToast('success', data.message || 'Sponsor berhasil dihapus');
                } else {
                    alert(data.message || 'Sponsor berhasil dihapus');
                }
            } else {
                // Re-enable button on error
                if (deleteBtn) {
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<span class="fe fe-trash fe-12"></span>';
                }
                alert(data.message || 'Gagal menghapus sponsor');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Re-enable button on error
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<span class="fe fe-trash fe-12"></span>';
            }
            alert('Terjadi kesalahan saat menghapus sponsor');
        });
    }
</script>
<?= $this->endSection() ?>

