<?php

namespace Modules\Helpdesk\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Helpdesk\Services\HelpdeskService;

class HelpdeskController extends BaseController
{
    protected HelpdeskService $helpdeskService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->helpdeskService = new HelpdeskService();
    }

    /**
     * Get categories
     * GET /helpdesk/categories
     */
    public function categories()
    {
        $categories = $this->helpdeskService->getCategories();

        return $this->response->setJSON([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get tickets (tenant)
     * GET /helpdesk/tickets
     */
    public function tickets()
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
            'category_id' => $this->request->getGet('category_id'),
            'priority' => $this->request->getGet('priority'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $tickets = $this->helpdeskService->getTicketsByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Get all tickets (admin)
     * GET /helpdesk/admin/tickets
     */
    public function adminTickets()
    {
        $filters = [
            'tenant_id' => $this->request->getGet('tenant_id'),
            'status' => $this->request->getGet('status'),
            'category_id' => $this->request->getGet('category_id'),
            'priority' => $this->request->getGet('priority'),
            'assigned_to' => $this->request->getGet('assigned_to'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $tickets = $this->helpdeskService->getAllTickets($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Get ticket by number
     * GET /helpdesk/ticket/{ticketNumber}
     */
    public function ticket($ticketNumber)
    {
        $ticket = $this->helpdeskService->getTicketByNumber($ticketNumber);

        if (!$ticket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket tidak ditemukan',
            ])->setStatusCode(404);
        }

        // Security: Check tenant ownership (if not admin)
        $user = auth_user();
        $isAdmin = isset($user['role']) && ($user['role'] === 'superadmin' || $user['role'] === 'super_admin');
        
        if (!$isAdmin) {
            // Ensure tenant_id is set in session
            $tenantId = (int) (session()->get('tenant_id') ?? 0);
            if (!$tenantId) {
                // Fallback: derive tenant from logged-in user
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
            }
            
            // Check if ticket belongs to tenant
            $ticketTenantId = (int) ($ticket['tenant_id'] ?? 0);
            if ($tenantId !== $ticketTenantId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Access denied',
                ])->setStatusCode(403);
            }
        }

        // Get replies
        $includeInternal = $isAdmin;
        $replies = $this->helpdeskService->getReplies($ticket['id'], $includeInternal);

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'replies' => $replies,
            ],
        ]);
    }

    /**
     * Create ticket
     * POST /helpdesk/ticket/create
     */
    public function createTicket()
    {
        // Ensure tenant_id is set in session
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        if (!$tenantId) {
            // Fallback: derive tenant from logged-in user
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
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant not found',
                ])->setStatusCode(401);
            }
        }

        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'subject' => $this->request->getPost('subject'),
            'description' => $this->request->getPost('description'),
            'priority' => $this->request->getPost('priority') ?? 'medium',
            'attachments' => $this->request->getPost('attachments'),
        ];

        if (empty($data['category_id']) || empty($data['subject']) || empty($data['description'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kategori, subjek, dan deskripsi wajib diisi',
            ])->setStatusCode(400);
        }

        // Parse attachments if string
        if (is_string($data['attachments'])) {
            $data['attachments'] = json_decode($data['attachments'], true) ?? [];
        }

        try {
            $id = $this->helpdeskService->createTicket($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Ticket berhasil dibuat',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat ticket',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Update ticket status (admin)
     * POST /helpdesk/ticket/{id}/status
     */
    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        $assignedTo = $this->request->getPost('assigned_to');

        if (empty($status)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status wajib diisi',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->helpdeskService->updateTicketStatus((int) $id, $status, $assignedTo ? (int) $assignedTo : null);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Status ticket berhasil diperbarui',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Add reply to ticket
     * POST /helpdesk/ticket/{id}/reply
     */
    public function addReply($id)
    {
        $data = [
            'message' => $this->request->getPost('message'),
            'attachments' => $this->request->getPost('attachments'),
            'is_internal' => $this->request->getPost('is_internal') === true || $this->request->getPost('is_internal') === 'true',
        ];

        if (empty($data['message'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pesan wajib diisi',
            ])->setStatusCode(400);
        }

        // Parse attachments if string
        if (is_string($data['attachments'])) {
            $data['attachments'] = json_decode($data['attachments'], true) ?? [];
        }

        try {
            $replyId = $this->helpdeskService->addReply((int) $id, $data);

            if ($replyId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Reply berhasil ditambahkan',
                    'data' => ['id' => $replyId],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Admin: List all tickets
     * GET /admin/helpdesk
     */
    public function adminIndex()
    {
        $status = $this->request->getGet('status');
        $priority = $this->request->getGet('priority');
        $tenantId = $this->request->getGet('tenant_id');

        $filters = [
            'status' => $status,
            'priority' => $priority,
            'tenant_id' => $tenantId ? (int) $tenantId : null,
            'limit' => 100,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $tickets = $this->helpdeskService->getAllTickets($filters);

        // Enrich with tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        foreach ($tickets as &$ticket) {
            if (isset($ticket['tenant_id'])) {
                $tenant = $tenantModel->find($ticket['tenant_id']);
                $ticket['tenant_name'] = $tenant['name'] ?? '-';
            }
        }

        $data = [
            'title' => 'Helpdesk - Admin Dashboard',
            'page_title' => 'Helpdesk',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('auth_user')['name'] ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'tickets' => $tickets,
            'status_filter' => $status,
            'priority_filter' => $priority,
            'tenant_id_filter' => $tenantId,
        ];

        return view('Modules\\Helpdesk\\Views\\admin_index', $data);
    }

    /**
     * Admin: Ticket detail
     * GET /admin/helpdesk/{id}
     */
    public function adminTicketDetail($id)
    {
        // Use single database architecture
        $ticketModel = new \Modules\Helpdesk\Models\TicketModel();
        $ticket = $ticketModel->find((int) $id);

        if (!$ticket) {
            return redirect()->to('/admin/helpdesk')->with('error', 'Ticket tidak ditemukan');
        }

        // Enrich ticket
        $ticket = $ticketModel->enrichTicket($ticket);

        // Get tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($ticket['tenant_id'] ?? null);
        $ticket['tenant_name'] = $tenant['name'] ?? '-';

        // Get replies (include internal for admin)
        $replies = $this->helpdeskService->getReplies((int) $id, true);

        // Enrich replies with user names
        $db = \Config\Database::connect();
        foreach ($replies as &$reply) {
            if (!empty($reply['attachments'])) {
                $reply['attachments'] = json_decode($reply['attachments'], true) ?? [];
            } else {
                $reply['attachments'] = [];
            }
            
            // Get user name
            if (!empty($reply['user_id'])) {
                $user = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                $reply['user_name'] = $user['name'] ?? 'User ' . $reply['user_id'];
                
                // Determine user_type from user role (override if wrong)
                if ($user) {
                    $userRole = $user['role'] ?? null;
                    if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
                        $reply['user_type'] = 'admin';
                    } else {
                        // Only set to tenant if not already set or if explicitly tenant
                        if (empty($reply['user_type']) || $reply['user_type'] !== 'admin') {
                            $reply['user_type'] = 'tenant';
                        }
                    }
                } else if (empty($reply['user_type'])) {
                    $reply['user_type'] = 'tenant';
                }
            } else {
                $reply['user_name'] = 'System';
                // Default to admin if no user_id
                if (empty($reply['user_type'])) {
                    $reply['user_type'] = 'admin';
                }
            }
        }

        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();

        $data = [
            'title' => 'Detail Ticket - Admin Dashboard',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'ticket' => $ticket,
            'replies' => $replies,
        ];

        return view('Modules\\Helpdesk\\Views\\admin_detail', $data);
    }

    /**
     * Admin: Add reply to ticket
     * POST /admin/helpdesk/{id}/reply
     */
    public function adminAddReply($id)
    {
        $data = [
            'message' => $this->request->getPost('message'),
            'attachments' => $this->request->getPost('attachments'),
            'is_internal' => $this->request->getPost('is_internal') === '1' || $this->request->getPost('is_internal') === true,
            'user_type' => 'admin', // Force admin type for admin replies
        ];

        if (empty($data['message'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pesan wajib diisi',
                ])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'Pesan wajib diisi');
        }

        // Parse attachments if string
        if (is_string($data['attachments'])) {
            $data['attachments'] = json_decode($data['attachments'], true) ?? [];
        }

        try {
            $replyId = $this->helpdeskService->addReply((int) $id, $data);

            if ($replyId) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Reply berhasil ditambahkan',
                        'data' => ['id' => $replyId],
                    ]);
                }
                return redirect()->back()->with('success', 'Reply berhasil ditambahkan');
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ticket tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->back()->with('error', 'Ticket tidak ditemukan');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                ])->setStatusCode(400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Update ticket status
     * POST /admin/helpdesk/{id}/status
     */
    public function adminUpdateStatus($id)
    {
        $status = $this->request->getPost('status');
        $assignedTo = $this->request->getPost('assigned_to');

        if (empty($status)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Status wajib diisi',
                ])->setStatusCode(400);
            }
            return redirect()->back()->with('error', 'Status wajib diisi');
        }

        try {
            $result = $this->helpdeskService->updateTicketStatus((int) $id, $status, $assignedTo ? (int) $assignedTo : null);

            if ($result) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Status ticket berhasil diperbarui',
                    ]);
                }
                return redirect()->back()->with('success', 'Status ticket berhasil diperbarui');
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ticket tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->back()->with('error', 'Ticket tidak ditemukan');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                ])->setStatusCode(400);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get user role from session and normalize for sidebar
     */
    protected function getUserRole(): string
    {
        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }
        
        return $userRole;
    }

    /**
     * Tenant: View helpdesk support page
     * GET /tenant/helpdesk
     */
    public function tenantIndex()
    {
        // Get tenant_id from session with fallback
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug'] ?? '');
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        } else {
            $tenantId = (int) $tenantId;
        }

        // Get filters from query string
        $statusFilter = $this->request->getGet('status');
        $priorityFilter = $this->request->getGet('priority');
        $categoryFilter = $this->request->getGet('category');
        $periodFilter = $this->request->getGet('period');

        // Build filters
        $filters = [];
        if ($statusFilter && $statusFilter !== 'all') {
            $filters['status'] = $statusFilter;
        }
        if ($priorityFilter && $priorityFilter !== 'all') {
            $filters['priority'] = $priorityFilter;
        }
        if ($categoryFilter && $categoryFilter !== 'all') {
            $filters['category_id'] = (int) $categoryFilter;
        }
        $filters['limit'] = 100;

        // Get tickets
        $tickets = $this->helpdeskService->getTicketsByTenant($tenantId, $filters);

        // Calculate statistics
        $allTickets = $this->helpdeskService->getTicketsByTenant($tenantId, ['limit' => 1000]);
        $stats = [
            'total' => count($allTickets),
            'open' => count(array_filter($allTickets, fn($t) => $t['status'] === 'open')),
            'resolved' => count(array_filter($allTickets, fn($t) => $t['status'] === 'resolved')),
            'average_response' => '2.5 jam', // TODO: Calculate actual average
            'new_today' => count(array_filter($allTickets, function($t) {
                return date('Y-m-d', strtotime($t['created_at'])) === date('Y-m-d');
            })),
        ];

        // Get categories
        $categories = $this->helpdeskService->getCategories();

        // Get tenant name
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($tenantId);
        $tenantName = $tenant['name'] ?? 'Tenant';

        $data = [
            'pageTitle' => 'Support',
            'userRole' => 'penggalang_dana',
            'tickets' => $tickets,
            'stats' => $stats,
            'categories' => $categories,
            'tenantName' => $tenantName,
            'statusFilter' => $statusFilter ?? 'all',
            'priorityFilter' => $priorityFilter ?? 'all',
            'categoryFilter' => $categoryFilter ?? 'all',
            'periodFilter' => $periodFilter ?? 'month',
        ];

        return view('Modules\\Helpdesk\\Views\\tenant_index', $data);
    }

    /**
     * Tenant: Ticket detail
     * GET /tenant/helpdesk/{id}
     */
    public function tenantTicketDetail($id)
    {
        // Get tenant_id from session with fallback
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug'] ?? '');
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
            if (!$tenantId) {
                return redirect()->to('/dashboard')->with('error', 'Tenant not found');
            }
        } else {
            $tenantId = (int) $tenantId;
        }

        // Use single database architecture
        $ticketModel = new \Modules\Helpdesk\Models\TicketModel();
        $ticket = $ticketModel->find((int) $id);

        if (!$ticket) {
            return redirect()->to('/tenant/helpdesk')->with('error', 'Ticket tidak ditemukan');
        }

        // Check if ticket belongs to tenant
        $ticketTenantId = (int) ($ticket['tenant_id'] ?? 0);
        if ($tenantId !== $ticketTenantId) {
            return redirect()->to('/tenant/helpdesk')->with('error', 'Access denied');
        }

        // Enrich ticket
        $ticket = $ticketModel->enrichTicket($ticket);

        // Get replies (exclude internal for tenant)
        $replies = $this->helpdeskService->getReplies((int) $id, false);

        // Enrich replies with user names
        $db = \Config\Database::connect();
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        $tenant = $tenantModel->find($tenantId);
        $tenantName = $tenant['name'] ?? 'Tenant';
        
        foreach ($replies as &$reply) {
            if (!empty($reply['attachments'])) {
                $reply['attachments'] = json_decode($reply['attachments'], true) ?? [];
            } else {
                $reply['attachments'] = [];
            }
            
            // Get user name
            if (!empty($reply['user_id'])) {
                $user = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                if ($reply['user_type'] === 'admin') {
                    $reply['user_name'] = 'Admin Support';
                } else {
                    $reply['user_name'] = $user['name'] ?? $tenantName;
                }
            } else {
                $reply['user_name'] = 'System';
            }
        }

        $authUser = session()->get('auth_user');
        $userRole = 'penggalang_dana';

        $data = [
            'title' => 'Detail Ticket - Support',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'ticket' => $ticket,
            'replies' => $replies,
            'tenantName' => $tenantName,
        ];

        return view('Modules\\Helpdesk\\Views\\tenant_detail', $data);
    }
}

