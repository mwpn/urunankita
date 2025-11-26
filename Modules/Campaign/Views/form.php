<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? ($campaign ? 'Edit Urunan' : 'Buat Urunan Baru')) ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $campaign ? 'Edit Urunan' : 'Buat Urunan Baru' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="/tenant/campaigns" class="btn btn-sm btn-outline-secondary mr-2">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-submit">
                        <span class="fe fe-check fe-12 mr-1"></span><?= $campaign ? 'Update Urunan' : 'Publish Urunan' ?>
                    </button>
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
            <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data" action="<?= $campaign ? "/tenant/campaigns/{$campaign['id']}/update" : "/tenant/campaigns/store" ?>" id="campaignForm">
                <?= csrf_field() ?>
                
                <!-- Informasi Dasar -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Dasar</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="title">Judul Urunan <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: Bantuan Korban Banjir Jakarta" required value="<?= esc($campaign['title'] ?? old('title')) ?>">
                                <div class="invalid-feedback">Judul urunan wajib diisi</div>
                                <small class="form-text text-muted">Judul yang menarik akan membuat lebih banyak donatur tertarik</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="category">Kategori</label>
                                <input type="text" id="category" name="category" class="form-control" placeholder="Contoh: Kesehatan, Pendidikan, Bencana Alam" value="<?= esc($campaign['category'] ?? old('category')) ?>">
                            </div>

                            <div class="form-group col-md-6">
                                <label for="campaign_type">Jenis Urunan <span class="text-danger">*</span></label>
                                <select id="campaign_type" name="campaign_type" class="form-control" required onchange="toggleJenis()">
                                    <option value="">Pilih Jenis</option>
                                    <option value="target_based" <?= ($campaign['campaign_type'] ?? old('campaign_type')) === 'target_based' ? 'selected' : '' ?>>Targeted (Ada Target Donasi)</option>
                                    <option value="ongoing" <?= ($campaign['campaign_type'] ?? old('campaign_type')) === 'ongoing' ? 'selected' : '' ?>>Open (Tanpa Target)</option>
                                </select>
                                <div class="invalid-feedback">Pilih jenis urunan</div>
                            </div>
                        </div>

                        <!-- Target & Deadline -->
                        <div class="d-none" id="target-row">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="target_amount">Target Donasi <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control input-money" id="target_amount" name="target_amount" placeholder="0" value="<?= !empty($campaign['target_amount']) ? esc((int) $campaign['target_amount']) : (old('target_amount') ? esc((int) old('target_amount')) : '') ?>">
                                    </div>
                                    <div class="invalid-feedback">Target donasi wajib diisi untuk urunan targeted</div>
                                    <small class="form-text text-muted">Target donasi yang ingin dicapai</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="deadline">Tanggal Deadline <span class="text-danger">*</span></label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="terusMenerus" onchange="toggleTerusMenerus()">
                                            <label class="custom-control-label" for="terusMenerus" style="cursor: pointer; font-size: 0.875rem;">
                                                <strong>Tanpa deadline</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="date" class="form-control" id="deadline" name="deadline" value="<?= (!empty($campaign) && !empty($campaign['deadline'])) ? date('Y-m-d', strtotime($campaign['deadline'])) : old('deadline') ?>">
                                    <div class="invalid-feedback">Deadline wajib diisi untuk urunan targeted</div>
                                    <small class="form-text text-muted">Tanggal akhir penggalangan dana</small>
                                </div>
                            </div>
                        </div>

                        <!-- Deadline untuk Open -->
                        <div class="d-none" id="deadline-row">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="deadline-open">Tanggal Deadline</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="terusMenerusOpen" onchange="toggleTerusMenerusOpen()">
                                            <label class="custom-control-label" for="terusMenerusOpen" style="cursor: pointer; font-size: 0.875rem;">
                                                <strong>Tanpa deadline</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="date" class="form-control" id="deadline-open" name="deadline" value="<?= (!empty($campaign) && !empty($campaign['deadline'])) ? date('Y-m-d', strtotime($campaign['deadline'])) : old('deadline') ?>">
                                    <small class="form-text text-muted">Tanggal akhir penggalangan dana (opsional untuk urunan open)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Urunan -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Deskripsi Urunan</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="description">Ceritakan Detail Urunan <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control" rows="8" required placeholder="Jelaskan detail urunan, tujuan, dan manfaat donasi"><?= esc($campaign['description'] ?? old('description')) ?></textarea>
                            <div class="invalid-feedback">Deskripsi wajib diisi</div>
                            <small class="form-text text-muted">Jelaskan detail urunan, tujuan, dan manfaat donasi</small>
                        </div>
                    </div>
                </div>

                <!-- Gambar Urunan -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Gambar Urunan</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Upload Gambar Utama</label>
                            <input type="file" id="featured_image_file" name="featured_image_file" accept="image/*" class="form-control-file">
                            <small class="form-text text-muted">Max: 5MB, Format: JPG/PNG</small>
                            <?php if (!empty($campaign['featured_image'])): ?>
                                <?php 
                                $feat = $campaign['featured_image'];
                                if ($feat && !preg_match('~^https?://~', $feat) && strpos($feat, '/uploads/') !== 0) {
                                    $feat = '/uploads/' . ltrim($feat, '/');
                                }
                                ?>
                                <div class="mt-2">
                                    <img src="<?= esc(base_url(ltrim($feat, '/'))) ?>" alt="Current" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Gambar Tambahan (Opsional)</label>
                            <input type="file" id="images_files" name="images_files[]" multiple accept="image/*" class="form-control-file">
                            <small class="form-text text-muted">Max: 5 gambar, masing-masing 5MB</small>
                            <?php if (!empty($campaign['images']) && is_array($campaign['images'])): ?>
                                <div class="row mt-2">
                                    <?php foreach ($campaign['images'] as $img): ?>
                                        <div class="col-md-3 mb-2">
                                            <img src="<?= esc($img) ?>" alt="Gallery" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Lokasi</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="latitude">Latitude (Opsional)</label>
                                <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -6.2088" value="<?= esc($campaign['latitude'] ?? old('latitude')) ?>">
                                <small class="form-text text-muted">Koordinat latitude lokasi</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="longitude">Longitude (Opsional)</label>
                                <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 106.8456" value="<?= esc($campaign['longitude'] ?? old('longitude')) ?>">
                                <small class="form-text text-muted">Koordinat longitude lokasi</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="location_address">Alamat Lokasi (Opsional)</label>
                            <input type="text" class="form-control" id="location_address" name="location_address" placeholder="Contoh: Jl. Sudirman No. 123, Jakarta Pusat" value="<?= esc($campaign['location_address'] ?? old('location_address')) ?>">
                            <small class="form-text text-muted">Alamat lengkap lokasi urunan</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="checkbox" class="custom-control-input" id="customDraft" name="status" value="draft">
                                            <label class="custom-control-label" for="customDraft">Simpan sebagai draft</label>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="isPriority" name="is_priority" value="1" <?= (!empty($campaign['is_priority']) && $campaign['is_priority'] == 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="isPriority">
                                                <strong>Urunan Prioritas</strong>
                                            </label>
                                            <small class="form-text text-muted d-block">Urunan prioritas akan ditampilkan di bagian atas halaman depan</small>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="/tenant/campaigns" class="btn btn-outline-secondary mr-2">Batal</a>
                                        <button type="submit" class="btn btn-primary">
                                            <span class="fe fe-check fe-12 mr-1"></span><?= $campaign ? 'Update Urunan' : 'Publish Urunan' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function() {
        function setupJenisToggle() {
            const campaignType = document.getElementById('campaign_type');
            const targetRow = document.getElementById('target-row');
            const deadlineRow = document.getElementById('deadline-row');
            const targetInput = document.getElementById('target_amount');
            const deadlineInput = document.getElementById('deadline');
            const deadlineOpen = document.getElementById('deadline-open');
            const terusTarget = document.getElementById('terusMenerus');
            const terusOpen = document.getElementById('terusMenerusOpen');

            function show(el) { if (el) el.classList.remove('d-none'); }
            function hide(el) { if (el) el.classList.add('d-none'); }

            function toggleTerusMenerus() {
                if (terusTarget.checked) {
                    deadlineInput.disabled = true;
                    deadlineInput.removeAttribute('required');
                    deadlineInput.value = '';
                    deadlineInput.classList.add('bg-light');
                } else {
                    deadlineInput.disabled = false;
                    deadlineInput.setAttribute('required', 'required');
                    deadlineInput.classList.remove('bg-light');
                }
            }

            function toggleTerusMenerusOpen() {
                if (terusOpen.checked) {
                    deadlineOpen.disabled = true;
                    deadlineOpen.removeAttribute('required');
                    deadlineOpen.value = '';
                    deadlineOpen.classList.add('bg-light');
                } else {
                    deadlineOpen.disabled = false;
                    deadlineOpen.classList.remove('bg-light');
                }
            }

            function toggleJenis() {
                const value = campaignType.value;
                if (value === 'target_based') {
                    show(targetRow);
                    hide(deadlineRow);
                    if (targetInput) targetInput.setAttribute('required', 'required');
                    if (deadlineInput) {
                        deadlineInput.setAttribute('required', 'required');
                        deadlineInput.disabled = false;
                        deadlineInput.classList.remove('bg-light');
                    }
                    if (terusTarget) terusTarget.checked = false;
                    toggleTerusMenerus();
                } else if (value === 'ongoing') {
                    hide(targetRow);
                    show(deadlineRow);
                    if (targetInput) targetInput.removeAttribute('required');
                    if (deadlineOpen) {
                        deadlineOpen.disabled = false;
                        deadlineOpen.classList.remove('bg-light');
                    }
                    if (terusOpen) terusOpen.checked = false;
                    toggleTerusMenerusOpen();
                } else {
                    hide(targetRow);
                    hide(deadlineRow);
                }
            }

            window.toggleJenis = toggleJenis;
            window.toggleTerusMenerus = toggleTerusMenerus;
            window.toggleTerusMenerusOpen = toggleTerusMenerusOpen;

            if (campaignType) campaignType.addEventListener('change', toggleJenis);
            if (terusTarget) terusTarget.addEventListener('change', toggleTerusMenerus);
            if (terusOpen) terusOpen.addEventListener('change', toggleTerusMenerusOpen);

            toggleJenis(); // initial
        }

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Submit button
        document.getElementById('btn-submit').addEventListener('click', function() {
            document.getElementById('campaignForm').submit();
        });

        // Remove decimal from money input
        const targetAmountInput = document.getElementById('target_amount');
        if (targetAmountInput) {
            // Prevent comma (decimal separator) from being typed
            targetAmountInput.addEventListener('keydown', function(e) {
                // Block comma key
                if (e.key === ',' || e.key === 'Comma') {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Clean up on paste
            targetAmountInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    let value = this.value.replace(/[,]/g, '').replace(/[^0-9.]/g, '');
                    if (value.includes(',')) {
                        value = value.split(',')[0];
                    }
                    this.value = value;
                }, 10);
            });
            
            // Clean up on blur
            targetAmountInput.addEventListener('blur', function() {
                let value = this.value.replace(/[,]/g, '').replace(/[^0-9]/g, '');
                if (value) {
                    // Format with dots as thousands separator
                    value = parseInt(value).toLocaleString('id-ID');
                    this.value = value;
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupJenisToggle);
        } else {
            setupJenisToggle();
        }
    })();
</script>
<?= $this->endSection() ?>
