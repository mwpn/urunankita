<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Form Paket') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $plan ? 'Edit Paket' : 'Buat Paket Baru' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/plans') ?>" class="btn btn-sm btn-outline-secondary">
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

            <!-- Form -->
            <form method="POST" action="<?= $plan ? base_url("admin/plans/{$plan['id']}/update") : base_url('admin/plans/store') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Paket</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="name">Nama Paket <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required 
                                    value="<?= esc($plan['name'] ?? old('name')) ?>"
                                    class="form-control"
                                    placeholder="Contoh: Free, Pro, Enterprise"
                                >
                                <div class="invalid-feedback">Nama paket wajib diisi</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="price">Harga (Rp)</label>
                                <input 
                                    type="number" 
                                    id="price" 
                                    name="price" 
                                    value="<?= esc($plan['price'] ?? old('price')) ?>"
                                    class="form-control"
                                    placeholder="0"
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="description">Deskripsi</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    rows="3"
                                    class="form-control"
                                    placeholder="Jelaskan paket ini..."
                                ><?= esc($plan['description'] ?? old('description')) ?></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="features">Fitur (Satu per baris)</label>
                                <textarea 
                                    id="features" 
                                    name="features" 
                                    rows="6"
                                    class="form-control"
                                    placeholder="Fitur 1&#10;Fitur 2&#10;Fitur 3"
                                ><?php
                                    if ($plan && !empty($plan['features'])) {
                                        if (is_array($plan['features'])) {
                                            echo esc(implode("\n", $plan['features']));
                                        } else {
                                            echo esc($plan['features']);
                                        }
                                    } else {
                                        echo esc(old('features'));
                                    }
                                ?></textarea>
                                <small class="form-text text-muted">Masukkan satu fitur per baris</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/plans') ?>" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= $plan ? 'Perbarui Paket' : 'Buat Paket' ?>
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
    // Convert features textarea to JSON array on submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const featuresTextarea = document.getElementById('features');
        const featuresLines = featuresTextarea.value.split('\n').filter(line => line.trim() !== '');
        featuresTextarea.value = JSON.stringify(featuresLines);
    });
</script>
<?= $this->endSection() ?>
