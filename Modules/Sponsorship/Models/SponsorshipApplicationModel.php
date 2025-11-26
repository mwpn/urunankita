<?php

namespace Modules\Sponsorship\Models;

use CodeIgniter\Model;

class SponsorshipApplicationModel extends Model
{
    protected $table = 'sponsorship_applications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'company_name',
        'pic_name',
        'pic_position',
        'email',
        'phone',
        'website',
        'address',
        'sponsor_type',
        'amount',
        'description',
        'categories',
        'public_visibility',
        'logo',
        'website_link',
        'reason',
        'expectations',
        'special_terms',
        'partnership_letter',
        'company_profile',
        'status',
        'notes',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}

