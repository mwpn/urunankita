<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Detail Ticket') ?></title>
<style>
    .bg-warning-light {
        background-color: #fff3cd !important;
    }
    .border-left.border-success {
        border-left-width: 4px !important;
    }
    .border-left.border-info {
        border-left-width: 4px !important;
    }
    .border-left.border-warning {
        border-left-width: 4px !important;
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
                    <h2 class="h5 page-title mb-0">Detail Ticket</h2>
                    <small class="text-muted">Ticket #<?= esc($ticket['ticket_number']) ?></small>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('admin/helpdesk') ?>" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-arrow-left fe-12 mr-1"></span>Kembali
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

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8 mb-4">
                    <!-- Ticket Info -->
                    <div class="card shadow mb-4">
                        <div class="card-header d-flex justify-content-between align-items-start">
                            <h3 class="h6 mb-0"><?= esc($ticket['subject']) ?></h3>
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
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0" style="white-space: pre-wrap;"><?= esc($ticket['description']) ?></p>
                            <?php if (!empty($ticket['attachments']) && is_array($ticket['attachments'])): ?>
                                <div class="mt-4 pt-4 border-top">
                                    <h5 class="mb-2">Attachments</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($ticket['attachments'] as $attachment): ?>
                                            <a href="<?= esc($attachment['url'] ?? '#') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fe fe-download fe-12 mr-1"></i><?= esc($attachment['name'] ?? 'File') ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Replies -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h3 class="h6 mb-0">Replies</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($replies)): ?>
                                <p class="text-muted mb-0">Belum ada reply.</p>
                            <?php else: ?>
                                <?php foreach ($replies as $reply): ?>
                                    <?php
                                    // Determine if admin reply
                                    $userType = $reply['user_type'] ?? '';
                                    $isAdminReply = false;
                                    
                                    // Check user_type first
                                    if ($userType === 'admin') {
                                        $isAdminReply = true;
                                    } else if (empty($userType) && !empty($reply['user_id'])) {
                                        // Fallback: check user role if user_type is empty
                                        $db = \Config\Database::connect();
                                        $user = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                                        if ($user) {
                                            $userRole = $user['role'] ?? null;
                                            if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
                                                $isAdminReply = true;
                                            }
                                        }
                                    }
                                    
                                    $isInternal = !empty($reply['is_internal']);
                                    
                                    // Styling berbeda untuk admin vs tenant
                                    if ($isAdminReply) {
                                        $borderClass = 'border-success';
                                        $bgClass = 'bg-light';
                                        $badgeClass = 'badge-success';
                                        $badgeText = 'Admin';
                                    } else {
                                        $borderClass = 'border-info';
                                        $bgClass = '';
                                        $badgeClass = 'badge-info';
                                        $badgeText = 'Penggalang';
                                    }
                                    
                                    // Jika internal, override dengan warna berbeda
                                    if ($isInternal) {
                                        $borderClass = 'border-warning';
                                        $bgClass = 'bg-warning-light';
                                        $badgeClass = 'badge-warning';
                                        $badgeText = 'Internal';
                                    }
                                    ?>
                                    <div class="border-left <?= $borderClass ?> <?= $bgClass ?> pl-3 py-3 mb-3 rounded-right">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center" style="gap: 8px;">
                                                <strong class="text-dark"><?= esc($reply['user_name'] ?? 'User') ?></strong>
                                                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
                                        </div>
                                        <p class="mb-0" style="white-space: pre-wrap;"><?= esc($reply['message']) ?></p>
                                        <?php if (!empty($reply['attachments']) && is_array($reply['attachments'])): ?>
                                            <div class="mt-2">
                                                <?php foreach ($reply['attachments'] as $attachment): ?>
                                                    <a href="<?= esc($attachment['url'] ?? '#') ?>" target="_blank" class="btn btn-sm btn-outline-primary mr-1">
                                                        <i class="fe fe-download fe-12 mr-1"></i><?= esc($attachment['name'] ?? 'File') ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h3 class="h6 mb-0">Balas Ticket</h3>
                        </div>
                        <div class="card-body">
                            <form id="replyForm" method="post" action="<?= base_url('admin/helpdesk/' . $ticket['id'] . '/reply') ?>">
                                <?= csrf_field() ?>
                                <div class="form-group">
                                    <label for="replyMessage" class="form-label">Pesan</label>
                                    <textarea name="message" id="replyMessage" class="form-control" rows="4" placeholder="Tulis balasan Anda di sini..." required></textarea>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="isInternal" name="is_internal" value="1">
                                        <label class="custom-control-label" for="isInternal">
                                            Balasan Internal (hanya terlihat oleh admin)
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex" style="gap: 10px;">
                                    <button type="submit" class="btn btn-primary" id="submitReplyBtn">
                                        <span class="fe fe-send fe-12 mr-1"></span>Kirim Balasan
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('replyForm').reset();">
                                        <span class="fe fe-x fe-12 mr-1"></span>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Ticket Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h3 class="h6 mb-0">Detail</h3>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <dt class="text-muted small">Status</dt>
                                <dd class="mb-3">
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
                                </dd>
                                <dt class="text-muted small">Penggalang</dt>
                                <dd class="mb-3"><?= esc($ticket['tenant_name'] ?? '-') ?></dd>
                                <dt class="text-muted small">Dibuat</dt>
                                <dd class="mb-3"><?= date('d M Y H:i', strtotime($ticket['created_at'])) ?></dd>
                                <?php if (!empty($ticket['last_replied_at'])): ?>
                                    <dt class="text-muted small">Last Reply</dt>
                                    <dd class="mb-3"><?= date('d M Y H:i', strtotime($ticket['last_replied_at'])) ?></dd>
                                <?php endif; ?>
                                <?php if (!empty($ticket['resolved_at'])): ?>
                                    <dt class="text-muted small">Resolved At</dt>
                                    <dd class="mb-0"><?= date('d M Y H:i', strtotime($ticket['resolved_at'])) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow">
                        <div class="card-header">
                            <h3 class="h6 mb-0">Aksi</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?= base_url('admin/helpdesk/' . $ticket['id'] . '/status') ?>" id="statusForm">
                                <?= csrf_field() ?>
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="open" <?= ($ticket['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="in_progress" <?= ($ticket['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="resolved" <?= ($ticket['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="closed" <?= ($ticket['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block" id="updateStatusBtn">
                                    Update Status
                                </button>
                            </form>
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
    // Handle reply form submission
    document.getElementById('replyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitReplyBtn');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Mengirim...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Balasan berhasil dikirim');
                location.reload();
            } else {
                showNotification('error', data.message || 'Gagal mengirim balasan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Terjadi kesalahan saat mengirim balasan');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Handle status form submission
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('updateStatusBtn');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...';
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Status berhasil diperbarui');
                location.reload();
            } else {
                showNotification('error', data.message || 'Gagal memperbarui status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Terjadi kesalahan saat memperbarui status');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
</script>
<?= $this->endSection() ?>
