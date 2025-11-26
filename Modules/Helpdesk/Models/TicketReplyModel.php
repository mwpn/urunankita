<?php

namespace Modules\Helpdesk\Models;

use Modules\Core\Models\BaseModel;

class TicketReplyModel extends BaseModel
{
    protected $table = 'ticket_replies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'user_type',
        'message',
        'attachments',
        'is_internal',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'ticket_id' => 'required|integer',
        'message' => 'required',
        'user_type' => 'in_list[tenant,admin]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get replies by ticket
     *
     * @param int $ticketId
     * @param bool $includeInternal
     * @return array
     */
    public function getByTicket(int $ticketId, bool $includeInternal = false): array
    {
        $builder = $this->builder();
        $builder->where('ticket_id', $ticketId);

        if (!$includeInternal) {
            $builder->where('is_internal', 0);
        }

        $builder->orderBy('created_at', 'ASC');

        $replies = $builder->get()->getResultArray();

        foreach ($replies as &$reply) {
            if ($reply['attachments']) {
                $reply['attachments'] = json_decode($reply['attachments'], true) ?? [];
            } else {
                $reply['attachments'] = [];
            }
        }

        return $replies;
    }
}

