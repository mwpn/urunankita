<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title><?= esc($article['title']) ?> - <?= esc($tenant['name'] ?? 'UrunanKita') ?></title>
<?php if (!empty($article['excerpt'])): ?>
<meta name="description" content="<?= esc($article['excerpt']) ?>">
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Back Link -->
    <a href="/articles" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Artikel
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
    <!-- Article Header -->
    <article class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <?php if (!empty($article['image'])): ?>
                    <?php 
                    $img = $article['image'];
                    if (!preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                        $img = '/uploads/' . ltrim($img, '/');
                    }
                    ?>
                    <div class="w-full h-96 bg-gray-200" style="background-image: url('<?= esc(base_url(ltrim($img, '/'))) ?>'); background-size: cover; background-position: center;"></div>
                <?php endif; ?>

                <div class="p-8">
                    <?php if (!empty($article['category'])): ?>
                        <span class="inline-block bg-primary-100 text-primary-700 text-xs font-semibold px-3 py-1 rounded-full mb-4"><?= esc($article['category']) ?></span>
                    <?php endif; ?>
                    
                    <h1 class="text-4xl font-bold text-gray-900 mb-4"><?= esc($article['title']) ?></h1>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-600 mb-6 pb-6 border-b border-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <?= esc($article['author_name'] ?? ($tenant['name'] ?? 'Admin')) ?>
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <?= date('d F Y', strtotime($article['created_at'])) ?>
                        </span>
                        <?php if (!empty($article['views'])): ?>
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <?= number_format($article['views'], 0, ',', '.') ?> views
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($article['excerpt'])): ?>
                        <div class="text-lg text-gray-700 mb-6 italic border-l-4 border-primary-500 pl-4">
                            <?= esc($article['excerpt']) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Article Content -->
                    <div class="prose prose-lg max-w-none">
                        <?= $article['content'] ?>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <div class="space-y-6">
            <?php if (!empty($campaign_stats)): ?>
                <?php
                    $campaign = $campaign_stats['campaign'];
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $baseDomain = env('app.baseDomain', 'urunankita.test');
                    $campaignTenantId = $campaign['tenant_id'] ?? null;
                    $campaignUrl = '/campaign/' . $campaign['slug'];
                    
                if ($campaignTenantId) {
                    $db = \Config\Database::connect();
                    $campaignTenant = $db->table('tenants')->where('id', $campaignTenantId)->get()->getRowArray();
                    // Only use subdomain if tenant is not platform
                    if ($campaignTenant && !empty($campaignTenant['slug']) && $campaignTenant['slug'] !== 'platform') {
                        $campaignUrl = $scheme . '://' . $campaignTenant['slug'] . '.' . $baseDomain . '/campaign/' . $campaign['slug'];
                    }
                }
                ?>
                <!-- Campaign Stats -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Statistik Urunan</h3>
                    <a href="<?= esc($campaignUrl) ?>" class="block mb-4 group">
                        <h4 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors mb-2">
                            <?= esc($campaign['title']) ?>
                        </h4>
                        <?php if (isset($campaign_stats['progress_percentage'])): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign_stats['progress_percentage']) ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mb-3"><?= number_format($campaign_stats['progress_percentage'], 0) ?>% tercapai</p>
                        <?php endif; ?>
                    </a>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <p class="text-xl font-bold text-[#055b16]"><?= number_format($campaign_stats['total_donations'], 0, ',', '.') ?></p>
                            <p class="text-xs text-gray-600">Total Donasi</p>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <p class="text-xl font-bold text-[#055b16]"><?= number_format($campaign_stats['total_donors'], 0, ',', '.') ?></p>
                            <p class="text-xs text-gray-600">Donatur</p>
                        </div>
                    </div>
                    
                    <div class="text-center p-3 bg-green-50 rounded-lg mb-4">
                        <p class="text-lg font-bold text-[#055b16]">Rp <?= number_format($campaign_stats['total_amount'], 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-600">Total Terkumpul</p>
                    </div>
                    
                    <a href="<?= esc($campaignUrl) ?>" class="block w-full text-center px-4 py-2 bg-[#055b16] text-white rounded-lg hover:opacity-90 transition-colors text-sm font-semibold">
                        Lihat Detail Urunan
                    </a>
                </div>
                
                <!-- Campaign Comments -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Diskusi</h3>
                    <?php if (!empty($campaign_comments)): ?>
                        <div class="space-y-4">
                            <?php foreach ($campaign_comments as $comment): ?>
                                <div class="border-b border-gray-100 last:border-0 pb-4 last:pb-0">
                                    <div class="text-sm font-semibold text-gray-900 mb-1"><?= esc($comment['commenter_name'] ?? 'Orang Baik') ?></div>
                                    <div class="text-sm text-gray-700 mb-2"><?= esc($comment['content'] ?? '') ?></div>
                                    <div class="text-xs text-gray-500"><?= !empty($comment['created_at']) ? date('d M Y, H:i', strtotime($comment['created_at'])) : '' ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?= esc($campaignUrl) ?>#diskusi" class="mt-4 inline-flex items-center text-sm font-semibold text-[#055b16] hover:underline">
                            Lihat semua diskusi
                        </a>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Belum ada diskusi. <a href="<?= esc($campaignUrl) ?>#diskusi" class="text-[#055b16] font-semibold hover:underline">Mulai diskusi di halaman urunan.</a></p>
                    <?php endif; ?>
                </div>

                <!-- Recent Donations -->
                <?php if (!empty($campaign_donations)): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Donatur Terbaru</h3>
                    <div class="space-y-3">
                        <?php foreach ($campaign_donations as $donation): ?>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-primary-700 font-semibold text-sm">
                                        <?= strtoupper(mb_substr($donation['donor_name'], 0, 1)) ?>
                                    </span>
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
            <?php else: ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Diskusi</h3>
                    <p class="text-sm text-gray-500">Diskusi urunan akan tampil jika artikel ini terhubung dengan "Urunan Terkait". Edit artikel dan pilih urunan yang sesuai untuk menampilkan statistik, donatur, dan komentar.</p>
                </div>

                    <?php if (!empty($related_campaigns)): ?>
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Urunan Terkait</h3>
                        <div class="space-y-4">
                            <?php foreach ($related_campaigns as $campaign): ?>
                                <?php
                                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                $baseDomain = env('app.baseDomain', 'urunankita.test');
                                $campaignTenantId = $campaign['tenant_id'] ?? null;
                                $campaignUrl = '/campaign/' . $campaign['slug'];
                                
                                if ($campaignTenantId) {
                                    $db = \Config\Database::connect();
                                    $campaignTenant = $db->table('tenants')->where('id', $campaignTenantId)->get()->getRowArray();
                                    if ($campaignTenant && !empty($campaignTenant['slug']) && $campaignTenant['slug'] !== 'platform') {
                                        $campaignUrl = $scheme . '://' . $campaignTenant['slug'] . '.' . $baseDomain . '/campaign/' . $campaign['slug'];
                                    }
                                }
                                ?>
                                <a href="<?= esc($campaignUrl) ?>" class="block group">
                                    <div class="space-y-2">
                                        <div class="flex gap-3">
                                            <?php 
                                            $img = $campaign['featured_image'] ?? $campaign['image'] ?? '';
                                            if ($img && !preg_match('~^https?://~', $img)) {
                                                if (strpos($img, '/uploads/') !== 0) {
                                                    $img = '/uploads/' . ltrim($img, '/');
                                                }
                                                $img = base_url(ltrim($img, '/'));
                                            }
                                            ?>
                                            <?php if (!empty($img)): ?>
                                                <img src="<?= esc($img) ?>" alt="<?= esc($campaign['title']) ?>" class="w-20 h-20 object-cover rounded-lg flex-shrink-0" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0 flex items-center justify-center" style="display: none;">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 transition-colors line-clamp-2 mb-1">
                                                    <?= esc($campaign['title']) ?>
                                                </h4>
                                                <?php if (isset($campaign['total_donations'])): ?>
                                                    <p class="text-xs text-gray-500"><?= number_format($campaign['total_donations'], 0, ',', '.') ?> donasi</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (isset($campaign['progress_percentage'])): ?>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-600"><?= number_format($campaign['progress_percentage'], 0) ?>% tercapai</span>
                                                <?php if (isset($campaign['total_amount'])): ?>
                                                    <span class="text-[#055b16] font-semibold">Rp <?= number_format($campaign['total_amount'], 0, ',', '.') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </aside>
    </div>

    <!-- Related Articles -->
    <?php if (isset($related_articles) && !empty($related_articles)): ?>
        <div class="mt-8 max-w-6xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Artikel Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($related_articles as $related): ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <a href="/article/<?= esc($related['slug']) ?>">
                            <?php if (!empty($related['image'])): ?>
                                <?php 
                                $img = $related['image'];
                                if (!preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                    $img = '/uploads/' . ltrim($img, '/');
                                }
                                ?>
                                <div class="h-40 bg-gray-200" style="background-image: url('<?= esc(base_url(ltrim($img, '/'))) ?>'); background-size: cover; background-position: center;"></div>
                            <?php else: ?>
                                <div class="h-40 bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="/article/<?= esc($related['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($related['title']) ?></a>
                            </h3>
                            <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($related['created_at'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.prose {
    color: #374151;
    line-height: 1.8;
}

.prose h2 {
    font-size: 1.875rem;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #111827;
}

.prose h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.prose p {
    margin-bottom: 1.25rem;
    color: #4b5563;
}

.prose ul, .prose ol {
    margin-bottom: 1.25rem;
    padding-left: 1.75rem;
}

.prose li {
    margin-bottom: 0.5rem;
    color: #4b5563;
}

.prose img {
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.prose a {
    color: #16a34a;
    text-decoration: underline;
}

.prose a:hover {
    color: #15803d;
}
</style>
<?= $this->endSection() ?>

