<?php

namespace Modules\Withdrawal\Models;

use Modules\Core\Models\BaseModel;

class WithdrawalModel extends BaseModel
{
    protected $table = 'withdrawals';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'campaign_id',
        'tenant_id',
        'beneficiary_id',
        'amount',
        'status',
        'requested_by',
        'approved_by',
        'rejection_reason',
        'notes',
        'transfer_proof',
        'requested_at',
        'approved_at',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'beneficiary_id' => 'required|integer',
        'amount' => 'required|decimal',
        'status' => 'in_list[pending,approved,processing,completed,rejected,cancelled]',
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
     * Get withdrawals by campaign
     *
     * @param int $campaignId
     * @return array
     */
    public function getByCampaign(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get withdrawals by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['campaign_id'])) {
            $builder->where('campaign_id', $filters['campaign_id']);
        }

        $builder->orderBy('created_at', 'DESC');

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get total withdrawn amount for campaign
     *
     * @param int $campaignId
     * @return float
     */
    public function getTotalWithdrawn(int $campaignId): float
    {
        $builder = $this->builder();
        $builder->selectSum('amount');
        $builder->where('campaign_id', $campaignId);
        $builder->whereIn('status', ['approved', 'processing', 'completed']);

        $result = $builder->get()->getRowArray();
        return (float) ($result['amount'] ?? 0);
    }
}

