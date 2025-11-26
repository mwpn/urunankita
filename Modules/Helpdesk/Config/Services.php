<?php

namespace Modules\Helpdesk\Config;

use CodeIgniter\Config\BaseService;
use Modules\Helpdesk\Services\HelpdeskService;

class Services extends BaseService
{
    public static function helpdesk(bool $getShared = true): HelpdeskService
    {
        if ($getShared) {
            return self::getSharedInstance('helpdesk');
        }
        return new HelpdeskService();
    }
}

