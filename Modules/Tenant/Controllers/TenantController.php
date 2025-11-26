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
        try {
            $db = Database::connect();
            $ownerUser = $db->table('users')
                ->where('tenant_id', (int) $id)
                ->where('role', 'tenant_owner')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            // ignore
        }

        $data = [
            'title' => 'Edit Penggalang - Admin Dashboard',
            'page_title' => 'Edit Penggalang',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'tenant' => $tenant,
            'owner_user' => $ownerUser,
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
}
