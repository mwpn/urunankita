<?php

if (! function_exists('send_whatsapp')) {
    /**
     * Helper function to send WhatsApp notification
     *
     * @param string $to Phone number
     * @param string $message Message text
     * @param array $context Additional context
     * @return array
     */
    function send_whatsapp(string $to, string $message, array $context = []): array
    {
        $notificationService = \Config\Services::notification();
        return $notificationService->sendWhatsApp($to, $message, $context);
    }
}

if (! function_exists('send_bulk_whatsapp')) {
    /**
     * Helper function to send bulk WhatsApp notifications
     *
     * @param array $recipients Array of phone numbers
     * @param string $message Message text
     * @param array $context Additional context
     * @return array
     */
    function send_bulk_whatsapp(array $recipients, string $message, array $context = []): array
    {
        $notificationService = \Config\Services::notification();
        return $notificationService->sendBulkWhatsApp($recipients, $message, $context);
    }
}

