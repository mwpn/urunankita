<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if ($is_main_domain): ?>
    <!-- Hero Section -->
    <section class="relative py-8 md:py-12 overflow-hidden" style="background-color:#7db173;">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="px-4">
                    <h1 class="text-5xl md:text-6xl font-bold mb-4 text-white">
                        Kebaikan <span class="text-[#055b16]">kecil</span> yang berarti <span class="text-[#055b16]">besar</span>
                    </h1>

                    <p class="text-base mb-8 text-black">
                        Bantuan kecil yang kita berikan mungkin adalah jawaban dari do'a yang mereka panjatkan.
                    </p>

                    <a href="<?= base_url('/campaigns') ?>"
                        class="inline-block px-5 py-1.5 rounded-md font-semibold text-sm hover:opacity-90 transition-colors shadow-md text-white bg-[#055b16]">
                        Lihat Urunan
                    </a>
                </div>

                <div class="py-0">
                    <?php
                    $heroImage = $settings['hero_image'] ?? null;
                    if ($heroImage && !preg_match('~^https?://~', $heroImage)) {
                        if (strpos($heroImage, '/uploads/') !== 0) {
                            $heroImage = '/uploads/' . ltrim($heroImage, '/');
                        }
                        $heroImage = base_url(ltrim($heroImage, '/'));
                    } elseif (!$heroImage) {
                        $heroImage = base_url('assets/images/hero-image.png');
                    }
                    ?>
                    <img src="<?= esc($heroImage) ?>" alt="Hero" class="w-full max-w-[85%] mx-auto h-auto object-contain" onerror="this.style.display='none';">
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($sponsors)): ?>
        <!-- Sponsor Section -->
        <section class="py-8 bg-gray-200">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12">
                    <?php foreach ($sponsors as $sponsor): ?>
                        <div class="flex items-center justify-center" style="max-width: 150px; height: 60px;">
                            <?php if (!empty($sponsor['website'])): ?>
                                <a href="<?= esc($sponsor['website']) ?>" target="_blank" rel="noopener noreferrer" class="hover:opacity-80 transition-opacity">
                                <?php endif; ?>
                                <?php
                                $logoUrl = $sponsor['logo'];
                                if (!preg_match('~^https?://~', $logoUrl) && strpos($logoUrl, '/uploads/') !== 0) {
                                    $logoUrl = '/uploads/' . ltrim($logoUrl, '/');
                                }
                                ?>
                                <img src="<?= esc(base_url(ltrim($logoUrl, '/'))) ?>" alt="<?= esc($sponsor['name']) ?>" class="max-h-full max-w-full object-contain" style="filter: grayscale(100%); opacity: 0.6;">
                                <?php if (!empty($sponsor['website'])): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($top_campaigns)): ?>
        <!-- Urunan Prioritas -->
        <section class="py-12 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Title Left, Cards Right (3 columns: 1 for text, 2 for cards) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <div class="md:col-span-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-primary-500 text-white">
                                Prioritas
                            </span>
                        </div>
                        <h2 class="text-6xl font-bold text-gray-800 mb-3">Urunan <span class="text-[#055b16]">Prioritas</h2>
                        <p class="text-sm text-gray-600 mb-2"><?= count($top_campaigns) ?> urunan prioritas</p>
                        <p class="text-sm text-gray-500">Urunan yang membutuhkan bantuan segera.</p>
                    </div>
                    <div class="md:col-span-2 grid grid-cols-1 <?= count($top_campaigns) >= 2 ? 'md:grid-cols-2' : '' ?> gap-6">
                        <?php foreach (array_slice($top_campaigns, 0, 2) as $campaign): ?>
                            <?php
                            // Generate campaign URL
                            $campaignUrl = '/campaign/' . esc($campaign['slug']);
                            if (isset($campaign['tenant_slug']) && $campaign['tenant_slug'] !== 'platform' && !empty($campaign['tenant_slug'])) {
                                $baseDomain = env('app.baseDomain', 'urunankita.test');
                                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                $campaignUrl = $scheme . '://' . $campaign['tenant_slug'] . '.' . $baseDomain . '/campaign/' . esc($campaign['slug']);
                            }
                            ?>
                            <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                                <?php
                                $img = $campaign['featured_image'] ?? '';
                                if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                    $img = '/uploads/' . ltrim($img, '/');
                                }
                                ?>
                                <a href="<?= esc($campaignUrl) ?>">
                                    <div class="h-40 <?= !empty($img) ? '' : 'bg-gradient-to-br from-gray-100 to-gray-200' ?>" <?= !empty($img) ? 'style="background-image: url(\'' . esc(base_url(ltrim($img, '/'))) . '\'); background-size: cover; background-position: center;"' : '' ?>>
                                        <?php if (empty($img)): ?>
                                            <div class="h-full flex items-center justify-center">
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="p-4">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <a href="<?= esc($campaignUrl) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc(substr($campaign['description'] ?? '', 0, 80)) ?><?= strlen($campaign['description'] ?? '') > 80 ? '...' : '' ?></p>

                                    <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                                        <div class="mb-3">
                                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                            </div>
                                            <div class="flex justify-between items-center text-xs text-gray-600">
                                                <span>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></span>
                                                <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <div class="text-sm font-semibold text-gray-900">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <a href="<?= esc($campaignUrl) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($campaigns) && isset($campaigns[0]['campaigns'])): ?>
        <!-- Semua Urunan - Grouped by Tenant -->
        <?php
        $platformSlug = 'platform';
        foreach ($campaigns as $index => $tenantGroup):
            // Check if this is platform tenant
            $isPlatform = (
                ($tenantGroup['tenant_slug'] === $platformSlug) ||
                ($tenantGroup['tenant_slug'] === 'platform') ||
                (isset($tenantGroup['tenant_id']) && $tenantGroup['tenant_id'] == 4)
            );

            // Background: Platform = light green, others alternate
            if ($isPlatform) {
                $bgClass = 'bg-green-50';
            } else {
                // After platform, index 1 = white, index 2 = green, etc
                $bgClass = ($index % 2 == 1) ? 'bg-white' : 'bg-green-50';
            }

            // Layout: Platform = title left, cards below | Others alternate
            if ($isPlatform) {
                $layoutType = 'platform'; // Title left, cards below
            } else {
                // After platform: index 1 = cards left title right (rata kanan), index 2 = title left cards right
                $layoutType = ($index % 2 == 1) ? 'title-right' : 'title-left';
            }
            // Check if this is the first section after platform (for text alignment)
            $isFirstAfterPlatform = ($index == 1);
        ?>
            <section class="py-12 <?= $bgClass ?>">
                <div class="max-w-6xl mx-auto px-4">
                    <?php if ($layoutType === 'platform'): ?>
                        <!-- Platform Layout: Title Left, Cards Below -->
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-primary-500 text-white">
                                    Platform
                                </span>
                                <h2 class="text-2xl font-bold text-gray-800">
                                    <?= esc($tenantGroup['tenant_name']) ?>
                                </h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <?php foreach (array_slice($tenantGroup['campaigns'], 0, 3) as $campaign): ?>
                                <?php
                                // Generate campaign URL
                                if ($isPlatform || $tenantGroup['tenant_slug'] === 'platform') {
                                    $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                } else {
                                    if (!empty($tenantGroup['tenant_slug']) && $tenantGroup['tenant_slug'] !== 'unknown') {
                                        $baseDomain = env('app.baseDomain', 'urunankita.test');
                                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                        $campaignUrl = $scheme . '://' . $tenantGroup['tenant_slug'] . '.' . $baseDomain . '/campaign/' . esc($campaign['slug']);
                                    } else {
                                        $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                    }
                                }
                                ?>
                                <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                                    <?php
                                    $img = $campaign['featured_image'] ?? '';
                                    if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                        $img = '/uploads/' . ltrim($img, '/');
                                    }
                                    ?>
                                    <a href="<?= esc($campaignUrl) ?>">
                                        <div class="h-40 <?= !empty($img) ? '' : 'bg-gradient-to-br from-gray-100 to-gray-200' ?>" <?= !empty($img) ? 'style="background-image: url(\'' . esc(base_url(ltrim($img, '/'))) . '\'); background-size: cover; background-position: center;"' : '' ?>>
                                            <?php if (empty($img)): ?>
                                                <div class="h-full flex items-center justify-center">
                                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    <div class="p-4">
                                        <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                            <a href="<?= esc($campaignUrl) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc(substr($campaign['description'] ?? '', 0, 80)) ?><?= strlen($campaign['description'] ?? '') > 80 ? '...' : '' ?></p>

                                        <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                                            <div class="mb-3">
                                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                    <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                                </div>
                                                <div class="flex justify-between items-center text-xs text-gray-600">
                                                    <span>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></span>
                                                    <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <div class="text-sm font-semibold text-gray-900">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?= esc($campaignUrl) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($layoutType === 'title-left'): ?>
                        <!-- Title Left, Cards Right (3 columns: 1 for text, 2 for cards) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="md:col-span-1">
                                <h2 class="text-2xl font-bold text-gray-800 mb-3">
                                    <?php if (!empty($tenantGroup['tenant_slug']) && $tenantGroup['tenant_slug'] !== 'unknown'): ?>
                                        <a href="https://<?= esc($tenantGroup['tenant_slug']) ?>.<?= env('app.baseDomain', 'urunankita.test') ?>" class="hover:text-primary-600">
                                            <?= esc($tenantGroup['tenant_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= esc($tenantGroup['tenant_name']) ?>
                                    <?php endif; ?>
                                </h2>
                                <p class="text-sm text-gray-600 mb-2"><?= count($tenantGroup['campaigns']) ?> urunan aktif</p>
                                <?php if (!empty($tenantGroup['tenant_description'])): ?>
                                    <p class="text-sm text-gray-500"><?= esc($tenantGroup['tenant_description']) ?></p>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">Deskripsi Urunan <?= esc($tenantGroup['tenant_name']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach (array_slice($tenantGroup['campaigns'], 0, 2) as $campaign): ?>
                                    <?php
                                    // Generate campaign URL
                                    if ($isPlatform || $tenantGroup['tenant_slug'] === 'platform') {
                                        $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                    } else {
                                        if (!empty($tenantGroup['tenant_slug']) && $tenantGroup['tenant_slug'] !== 'unknown') {
                                            $baseDomain = env('app.baseDomain', 'urunankita.test');
                                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                            $campaignUrl = $scheme . '://' . $tenantGroup['tenant_slug'] . '.' . $baseDomain . '/campaign/' . esc($campaign['slug']);
                                        } else {
                                            $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                        }
                                    }
                                    ?>
                                    <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                                        <?php
                                        $img = $campaign['featured_image'] ?? '';
                                        if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                            $img = '/uploads/' . ltrim($img, '/');
                                        }
                                        ?>
                                        <a href="<?= esc($campaignUrl) ?>">
                                            <div class="h-40 <?= !empty($img) ? '' : 'bg-gradient-to-br from-gray-100 to-gray-200' ?>" <?= !empty($img) ? 'style="background-image: url(\'' . esc(base_url(ltrim($img, '/'))) . '\'); background-size: cover; background-position: center;"' : '' ?>>
                                                <?php if (empty($img)): ?>
                                                    <div class="h-full flex items-center justify-center">
                                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <div class="p-4">
                                            <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                                <a href="<?= esc($campaignUrl) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc(substr($campaign['description'] ?? '', 0, 80)) ?><?= strlen($campaign['description'] ?? '') > 80 ? '...' : '' ?></p>

                                            <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                                                <div class="mb-3">
                                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                        <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                                    </div>
                                                    <div class="flex justify-between items-center text-xs text-gray-600">
                                                        <span>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></span>
                                                        <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-3">
                                                    <div class="text-sm font-semibold text-gray-900">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?= esc($campaignUrl) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Cards Left, Title Right (3 columns: 2 for cards, 1 for text) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach (array_slice($tenantGroup['campaigns'], 0, 2) as $campaign): ?>
                                    <?php
                                    // Generate campaign URL
                                    if ($isPlatform || $tenantGroup['tenant_slug'] === 'platform') {
                                        $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                    } else {
                                        if (!empty($tenantGroup['tenant_slug']) && $tenantGroup['tenant_slug'] !== 'unknown') {
                                            $baseDomain = env('app.baseDomain', 'urunankita.test');
                                            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                            $campaignUrl = $scheme . '://' . $tenantGroup['tenant_slug'] . '.' . $baseDomain . '/campaign/' . esc($campaign['slug']);
                                        } else {
                                            $campaignUrl = '/campaign/' . esc($campaign['slug']);
                                        }
                                    }
                                    ?>
                                    <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                                        <?php
                                        $img = $campaign['featured_image'] ?? '';
                                        if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                            $img = '/uploads/' . ltrim($img, '/');
                                        }
                                        ?>
                                        <a href="<?= esc($campaignUrl) ?>">
                                            <div class="h-40 <?= !empty($img) ? '' : 'bg-gradient-to-br from-gray-100 to-gray-200' ?>" <?= !empty($img) ? 'style="background-image: url(\'' . esc(base_url(ltrim($img, '/'))) . '\'); background-size: cover; background-position: center;"' : '' ?>>
                                                <?php if (empty($img)): ?>
                                                    <div class="h-full flex items-center justify-center">
                                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <div class="p-4">
                                            <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                                <a href="<?= esc($campaignUrl) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc(substr($campaign['description'] ?? '', 0, 80)) ?><?= strlen($campaign['description'] ?? '') > 80 ? '...' : '' ?></p>

                                            <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                                                <div class="mb-3">
                                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                        <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                                    </div>
                                                    <div class="flex justify-between items-center text-xs text-gray-600">
                                                        <span>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></span>
                                                        <span><?= round($campaign['progress_percentage'], 1) ?>%</span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-3">
                                                    <div class="text-sm font-semibold text-gray-900">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <a href="<?= esc($campaignUrl) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="md:col-span-1">
                                <h2 class="text-2xl font-bold text-gray-800 mb-3 <?= $isFirstAfterPlatform ? 'md:text-left' : '' ?>">
                                    <?php if (!empty($tenantGroup['tenant_slug']) && $tenantGroup['tenant_slug'] !== 'unknown'): ?>
                                        <a href="https://<?= esc($tenantGroup['tenant_slug']) ?>.<?= env('app.baseDomain', 'urunankita.test') ?>" class="hover:text-primary-600">
                                            <?= esc($tenantGroup['tenant_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= esc($tenantGroup['tenant_name']) ?>
                                    <?php endif; ?>
                                </h2>
                                <p class="text-sm text-gray-600 mb-2 <?= $isFirstAfterPlatform ? 'md:text-left' : '' ?>"><?= count($tenantGroup['campaigns']) ?> urunan aktif</p>
                                <?php if (!empty($tenantGroup['tenant_description'])): ?>
                                    <p class="text-sm text-gray-500 <?= $isFirstAfterPlatform ? 'md:text-left' : '' ?>"><?= esc($tenantGroup['tenant_description']) ?></p>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500 <?= $isFirstAfterPlatform ? 'md:text-left' : '' ?>">Deskripsi Urunan <?= esc($tenantGroup['tenant_name']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($articles)): ?>
        <!-- Kabar Terbaru -->
        <section class="py-12 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Kabar Terbaru</h2>
                    <p class="text-gray-600 text-sm">Kabar terbaru dari kami</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach (array_slice($articles, 0, 3) as $article): ?>
                        <div class="article-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                            <a href="/article/<?= esc($article['slug']) ?>">
                                <?php if (!empty($article['image'])): ?>
                                    <?php
                                    $img = $article['image'];
                                    if (!preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                        $img = '/uploads/' . ltrim($img, '/');
                                    }
                                    ?>
                                    <div class="h-40 bg-gray-200" style="background-image: url('<?= esc(base_url(ltrim($img, '/'))) ?>'); background-size: cover; background-position: center;"></div>
                                <?php else: ?>
                                    <div class="h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="p-4">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="inline-block bg-primary-100 text-primary-700 text-xs font-semibold px-2 py-1 rounded-full mb-2"><?= esc($article['category']) ?></span>
                                <?php endif; ?>
                                <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <a href="/article/<?= esc($article['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($article['title']) ?></a>
                                </h3>
                                <?php if (!empty($article['excerpt'])): ?>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc($article['excerpt']) ?></p>
                                <?php elseif (!empty($article['content'])): ?>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= esc(substr(strip_tags($article['content']), 0, 80)) ?><?= strlen(strip_tags($article['content'])) > 80 ? '...' : '' ?></p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                                    <span class="font-medium"><?= esc($article['author_name'] ?? 'Admin') ?></span>
                                    <span><?= date('d M Y', strtotime($article['created_at'] ?? 'now')) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($platform_stats)): ?>
        <!-- Statistik Platform -->
        <section class="py-12 bg-green-50">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Statistik </h2>
                    <p class="text-gray-600 text-sm">Dampak kebaikan yang telah kita ciptakan bersama</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                        <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($platform_stats['total_campaigns'] ?? 0) ?></div>
                        <div class="text-sm text-gray-600">Urunan Aktif</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                        <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($platform_stats['total_donors'] ?? 0) ?></div>
                        <div class="text-sm text-gray-600">Orang Baik</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                        <div class="text-3xl font-bold text-[#055b16] mb-2">Rp <?= number_format($platform_stats['total_donations'] ?? 0, 0, ',', '.') ?></div>
                        <div class="text-sm text-gray-600">Total Urunan</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                        <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($platform_stats['total_tenants'] ?? 0) ?></div>
                        <div class="text-sm text-gray-600">Mitra</div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($recent_donations)): ?>
        <!-- Daftar Donatur Terbaru -->
        <section class="py-12 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Orang Baik</h2>
                    <p class="text-gray-600 text-sm">Terima kasih untuk semua yang telah berbagi kebaikan</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        <?php foreach (array_slice($recent_donations, 0, 10) as $donation): ?>
                            <?php
                            // Generate campaign URL
                            $campaignUrl = '/campaign/' . esc($donation['campaign_slug'] ?? '');
                            if (!empty($donation['tenant_slug']) && $donation['tenant_slug'] !== 'platform') {
                                $baseDomain = env('app.baseDomain', 'urunankita.test');
                                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                $campaignUrl = $scheme . '://' . $donation['tenant_slug'] . '.' . $baseDomain . '/campaign/' . esc($donation['campaign_slug'] ?? '');
                            }
                            ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#7cb982] flex items-center justify-center text-white font-semibold">
                                            <?= strtoupper(substr($donation['donor_name'] ?? 'A', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= esc($donation['donor_name'] ?? 'Orang Baik') ?></div>
                                            <div class="text-sm text-gray-500">
                                                <a href="<?= esc($campaignUrl) ?>" class="hover:text-[#055b16]"><?= esc($donation['campaign_title'] ?? 'Urunan') ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-[#055b16]">Rp <?= number_format($donation['amount'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="text-xs text-gray-500"><?= date('d M Y', strtotime($donation['paid_at'] ?? 'now')) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php else: ?>
    <!-- Tenant Subdomain Homepage -->
    <?php if (!empty($banners)): ?>
        <!-- Banner Slider -->
        <section class="relative overflow-hidden">
            <div class="banner-slider" style="height: 400px;">
                <?php foreach ($banners as $banner): ?>
                    <?php
                    $imageUrl = $banner['image'];
                    if (!preg_match('~^https?://~', $imageUrl) && strpos($imageUrl, '/uploads/') !== 0) {
                        $imageUrl = '/uploads/' . ltrim($imageUrl, '/');
                    }
                    ?>
                    <div class="banner-slide relative w-full h-full" style="background-image: url('<?= esc(base_url(ltrim($imageUrl, '/'))) ?>'); background-size: cover; background-position: center;">
                        <?php if (!empty($banner['link'])): ?>
                            <a href="<?= esc($banner['link']) ?>" class="block w-full h-full">
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                                <div class="text-center text-white px-4">
                                    <?php if (!empty($banner['title'])): ?>
                                        <h2 class="text-3xl md:text-4xl font-bold mb-4"><?= esc($banner['title']) ?></h2>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($banner['link'])): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Hero Section (Fallback if no banners) -->
        <section class="hero-bg text-white py-20 md:py-28">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Mari Berbagi Kebaikan</h1>
                <p class="text-xl max-w-2xl mx-auto mb-8">Platform crowdfunding untuk membantu sesama. Bersama-sama kita bisa membuat perubahan yang berarti.</p>
                <a href="<?= base_url('/campaigns') ?>" class="inline-block bg-secondary-500 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-secondary-600 transition-colors shadow-lg">Lihat Urunan</a>
            </div>
        </section>
    <?php endif; ?>

    <?php if (isset($tenant)): ?>
        <!-- Tenant Info Banner -->
        <section class="bg-white border-b border-gray-200 py-6">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?= esc($tenant['name'] ?? 'Urunan') ?></h2>
                        <?php if (!empty($tenant['description'])): ?>
                            <p class="text-gray-600 mt-1"><?= esc(substr($tenant['description'], 0, 150)) ?><?= strlen($tenant['description']) > 150 ? '...' : '' ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Semua Urunan -->
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Urunan Kami</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Urunan yang sedang kami galang</p>
            </div>

            <?php if (empty($campaigns)): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">Belum ada urunan aktif</p>
                        <p class="text-gray-400 text-sm mt-2">Kami akan segera menampilkan urunan yang membutuhkan bantuan Anda</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="campaign-card bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                            <?php
                            $img = $campaign['featured_image'] ?? '';
                            if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                $img = '/uploads/' . ltrim($img, '/');
                            }
                            ?>
                            <a href="/campaign/<?= esc($campaign['slug']) ?>">
                                <div class="h-48 <?= !empty($img) ? '' : 'bg-gradient-to-br from-gray-100 to-gray-200' ?>" <?= !empty($img) ? 'style="background-image: url(\'' . esc(base_url(ltrim($img, '/'))) . '\'); background-size: cover; background-position: center;"' : '' ?>>
                                    <?php if (empty($img)): ?>
                                        <div class="h-full flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <a href="/campaign/<?= esc($campaign['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
                                </h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= esc(substr($campaign['description'] ?? '', 0, 100)) ?><?= strlen($campaign['description'] ?? '') > 100 ? '...' : '' ?></p>

                                <?php if (isset($campaign['progress_percentage']) && $campaign['campaign_type'] === 'target_based'): ?>
                                    <div class="mb-4">
                                        <div class="flex justify-between text-xs text-gray-700 mb-2">
                                            <span class="font-medium">Terkumpul</span>
                                            <span class="font-medium">Target</span>
                                        </div>
                                        <div class="flex justify-between text-sm font-semibold text-gray-900 mb-2">
                                            <span>Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></span>
                                            <span>Rp <?= number_format($campaign['target_amount'] ?? 0, 0, ',', '.') ?></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                            <div class="bg-primary-600 h-2.5 rounded-full progress-bar transition-all duration-500" style="width: <?= min(100, $campaign['progress_percentage']) ?>%"></div>
                                        </div>
                                        <div class="flex justify-between items-center text-xs text-gray-500">
                                            <span><?= round($campaign['progress_percentage'], 1) ?>% tercapai</span>
                                            <span><?= date('d M Y', strtotime($campaign['created_at'] ?? 'now')) ?></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-4">
                                        <div class="text-xs text-gray-500 mb-1">Terkumpul</div>
                                        <div class="text-lg font-semibold text-gray-900 mb-2">Rp <?= number_format($campaign['current_amount'] ?? 0, 0, ',', '.') ?></div>
                                        <div class="text-xs text-gray-500">
                                            <span><?= date('d M Y', strtotime($campaign['created_at'] ?? 'now')) ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <a href="/campaign/<?= esc($campaign['slug']) ?>" class="block w-full bg-primary-600 text-white py-2.5 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-sm">Lihat Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($campaigns) >= 6): ?>
                    <div class="text-center mt-12">
                        <a href="<?= base_url('/campaigns') ?>" class="inline-flex items-center gap-2 border-2 border-primary-600 text-primary-600 px-8 py-3 rounded-lg font-medium hover:bg-primary-50 transition-colors">
                            Lihat Semua Urunan
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Mobile menu is now handled in header.php

    // Banner Slider (Simple auto-slide with fade effect)
    <?php if (!empty($banners) && count($banners) > 1): ?>
        const bannerSlides = document.querySelectorAll('.banner-slide');
        if (bannerSlides.length > 1) {
            let currentSlide = 0;
            bannerSlides[0].style.display = 'block';
            bannerSlides[0].style.opacity = '1';
            for (let i = 1; i < bannerSlides.length; i++) {
                bannerSlides[i].style.display = 'none';
                bannerSlides[i].style.opacity = '0';
            }

            setInterval(function() {
                bannerSlides[currentSlide].style.transition = 'opacity 0.5s ease';
                bannerSlides[currentSlide].style.opacity = '0';

                setTimeout(function() {
                    bannerSlides[currentSlide].style.display = 'none';
                    currentSlide = (currentSlide + 1) % bannerSlides.length;
                    bannerSlides[currentSlide].style.display = 'block';
                    bannerSlides[currentSlide].style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        bannerSlides[currentSlide].style.opacity = '1';
                    }, 10);
                }, 500);
            }, 5000); // Change slide every 5 seconds
        }
    <?php endif; ?>
    });
</script>
<style>
    .banner-slider {
        position: relative;
    }

    .banner-slide {
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .banner-slide:first-child {
        display: block;
        opacity: 1;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }


    .campaign-card {
        transition: all 0.3s ease;
    }

    .campaign-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .article-card {
        transition: all 0.3s ease;
    }

    .article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
</style>
<?= $this->endSection() ?>