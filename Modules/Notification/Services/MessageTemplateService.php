<?php

namespace Modules\Notification\Services;

use Config\Services;

class MessageTemplateService
{
    /**
     * Render message template with placeholders
     *
     * @param string $templateKey Template key (e.g., 'donation_created', 'donation_paid')
     * @param array $data Data to replace placeholders
     * @param string|null $defaultTemplate Default template if not found in settings
     * @param int|null $tenantId Tenant ID for tenant-specific templates (optional)
     * @return string|null Rendered message, or null if template is disabled
     */
    public function render(string $templateKey, array $data = [], ?string $defaultTemplate = null, ?int $tenantId = null): ?string
    {
        $settingService = Services::setting();
        
        // Get template from settings
        $settingKey = 'whatsapp_template_' . $templateKey;
        $enabledKey = $settingKey . '_enabled';
        
        // Check if template is enabled (default to enabled if not set)
        $enabled = $settingService->get($enabledKey, '1', 'global', null);
        log_message('debug', "Template {$templateKey} enabled check: " . var_export($enabled, true) . " (tenantId: {$tenantId})");
        
        if ($enabled !== '1' && $enabled !== 1 && $enabled !== true) {
            // Template is disabled, return null
            log_message('debug', "Template {$templateKey} is disabled, skipping");
            return null;
        }
        
        // Try tenant-specific template first (if tenantId provided)
        $template = null;
        if ($tenantId) {
            $template = $settingService->get($settingKey, null, 'tenant', $tenantId);
            log_message('debug', "Template {$templateKey} from tenant {$tenantId}: " . ($template ? 'found (' . strlen($template) . ' chars)' : 'not found'));
        }
        
        // Fallback to global template if tenant template not found
        if (empty($template)) {
            $template = $settingService->get($settingKey, null, 'global', null);
            log_message('debug', "Template {$templateKey} from global: " . ($template ? 'found (' . strlen($template) . ' chars)' : 'not found'));
        }
        
        // Use default template if not found in settings
        if (empty($template)) {
            $template = $defaultTemplate ?? '';
            log_message('debug', "Template {$templateKey} using default: " . ($template ? 'yes (' . strlen($template) . ' chars)' : 'no default provided'));
        }
        
        // If template is empty, return null
        if (empty($template)) {
            log_message('warning', "Template {$templateKey} is empty, cannot send notification");
            return null;
        }
        
        // Replace placeholders
        $rendered = $this->replacePlaceholders($template, $data);
        log_message('debug', "Template {$templateKey} rendered successfully (" . strlen($rendered) . " chars)");
        return $rendered;
    }
    
    /**
     * Replace placeholders in template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function replacePlaceholders(string $template, array $data): string
    {
        // Get site name from settings
        $settingService = Services::setting();
        $siteName = $settingService->get('site_name', null, 'global', null) ?? 'UrunanKita';
        
        // Default replacements
        $replacements = [
            '{site_name}' => $siteName,
        ];
        
        // Add data replacements
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            
            // Format amount if key contains 'amount'
            if (strpos($key, 'amount') !== false && is_numeric($value)) {
                $value = 'Rp ' . number_format((float) $value, 0, ',', '.');
            }
            
            $replacements[$placeholder] = (string) $value;
        }
        
        // Replace all placeholders
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Get available placeholders for a template type
     *
     * @param string $templateKey
     * @return array
     */
    public function getAvailablePlaceholders(string $templateKey): array
    {
        $placeholders = [
            'donation_created' => ['amount', 'donor_name', 'campaign_title', 'site_name', 'bank', 'rekening', 'deskripsi_pembayaran'],
            'donation_paid' => ['amount', 'donor_name', 'campaign_title', 'site_name', 'bank', 'rekening', 'deskripsi_pembayaran'],
            'withdrawal_created' => ['amount', 'campaign_title', 'site_name'],
            'withdrawal_approved' => ['amount', 'campaign_title', 'site_name'],
            'tenant_donation_new' => ['amount', 'donor_name', 'campaign_title', 'site_name', 'donation_id', 'bank', 'rekening', 'deskripsi_pembayaran'],
        ];
        
        return $placeholders[$templateKey] ?? ['site_name'];
    }
}

