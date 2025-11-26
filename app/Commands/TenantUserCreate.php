<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class TenantUserCreate extends BaseCommand
{
    protected $group       = 'Tenant';
    protected $name        = 'tenant:user:create';
    protected $description = 'Membuat user untuk tenant yang sudah ada (single database).';
    protected $usage       = "tenant:user:create <tenant_slug> --email <email> [--password <password>] [--name <Full Name>] [--role <tenant_owner|tenant_admin>]";

    public function run(array $params)
    {
        $slug = $params[0] ?? null;
        if (! $slug) {
            CLI::error('Tenant slug wajib diisi');
            return;
        }

        $email = CLI::getOption('email');
        if (! $email) {
            CLI::error('--email wajib diisi');
            return;
        }

        $name = CLI::getOption('name') ?? ucfirst($slug) . ' User';
        $role = CLI::getOption('role') ?? 'tenant_owner';
        if (! in_array($role, ['tenant_owner', 'tenant_admin'], true)) {
            CLI::error('Role tidak valid. Gunakan tenant_owner atau tenant_admin');
            return;
        }

        $passwordPlain = CLI::getOption('password') ?: 'admin123';
        $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

        $db = Database::connect();

        // Cari tenant
        $tenant = $db->table('tenants')->where('slug', $slug)->get()->getRowArray();
        if (! $tenant) {
            CLI::error("Tenant dengan slug '{$slug}' tidak ditemukan");
            return;
        }

        // Cek duplikasi email di tenant yang sama
        $exists = $db->table('users')
            ->where('tenant_id', (int) $tenant['id'])
            ->where('email', $email)
            ->countAllResults();
        if ($exists > 0) {
            CLI::error('Email sudah terdaftar pada tenant ini');
            return;
        }

        // Insert user
        try {
            $db->table('users')->insert([
                'tenant_id' => (int) $tenant['id'],
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'role' => $role,
                'status' => 'active',
            ]);
            CLI::write("✅ User berhasil dibuat untuk tenant '{$slug}': {$email} (role: {$role}, password: {$passwordPlain})", 'green');
        } catch (\Throwable $e) {
            CLI::error('Gagal membuat user: ' . $e->getMessage());
        }
    }
}


