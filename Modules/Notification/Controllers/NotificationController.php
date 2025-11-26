<?php

namespace Modules\Notification\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Services;

class NotificationController extends BaseController
{
    protected function initialize(): void
    {
        parent::initialize();
        helper('Modules\\Notification\\Helpers\\notification');
    }

    /**
     * Send WhatsApp notification (example endpoint)
     * POST /notification/whatsapp/send
     */
    public function sendWhatsApp()
    {
        $to = $this->request->getPost('to');
        $message = $this->request->getPost('message');
        $type = $this->request->getPost('type') ?? 'general';

        if (empty($to) || empty($message)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phone number and message are required',
            ])->setStatusCode(400);
        }

        $notificationService = Services::notification();
        
        $result = $notificationService->sendWhatsApp($to, $message, [
            'type' => $type,
            'user_id' => auth_user()['id'] ?? null,
            'tenant_id' => session()->get('tenant_id'),
        ]);

        return $this->response->setJSON($result);
    }

    /**
     * Get notification logs
     * GET /notification/logs
     */
    public function getLogs()
    {
        $notificationService = Services::notification();
        
        $filters = [
            'tenant_id' => session()->get('tenant_id'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $logs = $notificationService->getLogs($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $logs,
        ]);
    }
}

