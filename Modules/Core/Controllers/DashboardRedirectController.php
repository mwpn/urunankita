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
            
            if ($userRow) {
                $tenantId = null;
                
                // Check if tenant_id column exists
                $columns = $db->getFieldNames('users');
                $hasTenantId = in_array('tenant_id', $columns);
                
                if ($hasTenantId && !empty($userRow['tenant_id'])) {
                    // Use tenant_id from users table
                    $tenantId = (int) $userRow['tenant_id'];
                } elseif (in_array($role, ['staff', 'tenant_staff'], true)) {
                    // For staff: resolve tenant from campaign_staff assignments
                    $campaignStaff = $db->table('campaign_staff')
                        ->where('user_id', (int) $userId)
                        ->join('campaigns', 'campaigns.id = campaign_staff.campaign_id', 'left')
                        ->select('campaigns.tenant_id')
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                    
                    if ($campaignStaff && !empty($campaignStaff['tenant_id'])) {
                        $tenantId = (int) $campaignStaff['tenant_id'];
                    } else {
                        // If no campaign assignment, try to find tenant from owner_id
                        // (for cases where staff might be associated via tenant owner)
                        $tenant = $db->table('tenants')
                            ->where('owner_id', (int) $userId)
                            ->limit(1)
                            ->get()
                            ->getRowArray();
                        
                        if ($tenant) {
                            $tenantId = (int) $tenant['id'];
                        }
                    }
                }
                
                if ($tenantId) {
                    $tenant = $db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                    }
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


