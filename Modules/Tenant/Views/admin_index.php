<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Penggalang') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Daftar Penggalang Dana</h2>
                    <small class="text-muted">Kelola semua penggalang dana yang terdaftar di platform</small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/tenants/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-user-plus fe-12 mr-1"></span>Tambah Penggalang Baru
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
                                    <label for="status-filter" class="form-label mb-0">Filter Status:</label>
                                    <select id="status-filter" class="form-control form-control-sm" onchange="window.location.href=this.value">
                                        <option value="<?= base_url('admin/tenants') ?>" <?= !isset($status_filter) || $status_filter === '' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="<?= base_url('admin/tenants?status=active') ?>" <?= isset($status_filter) && $status_filter === 'active' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="<?= base_url('admin/tenants?status=inactive') ?>" <?= isset($status_filter) && $status_filter === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                        <option value="<?= base_url('admin/tenants?status=suspended') ?>" <?= isset($status_filter) && $status_filter === 'suspended' ? 'selected' : '' ?>>Ditangguhkan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Daftar Penggalang -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="card-title">Daftar Penggalang Dana</strong>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control" placeholder="Cari penggalang..." id="searchPenggalang">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <span class="fe fe-search fe-12"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tenants)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted mb-4">Belum ada penggalang</p>
                                    <a href="<?= base_url('admin/tenants/create') ?>" class="btn btn-primary">
                                        <span class="fe fe-user-plus fe-12 mr-1"></span>Buat Penggalang Pertama
                                    </a>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-penggalang">
                                    <thead>
                                        <tr>
                                            <th>Nama Penggalang</th>
                                            <th>Slug</th>
                                            <th>Database</th>
                                            <th>Status</th>
                                            <th>Terdaftar</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tenants as $tenant): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= esc($tenant['name']) ?></strong>
                                                    <?php if ($tenant['domain']): ?>
                                                        <small class="d-block text-muted"><?= esc($tenant['domain']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="https://<?= esc($tenant['slug']) ?>.urunankita.test" target="_blank" class="text-primary">
                                                        <?= esc($tenant['slug']) ?>.urunankita.test
                                                    </a>
                                                </td>
                                                <td><?= esc($tenant['db_name'] ?? '-') ?></td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'active' => 'badge-success',
                                                        'inactive' => 'badge-secondary',
                                                        'suspended' => 'badge-danger',
                                                    ];
                                                    $statusTexts = [
                                                        'active' => 'Aktif',
                                                        'inactive' => 'Nonaktif',
                                                        'suspended' => 'Ditangguhkan',
                                                    ];
                                                    $badgeClass = $statusBadges[$tenant['status']] ?? 'badge-secondary';
                                                    $statusText = $statusTexts[$tenant['status']] ?? esc($tenant['status']);
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($tenant['created_at'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text-muted sr-only">Action</span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="https://<?= esc($tenant['slug']) ?>.urunankita.test" target="_blank">
                                                            <i class="fe fe-eye fe-12 mr-2"></i>Lihat Website
                                                        </a>
                                                        <a class="dropdown-item" href="<?= base_url('admin/tenants/' . $tenant['id'] . '/edit') ?>">
                                                            <i class="fe fe-edit fe-12 mr-2"></i>Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form method="POST" action="<?= base_url('admin/tenants/' . $tenant['id'] . '/delete') ?>" onsubmit="return confirm('Yakin ingin menghapus penggalang ini?');">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fe fe-trash-2 fe-12 mr-2"></i>Hapus
                                                            </button>
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
    // Initialize DataTable
    $(document).ready(function() {
        if ($.fn.DataTable && $('#dataTable-penggalang').length) {
            $('#dataTable-penggalang').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[4, "desc"]], // Sort by terdaftar descending
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
                        "targets": [5], // Action column
                        "orderable": false
                    }
                ]
            });
        }
    });
</script>
<?= $this->endSection() ?>