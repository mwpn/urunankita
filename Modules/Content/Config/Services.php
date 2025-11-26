<?php

namespace Modules\Content\Config;

use CodeIgniter\Config\BaseService;
use Modules\Content\Services\ContentService;

class Services extends BaseService
{
    public static function content(bool $getShared = true): ContentService
    {
        if ($getShared) {
            return static::getSharedInstance('content');
        }

        return new ContentService();
    }
}

