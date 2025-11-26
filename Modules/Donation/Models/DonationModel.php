<?php

namespace Modules\Donation\Models;

use Modules\Core\Models\BaseModel;

class DonationModel extends BaseModel
{
    protected $table = 'donations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'campaign_id',
        'tenant_id',
        'donor_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'amount',
        'is_anonymous',
        'payment_method',
        'payment_status',
        'payment_proof',
        'message',
        'invoice_id',
        'bank_account_id',
        'confirmed_by',
        'confirmed_at',
        'paid_at',
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
        'amount' => 'required|decimal',
        'payment_status' => 'in_list[pending,paid,failed,cancelled]',
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
     * Get donations by campaign
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getByCampaign(int $campaignId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('campaign_id', $campaignId);
        $builder->where('payment_status', 'paid'); // Only show paid donations

        if (isset($filters['include_anonymous'])) {
            // Handle anonymous visibility
        }

        $builder->orderBy('created_at', 'DESC');
        
        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $donations = $builder->get()->getResultArray();

        // Hide donor info if anonymous (untuk public view)
        foreach ($donations as &$donation) {
            if ($donation['is_anonymous']) {
                $donation['donor_name'] = 'Orang Baik';
                $donation['donor_email'] = null;
                $donation['donor_phone'] = null;
            }
        }

        return $donations;
    }

    /**
     * Get donations by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['campaign_id'])) {
            $builder->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['payment_status'])) {
            $builder->where('payment_status', $filters['payment_status']);
        }

        $builder->orderBy('created_at', 'DESC');

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get donation statistics for campaign
     *
     * @param int $campaignId
     * @return array
     */
    public function getCampaignStats(int $campaignId): array
    {
        $builder = $this->builder();
        $builder->select('
            COUNT(*) as total_donations,
            SUM(amount) as total_amount,
            COUNT(DISTINCT donor_id) as unique_donors
        ');
        $builder->where('campaign_id', $campaignId);
        $builder->where('payment_status', 'paid');

        return $builder->get()->getRowArray() ?? [
            'total_donations' => 0,
            'total_amount' => 0,
            'unique_donors' => 0,
        ];
    }
}

