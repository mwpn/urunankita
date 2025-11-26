<?php

namespace Modules\Withdrawal\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Withdrawal\Services\WithdrawalService;

class WithdrawalController extends BaseController
{
    protected WithdrawalService $withdrawalService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->withdrawalService = new WithdrawalService();
    }

    /**
     * Request withdrawal (Ajukan Penyaluran Dana)
     * POST /withdrawal/request
     */
    public function request()
    {
        $data = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'beneficiary_id' => $this->request->getPost('beneficiary_id'),
            'amount' => $this->request->getPost('amount'),
            'notes' => $this->request->getPost('notes'),
        ];

        if (empty($data['campaign_id']) || empty($data['beneficiary_id']) || empty($data['amount'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Urunan, Penerima, dan jumlah penyaluran wajib diisi',
            ])->setStatusCode(400);
        }

        try {
            $id = $this->withdrawalService->request($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Permintaan penyaluran dana berhasil diajukan',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengajukan penyaluran dana',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Get withdrawals by tenant
     * GET /withdrawal/list
     */
    public function list()
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
            'campaign_id' => $this->request->getGet('campaign_id'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $withdrawals = $this->withdrawalService->getByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $withdrawals,
        ]);
    }

    /**
     * Approve withdrawal (Tim UrunanKita)
     * POST /withdrawal/approve/{id}
     */
    public function approve($id)
    {
        try {
            $result = $this->withdrawalService->approve((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penyaluran dana disetujui',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penyaluran dana tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Reject withdrawal (Tim UrunanKita)
     * POST /withdrawal/reject/{id}
     */
    public function reject($id)
    {
        $reason = $this->request->getPost('reason');

        if (empty($reason)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Alasan penolakan wajib diisi',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->withdrawalService->reject((int) $id, $reason);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penyaluran dana ditolak',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penyaluran dana tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Complete withdrawal
     * POST /withdrawal/complete/{id}
     */
    public function complete($id)
    {
        $transferProof = $this->request->getPost('transfer_proof');

        try {
            $result = $this->withdrawalService->complete((int) $id, $transferProof);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penyaluran dana selesai',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penyaluran dana tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}

