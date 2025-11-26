<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Form Pengumuman') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title"><?= $announcement ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' ?></h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/announcements') ?>" class="btn btn-sm btn-outline-secondary">
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
            <form method="POST" action="<?= $announcement ? base_url("admin/announcements/{$announcement['id']}/update") : base_url('admin/announcements/store') ?>" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">Informasi Pengumuman</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="title">Judul Pengumuman <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    required 
                                    value="<?= esc($announcement['title'] ?? old('title')) ?>"
                                    class="form-control"
                                    placeholder="Contoh: Maintenance Sistem"
                                >
                                <div class="invalid-feedback">Judul wajib diisi</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="content">Isi Pengumuman <span class="text-danger">*</span></label>
                                <textarea 
                                    id="content" 
                                    name="content" 
                                    required
                                    rows="8"
                                    class="form-control"
                                    placeholder="Masukkan isi pengumuman di sini..."
                                ><?= esc($announcement['content'] ?? old('content')) ?></textarea>
                                <div class="invalid-feedback">Isi pengumuman wajib diisi</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="type">Tipe</label>
                                <select 
                                    id="type" 
                                    name="type" 
                                    class="form-control"
                                >
                                    <option value="info" <?= ($announcement['type'] ?? 'info') === 'info' ? 'selected' : '' ?>>Info</option>
                                    <option value="warning" <?= ($announcement['type'] ?? null) === 'warning' ? 'selected' : '' ?>>Peringatan</option>
                                    <option value="success" <?= ($announcement['type'] ?? null) === 'success' ? 'selected' : '' ?>>Sukses</option>
                                    <option value="error" <?= ($announcement['type'] ?? null) === 'error' ? 'selected' : '' ?>>Error</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="priority">Prioritas</label>
                                <select 
                                    id="priority" 
                                    name="priority" 
                                    class="form-control"
                                >
                                    <option value="low" <?= ($announcement['priority'] ?? 'normal') === 'low' ? 'selected' : '' ?>>Rendah</option>
                                    <option value="normal" <?= ($announcement['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="high" <?= ($announcement['priority'] ?? 'normal') === 'high' ? 'selected' : '' ?>>Tinggi</option>
                                    <option value="urgent" <?= ($announcement['priority'] ?? 'normal') === 'urgent' ? 'selected' : '' ?>>Mendesak</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="expires_at">Tanggal Kadaluarsa (Opsional)</label>
                                <input 
                                    type="datetime-local" 
                                    id="expires_at" 
                                    name="expires_at" 
                                    value="<?= $announcement['expires_at'] ? date('Y-m-d\TH:i', strtotime($announcement['expires_at'])) : '' ?>"
                                    class="form-control"
                                >
                                <small class="form-text text-muted">Kosongkan jika pengumuman tidak memiliki batas waktu</small>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="custom-control custom-checkbox mt-4">
                                    <input 
                                        type="checkbox" 
                                        class="custom-control-input" 
                                        id="is_published" 
                                        name="is_published" 
                                        value="1"
                                        <?= ($announcement['is_published'] ?? 0) ? 'checked' : '' ?>
                                    >
                                    <label class="custom-control-label" for="is_published">
                                        Publikasikan sekarang
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/announcements') ?>" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= $announcement ? 'Perbarui' : 'Simpan' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>
