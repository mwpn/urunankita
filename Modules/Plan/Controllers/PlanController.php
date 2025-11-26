<?php

namespace Modules\Plan\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Plan\Services\PlanService;
use Config\Services;

class PlanController extends BaseController
{
    protected PlanService $planService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->planService = new PlanService();
    }

    /**
     * Get all plans
     * GET /plan/list
     */
    public function list()
    {
        $plans = $this->planService->getAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get plan by ID
     * GET /plan/show/{id}
     */
    public function show($id)
    {
        $plan = $this->planService->getById((int) $id);

        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Create plan (superadmin only)
     * POST /plan/create
     */
    public function create()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price') ?? 0,
            'description' => $this->request->getPost('description'),
            'features' => $this->request->getPost('features'),
        ];

        if (empty($data['name'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan name is required',
            ])->setStatusCode(400);
        }

        // Parse features if string
        if (is_string($data['features'])) {
            $data['features'] = json_decode($data['features'], true) ?? [];
        }

        try {
            $id = $this->planService->create($data);

            if ($id) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Plan created successfully',
                    'data' => ['id' => $id],
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create plan',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Update plan (superadmin only)
     * POST /plan/update/{id}
     */
    public function update($id)
    {
        $data = [];
        
        if ($this->request->getPost('name')) {
            $data['name'] = $this->request->getPost('name');
        }
        if ($this->request->getPost('price') !== null) {
            $data['price'] = $this->request->getPost('price');
        }
        if ($this->request->getPost('description') !== null) {
            $data['description'] = $this->request->getPost('description');
        }
        if ($this->request->getPost('features') !== null) {
            $features = $this->request->getPost('features');
            $data['features'] = is_string($features) 
                ? json_decode($features, true) ?? [] 
                : $features;
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No data to update',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->planService->update((int) $id, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Plan updated successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found or failed to update',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Delete plan (superadmin only)
     * DELETE /plan/delete/{id}
     */
    public function delete($id)
    {
        try {
            $result = $this->planService->delete((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Plan deleted successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Admin: List plans page
     * GET /admin/plans
     */
    public function index()
    {
        $plans = $this->planService->getAll();

        $data = [
            'title' => 'Paket Langganan - Admin Dashboard',
            'page_title' => 'Paket Langganan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'plans' => $plans,
        ];

        return view('Modules\\Plan\\Views\\admin_index', $data);
    }

    /**
     * Admin: Create plan page
     * GET /admin/plans/create
     */
    public function createPage()
    {
        $data = [
            'title' => 'Buat Paket - Admin Dashboard',
            'page_title' => 'Buat Paket Langganan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'plan' => null,
        ];

        return view('Modules\\Plan\\Views\\admin_form', $data);
    }

    /**
     * Admin: Store plan
     * POST /admin/plans/store
     */
    public function store()
    {
        $data = [
            'name' => $this->request->getPost('name'),
            'price' => $this->request->getPost('price') ?? 0,
            'description' => $this->request->getPost('description'),
            'features' => $this->request->getPost('features'),
        ];

        if (empty($data['name'])) {
            return redirect()->back()->withInput()->with('error', 'Nama paket wajib diisi');
        }

        // Parse features if string
        if (is_string($data['features'])) {
            $data['features'] = json_decode($data['features'], true) ?? [];
        }

        try {
            $id = $this->planService->create($data);
            if ($id) {
                return redirect()->to('/admin/plans')->with('success', 'Paket berhasil dibuat');
            }
            return redirect()->back()->withInput()->with('error', 'Gagal membuat paket');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Edit plan page
     * GET /admin/plans/{id}/edit
     */
    public function edit(int $id)
    {
        $plan = $this->planService->getById($id);
        if (!$plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Paket - Admin Dashboard',
            'page_title' => 'Edit Paket Langganan',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'plan' => $plan,
        ];

        return view('Modules\\Plan\\Views\\admin_form', $data);
    }

    /**
     * Admin: Update plan
     * POST /admin/plans/{id}/update
     */
    public function updatePage(int $id)
    {
        $plan = $this->planService->getById($id);
        if (!$plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan');
        }

        $data = [];
        if ($this->request->getPost('name')) $data['name'] = $this->request->getPost('name');
        if ($this->request->getPost('price') !== null) $data['price'] = $this->request->getPost('price');
        if ($this->request->getPost('description') !== null) $data['description'] = $this->request->getPost('description');
        if ($this->request->getPost('features') !== null) {
            $features = $this->request->getPost('features');
            $data['features'] = is_string($features) ? json_decode($features, true) : $features;
        }

        try {
            $result = $this->planService->update($id, $data);
            if ($result) {
                return redirect()->to('/admin/plans')->with('success', 'Paket berhasil diperbarui');
            }
            return redirect()->back()->with('error', 'Gagal memperbarui paket');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Delete plan
     * POST /admin/plans/{id}/delete
     */
    public function deletePage(int $id)
    {
        $plan = $this->planService->getById($id);
        if (!$plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan');
        }

        try {
            $result = $this->planService->delete($id);
            if ($result) {
                return redirect()->to('/admin/plans')->with('success', 'Paket berhasil dihapus');
            }
            return redirect()->back()->with('error', 'Gagal menghapus paket');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

