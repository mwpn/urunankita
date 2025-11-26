<?php

namespace Modules\Report\Config;

use CodeIgniter\Config\BaseService;
use Modules\Report\Services\ReportService;

class Services extends BaseService
{
    public static function report(bool $getShared = true): ReportService
    {
        if ($getShared) {
            return self::getSharedInstance('report');
        }
        return new ReportService();
    }
}

