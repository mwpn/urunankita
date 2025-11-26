<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Verifikasi Urunan') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Verifikasi Urunan</h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/campaigns') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                    </a>
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

            <!-- Campaign Info -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title"><?= esc($campaign['title']) ?></strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Deskripsi:</strong>
                            <p class="mb-0"><?= esc($campaign['description'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Tipe:</strong>
                            <span><?= $campaign['campaign_type'] === 'target_based' ? 'Target Based' : 'Ongoing' ?></span>
                        </div>
                        <?php if ($campaign['campaign_type'] === 'target_based'): ?>
                            <div class="col-md-6 mb-3">
                                <strong class="text-muted d-block mb-1">Target:</strong>
                                <span>Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted d-block mb-1">Kategori:</strong>
                            <span><?= esc($campaign['category'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Form -->
            <form method="POST" action="<?= base_url('admin/campaigns/' . $campaign['id'] . '/verify') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Keputusan Verifikasi</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-radio mb-3">
                                <input type="radio" id="approved_1" name="approved" value="1" class="custom-control-input" required>
                                <label class="custom-control-label" for="approved_1">
                                    <strong class="text-success">Setujui dan Aktifkan</strong>
                                    <small class="d-block text-muted">Urunan akan diaktifkan dan dapat menerima donasi</small>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="approved_0" name="approved" value="0" class="custom-control-input" required>
                                <label class="custom-control-label" for="approved_0">
                                    <strong class="text-danger">Tolak</strong>
                                    <small class="d-block text-muted">Urunan akan ditolak dan tidak dapat diaktifkan</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="rejection_reason_field" style="display: none;">
                            <label for="rejection_reason">Alasan Penolakan</label>
                            <textarea 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                rows="4"
                                class="form-control"
                                placeholder="Jelaskan alasan penolakan..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/campaigns') ?>" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Submit Verifikasi
                            </button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const approvedInputs = document.querySelectorAll('input[name="approved"]');
        const rejectionField = document.getElementById('rejection_reason_field');
        const rejectionTextarea = document.getElementById('rejection_reason');
        
        approvedInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === '0') {
                    rejectionField.style.display = 'block';
                    rejectionTextarea.required = true;
                } else {
                    rejectionField.style.display = 'none';
                    rejectionTextarea.required = false;
                    rejectionTextarea.value = '';
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
