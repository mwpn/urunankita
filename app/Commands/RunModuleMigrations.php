<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Config\Services;

class RunModuleMigrations extends BaseCommand
{
    protected $group       = 'Migration';
    protected $name        = 'migrate:modules';
    protected $description = 'Run all module migrations to default database';
    protected $usage       = 'migrate:modules';

    public function run(array $params)
    {
        CLI::write('Running all module migrations...', 'yellow');
        
        // Ensure we're using default connection
        Database::connect('default', true);
        
        $modules = [
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
            'Modules\\Content',
        ];
        
        foreach ($modules as $namespace) {
            try {
                $migrations = Services::migrations();
                $migrations->setNamespace($namespace);
                $migrations->setGroup('default');
                
                // Set path explicitly for module migrations
                $namespaceParts = explode('\\', $namespace);
                if (count($namespaceParts) >= 2 && $namespaceParts[0] === 'Modules') {
                    $moduleName = $namespaceParts[1];
                    $migrationPath = ROOTPATH . 'Modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';
                    
                    if (!is_dir($migrationPath)) {
                        CLI::write("  ⚠️  {$namespace} - Migration directory not found: {$migrationPath}", 'yellow');
                        continue;
                    }
                    
                    // Use reflection to set path
                    $reflection = new \ReflectionClass($migrations);
                    if ($reflection->hasProperty('path')) {
                        $pathProperty = $reflection->getProperty('path');
                        $pathProperty->setAccessible(true);
                        $pathProperty->setValue($migrations, $migrationPath);
                    }
                }
                
                // Get all migration files
                $allMigrations = $migrations->findMigrations();
                
                if (empty($allMigrations)) {
                    CLI::write("  ⚠️  {$namespace} - No migration files found", 'yellow');
                    continue;
                }
                
                CLI::write("  Running migrations for {$namespace}... (" . count($allMigrations) . " files)", 'cyan');
                
                // Run latest migrations
                $result = $migrations->latest();
                
                if ($result) {
                    CLI::write("  ✅ {$namespace} - Migrations completed", 'green');
                } else {
                    // Check if already migrated
                    $history = $migrations->getHistory('default');
                    $count = count(array_filter($history, function($m) use ($namespace) {
                        return strpos($m['namespace'] ?? '', $namespace) !== false;
                    }));
                    
                    if ($count > 0) {
                        CLI::write("  ℹ️  {$namespace} - Already migrated ({$count} migrations)", 'cyan');
                    } else {
                        CLI::write("  ⚠️  {$namespace} - No migrations run", 'yellow');
                    }
                }
            } catch (\Exception $e) {
                CLI::write("  ❌ {$namespace} - Error: " . $e->getMessage(), 'red');
            }
        }
        
        CLI::write('Done!', 'green');
    }
}

