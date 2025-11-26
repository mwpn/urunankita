<?php

namespace Modules\Notification\Services;

use Modules\Notification\Models\NotificationLogModel;

class NotificationService
{
    protected WhatsAppService $whatsappService;
    protected ?NotificationLogModel $logModel = null;

    public function __construct()
    {
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Send WhatsApp notification and log it
     *
     * @param string $to Phone number
     * @param string $message Message text
     * @param array $context Additional context (user_id, tenant_id, type, etc)
     * @return array
     */
    public function sendWhatsApp(string $to, string $message, array $context = []): array
    {
        log_message('debug', 'NotificationService::sendWhatsApp called');
        log_message('debug', 'To: ' . $to);
        log_message('debug', 'Message length: ' . strlen($message));
        log_message('debug', 'WhatsApp service configured: ' . ($this->whatsappService->isConfigured() ? 'yes' : 'no'));
        
        if (!$this->whatsappService->isConfigured()) {
            log_message('error', 'WhatsApp service is not configured');
            return [
                'success' => false,
                'message' => 'WhatsApp service is not configured',
            ];
        }

        // Send message
        $result = $this->whatsappService->sendText($to, $message);
        
        log_message('debug', 'WhatsApp sendText result: ' . json_encode($result));

        // Log notification
        $this->log($to, $message, 'whatsapp', $result, $context);

        return $result;
    }

    /**
     * Send bulk WhatsApp notifications
     *
     * @param array $recipients Array of phone numbers
     * @param string $message Message text
     * @param array $context Additional context
     * @return array
     */
    public function sendBulkWhatsApp(array $recipients, string $message, array $context = []): array
    {
        if (!$this->whatsappService->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp service is not configured',
            ];
        }

        $results = $this->whatsappService->sendBulk($recipients, $message);

        // Log each notification
        foreach ($results as $result) {
            $this->log(
                $result['recipient'],
                $message,
                'whatsapp',
                $result['result'],
                $context
            );
        }

        return $results;
    }

    /**
     * Log notification to database
     *
     * @param string $recipient
     * @param string $message
     * @param string $channel (whatsapp, email, sms)
     * @param array $result
     * @param array $context
     * @return void
     */
    protected function log(string $recipient, string $message, string $channel, array $result, array $context = []): void
    {
        try {
            $logModel = $this->getLogModel();
            
            if ($logModel) {
                $logModel->insert([
                    'recipient' => $recipient,
                    'channel' => $channel,
                    'message' => $message,
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'response' => json_encode($result),
                    'user_id' => $context['user_id'] ?? null,
                    'tenant_id' => $context['tenant_id'] ?? session()->get('tenant_id'),
                    'type' => $context['type'] ?? 'general',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently - logging should not break the flow
            log_message('error', 'Failed to log notification: ' . $e->getMessage());
        }
    }

    /**
     * Get notification log model (lazy load)
     *
     * @return NotificationLogModel|null
     */
    protected function getLogModel(): ?NotificationLogModel
    {
        if ($this->logModel === null) {
            try {
                $this->logModel = new NotificationLogModel();
            } catch (\Exception $e) {
                // Model might not exist yet, return null
                return null;
            }
        }

        return $this->logModel;
    }

    /**
     * Get notification logs
     *
     * @param array $filters
     * @return array
     */
    public function getLogs(array $filters = []): array
    {
        try {
            $logModel = $this->getLogModel();
            
            if (!$logModel) {
                return [];
            }

            $builder = $logModel->builder();

            if (isset($filters['tenant_id'])) {
                $builder->where('tenant_id', $filters['tenant_id']);
            }

            if (isset($filters['user_id'])) {
                $builder->where('user_id', $filters['user_id']);
            }

            if (isset($filters['channel'])) {
                $builder->where('channel', $filters['channel']);
            }

            if (isset($filters['status'])) {
                $builder->where('status', $filters['status']);
            }

            if (isset($filters['limit'])) {
                $builder->limit($filters['limit']);
            }

            $builder->orderBy('created_at', 'DESC');

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}

