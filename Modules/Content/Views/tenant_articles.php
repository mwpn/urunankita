<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Artikel/Blog') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Artikel/Blog</h2>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('tenant/content/articles/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Artikel
                    </a>
                </div>
            </div>

            <!-- Articles List -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <strong class="card-title">Daftar Artikel</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="articlesTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Penulis</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Belum ada artikel. Klik "Tambah Artikel" untuk menambahkan.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= esc($article['title']) ?></strong>
                                            <?php if (!empty($article['excerpt'])): ?>
                                            <br><small class="text-muted"><?= esc($article['excerpt']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($article['category'] ?? '-') ?></td>
                                        <td><?= esc($article['author_name'] ?? 'Admin') ?></td>
                                        <td><?= date('d/m/Y', strtotime($article['created_at'])) ?></td>
                                        <td>
                                            <?php if ($article['published']): ?>
                                                <span class="badge badge-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php 
                                                // Generate article URL - use tenant subdomain if tenant_slug exists
                                                if (!empty($tenant_slug)) {
                                                    // Get base domain from current request or use default
                                                    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
                                                    $hostParts = explode('.', $currentHost);
                                                    $baseDomain = env('app.baseDomain', 'urunankita.id'); // default
                                                    
                                                    if (count($hostParts) >= 2) {
                                                        // Extract base domain (last 2 parts: domain.tld)
                                                        $baseDomain = $hostParts[count($hostParts) - 2] . '.' . $hostParts[count($hostParts) - 1];
                                                    }
                                                    
                                                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                                    $articleUrl = $scheme . '://' . $tenant_slug . '.' . $baseDomain . '/article/' . esc($article['slug']);
                                                } else {
                                                    $articleUrl = base_url('article/' . esc($article['slug']));
                                                }
                                                ?>
                                                <a href="<?= $articleUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat">
                                                    <span class="fe fe-eye fe-12"></span>
                                                </a>
                                                <a href="<?= base_url('tenant/content/articles/' . $article['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <span class="fe fe-edit fe-12"></span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteArticle(<?= $article['id'] ?>)" title="Hapus">
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
    function deleteArticle(id) {
        showConfirmation('Konfirmasi Hapus', 'Yakin ingin menghapus artikel ini?', function() {
            $.ajax({
                url: '<?= base_url('tenant/content/articles/delete/') ?>' + id,
                method: 'POST',
                data: {
                    '<?= csrf_token() ?>': $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response && response.success) {
                        showNotification('success', 'Artikel berhasil dihapus');
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        showNotification('error', (response && response.message) || 'Gagal menghapus artikel');
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
