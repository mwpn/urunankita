<?php

namespace Modules\Notification\Config;

use CodeIgniter\Config\BaseService;
use Modules\Notification\Services\NotificationService;
use Modules\Notification\Services\WhatsAppService;
use Modules\Notification\Services\MessageTemplateService;

class Services extends BaseService
{
    public static function notification(bool $getShared = true): NotificationService
    {
        if ($getShared) {
            return self::getSharedInstance('notification');
        }
        return new NotificationService();
    }

    public static function whatsapp(bool $getShared = true): WhatsAppService
    {
        if ($getShared) {
            return self::getSharedInstance('whatsapp');
        }
        return new WhatsAppService();
    }

    public static function messageTemplate(bool $getShared = true): MessageTemplateService
    {
        if ($getShared) {
            // Try to get shared instance first
            $instance = self::getSharedInstance('messageTemplate');
            
            // If not found, create new instance and register it
            // Note: getSharedInstance may return null if instance not registered yet
            // In that case, we create a new instance and register it
            if ($instance === null) {
                $instance = new MessageTemplateService();
                self::setSharedInstance('messageTemplate', $instance);
            }
            
            return $instance;
        }
        return new MessageTemplateService();
    }
}

