<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
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
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Support</h2>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahTicket">
                        <span class="fe fe-plus fe-12 mr-1"></span>Buat Tiket Baru
                    </button>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Tiket</small>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                            <small class="text-success"><span class="fe fe-arrow-up fe-12"></span> <?= $stats['new_today'] ?> baru</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Tiket Terbuka</small>
                            <h3 class="mb-0 text-warning"><?= $stats['open'] ?></h3>
                            <small class="text-muted">Menunggu respons</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Tiket Selesai</small>
                            <h3 class="mb-0 text-success"><?= $stats['resolved'] ?></h3>
                            <small class="text-muted"><?= $stats['total'] > 0 ? number_format(($stats['resolved'] / $stats['total']) * 100, 1) : 0 ?>%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Rata-rata Respon</small>
                            <h3 class="mb-0"><?= $stats['average_response'] ?></h3>
                            <small class="text-info">Bulan ini</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <form id="filterForm" method="GET" action="/tenant/helpdesk">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="filterStatus" class="small text-muted mb-1">Status</label>
                                        <select id="filterStatus" name="status" class="form-control form-control-sm select2">
                                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                                            <option value="open" <?= $statusFilter === 'open' ? 'selected' : '' ?>>Terbuka</option>
                                            <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>Sedang Diproses</option>
                                            <option value="resolved" <?= $statusFilter === 'resolved' ? 'selected' : '' ?>>Selesai</option>
                                            <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>Ditutup</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="filterPrioritas" class="small text-muted mb-1">Prioritas</label>
                                        <select id="filterPrioritas" name="priority" class="form-control form-control-sm select2">
                                            <option value="all" <?= $priorityFilter === 'all' ? 'selected' : '' ?>>Semua Prioritas</option>
                                            <option value="low" <?= $priorityFilter === 'low' ? 'selected' : '' ?>>Rendah</option>
                                            <option value="medium" <?= $priorityFilter === 'medium' ? 'selected' : '' ?>>Sedang</option>
                                            <option value="high" <?= $priorityFilter === 'high' ? 'selected' : '' ?>>Tinggi</option>
                                            <option value="urgent" <?= $priorityFilter === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="filterKategori" class="small text-muted mb-1">Kategori</label>
                                        <select id="filterKategori" name="category" class="form-control form-control-sm select2">
                                            <option value="all" <?= $categoryFilter === 'all' ? 'selected' : '' ?>>Semua Kategori</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                                    <?= esc($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="filterPeriode" class="small text-muted mb-1">Periode</label>
                                        <select id="filterPeriode" name="period" class="form-control form-control-sm select2">
                                            <option value="all" <?= $periodFilter === 'all' ? 'selected' : '' ?>>Semua Periode</option>
                                            <option value="today" <?= $periodFilter === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                            <option value="week" <?= $periodFilter === 'week' ? 'selected' : '' ?>>Minggu Ini</option>
                                            <option value="month" <?= $periodFilter === 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-12 text-right">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <span class="fe fe-filter fe-12 mr-1"></span>Terapkan Filter
                                        </button>
                                        <a href="/tenant/helpdesk" class="btn btn-sm btn-outline-secondary">
                                            <span class="fe fe-refresh-cw fe-12 mr-1"></span>Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Tiket -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Tiket Support</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatables table-hover" id="dataTable-support">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Tiket ID</th>
                                            <th>Subjek</th>
                                            <th>Kategori</th>
                                            <th>Prioritas</th>
                                            <th>Status</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tickets)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <p class="text-muted mb-0">Belum ada tiket support. <a href="#" data-toggle="modal" data-target="#modalTambahTicket" class="text-primary">Buat tiket baru</a> untuk memulai.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($tickets as $ticket): ?>
                                                <tr>
                                                    <td>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="ticket-<?= $ticket['id'] ?>">
                                                            <label class="custom-control-label" for="ticket-<?= $ticket['id'] ?>"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong><?= esc($ticket['ticket_number']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <strong><?= esc($ticket['subject']) ?></strong>
                                                        <small class="d-block text-muted"><?= esc(mb_substr($ticket['description'], 0, 60)) ?>...</small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $categoryName = '-';
                                                        foreach ($categories as $cat) {
                                                            if ($cat['id'] == $ticket['category_id']) {
                                                                $categoryName = $cat['name'];
                                                                break;
                                                            }
                                                        }
                                                        ?>
                                                        <span class="badge badge-info"><?= esc($categoryName) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $priorityBadge = [
                                                            'low' => 'badge-secondary',
                                                            'medium' => 'badge-info',
                                                            'high' => 'badge-warning',
                                                            'urgent' => 'badge-danger'
                                                        ];
                                                        $priorityLabel = [
                                                            'low' => 'Rendah',
                                                            'medium' => 'Sedang',
                                                            'high' => 'Tinggi',
                                                            'urgent' => 'Urgent'
                                                        ];
                                                        $badgeClass = $priorityBadge[$ticket['priority']] ?? 'badge-secondary';
                                                        $label = $priorityLabel[$ticket['priority']] ?? ucfirst($ticket['priority']);
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusBadge = [
                                                            'open' => 'badge-warning',
                                                            'in_progress' => 'badge-primary',
                                                            'resolved' => 'badge-success',
                                                            'closed' => 'badge-secondary'
                                                        ];
                                                        $statusLabel = [
                                                            'open' => 'Terbuka',
                                                            'in_progress' => 'Sedang Diproses',
                                                            'resolved' => 'Selesai',
                                                            'closed' => 'Ditutup'
                                                        ];
                                                        $statusClass = $statusBadge[$ticket['status']] ?? 'badge-secondary';
                                                        $statusText = $statusLabel[$ticket['status']] ?? ucfirst($ticket['status']);
                                                        ?>
                                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                                    </td>
                                                    <td><?= date('d M Y, H:i', strtotime($ticket['created_at'])) ?> WIB</td>
                                                    <td>
                                                        <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text-muted sr-only">Action</span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="<?= base_url('tenant/helpdesk/' . $ticket['id']) ?>">
                                                                <span class="fe fe-eye fe-12 mr-2"></span>Lihat Detail
                                                            </a>
                                                            <a class="dropdown-item" href="#" onclick="replyTicket(<?= $ticket['id'] ?>, '<?= esc($ticket['ticket_number'], 'js') ?>')">
                                                                <span class="fe fe-edit fe-12 mr-2"></span>Balas
                                                            </a>
                                                            <?php if ($ticket['status'] !== 'resolved' && $ticket['status'] !== 'closed'): ?>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item" href="#" onclick="openResolveTicketModal(<?= $ticket['id'] ?>, '<?= esc($ticket['subject'] ?? 'Tiket', 'js') ?>')">
                                                                    <span class="fe fe-check fe-12 mr-2"></span>Tandai Selesai
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tiket -->
<div class="modal fade" id="modalTambahTicket" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Tiket Support Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahTicket">
                    <div class="form-group">
                        <label>Subjek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="subject" placeholder="Contoh: Masalah Login ke Dashboard" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select class="form-control" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Prioritas</label>
                            <select class="form-control" name="priority">
                                <option value="low">Rendah</option>
                                <option value="medium" selected>Sedang</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Masalah <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="5" placeholder="Jelaskan masalah atau pertanyaan Anda secara detail..." required></textarea>
                        <small class="form-text text-muted">Semakin detail deskripsi, semakin cepat kami dapat membantu</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitTicket()">Kirim Tiket</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Tiket -->
<div class="modal fade" id="modalDetailTicket" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTicketTitle">Detail Tiket</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailTicketBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Resolve Ticket Modal -->
<div id="resolveTicketModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tandai Tiket Selesai</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeResolveTicketModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan menandai tiket berikut sebagai selesai:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between">
                        <span>Subjek Tiket:</span>
                        <span id="rt-ticket-subject" class="font-weight-bold">-</span>
                    </div>
                </div>
                <p class="text-info"><small><strong>Catatan:</strong> Tiket yang sudah ditandai sebagai selesai tidak dapat diubah kembali.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeResolveTicketModal()">Batal</button>
                <button type="button" id="rt-submit" class="btn btn-primary">Ya, Tandai Selesai</button>
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

    // Initialize Select2
    (function() {
        function initSelect2() {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#filterStatus, #filterPrioritas, #filterKategori, #filterPeriode').select2({
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

    // Initialize DataTable
    (function() {
        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-support').length) {
                if ($.fn.dataTable.isDataTable('#dataTable-support')) {
                    return;
                }
                
                $('#dataTable-support').DataTable({
                    autoWidth: true,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "order": [[6, "desc"]], // Sort by tanggal dibuat descending
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
                        { "targets": [0, 7], "orderable": false }
                    ]
                });
            } else {
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

    // Submit ticket
    function submitTicket() {
        const form = document.getElementById('formTambahTicket');
        const formData = new FormData(form);
        formData.append(csrfName, csrfValue);

        fetch('/helpdesk/ticket/create', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#modalTambahTicket').modal('hide');
                location.reload();
            } else {
                console.error('Error:', data.message || 'Gagal membuat tiket');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // View ticket detail
    function viewTicket(ticketNumber) {
        fetch('/helpdesk/ticket/' + ticketNumber, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ticket = data.data.ticket;
                const replies = data.data.replies;
                
                let html = `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">${ticket.subject}</h6>
                                <small class="text-muted">Oleh: <?= esc($tenantName) ?> | ${new Date(ticket.created_at).toLocaleString('id-ID')}</small>
                            </div>
                            <div>
                                <span class="badge badge-info mr-1">Kategori</span>
                                <span class="badge badge-warning">${ticket.status}</span>
                            </div>
                        </div>
                        <p class="mb-0">${ticket.description}</p>
                    </div>
                    <hr>
                    <div class="timeline-support" style="max-height: 400px; overflow-y: auto;">
                `;
                
                replies.forEach(reply => {
                    // Determine styling based on user_type
                    const isAdminReply = reply.user_type === 'admin';
                    const isInternal = reply.is_internal === true || reply.is_internal === 1;
                    
                    let borderClass, bgClass, badgeClass, badgeText;
                    
                    if (isInternal) {
                        borderClass = 'border-warning';
                        bgClass = 'bg-warning-light';
                        badgeClass = 'badge-warning';
                        badgeText = 'Internal';
                    } else if (isAdminReply) {
                        borderClass = 'border-success';
                        bgClass = 'bg-light';
                        badgeClass = 'badge-success';
                        badgeText = 'Admin';
                    } else {
                        borderClass = 'border-info';
                        bgClass = '';
                        badgeClass = 'badge-info';
                        badgeText = 'Penggalang';
                    }
                    
                    html += `
                        <div class="border-left ${borderClass} ${bgClass} pl-3 py-3 mb-3 rounded-right" style="border-left-width: 4px !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <strong class="text-dark">${reply.user_type === 'admin' ? 'Admin Support' : '<?= esc($tenantName) ?>'}</strong>
                                    <span class="badge ${badgeClass}">${badgeText}</span>
                                </div>
                                <small class="text-muted">${new Date(reply.created_at).toLocaleString('id-ID')}</small>
                            </div>
                            <p class="mb-0" style="white-space: pre-wrap;">${reply.message}</p>
                        </div>
                    `;
                });
                
                html += `
                    </div>
                    <hr>
                    <div id="replyFormContainer" style="display: none;">
                        <form id="formBalasanTicket">
                            <input type="hidden" name="ticket_id" value="${ticket.id}">
                            <div class="form-group">
                                <label>Balasan</label>
                                <textarea class="form-control" name="message" id="replyMessage" rows="3" placeholder="Tulis balasan Anda di sini..." required></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <div class="d-flex" style="gap: 10px;">
                                    <button type="button" class="btn btn-primary" onclick="submitReply(${ticket.id})">
                                        <span class="fe fe-send fe-12 mr-1"></span>Kirim Balasan
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleReplyForm()">
                                        <span class="fe fe-x fe-12 mr-1"></span>Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="replyButtonContainer" class="text-center">
                        <button type="button" class="btn btn-primary" onclick="toggleReplyForm()">
                            <span class="fe fe-message-circle fe-12 mr-1"></span>Balas Tiket
                        </button>
                    </div>
                `;
                
                document.getElementById('detailTicketTitle').textContent = 'Detail Tiket ' + ticket.ticket_number;
                document.getElementById('detailTicketBody').innerHTML = html;
                $('#modalDetailTicket').modal('show');
            } else {
                console.error('Error:', data.message || 'Tiket tidak ditemukan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Toggle reply form
    function toggleReplyForm() {
        const formContainer = document.getElementById('replyFormContainer');
        const buttonContainer = document.getElementById('replyButtonContainer');
        
        if (formContainer && buttonContainer) {
            if (formContainer.style.display === 'none') {
                formContainer.style.display = 'block';
                buttonContainer.style.display = 'none';
                // Focus on textarea
                setTimeout(() => {
                    const textarea = document.getElementById('replyMessage');
                    if (textarea) textarea.focus();
                }, 100);
            } else {
                formContainer.style.display = 'none';
                buttonContainer.style.display = 'block';
                // Clear form
                const form = document.getElementById('formBalasanTicket');
                if (form) form.reset();
            }
        }
    }

    // Reply ticket
    function replyTicket(ticketId, ticketNumber) {
        viewTicket(ticketNumber);
        // Show reply form after modal opens
        setTimeout(() => {
            toggleReplyForm();
        }, 300);
    }

    // Submit reply
    function submitReply(ticketId) {
        const form = document.getElementById('formBalasanTicket');
        const messageInput = form.querySelector('textarea[name="message"]');
        const message = messageInput ? messageInput.value.trim() : '';

        if (!message) {
            console.error('Pesan tidak boleh kosong');
            return;
        }

        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('message', message);

        fetch('/helpdesk/ticket/' + ticketId + '/reply', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload to show new reply
                const ticketNumber = document.getElementById('detailTicketTitle').textContent.replace('Detail Tiket ', '');
                viewTicket(ticketNumber);
                toggleReplyForm(); // Hide form
            } else {
                console.error('Error:', data.message || 'Gagal mengirim balasan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Resolve Ticket Modal Functions
    function openResolveTicketModal(ticketId, ticketSubject) {
        const modal = $('#resolveTicketModal');
        window.currentResolveTicketId = ticketId;
        document.getElementById('rt-ticket-subject').textContent = ticketSubject || '-';
        modal.modal('show');
    }

    function closeResolveTicketModal() {
        $('#resolveTicketModal').modal('hide');
    }

    // Submit Resolve Ticket
    document.getElementById('rt-submit').addEventListener('click', function() {
        const ticketId = window.currentResolveTicketId;
        if (!ticketId) return;
        
        closeResolveTicketModal();
        
        const formData = new FormData();
        formData.append(csrfName, csrfValue);
        formData.append('status', 'resolved');

        fetch('/helpdesk/ticket/' + ticketId + '/status', {
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
                console.error('Error:', data.message || 'Gagal mengupdate status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
</script>
<?= $this->endSection() ?>

