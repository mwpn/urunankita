<?php
// Get user role and determine if admin
$authUser = session()->get('auth_user') ?? [];
$userRole = $userRole ?? $authUser['role'] ?? null;
$isAdmin = $isAdmin ?? in_array($userRole, ['superadmin', 'super_admin', 'admin']);
$isLoggedIn = !empty($authUser);

// Load MenuItemModel for header menu
$menuModel = new \Modules\Content\Models\MenuItemModel();
?>
<header class="sticky top-0 z-50 shadow-sm" style="background-color:#7db173;">
    <div class="max-w-6xl mx-auto px-4">
        <nav class="flex items-center py-4">
            <a href="<?= ($is_main_domain ?? true) ? base_url('/') : '/' ?>" class="flex items-center space-x-3 flex-shrink-0">
                <?php if (!empty($settings['site_logo'])): ?>
                    <?php
                    $logoUrl = $settings['site_logo'];
                    $logoSrc = preg_match('~^https?://~', $logoUrl) ? $logoUrl : base_url(ltrim($logoUrl, '/'));
                    ?>
                    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($settings['site_name'] ?? 'UrunanKita') ?>" class="h-10 w-auto">
                <?php endif; ?>

                <?php
                $siteName = esc($settings['site_name'] ?? 'UrunanKita');
                $pos = preg_match('/[A-Z]/', substr($siteName, 1), $match, PREG_OFFSET_CAPTURE)
                    ? $match[0][1] + 1
                    : false;
                if ($pos) {
                    $part1 = substr($siteName, 0, $pos);
                    $part2 = substr($siteName, $pos);
                } else {
                    $part1 = $siteName;
                    $part2 = '';
                }
                ?>
                <span class="text-2xl font-bold">
                    <span class="text-black"><?= $part1 ?></span><span class="text-white"><?= $part2 ?></span>
                </span>
            </a>

            <div class="hidden md:flex space-x-6 items-center ml-auto">
                <?php
                // Get menu items from database
                // Determine tenant_id for menu
                // Jika di main domain (is_main_domain = true), gunakan null (platform menu)
                // Jika di subdomain tenant (is_main_domain = false), gunakan tenant_id dari session
                $tenantIdForMenu = null;
                $isMainDomain = $is_main_domain ?? true; // Default true jika tidak di-set

                if (!$isMainDomain) {
                    // Subdomain tenant - gunakan tenant_id dari session
                    $tenantIdForMenu = session()->get('tenant_id');
                }
                // else: Main domain - gunakan null untuk platform menu

                // Get menu items dengan struktur hierarki (null untuk platform, tenant_id untuk tenant)
                $headerMenuItems = $menuModel->getMenuItemsHierarchical($tenantIdForMenu);

                // Fallback ke default jika belum ada menu
                if (empty($headerMenuItems)) {
                    $defaultItems = $menuModel->getDefaultMenuItems();
                    // Convert default items to hierarchical structure
                    $headerMenuItems = [];
                    foreach ($defaultItems as $item) {
                        $item['children'] = [];
                        $headerMenuItems[] = $item;
                    }
                }

                // Helper function untuk generate URL dengan subdomain
                $generateMenuUrl = function ($url, $isExternal, $isMainDomain) {
                    // Jika external (bukan ke domain kita), return as is
                    if ($isExternal) {
                        return $url;
                    }

                    // Jika URL adalah full URL (http/https), cek apakah ke main domain
                    if (preg_match('~^https?://~', $url)) {
                        // Extract path dari URL
                        $parsedUrl = parse_url($url);
                        $path = $parsedUrl['path'] ?? '/';

                        // Jika di subdomain, gunakan path relatif saja
                        if (!$isMainDomain) {
                            return $path;
                        }

                        // Jika di main domain, return URL as is
                        return $url;
                    }

                    // Jika main domain, gunakan base_url biasa
                    if ($isMainDomain) {
                        return base_url(ltrim($url, '/'));
                    }

                    // Jika subdomain tenant, gunakan path relatif (tetap di subdomain)
                    // Path relatif akan otomatis menggunakan subdomain yang sama
                    // Pastikan path dimulai dengan /
                    $cleanUrl = ltrim($url, '/');
                    return '/' . $cleanUrl;
                };

                // Display menu items (with submenu support)
                foreach ($headerMenuItems as $menuItem):
                    // Skip jika tidak aktif (kecuali default menu yang tidak punya is_active)
                    if (isset($menuItem['is_active']) && empty($menuItem['is_active'])) {
                        continue;
                    }

                    $hasChildren = !empty($menuItem['children']) && count($menuItem['children']) > 0;
                    $menuUrl = $generateMenuUrl(
                        $menuItem['url'],
                        !empty($menuItem['is_external']),
                        $isMainDomain
                    );

                    if ($hasChildren):
                ?>
                        <div class="relative group">
                            <a href="<?= esc($menuUrl) ?>"
                                class="text-base font-medium text-black hover:text-gray-700 transition-colors flex items-center gap-1"
                                <?= !empty($menuItem['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                <?= esc($menuItem['label']) ?>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </a>
                            <div class="absolute top-full left-0 mt-2 bg-white rounded-lg shadow-lg py-2 min-w-[200px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <?php foreach ($menuItem['children'] as $child):
                                    if (isset($child['is_active']) && empty($child['is_active'])) {
                                        continue;
                                    }
                                    $childUrl = $generateMenuUrl(
                                        $child['url'],
                                        !empty($child['is_external']),
                                        $isMainDomain
                                    );
                                ?>
                                    <a href="<?= esc($childUrl) ?>"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-primary-600 transition-colors"
                                        <?= !empty($child['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                        <?= esc($child['label']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= esc($menuUrl) ?>"
                            class="text-base font-medium text-black hover:text-gray-700 transition-colors"
                            <?= !empty($menuItem['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                            <?= esc($menuItem['label']) ?>
                        </a>
                <?php endif;
                endforeach; ?>
            </div>

            <div class="flex items-center space-x-4 ml-auto md:ml-0">
                <button class="md:hidden text-black focus:outline-none" id="mobileMenuBtn" aria-label="Toggle menu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenu" class="hidden fixed inset-0 z-50 md:hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" id="mobileMenuBackdrop"></div>

        <!-- Menu Panel -->
        <div class="absolute right-0 top-0 h-full w-80 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out" id="mobileMenuPanel">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-[#7cb982]">
                    <span class="text-lg font-bold text-black">Menu</span>
                    <button id="mobileMenuClose" class="text-black hover:text-gray-700 focus:outline-none" aria-label="Close menu">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Menu Items -->
                <div class="flex-1 overflow-y-auto py-4">
                    <nav class="space-y-1">
                        <?php
                        // Reuse the same menu items logic from desktop menu
                        // Get menu items dengan struktur hierarki
                        $tenantIdForMenu = null;
                        $isMainDomain = $is_main_domain ?? true;

                        if (!$isMainDomain) {
                            $tenantIdForMenu = session()->get('tenant_id');
                        }

                        $headerMenuItems = $menuModel->getMenuItemsHierarchical($tenantIdForMenu);

                        if (empty($headerMenuItems)) {
                            $defaultItems = $menuModel->getDefaultMenuItems();
                            $headerMenuItems = [];
                            foreach ($defaultItems as $item) {
                                $item['children'] = [];
                                $headerMenuItems[] = $item;
                            }
                        }

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

                        foreach ($headerMenuItems as $menuItem):
                            if (isset($menuItem['is_active']) && empty($menuItem['is_active'])) {
                                continue;
                            }

                            $hasChildren = !empty($menuItem['children']) && count($menuItem['children']) > 0;
                            $menuUrl = $generateMenuUrl(
                                $menuItem['url'],
                                !empty($menuItem['is_external']),
                                $isMainDomain
                            );

                            if ($hasChildren):
                        ?>
                                <div class="mobile-menu-item">
                                    <button class="w-full flex items-center justify-between px-4 py-3 text-left text-gray-700 hover:bg-gray-100 transition-colors mobile-menu-toggle" data-target="submenu-<?= $menuItem['id'] ?? uniqid() ?>">
                                        <span class="font-medium"><?= esc($menuItem['label']) ?></span>
                                        <i class="fas fa-chevron-down text-xs transition-transform"></i>
                                    </button>
                                    <div class="hidden mobile-submenu" id="submenu-<?= $menuItem['id'] ?? uniqid() ?>">
                                        <?php foreach ($menuItem['children'] as $child):
                                            if (isset($child['is_active']) && empty($child['is_active'])) {
                                                continue;
                                            }
                                            $childUrl = $generateMenuUrl(
                                                $child['url'],
                                                !empty($child['is_external']),
                                                $isMainDomain
                                            );
                                        ?>
                                            <a href="<?= esc($childUrl) ?>"
                                                class="block px-8 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-[#7cb982] transition-colors"
                                                <?= !empty($child['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                                <?= esc($child['label']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="<?= esc($menuUrl) ?>"
                                    class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors font-medium"
                                    <?= !empty($menuItem['is_external']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <?= esc($menuItem['label']) ?>
                                </a>
                        <?php endif;
                        endforeach; ?>
                    </nav>

                </div>
            </div>
        </div>
    </div>
</header>

<style>
    #mobileMenu.active #mobileMenuPanel {
        transform: translateX(0);
    }

    .mobile-menu-toggle.active i {
        transform: rotate(180deg);
    }

    .mobile-submenu.active {
        display: block !important;
    }
</style>

<script>
    // Mobile menu functionality - wait for DOM ready
    (function() {
        function initMobileMenu() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const mobileMenuBackdrop = document.getElementById('mobileMenuBackdrop');
            const mobileMenuPanel = document.getElementById('mobileMenuPanel');

            if (!mobileMenu || !mobileMenuBtn) {
                // Retry if elements not found yet
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initMobileMenu);
                }
                return;
            }

            function openMobileMenu() {
                if (!mobileMenu) return;
                mobileMenu.classList.remove('hidden');
                // Trigger reflow to ensure transition works
                void mobileMenu.offsetHeight;
                mobileMenu.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileMenu() {
                if (!mobileMenu) return;
                mobileMenu.classList.remove('active');
                setTimeout(() => {
                    if (mobileMenu) {
                        mobileMenu.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                }, 300);
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', openMobileMenu);
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', closeMobileMenu);
            }

            if (mobileMenuBackdrop) {
                mobileMenuBackdrop.addEventListener('click', closeMobileMenu);
            }

            // Handle submenu toggles
            const menuToggles = document.querySelectorAll('.mobile-menu-toggle');
            menuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-target');
                    const submenu = document.getElementById(targetId);

                    if (submenu) {
                        const isActive = submenu.classList.contains('active');

                        // Close all other submenus
                        document.querySelectorAll('.mobile-submenu').forEach(menu => {
                            menu.classList.remove('active');
                        });
                        document.querySelectorAll('.mobile-menu-toggle').forEach(btn => {
                            btn.classList.remove('active');
                        });

                        // Toggle current submenu
                        if (!isActive) {
                            submenu.classList.add('active');
                            this.classList.add('active');
                        }
                    }
                });
            });

            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    closeMobileMenu();
                }
            });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMobileMenu);
        } else {
            initMobileMenu();
        }
    })();
</script>