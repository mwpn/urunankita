<?php

namespace Modules\Discussion\Services;

use Modules\Discussion\Models\CommentModel;
use Modules\Discussion\Models\CommentLikeModel;
use Modules\Discussion\Models\CommentAminModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class DiscussionService
{
    protected CommentModel $commentModel;
    protected CommentLikeModel $likeModel;
    protected CommentAminModel $aminModel;
    protected CampaignModel $campaignModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
        $this->likeModel = new CommentLikeModel();
        $this->aminModel = new CommentAminModel();
        $this->campaignModel = new CampaignModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Add comment (public - bisa guest atau user terdaftar)
     *
     * @param array $data
     * @return int|false
     */
    public function addComment(array $data)
    {
        // Verify campaign exists
        $campaign = $this->campaignModel->find($data['campaign_id']);
        if (!$campaign) {
            throw new \RuntimeException('Campaign not found');
        }

        // Only allow comments on active or completed campaigns
        if (!in_array($campaign['status'], ['active', 'completed'])) {
            throw new \RuntimeException('Komentar hanya bisa ditambahkan pada urunan aktif atau selesai');
        }

        $user = auth_user();
        $isGuest = !$user;
        
        // Cek apakah email yang dimasukkan adalah email dari tenant owner atau user di tenant tersebut
        $commenterEmail = $data['commenter_email'] ?? ($user['email'] ?? null);
        
        // Jika user login, pastikan user_id ter-set
        $detectedUserId = null;
        if ($user && isset($user['id'])) {
            $detectedUserId = (int) $user['id'];
        }
        $detectedIsGuest = $isGuest;
        
        // Jika guest memasukkan email, cek apakah email tersebut milik user di tenant campaign ini
        if ($isGuest && !empty($commenterEmail)) {
            $db = \Config\Database::connect();
            $tenantUser = $db->table('users')
                ->where('tenant_id', (int) $campaign['tenant_id'])
                ->where('email', trim($commenterEmail))
                ->get()
                ->getRowArray();
            
            if ($tenantUser) {
                // Email cocok dengan user di tenant ini, set user_id
                $detectedUserId = (int) $tenantUser['id'];
                $detectedIsGuest = 0; // Bukan guest, dia adalah user terdaftar
                
                // Update commenter_name jika kosong atau gunakan nama dari user
                if (empty($data['commenter_name']) || $data['commenter_name'] === 'Guest') {
                    $data['commenter_name'] = $tenantUser['name'] ?? $data['commenter_name'];
                }
            }
        }

        // Tentukan status: user terdaftar (login atau terdeteksi dari email) auto-approve, guest perlu moderasi
        // Jika user terdeteksi (ada user_id), langsung approved
        // Jika benar-benar guest (tidak ada user_id), perlu moderasi (pending)
        $defaultStatus = ($detectedUserId !== null) ? 'approved' : 'pending';
        
        $commentData = [
            'campaign_id' => $data['campaign_id'],
            'tenant_id' => $campaign['tenant_id'],
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            'user_id' => $detectedUserId,
            'commenter_name' => $data['commenter_name'] ?? ($user['name'] ?? 'Guest'),
            'commenter_email' => $commenterEmail,
            'content' => $data['content'],
            'is_guest' => $detectedIsGuest ? 1 : 0,
            'status' => $defaultStatus, // User terdaftar auto-approve, guest perlu moderasi
        ];

        $id = $this->commentModel->insert($commentData);

        if ($id) {
            // If this is a reply, increment parent's replies_count
            if ($commentData['parent_id']) {
                $this->commentModel->incrementReplies($commentData['parent_id']);
            }

            $this->activityLog->logCreate('Comment', $id, $commentData, 'Komentar ditambahkan');

            // Send notification to campaign creator (optional)
        }

        return $id ?: false;
    }

    /**
     * Get comments by campaign
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getComments(int $campaignId, array $filters = []): array
    {
        return $this->commentModel->getByCampaign($campaignId, $filters);
    }

    /**
     * Get comment by ID
     *
     * @param int $commentId
     * @return array|null
     */
    public function getCommentById(int $commentId): ?array
    {
        return $this->commentModel->find($commentId);
    }

    /**
     * Get likes count using COUNT query (best practice)
     *
     * @param int $commentId
     * @return int
     */
    public function getLikesCount(int $commentId): int
    {
        return (int) $this->likeModel->builder()
            ->where('comment_id', $commentId)
            ->countAllResults();
    }

    /**
     * Get amins count using COUNT query (best practice)
     *
     * @param int $commentId
     * @return int
     */
    public function getAminsCount(int $commentId): int
    {
        return (int) $this->aminModel->builder()
            ->where('comment_id', $commentId)
            ->countAllResults();
    }

    /**
     * Generate or get guest token
     * 
     * @return string UUID v4
     */
    private function generateGuestToken(): string
    {
        // Generate UUID v4
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Like comment
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function likeComment(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return false;
        }

        // Check if already liked to prevent duplicate
        if ($this->likeModel->hasLiked($commentId, $userId, $guestId)) {
            return false; // Already liked, cannot duplicate
        }

        $result = $this->likeModel->addLike($commentId, $userId, $guestId);

        if ($result) {
            // Update cached count (for performance) and sync with actual count
            $this->commentModel->incrementLikes($commentId);
            // Also update from actual COUNT to ensure accuracy
            $actualCount = $this->getLikesCount($commentId);
            $this->commentModel->update($commentId, ['likes_count' => $actualCount]);
            return true;
        }

        return false;
    }

    /**
     * Unlike comment
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function unlikeComment(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        // Check if liked before removing
        if (!$this->likeModel->hasLiked($commentId, $userId, $guestId)) {
            return false; // Not liked, cannot unlike
        }

        $result = $this->likeModel->removeLike($commentId, $userId, $guestId);

        if ($result) {
            // Update cached count (for performance) and sync with actual count
            $this->commentModel->decrementLikes($commentId);
            // Also update from actual COUNT to ensure accuracy
            $actualCount = $this->getLikesCount($commentId);
            $this->commentModel->update($commentId, ['likes_count' => $actualCount]);
            return true;
        }

        return false;
    }

    /**
     * Amin comment
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function aminComment(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return false;
        }

        // Check if already amined to prevent duplicate
        if ($this->aminModel->hasAmined($commentId, $userId, $guestId)) {
            return false; // Already amined, cannot duplicate
        }

        $result = $this->aminModel->addAmin($commentId, $userId, $guestId);

        if ($result) {
            // Update cached count (for performance) and sync with actual count
            $this->commentModel->incrementAmins($commentId);
            // Also update from actual COUNT to ensure accuracy
            $actualCount = $this->getAminsCount($commentId);
            $this->commentModel->update($commentId, ['amins_count' => $actualCount]);
            return true;
        }

        return false;
    }

    /**
     * Unamin comment
     *
     * @param int $commentId
     * @param int|null $userId
     * @param string|null $guestId
     * @return bool
     */
    public function unaminComment(int $commentId, ?int $userId = null, ?string $guestId = null): bool
    {
        // Check if amined before removing
        if (!$this->aminModel->hasAmined($commentId, $userId, $guestId)) {
            return false; // Not amined, cannot unamin
        }

        $result = $this->aminModel->removeAmin($commentId, $userId, $guestId);

        if ($result) {
            // Update cached count (for performance) and sync with actual count
            $this->commentModel->decrementAmins($commentId);
            // Also update from actual COUNT to ensure accuracy
            $actualCount = $this->getAminsCount($commentId);
            $this->commentModel->update($commentId, ['amins_count' => $actualCount]);
            return true;
        }

        return false;
    }

    /**
     * Moderate comment (approve/reject)
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function moderateComment(int $id, string $status): bool
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            throw new \RuntimeException('Invalid status');
        }

        $comment = $this->commentModel->find($id);
        if (!$comment) {
            return false;
        }

        $result = $this->commentModel->update($id, ['status' => $status]);

        if ($result) {
            $this->activityLog->logUpdate('Comment', $id, $comment, ['status' => $status], "Komentar {$status}");
        }

        return $result;
    }

    /**
     * Pin comment
     *
     * @param int $id
     * @param bool $pin
     * @return bool
     */
    public function pinComment(int $id, bool $pin): bool
    {
        $result = $this->commentModel->update($id, ['is_pinned' => $pin ? 1 : 0]);

        if ($result) {
            $action = $pin ? 'dipin' : 'unpin';
            $this->activityLog->logUpdate('Comment', $id, [], ['is_pinned' => $pin], "Komentar {$action}");
        }

        return $result;
    }

    /**
     * Delete comment
     *
     * @param int $id
     * @return bool
     */
    public function deleteComment(int $id): bool
    {
        $comment = $this->commentModel->find($id);
        if (!$comment) {
            return false;
        }

        // Security: Only tenant owner or comment owner can delete
        $tenantId = session()->get('tenant_id');
        $user = auth_user();

        if ($comment['tenant_id'] != $tenantId && ($comment['user_id'] != ($user['id'] ?? null))) {
            throw new \RuntimeException('Access denied');
        }

        $result = $this->commentModel->delete($id);

        if ($result) {
            // If this was a reply, decrement parent's replies_count
            if ($comment['parent_id']) {
                $this->commentModel->builder()
                    ->set('replies_count', 'GREATEST(replies_count - 1, 0)', false)
                    ->where('id', $comment['parent_id'])
                    ->update();
            }

            $this->activityLog->logDelete('Comment', $id, $comment, 'Komentar dihapus');
        }

        return $result;
    }
}

