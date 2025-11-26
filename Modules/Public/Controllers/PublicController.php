<?php

namespace Modules\Public\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Campaign\Models\CampaignModel;
use Modules\Campaign\Services\CampaignService;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Tenant\Models\TenantModel;
use Modules\Content\Models\BannerModel;
use Modules\Content\Models\ArticleModel;
use Config\Services as BaseServices;
use Config\Database;

class PublicController extends BaseController
{
    protected ?CampaignModel $campaignModel = null;
    protected ?CampaignService $campaignService = null;
    protected ?AnalyticsService $analyticsService = null;

    protected function initialize(): void
    {
        parent::initialize();
        $this->campaignModel = new CampaignModel();
        $this->campaignService = new CampaignService();
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Get platform settings for public pages
     */
    protected function getPlatformSettings(): array
    {
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $settingService = BaseServices::setting();
        $settings = [];
        
        // Get all platform settings
        $settingKeys = [
            'site_name',
            'site_tagline',
            'site_description',
            'site_logo',
            'site_favicon',
            'site_email',
            'site_phone',
            'site_address',
            'site_facebook',
            'site_instagram',
            'site_twitter',
            'hero_image',
            'frontend_font',
            'frontend_font_weights',
        ];
        
        foreach ($settingKeys as $key) {
            // Settings are stored with scope 'global' and scope_id null
            $settings[$key] = $settingService->get($key, null, 'global', null);
        }
        
        return $settings;
    }

    /**
     * Get tenant settings for public pages (with fallback to platform)
     */
    protected function getTenantSettings(?int $tenantId = null): array
    {
        if ($tenantId === null) {
            $tenantId = session()->get('tenant_id');
        }
        
        $settingService = BaseServices::setting();
        $settings = [];
        
        // Get all tenant settings with fallback to platform
        $settingKeys = [
            'site_name',
            'site_tagline',
            'site_description',
            'site_logo',
            'site_favicon',
            'site_email',
            'site_phone',
            'site_address',
            'site_facebook',
            'site_instagram',
            'site_twitter',
            'hero_image',
            'frontend_font',
            'frontend_font_weights',
        ];
        
        foreach ($settingKeys as $key) {
            // Try tenant setting first, fallback to global
            $settings[$key] = $settingService->getTenant($key, null, $tenantId);
        }
        
        return $settings;
    }

    /**
     * Get analytics service (lazy load)
     */
    protected function getAnalyticsService(): AnalyticsService
    {
        if ($this->analyticsService === null) {
            $this->analyticsService = new AnalyticsService();
        }
        return $this->analyticsService;
    }

    /**
     * Get campaign model (lazy load)
     * Simplified: All models use default database, BaseModel auto-filters by tenant_id
     */
    protected function getCampaignModel(): CampaignModel
    {
        return new CampaignModel();
    }

    /**
     * Get campaign service (lazy load)
     */
    protected function getCampaignService(): CampaignService
    {
        // Always create new instance to ensure it uses correct tenant DB
        return new CampaignService();
    }

    /**
     * Homepage - Routes based on subdomain
     * GET / 
     * - Main domain (urunankita.id): Aggregator semua urunan
     * - Tenant subdomain ({tenant}.urunankita.id): Urunan tenant tersebut saja
     */
    public function index()
    {
        // Debug mode - return JSON untuk test
        if ($this->request->getGet('debug') === '1') {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Controller reached!',
                'host' => $this->request->getUri()->getHost(),
                'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'not set',
                'env_tenant_subdomain' => $_ENV['TENANT_SUBDOMAIN'] ?? null,
                'session' => [
                    'is_subdomain' => session()->get('is_subdomain'),
                    'tenant_slug' => session()->get('tenant_slug'),
                    'tenant_id' => session()->get('tenant_id'),
                ],
                'uri' => $this->request->getUri()->getPath(),
            ]);
        }

        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantSlug = session()->get('tenant_slug');
        $tenantId = session()->get('tenant_id');

        if ($isSubdomain && $tenantId) {
            // Tenant subdomain - show tenant's campaigns only
            return $this->tenantHome($tenantId, $tenantSlug);
        }

        // Main domain - show all campaigns (aggregator)
        return $this->mainHome();
    }

    /**
     * Main domain homepage (Aggregator)
     */
    protected function mainHome()
    {
        $db = \Config\Database::connect();
        
        // Get platform tenant info first
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        $platformSlug = $platform ? $platform['slug'] : 'platform';
        $platformName = $platform ? $platform['name'] : 'UrunanKita';

        // Get ALL campaigns from ALL tenants (aggregator)
        $allCampaigns = $this->getAllPublicCampaignsAllTenants([
            'limit' => 20,
            'campaign_type' => $this->request->getGet('type'),
            'category' => $this->request->getGet('category'),
        ]);
        
        // Get all tenants info first to ensure correct grouping
        $tenantModel = new TenantModel();
        $allTenants = $tenantModel->findAll();
        $settingService = BaseServices::setting();
        $tenantInfoMap = [];
        foreach ($allTenants as $tenant) {
            // Get tenant site_description from settings
            $tenantDescription = $settingService->getTenant('site_description', null, (int) $tenant['id']);
            
            $tenantInfoMap[(int) $tenant['id']] = [
                'id' => (int) $tenant['id'],
                'name' => $tenant['name'],
                'slug' => $tenant['slug'],
                'description' => $tenantDescription,
            ];
        }
        
        // Group campaigns by tenant
        $campaignsByTenant = [];
        foreach ($allCampaigns as $campaign) {
            $tenantId = (int) ($campaign['tenant_id'] ?? 0);
            if (!isset($campaignsByTenant[$tenantId])) {
                // Get tenant info from map, fallback to campaign data
                if (isset($tenantInfoMap[$tenantId])) {
                    $tenantName = $tenantInfoMap[$tenantId]['name'];
                    $tenantSlug = $tenantInfoMap[$tenantId]['slug'];
                    $tenantDescription = $tenantInfoMap[$tenantId]['description'] ?? null;
                } else {
                    // Fallback to campaign data
                    $tenantName = $campaign['tenant_name'] ?? 'Unknown';
                    $tenantSlug = $campaign['tenant_slug'] ?? 'unknown';
                    $tenantDescription = null;
                }
                
                // For platform tenant, ensure slug is 'platform'
                if ($tenantId == $platformTenantId) {
                    $tenantSlug = 'platform';
                    $tenantName = $platformName;
                    // Get platform description from settings
                    $platformSettings = $this->getPlatformSettings();
                    $tenantDescription = $platformSettings['site_description'] ?? null;
                }
                
                $campaignsByTenant[$tenantId] = [
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenantName,
                    'tenant_slug' => $tenantSlug,
                    'tenant_description' => $tenantDescription,
                    'campaigns' => [],
                ];
            }
            $campaignsByTenant[$tenantId]['campaigns'][] = $campaign;
        }
        
        // Convert to array and sort - platform tenant first, then others alphabetically
        $campaigns = array_values($campaignsByTenant);
        
        usort($campaigns, function($a, $b) use ($platformTenantId) {
            // Platform tenant always first
            if ($a['tenant_id'] == $platformTenantId) return -1;
            if ($b['tenant_id'] == $platformTenantId) return 1;
            // Others sorted alphabetically
            return strcmp($a['tenant_name'], $b['tenant_name']);
        });

        // Get priority campaigns from ALL tenants (is_priority = 1)
        $db = \Config\Database::connect();
        $priorityCampaigns = $db->table('campaigns')
            ->select('campaigns.*, tenants.name as tenant_name, tenants.slug as tenant_slug')
            ->join('tenants', 'tenants.id = campaigns.tenant_id', 'left')
            ->where('campaigns.is_priority', 1)
            ->where('campaigns.status', 'active')
            ->orderBy('campaigns.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        
        // Enrich campaigns with progress data
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        foreach ($priorityCampaigns as &$campaign) {
            $campaign = $campaignModel->enrichCampaign($campaign);
        }
        
        $topCampaigns = $priorityCampaigns;

        // Get platform stats
        $analyticsService = $this->getAnalyticsService();
        $platformStats = $analyticsService->getPlatformStats();

        // Get recent donations from ALL campaigns (more for homepage display)
        $recentDonations = $db->table('donations')
            ->select('donations.*, campaigns.title as campaign_title, campaigns.tenant_id, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = donations.campaign_id', 'left')
            ->where('donations.payment_status', 'paid')
            ->orderBy('donations.paid_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();
        
        // Get total donors count based on total donations (all paid donations)
        $totalDonorsCount = $db->table('donations')
            ->where('payment_status', 'paid')
            ->countAllResults(false);
        
        // Process anonymous donations and add tenant info
        $tenantModel = new TenantModel();
        foreach ($recentDonations as &$donation) {
            if ($donation['is_anonymous']) {
                $donation['donor_name'] = 'Orang Baik';
            }
            // Add tenant info
            if (!empty($donation['tenant_id'])) {
                $tenant = $tenantModel->find($donation['tenant_id']);
                $donation['tenant_name'] = $tenant['name'] ?? null;
                $donation['tenant_slug'] = $tenant['slug'] ?? null;
            }
        }
        
        // Add total donors to platform stats
        $platformStats['total_donors'] = $totalDonorsCount;

        // Get active banners (platform banners)
        $bannerModel = new BannerModel();
        $banners = $bannerModel->getActiveBanners($platformTenantId);

        // Get active sponsors (platform sponsors)
        $sponsorModel = new \Modules\Content\Models\SponsorModel();
        $sponsors = $sponsorModel->getActiveSponsors($platformTenantId);

        // Get published articles from ALL tenants (latest 3)
        $articleModel = new ArticleModel();
        $articles = $this->getAllPublishedArticlesAllTenants([
            'limit' => 3,
            'order_by' => 'created_at',
            'order_dir' => 'DESC',
        ]);

        // Get platform settings
        $settings = $this->getPlatformSettings();

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => true,
            'campaigns' => $campaigns,
            'top_campaigns' => $topCampaigns,
            'platform_stats' => $platformStats,
            'recent_donations' => $recentDonations,
            'banners' => $banners,
            'sponsors' => $sponsors,
            'articles' => $articles,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
            'filters' => [
                'type' => $this->request->getGet('type'),
                'category' => $this->request->getGet('category'),
            ],
        ];

        return view('Modules\\Public\\Views\\home', $data);
    }

    /**
     * Tenant subdomain homepage
     */
    protected function tenantHome($tenantId, $tenantSlug)
    {
        // Get tenant info
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->findWithBankAccounts($tenantId);

        if (!$tenant) {
            // Redirect to main domain (use current protocol)
            $scheme = $this->request->getUri()->getScheme();
            $baseDomain = env('app.baseDomain', 'urunankita.test');
            return redirect()->to($scheme . '://' . $baseDomain);
        }

        // Simplified: No need to switch database
        // BaseModel will auto-filter by tenant_id from session

        // Get campaigns from this tenant only
        $filters = [
            'status' => 'active',
            'limit' => 20,
            'campaign_type' => $this->request->getGet('type'),
            'category' => $this->request->getGet('category'),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        // Create service instances - they will use single database
        $campaignService = new CampaignService();
        $analyticsService = new AnalyticsService();
        
        // Get campaigns - BaseModel will auto-filter by tenant_id from session
        $campaigns = $campaignService->getByTenant($tenantId, $filters);

        // Get tenant stats
        $tenantStats = $analyticsService->getTenantDashboardStats($tenantId);

        // Get priority campaigns from this tenant (is_priority = 1)
        $db = \Config\Database::connect();
        $priorityCampaigns = $db->table('campaigns')
            ->where('tenant_id', $tenantId)
            ->where('is_priority', 1)
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        
        // Enrich campaigns with progress data
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        foreach ($priorityCampaigns as &$campaign) {
            $campaign = $campaignModel->enrichCampaign($campaign);
        }
        
        $topCampaigns = $priorityCampaigns;

        // Get recent donations for this tenant
        $recentDonations = $db->table('donations')
            ->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug')
            ->join('campaigns', 'campaigns.id = donations.campaign_id', 'left')
            ->where('donations.tenant_id', $tenantId)
            ->where('donations.payment_status', 'paid')
            ->orderBy('donations.paid_at', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();
        
        // Process anonymous donations
        foreach ($recentDonations as &$donation) {
            if ($donation['is_anonymous']) {
                $donation['donor_name'] = 'Orang Baik';
            }
        }

        // Get active banners for tenant (fallback to platform if none)
        $bannerModel = new BannerModel();
        $banners = $bannerModel->getActiveBanners($tenantId);
        if (empty($banners)) {
            // Fallback to platform banners
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $platformTenantId = $platform ? (int) $platform['id'] : null;
            $banners = $bannerModel->getActiveBanners($platformTenantId);
        }

        // Get active sponsors for tenant (fallback to platform if none)
        $sponsorModel = new \Modules\Content\Models\SponsorModel();
        $sponsors = $sponsorModel->getActiveSponsors($tenantId);
        if (empty($sponsors)) {
            // Fallback to platform sponsors
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $platformTenantId = $platform ? (int) $platform['id'] : null;
            $sponsors = $sponsorModel->getActiveSponsors($platformTenantId);
        }

        // Get published articles for tenant only (no fallback to platform)
        $articleModel = new ArticleModel();
        $articles = $articleModel->getPublishedArticles($tenantId, [
            'limit' => 3,
            'order_by' => 'created_at',
            'order_dir' => 'DESC',
        ]);

        // Get tenant settings (with fallback to platform)
        $settings = $this->getTenantSettings($tenantId);

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => false,
            'tenant' => $tenant,
            'campaigns' => $campaigns,
            'tenant_stats' => $tenantStats,
            'top_campaigns' => $topCampaigns,
            'recent_donations' => $recentDonations,
            'banners' => $banners,
            'sponsors' => $sponsors,
            'articles' => $articles,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
            'filters' => [
                'type' => $this->request->getGet('type'),
                'category' => $this->request->getGet('category'),
            ],
        ];

        return view('Modules\\Public\\Views\\tenant_home', $data);
    }

    /**
     * Campaign detail - Routes based on subdomain
     * GET /campaign/{slug}
     */
    public function campaign($slug)
    {
        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');
        $db = Database::connect();

        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;

        if ($isSubdomain && $tenantId) {
            // Tenant subdomain - get campaign using tenant context
            $campaignModel = $this->getCampaignModel();
            $campaign = $campaignModel->getBySlug($slug);

            if (!$campaign) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Urunan tidak ditemukan');
            }

            // Verify campaign belongs to this tenant
            // If campaign belongs to platform and we're on platform subdomain, allow it
            // Otherwise, if campaign doesn't belong to this tenant, redirect to main domain
            if ($campaign['tenant_id'] != $tenantId && $campaign['tenant_id'] != $platformTenantId) {
                // Campaign doesn't belong to this tenant and not platform, redirect to main domain
                $scheme = $this->request->getUri()->getScheme();
                $baseDomain = env('app.baseDomain', 'urunankita.test');
                return redirect()->to($scheme . '://' . $baseDomain . '/campaign/' . $slug);
            }
            
            // If campaign belongs to platform, redirect to main domain (not platform subdomain)
            if ($campaign['tenant_id'] == $platformTenantId) {
                $scheme = $this->request->getUri()->getScheme();
                $baseDomain = env('app.baseDomain', 'urunankita.test');
                return redirect()->to($scheme . '://' . $baseDomain . '/campaign/' . $slug);
            }

            // Tenant subdomain view
            return $this->tenantCampaignDetail($campaign, $tenantId, $tenantSlug);
        }

        // Main domain - only show platform campaigns
        // Query directly to bypass BaseModel auto-filtering, then enrich manually
        $campaign = $db->table('campaigns')
            ->where('slug', $slug)
            ->where('tenant_id', $platformTenantId)
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (!$campaign) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Urunan tidak ditemukan');
        }

        // Enrich campaign data (parse images, calculate progress, etc.)
        $campaignModel = $this->getCampaignModel();
        $campaign = $campaignModel->enrichCampaign($campaign);

        // Main domain view - only platform campaigns
        return $this->mainCampaignDetail($campaign);
    }

    /**
     * Campaign transparency report page
     * GET /campaign/{slug}/report
     */
    public function campaignReport(string $slug)
    {
        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');
        $db = Database::connect();

        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;

        if ($isSubdomain && $tenantId) {
            $campaignModel = $this->getCampaignModel();
            $campaign = $campaignModel->getBySlug($slug);

            if (!$campaign) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Urunan tidak ditemukan');
            }

            if ($campaign['tenant_id'] != $tenantId && $campaign['tenant_id'] != $platformTenantId) {
                $scheme = $this->request->getUri()->getScheme();
                $baseDomain = env('app.baseDomain', 'urunankita.test');
                return redirect()->to($scheme . '://' . $baseDomain . '/campaign/' . $slug . '/report');
            }

            if ($campaign['tenant_id'] == $platformTenantId) {
                $scheme = $this->request->getUri()->getScheme();
                $baseDomain = env('app.baseDomain', 'urunankita.test');
                return redirect()->to($scheme . '://' . $baseDomain . '/campaign/' . $slug . '/report');
            }

            return $this->renderCampaignReportView($campaign, [
                'is_main_domain' => false,
                'tenant_id' => $tenantId,
                'tenant_slug' => $tenantSlug,
            ]);
        }

        $campaign = $db->table('campaigns')
            ->where('slug', $slug)
            ->where('tenant_id', $platformTenantId)
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (!$campaign) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Urunan tidak ditemukan');
        }

        return $this->renderCampaignReportView($campaign, [
            'is_main_domain' => true,
            'tenant_id' => $campaign['tenant_id'] ?? null,
        ]);
    }

    /**
     * Render campaign report view with shared data
     */
    protected function renderCampaignReportView(array $campaign, array $context = [])
    {
        $tenantModel = new TenantModel();
        $tenant = null;
        if (!empty($campaign['tenant_id'])) {
            $tenant = $tenantModel->findWithBankAccounts($campaign['tenant_id']);
        }

        $reportService = BaseServices::report();
        $financialReport = $reportService->getCampaignFinancialDetail($campaign['id']);
        $reportSummary = $financialReport['summary'] ?? [
            'total_donations' => 0,
            'donations_count' => 0,
            'total_withdrawn' => 0,
            'total_amount_used' => 0,
            'updates_count' => 0,
            'balance' => 0,
            'balance_percentage' => 0,
        ];

        $timestamps = [];
        foreach ($financialReport['donations'] ?? [] as $donation) {
            if (!empty($donation['paid_at'])) {
                $timestamps[] = $donation['paid_at'];
            }
        }
        foreach ($financialReport['updates'] ?? [] as $usage) {
            if (!empty($usage['created_at'])) {
                $timestamps[] = $usage['created_at'];
            }
        }
        if (!empty($campaign['updated_at'])) {
            $timestamps[] = $campaign['updated_at'];
        } elseif (!empty($campaign['created_at'])) {
            $timestamps[] = $campaign['created_at'];
        }
        $reportLastUpdated = !empty($timestamps) ? max($timestamps) : date('Y-m-d H:i:s');

        $isMainDomain = $context['is_main_domain'] ?? false;
        $settings = $isMainDomain
            ? $this->getPlatformSettings()
            : $this->getTenantSettings($context['tenant_id'] ?? null);

        $data = [
            'campaign' => $campaign,
            'tenant' => $tenant,
            'financial_report' => $financialReport,
            'report_summary' => $reportSummary,
            'report_last_updated' => $reportLastUpdated,
            'settings' => $settings,
            'is_main_domain' => $isMainDomain,
            'report_path' => '/campaign/' . ($campaign['slug'] ?? '') . '/report',
            'detail_path' => '/campaign/' . ($campaign['slug'] ?? ''),
        ];

        return view('Modules\\Public\\Views\\campaign_report', $data);
    }

    /**
     * Main domain campaign detail
     */
    protected function mainCampaignDetail($campaign)
    {
        // 1. Ambil tenant_id dari campaign (urunan milik siapa)
        $campaignTenantId = $campaign['tenant_id'] ?? null;
        
        // Get tenant info (penggalang urunan)
        $tenantModel = new TenantModel();
        $tenant = null;
        $activePaymentMethods = [];
        
        if ($campaignTenantId) {
            // 2. Ambil data tenant
            $tenant = $tenantModel->findWithBankAccounts($campaignTenantId);
            
            // 3. Ambil metode pembayaran yang aktif dari database
            $paymentMethodModel = new \Modules\Setting\Models\PaymentMethodModel();
            $activePaymentMethods = $paymentMethodModel->getActiveByTenant($campaignTenantId);
            
            // Convert enabled to boolean for view compatibility
            foreach ($activePaymentMethods as &$method) {
                $method['enabled'] = (bool) ($method['enabled'] ?? 0);
                $method['require_verification'] = (bool) ($method['require_verification'] ?? 0);
            }
        }

        // Get comments
        $discussionService = BaseServices::discussion();
        $comments = $discussionService->getComments($campaign['id'], ['limit' => 20]);

        // Get updates - use higher limit to get all updates for images/videos collection
        $updateService = BaseServices::campaignUpdate();
        $updates = $updateService->getByCampaign($campaign['id'], ['limit' => 100]);

        // Get donations (public view)
        $donationService = BaseServices::donation();
        $donations = $donationService->getByCampaign($campaign['id'], ['limit' => 10]);
        $donationStats = $donationService->getStats($campaign['id']);

        // Get report (transparency)
        $reportService = BaseServices::report();
        $report = $reportService->generateCampaignReport($campaign['id']);

        // Note: campaign['images'] already parsed by enrichCampaign() in getBySlug()

        // Get platform settings
        $settings = $this->getPlatformSettings();

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => true,
            'campaign' => $campaign,
            'tenant' => $tenant, // Add tenant info
            'active_payment_methods' => array_values($activePaymentMethods), // Reset array keys
            'comments' => $comments,
            'updates' => $updates,
            'donations' => $donations,
            'donation_stats' => $donationStats,
            'report' => $report,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
            'report_path' => '/campaign/' . ($campaign['slug'] ?? '') . '/report',
        ];

        return view('Modules\\Public\\Views\\campaign_detail', $data);
    }

    /**
     * Debug: Check campaign updates data
     * GET /debug/updates/{campaignId}
     */
    public function debugUpdates($campaignId)
    {
        $db = Database::connect();
        
        $updates = $db->table('campaign_updates')
            ->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        $output = "=== Campaign Updates for Campaign ID: {$campaignId} ===\n\n";
        $output .= "Total updates: " . count($updates) . "\n\n";
        
        foreach ($updates as $update) {
            $output .= "--- Update ID: {$update['id']} ---\n";
            $output .= "Title: " . ($update['title'] ?? 'N/A') . "\n";
            $output .= "Images (raw): " . ($update['images'] ?? 'NULL') . "\n";
            $output .= "YouTube URL: " . ($update['youtube_url'] ?? 'NULL') . "\n";
            $output .= "Created: " . ($update['created_at'] ?? 'N/A') . "\n";
            
            if (!empty($update['images'])) {
                $images = json_decode($update['images'], true);
                if (is_array($images) && !empty($images)) {
                    $output .= "Parsed Images (" . count($images) . "):\n";
                    foreach ($images as $img) {
                        $output .= "  - " . $img . "\n";
                    }
                } else {
                    $output .= "Images: Empty array or invalid JSON\n";
                }
            } else {
                $output .= "Images: NULL or empty\n";
            }
            $output .= "\n";
        }
        
        // Also check log file for recent entries
        $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
        $output .= "\n=== Recent Log Entries (last 20 lines) ===\n";
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $recentLines = array_slice($lines, -20);
            foreach ($recentLines as $line) {
                if (stripos($line, 'CampaignUpdate') !== false || stripos($line, 'images') !== false) {
                    $output .= $line;
                }
            }
        } else {
            $output .= "Log file not found: " . $logFile . "\n";
        }
        
        return $this->response->setBody('<pre>' . htmlspecialchars($output) . '</pre>');
    }

    /**
     * Tenant subdomain campaign detail
     */
    protected function tenantCampaignDetail($campaign, $tenantId, $tenantSlug)
    {
        // 1. Ambil tenant_id dari campaign (urunan milik siapa)
        $campaignTenantId = $campaign['tenant_id'] ?? $tenantId;
        
        // 2. Ambil data tenant
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->findWithBankAccounts($campaignTenantId);
        
        // 3. Ambil metode pembayaran yang aktif dari database
        $paymentMethodModel = new \Modules\Setting\Models\PaymentMethodModel();
        $activePaymentMethods = $paymentMethodModel->getActiveByTenant($campaignTenantId);
        
        // Convert enabled to boolean for view compatibility
        foreach ($activePaymentMethods as &$method) {
            $method['enabled'] = (bool) ($method['enabled'] ?? 0);
            $method['require_verification'] = (bool) ($method['require_verification'] ?? 0);
        }

        // Get comments
        $discussionService = BaseServices::discussion();
        $comments = $discussionService->getComments($campaign['id'], ['limit' => 20]);

        // Get updates - use higher limit to get all updates for images/videos collection
        $updateService = BaseServices::campaignUpdate();
        $updates = $updateService->getByCampaign($campaign['id'], ['limit' => 100]);

        // Get donations
        $donationService = BaseServices::donation();
        $donations = $donationService->getByCampaign($campaign['id'], ['limit' => 10]);
        $donationStats = $donationService->getStats($campaign['id']);

        // Get bank accounts for donation
        $bankAccounts = $donationService->getTenantBankAccounts($tenantId);

        // Get report
        $reportService = BaseServices::report();
        $report = $reportService->generateCampaignReport($campaign['id']);

        // Get tenant settings (with fallback to platform)
        $settings = $this->getTenantSettings($tenantId);

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => false,
            'tenant' => $tenant,
            'campaign' => $campaign,
            'active_payment_methods' => array_values($activePaymentMethods), // Reset array keys
            'comments' => $comments,
            'updates' => $updates,
            'donations' => $donations,
            'donation_stats' => $donationStats,
            'bank_accounts' => $bankAccounts,
            'report' => $report,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
            'report_path' => '/campaign/' . ($campaign['slug'] ?? '') . '/report',
        ];

        return view('Modules\\Public\\Views\\tenant_campaign_detail', $data);
    }

    /**
     * List campaigns - Routes based on subdomain
     * GET /campaigns
     */
    public function campaigns()
    {
        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');

            if ($isSubdomain && $tenantId) {
                // Tenant subdomain - show tenant's campaigns
                $tenantModel = new TenantModel();
                $tenant = $tenantModel->findWithBankAccounts($tenantId);

                // Simplified: No need to switch database
                // BaseModel will auto-filter by tenant_id from session

                $filters = [
                    'status' => 'active',
                    'category' => $this->request->getGet('category'),
                    'campaign_type' => $this->request->getGet('type'),
                    'limit' => $this->request->getGet('limit') ?? 20,
                ];

                $filters = array_filter($filters, fn($value) => $value !== null);
                $campaignService = $this->getCampaignService();
                $campaigns = $campaignService->getByTenant($tenantId, $filters);

            // Get tenant settings (with fallback to platform)
            $settings = $this->getTenantSettings($tenantId);

            // Get user role for header menu
            $authUser = session()->get('auth_user') ?? [];
            $userRole = $authUser['role'] ?? null;
            $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

            $data = [
                'is_main_domain' => false,
                'tenant' => $tenant,
                'campaigns' => $campaigns,
                'filters' => $filters,
                'settings' => $settings,
                'userRole' => $userRole,
                'isAdmin' => $isAdmin,
            ];

            return view('Modules\\Public\\Views\\tenant_campaigns_list', $data);
        }

        // Main domain - show ALL campaigns grouped by tenant
        $db = \Config\Database::connect();
        
        $filters = [
            'category' => $this->request->getGet('category'),
            'campaign_type' => $this->request->getGet('type'),
            'limit' => $this->request->getGet('limit') ?? 100, // Get more to group properly
            'page' => $this->request->getGet('page') ?? 1,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);
        $allCampaigns = $this->getAllPublicCampaignsAllTenants($filters);
        
        // Get platform tenant info first
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        $platformSlug = $platform ? $platform['slug'] : 'platform';
        $platformName = $platform ? $platform['name'] : 'UrunanKita';
        
        // Get all tenants info first to ensure correct grouping
        $tenantModel = new TenantModel();
        $allTenants = $tenantModel->findAll();
        $tenantInfoMap = [];
        foreach ($allTenants as $tenant) {
            $tenantInfoMap[(int) $tenant['id']] = [
                'id' => (int) $tenant['id'],
                'name' => $tenant['name'],
                'slug' => $tenant['slug'],
            ];
        }
        
        // Group campaigns by tenant
        $campaignsByTenant = [];
        foreach ($allCampaigns as $campaign) {
            $tenantId = (int) ($campaign['tenant_id'] ?? 0);
            if (!isset($campaignsByTenant[$tenantId])) {
                // Get tenant info from map, fallback to campaign data
                if (isset($tenantInfoMap[$tenantId])) {
                    $tenantName = $tenantInfoMap[$tenantId]['name'];
                    $tenantSlug = $tenantInfoMap[$tenantId]['slug'];
                } else {
                    // Fallback to campaign data
                    $tenantName = $campaign['tenant_name'] ?? 'Unknown';
                    $tenantSlug = $campaign['tenant_slug'] ?? 'unknown';
                }
                
                // For platform tenant, ensure slug is 'platform'
                if ($tenantId == $platformTenantId) {
                    $tenantSlug = 'platform';
                    $tenantName = $platformName;
                }
                
                $campaignsByTenant[$tenantId] = [
                    'tenant_id' => $tenantId,
                    'tenant_name' => $tenantName,
                    'tenant_slug' => $tenantSlug,
                    'campaigns' => [],
                ];
            }
            $campaignsByTenant[$tenantId]['campaigns'][] = $campaign;
        }
        
        // Convert to array and sort - platform tenant first, then others alphabetically
        $campaigns = array_values($campaignsByTenant);
        
        usort($campaigns, function($a, $b) use ($platformTenantId) {
            // Platform tenant always first
            if ($a['tenant_id'] == $platformTenantId) return -1;
            if ($b['tenant_id'] == $platformTenantId) return 1;
            // Others sorted alphabetically
            return strcmp($a['tenant_name'], $b['tenant_name']);
        });

        // Get platform settings
        $settings = $this->getPlatformSettings();

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => true,
            'campaigns' => $campaigns,
            'filters' => $filters,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Public\\Views\\campaigns_list', $data);
    }

    /**
     * List articles - Routes based on subdomain
     * GET /articles
     */
    public function articles()
    {
        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');
        $db = Database::connect();

        if ($isSubdomain && $tenantId) {
            // Tenant subdomain - show tenant's articles
            $tenantModel = new TenantModel();
            $tenant = $tenantModel->findWithBankAccounts($tenantId);

            if (!$tenant) {
                $scheme = $this->request->getUri()->getScheme();
                $baseDomain = env('app.baseDomain', 'urunankita.test');
                return redirect()->to($scheme . '://' . $baseDomain);
            }

            // Get published articles for tenant only (no fallback to platform)
            $articleModel = new ArticleModel();
            $articles = $articleModel->getPublishedArticles($tenantId, [
                'limit' => 20,
                'order_by' => 'created_at',
                'order_dir' => 'DESC',
            ]);

            // Get tenant settings (with fallback to platform)
            $settings = $this->getTenantSettings($tenantId);

            // Get user role for header menu
            $authUser = session()->get('auth_user') ?? [];
            $userRole = $authUser['role'] ?? null;
            $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

            $data = [
                'is_main_domain' => false,
                'tenant' => $tenant,
                'articles' => $articles,
                'settings' => $settings,
                'userRole' => $userRole,
                'isAdmin' => $isAdmin,
            ];

            return view('Modules\\Public\\Views\\tenant_articles_list', $data);
        }

        // Main domain - show only platform articles
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;

        $articleModel = new ArticleModel();
        $articles = $articleModel->getPublishedArticles($platformTenantId, [
            'limit' => 20,
            'order_by' => 'created_at',
            'order_dir' => 'DESC',
        ]);

        // Get platform settings
        $settings = $this->getPlatformSettings();

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => true,
            'articles' => $articles,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Public\\Views\\articles_list', $data);
    }

    /**
     * Article detail - Routes based on subdomain (same logic as campaign)
     * GET /article/{slug}
     */
    /**
     * Pedoman Syariah Page
     * GET /page/pedoman-syariah
     */
    public function pedomanSyariah()
    {
        // Get platform settings
        $settings = $this->getPlatformSettings();
        
        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);
        
        $data = [
            'settings' => $settings,
            'is_main_domain' => true,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];
        
        return view('Modules\\Public\\Views\\pedoman_syariah', $data);
    }
    
    /**
     * Ketentuan Sponsor Page
     * GET /page/ketentuan-sponsor
     */
    public function ketentuanSponsor()
    {
        // Get platform settings
        $settings = $this->getPlatformSettings();
        
        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);
        
        $data = [
            'settings' => $settings,
            'is_main_domain' => true,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];
        
        return view('Modules\\Public\\Views\\ketentuan_sponsor', $data);
    }

    public function article($slug)
    {
        $db = Database::connect();
        
        // Get subdomain info from request directly (more reliable than session)
        // CRITICAL: Use HTTP_HOST from server, not getUri()->getHost() which may not include subdomain
        $host = $_SERVER['HTTP_HOST'] ?? $this->request->getServer('HTTP_HOST') ?? $this->request->getUri()->getHost();
        $hostParts = explode('.', $host);
        // Check if subdomain: dendenny.urunankita.test = 3 parts, urunankita.test = 2 parts
        $isSubdomainFromHost = count($hostParts) > 2;
        
        // Debug logging for subdomain detection
        log_message('debug', "Article method - Initial host detection. Host: {$host}, Parts: " . count($hostParts) . ", IsSubdomainFromHost: " . ($isSubdomainFromHost ? 'true' : 'false'));
        
        // Also check if first part is not common subdomain
        if ($isSubdomainFromHost) {
            $firstPart = strtolower($hostParts[0]);
            if (in_array($firstPart, ['www', 'api', 'admin', 'app'])) {
                $isSubdomainFromHost = false; // Not a tenant subdomain
            }
        }
        
        // Also check session
        $sessionIsSubdomain = session()->get('is_subdomain') === true;
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');
        
        // Determine if subdomain: use host detection as primary
        // If host is subdomain, we MUST be in subdomain context
        $isSubdomain = $isSubdomainFromHost;
        
        // If host is subdomain, ALWAYS try to resolve tenant from host first
        // This ensures we have tenantId even if session is not set
        // CRITICAL: This must be done BEFORE article lookup to ensure correct tenant context
        if ($isSubdomainFromHost) {
            $subdomain = $hostParts[0];
            // Ignore common subdomains
            if (!in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app'])) {
                // Always resolve tenant from host to ensure we have the correct tenantId
                $tenant = $db->table('tenants')
                    ->where('slug', $subdomain)
                    ->where('status', 'active')
                    ->get()
                    ->getRowArray();
                if ($tenant) {
                    // CRITICAL: Always use the tenant resolved from host, not from session
                    // This ensures consistency and prevents redirect loops
                    $tenantId = (int) $tenant['id'];
                    $tenantSlug = $tenant['slug'];
                    // Set session for consistency
                    session()->set('tenant_id', $tenantId);
                    session()->set('tenant_slug', $tenantSlug);
                    session()->set('is_subdomain', true);
                    // Ensure isSubdomain flag is set correctly
                    $isSubdomain = true;
                } else {
                    // Tenant not found for this subdomain - can't proceed
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tenant tidak ditemukan');
                }
            } else {
                // Common subdomain, treat as main domain
                $isSubdomain = false;
                $tenantId = null;
                $tenantSlug = null;
            }
        } else {
            // Not a subdomain, ensure tenant context is cleared
            $isSubdomain = false;
            $tenantId = null;
            $tenantSlug = null;
        }

        $articleModel = new ArticleModel();
        
        // Try to find article by slug (without tenant filter first)
        $article = $db->table('articles')
            ->where('slug', $slug)
            ->where('published', 1)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$article) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Artikel tidak ditemukan');
        }

        // Enrich with author name
        if (!empty($article['author_id'])) {
            $user = $db->table('users')
                ->select('name')
                ->where('id', $article['author_id'])
                ->get()
                ->getRowArray();
            $article['author_name'] = $user ? $user['name'] : 'Admin';
        } else {
            $article['author_name'] = 'Admin';
        }

        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        $articleTenantId = $article['tenant_id'] ?? null;

        // Determine current host for loop prevention
        // CRITICAL: Use HTTP_HOST from server, not getUri()->getHost() which may not include subdomain
        $currentHost = $_SERVER['HTTP_HOST'] ?? $this->request->getServer('HTTP_HOST') ?? $this->request->getUri()->getHost();
        $baseDomain = env('app.baseDomain', 'urunankita.test');
        
        // Convert to int for comparison (ensure strict type)
        $articleTenantIdInt = $articleTenantId !== null ? (int)$articleTenantId : 0;
        $tenantIdInt = $tenantId ? (int)$tenantId : 0;
        $platformTenantIdInt = $platformTenantId !== null ? (int)$platformTenantId : 0;
        
        // Debug logging (remove in production)
        log_message('debug', "Article method - Host: {$currentHost}, IsSubdomain: " . ($isSubdomain ? 'true' : 'false') . ", TenantId: {$tenantIdInt}, ArticleTenantId: {$articleTenantIdInt}, PlatformTenantId: {$platformTenantIdInt}");
        
        // If we're on subdomain
        if ($isSubdomain && $tenantId) {
            // CRITICAL: Check if article belongs to current tenant FIRST
            // This is the most common case and should NEVER redirect
            // Use strict comparison and ensure both are positive integers
            // Also verify that we're actually on the correct subdomain
            $currentSubdomain = $hostParts[0] ?? '';
            $expectedSubdomain = $tenantSlug ?? '';
            
            // If article belongs to current tenant AND we're on correct subdomain
            if ($articleTenantIdInt > 0 && $tenantIdInt > 0 && $articleTenantIdInt === $tenantIdInt) {
                // Ensure we have tenant slug (should already be set from host resolution above)
                if (empty($tenantSlug)) {
                    $tenantModel = new TenantModel();
                    $currentTenant = $tenantModel->find($tenantId);
                    $tenantSlug = $currentTenant['slug'] ?? $currentSubdomain;
                    if ($tenantSlug) {
                        session()->set('tenant_slug', $tenantSlug);
                    }
                }
                
                // Article belongs to current tenant - show in subdomain (NO REDIRECT)
                // This is the most common case and should NEVER redirect
                log_message('debug', "Article belongs to current tenant - showing without redirect. Host: {$currentHost}, TenantId: {$tenantIdInt}, ArticleTenantId: {$articleTenantIdInt}, TenantSlug: {$tenantSlug}");
                
                // CRITICAL: Return immediately, no redirect - this should NEVER redirect
                return $this->tenantArticleDetail($article, $tenantId, $tenantSlug);
            }
            
            // Check if article belongs to platform
            if ($articleTenantIdInt === $platformTenantIdInt || $articleTenantIdInt === 0) {
                // Article belongs to platform - show in subdomain (NO REDIRECT)
                return $this->tenantArticleDetail($article, $tenantId, $tenantSlug);
            }
            
            // Article belongs to different tenant
            // Get the article's tenant to redirect to correct subdomain
            $tenantModel = new TenantModel();
            $articleTenant = $tenantModel->find($articleTenantId);
            if ($articleTenant && !empty($articleTenant['slug'])) {
                $expectedHost = $articleTenant['slug'] . '.' . $baseDomain;
                
                // CRITICAL: If we're already on the correct subdomain, show article (avoid loop)
                // This should never happen if logic above is correct, but safety check
                if ($currentHost === $expectedHost) {
                    // Set tenant context and show article
                    session()->set('tenant_id', (int) $articleTenant['id']);
                    session()->set('tenant_slug', $articleTenant['slug']);
                    session()->set('is_subdomain', true);
                    return $this->tenantArticleDetail($article, (int) $articleTenant['id'], $articleTenant['slug']);
                }
                
                // Only redirect if we're NOT already on the target subdomain
                $scheme = $this->request->getUri()->getScheme();
                return redirect()->to($scheme . '://' . $expectedHost . '/article/' . $slug);
            }
            
            // Article's tenant not found or no slug - show 404 (don't redirect to avoid loop)
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Artikel tidak ditemukan');
        }

        // We're on main domain
        // If article belongs to tenant (not platform), redirect to tenant subdomain
        if ($articleTenantIdInt > 0 && $articleTenantIdInt !== $platformTenantIdInt) {
            $tenantModel = new TenantModel();
            $articleTenant = $tenantModel->find($articleTenantId);
            if ($articleTenant && !empty($articleTenant['slug'])) {
                $expectedHost = $articleTenant['slug'] . '.' . $baseDomain;
                
                // CRITICAL: Check if we're already on that subdomain to avoid loop
                // This should never happen if we're on main domain, but safety check
                if ($currentHost === $expectedHost) {
                    // Already on the correct subdomain, don't redirect (avoid loop)
                    // This means we're actually on subdomain, not main domain
                    // Set tenant context and show article
                    log_message('debug', "Already on correct subdomain, showing article without redirect. Host: {$currentHost}, Expected: {$expectedHost}");
                    session()->set('tenant_id', (int) $articleTenant['id']);
                    session()->set('tenant_slug', $articleTenant['slug']);
                    session()->set('is_subdomain', true);
                    return $this->tenantArticleDetail($article, (int) $articleTenant['id'], $articleTenant['slug']);
                }
                
                // Only redirect if we're NOT already on the target subdomain
                // This should only happen when accessing from main domain
                log_message('debug', "Redirecting article to tenant subdomain. From: {$currentHost}, To: {$expectedHost}");
                $scheme = $this->request->getUri()->getScheme();
                return redirect()->to($scheme . '://' . $expectedHost . '/article/' . $slug);
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Artikel tidak ditemukan');
        }
        
        // Article belongs to platform or has no tenant_id - show on main domain
        return $this->mainArticleDetail($article);
    }

    /**
     * Main domain article detail
     */
    protected function mainArticleDetail($article)
    {
        $db = Database::connect();
        $articleTenantId = $article['tenant_id'] ?? null;

        // Get tenant info if article belongs to tenant
        $tenant = null;
        if ($articleTenantId) {
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $platformTenantId = $platform ? (int) $platform['id'] : null;
            
            // Only get tenant if not platform
            if ($articleTenantId !== $platformTenantId) {
                $tenantModel = new TenantModel();
                $tenant = $tenantModel->findWithBankAccounts($articleTenantId);
            }
        }

        // Get campaign stats and donations if article is linked to a campaign
        $campaignStats = null;
        $campaignDonations = [];
        $campaignComments = [];
        $relatedCampaigns = [];
        $articleHasCampaign = !empty($articleCampaignId);
        
        $articleCampaignId = $article['campaign_id'] ?? null;
        
        if ($articleCampaignId) {
            // Get campaign details
            $campaign = $db->table('campaigns')
                ->where('id', $articleCampaignId)
                ->where('status', 'active')
                ->get()
                ->getRowArray();
            
            if ($campaign) {
                // Calculate campaign stats
                $totalDonations = $db->table('donations')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->countAllResults();
                
                $totalAmount = $db->table('donations')
                    ->selectSum('amount')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->get()
                    ->getRowArray();
                
                $totalDonors = $db->table('donations')
                    ->select('donor_name')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->groupBy('donor_name')
                    ->countAllResults();
                
                $campaignStats = [
                    'campaign' => $campaign,
                    'total_donations' => $totalDonations,
                    'total_amount' => (float) ($totalAmount['amount'] ?? 0),
                    'total_donors' => $totalDonors,
                ];
                
                // Calculate progress
                if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                    $target = (float) $campaign['target_amount'];
                    $current = (float) $campaign['current_amount'];
                    $campaignStats['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                }
                
                // Get recent donations (last 10)
                $campaignDonations = $db->table('donations')
                    ->select('donations.*, campaigns.slug as campaign_slug')
                    ->join('campaigns', 'campaigns.id = donations.campaign_id', 'left')
                    ->where('donations.campaign_id', $articleCampaignId)
                    ->where('donations.payment_status', 'paid')
                    ->orderBy('donations.created_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();
                
                // Format donor names for anonymous
                foreach ($campaignDonations as &$donation) {
                    if ($donation['is_anonymous']) {
                        $donation['donor_name'] = 'Orang Baik';
                    }
                }

                // Get campaign comments
                $discussionService = BaseServices::discussion();
                $campaignComments = $discussionService->getComments($articleCampaignId, [
                    'limit' => 10,
                    'order_by' => 'created_at',
                    'order_dir' => 'DESC',
                ]);
            }
        } else {
            // Fallback: Get related campaigns based on article's tenant
            $targetTenantId = $articleTenantId;
            
            // If article belongs to platform, get platform campaigns
            if (!$targetTenantId) {
                $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
                $targetTenantId = $platform ? (int) $platform['id'] : null;
            }
            
            if ($targetTenantId) {
                $builder = $db->table('campaigns');
                $builder->where('campaigns.tenant_id', $targetTenantId);
                $builder->where('campaigns.status', 'active');
                $builder->orderBy('campaigns.created_at', 'DESC');
                $builder->limit(5);
                $relatedCampaigns = $builder->get()->getResultArray();
                
                // Calculate progress and stats for each campaign
                foreach ($relatedCampaigns as &$campaign) {
                    if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                        $target = (float) $campaign['target_amount'];
                        $current = (float) $campaign['current_amount'];
                        $campaign['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                    }
                    
                    // Get donation stats
                    $totalDonations = $db->table('donations')
                        ->where('campaign_id', $campaign['id'])
                        ->where('payment_status', 'paid')
                        ->countAllResults();
                    
                    $totalAmount = $db->table('donations')
                        ->selectSum('amount')
                        ->where('campaign_id', $campaign['id'])
                        ->where('payment_status', 'paid')
                        ->get()
                        ->getRowArray();
                    
                    $campaign['total_donations'] = $totalDonations;
                    $campaign['total_amount'] = (float) ($totalAmount['amount'] ?? 0);
                }
            }
        }

        // Get platform settings
        $settings = $this->getPlatformSettings();

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $data = [
            'is_main_domain' => true,
            'article' => $article,
            'tenant' => $tenant, // Add tenant info if article belongs to tenant
            'campaign_stats' => $campaignStats,
            'campaign_donations' => $campaignDonations,
             'campaign_comments' => $campaignComments,
            'article_has_campaign' => $articleHasCampaign,
            'related_campaigns' => $relatedCampaigns,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Public\\Views\\article_detail', $data);
    }

    /**
     * Tenant subdomain article detail
     */
    protected function tenantArticleDetail($article, $tenantId, $tenantSlug)
    {
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->findWithBankAccounts($tenantId);

        if (!$tenant) {
            // Tenant not found - don't redirect to avoid loop, just show 404
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tenant tidak ditemukan');
        }

        // Get tenant settings (with fallback to platform)
        $settings = $this->getTenantSettings($tenantId);

        // Get user role for header menu
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? null;
        $isAdmin = in_array($userRole, ['superadmin', 'super_admin', 'admin']);

        $db = Database::connect();
        $articleCampaignId = $article['campaign_id'] ?? null;
        $articleHasCampaign = !empty($articleCampaignId);

        $campaignStats = null;
        $campaignDonations = [];
        $campaignComments = [];
        $relatedCampaigns = [];

        if ($articleHasCampaign) {
            $campaign = $db->table('campaigns')
                ->where('id', $articleCampaignId)
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->get()
                ->getRowArray();

            if ($campaign) {
                $totalDonations = $db->table('donations')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->countAllResults();

                $totalAmount = $db->table('donations')
                    ->selectSum('amount')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->get()
                    ->getRowArray();

                $totalDonors = $db->table('donations')
                    ->select('donor_name')
                    ->where('campaign_id', $articleCampaignId)
                    ->where('payment_status', 'paid')
                    ->groupBy('donor_name')
                    ->countAllResults();

                $campaignStats = [
                    'campaign' => $campaign,
                    'total_donations' => $totalDonations,
                    'total_amount' => (float) ($totalAmount['amount'] ?? 0),
                    'total_donors' => $totalDonors,
                ];

                if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                    $target = (float) $campaign['target_amount'];
                    $current = (float) $campaign['current_amount'];
                    $campaignStats['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                }

                $campaignDonations = $db->table('donations')
                    ->select('donations.*, campaigns.slug as campaign_slug, tenants.slug as tenant_slug')
                    ->join('campaigns', 'campaigns.id = donations.campaign_id', 'left')
                    ->join('tenants', 'tenants.id = campaigns.tenant_id', 'left')
                    ->where('donations.campaign_id', $articleCampaignId)
                    ->where('donations.payment_status', 'paid')
                    ->orderBy('donations.created_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

                foreach ($campaignDonations as &$donation) {
                    if ($donation['is_anonymous']) {
                        $donation['donor_name'] = 'Orang Baik';
                    }
                }

                $discussionService = BaseServices::discussion();
                $campaignComments = $discussionService->getComments($articleCampaignId, [
                    'limit' => 10,
                    'order_by' => 'created_at',
                    'order_dir' => 'DESC',
                ]);
            }
        } else {
            $builder = $db->table('campaigns');
            $builder->where('campaigns.tenant_id', $tenantId);
            $builder->where('campaigns.status', 'active');
            $builder->orderBy('campaigns.created_at', 'DESC');
            $builder->limit(5);
            $relatedCampaigns = $builder->get()->getResultArray();

            foreach ($relatedCampaigns as &$campaign) {
                if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                    $target = (float) $campaign['target_amount'];
                    $current = (float) $campaign['current_amount'];
                    $campaign['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                }

                $totalDonations = $db->table('donations')
                    ->where('campaign_id', $campaign['id'])
                    ->where('payment_status', 'paid')
                    ->countAllResults();

                $totalAmount = $db->table('donations')
                    ->selectSum('amount')
                    ->where('campaign_id', $campaign['id'])
                    ->where('payment_status', 'paid')
                    ->get()
                    ->getRowArray();

                $campaign['total_donations'] = $totalDonations;
                $campaign['total_amount'] = (float) ($totalAmount['amount'] ?? 0);
            }
        }

        $data = [
            'is_main_domain' => false,
            'tenant' => $tenant,
            'article' => $article,
            'campaign_stats' => $campaignStats,
            'campaign_donations' => $campaignDonations,
            'campaign_comments' => $campaignComments,
            'article_has_campaign' => $articleHasCampaign,
            'related_campaigns' => $relatedCampaigns,
            'settings' => $settings,
            'userRole' => $userRole,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Public\\Views\\tenant_article_detail', $data);
    }

    /**
     * Get platform public campaigns only (for main domain)
     * Main domain (urunankita.test) only shows platform campaigns
     *
     * @param array $filters
     * @return array
     */
    protected function getAllPublicCampaigns(array $filters = []): array
    {
        $db = Database::connect();
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        if (!$platformTenantId) {
            return [];
        }
        
        // Query directly from database to bypass BaseModel auto-filtering
        $builder = $db->table('campaigns');
        
        // Only get campaigns from platform tenant
        $builder->where('campaigns.tenant_id', $platformTenantId);
        $builder->where('campaigns.status', 'active');

        if (isset($filters['category'])) {
            $builder->where('campaigns.category', $filters['category']);
        }

        if (isset($filters['campaign_type'])) {
            $builder->where('campaigns.campaign_type', $filters['campaign_type']);
        }

        $builder->orderBy('campaigns.created_at', 'DESC');
        
        if (isset($filters['limit'])) {
            $limit = (int) $filters['limit'];
            $offset = isset($filters['page']) ? ((int) $filters['page'] - 1) * $limit : 0;
            $builder->limit($limit, $offset);
        } else {
            $builder->limit(20);
        }

        $campaigns = $builder->get()->getResultArray();

        // Get tenant info for enrichment
        $tenantModel = new TenantModel();
        $tenants = $tenantModel->findAll();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }

        // Add tenant info to each campaign
        foreach ($campaigns as &$campaign) {
            $tenantId = (int) $campaign['tenant_id'];
            if (isset($tenantMap[$tenantId])) {
                $campaign['tenant_name'] = $tenantMap[$tenantId]['name'];
                $campaign['tenant_slug'] = $tenantMap[$tenantId]['slug'];
            } else {
                $campaign['tenant_name'] = 'Unknown';
                $campaign['tenant_slug'] = 'unknown';
            }
        }

        // Enrich each campaign
        foreach ($campaigns as &$campaign) {
            // Parse images
            if ($campaign['images']) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            } else {
                $campaign['images'] = [];
            }

            // Calculate progress for target_based
            if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                $target = (float) $campaign['target_amount'];
                $current = (float) $campaign['current_amount'];
                $campaign['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                $campaign['remaining_amount'] = max(0, $target - $current);
            }
        }

        return $campaigns;
    }

    /**
     * Get ALL public campaigns from ALL tenants (for aggregator homepage)
     *
     * @param array $filters
     * @return array
     */
    protected function getAllPublicCampaignsAllTenants(array $filters = []): array
    {
        $db = Database::connect();
        
        // Query directly from database to bypass BaseModel auto-filtering
        $builder = $db->table('campaigns');
        
        // Get campaigns from ALL tenants (no tenant_id filter)
        $builder->where('campaigns.status', 'active');

        if (isset($filters['category'])) {
            $builder->where('campaigns.category', $filters['category']);
        }

        if (isset($filters['campaign_type'])) {
            $builder->where('campaigns.campaign_type', $filters['campaign_type']);
        }

        $builder->orderBy('campaigns.created_at', 'DESC');
        
        if (isset($filters['limit'])) {
            $limit = (int) $filters['limit'];
            $offset = isset($filters['page']) ? ((int) $filters['page'] - 1) * $limit : 0;
            $builder->limit($limit, $offset);
        } else {
            $builder->limit(20);
        }

        $campaigns = $builder->get()->getResultArray();

        // Get tenant info for enrichment
        $tenantModel = new TenantModel();
        $tenants = $tenantModel->findAll();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }

        // Add tenant info to each campaign
        foreach ($campaigns as &$campaign) {
            $tenantId = (int) $campaign['tenant_id'];
            if (isset($tenantMap[$tenantId])) {
                $campaign['tenant_name'] = $tenantMap[$tenantId]['name'];
                $campaign['tenant_slug'] = $tenantMap[$tenantId]['slug'];
            } else {
                $campaign['tenant_name'] = 'Unknown';
                $campaign['tenant_slug'] = 'unknown';
            }
        }

        // Enrich each campaign
        foreach ($campaigns as &$campaign) {
            // Parse images
            if ($campaign['images']) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            } else {
                $campaign['images'] = [];
            }

            // Calculate progress for target_based
            if (($campaign['campaign_type'] ?? 'target_based') === 'target_based' && $campaign['target_amount']) {
                $target = (float) $campaign['target_amount'];
                $current = (float) $campaign['current_amount'];
                $campaign['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
                $campaign['remaining_amount'] = max(0, $target - $current);
            }
        }

        return $campaigns;
    }

    /**
     * Get ALL published articles from ALL tenants (for aggregator homepage)
     *
     * @param array $options
     * @return array
     */
    protected function getAllPublishedArticlesAllTenants(array $options = []): array
    {
        $db = Database::connect();
        
        // Query directly from database to bypass BaseModel auto-filtering
        $builder = $db->table('articles');
        
        // Get articles from ALL tenants (no tenant_id filter)
        $builder->where('published', 1);
        $builder->where('deleted_at IS NULL');
        
        if (isset($options['category']) && !empty($options['category'])) {
            $builder->where('category', $options['category']);
        }
        
        $orderBy = $options['order_by'] ?? 'created_at';
        $orderDir = $options['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);
        
        if (isset($options['limit'])) {
            $limit = (int) $options['limit'];
            $offset = isset($options['offset']) ? (int) $options['offset'] : 0;
            $builder->limit($limit, $offset);
        }
        
        $articles = $builder->get()->getResultArray();
        
        // Enrich with author name and tenant info
        $tenantModel = new TenantModel();
        $tenants = $tenantModel->findAll();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }
        
        foreach ($articles as &$article) {
            if (!empty($article['author_id'])) {
                $user = $db->table('users')
                    ->select('name')
                    ->where('id', $article['author_id'])
                    ->get()
                    ->getRowArray();
                $article['author_name'] = $user ? $user['name'] : 'Admin';
            } else {
                $article['author_name'] = 'Admin';
            }
            
            // Add tenant info
            if (!empty($article['tenant_id']) && isset($tenantMap[$article['tenant_id']])) {
                $article['tenant'] = $tenantMap[$article['tenant_id']];
                $article['tenant_name'] = $tenantMap[$article['tenant_id']]['name'];
                $article['tenant_slug'] = $tenantMap[$article['tenant_id']]['slug'];
            }
        }
        
        return $articles;
    }
}

