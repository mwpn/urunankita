<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Urunan') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0"><?= esc($page_title ?? 'Semua Urunan') ?></h2>
                    <small class="text-muted">
                        <?php if (isset($tenants) && !empty($tenants)): ?>
                            Daftar semua urunan dari seluruh penggalang dana
                        <?php else: ?>
                            Daftar urunan milik admin
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/campaigns/create') ?>" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus fe-12 mr-1"></span>Buat Urunan
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
                                <?php if (isset($tenants) && !empty($tenants)): ?>
                                    <div class="col-md-4 mb-2 mb-md-0">
                                        <label for="tenant-filter" class="form-label mb-0">Filter Tenant:</label>
                                        <select id="tenant-filter" class="form-control form-control-sm" onchange="filterByTenant(this.value)">
                                            <?php 
                                            $isAllPage = strpos(uri_string(), 'admin/all/campaigns') !== false;
                                            $baseUrl = $isAllPage ? base_url('admin/all/campaigns') : base_url('admin/campaigns');
                                            $statusParam = isset($status_filter) && $status_filter ? '&status=' . $status_filter : '';
                                            ?>
                                            <option value="<?= $baseUrl . $statusParam ?>" <?= !isset($selectedTenantId) || $selectedTenantId === '' ? 'selected' : '' ?>>Semua Tenant</option>
                                            <?php foreach ($tenants as $tenant): ?>
                                                <option value="<?= $baseUrl . '?tenant_id=' . $tenant['id'] . $statusParam ?>" <?= isset($selectedTenantId) && $selectedTenantId == $tenant['id'] ? 'selected' : '' ?>>
                                                    <?= esc($tenant['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-4">
                                    <label for="status-filter" class="form-label mb-0">Filter Status:</label>
                                    <select id="status-filter" class="form-control form-control-sm" onchange="filterByStatus(this.value)">
                                        <?php 
                                        // Determine base URL - check if we're on /admin/all/campaigns or /admin/campaigns
                                        $isAllPage = strpos(uri_string(), 'admin/all/campaigns') !== false;
                                        $baseUrl = $isAllPage ? base_url('admin/all/campaigns') : base_url('admin/campaigns');
                                        $currentUrl = $baseUrl;
                                        $params = [];
                                        if (isset($selectedTenantId) && $selectedTenantId) {
                                            $params[] = 'tenant_id=' . $selectedTenantId;
                                        }
                                        if (isset($status_filter) && $status_filter) {
                                            $params[] = 'status=' . $status_filter;
                                        }
                                        if (!empty($params)) {
                                            $currentUrl .= '?' . implode('&', $params);
                                        }
                                        ?>
                                        <option value="<?= $baseUrl . (isset($selectedTenantId) && $selectedTenantId ? '?tenant_id=' . $selectedTenantId : '') ?>" <?= !isset($status_filter) || $status_filter === '' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="<?= $baseUrl . (isset($selectedTenantId) && $selectedTenantId ? '?tenant_id=' . $selectedTenantId . '&' : '?') . 'status=active' ?>" <?= isset($status_filter) && $status_filter === 'active' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="<?= $baseUrl . (isset($selectedTenantId) && $selectedTenantId ? '?tenant_id=' . $selectedTenantId . '&' : '?') . 'status=pending_verification' ?>" <?= isset($status_filter) && $status_filter === 'pending_verification' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                        <option value="<?= $baseUrl . (isset($selectedTenantId) && $selectedTenantId ? '?tenant_id=' . $selectedTenantId . '&' : '?') . 'status=draft' ?>" <?= isset($status_filter) && $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="<?= $baseUrl . (isset($selectedTenantId) && $selectedTenantId ? '?tenant_id=' . $selectedTenantId . '&' : '?') . 'status=deleted' ?>" <?= isset($status_filter) && $status_filter === 'deleted' ? 'selected' : '' ?>>Terhapus</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Semua Urunan -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="card-title">Daftar Semua Urunan</strong>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control" placeholder="Cari urunan..." id="searchUrunan">
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
                            <?php if (empty($campaigns)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada urunan</p>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-all-urunan">
                                    <thead>
                                        <tr>
                                            <th>Judul Urunan</th>
                                            <th>Penggalang</th>
                                            <th>Terkumpul</th>
                                            <th>Status</th>
                                            <th>Dibuat</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= base_url('admin/campaigns/' . $campaign['id']) ?>" class="text-primary">
                                                        <strong><?= esc($campaign['title']) ?></strong>
                                                    </a>
                                                </td>
                                                <td><?= esc($campaign['tenant_name'] ?? '-') ?></td>
                                                <td><strong class="text-success">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></strong></td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'active' => 'badge-success',
                                                        'draft' => 'badge-secondary',
                                                        'pending_verification' => 'badge-warning',
                                                        'rejected' => 'badge-danger',
                                                    ];
                                                    $statusTexts = [
                                                        'active' => 'Aktif',
                                                        'draft' => 'Draft',
                                                        'pending_verification' => 'Menunggu',
                                                        'rejected' => 'Ditolak',
                                                    ];
                                                    $badgeClass = $statusBadges[$campaign['status']] ?? 'badge-secondary';
                                                    $statusText = $statusTexts[$campaign['status']] ?? esc($campaign['status']);
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($campaign['created_at'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text-muted sr-only">Action</span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <?php if ($campaign['status'] === 'pending_verification'): ?>
                                                            <a class="dropdown-item" href="<?= base_url('admin/campaigns/' . $campaign['id'] . '/verify') ?>">
                                                                <i class="fe fe-check fe-12 mr-2"></i>Verifikasi
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($campaign['status'] === 'active'): ?>
                                                            <form method="POST" action="<?= base_url('admin/campaigns/' . $campaign['id'] . '/update-status') ?>" onsubmit="event.preventDefault(); showConfirmation('Konfirmasi', 'Yakin ingin menangguhkan urunan ini?', function() { this.submit(); }.bind(this)); return false;">
                                                                <?= csrf_field() ?>
                                                                <input type="hidden" name="status" value="suspended">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fe fe-pause fe-12 mr-2"></i>Tangguhkan
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if (in_array($campaign['status'], ['suspended', 'draft', 'rejected'])): ?>
                                                            <form method="POST" action="<?= base_url('admin/campaigns/' . $campaign['id'] . '/update-status') ?>" onsubmit="event.preventDefault(); showConfirmation('Konfirmasi', 'Yakin ingin mengaktifkan urunan ini?', function() { this.submit(); }.bind(this)); return false;">
                                                                <?= csrf_field() ?>
                                                                <input type="hidden" name="status" value="active">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fe fe-play fe-12 mr-2"></i>Aktifkan
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ($campaign['status'] === 'pending_verification'): ?>
                                                            <form method="POST" action="<?= base_url('admin/campaigns/' . $campaign['id'] . '/update-status') ?>" onsubmit="event.preventDefault(); showConfirmation('Konfirmasi', 'Yakin ingin menolak urunan ini?', function() { this.submit(); }.bind(this)); return false;">
                                                                <?= csrf_field() ?>
                                                                <input type="hidden" name="status" value="rejected">
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fe fe-x fe-12 mr-2"></i>Tolak
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="<?= base_url('admin/campaigns/' . $campaign['id'] . '/edit') ?>">
                                                            <i class="fe fe-edit fe-12 mr-2"></i>Edit
                                                        </a>
                                                        <form method="POST" action="<?= base_url('admin/campaigns/' . $campaign['id'] . '/delete') ?>" onsubmit="event.preventDefault(); showConfirmation('Konfirmasi Hapus', 'Yakin ingin menghapus urunan ini?', function() { this.submit(); }.bind(this)); return false;">
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
    // Filter functions
    function filterByTenant(url) {
        if (url) {
            window.location.href = url;
        }
    }

    function filterByStatus(url) {
        if (url) {
            window.location.href = url;
        }
    }

    // Initialize DataTable
    $(document).ready(function() {
        if ($.fn.DataTable && $('#dataTable-all-urunan').length) {
            $('#dataTable-all-urunan').DataTable({
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
