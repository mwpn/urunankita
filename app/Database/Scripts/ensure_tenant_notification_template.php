<?php
/**
 * Script untuk memastikan template notifikasi tenant ada dan enabled
 * 
 * Usage: php app/Database/Scripts/ensure_tenant_notification_template.php
 * 
 * Atau via browser: http://yourdomain.com/app/Database/Scripts/ensure_tenant_notification_template.php
 * (Hapus file ini setelah selesai untuk keamanan)
 */

// Load environment
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();

// Koneksi database langsung (tanpa bootstrap penuh CodeIgniter)
$hostname = $_ENV['database.default.hostname'] ?? 'localhost';
$username = $_ENV['database.default.username'] ?? 'root';
$password = $_ENV['database.default.password'] ?? '';
$database = $_ENV['database.default.database'] ?? 'urunankita_master';
$port = (int) ($_ENV['database.default.port'] ?? 3306);

try {
    $db = new mysqli($hostname, $username, $password, $database, $port);
    
    if ($db->connect_error) {
        die("❌ Koneksi database gagal: " . $db->connect_error . "\n");
    }
    
    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    die("❌ Error koneksi database: " . $e->getMessage() . "\n");
}

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
    // Escape untuk SQL
    $key = $db->real_escape_string($template['key']);
    $scope = $db->real_escape_string($template['scope']);
    $scopeId = $template['scope_id'] === null ? 'NULL' : "'" . $db->real_escape_string($template['scope_id']) . "'";
    
    // Cek apakah sudah ada
    $query = "SELECT id, `value` FROM settings 
              WHERE `key` = '$key' 
              AND scope = '$scope' 
              AND (scope_id = $scopeId OR (scope_id IS NULL AND $scopeId = 'NULL'))";
    
    $result = $db->query($query);
    $exists = $result ? $result->fetch_assoc() : null;

    if ($exists) {
        // Update value jika berbeda (terutama untuk enabled)
        if ($exists['value'] !== $template['value']) {
            $newValue = $db->real_escape_string($template['value']);
            $updatedAt = date('Y-m-d H:i:s');
            $updateQuery = "UPDATE settings 
                           SET `value` = '$newValue', updated_at = '$updatedAt' 
                           WHERE id = " . (int)$exists['id'];
            
            if ($db->query($updateQuery)) {
                echo "✓ Setting '{$template['key']}' di-update: '{$exists['value']}' → '{$template['value']}'\n";
            } else {
                echo "❌ Error update '{$template['key']}': " . $db->error . "\n";
            }
        } else {
            echo "✓ Setting '{$template['key']}' sudah ada dan benar\n";
        }
    } else {
        // Insert jika belum ada
        $value = $db->real_escape_string($template['value']);
        $type = $db->real_escape_string($template['type']);
        $description = $db->real_escape_string($template['description']);
        $createdAt = date('Y-m-d H:i:s');
        $updatedAt = date('Y-m-d H:i:s');
        
        $insertQuery = "INSERT INTO settings (`key`, `value`, `type`, scope, scope_id, `description`, created_at, updated_at) 
                       VALUES ('$key', '$value', '$type', '$scope', $scopeId, '$description', '$createdAt', '$updatedAt')";
        
        if ($db->query($insertQuery)) {
            echo "✓ Setting '{$template['key']}' berhasil ditambahkan\n";
        } else {
            echo "❌ Error insert '{$template['key']}': " . $db->error . "\n";
        }
    }
}

echo "\n✅ Semua template notifikasi tenant sudah dipastikan ada dan enabled!\n\n";

// Verifikasi
echo "📋 Verifikasi:\n";
$verifyQuery = "SELECT `key`, `value` FROM settings 
                WHERE `key` IN ('whatsapp_template_tenant_donation_new', 'whatsapp_template_tenant_donation_new_enabled')
                ORDER BY `key`";
$verifyResult = $db->query($verifyQuery);

if ($verifyResult) {
    while ($t = $verifyResult->fetch_assoc()) {
        echo "  - {$t['key']}: {$t['value']}\n";
    }
} else {
    echo "  ❌ Error verifikasi: " . $db->error . "\n";
}

$db->close();
echo "\n";

