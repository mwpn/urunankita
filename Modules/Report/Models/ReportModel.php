<?php

namespace Modules\Report\Models;

use Modules\Core\Models\BaseModel;

class ReportModel extends BaseModel
{
    protected $table = 'reports';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'tenant_id',
        'campaign_id',
        'report_type',
        'title',
        'period_start',
        'period_end',
        'summary',
        'data',
        'total_donations',
        'total_withdrawals',
        'total_campaigns',
        'total_donors',
        'is_public',
        'published_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'report_type' => 'required|max_length[50]',
        'title' => 'required|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Get public reports
     *
     * @param array $filters
     * @return array
     */
    public function getPublicReports(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('is_public', 1);
        $builder->where('published_at IS NOT NULL');

        if (isset($filters['tenant_id'])) {
            $builder->where('tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['report_type'])) {
            $builder->where('report_type', $filters['report_type']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('published_at', 'DESC');

        $reports = $builder->get()->getResultArray();
        
        foreach ($reports as &$report) {
            $report = $this->enrichReport($report);
        }

        return $reports;
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
        $builder = $this->builder();
        $builder->where('tenant_id', $tenantId);

        if (isset($filters['report_type'])) {
            $builder->where('report_type', $filters['report_type']);
        }

        if (isset($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        $builder->orderBy('created_at', 'DESC');

        $reports = $builder->get()->getResultArray();
        
        foreach ($reports as &$report) {
            $report = $this->enrichReport($report);
        }

        return $reports;
    }

    /**
     * Enrich report data
     *
     * @param array $report
     * @return array
     */
    protected function enrichReport(array $report): array
    {
        // Parse data JSON
        if ($report['data']) {
            $report['data'] = json_decode($report['data'], true) ?? [];
        } else {
            $report['data'] = [];
        }

        return $report;
    }
}

