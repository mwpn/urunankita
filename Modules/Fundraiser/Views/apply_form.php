<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title>Pengajuan Penggalang Baru — UrunanKita.id</title>
<meta name="description" content="Ajukan diri atau lembaga Anda menjadi penggalang resmi UrunanKita. Lengkapi data pribadi, legalitas yayasan, kanal sosial, dan alasan penggalangan.">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="bg-gradient-to-b from-white via-slate-50 to-slate-100 border-b">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-medium mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Form UrunanKita.id
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Pengajuan Penggalang Baru</h1>
        <p class="mt-4 text-slate-600 text-base md:text-lg max-w-3xl mx-auto">
            Lengkapi data berikut untuk menjadi bagian dari Penggalang resmi UrunanKita.id. Tim kami akan memverifikasi identitas, legalitas, dan aktivitas sosial Anda sebelum membuatkan akun penggalang.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-xs text-slate-500">
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Verifikasi identitas & dokumen</span>
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Review aktivitas sosial</span>
            <span class="bg-white border border-slate-200 rounded-full px-3 py-1">Pembuatan akun & onboarding</span>
        </div>
    </div>
</section>

<!-- Process timeline -->
<section class="border-b bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <p class="text-xs font-semibold tracking-[0.3em] text-emerald-600 uppercase">Tahapan</p>
                <h3 class="text-2xl font-semibold text-slate-900 mt-2">Alur Verifikasi Penggalang</h3>
                <p class="text-sm text-slate-600 mt-1">Estimasi total proses 2-4 hari kerja setelah dokumen lengkap.</p>
            </div>
            <div class="text-sm text-slate-500 bg-white border border-slate-200 rounded-xl px-4 py-2 shadow-sm">
                <span class="font-semibold text-slate-900">Tips:</span> cantumkan kontak aktif agar mudah dihubungi.
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 1</div>
                <h4 class="mt-2 font-semibold text-slate-900">Kirim Form</h4>
                <p class="text-sm text-slate-600 mt-1">Lengkapi data diri, dokumen, dan alasan penggalangan.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 2</div>
                <h4 class="mt-2 font-semibold text-slate-900">Review Dokumen</h4>
                <p class="text-sm text-slate-600 mt-1">Tim compliance memeriksa kelengkapan dan keaslian berkas.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 3</div>
                <h4 class="mt-2 font-semibold text-slate-900">Wawancara Singkat</h4>
                <p class="text-sm text-slate-600 mt-1">Jika dibutuhkan, kami hubungi melalui telepon/Zoom.</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Langkah 4</div>
                <h4 class="mt-2 font-semibold text-slate-900">Akun Dibuat</h4>
                <p class="text-sm text-slate-600 mt-1">Penggalang menerima akses dashboard & panduan onboarding.</p>
            </div>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-gray-500 font-medium">Formulir Verifikasi Penggalang</p>
        <h2 class="mt-3 text-2xl font-semibold text-gray-900">Lengkapi Data Anda</h2>
        <p class="mt-2 text-base text-gray-600 max-w-2xl mx-auto">
            Harap siapkan dokumen KTP, data sosial media aktif, serta penjelasan singkat mengenai pengalaman dan alasan Anda ingin menggalang dana bersama UrunanKita.id.
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

    <form action="<?= base_url('page/penggalang-baru') ?>" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-xl shadow-sm px-6 py-7 space-y-6">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="<?= old('full_name') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Nomor HP Aktif <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="<?= old('phone') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Unggah KTP <span class="text-red-500">*</span></label>
                <input type="file" name="ktp" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <p class="text-xs text-gray-500 mt-1">Format JPG/PNG/PDF, maks 4 MB</p>
            </div>
        </div>

        <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-800">Bertindak atas nama <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                    <input type="radio" name="entity_type" value="personal" <?= old('entity_type', 'personal') === 'personal' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Pribadi</p>
                        <p class="text-xs text-gray-500">Penggalangan dana atas nama pribadi</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-400">
                    <input type="radio" name="entity_type" value="foundation" <?= old('entity_type') === 'foundation' ? 'checked' : '' ?> class="text-primary-600 focus:ring-primary-500" id="entityFoundationOption">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Yayasan / Lembaga</p>
                        <p class="text-xs text-gray-500">Memerlukan dokumen pendukung</p>
                    </div>
                </label>
            </div>
            <div id="foundationDocumentField" class="<?= old('entity_type') === 'foundation' ? '' : 'hidden' ?>">
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Unggah Dokumen Yayasan <span class="text-red-500">*</span></label>
                <div id="foundationDocumentsContainer" class="space-y-3">
                    <div class="foundation-doc-item flex items-center gap-3">
                        <div class="flex-1">
                            <input type="file" name="foundation_document[]" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                        <button type="button" onclick="addFoundationDocument()" class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-600 text-white hover:bg-primary-700 flex items-center justify-center transition-colors" title="Tambah file">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Akte pendirian, surat keputusan, surat izin, surat kuasa, atau dokumen pendukung lainnya. Klik + untuk menambah file.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Instagram</label>
                <input type="text" name="instagram" value="<?= old('instagram') ?>" placeholder="@akun" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Channel Youtube</label>
                <input type="url" name="youtube_channel" value="<?= old('youtube_channel') ?>" placeholder="https://youtube.com/..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Twitter</label>
                <input type="text" name="twitter" value="<?= old('twitter') ?>" placeholder="@akun" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1.5">Facebook</label>
                <input type="text" name="facebook" value="<?= old('facebook') ?>" placeholder="URL / Nama halaman" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Alasan Ingin Menjadi Penggalang <span class="text-red-500">*</span></label>
            <textarea name="reason" rows="5" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"><?= old('reason') ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Ceritakan rencana penggalangan, pengalaman sosial, atau motivasi Anda.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
            <p class="text-xs text-gray-500 flex-1">Dengan mengirimkan formulir ini, Anda menyetujui proses verifikasi oleh tim Urunankita.</p>
            <button type="button" onclick="openTermsModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 shadow-lg hover:shadow-xl transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Kirim Pengajuan
            </button>
        </div>
    </form>

    <!-- Terms of Service Modal -->
    <div id="termsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeTermsModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900" id="modal-title">Syarat & Ketentuan</h3>
                        <button type="button" onclick="closeTermsModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="termsContent" class="max-h-96 overflow-y-auto border border-gray-200 rounded-xl p-4 text-sm text-gray-700 space-y-4 mb-4">
                        <div>
                            <p class="font-semibold text-lg text-gray-900 mb-2">Syarat & Ketentuan Penggalang Dana – UrunanKita.id</p>
                            <p>Dengan mengajukan diri sebagai Penggalang Dana di platform UrunanKita.id, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh syarat & ketentuan berikut:</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">1. Verifikasi Identitas</p>
                            <p class="mb-1">Anda wajib memberikan identitas yang valid (KTP), nomor HP aktif, email, dan dokumen pendukung lainnya sesuai jenis penggalangan (pribadi, komunitas, sekolah, yayasan, atau organisasi).</p>
                            <p class="mb-1">UrunanKita.id berhak meminta dokumen tambahan seperti surat keterangan, bukti kondisi penerima manfaat, dan foto/video pendukung.</p>
                            <p>UrunanKita.id berhak menolak, menunda, atau membatalkan pengajuan jika data tidak valid atau tidak memenuhi ketentuan.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">2. Kejujuran & Akurasi Informasi</p>
                            <p class="mb-1">Semua informasi dalam kampanye harus benar, akurat, dan tidak menyesatkan.</p>
                            <p class="mb-1">Dilarang menggunakan konten palsu, manipulatif, menyinggung, atau melanggar hak cipta.</p>
                            <p>Informasi yang terbukti palsu dapat menyebabkan pembatalan kampanye, penangguhan akun, atau tindakan hukum.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">3. Transparansi Penggunaan Dana</p>
                            <p class="mb-1">Dana harus digunakan sesuai tujuan kampanye dan tidak boleh dialihkan untuk kepentingan lain.</p>
                            <p class="mb-1">Anda wajib menyediakan dokumentasi pertanggungjawaban (nota, invoice, bukti foto/video kegiatan) bila diminta.</p>
                            <p>Seluruh penggunaan dana sepenuhnya menjadi tanggung jawab Penggalang.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">4. Penggunaan Platform</p>
                            <p class="mb-1">Platform UrunanKita.id hanya boleh digunakan untuk penggalangan dana yang:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4 mb-2">
                                <li>sah secara hukum</li>
                                <li>bermanfaat bagi penerima manfaat</li>
                                <li>tidak mengandung unsur penipuan, kebencian, atau kegiatan ilegal</li>
                            </ul>
                            <p class="mb-1">Dilarang keras membuat kampanye untuk:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>pendanaan terorisme, kekerasan, atau radikalisme</li>
                                <li>perjudian, narkoba, pornografi, atau aktivitas ilegal lainnya</li>
                                <li>kampanye politik tertentu</li>
                                <li>manipulasi publik atau eksploitasi korban</li>
                            </ul>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">5. Komitmen & Tanggung Jawab Penggalang</p>
                            <p class="mb-1">Anda sebagai Penggalang menyatakan bahwa:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Anda bertanggung jawab penuh atas isi kampanye dan komunikasi dengan Donatur.</li>
                                <li>Anda bersedia menjalani audit, verifikasi tambahan, dan monitoring dari UrunanKita.id.</li>
                                <li>Anda wajib memberikan pembaruan (update) kondisi kampanye jika diminta.</li>
                            </ul>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">6. Pencairan Dana</p>
                            <p class="mb-1">Dana hanya dapat dicairkan setelah proses verifikasi dan validasi data rekening.</p>
                            <p class="mb-1">Proses pencairan membutuhkan waktu sesuai kebijakan operasional UrunanKita.id.</p>
                            <p class="mb-1">Kesalahan input data rekening menjadi tanggung jawab Penggalang.</p>
                            <p>UrunanKita.id berhak menahan pencairan jika ditemukan indikasi penyalahgunaan atau laporan dari Donatur.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">7. Privasi Data</p>
                            <p class="mb-1">Data pribadi Anda digunakan untuk verifikasi, validasi kampanye, dan komunikasi.</p>
                            <p class="mb-1">UrunanKita.id menjaga kerahasiaan data sesuai Kebijakan Privasi.</p>
                            <p>Data hanya dapat dibagikan kepada pihak berwenang apabila diwajibkan oleh hukum.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">8. Pembatalan, Penangguhan & Sanksi</p>
                            <p class="mb-1">UrunanKita.id berhak melakukan:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4 mb-2">
                                <li>penolakan kampanye</li>
                                <li>penghapusan kampanye</li>
                                <li>penangguhan/penonaktifan akun</li>
                                <li>pembekuan dana</li>
                                <li>pelaporan kepada aparat hukum</li>
                            </ul>
                            <p>Jika ditemukan pelanggaran, indikasi penipuan, pemalsuan dokumen, atau penyalahgunaan dana.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">9. Batasan Tanggung Jawab UrunanKita.id</p>
                            <p class="mb-1">Anda memahami dan menyetujui bahwa:</p>
                            <p class="mb-1">UrunanKita.id hanyalah platform perantara antara Penggalang dan Donatur.</p>
                            <p class="mb-1">UrunanKita.id tidak bertanggung jawab atas:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4 mb-2">
                                <li>kebenaran data kampanye yang diberikan Penggalang</li>
                                <li>penyalahgunaan dana oleh Penggalang</li>
                                <li>keterlambatan atau kegagalan penyaluran bantuan oleh Penggalang</li>
                                <li>perselisihan, kerugian, atau sengketa antara Penggalang dan Donatur</li>
                                <li>kerugian finansial atau non-finansial yang timbul akibat tindakan Penggalang</li>
                            </ul>
                            <p class="mb-1">UrunanKita.id tidak menjamin:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4 mb-2">
                                <li>target dana akan tercapai</li>
                                <li>keberhasilan atau hasil akhir kampanye</li>
                                <li>keaslian seluruh konten yang dibuat Penggalang</li>
                            </ul>
                            <p class="mb-1">Donatur memberikan dana atas dasar kepercayaan, dan seluruh risiko berada pada Penggalang.</p>
                            <p>Dalam keadaan apapun, UrunanKita.id tidak bertanggung jawab atas kerugian tidak langsung, kehilangan keuntungan, atau konsekuensi lain akibat penggunaan platform.</p>
                        </div>

                        <div>
                            <p class="font-semibold text-gray-900 mb-1">10. Perubahan Syarat & Ketentuan</p>
                            <p>UrunanKita.id berhak mengubah Syarat & Ketentuan ini kapan saja. Perubahan akan berlaku setelah dipublikasikan melalui platform.</p>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="font-semibold text-gray-900 mb-2">Pernyataan Persetujuan</p>
                            <p class="mb-2">Dengan mencentang kotak persetujuan, Anda menyatakan bahwa:</p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Anda telah membaca dan memahami seluruh isi Syarat & Ketentuan ini.</li>
                                <li>Semua informasi yang Anda berikan benar dan dapat dipertanggungjawabkan.</li>
                                <li>Anda menyetujui seluruh aturan yang berlaku dan siap menjalankan tanggung jawab sebagai Penggalang Dana.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 mb-4">
                        <input type="checkbox" id="termsAgree" disabled class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <label for="termsAgree" class="text-sm text-gray-700">
                            Saya telah membaca dan menyetujui <span class="font-semibold">Syarat & Ketentuan</span> di atas. Saya memahami bahwa pelanggaran terhadap ketentuan ini dapat mengakibatkan pembatalan akun dan tindakan hukum.
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
    </div>
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
                <summary class="font-medium cursor-pointer text-slate-900">Berapa lama proses verifikasi?</summary>
                <div class="mt-3 text-sm text-slate-600">Rata-rata 2–4 hari kerja sejak dokumen lengkap. Jika diperlukan klarifikasi tambahan, tim kami akan menghubungi Anda melalui WhatsApp atau email.</div>
            </details>
            <details class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                <summary class="font-medium cursor-pointer text-slate-900">Apakah harus berbadan hukum?</summary>
                <div class="mt-3 text-sm text-slate-600">Tidak. Penggalang pribadi diperbolehkan selama bersedia diverifikasi identitas dan aktivitas sosialnya. Yayasan/lembaga wajib melampirkan dokumen legal.</div>
            </details>
            <details class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                <summary class="font-medium cursor-pointer text-slate-900">Apakah ada biaya pendaftaran?</summary>
                <div class="mt-3 text-sm text-slate-600">Tidak ada biaya. UrunanKita.id tidak menarik biaya pembuatan akun penggalang. Kami hanya memastikan seluruh program berjalan amanah dan transparan.</div>
            </details>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const foundationField = document.getElementById('foundationDocumentField');
        const radios = document.querySelectorAll('input[name="entity_type"]');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'foundation') {
                    foundationField.classList.remove('hidden');
                } else {
                    foundationField.classList.add('hidden');
                }
            });
        });
    });

    function addFoundationDocument() {
        const container = document.getElementById('foundationDocumentsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'foundation-doc-item flex items-center gap-3';
        newItem.innerHTML = `
            <div class="flex-1">
                <input type="file" name="foundation_document[]" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
            <button type="button" onclick="removeFoundationDocument(this)" class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-500 text-white hover:bg-red-600 flex items-center justify-center transition-colors" title="Hapus file">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        container.appendChild(newItem);
    }

    function removeFoundationDocument(button) {
        const container = document.getElementById('foundationDocumentsContainer');
        const items = container.querySelectorAll('.foundation-doc-item');
        if (items.length > 1) {
            button.closest('.foundation-doc-item').remove();
        } else {
            // Jika hanya 1 item, reset input-nya saja
            const input = button.closest('.foundation-doc-item').querySelector('input[type="file"]');
            if (input) {
                input.value = '';
            }
        }
    }

    function openTermsModal() {
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
        const form = document.querySelector('form[action="<?= base_url('page/penggalang-baru') ?>"]');
        if (form) {
            closeTermsModal();
            form.submit();
        }
    }
</script>
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