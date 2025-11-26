<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Newsletter') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Newsletter</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addNewsletterModal">
                        <span class="fe fe-plus fe-12 mr-1"></span>Kirim Newsletter
                    </button>
                </div>
            </div>

            <!-- Newsletter Subscribers -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Subscriber</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="subscribersTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Email</th>
                                    <th>Nama</th>
                                    <th>Tanggal Subscribe</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada subscriber.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Newsletter History -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Riwayat Newsletter</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="newsletterHistoryTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Subject</th>
                                    <th>Dikirim ke</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada newsletter yang dikirim.
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

<!-- Send Newsletter Modal -->
<div class="modal fade" id="addNewsletterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kirim Newsletter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newsletterForm">
                    <div class="form-group">
                        <label for="newsletter_subject">Subject</label>
                        <input type="text" class="form-control" id="newsletter_subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="newsletter_content">Konten</label>
                        <textarea class="form-control" id="newsletter_content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="newsletter_recipients">Penerima</label>
                        <select class="form-control" id="newsletter_recipients" name="recipients" required>
                            <option value="all">Semua Subscriber</option>
                            <option value="active">Subscriber Aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="sendNewsletter()">Kirim</button>
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

    function sendNewsletter() {
        // TODO: Implement send newsletter functionality
        alert('Fungsi kirim newsletter akan diimplementasikan');
    }
</script>
<?= $this->endSection() ?>

