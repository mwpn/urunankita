<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? ($page ? 'Edit Halaman' : 'Tambah Halaman')) ?></title>
<link href="<?= base_url('admin-template/css/quill.snow.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $page ? 'Edit Halaman' : 'Tambah Halaman' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('tenant/content/pages') ?>" class="btn btn-sm btn-outline-secondary mr-2">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-submit">
                        <span class="fe fe-check fe-12 mr-1"></span><?= $page ? 'Update Halaman' : 'Simpan Halaman' ?>
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
            <form class="needs-validation" novalidate method="POST" action="<?= $page ? base_url('tenant/content/pages/update/' . $page['id']) : base_url('tenant/content/pages/store') ?>" id="pageForm">
                <?= csrf_field() ?>
                
                <!-- Informasi Dasar -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Dasar</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Judul Halaman <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Masukkan judul halaman" required value="<?= esc($page['title'] ?? old('title')) ?>">
                            <div class="invalid-feedback">Judul halaman wajib diisi</div>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug URL <span class="text-danger">*</span></label>
                            <input type="text" id="slug" name="slug" class="form-control" placeholder="tentang-kami" required value="<?= esc($page['slug'] ?? old('slug')) ?>">
                            <div class="invalid-feedback">Slug URL wajib diisi</div>
                            <small class="form-text text-muted">URL akan menjadi: <?= base_url('page/') ?><span id="slug-preview">tentang-kami</span></small>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi (untuk meta description)</label>
                            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Deskripsi singkat halaman yang akan ditampilkan di hero section"><?= esc($page['description'] ?? old('description')) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="badge_text">Badge Text (opsional)</label>
                            <input type="text" id="badge_text" name="badge_text" class="form-control" placeholder="Disusun sesuai prinsip halal, amanah, dan transparansi" value="<?= esc($page['badge_text'] ?? old('badge_text')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="subtitle">Subtitle (opsional)</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control" placeholder="Ketentuan Sponsor & Kemitraan (Draft)" value="<?= esc($page['subtitle'] ?? old('subtitle')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Konten -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Konten Halaman</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="content">Konten <span class="text-danger">*</span></label>
                            <div id="content_editor" style="height: 400px;"></div>
                            <textarea class="form-control d-none" id="content" name="content" required><?= esc($page['content'] ?? old('content')) ?></textarea>
                            <div class="invalid-feedback">Konten halaman wajib diisi</div>
                            <small class="form-text text-muted">Gunakan editor untuk memformat konten halaman</small>
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
                                <input type="checkbox" class="custom-control-input" id="published" name="published" <?= (!empty($page) && $page['published'] == 1) || empty($page) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="published">Publikasikan halaman</label>
                            </div>
                            <small class="form-text text-muted">Halaman yang dipublikasikan akan tampil di halaman publik</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <a href="<?= base_url('tenant/content/pages') ?>" class="btn btn-outline-secondary mr-2">
                                <span class="fe fe-x fe-12 mr-1"></span>Batal
                            </a>
                            <button type="button" class="btn btn-primary" id="btn-submit-bottom">
                                <span class="fe fe-check fe-12 mr-1"></span><?= $page ? 'Update Halaman' : 'Simpan Halaman' ?>
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
    var pageQuill;
    
    $(document).ready(function() {
        // Initialize Quill editor
        pageQuill = new Quill('#content_editor', {
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
            placeholder: 'Tulis konten halaman di sini...'
        });
        
        // Set initial content if editing
        <?php if (!empty($page['content'])): ?>
        pageQuill.root.innerHTML = <?= json_encode($page['content']) ?>;
        <?php endif; ?>
        
        // Sync Quill content to hidden textarea
        pageQuill.on('text-change', function() {
            $('#content').val(pageQuill.root.innerHTML);
        });
        
        // Auto-generate slug from title
        $('#title').on('blur', function() {
            if (!$('#slug').val() || $('#slug').val() === '<?= esc($page['slug'] ?? '') ?>') {
                var slug = $(this).val()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#slug').val(slug);
                $('#slug-preview').text(slug);
            }
        });
        
        // Update slug preview
        $('#slug').on('input', function() {
            $('#slug-preview').text($(this).val() || 'tentang-kami');
        });

        // Form submission handler
        function submitForm() {
            // Update hidden textarea with Quill content
            $('#content').val(pageQuill.root.innerHTML);
            
            // Validate form
            var form = document.getElementById('pageForm');
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
</script>
<?= $this->endSection() ?>

