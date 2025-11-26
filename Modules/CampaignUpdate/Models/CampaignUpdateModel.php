<?php

namespace Modules\CampaignUpdate\Models;

use Modules\Core\Models\BaseModel;

class CampaignUpdateModel extends BaseModel
{
    protected $table = 'campaign_updates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'campaign_id',
        'tenant_id',
        'title',
        'content',
        'amount_used',
        'images',
        'youtube_url',
        'author_id',
        'is_pinned',
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
        'content' => 'required',
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
     * Get updates by campaign
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getByCampaign(int $campaignId, array $filters = []): array
    {
        // Use db directly to bypass BaseModel tenant filtering for public view
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $builder->where('campaign_id', $campaignId);

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        // Pinned first, then by date
        $builder->orderBy('is_pinned', 'DESC');
        $builder->orderBy('created_at', 'DESC');

        $updates = $builder->get()->getResultArray();

        // Parse images JSON
        foreach ($updates as &$update) {
            if (!empty($update['images'])) {
                $decoded = json_decode($update['images'], true);
                $update['images'] = is_array($decoded) ? $decoded : [];
            } else {
                $update['images'] = [];
            }
        }

        return $updates;
    }

    /**
     * Get updates by tenant
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

        $builder->orderBy('created_at', 'DESC');

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $updates = $builder->get()->getResultArray();

        // Parse images JSON
        foreach ($updates as &$update) {
            if ($update['images']) {
                $update['images'] = json_decode($update['images'], true) ?? [];
            } else {
                $update['images'] = [];
            }
        }

        return $updates;
    }
}

