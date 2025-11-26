<?php

namespace Modules\Announcement\Models;

use Modules\Core\Models\BaseModel;

class AnnouncementModel extends BaseModel
{
    protected $table = 'announcements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'title',
        'content',
        'type',
        'priority',
        'is_published',
        'published_at',
        'expires_at',
        'created_by',
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
        'content' => 'required',
        'type' => 'in_list[info,warning,success,error]',
        'priority' => 'in_list[low,normal,high,urgent]',
        'is_published' => 'permit_empty|in_list[0,1]',
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
     * Get published announcements (for all tenants)
     *
     * @param int|null $limit
     * @return array
     */
    public function getPublished(?int $limit = null): array
    {
        $builder = $this->where('is_published', 1);
        
        // Only show non-expired announcements
        $builder->groupStart()
            ->where('expires_at IS NULL')
            ->orWhere('expires_at >', date('Y-m-d H:i:s'))
        ->groupEnd();

        $builder->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get all announcements (admin)
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $builder = $this->builder();

        if (isset($filters['is_published'])) {
            $builder->where('is_published', $filters['is_published']);
        }

        if (isset($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }

        $builder->orderBy('created_at', 'DESC');

        return $builder->get()->getResultArray();
    }
}

