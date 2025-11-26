<?php

namespace Modules\Campaign\Config;

use CodeIgniter\Config\BaseService;
use Modules\Campaign\Services\CampaignService;

class Services extends BaseService
{
    public static function campaign(bool $getShared = true): CampaignService
    {
        if ($getShared) {
            return self::getSharedInstance('campaign');
        }
        return new CampaignService();
    }
}

