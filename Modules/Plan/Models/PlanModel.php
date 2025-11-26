<?php

namespace Modules\Plan\Models;

use Modules\Core\Models\BaseModel;

class PlanModel extends BaseModel
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'price',
        'description',
        'features',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[plans.name,id,{id}]',
        'price' => 'permit_empty|decimal',
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
     * Get all active plans
     *
     * @return array
     */
    public function getActivePlans(): array
    {
        return $this->orderBy('price', 'ASC')->findAll();
    }

    /**
     * Get plan by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $plan = $this->find($id);
        if ($plan) {
            $plan['features'] = json_decode($plan['features'] ?? '[]', true);
        }
        return $plan;
    }

    /**
     * Get plan by name
     *
     * @param string $name
     * @return array|null
     */
    public function getByName(string $name): ?array
    {
        $plan = $this->where('name', $name)->first();
        if ($plan) {
            $plan['features'] = json_decode($plan['features'] ?? '[]', true);
        }
        return $plan;
    }
}

