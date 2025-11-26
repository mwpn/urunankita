<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\InvoiceModel;
use Modules\Plan\Models\PlanModel;
use Modules\ActivityLog\Services\ActivityLogService;
use Config\Services as BaseServices;

class BillingService
{
    protected InvoiceModel $invoiceModel;
    protected PlanModel $planModel;
    protected ActivityLogService $activityLog;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->planModel = new PlanModel();
        $this->activityLog = BaseServices::activityLog();
    }

    /**
     * Create invoice
     *
     * @param int $tenantId
     * @param int|null $planId
     * @param int $durationMonths
     * @param float|null $customAmount
     * @return int|false
     */
    public function createInvoice(int $tenantId, ?int $planId = null, int $durationMonths = 1, ?float $customAmount = null)
    {
        // Calculate amount
        if ($customAmount !== null) {
            $amount = $customAmount;
        } elseif ($planId) {
            // Get plan details
            $plan = $this->planModel->find($planId);
            if (!$plan) {
                throw new \RuntimeException('Plan not found');
            }
            $amount = (float) $plan['price'] * $durationMonths;
        } else {
            throw new \RuntimeException('Either plan_id or custom_amount must be provided');
        }

        $data = [
            'tenant_id' => $tenantId,
            'amount' => $amount,
            'status' => 'unpaid',
        ];

        $id = $this->invoiceModel->insert($data);

        if ($id) {
            $description = $planId ? "Invoice created for plan: {$plan['name']}" : "Invoice created";
            $this->activityLog->logCreate('Invoice', $id, $data, $description);
        }

        return $id ?: false;
    }

    /**
     * Get invoices by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function getInvoices(int $tenantId, array $filters = []): array
    {
        return $this->invoiceModel->getByTenant($tenantId, $filters);
    }

    /**
     * Get invoice by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getInvoice(int $id): ?array
    {
        return $this->invoiceModel->find($id);
    }

    /**
     * Mark invoice as paid
     *
     * @param int $id
     * @param string|null $paymentMethod
     * @return bool
     */
    public function markAsPaid(int $id, ?string $paymentMethod = null): bool
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return false;
        }

        $updateData = [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->invoiceModel->update($id, $updateData);

        if ($result) {
            $this->activityLog->logUpdate('Invoice', $id, $invoice, $updateData, 'Invoice marked as paid');
        }

        return $result;
    }

    /**
     * Get revenue statistics
     *
     * @param array $filters
     * @return array
     */
    public function getRevenueStats(array $filters = []): array
    {
        $builder = $this->invoiceModel->builder();
        $builder->select('SUM(amount) as total_revenue, COUNT(*) as total_invoices');
        $builder->where('status', 'paid');

        if (isset($filters['date_from'])) {
            $builder->where('paid_at >=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $builder->where('paid_at <=', $filters['date_to']);
        }

        $result = $builder->get()->getRowArray();

        return [
            'total_revenue' => (float) ($result['total_revenue'] ?? 0),
            'total_invoices' => (int) ($result['total_invoices'] ?? 0),
        ];
    }
}

