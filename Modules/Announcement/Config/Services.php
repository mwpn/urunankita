<?php

namespace Modules\Announcement\Config;

use CodeIgniter\Config\BaseService;
use Modules\Announcement\Services\AnnouncementService;

class Services extends BaseService
{
    public static function announcement(bool $getShared = true): AnnouncementService
    {
        if ($getShared) {
            return self::getSharedInstance('announcement');
        }
        return new AnnouncementService();
    }
}

