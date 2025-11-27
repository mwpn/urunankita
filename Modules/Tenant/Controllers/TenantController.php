<?php

namespace Modules\Tenant\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Tenant\Services\TenantService;
use Config\Database;

class TenantController extends BaseController
{
    protected TenantService $tenantService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->tenantService = new TenantService();
    }

    /**
     * Admin: List tenants page
     * GET /admin/tenants
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $tenants = $this->tenantService->getAll([
            'status' => $status,
            'limit' => 100,
        ]);

        $data = [
            'title' => 'Penggalang - Admin Dashboard',
            'page_title' => 'Penggalang',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'tenants' => $tenants,
            'status_filter' => $status,
        ];

        return view('Modules\\Tenant\\Views\\admin_index', $data);
    }

    /**
     * Admin: Create tenant page
     * GET /admin/tenants/create
     */
    public function createPage()
    {
        $data = [
            'title' => 'Buat Penggalang - Admin Dashboard',
            'page_title' => 'Buat Penggalang',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'tenant' => null,
        ];

        return view('Modules\\Tenant\\Views\\admin_form', $data);
    }

    /**
     * Admin: Store tenant
     * POST /admin/tenants/store
     */
    public function store()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'domain' => $this->request->getPost('domain'),
            'youtube_url' => $this->request->getPost('youtube_url'),
            'status' => $this->request->getPost('status') ?? 'active',
            'can_create_without_verification' => $this->request->getPost('can_create_without_verification') ? 1 : 0,
            'can_use_own_bank_account' => $this->request->getPost('can_use_own_bank_account') ? 1 : 0,
        ];

        // Require owner email for creation (owner user must be created along with tenant)
        $ownerEmail = trim((string) $this->request->getPost('owner_email'));
        if (empty($ownerEmail)) {
            return redirect()->back()->withInput()->with('error', 'Email Owner wajib diisi');
        }

        if (empty($data['name'])) {
            return redirect()->back()->withInput()->with('error', 'Nama wajib diisi');
        }

        try {
            $id = $this->tenantService->create($data);
            if ($id) {
                // Create mandatory owner user
                $ownerName = trim((string) ($this->request->getPost('owner_name') ?? ($data['name'] . ' Owner')));
                $ownerPassword = (string) ($this->request->getPost('owner_password') ?: 'admin123');
                $passwordHash = password_hash($ownerPassword, PASSWORD_DEFAULT);

                // Check duplicate email within same tenant (shouldn't exist yet but safe check)
                $db = Database::connect();
                $exists = $db->table('users')
                    ->where('tenant_id', (int) $id)
                    ->where('email', $ownerEmail)
                    ->countAllResults();
                if ($exists > 0) {
                    return redirect()->back()->withInput()->with('error', 'Email Owner sudah terdaftar pada penggalang ini');
                }

                $db->table('users')->insert([
                    'tenant_id' => (int) $id,
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'password' => $passwordHash,
                    'role' => 'tenant_owner',
                    'status' => 'active',
                ]);

                return redirect()->to('/admin/tenants')->with('success', 'Penggalang & owner user berhasil dibuat');
            }
            return redirect()->back()->withInput()->with('error', 'Gagal membuat penggalang');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Edit tenant page
     * GET /admin/tenants/{id}/edit
     */
    public function edit(int $id)
    {
        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Penggalang tidak ditemukan');
        }

        // Get bank accounts
        $tenant = $this->tenantService->getById($id);
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->findWithBankAccounts($id);

        // Get existing owner user (if any)
        $ownerUser = null;
        $staffUsers = [];
        try {
            $db = Database::connect();
            $ownerUser = $db->table('users')
                ->where('tenant_id', (int) $id)
                ->where('role', 'tenant_owner')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();
            
            // Get staff users (staff, tenant_staff)
            $staffUsers = $db->table('users')
                ->where('tenant_id', (int) $id)
                ->whereIn('role', ['staff', 'tenant_staff'])
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
            
            // Get campaign assignments for each staff
            foreach ($staffUsers as &$staff) {
                $campaignIds = $db->table('campaign_staff')
                    ->where('user_id', (int) $staff['id'])
                    ->get()
                    ->getResultArray();
                $staff['assigned_campaign_ids'] = array_column($campaignIds, 'campaign_id');
            }
            
            // Get all campaigns for this tenant (for assignment dropdown)
            $campaigns = $db->table('campaigns')
                ->where('tenant_id', (int) $id)
                ->where('status', 'active')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            // ignore
            $campaigns = [];
        }

        $data = [
            'title' => 'Edit Penggalang - Admin Dashboard',
            'page_title' => 'Edit Penggalang',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'tenant' => $tenant,
            'owner_user' => $ownerUser,
            'staff_users' => $staffUsers ?? [],
            'campaigns' => $campaigns ?? [],
        ];

        return view('Modules\\Tenant\\Views\\admin_form', $data);
    }

    /**
     * Admin: Update tenant
     * POST /admin/tenants/{id}/update
     */
    public function updatePage(int $id)
    {
        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Penggalang tidak ditemukan');
        }

        $data = [];
        if ($this->request->getPost('name')) $data['name'] = $this->request->getPost('name');
        if ($this->request->getPost('slug')) $data['slug'] = $this->request->getPost('slug');
        if ($this->request->getPost('domain') !== null) $data['domain'] = $this->request->getPost('domain');
        if ($this->request->getPost('youtube_url') !== null) $data['youtube_url'] = $this->request->getPost('youtube_url');
        if ($this->request->getPost('status') !== null) $data['status'] = $this->request->getPost('status');
        $data['can_create_without_verification'] = $this->request->getPost('can_create_without_verification') ? 1 : 0;
        $data['can_use_own_bank_account'] = $this->request->getPost('can_use_own_bank_account') ? 1 : 0;
        
        // Handle bank_accounts if provided
        $bankAccounts = $this->request->getPost('bank_accounts');
        if ($bankAccounts !== null) {
            $data['bank_accounts'] = is_array($bankAccounts) ? $bankAccounts : json_decode($bankAccounts, true);
        }

        try {
            // Ensure we have at least one field to update
            if (empty($data)) {
                return redirect()->back()->withInput()->with('error', 'Tidak ada data yang diubah');
            }
            
            $result = $this->tenantService->update($id, $data);
            if ($result) {
                // Optional: update/create owner user if provided
                $ownerEmail = trim((string) $this->request->getPost('owner_email'));
                $ownerName = trim((string) ($this->request->getPost('owner_name') ?? ($tenant['name'] . ' Owner')));
                $ownerPassword = (string) $this->request->getPost('owner_password');

                if (!empty($ownerEmail)) {
                    $db = Database::connect();
                    // cari owner existing di tenant ini
                    $owner = $db->table('users')
                        ->where('tenant_id', (int) $id)
                        ->where('role', 'tenant_owner')
                        ->orderBy('id', 'ASC')
                        ->get()
                        ->getRowArray();

                    // Cek duplikasi email pada tenant ini (kecuali jika sama dengan owner saat ini)
                    $emailExists = $db->table('users')
                        ->where('tenant_id', (int) $id)
                        ->where('email', $ownerEmail)
                        ->when($owner !== null, function($builder) use ($owner) {
                            $builder->where('id !=', (int) $owner['id']);
                            return $builder;
                        })
                        ->countAllResults();
                    if ($emailExists > 0) {
                        return redirect()->back()->withInput()->with('error', 'Email Owner sudah digunakan oleh user lain pada penggalang ini');
                    }

                    $userData = [
                        'tenant_id' => (int) $id,
                        'name' => $ownerName,
                        'email' => $ownerEmail,
                        'role' => 'tenant_owner',
                        'status' => 'active',
                    ];
                    if (!empty($ownerPassword)) {
                        $userData['password'] = password_hash($ownerPassword, PASSWORD_DEFAULT);
                    }

                    if ($owner) {
                        // update
                        $db->table('users')->where('id', (int) $owner['id'])->update($userData);
                        return redirect()->to('/admin/tenants')->with('success', 'Penggalang & owner user berhasil diperbarui');
                    } else {
                        // create baru
                        if (empty($userData['password'])) {
                            $userData['password'] = password_hash('admin123', PASSWORD_DEFAULT);
                        }
                        $db->table('users')->insert($userData);
                        return redirect()->to('/admin/tenants')->with('success', 'Penggalang diperbarui & owner user dibuat');
                    }
                }

                return redirect()->to('/admin/tenants')->with('success', 'Penggalang berhasil diperbarui');
            }
            return redirect()->back()->with('error', 'Gagal memperbarui penggalang');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Delete tenant
     * POST /admin/tenants/{id}/delete
     */
    public function delete(int $id)
    {
        try {
            $result = $this->tenantService->delete($id);
            if ($result) {
                return redirect()->to('/admin/tenants')->with('success', 'Penggalang berhasil dihapus');
            }
            return redirect()->back()->with('error', 'Gagal menghapus penggalang');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get all tenants (superadmin only) - API
     * GET /tenant/list
     */
    public function list()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $tenants = $this->tenantService->getAll($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $tenants,
        ]);
    }

    /**
     * Get tenant by ID (superadmin only) - API
     * GET /tenant/show/{id}
     */
    public function show($id)
    {
        $tenant = $this->tenantService->getById((int) $id);

        if (!$tenant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $tenant,
        ]);
    }

    /**
     * Create tenant (superadmin only) - API
     * POST /tenant/create
     */
    public function create()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'domain' => $this->request->getPost('domain'),
            'owner_id' => $this->request->getPost('owner_id'),
            'status' => $this->request->getPost('status') ?? 'active',
        ];

        if (empty($data['name'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant name is required',
            ])->setStatusCode(400);
        }

        try {
            $id = $this->tenantService->create($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Tenant created successfully',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create tenant',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Update tenant (superadmin only) - API
     * POST /tenant/update/{id}
     */
    public function update($id)
    {
        $data = [];

        if ($this->request->getPost('name')) {
            $data['name'] = $this->request->getPost('name');
        }
        if ($this->request->getPost('domain') !== null) {
            $data['domain'] = $this->request->getPost('domain');
        }
        if ($this->request->getPost('status') !== null) {
            $data['status'] = $this->request->getPost('status');
        }
        if ($this->request->getPost('owner_id') !== null) {
            $data['owner_id'] = $this->request->getPost('owner_id');
        }
        if ($this->request->getPost('bank_accounts') !== null) {
            $bankAccounts = $this->request->getPost('bank_accounts');
            $data['bank_accounts'] = is_string($bankAccounts) 
                ? json_decode($bankAccounts, true) 
                : $bankAccounts;
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No data to update',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->tenantService->update((int) $id, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Tenant updated successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found or failed to update',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Delete tenant (superadmin only) - API
     * DELETE /tenant/delete/{id}
     */
    public function deleteAPI($id)
    {
        try {
            $result = $this->tenantService->delete((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Tenant deleted successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Create staff user for tenant
     * POST /admin/tenants/{id}/staff/create
     */
    public function createStaff(int $id)
    {
        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penggalang tidak ditemukan',
            ])->setStatusCode(404);
        }

        $name = trim($this->request->getPost('name') ?? '');
        $email = trim($this->request->getPost('email') ?? '');
        $password = trim($this->request->getPost('password') ?? 'admin123');
        $role = $this->request->getPost('role') ?? 'staff';

        if (empty($name) || empty($email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama dan email wajib diisi',
            ])->setStatusCode(400);
        }

        if (!in_array($role, ['staff', 'tenant_staff'], true)) {
            $role = 'staff';
        }

        try {
            $db = Database::connect();
            
            // Check duplicate email
            $exists = $db->table('users')
                ->where('tenant_id', (int) $id)
                ->where('email', $email)
                ->countAllResults();
            
            if ($exists > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email sudah terdaftar pada penggalang ini',
                ])->setStatusCode(400);
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $db->table('users')->insert([
                'tenant_id' => (int) $id,
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'role' => $role,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Staff berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menambahkan staff: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete staff user
     * POST /admin/tenants/{id}/staff/{userId}/delete
     */
    public function deleteStaff(int $id, int $userId)
    {
        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            return redirect()->back()->with('error', 'Penggalang tidak ditemukan');
        }

        try {
            $db = Database::connect();
            
            // Verify user belongs to this tenant and is staff
            $user = $db->table('users')
                ->where('id', $userId)
                ->where('tenant_id', (int) $id)
                ->whereIn('role', ['staff', 'tenant_staff'])
                ->get()
                ->getRowArray();
            
            if (!$user) {
                return redirect()->back()->with('error', 'Staff tidak ditemukan');
            }

            $db->table('users')->where('id', $userId)->delete();

            return redirect()->to("/admin/tenants/{$id}/edit")->with('success', 'Staff berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus staff: ' . $e->getMessage());
        }
    }

    /**
     * Assign campaigns to staff user
     * POST /admin/tenants/{id}/staff/{userId}/assign-campaigns
     */
    public function assignCampaigns(int $id, int $userId)
    {
        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penggalang tidak ditemukan',
            ])->setStatusCode(404);
        }

        try {
            $db = Database::connect();
            
            // Verify user belongs to this tenant and is staff
            $user = $db->table('users')
                ->where('id', $userId)
                ->where('tenant_id', (int) $id)
                ->whereIn('role', ['staff', 'tenant_staff'])
                ->get()
                ->getRowArray();
            
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Staff tidak ditemukan',
                ])->setStatusCode(404);
            }

            // Get campaign_ids from POST
            $campaignIds = $this->request->getPost('campaign_ids') ?? [];
            if (!is_array($campaignIds)) {
                $campaignIds = [];
            }
            $campaignIds = array_map('intval', $campaignIds);
            $campaignIds = array_filter($campaignIds);

            // Verify all campaigns belong to this tenant
            if (!empty($campaignIds)) {
                $validCampaigns = $db->table('campaigns')
                    ->where('tenant_id', (int) $id)
                    ->whereIn('id', $campaignIds)
                    ->countAllResults();
                
                if ($validCampaigns !== count($campaignIds)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Beberapa urunan tidak valid atau tidak milik penggalang ini',
                    ])->setStatusCode(400);
                }
            }

            // Delete existing assignments
            $db->table('campaign_staff')
                ->where('user_id', $userId)
                ->delete();

            // Insert new assignments (if any)
            if (!empty($campaignIds)) {
                $insertData = [];
                foreach ($campaignIds as $campaignId) {
                    $insertData[] = [
                        'campaign_id' => $campaignId,
                        'user_id' => $userId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                $db->table('campaign_staff')->insertBatch($insertData);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => empty($campaignIds) ? 'Staff sekarang bisa mengelola semua urunan' : 'Assignment urunan berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan assignment: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
