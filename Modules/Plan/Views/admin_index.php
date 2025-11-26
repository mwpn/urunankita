<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Paket Langganan') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Paket Langganan</h2>
                    <small class="text-muted">Kelola paket langganan untuk penggalang</small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus fe-12 mr-1"></span>Buat Paket
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

            <!-- Plans Grid -->
            <?php if (empty($plans)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <p class="text-muted mb-4">Belum ada paket langganan</p>
                                <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-primary">
                                    <span class="fe fe-plus fe-12 mr-1"></span>Buat Paket Pertama
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($plans as $plan): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h3 class="h5 mb-0"><?= esc($plan['name']) ?></h3>
                                        <div class="text-right">
                                            <?php if ($plan['price'] == 0): ?>
                                                <strong class="text-primary">Gratis</strong>
                                            <?php else: ?>
                                                <strong class="text-primary">Rp <?= number_format($plan['price'], 0, ',', '.') ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($plan['description']): ?>
                                        <p class="text-muted mb-3"><?= esc($plan['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($plan['features']) && is_array($plan['features'])): ?>
                                        <ul class="list-unstyled mb-3">
                                            <?php foreach ($plan['features'] as $feature): ?>
                                                <li class="mb-2">
                                                    <i class="fe fe-check text-success mr-2"></i>
                                                    <small><?= esc($feature) ?></small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto pt-3 border-top">
                                        <a href="<?= base_url('admin/plans/' . $plan['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary btn-block">
                                            <i class="fe fe-edit fe-12 mr-1"></i>Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>
