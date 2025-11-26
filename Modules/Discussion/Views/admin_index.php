<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <?php if ($selectedCampaign): ?>
                        <a href="<?= base_url('admin/campaigns/' . $selectedCampaign['id']) ?>" class="btn btn-sm btn-outline-secondary mb-2">
                            <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali ke Detail Urunan
                        </a>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h2 class="h5 page-title mb-0">Diskusi & Komentar</h2>
                            <small class="text-muted" id="urunan-diskusi-title">
                                <?php if ($selectedCampaignId === 'all'): ?>
                                    Semua Urunan - Menampilkan semua komentar
                                <?php elseif ($selectedCampaign): ?>
                                    <?= esc($selectedCampaign['title']) ?> - ID: #<?= $selectedCampaign['id'] ?>
                                    <?php if (!empty($selectedCampaign['tenant_name'])): ?>
                                        <span class="badge badge-secondary ml-2"><?= esc($selectedCampaign['tenant_name']) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Pilih urunan untuk melihat diskusi
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="text-right">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label for="selectTenantDiskusi" class="small text-muted mb-1 d-block">Filter Tenant:</label>
                                    <select id="selectTenantDiskusi" class="form-control form-control-sm select2" onchange="filterTenantDiskusi(this.value)">
                                        <option value="">Semua Tenant</option>
                                        <?php foreach ($tenants as $tenant): ?>
                                            <option value="<?= $tenant['id'] ?>" <?= ($selectedTenantId == $tenant['id']) ? 'selected' : '' ?>>
                                                <?= esc($tenant['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="selectUrunanDiskusi" class="small text-muted mb-1 d-block">Pilih Urunan:</label>
                                    <select id="selectUrunanDiskusi" class="form-control form-control-sm select2" onchange="filterDiskusiUrunan(this.value)">
                                        <option value="all" <?= ($selectedCampaignId === 'all') ? 'selected' : '' ?>>Semua Urunan</option>
                                        <?php if (empty($campaigns)): ?>
                                            <option value="">Tidak ada urunan</option>
                                        <?php else: ?>
                                            <?php foreach ($campaigns as $campaign): ?>
                                                <option value="<?= $campaign['id'] ?>" <?= ($selectedCampaignId == $campaign['id']) ? 'selected' : '' ?>>
                                                    <?= esc($campaign['title']) ?> - #<?= $campaign['id'] ?>
                                                    <?php if (!empty($campaign['tenant_name'])): ?>
                                                        (<?= esc($campaign['tenant_name']) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($campaigns)): ?>
                <div class="alert alert-info">
                    <span class="fe fe-info fe-16 mr-2"></span>
                    Belum ada urunan. <a href="<?= base_url('admin/campaigns/create') ?>" class="alert-link">Buat urunan baru</a> terlebih dahulu.
                </div>
            <?php else: ?>
                <!-- Info Moderasi -->
                <?php 
                $pendingCount = 0;
                foreach ($comments as $comment) {
                    if ($comment['status'] === 'pending') {
                        $pendingCount++;
                    }
                }
                ?>
                <?php if ($pendingCount > 0): ?>
                    <div class="alert alert-warning mb-3">
                        <span class="fe fe-alert-circle fe-16 mr-2"></span>
                        <strong>Ada <?= $pendingCount ?> komentar yang menunggu moderasi.</strong> 
                        Klik tombol <span class="fe fe-more-vertical fe-12"></span> (3 titik) di setiap komentar untuk melihat opsi <strong>Setujui</strong> atau <strong>Tolak</strong>.
                    </div>
                <?php endif; ?>
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Daftar Komentar -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="card-title mb-0">Komentar (<span id="total-komentar"><?= $stats['total_comments'] ?></span>)</strong>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary active" onclick="sortComments('latest')">Terbaru</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="sortComments('oldest')">Terlama</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="sortComments('popular')">Terpopuler</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" id="comments-list">
                                <?php if (empty($comments)): ?>
                                    <div class="text-center py-5">
                                        <p class="text-muted">Belum ada komentar untuk urunan ini.</p>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    // Sort comments: pinned first, then by date
                                    $sortedComments = $comments;
                                    usort($sortedComments, function($a, $b) {
                                        // Pinned comments first
                                        $aPinned = (int)($a['is_pinned'] ?? 0);
                                        $bPinned = (int)($b['is_pinned'] ?? 0);
                                        if ($aPinned !== $bPinned) {
                                            return $bPinned - $aPinned; // Pinned first
                                        }
                                        // Then by date (newest first)
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                    ?>
                                    <?php foreach ($sortedComments as $comment): ?>
                                        <div class="media mb-4 pb-4 border-bottom" data-comment-id="<?= $comment['id'] ?>" data-campaign-id="<?= $comment['campaign_id'] ?>">
                                            <?php if ($comment['is_guest'] || empty($comment['commenter_name'])): ?>
                                                <div class="mr-3">
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <span class="fe fe-user fe-16 text-white"></span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= esc($comment['user_avatar'] ?? base_url('admin-template/assets/avatars/user-default.jpg')) ?>" class="mr-3 rounded-circle" alt="Avatar" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
                                            <?php endif; ?>
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mt-0 mb-0">
                                                            <?php if ($comment['is_pinned']): ?>
                                                                <span class="badge badge-secondary badge-sm mr-1">📌 Disematkan</span>
                                                            <?php endif; ?>
                                                            <?= esc($comment['commenter_name'] ?? 'Anonim') ?>
                                                            <?php if ($comment['status'] === 'pending'): ?>
                                                                <span class="badge badge-warning badge-sm ml-1">Menunggu Moderasi</span>
                                                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                                                <span class="badge badge-danger badge-sm ml-1">Ditolak</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?> WIB</small>
                                                        <?php if ($selectedCampaignId === 'all' && !empty($comment['campaign_title'])): ?>
                                                            <div class="mt-1">
                                                                <small class="badge badge-info">
                                                                    <span class="fe fe-file-text fe-10 mr-1"></span>
                                                                    <?= esc($comment['campaign_title']) ?> #<?= $comment['campaign_id_display'] ?? $comment['campaign_id'] ?>
                                                                    <?php if (!empty($comment['tenant_name'])): ?>
                                                                        - <?= esc($comment['tenant_name']) ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link text-muted" type="button" data-toggle="dropdown" title="Opsi komentar">
                                                            <span class="fe fe-more-vertical fe-12"></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <?php if ($comment['status'] === 'pending'): ?>
                                                                <h6 class="dropdown-header text-warning">
                                                                    <span class="fe fe-clock fe-12 mr-1"></span>Menunggu Moderasi
                                                                </h6>
                                                                <a class="dropdown-item text-success font-weight-bold" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'approved')">
                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui Komentar
                                                                </a>
                                                                <a class="dropdown-item text-danger font-weight-bold" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'rejected')">
                                                                    <span class="fe fe-x fe-12 mr-2"></span>Tolak Komentar
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                                                <h6 class="dropdown-header text-danger">
                                                                    <span class="fe fe-x-circle fe-12 mr-1"></span>Status: Ditolak
                                                                </h6>
                                                                <a class="dropdown-item text-success font-weight-bold" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'approved')">
                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui Komentar
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                            <?php else: ?>
                                                                <h6 class="dropdown-header text-success">
                                                                    <span class="fe fe-check-circle fe-12 mr-1"></span>Status: Disetujui
                                                                </h6>
                                                                <div class="dropdown-divider"></div>
                                                            <?php endif; ?>
                                                            <a class="dropdown-item" href="#" onclick="pinComment(<?= $comment['id'] ?>, <?= $comment['is_pinned'] ? 'false' : 'true' ?>)">
                                                                <span class="fe fe-<?= $comment['is_pinned'] ? 'unlock' : 'lock' ?> fe-12 mr-2"></span>
                                                                <?= $comment['is_pinned'] ? 'Unpin' : 'Pin' ?>
                                                            </a>
                                                            <a class="dropdown-item text-danger" href="#" onclick="openDeleteCommentModal(<?= $comment['id'] ?>)">
                                                                <span class="fe fe-trash-2 fe-12 mr-2"></span>Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="mb-2"><?= nl2br(esc($comment['content'])) ?></p>
                                                <div class="d-flex align-items-center">
                                                    <button class="btn btn-sm btn-link text-muted p-0 mr-3" onclick="likeComment(<?= $comment['id'] ?>, this)">
                                                        <span class="fe fe-thumbs-up fe-12 mr-1"></span>Suka (<span class="like-count"><?= $comment['likes_count'] ?? 0 ?></span>)
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-muted p-0 mr-3" onclick="aminComment(<?= $comment['id'] ?>, this)">
                                                        <span class="fe fe-check-circle fe-12 mr-1"></span>Aamiin (<span class="amin-count"><?= $comment['amins_count'] ?? 0 ?></span>)
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-muted p-0 mr-3" data-toggle="collapse" data-target="#reply-<?= $comment['id'] ?>">
                                                        <span class="fe fe-message-circle fe-12 mr-1"></span>Balas
                                                    </button>
                                                </div>

                                                <!-- Replies -->
                                                <?php if (!empty($comment['replies'])): ?>
                                                    <?php foreach ($comment['replies'] as $reply): ?>
                                                        <div class="media mt-3 ml-4">
                                                            <?php if ($reply['is_guest'] || empty($reply['commenter_name'])): ?>
                                                                <div class="mr-3">
                                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                        <span class="fe fe-user fe-12 text-white"></span>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <img src="<?= esc($reply['user_avatar'] ?? base_url('admin-template/assets/avatars/user-default.jpg')) ?>" class="mr-3 rounded-circle" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
                                                            <?php endif; ?>
                                                            <div class="media-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mt-0 mb-0">
                                                                            <?= esc($reply['commenter_name'] ?? 'Anonim') ?>
                                                                            <?php if ($reply['status'] === 'pending'): ?>
                                                                                <span class="badge badge-warning badge-sm ml-1">Menunggu Moderasi</span>
                                                                            <?php elseif ($reply['status'] === 'rejected'): ?>
                                                                                <span class="badge badge-danger badge-sm ml-1">Ditolak</span>
                                                                            <?php endif; ?>
                                                                        </h6>
                                                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($reply['created_at'])) ?> WIB</small>
                                                                    </div>
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-sm btn-link text-muted" type="button" data-toggle="dropdown" title="Opsi balasan">
                                                                            <span class="fe fe-more-vertical fe-12"></span>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right">
                                                                            <?php if ($reply['status'] === 'pending'): ?>
                                                                                <h6 class="dropdown-header text-warning">
                                                                                    <span class="fe fe-clock fe-12 mr-1"></span>Menunggu Moderasi
                                                                                </h6>
                                                                                <a class="dropdown-item text-success font-weight-bold" href="#" onclick="moderateComment(<?= $reply['id'] ?>, 'approved')">
                                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui Balasan
                                                                                </a>
                                                                                <a class="dropdown-item text-danger font-weight-bold" href="#" onclick="moderateComment(<?= $reply['id'] ?>, 'rejected')">
                                                                                    <span class="fe fe-x fe-12 mr-2"></span>Tolak Balasan
                                                                                </a>
                                                                                <div class="dropdown-divider"></div>
                                                                            <?php elseif ($reply['status'] === 'rejected'): ?>
                                                                                <h6 class="dropdown-header text-danger">
                                                                                    <span class="fe fe-x-circle fe-12 mr-1"></span>Status: Ditolak
                                                                                </h6>
                                                                                <a class="dropdown-item text-success font-weight-bold" href="#" onclick="moderateComment(<?= $reply['id'] ?>, 'approved')">
                                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui Balasan
                                                                                </a>
                                                                                <div class="dropdown-divider"></div>
                                                                            <?php else: ?>
                                                                                <h6 class="dropdown-header text-success">
                                                                                    <span class="fe fe-check-circle fe-12 mr-1"></span>Status: Disetujui
                                                                                </h6>
                                                                                <div class="dropdown-divider"></div>
                                                                            <?php endif; ?>
                                                                            <a class="dropdown-item text-danger" href="#" onclick="openDeleteCommentModal(<?= $reply['id'] ?>)">
                                                                                <span class="fe fe-trash-2 fe-12 mr-2"></span>Hapus
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="mb-2"><?= nl2br(esc($reply['content'])) ?></p>
                                                                <div class="d-flex align-items-center">
                                                                    <button class="btn btn-sm btn-link text-muted p-0 mr-3" onclick="likeComment(<?= $reply['id'] ?>, this)">
                                                                        <span class="fe fe-thumbs-up fe-12 mr-1"></span>Suka (<span class="like-count"><?= $reply['likes_count'] ?? 0 ?></span>)
                                                                    </button>
                                                                    <button class="btn btn-sm btn-link text-muted p-0 mr-3" onclick="aminComment(<?= $reply['id'] ?>, this)">
                                                                        <span class="fe fe-check-circle fe-12 mr-1"></span>Aamiin (<span class="amin-count"><?= $reply['amins_count'] ?? 0 ?></span>)
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>

                                                <!-- Reply Form -->
                                                <div class="collapse mt-3" id="reply-<?= $comment['id'] ?>">
                                                    <div class="card card-body bg-light">
                                                        <form onsubmit="replyComment(event, <?= $comment['id'] ?>)">
                                                            <div class="form-group mb-2">
                                                                <textarea class="form-control form-control-sm" rows="2" placeholder="Tulis balasan..." required name="content"></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-sm btn-primary">Kirim Balasan</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Statistik Komentar -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Statistik</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted d-block">Total Komentar</small>
                                    <strong class="h5"><?= $stats['total_comments'] ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Komentar Hari Ini</small>
                                    <strong class="h5"><?= $stats['comments_today'] ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Total Balasan</small>
                                    <strong class="h5"><?= $stats['total_replies'] ?></strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Komentar Terpopuler</small>
                                    <strong><?= $stats['most_liked'] ?> likes</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Komentar Terbaru -->
                        <div class="card shadow">
                            <div class="card-header">
                                <strong class="card-title">Komentar Terbaru</strong>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentComments)): ?>
                                    <p class="text-muted small mb-0">Belum ada komentar</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recentComments as $recent): ?>
                                            <div class="list-group-item px-0 py-2 border-0">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($recent['is_guest'] || empty($recent['commenter_name'])): ?>
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px;">
                                                            <span class="fe fe-user fe-12 text-white"></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <img src="<?= esc($recent['user_avatar'] ?? base_url('admin-template/assets/avatars/user-default.jpg')) ?>" class="rounded-circle mr-2" alt="Avatar" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
                                                    <?php endif; ?>
                                                    <div class="flex-grow-1">
                                                        <strong class="small"><?= esc($recent['commenter_name'] ?? 'Anonim') ?></strong>
                                                        <p class="small text-muted mb-0"><?= esc(mb_substr($recent['content'], 0, 50)) ?>...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Delete Comment Modal -->
<div id="deleteCommentModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Komentar</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeDeleteCommentModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan menghapus komentar ini.</p>
                <p class="text-danger"><small><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Komentar yang dihapus tidak akan muncul lagi.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDeleteCommentModal()">Batal</button>
                <button type="button" id="dc-submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // CSRF token
    const csrfName = '<?= csrf_token() ?>';
    const csrfValue = '<?= csrf_hash() ?>';

    // Filter function
    function filterDiskusiUrunan(campaignId) {
        const tenantId = document.getElementById('selectTenantDiskusi')?.value || '';
        let url = '<?= base_url('admin/discussions') ?>';
        const params = [];
        if (campaignId) {
            params.push('campaign_id=' + campaignId);
        }
        if (tenantId) {
            params.push('tenant_id=' + tenantId);
        }
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        window.location.href = url;
    }

    // Filter tenant function
    function filterTenantDiskusi(tenantId) {
        const campaignId = document.getElementById('selectUrunanDiskusi')?.value || '';
        let url = '<?= base_url('admin/discussions') ?>';
        const params = [];
        if (tenantId) {
            params.push('tenant_id=' + tenantId);
        }
        if (campaignId) {
            params.push('campaign_id=' + campaignId);
        }
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        window.location.href = url;
    }

    // Sort comments
    function sortComments(sortBy) {
        // TODO: Implement sorting
        console.log('Sort by:', sortBy);
    }

    // Like comment
    function likeComment(commentId, button) {
        fetch('/discussion/comment/' + commentId + '/like', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const countEl = button.querySelector('.like-count');
                if (countEl && data.likes_count !== undefined) {
                    countEl.textContent = data.likes_count;
                }
                
                // Update button state based on server response
                if (data.liked) {
                    button.classList.add('text-primary');
                } else {
                    button.classList.remove('text-primary');
                }
            } else {
                const msg = data.message || 'Gagal memberikan like';
                if (typeof showToast === 'function') { showToast('error', msg); } else { console.error(msg); }
            }
        })
        .catch(err => {
            console.error('Error liking comment:', err);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); }
        });
    }

    // Aamiin comment
    function aminComment(commentId, button) {
        fetch('/discussion/comment/' + commentId + '/amin', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const countEl = button.querySelector('.amin-count');
                if (countEl && data.amins_count !== undefined) {
                    countEl.textContent = data.amins_count;
                }
                
                // Update button state based on server response
                if (data.amined) {
                    button.classList.add('text-primary');
                } else {
                    button.classList.remove('text-primary');
                }
            } else {
                const msg = data.message || 'Gagal memberikan aamiin';
                if (typeof showToast === 'function') { showToast('error', msg); } else { console.error(msg); }
            }
        })
        .catch(err => {
            console.error('Error amining comment:', err);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); }
        });
    }

    // Reply comment
    function replyComment(event, parentId) {
        event.preventDefault();
        const form = event.target;
        const content = form.querySelector('textarea[name="content"]').value;

        if (!content.trim()) {
            if (typeof showToast === 'function') { showToast('warning', 'Komentar tidak boleh kosong'); }
            return;
        }

        // Get campaign_id from the parent comment element
        const commentElement = form.closest('[data-comment-id]');
        let campaignId = null;
        
        if (commentElement) {
            // Try to get from data attribute first
            campaignId = commentElement.getAttribute('data-campaign-id');
            
            // If not found, try to extract from badge
            if (!campaignId) {
                const campaignBadge = commentElement.querySelector('.badge-info');
                if (campaignBadge) {
                    const match = campaignBadge.textContent.match(/#(\d+)/);
                    if (match) {
                        campaignId = match[1];
                    }
                }
            }
        }
        
        // Fallback to selectedCampaignId if available and not 'all'
        if (!campaignId) {
            const selectedId = <?= ($selectedCampaignId !== 'all' && $selectedCampaignId) ? json_encode((int)$selectedCampaignId) : 'null' ?>;
            if (selectedId) {
                campaignId = selectedId;
            }
        }

        if (!campaignId) {
            if (typeof showToast === 'function') { showToast('error', 'Tidak dapat menentukan campaign. Silakan refresh halaman.'); }
            console.error('Campaign ID not found for reply');
            return;
        }

        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('campaign_id', campaignId);
        formData.append('parent_id', parentId);
        formData.append('content', content);

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';

        fetch('<?= base_url('discussion/comment') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') { 
                    showToast('success', 'Balasan berhasil dikirim'); 
                }
                // Reload after short delay to show toast
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                const msg = data.message || 'Gagal mengirim balasan';
                if (typeof showToast === 'function') { 
                    showToast('error', msg); 
                } else { 
                    alert(msg); 
                }
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error submitting reply:', error);
            const errorMsg = 'Terjadi kesalahan saat mengirim balasan. Silakan coba lagi.';
            if (typeof showToast === 'function') { 
                showToast('error', errorMsg); 
            } else { 
                alert(errorMsg); 
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    // Pin comment
    // Moderate comment
    function moderateComment(commentId, status) {
        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('status', status);
        
        fetch('/discussion/comment/' + commentId + '/moderate', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const statusText = status === 'approved' ? 'disetujui' : 'ditolak';
                if (typeof showToast === 'function') { 
                    showToast('success', 'Komentar berhasil ' + statusText);
                }
                // Reload page after a short delay to show toast message
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                const msg = data.message || 'Gagal memoderasi komentar';
                if (typeof showToast === 'function') { showToast('error', msg); } else { alert(msg); }
            }
        })
        .catch(err => {
            console.error('Error moderating comment:', err);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); } else { alert('Terjadi kesalahan. Silakan coba lagi.'); }
        });
    }

    function pinComment(commentId, pin) {
        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('pin', pin);

        fetch('/discussion/comment/' + commentId + '/pin', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI tanpa reload
                const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                if (commentElement) {
                    // Update badge pinned
                    const badgeContainer = commentElement.querySelector('h6.mt-0');
                    if (badgeContainer) {
                        let pinnedBadge = badgeContainer.querySelector('.badge-primary');
                        if (pin === true || pin === 'true') {
                            // Add pinned badge if not exists
                            if (!pinnedBadge) {
                                const badge = document.createElement('span');
                                badge.className = 'badge badge-primary badge-sm ml-1';
                                badge.textContent = 'Pinned';
                                badgeContainer.appendChild(badge);
                            }
                        } else {
                            // Remove pinned badge
                            if (pinnedBadge) {
                                pinnedBadge.remove();
                            }
                        }
                    }
                    
                    // Update dropdown menu text
                    const pinMenuItem = commentElement.querySelector('a[onclick*="pinComment"]');
                    if (pinMenuItem) {
                        const icon = pinMenuItem.querySelector('span.fe');
                        const text = pinMenuItem.childNodes[pinMenuItem.childNodes.length - 1];
                        if (pin === true || pin === 'true') {
                            if (icon) icon.className = 'fe fe-unlock fe-12 mr-2';
                            if (text) text.textContent = ' Unpin';
                        } else {
                            if (icon) icon.className = 'fe fe-lock fe-12 mr-2';
                            if (text) text.textContent = ' Pin';
                        }
                    }
                }
                
                const msg = pin === true || pin === 'true' ? 'Komentar berhasil dipin' : 'Pin komentar dihapus';
                if (typeof showToast === 'function') { showToast('success', msg); }
            } else {
                const msg = data.message || 'Gagal memproses';
                if (typeof showToast === 'function') { showToast('error', msg); } else { console.error(msg); }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); }
        });
    }

    // Delete Comment Modal Functions
    function openDeleteCommentModal(commentId) {
        const modal = $('#deleteCommentModal');
        window.currentDeleteCommentId = commentId;
        modal.modal('show');
    }

    function closeDeleteCommentModal() {
        $('#deleteCommentModal').modal('hide');
    }

    // Submit Delete Comment
    document.getElementById('dc-submit').addEventListener('click', function() {
        const commentId = window.currentDeleteCommentId;
        if (!commentId) return;
        
        closeDeleteCommentModal();
        
        const formData = new FormData();
        formData.append(csrfName, csrfValue);

        fetch('<?= base_url('discussion/comment') ?>/' + commentId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                const msg = data.message || 'Gagal menghapus komentar';
                if (typeof showToast === 'function') { showToast('error', msg); } else { console.error(msg); }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); }
        });
    });

    // Initialize Select2
    (function() {
        function initSelect2() {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#selectTenantDiskusi').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
                $('#selectUrunanDiskusi').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            } else {
                setTimeout(initSelect2, 500);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initSelect2, 300);
            });
        } else {
            setTimeout(initSelect2, 300);
        }
    })();
</script>
<?= $this->endSection() ?>

