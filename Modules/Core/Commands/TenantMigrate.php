<?php

namespace Modules\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Config\Services;

class TenantMigrate extends BaseCommand
{
    protected $group       = 'Tenant';
    protected $name        = 'tenant:migrate';
    protected $description = 'Menjalankan migrasi khusus database tenant_<slug>.';
    protected $usage       = 'tenant:migrate <slug>';

    public function run(array $params)
    {
        $slug = $params[0] ?? null;
        if (! $slug) {
            CLI::error('Slug tenant wajib diisi');
            return;
        }

        // Daftarkan koneksi runtime
        $dbKey = 'tenant_' . $slug;
        $config = config('Database');
        $config->{$dbKey} = [
            'DSN'      => '',
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', 'root'),
            'password' => env('database.default.password', 'dodolgarut'),
            'database' => $dbKey,
            'DBDriver' => env('database.default.DBDriver', 'MySQLi'),
            'DBPrefix' => '',
            'port'     => (int) (env('database.default.port') ?: 3306),
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci',
        ];

        $db = Database::connect($dbKey);
        $db->connect();

        // Set sebagai default connection untuk migrations
        Database::connect($dbKey, true);

        // Force create migrations table if not exists
        $db->query("CREATE TABLE IF NOT EXISTS `migrations` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `version` varchar(255) NOT NULL,
            `class` varchar(255) NOT NULL,
            `group` varchar(255) NOT NULL,
            `namespace` varchar(255) NOT NULL,
            `time` int(11) NOT NULL,
            `batch` int(11) unsigned NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Jalankan migrasi untuk semua modules
        $migrations = Services::migrations();
        
        // List semua modules yang perlu migrations untuk tenant database
        $modules = [
            'Modules\\Tenant',      // Users, roles, permissions per tenant
            'Modules\\Campaign',    // Campaigns
            'Modules\\Donation',    // Donations
            'Modules\\Beneficiary', // Beneficiaries
            'Modules\\Withdrawal',  // Withdrawals
            'Modules\\CampaignUpdate', // Campaign updates
            'Modules\\Discussion',  // Comments/Discussions
            'Modules\\Report',      // Reports
            // 'Modules\\Export',      // Export module doesn't have migrations (just service)
            'Modules\\Helpdesk',    // Tickets
            'Modules\\File',        // File storage
            'Modules\\Notification', // Notifications
            'Modules\\ActivityLog', // Activity logs
            'Modules\\Setting',     // Settings
        ];
        
        CLI::write("Menjalankan migrations untuk semua modules...", 'yellow');
        
        foreach ($modules as $namespace) {
            try {
                // IMPORTANT: Set connection as default BEFORE getting migrations service
                Database::connect($dbKey, true);
                
                // Also change defaultGroup in config to ensure forge uses correct connection
                $dbConfig = config('Database');
                $dbConfig->defaultGroup = $dbKey;
                
                // Get fresh migrations instance AFTER setting default connection
                $migrations = Services::migrations();
                $migrations->setNamespace($namespace);
                $migrations->setGroup($dbKey);
                
                // CRITICAL: Force set database connection in MigrationRunner using reflection
                // MigrationRunner's $this->db must use tenant connection
                $reflection = new \ReflectionClass($migrations);
                $dbProperty = $reflection->getProperty('db');
                $dbProperty->setAccessible(true);
                $tenantDb = Database::connect($dbKey);
                $dbProperty->setValue($migrations, $tenantDb);
                
                // Verify connection is correct
                $db = Database::connect($dbKey);
                $dbName = $db->getDatabase();
                if ($dbName !== $dbKey) {
                    CLI::write("  ⚠️  {$namespace} - Connection mismatch: expected {$dbKey}, got {$dbName}", 'yellow');
                    continue;
                }
                
                // Set path manually if namespace-based discovery fails
                // Convert namespace to path: Modules\Campaign -> Modules/Campaign/Database/Migrations
                $namespaceParts = explode('\\', $namespace);
                if (count($namespaceParts) >= 2 && $namespaceParts[0] === 'Modules') {
                    $moduleName = $namespaceParts[1];
                    $migrationPath = ROOTPATH . 'Modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';
                    if (is_dir($migrationPath)) {
                        // Use reflection to set protected path property
                        $reflection = new \ReflectionClass($migrations);
                        $pathProperty = $reflection->getProperty('path');
                        $pathProperty->setAccessible(true);
                        $pathProperty->setValue($migrations, $migrationPath);
                    }
                }
                
                $allMigrations = $migrations->findMigrations();
                $foundCount = count($allMigrations);
                
                if ($foundCount === 0) {
                    CLI::write("  ⚠️  {$namespace} - tidak ada migration files ditemukan", 'yellow');
                    continue;
                }
                
                // Get history before running to see what's already done
                $historyBefore = $migrations->getHistory($dbKey);
                $historyCount = count($historyBefore);
                
                try {
                    $result = $migrations->latest($dbKey);
                } catch (\Exception $e) {
                    CLI::write("  ⚠️  {$namespace} - Error saat menjalankan migrations: " . $e->getMessage(), 'yellow');
                    continue;
                } catch (\Throwable $e) {
                    CLI::write("  ❌ {$namespace} - Fatal error: " . $e->getMessage(), 'red');
                    continue;
                }
                
                // Verify migrations were actually run by checking migrations table
                $checkDb = Database::connect($dbKey);
                $historyAfter = $checkDb->table('migrations')
                    ->where('namespace', $namespace)
                    ->where('group', $dbKey)
                    ->countAllResults(false);
                
                $newMigrations = $historyAfter - $historyCount;
                
                if ($newMigrations > 0) {
                    CLI::write("  ✅ {$namespace} - {$newMigrations} new migrations ({$historyAfter} total)", 'green');
                } elseif ($historyAfter > 0) {
                    CLI::write("  ℹ️  {$namespace} - sudah up to date ({$historyAfter} migrations)", 'cyan');
                } else {
                    CLI::write("  ⚠️  {$namespace} - tidak ada migrations yang dijalankan (found: {$foundCount}, tracked: {$historyAfter})", 'yellow');
                }
            } catch (\Exception $e) {
                CLI::write("  ⚠️  {$namespace} - " . $e->getMessage(), 'yellow');
                continue;
            } catch (\Throwable $e) {
                CLI::write("  ❌ {$namespace} - Error: " . $e->getMessage(), 'red');
                continue;
            }
        }
        
        // Reset to default connection
        $dbConfig = config('Database');
        $dbConfig->defaultGroup = 'default';
        Database::connect('default', true);

        CLI::write("Migrasi tenant untuk '$dbKey' selesai.", 'green');
    }
}


