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
                        // If no campaign assignment, try alternative methods:
                        // 1. Check if staff is owner of a tenant
                        $tenant = $db->table('tenants')
                            ->where('owner_id', (int) $userId)
                            ->limit(1)
                            ->get()
                            ->getRowArray();
                        
                        if ($tenant) {
                            $tenantId = (int) $tenant['id'];
                        } else {
                            // 2. Fallback: Get first active tenant and assign staff to first campaign
                            // This is a workaround for staff created before auto-assign feature
                            $firstTenant = $db->table('tenants')
                                ->where('status', 'active')
                                ->orderBy('id', 'ASC')
                                ->limit(1)
                                ->get()
                                ->getRowArray();
                            
                            if ($firstTenant) {
                                $firstCampaign = $db->table('campaigns')
                                    ->where('tenant_id', (int) $firstTenant['id'])
                                    ->orderBy('id', 'ASC')
                                    ->limit(1)
                                    ->get()
                                    ->getRowArray();
                                
                                if ($firstCampaign) {
                                    // Auto-assign staff to first campaign as fallback
                                    $existing = $db->table('campaign_staff')
                                        ->where('campaign_id', (int) $firstCampaign['id'])
                                        ->where('user_id', (int) $userId)
                                        ->countAllResults();
                                    
                                    if ($existing === 0) {
                                        $db->table('campaign_staff')->insert([
                                            'campaign_id' => (int) $firstCampaign['id'],
                                            'user_id' => (int) $userId,
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);
                                    }
                                    
                                    $tenantId = (int) $firstTenant['id'];
                                }
                            }
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


