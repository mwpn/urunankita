<?php

namespace Modules\Beneficiary\Services;

use Modules\Beneficiary\Models\BeneficiaryModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class BeneficiaryService
{
    protected BeneficiaryModel $beneficiaryModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->beneficiaryModel = new BeneficiaryModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create beneficiary (Penerima Urunan)
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            throw new \RuntimeException('Tenant not found');
        }

        $beneficiaryData = [
            'tenant_id' => $tenantId,
            'type' => $data['type'] ?? 'individual',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'identity_number' => $data['identity_number'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
            'photo' => $data['photo'] ?? null,
            'status' => 'active',
        ];

        $id = $this->beneficiaryModel->insert($beneficiaryData);

        if ($id) {
            $this->activityLog->logCreate('Beneficiary', $id, $beneficiaryData, 'Penerima Urunan dibuat');
        }

        return $id ?: false;
    }

    /**
     * Get beneficiaries by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        return $this->beneficiaryModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get beneficiary by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->beneficiaryModel->find($id);
    }

    /**
     * Update beneficiary
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $oldBeneficiary = $this->beneficiaryModel->find($id);
        if (!$oldBeneficiary) {
            return false;
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($oldBeneficiary['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        $updateData = [];
        
        $allowedFields = ['name', 'description', 'identity_number', 'address', 'phone', 'email', 
                         'bank_name', 'bank_account', 'bank_account_name', 'photo', 'type', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return false;
        }

        $result = $this->beneficiaryModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Beneficiary', $id, $oldBeneficiary, $updateData, 'Penerima Urunan diperbarui');
        }

        return $result;
    }

    /**
     * Delete beneficiary
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $beneficiary = $this->beneficiaryModel->find($id);
        if (!$beneficiary) {
            return false;
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($beneficiary['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        // Soft delete (set status to inactive)
        $result = $this->beneficiaryModel->update($id, ['status' => 'inactive']);

        if ($result) {
            $this->activityLog->logUpdate('Beneficiary', $id, $beneficiary, ['status' => 'inactive'], 'Penerima Urunan dinonaktifkan');
        }

        return $result;
    }
}

