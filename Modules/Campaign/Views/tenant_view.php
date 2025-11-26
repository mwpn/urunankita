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
                    <h2 class="h5 page-title mb-0">Detail Urunan</h2>
                    <small class="text-muted">ID: #<?= str_pad($campaign['id'], 6, '0', STR_PAD_LEFT) ?></small>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <a href="/tenant/campaigns" class="btn btn-sm btn-outline-secondary">
                            <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
                        </a>
                        <a href="/tenant/campaigns/<?= $campaign['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                            <span class="fe fe-edit fe-12 mr-1"></span>Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="openDeleteCampaignModal(<?= $campaign['id'] ?>, '<?= esc($campaign['title'], 'js') ?>')">
                            <span class="fe fe-trash-2 fe-12 mr-1"></span>Hapus
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

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Informasi Utama -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-2"><?= esc($campaign['title']) ?></h3>
                                    <div class="mb-2">
                                        <?php if (!empty($campaign['category'])): ?>
                                            <span class="badge badge-secondary mr-2"><?= esc($campaign['category']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($campaign['campaign_type'] === 'target_based'): ?>
                                            <span class="badge badge-info mr-2">Targeted</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning mr-2">Open</span>
                                        <?php endif; ?>
                                        <?php
                                        $statusBadges = [
                                            'active' => 'badge-success',
                                            'draft' => 'badge-secondary',
                                            'pending_verification' => 'badge-warning',
                                            'rejected' => 'badge-danger',
                                            'completed' => 'badge-primary',
                                        ];
                                        $statusClass = $statusBadges[$campaign['status']] ?? 'badge-secondary';
                                        $statusText = [
                                            'active' => 'Aktif',
                                            'draft' => 'Draft',
                                            'pending_verification' => 'Pending',
                                            'rejected' => 'Ditolak',
                                            'completed' => 'Selesai',
                                        ];
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusText[$campaign['status']] ?? esc($campaign['status']) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Target Donasi</small>
                                    <strong class="h5">
                                        <?php if ($campaign['campaign_type'] === 'target_based'): ?>
                                            Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Terkumpul</small>
                                    <strong class="h5 text-success">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></strong>
                                </div>
                            </div>

                            <?php if ($campaign['campaign_type'] === 'target_based' && ($campaign['target_amount'] ?? 0) > 0): ?>
                                <?php
                                $progress = (($campaign['current_amount'] ?? 0) / $campaign['target_amount']) * 100;
                                $progress = min(100, max(0, $progress));
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted">Progress</small>
                                        <small class="text-muted"><strong><?= number_format($progress, 1) ?>%</strong></small>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                            <span class="small"><?= number_format($progress, 1) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Deadline</small>
                                    <strong>
                                        <?php if (!empty($campaign['deadline'])): ?>
                                            <?= date('d F Y', strtotime($campaign['deadline'])) ?>
                                        <?php else: ?>
                                            Tanpa deadline
                                        <?php endif; ?>
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Sisa Waktu</small>
                                    <strong class="<?= (!empty($campaign['deadline']) && strtotime($campaign['deadline']) < time()) ? 'text-danger' : 'text-info' ?>">
                                        <?php if (!empty($campaign['deadline'])): ?>
                                            <?php
                                            $deadline = strtotime($campaign['deadline']);
                                            $now = time();
                                            $diff = $deadline - $now;
                                            if ($diff > 0) {
                                                $days = floor($diff / (60 * 60 * 24));
                                                echo $days . ' hari lagi';
                                            } else {
                                                echo 'Telah berakhir';
                                            }
                                            ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <strong class="card-title">Deskripsi Urunan</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-justify"><?= nl2br(esc($campaign['description'] ?? '')) ?></p>
                        </div>
                    </div>

                    <!-- Galeri Gambar -->
                    <?php 
                    // Combine featured image, gallery images, and images from campaign updates
                    $allImages = [];
                    
                    // Add featured image
                    if (!empty($featured_image)) {
                        $allImages[] = $featured_image;
                    }
                    
                    // Add gallery images
                    if (!empty($campaign['images']) && is_array($campaign['images'])) {
                        foreach ($campaign['images'] as $img) {
                            // Skip if same as featured image
                            $imgPath = is_string($img) ? $img : '';
                            if ($imgPath && $imgPath !== $featured_image) {
                                $allImages[] = $imgPath;
                            }
                        }
                    }
                    
                    // Add images from campaign updates
                    if (!empty($updates) && is_array($updates)) {
                        foreach ($updates as $update) {
                            if (!empty($update['images'])) {
                                $updateImages = [];
                                if (is_string($update['images'])) {
                                    $decoded = json_decode($update['images'], true);
                                    $updateImages = is_array($decoded) ? $decoded : [];
                                } elseif (is_array($update['images'])) {
                                    $updateImages = $update['images'];
                                }
                                
                                foreach ($updateImages as $img) {
                                    if (!empty($img) && !in_array($img, $allImages)) {
                                        $allImages[] = $img;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($allImages)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Galeri Gambar</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php 
                                    $firstTwo = array_slice($allImages, 0, 2);
                                    $rest = array_slice($allImages, 2);
                                    ?>
                                    <?php foreach ($firstTwo as $img): ?>
                                        <?php 
                                        $imgUrl = $img;
                                        if ($imgUrl && !preg_match('~^https?://~', $imgUrl)) {
                                            if (strpos($imgUrl, '/uploads/') !== 0) {
                                                $imgUrl = '/uploads/' . ltrim($imgUrl, '/');
                                            }
                                        }
                                        ?>
                                        <div class="col-md-6 mb-3">
                                            <img src="<?= esc(base_url(ltrim($imgUrl, '/'))) ?>" class="img-fluid rounded shadow-sm" alt="Gambar Urunan" style="cursor: pointer;" onerror="this.src='<?= base_url('admin-template/assets/products/p1.jpg') ?>'" onclick="openImageModal('<?= esc(base_url(ltrim($imgUrl, '/')), 'js') ?>')">
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (!empty($rest)): ?>
                                        <?php foreach ($rest as $img): ?>
                                            <?php 
                                            $imgUrl = $img;
                                            if ($imgUrl && !preg_match('~^https?://~', $imgUrl)) {
                                                if (strpos($imgUrl, '/uploads/') !== 0) {
                                                    $imgUrl = '/uploads/' . ltrim($imgUrl, '/');
                                                }
                                            }
                                            ?>
                                            <div class="col-md-4 mb-3">
                                                <img src="<?= esc(base_url(ltrim($imgUrl, '/'))) ?>" class="img-fluid rounded shadow-sm" alt="Gambar Urunan" style="cursor: pointer;" onerror="this.src='<?= base_url('admin-template/assets/products/p1.jpg') ?>'" onclick="openImageModal('<?= esc(base_url(ltrim($imgUrl, '/')), 'js') ?>')">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Galeri Gambar - Empty State -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Galeri Gambar</strong>
                            </div>
                            <div class="card-body">
                                <p class="text-muted text-center mb-0">Belum ada gambar</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Galeri Video YouTube -->
                    <?php 
                    $youtubeUpdates = [];
                    if (!empty($updates) && is_array($updates)) {
                        foreach ($updates as $update) {
                            if (!empty($update['youtube_url'])) {
                                $youtubeUrl = trim($update['youtube_url']);
                                // Extract video ID
                                $videoId = null;
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtubeUrl, $matches)) {
                                    $videoId = $matches[1];
                                }
                                if ($videoId) {
                                    $youtubeUpdates[] = [
                                        'video_id' => $videoId,
                                        'title' => $update['title'] ?? null,
                                        'url' => $youtubeUrl
                                    ];
                                }
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($youtubeUpdates)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Video</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($youtubeUpdates as $video): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="embed-responsive embed-responsive-16by9 rounded shadow-sm">
                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= esc($video['video_id']) ?>" allowfullscreen></iframe>
                                            </div>
                                            <?php if (!empty($video['title'])): ?>
                                                <p class="mt-2 mb-0"><small class="text-muted"><?= esc($video['title']) ?></small></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Galeri Video - Empty State -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Video</strong>
                            </div>
                            <div class="card-body">
                                <p class="text-muted text-center mb-0">Belum ada video</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Lokasi -->
                    <?php if (!empty($campaign['latitude']) || !empty($campaign['longitude']) || !empty($campaign['location_address'])): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Lokasi</strong>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($campaign['location_address'])): ?>
                                    <p class="mb-2"><strong>Alamat:</strong> <?= esc($campaign['location_address']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($campaign['latitude']) || !empty($campaign['longitude'])): ?>
                                    <p class="mb-2"><strong>Koordinat:</strong></p>
                                    <p class="mb-1">Latitude: <?= esc($campaign['latitude'] ?? '-') ?></p>
                                    <p class="mb-0">Longitude: <?= esc($campaign['longitude'] ?? '-') ?></p>
                                <?php endif; ?>
                                <div class="mt-3 bg-light rounded p-3 text-center">
                                    <i class="fe fe-map-pin fe-24 text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">Peta lokasi akan ditampilkan di sini</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Statistik -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <strong class="card-title">Statistik</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Total Donasi</small>
                                <strong class="h5">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Jumlah Donatur</small>
                                <strong class="h5"><?= number_format($donation_stats['donor_count'] ?? 0, 0, ',', '.') ?></strong>
                            </div>
                            <?php if (!empty($donation_stats['max_amount'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Donasi Terbesar</small>
                                    <strong class="h5">Rp <?= number_format($donation_stats['max_amount'], 0, ',', '.') ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($donation_stats['average_amount'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Rata-rata Donasi</small>
                                    <strong class="h5">Rp <?= number_format($donation_stats['average_amount'], 0, ',', '.') ?></strong>
                                </div>
                            <?php endif; ?>
                            <div>
                                <small class="text-muted d-block">Dibuat</small>
                                <strong><?= date('d F Y', strtotime($campaign['created_at'])) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Donasi Terbaru -->
                    <?php if (!empty($donations)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Donasi Terbaru</strong>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($donations, 0, 5) as $donation): ?>
                                        <div class="list-group-item px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?= esc($donation['donor_name'] ?? 'Anonim') ?></strong>
                                                    <small class="d-block text-muted">
                                                        <?php
                                                        $created = strtotime($donation['created_at']);
                                                        $diff = time() - $created;
                                                        if ($diff < 3600) {
                                                            echo floor($diff / 60) . ' menit yang lalu';
                                                        } elseif ($diff < 86400) {
                                                            echo floor($diff / 3600) . ' jam yang lalu';
                                                        } elseif ($diff < 604800) {
                                                            echo floor($diff / 86400) . ' hari yang lalu';
                                                        } else {
                                                            echo date('d M Y, H:i', $created);
                                                        }
                                                        ?>
                                                    </small>
                                                </div>
                                                <strong class="text-success">Rp <?= number_format($donation['amount'] ?? 0, 0, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="/tenant/donations?campaign_id=<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-primary btn-block mt-3">
                                    Lihat Semua Donasi
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Quick Actions</strong>
                        </div>
                        <div class="card-body">
                            <a href="/tenant/reports/create?campaign_id=<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-primary btn-block mb-2">
                                <span class="fe fe-file-text fe-12 mr-1"></span>Laporan Penggunaan Dana
                            </a>
                            <a href="/tenant/discussions?campaign_id=<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-secondary btn-block mb-2">
                                <span class="fe fe-message-circle fe-12 mr-1"></span>Diskusi & Komentar
                            </a>
                            <button class="btn btn-sm btn-outline-info btn-block" onclick="shareCampaign()">
                                <span class="fe fe-share-2 fe-12 mr-1"></span>Bagikan Urunan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Gambar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeImageModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" class="img-fluid" alt="Preview">
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
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        $('#imageModal').modal('show');
    }

    function closeImageModal() {
        $('#imageModal').modal('hide');
    }

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

    function shareCampaign() {
        const url = window.location.href;
        if (navigator.share) {
            navigator.share({
                title: '<?= esc($campaign['title'], 'js') ?>',
                text: '<?= esc(mb_substr($campaign['description'] ?? '', 0, 100), 'js') ?>...',
                url: url
            }).catch(err => {
                console.log('Error sharing:', err);
                copyToClipboard(url);
            });
        } else {
            copyToClipboard(url);
        }
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof showToast === 'function') {
                showToast('success', 'Link berhasil disalin');
            } else {
                console.log('Link berhasil disalin');
            }
        }).catch(err => {
            console.error('Failed to copy:', err);
            if (typeof showToast === 'function') {
                showToast('error', 'Gagal menyalin link');
            }
        });
    }
</script>
<?= $this->endSection() ?>
