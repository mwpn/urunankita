<?php

namespace Modules\CampaignUpdate\Config;

use CodeIgniter\Config\BaseService;
use Modules\CampaignUpdate\Services\CampaignUpdateService;

class Services extends BaseService
{
    public static function campaignUpdate(bool $getShared = true): CampaignUpdateService
    {
        if ($getShared) {
            return self::getSharedInstance('campaignUpdate');
        }
        return new CampaignUpdateService();
    }
}

