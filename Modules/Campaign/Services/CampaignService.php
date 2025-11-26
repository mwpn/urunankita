<?php

namespace Modules\Campaign\Services;

use Modules\Campaign\Models\CampaignModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class CampaignService
{
    protected CampaignModel $campaignModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        // Simplified: All models use default database
        // BaseModel will auto-filter by tenant_id from session
        $this->campaignModel = new CampaignModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create campaign (Urunan)
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            throw new \RuntimeException('Tenant not found');
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Validate campaign type
        $campaignType = $data['campaign_type'] ?? 'target_based';
        if (!in_array($campaignType, ['target_based', 'ongoing'])) {
            throw new \RuntimeException('Campaign type harus target_based atau ongoing');
        }

        // Validate target_amount for target_based
        if ($campaignType === 'target_based') {
            // Parse target_amount if it's a string (from currency input)
            $targetAmount = $data['target_amount'] ?? null;
            if ($targetAmount !== null && !is_numeric($targetAmount)) {
                // Remove currency formatting
                $targetAmount = str_replace(['.', ',', ' ', 'Rp'], '', $targetAmount);
                // Convert to integer (remove decimal part)
                $targetAmount = (int) $targetAmount;
            } elseif ($targetAmount !== null) {
                // Convert to integer (remove decimal part)
                $targetAmount = (int) $targetAmount;
            }
            
            if (empty($targetAmount) || $targetAmount <= 0) {
                throw new \RuntimeException('Target dana wajib diisi untuk urunan target based');
            }
        } else {
            // Ongoing campaigns don't need target_amount
            $targetAmount = null;
        }

        // Handle images - can be array or JSON string
        $imagesValue = null;
        if (isset($data['images'])) {
            if (is_array($data['images'])) {
                $imagesValue = json_encode($data['images']);
            } elseif (is_string($data['images'])) {
                // Check if already JSON
                $decoded = json_decode($data['images'], true);
                $imagesValue = is_array($decoded) ? $data['images'] : json_encode([$data['images']]);
            }
        }

        $campaignData = [
            'tenant_id' => $tenantId,
            'creator_user_id' => auth_user()['id'] ?? null,
            'beneficiary_id' => $data['beneficiary_id'] ?? null,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'campaign_type' => $campaignType,
            'target_amount' => $targetAmount,
            'current_amount' => 0,
            'category' => $data['category'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'is_priority' => isset($data['is_priority']) ? (int) $data['is_priority'] : 0,
            'featured_image' => $data['featured_image'] ?? null,
            'images' => $imagesValue,
            'deadline' => $campaignType === 'target_based' ? ($data['deadline'] ?? null) : null, // Ongoing tidak perlu deadline
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_address' => $data['location_address'] ?? null,
            'views_count' => 0,
            'donors_count' => 0,
        ];

        try {
            $id = $this->campaignModel->insert($campaignData);
        } catch (\Exception $e) {
            log_message('error', 'CampaignModel insert failed: ' . $e->getMessage() . ' | Data: ' . json_encode($campaignData));
            throw new \RuntimeException('Gagal menyimpan data urunan: ' . $e->getMessage());
        }

        if ($id) {
            $this->activityLog->logCreate('Campaign', $id, $campaignData, 'Urunan dibuat');
        }

        return $id ?: false;
    }

    /**
     * Update campaign
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $oldCampaign = $this->campaignModel->find($id);
        if (!$oldCampaign) {
            return false;
        }

        // Security: Check tenant ownership (only for tenant users, not admin)
        $tenantId = session()->get('tenant_id');
        $userRole = auth_user()['role'] ?? null;
        
        // Admin can update any campaign, but tenant users can only update their own
        if ($tenantId && $userRole !== 'super_admin' && $oldCampaign['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['slug'])) {
            $newSlug = trim((string) $data['slug']);
            // Only process if slug actually changed
            if ($newSlug !== (string) ($oldCampaign['slug'] ?? '')) {
                // Normalize from provided slug (not title) to keep admin intent
                $updateData['slug'] = $this->generateSlug($newSlug, $id);
            }
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['campaign_type'])) {
            $campaignType = $data['campaign_type'];
            if (!in_array($campaignType, ['target_based', 'ongoing'])) {
                throw new \RuntimeException('Campaign type harus target_based atau ongoing');
            }
            
            $updateData['campaign_type'] = $campaignType;
            
            // If changing to ongoing, remove target_amount and deadline
            if ($campaignType === 'ongoing') {
                $updateData['target_amount'] = null;
                $updateData['deadline'] = null;
            } elseif ($campaignType === 'target_based' && isset($data['target_amount'])) {
                $updateData['target_amount'] = $data['target_amount'];
            }
        }
        
        if (isset($data['target_amount']) && ($oldCampaign['campaign_type'] ?? 'target_based') === 'target_based') {
            $updateData['target_amount'] = $data['target_amount'];
        }
        if (isset($data['category'])) {
            $updateData['category'] = $data['category'];
        }
        if (isset($data['featured_image'])) {
            $updateData['featured_image'] = $data['featured_image'];
        }
        if (isset($data['images'])) {
            $updateData['images'] = is_array($data['images']) 
                ? json_encode($data['images']) 
                : $data['images'];
        }
        if (isset($data['deadline']) && ($oldCampaign['campaign_type'] ?? 'target_based') === 'target_based') {
            // Only allow deadline for target_based campaigns
            $updateData['deadline'] = $data['deadline'];
        }
        if (isset($data['beneficiary_id'])) {
            $updateData['beneficiary_id'] = $data['beneficiary_id'];
        }
        if (isset($data['latitude'])) {
            $updateData['latitude'] = $data['latitude'];
        }
        if (isset($data['longitude'])) {
            $updateData['longitude'] = $data['longitude'];
        }
        if (isset($data['location_address'])) {
            $updateData['location_address'] = $data['location_address'];
        }

        // Handle status change
        if (isset($data['status'])) {
            $allowedStatuses = ['draft', 'pending_verification', 'active', 'completed', 'rejected', 'closed', 'suspended', 'deleted'];
            $newStatus = (string) $data['status'];
            if (!in_array($newStatus, $allowedStatuses, true)) {
                throw new \RuntimeException('Status tidak valid');
            }
            // Only set if different
            if (($oldCampaign['status'] ?? null) !== $newStatus) {
                $updateData['status'] = $newStatus;
            }
        }
        
        // Handle is_priority
        if (isset($data['is_priority'])) {
            $updateData['is_priority'] = (int) $data['is_priority'];
        }

        // Provide placeholder so is_unique[campaigns.slug,id,{id}] excludes current row when slug is set
        if (isset($updateData['slug']) && method_exists($this->campaignModel, 'setValidationRulePlaceholders')) {
            $this->campaignModel->setValidationRulePlaceholders(['id' => $id]);
        }

        $result = $this->campaignModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Campaign', $id, $oldCampaign, $updateData, 'Urunan diperbarui');
            return true;
        }

        // Bubble up validation/database errors for better feedback
        $errors = method_exists($this->campaignModel, 'errors') ? $this->campaignModel->errors() : [];
        $errorMessage = !empty($errors) ? json_encode($errors) : 'Unknown error';
        throw new \RuntimeException('Gagal memperbarui urunan: ' . $errorMessage);
    }

    /**
     * Submit campaign for verification
     *
     * @param int $id
     * @return bool
     */
    public function submitForVerification(int $id): bool
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return false;
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($campaign['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Access denied');
        }

        if ($campaign['status'] !== 'draft') {
            throw new \RuntimeException('Only draft campaigns can be submitted for verification');
        }

        $result = $this->campaignModel->update($id, [
            'status' => 'pending_verification',
        ]);

        if ($result) {
            $this->activityLog->logUpdate('Campaign', $id, $campaign, ['status' => 'pending_verification'], 'Urunan diajukan untuk verifikasi');
        }

        return $result;
    }

    /**
     * Verify campaign (Tim UrunanKita only)
     *
     * @param int $id
     * @param bool $approved
     * @param string|null $rejectionReason
     * @return bool
     */
    public function verify(int $id, bool $approved, ?string $rejectionReason = null): bool
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return false;
        }

        $user = auth_user();
        $updateData = [
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => $user['id'] ?? null,
        ];

        if ($approved) {
            $updateData['status'] = 'active';
        } else {
            $updateData['status'] = 'rejected';
            $updateData['rejection_reason'] = $rejectionReason;
        }

        $result = $this->campaignModel->update($id, $updateData);

        if ($result) {
            $action = $approved ? 'diverifikasi dan diaktifkan' : 'ditolak';
            $this->activityLog->logUpdate('Campaign', $id, $campaign, $updateData, "Urunan {$action}");
        }

        return $result;
    }

    /**
     * Get campaign by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->campaignModel->find($id);
    }

    /**
     * Get campaign by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->campaignModel->getBySlug($slug);
    }

    /**
     * Get campaigns by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        return $this->campaignModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get public campaigns
     *
     * @param array $filters
     * @return array
     */
    public function getPublicCampaigns(array $filters = []): array
    {
        return $this->campaignModel->getPublicCampaigns($filters);
    }

    /**
     * Generate slug from title
     *
     * @param string $title
     * @param int|null $excludeId
     * @return string
     */
    protected function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 200); // Limit length

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $existing = $this->campaignModel->where('slug', $slug);
            if ($excludeId) {
                $existing->where('id !=', $excludeId);
            }
            
            if ($existing->countAllResults() === 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

