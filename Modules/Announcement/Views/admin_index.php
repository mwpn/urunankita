<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Pengumuman') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Pengumuman Platform</h2>
                    <small class="text-muted">Kelola pengumuman untuk semua tenant</small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/announcements/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus fe-12 mr-1"></span>Buat Pengumuman
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

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="filter" class="form-label mb-0">Filter:</label>
                                    <select id="filter" class="form-control form-control-sm" onchange="window.location.href=this.value">
                                        <option value="<?= base_url('admin/announcements') ?>">Semua</option>
                                        <option value="<?= base_url('admin/announcements?is_published=1') ?>" <?= isset($filters['is_published']) && $filters['is_published'] === 1 ? 'selected' : '' ?>>Dipublikasikan</option>
                                        <option value="<?= base_url('admin/announcements?is_published=0') ?>" <?= isset($filters['is_published']) && $filters['is_published'] === 0 ? 'selected' : '' ?>>Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Table -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Pengumuman</strong>
                        </div>
                        <div class="card-body">
                            <?php if (empty($announcements)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted mb-4">Belum ada pengumuman</p>
                                    <a href="<?= base_url('admin/announcements/create') ?>" class="btn btn-primary">
                                        <span class="fe fe-plus fe-12 mr-1"></span>Buat Pengumuman Pertama
                                    </a>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-announcements">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Tipe</th>
                                            <th>Prioritas</th>
                                            <th>Status</th>
                                            <th>Dibuat</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($announcements as $announcement): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= esc($announcement['title']) ?></strong>
                                                    <small class="d-block text-muted"><?= esc(substr(strip_tags($announcement['content']), 0, 100)) ?>...</small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $typeBadges = [
                                                        'info' => 'badge-info',
                                                        'warning' => 'badge-warning',
                                                        'success' => 'badge-success',
                                                        'error' => 'badge-danger',
                                                    ];
                                                    $typeLabels = [
                                                        'info' => 'Info',
                                                        'warning' => 'Peringatan',
                                                        'success' => 'Sukses',
                                                        'error' => 'Error',
                                                    ];
                                                    $type = $announcement['type'] ?? 'info';
                                                    $badgeClass = $typeBadges[$type] ?? 'badge-info';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $typeLabels[$type] ?? 'Info' ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priorityLabels = [
                                                        'low' => 'Rendah',
                                                        'normal' => 'Normal',
                                                        'high' => 'Tinggi',
                                                        'urgent' => 'Mendesak',
                                                    ];
                                                    $priority = $announcement['priority'] ?? 'normal';
                                                    ?>
                                                    <small class="text-muted"><?= $priorityLabels[$priority] ?? 'Normal' ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($announcement['is_published']): ?>
                                                        <span class="badge badge-success">Dipublikasikan</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Draft</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $announcement['created_at'] ? date('d M Y H:i', strtotime($announcement['created_at'])) : '-' ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= base_url('admin/announcements/' . $announcement['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <form method="POST" action="<?= base_url('admin/announcements/' . $announcement['id'] . '/delete') ?>" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');" class="d-inline">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable && $('#dataTable-announcements').length) {
            $('#dataTable-announcements').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[4, "desc"]],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "columnDefs": [
                    {
                        "targets": [5],
                        "orderable": false
                    }
                ]
            });
        }
    });
</script>
<?= $this->endSection() ?>
