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
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Urunan Saya</h2>
                </div>
                <div class="col-auto">
                    <a href="/tenant/campaigns/create" class="btn btn-sm btn-primary">
                        <span class="fe fe-plus-circle fe-12 mr-1"></span>Buat Urunan Baru
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

            <!-- Table -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <?php if (empty($campaigns)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted mb-3">Belum ada urunan</p>
                                    <a href="/tenant/campaigns/create" class="btn btn-primary">
                                        <span class="fe fe-plus-circle fe-12 mr-1"></span>Buat Urunan Pertama
                                    </a>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-urunan">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Judul Urunan</th>
                                            <th>Kategori</th>
                                            <th>Jenis</th>
                                            <th>Target</th>
                                            <th>Terkumpul</th>
                                            <th>Progress</th>
                                            <th>Deadline</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <tr>
                                                <td>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="campaign-<?= $campaign['id'] ?>">
                                                        <label class="custom-control-label" for="campaign-<?= $campaign['id'] ?>"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?= esc($campaign['title']) ?></strong>
                                                    <small class="d-block text-muted">ID: #<?= str_pad($campaign['id'], 6, '0', STR_PAD_LEFT) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary"><?= esc($campaign['category'] ?? 'Umum') ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($campaign['campaign_type'] === 'target_based'): ?>
                                                        <span class="badge badge-info">Targeted</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Open</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($campaign['campaign_type'] === 'target_based'): ?>
                                                        Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></td>
                                                <td>
                                                    <?php if ($campaign['campaign_type'] === 'target_based' && ($campaign['target_amount'] ?? 0) > 0): ?>
                                                        <?php
                                                        $progress = (($campaign['current_amount'] ?? 0) / $campaign['target_amount']) * 100;
                                                        $progress = min(100, max(0, $progress));
                                                        $progressClass = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-info');
                                                        ?>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <small class="text-muted"><?= number_format($progress, 0) ?>%</small>
                                                    <?php else: ?>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <small class="text-muted">Terus Menerus</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $deadlineValue = $campaign['deadline'] ?? null;
                                                    if (!empty($deadlineValue) && $deadlineValue !== '0000-00-00' && $deadlineValue !== '0000-00-00 00:00:00') {
                                                        try {
                                                            // Handle different date formats
                                                            if (is_string($deadlineValue)) {
                                                                // Remove time if present
                                                                $dateOnly = explode(' ', $deadlineValue)[0];
                                                                // Try to parse the date
                                                                $timestamp = strtotime($dateOnly);
                                                                if ($timestamp !== false && $timestamp > 0) {
                                                                    // Format: 01 Jan 2024 (Indonesian months)
                                                                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                                                    $monthIndex = (int)date('n', $timestamp) - 1;
                                                                    $monthName = $months[$monthIndex] ?? date('M', $timestamp);
                                                                    echo date('d', $timestamp) . ' ' . $monthName . ' ' . date('Y', $timestamp);
                                                                } else {
                                                                    echo '<span class="text-muted">-</span>';
                                                                }
                                                            } elseif ($deadlineValue instanceof \DateTime || $deadlineValue instanceof \DateTimeInterface) {
                                                                echo $deadlineValue->format('d M Y');
                                                            } else {
                                                                echo '<span class="text-muted">-</span>';
                                                            }
                                                        } catch (\Exception $e) {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusBadges = [
                                                        'active' => 'badge-success',
                                                        'draft' => 'badge-secondary',
                                                        'pending_verification' => 'badge-warning',
                                                        'rejected' => 'badge-danger',
                                                        'completed' => 'badge-primary',
                                                        'deleted' => 'badge-secondary',
                                                    ];
                                                    $statusClass = $statusBadges[$campaign['status']] ?? 'badge-secondary';
                                                    $statusText = [
                                                        'active' => 'Aktif',
                                                        'draft' => 'Draft',
                                                        'pending_verification' => 'Pending',
                                                        'rejected' => 'Ditolak',
                                                        'completed' => 'Selesai',
                                                        'deleted' => 'Dihapus',
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= $statusText[$campaign['status']] ?? esc($campaign['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text-muted sr-only">Action</span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="/tenant/campaigns/<?= $campaign['id'] ?>">
                                                            <i class="fe fe-eye fe-12 mr-2"></i>Lihat Detail
                                                        </a>
                                                        <a class="dropdown-item" href="/tenant/campaigns/<?= $campaign['id'] ?>/edit">
                                                            <i class="fe fe-edit fe-12 mr-2"></i>Edit
                                                        </a>
                                                        <?php if (($campaign['campaign_type'] ?? 'target_based') !== 'target_based' && ($campaign['status'] ?? '') === 'active'): ?>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-success" href="#" onclick="openCompleteCampaignModal(<?= $campaign['id'] ?>, '<?= esc($campaign['title'], 'js') ?>'); return false;">
                                                                <i class="fe fe-check-circle fe-12 mr-2"></i>Urunan Selesai
                                                            </a>
                                                        <?php endif; ?>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="openDeleteCampaignModal(<?= $campaign['id'] ?>, '<?= esc($campaign['title'], 'js') ?>'); return false;">
                                                            <i class="fe fe-trash-2 fe-12 mr-2"></i>Hapus
                                                        </a>
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

<!-- Complete Campaign Modal -->
<div id="completeCampaignModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tandai Urunan Selesai</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeCompleteCampaignModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan menandai urunan berikut sebagai selesai:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Judul Urunan:</span>
                        <span id="cc-campaign-title" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>ID:</span>
                        <span id="cc-campaign-id">-</span>
                    </div>
                </div>
                <p class="text-info"><small><strong>Catatan:</strong> Urunan yang sudah ditandai sebagai selesai tidak akan menerima donasi lagi. Pastikan semua dana sudah diterima dan digunakan dengan baik.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeCompleteCampaignModal()">Batal</button>
                <button type="button" id="cc-submit" class="btn btn-success">Ya, Tandai Selesai</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Campaign Modal -->
<div id="deleteCampaignModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Urunan</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeDeleteCampaignModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan menghapus urunan berikut:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Judul Urunan:</span>
                        <span id="dc-campaign-title" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>ID:</span>
                        <span id="dc-campaign-id">-</span>
                    </div>
                </div>
                <p class="text-danger"><small><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Urunan yang dihapus tidak akan muncul lagi di halaman publik.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDeleteCampaignModal()">Batal</button>
                <button type="button" id="dc-submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize DataTable - hanya jika belum diinisialisasi
    (function() {
        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-urunan').length) {
                // Cek apakah table sudah diinisialisasi
                if ($.fn.dataTable.isDataTable('#dataTable-urunan')) {
                    console.log('DataTable already initialized');
                    return;
                }

                $('#dataTable-urunan').DataTable({
                    autoWidth: true,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "order": [
                        [7, "desc"]
                    ], // Sort by deadline (column 7)
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
                console.log('DataTable initialized');
            } else {
                console.warn('DataTable library not loaded or table not found');
                // Retry after a delay
                setTimeout(initDataTable, 500);
            }
        }

        // Wait for DOM and scripts to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initDataTable, 500);
            });
        } else {
            setTimeout(initDataTable, 500);
        }
    })();

    // Complete Campaign Modal Functions
    function openCompleteCampaignModal(campaignId, campaignTitle) {
        const modal = $('#completeCampaignModal');
        window.currentCompleteCampaignId = campaignId;
        document.getElementById('cc-campaign-title').textContent = campaignTitle || '-';
        document.getElementById('cc-campaign-id').textContent = '#' + String(campaignId).padStart(6, '0');
        modal.modal('show');
    }

    function closeCompleteCampaignModal() {
        $('#completeCampaignModal').modal('hide');
    }

    // Submit Complete Campaign
    document.getElementById('cc-submit').addEventListener('click', async function() {
        const campaignId = window.currentCompleteCampaignId;
        if (!campaignId) return;

        closeCompleteCampaignModal();

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const response = await fetch('/tenant/campaigns/' + campaignId + '/complete', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                redirect: 'follow'
            });

            // Controller returns redirect, so just reload the page
            // The flash message will be shown on the redirected page
            window.location.href = '/tenant/campaigns';
        } catch (error) {
            console.error('Error:', error);
            // Even on error, reload to show any flash messages
            window.location.href = '/tenant/campaigns';
        }
    });

    // Delete Campaign Modal Functions
    function openDeleteCampaignModal(campaignId, campaignTitle) {
        const modal = $('#deleteCampaignModal');
        window.currentDeleteCampaignId = campaignId;
        document.getElementById('dc-campaign-title').textContent = campaignTitle || '-';
        document.getElementById('dc-campaign-id').textContent = '#' + String(campaignId).padStart(6, '0');
        modal.modal('show');
    }

    function closeDeleteCampaignModal() {
        $('#deleteCampaignModal').modal('hide');
    }

    // Submit Delete Campaign
    document.getElementById('dc-submit').addEventListener('click', async function() {
        const campaignId = window.currentDeleteCampaignId;
        if (!campaignId) return;

        closeDeleteCampaignModal();

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const response = await fetch('/tenant/campaigns/' + campaignId + '/delete', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                redirect: 'follow'
            });

            // Controller returns redirect, so just reload the page
            // The flash message will be shown on the redirected page
            window.location.href = '/tenant/campaigns';
        } catch (error) {
            console.error('Error:', error);
            // Even on error, reload to show any flash messages
            window.location.href = '/tenant/campaigns';
        }
    });
</script>
<?= $this->endSection() ?>