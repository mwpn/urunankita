<?php

namespace Modules\Auth\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Database;
use Config\Services;

class AuthController extends BaseController
{
    public function login()
    {
        // Get platform settings for favicon and logo
        $settingService = Services::setting();
        
        $settings = [
            'site_name' => $settingService->get('site_name', 'Urunankita', 'global', null),
            'site_logo' => $settingService->get('site_logo', null, 'global', null),
            'site_favicon' => $settingService->get('site_favicon', null, 'global', null),
        ];
        
        return view('Modules\\Auth\\Views\\login', [
            'settings' => $settings,
        ]);
    }

    public function doLogin()
    {
        $request = Services::request();
        $email = $request->getPost('email');
        $password = $request->getPost('password');
        // Single DB: if subdomain context, authenticate against central users filtered by tenant_id
        $isSubdomain = session()->get('is_subdomain') === true;
        $tenantSlug = session()->get('tenant_slug');
        $tenantId = session()->get('tenant_id');

        // Fallback: if subdomain context not set (e.g., filter tidak jalan), coba resolve dari host
        if ((!$isSubdomain || !$tenantId) && !empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $maybeSlug = strtolower($parts[0]);
                if (!in_array($maybeSlug, ['www', 'api', 'admin', 'app'])) {
                    // Coba cari tenant berdasarkan slug
                    $db = Database::connect();
                    $tenant = $db->table('tenants')
                        ->where('slug', $maybeSlug)
                        ->where('status', 'active')
                        ->get()
                        ->getRowArray();
                    if ($tenant) {
                        $isSubdomain = true;
                        $tenantSlug = $maybeSlug;
                        $tenantId = (int) $tenant['id'];
                        session()->set('is_subdomain', true);
                        session()->set('tenant_slug', $tenantSlug);
                        session()->set('tenant_id', $tenantId);
                    }
                }
            }
        }

        if ($isSubdomain && $tenantId) {
            $db = Database::connect();
            $user = $db->table('users')
                ->where('tenant_id', (int) $tenantId)
                ->where('email', $email)
                ->get()
                ->getRowArray();

            if ($user && password_verify($password, $user['password'])) {
                Services::modulesCoreAuth()->login([
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?: 'tenant_user',
                ]);
                return redirect()->to('/dashboard');
            }
            return redirect()->back()->with('error', 'Kredensial tenant tidak valid');
        }

        // Superadmin (central)
        $db = Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();
        if ($user && password_verify($password, $user['password'])) {
            $roleRaw = $user['role'] ?? null;
            // Normalize role naming
            if ($roleRaw === 'superadmin') {
                $roleRaw = 'super_admin';
            }
            $role = $roleRaw ?: 'tenant_user';

            // If user belongs to a tenant, set tenant context in session (single DB)
            $tenantId = null;
            
            // Check if tenant_id column exists
            $columns = $db->getFieldNames('users');
            $hasTenantId = in_array('tenant_id', $columns);
            
            if ($hasTenantId && !empty($user['tenant_id']) && $role !== 'super_admin') {
                $tenantId = (int) $user['tenant_id'];
            } elseif (in_array($role, ['staff', 'tenant_staff'], true) && $role !== 'super_admin') {
                // For staff: resolve tenant from campaign_staff assignments
                $campaignStaff = $db->table('campaign_staff')
                    ->where('user_id', (int) $user['id'])
                    ->join('campaigns', 'campaigns.id = campaign_staff.campaign_id', 'left')
                    ->select('campaigns.tenant_id')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($campaignStaff && !empty($campaignStaff['tenant_id'])) {
                    $tenantId = (int) $campaignStaff['tenant_id'];
                } else {
                    // If no campaign assignment, try to find tenant from owner_id
                    $tenant = $db->table('tenants')
                        ->where('owner_id', (int) $user['id'])
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                    
                    if ($tenant) {
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
            
            if ($tenantId) {
                $tenantRow = $db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
                if ($tenantRow) {
                    session()->set('tenant_id', (int) $tenantRow['id']);
                    session()->set('tenant_slug', $tenantRow['slug']);
                    session()->set('is_subdomain', false);
                }
            }

            Services::modulesCoreAuth()->login([
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $role,
            ]);
            return redirect()->to('/dashboard');
        }
        return redirect()->back()->with('error', 'Kredensial tidak valid');
    }

    public function logout()
    {
        Services::modulesCoreAuth()->logout();
        return redirect()->to('/auth/login');
    }

    public function registerTenant()
    {
        return view('Modules\\Auth\\Views\\register_tenant');
    }

    public function doRegisterTenant()
    {
        $request = Services::request();
        $name = $request->getPost('name');
        $slug = $request->getPost('slug');
        $ownerEmail = $request->getPost('email');
        $ownerPassword = $request->getPost('password');

        if (! $slug || ! $name || ! $ownerEmail || ! $ownerPassword) {
            return redirect()->back()->with('error', 'Data tidak lengkap');
        }

        $db = Database::connect();
        // Simplified: No need to create separate database
        // Insert central tenant record
        $tenantId = $db->table('tenants')->insert([
            'name' => $name,
            'slug' => $slug,
            'db_name' => null, // No longer needed
            'status' => 'active',
        ]);

        // Create owner user in central database with tenant_id
        $db->table('users')->insert([
            'tenant_id' => $tenantId,
            'name' => $name . ' Owner',
            'email' => $ownerEmail,
            'password' => password_hash($ownerPassword, PASSWORD_DEFAULT),
            'role' => 'tenant_owner',
            'status' => 'active',
        ]);

        return redirect()->to('/auth/login')->with('success', 'Tenant terdaftar. Silakan login sebagai tenant.');
    }
}


