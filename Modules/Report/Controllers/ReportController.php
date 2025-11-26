<?php

namespace Modules\Report\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Report\Services\ReportService;

class ReportController extends BaseController
{
    protected ReportService $reportService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->reportService = new ReportService();
    }

    /**
     * Generate financial report
     * GET /report/financial
     */
    public function financial()
    {
        $tenantId = session()->get('tenant_id');
        $campaignId = $this->request->getGet('campaign_id');
        $periodStart = $this->request->getGet('period_start');
        $periodEnd = $this->request->getGet('period_end');

        try {
            $report = $this->reportService->generateFinancialReport(
                $tenantId,
                $campaignId ? (int) $campaignId : null,
                $periodStart,
                $periodEnd
            );

            return $this->response->setJSON([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Generate campaign report (Public untuk transparansi)
     * GET /report/campaign/{campaignId}
     */
    public function campaign($campaignId)
    {
        try {
            // Verify campaign exists and is active
            $campaignModel = new \Modules\Campaign\Models\CampaignModel();
            $campaign = $campaignModel->find((int) $campaignId);
            
            if (!$campaign) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Urunan tidak ditemukan',
                ])->setStatusCode(404);
            }

            // Only show report for active or completed campaigns (public transparency)
            if (!in_array($campaign['status'], ['active', 'completed'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Laporan hanya tersedia untuk urunan aktif atau selesai',
                ])->setStatusCode(403);
            }

            $report = $this->reportService->generateCampaignReport((int) $campaignId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Save report
     * POST /report/save
     */
    public function save()
    {
        $data = [
            'tenant_id' => session()->get('tenant_id'),
            'campaign_id' => $this->request->getPost('campaign_id'),
            'report_type' => $this->request->getPost('report_type'),
            'title' => $this->request->getPost('title'),
            'period_start' => $this->request->getPost('period_start'),
            'period_end' => $this->request->getPost('period_end'),
            'summary' => $this->request->getPost('summary'),
            'data' => $this->request->getPost('data'),
            'total_donations' => $this->request->getPost('total_donations'),
            'total_withdrawals' => $this->request->getPost('total_withdrawals'),
            'total_campaigns' => $this->request->getPost('total_campaigns'),
            'total_donors' => $this->request->getPost('total_donors'),
            'is_public' => $this->request->getPost('is_public') === true || $this->request->getPost('is_public') === 'true',
        ];

        if (empty($data['report_type']) || empty($data['title'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tipe laporan dan judul wajib diisi',
            ])->setStatusCode(400);
        }

        try {
            $id = $this->reportService->saveReport($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Laporan berhasil disimpan',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan laporan',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get public reports
     * GET /report/public
     */
    public function publicReports()
    {
        $filters = [
            'tenant_id' => $this->request->getGet('tenant_id'),
            'report_type' => $this->request->getGet('report_type'),
            'limit' => $this->request->getGet('limit') ?? 20,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $reports = $this->reportService->getPublicReports($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Get tenant reports
     * GET /report/list
     */
    public function list()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'report_type' => $this->request->getGet('report_type'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $reports = $this->reportService->getByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Tenant Reports Page (Laporan Transparansi)
     * GET /tenant/reports
     */
    public function tenantIndex()
    {
        $tenantId = session()->get('tenant_id');
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
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
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return redirect()->to('/tenant/dashboard')->with('error', 'Tenant not found');
        }

        // Get all campaigns for this tenant
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->getByTenant($tenantId, ['limit' => 100]);

        // Get campaign updates for each campaign
        $campaignUpdateModel = new \Modules\CampaignUpdate\Models\CampaignUpdateModel();
        foreach ($campaigns as &$campaign) {
            $campaign['updates'] = $campaignUpdateModel->where('campaign_id', $campaign['id'])
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $authUser = session()->get('auth_user');
        
        $data = [
            'pageTitle' => 'Laporan Transparansi',
            'userRole' => 'penggalang_dana',
            'title' => 'Laporan Transparansi - Dashboard',
            'page_title' => 'Laporan Transparansi',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => $authUser['name'] ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'campaigns' => $campaigns,
        ];

        return view('Modules\\Report\\Views\\tenant_index', $data);
    }

    /**
     * Tenant Create Report Page (Form untuk membuat laporan baru)
     * GET /tenant/reports/create
     */
    public function tenantCreate()
    {
        $tenantId = session()->get('tenant_id');
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
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
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return redirect()->to('/tenant/dashboard')->with('error', 'Tenant not found');
        }

        // Get all campaigns for this tenant
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->getByTenant($tenantId, ['limit' => 100]);

        $data = [
            'pageTitle' => 'Buat Laporan Transparansi',
            'userRole' => 'penggalang_dana',
            'title' => 'Buat Laporan Transparansi',
            'page_title' => 'Buat Laporan Transparansi',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'campaigns' => $campaigns,
        ];

        return view('Modules\\Report\\Views\\tenant_create', $data);
    }

    /**
     * Admin Reports Page (same as tenant but for platform tenant only)
     * GET /admin/reports
     */
    public function adminIndex()
    {
        // Get platform tenant ID
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // If platform tenant doesn't exist, return empty data
            $authUser = session()->get('auth_user');
            $data = [
                'pageTitle' => 'Laporan Transparansi',
                'userRole' => 'admin',
                'title' => 'Laporan Transparansi - Admin Dashboard',
                'page_title' => 'Laporan Transparansi',
                'sidebar_title' => 'UrunanKita Admin',
                'user_name' => $authUser['name'] ?? 'Admin',
                'user_role' => 'Admin',
                'campaigns' => [],
            ];
            return view('Modules\\Report\\Views\\admin_index', $data);
        }

        $platformTenantId = (int) $platform['id'];

        // Get all campaigns for platform tenant
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->getByTenant($platformTenantId, ['limit' => 100]);

        // Get campaign updates for each campaign
        $campaignUpdateModel = new \Modules\CampaignUpdate\Models\CampaignUpdateModel();
        foreach ($campaigns as &$campaign) {
            $campaign['updates'] = $campaignUpdateModel->where('campaign_id', $campaign['id'])
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';

        $data = [
            'pageTitle' => 'Laporan Transparansi',
            'userRole' => 'admin',
            'title' => 'Laporan Transparansi - Admin Dashboard',
            'page_title' => 'Laporan Transparansi',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaigns' => $campaigns,
        ];

        return view('Modules\\Report\\Views\\admin_index', $data);
    }

    /**
     * Admin All Reports Page (all campaigns from all tenants)
     * GET /admin/all/reports
     */
    public function adminAllReports()
    {
        $db = \Config\Database::connect();
        
        // Get all tenants for filter dropdown
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get selected tenant_id from query string (optional filter)
        $selectedTenantId = $this->request->getGet('tenant_id');
        
        // Get all campaigns from all tenants (or filtered by tenant_id)
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaignBuilder = $db->table('campaigns');
        
        if ($selectedTenantId) {
            $campaignBuilder->where('tenant_id', (int) $selectedTenantId);
        }
        
        $campaigns = $campaignBuilder
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
        
        // Get tenant info for enrichment
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }
        
        // Enrich campaigns with tenant info
        foreach ($campaigns as &$campaign) {
            $tenant = $tenantMap[$campaign['tenant_id']] ?? null;
            $campaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
            $campaign['tenant_slug'] = $tenant['slug'] ?? '';
        }
        
        // Get campaign updates for each campaign
        $campaignUpdateModel = new \Modules\CampaignUpdate\Models\CampaignUpdateModel();
        foreach ($campaigns as &$campaign) {
            $campaign['updates'] = $campaignUpdateModel->where('campaign_id', $campaign['id'])
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }

        $data = [
            'pageTitle' => 'Laporan Semua Urunan',
            'userRole' => $userRole,
            'title' => 'Laporan Semua Urunan - Admin Dashboard',
            'page_title' => 'Laporan Semua Urunan',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaigns' => $campaigns,
            'tenants' => $tenants,
            'selectedTenantId' => $selectedTenantId,
        ];

        return view('Modules\\Report\\Views\\admin_index', $data);
    }

    /**
     * Admin Create Report Page (Form untuk membuat laporan baru - same as tenant but for platform tenant)
     * GET /admin/reports/create
     */
    public function adminCreate()
    {
        // Get platform tenant ID
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            return redirect()->to('/admin/reports')->with('error', 'Platform tenant tidak ditemukan');
        }

        $platformTenantId = (int) $platform['id'];

        // Get all campaigns for platform tenant, exclude deleted
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $allCampaigns = $campaignModel->getByTenant($platformTenantId, ['limit' => 100]);
        
        // Filter out deleted campaigns
        $campaigns = array_filter($allCampaigns, function($campaign) {
            return ($campaign['status'] ?? '') !== 'deleted';
        });

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';

        $data = [
            'pageTitle' => 'Buat Laporan Penggunaan Dana',
            'userRole' => 'admin',
            'title' => 'Buat Laporan Penggunaan Dana',
            'page_title' => 'Buat Laporan Penggunaan Dana',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaigns' => $campaigns,
        ];

        return view('Modules\\Report\\Views\\admin_create', $data);
    }
}

