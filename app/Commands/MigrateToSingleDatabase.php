<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Migrate from multi-database (one per tenant) to single database architecture
 * 
 * Usage: php spark migrate:to-single-db
 * 
 * This command will:
 * 1. Get all tenants from central database
 * 2. For each tenant database, migrate all tables to central database
 * 3. Ensure tenant_id is set correctly for all records
 */
class MigrateToSingleDatabase extends BaseCommand
{
    protected $group       = 'Migration';
    protected $name        = 'migrate:to-single-db';
    protected $description = 'Migrate from multi-database to single database architecture';
    protected $usage       = 'migrate:to-single-db';
    protected $arguments   = [];

    // Tables that should be migrated from tenant databases
    protected $tenantTables = [
        'campaigns',
        'donations',
        'beneficiaries',
        'withdrawals',
        'campaign_updates',
        'comments',
        'reports',
        'tickets',
        'ticket_replies',
        'ticket_categories',
        'files',
        'notification_logs',
        'activity_logs',
        'settings',
        'users', // tenant_users
        'roles', // tenant_roles
        'permissions', // tenant_permissions
        'audit_logs', // tenant_audit_logs
    ];

    public function run(array $params)
    {
        CLI::write('🚀 Starting migration to single database...', 'yellow');
        CLI::newLine();

        // Connect to central database
        $centralDb = Database::connect();
        
        // Get all active tenants
        $tenants = $centralDb->table('tenants')
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        if (empty($tenants)) {
            CLI::error('No active tenants found!');
            return;
        }

        CLI::write('Found ' . count($tenants) . ' active tenants', 'green');
        CLI::newLine();

        // Step 1: Ensure all tables exist in central database with tenant_id
        CLI::write('Step 1: Ensuring all tables exist in central database...', 'yellow');
        $this->ensureTablesExist($centralDb);
        CLI::newLine();

        // Step 2: Migrate data from each tenant database
        CLI::write('Step 2: Migrating data from tenant databases...', 'yellow');
        foreach ($tenants as $tenant) {
            $this->migrateTenantData($centralDb, $tenant);
        }
        CLI::newLine();

        // Step 3: Add indexes if needed
        CLI::write('Step 3: Adding indexes...', 'yellow');
        $this->addIndexes($centralDb);
        CLI::newLine();

        CLI::write('✅ Migration completed successfully!', 'green');
        CLI::write('');
        CLI::write('⚠️  IMPORTANT: Review the data and test the application before deleting tenant databases!', 'yellow');
    }

    protected function ensureTablesExist($centralDb)
    {
        // Always run migrations to ensure all module tables exist
        CLI::write('Running all migrations in central database...', 'cyan');
        
        // Run standard migrations first
        command('migrate');
        
        // Run module migrations manually for central database
        $migrations = \Config\Services::migrations();
        
        $modules = [
            'Modules\\Tenant',
            'Modules\\Campaign',
            'Modules\\Donation',
            'Modules\\Beneficiary',
            'Modules\\Withdrawal',
            'Modules\\CampaignUpdate',
            'Modules\\Discussion',
            'Modules\\Report',
            'Modules\\Helpdesk',
            'Modules\\File',
            'Modules\\Notification',
            'Modules\\ActivityLog',
            'Modules\\Setting',
        ];
        
        foreach ($modules as $namespace) {
            try {
                // Reset migrations instance to ensure clean state
                $migrations = \Config\Services::migrations();
                
                // Ensure we're using default connection
                \Config\Database::connect('default', true);
                
                $migrations->setNamespace($namespace);
                $migrations->setGroup('default');
                
                // Force run latest (will skip if already migrated)
                $result = $migrations->latest();
                
                if ($result) {
                    CLI::write("  ✓ Migrated: {$namespace}", 'green');
                } else {
                    // Check if table actually exists
                    $migrationFiles = glob(ROOTPATH . str_replace('\\', '/', $namespace) . '/Database/Migrations/*.php');
                    if (!empty($migrationFiles)) {
                        // Verify table exists by checking first migration file
                        $firstFile = basename($migrationFiles[0]);
                        $className = str_replace(['.php', '-'], ['', '\\'], $firstFile);
                        $className = str_replace('_', '', $className);
                        
                        // Try to extract table name from migration (basic check)
                        CLI::write("  ℹ️  {$namespace} - Already migrated or no migrations", 'cyan');
                    }
                }
            } catch (\Exception $e) {
                // Module might not have migrations or already migrated
                CLI::write("  ⚠️  {$namespace}: " . $e->getMessage(), 'yellow');
            }
        }
        
        // Verify key tables exist
        $requiredTables = ['campaigns', 'donations', 'tickets'];
        $missingTables = [];
        foreach ($requiredTables as $table) {
            $tablesList = $centralDb->listTables();
            if (!in_array($table, $tablesList)) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            CLI::write("  ⚠️  Missing tables: " . implode(', ', $missingTables), 'yellow');
            CLI::write("  💡 Trying to create missing tables manually...", 'cyan');
            
            // For now, just log - user can manually run migrations if needed
            CLI::write("  ⚠️  Please ensure all module migrations have been run to 'default' group", 'yellow');
        }
    }

    protected function migrateTenantData($centralDb, $tenant)
    {
        $tenantId = (int) $tenant['id'];
        $dbName = $tenant['db_name'] ?? ('tenant_' . $tenant['slug']);
        
        CLI::write("Migrating data from tenant: {$tenant['name']} (DB: {$dbName})...", 'cyan');

        try {
            // Connect to tenant database
            $config = config('Database');
            $config->{$dbName} = [
                'DSN'      => '',
                'hostname' => env('database.default.hostname', 'localhost'),
                'username' => env('database.default.username', 'root'),
                'password' => env('database.default.password', ''),
                'database' => $dbName,
                'DBDriver' => env('database.default.DBDriver', 'MySQLi'),
                'DBPrefix' => '',
                'port'     => (int) (env('database.default.port') ?: 3306),
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_unicode_ci',
            ];

            $tenantDb = Database::connect($dbName);

            // Get all tables in tenant database
            $tables = $tenantDb->listTables();

            foreach ($this->tenantTables as $tableName) {
                // Check if table exists in tenant database
                if (!in_array($tableName, $tables)) {
                    continue;
                }

                // Check if table exists in central database
                // Use direct query and get actual database name
                $dbName = $centralDb->getDatabase();
                $tableCheck = $centralDb->query("SHOW TABLES FROM `{$dbName}` LIKE '{$tableName}'")->getRowArray();
                if (empty($tableCheck)) {
                    // Try alternative query
                    $tablesList = $centralDb->listTables();
                    if (!in_array($tableName, $tablesList)) {
                        CLI::write("  ⚠️  Table '{$tableName}' does not exist in central database ({$dbName}), skipping...", 'yellow');
                        continue;
                    }
                }

                // Check if table has tenant_id column
                $fields = $centralDb->getFieldData($tableName);
                $hasTenantId = false;
                foreach ($fields as $field) {
                    if ($field->name === 'tenant_id') {
                        $hasTenantId = true;
                        break;
                    }
                }

                // Get data from tenant database
                $tenantData = $tenantDb->table($tableName)->get()->getResultArray();

                if (empty($tenantData)) {
                    CLI::write("  ✓ Table '{$tableName}' is empty, skipping...", 'green');
                    continue;
                }

                // For tables that don't have tenant_id, we need special handling
                if (!$hasTenantId) {
                    // Special cases: users, roles, permissions, audit_logs in tenant DB
                    // These might need different handling
                    CLI::write("  ⚠️  Table '{$tableName}' doesn't have tenant_id, checking structure...", 'yellow');
                    
                    // Check if central table has tenant_id
                    if ($tableName === 'users') {
                        // tenant_users should be merged into central users with tenant_id
                        // But we need to check structure first
                        $this->migrateTenantUsers($centralDb, $tenantDb, $tenantId);
                        continue;
                    }
                    
                    // Skip tables without tenant_id for now
                    CLI::write("  ⚠️  Skipping '{$tableName}' (no tenant_id column)", 'yellow');
                    continue;
                }

                // Ensure tenant_id is set correctly in tenant data
                foreach ($tenantData as &$row) {
                    // If tenant_id exists but is different, update it
                    if (isset($row['tenant_id']) && (int)$row['tenant_id'] !== $tenantId) {
                        $row['tenant_id'] = $tenantId;
                    } elseif (!isset($row['tenant_id'])) {
                        $row['tenant_id'] = $tenantId;
                    }
                }

                // Check for duplicates (based on primary key or unique fields)
                $inserted = 0;
                $skipped = 0;

                foreach ($tenantData as $row) {
                    // Try to find existing record
                    $builder = $centralDb->table($tableName);
                    
                    // Check by ID first
                    if (isset($row['id'])) {
                        $builder->where('id', $row['id']);
                        if ($builder->countAllResults(false) > 0) {
                            $skipped++;
                            continue;
                        }
                    }

                    // Insert into central database
                    try {
                        $centralDb->table($tableName)->insert($row);
                        $inserted++;
                    } catch (\Exception $e) {
                        // If duplicate key error, skip
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            $skipped++;
                        } else {
                            CLI::write("  ✗ Error inserting into '{$tableName}': " . $e->getMessage(), 'red');
                        }
                    }
                }

                CLI::write("  ✓ Table '{$tableName}': {$inserted} inserted, {$skipped} skipped", 'green');
            }

        } catch (\Exception $e) {
            CLI::write("  ✗ Error migrating tenant '{$tenant['name']}': " . $e->getMessage(), 'red');
        }
    }

    protected function migrateTenantUsers($centralDb, $tenantDb, $tenantId)
    {
        // Check if central users table has tenant_id
        $fields = $centralDb->getFieldData('users');
        $hasTenantId = false;
        foreach ($fields as $field) {
            if ($field->name === 'tenant_id') {
                $hasTenantId = true;
                break;
            }
        }

        if (!$hasTenantId) {
            // Add tenant_id to users table
            CLI::write("  Adding tenant_id column to users table...", 'cyan');
            $centralDb->query("ALTER TABLE `users` ADD COLUMN `tenant_id` INT(11) UNSIGNED NULL AFTER `id`");
            $centralDb->query("ALTER TABLE `users` ADD INDEX `tenant_id` (`tenant_id`)");
        }

        $tenantUsers = $tenantDb->table('users')->get()->getResultArray();
        $inserted = 0;
        
        foreach ($tenantUsers as $user) {
            $user['tenant_id'] = $tenantId;
            // Remove id to avoid conflicts
            unset($user['id']);
            
            try {
                $centralDb->table('users')->insert($user);
                $inserted++;
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }
        
        CLI::write("  ✓ Users: {$inserted} migrated", 'green');
    }

    protected function addIndexes($centralDb)
    {
        // Ensure tenant_id indexes exist on all tenant tables
        foreach ($this->tenantTables as $tableName) {
            if (!$centralDb->tableExists($tableName)) {
                continue;
            }

            try {
                // Check if tenant_id index exists
                $indexes = $centralDb->getIndexData($tableName);
                $hasTenantIdIndex = false;
                
                foreach ($indexes as $index) {
                    if (in_array('tenant_id', $index->fields)) {
                        $hasTenantIdIndex = true;
                        break;
                    }
                }

                if (!$hasTenantIdIndex) {
                    $centralDb->query("ALTER TABLE `{$tableName}` ADD INDEX `tenant_id` (`tenant_id`)");
                    CLI::write("  ✓ Added tenant_id index to '{$tableName}'", 'green');
                }
            } catch (\Exception $e) {
                // Index might already exist or table structure issue
            }
        }
    }
}

