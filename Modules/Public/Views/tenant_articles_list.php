<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<title>Artikel - <?= esc($tenant['name'] ?? 'UrunanKita') ?></title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hero -->
<section class="bg-gradient-to-b from-white to-slate-100">
    <div class="max-w-6xl mx-auto px-4 py-12 text-center">
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900">Kabar Terbaru</h2>
        <p class="mt-4 text-slate-600">Kabar terbaru dari <?= esc($tenant['name'] ?? 'kami') ?></p>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-6xl mx-auto px-4 py-8">

    <?php if (empty($articles)): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
            <p class="text-gray-500 text-lg">Belum ada artikel tersedia</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $article): ?>
                <div class="article-card bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300 hover:shadow-md transition-all">
                    <a href="/article/<?= esc($article['slug']) ?>">
                        <?php if (!empty($article['image'])): ?>
                            <?php
                            $img = $article['image'];
                            if (!preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                $img = '/uploads/' . ltrim($img, '/');
                            }
                            ?>
                            <div class="h-48 bg-gray-200" style="background-image: url('<?= esc(base_url(ltrim($img, '/'))) ?>'); background-size: cover; background-position: center;"></div>
                        <?php else: ?>
                            <div class="h-48 bg-gray-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="p-5">
                        <?php if (!empty($article['category'])): ?>
                            <span class="inline-block bg-primary-100 text-primary-700 text-xs font-semibold px-3 py-1 rounded-full mb-3"><?= esc($article['category']) ?></span>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2">
                            <a href="/article/<?= esc($article['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($article['title']) ?></a>
                        </h3>
                        <?php if (!empty($article['excerpt'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= esc($article['excerpt']) ?></p>
                        <?php elseif (!empty($article['content'])): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= esc(substr(strip_tags($article['content']), 0, 100)) ?><?= strlen(strip_tags($article['content'])) > 100 ? '...' : '' ?></p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                            <span><?= esc($article['author_name'] ?? 'Admin') ?></span>
                            <span><?= date('d M Y', strtotime($article['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>