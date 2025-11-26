<?php

namespace Modules\Campaign\Models;

use Modules\Core\Models\BaseModel;

class CampaignModel extends BaseModel
{
    protected $table = 'campaigns';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'creator_user_id',
        'beneficiary_id',
        'title',
        'slug',
        'description',
        'campaign_type',
        'target_amount',
        'current_amount',
        'category',
        'status',
        'is_priority',
        'featured_image',
        'images',
        'deadline',
        'latitude',
        'longitude',
        'location_address',
        'use_tenant_bank_account',
        'bank_account_id',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'completed_at',
        'views_count',
        'donors_count',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'slug' => 'required|max_length[255]|is_unique[campaigns.slug,id,{id}]',
        'campaign_type' => 'in_list[target_based,ongoing]',
        'target_amount' => 'permit_empty|decimal',
        'tenant_id' => 'required|integer',
        'status' => 'in_list[draft,pending_verification,active,completed,rejected,closed,suspended,deleted]',
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
     * Get campaigns by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        // Simplified for single database - just filter by tenant_id
        $builder = $this->builder();
        
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (isset($filters['campaign_type'])) {
            $builder->where('campaign_type', $filters['campaign_type']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        // Execute query and verify
        $query = $builder->get();
        $campaigns = $query->getResultArray();
        
        // Debug: Log actual query and results
        $sql = $builder->getCompiledSelect(false);
        log_message('debug', "SQL Query: " . $sql);
        // In single-db mode, use current connection name for debug (optional)
        $dbNameForLog = method_exists($this->db, 'getDatabase') ? $this->db->getDatabase() : 'default';
        log_message('debug', "Found " . count($campaigns) . " campaigns from DB: {$dbNameForLog}, tenant_id: {$tenantId}");
        
        // Verify all campaigns belong to correct tenant
        $invalidCampaigns = [];
        foreach ($campaigns as $idx => $camp) {
            $campTenantId = (int) ($camp['tenant_id'] ?? 0);
            if ($campTenantId !== (int) $tenantId) {
                $invalidCampaigns[] = [
                    'id' => $camp['id'] ?? 'unknown',
                    'title' => $camp['title'] ?? 'unknown',
                    'expected_tenant_id' => $tenantId,
                    'actual_tenant_id' => $campTenantId,
                ];
                // Remove invalid campaign
                unset($campaigns[$idx]);
            }
        }
        
        if (!empty($invalidCampaigns)) {
            log_message('error', "Found " . count($invalidCampaigns) . " campaigns with wrong tenant_id: " . json_encode($invalidCampaigns));
        }
        
        // Re-index array after removing invalid campaigns
        $campaigns = array_values($campaigns);
        
        foreach ($campaigns as &$campaign) {
            $campaign = $this->enrichCampaign($campaign);
        }

        return $campaigns;
    }

    /**
     * Get public active campaigns
     *
     * @param array $filters
     * @return array
     */
    public function getPublicCampaigns(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('status', 'active');

        if (isset($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (isset($filters['campaign_type'])) {
            $builder->where('campaign_type', $filters['campaign_type']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        if (isset($filters['featured'])) {
            // Add featured logic if needed
        }

        $builder->orderBy('created_at', 'DESC');

        $campaigns = $builder->get()->getResultArray();
        
        foreach ($campaigns as &$campaign) {
            $campaign = $this->enrichCampaign($campaign);
        }

        return $campaigns;
    }

    /**
     * Get campaign by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        $campaign = $this->where('slug', $slug)->first();
        if ($campaign) {
            $campaign = $this->enrichCampaign($campaign);
            // Increment views
            $this->incrementViews($campaign['id']);
        }
        return $campaign;
    }

    /**
     * Enrich campaign data
     *
     * @param array $campaign
     * @return array
     */
    public function enrichCampaign(array $campaign): array
    {
        // Parse images JSON
        if ($campaign['images']) {
            $campaign['images'] = json_decode($campaign['images'], true) ?? [];
        } else {
            $campaign['images'] = [];
        }

        // Set default campaign_type if not set
        $campaignType = $campaign['campaign_type'] ?? 'target_based';
        $campaign['campaign_type'] = $campaignType;

        // Calculate progress (only for target_based)
        if ($campaignType === 'target_based' && $campaign['target_amount']) {
            $target = (float) $campaign['target_amount'];
            $current = (float) $campaign['current_amount'];
            $campaign['progress_percentage'] = $target > 0 ? round(($current / $target) * 100, 2) : 0;
            $campaign['remaining_amount'] = max(0, $target - $current);

            // Check if target reached
            if ($current >= $target && $campaign['status'] === 'active') {
                $campaign['is_target_reached'] = true;
            } else {
                $campaign['is_target_reached'] = false;
            }
        } else {
            // Ongoing campaigns
            $campaign['progress_percentage'] = null;
            $campaign['remaining_amount'] = null;
            $campaign['is_target_reached'] = false;
        }

        // Calculate days remaining (only for target_based with deadline)
        if ($campaignType === 'target_based' && $campaign['deadline']) {
            $deadline = strtotime($campaign['deadline']);
            $now = time();
            $daysRemaining = max(0, ceil(($deadline - $now) / 86400));
            $campaign['days_remaining'] = $daysRemaining;
            $campaign['is_expired'] = $daysRemaining === 0;
        } else {
            // Ongoing campaigns don't have deadline
            $campaign['days_remaining'] = null;
            $campaign['is_expired'] = false;
        }

        return $campaign;
    }

    /**
     * Increment views count
     *
     * @param int $id
     * @return void
     */
    public function incrementViews(int $id): void
    {
        $this->builder()
            ->set('views_count', 'views_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Increment donors count
     *
     * @param int $id
     * @return void
     */
    public function incrementDonors(int $id): void
    {
        $this->builder()
            ->set('donors_count', 'donors_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Update current amount
     *
     * @param int $id
     * @param float $amount
     * @return void
     */
    public function addAmount(int $id, float $amount): void
    {
        $this->builder()
            ->set('current_amount', 'current_amount + ' . $amount, false)
            ->where('id', $id)
            ->update();

        // Auto-complete only for target_based campaigns when target reached
        $campaign = $this->find($id);
        if ($campaign && 
            $campaign['status'] === 'active' && 
            ($campaign['campaign_type'] ?? 'target_based') === 'target_based' &&
            $campaign['target_amount'] &&
            $campaign['current_amount'] >= $campaign['target_amount']) {
            $this->update($id, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }
        // Ongoing campaigns never auto-complete
    }
}

