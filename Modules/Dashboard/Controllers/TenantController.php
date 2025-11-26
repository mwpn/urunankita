<?php

namespace Modules\Dashboard\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Tenant\Models\TenantModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\Donation\Models\DonationModel;
use Config\Database;

class TenantController extends BaseController
{
    public function indexNoSlug()
    {
        $tenantSlug = session()->get('tenant_slug');
        if ($tenantSlug) {
            return $this->index($tenantSlug);
        }

        // Fallback: derive from logged-in user
        $authUser = session()->get('auth_user') ?? [];
        $userId = $authUser['id'] ?? null;
        if ($userId) {
            $db = \Config\Database::connect();
            $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if ($userRow && !empty($userRow['tenant_id'])) {
                $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                if ($tenant) {
                    session()->set('tenant_id', (int) $tenant['id']);
                    session()->set('tenant_slug', $tenant['slug']);
                    session()->set('is_subdomain', false);
                    return $this->index($tenant['slug']);
                }
            }
        }

        return redirect()->to('/auth/login')->with('error', 'Tenant context not found');
    }

    public function index(string $slug)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        $tenantSlug = session()->get('tenant_slug') ?? $slug;

        if (!$tenantId) {
            return redirect()->to('/auth/login')->with('error', 'Tenant not found');
        }

        // Get tenant info
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->findWithBankAccounts($tenantId);
        
        if (!$tenant) {
            return redirect()->to('/auth/login')->with('error', 'Tenant not found');
        }

        // Simplified: No need to switch database
        // BaseModel will auto-filter by tenant_id from session

        // Get analytics stats
        $analyticsService = new AnalyticsService();
        $tenantStats = $analyticsService->getTenantDashboardStats($tenantId);

        // Get recent campaigns
        $campaignModel = new CampaignModel();
        $recentCampaigns = $campaignModel
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Get recent donations
        $donationModel = new DonationModel();
        $recentDonations = $donationModel
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')
            ->orderBy('paid_at', 'DESC')
            ->limit(5)
            ->findAll();

        $stats = [
            'tenant' => $tenant,
            'tenant_id' => $tenantId,
            'tenant_slug' => $tenantSlug,
            'tenant_db' => null,
            'active_campaigns' => $tenantStats['active_campaigns'] ?? 0,
            'total_donations' => $tenantStats['total_donations'] ?? 0,
            'total_donors' => $tenantStats['total_donors'] ?? 0,
            'recent_donations' => $tenantStats['recent_donations'] ?? 0,
            'balance' => $tenantStats['balance'] ?? 0,
            'total_withdrawals' => $tenantStats['total_withdrawals'] ?? 0,
            'recent_campaigns' => $recentCampaigns,
            'recent_donations_list' => $recentDonations,
        ];

        // Sidebar data
        $userName = session()->get('user_name') ?? $tenant['name'] ?? 'User';
        $data = [
            'stats' => $stats,
            'title' => 'Dashboard - ' . ($tenant['name'] ?? 'Tenant'),
            'pageTitle' => 'Dashboard',
            'page_title' => 'Dashboard',
            'sidebar_title' => $tenant['name'] ?? 'UrunanKita',
            'user_name' => $userName,
            'user_role' => 'Penggalang Urunan',
            'userRole' => 'penggalang_dana', // For template sidebar
            'recent_campaigns' => $recentCampaigns,
            'recent_donations' => array_map(function($d) {
                return [
                    'donor_name' => $d['donor_name'] ?? 'Anonim',
                    'amount' => $d['amount'] ?? 0,
                    'campaign_title' => $d['campaign_title'] ?? '-',
                    'created_at' => $d['paid_at'] ?? $d['created_at'] ?? '-',
                ];
            }, $recentDonations),
        ];

        // Use template layout instead of old layout
        return view('Modules\\Dashboard\\Views\\tenant_dashboard_template', $data);
    }
}


