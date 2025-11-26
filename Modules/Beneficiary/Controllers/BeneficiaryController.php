<?php

namespace Modules\Beneficiary\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Beneficiary\Services\BeneficiaryService;

class BeneficiaryController extends BaseController
{
    protected BeneficiaryService $beneficiaryService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->beneficiaryService = new BeneficiaryService();
    }

    /**
     * Get beneficiaries (Penerima Urunan)
     * GET /beneficiary/list
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
            'type' => $this->request->getGet('type'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        $filters = array_filter($filters, fn($value) => $value !== null);

        $beneficiaries = $this->beneficiaryService->getByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $beneficiaries,
        ]);
    }

    /**
     * Get beneficiary by ID
     * GET /beneficiary/show/{id}
     */
    public function show($id)
    {
        $beneficiary = $this->beneficiaryService->getById((int) $id);

        if (!$beneficiary) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penerima Urunan tidak ditemukan',
            ])->setStatusCode(404);
        }

        // Security check
        $tenantId = session()->get('tenant_id');
        if ($beneficiary['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied',
            ])->setStatusCode(403);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $beneficiary,
        ]);
    }

    /**
     * Create beneficiary
     * POST /beneficiary/create
     */
    public function create()
    {
        $data = [
            'type' => $this->request->getPost('type') ?? 'individual',
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'identity_number' => $this->request->getPost('identity_number'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'bank_name' => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_account_name' => $this->request->getPost('bank_account_name'),
            'photo' => $this->request->getPost('photo'),
        ];

        if (empty($data['name'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama penerima wajib diisi',
            ])->setStatusCode(400);
        }

        try {
            $id = $this->beneficiaryService->create($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penerima Urunan berhasil dibuat',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat Penerima Urunan',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Update beneficiary
     * POST /beneficiary/update/{id}
     */
    public function update($id)
    {
        $data = [];

        $fields = ['name', 'description', 'identity_number', 'address', 'phone', 'email',
                  'bank_name', 'bank_account', 'bank_account_name', 'photo', 'type', 'status'];

        foreach ($fields as $field) {
            if ($this->request->getPost($field) !== null) {
                $data[$field] = $this->request->getPost($field);
            }
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada data untuk diperbarui',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->beneficiaryService->update((int) $id, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penerima Urunan berhasil diperbarui',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penerima Urunan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Delete beneficiary
     * DELETE /beneficiary/delete/{id}
     */
    public function delete($id)
    {
        try {
            $result = $this->beneficiaryService->delete((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Penerima Urunan berhasil dinonaktifkan',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Penerima Urunan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}

