<?php

namespace Modules\Content\Models;

use Modules\Core\Models\BaseModel;

class SponsorModel extends BaseModel
{
    protected $table = 'sponsors';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'name',
        'logo',
        'website',
        'order',
        'active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'logo' => 'required|max_length[500]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all active sponsors for a tenant (or platform)
     * Excludes soft-deleted records
     * 
     * @param int|null $tenantId Platform tenant ID or null for platform sponsors
     * @return array
     */
    public function getActiveSponsors(?int $tenantId = null): array
    {
        // Use direct query to ensure soft-deleted records are excluded
        $builder = $this->db->table($this->table);
        
        if ($tenantId === null) {
            // Get platform sponsors (where tenant_id is NULL or platform tenant)
            $platform = $this->db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $platformTenantId = $platform ? (int) $platform['id'] : null;
            
            if ($platformTenantId) {
                $builder->where('tenant_id', $platformTenantId);
            } else {
                $builder->where('tenant_id IS NULL');
            }
        } else {
            $builder->where('tenant_id', $tenantId);
        }
        
        // Explicitly exclude soft-deleted records and only get active ones
        $builder->where('deleted_at IS NULL')
            ->where('active', 1)
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get all sponsors for admin (bypass tenant filtering)
     * Excludes soft-deleted records
     * 
     * @param int|null $tenantId Platform tenant ID
     * @return array
     */
    public function getAllSponsors(?int $tenantId = null): array
    {
        // Use direct query to ensure soft-deleted records are excluded
        $builder = $this->db->table($this->table);
        
        if ($tenantId === null) {
            // Get platform tenant ID
            $platform = $this->db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $tenantId = $platform ? (int) $platform['id'] : null;
        }
        
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        } else {
            $builder->where('tenant_id IS NULL');
        }
        
        // Explicitly exclude soft-deleted records
        $builder->where('deleted_at IS NULL');
        
        $builder->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}

