<?php

namespace Modules\Billing\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Billing\Services\BillingService;

class BillingController extends BaseController
{
    protected BillingService $billingService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->billingService = new BillingService();
    }

    /**
     * Get invoices for current tenant (API)
     * GET /billing/invoices
     */
    public function getInvoices()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'status' => $this->request->getGet('status'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $invoices = $this->billingService->getInvoices($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get invoice by ID
     * GET /billing/invoice/{id}
     */
    public function invoice($id)
    {
        $invoice = $this->billingService->getInvoice((int) $id);

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice not found',
            ])->setStatusCode(404);
        }

        // Security: Check tenant ownership
        $tenantId = session()->get('tenant_id');
        if ($invoice['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
            ])->setStatusCode(403);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Mark invoice as paid
     * POST /billing/invoice/{id}/pay
     */
    public function pay($id)
    {
        $invoice = $this->billingService->getInvoice((int) $id);
        
        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice not found',
            ])->setStatusCode(404);
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($invoice['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
            ])->setStatusCode(403);
        }

        $paymentMethod = $this->request->getPost('payment_method');

        try {
            $result = $this->billingService->markAsPaid((int) $id, $paymentMethod);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Invoice marked as paid',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update invoice',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get revenue statistics (superadmin only)
     * GET /billing/revenue
     */
    public function revenue()
    {
        $filters = [
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $stats = $this->billingService->getRevenueStats($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Admin: Billing overview
     * GET /admin/billing
     */
    public function index()
    {
        $stats = $this->billingService->getRevenueStats();

        $data = [
            'title' => 'Billing - Admin Dashboard',
            'page_title' => 'Billing',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'stats' => $stats,
        ];

        return view('Modules\\Billing\\Views\\admin_index', $data);
    }

    /**
     * Admin: List all invoices
     * GET /admin/invoices
     */
    public function invoices()
    {
        $status = $this->request->getGet('status');
        
        $invoiceModel = new \Modules\Billing\Models\InvoiceModel();
        $builder = $invoiceModel->builder();
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        $invoices = $builder->orderBy('created_at', 'DESC')->limit(100)->get()->getResultArray();

        // Enrich with tenant info
        $tenantModel = new \Modules\Tenant\Models\TenantModel();
        foreach ($invoices as &$invoice) {
            $tenant = $tenantModel->find($invoice['tenant_id']);
            $invoice['tenant_name'] = $tenant['name'] ?? '-';
        }

        $data = [
            'title' => 'Invoice - Admin Dashboard',
            'page_title' => 'Invoice',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'invoices' => $invoices,
            'status_filter' => $status,
        ];

        return view('Modules\\Billing\\Views\\admin_invoices', $data);
    }
}

