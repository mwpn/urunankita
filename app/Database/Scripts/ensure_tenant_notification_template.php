<?php
/**
 * Script untuk memastikan template notifikasi tenant ada dan enabled
 * 
 * Usage: php app/Database/Scripts/ensure_tenant_notification_template.php
 * 
 * Atau via browser: http://yourdomain.com/app/Database/Scripts/ensure_tenant_notification_template.php
 * (Hapus file ini setelah selesai untuk keamanan)
 */

// Bootstrap CodeIgniter
require_once __DIR__ . '/../../../vendor/autoload.php';

// Get paths
$pathsConfig = require __DIR__ . '/../../../app/Config/Paths.php';
$pathsConfig->systemDirectory = __DIR__ . '/../../../vendor/codeigniter4/framework/system';
$pathsConfig->appDirectory = __DIR__ . '/../../../app';
$pathsConfig->writableDirectory = __DIR__ . '/../../../writable';
$pathsConfig->testsDirectory = __DIR__ . '/../../../tests';
$pathsConfig->viewDirectory = __DIR__ . '/../../../app/Views';

// Load environment
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();

// Bootstrap CodeIgniter
require __DIR__ . '/../../../vendor/codeigniter4/framework/system/bootstrap.php';

// Get database connection
$db = \Config\Database::connect();

echo "🔍 Memeriksa template notifikasi tenant...\n\n";

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
            echo "✓ Setting '{$template['key']}' di-update: '{$exists['value']}' → '{$template['value']}'\n";
        } else {
            echo "✓ Setting '{$template['key']}' sudah ada dan benar\n";
        }
    } else {
        // Insert jika belum ada
        $template['created_at'] = date('Y-m-d H:i:s');
        $template['updated_at'] = date('Y-m-d H:i:s');
        $db->table('settings')->insert($template);
        echo "✓ Setting '{$template['key']}' berhasil ditambahkan\n";
    }
}

echo "\n✅ Semua template notifikasi tenant sudah dipastikan ada dan enabled!\n\n";

// Verifikasi
echo "📋 Verifikasi:\n";
$templates = $db->table('settings')
    ->whereIn('key', ['whatsapp_template_tenant_donation_new', 'whatsapp_template_tenant_donation_new_enabled'])
    ->get()
    ->getResultArray();

foreach ($templates as $t) {
    echo "  - {$t['key']}: {$t['value']}\n";
}

echo "\n";

