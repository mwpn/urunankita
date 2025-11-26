<?php

namespace Modules\Content\Models;

use Modules\Core\Models\BaseModel;

class BannerModel extends BaseModel
{
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'title',
        'image',
        'link',
        'order',
        'active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'image' => 'required|max_length[500]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get all active banners for a tenant (or platform)
     * 
     * @param int|null $tenantId Platform tenant ID or null for platform banners
     * @return array
     */
    public function getActiveBanners(?int $tenantId = null): array
    {
        $builder = $this->builder();
        
        if ($tenantId === null) {
            // Get platform banners (where tenant_id is NULL or platform tenant)
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
        
        $builder->where('active', 1)
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get all banners for admin (bypass tenant filtering)
     * 
     * @param int|null $tenantId Platform tenant ID
     * @return array
     */
    public function getAllBanners(?int $tenantId = null): array
    {
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
        
        $builder->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}

