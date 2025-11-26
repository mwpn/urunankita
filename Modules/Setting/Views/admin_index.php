<?= $this->extend('Modules\Core\Views\template_layout') ?>

<?= $this->section('head') ?>
<title><?= esc($title ?? 'Pengaturan Sistem') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="h5 page-title">Pengaturan Sistem</h2>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="adminSettingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                        <span class="fe fe-sliders fe-12 mr-1"></span>Pengaturan Umum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="template-tab" data-toggle="tab" href="#template" role="tab" aria-controls="template" aria-selected="false">
                        <span class="fe fe-message-square fe-12 mr-1"></span>Template Pesan WhatsApp
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tenant-tab" data-toggle="tab" href="#tenant" role="tab" aria-controls="tenant" aria-selected="false">
                        <span class="fe fe-users fe-12 mr-1"></span>Pengaturan Tenant
                    </a>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="adminSettingsTabsContent">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">

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

                    <!-- Settings Form -->
                    <form id="formPengaturanUmum" method="POST" action="<?= base_url('admin/settings/save') ?>" class="needs-validation" novalidate enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Informasi Platform -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Informasi Platform</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="site_name">Nama Platform <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        id="site_name"
                                        name="settings[site_name]"
                                        value="<?= esc($settings['site_name']['value'] ?? 'UrunanKita') ?>"
                                        class="form-control"
                                        placeholder="UrunanKita"
                                        required>
                                    <small class="form-text text-muted">Nama platform yang akan ditampilkan di website</small>
                                </div>
                                <div class="form-group">
                                    <label for="site_tagline">Tagline/Slogan</label>
                                    <input
                                        type="text"
                                        id="site_tagline"
                                        name="settings[site_tagline]"
                                        value="<?= esc($settings['site_tagline']['value'] ?? 'Platform Crowdfunding Terpercaya') ?>"
                                        class="form-control"
                                        placeholder="Platform Crowdfunding Terpercaya">
                                    <small class="form-text text-muted">Slogan atau tagline platform</small>
                                </div>
                                <div class="form-group">
                                    <label for="site_description">Deskripsi Platform</label>
                                    <textarea
                                        id="site_description"
                                        name="settings[site_description]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Deskripsi singkat tentang platform"><?= esc($settings['site_description']['value'] ?? 'Platform crowdfunding terpercaya untuk membantu berbagai kebutuhan sosial dan kemanusiaan.') ?></textarea>
                                    <small class="form-text text-muted">Deskripsi yang akan digunakan untuk SEO</small>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Logo Platform</label>
                                        <div class="dropzone bg-light rounded-lg mb-3" id="dropzone-logo" style="min-height: 150px;">
                                            <div class="dz-message needsclick">
                                                <div class="circle circle-lg bg-primary">
                                                    <i class="fe fe-upload fe-24 text-white"></i>
                                                </div>
                                                <h5 class="text-muted mt-4">Drop logo di sini atau klik untuk upload</h5>
                                                <span class="text-muted">Max: 2MB, Format: PNG/SVG (disarankan transparan)</span>
                                            </div>
                                        </div>
                                        <div id="logo-preview-container">
                                            <?php if (!empty($settings['site_logo']['value'])): ?>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= esc($settings['site_logo']['value']) ?>" alt="Logo" class="img-thumbnail mr-2" style="max-width: 200px; max-height: 100px;">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">
                                                        <span class="fe fe-trash-2 fe-12"></span> Hapus
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" id="site_logo" name="site_logo" value="<?= esc($settings['site_logo']['value'] ?? '') ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Favicon</label>
                                        <div class="dropzone bg-light rounded-lg mb-3" id="dropzone-favicon" style="min-height: 150px;">
                                            <div class="dz-message needsclick">
                                                <div class="circle circle-lg bg-secondary">
                                                    <i class="fe fe-upload fe-24 text-white"></i>
                                                </div>
                                                <h5 class="text-muted mt-4">Drop favicon di sini atau klik untuk upload</h5>
                                                <span class="text-muted">Max: 1MB, Format: ICO/PNG (16x16 atau 32x32)</span>
                                            </div>
                                        </div>
                                        <div id="favicon-preview-container">
                                            <?php if (!empty($settings['site_favicon']['value'])): ?>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= esc($settings['site_favicon']['value']) ?>" alt="Favicon" class="img-thumbnail mr-2" style="max-width: 64px; max-height: 64px;">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFavicon()">
                                                        <span class="fe fe-trash-2 fe-12"></span> Hapus
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" id="site_favicon" name="site_favicon" value="<?= esc($settings['site_favicon']['value'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Gambar Hero (Halaman Depan)</label>
                                    <div class="dropzone bg-light rounded-lg mb-3" id="dropzone-hero-image" style="min-height: 200px;">
                                        <div class="dz-message needsclick">
                                            <div class="circle circle-lg bg-info">
                                                <i class="fe fe-image fe-24 text-white"></i>
                                            </div>
                                            <h5 class="text-muted mt-4">Drop gambar hero di sini atau klik untuk upload</h5>
                                            <span class="text-muted">Max: 5MB, Format: JPG/PNG/GIF/WEBP (disarankan ukuran besar untuk kualitas baik)</span>
                                        </div>
                                    </div>
                                    <div id="hero-image-preview-container">
                                        <?php if (!empty($settings['hero_image']['value'])): ?>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= esc($settings['hero_image']['value']) ?>" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHeroImage()">
                                                    <span class="fe fe-trash-2 fe-12"></span> Hapus
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" id="hero_image" name="hero_image" value="<?= esc($settings['hero_image']['value'] ?? '') ?>">
                                    <small class="form-text text-muted">Gambar ini akan ditampilkan di bagian kanan hero section pada halaman depan</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('platform', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Platform
                                </button>
                            </div>
                        </div>

                        <!-- Tampilan & Font -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Tampilan & Font</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="frontend_font">Font Frontend (Google Fonts)</label>
                                    <select
                                        id="frontend_font"
                                        name="settings[frontend_font]"
                                        class="form-control">
                                        <!-- ============================================ -->
                                        <!-- TAMBAHKAN FONT BARU DI BAWAH INI -->
                                        <!-- Format: <option value="Nama Font" ...>Nama Font</option> -->
                                        <!-- Pastikan nama font sesuai dengan Google Fonts -->
                                        <!-- ============================================ -->

                                        <option value="Outfit" <?= ($settings['frontend_font']['value'] ?? 'Outfit') === 'Outfit' ? 'selected' : '' ?>>Outfit</option>
                                        <option value="Inter" <?= ($settings['frontend_font']['value'] ?? '') === 'Inter' ? 'selected' : '' ?>>Inter</option>
                                        <option value="Poppins" <?= ($settings['frontend_font']['value'] ?? '') === 'Poppins' ? 'selected' : '' ?>>Poppins</option>
                                        <option value="Roboto" <?= ($settings['frontend_font']['value'] ?? '') === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                                        <option value="Open Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'Open Sans' ? 'selected' : '' ?>>Open Sans</option>
                                        <option value="Lato" <?= ($settings['frontend_font']['value'] ?? '') === 'Lato' ? 'selected' : '' ?>>Lato</option>
                                        <option value="Montserrat" <?= ($settings['frontend_font']['value'] ?? '') === 'Montserrat' ? 'selected' : '' ?>>Montserrat</option>
                                        <option value="Raleway" <?= ($settings['frontend_font']['value'] ?? '') === 'Raleway' ? 'selected' : '' ?>>Raleway</option>
                                        <option value="Nunito" <?= ($settings['frontend_font']['value'] ?? '') === 'Nunito' ? 'selected' : '' ?>>Nunito</option>
                                        <option value="Source Sans Pro" <?= ($settings['frontend_font']['value'] ?? '') === 'Source Sans Pro' ? 'selected' : '' ?>>Source Sans Pro</option>
                                        <option value="Playfair Display" <?= ($settings['frontend_font']['value'] ?? '') === 'Playfair Display' ? 'selected' : '' ?>>Playfair Display</option>
                                        <option value="Merriweather" <?= ($settings['frontend_font']['value'] ?? '') === 'Merriweather' ? 'selected' : '' ?>>Merriweather</option>
                                        <option value="Ubuntu" <?= ($settings['frontend_font']['value'] ?? '') === 'Ubuntu' ? 'selected' : '' ?>>Ubuntu</option>
                                        <option value="Oswald" <?= ($settings['frontend_font']['value'] ?? '') === 'Oswald' ? 'selected' : '' ?>>Oswald</option>
                                        <option value="Dancing Script" <?= ($settings['frontend_font']['value'] ?? '') === 'Dancing Script' ? 'selected' : '' ?>>Dancing Script</option>
                                        <option value="Crimson Text" <?= ($settings['frontend_font']['value'] ?? '') === 'Crimson Text' ? 'selected' : '' ?>>Crimson Text</option>
                                        <option value="Work Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'Work Sans' ? 'selected' : '' ?>>Work Sans</option>
                                        <option value="Fira Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'Fira Sans' ? 'selected' : '' ?>>Fira Sans</option>
                                        <option value="PT Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'PT Sans' ? 'selected' : '' ?>>PT Sans</option>
                                        <option value="Noto Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'Noto Sans' ? 'selected' : '' ?>>Noto Sans</option>
                                        <option value="Quicksand" <?= ($settings['frontend_font']['value'] ?? '') === 'Quicksand' ? 'selected' : '' ?>>Quicksand</option>
                                        <option value="Rubik" <?= ($settings['frontend_font']['value'] ?? '') === 'Rubik' ? 'selected' : '' ?>>Rubik</option>
                                        <option value="Mukta" <?= ($settings['frontend_font']['value'] ?? '') === 'Mukta' ? 'selected' : '' ?>>Mukta</option>
                                        <option value="Barlow" <?= ($settings['frontend_font']['value'] ?? '') === 'Barlow' ? 'selected' : '' ?>>Barlow</option>
                                        <option value="Manrope" <?= ($settings['frontend_font']['value'] ?? '') === 'Manrope' ? 'selected' : '' ?>>Manrope</option>
                                        <option value="DM Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'DM Sans' ? 'selected' : '' ?>>DM Sans</option>
                                        <option value="Plus Jakarta Sans" <?= ($settings['frontend_font']['value'] ?? '') === 'Plus Jakarta Sans' ? 'selected' : '' ?>>Plus Jakarta Sans</option>

                                        <!-- ============================================ -->
                                        <!-- TAMBAHKAN FONT BARU DI ATAS INI -->
                                        <!-- Contoh: -->
                                        <!-- <option value="Comfortaa" <?= ($settings['frontend_font']['value'] ?? '') === 'Comfortaa' ? 'selected' : '' ?>>Comfortaa</option> -->
                                        <!-- ============================================ -->
                                    </select>
                                    <small class="form-text text-muted">Pilih font dari Google Fonts yang akan digunakan di frontend. Font akan otomatis dimuat dari Google Fonts. Untuk menambah font baru, tambahkan option baru di antara komentar di atas.</small>
                                </div>
                                <div class="form-group">
                                    <label for="frontend_font_weights">Font Weights (Opsional)</label>
                                    <input
                                        type="text"
                                        id="frontend_font_weights"
                                        name="settings[frontend_font_weights]"
                                        value="<?= esc($settings['frontend_font_weights']['value'] ?? '300;400;500;600;700') ?>"
                                        class="form-control"
                                        placeholder="300;400;500;600;700">
                                    <small class="form-text text-muted">Pisahkan dengan titik koma (;) contoh: 300;400;500;600;700. Kosongkan untuk menggunakan default.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('appearance', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Tampilan
                                </button>
                            </div>
                        </div>

                        <!-- Kontak & Informasi -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Kontak & Informasi</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="site_email">Email Kontak <span class="text-danger">*</span></label>
                                        <input
                                            type="email"
                                            id="site_email"
                                            name="settings[site_email]"
                                            value="<?= esc($settings['site_email']['value'] ?? 'info@urunankita.test') ?>"
                                            class="form-control"
                                            placeholder="info@urunankita.test"
                                            required>
                                        <small class="form-text text-muted">Email untuk kontak umum</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="site_phone">Nomor Telepon</label>
                                        <input
                                            type="text"
                                            id="site_phone"
                                            name="settings[site_phone]"
                                            value="<?= esc($settings['site_phone']['value'] ?? '+62 812 3456 7890') ?>"
                                            class="form-control"
                                            placeholder="+62 812 3456 7890">
                                        <small class="form-text text-muted">Nomor telepon kontak</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="site_address">Alamat Lengkap</label>
                                    <textarea
                                        id="site_address"
                                        name="settings[site_address]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Jl. Raya Contoh No. 123, Jakarta Selatan, DKI Jakarta 12345"><?= esc($settings['site_address']['value'] ?? '') ?></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="site_facebook">Facebook</label>
                                        <input
                                            type="url"
                                            id="site_facebook"
                                            name="settings[site_facebook]"
                                            value="<?= esc($settings['site_facebook']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="https://facebook.com/urunankita">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="site_instagram">Instagram</label>
                                        <input
                                            type="url"
                                            id="site_instagram"
                                            name="settings[site_instagram]"
                                            value="<?= esc($settings['site_instagram']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="https://instagram.com/urunankita">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="site_twitter">Twitter</label>
                                        <input
                                            type="url"
                                            id="site_twitter"
                                            name="settings[site_twitter]"
                                            value="<?= esc($settings['site_twitter']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="https://twitter.com/urunankita">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('contact', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Kontak & Informasi
                                </button>
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Pengaturan Email & Notifikasi</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_host">SMTP Host</label>
                                        <input
                                            type="text"
                                            id="smtp_host"
                                            name="settings[smtp_host]"
                                            value="<?= esc($settings['smtp_host']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_port">SMTP Port</label>
                                        <input
                                            type="number"
                                            id="smtp_port"
                                            name="settings[smtp_port]"
                                            value="<?= esc($settings['smtp_port']['value'] ?? '587') ?>"
                                            class="form-control"
                                            placeholder="587">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="smtp_user">SMTP Username</label>
                                        <input
                                            type="text"
                                            id="smtp_user"
                                            name="settings[smtp_user]"
                                            value="<?= esc($settings['smtp_user']['value'] ?? '') ?>"
                                            class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="smtp_password">SMTP Password</label>
                                        <input
                                            type="password"
                                            id="smtp_password"
                                            name="settings[smtp_password]"
                                            value="<?= esc($settings['smtp_password']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="Kosongkan jika tidak ingin mengubah">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="mb-3">Pengaturan WhatsApp</h6>

                                <div class="form-group">
                                    <label for="whatsapp_api_url">WhatsApp API URL</label>
                                    <input
                                        type="url"
                                        id="whatsapp_api_url"
                                        name="settings[whatsapp_api_url]"
                                        value="<?= esc($settings['whatsapp_api_url']['value'] ?? 'https://app.whappi.biz.id/api/qr/rest/send_message') ?>"
                                        class="form-control"
                                        placeholder="https://app.whappi.biz.id/api/qr/rest/send_message">
                                    <small class="form-text text-muted">URL endpoint API WhatsApp</small>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="whatsapp_api_token">WhatsApp API Token</label>
                                        <input
                                            type="text"
                                            id="whatsapp_api_token"
                                            name="settings[whatsapp_api_token]"
                                            value="<?= esc($settings['whatsapp_api_token']['value'] ?? '') ?>"
                                            class="form-control"
                                            placeholder="Masukkan API token WhatsApp">
                                        <small class="form-text text-muted">Token untuk autentikasi API WhatsApp</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="whatsapp_from_number">Nomor Pengirim WhatsApp</label>
                                        <input
                                            type="text"
                                            id="whatsapp_from_number"
                                            name="settings[whatsapp_from_number]"
                                            value="<?= esc($settings['whatsapp_from_number']['value'] ?? '6282119339330') ?>"
                                            class="form-control"
                                            placeholder="6282119339330">
                                        <small class="form-text text-muted">Nomor WhatsApp pengirim (dengan kode negara, tanpa +)</small>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="mb-3">Template Pesan WhatsApp</h6>
                                <p class="text-muted small mb-3">Gunakan placeholder seperti {amount}, {campaign_title}, {donor_name}, {site_name} untuk konten dinamis. Aktifkan/nonaktifkan template dengan toggle di bawah ini.</p>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="whatsapp_template_donation_created" class="mb-0">Template: Donasi Dibuat</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="whatsapp_template_donation_created_enabled" 
                                                name="settings[whatsapp_template_donation_created_enabled]" 
                                                value="1" 
                                                <?= ($settings['whatsapp_template_donation_created_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="whatsapp_template_donation_created_enabled">Aktif</label>
                                        </div>
                                    </div>
                                    <textarea
                                        id="whatsapp_template_donation_created"
                                        name="settings[whatsapp_template_donation_created]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Terima kasih! Donasi Anda sebesar Rp {amount} sedang diproses. Silakan lakukan pembayaran sesuai metode yang dipilih."><?= esc($settings['whatsapp_template_donation_created']['value'] ?? 'Terima kasih! Donasi Anda sebesar Rp {amount} sedang diproses. Silakan lakukan pembayaran sesuai metode yang dipilih.') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="whatsapp_template_donation_paid" class="mb-0">Template: Donasi Diterima</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="whatsapp_template_donation_paid_enabled" 
                                                name="settings[whatsapp_template_donation_paid_enabled]" 
                                                value="1" 
                                                <?= ($settings['whatsapp_template_donation_paid_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="whatsapp_template_donation_paid_enabled">Aktif</label>
                                        </div>
                                    </div>
                                    <textarea
                                        id="whatsapp_template_donation_paid"
                                        name="settings[whatsapp_template_donation_paid]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Terima kasih! Donasi Anda sebesar Rp {amount} untuk '{campaign_title}' telah diterima. Semoga menjadi amal jariyah yang berkah."><?= esc($settings['whatsapp_template_donation_paid']['value'] ?? 'Terima kasih! Donasi Anda sebesar Rp {amount} untuk \'{campaign_title}\' telah diterima. Semoga menjadi amal jariyah yang berkah.') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="whatsapp_template_withdrawal_created" class="mb-0">Template: Penarikan Dibuat</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="whatsapp_template_withdrawal_created_enabled" 
                                                name="settings[whatsapp_template_withdrawal_created_enabled]" 
                                                value="1" 
                                                <?= ($settings['whatsapp_template_withdrawal_created_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="whatsapp_template_withdrawal_created_enabled">Aktif</label>
                                        </div>
                                    </div>
                                    <textarea
                                        id="whatsapp_template_withdrawal_created"
                                        name="settings[whatsapp_template_withdrawal_created]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Permohonan penarikan dana sebesar Rp {amount} telah dibuat dan sedang diproses. Kami akan menginformasikan status selanjutnya."><?= esc($settings['whatsapp_template_withdrawal_created']['value'] ?? 'Permohonan penarikan dana sebesar Rp {amount} telah dibuat dan sedang diproses. Kami akan menginformasikan status selanjutnya.') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {campaign_title}, {site_name}</small>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="whatsapp_template_withdrawal_approved" class="mb-0">Template: Penarikan Disetujui</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="whatsapp_template_withdrawal_approved_enabled" 
                                                name="settings[whatsapp_template_withdrawal_approved_enabled]" 
                                                value="1" 
                                                <?= ($settings['whatsapp_template_withdrawal_approved_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="whatsapp_template_withdrawal_approved_enabled">Aktif</label>
                                        </div>
                                    </div>
                                    <textarea
                                        id="whatsapp_template_withdrawal_approved"
                                        name="settings[whatsapp_template_withdrawal_approved]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Selamat! Permohonan penarikan dana sebesar Rp {amount} telah disetujui dan sedang diproses transfer."><?= esc($settings['whatsapp_template_withdrawal_approved']['value'] ?? 'Selamat! Permohonan penarikan dana sebesar Rp {amount} telah disetujui dan sedang diproses transfer.') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {campaign_title}, {site_name}</small>
                                </div>

                                <hr class="my-4">
                                <h6 class="mb-3">Template Notifikasi untuk Tenant/Penggalang Dana</h6>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="whatsapp_template_tenant_donation_new" class="mb-0">Template: Donasi Baru (untuk Tenant)</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="whatsapp_template_tenant_donation_new_enabled" 
                                                name="settings[whatsapp_template_tenant_donation_new_enabled]" 
                                                value="1" 
                                                <?= ($settings['whatsapp_template_tenant_donation_new_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="whatsapp_template_tenant_donation_new_enabled">Aktif</label>
                                        </div>
                                    </div>
                                    <textarea
                                        id="whatsapp_template_tenant_donation_new"
                                        name="settings[whatsapp_template_tenant_donation_new]"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan '{campaign_title}'. Silakan konfirmasi pembayaran di dashboard."><?= esc($settings['whatsapp_template_tenant_donation_new']['value'] ?? 'Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan \'{campaign_title}\'. Silakan konfirmasi pembayaran di dashboard.') ?></textarea>
                                    <small class="form-text text-muted">Placeholder: {amount}, {donor_name}, {campaign_title}, {site_name}, {donation_id}, {bank}, {rekening}, {deskripsi_pembayaran}</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('email', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Email & Notifikasi
                                </button>
                            </div>
                        </div>

                        <!-- Payment Settings -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Pengaturan Payment Gateway</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="midtrans_server_key">Midtrans Server Key</label>
                                        <input
                                            type="text"
                                            id="midtrans_server_key"
                                            name="settings[midtrans_server_key]"
                                            value="<?= esc($settings['midtrans_server_key']['value'] ?? '') ?>"
                                            class="form-control">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="midtrans_client_key">Midtrans Client Key</label>
                                        <input
                                            type="text"
                                            id="midtrans_client_key"
                                            name="settings[midtrans_client_key]"
                                            value="<?= esc($settings['midtrans_client_key']['value'] ?? '') ?>"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="payment_mode">Mode Pembayaran</label>
                                        <select id="payment_mode" name="settings[payment_mode]" class="form-control">
                                            <option value="sandbox" <?= ($settings['payment_mode']['value'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                            <option value="production" <?= ($settings['payment_mode']['value'] ?? '') === 'production' ? 'selected' : '' ?>>Production</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('payment', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Payment
                                </button>
                            </div>
                        </div>

                        <!-- Domain & Subdomain -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Domain & Subdomain</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="main_domain">Domain Utama <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        id="main_domain"
                                        name="settings[main_domain]"
                                        value="<?= esc($settings['main_domain']['value'] ?? 'urunankita.test') ?>"
                                        class="form-control"
                                        placeholder="urunankita.test"
                                        required>
                                    <small class="form-text text-muted">Domain utama platform (contoh: urunankita.test)</small>
                                </div>
                                <div class="form-group">
                                    <label for="subdomain_format">Subdomain Format</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            id="subdomain_format"
                                            name="settings[subdomain_format]"
                                            value="<?= esc($settings['subdomain_format']['value'] ?? '{subdomain}') ?>"
                                            class="form-control"
                                            placeholder="{subdomain}"
                                            readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">.<?= esc($settings['main_domain']['value'] ?? 'urunankita.test') ?></span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Format subdomain untuk penggalang dana (gunakan {subdomain} sebagai placeholder)</small>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="subdomain_enabled"
                                            name="settings[subdomain_enabled]"
                                            value="1"
                                            <?= ($settings['subdomain_enabled']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="subdomain_enabled">
                                            <strong>Izinkan Pembuatan Subdomain Baru</strong>
                                            <small class="d-block text-muted">Aktifkan untuk memungkinkan pembuatan subdomain baru untuk penggalang dana</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('domain', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Domain
                                </button>
                            </div>
                        </div>

                        <!-- Pengaturan Umum -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">Pengaturan Umum</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="timezone">Zona Waktu</label>
                                    <select id="timezone" name="settings[timezone]" class="form-control">
                                        <option value="Asia/Jakarta" <?= ($settings['timezone']['value'] ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar" <?= ($settings['timezone']['value'] ?? '') === 'Asia/Makassar' ? 'selected' : '' ?>>Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura" <?= ($settings['timezone']['value'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' ?>>Asia/Jayapura (WIT)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="default_language">Bahasa Default</label>
                                    <select id="default_language" name="settings[default_language]" class="form-control">
                                        <option value="id" <?= ($settings['default_language']['value'] ?? 'id') === 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                                        <option value="en" <?= ($settings['default_language']['value'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="maintenance_mode"
                                            name="settings[maintenance_mode]"
                                            value="1"
                                            <?= ($settings['maintenance_mode']['value'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="maintenance_mode">
                                            <strong>Maintenance Mode</strong>
                                            <small class="d-block text-muted">Aktifkan untuk menampilkan halaman maintenance</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="allow_registration"
                                            name="settings[allow_registration]"
                                            value="1"
                                            <?= ($settings['allow_registration']['value'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="allow_registration">
                                            <strong>Izinkan Pendaftaran Publik</strong>
                                            <small class="d-block text-muted">Aktifkan untuk mengizinkan pendaftaran penggalang dana secara publik</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('general', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan Umum
                                </button>
                            </div>
                        </div>

                        <!-- SEO & Meta -->
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <strong class="card-title">SEO & Meta Tags</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="meta_title">Meta Title</label>
                                    <input
                                        type="text"
                                        id="meta_title"
                                        name="settings[meta_title]"
                                        value="<?= esc($settings['meta_title']['value'] ?? 'UrunanKita - Platform Crowdfunding Terpercaya') ?>"
                                        class="form-control"
                                        maxlength="60">
                                    <small class="form-text text-muted">Judul untuk SEO (disarankan maksimal 60 karakter)</small>
                                </div>
                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea
                                        id="meta_description"
                                        name="settings[meta_description]"
                                        rows="2"
                                        class="form-control"
                                        maxlength="160"><?= esc($settings['meta_description']['value'] ?? 'Platform crowdfunding terpercaya untuk membantu berbagai kebutuhan sosial dan kemanusiaan. Bergabunglah dengan ribuan penggalang dana dan donatur.') ?></textarea>
                                    <small class="form-text text-muted">Deskripsi untuk SEO (disarankan maksimal 160 karakter)</small>
                                </div>
                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <input
                                        type="text"
                                        id="meta_keywords"
                                        name="settings[meta_keywords]"
                                        value="<?= esc($settings['meta_keywords']['value'] ?? 'crowdfunding, urunan, donasi, bantuan, sosial, kemanusiaan') ?>"
                                        class="form-control"
                                        placeholder="crowdfunding, urunan, donasi, bantuan, sosial, kemanusiaan">
                                    <small class="form-text text-muted">Kata kunci untuk SEO (pisahkan dengan koma)</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-sm btn-primary" onclick="simpanSection('seo', this)">
                                    <span class="fe fe-save fe-12 mr-1"></span>Simpan Pengaturan SEO
                                </button>
                            </div>
                        </div>

                    </form>
                </div> <!-- End General Tab -->

                <!-- Template Messages Tab -->
                <div class="tab-pane fade" id="template" role="tabpanel" aria-labelledby="template-tab">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><span class="fe fe-info fe-16 mr-2"></span>Informasi</h6>
                        <p class="mb-0">
                            Pengaturan template pesan WhatsApp telah dipindahkan ke tab <strong>"Pengaturan Umum" → "Pengaturan Email & Notifikasi"</strong>
                            untuk memudahkan pengelolaan. Silakan gunakan tab tersebut untuk mengatur template WhatsApp.
                        </p>
                        <hr>
                        <p class="mb-0 small">
                            <strong>Catatan:</strong> Template WhatsApp digunakan untuk mengirim notifikasi otomatis kepada donor dan tenant
                            saat ada donasi baru, donasi diterima, penarikan dibuat, atau penarikan disetujui.
                        </p>
                    </div>
                </div> <!-- End Template Tab -->

                <!-- Tenant Settings Tab -->
                <div class="tab-pane fade" id="tenant" role="tabpanel" aria-labelledby="tenant-tab">
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <strong class="card-title">Gambar Hero Tenant</strong>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Atur gambar hero untuk setiap tenant. Gambar ini akan ditampilkan di halaman depan tenant.
                            </p>

                            <div class="form-group">
                                <label for="selectTenantForHero">Pilih Tenant</label>
                                <select id="selectTenantForHero" class="form-control" onchange="loadTenantHeroImage(this.value)">
                                    <option value="">-- Pilih Tenant --</option>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <option value="<?= $tenant['id'] ?>" data-tenant-name="<?= esc($tenant['name']) ?>">
                                            <?= esc($tenant['name']) ?> (<?= esc($tenant['slug']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="tenant-hero-section" style="display: none;">
                                <div class="form-group">
                                    <label>Gambar Hero <span id="selected-tenant-name"></span></label>
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
                                        <!-- Preview akan muncul di sini -->
                                    </div>
                                    <input type="hidden" id="hero_image_tenant" name="hero_image_tenant" value="">
                                    <small class="form-text text-muted">Gambar ini akan ditampilkan di bagian kanan hero section pada halaman depan tenant</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End Tenant Tab -->
            </div> <!-- End Tabs Content -->
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Simpan Pengaturan per Section
    function simpanSection(section, buttonElement) {
        var form = document.getElementById('formPengaturanUmum');
        var formData = new FormData();
        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content'));
        formData.append('section', section);

        // Define fields per section
        var sectionFields = {
            'platform': ['site_name', 'site_tagline', 'site_description', 'site_logo', 'site_favicon', 'hero_image'],
            'appearance': ['frontend_font', 'frontend_font_weights'],
            'contact': ['site_email', 'site_phone', 'site_address', 'site_facebook', 'site_instagram', 'site_twitter'],
            'email': ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_password', 'whatsapp_api_url', 'whatsapp_api_token', 'whatsapp_from_number', 'whatsapp_template_donation_created', 'whatsapp_template_donation_created_enabled', 'whatsapp_template_donation_paid', 'whatsapp_template_donation_paid_enabled', 'whatsapp_template_withdrawal_created', 'whatsapp_template_withdrawal_created_enabled', 'whatsapp_template_withdrawal_approved', 'whatsapp_template_withdrawal_approved_enabled', 'whatsapp_template_tenant_donation_new', 'whatsapp_template_tenant_donation_new_enabled'],
            'payment': ['midtrans_server_key', 'midtrans_client_key', 'payment_mode'],
            'domain': ['main_domain', 'subdomain_format', 'subdomain_enabled'],
            'general': ['timezone', 'default_language', 'maintenance_mode', 'allow_registration'],
            'seo': ['meta_title', 'meta_description', 'meta_keywords']
        };

        var fields = sectionFields[section] || [];
        var settings = {};

        // Collect data for this section
        fields.forEach(function(field) {
            var input = form.querySelector('[name="settings[' + field + ']"]');
            if (input) {
                if (input.type === 'checkbox') {
                    settings[field] = input.checked ? input.value : '0';
                } else {
                    settings[field] = input.value || '';
                }
            } else {
                // Check for hidden inputs (logo/favicon/hero_image)
                var hiddenInput = document.getElementById(field);
                if (hiddenInput) {
                    settings[field] = hiddenInput.value || '';
                }
            }
        });

        formData.append('settings', JSON.stringify(settings));

        // Show loading state
        var btn = buttonElement;
        if (!btn) {
            // Fallback: find button by section
            var buttons = document.querySelectorAll('button[onclick*="simpanSection(\'' + section + '\'"]');
            btn = buttons.length > 0 ? buttons[0] : null;
        }
        if (!btn) {
            console.error('Button not found for section:', section);
            return;
        }
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...';

        $.ajax({
            url: '<?= base_url('admin/settings/save-section') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response && response.success) {
                    // Show success message
                    showAlert('success', response.message || 'Pengaturan berhasil disimpan');
                } else {
                    showAlert('danger', (response && response.message) || 'Gagal menyimpan pengaturan');
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan saat menyimpan';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        message = response.message;
                    }
                } catch (e) {
                    // Use default message
                }
                showAlert('danger', message);
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    // Show alert message
    function showAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span></button></div>';

        // Remove existing alerts
        $('.alert').remove();

        // Add new alert at the top
        $('.container-fluid > .row:first').after(alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initialize Dropzone for logo and favicon (if available)
    $(document).ready(function() {
        // Disable auto-discover untuk semua dropzone
        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
        }

        function initDropzone() {
            if (typeof Dropzone !== 'undefined') {
                // Logo dropzone
                var logoElement = document.getElementById('dropzone-logo');
                if (logoElement) {
                    // Destroy existing dropzone if any
                    if (logoElement.dropzone) {
                        logoElement.dropzone.destroy();
                    }

                    var logoDropzone = new Dropzone('#dropzone-logo', {
                        url: '<?= base_url('admin/settings/upload-logo') ?>',
                        maxFiles: 1,
                        maxFilesize: 2,
                        acceptedFiles: 'image/png,image/svg+xml',
                        addRemoveLinks: true,
                        dictDefaultMessage: '',
                        dictRemoveFile: 'Hapus',
                        clickable: '#dropzone-logo .dz-message',
                        autoProcessQueue: true,
                        paramName: 'file',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        init: function() {
                            var myDropzone = this;

                            // Add CSRF token
                            myDropzone.on('sending', function(file, xhr, formData) {
                                var csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content');
                                if (csrfToken) {
                                    formData.append('<?= csrf_token() ?>', csrfToken);
                                }
                            });

                            myDropzone.on('success', function(file, response) {
                                console.log('Logo uploaded:', response);
                                if (typeof response === 'string') {
                                    try {
                                        response = JSON.parse(response);
                                    } catch (e) {
                                        console.error('Failed to parse response:', e);
                                    }
                                }
                                if (response && response.path) {
                                    document.getElementById('site_logo').value = response.path;
                                    // Update preview image (below dropzone)
                                    var previewHtml = '<div class="d-flex align-items-center">' +
                                        '<img src="' + response.path + '" alt="Logo" class="img-thumbnail mr-2" style="max-width: 200px; max-height: 100px;">' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLogo()">' +
                                        '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
                                    $('#logo-preview-container').html(previewHtml);
                                }
                            });

                            myDropzone.on('removedfile', function() {
                                document.getElementById('site_logo').value = '';
                                $('#logo-preview-container').empty();
                            });

                            myDropzone.on('error', function(file, errorMessage) {
                                console.error('Dropzone error:', errorMessage);
                                if (typeof errorMessage === 'string') {
                                    try {
                                        var errorObj = JSON.parse(errorMessage);
                                        errorMessage = errorObj.message || errorMessage;
                                    } catch (e) {}
                                }
                                alert('Error uploading logo: ' + errorMessage);
                            });
                        }
                    });
                    console.log('Logo dropzone initialized');
                }

                // Favicon dropzone
                var faviconElement = document.getElementById('dropzone-favicon');
                if (faviconElement) {
                    // Destroy existing dropzone if any
                    if (faviconElement.dropzone) {
                        faviconElement.dropzone.destroy();
                    }

                    var faviconDropzone = new Dropzone('#dropzone-favicon', {
                        url: '<?= base_url('admin/settings/upload-favicon') ?>',
                        maxFiles: 1,
                        maxFilesize: 1,
                        acceptedFiles: 'image/x-icon,image/png',
                        addRemoveLinks: true,
                        dictDefaultMessage: '',
                        dictRemoveFile: 'Hapus',
                        clickable: '#dropzone-favicon .dz-message',
                        autoProcessQueue: true,
                        paramName: 'file',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        init: function() {
                            var myDropzone = this;

                            // Add CSRF token
                            myDropzone.on('sending', function(file, xhr, formData) {
                                var csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content');
                                if (csrfToken) {
                                    formData.append('<?= csrf_token() ?>', csrfToken);
                                }
                            });

                            myDropzone.on('success', function(file, response) {
                                console.log('Favicon uploaded:', response);
                                if (typeof response === 'string') {
                                    try {
                                        response = JSON.parse(response);
                                    } catch (e) {
                                        console.error('Failed to parse response:', e);
                                    }
                                }
                                if (response && response.path) {
                                    document.getElementById('site_favicon').value = response.path;
                                    // Update preview image (below dropzone)
                                    var previewHtml = '<div class="d-flex align-items-center">' +
                                        '<img src="' + response.path + '" alt="Favicon" class="img-thumbnail mr-2" style="max-width: 64px; max-height: 64px;">' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFavicon()">' +
                                        '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
                                    $('#favicon-preview-container').html(previewHtml);
                                }
                            });

                            myDropzone.on('removedfile', function() {
                                document.getElementById('site_favicon').value = '';
                                $('#favicon-preview-container').empty();
                            });

                            myDropzone.on('error', function(file, errorMessage) {
                                console.error('Dropzone error:', errorMessage);
                                if (typeof errorMessage === 'string') {
                                    try {
                                        var errorObj = JSON.parse(errorMessage);
                                        errorMessage = errorObj.message || errorMessage;
                                    } catch (e) {}
                                }
                                alert('Error uploading favicon: ' + errorMessage);
                            });
                        }
                    });
                }

                // Hero image dropzone
                var heroImageElement = document.getElementById('dropzone-hero-image');
                if (heroImageElement) {
                    // Destroy existing dropzone if any
                    if (heroImageElement.dropzone) {
                        heroImageElement.dropzone.destroy();
                    }

                    var heroImageDropzone = new Dropzone('#dropzone-hero-image', {
                        url: '<?= base_url('admin/settings/upload-hero-image') ?>',
                        maxFiles: 1,
                        maxFilesize: 5,
                        acceptedFiles: 'image/jpeg,image/jpg,image/png,image/gif,image/webp',
                        addRemoveLinks: true,
                        dictDefaultMessage: '',
                        dictRemoveFile: 'Hapus',
                        clickable: '#dropzone-hero-image .dz-message',
                        autoProcessQueue: true,
                        paramName: 'file',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        init: function() {
                            var myDropzone = this;

                            // Add CSRF token
                            myDropzone.on('sending', function(file, xhr, formData) {
                                var csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content');
                                if (csrfToken) {
                                    formData.append('<?= csrf_token() ?>', csrfToken);
                                }
                            });

                            myDropzone.on('success', function(file, response) {
                                console.log('Hero image uploaded:', response);
                                if (typeof response === 'string') {
                                    try {
                                        response = JSON.parse(response);
                                    } catch (e) {
                                        console.error('Failed to parse response:', e);
                                    }
                                }
                                if (response && response.path) {
                                    document.getElementById('hero_image').value = response.path;
                                    // Update preview image (below dropzone)
                                    var previewHtml = '<div class="d-flex align-items-center">' +
                                        '<img src="' + response.path + '" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHeroImage()">' +
                                        '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
                                    $('#hero-image-preview-container').html(previewHtml);
                                }
                            });

                            myDropzone.on('removedfile', function() {
                                document.getElementById('hero_image').value = '';
                                $('#hero-image-preview-container').empty();
                            });

                            myDropzone.on('error', function(file, errorMessage) {
                                console.error('Dropzone error:', errorMessage);
                                if (typeof errorMessage === 'string') {
                                    try {
                                        var errorObj = JSON.parse(errorMessage);
                                        errorMessage = errorObj.message || errorMessage;
                                    } catch (e) {}
                                }
                                alert('Error uploading hero image: ' + errorMessage);
                            });
                        }
                    });
                }

                console.log('Dropzone initialized for logo, favicon, and hero image');
            } else {
                console.warn('Dropzone library not loaded, retrying...');
                setTimeout(initDropzone, 500);
            }
        }

        // Wait a bit for all scripts to load
        setTimeout(initDropzone, 500);
    });

    // Remove Logo
    function removeLogo() {
        if (confirm('Yakin ingin menghapus logo?')) {
            document.getElementById('site_logo').value = '';
            $('#logo-preview-container').empty();

            // Update setting via AJAX
            $.ajax({
                url: '<?= base_url('admin/settings/remove-logo') ?>',
                method: 'POST',
                data: {
                    <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Logo removed');
                },
                error: function(xhr) {
                    console.error('Error removing logo:', xhr);
                }
            });
        }
    }

    // Remove Favicon
    function removeFavicon() {
        if (confirm('Yakin ingin menghapus favicon?')) {
            document.getElementById('site_favicon').value = '';
            $('#favicon-preview-container').empty();

            // Update setting via AJAX
            $.ajax({
                url: '<?= base_url('admin/settings/remove-favicon') ?>',
                method: 'POST',
                data: {
                    <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Favicon removed');
                },
                error: function(xhr) {
                    console.error('Error removing favicon:', xhr);
                }
            });
        }
    }

    // Remove Hero Image
    function removeHeroImage() {
        if (confirm('Yakin ingin menghapus gambar hero?')) {
            document.getElementById('hero_image').value = '';
            $('#hero-image-preview-container').empty();

            // Update setting via AJAX
            $.ajax({
                url: '<?= base_url('admin/settings/remove-hero-image') ?>',
                method: 'POST',
                data: {
                    <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Hero image removed');
                },
                error: function(xhr) {
                    console.error('Error removing hero image:', xhr);
                }
            });
        }
    }

    // Simpan Template Section
    function simpanTemplateSection() {
        var form = document.getElementById('formTemplateSettings');
        var formData = new FormData();
        formData.append('<?= csrf_token() ?>', $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content'));
        formData.append('section', 'template');

        // Collect template fields
        var settings = {};
        var templateFields = [
            'whatsapp_template_donation_created',
            'whatsapp_template_donation_paid',
            'whatsapp_template_withdrawal_created',
            'whatsapp_template_withdrawal_approved',
            'whatsapp_template_tenant_donation_new'
        ];

        templateFields.forEach(function(field) {
            var input = form.querySelector('[name="settings[' + field + ']"]');
            if (input) {
                settings[field] = input.value || '';
            }
        });

        formData.append('settings', JSON.stringify(settings));

        // Show loading state
        var btn = event.target.closest('button');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...';

        $.ajax({
            url: '<?= base_url('admin/settings/save-section') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response && response.success) {
                    showAlert('success', response.message || 'Template berhasil disimpan');
                } else {
                    showAlert('danger', (response && response.message) || 'Gagal menyimpan template');
                }
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan saat menyimpan';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        message = response.message;
                    }
                } catch (e) {
                    // Use default message
                }
                showAlert('danger', message);
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    // Auto-activate template tab if hash is present
    $(document).ready(function() {
        // Check hash on page load
        if (window.location.hash === '#template') {
            $('#template-tab').tab('show');
        }

        // Listen for hash changes
        $(window).on('hashchange', function() {
            if (window.location.hash === '#template') {
                $('#template-tab').tab('show');
            } else if (window.location.hash === '#general' || window.location.hash === '') {
                $('#general-tab').tab('show');
            }
        });

        // Update hash when tab changes
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr('href');
            if (target === '#template') {
                window.location.hash = '#template';
            } else if (target === '#general') {
                window.location.hash = '#general';
            }
        });
    });

    // Load Tenant Hero Image
    function loadTenantHeroImage(tenantId) {
        if (!tenantId) {
            $('#tenant-hero-section').hide();
            return;
        }

        const tenantName = $('#selectTenantForHero option:selected').data('tenant-name') || '';
        $('#selected-tenant-name').text('(' + tenantName + ')');
        $('#tenant-hero-section').show();

        // Get current hero image for this tenant
        const currentHeroImage = <?= json_encode($tenantHeroImages ?? []) ?>[tenantId] || '';

        if (currentHeroImage) {
            const previewHtml = '<div class="d-flex align-items-center">' +
                '<img src="' + currentHeroImage + '" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTenantHeroImage(' + tenantId + ')">' +
                '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
            $('#hero-image-tenant-preview-container').html(previewHtml);
            $('#hero_image_tenant').val(currentHeroImage);
        } else {
            $('#hero-image-tenant-preview-container').empty();
            $('#hero_image_tenant').val('');
        }

        // Reinitialize dropzone for this tenant
        initTenantHeroDropzone(tenantId);
    }

    // Initialize Tenant Hero Dropzone
    function initTenantHeroDropzone(tenantId) {
        if (typeof Dropzone === 'undefined') {
            console.warn('Dropzone not loaded');
            return;
        }

        const heroElement = document.getElementById('dropzone-hero-image-tenant');
        if (!heroElement) return;

        // Destroy existing dropzone if any
        if (heroElement.dropzone) {
            heroElement.dropzone.destroy();
        }

        const tenantHeroDropzone = new Dropzone('#dropzone-hero-image-tenant', {
            url: '<?= base_url('admin/settings/upload-hero-image-tenant') ?>',
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

                // Add CSRF token and tenant_id
                myDropzone.on('sending', function(file, xhr, formData) {
                    const csrfToken = $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content');
                    if (csrfToken) {
                        formData.append('<?= csrf_token() ?>', csrfToken);
                    }
                    formData.append('tenant_id', tenantId);
                });

                myDropzone.on('success', function(file, response) {
                    console.log('Tenant hero image uploaded:', response);
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse response:', e);
                        }
                    }
                    if (response && response.path) {
                        $('#hero_image_tenant').val(response.path);
                        const previewHtml = '<div class="d-flex align-items-center">' +
                            '<img src="' + response.path + '" alt="Hero Image" class="img-thumbnail mr-2" style="max-width: 300px; max-height: 200px;">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTenantHeroImage(' + tenantId + ')">' +
                            '<span class="fe fe-trash-2 fe-12"></span> Hapus</button></div>';
                        $('#hero-image-tenant-preview-container').html(previewHtml);
                        showAlert('success', 'Gambar hero tenant berhasil diupload');
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
                        } catch (e) {}
                    }
                    showAlert('danger', 'Error uploading hero image: ' + errorMessage);
                });
            }
        });
    }

    // Remove Tenant Hero Image
    function removeTenantHeroImage(tenantId) {
        if (!confirm('Yakin ingin menghapus gambar hero tenant ini?')) {
            return;
        }

        $.ajax({
            url: '<?= base_url('admin/settings/remove-hero-image-tenant') ?>',
            method: 'POST',
            data: {
                <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val() || $('meta[name="csrf-token"]').attr('content'),
                tenant_id: tenantId
            },
            success: function(response) {
                console.log('Tenant hero image removed');
                $('#hero_image_tenant').val('');
                $('#hero-image-tenant-preview-container').empty();
                showAlert('success', 'Gambar hero tenant berhasil dihapus');
            },
            error: function(xhr) {
                console.error('Error removing tenant hero image:', xhr);
                showAlert('danger', 'Gagal menghapus gambar hero tenant');
            }
        });
    }

    // Make functions global
    window.simpanPengaturan = simpanPengaturan;
    window.removeLogo = removeLogo;
    window.removeFavicon = removeFavicon;
    window.removeHeroImage = removeHeroImage;
    window.simpanTemplateSection = simpanTemplateSection;
    window.loadTenantHeroImage = loadTenantHeroImage;
    window.removeTenantHeroImage = removeTenantHeroImage;
</script>
<?= $this->endSection() ?>