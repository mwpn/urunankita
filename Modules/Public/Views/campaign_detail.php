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

    // Debug output (commented out - uncomment for debugging if needed)
    /*
    echo "<!-- DEBUG: Updates count: " . count($updates) . " -->";
    echo "<!-- DEBUG: All images count: " . count($allImages) . " -->";
    echo "<!-- DEBUG: YouTube videos count: " . count($youtubeVideos) . " -->";
    */

    // Ensure variables are available for included views
    // In CodeIgniter 4, variables defined in parent view should be available in included views
    // But let's make sure by explicitly setting them
    $this->setVar('allImages', $allImages);
    $this->setVar('youtubeVideos', $youtubeVideos);
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 space-y-6">
            <?= $this->include('Modules\Public\Views\campaign\_campaign_header') ?>
            <?= $this->include('Modules\Public\Views\campaign\_campaign_stats') ?>
            <?= $this->include('Modules\Public\Views\campaign\_campaign_gallery') ?>
            <?= $this->include('Modules\Public\Views\campaign\_campaign_videos') ?>
            <?= $this->include('Modules\Public\Views\campaign\_campaign_comments') ?>
        </div>

        <?= $this->include('Modules\Public\Views\campaign\_campaign_sidebar') ?>
    </div>

    <?= $this->include('Modules\Public\Views\campaign\_campaign_donate_alerts') ?>
    <?= $this->include('Modules\Public\Views\campaign\_campaign_donate_modal') ?>
    <?= $this->include('Modules\Public\Views\campaign\_campaign_gallery_modal') ?>

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
    /* Simple fix - hanya pastikan tidak ada column layout */
    #commentsList {
        column-count: 1 !important;
        columns: 1 !important;
    }

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
$commentsListPath = parse_url(base_url('discussion/campaign/' . (int) ($campaign['id'] ?? 0)), PHP_URL_PATH) ?? '/discussion/campaign/' . (int) ($campaign['id'] ?? 0);
$commentCreatePath = parse_url(base_url('discussion/comment'), PHP_URL_PATH) ?? '/discussion/comment';
$commentBasePath = parse_url(base_url('discussion/comment'), PHP_URL_PATH) ?? '/discussion/comment';
?>
<script>
    window.CAMPAIGN_DETAIL_CONFIG = {
        campaignId: <?= (int) ($campaign['id'] ?? 0) ?>,
        csrfTokenName: "<?= csrf_token() ?>",
        csrfTokenValue: "<?= csrf_hash() ?>",
        endpoints: {
            donationCreate: "<?= $donationEndpointPath ?>",
            commentsList: "<?= $commentsListPath ?>",
            commentCreate: "<?= $commentCreatePath ?>",
            likeCommentBase: "<?= $commentBasePath ?>",
            aminCommentBase: "<?= $commentBasePath ?>"
        },
        galleryImages: <?= json_encode($allImages ?? []) ?>
    };
</script>

<script src="<?= base_url('assets/js/campaign_detail.js') ?>"></script>
<?= $this->endSection() ?>