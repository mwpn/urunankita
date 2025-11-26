<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Langganan') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Langganan</h2>
                    <small class="text-muted">Daftar langganan dari semua penggalang</small>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Langganan</strong>
                        </div>
                        <div class="card-body">
                            <?php if (empty($subscriptions)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada langganan</p>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-subscriptions">
                                    <thead>
                                        <tr>
                                            <th>Penggalang</th>
                                            <th>Paket</th>
                                            <th>Mulai</th>
                                            <th>Berakhir</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscriptions as $sub): ?>
                                            <tr>
                                                <td><strong><?= esc($sub['tenant_name']) ?></strong></td>
                                                <td><?= esc($sub['plan_name']) ?></td>
                                                <td><?= date('d M Y', strtotime($sub['started_at'])) ?></td>
                                                <td><?= $sub['expires_at'] ? date('d M Y', strtotime($sub['expires_at'])) : '-' ?></td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'active' => 'badge-success',
                                                        'expired' => 'badge-danger',
                                                        'cancelled' => 'badge-secondary',
                                                    ];
                                                    $badgeClass = $statusBadges[$sub['status']] ?? 'badge-secondary';
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($sub['status'])) ?></span>
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
        if ($.fn.DataTable && $('#dataTable-subscriptions').length) {
            $('#dataTable-subscriptions').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[2, "desc"]],
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
