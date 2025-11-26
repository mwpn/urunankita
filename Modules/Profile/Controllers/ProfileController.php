<?php

namespace Modules\Profile\Controllers;

use Modules\Core\Controllers\BaseController;

class ProfileController extends BaseController
{
    /**
     * Profile Overview
     * GET /profile/overview
     */
    public function overview()
    {
        $auth = session()->get('auth_user') ?? [];
        $userId = $auth['id'] ?? null;
        
        if (!$userId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Get user data
        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->select('users.*')
            ->where('users.id', (int) $userId)
            ->get()
            ->getRowArray();
        
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User tidak ditemukan');
        }
        
        // Ensure created_at and last_login are properly formatted
        if (empty($user['created_at']) || $user['created_at'] === '0000-00-00 00:00:00' || $user['created_at'] === '0000-00-00') {
            $user['created_at'] = null;
        }
        if (empty($user['last_login']) || $user['last_login'] === '0000-00-00 00:00:00' || $user['last_login'] === '0000-00-00') {
            $user['last_login'] = null;
        }

        // Get tenant info if exists
        $tenant = null;
        $tenantId = session()->get('tenant_id') ?? $user['tenant_id'] ?? null;
        if ($tenantId) {
            $tenant = $db->table('tenants')->where('id', (int) $tenantId)->get()->getRowArray();
            if (!$tenant) {
                $tenant = null;
            }
        }

        // Get user role
        $userRole = $auth['role'] ?? 'penggalang_dana';
        $isAdmin = ($userRole === 'superadmin' || $userRole === 'super_admin');

        // Get statistics (for tenant users)
        $stats = [
            'total_campaigns' => 0,
            'total_donations' => 0,
            'active_campaigns' => 0,
        ];

        if ($tenantId && !$isAdmin) {
            // Get campaign stats
            $campaignStats = $db->table('campaigns')
                ->select('COUNT(*) as total, SUM(current_amount) as total_donations, COUNT(CASE WHEN status = "active" THEN 1 END) as active')
                ->where('tenant_id', (int) $tenantId)
                ->get()
                ->getRowArray();
            
            $stats['total_campaigns'] = (int) ($campaignStats['total'] ?? 0);
            $stats['total_donations'] = (float) ($campaignStats['total_donations'] ?? 0);
            $stats['active_campaigns'] = (int) ($campaignStats['active'] ?? 0);
        }

        // Get recent activities (last 10)
        $activities = [];
        if ($tenantId) {
            $activities = $db->table('activity_logs')
                ->where('tenant_id', (int) $tenantId)
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
        }

        // Format avatar
        $avatar = $user['avatar'] ?? null;
        if ($avatar) {
            // If it's already a full URL, use it as is
            if (preg_match('~^https?://~', $avatar)) {
                // Already full URL, use as is
            } elseif (strpos($avatar, '/uploads/') === 0) {
                // Already starts with /uploads/, use base_url
                $avatar = base_url($avatar);
            } else {
                // Add /uploads/ prefix and use base_url
                $avatar = base_url('/uploads/' . ltrim($avatar, '/'));
            }
        } else {
            // Default avatar
            $avatar = base_url('admin-template/assets/avatars/face-1.jpg');
        }
        
        // Add timestamp to prevent caching issues
        if (strpos($avatar, '?') === false) {
            $avatar .= '?v=' . time();
        }

        $data = [
            'pageTitle' => 'Profil Saya',
            'userRole' => $userRole,
            'title' => 'Profil Saya - Dashboard',
            'page_title' => 'Profil Saya',
            'sidebar_title' => session()->get('tenant_name') ?? ($tenant['name'] ?? 'UrunanKita'),
            'user_name' => $user['name'] ?? 'User',
            'user_role' => $isAdmin ? 'Admin' : 'Penggalang Urunan',
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'activities' => $activities,
            'avatar' => $avatar,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Profile\\Views\\overview', $data);
    }

    /**
     * Profile Security Settings
     * GET /profile/security
     */
    public function security()
    {
        $auth = session()->get('auth_user') ?? [];
        $userId = $auth['id'] ?? null;
        
        if (!$userId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Get user data
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
        
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User tidak ditemukan');
        }

        // Get tenant info if exists
        $tenant = null;
        $tenantId = session()->get('tenant_id') ?? $user['tenant_id'] ?? null;
        if ($tenantId) {
            $tenant = $db->table('tenants')->where('id', (int) $tenantId)->get()->getRowArray();
        }

        // Get user role
        $userRole = $auth['role'] ?? 'penggalang_dana';
        $isAdmin = ($userRole === 'superadmin' || $userRole === 'super_admin');

        // Get active sessions (placeholder - can be implemented later)
        $sessions = [
            [
                'device' => 'Windows PC',
                'browser' => 'Chrome ' . (isset($_SERVER['HTTP_USER_AGENT']) ? 'Unknown' : 'Unknown'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'location' => 'Jakarta, Indonesia',
                'last_login' => date('d M Y, H:i') . ' WIB',
                'status' => 'active',
            ],
        ];

        $data = [
            'pageTitle' => 'Pengaturan Keamanan',
            'userRole' => $userRole,
            'title' => 'Pengaturan Keamanan - Dashboard',
            'page_title' => 'Pengaturan Keamanan',
            'sidebar_title' => session()->get('tenant_name') ?? ($tenant['name'] ?? 'UrunanKita'),
            'user_name' => $user['name'] ?? 'User',
            'user_role' => $isAdmin ? 'Admin' : 'Penggalang Urunan',
            'user' => $user,
            'tenant' => $tenant,
            'sessions' => $sessions,
            'isAdmin' => $isAdmin,
        ];

        return view('Modules\\Profile\\Views\\security', $data);
    }

    /**
     * Update Profile
     * POST /profile/update
     */
    public function update()
    {
        try {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ])->setStatusCode(401);
            }

            $db = \Config\Database::connect();
            
            // Get user data
            $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Get input data
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $phone = $this->request->getPost('phone');

            // Validate required fields
            if (empty($name) || empty($email) || empty($phone)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama, email, dan nomor telepon wajib diisi'
                ])->setStatusCode(400);
            }

            // Validate phone format
            if (!preg_match('/^[0-9+\-\s()]+$/', $phone)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format nomor telepon tidak valid'
                ])->setStatusCode(400);
            }

            // Clean phone number (remove spaces, dashes, etc.)
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Validate phone length (min 10 digits, max 15 digits)
            $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nomor telepon harus 10-15 digit'
                ])->setStatusCode(400);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format email tidak valid'
                ])->setStatusCode(400);
            }

            // Check if email is already used by another user
            $existingUser = $db->table('users')
                ->where('email', $email)
                ->where('id !=', (int) $userId)
                ->get()
                ->getRowArray();
            
            if ($existingUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email sudah digunakan oleh user lain'
                ])->setStatusCode(400);
            }

            // Update user data (only fields that exist in users table)
            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('users')
                ->where('id', (int) $userId)
                ->update($userData);

            // Update session
            $updatedUser = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            $authUser = session()->get('auth_user') ?? [];
            $authUser['name'] = $updatedUser['name'];
            $authUser['email'] = $updatedUser['email'];
            session()->set('auth_user', $authUser);

            // Set flashdata for success notification
            session()->setFlashdata('success', 'Profil berhasil diperbarui');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'redirect' => base_url('tenant/profile/overview')
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Update Avatar
     * POST /tenant/profile/avatar
     */
    public function updateAvatar()
    {
        try {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ])->setStatusCode(401);
            }

            $db = \Config\Database::connect();
            
            // Get user data
            $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Get tenant_id for upload path
            $tenantId = session()->get('tenant_id') ?? $user['tenant_id'] ?? 1;
            if (!$tenantId || $tenantId <= 0) {
                $tenantId = 1; // Fallback to tenant 1 if not found
            }
            
            $avatarFile = $this->request->getFile('avatar');
            
            if (!$avatarFile) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak ditemukan. Silakan pilih foto terlebih dahulu.'
                ])->setStatusCode(400);
            }
            
            if (!$avatarFile->isValid()) {
                $error = $avatarFile->getError();
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas maksimal (2MB)',
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas maksimal (2MB)',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension',
                ];
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMessages[$error] ?? 'File avatar tidak valid: ' . $avatarFile->getErrorString()
                ])->setStatusCode(400);
            }

            // Use StorageService for upload (consistent with other file uploads)
            try {
                $storage = \Modules\File\Config\Services::storage();
                $uploadResult = $storage->upload($avatarFile, $tenantId, [
                    'max_size' => 2097152, // 2MB
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'webp']
                ]);

                // Delete old avatar if exists
                if (!empty($user['avatar'])) {
                    $oldAvatarPath = $user['avatar'];
                    // Try to delete from old location (avatars folder)
                    $oldPath = FCPATH . ltrim($oldAvatarPath, '/');
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                    // Also try to delete from tenant files if it's there
                    if (strpos($oldAvatarPath, 'tenant_') !== false) {
                        try {
                            $storage->delete(basename($oldAvatarPath), $tenantId);
                        } catch (\Exception $e) {
                            // Ignore if file doesn't exist in storage
                        }
                    }
                }

                // Update user avatar - use path from StorageService
                $avatarPath = '/uploads/' . $uploadResult['path'];
                $db->table('users')
                    ->where('id', (int) $userId)
                    ->update([
                        'avatar' => $avatarPath,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                // Update session
                $authUser = session()->get('auth_user') ?? [];
                $authUser['avatar'] = $avatarPath;
                session()->set('auth_user', $authUser);

                // Set flashdata for success notification
                session()->setFlashdata('success', 'Foto profil berhasil diubah');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Foto profil berhasil diubah',
                    'avatar_url' => base_url($avatarPath),
                    'redirect' => base_url('tenant/profile/overview')
                ]);
            } catch (\RuntimeException $e) {
                // StorageService validation errors
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            log_message('error', 'Avatar update error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Admin Profile Overview
     * GET /admin/profile/overview
     */
    public function adminOverview()
    {
        $auth = session()->get('auth_user') ?? [];
        $userId = $auth['id'] ?? null;
        
        if (!$userId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Get user data
        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->select('users.*')
            ->where('users.id', (int) $userId)
            ->get()
            ->getRowArray();
        
        if (!$user) {
            return redirect()->to('/admin/dashboard')->with('error', 'User tidak ditemukan');
        }
        
        // Ensure created_at and last_login are properly formatted
        if (empty($user['created_at']) || $user['created_at'] === '0000-00-00 00:00:00' || $user['created_at'] === '0000-00-00') {
            $user['created_at'] = null;
        }
        if (empty($user['last_login']) || $user['last_login'] === '0000-00-00 00:00:00' || $user['last_login'] === '0000-00-00') {
            $user['last_login'] = null;
        }

        // Get user role
        $userRole = $auth['role'] ?? 'superadmin';
        $isAdmin = ($userRole === 'superadmin' || $userRole === 'super_admin' || $userRole === 'admin');

        // Get platform statistics (for admin)
        $stats = [
            'total_tenants' => 0,
            'total_campaigns' => 0,
            'total_donations' => 0,
        ];

        if ($isAdmin) {
            // Get tenant stats
            $tenantStats = $db->table('tenants')
                ->select('COUNT(*) as total')
                ->get()
                ->getRowArray();
            
            $stats['total_tenants'] = (int) ($tenantStats['total'] ?? 0);

            // Get campaign stats
            $campaignStats = $db->table('campaigns')
                ->select('COUNT(*) as total, SUM(current_amount) as total_donations')
                ->get()
                ->getRowArray();
            
            $stats['total_campaigns'] = (int) ($campaignStats['total'] ?? 0);
            $stats['total_donations'] = (float) ($campaignStats['total_donations'] ?? 0);
        }

        // Get recent activities (last 10) - platform wide
        $activities = [];
        $activities = $db->table('activity_logs')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Format avatar
        $avatar = $user['avatar'] ?? null;
        if ($avatar) {
            // If it's already a full URL, use it as is
            if (preg_match('~^https?://~', $avatar)) {
                // Already full URL, use as is
            } elseif (strpos($avatar, '/uploads/') === 0) {
                // Already starts with /uploads/, use base_url
                $avatar = base_url($avatar);
            } else {
                // Add /uploads/ prefix and use base_url
                $avatar = base_url('/uploads/' . ltrim($avatar, '/'));
            }
        } else {
            // Default avatar
            $avatar = base_url('admin-template/assets/avatars/face-1.jpg');
        }
        
        // Add timestamp to prevent caching issues
        if (strpos($avatar, '?') === false) {
            $avatar .= '?v=' . time();
        }

        $data = [
            'pageTitle' => 'Profil Saya',
            'userRole' => 'admin',
            'title' => 'Profil Saya - Admin Dashboard',
            'page_title' => 'Profil Saya',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $user['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'user' => $user,
            'tenant' => null,
            'stats' => $stats,
            'activities' => $activities,
            'avatar' => $avatar,
            'isAdmin' => true,
        ];

        return view('Modules\\Profile\\Views\\admin_overview', $data);
    }

    /**
     * Admin Profile Settings
     * GET /admin/profile/settings
     */
    public function adminSettings()
    {
        return $this->adminOverview(); // Reuse overview for now
    }

    /**
     * Admin Profile Security
     * GET /admin/profile/security
     */
    public function adminSecurity()
    {
        $auth = session()->get('auth_user') ?? [];
        $userId = $auth['id'] ?? null;
        
        if (!$userId) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Get user data
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
        
        if (!$user) {
            return redirect()->to('/admin/dashboard')->with('error', 'User tidak ditemukan');
        }

        // Get active sessions (placeholder - can be implemented later)
        $sessions = [
            [
                'device' => 'Windows PC',
                'browser' => 'Chrome ' . (isset($_SERVER['HTTP_USER_AGENT']) ? 'Unknown' : 'Unknown'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'location' => 'Jakarta, Indonesia',
                'last_login' => date('d M Y, H:i') . ' WIB',
                'status' => 'active',
            ],
        ];

        $data = [
            'pageTitle' => 'Pengaturan Keamanan',
            'userRole' => 'admin',
            'title' => 'Pengaturan Keamanan - Admin Dashboard',
            'page_title' => 'Pengaturan Keamanan',
            'sidebar_title' => 'UrunanKita Admin',
            'user_name' => $user['name'] ?? 'Admin',
            'user_role' => 'Admin',
            'user' => $user,
            'tenant' => null,
            'sessions' => $sessions,
            'isAdmin' => true,
        ];

        return view('Modules\\Profile\\Views\\admin_security', $data);
    }

    /**
     * Admin Update Profile
     * POST /admin/profile/update
     */
    public function adminUpdate()
    {
        try {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ])->setStatusCode(401);
            }

            $db = \Config\Database::connect();
            
            // Get user data
            $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Get input data
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $phone = $this->request->getPost('phone');

            // Validate required fields
            if (empty($name) || empty($email) || empty($phone)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nama, email, dan nomor telepon wajib diisi'
                ])->setStatusCode(400);
            }

            // Validate phone format
            if (!preg_match('/^[0-9+\-\s()]+$/', $phone)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format nomor telepon tidak valid'
                ])->setStatusCode(400);
            }

            // Clean phone number (remove spaces, dashes, etc.)
            $phone = preg_replace('/[^0-9+]/', '', $phone);
            
            // Validate phone length (min 10 digits, max 15 digits)
            $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Nomor telepon harus 10-15 digit'
                ])->setStatusCode(400);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format email tidak valid'
                ])->setStatusCode(400);
            }

            // Check if email is already used by another user
            $existingUser = $db->table('users')
                ->where('email', $email)
                ->where('id !=', (int) $userId)
                ->get()
                ->getRowArray();
            
            if ($existingUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email sudah digunakan oleh user lain'
                ])->setStatusCode(400);
            }

            // Update user data
            $userData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db->table('users')
                ->where('id', (int) $userId)
                ->update($userData);

            // Update session
            $updatedUser = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            $authUser = session()->get('auth_user') ?? [];
            $authUser['name'] = $updatedUser['name'];
            $authUser['email'] = $updatedUser['email'];
            session()->set('auth_user', $authUser);

            // Set flashdata for success notification
            session()->setFlashdata('success', 'Profil berhasil diperbarui');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'redirect' => base_url('admin/profile/overview')
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Admin profile update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Admin Update Avatar
     * POST /admin/profile/avatar
     */
    public function adminUpdateAvatar()
    {
        try {
            $auth = session()->get('auth_user') ?? [];
            $userId = $auth['id'] ?? null;
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu'
                ])->setStatusCode(401);
            }

            $db = \Config\Database::connect();
            
            // Get user data
            $user = $db->table('users')->where('id', (int) $userId)->get()->getRowArray();
            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ])->setStatusCode(404);
            }

            // Use tenant_id = 1 for admin (or get from user if exists)
            $tenantId = $user['tenant_id'] ?? 1;
            if (!$tenantId || $tenantId <= 0) {
                $tenantId = 1; // Fallback to tenant 1 if not found
            }
            
            $avatarFile = $this->request->getFile('avatar');
            
            if (!$avatarFile) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak ditemukan. Silakan pilih foto terlebih dahulu.'
                ])->setStatusCode(400);
            }
            
            if (!$avatarFile->isValid()) {
                $error = $avatarFile->getError();
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas maksimal (2MB)',
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas maksimal (2MB)',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension',
                ];
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMessages[$error] ?? 'File avatar tidak valid: ' . $avatarFile->getErrorString()
                ])->setStatusCode(400);
            }

            // Use StorageService for upload
            try {
                $storage = \Modules\File\Config\Services::storage();
                $uploadResult = $storage->upload($avatarFile, $tenantId, [
                    'max_size' => 2097152, // 2MB
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'webp']
                ]);

                // Delete old avatar if exists
                if (!empty($user['avatar'])) {
                    $oldAvatarPath = $user['avatar'];
                    // Try to delete from old location (avatars folder)
                    $oldPath = FCPATH . ltrim($oldAvatarPath, '/');
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                    // Also try to delete from tenant files if it's there
                    if (strpos($oldAvatarPath, 'tenant_') !== false) {
                        try {
                            $storage->delete(basename($oldAvatarPath), $tenantId);
                        } catch (\Exception $e) {
                            // Ignore if file doesn't exist in storage
                        }
                    }
                }

                // Update user avatar - use path from StorageService
                $avatarPath = '/uploads/' . $uploadResult['path'];
                $db->table('users')
                    ->where('id', (int) $userId)
                    ->update([
                        'avatar' => $avatarPath,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                // Update session
                $authUser = session()->get('auth_user') ?? [];
                $authUser['avatar'] = $avatarPath;
                session()->set('auth_user', $authUser);

                // Set flashdata for success notification
                session()->setFlashdata('success', 'Foto profil berhasil diubah');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Foto profil berhasil diubah',
                    'avatar_url' => base_url($avatarPath),
                    'redirect' => base_url('admin/profile/overview')
                ]);
            } catch (\RuntimeException $e) {
                // StorageService validation errors
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin avatar update error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

