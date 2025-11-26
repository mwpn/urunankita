<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title>Urunan - <?= esc($tenant['name'] ?? 'UrunanKita') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hero -->
<section class="bg-gradient-to-b from-white to-slate-100">
    <div class="max-w-6xl mx-auto px-4 py-12 text-center">
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900">Urunan Kami</h2>
        <p class="mt-4 text-slate-600">Urunan yang sedang kami galang</p>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-8">
    <?php if (empty($campaigns)): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-500">Belum ada urunan aktif</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <?php 
                    $img = $campaign['featured_image'] ?? '';
                    if ($img && !preg_match('~^https?://~', $img)) {
                        if (strpos($img, '/uploads/') !== 0) {
                            $img = '/uploads/' . ltrim($img, '/');
                        }
                        $img = base_url(ltrim($img, '/'));
                    }
                    ?>
                    <?php if (!empty($img)): ?>
                        <a href="/campaign/<?= esc($campaign['slug']) ?>">
                            <img src="<?= esc($img) ?>" alt="<?= esc($campaign['title']) ?>" class="w-full h-48 object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-48 bg-gray-100 flex items-center justify-center\'><span class=\'text-gray-400\'>No Image</span></div>';">
                        </a>
                    <?php else: ?>
                        <a href="/campaign/<?= esc($campaign['slug']) ?>">
                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        </a>
                    <?php endif; ?>
                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <a href="/campaign/<?= esc($campaign['slug']) ?>" class="hover:text-primary-600">
                                <?= esc($campaign['title']) ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            <?= esc(substr($campaign['description'] ?? '', 0, 100)) ?><?= strlen($campaign['description'] ?? '') > 100 ? '...' : '' ?>
                        </p>
                        
                        <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                            <div class="mb-3">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-500 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        dari Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="text-xs font-medium text-primary-600">
                                    <?= round($campaign['progress_percentage'], 1) ?>%
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-sm font-semibold text-gray-900">
                                Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?> terkumpul
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
