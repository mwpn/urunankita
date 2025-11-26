<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Invoice') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Invoice</h2>
                    <small class="text-muted">Daftar semua invoice dari penggalang</small>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="status-filter" class="form-label mb-0">Filter Status:</label>
                                    <select id="status-filter" class="form-control form-control-sm" onchange="window.location.href=this.value">
                                        <option value="<?= base_url('admin/invoices') ?>" <?= !isset($status_filter) || $status_filter === '' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="<?= base_url('admin/invoices?status=paid') ?>" <?= isset($status_filter) && $status_filter === 'paid' ? 'selected' : '' ?>>Lunas</option>
                                        <option value="<?= base_url('admin/invoices?status=pending') ?>" <?= isset($status_filter) && $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="<?= base_url('admin/invoices?status=overdue') ?>" <?= isset($status_filter) && $status_filter === 'overdue' ? 'selected' : '' ?>>Terlambat</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Invoice</strong>
                        </div>
                        <div class="card-body">
                            <?php if (empty($invoices)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada invoice</p>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-invoices">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Penggalang</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td><strong><?= esc($invoice['invoice_number'] ?? '#' . $invoice['id']) ?></strong></td>
                                                <td><?= esc($invoice['tenant_name']) ?></td>
                                                <td><strong class="text-success">Rp <?= number_format($invoice['amount'] ?? 0, 0, ',', '.') ?></strong></td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'paid' => 'badge-success',
                                                        'pending' => 'badge-warning',
                                                        'overdue' => 'badge-danger',
                                                        'cancelled' => 'badge-secondary',
                                                    ];
                                                    $statusTexts = [
                                                        'paid' => 'Lunas',
                                                        'pending' => 'Pending',
                                                        'overdue' => 'Terlambat',
                                                        'cancelled' => 'Dibatalkan',
                                                    ];
                                                    $badgeClass = $statusBadges[$invoice['status']] ?? 'badge-secondary';
                                                    $statusText = $statusTexts[$invoice['status']] ?? esc($invoice['status']);
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($invoice['created_at'])) ?></td>
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
        if ($.fn.DataTable && $('#dataTable-invoices').length) {
            $('#dataTable-invoices').DataTable({
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
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
