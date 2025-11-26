<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'FAQ Management') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">FAQ Management</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addFaqModal">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah FAQ
                    </button>
                </div>
            </div>

            <!-- FAQ List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar FAQ</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="faqTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pertanyaan</th>
                                    <th>Kategori</th>
                                    <th>Urutan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada FAQ. Klik "Tambah FAQ" untuk menambahkan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah FAQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="faqForm">
                    <div class="form-group">
                        <label for="faq_question">Pertanyaan</label>
                        <input type="text" class="form-control" id="faq_question" name="question" required>
                    </div>
                    <div class="form-group">
                        <label for="faq_answer">Jawaban</label>
                        <textarea class="form-control" id="faq_answer" name="answer" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="faq_category">Kategori</label>
                        <input type="text" class="form-control" id="faq_category" name="category" placeholder="Umum">
                    </div>
                    <div class="form-group">
                        <label for="faq_order">Urutan</label>
                        <input type="number" class="form-control" id="faq_order" name="order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="faq_active" name="active" checked>
                            <label class="custom-control-label" for="faq_active">Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveFaq()">Simpan</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable if needed
    });

    function saveFaq() {
        // TODO: Implement save FAQ functionality
        alert('Fungsi simpan FAQ akan diimplementasikan');
    }
</script>
<?= $this->endSection() ?>

