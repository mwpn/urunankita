<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<!-- Additional head content -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Dashboard</h2>
                </div>
                <div class="col-auto">
                    <form class="form-inline">
                        <div class="form-group d-none d-lg-inline">
                            <label for="reportrange" class="sr-only">Date Ranges</label>
                            <div id="reportrange" class="px-2 py-2 text-muted">
                                <span class="small"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-sm" onclick="location.reload()">
                                <span class="fe fe-refresh-ccw fe-16 text-muted"></span>
                            </button>
                            <button type="button" class="btn btn-sm mr-2">
                                <span class="fe fe-filter fe-16 text-muted"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="row mb-4">
                <div class="col-12 col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted mb-1">Total Penggalang</small>
                                    <h3 class="mb-0"><?= number_format($stats['total_tenants'] ?? 0) ?></h3>
                                    <span class="small text-success"><span class="fe fe-arrow-up fe-12"></span> <?= number_format($stats['active_tenants'] ?? 0) ?> aktif</span>
                                </div>
                                <div class="col-auto">
                                    <div class="card-icon bg-primary bg-opacity-25 text-primary">
                                        <span class="fe fe-users fe-24"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted mb-1">Total Urunan</small>
                                    <h3 class="mb-0"><?= number_format($stats['total_campaigns'] ?? 0) ?></h3>
                                    <span class="small text-success"><span class="fe fe-arrow-up fe-12"></span> Total</span>
                                </div>
                                <div class="col-auto">
                                    <div class="card-icon bg-success bg-opacity-25 text-success">
                                        <span class="fe fe-layers fe-24"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted mb-1">Total Donasi</small>
                                    <h3 class="mb-0">Rp <?= number_format(($stats['total_donations'] ?? 0) / 1000000, 1) ?>M</h3>
                                    <span class="small text-info"><span class="fe fe-arrow-up fe-12"></span> Rp <?= number_format($stats['total_donations'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="card-icon bg-info bg-opacity-25 text-info">
                                        <span class="fe fe-dollar-sign fe-24"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted mb-1">Total Penerima</small>
                                    <h3 class="mb-0"><?= number_format($stats['total_beneficiaries'] ?? 0) ?></h3>
                                    <span class="small text-muted">Penerima urunan</span>
                                </div>
                                <div class="col-auto">
                                    <div class="card-icon bg-warning bg-opacity-25 text-warning">
                                        <span class="fe fe-heart fe-24"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart & Recent Activity -->
            <div class="row mb-4">
                <div class="col-12 col-lg-8 mb-4 mb-lg-0">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Grafik Donasi</strong>
                        </div>
                        <div class="card-body">
                            <div class="chartbox">
                                <div id="donasiChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Penggalang Terbaru</strong>
                            <a class="float-right small text-muted" href="<?= base_url('admin/tenants') ?>">Lihat semua</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($stats['recent_tenants'])): ?>
                                <div class="list-group list-group-flush my-n3">
                                    <?php foreach (array_slice($stats['recent_tenants'], 0, 5) as $tenant): ?>
                                        <div class="list-group-item">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="fe fe-users text-primary fe-16"></span>
                                                </div>
                                                <div class="col">
                                                    <small><strong><?= esc($tenant['name']) ?></strong></small>
                                                    <div class="my-0 small text-muted"><?= esc($tenant['slug']) ?></div>
                                                </div>
                                                <div class="col-auto">
                                                    <?php
                                                    $status = $tenant['status'] ?? 'inactive';
                                                    $badgeClass = $status === 'active' ? 'badge-success' : 'badge-secondary';
                                                    $statusText = ucfirst($status);
                                                    ?>
                                                    <small class="badge <?= $badgeClass ?>"><?= $statusText ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Belum ada penggalang</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Aktivitas Terbaru</strong>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php if (!empty($stats['recent_tenants'])): ?>
                                    <?php foreach (array_slice($stats['recent_tenants'], 0, 4) as $tenant): ?>
                                        <div class="pb-3 timeline-item item-primary">
                                            <div class="pl-5">
                                                <div class="mb-3"><strong>Penggalang baru terdaftar</strong> - "<?= esc($tenant['name']) ?>"</div>
                                                <p class="small text-muted"><?= date('d M Y', strtotime($tenant['created_at'])) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Belum ada aktivitas</p>
                                <?php endif; ?>
                            </div>
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
    // Initialize chart after scripts loaded
    setTimeout(function() {
        if (typeof ApexCharts !== 'undefined' && document.querySelector("#donasiChart")) {
            var options = {
                series: [{
                    name: 'Donasi',
                    data: [<?php 
                        // Generate sample data for last 7 days
                        $chartData = [];
                        for ($i = 6; $i >= 0; $i--) {
                            $chartData[] = ($stats['total_donations'] ?? 0) / 7;
                        }
                        echo implode(', ', $chartData);
                    ?>]
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: ['<?php 
                        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $labels = [];
                        for ($i = 6; $i >= 0; $i--) {
                            $date = date('w', strtotime("-{$i} days"));
                            $labels[] = $days[$date];
                        }
                        echo implode("', '", $labels);
                    ?>']
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "Rp " + val.toLocaleString('id-ID');
                        }
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#donasiChart"), options);
            chart.render();
        }
    }, 1000);

    // Initialize date range picker
    setTimeout(function() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.daterangepicker && document.getElementById('reportrange')) {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
            cb(start, end);
        }
    }, 1500);
</script>
<?= $this->endSection() ?>