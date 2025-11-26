<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Donasi Masuk</h2>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <div>
                            <small class="text-muted" id="urunan-donasi-title">Semua Urunan</small>
                        </div>
                        <div style="min-width: 300px;" class="text-right">
                            <label for="selectUrunanDonasi" class="small text-muted mb-1 d-block">Filter Urunan:</label>
                            <select id="selectUrunanDonasi" class="form-control form-control-sm select2" onchange="filterDonasiUrunan(this.value)">
                                <option value="all" selected>Semua Urunan</option>
                                <?php foreach ($campaigns ?? [] as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= ((string)($campaign_id_filter ?? '') === (string)$c['id']) ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Donasi</small>
                            <h3 class="mb-0" id="stat-total">Rp <?= number_format($total_donations ?? 0, 0, ',', '.') ?></h3>
                            <small class="text-success"><span class="fe fe-arrow-up fe-12"></span> Aktif</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Jumlah Donatur</small>
                            <h3 class="mb-0" id="stat-donatur"><?= number_format($total_donors ?? 0, 0, ',', '.') ?></h3>
                            <small class="text-info"><span class="fe fe-users fe-12"></span> Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Donasi Hari Ini</small>
                            <h3 class="mb-0 text-success" id="stat-hari-ini">Rp <?= number_format($today_donations ?? 0, 0, ',', '.') ?></h3>
                            <small class="text-muted"><?= $today_count ?? 0 ?> donasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Rata-rata Donasi</small>
                            <h3 class="mb-0" id="stat-rata">Rp <?= number_format($avg_donation ?? 0, 0, ',', '.') ?></h3>
                            <small class="text-muted">Per donatur</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Donasi -->
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <strong class="card-title">Daftar Donasi</strong>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <span class="fe fe-download fe-12 mr-1"></span>Export Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($donations)): ?>
                                <div class="text-center py-5">
                                    <p class="text-muted">Belum ada donasi.</p>
                                </div>
                            <?php else: ?>
                                <table class="table datatables" id="dataTable-donasi">
                                    <thead>
                                        <tr>
                                            <th>Tanggal & Waktu</th>
                                            <th>Donatur</th>
                                            <th>Urunan</th>
                                            <th>Jumlah</th>
                                            <th>Metode</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($donations as $d): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($d['created_at'])): ?>
                                                        <div class="text-nowrap"><?= date('d M Y', strtotime($d['created_at'])) ?></div>
                                                        <small class="text-muted"><?= date('H:i', strtotime($d['created_at'])) ?> WIB</small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <strong><?= esc($d['donor_name'] ?? '-') ?></strong>
                                                        <?php if (!empty($d['is_anonymous'])): ?>
                                                            <span class="badge badge-info badge-sm ml-2">Anonim</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($d['donor_email'])): ?>
                                                        <small class="d-block text-muted"><?= esc($d['donor_email']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($d['donor_phone'])): ?>
                                                        <small class="d-block text-muted"><?= esc($d['donor_phone']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary"><?= esc($d['campaign_title'] ?? '-') ?></span>
                                                    <small class="d-block text-muted">#<?= str_pad($d['campaign_id'] ?? 0, 6, '0', STR_PAD_LEFT) ?></small>
                                                </td>
                                                <td><strong class="text-success">Rp <?= number_format((float)($d['amount'] ?? 0), 0, ',', '.') ?></strong></td>
                                                <td>
                                                    <span class="badge badge-info"><?= esc($d['payment_method'] ?? '-') ?></span>
                                                </td>
                                                <td>
                                                    <?php $status = $d['payment_status'] ?? 'pending'; ?>
                                                    <?php if ($status === 'paid'): ?>
                                                        <span class="badge badge-success">Berhasil</span>
                                                    <?php elseif ($status === 'failed'): ?>
                                                        <span class="badge badge-danger">Gagal</span>
                                                    <?php elseif ($status === 'cancelled'): ?>
                                                        <span class="badge badge-secondary">Dibatalkan</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?= esc($d['message'] ?? '-') ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($status === 'pending'): ?>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-primary" onclick="openConfirmModal(<?= (int) $d['id'] ?>, <?= (int) $d['campaign_id'] ?>, '<?= esc($d['donor_name'] ?? '', 'js') ?>', <?= (int) $d['amount'] ?>)">
                                                                <span class="fe fe-check fe-12"></span> Konfirmasi
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="openCancelModal(<?= (int) $d['id'] ?>, '<?= esc($d['donor_name'] ?? '', 'js') ?>', <?= (int) $d['amount'] ?>)">
                                                                <span class="fe fe-x fe-12"></span> Batalkan
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modalDetailDonasi" onclick="showDetailModal(<?= htmlspecialchars(json_encode($d), ENT_QUOTES, 'UTF-8') ?>)">
                                                            <span class="fe fe-eye fe-12"></span>
                                                        </button>
                                                    <?php endif; ?>
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

<!-- Modal Detail Donasi -->
<div class="modal fade" id="modalDetailDonasi" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Donasi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan diisi via JavaScript -->
            </div>
            <div class="modal-footer" id="modalDetailFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Donasi</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeConfirmModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan mengonfirmasi donasi berikut sebagai dibayar:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between mb-2"><span>Donatur:</span><span id="cm-donor">-</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Urunan:</span><a id="cm-campaign" href="#" class="text-primary">Lihat</a></div>
                    <div class="d-flex justify-content-between font-weight-bold"><span>Nominal:</span><span id="cm-amount">Rp 0</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeConfirmModal()">Batal</button>
                <button type="button" id="cm-submit" class="btn btn-primary">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Donasi</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeCancelDonationModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan membatalkan donasi berikut:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between mb-2"><span>Donatur:</span><span id="cn-donor">-</span></div>
                    <div class="d-flex justify-content-between font-weight-bold"><span>Nominal:</span><span id="cn-amount">Rp 0</span></div>
                </div>
                <p class="text-danger"><small>Donasi yang dibatalkan tidak dapat dikembalikan. Pastikan donasi memang tidak masuk transfer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeCancelDonationModal()">Batal</button>
                <button type="button" id="cn-submit" class="btn btn-danger">Ya, Batalkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Flash Modal -->
<div id="flashModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="flashTitle">Berhasil</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeFlashModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="flashMessage">Donasi dikonfirmasi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="closeFlashModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // CSRF holder
    const csrfName = '<?= csrf_token() ?>';
    const csrfValue = '<?= csrf_hash() ?>';
    
    // Initialize DataTable
    (function() {
        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-donasi').length) {
                if ($.fn.dataTable.isDataTable('#dataTable-donasi')) {
                    return;
                }
                
                $('#dataTable-donasi').DataTable({
                    autoWidth: true,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "order": [[0, "desc"]], // Sort by tanggal descending
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
        }

        // Initialize Select2
        function initSelect2() {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#selectUrunanDonasi').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            } else {
                setTimeout(initSelect2, 500);
            }
        }

        // Filter function
        function filterDonasiUrunan(urunanId) {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-donasi').length) {
                var table = $('#dataTable-donasi').DataTable();
                
                if (urunanId === 'all') {
                    table.column(2).search('').draw();
                    document.getElementById('urunan-donasi-title').textContent = 'Semua Urunan';
                } else {
                    // Get urunan name from option
                    var select = document.getElementById('selectUrunanDonasi');
                    var selectedOption = select.options[select.selectedIndex];
                    var urunanName = selectedOption.text;
                    
                    table.column(2).search(urunanName).draw();
                    document.getElementById('urunan-donasi-title').textContent = urunanName;
                }
            } else {
                // Redirect if DataTable not initialized
                window.location.href = '/admin/donations' + (urunanId !== 'all' ? '?campaign_id=' + urunanId : '');
            }
        }

        // Make filter function global
        window.filterDonasiUrunan = filterDonasiUrunan;

        // Wait for DOM and scripts to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initDataTable, 500);
                setTimeout(initSelect2, 500);
            });
        } else {
            setTimeout(initDataTable, 500);
            setTimeout(initSelect2, 500);
        }
    })();

    // Confirm Modal Functions
    function formatRupiah(n) {
        return 'Rp ' + (n || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function openConfirmModal(donationId, campaignId, donorName, amount) {
        const modal = $('#confirmModal');
        window.currentDonationId = donationId;
        document.getElementById('cm-donor').textContent = donorName || '-';
        document.getElementById('cm-amount').textContent = formatRupiah(amount);
        const link = document.getElementById('cm-campaign');
        link.href = '/admin/campaigns/' + campaignId;
        modal.modal('show');
    }

    function closeConfirmModal() {
        $('#confirmModal').modal('hide');
    }

    // Cancel Modal Functions
    function openCancelModal(donationId, donorName, amount) {
        const modal = $('#cancelModal');
        window.currentCancelDonationId = donationId;
        document.getElementById('cn-donor').textContent = donorName || '-';
        document.getElementById('cn-amount').textContent = formatRupiah(amount);
        modal.modal('show');
    }

    function closeCancelDonationModal() {
        $('#cancelModal').modal('hide');
    }

    // Flash Modal Functions
    function openFlashModal(title, message) {
        document.getElementById('flashTitle').textContent = title;
        document.getElementById('flashMessage').textContent = message;
        $('#flashModal').modal('show');
    }

    function closeFlashModal() {
        $('#flashModal').modal('hide');
    }

    // Show Detail Modal
    function showDetailModal(donation) {
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <small class="text-muted d-block">Donatur</small>
                        <div class="d-flex align-items-center">
                            <strong>${donation.donor_name || '-'}</strong>
                            ${donation.is_anonymous ? '<span class="badge badge-info badge-sm ml-2">Anonim</span>' : ''}
                        </div>
                        ${donation.donor_email ? '<small class="d-block text-muted">' + donation.donor_email + '</small>' : ''}
                        ${donation.donor_phone ? '<small class="d-block text-muted">' + donation.donor_phone + '</small>' : ''}
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Urunan</small>
                        <strong>${donation.campaign_title || '-'}</strong>
                        <small class="d-block text-muted">#${String(donation.campaign_id).padStart(6, '0')}</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Jumlah Donasi</small>
                        <strong class="h5 text-success">Rp ${parseInt(donation.amount || 0).toLocaleString('id-ID')}</strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <small class="text-muted d-block">Tanggal & Waktu</small>
                        <strong>${donation.created_at ? new Date(donation.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'}</strong>
                        <small class="d-block text-muted">${donation.created_at ? new Date(donation.created_at).toLocaleTimeString('id-ID') : ''}</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Metode Pembayaran</small>
                        <span class="badge badge-info">${donation.payment_method || '-'}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge badge-${donation.payment_status === 'paid' ? 'success' : (donation.payment_status === 'failed' ? 'danger' : (donation.payment_status === 'cancelled' ? 'secondary' : 'warning'))}">${donation.payment_status === 'paid' ? 'Berhasil' : (donation.payment_status === 'failed' ? 'Gagal' : (donation.payment_status === 'cancelled' ? 'Dibatalkan' : 'Pending'))}</span>
                    </div>
                    ${donation.payment_reference ? `<div class="mb-3">
                        <small class="text-muted d-block">No. Referensi</small>
                        <strong>${donation.payment_reference}</strong>
                    </div>` : ''}
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <small class="text-muted d-block">Keterangan</small>
                        <p class="mb-0">${donation.message || '-'}</p>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('modalDetailContent').innerHTML = content;
        
        // Update footer dengan tombol restore jika status cancelled
        const footer = document.getElementById('modalDetailFooter');
        if (donation.payment_status === 'cancelled') {
            footer.innerHTML = `
                <button type="button" class="btn btn-warning" onclick="restoreDonationToPending(${donation.id}, '${(donation.donor_name || '').replace(/'/g, "\\'")}', ${donation.amount})">
                    <span class="fe fe-refresh-cw fe-12"></span> Kembalikan ke Pending
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            `;
        } else {
            footer.innerHTML = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>';
        }
    }
    
    // Restore Donation to Pending
    async function restoreDonationToPending(donationId, donorName, amount) {
        try {
            const formData = new URLSearchParams();
            formData.append(csrfName, csrfValue);
            
            const res = await fetch('/donation/restore/' + donationId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const json = await res.json();
            if (json && json.success) {
                $('#modalDetailDonasi').modal('hide');
                openFlashModal('Berhasil', json.message || 'Donasi telah dikembalikan ke status pending');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                openFlashModal('Gagal', (json && json.message) ? json.message : 'Gagal mengembalikan donasi ke pending');
            }
        } catch (e) {
            openFlashModal('Error', 'Terjadi kesalahan jaringan.');
        }
    }

    // Submit Confirmation
    document.getElementById('cm-submit').addEventListener('click', async function() {
        const donationId = window.currentDonationId;
        if (!donationId) return;
        
        closeConfirmModal();
        
        try {
            const formData = new URLSearchParams();
            formData.append(csrfName, csrfValue);
            
            const res = await fetch('/donation/confirm/' + donationId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const json = await res.json();
            if (json && json.success) {
                // Reload page to update table
                location.reload();
            } else {
                openFlashModal('Gagal', (json && json.message) ? json.message : 'Gagal mengonfirmasi donasi');
            }
        } catch (e) {
            openFlashModal('Error', 'Terjadi kesalahan jaringan.');
        }
    });

    // Submit Cancel
    document.getElementById('cn-submit').addEventListener('click', async function() {
        const donationId = window.currentCancelDonationId;
        if (!donationId) return;
        
        closeCancelDonationModal();
        
        try {
            const formData = new URLSearchParams();
            formData.append(csrfName, csrfValue);
            
            const res = await fetch('/donation/cancel/' + donationId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const json = await res.json();
            if (json && json.success) {
                // Reload page to update table
                location.reload();
            } else {
                openFlashModal('Gagal', (json && json.message) ? json.message : 'Gagal membatalkan donasi');
            }
        } catch (e) {
            openFlashModal('Error', 'Terjadi kesalahan jaringan.');
        }
    });
</script>
<?= $this->endSection() ?>
