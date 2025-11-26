<?php

namespace Modules\Fundraiser\Models;

use CodeIgniter\Model;

class FundraiserApplicationModel extends Model
{
    protected $table = 'fundraiser_applications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'full_name',
        'phone',
        'ktp_document',
        'entity_type',
        'foundation_document',
        'youtube_channel',
        'instagram',
        'social_youtube',
        'twitter',
        'facebook',
        'reason',
        'status',
        'notes',
    ];
}

