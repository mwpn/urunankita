<?php

namespace Modules\Core\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;

/**
 * BaseModel - Simplified for single database architecture
 * 
 * All models now use the default (central) database.
 * Tenant isolation is achieved by filtering with tenant_id in queries.
 */
class BaseModel extends Model
{
    /**
     * Cached tenant_id for callbacks
     */
    protected ?int $tenantFilterTenantId = null;
    /**
     * Override constructor - now simplified for single database
     * All models use default database connection
     */
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        // Use default database connection (no tenant DB switching)
        parent::__construct($db, $validation);
        
        // Ensure all queries filter by tenant_id when tenant context exists
        $this->addTenantFilter();
    }

    /**
     * Automatically filter by tenant_id if tenant context exists
     * Override in child models if needed
     */
    protected function addTenantFilter()
    {
        $tenantId = session()->get('tenant_id');
        $userRole = session()->get('auth_user')['role'] ?? null;

        // Don't filter for super_admin or if no tenant_id in session (admin context)
        if (!$tenantId || $userRole === 'super_admin') {
            return;
        }

        if ($this->hasTenantId()) {
            $this->tenantFilterTenantId = (int) $tenantId;
            // Register callback by method name (CodeIgniter expects string)
            $this->beforeFind[] = 'applyTenantFilterCallback';
        }
    }

    /**
     * Model callback: apply tenant filter before find queries
     *
     * @param array $data
     * @return array
     */
    protected function applyTenantFilterCallback(array $data): array
    {
        if (!isset($data['builder'])) {
            return $data;
        }
        $tenantId = $this->tenantFilterTenantId;
        if ($tenantId && $this->hasTenantId()) {
            $data['builder']->where($this->table . '.tenant_id', $tenantId);
        }
        return $data;
    }

    /**
     * Check if this model's table has tenant_id column
     * Override in child models if needed
     */
    protected function hasTenantId(): bool
    {
        // Check if table exists and has tenant_id column
        if (empty($this->table)) {
            return false;
        }

        try {
            $fields = $this->db->getFieldData($this->table);
            foreach ($fields as $field) {
                if ($field->name === 'tenant_id') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return false;
    }

    /**
     * Override find() to ensure tenant_id filtering
     */
    public function find($id = null)
    {
        $tenantId = session()->get('tenant_id');
        $userRole = session()->get('auth_user')['role'] ?? null;
        
        // Don't filter for super_admin or if no tenant_id in session (admin context)
        if ($tenantId && $userRole !== 'super_admin' && $this->hasTenantId()) {
            $this->where('tenant_id', $tenantId);
        }

        return parent::find($id);
    }

    /**
     * Override findAll() to ensure tenant_id filtering
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $tenantId = session()->get('tenant_id');
        $userRole = session()->get('auth_user')['role'] ?? null;
        
        // Don't filter for super_admin or if no tenant_id in session (admin context)
        if ($tenantId && $userRole !== 'super_admin' && $this->hasTenantId()) {
            $this->where('tenant_id', $tenantId);
        }

        return parent::findAll($limit, $offset);
    }

    /**
     * Override insert() to automatically set tenant_id
     */
    public function insert($data = null, bool $returnID = true)
    {
        $tenantId = session()->get('tenant_id');
        if ($tenantId && $this->hasTenantId() && is_array($data)) {
            if (!isset($data['tenant_id'])) {
                $data['tenant_id'] = $tenantId;
            }
        }

        return parent::insert($data, $returnID);
    }

    /**
     * Override update() to ensure tenant_id filtering
     */
    public function update($id = null, $data = null): bool
    {
        $tenantId = session()->get('tenant_id');
        $userRole = session()->get('auth_user')['role'] ?? null;
        
        // Don't filter for super_admin or if no tenant_id in session (admin context)
        if ($tenantId && $userRole !== 'super_admin' && $this->hasTenantId()) {
            $this->where('tenant_id', $tenantId);
        }

        return parent::update($id, $data);
    }

    /**
     * Override delete() to ensure tenant_id filtering
     */
    public function delete($id = null, bool $purge = false)
    {
        $tenantId = session()->get('tenant_id');
        $userRole = session()->get('auth_user')['role'] ?? null;
        
        // Don't filter for super_admin or if no tenant_id in session (admin context)
        if ($tenantId && $userRole !== 'super_admin' && $this->hasTenantId()) {
            $this->where('tenant_id', $tenantId);
        }

        return parent::delete($id, $purge);
    }
}


