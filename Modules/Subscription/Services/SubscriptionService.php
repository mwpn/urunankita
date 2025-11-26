<?php

namespace Modules\Subscription\Services;

use Modules\Subscription\Models\SubscriptionModel;
use Modules\Plan\Models\PlanModel;
use Modules\Billing\Services\BillingService;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class SubscriptionService
{
    protected SubscriptionModel $subscriptionModel;
    protected PlanModel $planModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        $this->planModel = new PlanModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create subscription
     *
     * @param int $tenantId
     * @param int $planId
     * @param int $durationMonths
     * @return int|false
     */
    public function create(int $tenantId, int $planId, int $durationMonths = 1)
    {
        // Check if plan exists
        $plan = $this->planModel->find($planId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }

        // Deactivate existing active subscription
        $this->deactivateActive($tenantId);

        // Calculate dates
        $startedAt = date('Y-m-d H:i:s');
        $expiredAt = $durationMonths > 0 
            ? date('Y-m-d H:i:s', strtotime("+{$durationMonths} months"))
            : null; // null = lifetime

        $data = [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'started_at' => $startedAt,
            'expired_at' => $expiredAt,
            'status' => 'active',
        ];

        $id = $this->subscriptionModel->insert($data);

        if ($id) {
            // Create invoice
            try {
                $billingService = BaseServices::billing();
                if ($billingService) {
                    $billingService->createInvoice($tenantId, $planId, $durationMonths);
                }
            } catch (\Exception $e) {
                // Invoice creation failure shouldn't break subscription
                log_message('error', 'Failed to create invoice for subscription: ' . $e->getMessage());
            }

            $this->activityLog->logCreate('Subscription', $id, $data, "Subscription created for plan: {$plan['name']}");
        }

        return $id ?: false;
    }

    /**
     * Get active subscription for tenant
     *
     * @param int $tenantId
     * @return array|null
     */
    public function getActive(int $tenantId): ?array
    {
        return $this->subscriptionModel->getByTenant($tenantId);
    }

    /**
     * Check if tenant has active subscription
     *
     * @param int $tenantId
     * @return bool
     */
    public function isActive(int $tenantId): bool
    {
        $subscription = $this->getActive($tenantId);
        if (!$subscription) {
            return false;
        }

        // Check expiration
        if ($subscription['expired_at'] && strtotime($subscription['expired_at']) < time()) {
            $this->expire($subscription['id']);
            return false;
        }

        return $subscription['status'] === 'active';
    }

    /**
     * Cancel subscription
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function cancel(int $subscriptionId): bool
    {
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $result = $this->subscriptionModel->update($subscriptionId, [
            'status' => 'cancelled',
        ]);

        if ($result) {
            $this->activityLog->logUpdate('Subscription', $subscriptionId, $subscription, ['status' => 'cancelled'], 'Subscription cancelled');
        }

        return $result;
    }

    /**
     * Expire subscription
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function expire(int $subscriptionId): bool
    {
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $result = $this->subscriptionModel->update($subscriptionId, [
            'status' => 'expired',
        ]);

        if ($result) {
            $this->activityLog->logUpdate('Subscription', $subscriptionId, $subscription, ['status' => 'expired'], 'Subscription expired');
        }

        return $result;
    }

    /**
     * Renew subscription
     *
     * @param int $subscriptionId
     * @param int $durationMonths
     * @return bool
     */
    public function renew(int $subscriptionId, int $durationMonths = 1): bool
    {
        $subscription = $this->subscriptionModel->find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $newExpiredAt = $durationMonths > 0
            ? date('Y-m-d H:i:s', strtotime("+{$durationMonths} months"))
            : null;

        $updateData = [
            'expired_at' => $newExpiredAt,
            'status' => 'active',
        ];

        $result = $this->subscriptionModel->update($subscriptionId, $updateData);

        if ($result) {
            // Create invoice for renewal
            try {
                $billingService = BaseServices::billing();
                if ($billingService) {
                    $billingService->createInvoice($subscription['tenant_id'], $subscription['plan_id'], $durationMonths);
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to create invoice for renewal: ' . $e->getMessage());
            }

            $this->activityLog->logUpdate('Subscription', $subscriptionId, $subscription, $updateData, 'Subscription renewed');
        }

        return $result;
    }

    /**
     * Deactivate active subscriptions for tenant
     *
     * @param int $tenantId
     * @return void
     */
    protected function deactivateActive(int $tenantId): void
    {
        $this->subscriptionModel->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->set(['status' => 'inactive'])
            ->update();
    }
}

