<?php

namespace Modules\Discussion\Models;

use Modules\Core\Models\BaseModel;

class CommentAminModel extends BaseModel
{
    protected $table = 'comment_amins';
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
     * Check if user/guest already amined
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function hasAmined(int $commentId, ?int $userId = null, ?string $guestId = null): bool
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
     * Add amin
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return int|false
     */
    public function addAmin(int $commentId, ?int $userId = null, ?string $guestId = null): int|false
    {
        if ($this->hasAmined($commentId, $userId, $guestId)) {
            return false; // Already amined
        }

        return $this->insert([
            'comment_id' => $commentId,
            'user_id' => $userId,
            'guest_id' => $guestId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Remove amin
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function removeAmin(int $commentId, ?int $userId = null, ?string $guestId = null): bool
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

