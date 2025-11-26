<?php

namespace Modules\Report\Services;

use Modules\Report\Models\ReportModel;
use Modules\Campaign\Models\CampaignModel;
use Modules\Donation\Models\DonationModel;
use Modules\Withdrawal\Models\WithdrawalModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class ReportService
{
    protected ReportModel $reportModel;
    protected CampaignModel $campaignModel;
    protected DonationModel $donationModel;
    protected WithdrawalModel $withdrawalModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->withdrawalModel = new WithdrawalModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Generate financial transparency report
     *
     * @param int|null $tenantId
     * @param int|null $campaignId
     * @param string|null $periodStart
     * @param string|null $periodEnd
     * @return array
     */
    public function generateFinancialReport(?int $tenantId = null, ?int $campaignId = null, ?string $periodStart = null, ?string $periodEnd = null): array
    {
        $builder = $this->donationModel->builder();
        $builder->select('SUM(amount) as total_donations, COUNT(*) as total_donations_count');
        $builder->where('payment_status', 'paid');

        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        if ($campaignId) {
            $builder->where('campaign_id', $campaignId);
        }

        if ($periodStart) {
            $builder->where('paid_at >=', $periodStart);
        }

        if ($periodEnd) {
            $builder->where('paid_at <=', $periodEnd);
        }

        $donations = $builder->get()->getRowArray();

        // Get withdrawals
        $withdrawalBuilder = $this->withdrawalModel->builder();
        $withdrawalBuilder->select('SUM(amount) as total_withdrawals, COUNT(*) as total_withdrawals_count');
        $withdrawalBuilder->whereIn('status', ['approved', 'processing', 'completed']);

        if ($tenantId) {
            $withdrawalBuilder->where('tenant_id', $tenantId);
        }

        if ($campaignId) {
            $withdrawalBuilder->where('campaign_id', $campaignId);
        }

        if ($periodStart) {
            $withdrawalBuilder->where('created_at >=', $periodStart);
        }

        if ($periodEnd) {
            $withdrawalBuilder->where('created_at <=', $periodEnd);
        }

        $withdrawals = $withdrawalBuilder->get()->getRowArray();

        // Get campaigns count
        $campaignBuilder = $this->campaignModel->builder();
        $campaignBuilder->select('COUNT(*) as total_campaigns');
        $campaignBuilder->where('status', 'active');

        if ($tenantId) {
            $campaignBuilder->where('tenant_id', $tenantId);
        }

        $campaigns = $campaignBuilder->get()->getRowArray();

        // Get unique donors
        $donorBuilder = $this->donationModel->builder();
        $donorBuilder->select('COUNT(DISTINCT donor_id) as unique_donors');
        $donorBuilder->where('payment_status', 'paid');
        
        if ($tenantId) {
            $donorBuilder->where('tenant_id', $tenantId);
        }

        if ($periodStart) {
            $donorBuilder->where('paid_at >=', $periodStart);
        }

        if ($periodEnd) {
            $donorBuilder->where('paid_at <=', $periodEnd);
        }

        $donors = $donorBuilder->get()->getRowArray();

        return [
            'total_donations' => (float) ($donations['total_donations'] ?? 0),
            'total_donations_count' => (int) ($donations['total_donations_count'] ?? 0),
            'total_withdrawals' => (float) ($withdrawals['total_withdrawals'] ?? 0),
            'total_withdrawals_count' => (int) ($withdrawals['total_withdrawals_count'] ?? 0),
            'total_campaigns' => (int) ($campaigns['total_campaigns'] ?? 0),
            'total_donors' => (int) ($donors['unique_donors'] ?? 0),
            'balance' => (float) ($donations['total_donations'] ?? 0) - (float) ($withdrawals['total_withdrawals'] ?? 0),
        ];
    }

    /**
     * Generate campaign summary report (Public untuk transparansi)
     *
     * @param int $campaignId
     * @param bool $includeDonorDetails Include detail donatur (untuk admin/tenant only)
     * @return array
     */
    public function generateCampaignReport(int $campaignId, bool $includeDonorDetails = false): array
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (!$campaign) {
            throw new \RuntimeException('Campaign not found');
        }

        // Enrich campaign data
        $campaign = $this->campaignModel->enrichCampaign($campaign);

        $donationStats = $this->donationModel->getCampaignStats($campaignId);
        
        // Get withdrawals (public dapat lihat summary)
        $withdrawals = $this->withdrawalModel->getByCampaign($campaignId);
        $totalWithdrawn = 0;
        $withdrawalsSummary = [];
        
        foreach ($withdrawals as $w) {
            if (in_array($w['status'], ['approved', 'processing', 'completed'])) {
                $totalWithdrawn += (float) $w['amount'];
                // Public hanya lihat summary, bukan detail lengkap
                $withdrawalsSummary[] = [
                    'id' => $w['id'],
                    'amount' => (float) $w['amount'],
                    'status' => $w['status'],
                    'completed_at' => $w['completed_at'],
                    // Tidak include beneficiary details untuk public (privacy)
                ];
            }
        }

        // Get recent donations (public dapat lihat summary, bukan detail lengkap)
        $recentDonations = [];
        if ($includeDonorDetails) {
            $donations = $this->donationModel->getByCampaign($campaignId, ['limit' => 10]);
            foreach ($donations as $donation) {
                $recentDonations[] = [
                    'amount' => (float) $donation['amount'],
                    'donor_name' => $donation['is_anonymous'] ? 'Orang Baik Tanpa Nama' : $donation['donor_name'],
                    'paid_at' => $donation['paid_at'],
                    'message' => $donation['message'],
                ];
            }
        } else {
            // Public hanya lihat summary tanpa detail donatur
            $donationsQuery = $this->donationModel->builder();
            $donationsQuery->select('amount, paid_at, is_anonymous, donor_name');
            $donationsQuery->where('campaign_id', $campaignId);
            $donationsQuery->where('payment_status', 'paid');
            $donationsQuery->orderBy('paid_at', 'DESC');
            $donationsQuery->limit(10);
            $donations = $donationsQuery->get()->getResultArray();
            
            foreach ($donations as $donation) {
                $recentDonations[] = [
                    'amount' => (float) $donation['amount'],
                    'donor_name' => $donation['is_anonymous'] ? 'Orang Baik Tanpa Nama' : ($donation['donor_name'] ?? 'Orang Baik'),
                    'paid_at' => $donation['paid_at'],
                    'is_anonymous' => (bool) $donation['is_anonymous'],
                ];
            }
        }

        return [
            'campaign' => [
                'id' => $campaign['id'],
                'title' => $campaign['title'],
                'slug' => $campaign['slug'],
                'campaign_type' => $campaign['campaign_type'],
                'target_amount' => $campaign['target_amount'],
                'current_amount' => $campaign['current_amount'],
                'progress_percentage' => $campaign['progress_percentage'],
                'remaining_amount' => $campaign['remaining_amount'],
                'status' => $campaign['status'],
                'donors_count' => $campaign['donors_count'],
                'views_count' => $campaign['views_count'],
                'created_at' => $campaign['created_at'],
            ],
            'summary' => [
                'total_donations' => (float) ($donationStats['total_amount'] ?? 0),
                'total_donations_count' => (int) ($donationStats['total_donations'] ?? 0),
                'unique_donors' => (int) ($donationStats['unique_donors'] ?? 0),
                'total_withdrawals' => $totalWithdrawn,
                'withdrawals_count' => count($withdrawalsSummary),
                'balance' => (float) ($donationStats['total_amount'] ?? 0) - $totalWithdrawn,
            ],
            'withdrawals' => $withdrawalsSummary,
            'recent_donations' => $recentDonations,
            'transparency' => [
                'last_updated' => date('Y-m-d H:i:s'),
                'report_type' => 'campaign_transparency',
            ],
        ];
    }

    /**
     * Save report
     *
     * @param array $data
     * @return int|false
     */
    public function saveReport(array $data)
    {
        $reportData = [
            'tenant_id' => $data['tenant_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'report_type' => $data['report_type'],
            'title' => $data['title'],
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'summary' => $data['summary'] ?? null,
            'data' => isset($data['data']) ? json_encode($data['data']) : null,
            'total_donations' => $data['total_donations'] ?? 0,
            'total_withdrawals' => $data['total_withdrawals'] ?? 0,
            'total_campaigns' => $data['total_campaigns'] ?? 0,
            'total_donors' => $data['total_donors'] ?? 0,
            'is_public' => $data['is_public'] ?? 0,
            'published_at' => $data['is_public'] ? date('Y-m-d H:i:s') : null,
            'created_by' => auth_user()['id'] ?? null,
        ];

        $id = $this->reportModel->insert($reportData);

        if ($id) {
            $this->activityLog->logCreate('Report', $id, $reportData, 'Laporan transparansi dibuat');
        }

        return $id ?: false;
    }

    /**
     * Get public reports
     *
     * @param array $filters
     * @return array
     */
    public function getPublicReports(array $filters = []): array
    {
        return $this->reportModel->getPublicReports($filters);
    }

    /**
     * Get reports by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getByTenant(int $tenantId, array $filters = []): array
    {
        return $this->reportModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get detailed campaign financial report (Masuk & Penggunaan Dana)
     *
     * @param int $campaignId
     * @return array
     */
    public function getCampaignFinancialDetail(int $campaignId): array
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (!$campaign) {
            throw new \RuntimeException('Campaign not found');
        }

        // Get all donations (masuk dana)
        $donations = $this->donationModel
            ->where('campaign_id', $campaignId)
            ->where('payment_status', 'paid')
            ->orderBy('paid_at', 'DESC')
            ->findAll();

        // Format donations
        $donationsList = [];
        foreach ($donations as $donation) {
            $donationsList[] = [
                'id' => $donation['id'],
                'amount' => (float) $donation['amount'],
                'donor_name' => $donation['is_anonymous'] ? 'Orang Baik Tanpa Nama' : ($donation['donor_name'] ?? 'Orang Baik'),
                'donor_email' => $donation['is_anonymous'] ? null : ($donation['donor_email'] ?? null),
                'message' => $donation['message'] ?? null,
                'payment_method' => $donation['payment_method'] ?? null,
                'paid_at' => $donation['paid_at'] ?? $donation['created_at'],
                'is_anonymous' => (bool) ($donation['is_anonymous'] ?? false),
            ];
        }

        // Get all campaign updates (laporan transparansi) untuk menghitung penggunaan dana
        $campaignUpdateModel = new \Modules\CampaignUpdate\Models\CampaignUpdateModel();
        $updates = $campaignUpdateModel
            ->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Format updates dan hitung total penggunaan dana
        $updatesList = [];
        $totalAmountUsed = 0;
        
        foreach ($updates as $update) {
            $amountUsed = !empty($update['amount_used']) && $update['amount_used'] !== null && $update['amount_used'] !== '' 
                ? (float) $update['amount_used'] 
                : 0;
            
            $totalAmountUsed += $amountUsed;
            
            $updatesList[] = [
                'id' => $update['id'],
                'title' => $update['title'] ?? null,
                'content' => $update['content'] ?? null,
                'amount_used' => $amountUsed,
                'created_at' => $update['created_at'],
                'images' => $update['images'] ?? null,
                'youtube_url' => $update['youtube_url'] ?? null,
            ];
        }

        // Calculate totals
        $totalDonations = array_sum(array_column($donationsList, 'amount'));
        $totalWithdrawn = $totalAmountUsed; // Total penggunaan dana dari laporan transparansi
        $balance = $totalDonations - $totalWithdrawn;

        return [
            'campaign' => [
                'id' => $campaign['id'],
                'title' => $campaign['title'],
                'current_amount' => (float) ($campaign['current_amount'] ?? 0),
                'target_amount' => (float) ($campaign['target_amount'] ?? 0),
            ],
            'summary' => [
                'total_donations' => $totalDonations,
                'donations_count' => count($donationsList),
                'total_withdrawn' => $totalWithdrawn,
                'total_amount_used' => $totalAmountUsed, // Total penggunaan dana dari laporan
                'updates_count' => count($updatesList),
                'balance' => $balance,
                'balance_percentage' => $totalDonations > 0 ? ($totalWithdrawn / $totalDonations) * 100 : 0,
            ],
            'donations' => $donationsList,
            'updates' => $updatesList, // Laporan transparansi dengan penggunaan dana
        ];
    }

}
