<?php

namespace Modules\Subscription\Config;

use CodeIgniter\Config\BaseService;
use Modules\Subscription\Services\SubscriptionService;

class Services extends BaseService
{
    public static function subscription(bool $getShared = true): SubscriptionService
    {
        if ($getShared) {
            return self::getSharedInstance('subscription');
        }
        return new SubscriptionService();
    }
}

