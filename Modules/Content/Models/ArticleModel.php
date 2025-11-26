<?php

namespace Modules\Content\Models;

use Modules\Core\Models\BaseModel;

class ArticleModel extends BaseModel
{
    protected $table = 'articles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tenant_id',
        'campaign_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'image',
        'category',
        'author_id',
        'published',
        'views',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'slug' => 'required|max_length[255]|is_unique[articles.slug,id,{id}]',
        'content' => 'permit_empty',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get published articles for public view
     * 
     * @param int|null $tenantId Platform tenant ID or null for platform articles
     * @param array $options Options: limit, category, order_by
     * @return array
     */
    public function getPublishedArticles(?int $tenantId = null, array $options = []): array
    {
        // Use direct table builder to bypass BaseModel auto-filtering
        // This ensures proper tenant_id isolation based on the provided $tenantId parameter
        $builder = $this->db->table($this->table);
        
        if ($tenantId === null) {
            // Get platform articles (where tenant_id is NULL or platform tenant)
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
        
        $builder->where('published', 1);
        $builder->where('deleted_at IS NULL'); // Include soft delete check
        
        if (isset($options['category']) && !empty($options['category'])) {
            $builder->where('category', $options['category']);
        }
        
        $orderBy = $options['order_by'] ?? 'created_at';
        $orderDir = $options['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);
        
        if (isset($options['limit'])) {
            $limit = (int) $options['limit'];
            $offset = isset($options['offset']) ? (int) $options['offset'] : 0;
            $builder->limit($limit, $offset);
        }
        
        $articles = $builder->get()->getResultArray();
        
        // Enrich with author name if author_id exists
        foreach ($articles as &$article) {
            if (!empty($article['author_id'])) {
                $user = $this->db->table('users')
                    ->select('name')
                    ->where('id', $article['author_id'])
                    ->get()
                    ->getRowArray();
                $article['author_name'] = $user ? $user['name'] : 'Admin';
            } else {
                $article['author_name'] = 'Admin';
            }
        }
        
        return $articles;
    }

    /**
     * Get article by slug
     * 
     * @param string $slug
     * @param int|null $tenantId Platform tenant ID
     * @return array|null
     */
    public function getBySlug(string $slug, ?int $tenantId = null): ?array
    {
        // Use direct table builder to bypass BaseModel auto-filtering
        // This allows us to query articles from any tenant for public view
        $builder = $this->db->table($this->table);
        
        if ($tenantId === null) {
            $platform = $this->db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $tenantId = $platform ? (int) $platform['id'] : null;
        }
        
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        } else {
            $builder->where('tenant_id IS NULL');
        }
        
        $article = $builder->where('slug', $slug)
            ->where('published', 1)
            ->where('deleted_at IS NULL') // Include soft delete check
            ->get()
            ->getRowArray();
        
        if ($article) {
            // Increment views - use direct update to bypass tenant filter
            $this->db->table($this->table)
                ->where('id', $article['id'])
                ->update(['views' => ($article['views'] ?? 0) + 1]);
            $article['views'] = ($article['views'] ?? 0) + 1;
            
            // Enrich with author name
            if (!empty($article['author_id'])) {
                $user = $this->db->table('users')
                    ->select('name')
                    ->where('id', $article['author_id'])
                    ->get()
                    ->getRowArray();
                $article['author_name'] = $user ? $user['name'] : 'Admin';
            } else {
                $article['author_name'] = 'Admin';
            }
        }
        
        return $article;
    }

    /**
     * Get all articles for admin (bypass tenant filtering)
     * 
     * @param int|null $tenantId Platform tenant ID
     * @return array
     */
    public function getAllArticles(?int $tenantId = null): array
    {
        $builder = $this->db->table($this->table);
        
        if ($tenantId === null) {
            $platform = $this->db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            $tenantId = $platform ? (int) $platform['id'] : null;
        }
        
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        } else {
            $builder->where('tenant_id IS NULL');
        }
        
        // Filter out soft-deleted articles
        $builder->where('deleted_at IS NULL');
        
        $builder->orderBy('created_at', 'DESC');
        
        $articles = $builder->get()->getResultArray();
        
        // Enrich with author name
        foreach ($articles as &$article) {
            if (!empty($article['author_id'])) {
                $user = $this->db->table('users')
                    ->select('name')
                    ->where('id', $article['author_id'])
                    ->get()
                    ->getRowArray();
                $article['author_name'] = $user ? $user['name'] : 'Admin';
            } else {
                $article['author_name'] = 'Admin';
            }
        }
        
        return $articles;
    }
}

