<?php

namespace Modules\File\Models;

use Modules\Core\Models\BaseModel;

class FileModel extends BaseModel
{
    protected $table = 'files';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'original_name',
        'filename',
        'path',
        'full_path',
        'size',
        'mime_type',
        'extension',
        'tenant_id',
        'user_id',
        'folder',
        'type',
        'description',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'original_name' => 'required|max_length[255]',
        'filename' => 'required|max_length[255]',
        'tenant_id' => 'required|integer',
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
     * Get files by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }

        if (isset($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['folder'])) {
            $builder->where('folder', $filters['folder']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Find file by filename and tenant (with security check)
     *
     * @param string $filename
     * @param int $tenantId
     * @return array|null
     */
    public function findByTenantAndFilename(string $filename, int $tenantId): ?array
    {
        $file = $this->where('filename', $filename)
            ->where('tenant_id', $tenantId)
            ->first();

        return $file ?: null;
    }
}

