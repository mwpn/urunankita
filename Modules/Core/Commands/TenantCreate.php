<?php

namespace Modules\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class TenantCreate extends BaseCommand
{
    protected $group       = 'Tenant';
    protected $name        = 'tenant:create';
    protected $description = 'Membuat tenant baru pada single database, opsional sekalian membuat owner user.';
    protected $usage       = "tenant:create <slug> [--name <Tenant Name>] [--email <email>] [--password <password>]";

    public function run(array $params)
    {
        $slug = $params[0] ?? null;
        if (! $slug) {
            CLI::error('Slug tenant wajib diisi');
            return;
        }

        $db = Database::connect();

        // Read options (non-interactive)
        $tenantName = CLI::getOption('name') ?? ucfirst($slug);
        $ownerEmail = CLI::getOption('email');
        $ownerPassword = CLI::getOption('password');

        // Simplified: No need to create separate database anymore
        // All data will be stored in single database with tenant_id

        // Insert into central.tenants minimal data
        $db->table('tenants')->insert([
            'name'    => $tenantName,
            'slug'    => $slug,
            'db_name' => null, // No longer needed
            'status'  => 'active',
        ]);

        // Get tenant ID
        $tenant = $db->table('tenants')->where('slug', $slug)->get()->getRowArray();
        
        if ($tenant) {
            // If email provided, create owner user with provided creds; otherwise skip user creation
            if ($ownerEmail) {
                $ownerName = $tenantName . ' Owner';
                $passwordPlain = $ownerPassword ?: 'admin123';
                $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

                try {
                    $db->table('users')->insert([
                        'tenant_id' => $tenant['id'],
                        'name' => $ownerName,
                        'email' => $ownerEmail,
                        'password' => $passwordHash,
                        'role' => 'tenant_owner',
                        'status' => 'active',
                    ]);
                    CLI::write("Owner user berhasil dibuat: {$ownerEmail} (password: {$passwordPlain})", 'green');
                } catch (\Exception $e) {
                    CLI::error('Gagal membuat owner user: ' . $e->getMessage());
                    CLI::write("Silakan buat user secara manual.", 'yellow');
                }
            } else {
                CLI::write("Lewati pembuatan owner user (gunakan opsi --email dan --password untuk membuat).", 'yellow');
            }
        }

        CLI::write("Tenant '$slug' berhasil dibuat!", 'green');
        CLI::write('Note: Menggunakan single database architecture (semua data di satu database dengan tenant_id).', 'yellow');
    }
}


