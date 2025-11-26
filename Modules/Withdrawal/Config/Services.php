<?php

namespace Modules\Withdrawal\Config;

use CodeIgniter\Config\BaseService;
use Modules\Withdrawal\Services\WithdrawalService;

class Services extends BaseService
{
    public static function withdrawal(bool $getShared = true): WithdrawalService
    {
        if ($getShared) {
            return self::getSharedInstance('withdrawal');
        }
        return new WithdrawalService();
    }
}

