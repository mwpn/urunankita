<?php

namespace Modules\Export\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Export\Services\ExportService;

class ExportController extends BaseController
{
    protected ExportService $exportService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->exportService = new ExportService();
    }

    /**
     * Export donations to CSV
     * GET /export/donations
     */
    public function donations()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'tenant_id' => $tenantId,
            'campaign_id' => $this->request->getGet('campaign_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        try {
            $data = $this->exportService->exportDonations($filters);
            $csv = $this->exportService->generateCSV($data, 'donations_' . date('Ymd') . '.csv');

            $filename = 'donations_' . date('Ymd_His') . '.csv';
            
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody("\xEF\xBB\xBF" . $csv); // BOM untuk Excel UTF-8
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Export campaigns to CSV
     * GET /export/campaigns
     */
    public function campaigns()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'tenant_id' => $tenantId,
            'status' => $this->request->getGet('status'),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        try {
            $data = $this->exportService->exportCampaigns($filters);
            $csv = $this->exportService->generateCSV($data, 'campaigns_' . date('Ymd') . '.csv');

            $filename = 'campaigns_' . date('Ymd_His') . '.csv';
            
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody("\xEF\xBB\xBF" . $csv); // BOM untuk Excel UTF-8
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Export withdrawals to CSV
     * GET /export/withdrawals
     */
    public function withdrawals()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'tenant_id' => $tenantId,
            'campaign_id' => $this->request->getGet('campaign_id'),
            'status' => $this->request->getGet('status'),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        try {
            $data = $this->exportService->exportWithdrawals($filters);
            $csv = $this->exportService->generateCSV($data, 'withdrawals_' . date('Ymd') . '.csv');

            $filename = 'withdrawals_' . date('Ymd_His') . '.csv';
            
            return $this->response
                ->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody("\xEF\xBB\xBF" . $csv); // BOM untuk Excel UTF-8
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}

