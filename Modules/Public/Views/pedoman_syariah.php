<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title>Pedoman Syariah — UrunanKita.id</title>
<meta name="description" content="Pedoman Syariah resmi UrunanKita.id: landasan fiqih, akad, ketentuan Penggalang, transparansi, dan nilai-nilai utama.">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hero -->
<section class="bg-gradient-to-b from-white to-slate-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-medium mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Disusun dengan prinsip amanah & transparansi
        </div>
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900">Pedoman Syariah UrunanKita.id</h2>
        <p class="mt-4 text-slate-600">Dokumen ringkas yang menjelaskan landasan fiqih, akad, batas wajar imbalan Penggalang, dan praktik transparansi pada platform urunan sosial berbasis syariah.</p>
        <div class="mt-6 text-xs text-slate-500">Terakhir diperbarui: 21 Oktober 2025</div>
    </div>
</section>

<!-- Landasan -->
<section id="landasan" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            <h3 class="text-2xl font-semibold mb-4">1) Landasan Hukum & Fiqih</h3>
            <div class="prose prose-slate max-w-none">
                <p>
                    Pedoman ini berlandaskan prinsip-prinsip syariah dalam pengelolaan dana sosial: amanah, keadilan, dan transparansi. Dalil pokok pengelolaan dana zakat, infak, dan sedekah mengacu pada <strong>QS. At-Taubah: 60</strong> (delapan asnaf, termasuk <em>amil</em>), kaidah <em>al-umûr bi maqâshidihâ</em> (setiap urusan sesuai tujuannya), serta ketentuan fatwa dan peraturan perundangan terkait filantropi syariah dan wakaf.
                </p>
                <blockquote>
                    <p>"Sesungguhnya zakat itu hanyalah untuk orang-orang fakir, miskin, <strong>amil zakat</strong>, muallaf yang dibujuk hatinya, (untuk memerdekakan) budak, orang yang berutang, untuk jalan Allah, dan untuk orang yang sedang dalam perjalanan." (QS. At-Taubah: 60)</p>
                </blockquote>
                <p>
                    Intinya, setiap kerja pengelolaan yang bermanfaat dan halal berhak mendapatkan <em>ujrah</em> (imbalan) yang wajar. Angka pasti tidak ditetapkan nash, namun praktik lembaga syariah modern memberi rujukan rentang wajar yang digunakan di kebijakan ini.
                </p>
                <p class="text-xs text-slate-500 mt-4">
                    Pedoman syariah ini mengacu pada standar lembaga filantropi syariah dan ditinjau secara berkala bersama penasihat syariah untuk memastikan kepatuhan dan kemaslahatan.
                </p>
            </div>
        </div>
        <aside class="bg-white border rounded-2xl p-5 shadow-sm">
            <h4 class="font-semibold mb-2">Nilai-Nilai Utama</h4>
            <ul class="space-y-2 text-sm text-slate-700">
                <li>🌿 <strong>Amanah</strong> — dana dikelola sesuai tujuan.</li>
                <li>🔎 <strong>Transparansi</strong> — laporan terbuka & akuntabel.</li>
                <li>⚖️ <strong>Keadilan</strong> — imbalan proporsional.</li>
                <li>🤝 <strong>Ihsan</strong> — niat ikhlas membantu sesama.</li>
            </ul>
        </aside>
    </div>
</section>

<!-- Akad -->
<section id="akad" class="bg-white border-y">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h3 class="text-2xl font-semibold mb-6">2) Akad yang Digunakan</h3>
        <div class="overflow-x-auto rounded-2xl border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Jenis Urunan</th>
                        <th class="px-5 py-3">Akad Syariah</th>
                        <th class="px-5 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr>
                        <td class="px-5 py-4 font-medium">Donasi sosial (infak/sedekah)</td>
                        <td class="px-5 py-4 italic">Wakâlah bil Ujrah</td>
                        <td class="px-5 py-4">Penggalang mewakili platform menghimpun & menyalurkan dana, dengan imbalan (ujrah) wajar untuk biaya kerja lapangan.</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-4 font-medium">Urunan zakat</td>
                        <td class="px-5 py-4 italic">Amil Zakat</td>
                        <td class="px-5 py-4">Mengikuti QS. At-Taubah: 60; bagian amil maksimal <strong>12,5%</strong> — ditujukan untuk pengelola lapangan, bukan platform.</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-4 font-medium">Wakaf produktif</td>
                        <td class="px-5 py-4 italic">Nazhir</td>
                        <td class="px-5 py-4">Bagian operasional maksimal <strong>10% dari hasil pengelolaan</strong> (pokok wakaf tidak boleh berkurang).</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-4 font-medium">Urunan usaha sosial</td>
                        <td class="px-5 py-4 italic">Mudharabah / Musyarakah</td>
                        <td class="px-5 py-4">Nisbah berasal dari <em>keuntungan</em>, bukan dari modal donasi.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Ketentuan Penggalang -->
<section id="penggalang" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h3 class="text-2xl font-semibold mb-6">3) Ketentuan untuk Penggalang (Pengelola Urunan)</h3>
    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h4 class="font-semibold mb-3">Batas Wajar Ujrah</h4>
            <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                <li>Untuk donasi sosial (infak/sedekah): ujrah <strong>maksimal 10%</strong> untuk menutup biaya kerja lapangan (transport, koordinasi, dokumentasi, pembelian bahan, dll.).</li>
                <li>Untuk zakat: mengikuti ketentuan amil <strong>maksimal 12,5%</strong> — porsi amil ditujukan untuk pengelola lapangan, bukan platform.</li>
                <li>Untuk wakaf produktif: operasional <strong>maksimal 10% dari hasil pengelolaan</strong> (pokok wakaf tidak boleh berkurang).</li>
            </ul>
            <p class="text-xs text-slate-500 mt-3">Ujrah bersifat opsional — Penggalang dapat mengambil lebih kecil atau tidak mengambil sama sekali sesuai kebijakan program.</p>
        </div>

        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h4 class="font-semibold mb-3">Transparansi yang Wajib</h4>
            <p class="text-sm mb-3">Setiap halaman program wajib mencantumkan pernyataan transparansi berikut:</p>
            <div class="bg-slate-50 border rounded-xl p-4 text-sm">
                <p><em>"Sebagian kecil (maks. <span class="font-semibold">10%</span>) dari total donasi digunakan untuk biaya operasional penyaluran oleh pengelola program (Penggalang). Sisanya disalurkan 100% kepada penerima manfaat sesuai tujuan urunan."</em></p>
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h4 class="font-semibold mb-3">Contoh Perhitungan</h4>
            <div class="text-sm space-y-2">
                <p><strong>Program:</strong> "Bantuan Pembangunan Musholla"</p>
                <p><strong>Total donasi:</strong> Rp100.000.000</p>
                <p><strong>Ujrah Penggalang (10%):</strong> Rp10.000.000</p>
                <p><strong>Disalurkan ke penerima manfaat:</strong> Rp90.000.000</p>
                <p class="text-slate-500 text-xs">
                    Contoh ini bersifat ilustrasi. Rincian ujrah selalu dicantumkan di halaman program dan wajib dilaporkan penggunaannya.
                </p>
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h4 class="font-semibold mb-3">Kewajiban Pelaporan</h4>
            <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                <li>Unggah ringkasan penggunaan dana dan dokumentasi kegiatan.</li>
                <li>Rinci komponen biaya operasional (transport, administrasi, komunikasi, dokumentasi, dll.).</li>
                <li>Seluruh laporan dapat diaudit oleh tim UrunanKita.id.</li>
            </ul>
        </div>
    </div>
</section>

<!-- Ketentuan Platform -->
<section id="platform" class="bg-white border-y">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h3 class="text-2xl font-semibold mb-6">4) Ketentuan untuk Platform UrunanKita.id</h3>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <h4 class="font-semibold mb-3">Prinsip Platform</h4>
                <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                    <li>Seluruh donasi disalurkan kepada penerima manfaat sesuai tujuan program.</li>
                    <li>Platform <strong>tidak mengambil potongan apa pun</strong> dari donasi.</li>
                    <li>Penggalang Urunan <strong>gratis sepenuhnya</strong> untuk membuat dan mengelola urunan.</li>
                    <li>Dukungan operasional platform bersifat <strong>infaq sukarela</strong> dari donatur/penggalang dan dapat diabaikan.</li>
                    <li>Platform memastikan proses verifikasi kampanye, keamanan sistem, dan audit trail bagi transparansi.</li>
                </ul>
            </div>
            <div class="bg-white border rounded-2xl p-6 shadow-sm">
                <h4 class="font-semibold mb-3">Contoh Teks Dukungan Platform</h4>
                <div class="bg-slate-50 border rounded-xl p-4 text-sm italic">
                    "UrunanKita.id tidak mengambil potongan dari donasi dan gratis untuk penggalang.
                    Jika berkenan, Anda dapat memberikan infaq sukarela untuk mendukung operasional platform agar semakin banyak program sosial bisa terbantu."
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sponsor & Kemitraan -->
<section id="sponsor" class="bg-slate-50 border-y">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h3 class="text-2xl font-semibold mb-6">5) Ketentuan Sponsor & Kemitraan</h3>
        <p class="text-sm text-slate-700 mb-3">
            UrunanKita.id membuka peluang kerja sama dengan lembaga dan perusahaan untuk mendukung program sosial.
            Seluruh dukungan sponsor tunduk pada prinsip halal, transparansi, serta tidak bertentangan dengan syariah.
            Penjelasan lengkap mengenai tata kelola sponsor, jenis dukungan, dan prosedur persetujuan tersedia di
            <a href="<?= base_url('/page/ketentuan-sponsor') ?>" class="text-emerald-600 hover:underline font-medium">Halaman Ketentuan Sponsor & Kemitraan</a>.
        </p>
        <ul class="list-disc pl-6 text-sm space-y-2 text-slate-700">
            <li>Produk/layanan sponsor harus halal dan bermanfaat.</li>
            <li>Sponsor tidak mempengaruhi keputusan penyaluran dana.</li>
            <li>Kerja sama Penggalang–sponsor memerlukan persetujuan platform (model hybrid).</li>
            <li>Seluruh sponsorship diumumkan secara terbuka di laman program.</li>
        </ul>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h3 class="text-2xl font-semibold mb-6">6) Tanya Jawab (FAQ)</h3>
    <div class="space-y-4">
        <details class="bg-white border rounded-2xl p-5 shadow-sm">
            <summary class="font-medium cursor-pointer">Apakah ujrah Penggalang boleh lebih dari 10%?</summary>
            <div class="mt-3 text-sm text-slate-700">Kebijakan platform menetapkan batas wajar <strong>maks. 10%</strong> untuk donasi sosial agar dana mayoritas tersalurkan kepada penerima manfaat. Pengecualian hanya atas persetujuan khusus dan alasan operasional yang sangat kuat.</div>
        </details>
        <details class="bg-white border rounded-2xl p-5 shadow-sm">
            <summary class="font-medium cursor-pointer">Apakah ujrah ini wajib diambil?</summary>
            <div class="mt-3 text-sm text-slate-700">Tidak. Ujrah adalah imbalan <em>yang boleh</em> diambil sebagai ganti biaya/tenaga. Penggalang diperkenankan mengambil lebih kecil atau bahkan <em>nol</em> bila semua biaya ditanggung pihak lain.</div>
        </details>
        <details class="bg-white border rounded-2xl p-5 shadow-sm">
            <summary class="font-medium cursor-pointer">Bagaimana untuk program zakat?</summary>
            <div class="mt-3 text-sm text-slate-700">Program zakat mengikuti ketentuan delapan asnaf. Bagian amil <strong>maksimal 12,5%</strong>. Penandaan program zakat harus jelas dan laporan penyaluran khusus.</div>
        </details>
        <details class="bg-white border rounded-2xl p-5 shadow-sm">
            <summary class="font-medium cursor-pointer">Bagaimana untuk wakaf produktif?</summary>
            <div class="mt-3 text-sm text-slate-700">Pokok wakaf tidak boleh berkurang. Biaya operasional diambil dari <strong>hasil pengelolaan</strong> dengan batas wajar <strong>maks. 10%</strong>.</div>
        </details>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .prose {
        color: #334155;
        line-height: 1.8;
    }

    .prose p {
        margin-bottom: 1rem;
        color: #475569;
    }

    .prose strong {
        font-weight: 600;
        color: #0f172a;
    }

    .prose em {
        font-style: italic;
        color: #475569;
    }

    .prose blockquote {
        border-left: 4px solid #10b981;
        padding-left: 1rem;
        font-style: italic;
        color: #475569;
        margin: 1rem 0;
    }

    .prose ul,
    .prose ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .prose ul {
        list-style-type: disc;
    }

    .prose ol {
        list-style-type: decimal;
    }

    .prose li {
        margin-bottom: 0.5rem;
        color: #475569;
    }

    details summary {
        list-style: none;
    }

    details summary::-webkit-details-marker {
        display: none;
    }
</style>
<?= $this->endSection() ?>