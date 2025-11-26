<?php

namespace Modules\Beneficiary\Models;

use Modules\Core\Models\BaseModel;

class BeneficiaryModel extends BaseModel
{
    protected $table = 'beneficiaries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'type',
        'name',
        'description',
        'identity_number',
        'address',
        'phone',
        'email',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'photo',
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
        'name' => 'required|max_length[255]',
        'tenant_id' => 'required|integer',
        'type' => 'in_list[individual,family,institution,school,project]',
        'status' => 'in_list[active,inactive]',
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
     * Get beneficiaries by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);
        $builder->where('status', 'active');

        if (isset($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        return $builder->get()->getResultArray();
    }
}

