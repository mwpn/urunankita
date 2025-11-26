<?php

namespace Modules\Analytics\Services;

use Modules\Campaign\Models\CampaignModel;
use Modules\Donation\Models\DonationModel;
use Modules\Withdrawal\Models\WithdrawalModel;
use Modules\Beneficiary\Models\BeneficiaryModel;
use Config\Services as BaseServices;
use Config\Database;

class AnalyticsService
{
    protected CampaignModel $campaignModel;
    protected DonationModel $donationModel;
    protected WithdrawalModel $withdrawalModel;
    protected BeneficiaryModel $beneficiaryModel;

    public function __construct()
    {
        // Simplified: All models use default database
        // BaseModel will auto-filter by tenant_id from session
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->withdrawalModel = new WithdrawalModel();
        $this->beneficiaryModel = new BeneficiaryModel();
    }

    /**
     * Get dashboard stats for tenant
     *
     * @param int $tenantId
     * @return array
     */
    public function getTenantDashboardStats(int $tenantId): array
    {
        // Active campaigns
        $activeCampaigns = $this->campaignModel->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->countAllResults();

        // Completed campaigns
        $completedCampaigns = $this->campaignModel->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->countAllResults();

        // Total donations
        $totalDonations = $this->donationModel->builder()
            ->selectSum('amount')
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')
            ->get()
            ->getRowArray();

        // Total withdrawals
        $totalWithdrawals = $this->withdrawalModel->builder()
            ->selectSum('amount')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'processing', 'completed'])
            ->get()
            ->getRowArray();

        // Total donors (based on total paid donations)
        $totalDonors = $this->donationModel->builder()
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')
            ->countAllResults(false);

        // Recent donations (last 7 days)
        $recentDonations = $this->donationModel->builder()
            ->selectSum('amount')
            ->where('tenant_id', $tenantId)
            ->where('payment_status', 'paid')
            ->where('paid_at >=', date('Y-m-d', strtotime('-7 days')))
            ->get()
            ->getRowArray();

        return [
            'active_campaigns' => $activeCampaigns,
            'completed_campaigns' => $completedCampaigns,
            'total_donations' => (float) ($totalDonations['amount'] ?? 0),
            'total_withdrawals' => (float) ($totalWithdrawals['amount'] ?? 0),
            'total_donors' => (int) $totalDonors,
            'recent_donations' => (float) ($recentDonations['amount'] ?? 0),
            'balance' => (float) ($totalDonations['amount'] ?? 0) - (float) ($totalWithdrawals['amount'] ?? 0),
        ];
    }

    /**
     * Get platform-wide stats (admin)
     * Aggregate from all tenant databases
     *
     * @return array
     */
    public function getPlatformStats(): array
    {
        // Simplified: Query directly from single database
        $central = Database::connect();
        $date30DaysAgo = date('Y-m-d', strtotime('-30 days'));

        // Count active campaigns
        $totalCampaigns = $central->table('campaigns')
            ->where('status', 'active')
            ->countAllResults(false);

        // Sum donations
        $donationsSum = $central->table('donations')
            ->selectSum('amount')
            ->where('payment_status', 'paid')
            ->get()
            ->getRowArray();
        $totalDonations = (float) ($donationsSum['amount'] ?? 0);

        // Sum recent donations
        $recentSum = $central->table('donations')
            ->selectSum('amount')
            ->where('payment_status', 'paid')
            ->where('paid_at >=', $date30DaysAgo)
            ->get()
            ->getRowArray();
        $recentDonations = (float) ($recentSum['amount'] ?? 0);

        // Count beneficiaries
        $totalBeneficiaries = $central->table('beneficiaries')
            ->where('status', 'active')
            ->countAllResults(false);

        // Total tenants (from central database)
        $tenantsModel = new \Modules\Tenant\Models\TenantModel();
        $totalTenants = $tenantsModel->where('status', 'active')->countAllResults();

        return [
            'total_campaigns' => $totalCampaigns,
            'total_donations' => $totalDonations,
            'total_tenants' => $totalTenants,
            'total_beneficiaries' => $totalBeneficiaries,
            'recent_donations_30d' => $recentDonations,
        ];
    }

    /**
     * Get top campaigns
     *
     * @param int $limit
     * @param int|null $tenantId - If null, aggregate from all tenants
     * @return array
     */
    public function getTopCampaigns(int $limit = 10, ?int $tenantId = null): array
    {
        if ($tenantId) {
            // Query from specific tenant database
            $builder = $this->campaignModel->builder();
            $builder->select('campaigns.*, SUM(donations.amount) as total_raised');
            $builder->join('donations', 'donations.campaign_id = campaigns.id', 'left');
            $builder->where('donations.payment_status', 'paid');
            $builder->where('campaigns.status', 'active');
            $builder->where('campaigns.tenant_id', $tenantId);
            $builder->groupBy('campaigns.id');
            // Priority campaigns first, then by total_raised
            $builder->orderBy('campaigns.is_priority', 'DESC');
            $builder->orderBy('total_raised', 'DESC');
            $builder->limit($limit);

            return $builder->get()->getResultArray();
        }

        // Simplified: Query directly from single database
        $central = Database::connect();
        
        $campaigns = $central->query("
            SELECT 
                campaigns.*,
                COALESCE(SUM(CASE WHEN donations.payment_status = 'paid' THEN donations.amount ELSE 0 END), 0) as total_raised
            FROM campaigns
            LEFT JOIN donations ON donations.campaign_id = campaigns.id
            WHERE campaigns.status = 'active'
            GROUP BY campaigns.id
            ORDER BY campaigns.is_priority DESC, total_raised DESC
            LIMIT " . intval($limit) . "
        ")->getResultArray();

        // Get tenant info for enrichment
        $tenants = $central->table('tenants')->get()->getResultArray();
        $tenantMap = [];
        foreach ($tenants as $tenant) {
            $tenantMap[$tenant['id']] = $tenant;
        }

        // Add tenant info
        foreach ($campaigns as &$campaign) {
            $tenantId = (int) $campaign['tenant_id'];
            if (isset($tenantMap[$tenantId])) {
                $campaign['tenant_name'] = $tenantMap[$tenantId]['name'];
                $campaign['tenant_slug'] = $tenantMap[$tenantId]['slug'];
            } else {
                $campaign['tenant_name'] = 'Unknown';
                $campaign['tenant_slug'] = 'unknown';
            }
        }

        return $campaigns;
    }

    /**
     * Get donation trends (by period)
     *
     * @param string $period (daily, weekly, monthly)
     * @param int|null $tenantId
     * @param int $limit
     * @return array
     */
    public function getDonationTrends(string $period = 'daily', ?int $tenantId = null, int $limit = 30): array
    {
        $builder = $this->donationModel->builder();
        
        switch ($period) {
            case 'daily':
                $builder->select("DATE(paid_at) as period, SUM(amount) as total, COUNT(*) as count");
                $builder->groupBy('DATE(paid_at)');
                break;
            case 'weekly':
                $builder->select("YEARWEEK(paid_at) as period, SUM(amount) as total, COUNT(*) as count");
                $builder->groupBy('YEARWEEK(paid_at)');
                break;
            case 'monthly':
                $builder->select("DATE_FORMAT(paid_at, '%Y-%m') as period, SUM(amount) as total, COUNT(*) as count");
                $builder->groupBy('DATE_FORMAT(paid_at, "%Y-%m")');
                break;
        }

        $builder->where('payment_status', 'paid');
        $builder->where('paid_at IS NOT NULL');

        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        $builder->orderBy('period', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    /**
     * Get campaign performance
     *
     * @param int $campaignId
     * @return array
     */
    public function getCampaignPerformance(int $campaignId): array
    {
        $campaign = $this->campaignModel->find($campaignId);
        if (!$campaign) {
            return [];
        }

        $stats = $this->donationModel->getCampaignStats($campaignId);
        
        // Calculate conversion rate (views to donations)
        $conversionRate = $campaign['views_count'] > 0 
            ? round(($campaign['donors_count'] / $campaign['views_count']) * 100, 2)
            : 0;

        // Average donation amount
        $avgDonation = $stats['total_donations'] > 0 && $stats['total_donations_count'] > 0
            ? round($stats['total_amount'] / $stats['total_donations'], 2)
            : 0;

        return [
            'campaign' => $campaign,
            'total_raised' => (float) ($stats['total_amount'] ?? 0),
            'total_donations' => (int) ($stats['total_donations'] ?? 0),
            'unique_donors' => (int) ($stats['unique_donors'] ?? 0),
            'views' => (int) ($campaign['views_count'] ?? 0),
            'conversion_rate' => $conversionRate,
            'average_donation' => $avgDonation,
            'progress_percentage' => $campaign['progress_percentage'] ?? null,
        ];
    }
}

