<?php

namespace Modules\Beneficiary\Config;

use CodeIgniter\Config\BaseService;
use Modules\Beneficiary\Services\BeneficiaryService;

class Services extends BaseService
{
    public static function beneficiary(bool $getShared = true): BeneficiaryService
    {
        if ($getShared) {
            return self::getSharedInstance('beneficiary');
        }
        return new BeneficiaryService();
    }
}

