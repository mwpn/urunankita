<?php

namespace Modules\Helpdesk\Models;

use Modules\Core\Models\BaseModel;

class TicketCategoryModel extends BaseModel
{
    protected $table = 'ticket_categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'is_active' => 'in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get active categories
     *
     * @return array
     */
    public function getActiveCategories(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}

