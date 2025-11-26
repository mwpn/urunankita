<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<?php helper('text'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="h5 page-title mb-0">Pengajuan Penggalang</h2>
                    <small class="text-muted">Kelola pengajuan sebelum dibuatkan akun penggalang.</small>
                </div>
                <div class="col-auto text-right">
                    <a href="<?= base_url('admin/tenants/create') ?>" class="btn btn-sm btn-primary mr-2">
                        <span class="fe fe-user-plus fe-12 mr-1"></span>Tambah Penggalang Baru
                    </a>
                    <a href="<?= base_url('page/penggalang-baru') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <span class="fe fe-external-link fe-12 mr-1"></span>Lihat Form Publik
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

            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <strong class="card-title">Daftar Pengajuan Penggalang</strong>
                        </div>
                        <div class="col-auto">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <input type="text" class="form-control" placeholder="Cari pengajuan..." id="searchFundraiserApplications">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <span class="fe fe-search fe-12"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted mb-3">Belum ada pengajuan penggalang baru.</p>
                            <a href="<?= base_url('page/penggalang-baru') ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <span class="fe fe-external-link fe-12 mr-1"></span>Lihat Form Publik
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover datatables" id="dataTable-fundraiser-applications">
                            <thead class="thead-light">
                                <tr>
                                    <th>Pengaju</th>
                                    <th>Kontak & Dokumen</th>
                                    <th>Sosial Media</th>
                                    <th>Status</th>
                                    <th>Diajukan</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($app['full_name']) ?></strong>
                                            <div class="text-muted small mb-1"><?= $app['entity_type'] === 'foundation' ? 'Yayasan/Lembaga' : 'Pribadi' ?></div>
                                            <?php if (!empty($app['reason'])): ?>
                                                <div class="text-muted small" style="max-width: 320px; white-space: normal;">
                                                    <?= esc(character_limiter($app['reason'], 110)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-sm">
                                            <div class="font-weight-semibold mb-1"><?= esc($app['phone']) ?></div>
                                            <?php if (!empty($app['youtube_channel'])): ?>
                                                <a href="<?= esc($app['youtube_channel']) ?>" target="_blank" class="small text-primary d-block"><span class="fe fe-youtube fe-12 mr-1"></span>Channel Youtube</a>
                                            <?php endif; ?>
                                            <?php if (!empty($app['ktp_document'])): ?>
                                                <a href="<?= esc($app['ktp_document']) ?>" target="_blank" class="small text-primary d-block"><span class="fe fe-id-card fe-12 mr-1"></span>Lihat KTP</a>
                                            <?php endif; ?>
                                            <?php 
                                            $foundationDocs = [];
                                            if (!empty($app['foundation_document'])) {
                                                $decoded = json_decode($app['foundation_document'], true);
                                                $foundationDocs = is_array($decoded) ? $decoded : [$app['foundation_document']];
                                            }
                                            ?>
                                            <?php if (!empty($foundationDocs)): ?>
                                                <?php foreach ($foundationDocs as $index => $doc): ?>
                                                    <a href="<?= esc($doc) ?>" target="_blank" class="small text-primary d-block"><span class="fe fe-briefcase fe-12 mr-1"></span>Dokumen Yayasan <?= count($foundationDocs) > 1 ? '#' . ($index + 1) : '' ?></a>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-sm">
                                            <?php if (!empty($app['instagram'])): ?>
                                                <div><span class="text-muted">IG:</span> <?= esc($app['instagram']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($app['youtube_channel'])): ?>
                                                <div><span class="text-muted">YT:</span> <a href="<?= esc($app['youtube_channel']) ?>" target="_blank" class="text-primary"><?= esc($app['youtube_channel']) ?></a></div>
                                            <?php endif; ?>
                                            <?php if (!empty($app['twitter'])): ?>
                                                <div><span class="text-muted">TW:</span> <?= esc($app['twitter']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($app['facebook'])): ?>
                                                <div><span class="text-muted">FB:</span> <?= esc($app['facebook']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadges = [
                                                'pending' => 'badge-secondary',
                                                'reviewed' => 'badge-info',
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pending',
                                                'reviewed' => 'Diproses',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                            ];
                                            $badgeClass = $statusBadges[$app['status']] ?? 'badge-secondary';
                                            $statusText = $statusLabels[$app['status']] ?? esc($app['status']);
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                            <?php if (!empty($app['notes'])): ?>
                                                <div class="text-muted small mt-1"><?= esc($app['notes']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-sm"><?= date('d M Y H:i', strtotime($app['created_at'])) ?></td>
                                        <td class="text-right">
                                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#applicationModal<?= $app['id'] ?>">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($applications as $app): ?>
                <div class="modal fade" id="applicationModal<?= $app['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="applicationModalLabel<?= $app['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title"><?= esc($app['full_name']) ?></h5>
                                    <small class="text-muted"><?= $app['entity_type'] === 'foundation' ? 'Pengajuan Yayasan/Lembaga' : 'Pengajuan Pribadi' ?></small>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-2">Kontak</h6>
                                        <p class="mb-1"><strong>Nomor HP:</strong> <?= esc($app['phone']) ?></p>
                                        <?php if (!empty($app['youtube_channel'])): ?>
                                            <p class="mb-1"><strong>Channel Youtube:</strong> <a href="<?= esc($app['youtube_channel']) ?>" target="_blank"><?= esc($app['youtube_channel']) ?></a></p>
                                        <?php endif; ?>
                                        <?php if (!empty($app['instagram'])): ?>
                                            <p class="mb-1"><strong>Instagram:</strong> <?= esc($app['instagram']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($app['youtube_channel'])): ?>
                                            <p class="mb-1"><strong>Channel Youtube:</strong> <a href="<?= esc($app['youtube_channel']) ?>" target="_blank"><?= esc($app['youtube_channel']) ?></a></p>
                                        <?php endif; ?>
                                        <?php if (!empty($app['twitter'])): ?>
                                            <p class="mb-1"><strong>Twitter:</strong> <?= esc($app['twitter']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($app['facebook'])): ?>
                                            <p class="mb-1"><strong>Facebook:</strong> <?= esc($app['facebook']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-2">Dokumen</h6>
                                        <?php if (!empty($app['ktp_document'])): ?>
                                            <p class="mb-1"><a href="<?= esc($app['ktp_document']) ?>" target="_blank" class="text-primary"><span class="fe fe-id-card fe-12 mr-1"></span>Lihat KTP</a></p>
                                        <?php else: ?>
                                            <p class="mb-1 text-muted">KTP belum diunggah</p>
                                        <?php endif; ?>
                                        <?php 
                                        $foundationDocs = [];
                                        if (!empty($app['foundation_document'])) {
                                            $decoded = json_decode($app['foundation_document'], true);
                                            $foundationDocs = is_array($decoded) ? $decoded : [$app['foundation_document']];
                                        }
                                        ?>
                                        <?php if (!empty($foundationDocs)): ?>
                                            <?php foreach ($foundationDocs as $index => $doc): ?>
                                                <p class="mb-1"><a href="<?= esc($doc) ?>" target="_blank" class="text-primary"><span class="fe fe-briefcase fe-12 mr-1"></span>Dokumen Yayasan <?= count($foundationDocs) > 1 ? '#' . ($index + 1) : '' ?></a></p>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <p class="mb-1"><strong>Status:</strong> <?= $statusLabels[$app['status']] ?? esc($app['status']) ?></p>
                                        <p class="mb-1"><strong>Diajukan:</strong> <?= date('d M Y H:i', strtotime($app['created_at'])) ?></p>
                                        <?php if (!empty($app['notes'])): ?>
                                            <div class="alert alert-secondary mt-2 mb-0 p-2"><small><?= esc($app['notes']) ?></small></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($app['reason'])): ?>
                                    <div class="mt-4">
                                        <h6 class="text-uppercase text-muted mb-2">Alasan Pengajuan</h6>
                                        <p class="mb-0" style="white-space: pre-line;">
                                            <?= esc($app['reason']) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer flex-column align-items-stretch">
                                <form method="POST" action="<?= base_url('admin/fundraiser-applications/' . $app['id'] . '/status') ?>" class="w-100">
                                    <?= csrf_field() ?>
                                    <label class="small text-muted mb-1">Catatan internal (opsional)</label>
                                    <textarea name="notes" class="form-control mb-3" rows="2" placeholder="Misal: sudah dihubungi via WhatsApp"><?= esc($app['notes'] ?? '') ?></textarea>
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <button type="submit" name="status" value="rejected" class="btn btn-outline-danger">
                                            <span class="fe fe-x-circle fe-12 mr-1"></span>Tolak
                                        </button>
                                        <button type="submit" name="status" value="reviewed" class="btn btn-outline-secondary">
                                            <span class="fe fe-clock fe-12 mr-1"></span>Tandai Diproses
                                        </button>
                                        <button type="submit" name="status" value="approved" class="btn btn-success">
                                            <span class="fe fe-check fe-12 mr-1"></span>Setujui
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="alert alert-info mt-4 mb-0">
                Setelah status pengajuan disetujui, buat akun penggalang melalui halaman <a href="<?= base_url('admin/tenants/create') ?>" class="font-weight-semibold">Admin &raquo; Penggalang Dana</a>.
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable && $('#dataTable-fundraiser-applications').length) {
            var table = $('#dataTable-fundraiser-applications').DataTable({
                autoWidth: true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[4, "desc"]],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
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
                    { "targets": [5], "orderable": false }
                ]
            });

            $('#searchFundraiserApplications').on('keyup', function() {
                table.search(this.value).draw();
            });
        }
    });
</script>
<?= $this->endSection() ?>
