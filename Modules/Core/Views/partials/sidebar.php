<?php
$current_page = uri_string();
$authUser = session()->get('auth_user');
$userRole = $authUser['role'] ?? session()->get('user_role') ?? session()->get('role') ?? null;
// Deteksi admin dari role atau dari URL
$isAdmin = (in_array($userRole, ['super_admin', 'superadmin'], true)) || (strpos($current_page, '/admin') === 0);
$tenantSlug = session()->get('tenant_slug') ?? 'tenant';
$logoUrl = $isAdmin ? '/admin/dashboard' : '/tenant/dashboard';
?>
<!-- Sidebar -->
<div id="application-sidebar" class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-300 transform hidden fixed top-0 start-0 bottom-0 z-[60] w-64 bg-blue-900 border-e border-blue-800 pt-7 pb-10 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-blue-800 [&::-webkit-scrollbar-thumb]:bg-blue-600">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="px-6 pt-4 pb-2">
            <a class="flex-none text-xl font-semibold text-white" href="<?= esc($logoUrl) ?>" aria-label="Brand">
                <?= esc($sidebar_title ?? 'UrunanKita') ?>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="hs-accordion-group p-6 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
            <ul class="space-y-1.5">
                <?php
                if ($isAdmin) {
                    // Admin Menu - sesuai dengan routes yang tersedia
                    $nav_items = [
                        ['title' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'],
                        ['title' => 'Penggalang', 'url' => '/admin/tenants', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>'],
                        ['title' => 'Urunan', 'url' => '/admin/campaigns', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>'],
                        ['title' => 'Donasi', 'url' => '/admin/donations', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>'],
                        ['title' => 'Pengumuman', 'url' => '/admin/announcements', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>'],
                        ['title' => 'Helpdesk', 'url' => '/admin/helpdesk', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                    ];
                } else {
                    // Tenant Menu
                    $nav_items = [
                        ['title' => 'Dashboard', 'url' => "/tenant/dashboard", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'],
                        ['title' => 'Urunan', 'url' => "/tenant/campaigns", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>'],
                        ['title' => 'Donasi', 'url' => "/tenant/donations", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>'],
                        ['title' => 'Laporan', 'url' => "/tenant/reports", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
                        ['title' => 'Pengaturan', 'url' => "/tenant/settings", 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'],
                    ];
                }
                
                foreach ($nav_items as $item):
                    $isActive = strpos($current_page, $item['url']) !== false || ($current_page === '/' && $item['url'] === '/admin/dashboard');
                ?>
                    <li>
                        <a class="<?= $isActive ? 'bg-blue-800 text-white' : 'text-blue-100 hover:bg-blue-800 hover:text-white' ?> flex items-center gap-x-3.5 px-3 py-2 text-sm font-medium rounded-lg transition-colors" href="<?= esc($item['url']) ?>">
                            <?= $item['icon'] ?>
                            <?= esc($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- Footer -->
        <div class="px-6 pt-4 pb-4 border-t border-blue-800 mt-auto">
            <div class="flex items-center gap-x-3">
                <div class="flex-1">
                    <?php 
                    $authUser = session()->get('auth_user');
                    $footerUserName = $authUser['name'] ?? $user_name ?? 'User';
                    $normalizedRole = ($userRole === 'superadmin') ? 'super_admin' : $userRole;
                    $footerUserRole = ($normalizedRole === 'super_admin') ? 'Super Admin' : ($authUser['role'] ?? $user_role ?? 'Admin');
                    ?>
                    <p class="text-sm font-medium text-white"><?= esc($footerUserName) ?></p>
                    <p class="text-xs text-blue-200"><?= esc($footerUserRole) ?></p>
                </div>
                <a href="/logout" class="inline-flex items-center gap-x-2 text-sm text-blue-200 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

