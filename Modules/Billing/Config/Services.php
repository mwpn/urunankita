<?php

namespace Modules\Billing\Config;

use CodeIgniter\Config\BaseService;
use Modules\Billing\Services\BillingService;

class Services extends BaseService
{
    public static function billing(bool $getShared = true): BillingService
    {
        if ($getShared) {
            return self::getSharedInstance('billing');
        }
        return new BillingService();
    }
}

