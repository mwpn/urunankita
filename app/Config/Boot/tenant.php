<?php

/*
 |--------------------------------------------------------------------------
 | Tenant Bootstrap
 |--------------------------------------------------------------------------
 | Auto-detect tenant dari subdomain dan set session context
 | Dipanggil SEBELUM filters dan controllers dijalankan
 |
 | Contoh:
 | - jerry.urunankita.test -> tenant jerry
 | - dendenny.urunankita.test -> tenant dendenny
 | - urunankita.test -> main domain (no tenant)
 */

// Note: Di bootstrap stage, Config classes dan helpers belum tersedia
// Kita hanya set $_ENV untuk diambil oleh filter/controller nanti
// Filter akan handle database connection dan session setup

// Get host dari request
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Get baseDomain from .env file manually (env() helper not available yet in bootstrap)
$baseDomain = 'urunankita.id'; // default
if (file_exists(ROOTPATH . '.env')) {
    $envFile = file_get_contents(ROOTPATH . '.env');
    if (preg_match('/^app\.baseDomain\s*=\s*(.+)$/m', $envFile, $matches)) {
        $baseDomain = trim($matches[1]);
    }
}

// Parse subdomain
$parts = explode('.', $host);
$subdomain = null;

// For local development (.test) or production (.id)
if (strpos($host, '.test') !== false || strpos($host, '.id') !== false) {
    // Format: {tenant}.urunankita.test atau {tenant}.urunankita.id
    if (count($parts) >= 3) {
        $subdomain = $parts[0];
        $domain = $parts[1];
        
        // Skip common subdomains
        if (in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app'])) {
            $subdomain = null;
        }
        
        // Verify domain is urunankita
        if ($domain !== 'urunankita') {
            $subdomain = null;
        }
    }
}

// Jika subdomain ditemukan dan bukan domain utama
// Note: Di bootstrap stage, banyak helper belum tersedia.
// Kita hanya set $_ENV untuk diambil oleh filter/controller nanti
// Filter akan handle database connection dan session setup

if ($subdomain && $subdomain !== 'urunankita' && $subdomain !== 'www') {
    // Set subdomain untuk di-resolve oleh filter
    $_ENV['TENANT_SUBDOMAIN'] = $subdomain;
    $_ENV['IS_SUBDOMAIN'] = true;
} else {
    // Main domain - no tenant
    $_ENV['IS_SUBDOMAIN'] = false;
    unset($_ENV['TENANT_SUBDOMAIN']);
}

