<?php

namespace Modules\Discussion\Controllers;

use CodeIgniter\Cookie\Cookie;
use Modules\Core\Controllers\BaseController;
use Modules\Discussion\Services\DiscussionService;

class DiscussionController extends BaseController
{
    protected DiscussionService $discussionService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->discussionService = new DiscussionService();
    }

    /**
     * Get comments by campaign (Public)
     * GET /discussion/campaign/{campaignId}
     */
    public function getComments($campaignId)
    {
        $filters = [
            'limit' => $this->request->getGet('limit') ?? 20,
            'replies_limit' => $this->request->getGet('replies_limit') ?? 5,
        ];

        try {
            $comments = $this->discussionService->getComments((int) $campaignId, $filters);
            
            // Enrich comments with like/amin status for current user
            $user = auth_user();
            $guestId = null;
            
            // If user is not logged in, get guest token (don't create if doesn't exist yet)
            if (!$user) {
                $cookie = $this->request->getCookie('guest_like_amin_token');
                if ($cookie) {
                    $guestId = $cookie;
                }
            }
            
            $likeModel = new \Modules\Discussion\Models\CommentLikeModel();
            $aminModel = new \Modules\Discussion\Models\CommentAminModel();
            $db = \Config\Database::connect();
            
            foreach ($comments as &$comment) {
                $comment['is_liked'] = $likeModel->hasLiked((int) $comment['id'], $user['id'] ?? null, $guestId);
                $comment['is_amined'] = $aminModel->hasAmined((int) $comment['id'], $user['id'] ?? null, $guestId);
                
                // Enrich comment with user avatar
                if (!empty($comment['user_id'])) {
                    $commentUser = $db->table('users')->where('id', (int) $comment['user_id'])->get()->getRowArray();
                    if ($commentUser) {
                        $avatar = $commentUser['avatar'] ?? null;
                        if ($avatar) {
                            if (preg_match('~^https?://~', $avatar)) {
                                $comment['user_avatar'] = $avatar;
                            } elseif (strpos($avatar, '/uploads/') === 0) {
                                $comment['user_avatar'] = base_url($avatar);
                            } else {
                                $comment['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                            }
                        } else {
                            $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                } else {
                    $comment['user_avatar'] = null; // Guest tidak punya avatar
                }
                
                // Also enrich replies
                if (!empty($comment['replies'])) {
                    foreach ($comment['replies'] as &$reply) {
                        $reply['is_liked'] = $likeModel->hasLiked((int) $reply['id'], $user['id'] ?? null, $guestId);
                        $reply['is_amined'] = $aminModel->hasAmined((int) $reply['id'], $user['id'] ?? null, $guestId);
                        
                        // Enrich reply with user avatar
                        if (!empty($reply['user_id'])) {
                            $replyUser = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                            if ($replyUser) {
                                $avatar = $replyUser['avatar'] ?? null;
                                if ($avatar) {
                                    if (preg_match('~^https?://~', $avatar)) {
                                        $reply['user_avatar'] = $avatar;
                                    } elseif (strpos($avatar, '/uploads/') === 0) {
                                        $reply['user_avatar'] = base_url($avatar);
                                    } else {
                                        $reply['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                    }
                                } else {
                                    $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                                }
                            } else {
                                $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        } else {
                            $reply['user_avatar'] = null; // Guest tidak punya avatar
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $comments,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Add comment (Public - bisa guest)
     * POST /discussion/comment
     */
    public function addComment()
    {
        $user = auth_user();

        $data = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'parent_id' => $this->request->getPost('parent_id'),
            'commenter_name' => $this->request->getPost('commenter_name'),
            'commenter_email' => $this->request->getPost('commenter_email'),
            'content' => $this->request->getPost('content'),
        ];

        if (empty($data['campaign_id']) || empty($data['content'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Urunan dan konten komentar wajib diisi',
            ])->setStatusCode(400);
        }

        // If user is logged in, use their info
        if ($user) {
            $data['commenter_name'] = $user['name'] ?? $data['commenter_name'];
            $data['commenter_email'] = $user['email'] ?? $data['commenter_email'];
        } else {
            // Guest must provide name
            if (empty($data['commenter_name'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama wajib diisi untuk komentar sebagai guest',
                ])->setStatusCode(400);
            }
        }

        try {
            $id = $this->discussionService->addComment($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => ['id' => $id],
                    'message' => 'Komentar berhasil ditambahkan',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => 'Gagal menambahkan komentar',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get or create guest token from cookie
     * 
     * @return string
     */
    private function getOrCreateGuestToken(): string
    {
        $cookieName = 'guest_like_amin_token';
        $cookie = $this->request->getCookie($cookieName);
        
        if ($cookie) {
            return $cookie;
        }
        
        // Generate new UUID v4 token
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        $token = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        
        // Set cookie for 1 year (365 days)
        // Use Cookie object for proper format
        $expires = time() + (365 * 24 * 60 * 60);
        $cookie = new Cookie(
            $cookieName,
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => false,
                'samesite' => 'Lax',
            ]
        );
        $this->response->setCookie($cookie);
        
        return $token;
    }

    /**
     * Like comment
     * POST /discussion/comment/{id}/like
     */
    public function like($id)
    {
        $user = auth_user();
        $guestId = null;
        
        // If user is not logged in, get or create guest token
        if (!$user) {
            $guestId = $this->getOrCreateGuestToken();
        }

        try {
            // Check current status
            $likeModel = new \Modules\Discussion\Models\CommentLikeModel();
            $isLiked = $likeModel->hasLiked((int) $id, $user['id'] ?? null, $guestId);
            
            log_message('debug', 'Like toggle - Comment ID: ' . $id . ', User ID: ' . ($user['id'] ?? 'null') . ', Guest ID: ' . ($guestId ?? 'null') . ', Is Liked: ' . ($isLiked ? 'true' : 'false'));
            
            if ($isLiked) {
                // Unlike
                $result = $this->discussionService->unlikeComment(
                    (int) $id,
                    $user['id'] ?? null,
                    $guestId
                );
                
                if ($result) {
                    // Get count using COUNT query (best practice)
                    $likesCount = $this->discussionService->getLikesCount((int) $id);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'data' => [
                            'liked' => false,
                            'likes_count' => $likesCount,
                        ],
                        'message' => 'Like dihapus',
                    ]);
                }
            } else {
                // Like
                $result = $this->discussionService->likeComment(
                    (int) $id,
                    $user['id'] ?? null,
                    $guestId
                );

                if ($result) {
                    // Get count using COUNT query (best practice)
                    $likesCount = $this->discussionService->getLikesCount((int) $id);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'data' => [
                            'liked' => true,
                            'likes_count' => $likesCount,
                        ],
                        'message' => 'Komentar dilike',
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => 'Gagal memproses like',
            ])->setStatusCode(400);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Unlike comment
     * POST /discussion/comment/{id}/unlike
     */
    public function unlike($id)
    {
        // This method is kept for backward compatibility but like() now handles both
        return $this->like($id);
    }

    /**
     * Amin comment
     * POST /discussion/comment/{id}/amin
     */
    public function amin($id)
    {
        $user = auth_user();
        $guestId = null;
        
        // If user is not logged in, get or create guest token
        if (!$user) {
            $guestId = $this->getOrCreateGuestToken();
        }

        try {
            // Check current status
            $aminModel = new \Modules\Discussion\Models\CommentAminModel();
            $isAmined = $aminModel->hasAmined((int) $id, $user['id'] ?? null, $guestId);
            
            log_message('debug', 'Aamiin toggle - Comment ID: ' . $id . ', User ID: ' . ($user['id'] ?? 'null') . ', Guest ID: ' . ($guestId ?? 'null') . ', Is Amined: ' . ($isAmined ? 'true' : 'false'));
            
            if ($isAmined) {
                // Unamin
                $result = $this->discussionService->unaminComment(
                    (int) $id,
                    $user['id'] ?? null,
                    $guestId
                );
                
                if ($result) {
                    // Get count using COUNT query (best practice)
                    $aminsCount = $this->discussionService->getAminsCount((int) $id);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'data' => [
                            'amined' => false,
                            'amins_count' => $aminsCount,
                        ],
                        'message' => 'Aamiin dihapus',
                    ]);
                }
            } else {
                // Amin
                $result = $this->discussionService->aminComment(
                    (int) $id,
                    $user['id'] ?? null,
                    $guestId
                );

                if ($result) {
                    // Get count using COUNT query (best practice)
                    $aminsCount = $this->discussionService->getAminsCount((int) $id);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'data' => [
                            'amined' => true,
                            'amins_count' => $aminsCount,
                        ],
                        'message' => 'Aamiin berhasil',
                    ]);
                }
            }

            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => 'Gagal memproses aamiin',
            ])->setStatusCode(400);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Unamin comment
     * POST /discussion/comment/{id}/unamin
     */
    public function unamin($id)
    {
        // This method is kept for backward compatibility but amin() now handles both
        return $this->amin($id);
    }

    /**
     * Moderate comment (Tenant/Admin)
     * POST /discussion/comment/{id}/moderate
     */
    public function moderate($id)
    {
        $status = $this->request->getPost('status');

        if (empty($status) || !in_array($status, ['approved', 'rejected'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status harus approved atau rejected',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->discussionService->moderateComment((int) $id, $status);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Komentar berhasil dimoderasi',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Komentar tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Pin comment (Tenant)
     * POST /discussion/comment/{id}/pin
     */
    public function pin($id)
    {
        $pin = $this->request->getPost('pin') === true || $this->request->getPost('pin') === 'true';

        try {
            $result = $this->discussionService->pinComment((int) $id, $pin);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $pin ? 'Komentar dipin' : 'Pin komentar dihapus',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Komentar tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Delete comment
     * DELETE /discussion/comment/{id}
     */
    public function delete($id)
    {
        try {
            $result = $this->discussionService->deleteComment((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Komentar berhasil dihapus',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Komentar tidak ditemukan atau akses ditolak',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Tenant: View discussions for all campaigns
     * GET /tenant/discussions
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

        // Get all campaigns for this tenant
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaigns = $campaignModel->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Get selected campaign_id from query string
        $selectedCampaignId = $this->request->getGet('campaign_id');
        if ($selectedCampaignId && !empty($campaigns)) {
            $selectedCampaignId = (int) $selectedCampaignId;
            // Verify campaign belongs to tenant
            $campaignExists = false;
            foreach ($campaigns as $campaign) {
                if ($campaign['id'] == $selectedCampaignId) {
                    $campaignExists = true;
                    break;
                }
            }
            if (!$campaignExists) {
                $selectedCampaignId = null;
            }
        } else {
            $selectedCampaignId = null;
        }

        // If no campaign selected, use first campaign (if available)
        if (!$selectedCampaignId && !empty($campaigns)) {
            $selectedCampaignId = (int) $campaigns[0]['id'];
        }

        // Get comments for selected campaign
        $comments = [];
        $selectedCampaign = null;
        if ($selectedCampaignId) {
            $selectedCampaign = $campaignModel->find($selectedCampaignId);
            if ($selectedCampaign) {
                $comments = $this->discussionService->getComments($selectedCampaignId, [
                    'limit' => 50,
                    'replies_limit' => 10,
                    'status' => 'all', // Show all comments in tenant dashboard (including pending/rejected)
                ]);
            }
        }

        // Calculate statistics
        $commentModel = new \Modules\Discussion\Models\CommentModel();
        $stats = [
            'total_comments' => 0,
            'comments_today' => 0,
            'total_replies' => 0,
            'most_liked' => 0,
        ];

        if ($selectedCampaignId) {
            // Total comments for selected campaign
            $stats['total_comments'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->countAllResults(false);

            // Comments today
            $today = date('Y-m-d');
            $stats['comments_today'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->where('DATE(created_at)', $today)
                ->countAllResults(false);

            // Total replies
            $stats['total_replies'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NOT NULL')
                ->countAllResults(false);

            // Most liked comment
            $mostLiked = $commentModel->where('campaign_id', $selectedCampaignId)
                ->orderBy('likes_count', 'DESC')
                ->limit(1)
                ->first();
            if ($mostLiked) {
                $stats['most_liked'] = (int) ($mostLiked['likes_count'] ?? 0);
            }
        }

        // Get recent comments (last 5)
        $recentComments = [];
        if ($selectedCampaignId) {
            $recentComments = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }

        // Get user role
        $userRole = 'penggalang_dana';

        $data = [
            'pageTitle' => 'Diskusi & Komentar',
            'userRole' => $userRole,
            'campaigns' => $campaigns,
            'selectedCampaignId' => $selectedCampaignId,
            'selectedCampaign' => $selectedCampaign,
            'comments' => $comments,
            'stats' => $stats,
            'recentComments' => $recentComments,
        ];

        return view('Modules\\Discussion\\Views\\tenant_index', $data);
    }

    /**
     * Admin: View discussions for all campaigns from all tenants
     * GET /admin/discussions
     */
    public function adminIndex()
    {
        $db = \Config\Database::connect();
        
        // Get current admin user
        $authUser = session()->get('auth_user');
        $adminUserId = $authUser['id'] ?? null;
        $userRole = $authUser['role'] ?? 'admin';
        
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }
        
        if (!$adminUserId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }
        
        // Get all tenants for filter dropdown
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get selected tenant_id from query string (optional filter)
        $selectedTenantId = $this->request->getGet('tenant_id');
        
        // Get campaigns created by this admin user (not all campaigns)
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaignBuilder = $db->table('campaigns');
        
        // Filter: Only campaigns created by current admin
        $campaignBuilder->where('creator_user_id', (int) $adminUserId);
        
        if ($selectedTenantId) {
            $campaignBuilder->where('tenant_id', (int) $selectedTenantId);
        }
        
        $campaigns = $campaignBuilder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        // Enrich campaigns with tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        foreach ($campaigns as &$campaign) {
            $tenant = $tenantModel->find($campaign['tenant_id']);
            $campaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
            $campaign['tenant_slug'] = $tenant['slug'] ?? '';
        }

        // Get selected campaign_id from query string
        $selectedCampaignId = $this->request->getGet('campaign_id');
        if ($selectedCampaignId && $selectedCampaignId !== 'all' && !empty($campaigns)) {
            $selectedCampaignId = (int) $selectedCampaignId;
            // Verify campaign exists in the list
            $campaignExists = false;
            foreach ($campaigns as $campaign) {
                if ($campaign['id'] == $selectedCampaignId) {
                    $campaignExists = true;
                    break;
                }
            }
            if (!$campaignExists) {
                $selectedCampaignId = 'all';
            }
        } else {
            $selectedCampaignId = 'all'; // Default: show all comments from all campaigns
        }

        // Get comments - either from selected campaign or all campaigns
        // Show ALL comments (same as adminAllDiscussions)
        $comments = [];
        $selectedCampaign = null;
        $commentModel = new \Modules\Discussion\Models\CommentModel();
        
        if ($selectedCampaignId === 'all') {
            // Get all comments from campaigns created by this admin - ALL USERS, ALL STATUS
            // First, get campaign IDs created by this admin
            $adminCampaignIds = array_column($campaigns, 'id');
            
            if (empty($adminCampaignIds)) {
                // No campaigns created by admin, so no comments
                $comments = [];
            } else {
                $builder = $commentModel->builder();
                $builder->where('parent_id IS NULL');
                // Filter: Only comments from campaigns created by this admin
                $builder->whereIn('campaign_id', $adminCampaignIds);
                // No status filter - show all statuses (approved, pending, rejected)
                
                // Filter by tenant if selected
                if ($selectedTenantId) {
                    $builder->where('tenant_id', (int) $selectedTenantId);
                }
                
                // Pinned comments first, then by date
                $builder->orderBy('is_pinned', 'DESC');
                $builder->orderBy('created_at', 'DESC');
                $builder->limit(100); // Limit untuk performa
                
                $allComments = $builder->get()->getResultArray();
                
                // Enrich comments with campaign info and user avatar
                foreach ($allComments as &$comment) {
                $campaign = $campaignModel->find($comment['campaign_id']);
                if ($campaign) {
                    $comment['campaign_title'] = $campaign['title'] ?? 'Unknown Campaign';
                    $comment['campaign_id_display'] = $campaign['id'];
                    $tenant = $tenantModel->find($campaign['tenant_id']);
                    $comment['tenant_name'] = $tenant['name'] ?? 'Unknown';
                }
                
                // Get user data including avatar
                if (!empty($comment['user_id'])) {
                    $user = $db->table('users')->where('id', (int) $comment['user_id'])->get()->getRowArray();
                    if ($user) {
                        $avatar = $user['avatar'] ?? null;
                        if ($avatar) {
                            if (preg_match('~^https?://~', $avatar)) {
                                $comment['user_avatar'] = $avatar;
                            } elseif (strpos($avatar, '/uploads/') === 0) {
                                $comment['user_avatar'] = base_url($avatar);
                            } else {
                                $comment['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                            }
                        } else {
                            $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                } else {
                    $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                }
                
                // Get replies - ALL replies
                $replies = $commentModel->getReplies($comment['id'], 5, 'all');
                
                // Enrich replies with user avatar
                foreach ($replies as &$reply) {
                    if (!empty($reply['user_id'])) {
                        $replyUser = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                        if ($replyUser) {
                            $avatar = $replyUser['avatar'] ?? null;
                            if ($avatar) {
                                if (preg_match('~^https?://~', $avatar)) {
                                    $reply['user_avatar'] = $avatar;
                                } elseif (strpos($avatar, '/uploads/') === 0) {
                                    $reply['user_avatar'] = base_url($avatar);
                                } else {
                                    $reply['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                }
                            } else {
                                $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        } else {
                            $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                }
                
                    $comment['replies'] = $replies;
                }
                
                $comments = $allComments;
            }
        } else {
            // Get comments for selected campaign - ALL USERS
            // Verify that selected campaign is created by this admin
            $selectedCampaign = $campaignModel->find($selectedCampaignId);
            if ($selectedCampaign && isset($selectedCampaign['creator_user_id']) && (int) $selectedCampaign['creator_user_id'] === (int) $adminUserId) {
                // Enrich selected campaign with tenant info
                $tenant = $tenantModel->find($selectedCampaign['tenant_id']);
                $selectedCampaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
                $selectedCampaign['tenant_slug'] = $tenant['slug'] ?? '';
                
                // Get all comments - ALL USERS
                $allComments = $this->discussionService->getComments($selectedCampaignId, [
                    'limit' => 50,
                    'replies_limit' => 10,
                    'status' => 'all', // Show all comments in admin (including pending/rejected)
                ]);
                
                $comments = $allComments;
                
                // Enrich comments with user avatar
                foreach ($comments as &$comment) {
                    // Enrich comment with user avatar
                    if (!empty($comment['user_id'])) {
                        $user = $db->table('users')->where('id', (int) $comment['user_id'])->get()->getRowArray();
                        if ($user) {
                            $avatar = $user['avatar'] ?? null;
                            if ($avatar) {
                                if (preg_match('~^https?://~', $avatar)) {
                                    $comment['user_avatar'] = $avatar;
                                } elseif (strpos($avatar, '/uploads/') === 0) {
                                    $comment['user_avatar'] = base_url($avatar);
                                } else {
                                    $comment['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                }
                            } else {
                                $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        } else {
                            $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                    
                    if (isset($comment['replies']) && is_array($comment['replies'])) {
                        // Enrich replies with user avatar
                        foreach ($comment['replies'] as &$reply) {
                            if (!empty($reply['user_id'])) {
                                $replyUser = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                                if ($replyUser) {
                                    $avatar = $replyUser['avatar'] ?? null;
                                    if ($avatar) {
                                        if (preg_match('~^https?://~', $avatar)) {
                                            $reply['user_avatar'] = $avatar;
                                        } elseif (strpos($avatar, '/uploads/') === 0) {
                                            $reply['user_avatar'] = base_url($avatar);
                                        } else {
                                            $reply['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                        }
                                    } else {
                                        $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                                    }
                                } else {
                                    $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                                }
                            } else {
                                $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        }
                        
                        // Replies already in place
                    }
                }
            }
        }

        // Calculate statistics - ALL comments (no user_id filter)
        $stats = [
            'total_comments' => 0,
            'comments_today' => 0,
            'total_replies' => 0,
            'most_liked' => 0,
        ];

        if ($selectedCampaignId === 'all') {
            // Statistics for campaigns created by this admin - ALL USERS
            $adminCampaignIds = array_column($campaigns, 'id');
            if (!empty($adminCampaignIds)) {
                $builder = $commentModel->builder();
                $builder->where('parent_id IS NULL');
                $builder->whereIn('campaign_id', $adminCampaignIds);
                if ($selectedTenantId) {
                    $builder->where('tenant_id', (int) $selectedTenantId);
                }
                $stats['total_comments'] = $builder->countAllResults(false);
            } else {
                $stats['total_comments'] = 0;
            }

            // Comments today
            $today = date('Y-m-d');
            if (!empty($adminCampaignIds)) {
                $builderToday = $commentModel->builder();
                $builderToday->where('parent_id IS NULL');
                $builderToday->whereIn('campaign_id', $adminCampaignIds);
                $builderToday->where('DATE(created_at)', $today);
                if ($selectedTenantId) {
                    $builderToday->where('tenant_id', (int) $selectedTenantId);
                }
                $stats['comments_today'] = $builderToday->countAllResults(false);
            } else {
                $stats['comments_today'] = 0;
            }

            // Total replies - ALL USERS
            if (!empty($adminCampaignIds)) {
                $builderReplies = $commentModel->builder();
                $builderReplies->where('parent_id IS NOT NULL');
                $builderReplies->whereIn('campaign_id', $adminCampaignIds);
                if ($selectedTenantId) {
                    $builderReplies->where('tenant_id', (int) $selectedTenantId);
                }
                $stats['total_replies'] = $builderReplies->countAllResults(false);
            } else {
                $stats['total_replies'] = 0;
            }

            // Most liked comment - ALL USERS
            if (!empty($adminCampaignIds)) {
                $builderLiked = $commentModel->builder();
                $builderLiked->whereIn('campaign_id', $adminCampaignIds);
                $builderLiked->orderBy('likes_count', 'DESC');
                if ($selectedTenantId) {
                    $builderLiked->where('tenant_id', (int) $selectedTenantId);
                }
                $mostLiked = $builderLiked->limit(1)->get()->getRowArray();
                if ($mostLiked) {
                    $stats['most_liked'] = (int) ($mostLiked['likes_count'] ?? 0);
                }
            }
        } elseif ($selectedCampaignId) {
            // Statistics for selected campaign - ALL USERS
            $stats['total_comments'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                // Removed: Filter by admin user_id
                ->countAllResults(false);

            // Comments today
            $today = date('Y-m-d');
            $stats['comments_today'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                // Removed: Filter by admin user_id
                ->where('DATE(created_at)', $today)
                ->countAllResults(false);

            // Total replies - ALL USERS
            $stats['total_replies'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NOT NULL')
                // Removed: Filter by admin user_id
                ->countAllResults(false);

            // Most liked comment - ALL USERS
            $mostLiked = $commentModel->where('campaign_id', $selectedCampaignId)
                // Removed: Filter by admin user_id
                ->orderBy('likes_count', 'DESC')
                ->limit(1)
                ->first();
            if ($mostLiked) {
                $stats['most_liked'] = (int) ($mostLiked['likes_count'] ?? 0);
            }
        }

        // Get recent comments (last 5) - ALL USERS from admin's campaigns
        $recentComments = [];
        if ($selectedCampaignId === 'all') {
            $adminCampaignIds = array_column($campaigns, 'id');
            if (!empty($adminCampaignIds)) {
                $builderRecent = $commentModel->builder();
                $builderRecent->where('parent_id IS NULL');
                $builderRecent->whereIn('campaign_id', $adminCampaignIds);
                if ($selectedTenantId) {
                    $builderRecent->where('tenant_id', (int) $selectedTenantId);
                }
                $recentComments = $builderRecent->orderBy('created_at', 'DESC')
                    ->limit(5)
                    ->get()
                    ->getResultArray();
            }
        } elseif ($selectedCampaignId) {
            $recentComments = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                // Removed: Filter by admin user_id
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }
        
        // Enrich recent comments with user avatar
        foreach ($recentComments as &$recent) {
            if (!empty($recent['user_id'])) {
                $user = $db->table('users')->where('id', (int) $recent['user_id'])->get()->getRowArray();
                if ($user) {
                    $avatar = $user['avatar'] ?? null;
                    if ($avatar) {
                        if (preg_match('~^https?://~', $avatar)) {
                            $recent['user_avatar'] = $avatar;
                        } elseif (strpos($avatar, '/uploads/') === 0) {
                            $recent['user_avatar'] = base_url($avatar);
                        } else {
                            $recent['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                        }
                    } else {
                        $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                } else {
                    $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                }
            } else {
                $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
            }
        }

        $data = [
            'pageTitle' => 'Diskusi & Komentar',
            'userRole' => $userRole,
            'title' => 'Diskusi & Komentar - Admin Dashboard',
            'page_title' => 'Diskusi & Komentar',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'tenants' => $tenants,
            'selectedTenantId' => $selectedTenantId,
            'campaigns' => $campaigns,
            'selectedCampaignId' => $selectedCampaignId,
            'selectedCampaign' => $selectedCampaign,
            'comments' => $comments,
            'stats' => $stats,
            'recentComments' => $recentComments,
        ];

        return view('Modules\\Discussion\\Views\\admin_index', $data);
    }

    /**
     * Admin: View ALL discussions from all campaigns and all users
     * GET /admin/all/discussions
     */
    public function adminAllDiscussions()
    {
        $db = \Config\Database::connect();
        
        // Get current admin user (for session info only, not for filtering)
        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }
        
        // Get all tenants for filter dropdown
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get selected tenant_id from query string (optional filter)
        $selectedTenantId = $this->request->getGet('tenant_id');
        
        // Get all campaigns from all tenants (or filtered by tenant_id)
        $campaignModel = new \Modules\Campaign\Models\CampaignModel();
        $campaignBuilder = $db->table('campaigns');
        
        if ($selectedTenantId) {
            $campaignBuilder->where('tenant_id', (int) $selectedTenantId);
        }
        
        $campaigns = $campaignBuilder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        // Enrich campaigns with tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        foreach ($campaigns as &$campaign) {
            $tenant = $tenantModel->find($campaign['tenant_id']);
            $campaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
            $campaign['tenant_slug'] = $tenant['slug'] ?? '';
        }

        // Get selected campaign_id from query string
        $selectedCampaignId = $this->request->getGet('campaign_id');
        if ($selectedCampaignId && $selectedCampaignId !== 'all' && !empty($campaigns)) {
            $selectedCampaignId = (int) $selectedCampaignId;
            // Verify campaign exists in the list
            $campaignExists = false;
            foreach ($campaigns as $campaign) {
                if ($campaign['id'] == $selectedCampaignId) {
                    $campaignExists = true;
                    break;
                }
            }
            if (!$campaignExists) {
                $selectedCampaignId = 'all';
            }
        } else {
            $selectedCampaignId = 'all'; // Default: show all comments from all campaigns
        }

        // Get comments - ALL comments (no user_id filter)
        $comments = [];
        $selectedCampaign = null;
        $commentModel = new \Modules\Discussion\Models\CommentModel();
        
        if ($selectedCampaignId === 'all') {
            // Get all comments from all campaigns - ALL USERS
            $builder = $commentModel->builder();
            $builder->where('parent_id IS NULL');
            
            // Filter by tenant if selected
            if ($selectedTenantId) {
                $builder->where('tenant_id', (int) $selectedTenantId);
            }
            
            // Pinned comments first, then by date
            $builder->orderBy('is_pinned', 'DESC');
            $builder->orderBy('created_at', 'DESC');
            $builder->limit(100); // Limit untuk performa
            
            $allComments = $builder->get()->getResultArray();
            
            // Enrich comments with campaign info and user avatar
            foreach ($allComments as &$comment) {
                $campaign = $campaignModel->find($comment['campaign_id']);
                if ($campaign) {
                    $comment['campaign_title'] = $campaign['title'] ?? 'Unknown Campaign';
                    $comment['campaign_id_display'] = $campaign['id'];
                    $tenant = $tenantModel->find($campaign['tenant_id']);
                    $comment['tenant_name'] = $tenant['name'] ?? 'Unknown';
                }
                
                // Get user data including avatar
                if (!empty($comment['user_id'])) {
                    $user = $db->table('users')->where('id', (int) $comment['user_id'])->get()->getRowArray();
                    if ($user) {
                        $avatar = $user['avatar'] ?? null;
                        if ($avatar) {
                            if (preg_match('~^https?://~', $avatar)) {
                                $comment['user_avatar'] = $avatar;
                            } elseif (strpos($avatar, '/uploads/') === 0) {
                                $comment['user_avatar'] = base_url($avatar);
                            } else {
                                $comment['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                            }
                        } else {
                            $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                } else {
                    $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                }
                
                // Get replies - ALL replies
                $replies = $commentModel->getReplies($comment['id'], 5, 'all');
                
                // Enrich replies with user avatar
                foreach ($replies as &$reply) {
                    if (!empty($reply['user_id'])) {
                        $replyUser = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                        if ($replyUser) {
                            $avatar = $replyUser['avatar'] ?? null;
                            if ($avatar) {
                                if (preg_match('~^https?://~', $avatar)) {
                                    $reply['user_avatar'] = $avatar;
                                } elseif (strpos($avatar, '/uploads/') === 0) {
                                    $reply['user_avatar'] = base_url($avatar);
                                } else {
                                    $reply['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                }
                            } else {
                                $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        } else {
                            $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                }
                
                $comment['replies'] = $replies;
            }
            
            $comments = $allComments;
        } else {
            // Get comments for selected campaign - ALL USERS
            $selectedCampaign = $campaignModel->find($selectedCampaignId);
            if ($selectedCampaign) {
                // Enrich selected campaign with tenant info
                $tenant = $tenantModel->find($selectedCampaign['tenant_id']);
                $selectedCampaign['tenant_name'] = $tenant['name'] ?? 'Unknown';
                $selectedCampaign['tenant_slug'] = $tenant['slug'] ?? '';
                
                // Get all comments - ALL USERS
                $allComments = $this->discussionService->getComments($selectedCampaignId, [
                    'limit' => 50,
                    'replies_limit' => 10,
                    'status' => 'all', // Show all comments in admin (including pending/rejected)
                ]);
                
                // Enrich comments with user avatar
                foreach ($allComments as &$comment) {
                    if (!empty($comment['user_id'])) {
                        $user = $db->table('users')->where('id', (int) $comment['user_id'])->get()->getRowArray();
                        if ($user) {
                            $avatar = $user['avatar'] ?? null;
                            if ($avatar) {
                                if (preg_match('~^https?://~', $avatar)) {
                                    $comment['user_avatar'] = $avatar;
                                } elseif (strpos($avatar, '/uploads/') === 0) {
                                    $comment['user_avatar'] = base_url($avatar);
                                } else {
                                    $comment['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                }
                            } else {
                                $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        } else {
                            $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                        }
                    } else {
                        $comment['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                    
                    // Enrich replies with user avatar
                    if (isset($comment['replies']) && is_array($comment['replies'])) {
                        foreach ($comment['replies'] as &$reply) {
                            if (!empty($reply['user_id'])) {
                                $replyUser = $db->table('users')->where('id', (int) $reply['user_id'])->get()->getRowArray();
                                if ($replyUser) {
                                    $avatar = $replyUser['avatar'] ?? null;
                                    if ($avatar) {
                                        if (preg_match('~^https?://~', $avatar)) {
                                            $reply['user_avatar'] = $avatar;
                                        } elseif (strpos($avatar, '/uploads/') === 0) {
                                            $reply['user_avatar'] = base_url($avatar);
                                        } else {
                                            $reply['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                                        }
                                    } else {
                                        $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                                    }
                                } else {
                                    $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                                }
                            } else {
                                $reply['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                            }
                        }
                    }
                }
                
                $comments = $allComments;
            }
        }

        // Calculate statistics - ALL comments (no user_id filter)
        $stats = [
            'total_comments' => 0,
            'comments_today' => 0,
            'total_replies' => 0,
            'most_liked' => 0,
        ];

        if ($selectedCampaignId === 'all') {
            // Statistics for all campaigns - ALL USERS
            $builder = $commentModel->builder();
            $builder->where('parent_id IS NULL');
            if ($selectedTenantId) {
                $builder->where('tenant_id', (int) $selectedTenantId);
            }
            $stats['total_comments'] = $builder->countAllResults(false);

            // Comments today
            $today = date('Y-m-d');
            $builderToday = $commentModel->builder();
            $builderToday->where('parent_id IS NULL');
            $builderToday->where('DATE(created_at)', $today);
            if ($selectedTenantId) {
                $builderToday->where('tenant_id', (int) $selectedTenantId);
            }
            $stats['comments_today'] = $builderToday->countAllResults(false);

            // Total replies - ALL USERS
            $builderReplies = $commentModel->builder();
            $builderReplies->where('parent_id IS NOT NULL');
            if ($selectedTenantId) {
                $builderReplies->where('tenant_id', (int) $selectedTenantId);
            }
            $stats['total_replies'] = $builderReplies->countAllResults(false);

            // Most liked comment - ALL USERS
            $builderLiked = $commentModel->builder();
            $builderLiked->orderBy('likes_count', 'DESC');
            if ($selectedTenantId) {
                $builderLiked->where('tenant_id', (int) $selectedTenantId);
            }
            $mostLiked = $builderLiked->limit(1)->get()->getRowArray();
            if ($mostLiked) {
                $stats['most_liked'] = (int) ($mostLiked['likes_count'] ?? 0);
            }
        } elseif ($selectedCampaignId) {
            // Statistics for selected campaign - ALL USERS
            $stats['total_comments'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->countAllResults(false);

            // Comments today
            $today = date('Y-m-d');
            $stats['comments_today'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->where('DATE(created_at)', $today)
                ->countAllResults(false);

            // Total replies - ALL USERS
            $stats['total_replies'] = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NOT NULL')
                ->countAllResults(false);

            // Most liked comment - ALL USERS
            $mostLiked = $commentModel->where('campaign_id', $selectedCampaignId)
                ->orderBy('likes_count', 'DESC')
                ->limit(1)
                ->first();
            if ($mostLiked) {
                $stats['most_liked'] = (int) ($mostLiked['likes_count'] ?? 0);
            }
        }

        // Get recent comments (last 5) - ALL USERS
        $recentComments = [];
        if ($selectedCampaignId === 'all') {
            $builderRecent = $commentModel->builder();
            $builderRecent->where('parent_id IS NULL');
            if ($selectedTenantId) {
                $builderRecent->where('tenant_id', (int) $selectedTenantId);
            }
            $recentComments = $builderRecent->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        } elseif ($selectedCampaignId) {
            $recentComments = $commentModel->where('campaign_id', $selectedCampaignId)
                ->where('parent_id IS NULL')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->findAll();
        }
        
        // Enrich recent comments with user avatar
        foreach ($recentComments as &$recent) {
            if (!empty($recent['user_id'])) {
                $user = $db->table('users')->where('id', (int) $recent['user_id'])->get()->getRowArray();
                if ($user) {
                    $avatar = $user['avatar'] ?? null;
                    if ($avatar) {
                        if (preg_match('~^https?://~', $avatar)) {
                            $recent['user_avatar'] = $avatar;
                        } elseif (strpos($avatar, '/uploads/') === 0) {
                            $recent['user_avatar'] = base_url($avatar);
                        } else {
                            $recent['user_avatar'] = base_url('/uploads/' . ltrim($avatar, '/'));
                        }
                    } else {
                        $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                    }
                } else {
                    $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
                }
            } else {
                $recent['user_avatar'] = base_url('admin-template/assets/avatars/user-default.jpg');
            }
        }

        $data = [
            'pageTitle' => 'Diskusi & Komentar',
            'userRole' => $userRole,
            'title' => 'Diskusi & Komentar - Admin Dashboard',
            'page_title' => 'Diskusi & Komentar',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $authUser['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'tenants' => $tenants,
            'selectedTenantId' => $selectedTenantId,
            'campaigns' => $campaigns,
            'selectedCampaignId' => $selectedCampaignId,
            'selectedCampaign' => $selectedCampaign,
            'comments' => $comments,
            'stats' => $stats,
            'recentComments' => $recentComments,
        ];

        return view('Modules\\Discussion\\Views\\admin_index', $data);
    }
}

