<?php

namespace Modules\ActivityLog\Config;

use CodeIgniter\Config\BaseService;
use Modules\ActivityLog\Services\ActivityLogService;

class Services extends BaseService
{
    public static function activityLog(bool $getShared = true): ActivityLogService
    {
        if ($getShared) {
            return self::getSharedInstance('activityLog');
        }
        return new ActivityLogService();
    }
}

