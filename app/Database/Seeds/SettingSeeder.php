<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;

class SettingSeeder extends Seeder
{
    /**
     * Seed default platform settings
     * Usage: php spark db:seed SettingSeeder
     */
    public function run()
    {
        $db = Database::connect();
        
        $defaultSettings = [
            // Informasi Platform
            [
                'key' => 'site_name',
                'value' => 'UrunanKita',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Nama platform yang akan ditampilkan di website',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Platform Crowdfunding Terpercaya',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Slogan atau tagline platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_description',
                'value' => 'Platform crowdfunding terpercaya untuk membantu berbagai kebutuhan sosial dan kemanusiaan.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Deskripsi yang akan digunakan untuk SEO',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_logo',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Logo platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_favicon',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Favicon platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Kontak & Informasi
            [
                'key' => 'site_email',
                'value' => 'info@urunankita.test',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Email untuk kontak umum',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_phone',
                'value' => '+62 812 3456 7890',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Nomor telepon kontak',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_address',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Alamat lengkap platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_facebook',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'URL Facebook',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_instagram',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'URL Instagram',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'site_twitter',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'URL Twitter',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Domain & Subdomain
            [
                'key' => 'main_domain',
                'value' => 'urunankita.test',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Domain utama platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'subdomain_format',
                'value' => '{subdomain}',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Format subdomain untuk penggalang dana',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'subdomain_enabled',
                'value' => '1',
                'type' => 'boolean',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Izinkan pembuatan subdomain baru',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Pengaturan Umum
            [
                'key' => 'timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Zona waktu platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'default_language',
                'value' => 'id',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Bahasa default platform',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Maintenance mode',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'allow_registration',
                'value' => '1',
                'type' => 'boolean',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Izinkan pendaftaran publik',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // SEO & Meta
            [
                'key' => 'meta_title',
                'value' => 'UrunanKita - Platform Crowdfunding Terpercaya',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Meta title untuk SEO',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'meta_description',
                'value' => 'Platform crowdfunding terpercaya untuk membantu berbagai kebutuhan sosial dan kemanusiaan. Bergabunglah dengan ribuan penggalang dana dan donatur.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Meta description untuk SEO',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'crowdfunding, urunan, donasi, bantuan, sosial, kemanusiaan',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Meta keywords untuk SEO',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Email Settings
            [
                'key' => 'smtp_host',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'SMTP Host',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'SMTP Port',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'smtp_user',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'SMTP Username',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'SMTP Password',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // WhatsApp Settings
            [
                'key' => 'whatsapp_api_url',
                'value' => 'https://app.whappi.biz.id/api/qr/rest/send_message',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'WhatsApp API URL',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_api_token',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'WhatsApp API Token',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_from_number',
                'value' => '6282119339330',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Nomor pengirim WhatsApp',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // WhatsApp Message Templates
            [
                'key' => 'whatsapp_template_donation_created',
                'value' => 'Terima kasih! Donasi Anda sebesar Rp {amount} sedang diproses. Silakan lakukan pembayaran sesuai metode yang dipilih.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk donasi yang baru dibuat',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_template_donation_paid',
                'value' => 'Terima kasih! Donasi Anda sebesar Rp {amount} untuk \'{campaign_title}\' telah diterima. Semoga menjadi amal jariyah yang berkah.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk donasi yang sudah dibayar',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_template_withdrawal_created',
                'value' => 'Permohonan penarikan dana sebesar Rp {amount} telah dibuat dan sedang diproses. Kami akan menginformasikan status selanjutnya.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk penarikan yang baru dibuat',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_template_withdrawal_approved',
                'value' => 'Selamat! Permohonan penarikan dana sebesar Rp {amount} telah disetujui dan sedang diproses transfer.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk penarikan yang disetujui',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'whatsapp_template_tenant_donation_new',
                'value' => 'Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan \'{campaign_title}\'. Silakan konfirmasi pembayaran di dashboard.',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Template pesan WhatsApp untuk tenant ketika ada donasi baru yang perlu approval',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            
            // Payment Gateway Settings
            [
                'key' => 'midtrans_server_key',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Midtrans Server Key',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'midtrans_client_key',
                'value' => '',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Midtrans Client Key',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'key' => 'payment_mode',
                'value' => 'sandbox',
                'type' => 'string',
                'scope' => 'global',
                'scope_id' => null,
                'description' => 'Mode Pembayaran (sandbox/production)',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        
        // Insert settings (skip if already exists)
        foreach ($defaultSettings as $setting) {
            $exists = $db->table('settings')
                ->where('key', $setting['key'])
                ->where('scope', $setting['scope'])
                ->where('scope_id', $setting['scope_id'])
                ->countAllResults();
            
            if ($exists == 0) {
                $db->table('settings')->insert($setting);
                echo "✓ Setting '{$setting['key']}' berhasil ditambahkan\n";
            } else {
                echo "⊘ Setting '{$setting['key']}' sudah ada, dilewati\n";
            }
        }
        
        echo "\n✅ Seeding settings selesai!\n";
    }
}

