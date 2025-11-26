<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
    public static function modulesCoreAuth($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('modulesCoreAuth');
        }
        return new \Modules\Core\Services\AuthService();
    }

    public static function modulesCoreTenant($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('modulesCoreTenant');
        }
        return new \Modules\Core\Services\TenantService();
    }

    public static function modulesCoreResponse($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('modulesCoreResponse');
        }
        return new \Modules\Core\Services\ResponseService();
    }

    public static function notification($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('notification');
        }
        return \Modules\Notification\Config\Services::notification(false);
    }

    public static function whatsapp($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('whatsapp');
        }
        return \Modules\Notification\Config\Services::whatsapp(false);
    }

    public static function storage($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('storage');
        }
        return \Modules\File\Config\Services::storage(false);
    }

    public static function activityLog($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('activityLog');
        }
        return \Modules\ActivityLog\Config\Services::activityLog(false);
    }

    public static function setting($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('setting');
        }
        return \Modules\Setting\Config\Services::setting(false);
    }

    public static function plan($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('plan');
        }
        return \Modules\Plan\Config\Services::plan(false);
    }

    public static function subscription($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('subscription');
        }
        return \Modules\Subscription\Config\Services::subscription(false);
    }

    public static function billing($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('billing');
        }
        return \Modules\Billing\Config\Services::billing(false);
    }

    public static function tenantManagement($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('tenantManagement');
        }
        return \Modules\Tenant\Config\Services::tenantManagement(false);
    }

    public static function campaign($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('campaign');
        }
        return \Modules\Campaign\Config\Services::campaign(false);
    }

    public static function donation($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('donation');
        }
        return \Modules\Donation\Config\Services::donation(false);
    }

    public static function beneficiary($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('beneficiary');
        }
        return \Modules\Beneficiary\Config\Services::beneficiary(false);
    }

    public static function withdrawal($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('withdrawal');
        }
        return \Modules\Withdrawal\Config\Services::withdrawal(false);
    }

    public static function campaignUpdate($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('campaignUpdate');
        }
        return \Modules\CampaignUpdate\Config\Services::campaignUpdate(false);
    }

    public static function helpdesk($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('helpdesk');
        }
        return \Modules\Helpdesk\Config\Services::helpdesk(false);
    }

    public static function report($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('report');
        }
        return \Modules\Report\Config\Services::report(false);
    }

    public static function analytics($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('analytics');
        }
        return \Modules\Analytics\Config\Services::analytics(false);
    }

    public static function export($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('export');
        }
        return \Modules\Export\Config\Services::export(false);
    }

    public static function discussion($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('discussion');
        }
        return \Modules\Discussion\Config\Services::discussion(false);
    }
}
