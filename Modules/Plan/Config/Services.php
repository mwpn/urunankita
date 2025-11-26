<?php

namespace Modules\Plan\Config;

use CodeIgniter\Config\BaseService;
use Modules\Plan\Services\PlanService;

class Services extends BaseService
{
    public static function plan(bool $getShared = true): PlanService
    {
        if ($getShared) {
            return self::getSharedInstance('plan');
        }
        return new PlanService();
    }
}

