<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Database;
use Config\Services;

class TenantSampleDataSeeder extends Seeder
{
    /**
     * Seed sample data untuk semua tenant atau tenant tertentu
     * Usage: 
     *   php spark db:seed TenantSampleDataSeeder
     *   php spark db:seed TenantSampleDataSeeder --tenant=jerry
     */
    public function run()
    {
        $central = Database::connect();
        
        // Ambil tenant slug dari CLI args (jika ada)
        $tenantSlug = null;
        $args = $_SERVER['argv'] ?? [];
        foreach ($args as $i => $arg) {
            if (strpos($arg, '--tenant=') === 0) {
                $tenantSlug = substr($arg, strlen('--tenant='));
                break;
            }
        }
        
        if ($tenantSlug) {
            $tenants = $central->table('tenants')
                ->where('slug', $tenantSlug)
                ->where('status', 'active')
                ->get()
                ->getResultArray();
        } else {
            $tenants = $central->table('tenants')
                ->where('status', 'active')
                ->get()
                ->getResultArray();
        }

        if (empty($tenants)) {
            echo "Tidak ada tenant yang ditemukan.\n";
            return;
        }

        foreach ($tenants as $tenant) {
            echo "Seeding data untuk tenant: {$tenant['name']} ({$tenant['slug']})\n";
            $this->seedTenantData($tenant);
        }
    }

    /**
     * Seed data untuk satu tenant
     */
    protected function seedTenantData(array $tenant): void
    {
        $tenantSlug = $tenant['slug'];
        $dbName = $tenant['db_name'] ?? ('tenant_' . $tenantSlug);
        $tenantId = (int) $tenant['id'];

        // Connect to tenant database
        $config = config('Database');
        $config->{$dbName} = [
            'DSN'      => '',
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', 'root'),
            'password' => env('database.default.password', 'dodolgarut'),
            'database' => $dbName,
            'DBDriver' => env('database.default.DBDriver', 'MySQLi'),
            'DBPrefix' => '',
            'port'     => (int) (env('database.default.port') ?: 3306),
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
        ];

        // Check if migrations have been run, run all module migrations if needed
        $tenantDb = Database::connect($dbName);
        $tablesExist = false;
        
        try {
            $tenantDb->query("SELECT 1 FROM campaigns LIMIT 1");
            $tablesExist = true;
        } catch (\Exception $e) {
            // Tables don't exist, run migrations first
            echo "  ⚠️  Migrations belum dijalankan untuk tenant {$tenant['slug']}, menjalankan migrations...\n";
            
            // Run tenant migrations (creates users, roles, etc)
            command('tenant:migrate ' . $tenant['slug']);
            
            // Run all module migrations for this tenant
            $this->runModuleMigrations($tenantDb, $dbName);
            
            // Reconnect and verify - reconnect dengan fresh connection
            sleep(2); // Wait a bit for migrations to complete
            
            // Force fresh connection
            Database::connect('default', true);
            $tenantDb = Database::connect($dbName);
            $tenantDb->reconnect();
            
            try {
                // Try multiple ways to check if table exists
                $tables = $tenantDb->listTables();
                if (in_array('campaigns', $tables)) {
                    $tablesExist = true;
                    echo "  ✅ Migrations selesai, tables sudah dibuat.\n";
                } else {
                    echo "  ⚠️  Tabel campaigns belum ada di list tables.\n";
                    echo "  Tables yang ada: " . implode(', ', $tables) . "\n";
                }
            } catch (\Exception $e2) {
                echo "  ❌ Error: " . $e2->getMessage() . "\n";
                echo "  ⚠️  Migrations gagal membuat tabel. Silakan jalankan manual: php spark tenant:migrate {$tenant['slug']}\n";
                return;
            }
            
            if (!$tablesExist) {
                echo "  ⚠️  Tabel campaigns belum dibuat. Silakan jalankan: php spark tenant:migrate {$tenant['slug']}\n";
                return;
            }
        }

        // Check if already seeded
        if ($tablesExist) {
            try {
                $existingCampaigns = $tenantDb->table('campaigns')->countAllResults(false);
                if ($existingCampaigns > 0) {
                    echo "  ⚠️  Tenant {$tenant['slug']} sudah punya data, skip...\n";
                    return;
                }
            } catch (\Exception $e) {
                echo "  ❌ Error checking existing data: " . $e->getMessage() . "\n";
                return;
            }
        }

        // Seed users (owner)
        $ownerId = $this->seedUsers($tenantDb, $tenantId);
        
        // Seed beneficiaries - pass tenant slug for unique data
        $beneficiaryIds = $this->seedBeneficiaries($tenantDb, $tenantId, $tenantSlug);
        
        // Seed campaigns - pass tenant slug for unique data
        $campaignIds = $this->seedCampaigns($tenantDb, $tenantId, $ownerId, $beneficiaryIds, $tenantSlug);
        
        // Seed donations
        $this->seedDonations($tenantDb, $tenantId, $campaignIds, $ownerId);

        echo "  ✅ Data berhasil di-seed untuk tenant {$tenant['slug']}\n";
    }

    /**
     * Seed users
     */
    protected function seedUsers($db, int $tenantId): int
    {
        // Get existing owner
        $owner = $db->table('users')
            ->where('role', 'tenant_owner')
            ->get()
            ->getRowArray();

        if ($owner) {
            return (int) $owner['id'];
        }

        // Create additional users
        $users = [
            [
                'name' => 'Manager ' . $tenantId,
                'email' => "manager{$tenantId}@test.com",
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'manager',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Staff ' . $tenantId,
                'email' => "staff{$tenantId}@test.com",
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $db->table('users')->insertBatch($users);
        
        return (int) $db->insertID();
    }

    /**
     * Seed beneficiaries
     */
    protected function seedBeneficiaries($db, int $tenantId, string $tenantSlug): array
    {
        $tenantPrefix = strtoupper(substr($tenantSlug, 0, 1));
        $beneficiaries = [
            [
                'tenant_id' => $tenantId,
                'type' => 'individual',
                'name' => '[' . $tenantPrefix . '] Ahmad Santoso',
                'description' => 'Warga yang membutuhkan bantuan biaya operasi',
                'identity_number' => '3201234567890123',
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'phone' => '081234567890',
                'email' => 'ahmad@example.com',
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'bank_account_name' => 'Ahmad Santoso',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id' => $tenantId,
                'type' => 'family',
                'name' => '[' . $tenantPrefix . '] Keluarga Pak Budi',
                'description' => 'Keluarga yang rumahnya terkena bencana banjir',
                'address' => 'Jl. Raya No. 456, Bandung',
                'phone' => '081234567891',
                'bank_name' => 'Mandiri',
                'bank_account' => '0987654321',
                'bank_account_name' => 'Budi Santoso',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id' => $tenantId,
                'type' => 'institution',
                'name' => '[' . $tenantPrefix . '] Panti Asuhan Kasih Ibu',
                'description' => 'Panti asuhan yang membutuhkan bantuan untuk operasional',
                'address' => 'Jl. Sosial No. 789, Surabaya',
                'phone' => '081234567892',
                'email' => 'panti@example.com',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert one by one to get IDs properly
        $ids = [];
        foreach ($beneficiaries as $beneficiary) {
            $db->table('beneficiaries')->insert($beneficiary);
            $ids[] = $db->insertID();
        }
        
        return $ids;
    }

    /**
     * Seed campaigns
     */
    protected function seedCampaigns($db, int $tenantId, int $ownerId, array $beneficiaryIds, string $tenantSlug): array
    {
        // Generate different data based on tenant slug to make it distinguishable
        $tenantPrefix = strtolower(substr($tenantSlug, 0, 3));
        
        $campaigns = [
            [
                'tenant_id' => $tenantId,
                'creator_user_id' => $ownerId,
                'beneficiary_id' => $beneficiaryIds[0] ?? null,
                'title' => '[' . ucfirst($tenantSlug) . '] Bantuan Operasi untuk Ahmad',
                'slug' => $tenantPrefix . '-bantuan-operasi-ahmad-' . time(),
                'description' => '[' . ucfirst($tenantSlug) . '] Ahmad membutuhkan bantuan biaya operasi jantung yang sangat mendesak. Mari kita bantu Ahmad untuk mendapatkan pengobatan yang layak.',
                'campaign_type' => 'target_based',
                'target_amount' => 50000000,
                'current_amount' => 12500000,
                'category' => 'kesehatan',
                'status' => 'active',
                'featured_image' => null,
                'images' => json_encode([]),
                'deadline' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => $ownerId,
                'views_count' => 1250,
                'donors_count' => 87,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id' => $tenantId,
                'creator_user_id' => $ownerId,
                'beneficiary_id' => $beneficiaryIds[1] ?? null,
                'title' => '[' . ucfirst($tenantSlug) . '] Renovasi Rumah Korban Banjir',
                'slug' => $tenantPrefix . '-renovasi-rumah-banjir-' . time(),
                'description' => '[' . ucfirst($tenantSlug) . '] Keluarga Pak Budi kehilangan rumah akibat banjir. Mari kita bantu mereka membangun kembali rumah yang layak huni.',
                'campaign_type' => 'target_based',
                'target_amount' => 75000000,
                'current_amount' => 45000000,
                'category' => 'bencana',
                'status' => 'active',
                'featured_image' => null,
                'images' => json_encode([]),
                'deadline' => date('Y-m-d H:i:s', strtotime('+45 days')),
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => $ownerId,
                'views_count' => 2100,
                'donors_count' => 145,
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id' => $tenantId,
                'creator_user_id' => $ownerId,
                'beneficiary_id' => $beneficiaryIds[2] ?? null,
                'title' => '[' . ucfirst($tenantSlug) . '] Program Pendidikan Anak Yatim',
                'slug' => $tenantPrefix . '-program-pendidikan-anak-yatim-' . time(),
                'description' => '[' . ucfirst($tenantSlug) . '] Program berkelanjutan untuk memberikan pendidikan yang layak bagi anak-anak yatim di panti asuhan.',
                'campaign_type' => 'ongoing',
                'target_amount' => null,
                'current_amount' => 3500000,
                'category' => 'pendidikan',
                'status' => 'active',
                'featured_image' => null,
                'images' => json_encode([]),
                'deadline' => null,
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => $ownerId,
                'views_count' => 890,
                'donors_count' => 23,
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'tenant_id' => $tenantId,
                'creator_user_id' => $ownerId,
                'beneficiary_id' => $beneficiaryIds[0] ?? null,
                'title' => '[' . ucfirst($tenantSlug) . '] Bantuan Makanan untuk Lansia',
                'slug' => $tenantPrefix . '-bantuan-makanan-lansia-' . time(),
                'description' => '[' . ucfirst($tenantSlug) . '] Program rutin menyediakan makanan bergizi untuk lansia yang kurang mampu di lingkungan sekitar.',
                'campaign_type' => 'ongoing',
                'target_amount' => null,
                'current_amount' => 2500000,
                'category' => 'sosial',
                'status' => 'active',
                'featured_image' => null,
                'images' => json_encode([]),
                'deadline' => null,
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => $ownerId,
                'views_count' => 450,
                'donors_count' => 15,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $campaignIds = [];
        foreach ($campaigns as $campaign) {
            $db->table('campaigns')->insert($campaign);
            $campaignIds[] = $db->insertID();
        }

        return $campaignIds;
    }

    /**
     * Seed donations
     */
    protected function seedDonations($db, int $tenantId, array $campaignIds, int $ownerId): void
    {
        $donations = [];
        
        // Generate donations untuk setiap campaign
        foreach ($campaignIds as $campaignId) {
            // 10-20 donations per campaign
            $count = rand(10, 20);
            
            for ($i = 0; $i < $count; $i++) {
                $amount = [50000, 100000, 250000, 500000, 1000000, 2500000][rand(0, 5)];
                $daysAgo = rand(0, 14);
                $isPaid = rand(0, 100) > 15; // 85% paid
                
                $donations[] = [
                    'campaign_id' => $campaignId,
                    'tenant_id' => $tenantId,
                    'donor_id' => rand(0, 10) > 7 ? null : $ownerId, // 30% anonim
                    'donor_name' => $i % 3 === 0 ? 'Orang Baik Tanpa Nama' : 'Donatur ' . ($i + 1),
                    'donor_email' => $i % 3 === 0 ? null : "donatur{$i}@example.com",
                    'donor_phone' => $i % 3 === 0 ? null : '08' . rand(1000000000, 9999999999),
                    'amount' => $amount,
                    'is_anonymous' => $i % 3 === 0 ? 1 : 0,
                    'payment_method' => ['bank_transfer', 'e_wallet', 'qris'][rand(0, 2)],
                    'payment_status' => $isPaid ? 'paid' : 'pending',
                    'confirmed_by' => $isPaid ? $ownerId : null,
                    'confirmed_at' => $isPaid ? date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")) : null,
                    'paid_at' => $isPaid ? date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")) : null,
                    'message' => $i % 5 === 0 ? 'Semoga cepat sembuh dan diberi kesehatan.' : null,
                    'created_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")),
                    'updated_at' => date('Y-m-d H:i:s', strtotime("-{$daysAgo} days")),
                ];
            }
        }

        // Insert in batches
        $batchSize = 50;
        for ($i = 0; $i < count($donations); $i += $batchSize) {
            $batch = array_slice($donations, $i, $batchSize);
            $db->table('donations')->insertBatch($batch);
        }
    }

    /**
     * Run all module migrations for tenant
     */
    protected function runModuleMigrations($tenantDb, string $dbKey): void
    {
        echo "    Menjalankan module migrations untuk {$dbKey}...\n";
        
        // Set tenant database as default connection
        Database::connect($dbKey, true);
        
        $migrations = Services::migrations();
        
        // List of module namespaces to migrate
        $modules = [
            'Modules\\Campaign',
            'Modules\\Donation',
            'Modules\\Beneficiary',
            'Modules\\Withdrawal',
            'Modules\\CampaignUpdate',
            'Modules\\Discussion',
            'Modules\\Report',
            'Modules\\Export',
            'Modules\\Helpdesk',
            'Modules\\File',
            'Modules\\Notification',
            'Modules\\ActivityLog',
            'Modules\\Setting',
        ];

        foreach ($modules as $namespace) {
            try {
                $migrations->setNamespace($namespace);
                $migrations->setGroup($dbKey);
                $result = $migrations->latest();
                if ($result) {
                    echo "      ✅ Migrated: {$namespace}\n";
                }
            } catch (\Exception $e) {
                // Module might not have migrations, skip
                echo "      ⚠️  Skip {$namespace}: " . $e->getMessage() . "\n";
                continue;
            }
        }
        
        // Reset to default connection
        Database::connect('default', true);
        echo "    Module migrations selesai.\n";
    }
}
