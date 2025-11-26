<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Command untuk memastikan template notifikasi tenant ada dan enabled
 * Usage: php spark ensure:notification-templates
 */
class EnsureNotificationTemplates extends BaseCommand
{
    protected $group       = 'Notification';
    protected $name        = 'ensure:notification-templates';
    protected $description = 'Memastikan template notifikasi tenant ada dan enabled di database';
    protected $usage       = 'ensure:notification-templates';

    public function run(array $params)
    {
        CLI::write('🔍 Memeriksa template notifikasi tenant...', 'yellow');
        CLI::newLine();

        $db = Database::connect();

        // Template yang harus ada
        $requiredTemplates = [
            [
                'key' => 'whatsapp_template_tenant_donation_new',
                'value' => 'Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan \'{campaign_title}\'. Silakan konfirmasi pembayaran di dashboard.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk tenant ketika ada donasi baru yang perlu approval',
            ],
            [
                'key' => 'whatsapp_template_tenant_donation_new_enabled',
                'value' => '1',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Aktifkan/nonaktifkan template notifikasi donasi baru untuk tenant',
            ],
        ];

        foreach ($requiredTemplates as $template) {
            $exists = $db->table('settings')
                ->where('key', $template['key'])
                ->where('scope', $template['scope'])
                ->where('scope_id', $template['scope_id'])
                ->get()
                ->getRowArray();

            if ($exists) {
                // Update value jika berbeda (terutama untuk enabled)
                if ($exists['value'] !== $template['value']) {
                    $db->table('settings')
                        ->where('id', $exists['id'])
                        ->update([
                            'value' => $template['value'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    CLI::write("✓ Setting '{$template['key']}' di-update: '{$exists['value']}' → '{$template['value']}'", 'green');
                } else {
                    CLI::write("✓ Setting '{$template['key']}' sudah ada dan benar", 'green');
                }
            } else {
                // Insert jika belum ada
                $template['created_at'] = date('Y-m-d H:i:s');
                $template['updated_at'] = date('Y-m-d H:i:s');
                $db->table('settings')->insert($template);
                CLI::write("✓ Setting '{$template['key']}' berhasil ditambahkan", 'green');
            }
        }

        CLI::newLine();
        CLI::write('✅ Semua template notifikasi tenant sudah dipastikan ada dan enabled!', 'green');
        CLI::newLine();

        // Verifikasi
        CLI::write('📋 Verifikasi:', 'yellow');
        $templates = $db->table('settings')
            ->whereIn('key', ['whatsapp_template_tenant_donation_new', 'whatsapp_template_tenant_donation_new_enabled'])
            ->get()
            ->getResultArray();

        foreach ($templates as $t) {
            CLI::write("  - {$t['key']}: {$t['value']}", 'cyan');
        }
    }
}

