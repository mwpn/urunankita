<?php

namespace Modules\ActivityLog\Models;

use Modules\Core\Models\BaseModel;

class ActivityLogModel extends BaseModel
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'user_id',
        'action',
        'entity',
        'entity_id',
        'old_value',
        'new_value',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'action' => 'required|max_length[50]',
        'tenant_id' => 'integer',
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
     * Get logs with filters and tenant isolation
     *
     * @param array $filters
     * @return array
     */
    public function getLogs(array $filters = []): array
    {
        $builder = $this->builder();

        // Tenant isolation - mandatory
        $tenantId = $filters['tenant_id'] ?? session()->get('tenant_id');
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        // User filter
        if (isset($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }

        // Action filter
        if (isset($filters['action'])) {
            $builder->where('action', $filters['action']);
        }

        // Entity filter
        if (isset($filters['entity'])) {
            $builder->where('entity', $filters['entity']);
        }

        // Entity ID filter
        if (isset($filters['entity_id'])) {
            $builder->where('entity_id', $filters['entity_id']);
        }

        // Date range filter
        if (isset($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }

        // Limit
        $limit = $filters['limit'] ?? 100;
        $offset = $filters['offset'] ?? 0;

        $builder->orderBy('created_at', 'DESC');
        $builder->limit($limit, $offset);

        $logs = $builder->get()->getResultArray();

        // Decode JSON fields
        foreach ($logs as &$log) {
            if ($log['old_value']) {
                $log['old_value'] = json_decode($log['old_value'], true);
            }
            if ($log['new_value']) {
                $log['new_value'] = json_decode($log['new_value'], true);
            }
            if ($log['metadata']) {
                $log['metadata'] = json_decode($log['metadata'], true);
            }
        }

        return $logs;
    }

    /**
     * Get logs count with filters
     *
     * @param array $filters
     * @return int
     */
    public function getLogsCount(array $filters = []): int
    {
        $builder = $this->builder();

        $tenantId = $filters['tenant_id'] ?? session()->get('tenant_id');
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        if (isset($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        if (isset($filters['action'])) {
            $builder->where('action', $filters['action']);
        }
        if (isset($filters['entity'])) {
            $builder->where('entity', $filters['entity']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get activity summary
     *
     * @param array $filters
     * @return array
     */
    public function getSummary(array $filters = []): array
    {
        $tenantId = $filters['tenant_id'] ?? session()->get('tenant_id');
        
        $builder = $this->builder();
        $builder->select('action, COUNT(*) as count');
        $builder->where('tenant_id', $tenantId);
        $builder->groupBy('action');
        $builder->orderBy('count', 'DESC');

        return $builder->get()->getResultArray();
    }
}

