<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Buat Laporan Penggunaan Dana</h2>
                    <small class="text-muted">Form untuk membuat laporan penggunaan dana baru</small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/reports') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
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

            <!-- Form -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Form Laporan Penggunaan Dana</strong>
                        </div>
                        <div class="card-body">
                            <form id="updateForm" class="needs-validation" novalidate method="POST" enctype="multipart/form-data" action="<?= base_url('campaign-update/create') ?>">
                                <?= csrf_field() ?>
                                
                                <!-- Pilih Urunan -->
                                <div class="form-group">
                                    <label for="campaign_id">
                                        Pilih Urunan <span class="text-danger">*</span>
                                    </label>
                                    <select 
                                        id="campaign_id" 
                                        name="campaign_id" 
                                        class="form-control select2" 
                                        required
                                    >
                                        <option value="">-- Pilih Urunan --</option>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <option value="<?= esc($campaign['id']) ?>">
                                                <?= esc($campaign['title']) ?> 
                                                (<?= esc($campaign['status'] ?? 'draft') ?>) 
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Pilih urunan terlebih dahulu</div>
                                </div>

                                <!-- Judul (Opsional) -->
                                <div class="form-group">
                                    <label for="title">Judul Laporan (Opsional)</label>
                                    <input 
                                        type="text" 
                                        id="title" 
                                        name="title"
                                        class="form-control"
                                        placeholder="Contoh: Update Penggunaan Dana - Minggu Ke-1"
                                    >
                                </div>

                                <!-- Jumlah Penggunaan Dana -->
                                <div class="form-group">
                                    <label for="amount_used">Jumlah Penggunaan Dana (Rp)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input 
                                            type="text" 
                                            id="amount_used" 
                                            name="amount_used"
                                            class="form-control"
                                            placeholder="0"
                                            oninput="formatRupiahInput(this)"
                                        >
                                    </div>
                                    <small class="form-text text-muted">Masukkan jumlah dana yang digunakan dalam laporan ini (opsional). Setiap laporan memiliki penggunaan dana sendiri-sendiri.</small>
                                </div>

                                <!-- Konten -->
                                <div class="form-group">
                                    <label for="content">
                                        Konten Laporan <span class="text-danger">*</span>
                                    </label>
                                    <textarea 
                                        id="content" 
                                        name="content" 
                                        rows="6"
                                        class="form-control"
                                        required
                                        placeholder="Jelaskan penggunaan dana dan perkembangan urunan..."
                                    ></textarea>
                                    <div class="invalid-feedback">Konten laporan wajib diisi</div>
                                </div>

                                <!-- Upload Gambar -->
                                <div class="form-group">
                                    <label for="images_files">Upload Foto (Multiple)</label>
                                    <input 
                                        type="file" 
                                        id="images_files" 
                                        name="images_files[]" 
                                        multiple 
                                        accept="image/*"
                                        class="form-control-file"
                                    >
                                    <small class="form-text text-muted">Pilih beberapa foto untuk ditampilkan di laporan</small>
                                    <div id="images_preview" class="mt-3 row"></div>
                                </div>

                                <!-- YouTube URL -->
                                <div class="form-group">
                                    <label for="youtube_url">URL Video YouTube (Opsional)</label>
                                    <input 
                                        type="url" 
                                        id="youtube_url" 
                                        name="youtube_url"
                                        class="form-control"
                                        placeholder="https://www.youtube.com/watch?v=..."
                                    >
                                    <small class="form-text text-muted">Masukkan URL video YouTube untuk laporan penggunaan dana</small>
                                </div>

                                <!-- Pin to Top -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input 
                                            type="checkbox" 
                                            id="is_pinned" 
                                            name="is_pinned" 
                                            value="1"
                                            class="custom-control-input"
                                        >
                                        <label class="custom-control-label" for="is_pinned">
                                            Pin ke atas (tampilkan di urutan teratas)
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="fe fe-save fe-12 mr-1"></span>Simpan Laporan
                                    </button>
                                    <a href="<?= base_url('admin/reports') ?>" class="btn btn-secondary">
                                        Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Format Rupiah Input
    function formatRupiahInput(input) {
        // Remove all non-numeric characters
        let value = input.value.replace(/[^0-9]/g, '');
        
        // Store the raw numeric value in a data attribute
        input.setAttribute('data-raw-value', value);
        
        // Format with thousand separators (optional, for display)
        if (value) {
            // Just keep the numeric value, no formatting needed for input
            input.value = value;
        } else {
            input.value = '';
        }
    }
    
    // Get raw numeric value from input
    function getRawNumericValue(input) {
        return input.getAttribute('data-raw-value') || input.value.replace(/[^0-9]/g, '');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('updateForm');
        const imagesInput = document.getElementById('images_files');
        const imagesPreview = document.getElementById('images_preview');

        // Image preview
        if (imagesInput && imagesPreview) {
            imagesInput.addEventListener('change', function(e) {
                imagesPreview.innerHTML = '';
                const files = e.target.files;
                
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'col-md-3 mb-3';
                                div.innerHTML = `
                                    <div class="card">
                                        <img src="${e.target.result}" alt="Preview" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    </div>
                                `;
                                imagesPreview.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            });
        }

        // Submit form - use traditional form submission like Campaign form
        form.addEventListener('submit', function(e) {
            // Validate before submit
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            const campaignSelect = document.getElementById('campaign_id');
            const campaignId = campaignSelect ? campaignSelect.value : null;
            const contentTextarea = document.getElementById('content');
            const content = contentTextarea ? contentTextarea.value : '';

            if (!campaignId || campaignId === '' || campaignId === 'null' || campaignId === null) {
                e.preventDefault();
                if (typeof showToast === 'function') { 
                    showToast('warning', 'Pilih Urunan terlebih dahulu'); 
                } else { 
                    console.warn('Pilih Urunan terlebih dahulu'); 
                }
                campaignSelect?.focus();
                return;
            }
            
            if (!content || content.trim() === '') {
                e.preventDefault();
                if (typeof showToast === 'function') { 
                    showToast('warning', 'Konten laporan wajib diisi'); 
                } else { 
                    console.warn('Konten laporan wajib diisi'); 
                }
                contentTextarea?.focus();
                return;
            }

            // Form will submit normally with files included
            // Controller will handle file uploads directly like CampaignController
        });
    });
</script>
<?= $this->endSection() ?>

