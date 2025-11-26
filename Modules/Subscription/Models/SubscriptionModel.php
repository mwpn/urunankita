<?php

namespace Modules\Subscription\Models;

use Modules\Core\Models\BaseModel;

class SubscriptionModel extends BaseModel
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'plan_id',
        'started_at',
        'expired_at',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'tenant_id' => 'required|integer',
        'plan_id' => 'required|integer',
        'status' => 'in_list[active,inactive,expired,cancelled]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get subscription by tenant
     *
     * @param int $tenantId
     * @return array|null
     */
    public function getByTenant(int $tenantId): ?array
    {
        $subscription = $this->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($subscription) {
            $subscription['plan'] = $this->getPlanDetails($subscription['plan_id']);
            $subscription['is_expired'] = $this->isExpired($subscription['expired_at']);
        }

        return $subscription;
    }

    /**
     * Get all subscriptions for tenant
     *
     * @param int $tenantId
     * @return array
     */
    public function getAllByTenant(int $tenantId): array
    {
        $subscriptions = $this->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($subscriptions as &$subscription) {
            $subscription['plan'] = $this->getPlanDetails($subscription['plan_id']);
            $subscription['is_expired'] = $this->isExpired($subscription['expired_at']);
        }

        return $subscriptions;
    }

    /**
     * Check if subscription is expired
     *
     * @param string|null $expiredAt
     * @return bool
     */
    protected function isExpired(?string $expiredAt): bool
    {
        if (!$expiredAt) {
            return false; // No expiration = lifetime
        }

        return strtotime($expiredAt) < time();
    }

    /**
     * Get plan details
     *
     * @param int $planId
     * @return array|null
     */
    protected function getPlanDetails(int $planId): ?array
    {
        $planModel = new \Modules\Plan\Models\PlanModel();
        return $planModel->getById($planId);
    }

    /**
     * Get active subscriptions count
     *
     * @param int $planId
     * @return int
     */
    public function getActiveCountByPlan(int $planId): int
    {
        return $this->where('plan_id', $planId)
            ->where('status', 'active')
            ->countAllResults();
    }
}

