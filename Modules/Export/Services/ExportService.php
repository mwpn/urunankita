<?php

namespace Modules\Export\Services;

use Modules\Campaign\Models\CampaignModel;
use Modules\Donation\Models\DonationModel;
use Modules\Withdrawal\Models\WithdrawalModel;

class ExportService
{
    protected CampaignModel $campaignModel;
    protected DonationModel $donationModel;
    protected WithdrawalModel $withdrawalModel;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->withdrawalModel = new WithdrawalModel();
    }

    /**
     * Export donations to array (ready for Excel/CSV)
     *
     * @param array $filters
     * @return array
     */
    public function exportDonations(array $filters = []): array
    {
        $builder = $this->donationModel->builder();
        $builder->select('donations.*, campaigns.title as campaign_title, campaigns.slug as campaign_slug');
        $builder->join('campaigns', 'campaigns.id = donations.campaign_id', 'left');
        $builder->where('donations.payment_status', 'paid');

        if (isset($filters['tenant_id'])) {
            $builder->where('donations.tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['campaign_id'])) {
            $builder->where('donations.campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('donations.paid_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('donations.paid_at <=', $filters['date_to']);
        }

        $builder->orderBy('donations.paid_at', 'DESC');

        $donations = $builder->get()->getResultArray();

        // Format for export
        $exportData = [];
        $exportData[] = [
            'No',
            'Ticket Number',
            'Tanggal',
            'Campaign',
            'Nama Donatur',
            'Email',
            'Nomor HP',
            'Jumlah Donasi',
            'Metode Pembayaran',
            'Status',
            'Pesan',
        ];

        $no = 1;
        foreach ($donations as $donation) {
            $exportData[] = [
                $no++,
                $donation['id'],
                $donation['paid_at'] ? date('d/m/Y H:i', strtotime($donation['paid_at'])) : '',
                $donation['campaign_title'] ?? '',
                $donation['is_anonymous'] ? 'Orang Baik Tanpa Nama' : ($donation['donor_name'] ?? ''),
                $donation['is_anonymous'] ? '' : ($donation['donor_email'] ?? ''),
                $donation['is_anonymous'] ? '' : ($donation['donor_phone'] ?? ''),
                number_format($donation['amount'], 0, ',', '.'),
                $donation['payment_method'] ?? '',
                $donation['payment_status'],
                $donation['message'] ?? '',
            ];
        }

        return $exportData;
    }

    /**
     * Export campaigns to array (ready for Excel/CSV)
     *
     * @param array $filters
     * @return array
     */
    public function exportCampaigns(array $filters = []): array
    {
        $builder = $this->campaignModel->builder();

        if (isset($filters['tenant_id'])) {
            $builder->where('tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        $builder->orderBy('created_at', 'DESC');

        $campaigns = $builder->get()->getResultArray();

        // Format for export
        $exportData = [];
        $exportData[] = [
            'No',
            'Judul',
            'Slug',
            'Kategori',
            'Tipe',
            'Target',
            'Terkumpul',
            'Sisa',
            'Progress %',
            'Status',
            'Jumlah Donatur',
            'Views',
            'Tanggal Dibuat',
        ];

        $no = 1;
        foreach ($campaigns as $campaign) {
            $target = (float) ($campaign['target_amount'] ?? 0);
            $current = (float) ($campaign['current_amount'] ?? 0);
            $remaining = max(0, $target - $current);
            $progress = $target > 0 ? round(($current / $target) * 100, 2) : 0;

            $exportData[] = [
                $no++,
                $campaign['title'],
                $campaign['slug'],
                $campaign['category'] ?? '',
                $campaign['campaign_type'] ?? 'target_based',
                number_format($target, 0, ',', '.'),
                number_format($current, 0, ',', '.'),
                number_format($remaining, 0, ',', '.'),
                $progress . '%',
                $campaign['status'],
                $campaign['donors_count'] ?? 0,
                $campaign['views_count'] ?? 0,
                $campaign['created_at'] ? date('d/m/Y H:i', strtotime($campaign['created_at'])) : '',
            ];
        }

        return $exportData;
    }

    /**
     * Export withdrawals to array (ready for Excel/CSV)
     *
     * @param array $filters
     * @return array
     */
    public function exportWithdrawals(array $filters = []): array
    {
        $builder = $this->withdrawalModel->builder();
        $builder->select('withdrawals.*, campaigns.title as campaign_title, beneficiaries.name as beneficiary_name');
        $builder->join('campaigns', 'campaigns.id = withdrawals.campaign_id', 'left');
        $builder->join('beneficiaries', 'beneficiaries.id = withdrawals.beneficiary_id', 'left');

        if (isset($filters['tenant_id'])) {
            $builder->where('withdrawals.tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['campaign_id'])) {
            $builder->where('withdrawals.campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['status'])) {
            $builder->where('withdrawals.status', $filters['status']);
        }

        $builder->orderBy('withdrawals.created_at', 'DESC');

        $withdrawals = $builder->get()->getResultArray();

        // Format for export
        $exportData = [];
        $exportData[] = [
            'No',
            'ID',
            'Tanggal Request',
            'Campaign',
            'Penerima',
            'Jumlah',
            'Status',
            'Tanggal Approve',
            'Tanggal Complete',
            'Catatan',
        ];

        $no = 1;
        foreach ($withdrawals as $withdrawal) {
            $exportData[] = [
                $no++,
                $withdrawal['id'],
                $withdrawal['requested_at'] ? date('d/m/Y H:i', strtotime($withdrawal['requested_at'])) : '',
                $withdrawal['campaign_title'] ?? '',
                $withdrawal['beneficiary_name'] ?? '',
                number_format($withdrawal['amount'], 0, ',', '.'),
                $withdrawal['status'],
                $withdrawal['approved_at'] ? date('d/m/Y H:i', strtotime($withdrawal['approved_at'])) : '',
                $withdrawal['completed_at'] ? date('d/m/Y H:i', strtotime($withdrawal['completed_at'])) : '',
                $withdrawal['notes'] ?? '',
            ];
        }

        return $exportData;
    }

    /**
     * Generate CSV content from array
     *
     * @param array $data
     * @param string $filename
     * @return string
     */
    public function generateCSV(array $data, string $filename = 'export.csv'): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Format data for PDF (simplified structure)
     *
     * @param array $data
     * @param string $title
     * @return array
     */
    public function formatForPDF(array $data, string $title = 'Laporan'): array
    {
        return [
            'title' => $title,
            'generated_at' => date('d F Y H:i:s'),
            'data' => $data,
        ];
    }
}

