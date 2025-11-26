<?php

namespace Modules\Subscription\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Subscription\Services\SubscriptionService;
use Config\Services;

class SubscriptionController extends BaseController
{
    protected SubscriptionService $subscriptionService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->subscriptionService = new SubscriptionService();
    }

    /**
     * Get active subscription for current tenant
     * GET /subscription/active
     */
    public function active()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $subscription = $this->subscriptionService->getActive($tenantId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $subscription,
        ]);
    }

    /**
     * Create subscription
     * POST /subscription/create
     */
    public function create()
    {
        $tenantId = session()->get('tenant_id') ?? $this->request->getPost('tenant_id');
        $planId = $this->request->getPost('plan_id');
        $durationMonths = $this->request->getPost('duration_months') ?? 1;

        if (!$tenantId || !$planId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant ID and Plan ID are required',
            ])->setStatusCode(400);
        }

        try {
            $id = $this->subscriptionService->create((int) $tenantId, (int) $planId, (int) $durationMonths);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Subscription created successfully',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create subscription',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Cancel subscription
     * POST /subscription/cancel/{id}
     */
    public function cancel($id)
    {
        try {
            $result = $this->subscriptionService->cancel((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Subscription not found',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Renew subscription
     * POST /subscription/renew/{id}
     */
    public function renew($id)
    {
        $durationMonths = $this->request->getPost('duration_months') ?? 1;

        try {
            $result = $this->subscriptionService->renew((int) $id, (int) $durationMonths);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Subscription renewed successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Subscription not found',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Admin: List all subscriptions
     * GET /admin/subscriptions
     */
    public function index()
    {
        $subscriptionModel = new \Modules\Subscription\Models\SubscriptionModel();
        $subscriptions = $subscriptionModel->findAll();

        // Enrich with tenant and plan info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $planModel = new \Modules\Plan\Models\PlanModel();
        
        foreach ($subscriptions as &$subscription) {
            $tenant = $tenantModel->find($subscription['tenant_id']);
            $plan = $planModel->find($subscription['plan_id']);
            $subscription['tenant_name'] = $tenant['name'] ?? '-';
            $subscription['plan_name'] = $plan['name'] ?? '-';
        }

        $data = [
            'title' => 'Langganan - Admin Dashboard',
            'page_title' => 'Langganan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'subscriptions' => $subscriptions,
        ];

        return view('Modules\\Subscription\\Views\\admin_index', $data);
    }
}

