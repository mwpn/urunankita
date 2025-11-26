<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? ($article ? 'Edit Artikel' : 'Tambah Artikel')) ?></title>
<link href="<?= base_url('admin-template/css/quill.snow.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $article ? 'Edit Artikel' : 'Tambah Artikel' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('tenant/content/articles') ?>" class="btn btn-sm btn-outline-secondary mr-2">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-submit">
                        <span class="fe fe-check fe-12 mr-1"></span><?= $article ? 'Update Artikel' : 'Simpan Artikel' ?>
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
            <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data" action="<?= $article ? base_url('tenant/content/articles/update/' . $article['id']) : base_url('tenant/content/articles/store') ?>" id="articleForm">
                <?= csrf_field() ?>
                
                <!-- Informasi Dasar -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Dasar</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Judul Artikel <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Masukkan judul artikel" required value="<?= esc($article['title'] ?? old('title')) ?>">
                            <div class="invalid-feedback">Judul artikel wajib diisi</div>
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar Utama</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <label class="custom-file-label" for="image">Pilih gambar...</label>
                            </div>
                            <small class="form-text text-muted">Format: JPG, PNG, GIF, WebP. Maksimal 5MB.</small>
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                    <span class="fe fe-x fe-12"></span> Hapus Gambar
                                </button>
                            </div>
                            <?php if (!empty($article['image'])): ?>
                            <div id="existingImagePreview" class="mt-3">
                                <img src="<?= esc($article['image']) ?>" alt="Current Image" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                <small class="d-block text-muted mt-2">Gambar saat ini. Upload gambar baru untuk mengganti.</small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="category">Kategori</label>
                            <input type="text" id="category" name="category" class="form-control" placeholder="Contoh: Berita, Tips, Tutorial" value="<?= esc($article['category'] ?? old('category')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="campaign_id">Urunan Terkait (opsional)</label>
                            <select id="campaign_id" name="campaign_id" class="form-control">
                                <option value="">-- Pilih Urunan (opsional) --</option>
                                <?php
                                $db = \Config\Database::connect();
                                $tenantId = session()->get('tenant_id');
                                
                                // Fallback: derive from logged-in user if tenant_id not in session
                                if (!$tenantId) {
                                    $authUser = session()->get('auth_user') ?? [];
                                    $userId = $authUser['id'] ?? null;
                                    if ($userId) {
                                        $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                                        if ($userRow && !empty($userRow['tenant_id'])) {
                                            $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                                            if ($tenant) {
                                                $tenantId = (int) $tenant['id'];
                                            }
                                        }
                                    }
                                }
                                
                                if ($tenantId) {
                                    $campaigns = $db->table('campaigns')
                                        ->where('tenant_id', $tenantId)
                                        ->where('status', 'active')
                                        ->orderBy('title', 'ASC')
                                        ->get()
                                        ->getResultArray();
                                    
                                    $selectedCampaignId = $article['campaign_id'] ?? old('campaign_id');
                                    foreach ($campaigns as $campaign) {
                                        $selected = ($selectedCampaignId == $campaign['id']) ? 'selected' : '';
                                        echo '<option value="' . esc($campaign['id']) . '" ' . $selected . '>' . esc($campaign['title']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <small class="form-text text-muted">Pilih urunan jika artikel ini terkait dengan urunan tertentu. Statistik dan donasi urunan akan ditampilkan di sidebar artikel.</small>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Ringkasan (opsional)</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="Ringkasan singkat artikel yang akan ditampilkan di halaman daftar artikel"><?= esc($article['excerpt'] ?? old('excerpt')) ?></textarea>
                            <small class="form-text text-muted">Ringkasan singkat yang akan muncul di halaman daftar artikel</small>
                        </div>
                    </div>
                </div>

                <!-- Konten -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Konten Artikel</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="content">Konten <span class="text-danger">*</span></label>
                            <div id="content_editor" style="height: 400px;"></div>
                            <textarea class="form-control d-none" id="content" name="content" required><?= esc($article['content'] ?? old('content')) ?></textarea>
                            <div class="invalid-feedback">Konten artikel wajib diisi</div>
                            <small class="form-text text-muted">Gunakan editor untuk memformat konten artikel</small>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Pengaturan</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="published" name="published" <?= (!empty($article) && $article['published'] == 1) || empty($article) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="published">Publikasikan artikel</label>
                            </div>
                            <small class="form-text text-muted">Artikel yang dipublikasikan akan tampil di halaman publik</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <a href="<?= base_url('tenant/content/articles') ?>" class="btn btn-outline-secondary mr-2">
                                <span class="fe fe-x fe-12 mr-1"></span>Batal
                            </a>
                            <button type="button" class="btn btn-primary" id="btn-submit-bottom">
                                <span class="fe fe-check fe-12 mr-1"></span><?= $article ? 'Update Artikel' : 'Simpan Artikel' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('admin-template/js/quill.min.js') ?>"></script>
<script>
    var articleQuill;
    
    $(document).ready(function() {
        // Initialize Quill editor
        articleQuill = new Quill('#content_editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            },
            placeholder: 'Tulis konten artikel di sini...'
        });
        
        // Set initial content if editing
        <?php if (!empty($article['content'])): ?>
        articleQuill.root.innerHTML = <?= json_encode($article['content']) ?>;
        <?php endif; ?>
        
        // Sync Quill content to hidden textarea
        articleQuill.on('text-change', function() {
            $('#content').val(articleQuill.root.innerHTML);
        });
        
        // Update custom file input label
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
        });

        // Form submission handler
        function submitForm() {
            // Update hidden textarea with Quill content
            $('#content').val(articleQuill.root.innerHTML);
            
            // Validate form
            var form = document.getElementById('articleForm');
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
                return false;
            }
            
            // Submit form
            form.submit();
        }

        // Form submission
        $('#btn-submit, #btn-submit-bottom').on('click', function() {
            submitForm();
        });
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
                $('#existingImagePreview').hide();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        $('#image').val('');
        $('#previewImg').attr('src', '');
        $('#imagePreview').hide();
        $('.custom-file-label').removeClass('selected').html('Pilih gambar...');
    }
</script>
<?= $this->endSection() ?>

