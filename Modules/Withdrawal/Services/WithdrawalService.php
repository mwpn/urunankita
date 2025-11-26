<?php

namespace Modules\Withdrawal\Services;

use Modules\Withdrawal\Models\WithdrawalModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\Beneficiary\Models\BeneficiaryModel;
use Modules\Notification\Services\NotificationService;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class WithdrawalService
{
    protected WithdrawalModel $withdrawalModel;
    protected CampaignModel $campaignModel;
    protected BeneficiaryModel $beneficiaryModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->withdrawalModel = new WithdrawalModel();
        $this->campaignModel = new CampaignModel();
        $this->beneficiaryModel = new BeneficiaryModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Request withdrawal (Penggalang request penyaluran)
     *
     * @param array $data
     * @return int|false
     */
    public function request(array $data)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            throw new \RuntimeException('Tenant not found');
        }

        // Verify campaign
        $campaign = $this->campaignModel->find($data['campaign_id']);
        if (!$campaign || $campaign['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Campaign not found or access denied');
        }

        // Verify beneficiary
        $beneficiary = $this->beneficiaryModel->find($data['beneficiary_id']);
        if (!$beneficiary || $beneficiary['tenant_id'] != $tenantId) {
            throw new \RuntimeException('Beneficiary not found or access denied');
        }

        // Check available amount
        $totalWithdrawn = $this->withdrawalModel->getTotalWithdrawn($data['campaign_id']);
        $availableAmount = (float) $campaign['current_amount'] - $totalWithdrawn;

        if ((float) $data['amount'] > $availableAmount) {
            throw new \RuntimeException('Jumlah penyaluran melebihi dana tersedia');
        }

        $withdrawalData = [
            'campaign_id' => $data['campaign_id'],
            'tenant_id' => $tenantId,
            'beneficiary_id' => $data['beneficiary_id'],
            'amount' => $data['amount'],
            'status' => 'pending',
            'requested_by' => auth_user()['id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'requested_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->withdrawalModel->insert($withdrawalData);

        if ($id) {
            $this->activityLog->logCreate('Withdrawal', $id, $withdrawalData, 'Permintaan penyaluran dana diajukan');
        }

        return $id ?: false;
    }

    /**
     * Approve withdrawal (Tim UrunanKita)
     *
     * @param int $id
     * @return bool
     */
    public function approve(int $id): bool
    {
        $withdrawal = $this->withdrawalModel->find($id);
        if (!$withdrawal) {
            return false;
        }

        if ($withdrawal['status'] !== 'pending') {
            throw new \RuntimeException('Only pending withdrawals can be approved');
        }

        $user = auth_user();
        $updateData = [
            'status' => 'approved',
            'approved_by' => $user['id'] ?? null,
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->withdrawalModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Withdrawal', $id, $withdrawal, $updateData, 'Penyaluran dana disetujui');
            
            // Send notification to beneficiary
            $this->sendApprovalNotification($withdrawal);
        }

        return $result;
    }

    /**
     * Reject withdrawal
     *
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function reject(int $id, string $reason): bool
    {
        $withdrawal = $this->withdrawalModel->find($id);
        if (!$withdrawal) {
            return false;
        }

        $user = auth_user();
        $updateData = [
            'status' => 'rejected',
            'approved_by' => $user['id'] ?? null,
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason,
        ];

        $result = $this->withdrawalModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Withdrawal', $id, $withdrawal, $updateData, 'Penyaluran dana ditolak');
        }

        return $result;
    }

    /**
     * Complete withdrawal (mark as transferred)
     *
     * @param int $id
     * @param string|null $transferProof
     * @return bool
     */
    public function complete(int $id, ?string $transferProof = null): bool
    {
        $withdrawal = $this->withdrawalModel->find($id);
        if (!$withdrawal) {
            return false;
        }

        if (!in_array($withdrawal['status'], ['approved', 'processing'])) {
            throw new \RuntimeException('Only approved or processing withdrawals can be completed');
        }

        $updateData = [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        if ($transferProof) {
            $updateData['transfer_proof'] = $transferProof;
        }

        $result = $this->withdrawalModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Withdrawal', $id, $withdrawal, $updateData, 'Penyaluran dana selesai');

            // Send completion notification
            $this->sendCompletionNotification($withdrawal);
        }

        return $result;
    }

    /**
     * Get withdrawals by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        return $this->withdrawalModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get withdrawals by campaign
     *
     * @param int $campaignId
     * @return array
     */
    public function getByCampaign(int $campaignId): array
    {
        return $this->withdrawalModel->getByCampaign($campaignId);
    }

    /**
     * Send approval notification
     *
     * @param array $withdrawal
     * @return void
     */
    protected function sendApprovalNotification(array $withdrawal): void
    {
        try {
            $beneficiary = $this->beneficiaryModel->find($withdrawal['beneficiary_id']);
            $notificationService = BaseServices::notification();
            
            if ($notificationService && $beneficiary && $beneficiary['phone']) {
                $templateService = \Modules\Notification\Config\Services::messageTemplate();
                $campaign = $this->campaignModel->find($withdrawal['campaign_id']);
                
                // Render template (with tenant context)
                $message = $templateService->render('withdrawal_approved', [
                    'amount' => $withdrawal['amount'],
                    'campaign_title' => $campaign['title'] ?? '',
                ], null, $withdrawal['tenant_id'] ?? null);
                
                // Skip sending if template is disabled or message is empty
                if (empty($message)) {
                    log_message('debug', 'Template withdrawal_approved is disabled or empty, skipping WhatsApp notification');
                    return;
                }
                
                $notificationService->sendWhatsApp(
                    $beneficiary['phone'],
                    $message,
                    [
                        'type' => 'withdrawal_approved',
                        'withdrawal_id' => $withdrawal['id'],
                    ]
                );
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send approval notification: ' . $e->getMessage());
        }
    }

    /**
     * Send completion notification
     *
     * @param array $withdrawal
     * @return void
     */
    protected function sendCompletionNotification(array $withdrawal): void
    {
        try {
            $beneficiary = $this->beneficiaryModel->find($withdrawal['beneficiary_id']);
            $notificationService = BaseServices::notification();
            
            if ($notificationService && $beneficiary && $beneficiary['phone']) {
                $campaign = $this->campaignModel->find($withdrawal['campaign_id']);
                $message = "Dana sebesar Rp " . number_format($withdrawal['amount'], 0, ',', '.') . " untuk urunan '{$campaign['title']}' telah ditransfer ke rekening Anda. Terima kasih atas kepercayaannya.";
                
                $notificationService->sendWhatsApp(
                    $beneficiary['phone'],
                    $message,
                    [
                        'type' => 'withdrawal_completed',
                        'withdrawal_id' => $withdrawal['id'],
                    ]
                );
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send completion notification: ' . $e->getMessage());
        }
    }
}

