<?php

namespace Modules\Donation\Config;

use CodeIgniter\Config\BaseService;
use Modules\Donation\Services\DonationService;

class Services extends BaseService
{
    public static function donation(bool $getShared = true): DonationService
    {
        if ($getShared) {
            return self::getSharedInstance('donation');
        }
        return new DonationService();
    }
}

