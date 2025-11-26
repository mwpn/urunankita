<?php

namespace Modules\Export\Config;

use CodeIgniter\Config\BaseService;
use Modules\Export\Services\ExportService;

class Services extends BaseService
{
    public static function export(bool $getShared = true): ExportService
    {
        if ($getShared) {
            return self::getSharedInstance('export');
        }
        return new ExportService();
    }
}

