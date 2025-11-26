<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<?php
// Prepare Open Graph meta tags for social sharing
$campaignTitle = esc($campaign['title'] ?? 'Urunan');
$campaignDescription = esc(strip_tags($campaign['description'] ?? ''));
if (mb_strlen($campaignDescription) > 160) {
    $campaignDescription = mb_substr($campaignDescription, 0, 157) . '...';
}

// Get campaign image
$campaignImage = '';
if (!empty($campaign['featured_image'])) {
    $imgPath = $campaign['featured_image'];
    if (preg_match('~^https?://~', $imgPath)) {
        $campaignImage = $imgPath;
    } else {
        $campaignImage = base_url(ltrim($imgPath, '/'));
    }
} elseif (!empty($campaign['images']) && is_array($campaign['images']) && !empty($campaign['images'][0])) {
    $firstImg = is_array($campaign['images'][0]) ? ($campaign['images'][0]['path'] ?? $campaign['images'][0]['url'] ?? '') : (string) $campaign['images'][0];
    if (!empty($firstImg)) {
        if (preg_match('~^https?://~', $firstImg)) {
            $campaignImage = $firstImg;
        } else {
            $campaignImage = base_url(ltrim($firstImg, '/'));
        }
    }
}

// Fallback to site logo if no campaign image
if (empty($campaignImage) && !empty($settings['site_logo'])) {
    $logoPath = $settings['site_logo'];
    if (preg_match('~^https?://~', $logoPath)) {
        $campaignImage = $logoPath;
    } else {
        $campaignImage = base_url(ltrim($logoPath, '/'));
    }
}

$campaignUrl = current_url();
$siteName = esc($settings['site_name'] ?? 'UrunanKita');
?>
<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= $campaignUrl ?>">
<meta property="og:title" content="<?= $campaignTitle ?> - <?= $siteName ?>">
<meta property="og:description" content="<?= $campaignDescription ?>">
<?php if (!empty($campaignImage)): ?>
<meta property="og:image" content="<?= $campaignImage ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?= $campaignUrl ?>">
<meta name="twitter:title" content="<?= $campaignTitle ?> - <?= $siteName ?>">
<meta name="twitter:description" content="<?= $campaignDescription ?>">
<?php if (!empty($campaignImage)): ?>
<meta name="twitter:image" content="<?= $campaignImage ?>">
<?php endif; ?>

<!-- Additional Meta -->
<meta name="description" content="<?= $campaignDescription ?>">
<link rel="canonical" href="<?= $campaignUrl ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <!-- Back Link -->
    <a href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Beranda
    </a>

    <?php
    // Ensure $updates is always an array (prevent null errors)
    $updates = is_array($updates ?? null) ? $updates : [];

    // Helper function untuk resolve image URL
    $resolveImageUrl = static function ($path) {
        if (empty($path)) {
            return null;
        }

        // Jika sudah URL lengkap, return langsung
        if (preg_match('~^https?://~', $path)) {
            return $path;
        }

        // Path yang tersimpan sudah dalam format: /uploads/tenant_X/files/filename.jpg
        // Atau: uploads/tenant_X/files/filename.jpg
        // Normalize: hapus leading slash, pastikan dimulai dengan 'uploads/'
        $path = ltrim($path, '/');

        // Jika path sudah dimulai dengan 'uploads/', gunakan langsung
        // Jika tidak, tambahkan 'uploads/'
        if (strpos($path, 'uploads/') !== 0) {
            $path = 'uploads/' . $path;
        }

        // Return dengan base_url (base_url akan menambahkan leading slash jika perlu)
        // base_url('uploads/...') akan menghasilkan: http://domain.com/uploads/...
        return base_url($path);
    };

    // Kumpulkan semua gambar untuk galeri
    $allImages = [];

    // Featured image dari campaign
    if (!empty($campaign['featured_image'])) {
        $normalized = $resolveImageUrl($campaign['featured_image']);
        if ($normalized) {
            $allImages[] = $normalized;
        }
    }

    // Gallery images dari campaign
    if (!empty($campaign['images']) && is_array($campaign['images'])) {
        foreach ($campaign['images'] as $galleryImg) {
            $imgPath = is_array($galleryImg) ? ($galleryImg['path'] ?? $galleryImg['url'] ?? '') : (string) $galleryImg;
            if (!empty($imgPath)) {
                $normalized = $resolveImageUrl($imgPath);
                if ($normalized && !in_array($normalized, $allImages, true)) {
                    $allImages[] = $normalized;
                }
            }
        }
    }

    // Images dari campaign updates (laporan penggunaan dana)
    // Sama seperti di admin_view.php - ambil dari $updates
    if (!empty($updates)) {
        foreach ($updates as $update) {
            // Debug: Check if update has images
            if (empty($update['images'])) {
                continue;
            }

            // Parse images - should already be array from CampaignUpdateModel
            $updateImages = [];
            if (is_string($update['images'])) {
                $decoded = json_decode($update['images'], true);
                $updateImages = is_array($decoded) ? $decoded : [];
            } elseif (is_array($update['images'])) {
                $updateImages = $update['images'];
            }

            // Add each image path (images stored as array of string paths)
            foreach ($updateImages as $img) {
                if (!empty($img)) {
                    // Normalize path like in admin view
                    $imgPath = (string) $img;
                    $normalized = $resolveImageUrl($imgPath);
                    if ($normalized && !in_array($normalized, $allImages, true)) {
                        $allImages[] = $normalized;
                    }
                }
            }
        }
    }

    // Kumpulkan video YouTube
    $youtubeVideos = [];
    if (!empty($updates)) {
        foreach ($updates as $updateVideo) {
            if (empty($updateVideo['youtube_url'])) {
                continue;
            }
            $youtubeUrl = trim((string) $updateVideo['youtube_url']);

            // Skip if empty after trim
            if (empty($youtubeUrl)) {
                continue;
            }

            // Try multiple YouTube URL patterns
            $videoId = null;

            // Pattern 1: youtube.com/watch?v=VIDEO_ID (most common)
            // Match: v= followed by 11 characters (video ID) before & or end of string
            if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})(?:[&?#]|$)/', $youtubeUrl, $matches)) {
                $videoId = $matches[1];
            }
            // Pattern 2: youtube.com/embed/VIDEO_ID
            elseif (preg_match('/\/embed\/([a-zA-Z0-9_-]{11})(?:[?&#]|$)/', $youtubeUrl, $matches)) {
                $videoId = $matches[1];
            }
            // Pattern 3: youtu.be/VIDEO_ID
            elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})(?:[?&#]|$)/', $youtubeUrl, $matches)) {
                $videoId = $matches[1];
            }
            // Pattern 4: youtube.com/v/VIDEO_ID
            elseif (preg_match('/\/v\/([a-zA-Z0-9_-]{11})(?:[?&#]|$)/', $youtubeUrl, $matches)) {
                $videoId = $matches[1];
            }
            // Pattern 5: Generic - find 11 character video ID (fallback)
            elseif (preg_match('/([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches)) {
                $potentialId = $matches[1];
                // Make sure it's not part of domain name
                if (
                    strlen($potentialId) === 11 &&
                    !preg_match('/^(www|youtube|youtu|be|com|http|https)/i', $potentialId)
                ) {
                    $videoId = $potentialId;
                }
            }

            if ($videoId) {
                $youtubeVideos[] = [
                    'video_id' => $videoId,
                    'title' => $updateVideo['title'] ?? 'Laporan Penggunaan Dana',
                    'content' => $updateVideo['content'] ?? '',
                    'update_id' => $updateVideo['id'] ?? null,
                ];
            }
        }
    }

    // Hitung data real dari campaign dan updates (dipindah ke sini agar bisa diakses sidebar)
    $totalTerkumpul = (float) ($campaign['current_amount'] ?? 0);
    $totalDonationsCount = (int) ($donation_stats['total_donations'] ?? 0);

    // Hitung total urunan terpakai dari updates
    $totalTerpakai = 0;
    $updatesCount = 0;
    if (!empty($updates) && is_array($updates)) {
        foreach ($updates as $update) {
            if (!empty($update['amount_used']) && $update['amount_used'] !== null && $update['amount_used'] !== '') {
                $totalTerpakai += (float) $update['amount_used'];
            }
            $updatesCount++;
        }
    }

    // Sisa urunan
    $sisaUrunan = $totalTerkumpul - $totalTerpakai;
    $balancePercentage = $totalTerkumpul > 0 ? ($totalTerpakai / $totalTerkumpul) * 100 : 0;

    $reportLink = $report_path ?? (!empty($campaign['slug']) ? '/campaign/' . $campaign['slug'] . '/report' : null);
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 space-y-6">
            <article class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <?php
                $img = $campaign['featured_image'] ?? '';
                if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                    $img = '/uploads/' . ltrim($img, '/');
                }
                ?>
                <?php if (!empty($img)): ?>
                    <img src="<?= esc(base_url(ltrim($img, '/'))) ?>" alt="<?= esc($campaign['title']) ?>" class="w-full h-80 object-cover">
                <?php endif; ?>

                <div class="p-8">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 mb-4">
                        <?= esc($campaign['category'] ?? 'Urunan Aktif') ?>
                    </span>
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 flex-1"><?= esc($campaign['title']) ?></h1>
                        <div class="flex items-center gap-2">
                            <button onclick="shareCampaign()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors duration-200 text-sm font-semibold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                Bagikan
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-6 pb-6 border-b border-gray-200">
                        <?php if (isset($tenant) && $tenant): ?>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <?= esc($tenant['name'] ?? 'Penggalang Urunan') ?>
                            </span>
                        <?php endif; ?>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Dibuat <?= date('d F Y', strtotime($campaign['created_at'])) ?>
                        </span>
                        <?php if (!empty($campaign['target_amount'])): ?>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Target Rp <?= number_format($campaign['target_amount'], 0, ',', '.') ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="prose prose-gray max-w-none">
                        <?= $campaign['description'] ?>
                    </div>
                </div>
            </article>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Urunan</h3>
                <?php if (isset($campaign['progress_percentage'])): ?>
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                            <span>Terkumpul</span>
                            <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
                        </div>
                        <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center p-3 bg-emerald-50 rounded-lg">
                        <p class="text-xl font-bold text-primary-600">Rp <?= number_format($campaign['current_amount'], 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-600">Terkumpul</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 rounded-lg">
                        <p class="text-base font-semibold text-gray-900">Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-600">Target</p>
                    </div>
                </div>
                <button type="button" onclick="openDonateModal()" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ikut Urunan
                </button>
            </div>

            <?php if (!empty($allImages)): ?>
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Galeri Foto</h2>
                        <span class="text-sm text-gray-500"><?= count($allImages) ?> media</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($allImages as $index => $imageUrl): ?>
                            <button type="button" onclick="openGalleryModal(<?= $index ?>)"
                                class="group block overflow-hidden rounded-xl border border-gray-100 bg-gray-50 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                <img src="<?= esc($imageUrl) ?>" alt="Dokumentasi urunan"
                                    class="h-40 w-full object-cover transition duration-300 group-hover:scale-105"
                                    onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center'); this.parentElement.innerHTML='<span class=\'text-sm text-gray-400\'>Gambar tidak tersedia</span>';">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($youtubeVideos)): ?>
                <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Video Laporan</h2>
                        <span class="text-sm text-gray-500"><?= count($youtubeVideos) ?> video</span>
                    </div>
                    <div class="space-y-6">
                        <?php foreach ($youtubeVideos as $video): ?>
                            <div class="flex flex-col md:flex-row gap-4 p-4 border border-gray-200 rounded-xl hover:border-gray-300 transition-colors">
                                <!-- Video di kiri -->
                                <div class="w-full md:w-1/2 lg:w-2/5 flex-shrink-0">
                                    <div class="aspect-video rounded-xl overflow-hidden bg-black">
                                        <iframe
                                            src="https://www.youtube.com/embed/<?= esc($video['video_id']) ?>"
                                            title="<?= esc($video['title'] ?? 'Video laporan urunan') ?>"
                                            class="h-full w-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                    </div>
                                </div>

                                <!-- Judul dan konten di kanan -->
                                <div class="flex-1 flex flex-col justify-start">
                                    <?php if (!empty($video['title'])): ?>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <?= esc($video['title']) ?>
                                        </h3>
                                    <?php endif; ?>

                                    <?php if (!empty($video['content'])): ?>
                                        <div class="text-sm text-gray-600 leading-relaxed">
                                            <?= nl2br(esc($video['content'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500 italic">Tidak ada deskripsi</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Section Komentar & Diskusi -->
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Diskusi & Komentar</h2>
                    </div>
                    <span id="commentsCount" class="text-sm text-gray-500">
                        0 komentar
                    </span>
                </div>

                <!-- Form Komentar -->
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <form id="commentForm" class="space-y-4 p-0">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                        <input type="hidden" name="campaign_id" value="<?= (int) ($campaign['id'] ?? 0) ?>">
                        <input type="hidden" name="parent_id" id="reply_to_id" value="">

                        <div id="replyIndicator" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-blue-700">
                                    <span id="replyToName"></span>
                                </span>
                                <button type="button" id="cancelReply" class="text-sm text-blue-600 hover:text-blue-800">Batal</button>
                            </div>
                        </div>

                        <?php
                        $user = auth_user();
                        $isLoggedIn = !empty($user);
                        ?>

                        <?php if (!$isLoggedIn): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="commenter_name" class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                                    <input type="text" id="commenter_name" name="commenter_name" required
                                        class="w-full px-3 py-2 rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                                        placeholder="Masukkan nama Anda">
                                </div>
                                <div>
                                    <label for="commenter_email" class="block text-sm font-medium text-gray-700 mb-1">Email (opsional)</label>
                                    <input type="email" id="commenter_email" name="commenter_email"
                                        class="w-full px-3 py-2 rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                                        placeholder="email@example.com">
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-600 pb-2">
                                Berkomentar sebagai: <span class="font-semibold text-gray-900"><?= esc($user['name'] ?? 'User') ?></span>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label for="comment_content" class="block text-sm font-medium text-gray-700 mb-1">Komentar *</label>
                            <textarea id="comment_content" name="content" rows="4" required
                                class="w-full px-3 py-2 rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Tulis komentar atau pertanyaan Anda di sini..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" id="submitCommentBtn"
                                class="px-5 py-2 rounded-lg bg-primary-600 text-white font-semibold hover:bg-primary-700 transition-colors">
                                <span id="submitCommentText">Kirim Komentar</span>
                                <span id="submitCommentLoading" class="hidden">Mengirim...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Daftar Komentar -->
                <div id="commentsList" class="space-y-4">
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p>Memuat komentar...</p>
                    </div>
                </div>
            </section>
        </div>
        <aside class="space-y-6">
            <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">Ringkasan Penggunaan</h2>
                    </div>
                    <?php if ($reportLink): ?>
                        <a href="<?= esc($reportLink) ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Lihat Laporan Lengkap
                        </a>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase">Urunan Terkumpul</p>
                        <p class="text-2xl font-semibold text-primary-600 mt-1">Rp <?= number_format($totalTerkumpul, 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= $totalDonationsCount ?> transaksi</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase">Urunan Terpakai</p>
                        <p class="text-2xl font-semibold text-[#b45309] mt-1">Rp <?= number_format($totalTerpakai, 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= $updatesCount ?> laporan penggunaan</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase">Sisa Urunan</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-1">Rp <?= number_format($sisaUrunan, 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-500 mt-2">≈ <?= number_format(100 - $balancePercentage, 1) ?>% dana siap disalurkan</p>
                    </div>
                </div>
            </section>
            <?php if (!empty($donations)): ?>
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Orang-orang Baik</h3>
                    <div class="space-y-3">
                        <?php foreach ($donations as $donation): ?>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold">
                                    <?= strtoupper(mb_substr($donation['donor_name'], 0, 1)) ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900"><?= esc($donation['donor_name']) ?></p>
                                    <p class="text-xs text-gray-500">Rp <?= number_format($donation['amount'], 0, ',', '.') ?> • <?= date('d M Y', strtotime($donation['created_at'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($tenant) && !empty($tenant['bank_accounts'])): ?>
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rekening Donasi</h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <?php foreach (($tenant['bank_accounts'] ?? []) as $acc): ?>
                            <div class="p-3 rounded-xl border border-gray-100">
                                <p class="font-semibold text-gray-900"><?= esc($acc['bank'] ?? '-') ?></p>
                                <p>No. Rekening: <span class="font-semibold"><?= esc($acc['account_number'] ?? '-') ?></span></p>
                                <p>a.n <span class="font-semibold"><?= esc($acc['account_name'] ?? '-') ?></span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($tenant) && !empty($tenant['description'])): ?>
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Tentang Penggalang</h3>
                    <p class="text-sm text-gray-600"><?= esc($tenant['description']) ?></p>
                </div>
            <?php endif; ?>
        </aside>
    </div>


    <!-- Donation Success/Fail Alerts -->
    <div id="donateSuccess" class="hidden max-w-4xl mx-auto">
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
            <div class="font-medium" id="donateSuccessMsg">Donasi berhasil.</div>
        </div>
        <?php if (!empty($tenant['bank_accounts'])): ?>
            <div class="mb-8 bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Instruksi Pembayaran (Transfer Bank)</h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <?php foreach (($tenant['bank_accounts'] ?? []) as $acc): ?>
                        <div class="p-3 border border-gray-100 rounded-lg">
                            <div class="font-medium text-gray-900"><?= esc($acc['bank'] ?? '-') ?> - <?= esc($acc['account_name'] ?? '-') ?></div>
                            <div class="text-gray-700">No. Rekening: <span class="font-semibold"><?= esc($acc['account_number'] ?? '-') ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mt-3">Setelah transfer, mohon tunggu verifikasi atau unggah bukti transfer melalui tautan konfirmasi yang akan kami kirimkan.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="donateError" class="hidden max-w-4xl mx-auto">
        <div class="mb-8 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            <div class="font-medium" id="donateErrorMsg">Terjadi kesalahan.</div>
        </div>
    </div>

    <!-- Donation Modal -->
    <div id="donateModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-5 sm:p-6 w-full max-w-lg relative mx-4 shadow-2xl transform transition-all duration-300 scale-95" id="donateModalContent">
            <button id="btnCloseModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1.5 transition-colors duration-200" aria-label="Tutup modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Success Message (hidden by default) -->
            <div id="donateSuccessModal" class="hidden">
                <div class="text-center py-4">
                    <div class="mb-6 animate-bounce">
                        <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">Donasi Berhasil!</h3>
                    <p id="donateSuccessModalMsg" class="text-sm text-gray-600 mb-8 leading-relaxed px-2">Donasi berhasil dibuat. silakan melakukan transfer sesuai instruksi pada pesan Whatsapp yang terkirim</p>
                    <button type="button" id="btnCloseSuccessModal" class="w-full sm:w-auto px-6 py-3 rounded-xl text-white bg-primary-600 hover:bg-primary-700 font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">Tutup</button>
                </div>
            </div>

            <!-- Donation Form (shown by default) -->
            <div id="donateFormContainer">
                <div class="mb-5 pb-3 border-b border-gray-200">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                            <svg class="w-4.5 h-4.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Ikut Urunan</h3>
                    </div>
                    <p class="text-sm text-gray-600 ml-12"><?= esc($campaign['title']) ?></p>
                </div>
                <form id="donateForm" onsubmit="submitDonation(event)">
                    <input type="hidden" id="donate_campaign_id" name="campaign_id" value="<?= (int) ($campaign['id'] ?? 0) ?>">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Donasi (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-medium text-sm">Rp</span>
                                </div>
                                <input type="number" min="1000" step="1000" name="amount" required class="block w-full pl-9 pr-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="50000">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="donor_name" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="Nama Anda">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                                <input type="text" name="donor_phone" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="081234567890">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="donor_email" required class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm" placeholder="email@contoh.com">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pesan (opsional)</label>
                            <textarea name="message" rows="3" class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 resize-none text-sm" placeholder="Tulis dukungan Anda..."></textarea>
                        </div>

                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                            <input id="is_anonymous" type="checkbox" name="is_anonymous" value="true" class="mt-1 h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <div>
                                <label for="is_anonymous" class="text-sm font-medium text-gray-700 cursor-pointer">Sembunyikan nama saya dari publik (Anonim)</label>
                                <p class="text-xs text-gray-500 mt-1">Catatan: Data Anda tetap diperlukan untuk keperluan administrasi, namun tidak akan ditampilkan di halaman publik.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
                            <div class="space-y-2" id="paymentMethodsContainer">
                                <?php if (!empty($active_payment_methods)): ?>
                                    <?php foreach ($active_payment_methods as $idx => $method): ?>
                                        <label class="flex items-center gap-3 p-2.5 border border-gray-200 rounded-xl hover:border-primary-400 hover:bg-primary-50 cursor-pointer transition-all duration-200 <?= $idx === 0 ? 'border-primary-400 bg-primary-50' : '' ?>">
                                            <input type="radio" name="payment_method" value="<?= esc($method['code'] ?? 'bank_transfer') ?>"
                                                data-type="<?= esc($method['type'] ?? 'bank-transfer') ?>"
                                                class="text-primary-600 payment-method-radio w-5 h-5"
                                                <?= $idx === 0 ? 'checked' : '' ?>>
                                            <div class="flex-1">
                                                <span class="text-sm font-semibold text-gray-800 block"><?= esc($method['name'] ?? 'Transfer Bank') ?></span>
                                                <?php if (!empty($method['description'])): ?>
                                                    <p class="text-xs text-gray-600 mt-1"><?= esc($method['description']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <label class="flex items-center gap-3 p-2.5 border border-primary-400 bg-primary-50 rounded-xl">
                                        <input type="radio" name="payment_method" value="bank_transfer" class="text-primary-600 payment-method-radio w-5 h-5" checked>
                                        <span class="text-sm font-semibold text-gray-800">Transfer Bank</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="bankAccountContainer" class="<?= (!empty($active_payment_methods) && ($active_payment_methods[0]['type'] ?? '') !== 'bank-transfer') ? 'hidden' : '' ?>">
                            <?php if (!empty($tenant['bank_accounts'])): ?>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Rekening Tujuan</label>
                                    <select name="bank_account_id" class="block w-full px-3.5 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm">
                                        <option value="">-- Pilih Rekening --</option>
                                        <?php foreach (($tenant['bank_accounts'] ?? []) as $idx => $acc): ?>
                                            <option value="<?= esc($acc['id'] ?? $idx) ?>"><?= esc(($acc['bank'] ?? '-') . ' - ' . ($acc['account_number'] ?? '-') . ' a.n ' . ($acc['account_name'] ?? '-')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-5 border-t border-gray-200">
                        <button type="button" id="btnCancelModal" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 font-medium transition-all duration-200 text-center">Batal</button>
                        <button id="donateSubmit" type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl text-white bg-primary-600 hover:bg-primary-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Kirim Donasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <?php if (!empty($allImages)): ?>
        <div id="galleryModal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center">
            <button id="galleryCloseBtn" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-2" aria-label="Tutup">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <button id="galleryPrevBtn" class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 z-10 p-2 bg-black/50 rounded-full" aria-label="Sebelumnya">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button id="galleryNextBtn" class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 z-10 p-2 bg-black/50 rounded-full" aria-label="Selanjutnya">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <div class="relative w-full h-full flex items-center justify-center px-20 py-16">
                <img id="galleryImage" src="" alt="Gallery" class="max-w-4xl max-h-[85vh] w-auto h-auto object-contain rounded-lg shadow-2xl">
            </div>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm">
                <span id="galleryCounter">1 / <?= count($allImages) ?></span>
            </div>
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Share campaign function
function shareCampaign() {
    const url = window.location.href;
    const title = '<?= esc(js_escape($campaign['title'] ?? 'Urunan')) ?>';
    const text = '<?= esc(js_escape(strip_tags($campaign['description'] ?? ''))) ?>';
    
    // Check if Web Share API is available (mobile browsers)
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).catch(err => {
            console.log('Error sharing:', err);
            // Fallback to copy URL
            copyToClipboard(url);
        });
    } else {
        // Show share options modal
        showShareModal(url, title);
    }
}

// Show share modal with options
function showShareModal(url, title) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Bagikan Urunan</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                <button onclick="shareToWhatsApp('${url}', '${title}')" class="w-full flex items-center gap-3 px-4 py-3 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span class="font-semibold text-gray-900">WhatsApp</span>
                </button>
                <button onclick="shareToFacebook('${url}')" class="w-full flex items-center gap-3 px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="font-semibold text-gray-900">Facebook</span>
                </button>
                <button onclick="shareToTwitter('${url}', '${title}')" class="w-full flex items-center gap-3 px-4 py-3 bg-sky-50 hover:bg-sky-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-sky-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                    <span class="font-semibold text-gray-900">Twitter</span>
                </button>
                <button onclick="copyToClipboard('${url}')" class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-semibold text-gray-900">Salin Link</span>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Share to WhatsApp
function shareToWhatsApp(url, title) {
    const text = encodeURIComponent(title + ' - ' + url);
    window.open('https://wa.me/?text=' + text, '_blank');
    document.querySelector('.fixed').remove();
}

// Share to Facebook
function shareToFacebook(url) {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
    document.querySelector('.fixed').remove();
}

// Share to Twitter
function shareToTwitter(url, title) {
    const text = encodeURIComponent(title);
    window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
    document.querySelector('.fixed').remove();
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Link berhasil disalin!');
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Link berhasil disalin!');
    }
    const modal = document.querySelector('.fixed');
    if (modal) modal.remove();
}

// Show toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
<style>
    /* Ensure donation modal overlay is full screen */
    #donateModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 9999 !important;
    }

    /* Mobile - modal bisa di-scroll (tanpa menampilkan scrollbar) */
    @media (max-width: 640px) {
        #donateModal {
            align-items: flex-start !important;
            overflow-y: auto !important;
            padding: 16px !important;
        }

        #donateModalContent {
            margin-top: 16px !important;
            margin-bottom: 16px !important;
            max-height: 100vh !important;
            overflow-y: auto !important;
            touch-action: auto !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        #donateModalContent::-webkit-scrollbar {
            display: none;
        }
    }

    /* Desktop - scroll jika perlu (tanpa menampilkan scrollbar) */
    @media (min-width: 641px) {
        #donateModal {
            overflow-y: visible !important;
        }

        #donateModalContent {
            max-height: 90vh !important;
            overflow-y: auto !important;
            scrollbar-width: none;
        }

        #donateModalContent::-webkit-scrollbar {
            display: none;
        }
    }

    /* Modal animation */
    #donateModal:not(.hidden) #donateModalContent {
        animation: modalFadeIn 0.3s ease-out forwards;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Ensure gallery modal is full screen */
    #galleryModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 9999 !important;
    }
</style>
<?php
$donationEndpointPath = parse_url(base_url('donation/create'), PHP_URL_PATH) ?? '/donation/create';
?>
<script>
    // === Modal Donasi (Pure JS, Fixed) ===
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('donateModal');
        const btnDonate = document.querySelectorAll('[onclick="openDonateModal()"]');
        const btnClose = document.getElementById('btnCloseModal');
        const btnCancel = document.getElementById('btnCancelModal');


        // Fungsi buka modal
        window.openDonateModal = function() {
            if (!modal) {
                console.error('Modal donasi tidak ditemukan di DOM');
                return;
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.opacity = 0;
            modal.style.transition = 'opacity 0.3s ease';

            const modalContent = document.getElementById('donateModalContent');
            if (modalContent) {
                modalContent.style.transform = 'scale(0.95) translateY(-10px)';
                modalContent.style.opacity = '0';
            }

            requestAnimationFrame(() => {
                modal.style.opacity = 1;
                if (modalContent) {
                    modalContent.style.transform = 'scale(1) translateY(0)';
                    modalContent.style.opacity = '1';
                    modalContent.style.transition = 'transform 0.3s ease-out, opacity 0.3s ease-out';
                }
            });

            document.body.style.overflow = 'hidden';
            const hiddenId = document.getElementById('donate_campaign_id');
            if (hiddenId) hiddenId.value = '<?= (int) ($campaign['id'] ?? 0) ?>';

            // Reset form and show form container, hide success message
            const formContainer = document.getElementById('donateFormContainer');
            const successModal = document.getElementById('donateSuccessModal');
            const form = document.getElementById('donateForm');

            if (formContainer) formContainer.classList.remove('hidden');
            if (successModal) successModal.classList.add('hidden');
            if (form) form.reset();

        };

        // Fungsi tutup modal
        window.closeDonateModal = function() {
            if (!modal) return;
            modal.style.opacity = 0;
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';

                // Reset form and show form container, hide success message
                const formContainer = document.getElementById('donateFormContainer');
                const successModal = document.getElementById('donateSuccessModal');
                const form = document.getElementById('donateForm');

                if (formContainer) formContainer.classList.remove('hidden');
                if (successModal) successModal.classList.add('hidden');
                if (form) form.reset();
            }, 200);
        };

        // tombol tutup (X)
        btnClose?.addEventListener('click', window.closeDonateModal);

        // tombol batal
        btnCancel?.addEventListener('click', window.closeDonateModal);

        // klik di luar modal (backdrop)
        modal?.addEventListener('click', e => {
            if (e.target === modal) {
                window.closeDonateModal();
            }
        });

        // fallback extra: kalau JS inline error di awal
        btnDonate.forEach(b => {
            b.addEventListener('click', (e) => {
                e.preventDefault();
                openDonateModal();
            });
        });

        // Toggle bank account selection based on payment method
        const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
        const bankAccountContainer = document.getElementById('bankAccountContainer');

        paymentMethodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (bankAccountContainer) {
                    const selectedType = this.getAttribute('data-type');
                    if (selectedType === 'bank-transfer') {
                        bankAccountContainer.classList.remove('hidden');
                    } else {
                        bankAccountContainer.classList.add('hidden');
                        // Clear bank_account_id selection if not bank transfer
                        const bankSelect = bankAccountContainer.querySelector('select[name="bank_account_id"]');
                        if (bankSelect) bankSelect.value = '';
                    }
                }
            });
        });

        // Init drag saat DOM ready
        setTimeout(() => {
            initModalDrag();
        }, 100);
    });

    // === Submit Donasi (tidak diubah) ===
    async function submitDonation(e) {
        e.preventDefault();
        const form = document.getElementById('donateForm');
        const formData = new FormData(form);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const submitBtn = document.getElementById('donateSubmit');
        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Memproses...';

        const donationEndpoint = (window.location.origin || '') + '<?= $donationEndpointPath ?>';

        try {
            const res = await fetch(donationEndpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Hide form and show success message in modal
                const formContainer = document.getElementById('donateFormContainer');
                const successModal = document.getElementById('donateSuccessModal');
                const successMsg = document.getElementById('donateSuccessModalMsg');
                const closeSuccessBtn = document.getElementById('btnCloseSuccessModal');

                if (formContainer && successModal && successMsg) {
                    formContainer.classList.add('hidden');
                    successModal.classList.remove('hidden');
                    successMsg.textContent = 'Donasi berhasil dibuat. silakan melakukan transfer sesuai instruksi pada pesan Whatsapp yang terkirim';

                    // Close modal when close button is clicked
                    if (closeSuccessBtn) {
                        closeSuccessBtn.onclick = function() {
                            window.closeDonateModal();
                            // Reset form for next donation
                            formContainer.classList.remove('hidden');
                            successModal.classList.add('hidden');
                            form.reset();
                        };
                    }
                }
            } else {
                const errBox = document.getElementById('donateError');
                const msg = document.getElementById('donateErrorMsg');
                errBox.classList.remove('hidden');
                msg.textContent = data.message || 'Gagal membuat donasi';
            }
        } catch (err) {
            console.error(err);
            const errBox = document.getElementById('donateError');
            const msg = document.getElementById('donateErrorMsg');
            errBox.classList.remove('hidden');
            msg.textContent = 'Terjadi kesalahan saat membuat donasi';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        }
    }

    // === Gallery Modal dengan Swipe ===
    <?php if (!empty($allImages)): ?>
        const galleryImages = <?= json_encode($allImages) ?>;
        let currentGalleryIndex = 0;
        let touchStartX = 0;
        let touchEndX = 0;

        const galleryModal = document.getElementById('galleryModal');
        const galleryImage = document.getElementById('galleryImage');
        const galleryCounter = document.getElementById('galleryCounter');
        const galleryCloseBtn = document.getElementById('galleryCloseBtn');
        const galleryPrevBtn = document.getElementById('galleryPrevBtn');
        const galleryNextBtn = document.getElementById('galleryNextBtn');

        function openGalleryModal(index) {
            currentGalleryIndex = index;
            updateGalleryImage();
            if (galleryModal) {
                galleryModal.classList.remove('hidden');
                galleryModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeGalleryModal() {
            if (galleryModal) {
                galleryModal.classList.add('hidden');
                galleryModal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }

        function updateGalleryImage() {
            if (galleryImage && galleryImages[currentGalleryIndex]) {
                galleryImage.src = galleryImages[currentGalleryIndex];
                if (galleryCounter) {
                    galleryCounter.textContent = (currentGalleryIndex + 1) + ' / ' + galleryImages.length;
                }
            }
        }

        function showNextImage() {
            if (currentGalleryIndex < galleryImages.length - 1) {
                currentGalleryIndex++;
            } else {
                currentGalleryIndex = 0;
            }
            updateGalleryImage();
        }

        function showPrevImage() {
            if (currentGalleryIndex > 0) {
                currentGalleryIndex--;
            } else {
                currentGalleryIndex = galleryImages.length - 1;
            }
            updateGalleryImage();
        }

        // Event listeners
        if (galleryCloseBtn) {
            galleryCloseBtn.addEventListener('click', closeGalleryModal);
        }

        if (galleryPrevBtn) {
            galleryPrevBtn.addEventListener('click', showPrevImage);
        }

        if (galleryNextBtn) {
            galleryNextBtn.addEventListener('click', showNextImage);
        }

        // Close on backdrop click
        if (galleryModal) {
            galleryModal.addEventListener('click', function(e) {
                if (e.target === galleryModal) {
                    closeGalleryModal();
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!galleryModal || galleryModal.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeGalleryModal();
            } else if (e.key === 'ArrowLeft') {
                showPrevImage();
            } else if (e.key === 'ArrowRight') {
                showNextImage();
            }
        });

        // Touch swipe support
        if (galleryModal) {
            galleryModal.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, {
                passive: true
            });

            galleryModal.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, {
                passive: true
            });
        }

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next image
                    showNextImage();
                } else {
                    // Swipe right - prev image
                    showPrevImage();
                }
            }
        }

        window.openGalleryModal = openGalleryModal;
    <?php endif; ?>

    // === Komentar & Diskusi ===
    const campaignId = <?= (int) ($campaign['id'] ?? 0) ?>;
    let currentReplyTo = null;

    // Load komentar
    function loadComments() {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;

        fetch(`/discussion/campaign/${campaignId}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Comments loaded:', data);
                if (data.success && data.data) {
                    renderComments(data.data);
                    updateCommentsCount(data.data);
                } else {
                    commentsList.innerHTML = '<div class="text-center py-8 text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar!</div>';
                }
            })
            .catch(err => {
                console.error('Error loading comments:', err);
                commentsList.innerHTML = '<div class="text-center py-8 text-red-500">Gagal memuat komentar. Silakan refresh halaman.</div>';
            });
    }

    // Render komentar
    function renderComments(comments) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList || !Array.isArray(comments)) return;

        if (comments.length === 0) {
            commentsList.innerHTML = '<div class="text-center py-8 text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar!</div>';
            return;
        }

        let html = '';
        comments.forEach(comment => {
            const date = new Date(comment.created_at);
            const dateStr = date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            html += `
                <div class="border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors" data-comment-id="${comment.id}">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold">
                            ${(comment.commenter_name || 'User').charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900">${escapeHtml(comment.commenter_name || 'User')}</span>
                                ${comment.is_pinned ? '<span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full" title="Komentar disematkan" aria-label="Komentar disematkan">📌</span>' : ''}
                                <span class="text-xs text-gray-500">${dateStr}</span>
                            </div>
                            <div class="text-gray-700 mb-3 whitespace-pre-wrap">${escapeHtml(comment.content || '')}</div>
                            <div class="flex items-center gap-4">
                                <button onclick="likeComment(${comment.id}, this)" class="flex items-center gap-1 text-sm ${comment.is_liked ? 'text-primary-600' : 'text-gray-600'} hover:text-primary-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span>Suka</span>
                                    <span class="like-count">${comment.likes_count || 0}</span>
                                </button>
                                <button onclick="aminComment(${comment.id}, this)" class="flex items-center gap-1 text-sm ${comment.is_amined ? 'text-primary-600' : 'text-gray-600'} hover:text-primary-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="amin-count">Aamiin (${comment.amins_count || 0})</span>
                                </button>
                                <button onclick="replyToComment(${comment.id}, '${escapeHtml(comment.commenter_name || 'User')}')" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">
                                    Balas
                                </button>
                            </div>
                            
                            ${comment.replies && comment.replies.length > 0 ? `
                                <div class="mt-4 ml-4 pl-4 border-l-2 border-gray-200 space-y-3">
                                    ${comment.replies.map(reply => {
                                        const replyDate = new Date(reply.created_at);
                                        const replyDateStr = replyDate.toLocaleDateString('id-ID', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        });
                                        return `
                                            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-sm text-gray-900">${escapeHtml(reply.commenter_name || 'User')}</span>
                                                    <span class="text-xs text-gray-500">${replyDateStr}</span>
                                                </div>
                                                <div class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(reply.content || '')}</div>
                                                <div class="flex items-center gap-3 mt-2">
                                                    <button onclick="likeComment(${reply.id}, this)" class="flex items-center gap-1 text-xs ${reply.is_liked ? 'text-primary-600' : 'text-gray-600'} hover:text-primary-600 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                        </svg>
                                                        <span>Suka</span>
                                                        <span class="like-count">${reply.likes_count || 0}</span>
                                                    </button>
                                                    <button onclick="aminComment(${reply.id}, this)" class="flex items-center gap-1 text-xs ${reply.is_amined ? 'text-primary-600' : 'text-gray-600'} hover:text-primary-600 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="amin-count">Aamiin (${reply.amins_count || 0})</span>
                                                    </button>
                                                </div>
                                            </div>
                                        `;
        }).join('')
    } <
    /div>
    ` : ''}
                        </div>
                    </div>
                </div>
            `;
    });

    commentsList.innerHTML = html;
    }

    // Update jumlah komentar
    function updateCommentsCount(comments) {
        const countEl = document.getElementById('commentsCount');
        if (countEl) {
            const total = comments.reduce((sum, c) => sum + 1 + (c.replies?.length || 0), 0);
            countEl.textContent = `${total} komentar`;
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Reply ke komentar
    function replyToComment(commentId, commenterName) {
        currentReplyTo = commentId;
        document.getElementById('reply_to_id').value = commentId;
        document.getElementById('replyToName').textContent = `Membalas ${commenterName}`;
        document.getElementById('replyIndicator').classList.remove('hidden');
        document.getElementById('comment_content').focus();

        // Scroll ke form
        document.getElementById('commentForm').scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    }

    // Cancel reply
    document.getElementById('cancelReply')?.addEventListener('click', function() {
        currentReplyTo = null;
        document.getElementById('reply_to_id').value = '';
        document.getElementById('replyIndicator').classList.add('hidden');
    });

    // Submit komentar
    document.getElementById('commentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitCommentBtn');
        const submitText = document.getElementById('submitCommentText');
        const submitLoading = document.getElementById('submitCommentLoading');

        // Pastikan parent_id hanya dikirim jika ada reply aktif
        const replyToId = document.getElementById('reply_to_id').value;
        if (!replyToId || replyToId.trim() === '' || !currentReplyTo) {
            // Hapus parent_id dari FormData jika tidak ada reply
            formData.delete('parent_id');
        } else {
            // Pastikan nilai parent_id sesuai dengan currentReplyTo
            formData.set('parent_id', currentReplyTo);
        }

        // Disable button
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitLoading.classList.remove('hidden');

        fetch('/discussion/comment', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) {
                    return res.json().then(data => {
                        console.error('Error response:', data);
                        throw new Error(data.message || 'Gagal mengirim komentar');
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log('Comment submitted:', data);
                if (data.success) {
                    // Reset form dan clear reply state
                    form.reset();
                    document.getElementById('reply_to_id').value = '';
                    document.getElementById('replyIndicator').classList.add('hidden');
                    currentReplyTo = null;

                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    successMsg.textContent = 'Komentar berhasil dikirim! Komentar Anda sedang menunggu moderasi.';
                    document.body.appendChild(successMsg);
                    setTimeout(() => successMsg.remove(), 5000);

                    // Tidak perlu reload comments karena komentar masih pending
                    // Komentar akan muncul setelah di-approve oleh admin/tenant
                } else {
                    throw new Error(data.message || 'Gagal mengirim komentar');
                }
            })
            .catch(err => {
                console.error('Error submitting comment:', err);
                alert(err.message || 'Terjadi kesalahan. Silakan coba lagi.');
            })
            .finally(() => {
                // Enable button
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            });
    });

    // Like komentar
    function likeComment(commentId, button) {
        fetch(`/discussion/comment/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const countEl = button.querySelector('.like-count');
                    const likesCount = data.data.likes_count ?? data.likes_count ?? 0;
                    const isLiked = data.data.liked ?? data.liked ?? false;

                    if (countEl && likesCount !== undefined) {
                        countEl.textContent = likesCount;
                    }

                    // Update button state based on server response
                    if (isLiked) {
                        button.classList.add('text-primary-600');
                    } else {
                        button.classList.remove('text-primary-600');
                    }
                } else {
                    console.log(data.message);
                }
            })
            .catch(err => {
                console.error('Error liking comment:', err);
            });
    }

    // Aamiin komentar
    function aminComment(commentId, button) {
        fetch(`/discussion/comment/${commentId}/amin`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const countEl = button.querySelector('.amin-count');
                    const aminsCount = data.data.amins_count ?? data.amins_count ?? 0;
                    const isAmined = data.data.amined ?? data.amined ?? false;

                    if (countEl && aminsCount !== undefined) {
                        countEl.textContent = `Aamiin (${aminsCount})`;
                    }

                    // Update button state based on server response
                    if (isAmined) {
                        button.classList.add('text-primary-600');
                    } else {
                        button.classList.remove('text-primary-600');
                    }
                } else {
                    console.log(data.message);
                }
            })
            .catch(err => {
                console.error('Error amining comment:', err);
            });
    }

    // Load comments on page load
    if (campaignId > 0) {
        loadComments();
    }
</script>
<?= $this->endSection() ?>