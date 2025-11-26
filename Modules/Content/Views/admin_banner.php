<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Banner & Slider') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Banner & Slider</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addBannerModal">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Banner
                    </button>
                </div>
            </div>

            <!-- Banner List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Banner</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="bannerTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Judul</th>
                                    <th>Link</th>
                                    <th>Urutan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Belum ada banner. Klik "Tambah Banner" untuk menambahkan.
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

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Banner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bannerForm">
                    <div class="form-group">
                        <label for="banner_title">Judul Banner</label>
                        <input type="text" class="form-control" id="banner_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="banner_image">Gambar</label>
                        <input type="file" class="form-control-file" id="banner_image" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="banner_link">Link (opsional)</label>
                        <input type="url" class="form-control" id="banner_link" name="link" placeholder="https://example.com">
                    </div>
                    <div class="form-group">
                        <label for="banner_order">Urutan</label>
                        <input type="number" class="form-control" id="banner_order" name="order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="banner_active" name="active" checked>
                            <label class="custom-control-label" for="banner_active">Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveBanner()">Simpan</button>
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

    function saveBanner() {
        // TODO: Implement save banner functionality
        alert('Fungsi simpan banner akan diimplementasikan');
    }
</script>
<?= $this->endSection() ?>

