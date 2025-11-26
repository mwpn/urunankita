<?php

namespace Modules\Content\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table            = 'menu_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tenant_id',
        'location',
        'parent_id',
        'label',
        'url',
        'icon',
        'order',
        'is_active',
        'is_external',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'label' => 'required|max_length[255]',
        'url'   => 'required|max_length[500]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get menu items for tenant or platform
     * 
     * @param int|null $tenantId NULL for platform menu, tenant_id for tenant menu
     * @param string $location 'header' or 'footer', default 'header'
     * @return array
     */
    public function getMenuItems(?int $tenantId = null, string $location = 'header'): array
    {
        $builder = $this->where('is_active', 1)
            ->where('location', $location);
        
        if ($tenantId === null) {
            // Platform menu (admin)
            $builder->where('tenant_id IS NULL');
        } else {
            // Tenant menu
            $builder->where('tenant_id', $tenantId);
        }
        
        return $builder->orderBy('order', 'ASC')
            ->findAll();
    }

    /**
     * Get menu items with nested structure (parent-child)
     * 
     * @param int|null $tenantId NULL for platform menu, tenant_id for tenant menu
     * @param bool $onlyActive Only return active items (for public display)
     * @param string $location 'header' or 'footer', default 'header'
     * @return array Hierarchical menu structure
     */
    public function getMenuItemsHierarchical(?int $tenantId = null, bool $onlyActive = true, string $location = 'header'): array
    {
        // Get all items (not filtered by is_active for admin view, filtered for public)
        $builder = $this->builder()
            ->where('location', $location);
        
        if ($tenantId === null) {
            $builder->where('tenant_id IS NULL');
        } else {
            $builder->where('tenant_id', $tenantId);
        }
        
        if ($onlyActive) {
            $builder->where('is_active', 1);
        }
        
        $allItems = $builder->orderBy('order', 'ASC')->get()->getResultArray();
        
        // Build hierarchical structure
        $menuTree = [];
        $itemsByParent = [];
        
        // Group items by parent_id
        foreach ($allItems as $item) {
            $parentId = $item['parent_id'] ?? null;
            if (!isset($itemsByParent[$parentId])) {
                $itemsByParent[$parentId] = [];
            }
            $itemsByParent[$parentId][] = $item;
        }
        
        // Build tree starting from root items (parent_id = null)
        if (isset($itemsByParent[null])) {
            foreach ($itemsByParent[null] as $rootItem) {
                $rootItem['children'] = $this->buildMenuChildren($rootItem['id'], $itemsByParent, $onlyActive);
                $menuTree[] = $rootItem;
            }
        }
        
        return $menuTree;
    }

    /**
     * Recursively build menu children
     * 
     * @param int $parentId
     * @param array $itemsByParent
     * @param bool $onlyActive
     * @return array
     */
    protected function buildMenuChildren(int $parentId, array $itemsByParent, bool $onlyActive = true): array
    {
        $children = [];
        
        if (isset($itemsByParent[$parentId])) {
            foreach ($itemsByParent[$parentId] as $child) {
                // Skip inactive children if filtering
                if ($onlyActive && isset($child['is_active']) && empty($child['is_active'])) {
                    continue;
                }
                $child['children'] = $this->buildMenuChildren($child['id'], $itemsByParent, $onlyActive);
                $children[] = $child;
            }
        }
        
        return $children;
    }

    /**
     * Get default menu items
     * 
     * @return array
     */
    public function getDefaultMenuItems(): array
    {
        return [
            [
                'label' => 'Beranda',
                'url' => '/',
                'icon' => null,
                'order' => 1,
                'is_external' => 0,
                'is_active' => 1,
            ],
            [
                'label' => 'Urunan',
                'url' => '/campaigns',
                'icon' => null,
                'order' => 2,
                'is_external' => 0,
                'is_active' => 1,
            ],
            [
                'label' => "What's New",
                'url' => '/articles',
                'icon' => null,
                'order' => 3,
                'is_external' => 0,
                'is_active' => 1,
            ],
        ];
    }
}

