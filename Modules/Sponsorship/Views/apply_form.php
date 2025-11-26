<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title>Pengajuan Sponsorship — UrunanKita.id</title>
<meta name="description" content="Ajukan sponsorship untuk mendukung program kemanusiaan di UrunanKita.id. Bantu wujudkan kebaikan bersama melalui donasi, barang, jasa, atau kolaborasi program.">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="bg-gradient-to-b from-white via-slate-50 to-slate-100 border-b">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-medium mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Form UrunanKita.id
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Pengajuan Sponsorship</h1>
        <p class="mt-4 text-slate-600 text-base md:text-lg max-w-3xl mx-auto">
            Mari bersama-sama wujudkan kebaikan melalui sponsorship. Lengkapi data berikut untuk menjadi sponsor resmi UrunanKita.id dan dukung program kemanusiaan yang bermanfaat.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-xs text-slate-500">
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Verifikasi data perusahaan</span>
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Review proposal sponsorship</span>
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Penandatanganan kerjasama</span>
        </div>
    </div>
</section>

<!-- Process timeline -->
<section class="border-b bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <p class="text-xs font-semibold tracking-[0.3em] text-emerald-600 uppercase">Tahapan</p>
                <h3 class="text-2xl font-semibold text-slate-900 mt-2">Alur Kerjasama Sponsorship</h3>
                <p class="text-sm text-slate-600 mt-1">Estimasi total proses 3-7 hari kerja setelah dokumen lengkap.</p>
            </div>
            <div class="text-sm text-slate-500 bg-white border border-slate-200 rounded-xl px-4 py-2 shadow-sm">
                <span class="font-semibold text-slate-900">Tips:</span> siapkan dokumen perusahaan dan proposal sponsorship.
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 1</div>
                <h4 class="mt-2 font-semibold text-slate-900">Kirim Form</h4>
                <p class="text-sm text-slate-600 mt-1">Lengkapi data perusahaan, detail sponsorship, dan dokumen pendukung.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 2</div>
                <h4 class="mt-2 font-semibold text-slate-900">Review Proposal</h4>
                <p class="text-sm text-slate-600 mt-1">Tim kami memeriksa kelengkapan data dan kesesuaian program.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 3</div>
                <h4 class="mt-2 font-semibold text-slate-900">Diskusi & Negosiasi</h4>
                <p class="text-sm text-slate-600 mt-1">Koordinasi detail kerjasama, kontrak, dan implementasi program.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 4</div>
                <h4 class="mt-2 font-semibold text-slate-900">Penandatanganan</h4>
                <p class="text-sm text-slate-600 mt-1">Kesepakatan resmi dan peluncuran program sponsorship.</p>
            </div>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-gray-500 font-medium">Formulir Pengajuan Sponsorship</p>
        <h2 class="mt-3 text-2xl font-semibold text-gray-900">Lengkapi Data Sponsorship</h2>
        <p class="mt-2 text-base text-gray-600 max-w-2xl mx-auto">
            Harap siapkan data perusahaan, detail sponsorship, logo (jika ada), dan dokumen pendukung lainnya.
        </p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 text-sm">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 text-sm space-y-1">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('page/sponsorship') ?>" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-xl shadow-sm px-6 py-7 space-y-8">
        <?= csrf_field() ?>
        
        <!-- A. Informasi Perusahaan / Sponsor -->
        <div class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">A. Informasi Perusahaan / Sponsor</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nama Perusahaan / Instansi <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="<?= old('company_name') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nama PIC (Penanggung Jawab) <span class="text-red-500">*</span></label>
                    <input type="text" name="pic_name" value="<?= old('pic_name') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Jabatan PIC <span class="text-red-500">*</span></label>
                    <input type="text" name="pic_position" value="<?= old('pic_position') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?= old('email') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="<?= old('phone') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Website / Sosial Media (opsional)</label>
                    <input type="url" name="website" value="<?= old('website') ?>" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Alamat Perusahaan <span class="text-red-500">*</span></label>
                <textarea name="address" rows="3" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('address') ?></textarea>
            </div>
        </div>

        <!-- B. Detail Sponsorship -->
        <div class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">B. Detail Sponsorship</h3>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Jenis Sponsorship <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                        <input type="radio" name="sponsor_type" value="donasi" <?= old('sponsor_type', 'donasi') === 'donasi' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Donasi Dana</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                        <input type="radio" name="sponsor_type" value="barang" <?= old('sponsor_type') === 'barang' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Barang / Produk</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                        <input type="radio" name="sponsor_type" value="jasa" <?= old('sponsor_type') === 'jasa' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Layanan / Jasa</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                        <input type="radio" name="sponsor_type" value="kolaborasi" <?= old('sponsor_type') === 'kolaborasi' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Kolaborasi Program</p>
                        </div>
                    </label>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nominal Sponsorship / Nilai Bantuan</label>
                    <input type="number" name="amount" value="<?= old('amount') ?>" placeholder="0" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika belum ditentukan</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Kategori yang Ingin Didukung <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="pendidikan" <?= in_array('pendidikan', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Pendidikan</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="sosial" <?= in_array('sosial', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Sosial</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="kesehatan" <?= in_array('kesehatan', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Kesehatan</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="bencana_alam" <?= in_array('bencana_alam', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Bencana Alam</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="ekonomi" <?= in_array('ekonomi', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Ekonomi/Kerakyatan</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="event" <?= in_array('event', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Event Komunitas</span>
                        </label>
                    </div>
                    <div class="mt-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="categories[]" value="lainnya" id="categoryLainnya" <?= in_array('lainnya', old('categories', [])) ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <span>Lainnya</span>
                        </label>
                        <input type="text" name="category_other" value="<?= old('category_other') ?>" placeholder="Sebutkan kategori lainnya" id="categoryOtherInput" class="mt-2 w-full px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm <?= !in_array('lainnya', old('categories', [])) ? 'hidden' : '' ?>">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Deskripsi Bantuan (jika berupa barang/jasa)</label>
                <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('description') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Jelaskan detail bantuan yang akan diberikan (jenis barang, spesifikasi, jumlah, dll.)</p>
            </div>
        </div>

        <!-- C. Preferensi Publikasi -->
        <div class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">C. Preferensi Publikasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Apakah sponsor ingin ditampilkan di halaman kampanye? <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                            <input type="radio" name="public_visibility" value="yes" <?= old('public_visibility', 'yes') === 'yes' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Ya</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                            <input type="radio" name="public_visibility" value="no" <?= old('public_visibility') === 'no' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Tidak (anonim)</p>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Link profil/website untuk disematkan (opsional)</label>
                    <input type="url" name="website_link" value="<?= old('website_link') ?>" placeholder="https://..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Logo resmi untuk ditampilkan (upload file)</label>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <p class="text-xs text-gray-500 mt-1">Format JPG/PNG, maks 2 MB</p>
            </div>
        </div>

        <!-- D. Tujuan & Harapan Sponsor -->
        <div class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">D. Tujuan & Harapan Sponsor</h3>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Alasan ingin menjadi sponsor <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('reason') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Harapan terhadap kegiatan yang didukung</label>
                <textarea name="expectations" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('expectations') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Ketentuan khusus dari sponsor (jika ada)</label>
                <textarea name="special_terms" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('special_terms') ?></textarea>
            </div>
        </div>

        <!-- E. Dokumen Pendukung -->
        <div class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">E. Dokumen Pendukung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Upload Surat Kerjasama (jika sudah ada)</label>
                    <input type="file" name="partnership_letter" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="text-xs text-gray-500 mt-1">Format JPG/PNG/PDF, maks 4 MB</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5">Upload Proposal / Profil Perusahaan (opsional)</label>
                    <input type="file" name="company_profile" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="text-xs text-gray-500 mt-1">Format JPG/PNG/PDF, maks 4 MB</p>
                </div>
            </div>
        </div>

        <!-- F. Pernyataan & Persetujuan -->
        <div class="space-y-4 pt-4 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">F. Pernyataan & Persetujuan</h3>
            <div class="space-y-3">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="agree_info" required class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Saya menyatakan informasi yang diberikan adalah benar.</span>
                </label>
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="agree_terms" required class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Saya menyetujui bahwa sponsorship ini tunduk pada aturan UrunanKita.id.</span>
                </label>
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="agree_responsibility" required class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Saya memahami bahwa UrunanKita.id adalah platform perantara dan tidak bertanggung jawab atas penyalahgunaan di luar ketentuan.</span>
                </label>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500 flex-1">Dengan mengirimkan formulir ini, Anda menyetujui proses verifikasi oleh tim Urunankita.</p>
            <button type="button" onclick="openTermsModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Kirim Pengajuan Sponsorship
            </button>
        </div>
    </form>
</section>

<!-- FAQ -->
<section class="bg-white border-t">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-8">
            <p class="text-xs font-semibold tracking-[0.3em] text-emerald-600 uppercase">FAQ</p>
            <h3 class="text-2xl font-semibold text-slate-900 mt-2">Pertanyaan yang Sering Diajukan</h3>
        </div>
        <div class="space-y-4">
            <details class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                <summary class="font-medium cursor-pointer text-slate-900">Apa saja jenis sponsorship yang diterima?</summary>
                <div class="mt-3 text-sm text-slate-600">Kami menerima sponsorship berupa donasi dana, barang/produk, layanan/jasa, dan kolaborasi program. Setiap jenis sponsorship memiliki proses verifikasi dan implementasi yang disesuaikan.</div>
            </details>
            <details class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                <summary class="font-medium cursor-pointer text-slate-900">Apakah sponsor bisa memilih program yang didukung?</summary>
                <div class="mt-3 text-sm text-slate-600">Ya, sponsor dapat memilih kategori program yang ingin didukung seperti pendidikan, sosial, kesehatan, bencana alam, ekonomi, atau event komunitas. Tim kami akan menyesuaikan program yang sesuai dengan preferensi sponsor.</div>
            </details>
            <details class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                <summary class="font-medium cursor-pointer text-slate-900">Bagaimana proses verifikasi sponsorship?</summary>
                <div class="mt-3 text-sm text-slate-600">Setelah pengajuan diterima, tim kami akan melakukan verifikasi data perusahaan, review proposal, dan koordinasi detail kerjasama. Proses ini membutuhkan waktu 3-7 hari kerja setelah dokumen lengkap.</div>
            </details>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryLainnya = document.getElementById('categoryLainnya');
        const categoryOtherInput = document.getElementById('categoryOtherInput');
        
        if (categoryLainnya) {
            categoryLainnya.addEventListener('change', function() {
                if (this.checked) {
                    categoryOtherInput.classList.remove('hidden');
                    categoryOtherInput.required = true;
                } else {
                    categoryOtherInput.classList.add('hidden');
                    categoryOtherInput.required = false;
                    categoryOtherInput.value = '';
                }
            });
        }
    });

    function openTermsModal() {
        // Check if all required checkboxes are checked
        const agreeInfo = document.querySelector('input[name="agree_info"]');
        const agreeTerms = document.querySelector('input[name="agree_terms"]');
        const agreeResponsibility = document.querySelector('input[name="agree_responsibility"]');
        
        if (!agreeInfo.checked || !agreeTerms.checked || !agreeResponsibility.checked) {
            alert('Harap centang semua pernyataan persetujuan terlebih dahulu.');
            return;
        }

        const modal = document.getElementById('termsModal');
        const termsContent = document.getElementById('termsContent');
        const termsAgree = document.getElementById('termsAgree');
        const submitBtn = document.getElementById('termsSubmitBtn');
        
        // Reset state
        termsAgree.checked = false;
        termsAgree.disabled = true;
        submitBtn.disabled = true;
        submitBtn.className = 'flex-1 px-4 py-2.5 rounded-xl bg-gray-300 text-gray-500 font-semibold cursor-not-allowed';
        termsContent.scrollTop = 0;
        
        // Check if scrolled to bottom
        function checkScroll() {
            const isScrolledToBottom = termsContent.scrollHeight - termsContent.scrollTop <= termsContent.clientHeight + 10;
            if (isScrolledToBottom) {
                termsAgree.disabled = false;
                termsContent.removeEventListener('scroll', checkScroll);
            }
        }
        
        termsContent.addEventListener('scroll', checkScroll);
        modal.classList.remove('hidden');
    }

    function closeTermsModal() {
        const modal = document.getElementById('termsModal');
        modal.classList.add('hidden');
    }

    document.getElementById('termsAgree').addEventListener('change', function() {
        const submitBtn = document.getElementById('termsSubmitBtn');
        if (this.checked) {
            submitBtn.disabled = false;
            submitBtn.className = 'flex-1 px-4 py-2.5 rounded-xl bg-primary-600 text-white font-semibold hover:bg-primary-700 transition-colors';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'flex-1 px-4 py-2.5 rounded-xl bg-gray-300 text-gray-500 font-semibold cursor-not-allowed';
        }
    });

    function submitForm() {
        const form = document.querySelector('form[action="<?= base_url('page/sponsorship') ?>"]');
        if (form) {
            closeTermsModal();
            form.submit();
        }
    }
</script>

<!-- Terms of Service Modal -->
<div id="termsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeTermsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900" id="modal-title">Syarat & Ketentuan Sponsorship</h3>
                    <button type="button" onclick="closeTermsModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="termsContent" class="max-h-96 overflow-y-auto border border-gray-200 rounded-xl p-4 text-sm text-gray-700 space-y-4 mb-4">
                    <div>
                        <p class="font-semibold text-lg text-gray-900 mb-2">Syarat & Ketentuan Sponsorship – UrunanKita.id</p>
                        <p>Dengan mengajukan sponsorship di platform UrunanKita.id, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh syarat & ketentuan berikut:</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">1. Verifikasi & Validasi</p>
                        <p class="mb-1">Sponsor wajib memberikan data perusahaan yang valid dan dokumen pendukung. UrunanKita.id berhak memverifikasi dan memvalidasi seluruh informasi yang diberikan.</p>
                        <p>UrunanKita.id berhak menolak, menunda, atau membatalkan pengajuan sponsorship jika data tidak valid atau tidak memenuhi ketentuan.</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">2. Komitmen Sponsorship</p>
                        <p class="mb-1">Sponsor wajib memenuhi komitmen sponsorship sesuai dengan kesepakatan yang telah ditandatangani.</p>
                        <p>Kegagalan memenuhi komitmen dapat mengakibatkan pembatalan kerjasama dan tindakan hukum sesuai peraturan yang berlaku.</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">3. Penggunaan Logo & Publikasi</p>
                        <p class="mb-1">Logo dan informasi sponsor akan ditampilkan sesuai dengan preferensi publikasi yang dipilih.</p>
                        <p>Sponsor memberikan izin kepada UrunanKita.id untuk menggunakan logo dan informasi sponsor untuk keperluan publikasi program yang didukung.</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">4. Transparansi & Akuntabilitas</p>
                        <p class="mb-1">UrunanKita.id berkomitmen untuk memberikan laporan penggunaan sponsorship secara transparan kepada sponsor.</p>
                        <p>Sponsor berhak meminta laporan penggunaan sponsorship sesuai dengan kesepakatan yang telah dibuat.</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">5. Batasan Tanggung Jawab</p>
                        <p class="mb-1">UrunanKita.id adalah platform perantara antara sponsor dan penerima manfaat program.</p>
                        <p>UrunanKita.id tidak bertanggung jawab atas hasil akhir program atau perselisihan yang timbul di luar lingkup platform.</p>
                    </div>

                    <div>
                        <p class="font-semibold text-gray-900 mb-1">6. Privasi Data</p>
                        <p>Data sponsor akan digunakan untuk keperluan verifikasi, komunikasi, dan publikasi program. UrunanKita.id menjaga kerahasiaan data sesuai Kebijakan Privasi.</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="font-semibold text-gray-900 mb-2">Pernyataan Persetujuan</p>
                        <p class="mb-2">Dengan mencentang kotak persetujuan, Anda menyatakan bahwa:</p>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Anda telah membaca dan memahami seluruh isi Syarat & Ketentuan Sponsorship ini.</li>
                            <li>Semua informasi yang Anda berikan benar dan dapat dipertanggungjawabkan.</li>
                            <li>Anda menyetujui seluruh aturan yang berlaku dan siap menjalankan komitmen sebagai Sponsor.</li>
                        </ul>
                    </div>
                </div>
                <div class="flex items-start gap-3 mb-4">
                    <input type="checkbox" id="termsAgree" disabled class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <label for="termsAgree" class="text-sm text-gray-700">
                        Saya telah membaca dan menyetujui <span class="font-semibold">Syarat & Ketentuan Sponsorship</span> di atas. Saya memahami bahwa pelanggaran terhadap ketentuan ini dapat mengakibatkan pembatalan kerjasama dan tindakan hukum.
                    </label>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeTermsModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" id="termsSubmitBtn" onclick="submitForm()" disabled class="flex-1 px-4 py-2.5 rounded-xl bg-gray-300 text-gray-500 font-semibold cursor-not-allowed">
                        Setujui & Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #termsContent::-webkit-scrollbar {
        width: 8px;
    }
    #termsContent::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    #termsContent::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    #termsContent::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
<?= $this->endSection() ?>

