<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Halaman') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Halaman</h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/content/pages/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Halaman
                    </a>
                </div>
            </div>

            <!-- Pages List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Halaman</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="pagesTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Slug</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pages)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada halaman. Klik "Tambah Halaman" untuk menambahkan.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($pages as $page): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= esc($page['title']) ?></strong>
                                            <?php if (!empty($page['subtitle'])): ?>
                                            <br><small class="text-muted"><?= esc($page['subtitle']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= esc($page['slug']) ?></code>
                                            <br><small class="text-muted">
                                                <a href="<?= base_url('page/' . $page['slug']) ?>" target="_blank" class="text-primary">
                                                    <?= base_url('page/' . $page['slug']) ?>
                                                </a>
                                            </small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($page['created_at'])) ?></td>
                                        <td>
                                            <?php if ($page['published']): ?>
                                                <span class="badge badge-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('page/' . $page['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat">
                                                    <span class="fe fe-eye fe-12"></span>
                                                </a>
                                                <a href="<?= base_url('admin/content/pages/' . $page['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <span class="fe fe-edit fe-12"></span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePage(<?= $page['id'] ?>)" title="Hapus">
                                                    <span class="fe fe-trash-2 fe-12"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function deletePage(id) {
        showConfirmation('Konfirmasi Hapus', 'Yakin ingin menghapus halaman ini?', function() {
            $.ajax({
                url: '<?= base_url('admin/content/pages/delete/') ?>' + id,
                method: 'POST',
                data: {
                    '<?= csrf_token() ?>': $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response && response.success) {
                        showNotification('success', 'Halaman berhasil dihapus');
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        showNotification('error', (response && response.message) || 'Gagal menghapus halaman');
                    }
                },
                error: function(xhr) {
                    var message = 'Terjadi kesalahan saat menghapus';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            message = response.message;
                        }
                    } catch(e) {
                        // Use default message
                    }
                    showNotification('error', message);
                }
            });
        });
    }
</script>
<?= $this->endSection() ?>
