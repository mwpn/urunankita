<?php

namespace Modules\CampaignUpdate\Services;

use Modules\CampaignUpdate\Models\CampaignUpdateModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class CampaignUpdateService
{
    protected CampaignUpdateModel $updateModel;
    protected CampaignModel $campaignModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->updateModel = new CampaignUpdateModel();
        $this->campaignModel = new CampaignModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        $authUser = session()->get('auth_user') ?? [];
        $userRole = $authUser['role'] ?? '';
        return in_array($userRole, ['superadmin', 'super_admin', 'admin'], true);
    }

    /**
     * Create campaign update (Laporan Kabar Terbaru)
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $isAdmin = $this->isAdmin();
        
        // For admin, get platform tenant ID
        if ($isAdmin) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if (!$platform) {
                throw new \RuntimeException('Platform tenant not found');
            }
            $tenantId = (int) $platform['id'];
        } else {
            // For tenant, resolve tenant_id from session or derive from logged-in user
            $tenantId = session()->get('tenant_id');
            
            // Fallback: derive from logged-in user if tenant_id not in session
            if (!$tenantId) {
                $authUser = session()->get('auth_user') ?? [];
                $userId = $authUser['id'] ?? null;
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
            }
            
            if (!$tenantId) {
                throw new \RuntimeException('Tenant not found');
            }
        }

        // Verify campaign exists
        $campaign = $this->campaignModel->find($data['campaign_id']);
        if (!$campaign) {
            throw new \RuntimeException('Campaign not found');
        }
        
        // For tenant, verify ownership; for admin, only allow platform tenant campaigns
        if (!$isAdmin && $campaign['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Campaign not found or access denied');
        }
        
        // For admin, only allow creating updates for platform tenant campaigns
        if ($isAdmin && $campaign['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Admin hanya dapat membuat laporan untuk urunan platform tenant');
        }

        // Handle images - encode array to JSON, or null if empty/not set
        $imagesValue = null;
        if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
            $imagesValue = json_encode($data['images']);
        } elseif (isset($data['images']) && !empty($data['images']) && is_string($data['images'])) {
            // Already JSON string, use as is
            $imagesValue = $data['images'];
        }
        
        $updateData = [
            'campaign_id' => $data['campaign_id'],
            'tenant_id' => $tenantId,
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'amount_used' => isset($data['amount_used']) && $data['amount_used'] !== '' && $data['amount_used'] !== null 
                ? (float) $data['amount_used'] 
                : null,
            'images' => $imagesValue,
            'youtube_url' => $data['youtube_url'] ?? null,
            'author_id' => auth_user()['id'] ?? null,
            'is_pinned' => $data['is_pinned'] ?? false,
        ];

        $id = $this->updateModel->insert($updateData);

        if ($id) {
            $this->activityLog->logCreate('CampaignUpdate', $id, $updateData, 'Laporan Kabar Terbaru dibuat');
        }

        return $id ?: false;
    }

    /**
     * Get updates by campaign
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getByCampaign(int $campaignId, array $filters = []): array
    {
        return $this->updateModel->getByCampaign($campaignId, $filters);
    }

    /**
     * Get update by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $update = $this->updateModel->find($id);
        if ($update) {
            if ($update['images']) {
                $update['images'] = json_decode($update['images'], true) ?? [];
            } else {
                $update['images'] = [];
            }
        }
        return $update;
    }

    /**
     * Update campaign update
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $oldUpdate = $this->updateModel->find($id);
        if (!$oldUpdate) {
            return false;
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($oldUpdate['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        if (isset($data['images'])) {
            $updateData['images'] = is_array($data['images']) 
                ? json_encode($data['images']) 
                : $data['images'];
        }
        if (isset($data['youtube_url'])) {
            $updateData['youtube_url'] = $data['youtube_url'];
        }
        if (isset($data['is_pinned'])) {
            $updateData['is_pinned'] = $data['is_pinned'] ? 1 : 0;
        }

        if (empty($updateData)) {
            return false;
        }

        $result = $this->updateModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('CampaignUpdate', $id, $oldUpdate, $updateData, 'Laporan Kabar Terbaru diperbarui');
        }

        return $result;
    }

    /**
     * Delete campaign update
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $update = $this->updateModel->find($id);
        if (!$update) {
            return false;
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($update['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        $result = $this->updateModel->delete($id);

        if ($result) {
            $this->activityLog->logDelete('CampaignUpdate', $id, $update, 'Laporan Kabar Terbaru dihapus');
        }

        return $result;
    }
}

