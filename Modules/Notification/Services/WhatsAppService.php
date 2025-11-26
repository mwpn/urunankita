<?php

namespace Modules\Notification\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $fromNumber;
    protected CURLRequest $client;

    public function __construct()
    {
        // Try to get from settings database first, fallback to env
        $settingService = Services::setting();
        
        $apiUrl = $settingService->get('whatsapp_api_url', null, 'global', null);
        $this->apiUrl = !empty($apiUrl) ? (string) $apiUrl 
            : env('whatsapp.api_url', 'https://app.whappi.biz.id/api/qr/rest/send_message');
        
        $apiToken = $settingService->get('whatsapp_api_token', null, 'global', null);
        $this->apiToken = !empty($apiToken) ? (string) $apiToken 
            : env('whatsapp.api_token', '');
        
        $fromNumber = $settingService->get('whatsapp_from_number', null, 'global', null);
        $this->fromNumber = !empty($fromNumber) ? (string) $fromNumber 
            : env('whatsapp.from_number', '6282119339330');
        
        $this->client = Services::curlrequest();
    }

    /**
     * Format phone number to international format (62xx)
     * Converts local format (0xx or 8xx) to international format (62xx)
     *
     * @param string $phone Phone number in any format
     * @return string Formatted phone number with country code 62
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If empty, return as is
        if (empty($phone)) {
            return $phone;
        }
        
        // If already starts with 62, return as is
        if (preg_match('/^62/', $phone)) {
            return $phone;
        }
        
        // If starts with 0, replace with 62 (081234567890 -> 6281234567890)
        if (preg_match('/^0/', $phone)) {
            return '62' . substr($phone, 1);
        }
        
        // If starts with 8 (without 0), add 62 prefix (81234567890 -> 6281234567890)
        if (preg_match('/^8/', $phone)) {
            return '62' . $phone;
        }
        
        // For other cases, assume it's already in correct format or add 62
        // This handles edge cases
        return $phone;
    }

    /**
     * Send text message via WhatsApp
     *
     * @param string $to Phone number (with country code, e.g. 6281234567890)
     * @param string $message Message text
     * @return array Response data
     */
    public function sendText(string $to, string $message): array
    {
        log_message('debug', 'WhatsAppService::sendText called');
        log_message('debug', 'API URL: ' . $this->apiUrl);
        log_message('debug', 'API Token: ' . (empty($this->apiToken) ? 'empty' : substr($this->apiToken, 0, 20) . '...'));
        log_message('debug', 'From Number: ' . $this->fromNumber);
        log_message('debug', 'To (original): ' . $to);
        
        // Format nomor ke format internasional (62xx)
        $to = $this->formatPhoneNumber($to);
        
        log_message('debug', 'To (formatted): ' . $to);
        log_message('debug', 'Message: ' . substr($message, 0, 100) . '...');

        $payload = [
            'messageType' => 'text',
            'requestType' => 'POST',
            'token' => $this->apiToken,
            'from' => $this->fromNumber,
            'to' => $to,
            'text' => $message,
        ];
        
        log_message('debug', 'Payload: ' . json_encode($payload));

        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'json' => $payload,
                'http_errors' => false,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            log_message('debug', 'Response status: ' . $statusCode);
            log_message('debug', 'Response body: ' . json_encode($body));

            if ($statusCode === 200 && isset($body['success']) && $body['success']) {
                return [
                    'success' => true,
                    'message' => $body['message'] ?? 'Message sent successfully',
                    'data' => $body['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Message was not sent',
                'error' => $body['solution'] ?? $body['error'] ?? 'Unknown error',
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp sendText exception: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send message to multiple recipients
     *
     * @param array $recipients Array of phone numbers
     * @param string $message Message text
     * @return array Array of results for each recipient
     */
    public function sendBulk(array $recipients, string $message): array
    {
        $results = [];

        foreach ($recipients as $to) {
            $results[] = [
                'recipient' => $to,
                'result' => $this->sendText($to, $message),
            ];
        }

        return $results;
    }

    /**
     * Check if WhatsApp service is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->fromNumber);
    }
}

