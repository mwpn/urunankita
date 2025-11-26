<?php

namespace Modules\Setting\Models;

use Modules\Core\Models\BaseModel;

class PaymentMethodModel extends BaseModel
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'code',
        'name',
        'type',
        'enabled',
        'description',
        'provider',
        'admin_fee_percent',
        'admin_fee_fixed',
        'require_verification',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'tenant_id' => 'required|integer',
        'code' => 'required|max_length[100]',
        'name' => 'required|max_length[255]',
        'type' => 'permit_empty|max_length[50]',
        'enabled' => 'permit_empty|in_list[0,1]',
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
     * Get active payment methods for a tenant
     *
     * @param int $tenantId
     * @return array
     */
    public function getActiveByTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('enabled', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all payment methods for a tenant (including disabled)
     *
     * @param int $tenantId
     * @return array
     */
    public function getByTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get payment method by code and tenant
     *
     * @param string $code
     * @param int $tenantId
     * @return array|null
     */
    public function getByCode(string $code, int $tenantId): ?array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();
    }
}

