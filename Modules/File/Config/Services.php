<?php

namespace Modules\File\Config;

use CodeIgniter\Config\BaseService;
use Modules\File\Services\StorageService;

class Services extends BaseService
{
    public static function storage(bool $getShared = true): StorageService
    {
        if ($getShared) {
            return self::getSharedInstance('storage');
        }
        return new StorageService();
    }
}

