<?php

namespace Modules\Analytics\Config;

use CodeIgniter\Config\BaseService;
use Modules\Analytics\Services\AnalyticsService;

class Services extends BaseService
{
    public static function analytics(bool $getShared = true): AnalyticsService
    {
        if ($getShared) {
            return self::getSharedInstance('analytics');
        }
        return new AnalyticsService();
    }
}

