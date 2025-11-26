<?php

namespace Modules\Discussion\Config;

use CodeIgniter\Config\BaseService;
use Modules\Discussion\Services\DiscussionService;

class Services extends BaseService
{
    public static function discussion(bool $getShared = true): DiscussionService
    {
        if ($getShared) {
            return self::getSharedInstance('discussion');
        }
        return new DiscussionService();
    }
}

