<?php

namespace Modules\Core\Config;

use CodeIgniter\Config\BaseService;
use Modules\Core\Services\AuthService;
use Modules\Core\Services\TenantService;
use Modules\Core\Services\ResponseService;

class Services extends BaseService
{
    public static function modulesCoreAuth(bool $getShared = true): AuthService
    {
        if ($getShared) {
            return self::getSharedInstance('modulesCoreAuth');
        }
        return new AuthService();
    }

    public static function modulesCoreTenant(bool $getShared = true): TenantService
    {
        if ($getShared) {
            return self::getSharedInstance('modulesCoreTenant');
        }
        return new TenantService();
    }

    public static function modulesCoreResponse(bool $getShared = true): ResponseService
    {
        if ($getShared) {
            return self::getSharedInstance('modulesCoreResponse');
        }
        return new ResponseService();
    }
}


