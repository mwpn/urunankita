<?php
// Determine current page and user role
$currentUri = uri_string();

// Get userRole from passed variable (from controller), session, or default
// Priority: 1. Variable from controller, 2. Session auth_user role, 3. Default based on URL
$authUser = session()->get('auth_user') ?? [];
$userRoleRaw = $userRole ?? $authUser['role'] ?? null;

// Normalize role values - determine if admin or tenant
$isAdmin = false;
if (in_array($userRoleRaw, ['superadmin', 'super_admin', 'admin'])) {
    $userRole = 'admin';
    $isAdmin = true;
} elseif (in_array($userRoleRaw, ['tenant_owner', 'tenant_admin', 'tenant_user', 'penggalang_dana'])) {
    $userRole = 'penggalang_dana';
    $isAdmin = false;
} else {
    // Fallback: check URL if role not found
    $isAdmin = (strpos($currentUri, '/admin/') === 0);
    $userRole = $isAdmin ? 'admin' : 'penggalang_dana';
}

$baseUrl = base_url();

// Helper function to check if menu is active
$isActive = function($url) use ($currentUri) {
    return strpos($currentUri, $url) !== false ? 'active' : '';
};

// Get platform settings for logo
$settingService = \Config\Services::setting();
$siteLogo = $settingService->get('site_logo', null, 'global', null);
$siteName = $settingService->get('site_name', 'Urunankita', 'global', null);
?>
<aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
    <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
        <i class="fe fe-x"><span class="sr-only"></span></i>
    </a>
    <nav class="vertnav navbar navbar-light">
        <!-- Logo -->
        <div class="w-100 mb-4 d-flex">
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="<?= $isAdmin ? base_url('admin/dashboard') : base_url('tenant/dashboard') ?>">
                <?php if (!empty($siteLogo)): ?>
                    <?php
                    $logoUrl = preg_match('~^https?://~', $siteLogo) ? $siteLogo : base_url(ltrim($siteLogo, '/'));
                    ?>
                    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($siteName) ?>" class="navbar-brand-img brand-sm" style="max-height: 40px; width: auto;">
                <?php else: ?>
                    <svg version="1.1" id="logo" class="navbar-brand-img brand-sm" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120" xml:space="preserve">
                        <g>
                            <polygon class="st0" points="78,105 15,105 24,87 87,87 	" />
                            <polygon class="st0" points="96,69 33,69 42,51 105,51 	" />
                            <polygon class="st0" points="78,33 15,33 24,15 87,15 	" />
                        </g>
                    </svg>
                <?php endif; ?>
            </a>
        </div>

        <?php
        // Load sidebar.html for both admin and tenant (same structure)
        $sidebarPath = FCPATH . 'template/includes/sidebar.html';
        if (file_exists($sidebarPath)) {
            $sidebarContent = file_get_contents($sidebarPath);
            
            // Extract menu content after logo
            if (preg_match('/<!-- Dashboard - Semua User -->(.*?)<\/nav>/s', $sidebarContent, $matches)) {
                $menuContent = $matches[1];
                
                // Filter menu items based on role
                if ($isAdmin) {
                    // Admin: show items with data-role="admin" or "admin,penggalang_dana"
                    // Remove items with data-role="penggalang_dana" only (tenant-only items)
                    $menuContent = preg_replace('/<p[^>]*data-role="penggalang_dana"[^>]*>.*?<\/p>/s', '', $menuContent);
                    $menuContent = preg_replace('/<ul[^>]*data-role="penggalang_dana"[^>]*>.*?<\/ul>/s', '', $menuContent);
                    // Remove individual <li> items with data-role="penggalang_dana" from shared <ul>
                    $menuContent = preg_replace('/<li[^>]*data-role="penggalang_dana"[^>]*>.*?<\/li>/s', '', $menuContent);
                } else {
                    // Tenant: show items with data-role="penggalang_dana" or "admin,penggalang_dana"
                    // Remove items with data-role="admin" only (admin-only items)
                    $menuContent = preg_replace('/<p[^>]*data-role="admin"[^>]*>.*?<\/p>/s', '', $menuContent);
                    $menuContent = preg_replace('/<ul[^>]*data-role="admin"[^>]*>.*?<\/ul>/s', '', $menuContent);
                    // Remove individual <li> items with data-role="admin" from shared <ul>
                    $menuContent = preg_replace('/<li[^>]*data-role="admin"[^>]*>.*?<\/li>/s', '', $menuContent);
                }
                
                // Remove data-role attributes
                $menuContent = preg_replace('/\s+data-role="[^"]*"/', '', $menuContent);
                
                // Replace dashboard link
                $dashboardUrl = $isAdmin ? base_url('admin/dashboard') : base_url('tenant/dashboard');
                $menuContent = str_replace('href="index.php" data-content="includes/dashboard-content.html"', 'href="' . $dashboardUrl . '"', $menuContent);
                
                // Replace Urunan Kita menu items
                $baseUrl = $isAdmin ? base_url('admin') : base_url('tenant');
                $menuContent = str_replace('href="index.php?page=urunan-create" data-content="includes/urunan-create-content.html"', 'href="' . $baseUrl . '/campaigns/create"', $menuContent);
                $menuContent = str_replace('href="index.php?page=urunan-list" data-content="includes/urunan-list-content.html"', 'href="' . $baseUrl . '/campaigns"', $menuContent);
                $menuContent = str_replace('href="index.php?page=urunan-donasi" data-content="includes/urunan-donasi-content.html"', 'href="' . $baseUrl . '/donations"', $menuContent);
                $menuContent = str_replace('href="index.php?page=urunan-laporan" data-content="includes/urunan-laporan-content.html"', 'href="' . $baseUrl . '/reports"', $menuContent);
                $menuContent = str_replace('href="index.php?page=urunan-diskusi" data-content="includes/urunan-diskusi-content.html"', 'href="' . $baseUrl . '/discussions"', $menuContent);
                
                // Replace Semua Urunan menu items (admin only)
                if ($isAdmin) {
                    $menuContent = str_replace('href="index.php?page=urunan-all" data-content="includes/urunan-all-content.html"', 'href="' . base_url('admin/all/campaigns') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=urunan-laporan-semua" data-content="includes/urunan-laporan-semua-content.html"', 'href="' . base_url('admin/all/reports') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=urunan-riwayat" data-content="includes/urunan-riwayat-content.html"', 'href="' . base_url('admin/all/logs') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=urunan-komentar-semua" data-content="includes/urunan-komentar-semua-content.html"', 'href="' . base_url('admin/all/discussions') . '"', $menuContent);
                }
                
                // Replace Penggalang Dana menu items (admin only)
                if ($isAdmin) {
                    $menuContent = str_replace('href="index.php?page=admin-penggalang-dana-add" data-content="includes/admin-penggalang-dana-add-content.html"', 'href="' . base_url('admin/tenants/create') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=admin-penggalang-dana-list" data-content="includes/admin-penggalang-dana-list-content.html"', 'href="' . base_url('admin/tenants') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=admin-penggalang-applications" data-content="includes/admin-penggalang-applications-content.html"', 'href="' . base_url('admin/fundraiser-applications') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=admin-sponsorship-applications" data-content="includes/admin-sponsorship-applications-content.html"', 'href="' . base_url('admin/sponsorship-applications') . '"', $menuContent);
                }
                
                // Replace Konten Web menu items
                if ($isAdmin) {
                    // Admin content management
                    $menuContent = str_replace('href="index.php?page=content-banner" data-content="includes/content-banner-content.html"', 'href="' . base_url('admin/content/banner') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-sponsors" data-content="includes/content-sponsors-content.html"', 'href="' . base_url('admin/content/sponsors') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-halaman" data-content="includes/content-halaman-content.html"', 'href="' . base_url('admin/content/pages') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-blog" data-content="includes/content-blog-content.html"', 'href="' . base_url('admin/content/articles') . '"', $menuContent);
                    // Add menu management
                    $menuContent = str_replace('href="index.php?page=content-menu" data-content="includes/content-menu-content.html"', 'href="' . base_url('admin/content/menu') . '"', $menuContent);
                } else {
                    // Tenant content management (only pages and articles, no banner, no sponsors)
                    $menuContent = str_replace('href="index.php?page=content-banner" data-content="includes/content-banner-content.html"', 'href="#" onclick="return false;"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-sponsors" data-content="includes/content-sponsors-content.html"', 'href="#" onclick="return false;"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-halaman" data-content="includes/content-halaman-content.html"', 'href="' . base_url('tenant/content/pages') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=content-blog" data-content="includes/content-blog-content.html"', 'href="' . base_url('tenant/content/articles') . '"', $menuContent);
                    // Add menu management
                    $menuContent = str_replace('href="index.php?page=content-menu" data-content="includes/content-menu-content.html"', 'href="' . base_url('tenant/content/menu') . '"', $menuContent);
                }
                
                // Replace Pengaturan menu items
                if ($isAdmin) {
                    $menuContent = str_replace('href="index.php?page=admin-settings-general" data-content="includes/admin-settings-general-content.html"', 'href="' . base_url('admin/settings') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=settings-payment" data-content="includes/settings-payment-content.html"', 'href="' . base_url('admin/settings/payment-methods') . '"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=settings-rekening" data-content="includes/settings-rekening-content.html"', 'href="' . base_url('admin/settings/payment-methods#rekening') . '"', $menuContent);
                } else {
                    $menuContent = str_replace('href="index.php?page=settings-payment" data-content="includes/settings-payment-content.html"', 'href="' . $baseUrl . '/settings"', $menuContent);
                    $menuContent = str_replace('href="index.php?page=settings-rekening" data-content="includes/settings-rekening-content.html"', 'href="' . $baseUrl . '/settings#rekening"', $menuContent);
                }
                // Template Pesan WhatsApp: for tenant only (admin has it in settings tab)
                $menuContent = str_replace('href="index.php?page=settings-template" data-content="includes/settings-template-content.html"', 'href="' . $baseUrl . '/settings#template"', $menuContent);
                
                // Replace Profil menu items
                $menuContent = str_replace('href="index.php?page=profile-overview" data-content="includes/profile-overview-content.html"', 'href="' . $baseUrl . '/profile/overview"', $menuContent);
                // Security menu (password change) - for both admin and tenant
                $menuContent = str_replace('href="index.php?page=profile-security" data-content="includes/profile-security-content.html"', 'href="' . $baseUrl . '/profile/security"', $menuContent);
                // Settings menu in Profile section - for tenant only, goes to tenant settings
                if (!$isAdmin) {
                    $menuContent = str_replace('href="index.php?page=profile-settings" data-content="includes/profile-settings-content.html"', 'href="' . $baseUrl . '/settings"', $menuContent);
                } else {
                    // For admin, Settings in Profile section goes to profile security (legacy)
                    $menuContent = str_replace('href="index.php?page=profile-settings" data-content="includes/profile-settings-content.html"', 'href="' . $baseUrl . '/profile/security"', $menuContent);
                }
                
                // Replace Bantuan menu items
                $menuContent = str_replace('href="index.php?page=helpdesk-support" data-content="includes/helpdesk-support-content.html"', 'href="' . $baseUrl . '/helpdesk"', $menuContent);
                
                // Remove all data-content attributes
                $menuContent = preg_replace('/\s+data-content="[^"]*"/', '', $menuContent);
                
                // Add active class to current page
                $menuContent = preg_replace_callback(
                    '/(<a[^>]*href="([^"]+)"[^>]*class="([^"]*)")/',
                    function($matches) use ($currentUri) {
                        $fullTag = $matches[0];
                        $url = $matches[2];
                        $existingClass = $matches[3];
                        $urlPath = parse_url($url, PHP_URL_PATH);
                        
                        // Check if current URI matches this URL
                        if ($urlPath && strpos($currentUri, $urlPath) !== false) {
                            // Add active class if not already present
                            if (strpos($existingClass, 'active') === false) {
                                $fullTag = str_replace('class="' . $existingClass . '"', 'class="' . $existingClass . ' active"', $fullTag);
                            }
                        }
                        return $fullTag;
                    },
                    $menuContent
                );
                
                // Also handle links without class attribute
                $menuContent = preg_replace_callback(
                    '/(<a[^>]*href="([^"]+)")([^>]*>)/',
                    function($matches) use ($currentUri) {
                        $beforeClass = $matches[1];
                        $url = $matches[2];
                        $after = $matches[3];
                        $urlPath = parse_url($url, PHP_URL_PATH);
                        
                        // Check if current URI matches this URL and no class attribute exists
                        if ($urlPath && strpos($currentUri, $urlPath) !== false && strpos($beforeClass, 'class=') === false) {
                            return $beforeClass . ' class="nav-link active"' . $after;
                        }
                        return $matches[0];
                    },
                    $menuContent
                );
                
                echo $menuContent;
            }
        } else {
            // Fallback if file not found
            echo '<!-- Sidebar file not found -->';
        }
        ?>

    </nav>
</aside>

