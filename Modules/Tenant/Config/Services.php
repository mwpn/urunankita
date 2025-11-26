<?php

namespace Modules\Tenant\Config;

use CodeIgniter\Config\BaseService;
use Modules\Tenant\Services\TenantService;

class Services extends BaseService
{
    public static function tenantManagement(bool $getShared = true): TenantService
    {
        if ($getShared) {
            return self::getSharedInstance('tenantManagement');
        }
        return new TenantService();
    }
}

