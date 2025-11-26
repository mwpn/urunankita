<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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
                    <h2 class="h5 page-title mb-0"><?= esc($page_title ?? 'Laporan Transparansi') ?></h2>
                    <small class="text-muted">Ringkasan laporan keuangan dari semua urunan</small>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <a href="<?= base_url('tenant/reports/create') ?>" class="btn btn-sm btn-primary">
                            <span class="fe fe-plus fe-12 mr-1"></span>Tambah Laporan
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <span class="fe fe-download fe-12 mr-1"></span>Export Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <span class="fe fe-printer fe-12 mr-1"></span>Print
                        </button>
                    </div>
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

            <?php 
            // Calculate statistics
            $totalDonations = 0;
            $totalAmountUsed = 0;
            $totalCampaigns = count($campaigns);
            $activeCampaigns = 0;
            $completedCampaigns = 0;
            
            $campaignStats = [];
            $categoryStats = [];
            
            foreach ($campaigns as $campaign) {
                if ($campaign['status'] === 'active') $activeCampaigns++;
                if ($campaign['status'] === 'completed') $completedCampaigns++;
                
                $campaignDonations = (float) ($campaign['current_amount'] ?? 0);
                $campaignAmountUsed = 0;
                
                if (!empty($campaign['updates']) && is_array($campaign['updates'])) {
                    foreach ($campaign['updates'] as $update) {
                        $amountUsed = (float) ($update['amount_used'] ?? 0);
                        if ($amountUsed > 0) {
                            $campaignAmountUsed += $amountUsed;
                        }
                    }
                }
                
                $totalDonations += $campaignDonations;
                $totalAmountUsed += $campaignAmountUsed;
                
                $campaignStats[] = [
                    'id' => $campaign['id'],
                    'title' => $campaign['title'],
                    'category' => $campaign['category'] ?? 'Lainnya',
                    'status' => $campaign['status'],
                    'total_donations' => $campaignDonations,
                    'total_used' => $campaignAmountUsed,
                    'balance' => $campaignDonations - $campaignAmountUsed,
                    'percentage' => $campaignDonations > 0 ? ($campaignAmountUsed / $campaignDonations) * 100 : 0,
                    'updates' => $campaign['updates'] ?? [], // Include updates in stats
                ];
                
                // Category stats
                $category = $campaign['category'] ?? 'Lainnya';
                if (!isset($categoryStats[$category])) {
                    $categoryStats[$category] = [
                        'count' => 0,
                        'total_donations' => 0,
                        'total_used' => 0,
                    ];
                }
                $categoryStats[$category]['count']++;
                $categoryStats[$category]['total_donations'] += $campaignDonations;
                $categoryStats[$category]['total_used'] += $campaignAmountUsed;
            }
            
            $totalBalance = $totalDonations - $totalAmountUsed;
            $totalPercentage = $totalDonations > 0 ? ($totalAmountUsed / $totalDonations) * 100 : 0;
            ?>

            <!-- Statistik Keseluruhan -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Donasi Masuk</small>
                            <h3 class="mb-0">Rp <?= number_format($totalDonations, 0, ',', '.') ?></h3>
                            <small class="text-success"><span class="fe fe-arrow-up fe-12"></span> Total donasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Penggunaan</small>
                            <h3 class="mb-0 text-success">Rp <?= number_format($totalAmountUsed, 0, ',', '.') ?></h3>
                            <small class="text-muted"><?= number_format($totalPercentage, 1) ?>% dari total donasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Sisa Dana</small>
                            <h3 class="mb-0 text-info">Rp <?= number_format($totalBalance, 0, ',', '.') ?></h3>
                            <small class="text-muted"><?= number_format(100 - $totalPercentage, 1) ?>% belum digunakan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Jumlah Urunan</small>
                            <h3 class="mb-0"><?= number_format($totalCampaigns) ?></h3>
                            <small class="text-success"><?= $activeCampaigns ?> aktif, <?= $completedCampaigns ?> selesai</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Grafik Donasi vs Penggunaan</strong>
                        </div>
                        <div class="card-body">
                            <div id="chartDonasiPenggunaan" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Grafik per Kategori</strong>
                        </div>
                        <div class="card-body">
                            <div id="chartKategori" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan per Urunan -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="card-title">Ringkasan Laporan per Urunan</strong>
                                </div>
                                <div class="col-auto">
                                    <a href="<?= base_url('tenant/reports/create') ?>" class="btn btn-sm btn-primary">
                                        <span class="fe fe-plus fe-12 mr-1"></span>Tambah Laporan Penggunaan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($campaignStats)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted mb-4">Belum ada urunan. <a href="<?= base_url('tenant/campaigns/create') ?>" class="text-primary">Buat urunan baru</a> terlebih dahulu.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table datatables table-hover" id="dataTable-laporan-semua">
                                        <thead>
                                            <tr>
                                                <th>Judul Urunan</th>
                                                <th>Kategori</th>
                                                <th>Total Donasi</th>
                                                <th>Total Digunakan</th>
                                                <th>Sisa Dana</th>
                                                <th>% Digunakan</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($campaignStats as $stat): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= esc($stat['title']) ?></strong>
                                                        <small class="d-block text-muted">ID: #<?= str_pad($stat['id'], 6, '0', STR_PAD_LEFT) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary"><?= esc($stat['category']) ?></span>
                                                    </td>
                                                    <td><strong>Rp <?= number_format($stat['total_donations'], 0, ',', '.') ?></strong></td>
                                                    <td class="text-success"><strong>Rp <?= number_format($stat['total_used'], 0, ',', '.') ?></strong></td>
                                                    <td class="text-info"><strong>Rp <?= number_format($stat['balance'], 0, ',', '.') ?></strong></td>
                                                    <td>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, $stat['percentage']) ?>%"></div>
                                                        </div>
                                                        <small class="text-muted"><?= number_format($stat['percentage'], 1) ?>%</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button 
                                                                type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="showUsageDetail(<?= htmlspecialchars(json_encode($stat), ENT_QUOTES, 'UTF-8') ?>)"
                                                                title="Lihat Rincian Penggunaan"
                                                            >
                                                                <span class="fe fe-list fe-12 mr-1"></span>Rincian
                                                            </button>
                                                            <a href="<?= base_url('tenant/campaigns/' . $stat['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                                <span class="fe fe-eye fe-12 mr-1"></span>Detail
                                                            </a>
                                                        </div>
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

        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<!-- Modal Rincian Penggunaan Dana -->
<div class="modal fade" id="modalRincianPenggunaan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rincian Penggunaan Dana</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalRincianContent">
                <!-- Content akan diisi via JavaScript -->
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
    // Chart data
    const categoryData = <?= json_encode($categoryStats) ?>;
    const categoryLabels = Object.keys(categoryData);
    const categoryDonations = categoryLabels.map(cat => categoryData[cat].total_donations);
    const categoryUsed = categoryLabels.map(cat => categoryData[cat].total_used);

    // Initialize ApexCharts
    (function() {
        function initCharts() {
            if (typeof ApexCharts !== 'undefined') {
                // Chart Donasi vs Penggunaan
                if (document.getElementById('chartDonasiPenggunaan')) {
                    var optionsDonasiPenggunaan = {
                        series: [{
                            name: 'Donasi Masuk',
                            data: categoryDonations
                        }, {
                            name: 'Penggunaan',
                            data: categoryUsed
                        }],
                        chart: {
                            type: 'bar',
                            height: 300,
                            toolbar: {
                                show: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                endingShape: 'rounded'
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        xaxis: {
                            categories: categoryLabels
                        },
                        yaxis: {
                            labels: {
                                formatter: function(val) {
                                    return 'Rp ' + (val / 1000000).toFixed(0) + 'M';
                                }
                            }
                        },
                        fill: {
                            opacity: 1
                        },
                        colors: ['#3b82f6', '#10b981'],
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return 'Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        }
                    };

                    var chartDonasiPenggunaan = new ApexCharts(document.querySelector("#chartDonasiPenggunaan"), optionsDonasiPenggunaan);
                    chartDonasiPenggunaan.render();
                }

                // Chart per Kategori (Pie Chart)
                if (document.getElementById('chartKategori')) {
                    var optionsKategori = {
                        series: categoryDonations.map(val => val / 1000000),
                        chart: {
                            type: 'pie',
                            height: 300
                        },
                        labels: categoryLabels,
                        colors: ['#6c757d', '#3b82f6', '#ef4444', '#06b6d4', '#10b981', '#f59e0b'],
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return 'Rp ' + (val * 1000000).toLocaleString('id-ID');
                                }
                            }
                        }
                    };

                    var chartKategori = new ApexCharts(document.querySelector("#chartKategori"), optionsKategori);
                    chartKategori.render();
                }

                console.log('ApexCharts initialized');
            } else {
                console.warn('ApexCharts library not loaded');
                setTimeout(initCharts, 500);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initCharts, 300);
            });
        } else {
            setTimeout(initCharts, 300);
        }
    })();

    // Initialize DataTable
    (function() {
        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-laporan-semua').length) {
                if ($.fn.dataTable.isDataTable('#dataTable-laporan-semua')) {
                    return;
                }
                
                $('#dataTable-laporan-semua').DataTable({
                    autoWidth: true,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "order": [[2, "desc"]], // Sort by total donasi descending
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
                console.log('DataTable laporan semua initialized');
            } else {
                console.warn('DataTable library not loaded or table not found');
                setTimeout(initDataTable, 500);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initDataTable, 300);
            });
        } else {
            setTimeout(initDataTable, 300);
        }
    })();

    // Show Usage Detail Modal
    function showUsageDetail(campaignStat) {
        // Get updates from campaignStat (already included in the data)
        const updates = campaignStat.updates || [];
        // Filter updates yang memiliki amount_used
        const usageUpdates = updates.filter(update => update.amount_used && parseFloat(update.amount_used) > 0);
        
        let content = `
            <div class="mb-4">
                <h6 class="mb-3">${campaignStat.title}</h6>
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Total Donasi</small>
                        <strong class="h6">Rp ${parseInt(campaignStat.total_donations || 0).toLocaleString('id-ID')}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Total Digunakan</small>
                        <strong class="h6 text-success">Rp ${parseInt(campaignStat.total_used || 0).toLocaleString('id-ID')}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Sisa Dana</small>
                        <strong class="h6 text-info">Rp ${parseInt(campaignStat.balance || 0).toLocaleString('id-ID')}</strong>
                    </div>
                </div>
            </div>
        `;
        
        if (usageUpdates.length === 0) {
            content += `
                <div class="alert alert-info">
                    <p class="mb-0">Belum ada rincian penggunaan dana untuk urunan ini.</p>
                </div>
            `;
        } else {
            content += `
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Judul Laporan</th>
                                <th>Jumlah Digunakan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            usageUpdates.forEach(update => {
                const date = update.created_at ? new Date(update.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }) : '-';
                const amount = parseFloat(update.amount_used || 0);
                const contentPreview = update.content ? (update.content.length > 100 ? update.content.substring(0, 100) + '...' : update.content) : '-';
                
                content += `
                    <tr>
                        <td>${date}</td>
                        <td>${update.title || '-'}</td>
                        <td><strong class="text-success">Rp ${amount.toLocaleString('id-ID')}</strong></td>
                        <td><small class="text-muted">${contentPreview}</small></td>
                    </tr>
                `;
            });
            
            content += `
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        document.getElementById('modalRincianContent').innerHTML = content;
        $('#modalRincianPenggunaan').modal('show');
    }

</script>
<?= $this->endSection() ?>
