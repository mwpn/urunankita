<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
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