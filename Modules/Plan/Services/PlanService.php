<?php

namespace Modules\Plan\Services;

use Modules\Plan\Models\PlanModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class PlanService
{
    protected PlanModel $planModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->planModel = new PlanModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Get all plans
     *
     * @return array
     */
    public function getAll(): array
    {
        $plans = $this->planModel->getActivePlans();
        
        foreach ($plans as &$plan) {
            $plan['features'] = json_decode($plan['features'] ?? '[]', true);
        }

        return $plans;
    }

    /**
     * Get plan by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->planModel->getById($id);
    }

    /**
     * Create plan
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $planData = [
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'description' => $data['description'] ?? null,
            'features' => isset($data['features']) ? json_encode($data['features']) : null,
        ];

        $id = $this->planModel->insert($planData);

        if ($id) {
            $this->activityLog->logCreate('Plan', $id, $planData, 'Plan created');
        }

        return $id ?: false;
    }

    /**
     * Update plan
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $oldPlan = $this->planModel->find($id);
        if (!$oldPlan) {
            return false;
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['price'])) {
            $updateData['price'] = $data['price'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['features'])) {
            $updateData['features'] = is_array($data['features']) 
                ? json_encode($data['features']) 
                : $data['features'];
        }

        $result = $this->planModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Plan', $id, $oldPlan, $updateData, 'Plan updated');
        }

        return $result;
    }

    /**
     * Delete plan
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $plan = $this->planModel->find($id);
        if (!$plan) {
            return false;
        }

        // Check if plan is used in subscriptions
        $subscriptionModel = new \Modules\Subscription\Models\SubscriptionModel();
        $subscriptions = $subscriptionModel->where('plan_id', $id)->countAllResults();
        
        if ($subscriptions > 0) {
            throw new \RuntimeException('Cannot delete plan that has active subscriptions');
        }

        $result = $this->planModel->delete($id);

        if ($result) {
            $this->activityLog->logDelete('Plan', $id, $plan, 'Plan deleted');
        }

        return $result;
    }
}

