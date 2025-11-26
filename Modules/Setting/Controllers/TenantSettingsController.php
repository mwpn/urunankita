<?php

namespace Modules\Setting\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Setting\Models\PaymentMethodModel;
use Config\Services;

class TenantSettingsController extends BaseController
{
    public function index()
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return redirect()->to('/tenant/dashboard')->with('error', 'Tenant not found');
        }

        // Get payment methods from database
        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->getByTenant($tenantId);
        
        // If no payment methods exist, create default ones
        if (empty($paymentMethods)) {
            $defaultPaymentMethods = [
                [
                    'tenant_id' => $tenantId,
                    'code' => 'bank_transfer',
                    'name' => 'Transfer Bank',
                    'type' => 'bank-transfer',
                    'enabled' => 1,
                    'description' => 'Transfer melalui rekening bank',
                    'provider' => 'BCA, Mandiri, BNI, BRI',
                    'admin_fee_percent' => 0,
                    'admin_fee_fixed' => 0,
                    'require_verification' => 0,
                ],
                [
                    'tenant_id' => $tenantId,
                    'code' => 'e_wallet',
                    'name' => 'E-Wallet',
                    'type' => 'e-wallet',
                    'enabled' => 1,
                    'description' => 'OVO, DANA, GoPay, LinkAja, dll',
                    'provider' => 'OVO, DANA, GoPay, LinkAja',
                    'admin_fee_percent' => 0.5,
                    'admin_fee_fixed' => 0,
                    'require_verification' => 0,
                ],
                [
                    'tenant_id' => $tenantId,
                    'code' => 'qris',
                    'name' => 'QRIS',
                    'type' => 'qris',
                    'enabled' => 1,
                    'description' => 'Pembayaran via QRIS',
                    'provider' => 'QR Code Indonesia Standard',
                    'admin_fee_percent' => 0.7,
                    'admin_fee_fixed' => 0,
                    'require_verification' => 0,
                ],
                [
                    'tenant_id' => $tenantId,
                    'code' => 'virtual_account',
                    'name' => 'Virtual Account',
                    'type' => 'virtual-account',
                    'enabled' => 0,
                    'description' => 'Virtual Account (VA)',
                    'provider' => 'VA BCA, Mandiri, BNI',
                    'admin_fee_percent' => 0,
                    'admin_fee_fixed' => 0,
                    'require_verification' => 0,
                ],
                [
                    'tenant_id' => $tenantId,
                    'code' => 'credit_card',
                    'name' => 'Kartu Kredit',
                    'type' => 'kartu',
                    'enabled' => 0,
                    'description' => 'Kartu Kredit / Debit',
                    'provider' => 'Visa, Mastercard',
                    'admin_fee_percent' => 2.5,
                    'admin_fee_fixed' => 0,
                    'require_verification' => 0,
                ],
            ];
            
            foreach ($defaultPaymentMethods as $method) {
                $paymentMethodModel->insert($method);
            }
            
            // Reload payment methods
            $paymentMethods = $paymentMethodModel->getByTenant($tenantId);
        }
        
        // Convert enabled to boolean for view compatibility
        foreach ($paymentMethods as &$method) {
            $method['enabled'] = (bool) ($method['enabled'] ?? 0);
            $method['require_verification'] = (bool) ($method['require_verification'] ?? 0);
        }

        // Get template settings (tenant-specific with fallback to global)
        $settingService = Services::setting();
        $templateKeys = [
            'whatsapp_template_donation_created',
            'whatsapp_template_donation_paid',
            'whatsapp_template_tenant_donation_new',
        ];
        
        $templates = [];
        $templatesSource = []; // Track if template is from tenant or global
        foreach ($templateKeys as $key) {
            // Try tenant template first
            $tenantValue = $settingService->get($key, null, 'tenant', $tenantId);
            if (!empty($tenantValue)) {
                $templates[$key] = $tenantValue;
                $templatesSource[$key] = 'tenant';
            } else {
                // Fallback to global
                $globalValue = $settingService->get($key, null, 'global', null);
                $templates[$key] = $globalValue ?? '';
                $templatesSource[$key] = 'global';
            }
        }

        // Get site settings (with fallback to global)
        // Note: logo and favicon always use platform defaults
        $settingService = Services::setting();
        $siteKeys = [
            'site_name',
            'site_tagline',
            'site_description',
            'site_email',
            'site_phone',
            'site_address',
            'site_facebook',
            'site_instagram',
            'site_twitter',
        ];
        $siteSettings = [];
        foreach ($siteKeys as $key) {
            // Try tenant setting first, fallback to global
            $siteSettings[$key] = $settingService->getTenant($key, null, $tenantId);
        }
        // Logo and favicon always use platform defaults
        $siteSettings['site_logo'] = $settingService->get('site_logo', null, 'global', null);
        $siteSettings['site_favicon'] = $settingService->get('site_favicon', null, 'global', null);
        // Hero image for tenant
        $siteSettings['hero_image'] = $settingService->get('hero_image', null, 'tenant', $tenantId);

        $data = [
            'pageTitle' => 'Pengaturan',
            'userRole' => 'penggalang_dana',
            'title' => 'Pengaturan - Tenant',
            'page_title' => 'Pengaturan',
            'sidebar_title' => session()->get('tenant_name') ?? 'UrunanKita',
            'payment_methods' => $paymentMethods,
            'templates' => $templates,
            'templatesSource' => $templatesSource,
            'siteSettings' => $siteSettings,
        ];

        return view('Modules\\Setting\\Views\\tenant_settings', $data);
    }

    public function save()
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return redirect()->to('/tenant/dashboard')->with('error', 'Tenant not found');
        }

        $paymentMethodModel = new PaymentMethodModel();
        $settingService = Services::setting();

        // Save template settings (if provided)
        $templatesPost = $this->request->getPost('templates');
        if ($templatesPost && is_array($templatesPost)) {
            foreach ($templatesPost as $key => $value) {
                if (strpos($key, 'whatsapp_template_') === 0) {
                    // If value is empty, delete tenant-specific setting (fallback to global)
                    if (empty(trim($value))) {
                        $settingService->delete($key, 'tenant', $tenantId);
                    } else {
                        // Save as tenant-specific setting
                        $settingService->set($key, trim($value), 'tenant', $tenantId);
                    }
                }
            }
        }

        // Save payment methods
        $paymentMethodsPost = $this->request->getPost('payment_methods');
        if ($paymentMethodsPost && is_array($paymentMethodsPost)) {
            // Handle new payment method
            if (isset($paymentMethodsPost['new']) && !empty($paymentMethodsPost['new']['name'])) {
                $newMethod = $paymentMethodsPost['new'];
                $code = 'payment_' . time() . '_' . rand(1000, 9999);
                
                $paymentMethodModel->insert([
                    'tenant_id' => $tenantId,
                    'code' => $code,
                    'name' => $newMethod['name'],
                    'type' => $newMethod['type'] ?? 'bank-transfer',
                    'enabled' => isset($newMethod['enabled']) && $newMethod['enabled'] === '1' ? 1 : 0,
                    'description' => $newMethod['description'] ?? '',
                    'provider' => $newMethod['provider'] ?? '',
                    'admin_fee_percent' => floatval($newMethod['admin_fee_percent'] ?? 0),
                    'admin_fee_fixed' => floatval($newMethod['admin_fee_fixed'] ?? 0),
                    'require_verification' => isset($newMethod['require_verification']) && $newMethod['require_verification'] === '1' ? 1 : 0,
                ]);
            }
            
            // Handle edit payment method
            if (isset($paymentMethodsPost['edit']) && !empty($paymentMethodsPost['edit']['name'])) {
                $editId = $this->request->getPost('edit_id');
                $editMethod = $paymentMethodsPost['edit'];
                
                if ($editId) {
                    // Verify the payment method belongs to this tenant
                    $methodToUpdate = $paymentMethodModel->find($editId);
                    
                    if ($methodToUpdate && $methodToUpdate['tenant_id'] == $tenantId) {
                        $paymentMethodModel->update($editId, [
                            'name' => $editMethod['name'],
                            'type' => $editMethod['type'] ?? 'bank-transfer',
                            'enabled' => isset($editMethod['enabled']) && $editMethod['enabled'] === '1' ? 1 : 0,
                            'description' => $editMethod['description'] ?? '',
                            'provider' => $editMethod['provider'] ?? '',
                            'admin_fee_percent' => floatval($editMethod['admin_fee_percent'] ?? 0),
                            'admin_fee_fixed' => floatval($editMethod['admin_fee_fixed'] ?? 0),
                            'require_verification' => isset($editMethod['require_verification']) && $editMethod['require_verification'] === '1' ? 1 : 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->to('/tenant/settings')->with('success', 'Pengaturan berhasil disimpan');
    }

    /**
     * Toggle payment method enabled status
     * POST /tenant/settings/payment-method/{id}/toggle
     */
    public function togglePaymentMethod($id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(400);
        }

        $paymentMethodModel = new PaymentMethodModel();
        $method = $paymentMethodModel->find($id);
        
        if (!$method || $method['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment method not found',
            ])->setStatusCode(404);
        }

        $enabled = $this->request->getPost('enabled');
        if ($enabled === null) {
            $enabled = !$method['enabled']; // Toggle if not provided
        } else {
            $enabled = $enabled === '1' || $enabled === 1 || $enabled === true;
        }
        
        $paymentMethodModel->update($id, [
            'enabled' => $enabled ? 1 : 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status metode pembayaran berhasil diubah',
        ]);
    }

    /**
     * Delete payment method
     * POST /tenant/settings/payment-method/{id}/delete
     */
    public function deletePaymentMethod($id)
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(400);
        }

        $paymentMethodModel = new PaymentMethodModel();
        $method = $paymentMethodModel->find($id);
        
        if (!$method || $method['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment method not found',
            ])->setStatusCode(404);
        }

        $paymentMethodModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dihapus',
        ]);
    }

    /**
     * Save tenant site settings
     * POST /tenant/settings/save-site
     */
    public function saveSite()
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        // Fallback: derive from logged-in user if tenant_id not in session
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        session()->set('tenant_id', (int) $tenant['id']);
                        session()->set('tenant_slug', $tenant['slug']);
                        session()->set('is_subdomain', false);
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return redirect()->to('/tenant/settings')->with('error', 'Tenant not found');
        }

        $settingService = Services::setting();
        // Exclude logo and favicon - tenant will use platform defaults
        $siteKeys = [
            'site_name',
            'site_tagline',
            'site_description',
            'site_email',
            'site_phone',
            'site_address',
            'site_facebook',
            'site_instagram',
            'site_twitter',
            'hero_image',
        ];

        foreach ($siteKeys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                // Save as tenant-specific setting
                $settingService->set($key, $value, 'tenant', $tenantId);
            }
        }

        return redirect()->to('/tenant/settings#site')->with('success', 'Pengaturan situs berhasil disimpan');
    }

    /**
     * Upload Hero Image for Tenant
     * POST /tenant/settings/upload-hero-image
     */
    public function uploadHeroImage()
    {
        $file = $this->request->getFile('file');
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
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
            // Create tenant uploads directory
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
            if (!$file->move($uploadPath, $newFilename)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file',
                ])->setStatusCode(500);
            }

            $filePath = 'uploads/tenants/' . $tenantId . '/' . $newFilename;
            $fullUrl = base_url($filePath);

            // Save to settings with tenant scope
            $settingService = Services::setting();
            $settingService->set('hero_image', $fullUrl, 'tenant', $tenantId);

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
     * Remove Hero Image for Tenant
     * POST /tenant/settings/remove-hero-image
     */
    public function removeHeroImage()
    {
        $tenantId = (int) (session()->get('tenant_id') ?? 0);
        
        if (!$tenantId) {
            $authUser = session()->get('auth_user') ?? [];
            $userId = $authUser['id'] ?? null;
            if ($userId) {
                $db = \Config\Database::connect();
                $userRow = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
                if ($userRow && !empty($userRow['tenant_id'])) {
                    $tenant = $db->table('tenants')->where('id', (int) $userRow['tenant_id'])->get()->getRowArray();
                    if ($tenant) {
                        $tenantId = (int) $tenant['id'];
                    }
                }
            }
        }
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant ID diperlukan',
            ])->setStatusCode(400);
        }

        try {
            $settingService = Services::setting();
            $settingService->set('hero_image', '', 'tenant', $tenantId);

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
}


