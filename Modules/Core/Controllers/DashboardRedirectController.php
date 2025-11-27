<?php

namespace Modules\Core\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class DashboardRedirectController extends BaseController
{
    public function index(): RedirectResponse
    {
        $authUser = session()->get('auth_user') ?? [];
        $role = $authUser['role'] ?? null;
        $userId = $authUser['id'] ?? null;

        // Ensure tenant context if user belongs to a tenant
        if (!in_array($role, ['super_admin'], true) && $userId) {
            $db = \Config\Database::connect();
            $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if ($userRow && !empty($userRow['tenant_id'])) {
                $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                if ($tenant) {
                    session()->set('tenant_id', (int) $tenant['id']);
                    session()->set('tenant_slug', $tenant['slug']);
                    session()->set('is_subdomain', false);
                }
            }
        }

        if ($role === 'super_admin') {
            return redirect()->to('/admin/dashboard');
        }

        // Tenant roles (including staff)
        if (in_array($role, ['tenant_owner', 'tenant_admin', 'tenant_user', 'penggalang_dana', 'staff', 'tenant_staff'], true)) {
            return redirect()->to('/tenant/dashboard');
        }

        // Default: go to login
        return redirect()->to('/auth/login');
    }
}


