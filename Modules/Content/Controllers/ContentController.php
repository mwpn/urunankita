<?php

namespace Modules\Content\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Services as BaseServices;
use Modules\File\Services\StorageService;
use Modules\Content\Models\PageModel;
use Modules\Content\Models\BannerModel;
use Modules\Content\Models\ArticleModel;
use Modules\Content\Models\SponsorModel;
use Modules\Content\Models\MenuItemModel;
use Config\Database;

class ContentController extends BaseController
{
    protected function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Get user role from session and normalize for sidebar
     */
    protected function getUserRole(): string
    {
        $authUser = session()->get('auth_user');
        $userRole = $authUser['role'] ?? 'admin';
        
        // Normalize role for sidebar
        if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
            $userRole = 'admin';
        }
        
        return $userRole;
    }

    /**
     * Get tenant ID from session with fallback to user's tenant_id
     */
    protected function getTenantId(): ?int
    {
        $tenantId = session()->get('tenant_id');
        
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
        
        return $tenantId ? (int) $tenantId : null;
    }

    /**
     * Banner & Slider Management
     * GET /admin/content/banner
     */
    public function adminBanner()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $bannerModel = new BannerModel();
        $banners = $bannerModel->getAllBanners($platformTenantId);
        
        $data = [
            'title' => 'Banner & Slider',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'banners' => $banners,
        ];

        return view('Modules\\Content\\Views\\admin_banner', $data);
    }

    /**
     * Store Banner
     * POST /admin/content/banner/store
     */
    public function storeBanner()
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        // Handle image upload
        $imageFile = $this->request->getFile('image');
        if (!$imageFile || !$imageFile->isValid() || $imageFile->hasMoved()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gambar banner wajib diisi',
            ])->setStatusCode(400);
        }
        
        try {
            $storageService = new StorageService();
            $uploadResult = $storageService->upload($imageFile, $platformTenantId, [
                'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'max_size' => 10485760, // 10MB
            ]);
            
            $data = [
                'title' => $this->request->getPost('title'),
                'image' => '/uploads/' . $uploadResult['path'],
                'link' => $this->request->getPost('link') ?? null,
                'order' => (int) ($this->request->getPost('order') ?? 0),
                'active' => $this->request->getPost('active') ? 1 : 0,
                'tenant_id' => $platformTenantId,
            ];
            
            $bannerModel = new BannerModel();
            $bannerId = $bannerModel->insert($data);
            
            if ($bannerId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Banner berhasil disimpan',
                    'data' => ['id' => $bannerId],
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan banner: ' . implode(', ', $bannerModel->errors()),
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Update Banner
     * POST /admin/content/banner/update/{id}
     */
    public function updateBanner($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $bannerModel = new BannerModel();
        $banner = $bannerModel->find($id);
        
        if (!$banner || $banner['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Banner tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'link' => $this->request->getPost('link') ?? null,
            'order' => (int) ($this->request->getPost('order') ?? 0),
            'active' => $this->request->getPost('active') ? 1 : 0,
        ];
        
        // Handle image upload if provided
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($imageFile, $platformTenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size' => 10485760, // 10MB
                ]);
                
                $data['image'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal upload gambar: ' . $e->getMessage(),
                ])->setStatusCode(400);
            }
        }
        
        try {
            $bannerModel->update($id, $data);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Banner berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete Banner
     * POST /admin/content/banner/delete/{id}
     */
    public function deleteBanner($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $bannerModel = new BannerModel();
        $banner = $bannerModel->find($id);
        
        if (!$banner || $banner['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Banner tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            $bannerModel->delete($id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Banner berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Articles/Blog Management
     * GET /admin/content/articles
     */
    public function adminArticles()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $articleModel = new ArticleModel();
        $articles = $articleModel->getAllArticles($platformTenantId);
        
        $data = [
            'title' => 'Artikel/Blog',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'articles' => $articles,
        ];

        return view('Modules\\Content\\Views\\admin_articles', $data);
    }

    /**
     * Admin Create Article Page
     * GET /admin/content/articles/create
     */
    public function adminCreateArticle()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $data = [
            'title' => 'Tambah Artikel',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'article' => null,
        ];

        return view('Modules\\Content\\Views\\admin_article_form', $data);
    }

    /**
     * Admin Edit Article Page
     * GET /admin/content/articles/{id}/edit
     */
    public function adminEditArticle($id)
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $articleModel = new ArticleModel();
        $article = $articleModel->find($id);
        
        if (!$article || $article['tenant_id'] != $platformTenantId) {
            return redirect()->to('admin/content/articles')->with('error', 'Artikel tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Artikel',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'article' => $article,
        ];

        return view('Modules\\Content\\Views\\admin_article_form', $data);
    }

    /**
     * Pages Management
     * GET /admin/content/pages
     */
    public function adminPages()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $pageModel = new PageModel();
        $pages = $pageModel->getAllPages($platformTenantId);
        
        $data = [
            'title' => 'Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'pages' => $pages,
        ];

        return view('Modules\\Content\\Views\\admin_pages', $data);
    }

    /**
     * Admin Create Page
     * GET /admin/content/pages/create
     */
    public function adminCreatePage()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $data = [
            'title' => 'Tambah Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'page' => null,
        ];

        return view('Modules\\Content\\Views\\admin_page_form', $data);
    }

    /**
     * Admin Edit Page
     * GET /admin/content/pages/{id}/edit
     */
    public function adminEditPage($id)
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $platformTenantId) {
            return redirect()->to('admin/content/pages')->with('error', 'Halaman tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'page' => $page,
        ];

        return view('Modules\\Content\\Views\\admin_page_form', $data);
    }

    /**
     * Store Page
     * POST /admin/content/pages/store
     */
    public function storePage()
    {
        $db = Database::connect();
        $isAjax = $this->request->isAJAX();
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('admin/content/pages')->with('error', 'Platform tenant tidak ditemukan');
        }
        
        $platformTenantId = (int) $platform['id'];
        
        // Check if slug already exists
        $pageModel = new PageModel();
        $existingPage = $pageModel->where('slug', $this->request->getPost('slug'))
            ->where('tenant_id', $platformTenantId)
            ->first();
        
        if ($existingPage) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Slug sudah digunakan. Gunakan slug yang berbeda.',
                ])->setStatusCode(400);
            }
            return redirect()->back()->withInput()->with('error', 'Slug sudah digunakan. Gunakan slug yang berbeda.');
        }
        
        // Handle checkbox - if not checked, it won't be in POST data
        $published = $this->request->getPost('published');
        $publishedValue = ($published !== null && $published !== false && $published !== '') ? 1 : 0;
        
        $data = [
            'title' => $this->request->getPost('title'),
            'slug' => $this->request->getPost('slug'),
            'content' => $this->request->getPost('content'),
            'description' => $this->request->getPost('description') ?? null,
            'badge_text' => $this->request->getPost('badge_text') ?? null,
            'subtitle' => $this->request->getPost('subtitle') ?? null,
            'sidebar_content' => $this->request->getPost('sidebar_content') ?? null,
            'published' => $publishedValue,
            'tenant_id' => $platformTenantId,
        ];
        
        try {
            $pageId = $pageModel->insert($data);
            
            if ($pageId) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Halaman berhasil disimpan',
                        'data' => ['id' => $pageId],
                    ]);
                }
                return redirect()->to('admin/content/pages')->with('success', 'Halaman berhasil disimpan');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menyimpan halaman: ' . implode(', ', $pageModel->errors()),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan halaman: ' . implode(', ', $pageModel->errors()));
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete Page (Admin)
     * POST /admin/content/pages/delete/{id}
     */
    public function deletePage($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Halaman tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            $pageModel->delete($id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Halaman berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Update Page
     * POST /admin/content/pages/update/{id}
     */
    public function updatePage($id)
    {
        $db = Database::connect();
        $isAjax = $this->request->isAJAX();
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('admin/content/pages')->with('error', 'Platform tenant tidak ditemukan');
        }
        
        $platformTenantId = (int) $platform['id'];
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $platformTenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Halaman tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->to('admin/content/pages')->with('error', 'Halaman tidak ditemukan');
        }
        
        // Handle checkbox - if not checked, it won't be in POST data
        // Checkbox sends "on" when checked, or nothing when unchecked
        $published = $this->request->getPost('published');
        $publishedValue = ($published !== null && $published !== false && $published !== '') ? 1 : 0;
        
        $data = [
            'title' => $this->request->getPost('title'),
            'slug' => $this->request->getPost('slug'),
            'content' => $this->request->getPost('content'),
            'description' => $this->request->getPost('description') ?? null,
            'badge_text' => $this->request->getPost('badge_text') ?? null,
            'subtitle' => $this->request->getPost('subtitle') ?? null,
            'sidebar_content' => $this->request->getPost('sidebar_content') ?? null,
            'published' => $publishedValue,
        ];
        
        // Log for debugging
        log_message('debug', 'Published checkbox value: ' . ($published !== null ? 'checked' : 'unchecked') . ' -> ' . $publishedValue);
        
        // Log content for debugging
        log_message('debug', 'Updating page ID: ' . $id);
        log_message('debug', 'Content length: ' . strlen($data['content'] ?? ''));
        
        try {
            // Use direct database update to ensure content is saved correctly
            $db->table('pages')
                ->where('id', $id)
                ->where('tenant_id', $platformTenantId) // Double check for security
                ->update($data);
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Halaman berhasil diperbarui',
                ]);
            }
            return redirect()->to('admin/content/pages')->with('success', 'Halaman berhasil diperbarui');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * View Public Page
     * GET /page/{slug}
     * Routes based on subdomain - main domain shows platform pages, subdomain shows tenant pages
     */
    public function viewPage($slug)
    {
        $db = Database::connect();
        
        // Get subdomain info from request directly (more reliable than session)
        $host = $this->request->getUri()->getHost();
        $hostParts = explode('.', $host);
        $isSubdomainFromHost = count($hostParts) > 2;
        
        // Check if first part is not common subdomain
        if ($isSubdomainFromHost) {
            $firstPart = strtolower($hostParts[0]);
            if (in_array($firstPart, ['www', 'api', 'admin', 'app'])) {
                $isSubdomainFromHost = false; // Not a tenant subdomain
            }
        }
        
        // Get tenant info from session
        $tenantId = session()->get('tenant_id');
        $tenantSlug = session()->get('tenant_slug');
        $isSubdomain = $isSubdomainFromHost;
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        // If host is subdomain, ALWAYS try to resolve tenant from host first
        if ($isSubdomainFromHost && !$tenantId) {
            $subdomain = $hostParts[0];
            if (!in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app'])) {
                $tenant = $db->table('tenants')
                    ->where('slug', $subdomain)
                    ->where('status', 'active')
                    ->get()
                    ->getRowArray();
                if ($tenant) {
                    $tenantId = (int) $tenant['id'];
                    $tenantSlug = $tenant['slug'];
                    session()->set('tenant_id', $tenantId);
                    session()->set('tenant_slug', $tenantSlug);
                    session()->set('is_subdomain', true);
                } else {
                    // Tenant not found for this subdomain
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tenant tidak ditemukan');
                }
            } else {
                $isSubdomain = false;
            }
        }
        
        $pageModel = new PageModel();
        $page = null;
        
        // Determine current host for loop prevention
        $currentHost = $this->request->getUri()->getHost();
        $baseDomain = env('app.baseDomain', 'urunankita.test');
        
        // If subdomain, get tenant page
        if ($isSubdomain && $tenantId) {
            // Use direct query to bypass BaseModel auto-filtering
            $page = $db->table('pages')
                ->where('slug', $slug)
                ->where('tenant_id', $tenantId)
                ->where('published', 1)
                ->where('deleted_at IS NULL')
                ->get()
                ->getRowArray();
            
            if (!$page) {
                // Page not found for this tenant, try platform page
                $page = $db->table('pages')
                    ->where('slug', $slug)
                    ->where('tenant_id', $platformTenantId)
                    ->where('published', 1)
                    ->where('deleted_at IS NULL')
                    ->get()
                    ->getRowArray();
                
                if (!$page) {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan');
                }
            }
            
            // Get tenant settings (with fallback to platform)
            $settingService = BaseServices::setting();
            $settings = [];
            $settingKeys = [
                'site_name',
                'site_tagline',
                'site_description',
                'site_logo',
                'site_favicon',
                'site_email',
                'site_phone',
                'site_address',
                'site_facebook',
                'site_instagram',
                'site_twitter',
                'frontend_font',
                'frontend_font_weights',
            ];
            
            foreach ($settingKeys as $key) {
                $settings[$key] = $settingService->getTenant($key, null, $tenantId);
            }
            
            // Get tenant info
            $tenantModel = new \Modules\Tenant\Models\TenantModel();
            $tenant = $tenantModel->findWithBankAccounts($tenantId);
            
            return view('Modules\\Public\\Views\\page', [
                'page' => $page,
                'settings' => $settings,
                'is_main_domain' => false,
                'tenant' => $tenant,
            ]);
        }
        
        // Main domain - only show platform pages
        $page = $db->table('pages')
            ->where('slug', $slug)
            ->where('tenant_id', $platformTenantId)
            ->where('published', 1)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
        
        if (!$page) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan');
        }
        
        // Get platform settings
        $settingService = BaseServices::setting();
        $settings = [];
        $settingKeys = [
            'site_name',
            'site_tagline',
            'site_description',
            'site_logo',
            'site_favicon',
            'site_email',
            'site_phone',
            'site_address',
            'site_facebook',
            'site_instagram',
            'site_twitter',
            'frontend_font',
            'frontend_font_weights',
        ];
        
        foreach ($settingKeys as $key) {
            $settings[$key] = $settingService->get($key, null, 'global', null);
        }
        
        return view('Modules\\Public\\Views\\page', [
            'page' => $page,
            'settings' => $settings,
            'is_main_domain' => true,
        ]);
    }

    /**
     * FAQ Management
     * GET /admin/content/faq
     */
    public function adminFaq()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $data = [
            'title' => 'FAQ Management',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
        ];

        return view('Modules\\Content\\Views\\admin_faq', $data);
    }

    /**
     * Testimonials Management
     * GET /admin/content/testimonials
     */
    public function adminTestimonials()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $data = [
            'title' => 'Testimoni',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
        ];

        return view('Modules\\Content\\Views\\admin_testimonials', $data);
    }

    /**
     * Newsletter Management
     * GET /admin/content/newsletter
     */
    public function adminNewsletter()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $data = [
            'title' => 'Newsletter',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
        ];

        return view('Modules\\Content\\Views\\admin_newsletter', $data);
    }

    /**
     * Store Article
     * POST /admin/content/articles/store
     */
    public function storeArticle()
    {
        $db = Database::connect();
        $isAjax = $this->request->isAJAX();
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('admin/content/articles')->with('error', 'Platform tenant tidak ditemukan');
        }
        
        $platformTenantId = (int) $platform['id'];
        
        // Generate slug from title
        $title = $this->request->getPost('title');
        $slug = url_title($title, '-', true);
        
        // Check if slug already exists
        $articleModel = new ArticleModel();
        $existingArticle = $articleModel->where('slug', $slug)
            ->where('tenant_id', $platformTenantId)
            ->first();
        
        if ($existingArticle) {
            $slug = $slug . '-' . time();
        }
        
        $authUser = session()->get('auth_user');
        
        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'category' => $this->request->getPost('category') ?? null,
            'excerpt' => $this->request->getPost('excerpt') ?? null,
            'campaign_id' => $this->request->getPost('campaign_id') ? (int) $this->request->getPost('campaign_id') : null,
            'published' => $this->request->getPost('published') ? 1 : 0,
            'author_id' => $authUser['id'] ?? null,
            'tenant_id' => $platformTenantId,
        ];
        
        // Handle image upload
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($imageFile, $platformTenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size' => 5242880, // 5MB
                ]);
                
                $data['image'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal upload gambar: ' . $e->getMessage(),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal upload gambar: ' . $e->getMessage());
            }
        }
        
        try {
            $articleId = $articleModel->insert($data);
            
            if ($articleId) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Artikel berhasil disimpan',
                        'data' => ['id' => $articleId],
                    ]);
                }
                return redirect()->to('admin/content/articles')->with('success', 'Artikel berhasil disimpan');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menyimpan artikel: ' . implode(', ', $articleModel->errors()),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan artikel: ' . implode(', ', $articleModel->errors()));
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update Article
     * POST /admin/content/articles/update/{id}
     */
    public function updateArticle($id)
    {
        $db = Database::connect();
        $isAjax = $this->request->isAJAX();
        
        // Get platform tenant ID
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Platform tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('admin/content/articles')->with('error', 'Platform tenant tidak ditemukan');
        }
        
        $platformTenantId = (int) $platform['id'];
        
        $articleModel = new ArticleModel();
        $article = $articleModel->find($id);
        
        if (!$article || $article['tenant_id'] != $platformTenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Artikel tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->to('admin/content/articles')->with('error', 'Artikel tidak ditemukan');
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'category' => $this->request->getPost('category') ?? null,
            'excerpt' => $this->request->getPost('excerpt') ?? null,
            'campaign_id' => $this->request->getPost('campaign_id') ? (int) $this->request->getPost('campaign_id') : null,
            'published' => $this->request->getPost('published') ? 1 : 0,
        ];
        
        // Handle image upload if provided
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($imageFile, $platformTenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size' => 5242880, // 5MB
                ]);
                
                $data['image'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal upload gambar: ' . $e->getMessage(),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal upload gambar: ' . $e->getMessage());
            }
        }
        
        try {
            $articleModel->update($id, $data);
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Artikel berhasil diperbarui',
                ]);
            }
            return redirect()->to('admin/content/articles')->with('success', 'Artikel berhasil diperbarui');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete Article
     * POST /admin/content/articles/delete/{id}
     */
    public function deleteArticle($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $articleModel = new ArticleModel();
        $article = $articleModel->find($id);
        
        if (!$article || $article['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Artikel tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            $articleModel->delete($id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Artikel berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Tenant Pages Management
     * GET /tenant/content/pages
     */
    public function tenantPages()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $pageModel = new PageModel();
        $pages = $pageModel->getAllPages($tenantId);
        
        $data = [
            'title' => 'Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'pages' => $pages,
        ];

        return view('Modules\\Content\\Views\\tenant_pages', $data);
    }

    /**
     * Tenant Create Page
     * GET /tenant/content/pages/create
     */
    public function tenantCreatePage()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $data = [
            'title' => 'Tambah Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'page' => null,
        ];

        return view('Modules\\Content\\Views\\tenant_page_form', $data);
    }

    /**
     * Tenant Edit Page
     * GET /tenant/content/pages/{id}/edit
     */
    public function tenantEditPage($id)
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $tenantId) {
            return redirect()->to('tenant/content/pages')->with('error', 'Halaman tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Halaman',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'page' => $page,
        ];

        return view('Modules\\Content\\Views\\tenant_page_form', $data);
    }

    /**
     * Tenant Articles Management
     * GET /tenant/content/articles
     */
    public function tenantArticles()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        // Get tenant slug for generating article URLs
        $tenantSlug = session()->get('tenant_slug');
        if (!$tenantSlug) {
            $db = Database::connect();
            $tenant = $db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
            $tenantSlug = $tenant['slug'] ?? null;
            if ($tenantSlug) {
                session()->set('tenant_slug', $tenantSlug);
            }
        }
        
        $articleModel = new ArticleModel();
        $articles = $articleModel->getAllArticles($tenantId);
        
        $data = [
            'title' => 'Artikel/Blog',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'articles' => $articles,
            'tenant_slug' => $tenantSlug,
        ];

        return view('Modules\\Content\\Views\\tenant_articles', $data);
    }

    /**
     * Tenant Create Article Page
     * GET /tenant/content/articles/create
     */
    public function tenantCreateArticle()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $data = [
            'title' => 'Tambah Artikel',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'article' => null,
        ];

        return view('Modules\\Content\\Views\\tenant_article_form', $data);
    }

    /**
     * Tenant Edit Article Page
     * GET /tenant/content/articles/{id}/edit
     */
    public function tenantEditArticle($id)
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $articleModel = new ArticleModel();
        $article = $articleModel->find($id);
        
        if (!$article || $article['tenant_id'] != $tenantId) {
            return redirect()->to('tenant/content/articles')->with('error', 'Artikel tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Artikel',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'article' => $article,
        ];

        return view('Modules\\Content\\Views\\tenant_article_form', $data);
    }

    /**
     * Store Tenant Page
     * POST /tenant/content/pages/store
     */
    public function storeTenantPage()
    {
        $tenantId = $this->getTenantId();
        $isAjax = $this->request->isAJAX();
        
        if (!$tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('tenant/content/pages')->with('error', 'Tenant tidak ditemukan');
        }
        
        // Check if slug already exists
        $pageModel = new PageModel();
        $existingPage = $pageModel->where('slug', $this->request->getPost('slug'))
            ->where('tenant_id', $tenantId)
            ->first();
        
        if ($existingPage) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Slug sudah digunakan. Gunakan slug yang berbeda.',
                ])->setStatusCode(400);
            }
            return redirect()->back()->withInput()->with('error', 'Slug sudah digunakan. Gunakan slug yang berbeda.');
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'slug' => $this->request->getPost('slug'),
            'content' => $this->request->getPost('content'),
            'description' => $this->request->getPost('description') ?? null,
            'badge_text' => $this->request->getPost('badge_text') ?? null,
            'subtitle' => $this->request->getPost('subtitle') ?? null,
            'sidebar_content' => $this->request->getPost('sidebar_content') ?? null,
            'published' => $this->request->getPost('published') ? 1 : 0,
            'tenant_id' => $tenantId,
        ];
        
        try {
            $pageId = $pageModel->insert($data);
            
            if ($pageId) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Halaman berhasil disimpan',
                        'data' => ['id' => $pageId],
                    ]);
                }
                return redirect()->to('tenant/content/pages')->with('success', 'Halaman berhasil disimpan');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menyimpan halaman: ' . implode(', ', $pageModel->errors()),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan halaman: ' . implode(', ', $pageModel->errors()));
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store Tenant Article
     * POST /tenant/content/articles/store
     */
    public function storeTenantArticle()
    {
        $tenantId = $this->getTenantId();
        $isAjax = $this->request->isAJAX();
        
        if (!$tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('tenant/content/articles')->with('error', 'Tenant tidak ditemukan');
        }
        
        // Generate slug from title
        $title = $this->request->getPost('title');
        $slug = url_title($title, '-', true);
        
        // Check if slug already exists
        $articleModel = new ArticleModel();
        $existingArticle = $articleModel->where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();
        
        if ($existingArticle) {
            $slug = $slug . '-' . time();
        }
        
        $authUser = session()->get('auth_user');
        
        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'category' => $this->request->getPost('category') ?? null,
            'excerpt' => $this->request->getPost('excerpt') ?? null,
            'campaign_id' => $this->request->getPost('campaign_id') ? (int) $this->request->getPost('campaign_id') : null,
            'published' => $this->request->getPost('published') ? 1 : 0,
            'author_id' => $authUser['id'] ?? null,
            'tenant_id' => $tenantId,
        ];
        
        // Handle image upload
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($imageFile, $tenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size' => 5242880, // 5MB
                ]);
                
                $data['image'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal upload gambar: ' . $e->getMessage(),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal upload gambar: ' . $e->getMessage());
            }
        }
        
        try {
            // Use direct database insert to ensure tenant_id is saved correctly
            // This bypasses BaseModel's auto-set which might use session tenant_id
            $db = Database::connect();
            
            // Add timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $db->table('articles')->insert($data);
            $articleId = $db->insertID();
            
            if ($articleId) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Artikel berhasil disimpan',
                        'data' => ['id' => $articleId],
                    ]);
                }
                return redirect()->to('tenant/content/articles')->with('success', 'Artikel berhasil disimpan');
            } else {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menyimpan artikel',
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan artikel');
            }
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update Tenant Page
     * POST /tenant/content/pages/update/{id}
     */
    public function updateTenantPage($id)
    {
        $tenantId = $this->getTenantId();
        $isAjax = $this->request->isAJAX();
        
        if (!$tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('tenant/content/pages')->with('error', 'Tenant tidak ditemukan');
        }
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Halaman tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->to('tenant/content/pages')->with('error', 'Halaman tidak ditemukan');
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'slug' => $this->request->getPost('slug'),
            'content' => $this->request->getPost('content'),
            'description' => $this->request->getPost('description') ?? null,
            'badge_text' => $this->request->getPost('badge_text') ?? null,
            'subtitle' => $this->request->getPost('subtitle') ?? null,
            'sidebar_content' => $this->request->getPost('sidebar_content') ?? null,
            'published' => $this->request->getPost('published') ? 1 : 0,
            // Explicitly ensure tenant_id cannot be changed - use verified tenant_id
            'tenant_id' => $tenantId,
        ];
        
        try {
            // Use direct database update to ensure tenant_id is not changed and bypass BaseModel auto-filtering
            $db = Database::connect();
            $db->table('pages')
                ->where('id', $id)
                ->where('tenant_id', $tenantId) // Double check for security
                ->update($data);
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Halaman berhasil diperbarui',
                ]);
            }
            return redirect()->to('tenant/content/pages')->with('success', 'Halaman berhasil diperbarui');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update Tenant Article
     * POST /tenant/content/articles/update/{id}
     */
    public function updateTenantArticle($id)
    {
        $tenantId = $this->getTenantId();
        $isAjax = $this->request->isAJAX();
        
        if (!$tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('tenant/content/articles')->with('error', 'Tenant tidak ditemukan');
        }
        
        $articleModel = new ArticleModel();
        $article = $articleModel->find($id);
        
        if (!$article || $article['tenant_id'] != $tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Artikel tidak ditemukan',
                ])->setStatusCode(404);
            }
            return redirect()->to('tenant/content/articles')->with('error', 'Artikel tidak ditemukan');
        }
        
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'category' => $this->request->getPost('category') ?? null,
            'excerpt' => $this->request->getPost('excerpt') ?? null,
            'campaign_id' => $this->request->getPost('campaign_id') ? (int) $this->request->getPost('campaign_id') : null,
            'published' => $this->request->getPost('published') ? 1 : 0,
        ];
        
        // Handle image upload if provided
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($imageFile, $tenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size' => 5242880, // 5MB
                ]);
                
                $data['image'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                if ($isAjax) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal upload gambar: ' . $e->getMessage(),
                    ])->setStatusCode(400);
                }
                return redirect()->back()->withInput()->with('error', 'Gagal upload gambar: ' . $e->getMessage());
            }
        }
        
        try {
            $articleModel->update($id, $data);
            
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Artikel berhasil diperbarui',
                ]);
            }
            return redirect()->to('tenant/content/articles')->with('success', 'Artikel berhasil diperbarui');
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ])->setStatusCode(500);
            }
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete Tenant Page
     * POST /tenant/content/pages/delete/{id}
     */
    public function deleteTenantPage($id)
    {
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant tidak ditemukan',
            ])->setStatusCode(400);
        }
        
        $pageModel = new PageModel();
        $page = $pageModel->find($id);
        
        if (!$page || $page['tenant_id'] != $tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Halaman tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            $pageModel->delete($id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Halaman berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Admin Menu Management
     * GET /admin/content/menu
     * GET /admin/content/menu/footer
     */
    public function adminMenu()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        // Get location from query string (default: header)
        $location = $this->request->getGet('location') ?? 'header';
        if (!in_array($location, ['header', 'footer'])) {
            $location = 'header';
        }
        
        $menuModel = new MenuItemModel();
        $menuItems = $menuModel->getMenuItems(null, $location); // null untuk platform menu
        
        // Jika belum ada menu, gunakan default (hanya untuk header)
        if (empty($menuItems) && $location === 'header') {
            $defaultMenus = $menuModel->getDefaultMenuItems();
            $menuItems = $defaultMenus;
        }
        
        // Get published pages for dropdown
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $pageModel = new PageModel();
        $pages = $pageModel->getAllPages($platformTenantId);
        $publishedPages = array_filter($pages, function($page) {
            return !empty($page['published']) && empty($page['deleted_at']);
        });
        
        $data = [
            'title' => $location === 'footer' ? 'Pengaturan Menu Footer' : 'Pengaturan Menu Header',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'menuItems' => $menuItems,
            'pages' => $publishedPages,
            'location' => $location,
        ];

        return view('Modules\\Content\\Views\\admin_menu', $data);
    }

    /**
     * Tenant Menu Management
     * GET /tenant/content/menu
     * GET /tenant/content/menu/footer
     */
    public function tenantMenu()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        // Get location from query string (default: header)
        $location = $this->request->getGet('location') ?? 'header';
        if (!in_array($location, ['header', 'footer'])) {
            $location = 'header';
        }
        
        $menuModel = new MenuItemModel();
        $menuItems = $menuModel->getMenuItems($tenantId, $location);
        
        // Jika belum ada menu, gunakan default (hanya untuk header)
        if (empty($menuItems) && $location === 'header') {
            $defaultMenus = $menuModel->getDefaultMenuItems();
            $menuItems = $defaultMenus;
        }
        
        // Get published pages for dropdown
        $pageModel = new PageModel();
        $pages = $pageModel->getAllPages($tenantId);
        $publishedPages = array_filter($pages, function($page) {
            return !empty($page['published']) && empty($page['deleted_at']);
        });
        
        $data = [
            'title' => $location === 'footer' ? 'Pengaturan Menu Footer' : 'Pengaturan Menu Header',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'User',
            'menuItems' => $menuItems,
            'tenantId' => $tenantId,
            'pages' => $publishedPages,
            'location' => $location,
        ];

        return view('Modules\\Content\\Views\\tenant_menu', $data);
    }

    /**
     * Store Menu Items (Admin)
     * POST /admin/content/menu/store
     */
    public function storeAdminMenu()
    {
        $menuModel = new MenuItemModel();
        $isAjax = $this->request->isAJAX();
        
        // Get location from POST (default: header)
        $location = $this->request->getPost('location') ?? 'header';
        if (!in_array($location, ['header', 'footer'])) {
            $location = 'header';
        }
        
        // Hapus menu lama untuk platform dengan location yang sama
        $menuModel->where('tenant_id IS NULL')
            ->where('location', $location)
            ->delete();
        
        // Simpan menu baru
        $menuItems = $this->request->getPost('menu_items') ?? [];
        $savedItems = [];
        
        // First pass: insert all items and track IDs
        $tempIdMap = []; // Maps temporary IDs to real database IDs
        $itemsToSave = [];
        
        foreach ($menuItems as $index => $item) {
            if (empty($item['label']) || empty($item['url'])) {
                continue;
            }
            
            $tempId = $item['id'] ?? null;
            $parentId = !empty($item['parent_id']) ? $item['parent_id'] : null;
            
            $data = [
                'tenant_id' => null, // Platform menu
                'location' => $location,
                'parent_id' => null, // Will be set in second pass
                'label' => $item['label'],
                'url' => $item['url'],
                'icon' => $item['icon'] ?? null,
                'order' => $index + 1,
                'is_active' => isset($item['is_active']) ? 1 : 0,
                'is_external' => isset($item['is_external']) ? 1 : 0,
            ];
            
            $menuModel->insert($data);
            $realId = $menuModel->getInsertID();
            $savedItems[] = $realId;
            
            // Store mapping for parent_id resolution
            if ($tempId) {
                $tempIdMap[$tempId] = $realId;
            }
            $itemsToSave[] = [
                'id' => $realId,
                'temp_id' => $tempId,
                'parent_temp_id' => $parentId,
            ];
        }
        
        // Second pass: update parent_id with real IDs
        foreach ($itemsToSave as $item) {
            if ($item['parent_temp_id'] && isset($tempIdMap[$item['parent_temp_id']])) {
                $menuModel->update($item['id'], [
                    'parent_id' => $tempIdMap[$item['parent_temp_id']],
                ]);
            }
        }
        
        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu berhasil disimpan',
                'data' => $savedItems,
            ]);
        }
        return redirect()->to('admin/content/menu')->with('success', 'Menu berhasil disimpan');
    }

    /**
     * Store Menu Items (Tenant)
     * POST /tenant/content/menu/store
     */
    public function storeTenantMenu()
    {
        $tenantId = $this->getTenantId();
        $isAjax = $this->request->isAJAX();
        
        if (!$tenantId) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant tidak ditemukan',
                ])->setStatusCode(400);
            }
            return redirect()->to('tenant/dashboard')->with('error', 'Tenant tidak ditemukan');
        }
        
        $menuModel = new MenuItemModel();
        
        // Get location from POST (default: header)
        $location = $this->request->getPost('location') ?? 'header';
        if (!in_array($location, ['header', 'footer'])) {
            $location = 'header';
        }
        
        // Hapus menu lama untuk tenant dengan location yang sama
        $menuModel->where('tenant_id', $tenantId)
            ->where('location', $location)
            ->delete();
        
        // Simpan menu baru
        $menuItems = $this->request->getPost('menu_items') ?? [];
        $savedItems = [];
        
        foreach ($menuItems as $index => $item) {
            if (empty($item['label']) || empty($item['url'])) {
                continue;
            }
            
            $data = [
                'tenant_id' => $tenantId,
                'location' => $location,
                'label' => $item['label'],
                'url' => $item['url'],
                'icon' => $item['icon'] ?? null,
                'order' => $index + 1,
                'is_active' => isset($item['is_active']) ? 1 : 0,
                'is_external' => isset($item['is_external']) ? 1 : 0,
            ];
            
            $menuModel->insert($data);
            $savedItems[] = $menuModel->getInsertID();
        }
        
        if ($isAjax) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu berhasil disimpan',
                'data' => $savedItems,
            ]);
        }
        $redirectUrl = $location === 'footer' 
            ? 'tenant/content/menu?location=footer' 
            : 'tenant/content/menu';
        return redirect()->to($redirectUrl)->with('success', 'Menu berhasil disimpan');
    }

    /**
     * Delete Tenant Article
     * POST /tenant/content/articles/delete/{id}
     */
    public function deleteTenantArticle($id)
    {
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant tidak ditemukan',
            ])->setStatusCode(400);
        }
        
        $db = Database::connect();
        
        // Use direct query to bypass BaseModel auto-filtering
        $article = $db->table('articles')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
        
        if (!$article) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Artikel tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            // Use direct database update for soft delete to bypass BaseModel auto-filtering
            $db->table('articles')
                ->where('id', $id)
                ->where('tenant_id', $tenantId) // Double check tenant_id for security
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Artikel berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Sponsor Management
     * GET /admin/content/sponsors
     */
    public function adminSponsors()
    {
        $authUser = session()->get('auth_user');
        $userRole = $this->getUserRole();
        
        $db = Database::connect();
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platform ? (int) $platform['id'] : null;
        
        $sponsorModel = new SponsorModel();
        $sponsors = $sponsorModel->getAllSponsors($platformTenantId);
        
        $data = [
            'title' => 'Logo Sponsor',
            'userRole' => $userRole,
            'user_name' => $authUser['name'] ?? 'Admin',
            'sponsors' => $sponsors,
        ];

        return view('Modules\\Content\\Views\\admin_sponsors', $data);
    }

    /**
     * Store Sponsor
     * POST /admin/content/sponsors/store
     */
    public function storeSponsor()
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        // Handle logo upload (only required for new sponsor, not for update)
        $logoFile = $this->request->getFile('logo');
        $logoPath = null;
        
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($logoFile, $platformTenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                    'max_size' => 5242880, // 5MB
                ]);
                $logoPath = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal upload logo: ' . $e->getMessage(),
                ])->setStatusCode(400);
            }
        } elseif (!$logoFile || !$logoFile->isValid()) {
            // Logo is required for new sponsor
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Logo sponsor wajib diisi',
            ])->setStatusCode(400);
        }
        
        try {
            if (!$logoPath) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Logo sponsor wajib diisi',
                ])->setStatusCode(400);
            }
            
            $activeValue = $this->request->getPost('active');
            $data = [
                'name' => $this->request->getPost('name'),
                'logo' => $logoPath,
                'website' => $this->request->getPost('website') ?? null,
                'order' => (int) ($this->request->getPost('order') ?? 0),
                'active' => ($activeValue === '1' || $activeValue === 'on' || $activeValue === true) ? 1 : 0,
                'tenant_id' => $platformTenantId,
            ];
            
            $sponsorModel = new SponsorModel();
            $sponsorId = $sponsorModel->insert($data);
            
            if ($sponsorId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Sponsor berhasil disimpan',
                    'data' => ['id' => $sponsorId],
                ]);
            } else {
                $errors = $sponsorModel->errors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Gagal menyimpan sponsor';
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $errorMessage,
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Get Sponsor
     * GET /admin/content/sponsors/get/{id}
     */
    public function getSponsor($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $sponsorModel = new SponsorModel();
        $sponsor = $sponsorModel->find($id);
        
        if (!$sponsor || $sponsor['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sponsor tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $sponsor,
        ]);
    }

    /**
     * Update Sponsor
     * POST /admin/content/sponsors/update/{id}
     */
    public function updateSponsor($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $sponsorModel = new SponsorModel();
        $sponsor = $sponsorModel->find($id);
        
        if (!$sponsor || $sponsor['tenant_id'] != $platformTenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sponsor tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        $data = [
            'name' => $this->request->getPost('name'),
            'website' => $this->request->getPost('website') ?? null,
            'order' => (int) ($this->request->getPost('order') ?? 0),
            'active' => $this->request->getPost('active') ? 1 : 0,
        ];
        
        // Handle logo upload if provided
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            try {
                $storageService = new StorageService();
                $uploadResult = $storageService->upload($logoFile, $platformTenantId, [
                    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                    'max_size' => 5242880, // 5MB
                ]);
                
                $data['logo'] = '/uploads/' . $uploadResult['path'];
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal upload logo: ' . $e->getMessage(),
                ])->setStatusCode(400);
            }
        }
        
        try {
            $sponsorModel->update($id, $data);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sponsor berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete Sponsor
     * POST /admin/content/sponsors/delete/{id}
     */
    public function deleteSponsor($id)
    {
        $db = Database::connect();
        
        // Get platform tenant ID with fallback
        $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        if (!$platform) {
            // Fallback to tenant ID 1 if platform tenant doesn't exist
            $platformTenantId = 1;
            log_message('warning', 'Platform tenant not found, using tenant ID 1 as fallback');
        } else {
            $platformTenantId = (int) $platform['id'];
        }
        
        $sponsorModel = new SponsorModel();
        
        // Use direct query to find sponsor (including soft-deleted ones)
        $sponsor = $db->table('sponsors')
            ->where('id', (int) $id)
            ->where('tenant_id', $platformTenantId)
            ->get()
            ->getRowArray();
        
        if (!$sponsor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sponsor tidak ditemukan',
            ])->setStatusCode(404);
        }
        
        try {
            // Force delete (hard delete) to permanently remove the record from database
            $result = $db->table('sponsors')
                ->where('id', (int) $id)
                ->where('tenant_id', $platformTenantId) // Double check for security
                ->delete();
            
            if (!$result) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menghapus sponsor',
                ])->setStatusCode(500);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sponsor berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}

