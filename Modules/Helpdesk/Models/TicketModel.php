<?php

namespace Modules\Helpdesk\Models;

use Modules\Core\Models\BaseModel;

class TicketModel extends BaseModel
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'ticket_number',
        'tenant_id',
        'user_id',
        'category_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'attachments',
        'resolved_at',
        'resolved_by',
        'closed_at',
        'closed_by',
        'last_replied_at',
        'last_replied_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'subject' => 'required|max_length[255]',
        'description' => 'required',
        'tenant_id' => 'required|integer',
        'category_id' => 'required|integer',
        'priority' => 'in_list[low,medium,high,urgent]',
        'status' => 'in_list[open,in_progress,resolved,closed]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get tickets by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }

        if (isset($filters['priority'])) {
            $builder->where('priority', $filters['priority']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        $tickets = $builder->get()->getResultArray();
        
        foreach ($tickets as &$ticket) {
            $ticket = $this->enrichTicket($ticket);
        }

        return $tickets;
    }

    /**
     * Get all tickets (for admin)
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $builder = $this->builder();

        if (isset($filters['tenant_id'])) {
            $builder->where('tenant_id', $filters['tenant_id']);
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

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        $tickets = $builder->get()->getResultArray();
        
        foreach ($tickets as &$ticket) {
            $ticket = $this->enrichTicket($ticket);
        }

        return $tickets;
    }

    /**
     * Get ticket by number
     *
     * @param string $ticketNumber
     * @return array|null
     */
    public function getByNumber(string $ticketNumber): ?array
    {
        $ticket = $this->where('ticket_number', $ticketNumber)->first();
        if ($ticket) {
            $ticket = $this->enrichTicket($ticket);
        }
        return $ticket;
    }

    /**
     * Enrich ticket data
     *
     * @param array $ticket
     * @return array
     */
    public function enrichTicket(array $ticket): array
    {
        // Parse attachments JSON
        if ($ticket['attachments']) {
            $ticket['attachments'] = json_decode($ticket['attachments'], true) ?? [];
        } else {
            $ticket['attachments'] = [];
        }

        return $ticket;
    }
}

