<?php

namespace Modules\Content\Models;

use Modules\Core\Models\BaseModel;

class PageModel extends BaseModel
{
    protected $table            = 'pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tenant_id',
        'title',
        'slug',
        'content',
        'description',
        'badge_text',
        'subtitle',
        'sidebar_content',
        'published',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'slug'  => 'required|max_length[255]|is_unique[pages.slug,id,{id}]',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind       = [];
    protected $beforeDelete    = [];
    protected $afterDelete     = [];

    /**
     * Get page by slug
     */
    public function getBySlug(string $slug, ?int $tenantId = null): ?array
    {
        $builder = $this->builder();
        
        if ($tenantId !== null) {
            $builder->where('tenant_id', $tenantId);
        } else {
            // For platform pages, tenant_id should be platform tenant ID or NULL
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if ($platform) {
                $builder->where('tenant_id', $platform['id']);
            }
        }
        
        $builder->where('slug', $slug);
        $builder->where('published', 1);
        
        $result = $builder->get()->getRowArray();
        return $result ?: null;
    }

    /**
     * Get all pages for admin
     * Bypass BaseModel tenant filtering for admin view
     */
    public function getAllPages(?int $tenantId = null): array
    {
        // Use builder directly to bypass BaseModel tenant filtering
        $builder = $this->db->table($this->table);
        
        if ($tenantId !== null) {
            $builder->where('tenant_id', $tenantId);
        }
        
        $builder->where('deleted_at', null); // Only non-deleted pages
        $builder->orderBy('created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
}

