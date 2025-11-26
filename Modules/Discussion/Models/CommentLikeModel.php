<?php

namespace Modules\Discussion\Models;

use Modules\Core\Models\BaseModel;

class CommentLikeModel extends BaseModel
{
    protected $table = 'comment_likes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'comment_id',
        'user_id',
        'guest_id',
        'guest_ip', // Keep for backward compatibility during migration
        'created_at',
    ];

    protected $useTimestamps = false; // Manual timestamps karena tidak ada updated_at
    protected $dateFormat = 'datetime';

    /**
     * Check if user/guest already liked
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function hasLiked(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        $builder = $this->builder();
        $builder->where('comment_id', $commentId);

        if ($userId) {
            $builder->where('user_id', $userId);
        } elseif ($guestId) {
            $builder->where('guest_id', $guestId);
        } else {
            return false;
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Add like
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return int|false
     */
    public function addLike(int $commentId, ?int $userId = null, ?string $guestId = null): int|false
    {
        if ($this->hasLiked($commentId, $userId, $guestId)) {
            return false; // Already liked
        }

        return $this->insert([
            'comment_id' => $commentId,
            'user_id' => $userId,
            'guest_id' => $guestId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remove like
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function removeLike(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        $builder = $this->builder();
        $builder->where('comment_id', $commentId);

        if ($userId) {
            $builder->where('user_id', $userId);
        } elseif ($guestId) {
            $builder->where('guest_id', $guestId);
        } else {
            return false;
        }

        return $builder->delete() !== false;
    }
}

