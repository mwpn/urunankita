<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigratePaymentMethodsFromSettings extends Migration
{
    public function up()
    {
        // Migrate payment methods from settings table to payment_methods table
        $db = \Config\Database::connect();
        
        // Get all payment_methods from settings table
        $settings = $db->table('settings')
            ->where('key', 'payment_methods')
            ->where('scope', 'tenant')
            ->get()
            ->getResultArray();
        
        if (empty($settings)) {
            return; // No data to migrate
        }
        
        foreach ($settings as $setting) {
            $tenantId = $setting['scope_id'];
            $paymentMethods = json_decode($setting['value'], true);
            
            if (!is_array($paymentMethods) || empty($paymentMethods)) {
                continue;
            }
            
            // Insert each payment method to payment_methods table
            foreach ($paymentMethods as $method) {
                // Check if already exists
                $exists = $db->table('payment_methods')
                    ->where('tenant_id', $tenantId)
                    ->where('code', $method['code'] ?? '')
                    ->get()
                    ->getRowArray();
                
                if ($exists) {
                    continue; // Skip if already exists
                }
                
                $data = [
                    'tenant_id' => $tenantId,
                    'code' => $method['code'] ?? 'payment_' . time() . '_' . rand(1000, 9999),
                    'name' => $method['name'] ?? 'Payment Method',
                    'type' => $method['type'] ?? 'bank-transfer',
                    'enabled' => isset($method['enabled']) && $method['enabled'] === true ? 1 : 0,
                    'description' => $method['description'] ?? null,
                    'provider' => $method['provider'] ?? null,
                    'admin_fee_percent' => floatval($method['admin_fee_percent'] ?? 0),
                    'admin_fee_fixed' => floatval($method['admin_fee_fixed'] ?? 0),
                    'require_verification' => isset($method['require_verification']) && $method['require_verification'] === true ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                
                $db->table('payment_methods')->insert($data);
            }
        }
    }

    public function down()
    {
        // This migration is one-way (data migration)
        // To rollback, you would need to manually move data back to settings
        // For safety, we'll leave this empty
    }
}
