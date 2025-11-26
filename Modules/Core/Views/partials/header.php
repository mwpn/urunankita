<?php
$current_page = uri_string();
$authUser = session()->get('auth_user');
$userRole = $authUser['role'] ?? session()->get('user_role') ?? null;
$isAdmin = ($userRole === 'superadmin') || (strpos($current_page, '/admin') === 0);
$userName = $authUser['name'] ?? session()->get('user_name') ?? 'User';
$userRoleText = $userRole === 'superadmin' ? 'Super Admin' : ($authUser['role'] ?? 'Role');
?>
<!-- Topbar -->
<header class="sticky top-0 inset-x-0 flex flex-wrap sm:justify-start sm:flex-nowrap z-[48] w-full bg-white border-b border-gray-200 text-sm py-2 lg:ps-64">
    <nav class="flex items-center w-full mx-auto px-6 sm:px-8" aria-label="Global">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-x-3">
                <button type="button" class="hs-collapse-toggle lg:hidden inline-flex items-center gap-x-2 text-sm font-medium text-gray-500 hover:text-gray-900" data-hs-collapse="#application-sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-x-3">
                <?= $this->renderSection('topbar_actions') ?>
                <!-- User Dropdown -->
                <div class="hs-dropdown relative inline-flex">
                    <button id="hs-dropdown-with-header" type="button" class="hs-dropdown-toggle hs-dropdown-open:text-primary-600 flex items-center gap-x-2 text-sm font-medium rounded-full py-1.5 px-2 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">
                        <img class="inline-block h-9 w-9 rounded-full ring-2 ring-white dark:ring-gray-800" src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=4CAF50&color=ffffff" alt="Image Description">
                        <span class="hidden sm:flex flex-col text-left">
                            <span class="text-gray-700 font-medium text-sm"><?= esc($userName) ?></span>
                            <span class="text-xs text-gray-400"><?= esc($userRoleText) ?></span>
                        </span>
                        <svg class="hs-dropdown-open:rotate-180 w-4 h-4 text-gray-600 hidden sm:block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-blue-900 border border-blue-800 shadow-md rounded-lg p-2 mt-2" aria-labelledby="hs-dropdown-with-header">
                        <div class="py-3 px-5 -m-2 bg-blue-800 rounded-t-lg">
                            <p class="text-sm text-blue-200">Signed in as</p>
                            <p class="text-sm font-medium text-white"><?= esc($userName) ?></p>
                        </div>
                        <div class="mt-2 py-2 first:pt-0 last:pb-0">
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-blue-100 hover:bg-blue-800 hover:text-white focus:ring-2 focus:ring-blue-500" href="#">
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                Pengaturan
                            </a>
                            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-blue-100 hover:bg-blue-800 hover:text-white focus:ring-2 focus:ring-blue-500" href="<?= site_url('logout') ?>">
                                <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" x2="9" y1="12" y2="12" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>