<?php

namespace Modules\Analytics\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Analytics\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    protected AnalyticsService $analyticsService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->analyticsService = new AnalyticsService();
    }

    /**
     * Get tenant dashboard stats
     * GET /analytics/dashboard
     */
    public function dashboard()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        try {
            $stats = $this->analyticsService->getTenantDashboardStats($tenantId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get platform stats (admin)
     * GET /analytics/platform
     */
    public function platform()
    {
        try {
            $stats = $this->analyticsService->getPlatformStats();

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get top campaigns
     * GET /analytics/top-campaigns
     */
    public function topCampaigns()
    {
        $tenantId = session()->get('tenant_id');
        $limit = $this->request->getGet('limit') ?? 10;

        try {
            $campaigns = $this->analyticsService->getTopCampaigns((int) $limit, $tenantId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $campaigns,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get donation trends
     * GET /analytics/trends
     */
    public function trends()
    {
        $tenantId = session()->get('tenant_id');
        $period = $this->request->getGet('period') ?? 'daily';
        $limit = $this->request->getGet('limit') ?? 30;

        try {
            $trends = $this->analyticsService->getDonationTrends($period, $tenantId, (int) $limit);

            return $this->response->setJSON([
                'success' => true,
                'data' => $trends,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get campaign performance
     * GET /analytics/campaign/{campaignId}
     */
    public function campaignPerformance($campaignId)
    {
        try {
            $performance = $this->analyticsService->getCampaignPerformance((int) $campaignId);

            if (empty($performance)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Campaign not found',
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $performance,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}

