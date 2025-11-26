<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <?php if ($selectedCampaign): ?>
                        <a href="/tenant/campaigns/<?= $selectedCampaign['id'] ?>" class="btn btn-sm btn-outline-secondary mb-2">
                            <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali ke Detail Urunan
                        </a>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h2 class="h5 page-title mb-0">Diskusi & Komentar</h2>
                            <small class="text-muted" id="urunan-diskusi-title">
                                <?php if ($selectedCampaign): ?>
                                    <?= esc($selectedCampaign['title']) ?> - ID: #<?= $selectedCampaign['id'] ?>
                                <?php else: ?>
                                    Pilih urunan untuk melihat diskusi
                                <?php endif; ?>
                            </small>
                        </div>
                        <div style="min-width: 300px;" class="text-right">
                            <label for="selectUrunanDiskusi" class="small text-muted mb-1 d-block">Pilih Urunan:</label>
                            <select id="selectUrunanDiskusi" class="form-control form-control-sm select2" onchange="filterDiskusiUrunan(this.value)">
                                <?php if (empty($campaigns)): ?>
                                    <option value="">Tidak ada urunan</option>
                                <?php else: ?>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <option value="<?= $campaign['id'] ?>" <?= ($selectedCampaignId == $campaign['id']) ? 'selected' : '' ?>>
                                            <?= esc($campaign['title']) ?> - #<?= $campaign['id'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($campaigns)): ?>
                <div class="alert alert-info">
                    <span class="fe fe-info fe-16 mr-2"></span>
                    Belum ada urunan. <a href="/tenant/campaigns/create" class="alert-link">Buat urunan baru</a> terlebih dahulu.
                </div>
            <?php else: ?>
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
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="media mb-4 pb-4 border-bottom" data-comment-id="<?= $comment['id'] ?>">
                                            <?php if ($comment['is_guest'] || empty($comment['commenter_name'])): ?>
                                                <div class="mr-3">
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <span class="fe fe-user fe-16 text-white"></span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>" class="mr-3 rounded-circle" alt="Avatar" style="width: 48px; height: 48px;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
                                            <?php endif; ?>
                                            <div class="media-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mt-0 mb-0">
                                                            <?= esc($comment['commenter_name'] ?? 'Anonim') ?>
                                                            <?php if ($comment['is_pinned']): ?>
                                                                <span class="badge badge-primary badge-sm ml-1">Pinned</span>
                                                            <?php endif; ?>
                                                            <?php if ($comment['status'] === 'pending'): ?>
                                                                <span class="badge badge-warning badge-sm ml-1">Menunggu Moderasi</span>
                                                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                                                <span class="badge badge-danger badge-sm ml-1">Ditolak</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?> WIB</small>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link text-muted" type="button" data-toggle="dropdown">
                                                            <span class="fe fe-more-vertical fe-12"></span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <?php if ($comment['status'] === 'pending'): ?>
                                                                <a class="dropdown-item text-success" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'approved')">
                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui
                                                                </a>
                                                                <a class="dropdown-item text-danger" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'rejected')">
                                                                    <span class="fe fe-x fe-12 mr-2"></span>Tolak
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                                                <a class="dropdown-item text-success" href="#" onclick="moderateComment(<?= $comment['id'] ?>, 'approved')">
                                                                    <span class="fe fe-check fe-12 mr-2"></span>Setujui
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                            <?php endif; ?>
                                                            <a class="dropdown-item" href="#" onclick="pinComment(<?= $comment['id'] ?>, <?= $comment['is_pinned'] ? 'false' : 'true' ?>)">
                                                                <span class="fe fe-<?= $comment['is_pinned'] ? 'unlock' : 'lock' ?> fe-12 mr-2"></span>
                                                                <?= $comment['is_pinned'] ? 'Unpin' : 'Pin' ?>
                                                            </a>
                                                            <a class="dropdown-item" href="#" onclick="openDeleteCommentModal(<?= $comment['id'] ?>)">
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
                                                                <img src="<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>" class="mr-3 rounded-circle" alt="Avatar" style="width: 40px; height: 40px;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
                                                            <?php endif; ?>
                                                            <div class="media-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mt-0 mb-0"><?= esc($reply['commenter_name'] ?? 'Anonim') ?></h6>
                                                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($reply['created_at'])) ?> WIB</small>
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
                                                        <img src="<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>" class="rounded-circle mr-2" alt="Avatar" style="width: 32px; height: 32px;" onerror="this.src='<?= base_url('admin-template/assets/avatars/user-default.jpg') ?>'">
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
        if (!campaignId) return;
        window.location.href = '/tenant/discussions?campaign_id=' + campaignId;
    }

    // Sort comments
    function sortComments(sortBy) {
        // TODO: Implement sorting
        console.log('Sort by:', sortBy);
    }

    // Like comment
    function likeComment(commentId, button) {
        console.log('Like clicked for comment:', commentId);
        
        fetch('/discussion/comment/' + commentId + '/like', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Like response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Like response data:', data);
            if (data.success && data.data) {
                const countEl = button.querySelector('.like-count');
                const likesCount = data.data.likes_count ?? data.likes_count ?? 0;
                const isLiked = data.data.liked ?? data.liked ?? false;
                
                console.log('Found count element:', countEl, 'New count:', likesCount);
                if (countEl && likesCount !== undefined) {
                    countEl.textContent = likesCount;
                    console.log('Updated count to:', countEl.textContent);
                } else {
                    console.warn('Count element not found or likes_count undefined');
                }

                // Update button state
                if (isLiked) {
                    button.classList.add('text-primary');
                    button.classList.remove('text-muted');
                } else {
                    button.classList.remove('text-primary');
                    button.classList.add('text-muted');
                }
                
                // Show success message
                if (typeof showToast === 'function') { 
                    showToast('success', data.message || 'Like berhasil'); 
                }
            } else {
                const msg = data.message || 'Gagal memberikan like';
                console.error('Like error:', msg);
                if (typeof showToast === 'function') { showToast('error', msg); } else { alert(msg); }
            }
        })
        .catch(error => {
            console.error('Error liking comment:', error);
            const errorMsg = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
            if (typeof showToast === 'function') { showToast('error', errorMsg); } else { alert(errorMsg); }
        });
    }

    // Aamiin comment
    function aminComment(commentId, button) {
        console.log('Aamiin clicked for comment:', commentId);
        
        fetch('/discussion/comment/' + commentId + '/amin', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Aamiin response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Aamiin response data:', data);
            if (data.success && data.data) {
                const countEl = button.querySelector('.amin-count');
                const aminsCount = data.data.amins_count ?? data.amins_count ?? 0;
                const isAmined = data.data.amined ?? data.amined ?? false;
                
                console.log('Found count element:', countEl, 'New count:', aminsCount);
                if (countEl && aminsCount !== undefined) {
                    // Update hanya angka di dalam span, format: "Aamiin (0)"
                    countEl.textContent = aminsCount;
                    console.log('Updated count to:', countEl.textContent);
                } else {
                    console.warn('Count element not found or amins_count undefined');
                }

                // Update button state
                if (isAmined) {
                    button.classList.add('text-primary');
                    button.classList.remove('text-muted');
                } else {
                    button.classList.remove('text-primary');
                    button.classList.add('text-muted');
                }
                
                // Show success message
                if (typeof showToast === 'function') { 
                    showToast('success', data.message || 'Aamiin berhasil'); 
                }
            } else {
                const msg = data.message || 'Gagal memberikan aamiin';
                console.error('Aamiin error:', msg);
                if (typeof showToast === 'function') { showToast('error', msg); } else { alert(msg); }
            }
        })
        .catch(error => {
            console.error('Error amining comment:', error);
            const errorMsg = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
            if (typeof showToast === 'function') { showToast('error', errorMsg); } else { alert(errorMsg); }
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

        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('campaign_id', <?= $selectedCampaignId ?? 'null' ?>);
        formData.append('parent_id', parentId);
        formData.append('content', content);

        fetch('/discussion/comment', {
            method: 'POST',
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
                const msg = data.message || 'Gagal mengirim balasan';
                if (typeof showToast === 'function') { showToast('error', msg); } else { console.error(msg); }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') { showToast('error', 'Terjadi kesalahan'); }
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
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated status
                location.reload();
            } else {
                alert(data.message || 'Gagal memoderasi komentar');
            }
        })
        .catch(err => {
            console.error('Error moderating comment:', err);
            alert('Terjadi kesalahan. Silakan coba lagi.');
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
                location.reload();
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

        fetch('/discussion/comment/' + commentId, {
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

