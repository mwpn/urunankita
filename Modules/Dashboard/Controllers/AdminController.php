<?php

namespace Modules\Dashboard\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Tenant\Models\TenantModel;
use Config\Database;

class AdminController extends BaseController
{
    public function index()
    {
        $analyticsService = new AnalyticsService();
        $central = Database::connect();
        
        // Get platform stats
        $platformStats = $analyticsService->getPlatformStats();
        
        // Get recent tenants
        $tenantModel = new TenantModel();
        $recentTenants = $central->table('tenants')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        
        // Get active tenants count
        $activeTenants = $central->table('tenants')
            ->where('status', 'active')
            ->countAllResults();
        
        // Get total plans
        $totalPlans = $central->table('plans')->countAllResults();
        
        // Get total invoices
        $totalInvoices = $central->table('invoices')->countAllResults();
        
        $stats = [
            'total_tenants' => $platformStats['total_tenants'] ?? 0,
            'active_tenants' => $activeTenants,
            'total_campaigns' => $platformStats['total_campaigns'] ?? 0,
            'total_donations' => $platformStats['total_donations'] ?? 0,
            'total_beneficiaries' => $platformStats['total_beneficiaries'] ?? 0,
            'total_plans' => $totalPlans,
            'total_invoices' => $totalInvoices,
            'recent_donations_30d' => $platformStats['recent_donations_30d'] ?? 0,
            'recent_tenants' => $recentTenants,
        ];
        
        // Get user from auth_user session
        $authUser = session()->get('auth_user');
        
        // Normalize user role for sidebar
        $userRole = $authUser['role'] ?? 'superadmin';
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }
        
        // Sidebar data
        $data = [
            'stats' => $stats,
            'title' => 'Admin Dashboard',
            'pageTitle' => 'Dashboard',
            'page_title' => 'Dashboard',
            'sidebar_title' => 'UrunanKita',
            'user_name' => $authUser['name'] ?? session()->get('user_name') ?? 'Super Admin',
            'user_role' => $userRole,
            'userRole' => $userRole, // For template sidebar
        ];
        
        return view('Modules\\Dashboard\\Views\\admin_dashboard', $data);
    }
}


