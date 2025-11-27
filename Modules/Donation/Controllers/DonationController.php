<?php

namespace Modules\Donation\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Donation\Services\DonationService;
use Modules\Analytics\Services\AnalyticsService;

class DonationController extends BaseController
{
    protected DonationService $donationService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->donationService = new DonationService();
    }

    /**
     * Admin: List donations from platform tenant only
     * GET /admin/donations
     */
    public function adminIndex()
    {
        $status = $this->request->getGet('status');
        $page = (int) ($this->request->getGet('page') ?? 1);

        // Get platform tenant ID
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // If platform tenant doesn't exist, return empty list
            $data = [
                'title' => 'Donasi - Admin Dashboard',
                'page_title' => 'Donasi',
                'sidebar_title' => 'UrunanKita',
                'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
                'user_role' => 'Super Admin',
                'donations' => [],
                'stats' => [
                    'total_donations' => 0,
                    'total_amount' => 0,
                    'pending_donations' => 0,
                    'paid_donations' => 0,
                ],
                'status_filter' => $status,
                'tenant_id_filter' => null,
                'tenants' => [],
            ];
            return view('Modules\\Donation\\Views\\admin_index', $data);
        }

        $platformTenantId = (int) $platform['id'];
        $campaignId = $this->request->getGet('campaign_id');

        // Get donations from platform tenant only (DataTables will handle pagination)
        $filters = [
            'status' => $status,
            'tenant_id' => $platformTenantId, // Always filter by platform tenant
            'campaign_id' => $campaignId ? (int) $campaignId : null,
            'page' => 1,
            'per_page' => 10000, // Get all data, DataTables will handle pagination
        ];
        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

        $result = $this->donationService->getAllDonations($filters);

        // Get platform tenant's campaigns for filter dropdown
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->where('tenant_id', $platformTenantId)->orderBy('created_at', 'DESC')->findAll();

        // Calculate statistics (same as tenant)
        $donationModel = new \Modules\Donation\Models\DonationModel();
        $allDonations = $donationModel->where('tenant_id', $platformTenantId)->findAll();
        
        // Only count paid donations for statistics
        $paidDonations = array_filter($allDonations, fn($d) => ($d['payment_status'] ?? '') === 'paid');
        
        $total_donations = array_sum(array_column($paidDonations, 'amount'));
        $total_donors = count(array_unique(array_filter(array_column($paidDonations, 'donor_email'))));
        $today = date('Y-m-d');
        $today_donations = array_sum(array_column(
            array_filter($paidDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today),
            'amount'
        ));
        $today_count = count(array_filter($paidDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today));
        $avg_donation = $total_donors > 0 ? ($total_donations / $total_donors) : 0;

        $data = [
            'title' => 'Donasi - Admin Dashboard',
            'page_title' => 'Donasi',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'donations' => $result['data'],
            'status_filter' => $status,
            'campaign_id_filter' => $campaignId,
            'campaigns' => $campaigns,
            'total_donations' => $total_donations,
            'total_donors' => $total_donors,
            'today_donations' => $today_donations,
            'today_count' => $today_count,
            'avg_donation' => $avg_donation,
        ];

        return view('Modules\\Donation\\Views\\admin_index', $data);
    }

    /**
     * Tenant: List donations for current tenant (no slug)
     * GET /tenant/donations
     */
    public function tenantIndex()
    {
        // Resolve tenant_id from session or derive from logged-in user (robust fallback)
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }

        if (!$tenantId) {
            return redirect()->to('/tenant/dashboard');
        }

        $status = $this->request->getGet('status'); // pending|paid|failed
        $campaignId = $this->request->getGet('campaign_id');
        $page = (int) ($this->request->getGet('page') ?? 1);

        // For staff, filter by assigned campaigns
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        $userId = $authUser['id'] ?? null;
        $allowedCampaignIds = null;
        
        if (in_array($userRole, ['staff', 'tenant_staff'], true) && $userId) {
            $db = \Config\Database::connect();
            $assignments = $db->table('campaign_staff')
                ->where('user_id', (int) $userId)
                ->get()
                ->getResultArray();
            
            // If staff has assignments, filter by those campaigns
            if (!empty($assignments)) {
                $allowedCampaignIds = array_column($assignments, 'campaign_id');
                // If specific campaign_id filter is set, verify it's in allowed campaigns
                if ($campaignId && !in_array((int) $campaignId, $allowedCampaignIds)) {
                    return redirect()->to('/tenant/donations')->with('error', 'Anda tidak memiliki akses ke urunan tersebut');
                }
            }
            // If no assignments, staff can see all campaigns (null = no filter)
        }

        $filters = array_filter([
            'tenant_id' => $tenantId,
            'status' => $status,
            'campaign_id' => $campaignId ? (int) $campaignId : null,
            'page' => $page,
            'per_page' => 20,
            'allowed_campaign_ids' => $allowedCampaignIds, // For staff filtering
        ], fn($v) => $v !== null && $v !== '');

        $result = $this->donationService->getAllDonations($filters);

        // Get tenant's campaigns for filter dropdown
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $allCampaigns = $campaignModel->where('tenant_id', $tenantId)->orderBy('created_at', 'DESC')->findAll();
        
        // For staff, filter campaigns based on assignment
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        $userId = $authUser['id'] ?? null;
        
        if (in_array($userRole, ['staff', 'tenant_staff'], true) && $userId) {
            $db = \Config\Database::connect();
            $assignments = $db->table('campaign_staff')
                ->where('user_id', (int) $userId)
                ->get()
                ->getResultArray();
            
            // If staff has assignments, filter campaigns
            if (!empty($assignments)) {
                $assignedCampaignIds = array_column($assignments, 'campaign_id');
                $campaigns = array_filter($allCampaigns, function($camp) use ($assignedCampaignIds) {
                    return in_array($camp['id'], $assignedCampaignIds);
                });
            } else {
                // No assignments = can see all campaigns
                $campaigns = $allCampaigns;
            }
        } else {
            // Owner/admin can see all campaigns
            $campaigns = $allCampaigns;
        }

        // Calculate statistics
        $donationModel = new \Modules\Donation\Models\DonationModel();
        $allDonations = $donationModel->where('tenant_id', $tenantId)->findAll();
        
        $total_donations = array_sum(array_column($allDonations, 'amount'));
        $total_donors = count(array_unique(array_column($allDonations, 'donor_email')));
        $today = date('Y-m-d');
        $today_donations = array_sum(array_column(
            array_filter($allDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today),
            'amount'
        ));
        $today_count = count(array_filter($allDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today));
        $avg_donation = $total_donors > 0 ? ($total_donations / $total_donors) : 0;

        $data = [
            'pageTitle' => 'Donasi Masuk',
            'userRole' => 'penggalang_dana',
            'title' => 'Donasi - Tenant',
            'page_title' => 'Donasi',
            'donations' => $result['data'],
            'status_filter' => $status,
            'campaign_id_filter' => $campaignId,
            'campaigns' => $campaigns,
            'total_donations' => $total_donations,
            'total_donors' => $total_donors,
            'today_donations' => $today_donations,
            'today_count' => $today_count,
            'avg_donation' => $avg_donation,
            'pagination' => [
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
                'total_pages' => $result['total_pages'],
            ],
        ];

        return view('Modules\\Donation\\Views\\tenant_index', $data);
    }

    /**
     * Create donation (Donasi dari Orang Baik)
     * POST /donation/create
     */
    public function create()
    {
        $data = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'donor_name' => $this->request->getPost('donor_name'),
            'donor_email' => $this->request->getPost('donor_email'),
            'donor_phone' => $this->request->getPost('donor_phone'),
            'amount' => $this->request->getPost('amount'),
            'is_anonymous' => $this->request->getPost('is_anonymous') === true || $this->request->getPost('is_anonymous') === 'true',
            'payment_method' => $this->request->getPost('payment_method'),
            'bank_account_id' => $this->request->getPost('bank_account_id'),
            'payment_proof' => $this->request->getPost('payment_proof'),
            'message' => $this->request->getPost('message'),
            'create_invoice' => $this->request->getPost('create_invoice') ?? false,
        ];

        if (empty($data['campaign_id']) || empty($data['amount'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Urunan dan jumlah donasi wajib diisi',
            ])->setStatusCode(400);
        }

        // If user is logged in, use their info
        $user = auth_user();
        if ($user) {
            $data['donor_id'] = $user['id'];
            if (empty($data['donor_name'])) {
                $data['donor_name'] = $user['name'] ?? null;
            }
            if (empty($data['donor_email'])) {
                $data['donor_email'] = $user['email'] ?? null;
            }
        }

        try {
            // Validate required fields - semua wajib meskipun anonim
            if (empty($data['donor_name'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama donatur wajib diisi',
                ])->setStatusCode(400);
            }

            if (empty($data['donor_email'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email wajib diisi',
                ])->setStatusCode(400);
            }

            if (empty($data['donor_phone'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nomor telepon wajib diisi',
                ])->setStatusCode(400);
            }

            if (empty($data['payment_method'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Metode pembayaran wajib dipilih',
                ])->setStatusCode(400);
            }

            // Parse amount (remove currency formatting)
            if (is_string($data['amount'])) {
                $data['amount'] = str_replace(['.', ',', ' '], '', $data['amount']);
                $data['amount'] = (float) $data['amount'];
            }

            if ($data['amount'] <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Jumlah donasi harus lebih dari 0',
                ])->setStatusCode(400);
            }

            $id = $this->donationService->create($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Donasi berhasil dibuat. Silakan lakukan pembayaran.',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat donasi. Silakan coba lagi.',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            // Log error for debugging
            log_message('error', 'Donation creation error: ' . $e->getMessage());
            log_message('error', 'Donation data: ' . json_encode($data));
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage() ?: 'Terjadi kesalahan saat membuat donasi. Silakan coba lagi.',
            ])->setStatusCode(400);
        }
    }

    /**
     * Get donations by campaign
     * GET /donation/campaign/{campaignId}
     */
    public function getByCampaign($campaignId)
    {
        $filters = [
            'limit' => $this->request->getGet('limit') ?? 50,
            'include_anonymous' => $this->request->getGet('include_anonymous') ?? true,
        ];

        $donations = $this->donationService->getByCampaign((int) $campaignId, $filters);
        $stats = $this->donationService->getStats((int) $campaignId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $donations,
            'stats' => $stats,
        ]);
    }

    /**
     * Get tenant bank accounts for donation instructions
     * GET /donation/bank-accounts/{campaignId}
     */
    public function getBankAccounts($campaignId)
    {
        try {
            $campaignModel = new \Modules\Campaign\Models\CampaignModel();
            $campaign = $campaignModel->find((int) $campaignId);

            if (!$campaign) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Urunan tidak ditemukan',
                ])->setStatusCode(404);
            }

            $bankAccounts = $this->donationService->getTenantBankAccounts($campaign['tenant_id']);

            return $this->response->setJSON([
                'success' => true,
                'data' => $bankAccounts,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Mark donation as paid (auto confirmation)
     * POST /donation/pay/{id}
     */
    public function pay($id)
    {
        $paymentProof = $this->request->getPost('payment_proof');

        try {
            $result = $this->donationService->markAsPaid((int) $id, $paymentProof, false);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Donasi telah dikonfirmasi sebagai dibayar',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        return in_array($userRole, ['superadmin', 'super_admin', 'admin'], true);
    }

    /**
     * Check if current user is tenant staff (can manage donations and reports)
     */
    protected function isTenantStaff(): bool
    {
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        return in_array($userRole, ['staff', 'tenant_staff', 'penggalang_dana'], true);
    }

    /**
     * Check if current user can manage tenant (owner, admin, or staff)
     */
    protected function canManageTenant(): bool
    {
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        return in_array($userRole, ['tenant_owner', 'tenant_admin', 'penggalang_dana', 'staff', 'tenant_staff'], true);
    }

    /**
     * Check if staff user can manage specific campaign
     * Returns true if:
     * - User is not staff (owner/admin can manage all)
     * - Staff has no campaign assignments (can manage all)
     * - Campaign is in staff's assigned campaigns
     */
    protected function canStaffManageCampaign(int $campaignId, int $userId): bool
    {
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        
        // If not staff, can manage
        if (!in_array($userRole, ['staff', 'tenant_staff'], true)) {
            return true;
        }
        
        // Check if staff has campaign assignments
        $db = \Config\Database::connect();
        $assignments = $db->table('campaign_staff')
            ->where('user_id', $userId)
            ->countAllResults();
        
        // If no assignments, staff can manage all campaigns
        if ($assignments === 0) {
            return true;
        }
        
        // Check if this campaign is assigned to staff
        $isAssigned = $db->table('campaign_staff')
            ->where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->countAllResults() > 0;
        
        return $isAssigned;
    }

    /**
     * Confirm donation payment manually (by tenant or admin)
     * POST /donation/confirm/{id}
     */
    public function confirm($id)
    {
        $isAdmin = $this->isAdmin();
        
        // For admin, get platform tenant ID
        if ($isAdmin) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if (!$platform) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant not found',
                ])->setStatusCode(404);
            }
            $tenantId = (int) $platform['id'];
        } else {
            // For tenant, resolve tenant_id from session or derive from logged-in user
            $tenantId = session()->get('tenant_id');
            if (!$tenantId) {
                $authUser = session()->get('auth_user') ?? [];
                $userId = $authUser['id'] ?? null;
                if ($userId) {
                    $db = \Config\Database::connect();
                    $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                    if ($userRow && !empty($userRow['tenant_id'])) {
                        $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                        if ($tenant) {
                            session()->set('tenant_id', (int) $tenant['id']);
                            session()->set('tenant_slug', $tenant['slug']);
                            session()->set('is_subdomain', false);
                            $tenantId = (int) $tenant['id'];
                        }
                    }
                }
            }
            if (!$tenantId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant not found',
                ])->setStatusCode(401);
            }
        }

        // Verify donation belongs to tenant (or allow admin to confirm any donation from platform tenant)
        $donation = $this->donationService->getDonationById((int) $id);
        if (!$donation) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        // For tenant, verify ownership; for admin, only allow platform tenant donations
        if (!$isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan atau akses ditolak',
            ])->setStatusCode(404);
        }
        
        // For staff, check campaign assignment
        if ($this->isTenantStaff() && !$isAdmin) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId && !$this->canStaffManageCampaign((int) $donation['campaign_id'], (int) $userId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengelola donasi dari urunan ini',
                ])->setStatusCode(403);
            }
        }
        
        // For admin, only allow confirming donations from platform tenant
        if ($isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin hanya dapat mengonfirmasi donasi dari platform tenant',
            ])->setStatusCode(403);
        }

        try {
            $result = $this->donationService->markAsPaid((int) $id, null, true);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Donasi telah dikonfirmasi secara manual',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengonfirmasi donasi',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Cancel donation (by tenant or admin)
     * POST /donation/cancel/{id}
     */
    public function cancel($id)
    {
        $isAdmin = $this->isAdmin();
        
        // For admin, get platform tenant ID
        if ($isAdmin) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if (!$platform) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant not found',
                ])->setStatusCode(404);
            }
            $tenantId = (int) $platform['id'];
        } else {
            // For tenant, resolve tenant_id from session or derive from logged-in user
            $tenantId = session()->get('tenant_id');
            if (!$tenantId) {
                $authUser = session()->get('auth_user') ?? [];
                $userId = $authUser['id'] ?? null;
                if ($userId) {
                    $db = \Config\Database::connect();
                    $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                    if ($userRow && !empty($userRow['tenant_id'])) {
                        $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                        if ($tenant) {
                            session()->set('tenant_id', (int) $tenant['id']);
                            session()->set('tenant_slug', $tenant['slug']);
                            session()->set('is_subdomain', false);
                            $tenantId = (int) $tenant['id'];
                        }
                    }
                }
            }
            if (!$tenantId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant not found',
                ])->setStatusCode(401);
            }
        }

        // Verify donation belongs to tenant (or allow admin to cancel any donation from platform tenant)
        $donation = $this->donationService->getDonationById((int) $id);
        if (!$donation) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        // For tenant, verify ownership; for admin, only allow platform tenant donations
        if (!$isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan atau akses ditolak',
            ])->setStatusCode(404);
        }
        
        // For admin, only allow canceling donations from platform tenant
        if ($isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin hanya dapat membatalkan donasi dari platform tenant',
            ])->setStatusCode(403);
        }

        // Only allow cancel for pending donations
        if ($donation['payment_status'] !== 'pending') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hanya donasi dengan status pending yang dapat dibatalkan',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->donationService->cancel((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Donasi telah dibatalkan',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membatalkan donasi',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Restore cancelled donation to pending status
     * POST /donation/restore/{id}
     */
    public function restoreToPending($id)
    {
        $isAdmin = $this->isAdmin();
        
        // For admin, get platform tenant ID
        if ($isAdmin) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if (!$platform) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant not found',
                ])->setStatusCode(404);
            }
            $tenantId = (int) $platform['id'];
        } else {
            // For tenant, resolve tenant_id from session or derive from logged-in user
            $tenantId = session()->get('tenant_id');
            if (!$tenantId) {
                $authUser = session()->get('auth_user') ?? [];
                $userId = $authUser['id'] ?? null;
                if ($userId) {
                    $db = \Config\Database::connect();
                    $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                    if ($userRow && !empty($userRow['tenant_id'])) {
                        $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                        if ($tenant) {
                            session()->set('tenant_id', (int) $tenant['id']);
                            session()->set('tenant_slug', $tenant['slug']);
                            session()->set('is_subdomain', false);
                            $tenantId = (int) $tenant['id'];
                        }
                    }
                }
            }
            if (!$tenantId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant not found',
                ])->setStatusCode(401);
            }
        }

        // Verify donation belongs to tenant (or allow admin to restore any donation from platform tenant)
        $donation = $this->donationService->getDonationById((int) $id);
        if (!$donation) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        // For tenant, verify ownership; for admin, only allow platform tenant donations
        if (!$isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Donasi tidak ditemukan atau akses ditolak',
            ])->setStatusCode(404);
        }
        
        // For staff, check campaign assignment
        if ($this->isTenantStaff() && !$isAdmin) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId && !$this->canStaffManageCampaign((int) $donation['campaign_id'], (int) $userId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengelola donasi dari urunan ini',
                ])->setStatusCode(403);
            }
        }
        
        // For admin, only allow restoring donations from platform tenant
        if ($isAdmin && $donation['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin hanya dapat mengembalikan donasi dari platform tenant',
            ])->setStatusCode(403);
        }

        // Only allow restore for cancelled donations
        if ($donation['payment_status'] !== 'cancelled') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hanya donasi dengan status cancelled yang dapat dikembalikan ke pending',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->donationService->restoreToPending((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Donasi telah dikembalikan ke status pending',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengembalikan donasi ke pending',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}
