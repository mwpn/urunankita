<?php

namespace Modules\Campaign\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Campaign\Services\CampaignService;

class CampaignController extends BaseController
{
    // ===== Tenant routes without slug (wrapper) =====
    public function indexNoSlug()
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            // Fallback: derive tenant from logged-in user
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }
        return $this->index($slug);
    }

    public function createPageNoSlug()
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }
        return $this->createPage($slug);
    }

    public function storeNoSlug()
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }
        return $this->store($slug);
    }

    public function editNoSlug(int $id)
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }
        return $this->edit($slug, $id);
    }

    public function updateNoSlug(int $id)
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }
        return $this->updatePage($slug, $id);
    }

    /**
     * Complete campaign (no slug) - for ongoing/open campaigns
     * POST /tenant/campaigns/{id}/complete
     */
    public function completeNoSlug(int $id)
    {
        // Get tenant context
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            $user = session()->get('auth_user');
            if ($user && isset($user['tenant_id'])) {
                $tenantId = $user['tenant_id'];
                session()->set('tenant_id', $tenantId);
            } else {
                return redirect()->to('/tenant/dashboard')->with('error', 'Tenant context tidak ditemukan');
            }
        }

        // Find campaign
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Verify ownership
        if ($campaign['tenant_id'] != $tenantId) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Akses ditolak');
        }

        // Only allow completing ongoing/open campaigns that are active
        if (($campaign['campaign_type'] ?? 'target_based') === 'target_based') {
            return redirect()->to('/tenant/campaigns')->with('error', 'Hanya urunan open yang dapat ditandai selesai');
        }

        if (($campaign['status'] ?? '') !== 'active') {
            return redirect()->to('/tenant/campaigns')->with('error', 'Hanya urunan aktif yang dapat ditandai selesai');
        }

        try {
            // Update status to completed
            $result = $this->campaignService->update($id, [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

            if ($result) {
                return redirect()->to('/tenant/campaigns')->with('success', 'Urunan berhasil ditandai sebagai selesai');
            }
            return redirect()->to('/tenant/campaigns')->with('error', 'Gagal menandai urunan sebagai selesai');
        } catch (\Exception $e) {
            return redirect()->to('/tenant/campaigns')->with('error', $e->getMessage());
        }
    }

    public function deleteNoSlug(int $id)
    {
        $slug = session()->get('tenant_slug');
        if (!$slug) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $slug = $tenant['slug'];
                    }
                }
            }
            if (!$slug) {
                return redirect()->to('/auth/login')->with('error', 'Tenant not found');
            }
        }
        return $this->delete($slug, $id);
    }

    public function viewNoSlug(int $id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    session()->set('tenant_id', (int) $userRow['tenant_id']);
                    $tenantId = (int) $userRow['tenant_id'];
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        $campaign = $this->campaignService->getById($id);
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Get beneficiary info
        $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
        $beneficiary = null;
        if (isset($campaign['beneficiary_id']) && $campaign['beneficiary_id']) {
            $beneficiary = $beneficiaryModel->find($campaign['beneficiary_id']);
        }

        // Get all beneficiaries for withdrawal form
        $beneficiaries = $beneficiaryModel
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Get donations
        $donationService = \Config\Services::donation();
        $donations = $donationService->getByCampaign($id, ['limit' => 50]);
        $donationStats = $donationService->getStats($id);

        // Get updates
        $updateService = \Config\Services::campaignUpdate();
        $updates = $updateService->getByCampaign($id, ['limit' => 20]);

        // Get comments
        $discussionService = \Config\Services::discussion();
        $comments = $discussionService->getComments($id, ['limit' => 20]);

        // Get detailed financial report (Masuk & Penggunaan Dana)
        $reportService = \Config\Services::report();
        $financialReport = $reportService->getCampaignFinancialDetail($id);

        // Get tenant info with YouTube URL
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($tenantId);

        // Parse images if JSON
        if (!empty($campaign['images'])) {
            if (is_string($campaign['images'])) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            }
        } else {
            $campaign['images'] = [];
        }

        // Format featured image
        $featuredImage = $campaign['featured_image'] ?? '';
        if ($featuredImage && !preg_match('~^https?://~', $featuredImage) && strpos($featuredImage, '/uploads/') !== 0) {
            $featuredImage = '/uploads/' . ltrim($featuredImage, '/');
        }

        $data = [
            'pageTitle' => 'Detail Urunan',
            'userRole' => 'penggalang_dana',
            'title' => 'Detail Urunan - Dashboard',
            'page_title' => 'Detail Urunan',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => session()->get('auth_user')['name'] ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'campaign' => $campaign,
            'tenant' => $tenant,
            'beneficiary' => $beneficiary,
            'beneficiaries' => $beneficiaries,
            'donations' => $donations,
            'donation_stats' => $donationStats,
            'updates' => $updates,
            'comments' => $comments,
            'featured_image' => $featuredImage,
            'financial_report' => $financialReport,
        ];

        return view('Modules\\Campaign\\Views\\tenant_view', $data);
    }

    protected CampaignService $campaignService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->campaignService = new CampaignService();
    }

    /**
     * Get public campaigns (Lihat Urunan)
     * GET /campaign/list
     */
    public function list()
    {
        $filters = [
            'category' => $this->request->getGet('category'),
            'campaign_type' => $this->request->getGet('campaign_type'),
            'limit' => $this->request->getGet('limit') ?? 20,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $campaigns = $this->campaignService->getPublicCampaigns($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * Get campaign by slug (Detail Urunan)
     * GET /campaign/{slug}
     */
    public function show($slug)
    {
        $campaign = $this->campaignService->getBySlug($slug);

        if (!$campaign) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Urunan tidak ditemukan',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $campaign,
        ]);
    }

    /**
     * Get my campaigns (Penggalang Urunan)
     * GET /campaign/my-campaigns
     */
    public function myCampaigns()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'status' => $this->request->getGet('status'),
            'category' => $this->request->getGet('category'),
            'campaign_type' => $this->request->getGet('campaign_type'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $campaigns = $this->campaignService->getByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * List campaigns page (Tenant Dashboard)
     * GET /tenant/{slug}/campaigns
     */
    public function index(string $slug)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            return redirect()->to('/auth/login')->with('error', 'Tenant not found');
        }

        $status = $this->request->getGet('status');
        $campaigns = $this->campaignService->getByTenant($tenantId, [
            'status' => $status,
            'limit' => 50,
        ]);

        $data = [
            'title' => 'Urunan - ' . ($tenantId ? 'Tenant' : 'Dashboard'),
            'pageTitle' => 'Urunan',
            'page_title' => 'Urunan',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'userRole' => 'penggalang_dana', // For template sidebar
            'campaigns' => $campaigns,
            'status_filter' => $status,
        ];

        return view('Modules\\Campaign\\Views\\index', $data);
    }

    /**
     * Create campaign page
     * GET /tenant/{slug}/campaigns/create
     */
    public function createPage(string $slug)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            // Fallback resolve from authenticated user
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
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
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        // Get tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($tenantId);
        $canUseOwnBank = !empty($tenant['can_use_own_bank_account']) && $tenant['can_use_own_bank_account'] == 1;

        // Get tenant payment methods (bank-transfer only) if can use own bank
        $paymentMethods = [];
        if ($canUseOwnBank) {
            $paymentMethodModel = new \Modules\Setting\Models\PaymentMethodModel();
            $paymentMethods = $paymentMethodModel->where('tenant_id', $tenantId)
                ->where('type', 'bank-transfer')
                ->where('enabled', 1)
                ->findAll();
        }

        // Get beneficiaries for dropdown
        $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
        $beneficiaries = $beneficiaryModel->where('tenant_id', $tenantId)->findAll();

        $data = [
            'pageTitle' => 'Buat Urunan Baru',
            'userRole' => 'penggalang_dana',
            'title' => 'Buat Urunan - Dashboard',
            'page_title' => 'Buat Urunan',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'beneficiaries' => $beneficiaries,
            'campaign' => null,
            'can_use_own_bank_account' => $canUseOwnBank,
            'payment_methods' => $paymentMethods,
        ];

        return view('Modules\\Campaign\\Views\\form', $data);
    }

    /**
     * Store campaign (Tenant Dashboard)
     * POST /tenant/{slug}/campaigns/store
     */
    public function store(string $slug)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    session()->set('tenant_id', (int) $userRow['tenant_id']);
                    $tenantId = (int) $userRow['tenant_id'];
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        // Tentukan status default berdasarkan setting tenant
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($tenantId);
        $canCreateWithoutVerification = (bool) ($tenant['can_create_without_verification'] ?? 0);
        // Campaign langsung aktif tanpa verifikasi, tapi diketahui admin
        $defaultStatus = 'active';

        $campaignType = $this->request->getPost('campaign_type') ?? 'target_based';
        
        // Parse target_amount (remove currency formatting)
        $targetAmount = $this->request->getPost('target_amount');
        if ($targetAmount !== null && $targetAmount !== '') {
            // Remove dots, commas, and spaces (format: 50.000.000 -> 50000000)
            $targetAmount = str_replace(['.', ',', ' '], '', $targetAmount);
            // Convert to integer (remove decimal part)
            $targetAmount = (int) $targetAmount;
        } else {
            $targetAmount = null;
        }
        
        // Handle deadline - only for target_based
        $deadline = null;
        if ($campaignType === 'target_based') {
            $deadlineInput = $this->request->getPost('deadline');
            // Check if "terus menerus" checkbox is checked
            $terusMenerus = $this->request->getPost('terus_menerus') === 'on' || $this->request->getPost('terus_menerus') === '1';
            if (!$terusMenerus && !empty($deadlineInput)) {
                $deadline = $deadlineInput;
            }
        } else {
            // For ongoing, check deadline-open
            $deadlineInput = $this->request->getPost('deadline');
            $terusMenerusOpen = $this->request->getPost('terus_menerus_open') === 'on' || $this->request->getPost('terus_menerus_open') === '1';
            if (!$terusMenerusOpen && !empty($deadlineInput)) {
                $deadline = $deadlineInput;
            }
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'campaign_type' => $campaignType,
            'target_amount' => $targetAmount,
            'category' => $this->request->getPost('category'),
            'featured_image' => $this->request->getPost('featured_image'),
            'deadline' => $deadline,
            'beneficiary_id' => $this->request->getPost('beneficiary_id'),
            'latitude' => $this->request->getPost('latitude') ?: null,
            'longitude' => $this->request->getPost('longitude') ?: null,
            'location_address' => $this->request->getPost('location_address') ?: null,
            'status' => $this->request->getPost('status') ?? $defaultStatus,
            'is_priority' => $this->request->getPost('is_priority') ? 1 : 0,
            'use_tenant_bank_account' => $this->request->getPost('use_tenant_bank_account') ? 1 : 0,
            'payment_method_id' => $this->request->getPost('payment_method_id') ? (int) $this->request->getPost('payment_method_id') : null,
        ];

        if (empty($data['title'])) {
            return redirect()->back()->withInput()->with('error', 'Judul wajib diisi');
        }

        // Validate target_amount for target_based
        if ($campaignType === 'target_based' && (empty($targetAmount) || $targetAmount <= 0)) {
            return redirect()->back()->withInput()->with('error', 'Target dana wajib diisi untuk urunan target based');
        }

        try {
            // Handle file uploads (featured + gallery)
            $featuredImageFile = $this->request->getFile('featured_image_file');
            if ($featuredImageFile && $featuredImageFile->isValid() && !$featuredImageFile->hasMoved()) {
                $uploadedFile = \Modules\File\Config\Services::storage()->upload($featuredImageFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                $data['featured_image'] = '/uploads/' . ltrim($uploadedFile['path'], '/');
            }

            $imagesFiles = $this->request->getFiles('images_files');
            $uploadedImages = [];
            if ($imagesFiles && isset($imagesFiles['images_files'])) {
                foreach ($imagesFiles['images_files'] as $imgFile) {
                    if ($imgFile->isValid() && !$imgFile->hasMoved()) {
                        $uploadedImg = \Modules\File\Config\Services::storage()->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                        $uploadedImages[] = '/uploads/' . ltrim($uploadedImg['path'], '/');
                    }
                }
            }
            if (!empty($uploadedImages)) {
                $data['images'] = json_encode($uploadedImages);
            }

            $id = $this->campaignService->create($data);
            if ($id) {
                return redirect()->to('/tenant/campaigns')->with('success', 'Urunan berhasil dibuat');
            }
            
            // Log error for debugging
            log_message('error', 'Campaign creation failed - ID is false. Data: ' . json_encode($data));
            return redirect()->back()->withInput()->with('error', 'Gagal membuat urunan. Silakan coba lagi atau hubungi administrator.');
        } catch (\Exception $e) {
            // Log exception for debugging
            log_message('error', 'Campaign creation exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit campaign page
     * GET /tenant/{slug}/campaigns/{id}/edit
     */
    public function edit(string $slug, int $id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    session()->set('tenant_id', (int) $userRow['tenant_id']);
                    $tenantId = (int) $userRow['tenant_id'];
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        $campaign = $this->campaignService->getById($id);
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Get beneficiaries
        $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
        $beneficiaries = $beneficiaryModel->where('tenant_id', $tenantId)->findAll();

        $data = [
            'pageTitle' => 'Edit Urunan',
            'userRole' => 'penggalang_dana',
            'title' => 'Edit Urunan - Dashboard',
            'page_title' => 'Edit Urunan',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'User',
            'user_role' => 'Penggalang Urunan',
            'campaign' => $campaign,
            'beneficiaries' => $beneficiaries,
        ];

        return view('Modules\\Campaign\\Views\\form', $data);
    }

    /**
     * Update campaign (Tenant Dashboard)
     * POST /tenant/{slug}/campaigns/{id}/update
     */
    public function updatePage(string $slug, int $id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    session()->set('tenant_id', (int) $userRow['tenant_id']);
                    $tenantId = (int) $userRow['tenant_id'];
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        $campaign = $this->campaignService->getById($id);
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        $campaignType = $this->request->getPost('campaign_type') ?? $campaign['campaign_type'] ?? 'target_based';
        
        $data = [];
        if ($this->request->getPost('title')) $data['title'] = $this->request->getPost('title');
        if ($this->request->getPost('description') !== null) $data['description'] = $this->request->getPost('description');
        if ($this->request->getPost('campaign_type') !== null) {
            $data['campaign_type'] = $this->request->getPost('campaign_type');
            $campaignType = $data['campaign_type'];
        }
        
        // Parse target_amount (remove currency formatting)
        if ($this->request->getPost('target_amount') !== null) {
            $targetAmount = $this->request->getPost('target_amount');
            if ($targetAmount !== '') {
                // Remove dots, commas, and spaces (format: 50.000.000 -> 50000000)
                $targetAmount = str_replace(['.', ',', ' '], '', $targetAmount);
                // Convert to integer (remove decimal part)
                $data['target_amount'] = (int) $targetAmount;
            } else {
                $data['target_amount'] = null;
            }
        }
        
        if ($this->request->getPost('category') !== null) $data['category'] = $this->request->getPost('category');
        if ($this->request->getPost('featured_image') !== null) $data['featured_image'] = $this->request->getPost('featured_image');
        
        // Handle deadline
        if ($this->request->getPost('deadline') !== null) {
            $deadlineInput = $this->request->getPost('deadline');
            if ($campaignType === 'target_based') {
                $terusMenerus = $this->request->getPost('terus_menerus') === 'on' || $this->request->getPost('terus_menerus') === '1';
                $data['deadline'] = (!$terusMenerus && !empty($deadlineInput)) ? $deadlineInput : null;
            } else {
                $terusMenerusOpen = $this->request->getPost('terus_menerus_open') === 'on' || $this->request->getPost('terus_menerus_open') === '1';
                $data['deadline'] = (!$terusMenerusOpen && !empty($deadlineInput)) ? $deadlineInput : null;
            }
        }
        
        if ($this->request->getPost('beneficiary_id') !== null) $data['beneficiary_id'] = $this->request->getPost('beneficiary_id');
        if ($this->request->getPost('latitude') !== null) $data['latitude'] = $this->request->getPost('latitude') ?: null;
        if ($this->request->getPost('longitude') !== null) $data['longitude'] = $this->request->getPost('longitude') ?: null;
        if ($this->request->getPost('location_address') !== null) $data['location_address'] = $this->request->getPost('location_address') ?: null;
        if ($this->request->getPost('status') !== null) $data['status'] = $this->request->getPost('status');
        $data['is_priority'] = $this->request->getPost('is_priority') ? 1 : 0;

        try {
            // Handle file uploads (featured + gallery)
            $featuredImageFile = $this->request->getFile('featured_image_file');
            if ($featuredImageFile && $featuredImageFile->isValid() && !$featuredImageFile->hasMoved()) {
                $uploadedFile = \Modules\File\Config\Services::storage()->upload($featuredImageFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                $data['featured_image'] = $uploadedFile['path'];
            }

            $imagesFiles = $this->request->getFiles('images_files');
            $uploadedImages = [];
            if ($imagesFiles && isset($imagesFiles['images_files'])) {
                foreach ($imagesFiles['images_files'] as $imgFile) {
                    if ($imgFile->isValid() && !$imgFile->hasMoved()) {
                        $uploadedImg = \Modules\File\Config\Services::storage()->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                        $uploadedImages[] = $uploadedImg['path'];
                    }
                }
            }
            if (!empty($uploadedImages)) {
                $data['images'] = json_encode($uploadedImages);
            }

            $result = $this->campaignService->update($id, $data);
            if ($result) {
                return redirect()->to('/tenant/campaigns')->with('success', 'Urunan berhasil diperbarui');
            }
            return redirect()->back()->with('error', 'Gagal memperbarui urunan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete campaign
     * POST /tenant/{slug}/campaigns/{id}/delete
     */
    public function delete(string $slug, int $id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    session()->set('tenant_id', (int) $userRow['tenant_id']);
                    $tenantId = (int) $userRow['tenant_id'];
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        }

        $campaign = $this->campaignService->getById($id);
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            return redirect()->to('/tenant/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        try {
            // Soft delete - set status to deleted
            $result = $this->campaignService->update($id, ['status' => 'deleted']);
            if ($result) {
                return redirect()->to('/tenant/campaigns')->with('success', 'Urunan berhasil dihapus');
            }
            return redirect()->back()->with('error', 'Gagal menghapus urunan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create campaign (Buat Urunan)
     * POST /campaign/create
     */
    public function create()
    {
        $data = [
            'title' => $this->request->getPost('title'),
            'slug' => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
            'campaign_type' => $this->request->getPost('campaign_type') ?? 'target_based',
            'target_amount' => $this->request->getPost('target_amount'),
            'category' => $this->request->getPost('category'),
            'featured_image' => $this->request->getPost('featured_image'),
            'images' => $this->request->getPost('images'),
            'deadline' => $this->request->getPost('deadline'),
            'beneficiary_id' => $this->request->getPost('beneficiary_id'),
            'latitude' => $this->request->getPost('latitude') ?: null,
            'longitude' => $this->request->getPost('longitude') ?: null,
            'location_address' => $this->request->getPost('location_address') ?: null,
            'status' => $this->request->getPost('status') ?? 'draft',
        ];

        if (empty($data['title'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Judul wajib diisi',
            ])->setStatusCode(400);
        }

        // Validate target_amount for target_based
        if ($data['campaign_type'] === 'target_based' && (empty($data['target_amount']) || $data['target_amount'] <= 0)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Target dana wajib diisi untuk urunan target based',
            ])->setStatusCode(400);
        }

        // Parse images if string
        if (is_string($data['images'])) {
            $data['images'] = json_decode($data['images'], true) ?? [];
        }

        try {
            $id = $this->campaignService->create($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Urunan berhasil dibuat',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat urunan',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Update campaign
     * POST /campaign/update/{id}
     */
    public function update($id)
    {
        $data = [];

        if ($this->request->getPost('title')) {
            $data['title'] = $this->request->getPost('title');
        }
        if ($this->request->getPost('description') !== null) {
            $data['description'] = $this->request->getPost('description');
        }
        if ($this->request->getPost('campaign_type') !== null) {
            $data['campaign_type'] = $this->request->getPost('campaign_type');
        }
        if ($this->request->getPost('target_amount') !== null) {
            $data['target_amount'] = $this->request->getPost('target_amount');
        }
        if ($this->request->getPost('category') !== null) {
            $data['category'] = $this->request->getPost('category');
        }
        if ($this->request->getPost('featured_image') !== null) {
            $data['featured_image'] = $this->request->getPost('featured_image');
        }
        if ($this->request->getPost('images') !== null) {
            $images = $this->request->getPost('images');
            $data['images'] = is_string($images) ? json_decode($images, true) : $images;
        }
        if ($this->request->getPost('deadline') !== null) {
            $data['deadline'] = $this->request->getPost('deadline');
        }
        if ($this->request->getPost('beneficiary_id') !== null) {
            $data['beneficiary_id'] = $this->request->getPost('beneficiary_id');
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada data untuk diperbarui',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->campaignService->update((int) $id, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Urunan berhasil diperbarui',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Urunan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Submit for verification
     * POST /campaign/submit/{id}
     */
    public function submit($id)
    {
        try {
            $result = $this->campaignService->submitForVerification((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Urunan berhasil diajukan untuk verifikasi',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengajukan verifikasi',
            ])->setStatusCode(400);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Verify campaign (Tim UrunanKita only)
     * POST /campaign/verify/{id}
     */
    public function verify($id)
    {
        $approved = $this->request->getPost('approved') === true || $this->request->getPost('approved') === 'true';
        $rejectionReason = $this->request->getPost('rejection_reason');

        try {
            $result = $this->campaignService->verify((int) $id, $approved, $rejectionReason);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $approved ? 'Urunan berhasil diverifikasi' : 'Urunan ditolak',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Urunan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Admin: List campaigns from platform tenant only
     * GET /admin/campaigns
     */
    public function adminIndex()
    {
        $status = $this->request->getGet('status');
        
        // Get platform tenant ID
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // If platform tenant doesn't exist, return empty list
            $data = [
                'title' => 'Urunan - Admin Dashboard',
                'page_title' => 'Urunan',
                'sidebar_title' => 'UrunanKita',
                'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
                'user_role' => 'Super Admin',
                'campaigns' => [],
                'status_filter' => $status,
            ];
            return view('Modules\\Campaign\\Views\\admin_index', $data);
        }
        
        $platformTenantId = (int) $platform['id'];
        
        // Query campaigns from platform tenant only
        $builder = $db->table('campaigns');
        
        // Filter by platform tenant_id
        $builder->where('campaigns.tenant_id', $platformTenantId);
        
        // Exclude deleted campaigns by default
        if ($status === 'deleted') {
            // If explicitly filtering for deleted, show only deleted
            $builder->where('campaigns.status', 'deleted');
        } else {
            // Otherwise, exclude deleted campaigns
            $builder->where('campaigns.status !=', 'deleted');
            
            // Filter by status if provided
            if ($status) {
                $builder->where('campaigns.status', $status);
            }
        }
        
        $builder->orderBy('campaigns.created_at', 'DESC');
        $campaigns = $builder->get()->getResultArray();
        
        // Enrich campaigns with tenant info
        foreach ($campaigns as &$campaign) {
            $campaign['tenant_name'] = $platform['name'];
            $campaign['tenant_slug'] = $platform['slug'];
            
            // Parse images if JSON
            if (!empty($campaign['images'])) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            } else {
                $campaign['images'] = [];
            }
            
            // Format amounts
            $campaign['current_amount'] = (float) ($campaign['current_amount'] ?? 0);
            $campaign['target_amount'] = (float) ($campaign['target_amount'] ?? 0);
        }

        $data = [
            'title' => 'Urunan - Admin Dashboard',
            'page_title' => 'Urunan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'campaigns' => $campaigns,
            'status_filter' => $status,
        ];

        return view('Modules\\Campaign\\Views\\admin_index', $data);
    }

    /**
     * Admin: List all campaigns from all tenants
     * GET /admin/all/campaigns
     */
    public function adminAllCampaigns()
    {
        $status = $this->request->getGet('status');
        $tenantId = $this->request->getGet('tenant_id');
        
        $db = \Config\Database::connect();
        
        // Get all tenants for filter dropdown
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
        
        // Query campaigns from all tenants
        $builder = $db->table('campaigns');
        
        // Optional filter by tenant_id
        if ($tenantId) {
            $builder->where('campaigns.tenant_id', (int) $tenantId);
        }
        
        // Exclude deleted campaigns by default
        if ($status === 'deleted') {
            // If explicitly filtering for deleted, show only deleted
            $builder->where('campaigns.status', 'deleted');
        } else {
            // Otherwise, exclude deleted campaigns
            $builder->where('campaigns.status !=', 'deleted');
            
            // Filter by status if provided
            if ($status) {
                $builder->where('campaigns.status', $status);
            }
        }
        
        $builder->orderBy('campaigns.created_at', 'DESC');
        $campaigns = $builder->get()->getResultArray();
        
        // Get tenant info for enrichment
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }
        
        // Enrich campaigns with tenant info
        foreach ($campaigns as &$campaign) {
            $tenant = $tenantMap[$campaign['tenant_id']] ?? null;
            $campaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
            $campaign['tenant_slug'] = $tenant['slug'] ?? '';
            
            // Parse images if JSON
            if (!empty($campaign['images'])) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            } else {
                $campaign['images'] = [];
            }
            
            // Format amounts
            $campaign['current_amount'] = (float) ($campaign['current_amount'] ?? 0);
            $campaign['target_amount'] = (float) ($campaign['target_amount'] ?? 0);
        }

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }

        $data = [
            'pageTitle' => 'Semua Urunan',
            'userRole' => $userRole,
            'title' => 'Semua Urunan - Admin Dashboard',
            'page_title' => 'Semua Urunan',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaigns' => $campaigns,
            'tenants' => $tenants,
            'selectedTenantId' => $tenantId,
            'status_filter' => $status,
        ];

        return view('Modules\\Campaign\\Views\\admin_index', $data);
    }

    /**
     * Admin: View all activity logs from all tenants
     * GET /admin/all/logs
     */
    public function adminAllLogs()
    {
        $db = \Config\Database::connect();
        
        // Get all tenants for filter dropdown
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get selected tenant_id from query string (optional filter)
        $selectedTenantId = $this->request->getGet('tenant_id');
        $selectedAction = $this->request->getGet('action');
        $selectedEntity = $this->request->getGet('entity');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        
        // Get all activity logs from all tenants (or filtered by tenant_id)
        // Use direct query builder instead of model to bypass tenant isolation
        $logBuilder = $db->table('activity_logs');
        
        // Optional filter by tenant_id
        if ($selectedTenantId) {
            $logBuilder->where('activity_logs.tenant_id', (int) $selectedTenantId);
        }
        
        // Optional filter by action
        if ($selectedAction) {
            $logBuilder->where('activity_logs.action', $selectedAction);
        }
        
        // Optional filter by entity
        if ($selectedEntity) {
            $logBuilder->where('activity_logs.entity', $selectedEntity);
        }
        
        // Optional date range filter
        if ($dateFrom) {
            $logBuilder->where('activity_logs.created_at >=', $dateFrom);
        }
        if ($dateTo) {
            $logBuilder->where('activity_logs.created_at <=', $dateTo . ' 23:59:59');
        }
        
        $logs = $logBuilder
            ->orderBy('activity_logs.created_at', 'DESC')
            ->limit(500)
            ->get()
            ->getResultArray();
        
        // Get tenant info for enrichment
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }
        
        // Get user info for enrichment
        $userMap = [];
        $userIds = array_filter(array_column($logs, 'user_id'));
        if (!empty($userIds)) {
            $users = $db->table('users')
                ->whereIn('id', array_unique($userIds))
                ->get()
                ->getResultArray();
            foreach ($users as $user) {
                $userMap[$user['id']] = $user;
            }
        }
        
        // Enrich logs with tenant and user info
        foreach ($logs as &$log) {
            // Tenant info
            $tenant = $tenantMap[$log['tenant_id']] ?? null;
            $log['tenant_name'] = $tenant['name'] ?? 'Unknown';
            $log['tenant_slug'] = $tenant['slug'] ?? '';
            
            // User info
            $user = $userMap[$log['user_id']] ?? null;
            $log['user_name'] = $user['name'] ?? 'Unknown';
            $log['user_email'] = $user['email'] ?? '';
            
            // Decode JSON fields
            if (!empty($log['old_value'])) {
                $log['old_value'] = json_decode($log['old_value'], true);
            }
            if (!empty($log['new_value'])) {
                $log['new_value'] = json_decode($log['new_value'], true);
            }
            if (!empty($log['metadata'])) {
                $log['metadata'] = json_decode($log['metadata'], true);
            }
        }
        
        // Get unique actions and entities for filter dropdowns
        $allActions = $db->table('activity_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action', 'ASC')
            ->get()
            ->getResultArray();
        $actions = array_column($allActions, 'action');
        
        $allEntities = $db->table('activity_logs')
            ->select('entity')
            ->distinct()
            ->where('entity IS NOT NULL')
            ->orderBy('entity', 'ASC')
            ->get()
            ->getResultArray();
        $entities = array_column($allEntities, 'entity');

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }

        $data = [
            'pageTitle' => 'Riwayat & Log',
            'userRole' => $userRole,
            'title' => 'Riwayat & Log - Admin Dashboard',
            'page_title' => 'Riwayat & Log',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'logs' => $logs,
            'tenants' => $tenants,
            'selectedTenantId' => $selectedTenantId,
            'actions' => $actions,
            'selectedAction' => $selectedAction,
            'entities' => $entities,
            'selectedEntity' => $selectedEntity,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        return view('Modules\\Campaign\\Views\\admin_logs', $data);
    }

    /**
     * Admin: View campaign detail
     * GET /admin/campaigns/{id}
     */
    public function adminView(int $id)
    {
        $campaign = $this->campaignService->getById($id);
        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Get tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = null;
        if (isset($campaign['tenant_id']) && $campaign['tenant_id']) {
            $tenant = $tenantModel->find($campaign['tenant_id']);
        }

        // Get beneficiary info
        $beneficiary = null;
        if (isset($campaign['beneficiary_id']) && $campaign['beneficiary_id']) {
            $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
            $beneficiary = $beneficiaryModel->find($campaign['beneficiary_id']);
        }

        // Get donations
        $donationService = \Config\Services::donation();
        $donations = $donationService->getByCampaign($id, ['limit' => 50]);
        $donationStats = $donationService->getStats($id);

        // Get updates
        $updateService = \Config\Services::campaignUpdate();
        $updates = $updateService->getByCampaign($id, ['limit' => 20]);

        // Get comments
        $discussionService = \Config\Services::discussion();
        $comments = $discussionService->getComments($id, ['limit' => 20]);

        // Get detailed financial report (Masuk & Penggunaan Dana)
        $reportService = \Config\Services::report();
        $financialReport = $reportService->getCampaignFinancialDetail($id);

        // Parse images if JSON
        if (!empty($campaign['images'])) {
            if (is_string($campaign['images'])) {
                $campaign['images'] = json_decode($campaign['images'], true) ?? [];
            }
        } else {
            $campaign['images'] = [];
        }

        // Format featured image
        $featuredImage = $campaign['featured_image'] ?? '';
        if ($featuredImage && !preg_match('~^https?://~', $featuredImage) && strpos($featuredImage, '/uploads/') !== 0) {
            $featuredImage = '/uploads/' . ltrim($featuredImage, '/');
        }

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }

        $data = [
            'pageTitle' => 'Detail Urunan',
            'userRole' => $userRole,
            'title' => 'Detail Urunan - Admin Dashboard',
            'page_title' => 'Detail Urunan',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaign' => $campaign,
            'tenant' => $tenant,
            'beneficiary' => $beneficiary,
            'donations' => $donations,
            'donation_stats' => $donationStats,
            'updates' => $updates,
            'comments' => $comments,
            'featured_image' => $featuredImage,
            'financial_report' => $financialReport,
        ];

        return view('Modules\\Campaign\\Views\\admin_view', $data);
    }

    /**
     * Admin: Verify campaign page
     * GET /admin/campaigns/{id}/verify
     */
    public function verifyPage(int $id)
    {
        $campaign = $this->campaignService->getById($id);
        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        $data = [
            'title' => 'Verifikasi Urunan - Admin Dashboard',
            'page_title' => 'Verifikasi Urunan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'campaign' => $campaign,
        ];

        return view('Modules\\Campaign\\Views\\admin_verify', $data);
    }

    /**
     * Admin: Submit verification
     * POST /admin/campaigns/{id}/verify
     */
    public function verifySubmit(int $id)
    {
        $approved = $this->request->getPost('approved') === '1' || $this->request->getPost('approved') === true;
        $rejectionReason = $this->request->getPost('rejection_reason');

        try {
            $result = $this->campaignService->verify($id, $approved, $rejectionReason);
            if ($result) {
                return redirect()->to('/admin/campaigns')->with('success', $approved ? 'Urunan berhasil diverifikasi' : 'Urunan ditolak');
            }
            return redirect()->back()->with('error', 'Gagal memverifikasi urunan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Create campaign page
     * GET /admin/campaigns/create
     */
    public function adminCreatePage()
    {
        // Ensure Platform tenant exists so admin can create campaign atas nama platform
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            try {
                $db->table('tenants')->insert([
                    'name' => 'UrunanKita',
                    'slug' => 'platform',
                    'db_name' => null,
                    'status' => 'active',
                ]);
                $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Set platform tenant_id directly (no dropdown needed)
        $platformTenantId = $platform['id'] ?? 1;
        session()->set('tenant_id', $platformTenantId);

        // Get beneficiaries for platform tenant only
        $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
        $beneficiaries = $beneficiaryModel->where('tenant_id', $platformTenantId)->findAll();

        // Get platform payment methods (bank-transfer only)
        $paymentMethodModel = new \Modules\Setting\Models\PaymentMethodModel();
        $platformPaymentMethods = $paymentMethodModel->where('tenant_id', $platformTenantId)
            ->where('type', 'bank-transfer')
            ->where('enabled', 1)
            ->findAll();

        $data = [
            'pageTitle' => 'Buat Urunan Baru',
            'userRole' => 'admin',
            'title' => 'Buat Urunan - Admin Dashboard',
            'page_title' => 'Buat Urunan',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => session()->get('auth_user')['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'campaign' => null,
            'beneficiaries' => $beneficiaries,
            'platform_tenant_id' => $platformTenantId,
            'payment_methods' => $platformPaymentMethods,
        ];

        return view('Modules\\Campaign\\Views\\admin_form', $data);
    }

    /**
     * Admin: Store campaign
     * POST /admin/campaigns/store
     */
    public function adminStore()
    {
        // Get platform tenant directly (no need to select)
        $db = \Config\Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            return redirect()->back()->withInput()->with('error', 'Platform tenant tidak ditemukan');
        }

        $tenantId = (int) $platform['id'];
        
        // Set tenant context for service
        session()->set('tenant_id', $tenantId);

        // Prepare campaign data
        $status = $this->request->getPost('status');
        // If checkbox is checked, status will be 'draft', otherwise it won't be in POST, so default to 'active'
        if (empty($status)) {
            $status = 'active';
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'campaign_type' => $this->request->getPost('campaign_type') ?? 'target_based',
            'target_amount' => $this->request->getPost('target_amount') ? (float) str_replace('.', '', $this->request->getPost('target_amount')) : null,
            'category' => $this->request->getPost('category'),
            'featured_image' => $this->request->getPost('featured_image'),
            'deadline' => $this->request->getPost('deadline') ?: null,
            'beneficiary_id' => $this->request->getPost('beneficiary_id') ?: null,
            'latitude' => $this->request->getPost('latitude') ?: null,
            'longitude' => $this->request->getPost('longitude') ?: null,
            'location_address' => $this->request->getPost('location_address') ?: null,
            'status' => $status,
            'is_priority' => $this->request->getPost('is_priority') ? 1 : 0,
        ];

        // Handle uploads via Files StorageService
        try {
            $featuredFile = $this->request->getFile('featured_image_file');
            if ($featuredFile && $featuredFile->isValid() && !$featuredFile->hasMoved()) {
                $storage = \Modules\File\Config\Services::storage();
                $upload = $storage->upload($featuredFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                $data['featured_image'] = '/uploads/' . $upload['path'];
            }

            $files = $this->request->getFiles();
            if (isset($files['images_files']) && is_array($files['images_files'])) {
                $gallery = [];
                $storage = isset($storage) ? $storage : \Modules\File\Config\Services::storage();
                foreach ($files['images_files'] as $imgFile) {
                    if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                        $upload = $storage->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                        $gallery[] = '/uploads/' . $upload['path'];
                    }
                }
                if (!empty($gallery)) {
                    $data['images'] = json_encode($gallery);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Upload failed: ' . $e->getMessage());
        }

        if (empty($data['title'])) {
            session()->remove('tenant_id');
            return redirect()->back()->withInput()->with('error', 'Judul wajib diisi');
        }

        try {
            $id = $this->campaignService->create($data);
            if ($id) {
                return redirect()->to('/admin/campaigns')->with('success', 'Urunan berhasil dibuat');
            }
            return redirect()->back()->withInput()->with('error', 'Gagal membuat urunan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } finally {
            session()->remove('tenant_id');
        }
    }

    /**
     * Admin: Edit campaign page
     * GET /admin/campaigns/{id}/edit
     */
    public function adminEdit(int $id)
    {
        // Simplified: Find campaign in single database
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaign = $campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Get tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($campaign['tenant_id']);
        
        if (!$tenant) {
            return redirect()->to('/admin/campaigns')->with('error', 'Penggalang tidak ditemukan');
        }

        // Get all tenants for dropdown
        $tenants = $tenantModel->where('status', 'active')->findAll();

        // Get beneficiaries from selected tenant
        $beneficiaryModel = new \Modules\Beneficiary\Models\BeneficiaryModel();
        $beneficiaries = $beneficiaryModel
            ->where('tenant_id', $tenant['id'])
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();
        
        // Add tenant_id to each beneficiary (for JavaScript compatibility)
        foreach ($beneficiaries as &$beneficiary) {
            $beneficiary['tenant_id'] = $tenant['id'];
        }

        $data = [
            'title' => 'Edit Urunan - Admin Dashboard',
            'page_title' => 'Edit Urunan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'campaign' => $campaign,
            'tenants' => $tenants,
            'selected_tenant_id' => $tenant['id'],
            'beneficiaries' => $beneficiaries,
        ];

        return view('Modules\\Campaign\\Views\\admin_form', $data);
    }

    /**
     * Admin: Update campaign
     * POST /admin/campaigns/{id}/update
     */
    public function adminUpdate(int $id)
    {
        // Simplified: Find campaign in single database
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaign = $campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Get tenant for update (allow changing tenant)
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenantId = (int) $this->request->getPost('tenant_id') ?: $campaign['tenant_id'];
        $updateTenant = $tenantModel->find($tenantId);
        
        if (!$updateTenant) {
            return redirect()->back()->withInput()->with('error', 'Penggalang tidak ditemukan');
        }

        // Set tenant context temporarily for service
        session()->set('tenant_id', $tenantId);

        // Prepare update data
        $data = [];
        if ($this->request->getPost('title')) $data['title'] = $this->request->getPost('title');
        if ($this->request->getPost('description') !== null) $data['description'] = $this->request->getPost('description');
        if ($this->request->getPost('campaign_type') !== null) $data['campaign_type'] = $this->request->getPost('campaign_type');
        if ($this->request->getPost('target_amount') !== null) $data['target_amount'] = $this->request->getPost('target_amount');
        if ($this->request->getPost('category') !== null) $data['category'] = $this->request->getPost('category');
        if ($this->request->getPost('featured_image') !== null) $data['featured_image'] = $this->request->getPost('featured_image');
        if ($this->request->getPost('deadline') !== null) $data['deadline'] = $this->request->getPost('deadline');
        if ($this->request->getPost('beneficiary_id') !== null) $data['beneficiary_id'] = $this->request->getPost('beneficiary_id');
        if ($this->request->getPost('status') !== null) $data['status'] = $this->request->getPost('status');
        $data['is_priority'] = $this->request->getPost('is_priority') ? 1 : 0;
        
        // Handle featured image upload if present
        try {
            $featuredFile = $this->request->getFile('featured_image_file');
            if ($featuredFile && $featuredFile->isValid() && !$featuredFile->hasMoved()) {
                $storage = \Modules\File\Config\Services::storage();
                $upload = $storage->upload($featuredFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                $data['featured_image'] = '/uploads/' . $upload['path'];
            }

            $files = $this->request->getFiles();
            if (isset($files['images_files']) && is_array($files['images_files'])) {
                $gallery = [];
                $storage = isset($storage) ? $storage : \Modules\File\Config\Services::storage();
                foreach ($files['images_files'] as $imgFile) {
                    if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                        $upload = $storage->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                        $gallery[] = '/uploads/' . $upload['path'];
                    }
                }
                if (!empty($gallery)) {
                    $data['images'] = json_encode($gallery);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Upload failed: ' . $e->getMessage());
        }

        // Allow changing tenant_id for admin
        if ($campaign['tenant_id'] != $tenantId) {
            $data['tenant_id'] = $tenantId;
        }

        try {
            $result = $this->campaignService->update($id, $data);

            if ($result) {
                return redirect()->to('/admin/campaigns')->with('success', 'Urunan berhasil diperbarui');
            }
            return redirect()->back()->with('error', 'Gagal memperbarui urunan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } finally {
            // Clear tenant context if it was set
            if (session()->get('tenant_id') == $tenantId) {
                session()->remove('tenant_id');
            }
        }
    }

    /**
     * Admin: Delete campaign
     * POST /admin/campaigns/{id}/delete
     */
    public function adminDelete(int $id)
    {
        // Simplified: Find campaign in single database
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        
        // Use builder directly to bypass BaseModel tenant filtering for admin
        $db = \Config\Database::connect();
        $campaign = $db->table('campaigns')->where('id', (int) $id)->get()->getRowArray();

        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        try {
            // Soft delete - set status to deleted using builder directly
            $result = $db->table('campaigns')
                ->where('id', (int) $id)
                ->update(['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')]);

            if ($result) {
                // Log activity
                $activityLog = \Config\Services::activityLog();
                $activityLog->logUpdate('Campaign', $id, $campaign, ['status' => 'deleted'], 'Urunan dihapus oleh admin');
                
                return redirect()->to('/admin/campaigns')->with('success', 'Urunan berhasil dihapus');
            }
            return redirect()->back()->with('error', 'Gagal menghapus urunan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Update campaign status
     * POST /admin/campaigns/{id}/update-status
     */
    public function adminUpdateStatus(int $id)
    {
        $newStatus = $this->request->getPost('status');
        
        if (!in_array($newStatus, ['active', 'suspended', 'rejected', 'draft'])) {
            return redirect()->to('/admin/campaigns')->with('error', 'Status tidak valid');
        }

        // Simplified: Find campaign in single database
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaign = $campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('/admin/campaigns')->with('error', 'Urunan tidak ditemukan');
        }

        // Set tenant context temporarily for service
        session()->set('tenant_id', $campaign['tenant_id']);

        try {
            $result = $this->campaignService->update($id, ['status' => $newStatus]);

            if ($result) {
                $statusText = [
                    'active' => 'diaktifkan',
                    'suspended' => 'ditangguhkan',
                    'rejected' => 'ditolak',
                    'draft' => 'diubah ke draft',
                ];
                return redirect()->to('/admin/campaigns')->with('success', 'Urunan berhasil ' . ($statusText[$newStatus] ?? 'diperbarui'));
            }
            return redirect()->back()->with('error', 'Gagal memperbarui status urunan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } finally {
            session()->remove('tenant_id');
        }
    }
}

