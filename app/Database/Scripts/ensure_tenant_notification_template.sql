-- Script untuk memastikan template notifikasi tenant ada di database
-- Jalankan script ini di database produksi jika template belum ada

-- Cek dan insert template tenant_donation_new jika belum ada
INSERT INTO settings (`key`, `value`, `type`, `scope`, `scope_id`, `description`, `created_at`, `updated_at`)
SELECT 
    'whatsapp_template_tenant_donation_new',
    'Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan ''{campaign_title}''. Silakan konfirmasi pembayaran di dashboard.',
    'string',
    'global',
    NULL,
    'Template pesan WhatsApp untuk tenant ketika ada donasi baru yang perlu approval',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM settings WHERE `key` = 'whatsapp_template_tenant_donation_new'
);

-- Cek dan insert setting enabled jika belum ada
INSERT INTO settings (`key`, `value`, `type`, `scope`, `scope_id`, `description`, `created_at`, `updated_at`)
SELECT 
    'whatsapp_template_tenant_donation_new_enabled',
    '1',
    'string',
    'global',
    NULL,
    'Aktifkan/nonaktifkan template notifikasi donasi baru untuk tenant',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM settings WHERE `key` = 'whatsapp_template_tenant_donation_new_enabled'
);

-- Verifikasi
SELECT `key`, `value`, `scope` FROM settings 
WHERE `key` IN ('whatsapp_template_tenant_donation_new', 'whatsapp_template_tenant_donation_new_enabled')
ORDER BY `key`;

