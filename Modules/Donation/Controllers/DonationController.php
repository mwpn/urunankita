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
        
        $total_donations = array_sum(array_column($allDonations, 'amount'));
        $total_donors = count(array_unique(array_filter(array_column($allDonations, 'donor_email'))));
        $today = date('Y-m-d');
        $today_donations = array_sum(array_column(
            array_filter($allDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today),
            'amount'
        ));
        $today_count = count(array_filter($allDonations, fn($d) => date('Y-m-d', strtotime($d['created_at'])) === $today));
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

        $filters = array_filter([
            'tenant_id' => $tenantId,
            'status' => $status,
            'campaign_id' => $campaignId ? (int) $campaignId : null,
            'page' => $page,
            'per_page' => 20,
        ], fn($v) => $v !== null && $v !== '');

        $result = $this->donationService->getAllDonations($filters);

        // Get tenant's campaigns for filter dropdown
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->where('tenant_id', $tenantId)->orderBy('created_at', 'DESC')->findAll();

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
}
