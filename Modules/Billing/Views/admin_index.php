<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Billing') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Billing</h2>
                    <small class="text-muted">Ringkasan pendapatan dan billing</small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/invoices') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-file-text fe-12 mr-1"></span>Lihat Semua Invoice
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <?php if (!empty($stats)): ?>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card shadow">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">Total Revenue</small>
                                <h3 class="mb-0">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">Total Invoice</small>
                                <h3 class="mb-0"><?= number_format($stats['total_invoices'] ?? 0, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow">
                            <div class="card-body">
                                <small class="text-muted d-block mb-1">Paid Invoice</small>
                                <h3 class="mb-0"><?= number_format($stats['paid_invoices'] ?? 0, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <p class="text-muted mb-0">Detail billing dan grafik revenue akan ditampilkan di sini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>
