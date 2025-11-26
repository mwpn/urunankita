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
                    <a href="<?= base_url('admin/content/pages') ?>" class="btn btn-sm btn-outline-secondary mr-2">
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
            <form class="needs-validation" novalidate method="POST" action="<?= $page ? base_url('admin/content/pages/update/' . $page['id']) : base_url('admin/content/pages/store') ?>" id="pageForm">
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
                            <div id="content_editor" style="height: 400px; background-color: white;"></div>
                            <textarea class="form-control d-none" id="content" name="content" required><?= isset($page['content']) ? html_entity_decode($page['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : (old('content') ?? '') ?></textarea>
                            <div class="invalid-feedback">Konten halaman wajib diisi</div>
                            <small class="form-text text-muted">Gunakan editor untuk memformat konten halaman</small>
                            <?php if (!empty($page['content'])): ?>
                            <small class="form-text text-info">Debug: Content length = <?= strlen($page['content']) ?> chars</small>
                            <?php endif; ?>
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
                            <a href="<?= base_url('admin/content/pages') ?>" class="btn btn-outline-secondary mr-2">
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
        // Wait a bit to ensure DOM is fully ready
        setTimeout(function() {
            // Get content from textarea first (before initializing Quill)
            var initialContent = $('#content').val() || '';
            var contentLength = initialContent ? initialContent.length : 0;
            console.log('Initial content from textarea - Length:', contentLength);
            if (contentLength > 0) {
                console.log('First 200 chars:', initialContent.substring(0, 200));
            }
            
            // Initialize Quill editor
            try {
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
                
                // Make sure editor is editable
                pageQuill.enable(true);
                console.log('Quill editor initialized and enabled');
                
                // Set initial content if exists
                if (initialContent && initialContent.trim() !== '') {
                    // Wait a bit more to ensure Quill is fully ready
                    setTimeout(function() {
                        try {
                            console.log('Attempting to load content to editor...');
                            
                            // Method 1: Try using dangerouslyPasteHTML (most reliable for HTML)
                            if (pageQuill.clipboard && typeof pageQuill.clipboard.dangerouslyPasteHTML === 'function') {
                                pageQuill.clipboard.dangerouslyPasteHTML(initialContent);
                                console.log('Content loaded using dangerouslyPasteHTML');
                            } else {
                                // Method 2: Fallback to innerHTML
                                pageQuill.root.innerHTML = initialContent;
                                console.log('Content loaded using innerHTML');
                            }
                            
                            // Verify and sync
                            setTimeout(function() {
                                var editorHtml = pageQuill.root.innerHTML;
                                $('#content').val(editorHtml);
                                console.log('Content synced. Editor has content:', editorHtml.length > 0);
                                console.log('Editor content preview:', editorHtml.substring(0, 200));
                            }, 100);
                        } catch(e) {
                            console.error('Error setting content:', e);
                            // Last resort: set innerHTML directly
                            try {
                                pageQuill.root.innerHTML = initialContent;
                                $('#content').val(initialContent);
                                console.log('Content set using fallback method');
                            } catch(e2) {
                                console.error('Fallback also failed:', e2);
                            }
                        }
                    }, 100);
                } else {
                    console.log('No initial content to load - editor is ready for new content');
                }
                
                // Sync Quill content to hidden textarea on every change
                pageQuill.on('text-change', function() {
                    var htmlContent = pageQuill.root.innerHTML;
                    $('#content').val(htmlContent);
                });
                
                // Also sync on selection change (for better compatibility)
                pageQuill.on('selection-change', function() {
                    var htmlContent = pageQuill.root.innerHTML;
                    $('#content').val(htmlContent);
                });
            } catch(e) {
                console.error('Error initializing Quill editor:', e);
                alert('Error initializing editor. Please refresh the page.');
            }
        }, 100);
        
        // Auto-generate slug from title
        $('#title').on('blur', function() {
            var currentSlug = <?= json_encode($page['slug'] ?? '') ?>;
            if (!$('#slug').val() || $('#slug').val() === currentSlug) {
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
        function submitForm(e) {
            if (e) {
                e.preventDefault();
            }
            
            // Ensure Quill is initialized
            if (typeof pageQuill === 'undefined' || !pageQuill) {
                alert('Editor belum siap. Silakan tunggu sebentar dan coba lagi.');
                return false;
            }
            
            // Get content from Quill editor
            var quillContent = pageQuill.root.innerHTML;
            console.log('Quill content before submit:', quillContent.substring(0, 200));
            
            // Update hidden textarea with Quill content
            $('#content').val(quillContent);
            
            // Verify content is in textarea
            var textareaContent = $('#content').val();
            console.log('Textarea content after sync:', textareaContent.substring(0, 200));
            
            if (!textareaContent || textareaContent.trim() === '') {
                alert('Konten tidak boleh kosong!');
                return false;
            }
            
            // Validate form
            var form = document.getElementById('pageForm');
            if (form.checkValidity() === false) {
                form.classList.add('was-validated');
                return false;
            }
            
            // Submit form
            console.log('Submitting form...');
            form.submit();
        }

        // Form submission - use on click with event parameter
        $('#btn-submit, #btn-submit-bottom').on('click', function(e) {
            submitForm(e);
        });
        
        // Also handle form submit event directly
        $('#pageForm').on('submit', function(e) {
            // Ensure content is synced before actual submit
            if (typeof pageQuill !== 'undefined' && pageQuill) {
                var quillContent = pageQuill.root.innerHTML;
                $('#content').val(quillContent);
                console.log('Form submit event - content synced');
            }
        });
    });
</script>
<?= $this->endSection() ?>

