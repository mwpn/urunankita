<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Helpdesk') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Helpdesk</h2>
                    <small class="text-muted">Kelola semua ticket dari penggalang</small>
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
                                        <option value="<?= base_url('admin/helpdesk') ?>" <?= !isset($status_filter) || $status_filter === '' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="<?= base_url('admin/helpdesk?status=open') ?>" <?= isset($status_filter) && $status_filter === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="<?= base_url('admin/helpdesk?status=in_progress') ?>" <?= isset($status_filter) && $status_filter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="<?= base_url('admin/helpdesk?status=resolved') ?>" <?= isset($status_filter) && $status_filter === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="<?= base_url('admin/helpdesk?status=closed') ?>" <?= isset($status_filter) && $status_filter === 'closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Ticket</strong>
                        </div>
                        <div class="card-body">
                            <?php if (empty($tickets)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada ticket</p>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-tickets">
                                    <thead>
                                        <tr>
                                            <th>Ticket #</th>
                                            <th>Subjek</th>
                                            <th>Penggalang</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td><strong><?= esc($ticket['ticket_number']) ?></strong></td>
                                                <td>
                                                    <strong><?= esc($ticket['subject']) ?></strong>
                                                    <?php if (!empty($ticket['description'])): ?>
                                                        <small class="d-block text-muted"><?= esc(substr($ticket['description'], 0, 60)) ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= esc($ticket['tenant_name'] ?? '-') ?></td>
                                                <td>
                                                    <?php
                                                    $priorityBadges = [
                                                        'low' => 'badge-secondary',
                                                        'medium' => 'badge-info',
                                                        'high' => 'badge-warning',
                                                        'urgent' => 'badge-danger',
                                                    ];
                                                    $priorityLabels = [
                                                        'low' => 'Low',
                                                        'medium' => 'Medium',
                                                        'high' => 'High',
                                                        'urgent' => 'Urgent',
                                                    ];
                                                    $priority = $ticket['priority'] ?? 'medium';
                                                    $badgeClass = $priorityBadges[$priority] ?? 'badge-info';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $priorityLabels[$priority] ?? ucfirst($priority) ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'open' => 'badge-warning',
                                                        'in_progress' => 'badge-info',
                                                        'resolved' => 'badge-success',
                                                        'closed' => 'badge-secondary',
                                                    ];
                                                    $statusLabels = [
                                                        'open' => 'Open',
                                                        'in_progress' => 'In Progress',
                                                        'resolved' => 'Resolved',
                                                        'closed' => 'Closed',
                                                    ];
                                                    $status = $ticket['status'] ?? 'open';
                                                    $badgeClass = $statusBadges[$status] ?? 'badge-secondary';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $statusLabels[$status] ?? ucfirst($status) ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($ticket['created_at'])) ?></td>
                                                <td>
                                                    <a href="<?= base_url('admin/helpdesk/' . $ticket['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                        Lihat
                                                    </a>
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
        if ($.fn.DataTable && $('#dataTable-tickets').length) {
            $('#dataTable-tickets').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[5, "desc"]],
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
                        "targets": [6],
                        "orderable": false
                    }
                ]
            });
        }
    });
</script>
<?= $this->endSection() ?>
