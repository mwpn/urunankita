<?php

namespace Modules\Setting\Models;

use Modules\Core\Models\BaseModel;

class SettingModel extends BaseModel
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'key',
        'value',
        'type',
        'scope',
        'scope_id',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'key' => 'required|max_length[255]',
        'scope' => 'required|in_list[global,tenant,user]',
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
     * Get setting by key and scope
     *
     * @param string $key
     * @param string $scope
     * @param int|null $scopeId
     * @return array|null
     */
    public function getSetting(string $key, string $scope, ?int $scopeId = null): ?array
    {
        $builder = $this->builder();
        $builder->where('key', $key);
        $builder->where('scope', $scope);

        if ($scope === 'global') {
            $builder->where('scope_id IS NULL');
        } else {
            $builder->where('scope_id', $scopeId);
        }

        $setting = $builder->get()->getRowArray();
        return $setting ?: null;
    }

    /**
     * Get all settings by scope
     *
     * @param string $scope
     * @param int|null $scopeId
     * @return array
     */
    public function getByScope(string $scope, ?int $scopeId = null): array
    {
        $builder = $this->builder();
        $builder->where('scope', $scope);

        if ($scope === 'global') {
            $builder->where('scope_id IS NULL');
        } else {
            $builder->where('scope_id', $scopeId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get settings with tenant isolation
     *
     * @param array $filters
     * @return array
     */
    public function getSettings(array $filters = []): array
    {
        $builder = $this->builder();

        if (isset($filters['scope'])) {
            $builder->where('scope', $filters['scope']);
        }

        if (isset($filters['scope_id'])) {
            if ($filters['scope'] === 'global') {
                $builder->where('scope_id IS NULL');
            } else {
                $builder->where('scope_id', $filters['scope_id']);
            }
        }

        if (isset($filters['key'])) {
            $builder->where('key', $filters['key']);
        }

        $builder->orderBy('key', 'ASC');

        return $builder->get()->getResultArray();
    }
}

