<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Testimoni') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Testimoni</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTestimonialModal">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Testimoni
                    </button>
                </div>
            </div>

            <!-- Testimonials List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Testimoni</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="testimonialsTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Testimoni</th>
                                    <th>Rating</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Belum ada testimoni. Klik "Tambah Testimoni" untuk menambahkan.
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

<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Testimoni</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="testimonialForm">
                    <div class="form-group">
                        <label for="testimonial_name">Nama</label>
                        <input type="text" class="form-control" id="testimonial_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="testimonial_content">Testimoni</label>
                        <textarea class="form-control" id="testimonial_content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="testimonial_rating">Rating</label>
                        <select class="form-control" id="testimonial_rating" name="rating" required>
                            <option value="5">5 Bintang</option>
                            <option value="4">4 Bintang</option>
                            <option value="3">3 Bintang</option>
                            <option value="2">2 Bintang</option>
                            <option value="1">1 Bintang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="testimonial_photo">Foto (opsional)</label>
                        <input type="file" class="form-control-file" id="testimonial_photo" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="testimonial_active" name="active" checked>
                            <label class="custom-control-label" for="testimonial_active">Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveTestimonial()">Simpan</button>
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

    function saveTestimonial() {
        // TODO: Implement save testimonial functionality
        alert('Fungsi simpan testimoni akan diimplementasikan');
    }
</script>
<?= $this->endSection() ?>

