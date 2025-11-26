<?php

namespace Modules\Setting\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Services;
use Modules\File\Services\StorageService;
use Modules\Setting\Models\PaymentMethodModel;

class SettingController extends BaseController
{
    /**
     * Get setting value
     * GET /setting/get/{key}
     */
    public function get($key)
    {
        $settingService = Services::setting();
        $value = $settingService->get($key);

        return $this->response->setJSON([
            'success' => true,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Set setting value
     * POST /setting/set
     */
    public function set()
    {
        $key = $this->request->getPost('key');
        $value = $this->request->getPost('value');
        $scope = $this->request->getPost('scope');
        $scopeId = $this->request->getPost('scope_id');
        $type = $this->request->getPost('type');
        $description = $this->request->getPost('description');

        if (empty($key) || $value === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Key and value are required',
            ])->setStatusCode(400);
        }

        $settingService = Services::setting();
        $result = $settingService->set($key, $value, $scope, $scopeId ? (int) $scopeId : null, $type, $description);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Setting saved successfully' : 'Failed to save setting',
        ]);
    }

    /**
     * Get all settings for current scope
     * GET /setting/all
     */
    public function all()
    {
        $scope = $this->request->getGet('scope');
        $scopeId = $this->request->getGet('scope_id') ? (int) $this->request->getGet('scope_id') : null;

        $settingService = Services::setting();
        $settings = $settingService->getAll($scope, $scopeId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Delete setting
     * DELETE /setting/delete/{key}
     */
    public function delete($key)
    {
        $scope = $this->request->getGet('scope');
        $scopeId = $this->request->getGet('scope_id') ? (int) $this->request->getGet('scope_id') : null;

        $settingService = Services::setting();
        $result = $settingService->delete($key, $scope, $scopeId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Setting deleted successfully' : 'Setting not found',
        ]);
    }

    /**
     * Get tenant setting
     * GET /setting/tenant/{key}
     */
    public function getTenant($key)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $settingService = Services::setting();
        $value = $settingService->getTenant($key, null, $tenantId);

        return $this->response->setJSON([
            'success' => true,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Set tenant setting
     * POST /setting/tenant/set
     */
    public function setTenant()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $key = $this->request->getPost('key');
        $value = $this->request->getPost('value');

        if (empty($key) || $value === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Key and value are required',
            ])->setStatusCode(400);
        }

        $settingService = Services::setting();
        $result = $settingService->setTenant($key, $value, $tenantId);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Tenant setting saved successfully' : 'Failed to save setting',
        ]);
    }

    /**
     * Admin Settings Page
     * GET /admin/settings
     */
    public function adminIndex()
    {
        $settingModel = new \Modules\Setting\Models\SettingModel();
        $settingsRaw = $settingModel->getByScope('global', null);

        // Convert to key-value array for easier access in view
        $settingService = Services::setting();
        $settings = [];
        foreach ($settingsRaw as $setting) {
            // Use reflection to access protected method or use getAll which already decodes
            $value = $settingService->get($setting['key'], null, 'global', null);
            $settings[$setting['key']] = [
                'key' => $setting['key'],
                'value' => $value,
                'type' => $setting['type'],
                'description' => $setting['description'] ?? '',
            ];
        }

        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'superadmin';

        // Get all tenants for tenant settings
        $db = \Config\Database::connect();
        $tenants = $db->table('tenants')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        // Get tenant hero images
        $tenantHeroImages = [];
        foreach ($tenants as $tenant) {
            $heroImage = $settingService->get('hero_image', null, 'tenant', $tenant['id']);
            $tenantHeroImages[$tenant['id']] = $heroImage;
        }

        $data = [
            'title' => 'Pengaturan Sistem - Admin Dashboard',
            'page_title' => 'Pengaturan Sistem',
            'sidebar_title' => 'UrunanKita',
            'user_name' => $authUser['name'] ?? session()->get('user_name') ?? 'Super Admin',
            'user_role' => $userRole,
            'userRole' => $userRole,
            'settings' => $settings,
            'tenants' => $tenants,
            'tenantHeroImages' => $tenantHeroImages,
        ];

        return view('Modules\\Setting\\Views\\admin_index', $data);
    }

    /**
     * Admin Save Settings
     * POST /admin/settings/save
     */
    public function adminSave()
    {
        $settings = $this->request->getPost('settings');
        
        if (empty($settings) || !is_array($settings)) {
            return redirect()->back()->with('error', 'Tidak ada pengaturan yang disimpan');
        }

        $settingService = Services::setting();
        $saved = 0;
        $errors = [];

        foreach ($settings as $key => $value) {
            try {
                // Use 'global' scope for platform settings (same as 'platform')
                $result = $settingService->set($key, $value, 'global', null);
                if ($result) {
                    $saved++;
                } else {
                    $errors[] = $key;
                }
            } catch (\Exception $e) {
                $errors[] = $key . ': ' . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->with('error', 'Beberapa pengaturan gagal disimpan: ' . implode(', ', $errors));
        }

        return redirect()->back()->with('success', "Berhasil menyimpan {$saved} pengaturan");
    }

    /**
     * Admin Save Settings per Section
     * POST /admin/settings/save-section
     */
    public function adminSaveSection()
    {
        $section = $this->request->getPost('section');
        $settingsJson = $this->request->getPost('settings');
        
        if (empty($section) || empty($settingsJson)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Section dan settings diperlukan',
            ])->setStatusCode(400);
        }

        $settings = json_decode($settingsJson, true);
        
        if (empty($settings) || !is_array($settings)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format settings tidak valid',
            ])->setStatusCode(400);
        }

        $settingService = Services::setting();
        $saved = 0;
        $errors = [];

        foreach ($settings as $key => $value) {
            try {
                // Use 'global' scope for platform settings
                $result = $settingService->set($key, $value, 'global', null);
                if ($result) {
                    $saved++;
                } else {
                    $errors[] = $key;
                }
            } catch (\Exception $e) {
                $errors[] = $key . ': ' . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Beberapa pengaturan gagal disimpan: ' . implode(', ', $errors),
            ]);
        }

        $sectionNames = [
            'platform' => 'Pengaturan Platform',
            'contact' => 'Kontak & Informasi',
            'email' => 'Pengaturan Email & Notifikasi',
            'payment' => 'Pengaturan Payment',
            'domain' => 'Pengaturan Domain',
            'general' => 'Pengaturan Umum',
            'seo' => 'Pengaturan SEO',
            'template' => 'Template Pesan WhatsApp',
        ];

        $sectionName = $sectionNames[$section] ?? $section;

        return $this->response->setJSON([
            'success' => true,
            'message' => "{$sectionName} berhasil disimpan",
            'saved' => $saved,
        ]);
    }

    /**
     * Admin Payment Methods Page
     * GET /admin/settings/payment-methods
     */
    public function adminPaymentMethods()
    {
        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'super_admin';

        $db = \Config\Database::connect();
        $tenants = $db->table('tenants')
            ->orderBy('slug', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($tenants)) {
            return redirect()->to('admin/settings')->with('error', 'Belum ada tenant yang terdaftar.');
        }

        $requestedTenantId = (int) ($this->request->getGet('tenant_id') ?? 0);
        $selectedTenant = null;

        foreach ($tenants as $tenant) {
            if ($requestedTenantId && (int) $tenant['id'] === $requestedTenantId) {
                $selectedTenant = $tenant;
                break;
            }
        }

        if (!$selectedTenant) {
            foreach ($tenants as $tenant) {
                if (($tenant['slug'] ?? '') === 'platform') {
                    $selectedTenant = $tenant;
                    break;
                }
            }
        }

        if (!$selectedTenant) {
            $selectedTenant = $tenants[0];
        }

        $selectedTenantId = (int) $selectedTenant['id'];
        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->getByTenant($selectedTenantId);

        $data = [
            'title' => 'Metode Pembayaran',
            'pageTitle' => 'Metode Pembayaran',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'tenants' => $tenants,
            'selectedTenant' => $selectedTenant,
            'selectedTenantId' => $selectedTenantId,
            'payment_methods' => $paymentMethods,
        ];

        return view('Modules\\Setting\\Views\\admin_payment_methods', $data);
    }

    /**
     * Admin Save Payment Method (create/update)
     * POST /admin/settings/payment-methods/save
     */
    public function adminSavePaymentMethod()
    {
        $tenantId = (int) $this->request->getPost('tenant_id');
        if (!$tenantId) {
            return redirect()->back()->with('error', 'Tenant tidak valid.');
        }

        $name = trim($this->request->getPost('name') ?? '');
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama metode pembayaran wajib diisi.');
        }

        $paymentMethodModel = new PaymentMethodModel();
        $methodId = (int) ($this->request->getPost('method_id') ?? 0);

        $data = [
            'tenant_id' => $tenantId,
            'name' => $name,
            'type' => $this->request->getPost('type') ?? 'bank-transfer',
            'enabled' => $this->request->getPost('enabled') ? 1 : 0,
            'description' => $this->request->getPost('description') ?? '',
            'provider' => $this->request->getPost('provider') ?? '',
            'admin_fee_percent' => (float) ($this->request->getPost('admin_fee_percent') ?? 0),
            'admin_fee_fixed' => (float) ($this->request->getPost('admin_fee_fixed') ?? 0),
            'require_verification' => $this->request->getPost('require_verification') ? 1 : 0,
        ];

        try {
            if ($methodId) {
                $method = $paymentMethodModel->find($methodId);
                if (!$method || (int) $method['tenant_id'] !== $tenantId) {
                    return redirect()->back()->with('error', 'Metode pembayaran tidak ditemukan.');
                }
                $paymentMethodModel->update($methodId, $data);
                $message = 'Metode pembayaran berhasil diperbarui.';
            } else {
                $code = $this->request->getPost('code');
                if (!$code) {
                    $code = 'payment_' . time() . '_' . random_int(1000, 9999);
                }
                $data['code'] = $code;
                $paymentMethodModel->insert($data);
                $message = 'Metode pembayaran baru berhasil ditambahkan.';
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect()->to('admin/settings/payment-methods?tenant_id=' . $tenantId)->with('success', $message);
    }

    /**
     * Toggle payment method status
     * POST /admin/settings/payment-methods/{id}/toggle
     */
    public function adminTogglePaymentMethod($id)
    {
        $paymentMethodModel = new PaymentMethodModel();
        $method = $paymentMethodModel->find($id);

        if (!$method) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Metode pembayaran tidak ditemukan.',
            ])->setStatusCode(404);
        }

        $enabled = $this->request->getPost('enabled');
        if ($enabled === null) {
            $enabled = !$method['enabled'];
        }

        $paymentMethodModel->update($id, [
            'enabled' => $enabled ? 1 : 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status metode pembayaran berhasil diperbarui.',
        ]);
    }

    /**
     * Delete payment method
     * POST /admin/settings/payment-methods/{id}/delete
     */
    public function adminDeletePaymentMethod($id)
    {
        $paymentMethodModel = new PaymentMethodModel();
        $method = $paymentMethodModel->find($id);

        if (!$method) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Metode pembayaran tidak ditemukan.',
            ])->setStatusCode(404);
        }

        $paymentMethodModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dihapus.',
        ]);
    }

    /**
     * Upload Logo
     * POST /admin/settings/upload-logo
     */
    public function uploadLogo()
    {
        $file = $this->request->getFile('file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid',
            ])->setStatusCode(400);
        }

        try {
            // Use StorageService for platform files (tenant_id = 0 for platform)
            $storageService = new StorageService();
            
            // Create platform uploads directory
            // FCPATH points to public/ directory
            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'platform';
            
            // Ensure parent directory exists first
            $parentPath = FCPATH . 'uploads';
            if (!is_dir($parentPath)) {
                if (!mkdir($parentPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads. Pastikan folder public/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Create platform subdirectory
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads/platform. Pastikan folder public/uploads/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }

            // Validate file type
            $allowedTypes = ['png', 'svg'];
            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak diizinkan. Hanya PNG dan SVG yang diperbolehkan.',
                ])->setStatusCode(400);
            }

            // Validate file size (max 2MB)
            if ($file->getSize() > 2097152) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal 2MB.',
                ])->setStatusCode(400);
            }

            // Generate unique filename
            $newFilename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Move uploaded file
            // CodeIgniter move() needs directory path without trailing slash
            // Log for debugging
            log_message('debug', 'Attempting to move file to: ' . $uploadPath . '/' . $newFilename);
            log_message('debug', 'Upload path exists: ' . (is_dir($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Upload path writable: ' . (is_writable($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Temp file: ' . $file->getTempName());
            
            if (!$file->move($uploadPath, $newFilename)) {
                $errors = $file->getErrorString();
                $errorCode = $file->getError();
                log_message('error', 'File move failed: ' . $errors . ' (Code: ' . $errorCode . ')');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file: ' . $errors . ' (Error code: ' . $errorCode . '). Pastikan folder ' . $uploadPath . ' writable.',
                ])->setStatusCode(500);
            }
            
            log_message('debug', 'File moved successfully');

            $filePath = 'uploads/platform/' . $newFilename;
            $fullUrl = base_url($filePath);

            // Save to settings
            $settingService = Services::setting();
            $settingService->set('site_logo', $fullUrl, 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logo berhasil diupload',
                'path' => $fullUrl,
                'filename' => $newFilename,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Upload Favicon
     * POST /admin/settings/upload-favicon
     */
    public function uploadFavicon()
    {
        $file = $this->request->getFile('file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid',
            ])->setStatusCode(400);
        }

        try {
            // Use StorageService for platform files (tenant_id = 0 for platform)
            $storageService = new StorageService();
            
            // Create platform uploads directory
            // FCPATH points to public/ directory
            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'platform';
            
            // Ensure parent directory exists first
            $parentPath = FCPATH . 'uploads';
            if (!is_dir($parentPath)) {
                if (!mkdir($parentPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads. Pastikan folder public/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Create platform subdirectory
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads/platform. Pastikan folder public/uploads/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }

            // Validate file type
            $allowedTypes = ['ico', 'png'];
            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak diizinkan. Hanya ICO dan PNG yang diperbolehkan.',
                ])->setStatusCode(400);
            }

            // Validate file size (max 1MB)
            if ($file->getSize() > 1048576) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal 1MB.',
                ])->setStatusCode(400);
            }

            // Generate unique filename
            $newFilename = 'favicon_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Move uploaded file
            // CodeIgniter move() needs directory path without trailing slash
            // Log for debugging
            log_message('debug', 'Attempting to move file to: ' . $uploadPath . '/' . $newFilename);
            log_message('debug', 'Upload path exists: ' . (is_dir($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Upload path writable: ' . (is_writable($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Temp file: ' . $file->getTempName());
            
            if (!$file->move($uploadPath, $newFilename)) {
                $errors = $file->getErrorString();
                $errorCode = $file->getError();
                log_message('error', 'File move failed: ' . $errors . ' (Code: ' . $errorCode . ')');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file: ' . $errors . ' (Error code: ' . $errorCode . '). Pastikan folder ' . $uploadPath . ' writable.',
                ])->setStatusCode(500);
            }
            
            log_message('debug', 'File moved successfully');

            $filePath = 'uploads/platform/' . $newFilename;
            $fullUrl = base_url($filePath);

            // Save to settings
            $settingService = Services::setting();
            $settingService->set('site_favicon', $fullUrl, 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Favicon berhasil diupload',
                'path' => $fullUrl,
                'filename' => $newFilename,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove Logo
     * POST /admin/settings/remove-logo
     */
    public function removeLogo()
    {
        try {
            $settingService = Services::setting();
            $settingService->set('site_logo', '', 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logo berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove Favicon
     * POST /admin/settings/remove-favicon
     */
    public function removeFavicon()
    {
        try {
            $settingService = Services::setting();
            $settingService->set('site_favicon', '', 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Favicon berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Upload Hero Image
     * POST /admin/settings/upload-hero-image
     */
    public function uploadHeroImage()
    {
        $file = $this->request->getFile('file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid',
            ])->setStatusCode(400);
        }

        try {
            // Create platform uploads directory
            // FCPATH points to public/ directory
            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'platform';
            
            // Ensure parent directory exists first
            $parentPath = FCPATH . 'uploads';
            if (!is_dir($parentPath)) {
                if (!mkdir($parentPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads. Pastikan folder public/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Create platform subdirectory
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal membuat folder uploads/platform. Pastikan folder public/uploads/ writable.',
                    ])->setStatusCode(500);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Folder uploads tidak writable. Jalankan: chmod -R 775 public/uploads/',
                ])->setStatusCode(500);
            }

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak diizinkan. Hanya JPG, PNG, GIF, dan WEBP yang diperbolehkan.',
                ])->setStatusCode(400);
            }

            // Validate file size (max 5MB)
            if ($file->getSize() > 5242880) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal 5MB.',
                ])->setStatusCode(400);
            }

            // Generate unique filename
            $newFilename = 'hero_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Move uploaded file
            // CodeIgniter move() needs directory path without trailing slash
            // Log for debugging
            log_message('debug', 'Attempting to move file to: ' . $uploadPath . '/' . $newFilename);
            log_message('debug', 'Upload path exists: ' . (is_dir($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Upload path writable: ' . (is_writable($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Temp file: ' . $file->getTempName());
            
            if (!$file->move($uploadPath, $newFilename)) {
                $errors = $file->getErrorString();
                $errorCode = $file->getError();
                log_message('error', 'File move failed: ' . $errors . ' (Code: ' . $errorCode . ')');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file: ' . $errors . ' (Error code: ' . $errorCode . '). Pastikan folder ' . $uploadPath . ' writable.',
                ])->setStatusCode(500);
            }
            
            log_message('debug', 'File moved successfully');

            $filePath = 'uploads/platform/' . $newFilename;
            $fullUrl = base_url($filePath);

            // Save to settings
            $settingService = Services::setting();
            $settingService->set('hero_image', $fullUrl, 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Gambar hero berhasil diupload',
                'path' => $fullUrl,
                'filename' => $newFilename,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove Hero Image
     * POST /admin/settings/remove-hero-image
     */
    public function removeHeroImage()
    {
        try {
            $settingService = Services::setting();
            $settingService->set('hero_image', '', 'global', null);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Gambar hero berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Upload Hero Image for Tenant (by Admin)
     * POST /admin/settings/upload-hero-image-tenant
     */
    public function uploadHeroImageTenant()
    {
        $file = $this->request->getFile('file');
        $tenantId = $this->request->getPost('tenant_id');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid',
            ])->setStatusCode(400);
        }

        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant ID diperlukan',
            ])->setStatusCode(400);
        }

        try {
            // Create tenant uploads directory (BUKAN platform)
            $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, $allowedTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak diizinkan. Hanya JPG, PNG, GIF, dan WEBP yang diperbolehkan.',
                ])->setStatusCode(400);
            }

            // Validate file size (max 5MB)
            if ($file->getSize() > 5242880) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ukuran file terlalu besar. Maksimal 5MB.',
                ])->setStatusCode(400);
            }

            // Generate unique filename
            $newFilename = 'hero_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

            // Move uploaded file
            // CodeIgniter move() needs directory path without trailing slash
            // Log for debugging
            log_message('debug', 'Attempting to move file to: ' . $uploadPath . '/' . $newFilename);
            log_message('debug', 'Upload path exists: ' . (is_dir($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Upload path writable: ' . (is_writable($uploadPath) ? 'yes' : 'no'));
            log_message('debug', 'Temp file: ' . $file->getTempName());
            
            if (!$file->move($uploadPath, $newFilename)) {
                $errors = $file->getErrorString();
                $errorCode = $file->getError();
                log_message('error', 'File move failed: ' . $errors . ' (Code: ' . $errorCode . ')');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file: ' . $errors . ' (Error code: ' . $errorCode . '). Pastikan folder ' . $uploadPath . ' writable.',
                ])->setStatusCode(500);
            }
            
            log_message('debug', 'File moved successfully');

            // Path untuk tenant (BUKAN platform)
            $filePath = 'uploads/tenants/' . $tenantId . '/' . $newFilename;
            $fullUrl = base_url($filePath);

            // Save to settings with tenant scope (BUKAN global)
            $settingService = Services::setting();
            $settingService->set('hero_image', $fullUrl, 'tenant', (int) $tenantId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Gambar hero tenant berhasil diupload',
                'path' => $fullUrl,
                'filename' => $newFilename,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove Hero Image for Tenant (by Admin)
     * POST /admin/settings/remove-hero-image-tenant
     */
    public function removeHeroImageTenant()
    {
        $tenantId = $this->request->getPost('tenant_id');
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant ID diperlukan',
            ])->setStatusCode(400);
        }

        try {
            $settingService = Services::setting();
            // Remove dengan scope tenant (BUKAN global)
            $settingService->set('hero_image', '', 'tenant', (int) $tenantId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Gambar hero tenant berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}

