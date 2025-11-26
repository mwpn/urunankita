<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Pengaturan</h2>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= (strpos(uri_string(), '#template') === false && strpos(uri_string(), '#site') === false && (!isset($_GET['tab']) || ($_GET['tab'] !== 'template' && $_GET['tab'] !== 'site'))) ? 'active' : '' ?>" id="payment-tab" data-toggle="tab" href="#payment" role="tab" aria-controls="payment" aria-selected="<?= (strpos(uri_string(), '#template') === false && strpos(uri_string(), '#site') === false && (!isset($_GET['tab']) || ($_GET['tab'] !== 'template' && $_GET['tab'] !== 'site'))) ? 'true' : 'false' ?>">
                        <span class="fe fe-credit-card fe-12 mr-1"></span>Metode Pembayaran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos(uri_string(), '#site') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'site')) ? 'active' : '' ?>" id="site-tab" data-toggle="tab" href="#site" role="tab" aria-controls="site" aria-selected="<?= (strpos(uri_string(), '#site') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'site')) ? 'true' : 'false' ?>">
                        <span class="fe fe-globe fe-12 mr-1"></span>Pengaturan Situs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos(uri_string(), '#template') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'template')) ? 'active' : '' ?>" id="template-tab" data-toggle="tab" href="#template" role="tab" aria-controls="template" aria-selected="<?= (strpos(uri_string(), '#template') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'template')) ? 'true' : 'false' ?>">
                        <span class="fe fe-message-square fe-12 mr-1"></span>Template Pesan WhatsApp
                    </a>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="settingsTabsContent">
                <!-- Payment Methods Tab -->
                <div class="tab-pane fade <?= (strpos(uri_string(), '#template') === false && !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] !== 'template')) ? 'show active' : '' ?>" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                    <div class="row align-items-center mb-2">
                        <div class="col">
                            <h4 class="h6">Metode Pembayaran</h4>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambahPayment">
                                <span class="fe fe-plus fe-12 mr-1"></span>Tambah Metode Pembayaran
                            </button>
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

            <!-- Statistik -->
            <?php 
            $totalMethods = count($payment_methods);
            $activeMethods = 0;
            foreach ($payment_methods as $method) {
                if (!empty($method['enabled'])) {
                    $activeMethods++;
                }
            }
            $activePercentage = $totalMethods > 0 ? round(($activeMethods / $totalMethods) * 100, 1) : 0;
            ?>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Metode</small>
                            <h3 class="mb-0"><?= number_format($totalMethods) ?></h3>
                            <small class="text-success"><span class="fe fe-arrow-up fe-12"></span> <?= $activeMethods ?> aktif</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Metode Aktif</small>
                            <h3 class="mb-0 text-success"><?= number_format($activeMethods) ?></h3>
                            <small class="text-muted"><?= $activePercentage ?>%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Transaksi</small>
                            <h3 class="mb-0">0</h3>
                            <small class="text-info">Bulan ini</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card shadow">
                        <div class="card-body">
                            <small class="text-muted d-block mb-1">Total Nominal</small>
                            <h3 class="mb-0">Rp 0</h3>
                            <small class="text-muted">Bulan ini</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Metode Pembayaran -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <strong class="card-title">Daftar Metode Pembayaran</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatables table-hover" id="dataTable-payment">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Metode Pembayaran</th>
                                            <th>Tipe</th>
                                            <th>Status</th>
                                            <th>Total Transaksi</th>
                                            <th>Total Nominal</th>
                                            <th>Biaya Admin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payment_methods as $index => $method): ?>
                                            <?php
                                            $methodType = $method['type'] ?? 'bank-transfer';
                                            $typeIcons = [
                                                'bank-transfer' => 'fe-credit-card text-primary',
                                                'e-wallet' => 'fe-smartphone text-success',
                                                'kartu' => 'fe-credit-card text-info',
                                                'qris' => 'fe-shield text-warning',
                                                'virtual-account' => 'fe-dollar-sign text-danger',
                                                'payment-gateway' => 'fe-credit-card text-secondary',
                                            ];
                                            $typeBadges = [
                                                'bank-transfer' => 'badge-primary',
                                                'e-wallet' => 'badge-success',
                                                'kartu' => 'badge-info',
                                                'qris' => 'badge-warning',
                                                'virtual-account' => 'badge-danger',
                                                'payment-gateway' => 'badge-secondary',
                                            ];
                                            $typeLabels = [
                                                'bank-transfer' => 'Bank Transfer',
                                                'e-wallet' => 'E-Wallet',
                                                'kartu' => 'Kartu',
                                                'qris' => 'QR Code',
                                                'virtual-account' => 'Virtual Account',
                                                'payment-gateway' => 'Payment Gateway',
                                            ];
                                            $icon = $typeIcons[$methodType] ?? 'fe-credit-card text-primary';
                                            $badge = $typeBadges[$methodType] ?? 'badge-secondary';
                                            $label = $typeLabels[$methodType] ?? 'Lainnya';
                                            $isEnabled = !empty($method['enabled']);
                                            $provider = $method['provider'] ?? '';
                                            $adminFee = $method['admin_fee_percent'] ?? 0;
                                            $adminFeeFixed = $method['admin_fee_fixed'] ?? 0;
                                            $adminFeeDisplay = $adminFee > 0 ? $adminFee . '%' : ($adminFeeFixed > 0 ? 'Rp ' . number_format($adminFeeFixed, 0, ',', '.') : 'Gratis');
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="check-<?= $index ?>">
                                                        <label class="custom-control-label" for="check-<?= $index ?>"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3">
                                                            <span class="fe fe-24 <?= $icon ?>"></span>
                                                        </div>
                                                        <div>
                                                            <strong><?= esc($method['name'] ?? 'Metode Pembayaran') ?></strong>
                                                            <?php if ($provider): ?>
                                                                <small class="d-block text-muted"><?= esc($provider) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $badge ?>"><?= esc($label) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($isEnabled): ?>
                                                        <span class="badge badge-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong>0</strong>
                                                </td>
                                                <td>
                                                    <strong class="text-success">Rp 0</strong>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?= esc($adminFeeDisplay) ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text-muted sr-only">Action</span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="#" onclick="editPaymentMethod(<?= $method['id'] ?>)" data-toggle="modal" data-target="#modalEditPayment">
                                                            <i class="fe fe-edit fe-12 mr-2"></i>Edit
                                                        </a>
                                                        <a class="dropdown-item" href="#" onclick="togglePaymentMethod(<?= $method['id'] ?>, <?= $isEnabled ? 'false' : 'true' ?>)">
                                                            <i class="fe fe-<?= $isEnabled ? 'x-circle' : 'check-circle' ?> fe-12 mr-2"></i><?= $isEnabled ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="openDeletePaymentMethodModal(<?= $method['id'] ?>, '<?= esc($method['name'], 'js') ?>')">
                                                            <i class="fe fe-trash-2 fe-12 mr-2"></i>Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div> <!-- End Payment Tab -->

                <!-- Site Settings Tab -->
                <div class="tab-pane fade <?= (strpos(uri_string(), '#site') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'site')) ? 'show active' : '' ?>" id="site" role="tabpanel" aria-labelledby="site-tab">
                    <form id="formSiteSettings" method="POST" action="<?= base_url('tenant/settings/save-site') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Pengaturan Situs</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="site_name">Nama Situs</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= esc($siteSettings['site_name'] ?? '') ?>" placeholder="Nama situs Anda">
                                    <small class="form-text text-muted">Nama situs yang akan ditampilkan di halaman public</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_tagline">Tagline</label>
                                    <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="<?= esc($siteSettings['site_tagline'] ?? '') ?>" placeholder="Tagline situs Anda">
                                    <small class="form-text text-muted">Tagline singkat yang menjelaskan situs Anda</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_description">Deskripsi</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3" placeholder="Deskripsi lengkap tentang situs Anda"><?= esc($siteSettings['site_description'] ?? '') ?></textarea>
                                    <small class="form-text text-muted">Deskripsi yang akan digunakan untuk meta description</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fe fe-info fe-12 mr-1"></i>
                                    <strong>Catatan:</strong> Logo dan favicon akan menggunakan logo dan favicon default dari pengaturan admin. Untuk mengubah logo dan favicon, silakan hubungi administrator.
                                </div>
                                
                                <div class="form-group">
                                    <label>Gambar Hero (Halaman Depan)</label>
                                    <div class="dropzone bg-light rounded-lg mb-3" id="dropzone-hero-image-tenant" style="min-height: 200px;">
                                        <div class="dz-message needsclick">
                                            <div class="circle circle-lg bg-info">
                                                <i class="fe fe-image fe-24 text-white"></i>
                                            </div>
                                            <h5 class="text-muted mt-4">Drop gambar hero di sini atau klik untuk upload</h5>
                                            <span class="text-muted">Max: 5MB, Format: JPG/PNG/GIF/WEBP (disarankan ukuran besar untuk kualitas baik)</span>
                                        </div>
                                    </div>
                                    <div id="hero-image-tenant-preview-container">
                                        <?php 
                                        $tenantHeroImage = $siteSettings['hero_image'] ?? '';
                                        if (!empty($tenantHeroImage)): 
                                        ?>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= esc($tenantHeroImage) ?>" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTenantHeroImage()">
                                                    <span class="fe fe-trash-2 fe-12"></span> Hapus
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" id="hero_image_tenant" name="hero_image" value="<?= esc($tenantHeroImage) ?>">
                                    <small class="form-text text-muted">Gambar ini akan ditampilkan di bagian kanan hero section pada halaman depan</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_email">Email</label>
                                    <input type="email" class="form-control" id="site_email" name="site_email" value="<?= esc($siteSettings['site_email'] ?? '') ?>" placeholder="info@example.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_phone">Telepon</label>
                                    <input type="text" class="form-control" id="site_phone" name="site_phone" value="<?= esc($siteSettings['site_phone'] ?? '') ?>" placeholder="+62 812-3456-7890">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_address">Alamat</label>
                                    <textarea class="form-control" id="site_address" name="site_address" rows="2" placeholder="Alamat lengkap"><?= esc($siteSettings['site_address'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="site_facebook">Facebook</label>
                                        <input type="url" class="form-control" id="site_facebook" name="site_facebook" value="<?= esc($siteSettings['site_facebook'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="site_instagram">Instagram</label>
                                        <input type="url" class="form-control" id="site_instagram" name="site_instagram" value="<?= esc($siteSettings['site_instagram'] ?? '') ?>" placeholder="https://instagram.com/yourpage">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="site_twitter">Twitter</label>
                                        <input type="url" class="form-control" id="site_twitter" name="site_twitter" value="<?= esc($siteSettings['site_twitter'] ?? '') ?>" placeholder="https://twitter.com/yourpage">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Situs
                                </button>
                            </div>
                        </div>
                    </form>
                </div> <!-- End Site Settings Tab -->

                <!-- Template Messages Tab -->
                <div class="tab-pane fade <?= (strpos(uri_string(), '#template') !== false || (isset($_GET['tab']) && $_GET['tab'] === 'template')) ? 'show active' : '' ?>" id="template" role="tabpanel" aria-labelledby="template-tab">
                    <form id="formTemplateSettings" method="POST" action="<?= base_url('tenant/settings/save') ?>">
                        <?= csrf_field() ?>
                        
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Template Pesan WhatsApp</strong>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Atur template pesan WhatsApp untuk notifikasi. Jika tidak diisi, akan menggunakan template global dari admin. 
                                    Gunakan placeholder seperti {amount}, {campaign_title}, {donor_name}, {site_name} untuk konten dinamis.
                                </p>
                                
                                <div class="form-group">
                                    <label for="template_donation_created">
                                        Template: Donasi Dibuat (untuk Donor)
                                        <?php if (($templatesSource['whatsapp_template_donation_created'] ?? 'global') === 'global'): ?>
                                            <span class="badge badge-info ml-2">Menggunakan Template Global</span>
                                        <?php else: ?>
                                            <span class="badge badge-success ml-2">Template Kustom</span>
                                        <?php endif; ?>
                                    </label>
                                    <textarea 
                                        id="template_donation_created" 
                                        name="templates[whatsapp_template_donation_created]" 
                                        rows="3"
                                        class="form-control"
                                        placeholder="Kosongkan untuk menggunakan template global"
                                    ><?= esc(($templatesSource['whatsapp_template_donation_created'] ?? 'global') === 'tenant' ? ($templates['whatsapp_template_donation_created'] ?? '') : '') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                    <?php if (($templatesSource['whatsapp_template_donation_created'] ?? 'global') === 'global'): ?>
                                        <small class="form-text text-info">
                                            <strong>Template Global:</strong> <?= esc($templates['whatsapp_template_donation_created'] ?? 'Belum diatur') ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="template_donation_paid">
                                        Template: Donasi Diterima (untuk Donor)
                                        <?php if (($templatesSource['whatsapp_template_donation_paid'] ?? 'global') === 'global'): ?>
                                            <span class="badge badge-info ml-2">Menggunakan Template Global</span>
                                        <?php else: ?>
                                            <span class="badge badge-success ml-2">Template Kustom</span>
                                        <?php endif; ?>
                                    </label>
                                    <textarea 
                                        id="template_donation_paid" 
                                        name="templates[whatsapp_template_donation_paid]" 
                                        rows="3"
                                        class="form-control"
                                        placeholder="Kosongkan untuk menggunakan template global"
                                    ><?= esc(($templatesSource['whatsapp_template_donation_paid'] ?? 'global') === 'tenant' ? ($templates['whatsapp_template_donation_paid'] ?? '') : '') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                    <?php if (($templatesSource['whatsapp_template_donation_paid'] ?? 'global') === 'global'): ?>
                                        <small class="form-text text-info">
                                            <strong>Template Global:</strong> <?= esc($templates['whatsapp_template_donation_paid'] ?? 'Belum diatur') ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="template_tenant_donation_new">
                                        Template: Donasi Baru (untuk Anda/Tenant)
                                        <?php if (($templatesSource['whatsapp_template_tenant_donation_new'] ?? 'global') === 'global'): ?>
                                            <span class="badge badge-info ml-2">Menggunakan Template Global</span>
                                        <?php else: ?>
                                            <span class="badge badge-success ml-2">Template Kustom</span>
                                        <?php endif; ?>
                                    </label>
                                    <textarea 
                                        id="template_tenant_donation_new" 
                                        name="templates[whatsapp_template_tenant_donation_new]" 
                                        rows="3"
                                        class="form-control"
                                        placeholder="Kosongkan untuk menggunakan template global"
                                    ><?= esc(($templatesSource['whatsapp_template_tenant_donation_new'] ?? 'global') === 'tenant' ? ($templates['whatsapp_template_tenant_donation_new'] ?? '') : '') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {donation_id}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                    <?php if (($templatesSource['whatsapp_template_tenant_donation_new'] ?? 'global') === 'global'): ?>
                                        <small class="form-text text-info">
                                            <strong>Template Global:</strong> <?= esc($templates['whatsapp_template_tenant_donation_new'] ?? 'Belum diatur') ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Template
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetTemplates()">
                                    <span class="fe fe-refresh-cw fe-12 mr-1"></span>Reset ke Template Global
                                </button>
                            </div>
                        </div>
                    </form>
                </div> <!-- End Template Tab -->
            </div> <!-- End Tabs Content -->
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<!-- Modal Tambah Metode Pembayaran -->
<div class="modal fade" id="modalTambahPayment" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formTambahPayment" method="POST" action="/tenant/settings/save">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Metode Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" name="payment_methods[new][name]" class="form-control" placeholder="Contoh: Transfer Bank" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipe <span class="text-danger">*</span></label>
                            <select name="payment_methods[new][type]" class="form-control" required>
                                <option value="">Pilih Tipe</option>
                                <option value="bank-transfer">Bank Transfer</option>
                                <option value="e-wallet">E-Wallet</option>
                                <option value="kartu">Kartu Kredit/Debit</option>
                                <option value="qris">QRIS</option>
                                <option value="virtual-account">Virtual Account</option>
                                <option value="payment-gateway">Payment Gateway</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="payment_methods[new][enabled]" class="form-control">
                                <option value="1" selected>Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Provider/Channel</label>
                        <input type="text" name="payment_methods[new][provider]" class="form-control" placeholder="Contoh: BCA, Mandiri, BNI, BRI">
                        <small class="form-text text-muted">Daftar provider atau channel yang didukung (pisahkan dengan koma)</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Biaya Admin (%)</label>
                            <input type="number" name="payment_methods[new][admin_fee_percent]" class="form-control" placeholder="0" min="0" max="100" step="0.1">
                            <small class="form-text text-muted">Persentase biaya admin yang dibebankan (kosongkan untuk gratis)</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Biaya Admin Tetap (Rp)</label>
                            <input type="number" name="payment_methods[new][admin_fee_fixed]" class="form-control" placeholder="0" min="0">
                            <small class="form-text text-muted">Biaya admin tetap dalam rupiah (opsional)</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="payment_methods[new][description]" class="form-control" rows="3" placeholder="Keterangan atau instruksi untuk metode pembayaran ini"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="requireVerification" name="payment_methods[new][require_verification]" value="1">
                            <label class="custom-control-label" for="requireVerification">
                                Perlu Verifikasi Manual
                            </label>
                            <small class="d-block text-muted">Aktifkan jika pembayaran perlu diverifikasi secara manual</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Metode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Metode Pembayaran -->
<div class="modal fade" id="modalEditPayment" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditPayment" method="POST" action="/tenant/settings/save">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="edit_id" value="">
                    <div class="form-group">
                        <label>Nama Metode Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" name="payment_methods[edit][name]" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipe <span class="text-danger">*</span></label>
                            <select id="edit_type" name="payment_methods[edit][type]" class="form-control" required>
                                <option value="bank-transfer">Bank Transfer</option>
                                <option value="e-wallet">E-Wallet</option>
                                <option value="kartu">Kartu Kredit/Debit</option>
                                <option value="qris">QRIS</option>
                                <option value="virtual-account">Virtual Account</option>
                                <option value="payment-gateway">Payment Gateway</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select id="edit_enabled" name="payment_methods[edit][enabled]" class="form-control">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Provider/Channel</label>
                        <input type="text" id="edit_provider" name="payment_methods[edit][provider]" class="form-control">
                        <small class="form-text text-muted">Daftar provider atau channel yang didukung</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Biaya Admin (%)</label>
                            <input type="number" id="edit_admin_fee_percent" name="payment_methods[edit][admin_fee_percent]" class="form-control" min="0" max="100" step="0.1">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Biaya Admin Tetap (Rp)</label>
                            <input type="number" id="edit_admin_fee_fixed" name="payment_methods[edit][admin_fee_fixed]" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="edit_description" name="payment_methods[edit][description]" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="requireVerificationEdit" name="payment_methods[edit][require_verification]" value="1">
                            <label class="custom-control-label" for="requireVerificationEdit">
                                Perlu Verifikasi Manual
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Metode</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Payment Method Modal -->
<div id="deletePaymentMethodModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Metode Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeDeletePaymentMethodModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Anda akan menghapus metode pembayaran berikut:</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-800 mb-4">
                    <div class="d-flex justify-content-between">
                        <span>Nama Metode:</span>
                        <span id="dpm-method-name" class="font-weight-bold">-</span>
                    </div>
                </div>
                <p class="text-danger"><small><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Metode pembayaran yang dihapus tidak akan tersedia lagi untuk donasi.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeDeletePaymentMethodModal()">Batal</button>
                <button type="button" id="dpm-submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="notificationModalHeader">
                <h5 class="modal-title" id="notificationModalTitle">Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <p id="notificationModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                <p id="confirmationModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmationModalConfirm">Ya</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Payment methods data (indexed by ID for easy lookup)
    const paymentMethods = <?= json_encode($payment_methods) ?>;
    const paymentMethodsById = {};
    paymentMethods.forEach(method => {
        paymentMethodsById[method.id] = method;
    });

    function editPaymentMethod(id) {
        const method = paymentMethodsById[id];
        if (!method) return;

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = method.name || '';
        document.getElementById('edit_type').value = method.type || 'bank-transfer';
        document.getElementById('edit_enabled').value = method.enabled ? '1' : '0';
        document.getElementById('edit_provider').value = method.provider || '';
        document.getElementById('edit_admin_fee_percent').value = method.admin_fee_percent || 0;
        document.getElementById('edit_admin_fee_fixed').value = method.admin_fee_fixed || 0;
        document.getElementById('edit_description').value = method.description || '';
        document.getElementById('requireVerificationEdit').checked = method.require_verification || false;
    }

    async function togglePaymentMethod(id, enabled) {
        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('enabled', enabled === 'true' || enabled === true ? '1' : '0');
            
            const response = await fetch('/tenant/settings/payment-method/' + id + '/toggle', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                showNotification('success', 'Status metode pembayaran berhasil diubah');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Gagal mengubah status metode pembayaran');
            }
        } catch (error) {
            console.error('Error toggling payment method:', error);
            showNotification('error', 'Terjadi kesalahan saat mengubah status metode pembayaran');
        }
    }

    // Delete Payment Method Modal Functions
    function openDeletePaymentMethodModal(id, methodName) {
        const modal = $('#deletePaymentMethodModal');
        window.currentDeletePaymentMethodId = id;
        document.getElementById('dpm-method-name').textContent = methodName || '-';
        modal.modal('show');
    }

    function closeDeletePaymentMethodModal() {
        $('#deletePaymentMethodModal').modal('hide');
    }

    // Submit Delete Payment Method
    document.getElementById('dpm-submit').addEventListener('click', async function() {
        const id = window.currentDeletePaymentMethodId;
        if (!id) return;
        
        closeDeletePaymentMethodModal();
        
        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            
            const response = await fetch('/tenant/settings/payment-method/' + id + '/delete', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                if (typeof showToast === 'function') {
                    showToast('error', data.message || 'Gagal menghapus metode pembayaran');
                } else {
                    console.error(data.message || 'Gagal menghapus metode pembayaran');
                }
            }
        } catch (error) {
            console.error('Error deleting payment method:', error);
            if (typeof showToast === 'function') {
                showToast('error', 'Terjadi kesalahan saat menghapus metode pembayaran');
            } else {
                console.error('Terjadi kesalahan saat menghapus metode pembayaran');
            }
        }
    });

    // Initialize DataTable
    (function() {
        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined' && $('#dataTable-payment').length) {
                if ($.fn.dataTable.isDataTable('#dataTable-payment')) {
                    $('#dataTable-payment').DataTable().destroy();
                }
                $('#dataTable-payment').DataTable({
                    autoWidth: true,
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "order": [[4, "desc"]], // Sort by total transaksi descending
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
                        {
                            "targets": [0], // Checkbox column
                            "orderable": false
                        },
                        {
                            "targets": [7], // Action column
                            "orderable": false
                        }
                    ]
                });
                console.log('DataTable payment methods initialized');
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

    // Reset templates to global
    function resetTemplates() {
        showConfirmation(
            'Reset Template',
            'Yakin ingin mereset template ke template global? Template yang sudah diatur akan dihapus.',
            function() {
                document.getElementById('template_donation_created').value = '';
                document.getElementById('template_donation_paid').value = '';
                document.getElementById('template_tenant_donation_new').value = '';
                
                // Submit form to save empty values (which will delete tenant-specific templates)
                document.getElementById('formTemplateSettings').submit();
            }
        );
    }

    // Show notification modal
    function showNotification(type, message) {
        const modal = $('#notificationModal');
        const header = $('#notificationModalHeader');
        const title = $('#notificationModalTitle');
        const body = $('#notificationModalBody');
        
        // Remove existing classes
        header.removeClass('bg-success bg-danger bg-warning bg-info');
        
        // Set type-specific styling
        if (type === 'success') {
            header.addClass('bg-success text-white');
            title.text('Berhasil');
        } else if (type === 'error') {
            header.addClass('bg-danger text-white');
            title.text('Error');
        } else if (type === 'warning') {
            header.addClass('bg-warning text-dark');
            title.text('Peringatan');
        } else {
            header.addClass('bg-info text-white');
            title.text('Informasi');
        }
        
        body.find('#notificationModalMessage').text(message);
        modal.modal('show');
    }

    // Show confirmation modal
    function showConfirmation(title, message, onConfirm) {
        const modal = $('#confirmationModal');
        $('#confirmationModalTitle').text(title);
        $('#confirmationModalMessage').text(message);
        
        // Remove previous event listeners
        $('#confirmationModalConfirm').off('click');
        
        // Add new event listener
        $('#confirmationModalConfirm').on('click', function() {
            modal.modal('hide');
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });
        
        modal.modal('show');
    }

    // Initialize Dropzone for tenant hero image
    function initTenantHeroDropzone() {
        if (typeof Dropzone === 'undefined') {
            console.warn('Dropzone library not loaded, retrying...');
            setTimeout(initTenantHeroDropzone, 500);
            return;
        }
        
        Dropzone.autoDiscover = false;
        
        const heroElement = document.getElementById('dropzone-hero-image-tenant');
        if (!heroElement) {
            console.warn('Hero dropzone element not found');
            return;
        }
        
        // Destroy existing dropzone if any
        if (heroElement.dropzone) {
            heroElement.dropzone.destroy();
        }
        
        const tenantHeroDropzone = new Dropzone('#dropzone-hero-image-tenant', {
            url: '<?= base_url('tenant/settings/upload-hero-image') ?>',
            maxFiles: 1,
            maxFilesize: 5,
            acceptedFiles: 'image/jpeg,image/jpg,image/png,image/gif,image/webp',
            addRemoveLinks: true,
            dictDefaultMessage: '',
            dictRemoveFile: 'Hapus',
            clickable: '#dropzone-hero-image-tenant .dz-message',
            autoProcessQueue: true,
            paramName: 'file',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            init: function() {
                const myDropzone = this;
                
                // Add CSRF token
                myDropzone.on('sending', function(file, xhr, formData) {
                    const csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content');
                    if (csrfToken) {
                        formData.append('<?= csrf_token() ?>', csrfToken);
                    }
                });
                
                myDropzone.on('success', function(file, response) {
                    console.log('Tenant hero image uploaded:', response);
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch(e) {
                            console.error('Failed to parse response:', e);
                        }
                    }
                    if (response && response.path) {
                        $('#hero_image_tenant').val(response.path);
                        const previewHtml = '<div class="d-flex align-items-center">' +
                            '<img src="' + response.path + '" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTenantHeroImage()">' +
                            '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
                        $('#hero-image-tenant-preview-container').html(previewHtml);
                        showNotification('success', 'Gambar hero berhasil diupload');
                    }
                });
                
                myDropzone.on('removedfile', function() {
                    $('#hero_image_tenant').val('');
                    $('#hero-image-tenant-preview-container').empty();
                });
                
                myDropzone.on('error', function(file, errorMessage) {
                    console.error('Dropzone error:', errorMessage);
                    if (typeof errorMessage === 'string') {
                        try {
                            const errorObj = JSON.parse(errorMessage);
                            errorMessage = errorObj.message || errorMessage;
                        } catch(e) {}
                    }
                    showNotification('error', 'Error uploading hero image: ' + errorMessage);
                });
            }
        });
        
        console.log('Tenant hero dropzone initialized');
    }

    // Auto-activate template tab if hash is present
    $(document).ready(function() {
        if (window.location.hash === '#template') {
            $('#template-tab').tab('show');
        }
        
        // Initialize Dropzone when site tab is shown
        $('#site-tab').on('shown.bs.tab', function() {
            setTimeout(initTenantHeroDropzone, 300);
        });
        
        // Also initialize if site tab is already active
        const isSiteTabActive = $('#site-tab').hasClass('active') || window.location.hash === '#site' || 
            (window.location.search.indexOf('tab=site') !== -1);
        if (isSiteTabActive) {
            console.log('Site tab is active, initializing dropzone...');
            setTimeout(initTenantHeroDropzone, 800);
        }
        
        // Try to initialize after a delay (in case tab is not active yet)
        setTimeout(function() {
            const heroElement = document.getElementById('dropzone-hero-image-tenant');
            if (heroElement) {
                console.log('Hero element found, checking Dropzone...');
                if (typeof Dropzone !== 'undefined') {
                    console.log('Dropzone is available, initializing...');
                    initTenantHeroDropzone();
                } else {
                    console.warn('Dropzone not available yet');
                }
            } else {
                console.warn('Hero element not found');
            }
        }, 1500);
    });
    
    // Remove Tenant Hero Image
    function removeTenantHeroImage() {
        if (!confirm('Yakin ingin menghapus gambar hero?')) {
            return;
        }

        $.ajax({
            url: '<?= base_url('tenant/settings/remove-hero-image') ?>',
            method: 'POST',
            data: {
                <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Tenant hero image removed');
                $('#hero_image_tenant').val('');
                $('#hero-image-tenant-preview-container').empty();
                showNotification('success', 'Gambar hero berhasil dihapus');
            },
            error: function(xhr) {
                console.error('Error removing tenant hero image:', xhr);
                showNotification('error', 'Gagal menghapus gambar hero');
            }
        });
    }
    
    window.removeTenantHeroImage = removeTenantHeroImage;
</script>
<?= $this->endSection() ?>
