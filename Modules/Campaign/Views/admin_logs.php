<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<style>
    .log-action-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .log-entity {
        font-weight: 600;
        color: #495057;
    }
    .log-description {
        color: #6c757d;
        font-size: 0.875rem;
    }
    .log-meta {
        font-size: 0.75rem;
        color: #868e96;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0"><?= esc($page_title ?? 'Riwayat & Log') ?></h2>
                    <small class="text-muted">Riwayat aktivitas dari semua tenant</small>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportLogs()">
                        <span class="fe fe-download fe-12 mr-1"></span>Export
                    </button>
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
                            <form method="GET" action="<?= base_url('admin/all/logs') ?>" id="filterForm">
                                <div class="row align-items-end">
                                    <?php if (isset($tenants) && !empty($tenants)): ?>
                                        <div class="col-md-3 mb-2 mb-md-0">
                                            <label for="tenant-filter" class="form-label mb-0 small">Filter Tenant:</label>
                                            <select id="tenant-filter" name="tenant_id" class="form-control form-control-sm" onchange="document.getElementById('filterForm').submit()">
                                                <option value="">Semua Tenant</option>
                                                <?php foreach ($tenants as $tenant): ?>
                                                    <option value="<?= $tenant['id'] ?>" <?= isset($selectedTenantId) && $selectedTenantId == $tenant['id'] ? 'selected' : '' ?>>
                                                        <?= esc($tenant['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($actions)): ?>
                                        <div class="col-md-2 mb-2 mb-md-0">
                                            <label for="action-filter" class="form-label mb-0 small">Filter Action:</label>
                                            <select id="action-filter" name="action" class="form-control form-control-sm" onchange="document.getElementById('filterForm').submit()">
                                                <option value="">Semua Action</option>
                                                <?php foreach ($actions as $action): ?>
                                                    <option value="<?= esc($action) ?>" <?= isset($selectedAction) && $selectedAction == $action ? 'selected' : '' ?>>
                                                        <?= esc(ucfirst($action)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entities)): ?>
                                        <div class="col-md-2 mb-2 mb-md-0">
                                            <label for="entity-filter" class="form-label mb-0 small">Filter Entity:</label>
                                            <select id="entity-filter" name="entity" class="form-control form-control-sm" onchange="document.getElementById('filterForm').submit()">
                                                <option value="">Semua Entity</option>
                                                <?php foreach ($entities as $entity): ?>
                                                    <option value="<?= esc($entity) ?>" <?= isset($selectedEntity) && $selectedEntity == $entity ? 'selected' : '' ?>>
                                                        <?= esc($entity) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label for="date-from" class="form-label mb-0 small">Dari Tanggal:</label>
                                        <input type="date" id="date-from" name="date_from" class="form-control form-control-sm" value="<?= esc($dateFrom ?? '') ?>" onchange="document.getElementById('filterForm').submit()">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label for="date-to" class="form-label mb-0 small">Sampai Tanggal:</label>
                                        <input type="date" id="date-to" name="date_to" class="form-control form-control-sm" value="<?= esc($dateTo ?? '') ?>" onchange="document.getElementById('filterForm').submit()">
                                    </div>
                                    <div class="col-md-1 mb-2 mb-md-0">
                                        <a href="<?= base_url('admin/all/logs') ?>" class="btn btn-sm btn-outline-secondary btn-block">
                                            <span class="fe fe-refresh-cw fe-12"></span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="card-title">Riwayat Aktivitas (<?= count($logs) ?> log)</strong>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control" placeholder="Cari log..." id="searchLogs">
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
                            <?php if (empty($logs)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada log aktivitas</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table datatables table-hover" id="dataTable-logs">
                                        <thead>
                                            <tr>
                                                <th>Waktu</th>
                                                <?php if (isset($tenants) && !empty($tenants)): ?>
                                                    <th>Tenant</th>
                                                <?php endif; ?>
                                                <th>User</th>
                                                <th>Action</th>
                                                <th>Entity</th>
                                                <th>Deskripsi</th>
                                                <th>IP Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('d M Y', strtotime($log['created_at'])) ?><br>
                                                            <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                                        </small>
                                                    </td>
                                                    <?php if (isset($tenants) && !empty($tenants)): ?>
                                                        <td>
                                                            <?php if (!empty($log['tenant_name'])): ?>
                                                                <span class="badge badge-secondary"><?= esc($log['tenant_name']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <strong><?= esc($log['user_name'] ?? 'Unknown') ?></strong>
                                                        <?php if (!empty($log['user_email'])): ?>
                                                            <small class="d-block text-muted"><?= esc($log['user_email']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $actionBadges = [
                                                            'create' => 'badge-success',
                                                            'update' => 'badge-info',
                                                            'delete' => 'badge-danger',
                                                            'login' => 'badge-primary',
                                                            'logout' => 'badge-secondary',
                                                            'view' => 'badge-light',
                                                        ];
                                                        $badgeClass = $actionBadges[$log['action']] ?? 'badge-secondary';
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?> log-action-badge"><?= esc(ucfirst($log['action'])) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($log['entity'])): ?>
                                                            <span class="log-entity"><?= esc($log['entity']) ?></span>
                                                            <?php if (!empty($log['entity_id'])): ?>
                                                                <small class="d-block text-muted">#<?= esc($log['entity_id']) ?></small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="log-description"><?= esc($log['description'] ?? '-') ?></span>
                                                    </td>
                                                    <td>
                                                        <small class="log-meta"><?= esc($log['ip_address'] ?? '-') ?></small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewLogDetail(<?= $log['id'] ?>)">
                                                            <span class="fe fe-eye fe-12"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailContent">
                <p class="text-muted">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function viewLogDetail(logId) {
        // TODO: Implement log detail view
        $('#logDetailModal').modal('show');
        document.getElementById('logDetailContent').innerHTML = '<p class="text-muted">Detail log akan ditampilkan di sini</p>';
    }

    function exportLogs() {
        // TODO: Implement export logs
        if (typeof showToast === 'function') {
            showToast('info', 'Fitur export akan segera tersedia');
        } else {
            console.log('Fitur export akan segera tersedia');
        }
    }

    // Initialize DataTable
    $(document).ready(function() {
        if ($.fn.DataTable && $('#dataTable-logs').length) {
            $('#dataTable-logs').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "order": [[0, "desc"]], // Sort by waktu descending
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
                        "targets": [<?= isset($tenants) && !empty($tenants) ? '7' : '6' ?>],
                        "orderable": false
                    }
                ],
                "pageLength": 25
            });
        }
    });
</script>
<?= $this->endSection() ?>

