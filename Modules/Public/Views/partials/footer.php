<footer class="bg-gray-900 text-gray-300 pt-14 pb-8 text-sm md:text-base">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">

            <!-- Brand & Info -->
            <div>
                <div class="flex items-center space-x-3 mb-4">
                    <!-- Logo -->


                    <div class="text-base font-semibold text-white"><?= esc($settings['site_name'] ?? 'UrunanKita') ?></div>
                </div>

                <p class="text-sm leading-relaxed mb-5">
                    <?= esc($settings['site_description'] ?? 'Platform crowdfunding terpercaya untuk membantu mereka yang membutuhkan. Bersama-sama kita bisa membuat perubahan yang berarti.') ?>
                </p>

                <div class="space-y-1 text-sm">
                    <?php if (!empty($settings['site_address'])): ?>
                        <p><i class="fas fa-map-marker-alt mr-2"></i><?= esc($settings['site_address']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($settings['site_phone'])): ?>
                        <p><i class="fas fa-phone mr-2"></i><?= esc($settings['site_phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($settings['site_email'])): ?>
                        <p><i class="fas fa-envelope mr-2"></i><?= esc($settings['site_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            // Get footer menu items
            $menuModel = new \Modules\Content\Models\MenuItemModel();
            $tenantIdForMenu = null;
            $isMainDomain = $is_main_domain ?? true;

            if (!$isMainDomain) {
                $tenantIdForMenu = session()->get('tenant_id');
            }

            // Get footer menu items (hierarchical)
            $footerMenuItems = $menuModel->getMenuItemsHierarchical($tenantIdForMenu, true, 'footer');

            // Helper function untuk generate URL
            $generateMenuUrl = function ($url, $isExternal, $isMainDomain) {
                if ($isExternal) {
                    return $url;
                }

                if (preg_match('~^https?://~', $url)) {
                    $parsedUrl = parse_url($url);
                    $path = $parsedUrl['path'] ?? '/';

                    if (!$isMainDomain) {
                        return $path;
                    }

                    return $url;
                }

                if ($isMainDomain) {
                    return base_url(ltrim($url, '/'));
                }

                $cleanUrl = ltrim($url, '/');
                return '/' . $cleanUrl;
            };

            // Group menu items by parent (for footer columns)
            $menuColumns = [];
            foreach ($footerMenuItems as $menuItem) {
                if (isset($menuItem['is_active']) && empty($menuItem['is_active'])) {
                    continue;
                }
                $menuColumns[] = $menuItem;
            }

            // Display footer menu columns (max 3 columns)
            $maxColumns = 3;
            $columnsToShow = array_slice($menuColumns, 0, $maxColumns);

            if (!empty($columnsToShow)):
                foreach ($columnsToShow as $column):
                    $columnTitle = $column['label'] ?? 'Menu';
            ?>
                    <div>
                        <h3 class="text-white text-base font-semibold mb-4"><?= esc($columnTitle) ?></h3>
                        <ul class="space-y-2 text-sm">
                            <?php if (!empty($column['children'])): ?>
                                <?php foreach ($column['children'] as $child): ?>
                                    <?php if (isset($child['is_active']) && empty($child['is_active'])) continue; ?>
                                    <?php
                                    $childUrl = $generateMenuUrl(
                                        $child['url'],
                                        !empty($child['is_external']),
                                        $isMainDomain
                                    );
                                    ?>
                                    <li>
                                        <a href="<?= esc($childUrl) ?>"
                                            class="hover:text-white transition-colors"
                                            <?= !empty($child['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                            <?= esc($child['label']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                $columnUrl = $generateMenuUrl(
                                    $column['url'],
                                    !empty($column['is_external']),
                                    $isMainDomain
                                );
                                ?>
                                <li>
                                    <a href="<?= esc($columnUrl) ?>"
                                        class="hover:text-white transition-colors"
                                        <?= !empty($column['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                        <?= esc($column['label']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
            <?php
                endforeach;
            endif;
            ?>

            <!-- Social -->
            <div>
                <h3 class="text-white text-base font-semibold mb-4">Tetap Terhubung</h3>
                <p class="text-sm mb-4 leading-relaxed">Ikuti kami di media sosial untuk update terbaru.</p>
                <div class="flex space-x-3">
                    <?php if (!empty($settings['site_facebook'])): ?>
                        <a href="<?= esc($settings['site_facebook']) ?>" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary-600 transition-colors duration-200">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['site_twitter'])): ?>
                        <a href="<?= esc($settings['site_twitter']) ?>" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary-600 transition-colors duration-200">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['site_instagram'])): ?>
                        <a href="<?= esc($settings['site_instagram']) ?>" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary-600 transition-colors duration-200">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-800 text-center text-sm text-gray-500">
            <p>© <?= date('Y') ?> <?= esc($settings['site_name'] ?? 'UrunanKita') ?>. All rights reserved.</p>
        </div>
    </div>
</footer>