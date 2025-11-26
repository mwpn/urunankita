<?php

namespace Modules\Announcement\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\Announcement\Services\AnnouncementService;

class AnnouncementController extends BaseController
{
    protected AnnouncementService $announcementService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->announcementService = new AnnouncementService();
    }

    /**
     * Admin: List announcements page
     * GET /admin/announcements
     */
    public function index()
    {
        $isPublished = $this->request->getGet('is_published');
        $type = $this->request->getGet('type');
        
        $filters = [];
        if ($isPublished !== null) {
            $filters['is_published'] = (int) $isPublished;
        }
        if ($type) {
            $filters['type'] = $type;
        }

        $announcements = $this->announcementService->getAll($filters);

        $data = [
            'title' => 'Pengumuman - Admin Dashboard',
            'page_title' => 'Pengumuman Platform',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'announcements' => $announcements,
            'filters' => $filters,
        ];

        return view('Modules\\Announcement\\Views\\admin_index', $data);
    }

    /**
     * Admin: Create announcement page
     * GET /admin/announcements/create
     */
    public function createPage()
    {
        $data = [
            'title' => 'Buat Pengumuman - Admin Dashboard',
            'page_title' => 'Buat Pengumuman',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'announcement' => null,
        ];

        return view('Modules\\Announcement\\Views\\admin_form', $data);
    }

    /**
     * Admin: Store announcement
     * POST /admin/announcements/store
     */
    public function store()
    {
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'type' => $this->request->getPost('type') ?? 'info',
            'priority' => $this->request->getPost('priority') ?? 'normal',
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
            'expires_at' => $this->request->getPost('expires_at') ?: null,
            'created_by' => auth_user()['id'] ?? null,
        ];

        if (empty($data['title']) || empty($data['content'])) {
            return redirect()->back()->withInput()->with('error', 'Judul dan konten wajib diisi');
        }

        try {
            $id = $this->announcementService->create($data);
            if ($id) {
                return redirect()->to('/admin/announcements')->with('success', 'Pengumuman berhasil dibuat');
            }
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pengumuman');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Edit announcement page
     * GET /admin/announcements/{id}/edit
     */
    public function edit(int $id)
    {
        $announcement = $this->announcementService->getById($id);
        if (!$announcement) {
            return redirect()->to('/admin/announcements')->with('error', 'Pengumuman tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Pengumuman - Admin Dashboard',
            'page_title' => 'Edit Pengumuman',
            'sidebar_title' => 'UrunanKita',
            'user_name' => session()->get('user_name') ?? 'Super Admin',
            'user_role' => 'Super Admin',
            'announcement' => $announcement,
        ];

        return view('Modules\\Announcement\\Views\\admin_form', $data);
    }

    /**
     * Admin: Update announcement
     * POST /admin/announcements/{id}/update
     */
    public function update(int $id)
    {
        $announcement = $this->announcementService->getById($id);
        if (!$announcement) {
            return redirect()->to('/admin/announcements')->with('error', 'Pengumuman tidak ditemukan');
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'type' => $this->request->getPost('type') ?? 'info',
            'priority' => $this->request->getPost('priority') ?? 'normal',
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
            'expires_at' => $this->request->getPost('expires_at') ?: null,
        ];

        if (empty($data['title']) || empty($data['content'])) {
            return redirect()->back()->withInput()->with('error', 'Judul dan konten wajib diisi');
        }

        try {
            $result = $this->announcementService->update($id, $data);
            if ($result) {
                return redirect()->to('/admin/announcements')->with('success', 'Pengumuman berhasil diperbarui');
            }
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengumuman');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin: Delete announcement
     * POST /admin/announcements/{id}/delete
     */
    public function delete(int $id)
    {
        $announcement = $this->announcementService->getById($id);
        if (!$announcement) {
            return redirect()->to('/admin/announcements')->with('error', 'Pengumuman tidak ditemukan');
        }

        try {
            $result = $this->announcementService->delete($id);
            if ($result) {
                return redirect()->to('/admin/announcements')->with('success', 'Pengumuman berhasil dihapus');
            }
            return redirect()->back()->with('error', 'Gagal menghapus pengumuman');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

