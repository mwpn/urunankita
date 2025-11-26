<?php

namespace Modules\Tenant\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;

class TenantModel extends Model
{
    /**
     * TenantModel harus SELALU menggunakan CENTRAL database,
     * karena tabel 'tenants' ada di central database, bukan tenant database.
     * Jadi kita override constructor untuk force menggunakan default (central) connection.
     */
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        // CRITICAL: Always use central database (default connection)
        // DO NOT use BaseModel because it will auto-switch to tenant DB
        if ($db === null) {
            $db = Database::connect(); // This connects to default (central) database
        }
        
        parent::__construct($db, $validation);
        
        // Force set DBGroup to 'default' to ensure we use central DB
        $this->DBGroup = 'default';
    }
    
    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'slug',
        'db_name',
        'domain',
        'owner_id',
        'status',
        'bank_accounts',
        'can_create_without_verification',
        'can_use_own_bank_account',
        'youtube_url',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|max_length[150]',
        'slug' => 'required|max_length[100]|is_unique[tenants.slug,id,{id}]',
        'db_name' => 'required|max_length[150]',
        'status' => 'in_list[active,inactive,suspended]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get tenant by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get active tenants
     *
     * @return array
     */
    public function getActiveTenants(): array
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Get tenants with bank accounts parsed
     *
     * @return array
     */
    public function getTenantsWithBankAccounts(): array
    {
        $tenants = $this->findAll();
        
        foreach ($tenants as &$tenant) {
            // Parse bank accounts JSON
            if (isset($tenant['bank_accounts']) && $tenant['bank_accounts']) {
                $tenant['bank_accounts'] = json_decode($tenant['bank_accounts'], true) ?? [];
            } else {
                $tenant['bank_accounts'] = [];
            }
        }

        return $tenants;
    }

    /**
     * Get tenant with parsed bank accounts
     *
     * @param int $id
     * @return array|null
     */
    public function findWithBankAccounts(int $id): ?array
    {
        $tenant = $this->find($id);
        if ($tenant) {
            if (isset($tenant['bank_accounts']) && $tenant['bank_accounts']) {
                $tenant['bank_accounts'] = json_decode($tenant['bank_accounts'], true) ?? [];
            } else {
                $tenant['bank_accounts'] = [];
            }
        }
        return $tenant;
    }
}

