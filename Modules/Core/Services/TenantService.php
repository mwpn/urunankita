<?php

namespace Modules\Core\Services;

use Config\Database;
use CodeIgniter\Database\ConnectionInterface;

class TenantService
{
    protected array $slugToDb = [];

    public function registerTenantConnection(string $slug, string $databaseName): void
    {
        $this->slugToDb[$slug] = $databaseName;
    }

    public function getTenantDatabaseName(string $slug): ?string
    {
        return $this->slugToDb[$slug] ?? null;
    }

    public function connect(string $slug): ConnectionInterface
    {
        $dbKey = 'tenant_' . $slug;
        return Database::connect($dbKey);
    }

    /**
     * Resolve tenant from slug and set session context
     * Simplified for single database - no database connection needed
     *
     * @param string $slug
     * @return ConnectionInterface (returns default database connection)
     */
    public function resolveAndConnectFromSlug(string $slug): ConnectionInterface
    {
        $central = Database::connect();
        $tenant = $central->table('tenants')->where('slug', $slug)->where('status', 'active')->get()->getRowArray();
        if (! $tenant) {
            throw new \RuntimeException('Tenant tidak ditemukan atau nonaktif');
        }

        // Simplified: Just set session context, no need to connect to separate database
        session()->set('tenant_id', (int) $tenant['id']);
        session()->set('tenant_slug', $slug);
        
        // Return default database connection (single database)
        return $central;
    }
}


