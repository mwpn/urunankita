<?php

namespace Modules\Discussion\Models;

use Modules\Core\Models\BaseModel;

class CommentModel extends BaseModel
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'campaign_id',
        'tenant_id',
        'parent_id',
        'user_id',
        'commenter_name',
        'commenter_email',
        'content',
        'is_guest',
        'is_pinned',
        'status',
        'likes_count',
        'amins_count',
        'replies_count',
        'reported_count',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'content' => 'required',
        'status' => 'in_list[approved,pending,rejected]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get comments by campaign (with nested replies)
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getByCampaign(int $campaignId, array $filters = []): array
    {
        // Get parent comments (top level)
        $builder = $this->builder();
        $builder->where('campaign_id', $campaignId);
        $builder->where('parent_id IS NULL');
        
        // Filter by status if specified, otherwise default to 'approved' for public
        if (isset($filters['status'])) {
            if ($filters['status'] === 'all') {
                // Don't filter by status - show all
            } else {
                $builder->where('status', $filters['status']);
            }
        } else {
            // Default: only show approved comments for public
            $builder->where('status', 'approved');
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        // Pinned first, then by date
        $builder->orderBy('is_pinned', 'DESC');
        $builder->orderBy('created_at', 'DESC');

        $comments = $builder->get()->getResultArray();

        // Get replies for each comment
        $repliesLimit = isset($filters['replies_limit']) ? $filters['replies_limit'] : 10;
        $repliesStatus = isset($filters['status']) ? $filters['status'] : 'approved';
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->getReplies($comment['id'], $repliesLimit, $repliesStatus);
        }

        return $comments;
    }

    /**
     * Get replies for a comment
     *
     * @param int $parentId
     * @param int $limit
     * @param string|null $status Filter by status ('approved', 'pending', 'rejected', or null for all)
     * @return array
     */
    public function getReplies(int $parentId, int $limit = 10, ?string $status = 'approved'): array
    {
        $builder = $this->builder();
        $builder->where('parent_id', $parentId);
        
        if ($status === 'all') {
            // Don't filter by status
        } elseif ($status !== null) {
            $builder->where('status', $status);
        } else {
            // Default to approved
            $builder->where('status', 'approved');
        }
        
        return $builder->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Increment likes count
     *
     * @param int $id
     * @return void
     */
    public function incrementLikes(int $id): void
    {
        $this->builder()
            ->set('likes_count', 'likes_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Decrement likes count
     *
     * @param int $id
     * @return void
     */
    public function decrementLikes(int $id): void
    {
        $this->builder()
            ->set('likes_count', 'GREATEST(likes_count - 1, 0)', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Increment replies count
     *
     * @param int $id
     * @return void
     */
    public function incrementReplies(int $id): void
    {
        $this->builder()
            ->set('replies_count', 'replies_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Increment amins count
     *
     * @param int $id
     * @return void
     */
    public function incrementAmins(int $id): void
    {
        $this->builder()
            ->set('amins_count', 'amins_count + 1', false)
            ->where('id', $id)
            ->update();
    }

    /**
     * Decrement amins count
     *
     * @param int $id
     * @return void
     */
    public function decrementAmins(int $id): void
    {
        $this->builder()
            ->set('amins_count', 'GREATEST(amins_count - 1, 0)', false)
            ->where('id', $id)
            ->update();
    }
}

