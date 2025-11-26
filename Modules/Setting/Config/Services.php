<?php

namespace Modules\Setting\Config;

use CodeIgniter\Config\BaseService;
use Modules\Setting\Services\SettingService;

class Services extends BaseService
{
    public static function setting(bool $getShared = true): SettingService
    {
        if ($getShared) {
            return self::getSharedInstance('setting');
        }
        return new SettingService();
    }
}

