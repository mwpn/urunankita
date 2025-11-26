<?php

namespace Modules\Helpdesk\Services;

use Modules\Helpdesk\Models\TicketModel;
use Modules\Helpdesk\Models\TicketCategoryModel;
use Modules\Helpdesk\Models\TicketReplyModel;
use Modules\Tenant\Models\TenantModel;
use Modules\Notification\Services\NotificationService;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;
use Config\Database;

class HelpdeskService
{
    protected TicketModel $ticketModel;
    protected TicketCategoryModel $categoryModel;
    protected TicketReplyModel $replyModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->categoryModel = new TicketCategoryModel();
        $this->replyModel = new TicketReplyModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Get active categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categoryModel->getActiveCategories();
    }

    /**
     * Create ticket
     *
     * @param array $data
     * @return int|false
     */
    public function createTicket(array $data)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            throw new \RuntimeException('Tenant not found');
        }

        $ticketNumber = $this->generateTicketNumber();

        $ticketData = [
            'ticket_number' => $ticketNumber,
            'tenant_id' => $tenantId,
            'user_id' => auth_user()['id'] ?? null,
            'category_id' => $data['category_id'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
        ];

        $id = $this->ticketModel->insert($ticketData);

        if ($id) {
            $this->activityLog->logCreate('Ticket', $id, $ticketData, "Ticket #{$ticketNumber} created");
            
            // Send notification to admin
            $this->sendTicketCreatedNotification($id, $ticketData);
        }

        return $id ?: false;
    }

    /**
     * Get tickets by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getTicketsByTenant(int $tenantId, array $filters = []): array
    {
        return $this->ticketModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get all tickets (admin) - simplified for single database
     *
     * @param array $filters
     * @return array
     */
    public function getAllTickets(array $filters = []): array
    {
        // Simplified: Query directly from single database
        $builder = $this->ticketModel->builder();

        // Apply filters
        if (isset($filters['tenant_id'])) {
            $builder->where('tickets.tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }

        if (isset($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            $builder->where('assigned_to', $filters['assigned_to']);
        }

        $builder->orderBy('created_at', 'DESC');
        
        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $tickets = $builder->get()->getResultArray();

        // Get tenant info for enrichment
        $tenantModel = new TenantModel();
        $tenants = $tenantModel->findAll();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }

        // Enrich tickets with tenant info
        foreach ($tickets as &$ticket) {
            $ticket = $this->enrichTicketData($ticket);
            
            $tenantId = (int) $ticket['tenant_id'];
            if (isset($tenantMap[$tenantId])) {
                $ticket['tenant_name'] = $tenantMap[$tenantId]['name'];
                $ticket['tenant_slug'] = $tenantMap[$tenantId]['slug'];
            } else {
                $ticket['tenant_name'] = 'Unknown';
                $ticket['tenant_slug'] = 'unknown';
            }
        }

        return $tickets;
    }

    /**
     * Enrich ticket data
     *
     * @param array $ticket
     * @return array
     */
    protected function enrichTicketData(array $ticket): array
    {
        // Parse attachments JSON
        if (!empty($ticket['attachments'])) {
            $ticket['attachments'] = json_decode($ticket['attachments'], true) ?? [];
        } else {
            $ticket['attachments'] = [];
        }

        return $ticket;
    }

    /**
     * Get ticket by number
     *
     * @param string $ticketNumber
     * @return array|null
     */
    public function getTicketByNumber(string $ticketNumber): ?array
    {
        return $this->ticketModel->getByNumber($ticketNumber);
    }

    /**
     * Update ticket status
     *
     * @param int $id
     * @param string $status
     * @param int|null $assignedTo
     * @return bool
     */
    public function updateTicketStatus(int $id, string $status, ?int $assignedTo = null): bool
    {
        $ticket = $this->ticketModel->find($id);
        if (!$ticket) {
            return false;
        }

        $updateData = [
            'status' => $status,
        ];

        if ($assignedTo !== null) {
            $updateData['assigned_to'] = $assignedTo;
        }

        if ($status === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
            $updateData['resolved_by'] = auth_user()['id'] ?? null;
        } elseif ($status === 'closed') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
            $updateData['closed_by'] = auth_user()['id'] ?? null;
        }

        $result = $this->ticketModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Ticket', $id, $ticket, $updateData, "Ticket status updated to {$status}");
        }

        return $result;
    }

    /**
     * Add reply to ticket
     *
     * @param int $ticketId
     * @param array $data
     * @return int|false
     */
    public function addReply(int $ticketId, array $data): int|false
    {
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket) {
            return false;
        }

        $user = auth_user();
        
        // If user_type is explicitly set in data, use it (for admin replies)
        // Otherwise, determine from user role
        $userType = $data['user_type'] ?? $this->getUserType();

        $replyData = [
            'ticket_id' => $ticketId,
            'user_id' => $user['id'] ?? null,
            'user_type' => $userType,
            'message' => $data['message'],
            'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
            'is_internal' => $data['is_internal'] ?? false,
        ];

        $id = $this->replyModel->insert($replyData);

        if ($id) {
            // Update ticket last_replied_at
            $this->ticketModel->update($ticketId, [
                'last_replied_at' => date('Y-m-d H:i:s'),
                'last_replied_by' => $user['id'] ?? null,
                'status' => $ticket['status'] === 'resolved' ? 'open' : $ticket['status'], // Reopen if resolved
            ]);

            $this->activityLog->logCreate('TicketReply', $id, $replyData, "Reply added to ticket #{$ticket['ticket_number']}");

            // Send notification
            if ($userType === 'tenant') {
                $this->sendReplyNotification($ticket, $replyData, 'admin');
            } else {
                $this->sendReplyNotification($ticket, $replyData, 'tenant');
            }
        }

        return $id ?: false;
    }

    /**
     * Get replies by ticket
     *
     * @param int $ticketId
     * @param bool $includeInternal
     * @return array
     */
    public function getReplies(int $ticketId, bool $includeInternal = false): array
    {
        return $this->replyModel->getByTicket($ticketId, $includeInternal);
    }

    /**
     * Generate unique ticket number
     *
     * @return string
     */
    protected function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        
        $ticketNumber = "{$prefix}-{$date}-{$random}";
        
        // Check uniqueness
        while ($this->ticketModel->where('ticket_number', $ticketNumber)->countAllResults() > 0) {
            $random = strtoupper(substr(uniqid(), -5));
            $ticketNumber = "{$prefix}-{$date}-{$random}";
        }

        return $ticketNumber;
    }

    /**
     * Get user type (tenant or admin)
     *
     * @return string
     */
    protected function getUserType(): string
    {
        $user = auth_user();
        $role = $user['role'] ?? null;
        
        // Check if user is admin (superadmin, super_admin, or admin)
        if (in_array($role, ['superadmin', 'super_admin', 'admin'])) {
            return 'admin';
        }
        
        // Check if there's no tenant_id in session (admin context)
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return 'admin';
        }
        
        return 'tenant';
    }

    /**
     * Send ticket created notification
     *
     * @param int $ticketId
     * @param array $ticketData
     * @return void
     */
    protected function sendTicketCreatedNotification(int $ticketId, array $ticketData): void
    {
        try {
            // Notify admin via WhatsApp or email
            // Implementation depends on your notification system
        } catch (\Exception $e) {
            log_message('error', 'Failed to send ticket notification: ' . $e->getMessage());
        }
    }

    /**
     * Send reply notification
     *
     * @param array $ticket
     * @param array $replyData
     * @param string $recipientType
     * @return void
     */
    protected function sendReplyNotification(array $ticket, array $replyData, string $recipientType): void
    {
        try {
            // Send notification to tenant or admin
            // Implementation depends on your notification system
        } catch (\Exception $e) {
            log_message('error', 'Failed to send reply notification: ' . $e->getMessage());
        }
    }
}


