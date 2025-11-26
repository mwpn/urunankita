<?php

namespace Modules\Donation\Services;

use Modules\Donation\Models\DonationModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\Tenant\Models\TenantModel;
use Modules\Notification\Services\NotificationService;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;
use Config\Database;
use Modules\Setting\Models\PaymentMethodModel;

class DonationService
{
    protected DonationModel $donationModel;
    protected CampaignModel $campaignModel;
    protected TenantModel $tenantModel;
    protected PaymentMethodModel $paymentMethodModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->donationModel = new DonationModel();
        $this->campaignModel = new CampaignModel();
        $this->tenantModel = new TenantModel();
        $this->activityLog = BaseServices::activityLog();
        $this->paymentMethodModel = new PaymentMethodModel();
    }

    /**
     * Create donation (Donasi dari Orang Baik)
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        // Get campaign info - bypass tenant filter for public donations
        // Use query builder directly to avoid tenant filtering
        $db = \Config\Database::connect();
        $campaign = $db->table('campaigns')
            ->where('id', $data['campaign_id'])
            ->get()
            ->getRowArray();

        if (!$campaign) {
            throw new \RuntimeException('Urunan tidak ditemukan');
        }

        if ($campaign['status'] !== 'active') {
            throw new \RuntimeException('Urunan tidak aktif');
        }

        // Get tenant bank accounts if bank transfer
        $bankAccountId = null;
        if (($data['payment_method'] ?? null) === 'bank_transfer' && isset($data['bank_account_id'])) {
            $bankAccountId = $data['bank_account_id'];
        }

        $now = date('Y-m-d H:i:s');
        $donationData = [
            'campaign_id' => (int) $data['campaign_id'],
            'tenant_id' => (int) $campaign['tenant_id'],
            'donor_id' => auth_user()['id'] ?? null,
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'amount' => (float) $data['amount'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_status' => 'pending',
            'payment_proof' => $data['payment_proof'] ?? null,
            'message' => $data['message'] ?? null,
            'bank_account_id' => $bankAccountId ? (int) $bankAccountId : null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Insert directly using query builder to skip validation for public donations
        try {
            $db = \Config\Database::connect();
            $db->table('donations')->insert($donationData);
            $id = $db->insertID();
        } catch (\Exception $e) {
            throw new \RuntimeException('Gagal menyimpan donasi: ' . $e->getMessage());
        }

        if ($id) {
            $this->activityLog->logCreate('Donation', $id, $donationData, 'Donasi dari Orang Baik');

            // Send notification to donor
            log_message('info', '=== Starting notification process for donation ID: ' . $id . ' ===');
            log_message('info', 'Donation data: tenant_id=' . ($donationData['tenant_id'] ?? 'null') . ', campaign_id=' . ($donationData['campaign_id'] ?? 'null'));
            
            try {
                $this->sendDonationNotification($id, $donationData);
                log_message('info', 'Donor notification process completed');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send donor notification: ' . $e->getMessage());
            }
            
            // Send notification to tenant (owner) for manual approval
            // IMPORTANT: This should always be sent, even if donor and owner have the same phone number
            // because they are different notifications with different purposes
            try {
                log_message('info', 'Starting tenant notification process...');
                $this->sendTenantDonationNotification($id, $donationData);
                log_message('info', 'Tenant notification process completed');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send tenant notification: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            }
            
            // Send notification to admin platform (superadmin) if configured
            try {
                log_message('info', 'Starting admin notification process...');
                $this->sendAdminDonationNotification($id, $donationData);
                log_message('info', 'Admin notification process completed');
            } catch (\Exception $e) {
                log_message('error', 'Failed to send admin notification: ' . $e->getMessage());
            }
            
            log_message('info', '=== Notification process completed for donation ID: ' . $id . ' ===');
        }

        return $id ?: false;
    }

    /**
     * Get bank accounts for tenant (for donation instructions)
     *
     * @param int $tenantId
     * @return array
     */
    public function getTenantBankAccounts(int $tenantId): array
    {
        $tenant = $this->tenantModel->findWithBankAccounts($tenantId);
        return $tenant['bank_accounts'] ?? [];
    }

    /**
     * Mark donation as paid (auto or manual confirmation)
     *
     * @param int $id
     * @param string|null $paymentProof
     * @param bool $isManualConfirmation
     * @return bool
     */
    public function markAsPaid(int $id, ?string $paymentProof = null, bool $isManualConfirmation = false): bool
    {
        $donation = $this->donationModel->find($id);
        if (!$donation) {
            return false;
        }

        if ($donation['payment_status'] === 'paid') {
            return true; // Already paid
        }

        $updateData = [
            'payment_status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ];

        if ($paymentProof) {
            $updateData['payment_proof'] = $paymentProof;
        }

        // If manual confirmation by tenant
        if ($isManualConfirmation) {
            $user = auth_user();
            $updateData['confirmed_by'] = $user['id'] ?? null;
            $updateData['confirmed_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->donationModel->update($id, $updateData);

        if ($result) {
            // Update campaign current_amount and donors_count
            $this->campaignModel->addAmount($donation['campaign_id'], (float) $donation['amount']);
            $this->campaignModel->incrementDonors($donation['campaign_id']);

            $this->activityLog->logUpdate('Donation', $id, $donation, $updateData, 'Donasi telah dibayar');
            
            // Send thank you notification
            $this->sendThankYouNotification($donation);
        }

        return $result;
    }

    /**
     * Get donations by campaign
     *
     * @param int $campaignId
     * @param array $filters
     * @return array
     */
    public function getByCampaign(int $campaignId, array $filters = []): array
    {
        return $this->donationModel->getByCampaign($campaignId, $filters);
    }

    /**
     * Get donation statistics
     *
     * @param int $campaignId
     * @return array
     */
    public function getStats(int $campaignId): array
    {
        return $this->donationModel->getCampaignStats($campaignId);
    }

    /**
     * Send donation notification
     *
     * @param int $donationId
     * @param array $donationData
     * @return void
     */
    protected function sendDonationNotification(int $donationId, array $donationData): void
    {
        try {
            $notificationService = BaseServices::notification();
            $templateService = \Modules\Notification\Config\Services::messageTemplate();
            
            // Log untuk debugging
            log_message('debug', 'sendDonationNotification called for donation ID: ' . $donationId);
            log_message('debug', 'Donor phone: ' . ($donationData['donor_phone'] ?? 'not set'));
            log_message('debug', 'Notification service: ' . ($notificationService ? 'exists' : 'null'));
            log_message('debug', 'Template service: ' . ($templateService ? 'exists' : 'null'));
            
            if (!$notificationService) {
                log_message('error', 'NotificationService is null');
                return;
            }
            
            if (!$templateService) {
                log_message('error', 'MessageTemplateService is null');
                return;
            }
            
            if (empty($donationData['donor_phone'])) {
                log_message('warning', 'Donor phone is empty, skipping WhatsApp notification');
                return;
            }
            
            // Get campaign title if available
            $campaignTitle = '';
            if (!empty($donationData['campaign_id'])) {
                $campaign = $this->campaignModel->find($donationData['campaign_id']);
                $campaignTitle = $campaign['title'] ?? '';
            }
            
            $paymentPlaceholders = $this->getPaymentPlaceholderData($donationData);
            
            // Render template (with tenant context for tenant-specific templates)
            $message = $templateService->render('donation_created', array_merge([
                'amount' => $donationData['amount'],
                'donor_name' => $donationData['donor_name'] ?? '',
                'campaign_title' => $campaignTitle,
            ], $paymentPlaceholders), null, $donationData['tenant_id'] ?? null);
            
            // Skip sending if template is disabled or message is empty
            if (empty($message)) {
                log_message('debug', 'Template is disabled or empty, skipping WhatsApp notification');
                return;
            }
            
            log_message('debug', 'Rendered message: ' . substr($message, 0, 100) . '...');
            log_message('debug', 'Sending WhatsApp to: ' . $donationData['donor_phone']);
            
            $result = $notificationService->sendWhatsApp(
                $donationData['donor_phone'],
                $message,
                [
                    'type' => 'donation_created',
                    'donation_id' => $donationId,
                ]
            );
            
            log_message('debug', 'WhatsApp send result: ' . json_encode($result));
            
            if (!$result['success']) {
                log_message('error', 'WhatsApp send failed: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send donation notification: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Send notification to tenant when new donation needs approval
     *
     * @param int $donationId
     * @param array $donationData
     * @return void
     */
    protected function sendTenantDonationNotification(int $donationId, array $donationData): void
    {
        try {
            // SAME LOGGING STYLE AS DONOR NOTIFICATION
            log_message('info', 'sendTenantDonationNotification called for donation ID: ' . $donationId);
            log_message('info', 'Donation data: tenant_id=' . ($donationData['tenant_id'] ?? 'null') . ', campaign_id=' . ($donationData['campaign_id'] ?? 'null'));
            
            $notificationService = BaseServices::notification();
            $templateService = \Modules\Notification\Config\Services::messageTemplate();
            
            log_message('debug', 'Notification service: ' . ($notificationService ? 'exists' : 'null'));
            log_message('debug', 'Template service: ' . ($templateService ? 'exists' : 'null'));
            
            // SAME CHECK AS DONOR NOTIFICATION
            if (!$notificationService) {
                log_message('error', 'NotificationService is null');
                return;
            }
            
            if (!$templateService) {
                log_message('error', 'MessageTemplateService is null');
                return;
            }
            
            // STEP 1: Get campaign info to know which tenant owns this urunan
            $campaign = null;
            $campaignTitle = '';
            $tenantId = null;
            
            if (!empty($donationData['campaign_id'])) {
                $campaign = $this->campaignModel->find($donationData['campaign_id']);
                log_message('debug', 'Campaign found: ' . ($campaign ? 'yes (ID: ' . $campaign['id'] . ', Title: ' . ($campaign['title'] ?? 'null') . ', Tenant ID: ' . ($campaign['tenant_id'] ?? 'null') . ')' : 'no'));
                
                if ($campaign) {
                    $campaignTitle = $campaign['title'] ?? '';
                    $tenantId = (int) $campaign['tenant_id'];
                    log_message('debug', 'Campaign belongs to tenant_id: ' . $tenantId);
                } else {
                    log_message('warning', 'Campaign not found for campaign_id: ' . $donationData['campaign_id']);
                }
            }
            
            // Fallback to donationData tenant_id if campaign not found
            if (!$tenantId && !empty($donationData['tenant_id'])) {
                $tenantId = (int) $donationData['tenant_id'];
                log_message('debug', 'Using tenant_id from donationData: ' . $tenantId);
            }
            
            if (!$tenantId) {
                log_message('error', 'Cannot determine tenant_id - both campaign and donationData tenant_id are missing');
                return;
            }
            
            // STEP 2: Get tenant info
            $tenant = $this->tenantModel->find($tenantId);
            log_message('debug', 'Tenant found: ' . ($tenant ? 'yes (ID: ' . $tenant['id'] . ', Name: ' . ($tenant['name'] ?? 'null') . ', Owner ID: ' . ($tenant['owner_id'] ?? 'null') . ')' : 'no'));
            
            if (!$tenant) {
                log_message('warning', 'Tenant not found for tenant_id: ' . $tenantId);
                return;
            }
            
            // STEP 3: Get owner/tenant user phone
            $db = \Config\Database::connect();
            $owner = null;
            $ownerPhone = null;
            
            // Try to get owner by owner_id first (from tenant table)
            if (!empty($tenant['owner_id'])) {
                $owner = $db->table('users')
                    ->where('id', (int) $tenant['owner_id'])
                    ->get()
                    ->getRowArray();
                log_message('debug', 'Owner found by owner_id (' . $tenant['owner_id'] . '): ' . ($owner ? 'yes (ID: ' . $owner['id'] . ', Name: ' . ($owner['name'] ?? 'null') . ', Phone: ' . ($owner['phone'] ?? 'empty') . ')' : 'no'));
                
                if ($owner && !empty($owner['phone']) && trim($owner['phone']) !== '') {
                    $ownerPhone = trim($owner['phone']);
                    log_message('debug', 'Using owner from owner_id: ' . $ownerPhone);
                }
            }
            
            // If owner_id is empty or owner not found, try to find tenant owner/admin user
            // NOTE: Since tenant_id column may not exist in users table, we use alternative approach:
            // 1. Try to find users by role (tenant_owner, tenant_admin, penggalang_dana) without tenant_id filter
            // 2. Then filter by checking if they're associated with this tenant via other means
            if (empty($ownerPhone)) {
                log_message('debug', 'Owner_id is empty or owner not found, trying to find tenant owner/admin user for tenant_id: ' . $tenantId);
                
                // Try to find user with tenant_owner or tenant_admin role
                // Since tenant_id column may not exist, we'll search by role and check owner_id match
                try {
                    // First, try to find users with matching roles
                    // We'll check if tenant_id column exists first
                    $columns = $db->getFieldNames('users');
                    $hasTenantId = in_array('tenant_id', $columns);
                    
                    if ($hasTenantId) {
                        // If tenant_id column exists, use it
                        $allOwners = $db->table('users')
                            ->where('tenant_id', $tenantId)
                            ->whereIn('role', ['tenant_owner', 'tenant_admin', 'penggalang_dana'])
                            ->orderBy('id', 'ASC')
                            ->get()
                            ->getResultArray();
                    } else {
                        // If tenant_id column doesn't exist, find by role only
                        // Then we'll rely on owner_id from tenant table or superadmin fallback
                        $allOwners = $db->table('users')
                            ->whereIn('role', ['tenant_owner', 'tenant_admin', 'penggalang_dana'])
                            ->orderBy('id', 'ASC')
                            ->get()
                            ->getResultArray();
                        
                        log_message('debug', 'tenant_id column not found in users table, searching by role only. Found ' . count($allOwners) . ' users with matching roles');
                    }
                    
                    log_message('debug', 'Found ' . count($allOwners) . ' users with tenant_owner/admin role');
                } catch (\Exception $e) {
                    log_message('error', 'Error querying users table: ' . $e->getMessage());
                    log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                    $allOwners = [];
                }
                
                // Find first one with phone
                foreach ($allOwners as $ownerCandidate) {
                    log_message('debug', 'Checking user ID: ' . $ownerCandidate['id'] . ', Role: ' . ($ownerCandidate['role'] ?? 'null') . ', Phone: ' . ($ownerCandidate['phone'] ?? 'empty'));
                    if (!empty($ownerCandidate['phone']) && trim($ownerCandidate['phone']) !== '') {
                        $owner = $ownerCandidate;
                        $ownerPhone = trim($ownerCandidate['phone']);
                        log_message('debug', 'Owner found by role: yes (ID: ' . $owner['id'] . ', Role: ' . ($owner['role'] ?? 'unknown') . ', Phone: ' . $ownerPhone . ')');
                        break;
                    }
                }
            }
            
            log_message('debug', 'Final owner check: ' . ($ownerPhone ? 'yes (Phone: ' . $ownerPhone . ')' : 'no'));
            
            // STEP 4: Final check - ensure we have owner phone
            // If no owner found, try to get superadmin as fallback (especially for platform tenant)
            if (empty($ownerPhone)) {
                log_message('warning', 'Owner not found or phone is empty for tenant_id: ' . $tenantId . ' (owner_id: ' . ($tenant['owner_id'] ?? 'null') . ')');
                
                // Fallback: Try to get superadmin user (for platform tenant or if no owner found)
                log_message('debug', 'Trying to find superadmin as fallback...');
                $allSuperadmins = $db->table('users')
                    ->whereIn('role', ['superadmin', 'super_admin', 'admin'])
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
                
                log_message('debug', 'Found ' . count($allSuperadmins) . ' superadmin users');
                
                // Find first superadmin with phone
                $superadmin = null;
                foreach ($allSuperadmins as $sa) {
                    log_message('debug', 'Checking superadmin ID: ' . $sa['id'] . ', Name: ' . ($sa['name'] ?? 'null') . ', Phone: ' . ($sa['phone'] ?? 'empty'));
                    if (!empty($sa['phone']) && trim($sa['phone']) !== '') {
                        $superadmin = $sa;
                        break;
                    }
                }
                
                if ($superadmin && !empty($superadmin['phone']) && trim($superadmin['phone']) !== '') {
                    $owner = $superadmin;
                    $ownerPhone = trim($superadmin['phone']);
                    log_message('info', 'Using superadmin as fallback: User ID ' . $superadmin['id'] . ', Name: ' . ($superadmin['name'] ?? 'unknown') . ', Phone: ' . $ownerPhone);
                } else {
                    log_message('warning', 'Superadmin also not found or has no phone, skipping tenant notification');
                    log_message('warning', 'Please create a user with tenant_id=' . $tenantId . ' or ensure superadmin has phone number');
                    return;
                }
            }
            
            log_message('info', 'Owner found for tenant notification: User ID ' . ($owner['id'] ?? 'unknown') . ', Phone: ' . $ownerPhone);
            
            // Campaign title already retrieved in STEP 1
            // If not retrieved yet, get it now
            if (empty($campaignTitle) && !empty($donationData['campaign_id'])) {
                if (!$campaign) {
                    $campaign = $this->campaignModel->find($donationData['campaign_id']);
                }
                $campaignTitle = $campaign['title'] ?? '';
            }
            
            $paymentPlaceholders = $this->getPaymentPlaceholderData($donationData);
            
            log_message('debug', 'Rendering template tenant_donation_new for tenant_id: ' . $tenantId);
            log_message('debug', 'Template data: amount=' . $donationData['amount'] . ', donor_name=' . ($donationData['donor_name'] ?? 'Anonim') . ', campaign_title=' . $campaignTitle);
            
            // Default template if not found in settings - SAME APPROACH AS DONOR NOTIFICATION
            $defaultTemplate = 'Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan \'{campaign_title}\'. Silakan konfirmasi pembayaran di dashboard.';
            
            // Render template (with tenant context for tenant-specific templates) - SAME AS DONOR NOTIFICATION
            // Use null as default like donation_created, then fallback to defaultTemplate if needed
            $message = $templateService->render('tenant_donation_new', array_merge([
                'amount' => $donationData['amount'],
                'donor_name' => $donationData['donor_name'] ?? 'Anonim',
                'campaign_title' => $campaignTitle,
                'donation_id' => $donationId,
            ], $paymentPlaceholders), $defaultTemplate, $tenantId);
            
            // If template returns null (disabled) but we have defaultTemplate, use it anyway
            // This ensures notification is always sent if owner phone is found
            if (empty($message) && !empty($defaultTemplate)) {
                log_message('info', 'Template returned empty, using default template for tenant notification');
                // Manually replace placeholders in default template
                $message = str_replace(
                    ['{amount}', '{donor_name}', '{campaign_title}', '{donation_id}'],
                    [
                        'Rp ' . number_format((float) $donationData['amount'], 0, ',', '.'),
                        $donationData['donor_name'] ?? 'Anonim',
                        $campaignTitle,
                        $donationId
                    ],
                    $defaultTemplate
                );
                log_message('debug', 'Default template rendered: ' . substr($message, 0, 150));
            }
            
            log_message('debug', 'Template rendered message: ' . ($message ? 'yes (' . strlen($message) . ' chars)' : 'null/empty'));
            if ($message) {
                log_message('debug', 'Rendered message preview: ' . substr($message, 0, 150));
            }
            
            // Final check - if still empty, skip (same as donor notification)
            if (empty($message)) {
                log_message('error', 'Message is still empty after all attempts, skipping notification');
                log_message('error', 'This should not happen - default template should always be used');
                return;
            }
            
            // SAME AS DONOR NOTIFICATION - simple logging and send
            log_message('debug', 'Rendered message: ' . substr($message, 0, 100) . '...');
            log_message('debug', 'Sending WhatsApp to tenant owner: ' . $ownerPhone);
            
            // Send notification to tenant owner - SAME PROCESS AS DONOR NOTIFICATION
            $result = $notificationService->sendWhatsApp(
                $ownerPhone,
                $message,
                [
                    'type' => 'tenant_donation_new',
                    'donation_id' => $donationId,
                    'tenant_id' => $tenantId,
                ]
            );
            
            log_message('debug', 'WhatsApp send result: ' . json_encode($result));
            
            if (!$result['success']) {
                log_message('error', 'WhatsApp send failed: ' . ($result['message'] ?? 'Unknown error'));
            } else {
                log_message('info', 'Tenant notification sent successfully to ' . $ownerPhone);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to send tenant donation notification: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Send notification to admin platform when new donation is created
     *
     * @param int $donationId
     * @param array $donationData
     * @return void
     */
    protected function sendAdminDonationNotification(int $donationId, array $donationData): void
    {
        try {
            log_message('debug', 'sendAdminDonationNotification called for donation ID: ' . $donationId);
            
            $notificationService = BaseServices::notification();
            $templateService = \Modules\Notification\Config\Services::messageTemplate();
            
            if (!$notificationService || !$templateService) {
                log_message('debug', 'Notification or template service is null, skipping admin notification');
                return;
            }
            
            // Get admin phone from settings (optional - only send if configured)
            $settingService = BaseServices::setting();
            $adminPhone = $settingService->get('admin_notification_phone', null, 'global', null);
            
            if (empty($adminPhone)) {
                log_message('debug', 'Admin notification phone not configured, skipping admin notification');
                return;
            }
            
            // Get tenant info
            $tenant = $this->tenantModel->find($donationData['tenant_id']);
            $tenantName = $tenant['name'] ?? 'Unknown Tenant';
            
            // Get campaign title
            $campaignTitle = '';
            if (!empty($donationData['campaign_id'])) {
                $campaign = $this->campaignModel->find($donationData['campaign_id']);
                $campaignTitle = $campaign['title'] ?? '';
            }
            
            // Default template for admin
            $defaultTemplate = 'Notifikasi Admin: Ada donasi baru sebesar Rp {amount} dari {donor_name} untuk urunan \'{campaign_title}\' dari tenant {tenant_name}. Donation ID: {donation_id}';
            
            // Render template
            $message = $templateService->render('admin_donation_new', array_merge([
                'amount' => $donationData['amount'],
                'donor_name' => $donationData['donor_name'] ?? 'Anonim',
                'campaign_title' => $campaignTitle,
                'donation_id' => $donationId,
                'tenant_name' => $tenantName,
            ], $this->getPaymentPlaceholderData($donationData)), $defaultTemplate, null);
            
            if (empty($message)) {
                log_message('debug', 'Admin template is disabled or empty, skipping admin notification');
                return;
            }
            
            log_message('debug', 'Sending WhatsApp notification to admin: ' . $adminPhone);
            
            $result = $notificationService->sendWhatsApp(
                $adminPhone,
                $message,
                [
                    'type' => 'admin_donation_new',
                    'donation_id' => $donationId,
                    'tenant_id' => $donationData['tenant_id'],
                ]
            );
            
            if (!$result['success']) {
                log_message('error', 'Failed to send admin notification: ' . ($result['message'] ?? 'Unknown error'));
            } else {
                log_message('info', 'Admin notification sent successfully to ' . $adminPhone);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to send admin donation notification: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Send thank you notification
     *
     * @param array $donation
     * @return void
     */
    protected function sendThankYouNotification(array $donation): void
    {
        try {
            $notificationService = BaseServices::notification();
            $templateService = \Modules\Notification\Config\Services::messageTemplate();
            
            if ($notificationService && $donation['donor_phone']) {
                $campaign = $this->campaignModel->find($donation['campaign_id']);
                
                // Render template (with tenant context)
                $paymentPlaceholders = $this->getPaymentPlaceholderData($donation);
                $message = $templateService->render('donation_paid', array_merge([
                    'amount' => $donation['amount'],
                    'donor_name' => $donation['donor_name'] ?? '',
                    'campaign_title' => $campaign['title'] ?? '',
                ], $paymentPlaceholders), null, $donation['tenant_id'] ?? null);
                
                $notificationService->sendWhatsApp(
                    $donation['donor_phone'],
                    $message,
                    [
                        'type' => 'donation_paid',
                        'donation_id' => $donation['id'],
                    ]
                );
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send thank you notification: ' . $e->getMessage());
        }
    }

    /**
     * Build payment placeholders for WhatsApp templates
     */
    protected function getPaymentPlaceholderData(array $donationData): array
    {
        $placeholders = [
            'bank' => '',
            'rekening' => '',
            'deskripsi_pembayaran' => '',
        ];

        $tenantId = $donationData['tenant_id'] ?? null;
        if (!$tenantId) {
            return $placeholders;
        }

        $bankAccountId = isset($donationData['bank_account_id']) ? (int) $donationData['bank_account_id'] : null;
        $tenant = $this->tenantModel->findWithBankAccounts((int) $tenantId);
        if ($tenant && !empty($tenant['bank_accounts'])) {
            $selectedAccount = null;
            $bankAccounts = $tenant['bank_accounts'];

            if ($bankAccountId !== null) {
                foreach ($bankAccounts as $index => $account) {
                    $accountId = isset($account['id']) ? (int) $account['id'] : $index;
                    if ($accountId === $bankAccountId) {
                        $selectedAccount = $account;
                        break;
                    }
                }
            }

            if (!$selectedAccount) {
                $selectedAccount = $bankAccounts[array_key_first($bankAccounts)];
            }

            if ($selectedAccount) {
                $placeholders['bank'] = $selectedAccount['bank'] ?? '';
                $placeholders['rekening'] = $selectedAccount['account_number'] ?? '';
                $placeholders['deskripsi_pembayaran'] = $selectedAccount['description']
                    ?? $selectedAccount['notes']
                    ?? $selectedAccount['instructions']
                    ?? '';
            }
        }

        if (!empty($donationData['payment_method'])) {
            $paymentMethod = $this->paymentMethodModel->getByCode(
                (string) $donationData['payment_method'],
                (int) $tenantId
            );

            if ($paymentMethod) {
                if (empty($placeholders['bank'])) {
                    $placeholders['bank'] = $paymentMethod['name'] ?? '';
                }
                if (empty($placeholders['deskripsi_pembayaran'])) {
                    $placeholders['deskripsi_pembayaran'] = $paymentMethod['description'] ?? '';
                }
            }
        }

        return $placeholders;
    }

    /**
     * Get all donations from all tenants (admin) with pagination
     * Simplified for single database architecture
     *
     * @param array $filters
     * @return array ['data' => [], 'total' => int, 'page' => int, 'per_page' => int]
     */
    public function getAllDonations(array $filters = []): array
    {
        // Simplified: Query directly from single database
        $builder = $this->donationModel->builder();
        $builder->select('donations.*, campaigns.title as campaign_title')
            ->join('campaigns', 'campaigns.id = donations.campaign_id', 'left');

        // Apply filters
        if (isset($filters['tenant_id'])) {
            $builder->where('donations.tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['status'])) {
            $builder->where('donations.payment_status', $filters['status']);
        }

        if (isset($filters['campaign_id'])) {
            $builder->where('donations.campaign_id', $filters['campaign_id']);
        }

        // Get total count before pagination
        $total = $builder->countAllResults(false);

        // Apply pagination
        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $builder->orderBy('donations.created_at', 'DESC')
            ->limit($perPage, $offset);

        $donations = $builder->get()->getResultArray();

        // Get tenant info for enrichment
        $tenantModel = new TenantModel();
        $tenants = $tenantModel->findAll();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }

        // Enrich donations with tenant info
        foreach ($donations as &$donation) {
            $tenantId = (int) $donation['tenant_id'];
            if (isset($tenantMap[$tenantId])) {
                $donation['tenant_name'] = $tenantMap[$tenantId]['name'];
                $donation['tenant_slug'] = $tenantMap[$tenantId]['slug'];
            } else {
                $donation['tenant_name'] = 'Unknown';
                $donation['tenant_slug'] = 'unknown';
            }
            
            // Format amount
            $donation['amount_formatted'] = 'Rp ' . number_format((float) $donation['amount'], 0, ',', '.');
            
            // Format dates
            if ($donation['created_at']) {
                $donation['created_at_formatted'] = date('d M Y H:i', strtotime($donation['created_at']));
            }
            if ($donation['paid_at']) {
                $donation['paid_at_formatted'] = date('d M Y H:i', strtotime($donation['paid_at']));
            }
        }

        return [
            'data' => $donations,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get donation by ID (for admin)
     *
     * @param int $id
     * @return array|null
     */
    public function getDonationById(int $id): ?array
    {
        return $this->donationModel->find($id);
    }

    /**
     * Cancel donation (mark as cancelled)
     *
     * @param int $id
     * @return bool
     */
    public function cancel(int $id): bool
    {
        $donation = $this->donationModel->find($id);
        if (!$donation) {
            return false;
        }

        if ($donation['payment_status'] !== 'pending') {
            throw new \RuntimeException('Hanya donasi dengan status pending yang dapat dibatalkan');
        }

        $updateData = [
            'payment_status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->donationModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Donation', $id, $donation, $updateData, 'Donasi telah dibatalkan');
        }

        return $result;
    }
}

