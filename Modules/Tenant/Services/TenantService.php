<?php

namespace Modules\Tenant\Services;

use Modules\Tenant\Models\TenantModel;
use Modules\Core\Services\TenantService as CoreTenantService;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;
use Config\Database;

class TenantService
{
    protected TenantModel $tenantModel;
    protected CoreTenantService $coreTenantService;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        $this->coreTenantService = BaseServices::modulesCoreTenant();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create tenant
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $slug = $data['slug'] ?? $this->generateSlug($data['name']);

        // Simplified: No need to create separate database anymore
        // All data will be stored in single database with tenant_id

        // Insert tenant record
        $tenantData = [
            'name' => $data['name'],
            'slug' => $slug,
            'db_name' => null, // No longer needed, but keep for backward compatibility
            'domain' => $data['domain'] ?? null,
            'owner_id' => $data['owner_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ];

        $id = $this->tenantModel->insert($tenantData);

        if ($id) {
            // Migrations are now run globally, not per-tenant
            // All tables already exist in central database

            $this->activityLog->logCreate('Tenant', $id, $tenantData, 'Tenant created');
        }

        return $id ?: false;
    }

    /**
     * Get all tenants
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $builder = $this->tenantModel->builder();

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get tenant by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->tenantModel->find($id);
    }

    /**
     * Get tenant by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->tenantModel->getBySlug($slug);
    }

    /**
     * Update tenant
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $oldTenant = $this->tenantModel->find($id);
        if (!$oldTenant) {
            return false;
        }

        $updateData = [];
        
        // Update all allowed fields
        $allowedFields = [
            'name',
            'slug',
            'domain',
            'status',
            'owner_id',
            'youtube_url',
            'can_create_without_verification',
            'can_use_own_bank_account',
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // Handle bank_accounts separately (needs JSON encoding)
        if (isset($data['bank_accounts'])) {
            $updateData['bank_accounts'] = is_array($data['bank_accounts']) 
                ? json_encode($data['bank_accounts']) 
                : $data['bank_accounts'];
        }

        $result = $this->tenantModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Tenant', $id, $oldTenant, $updateData, 'Tenant updated');
        }

        return $result;
    }

    /**
     * Delete tenant
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $tenant = $this->tenantModel->find($id);
        if (!$tenant) {
            return false;
        }

        // Don't allow deletion if tenant has data
        // You can add more checks here

        $result = $this->tenantModel->delete($id);

        if ($result) {
            // Optionally drop database (commented for safety)
            // $db = Database::connect();
            // $db->query("DROP DATABASE IF EXISTS `{$tenant['db_name']}`");

            $this->activityLog->logDelete('Tenant', $id, $tenant, 'Tenant deleted');
        }

        return $result;
    }

    /**
     * Generate slug from name
     *
     * @param string $name
     * @return string
     */
    protected function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while ($this->tenantModel->getBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

