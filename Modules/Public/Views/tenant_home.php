<?= $this->extend('Modules\Public\Views\layout') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hero Section -->
<section class="relative py-8 md:py-12 overflow-hidden" style="background-color:#7db173;">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="px-4">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 text-white"><?= esc($tenant['name'] ?? 'Mari Berbagi Kebaikan') ?></h1>
                <p class="text-base mb-8 text-black"><?= !empty($tenant['description']) ? esc(substr($tenant['description'], 0, 150)) . '...' : 'Platform crowdfunding untuk membantu sesama. Bersama-sama kita bisa membuat perubahan yang berarti.' ?></p>
                <a href="<?= base_url('/campaigns') ?>"
                    class="inline-block px-5 py-1.5 rounded-md font-semibold text-sm hover:opacity-90 transition-colors shadow-md text-white bg-[#055b16]">
                    Lihat Urunan
                </a>
            </div>
            <div class="py-0">
                <?php
                // Get hero image from tenant settings
                $heroImage = $settings['hero_image'] ?? null;
                if ($heroImage && !preg_match('~^https?://~', $heroImage)) {
                    if (strpos($heroImage, '/uploads/') !== 0) {
                        $heroImage = '/uploads/' . ltrim($heroImage, '/');
                    }
                    $heroImage = base_url(ltrim($heroImage, '/'));
                } elseif (!$heroImage) {
                    // Fallback to default hero image
                    $heroImage = base_url('assets/images/hero-image.png');
                }
                ?>
                <img src="<?= esc($heroImage) ?>" alt="Hero" class="w-full h-auto" onerror="this.style.display='none';">
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
            <!-- Title Left, Cards Right (3 columns: 1 for text, 2 for cards) - -->
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
                        <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                            <?php
                            $img = $campaign['featured_image'] ?? '';
                            if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                                $img = '/uploads/' . ltrim($img, '/');
                            }
                            ?>
                            <a href="/campaign/<?= esc($campaign['slug']) ?>">
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
                                    <a href="/campaign/<?= esc($campaign['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
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

                                <a href="/campaign/<?= esc($campaign['slug']) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($campaigns)): ?>
    <!-- Semua Urunan -->
    <section class="py-12 bg-green-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-3"><?= esc($tenant['name'] ?? 'Urunan') ?></h2>
                <p class="text-sm text-gray-600 mb-2"><?= count($campaigns) ?> urunan aktif</p>
                <?php if (!empty($tenant['description'])): ?>
                    <p class="text-sm text-gray-500"><?= esc($tenant['description']) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Urunan yang sedang kami galang</p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach (array_slice($campaigns, 0, 3) as $campaign): ?>
                    <div class="campaign-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200 hover:border-primary-300">
                        <?php
                        $img = $campaign['featured_image'] ?? '';
                        if ($img && !preg_match('~^https?://~', $img) && strpos($img, '/uploads/') !== 0) {
                            $img = '/uploads/' . ltrim($img, '/');
                        }
                        ?>
                        <a href="/campaign/<?= esc($campaign['slug']) ?>">
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
                                <a href="/campaign/<?= esc($campaign['slug']) ?>" class="hover:text-primary-600 transition-colors"><?= esc($campaign['title']) ?></a>
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

                            <a href="/campaign/<?= esc($campaign['slug']) ?>" class="block w-full bg-primary-600 text-white py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-center text-xs">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($tenant_stats)): ?>
    <!-- Statistik Tenant -->
    <section class="py-12 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Statistik</h2>
                <p class="text-gray-600 text-sm">Dampak kebaikan yang telah kita ciptakan bersama</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                    <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($tenant_stats['active_campaigns'] ?? 0) ?></div>
                    <div class="text-sm text-gray-600">Urunan Aktif</div>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                    <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($tenant_stats['total_donors'] ?? 0) ?></div>
                    <div class="text-sm text-gray-600">Orang Baik</div>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                    <div class="text-3xl font-bold text-[#055b16] mb-2">Rp <?= number_format($tenant_stats['total_donations'] ?? 0, 0, ',', '.') ?></div>
                    <div class="text-sm text-gray-600">Total Urunan</div>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center border border-green-100">
                    <div class="text-3xl font-bold text-[#055b16] mb-2"><?= number_format($tenant_stats['completed_campaigns'] ?? 0) ?></div>
                    <div class="text-sm text-gray-600">Urunan Selesai</div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($recent_donations)): ?>
    <!-- Daftar Donatur Terbaru -->
    <section class="py-12 bg-green-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Orang Baik</h2>
                <p class="text-gray-600 text-sm">Terima kasih untuk semua yang telah berbagi kebaikan</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-200">
                    <?php foreach (array_slice($recent_donations, 0, 10) as $donation): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#7cb982] flex items-center justify-center text-white font-semibold">
                                        <?= strtoupper(substr($donation['donor_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900"><?= esc($donation['donor_name'] ?? 'Orang Baik') ?></div>
                                        <div class="text-sm text-gray-500">
                                            <a href="/campaign/<?= esc($donation['campaign_slug'] ?? '') ?>" class="hover:text-[#055b16]"><?= esc($donation['campaign_title'] ?? 'Urunan') ?></a>
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

<?= $this->endSection() ?>