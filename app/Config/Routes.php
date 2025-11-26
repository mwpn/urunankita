<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public routes (main & tenant subdomain)
// Use full namespace with backslash prefix to override default namespace
// SubdomainFilter akan auto-detect tenant dari subdomain
$routes->group('', ['filter' => 'subdomain'], function($routes) {
    $routes->get('/', '\Modules\Public\Controllers\PublicController::index');
    $routes->get('campaign/(:segment)/report', '\Modules\Public\Controllers\PublicController::campaignReport/$1');
    $routes->get('campaign/(:segment)', '\Modules\Public\Controllers\PublicController::campaign/$1');
    $routes->get('campaigns', '\Modules\Public\Controllers\PublicController::campaigns');
    $routes->get('articles', '\Modules\Public\Controllers\PublicController::articles');
    $routes->get('article/(:segment)', '\Modules\Public\Controllers\PublicController::article/$1');
    // Special pages with custom layout
    $routes->get('page/pedoman-syariah', '\Modules\Public\Controllers\PublicController::pedomanSyariah');
    $routes->get('page/ketentuan-sponsor', '\Modules\Public\Controllers\PublicController::ketentuanSponsor');
    $routes->get('page/penggalang-baru', '\Modules\Fundraiser\Controllers\FundraiserController::showForm');
    $routes->post('page/penggalang-baru', '\Modules\Fundraiser\Controllers\FundraiserController::submitForm');
    $routes->get('page/sponsorship', '\Modules\Sponsorship\Controllers\SponsorshipController::showForm');
    $routes->post('page/sponsorship', '\Modules\Sponsorship\Controllers\SponsorshipController::submitForm');
    // Public pages (static content pages)
    $routes->get('page/(:segment)', '\Modules\Content\Controllers\ContentController::viewPage/$1');
    
    // Discussion/Comments (Public)
    $routes->get('discussion/campaign/(:num)', '\Modules\Discussion\Controllers\DiscussionController::getComments/$1');
    $routes->post('discussion/comment', '\Modules\Discussion\Controllers\DiscussionController::addComment');
    $routes->post('discussion/comment/(:num)/like', '\Modules\Discussion\Controllers\DiscussionController::like/$1');
    $routes->post('discussion/comment/(:num)/unlike', '\Modules\Discussion\Controllers\DiscussionController::unlike/$1');
    $routes->post('discussion/comment/(:num)/amin', '\Modules\Discussion\Controllers\DiscussionController::amin/$1');
    $routes->post('discussion/comment/(:num)/unamin', '\Modules\Discussion\Controllers\DiscussionController::unamin/$1');
    
    // Debug route (temporary)
    $routes->get('debug/updates/(:num)', '\Modules\Public\Controllers\PublicController::debugUpdates/$1');
});

// Fundraiser application (public)
$routes->get('apply-fundraiser', '\Modules\Fundraiser\Controllers\FundraiserController::showForm');
$routes->post('apply-fundraiser', '\Modules\Fundraiser\Controllers\FundraiserController::submitForm');

// Login routes - redirect to /auth/login
$routes->get('login', function() {
    $isSubdomain = session()->get('is_subdomain') === true;
    if ($isSubdomain) {
        // Redirect from subdomain to main domain login
        $mainDomain = env('app.baseDomain', 'urunankita.test');
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return redirect()->to($scheme . '://' . $mainDomain . '/auth/login');
    }
    // If on main domain, redirect to /auth/login
    return redirect()->to('/auth/login');
});
$routes->post('login', function() {
    $isSubdomain = session()->get('is_subdomain') === true;
    if ($isSubdomain) {
        // Redirect from subdomain to main domain login
        $mainDomain = env('app.baseDomain', 'urunankita.test');
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return redirect()->to($scheme . '://' . $mainDomain . '/auth/login');
    }
    // If on main domain, redirect to /auth/login
    return redirect()->to('/auth/login');
});

// Auth routes (apply subdomain filter so tenant context is detected on subdomains)
$routes->group('auth', ['filter' => 'subdomain'], function($routes) {
    // Login routes - only accessible from main domain, redirect subdomain to main domain
    $routes->get('login', function() {
        $isSubdomain = session()->get('is_subdomain') === true;
        if ($isSubdomain) {
            // Redirect from subdomain to main domain login
            $mainDomain = env('app.baseDomain', 'urunankita.test');
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            return redirect()->to($scheme . '://' . $mainDomain . '/auth/login');
        }
        // If on main domain, show login page
        $authController = new \Modules\Auth\Controllers\AuthController();
        return $authController->login();
    });
    $routes->post('login', function() {
        $isSubdomain = session()->get('is_subdomain') === true;
        if ($isSubdomain) {
            // Redirect from subdomain to main domain login
            $mainDomain = env('app.baseDomain', 'urunankita.test');
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            return redirect()->to($scheme . '://' . $mainDomain . '/auth/login');
        }
        // If on main domain, process login
        $authController = new \Modules\Auth\Controllers\AuthController();
        return $authController->doLogin();
    });
    // Tenant registration is now only available through admin panel
    // $routes->get('register-tenant', '\Modules\\Auth\\Controllers\\AuthController::registerTenant');
    // $routes->post('register-tenant', '\Modules\\Auth\\Controllers\\AuthController::doRegisterTenant');
});
$routes->get('logout', '\Modules\\Auth\\Controllers\\AuthController::logout');

// Admin (superadmin) dashboard
$routes->group('admin', ['filter' => ['auth', 'role:super_admin']], static function (RouteCollection $routes) {
    $routes->get('/', '\Modules\Dashboard\Controllers\AdminController::index');
    $routes->get('dashboard', '\Modules\Dashboard\Controllers\AdminController::index');
    
    // Tenants management
    $routes->get('tenants', '\Modules\Tenant\Controllers\TenantController::index');
    $routes->get('tenants/create', '\Modules\Tenant\Controllers\TenantController::createPage');
    $routes->post('tenants/store', '\Modules\Tenant\Controllers\TenantController::store');
    $routes->get('tenants/(:num)/edit', '\Modules\Tenant\Controllers\TenantController::edit/$1');
    $routes->post('tenants/(:num)/update', '\Modules\Tenant\Controllers\TenantController::updatePage/$1');
    $routes->post('tenants/(:num)/delete', '\Modules\Tenant\Controllers\TenantController::delete/$1');
    
    // Campaigns management (all tenants)
    $routes->get('campaigns', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/create', '\Modules\Campaign\Controllers\CampaignController::adminCreatePage');
    $routes->post('campaigns/store', '\Modules\Campaign\Controllers\CampaignController::adminStore');
    $routes->get('campaigns/(:num)/edit', '\Modules\Campaign\Controllers\CampaignController::adminEdit/$1');
    $routes->post('campaigns/(:num)/update', '\Modules\Campaign\Controllers\CampaignController::adminUpdate/$1');
    $routes->post('campaigns/(:num)/update-status', '\Modules\Campaign\Controllers\CampaignController::adminUpdateStatus/$1');
    $routes->post('campaigns/(:num)/delete', '\Modules\Campaign\Controllers\CampaignController::adminDelete/$1');
    $routes->get('campaigns/(:num)/verify', '\Modules\Campaign\Controllers\CampaignController::verifyPage/$1');
    $routes->post('campaigns/(:num)/verify', '\Modules\Campaign\Controllers\CampaignController::verifySubmit/$1');
    $routes->get('campaigns/(:num)', '\Modules\Campaign\Controllers\CampaignController::adminView/$1');
    
    // Donations management (all tenants)
    $routes->get('donations', '\Modules\Donation\Controllers\DonationController::adminIndex');
    
    // Announcements management (platform announcements for all tenants)
    $routes->get('announcements', '\Modules\Announcement\Controllers\AnnouncementController::index');
    $routes->get('announcements/create', '\Modules\Announcement\Controllers\AnnouncementController::createPage');
    $routes->post('announcements/store', '\Modules\Announcement\Controllers\AnnouncementController::store');
    $routes->get('announcements/(:num)/edit', '\Modules\Announcement\Controllers\AnnouncementController::edit/$1');
    $routes->post('announcements/(:num)/update', '\Modules\Announcement\Controllers\AnnouncementController::update/$1');
    $routes->post('announcements/(:num)/delete', '\Modules\Announcement\Controllers\AnnouncementController::delete/$1');
    
    // Helpdesk management
    $routes->get('helpdesk', '\Modules\Helpdesk\Controllers\HelpdeskController::adminIndex');
    $routes->get('helpdesk/(:num)', '\Modules\Helpdesk\Controllers\HelpdeskController::adminTicketDetail/$1');
    $routes->post('helpdesk/(:num)/reply', '\Modules\Helpdesk\Controllers\HelpdeskController::adminAddReply/$1');
    $routes->post('helpdesk/(:num)/status', '\Modules\Helpdesk\Controllers\HelpdeskController::adminUpdateStatus/$1');
    
    // Billing management
    $routes->get('billing', '\Modules\Billing\Controllers\BillingController::index');
    $routes->get('invoices', '\Modules\Billing\Controllers\BillingController::invoices');
    
    // Plans management
    $routes->get('plans', '\Modules\Plan\Controllers\PlanController::index');
    $routes->get('plans/create', '\Modules\Plan\Controllers\PlanController::createPage');
    $routes->post('plans/store', '\Modules\Plan\Controllers\PlanController::store');
    $routes->get('plans/(:num)/edit', '\Modules\Plan\Controllers\PlanController::edit/$1');
    $routes->post('plans/(:num)/update', '\Modules\Plan\Controllers\PlanController::updatePage/$1');
    $routes->post('plans/(:num)/delete', '\Modules\Plan\Controllers\PlanController::deletePage/$1');
    
    // Subscriptions management
    $routes->get('subscriptions', '\Modules\Subscription\Controllers\SubscriptionController::index');
    
    // Reports management
    $routes->get('reports', '\Modules\Report\Controllers\ReportController::adminIndex');
    $routes->get('reports/create', '\Modules\Report\Controllers\ReportController::adminCreate');
    
    // Discussions management
    $routes->get('discussions', '\Modules\Discussion\Controllers\DiscussionController::adminIndex');
    
    // All data management (for "Semua Urunan" menu)
    $routes->group('all', function($routes) {
        $routes->get('campaigns', '\Modules\Campaign\Controllers\CampaignController::adminAllCampaigns');
        $routes->get('reports', '\Modules\Report\Controllers\ReportController::adminAllReports');
        $routes->get('logs', '\Modules\Campaign\Controllers\CampaignController::adminAllLogs');
        $routes->get('discussions', '\Modules\Discussion\Controllers\DiscussionController::adminAllDiscussions');
    });
    
    // Settings management
    $routes->get('settings', '\Modules\Setting\Controllers\SettingController::adminIndex');
    $routes->post('settings/save', '\Modules\Setting\Controllers\SettingController::adminSave');
    $routes->post('settings/save-section', '\Modules\Setting\Controllers\SettingController::adminSaveSection');
    $routes->post('settings/upload-logo', '\Modules\Setting\Controllers\SettingController::uploadLogo');
    $routes->post('settings/upload-favicon', '\Modules\Setting\Controllers\SettingController::uploadFavicon');
    $routes->post('settings/upload-hero-image', '\Modules\Setting\Controllers\SettingController::uploadHeroImage');
    $routes->post('settings/upload-hero-image-tenant', '\Modules\Setting\Controllers\SettingController::uploadHeroImageTenant');
    $routes->post('settings/remove-logo', '\Modules\Setting\Controllers\SettingController::removeLogo');
    $routes->post('settings/remove-favicon', '\Modules\Setting\Controllers\SettingController::removeFavicon');
    $routes->post('settings/remove-hero-image', '\Modules\Setting\Controllers\SettingController::removeHeroImage');
    $routes->post('settings/remove-hero-image-tenant', '\Modules\Setting\Controllers\SettingController::removeHeroImageTenant');
    $routes->get('settings/domain', '\Modules\Setting\Controllers\SettingController::adminIndex');
    $routes->get('settings/email', '\Modules\Setting\Controllers\SettingController::adminIndex');
    $routes->get('settings/payment', '\Modules\Setting\Controllers\SettingController::adminIndex');
    $routes->get('settings/payment-methods', '\Modules\Setting\Controllers\SettingController::adminPaymentMethods');
    $routes->post('settings/payment-methods/save', '\Modules\Setting\Controllers\SettingController::adminSavePaymentMethod');
    $routes->post('settings/payment-methods/(:num)/toggle', '\Modules\Setting\Controllers\SettingController::adminTogglePaymentMethod/$1');
    $routes->post('settings/payment-methods/(:num)/delete', '\Modules\Setting\Controllers\SettingController::adminDeletePaymentMethod/$1');
    $routes->get('settings/terms', '\Modules\Setting\Controllers\SettingController::adminIndex');
    $routes->get('settings/backup', '\Modules\Setting\Controllers\SettingController::adminIndex');
    
    // User Management
    $routes->get('users/all', '\Modules\User\Controllers\UserController::adminIndex');
    $routes->get('users/verified-fundraisers', '\Modules\User\Controllers\UserController::adminIndex');
    $routes->get('users/pending-fundraisers', '\Modules\User\Controllers\UserController::adminIndex');
    $routes->get('users/active-donors', '\Modules\User\Controllers\UserController::adminIndex');
    $routes->get('users/blocked', '\Modules\User\Controllers\UserController::adminIndex');
    $routes->get('users/login-activity', '\Modules\User\Controllers\UserController::adminLoginActivity');
    
    // Campaign Management filters
    $routes->get('campaigns/official', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/pending', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/active', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/completed', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/rejected', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('campaigns/flagged', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    
    // Official Campaigns
    $routes->get('official/create', '\Modules\Campaign\Controllers\CampaignController::adminCreatePage');
    $routes->get('official/active', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('official/donations', '\Modules\Donation\Controllers\DonationController::adminIndex');
    $routes->get('official/manage', '\Modules\Campaign\Controllers\CampaignController::adminIndex');
    $routes->get('official/financial-report', '\Modules\Report\Controllers\ReportController::adminIndex');
    
    // Official Donasi filters
    $routes->get('donations/incoming', '\Modules\Donation\Controllers\DonationController::adminIndex');
    $routes->get('donations/transfer-history', '\Modules\Donation\Controllers\DonationController::adminIndex');
    $routes->get('donations/disbursement', '\Modules\Donation\Controllers\DonationController::adminIndex');
    $routes->get('donations/financial-report', '\Modules\Report\Controllers\ReportController::adminIndex');
    
    // Rekening Saya
    $routes->get('rekening/add', '\Modules\Rekening\Controllers\RekeningController::adminAdd');
    $routes->get('rekening/primary', '\Modules\Rekening\Controllers\RekeningController::adminPrimary');
    $routes->get('rekening/verify', '\Modules\Rekening\Controllers\RekeningController::adminVerify');
    $routes->get('rekening/alternatives', '\Modules\Rekening\Controllers\RekeningController::adminAlternatives');
    
    // Rekening Verified
    $routes->get('rekening-verified/all', '\Modules\Rekening\Controllers\RekeningController::adminVerifiedIndex');
    $routes->get('rekening-verified/new', '\Modules\Rekening\Controllers\RekeningController::adminVerifiedNew');
    $routes->get('rekening-verified/verified', '\Modules\Rekening\Controllers\RekeningController::adminVerifiedVerified');
    $routes->get('rekening-verified/rejected', '\Modules\Rekening\Controllers\RekeningController::adminVerifiedRejected');
    $routes->get('rekening-verified/platform', '\Modules\Rekening\Controllers\RekeningController::adminVerifiedPlatform');
    
    // Content Management
    $routes->get('content/banner', '\Modules\Content\Controllers\ContentController::adminBanner');
    $routes->post('content/banner/store', '\Modules\Content\Controllers\ContentController::storeBanner');
    $routes->post('content/banner/update/(:num)', '\Modules\Content\Controllers\ContentController::updateBanner/$1');
    $routes->post('content/banner/delete/(:num)', '\Modules\Content\Controllers\ContentController::deleteBanner/$1');
    $routes->get('content/sponsors', '\Modules\Content\Controllers\ContentController::adminSponsors');
    $routes->get('content/sponsors/get/(:num)', '\Modules\Content\Controllers\ContentController::getSponsor/$1');
    $routes->post('content/sponsors/store', '\Modules\Content\Controllers\ContentController::storeSponsor');
    $routes->post('content/sponsors/update/(:num)', '\Modules\Content\Controllers\ContentController::updateSponsor/$1');
    $routes->post('content/sponsors/delete/(:num)', '\Modules\Content\Controllers\ContentController::deleteSponsor/$1');
    $routes->get('content/articles', '\Modules\Content\Controllers\ContentController::adminArticles');
    $routes->get('content/articles/create', '\Modules\Content\Controllers\ContentController::adminCreateArticle');
    $routes->get('content/articles/(:num)/edit', '\Modules\Content\Controllers\ContentController::adminEditArticle/$1');
    $routes->post('content/articles/store', '\Modules\Content\Controllers\ContentController::storeArticle');
    $routes->post('content/articles/update/(:num)', '\Modules\Content\Controllers\ContentController::updateArticle/$1');
    $routes->post('content/articles/delete/(:num)', '\Modules\Content\Controllers\ContentController::deleteArticle/$1');
    $routes->get('content/pages', '\Modules\Content\Controllers\ContentController::adminPages');
    $routes->get('content/pages/create', '\Modules\Content\Controllers\ContentController::adminCreatePage');
    $routes->get('content/pages/(:num)/edit', '\Modules\Content\Controllers\ContentController::adminEditPage/$1');
    $routes->post('content/pages/delete/(:num)', '\Modules\Content\Controllers\ContentController::deletePage/$1');
    $routes->post('content/pages/store', '\Modules\Content\Controllers\ContentController::storePage');
    $routes->post('content/pages/update/(:num)', '\Modules\Content\Controllers\ContentController::updatePage/$1');
    $routes->get('content/menu', '\Modules\Content\Controllers\ContentController::adminMenu');
    $routes->post('content/menu/store', '\Modules\Content\Controllers\ContentController::storeAdminMenu');
    $routes->get('content/faq', '\Modules\Content\Controllers\ContentController::adminFaq');
    $routes->get('content/testimonials', '\Modules\Content\Controllers\ContentController::adminTestimonials');
    $routes->get('content/newsletter', '\Modules\Content\Controllers\ContentController::adminNewsletter');

    // Fundraiser applications
    $routes->get('fundraiser-applications', '\Modules\Fundraiser\Controllers\FundraiserController::adminIndex');
    $routes->post('fundraiser-applications/(:num)/status', '\Modules\Fundraiser\Controllers\FundraiserController::updateStatus/$1');
    
    // Sponsorship applications
    $routes->get('sponsorship-applications', '\Modules\Sponsorship\Controllers\SponsorshipController::adminIndex');
    $routes->post('sponsorship-applications/(:num)/status', '\Modules\Sponsorship\Controllers\SponsorshipController::updateStatus/$1');
    
    // Reports & Moderation
    $routes->get('reports/user-reports', '\Modules\Report\Controllers\ReportController::adminUserReports');
    $routes->get('reports/campaign-complaints', '\Modules\Report\Controllers\ReportController::adminCampaignComplaints');
    $routes->get('reports/dispute-mediation', '\Modules\Report\Controllers\ReportController::adminDisputeMediation');
    $routes->get('reports/system-logs', '\Modules\Report\Controllers\ReportController::adminSystemLogs');
    
    // Helpdesk
    $routes->get('helpdesk/documentation', '\Modules\Helpdesk\Controllers\HelpdeskController::adminDocumentation');
    
    // Profil
    $routes->get('profile/overview', '\Modules\Profile\Controllers\ProfileController::adminOverview');
    $routes->get('profile/settings', '\Modules\Profile\Controllers\ProfileController::adminSettings');
    $routes->get('profile/security', '\Modules\Profile\Controllers\ProfileController::adminSecurity');
    $routes->post('profile/update', '\Modules\Profile\Controllers\ProfileController::adminUpdate');
    $routes->post('profile/avatar', '\Modules\Profile\Controllers\ProfileController::adminUpdateAvatar');
});

// Unified Dashboard entrypoint
$routes->get('dashboard', '\Modules\\Core\\Controllers\\DashboardRedirectController::index', ['filter' => 'auth']);

// Tenant dashboard without slug (use session tenant context)
$routes->get('tenant/dashboard', '\Modules\\Dashboard\\Controllers\\TenantController::indexNoSlug', ['filter' => 'auth']);

// Tenant campaign routes without slug (use session tenant context)
$routes->group('tenant', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('campaigns', '\Modules\\Campaign\\Controllers\\CampaignController::indexNoSlug');
    $routes->get('campaigns/create', '\Modules\\Campaign\\Controllers\\CampaignController::createPageNoSlug');
    $routes->post('campaigns/store', '\Modules\\Campaign\\Controllers\\CampaignController::storeNoSlug');
    $routes->get('campaigns/(:num)/edit', '\Modules\\Campaign\\Controllers\\CampaignController::editNoSlug/$1');
    $routes->post('campaigns/(:num)/update', '\Modules\\Campaign\\Controllers\\CampaignController::updateNoSlug/$1');
    $routes->post('campaigns/(:num)/delete', '\Modules\\Campaign\\Controllers\\CampaignController::deleteNoSlug/$1');
    $routes->post('campaigns/(:num)/complete', '\Modules\\Campaign\\Controllers\\CampaignController::completeNoSlug/$1');
    $routes->get('campaigns/(:num)', '\Modules\\Campaign\\Controllers\\CampaignController::viewNoSlug/$1');

    // Tenant settings UI
    $routes->get('settings', '\Modules\\Setting\\Controllers\\TenantSettingsController::index');
    $routes->post('settings/save', '\Modules\\Setting\\Controllers\\TenantSettingsController::save');
    $routes->post('settings/save-site', '\Modules\\Setting\\Controllers\\TenantSettingsController::saveSite');
    $routes->post('settings/upload-hero-image', '\Modules\\Setting\\Controllers\\TenantSettingsController::uploadHeroImage');
    $routes->post('settings/remove-hero-image', '\Modules\\Setting\\Controllers\\TenantSettingsController::removeHeroImage');
    $routes->post('settings/payment-method/(:num)/toggle', '\Modules\\Setting\\Controllers\\TenantSettingsController::togglePaymentMethod/$1');
    $routes->post('settings/payment-method/(:num)/delete', '\Modules\\Setting\\Controllers\\TenantSettingsController::deletePaymentMethod/$1');
    
    // Tenant reports UI
    $routes->get('reports', '\Modules\\Report\\Controllers\\ReportController::tenantIndex');
    $routes->get('reports/create', '\Modules\\Report\\Controllers\\ReportController::tenantCreate');

    // Tenant donations UI
    $routes->get('donations', '\Modules\\Donation\\Controllers\\DonationController::tenantIndex');

    // Tenant discussions UI
    $routes->get('discussions', '\Modules\\Discussion\\Controllers\\DiscussionController::tenantIndex');
    
    // Tenant content management
    $routes->get('content/pages', '\Modules\\Content\\Controllers\\ContentController::tenantPages');
    $routes->get('content/pages/create', '\Modules\\Content\\Controllers\\ContentController::tenantCreatePage');
    $routes->get('content/pages/(:num)/edit', '\Modules\\Content\\Controllers\\ContentController::tenantEditPage/$1');
    $routes->post('content/pages/store', '\Modules\\Content\\Controllers\\ContentController::storeTenantPage');
    $routes->post('content/pages/update/(:num)', '\Modules\\Content\\Controllers\\ContentController::updateTenantPage/$1');
    $routes->post('content/pages/delete/(:num)', '\Modules\\Content\\Controllers\\ContentController::deleteTenantPage/$1');
    $routes->get('content/articles', '\Modules\\Content\\Controllers\\ContentController::tenantArticles');
    $routes->get('content/articles/create', '\Modules\\Content\\Controllers\\ContentController::tenantCreateArticle');
    $routes->get('content/articles/(:num)/edit', '\Modules\\Content\\Controllers\\ContentController::tenantEditArticle/$1');
    $routes->post('content/articles/store', '\Modules\\Content\\Controllers\\ContentController::storeTenantArticle');
    $routes->post('content/articles/update/(:num)', '\Modules\\Content\\Controllers\\ContentController::updateTenantArticle/$1');
    $routes->post('content/articles/delete/(:num)', '\Modules\\Content\\Controllers\\ContentController::deleteTenantArticle/$1');
    $routes->get('content/menu', '\Modules\\Content\\Controllers\\ContentController::tenantMenu');
    $routes->post('content/menu/store', '\Modules\\Content\\Controllers\\ContentController::storeTenantMenu');

    // Tenant helpdesk/support UI
    $routes->get('helpdesk', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::tenantIndex');
    $routes->get('helpdesk/(:num)', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::tenantTicketDetail/$1');
    $routes->post('helpdesk/ticket/create', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::createTicket');
    $routes->get('helpdesk/ticket/(:segment)', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::ticket/$1');
    $routes->post('helpdesk/ticket/(:num)/reply', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::addReply/$1');

    // Tenant profile routes
    $routes->get('profile/overview', '\Modules\\Profile\\Controllers\\ProfileController::overview');
    $routes->get('profile/security', '\Modules\\Profile\\Controllers\\ProfileController::security');
    $routes->post('profile/update', '\Modules\\Profile\\Controllers\\ProfileController::update');
    $routes->post('profile/avatar', '\Modules\\Profile\\Controllers\\ProfileController::updateAvatar');

    // Tenant content management
    $routes->get('content/pages', '\Modules\\Content\\Controllers\\ContentController::tenantPages');
    $routes->get('content/pages/create', '\Modules\\Content\\Controllers\\ContentController::tenantCreatePage');
    $routes->get('content/pages/(:num)/edit', '\Modules\\Content\\Controllers\\ContentController::tenantEditPage/$1');
    $routes->post('content/pages/store', '\Modules\\Content\\Controllers\\ContentController::storeTenantPage');
    $routes->post('content/pages/update/(:num)', '\Modules\\Content\\Controllers\\ContentController::updateTenantPage/$1');
    $routes->post('content/pages/delete/(:num)', '\Modules\\Content\\Controllers\\ContentController::deleteTenantPage/$1');
    $routes->get('content/articles', '\Modules\\Content\\Controllers\\ContentController::tenantArticles');
    $routes->get('content/articles/create', '\Modules\\Content\\Controllers\\ContentController::tenantCreateArticle');
    $routes->get('content/articles/(:num)/edit', '\Modules\\Content\\Controllers\\ContentController::tenantEditArticle/$1');
    $routes->post('content/articles/store', '\Modules\\Content\\Controllers\\ContentController::storeTenantArticle');
    $routes->post('content/articles/update/(:num)', '\Modules\\Content\\Controllers\\ContentController::updateTenantArticle/$1');
    $routes->post('content/articles/delete/(:num)', '\Modules\\Content\\Controllers\\ContentController::deleteTenantArticle/$1');
});


// Public donation creation (no auth required)
$routes->post('donation/create', '\Modules\\Donation\\Controllers\\DonationController::create');

// Donation actions (requires auth)
$routes->post('donation/confirm/(:num)', '\Modules\\Donation\\Controllers\\DonationController::confirm/$1', ['filter' => 'auth']);
$routes->post('donation/cancel/(:num)', '\Modules\\Donation\\Controllers\\DonationController::cancel/$1', ['filter' => 'auth']);

// Helpdesk ticket actions (for tenant - path without tenant prefix)
$routes->group('helpdesk', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('ticket/create', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::createTicket');
    $routes->get('ticket/(:segment)', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::ticket/$1');
    $routes->post('ticket/(:num)/reply', '\Modules\\Helpdesk\\Controllers\\HelpdeskController::addReply/$1');
});

// Campaign Update routes (for creating campaign updates/laporan transparansi)
$routes->group('campaign-update', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('create', '\Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::create');
    $routes->post('update/(:num)', '\Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::update/$1');
    $routes->delete('delete/(:num)', '\Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::delete/$1');
});

// Discussion comment management routes (for admin and tenant - requires auth)
$routes->group('discussion', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('comment/(:num)/moderate', '\Modules\\Discussion\\Controllers\\DiscussionController::moderate/$1');
    $routes->post('comment/(:num)/pin', '\Modules\\Discussion\\Controllers\\DiscussionController::pin/$1');
    $routes->delete('comment/(:num)', '\Modules\\Discussion\\Controllers\\DiscussionController::delete/$1');
});

// Legacy slug-based tenant routes are disabled to avoid conflicts with non-slug routes
