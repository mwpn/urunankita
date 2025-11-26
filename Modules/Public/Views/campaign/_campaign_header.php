<?php
// Helper function untuk resolve image URL
$resolveImageUrl = static function ($path) {
    if (empty($path)) {
        return null;
    }
    if (!preg_match('~^https?://~', $path) && strpos($path, '/uploads/') !== 0) {
        $path = '/uploads/' . ltrim($path, '/');
    }
    return base_url(ltrim($path, '/'));
};

$img = $campaign['featured_image'] ?? '';
if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
    $img = '/uploads/' . ltrim($img, '/');
}
?>
<article class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
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

